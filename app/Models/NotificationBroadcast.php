<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationBroadcast extends Model
{
    protected $fillable = [
        'created_by',
        'tipo',
        'titulo',
        'mensagem',
        'publico_alvo',
        'enviado_em',
        'total_enviados',
    ];

    protected function casts(): array
    {
        return [
            'publico_alvo' => 'array',
            'enviado_em' => 'datetime',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
