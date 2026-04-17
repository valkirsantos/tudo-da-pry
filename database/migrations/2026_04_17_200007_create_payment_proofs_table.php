<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_proofs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained();
            $table->foreignId('installment_id')->nullable()->constrained();
            $table->string('path_s3');
            $table->string('nome_arquivo');
            $table->integer('tamanho_bytes');
            $table->string('status')->default('pendente'); // pendente|aprovado|rejeitado
            $table->text('motivo_rejeicao')->nullable();
            $table->timestamp('validado_em')->nullable();
            $table->foreignId('validado_por')->nullable()->constrained('users');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_proofs');
    }
};
