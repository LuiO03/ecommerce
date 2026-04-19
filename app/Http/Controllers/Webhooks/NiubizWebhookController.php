<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NiubizWebhookController extends Controller
{
    public function health(): JsonResponse
    {
        $hasBaseConfig = (string) config('services.niubiz.url_api', '') !== ''
            && (string) config('services.niubiz.merchant_id', '') !== ''
            && (string) config('services.niubiz.user', '') !== ''
            && (string) config('services.niubiz.password', '') !== '';

        return response()->json([
            'status' => $hasBaseConfig ? 'ok' : 'degraded',
            'provider' => 'niubiz',
            'checks' => [
                'base_config' => $hasBaseConfig,
                'signature_validation_enabled' => (string) config('services.niubiz.webhook_secret', '') !== '',
            ],
        ], 200);
    }

    public function __invoke(Request $request): JsonResponse
    {
        $payload = $request->json()->all();
        $correlationId = $this->resolveCorrelationId($request, $payload);

        if (empty($payload)) {
            Log::info('Niubiz webhook ignored empty payload', [
                'correlation_id' => $correlationId,
            ]);

            return response()->json(['status' => 'ignored', 'message' => 'Payload vacío'], 200);
        }

        if (! $this->verifySignature($request)) {
            Log::warning('Niubiz webhook invalid signature', [
                'correlation_id' => $correlationId,
            ]);

            return response()->json(['status' => 'error', 'message' => 'Firma inválida'], 401);
        }

        $transactionId = (string) (
            $payload['dataMap']['TRANSACTION_ID']
            ?? $payload['data']['TRANSACTION_ID']
            ?? $payload['order']['transactionId']
            ?? ''
        );

        if ($transactionId === '') {
            Log::info('Niubiz webhook ignored without transaction id', [
                'correlation_id' => $correlationId,
            ]);

            return response()->json(['status' => 'ignored', 'message' => 'transaction_id no encontrado'], 200);
        }

        $payment = Payment::where('provider', 'niubiz')
            ->where('transaction_id', $transactionId)
            ->latest('id')
            ->first();

        if (! $payment) {
            Log::warning('Niubiz webhook payment not found', [
                'correlation_id' => $correlationId,
                'transaction_id' => $transactionId,
                'payload' => $payload,
            ]);

            return response()->json(['status' => 'ignored', 'message' => 'payment no encontrado'], 200);
        }

        $mappedStatus = $this->mapPaymentStatus($payload);

        $payment->update([
            'status' => $mappedStatus,
            'paid_at' => $mappedStatus === 'paid' ? ($payment->paid_at ?? now()) : $payment->paid_at,
            'response' => $payload,
        ]);

        if ($payment->order && $mappedStatus === 'paid' && $payment->order->status === 'pending') {
            $payment->order->update(['status' => 'processing']);
        }

        Log::info('Niubiz webhook processed', [
            'correlation_id' => $correlationId,
            'payment_id' => $payment->id,
            'order_id' => $payment->order_id,
            'transaction_id' => $transactionId,
            'mapped_status' => $mappedStatus,
        ]);

        return response()->json(['status' => 'ok'], 200);
    }

    private function verifySignature(Request $request): bool
    {
        $secret = (string) config('services.niubiz.webhook_secret', '');

        if ($secret === '') {
            return true;
        }

        $received = (string) $request->header('X-Niubiz-Signature', '');

        if ($received === '') {
            return false;
        }

        $expected = hash_hmac('sha256', $request->getContent(), $secret);

        return hash_equals($expected, $received);
    }

    private function mapPaymentStatus(array $payload): string
    {
        $dataMap = $payload['dataMap'] ?? [];
        $data = $payload['data'] ?? [];

        $actionCode = (string) (
            $dataMap['ACTION_CODE']
            ?? $dataMap['ACTIONCODE']
            ?? $data['ACTION_CODE']
            ?? $data['ACTIONCODE']
            ?? ''
        );

        $status = strtoupper((string) ($dataMap['STATUS'] ?? $data['STATUS'] ?? ''));

        if (in_array($actionCode, ['000', '010'], true) || in_array($status, ['AUTHORIZED', 'AUTHORIZED AND COMPLETED WITH SUCCESS'], true)) {
            return 'paid';
        }

        if (in_array($status, ['REFUNDED', 'VOIDED'], true)) {
            return 'refunded';
        }

        return 'failed';
    }

    private function resolveCorrelationId(Request $request, array $payload): string
    {
        return (string) (
            $request->header('X-Correlation-Id')
            ?? ($payload['meta']['correlation_id'] ?? null)
            ?? ($payload['dataMap']['TRANSACTION_ID'] ?? null)
            ?? ($payload['data']['TRANSACTION_ID'] ?? null)
            ?? ($payload['order']['transactionId'] ?? null)
            ?? uniqid('wh_', true)
        );
    }
}
