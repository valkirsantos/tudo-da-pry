<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\TooManyOtpAttemptsException;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Contracts\Redis\Factory as RedisFactory;

class OtpService
{
    private const int TTL = 300;
    private const int MAX_ATTEMPTS = 3;

    public function __construct(
        private readonly RedisFactory $redis,
        private readonly Hasher $hasher,
    ) {}

    private function conn(): \Illuminate\Redis\Connections\Connection
    {
        return $this->redis->connection();
    }

    public function generate(string $celular): string
    {
        $codigo = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $this->conn()->setex("otp:{$celular}", self::TTL, $this->hasher->make($codigo));
        $this->conn()->setex("otp_tries:{$celular}", self::TTL, '0');

        return $codigo;
    }

    /**
     * @throws TooManyOtpAttemptsException
     */
    public function verify(string $celular, string $codigo): bool
    {
        $conn = $this->conn();

        $tries = (int) ($conn->get("otp_tries:{$celular}") ?? 0);

        if ($tries >= self::MAX_ATTEMPTS) {
            throw new TooManyOtpAttemptsException();
        }

        $conn->incr("otp_tries:{$celular}");

        $hash = $conn->get("otp:{$celular}");

        if (! $hash || ! $this->hasher->check($codigo, $hash)) {
            return false;
        }

        $conn->del("otp:{$celular}", "otp_tries:{$celular}");

        return true;
    }
}
