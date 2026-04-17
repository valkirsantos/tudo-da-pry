<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'nome',
        'celular',
        'email',
        'role',
        'push_subscription',
        'ativo',
    ];

    protected $hidden = ['remember_token'];

    protected function casts(): array
    {
        return [
            'push_subscription' => 'array',
            'ativo' => 'boolean',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function createdOrders(): HasMany
    {
        return $this->hasMany(Order::class, 'created_by');
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'created_by');
    }

    public function broadcasts(): HasMany
    {
        return $this->hasMany(NotificationBroadcast::class, 'created_by');
    }

    public function isVendedor(): bool
    {
        return $this->role === 'vendedor';
    }

    public function isCliente(): bool
    {
        return $this->role === 'cliente';
    }
}
