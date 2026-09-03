<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'platform' => 'Zacma AI Marketplace + CRM SaaS',
        'version' => '1.0.0',
        'timestamp' => now()->toIso8601String(),
    ]);
});

Route::post('/webhooks/payment/{provider}', [\App\Http\Controllers\PaymentController::class, 'handleWebhook'])->name('api.payment.webhook');
