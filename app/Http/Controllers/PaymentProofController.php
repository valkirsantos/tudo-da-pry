<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Events\ComprovantEnviado;
use App\Models\Notification;
use App\Models\Order;
use App\Models\PaymentProof;
use App\Services\S3PresignedService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PaymentProofController extends Controller
{
    private const MIME_EXTENSIONS = [
        'image/jpeg'      => 'jpg',
        'image/png'       => 'png',
        'application/pdf' => 'pdf',
    ];

    public function __construct(private readonly S3PresignedService $s3Service) {}

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'order_id'       => ['required', 'integer', 'exists:orders,id'],
            'installment_id' => ['nullable', 'integer', 'exists:installments,id'],
            'nome_arquivo'   => ['required', 'string', 'max:255'],
            'tamanho_bytes'  => ['required', 'integer', 'min:1', 'max:5242880'],
            'mime_type'      => ['required', 'string', 'in:image/jpeg,image/png,application/pdf'],
        ]);

        $user  = $request->user();
        $order = Order::findOrFail($data['order_id']);

        if ($user->isCliente() && $order->user_id !== $user->id) {
            return response()->json(['error' => true, 'message' => 'Pedido não encontrado.'], 404);
        }

        $ext    = self::MIME_EXTENSIONS[$data['mime_type']];
        $uuid   = Str::uuid()->toString();
        $path   = "comprovantes/{$order->id}/{$uuid}.{$ext}";

        $proof = PaymentProof::create([
            'order_id'       => $order->id,
            'installment_id' => $data['installment_id'] ?? null,
            'path_s3'        => $path,
            'nome_arquivo'   => $data['nome_arquivo'],
            'tamanho_bytes'  => $data['tamanho_bytes'],
            'status'         => 'pendente',
        ]);

        $uploadUrl = empty(config('filesystems.disks.s3.bucket'))
            ? null
            : $this->s3Service->generateUploadUrl($path, $data['mime_type']);

        ComprovantEnviado::dispatch($proof->load('order'));

        return response()->json([
            'data' => [
                'upload_url' => $uploadUrl,
                'proof_id'   => $proof->id,
            ],
        ], 201);
    }

    public function index(Request $request): JsonResponse
    {
        $query = PaymentProof::with(['order.user'])
            ->latest();

        if ($request->filled('tipo')) {
            $tipo = $request->input('tipo');
            if ($tipo === 'avista') {
                $query->whereNull('installment_id');
            } elseif ($tipo === 'parcela') {
                $query->whereNotNull('installment_id');
            }
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $proofs = $query->paginate(20);

        $data = $proofs->getCollection()->map(function (PaymentProof $proof) {
            return [
                'id'             => $proof->id,
                'order_id'       => $proof->order_id,
                'installment_id' => $proof->installment_id,
                'nome_arquivo'   => $proof->nome_arquivo,
                'tamanho_bytes'  => $proof->tamanho_bytes,
                'status'         => $proof->status,
                'motivo_rejeicao'=> $proof->motivo_rejeicao,
                'validado_em'    => $proof->validado_em,
                'created_at'     => $proof->created_at,
                'cliente'        => $proof->order?->user ? [
                    'id'      => $proof->order->user->id,
                    'nome'    => $proof->order->user->nome,
                    'celular' => $proof->order->user->celular,
                ] : null,
                'download_url'   => $this->safeDownloadUrl($proof->path_s3),
            ];
        });

        return response()->json([
            'data' => $data,
            'meta' => [
                'current_page' => $proofs->currentPage(),
                'last_page'    => $proofs->lastPage(),
                'per_page'     => $proofs->perPage(),
                'total'        => $proofs->total(),
            ],
        ]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'action' => ['required', 'in:approve,reject'],
            'motivo' => ['required_if:action,reject', 'nullable', 'string', 'max:500'],
        ]);

        $proof = PaymentProof::with(['order', 'installment'])->findOrFail($id);

        if ($proof->status !== 'pendente') {
            return response()->json(['error' => true, 'message' => 'Este comprovante já foi processado.'], 422);
        }

        DB::transaction(function () use ($data, $proof, $request) {
            $isApprove = $data['action'] === 'approve';

            $proof->update([
                'status'          => $isApprove ? 'aprovado' : 'rejeitado',
                'motivo_rejeicao' => $isApprove ? null : ($data['motivo'] ?? null),
                'validado_em'     => now(),
                'validado_por'    => $request->user()->id,
            ]);

            if ($isApprove) {
                $this->processApproval($proof);
            }

            $this->notifyClient($proof, $isApprove);
        });

        return response()->json([
            'data'    => $proof->fresh(),
            'message' => $data['action'] === 'approve' ? 'Comprovante aprovado.' : 'Comprovante rejeitado.',
        ]);
    }

    public function storeFile(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'max:5120', 'mimes:jpeg,png,pdf'],
        ]);

        $proof = PaymentProof::findOrFail($id);

        if ($request->user()->isCliente() && $proof->order->user_id !== $request->user()->id) {
            return response()->json(['error' => true, 'message' => 'Não autorizado.'], 403);
        }

        Storage::disk('public')->putFileAs(
            dirname($proof->path_s3),
            $request->file('file'),
            basename($proof->path_s3)
        );

        return response()->json(['data' => ['stored' => true]]);
    }

    private function safeDownloadUrl(string $path): ?string
    {
        if (empty(config('filesystems.disks.s3.bucket'))) {
            return Storage::disk('public')->exists($path)
                ? Storage::disk('public')->url($path)
                : null;
        }

        try {
            return $this->s3Service->generateDownloadUrl($path);
        } catch (\Throwable) {
            return null;
        }
    }

    private function processApproval(PaymentProof $proof): void
    {
        $order = $proof->order;

        if ($proof->installment_id === null) {
            $order->update(['status_pagamento' => 'pago']);
            return;
        }

        $proof->installment->update(['status' => 'pago']);

        $allPaid = $order->installments()
            ->where('status', '!=', 'pago')
            ->doesntExist();

        if ($allPaid) {
            $order->update(['status_pagamento' => 'pago']);
        } else {
            $order->update(['status_pagamento' => 'parcial']);
        }
    }

    private function notifyClient(PaymentProof $proof, bool $approved): void
    {
        $titulo   = $approved ? 'Comprovante aprovado' : 'Comprovante rejeitado';
        $mensagem = $approved
            ? "Seu comprovante de pagamento para o pedido #{$proof->order_id} foi aprovado."
            : "Seu comprovante de pagamento para o pedido #{$proof->order_id} foi rejeitado. Motivo: {$proof->motivo_rejeicao}";

        Notification::create([
            'user_id'      => $proof->order->user_id,
            'tipo'         => $approved ? 'comprovante_aprovado' : 'comprovante_rejeitado',
            'titulo'       => $titulo,
            'mensagem'     => $mensagem,
            'ref_type'     => 'payment_proof',
            'ref_id'       => $proof->id,
            'lida'         => false,
            'enviada_push' => false,
        ]);
    }
}
