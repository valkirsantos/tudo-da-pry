<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\PaymentProof;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ComprovantEnviado
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly PaymentProof $proof) {}
}
