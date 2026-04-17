<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('tipo');
            $table->string('titulo');
            $table->text('mensagem');
            $table->string('ref_type')->nullable(); // order|installment|payment_proof|broadcast
            $table->unsignedBigInteger('ref_id')->nullable();
            $table->boolean('lida')->default(false);
            $table->boolean('enviada_push')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
