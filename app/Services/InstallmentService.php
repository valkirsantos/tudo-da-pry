<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Installment;
use App\Models\Order;
use Carbon\Carbon;

class InstallmentService
{
    /**
     * Generate installments for a parcelado order.
     *
     * @return Installment[]
     */
    public function generate(Order $order): array
    {
        $total = (int) round($order->total * 100); // work in cents
        $n = $order->num_parcelas;
        $dia = $order->dia_vencimento;

        $valorBase = intdiv($total, $n);
        $resto = $total - ($valorBase * $n);

        $installments = [];

        for ($i = 1; $i <= $n; $i++) {
            $valor = $valorBase + ($i === 1 ? $resto : 0);

            $vencimento = Carbon::now()->addMonthsNoOverflow($i)->day($dia);

            // If day doesn't exist in month (e.g. 31 in February), use last day
            $maxDay = $vencimento->daysInMonth;
            if ($dia > $maxDay) {
                $vencimento->day($maxDay);
            }

            $installments[] = Installment::create([
                'order_id' => $order->id,
                'numero_parcela' => $i,
                'valor' => $valor / 100,
                'data_vencimento' => $vencimento->toDateString(),
                'status' => 'pendente',
            ]);
        }

        return $installments;
    }
}
