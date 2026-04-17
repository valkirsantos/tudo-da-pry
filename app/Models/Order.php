<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'created_by',
        'total',
        'tipo_pagamento',
        'num_parcelas',
        'dia_vencimento',
        'status_pedido',
        'status_pagamento',
        'endereco_entrega',
        'observacoes',
    ];

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
            'endereco_entrega' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function installments(): HasMany
    {
        return $this->hasMany(Installment::class)->orderBy('numero_parcela');
    }

    public function paymentProofs(): HasMany
    {
        return $this->hasMany(PaymentProof::class);
    }
}
