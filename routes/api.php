<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Webhooks\CulqiWebhookController;
use App\Http\Controllers\Webhooks\MercadoPagoWebhookController;
use App\Http\Controllers\Webhooks\NiubizWebhookController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/webhooks/niubiz/health', [NiubizWebhookController::class, 'health'])
    ->name('api.webhooks.niubiz.health');

Route::post('/webhooks/niubiz', NiubizWebhookController::class)->name('api.webhooks.niubiz');

Route::get('/webhooks/culqi/health', [CulqiWebhookController::class, 'health'])
    ->name('api.webhooks.culqi.health');

Route::post('/webhooks/culqi', CulqiWebhookController::class)->name('api.webhooks.culqi');

Route::get('/webhooks/mercadopago/health', [MercadoPagoWebhookController::class, 'health'])
    ->name('api.webhooks.mercadopago.health');

Route::post('/webhooks/mercadopago', MercadoPagoWebhookController::class)->name('api.webhooks.mercadopago');
