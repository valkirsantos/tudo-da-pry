<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'tipo',
        'titulo',
        'mensagem',
        'ref_type',
        'ref_id',
        'lida',
        'enviada_push',
    ];

    protected function casts(): array
    {
        return [
            'lida' => 'boolean',
            'enviada_push' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
