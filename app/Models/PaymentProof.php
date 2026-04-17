<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentProof extends Model
{
    protected $fillable = [
        'order_id',
        'installment_id',
        'path_s3',
        'nome_arquivo',
        'tamanho_bytes',
        'status',
        'motivo_rejeicao',
        'validado_em',
        'validado_por',
    ];

    protected function casts(): array
    {
        return [
            'validado_em' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function installment(): BelongsTo
    {
        return $this->belongsTo(Installment::class);
    }

    public function validatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validado_por');
    }
}
