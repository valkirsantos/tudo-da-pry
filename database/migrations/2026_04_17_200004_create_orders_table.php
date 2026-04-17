<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users');
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->decimal('total', 10, 2);
            $table->string('tipo_pagamento'); // pix|dinheiro|parcelado
            $table->integer('num_parcelas')->nullable();
            $table->integer('dia_vencimento')->nullable();
            $table->string('status_pedido')->default('aguardando_pagamento');
            // aguardando_pagamento|confirmado|separando|em_entrega|entregue|cancelado
            $table->string('status_pagamento')->default('pendente');
            // pendente|parcial|pago
            $table->json('endereco_entrega')->nullable();
            $table->text('observacoes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
