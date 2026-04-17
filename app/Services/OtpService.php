<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Support\Facades\Redis;
use RuntimeException;

class OtpService
{
    private const TTL = 300; // 5 minutes
    private const MAX_ATTEMPTS = 3;

    public function generate(string $celular): string
    {
        $codigo = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        Redis::setex("otp:{$celular}", self::TTL, bcrypt($codigo));
        Redis::setex("otp_tries:{$celular}", self::TTL, '0');

        return $codigo;
    }

    public function verify(string $celular, string $codigo): bool
    {
        $tries = (int) (Redis::get("otp_tries:{$celular}") ?? 0);

        if ($tries >= self::MAX_ATTEMPTS) {
            throw new RuntimeException('Muitas tentativas. Solicite um novo código.');
        }

        Redis::incr("otp_tries:{$celular}");

        $hash = Redis::get("otp:{$celular}");

        if (! $hash || ! password_verify($codigo, $hash)) {
            return false;
        }

        Redis::del("otp:{$celular}", "otp_tries:{$celular}");

        return true;
    }
}
