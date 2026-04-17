<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client as TwilioClient;

class SendOtpSms implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $celular,
        private readonly string $codigo,
    ) {}

    public function handle(): void
    {
        $sid = config('services.twilio.sid');
        $mensagem = "Tudo da Pry: seu código é {$this->codigo}. Válido por 5 minutos.";
        $numero = '+55' . $this->celular;

        if (empty($sid)) {
            Log::info('[OTP DEV] SMS para ' . $numero . ': ' . $mensagem);
            return;
        }

        $client = new TwilioClient(
            $sid,
            config('services.twilio.token'),
        );

        $client->messages->create($numero, [
            'from' => config('services.twilio.from'),
            'body' => $mensagem,
        ]);
    }
}
