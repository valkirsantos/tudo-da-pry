<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\OtpController;
use App\Http\Controllers\BroadcastController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DeliveryController;
use App\Http\Controllers\InstallmentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentProofController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    // Autenticação (pública)
    Route::post('/auth/send-otp', [OtpController::class, 'send']);
    Route::post('/auth/verify-otp', [OtpController::class, 'verify']);

    // Catálogo (pública)
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{id}', [ProductController::class, 'show']);

    // Área autenticada (cliente e vendedor)
    Route::middleware(['auth:sanctum'])->group(function () {
        Route::get('/orders', [OrderController::class, 'index']);
        Route::post('/orders', [OrderController::class, 'store']);
        Route::get('/orders/{id}', [OrderController::class, 'show']);
        Route::get('/orders/{id}/installments', [InstallmentController::class, 'index']);
        Route::post('/payment-proofs', [PaymentProofController::class, 'store']);
        Route::post('/payment-proofs/{id}/file', [PaymentProofController::class, 'storeFile']);
        Route::get('/notifications', [NotificationController::class, 'index']);
        Route::post('/notifications/read', [NotificationController::class, 'markRead']);
        Route::post('/push/subscribe', [NotificationController::class, 'subscribe']);
    });

    // Área da vendedora
    Route::middleware(['auth:sanctum', 'role:vendedor'])->group(function () {
        Route::post('/products', [ProductController::class, 'store']);
        Route::put('/products/{id}', [ProductController::class, 'update']);
        Route::delete('/products/{id}', [ProductController::class, 'destroy']);

        Route::get('/clients', [ClientController::class, 'index']);
        Route::post('/clients', [ClientController::class, 'store']);

        Route::get('/payment-proofs', [PaymentProofController::class, 'index']);
        Route::put('/payment-proofs/{id}', [PaymentProofController::class, 'update']);

        Route::put('/orders/{id}/status', [OrderController::class, 'updateStatus']);
        Route::put('/orders/{id}/delivery', [DeliveryController::class, 'update']);

        Route::get('/reports/sales', [ReportController::class, 'sales']);
        Route::get('/reports/installments', [ReportController::class, 'installments']);

        Route::post('/broadcasts', [BroadcastController::class, 'store']);
        Route::get('/broadcasts', [BroadcastController::class, 'index']);

        Route::post('/orders/seller', [OrderController::class, 'storeBySeller']);
    });
});
