<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\Installment;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

class EnviarAlertaVencimento implements ShouldQueue
{
    use Queueable;

    public function handle(): void
    {
        $amanha = Carbon::tomorrow()->toDateString();

        Installment::with('order.user')
            ->whereDate('data_vencimento', $amanha)
            ->where('status', 'pendente')
            ->each(function (Installment $installment): void {
                $this->notificarCliente($installment);
            });
    }

    private function notificarCliente(Installment $installment): void
    {
        $user = $installment->order?->user;

        if ($user === null) {
            return;
        }

        $notificacao = Notification::create([
            'user_id'      => $user->id,
            'tipo'         => 'parcela_vencendo',
            'titulo'       => 'Parcela vence amanhã',
            'mensagem'     => "A parcela {$installment->numero_parcela} do pedido #{$installment->order_id} no valor de R$ {$installment->valor} vence amanhã.",
            'ref_type'     => 'installment',
            'ref_id'       => $installment->id,
            'lida'         => false,
            'enviada_push' => false,
        ]);

        $this->enviarWebPush($user, $notificacao->titulo, $notificacao->mensagem, $notificacao->id);
    }

    private function enviarWebPush(
        \App\Models\User $user,
        string $titulo,
        string $mensagem,
        int $notificacaoId,
    ): void {
        if (empty($user->push_subscription) || empty(config('services.vapid.public_key'))) {
            return;
        }

        try {
            $webPush = new WebPush([
                'VAPID' => [
                    'subject'    => config('services.vapid.subject'),
                    'publicKey'  => config('services.vapid.public_key'),
                    'privateKey' => config('services.vapid.private_key'),
                ],
            ]);

            $subscription = Subscription::create($user->push_subscription);

            $payload = json_encode([
                'title'           => $titulo,
                'body'            => $mensagem,
                'notificacao_id'  => $notificacaoId,
            ]);

            $webPush->queueNotification($subscription, $payload);

            foreach ($webPush->flush() as $report) {
                if ($report->isSuccess()) {
                    \App\Models\Notification::where('id', $notificacaoId)
                        ->update(['enviada_push' => true]);
                } else {
                    Log::warning('[WebPush] Falha ao enviar', [
                        'user_id' => $user->id,
                        'reason'  => $report->getReason(),
                    ]);
                }
            }
        } catch (\Throwable $e) {
            Log::error('[WebPush] Erro', ['user_id' => $user->id, 'error' => $e->getMessage()]);
        }
    }
}
