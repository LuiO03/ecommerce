<?php

namespace Tests\Feature;

use App\Http\Controllers\Webhooks\CulqiWebhookController;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class CulqiWebhookControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_endpoint_returns_provider_status(): void
    {
        config([
            'services.culqi.secret_key' => 'sk_test_123',
            'services.culqi.base_url' => 'https://api.culqi.test',
            'services.culqi.webhook_secret' => 'secret-health',
        ]);

        $controller = app(CulqiWebhookController::class);
        $response = $controller->health();
        $data = $response->getData(true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('ok', $data['status']);
        $this->assertSame('culqi', $data['provider']);
        $this->assertTrue($data['checks']['base_config']);
        $this->assertTrue($data['checks']['signature_validation_enabled']);
    }

    public function test_webhook_rejects_invalid_signature_when_secret_is_enabled(): void
    {
        config(['services.culqi.webhook_secret' => 'my-webhook-secret']);

        $payload = [
            'type' => 'charge.captured',
            'data' => [
                'id' => 'CH-INVALID-SIGNATURE',
                'outcome' => [
                    'type' => 'venta_exitosa',
                ],
            ],
        ];

        $request = Request::create('/api/webhooks/culqi', 'POST', [], [], [], [
            'HTTP_X_CULQI_SIGNATURE' => 'invalid-signature',
            'CONTENT_TYPE' => 'application/json',
        ], json_encode($payload));

        $controller = app(CulqiWebhookController::class);
        $response = $controller($request);
        $data = $response->getData(true);

        $this->assertSame(401, $response->getStatusCode());
        $this->assertSame('error', $data['status']);
    }

    public function test_webhook_marks_payment_paid_and_moves_order_to_processing(): void
    {
        config(['services.culqi.webhook_secret' => 'my-webhook-secret']);

        $user = User::factory()->create();

        $order = Order::query()->create([
            'user_id' => $user->id,
            'order_number' => 'ORD-WH-' . now()->format('YmdHis') . '-1',
            'total' => 100.00,
            'subtotal' => 95.00,
            'shipping_cost' => 5.00,
            'status' => 'pending',
            'shipping_address' => 'Av. Prueba 123',
            'shipping_city' => 'Lima',
            'shipping_phone' => '999999999',
        ]);

        Payment::query()->create([
            'order_id' => $order->id,
            'provider' => 'culqi',
            'transaction_id' => 'CH-PAID-123',
            'amount' => 100.00,
            'status' => 'pending',
        ]);

        $payload = [
            'type' => 'charge.captured',
            'data' => [
                'id' => 'CH-PAID-123',
                'outcome' => [
                    'type' => 'venta_exitosa',
                ],
            ],
        ];

        $rawPayload = json_encode($payload);
        $signature = hash_hmac('sha256', $rawPayload, 'my-webhook-secret');

        $request = Request::create('/api/webhooks/culqi', 'POST', [], [], [], [
            'HTTP_X_CULQI_SIGNATURE' => $signature,
            'CONTENT_TYPE' => 'application/json',
        ], $rawPayload);

        $controller = app(CulqiWebhookController::class);
        $response = $controller($request);
        $data = $response->getData(true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('ok', $data['status']);

        $this->assertDatabaseHas('payments', [
            'provider' => 'culqi',
            'transaction_id' => 'CH-PAID-123',
            'status' => 'paid',
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'processing',
        ]);
    }
}
