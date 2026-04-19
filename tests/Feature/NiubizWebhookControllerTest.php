<?php

namespace Tests\Feature;

use App\Http\Controllers\Webhooks\NiubizWebhookController;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class NiubizWebhookControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_endpoint_returns_provider_status(): void
    {
        config([
            'services.niubiz.url_api' => 'https://api.niubiz.test',
            'services.niubiz.merchant_id' => '123456789',
            'services.niubiz.user' => 'integration-user',
            'services.niubiz.password' => 'integration-password',
            'services.niubiz.webhook_secret' => 'secret-health',
        ]);

        $controller = app(NiubizWebhookController::class);
        $response = $controller->health();
        $data = $response->getData(true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('ok', $data['status']);
        $this->assertSame('niubiz', $data['provider']);
        $this->assertTrue($data['checks']['base_config']);
        $this->assertTrue($data['checks']['signature_validation_enabled']);
    }

    public function test_webhook_rejects_invalid_signature_when_secret_is_enabled(): void
    {
        config(['services.niubiz.webhook_secret' => 'my-webhook-secret']);

        $payload = [
            'dataMap' => [
                'TRANSACTION_ID' => 'TX-INVALID-SIGNATURE',
                'ACTION_CODE' => '000',
                'STATUS' => 'AUTHORIZED',
            ],
        ];

        $request = Request::create('/api/webhooks/niubiz', 'POST', [], [], [], [
            'HTTP_X_NIUBIZ_SIGNATURE' => 'invalid-signature',
            'CONTENT_TYPE' => 'application/json',
        ], json_encode($payload));

        $controller = app(NiubizWebhookController::class);
        $response = $controller($request);
        $data = $response->getData(true);

        $this->assertSame(401, $response->getStatusCode());
        $this->assertSame('error', $data['status']);
    }

    public function test_webhook_marks_payment_paid_and_moves_order_to_processing(): void
    {
        config(['services.niubiz.webhook_secret' => 'my-webhook-secret']);

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
            'provider' => 'niubiz',
            'transaction_id' => 'TX-PAID-123',
            'amount' => 100.00,
            'status' => 'pending',
        ]);

        $payload = [
            'dataMap' => [
                'TRANSACTION_ID' => 'TX-PAID-123',
                'ACTION_CODE' => '000',
                'STATUS' => 'AUTHORIZED',
            ],
        ];

        $signature = hash_hmac('sha256', json_encode($payload), 'my-webhook-secret');

        $request = Request::create('/api/webhooks/niubiz', 'POST', [], [], [], [
            'HTTP_X_NIUBIZ_SIGNATURE' => $signature,
            'CONTENT_TYPE' => 'application/json',
        ], json_encode($payload));

        $controller = app(NiubizWebhookController::class);
        $response = $controller($request);
        $data = $response->getData(true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('ok', $data['status']);

        $this->assertDatabaseHas('payments', [
            'provider' => 'niubiz',
            'transaction_id' => 'TX-PAID-123',
            'status' => 'paid',
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'processing',
        ]);
    }
}
