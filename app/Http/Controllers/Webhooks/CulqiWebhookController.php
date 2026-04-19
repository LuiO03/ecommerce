<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CulqiWebhookController extends Controller
{
    public function health(): JsonResponse
    {
        $hasBaseConfig = (string) config('services.culqi.secret_key', '') !== ''
            && (string) config('services.culqi.base_url', '') !== '';

        return response()->json([
            'status' => $hasBaseConfig ? 'ok' : 'degraded',
            'provider' => 'culqi',
            'checks' => [
                'base_config' => $hasBaseConfig,
                'signature_validation_enabled' => (string) config('services.culqi.webhook_secret', '') !== '',
            ],
        ], 200);
    }

    public function __invoke(Request $request): JsonResponse
    {
        $payload = $request->json()->all();

        if (empty($payload)) {
            return response()->json(['status' => 'ignored', 'message' => 'Payload vacío'], 200);
        }

        if (! $this->verifySignature($request)) {
            return response()->json(['status' => 'error', 'message' => 'Firma inválida'], 401);
        }

        $data = $payload['data'] ?? [];
        $transactionId = (string) ($data['id'] ?? '');
        if ($transactionId === '') {
            return response()->json(['status' => 'ignored', 'message' => 'transaction_id no encontrado'], 200);
        }

        $payment = Payment::where('provider', 'culqi')
            ->where('transaction_id', $transactionId)
            ->latest('id')
            ->first();

        if (! $payment) {
            Log::warning('Culqi webhook payment not found', [
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

        return response()->json(['status' => 'ok'], 200);
    }

    private function verifySignature(Request $request): bool
    {
        $secret = (string) config('services.culqi.webhook_secret', '');
        if ($secret === '') {
            return true;
        }

        $received = (string) $request->header('X-Culqi-Signature', '');
        if ($received === '') {
            return false;
        }

        $expected = hash_hmac('sha256', $request->getContent(), $secret);

        return hash_equals($expected, $received);
    }

    private function mapPaymentStatus(array $payload): string
    {
        $event = mb_strtolower((string) ($payload['type'] ?? ''));
        $outcome = mb_strtolower((string) ($payload['data']['outcome']['type'] ?? ''));

        if (in_array($event, ['charge.captured', 'charge.paid'], true) || in_array($outcome, ['venta_exitosa', 'authorized'], true)) {
            return 'paid';
        }

        if (in_array($event, ['charge.refunded', 'refund.created'], true)) {
            return 'refunded';
        }

        return 'failed';
    }
}
