<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MercadoPagoWebhookController extends Controller
{
    public function health()
    {
        $publicKey = config('services.mercadopago.public_key');
        $secretKey = config('services.mercadopago.secret_key');

        $isConfigured = !empty($publicKey) && !empty($secretKey);

        return response()->json([
            'status' => $isConfigured ? 'healthy' : 'unconfigured',
            'message' => $isConfigured ? 'Mercado Pago webhook ready' : 'Mercado Pago credentials missing',
        ]);
    }

    public function __invoke(Request $request)
    {
        $payload = $request->all();

        Log::info('Mercado Pago webhook received', [
            'topic' => $payload['topic'] ?? null,
            'id' => $payload['id'] ?? null,
            'data' => $payload['data'] ?? [],
        ]);

        // Mercado Pago envía webhooks con topic (payment.created, payment.updated)
        $topic = $payload['topic'] ?? null;
        $paymentData = $payload['data'] ?? [];

        if ($topic !== 'payment.updated' || empty($paymentData['id'])) {
            Log::warning('Ignored Mercado Pago webhook', ['topic' => $topic]);
            return response()->json(['status' => 'ignored']);
        }

        $mpPaymentId = (string) $paymentData['id'];

        // Buscar el pago en nuestra BD por el ID de Mercado Pago
        $payment = Payment::where('transaction_id', $mpPaymentId)
            ->orWhere('response->id', $mpPaymentId)
            ->first();

        if (!$payment) {
            Log::warning('Payment not found for Mercado Pago ID', ['mp_id' => $mpPaymentId]);
            return response()->json(['status' => 'not_found']);
        }

        // Mapear status de Mercado Pago a nuestro status
        $mpStatus = $paymentData['status'] ?? null;
        $newStatus = $this->mapMercadoPagoStatus($mpStatus);

        if ($newStatus && $newStatus !== $payment->status) {
            $payment->update([
                'status' => $newStatus,
                'response' => $payment->response ? array_merge($payment->response, $paymentData) : $paymentData,
            ]);

            // Si fue aprobado, actualizar orden a processing
            if ($newStatus === 'paid') {
                $payment->order?->update(['status' => 'processing']);
            }

            Log::info('Payment updated from Mercado Pago webhook', [
                'payment_id' => $payment->id,
                'mp_id' => $mpPaymentId,
                'status' => $newStatus,
            ]);
        }

        return response()->json(['status' => 'processed']);
    }

    private function mapMercadoPagoStatus(?string $mpStatus): ?string
    {
        return match ($mpStatus) {
            'approved', 'authorized' => 'paid',
            'rejected', 'cancelled' => 'declined',
            'refunded' => 'refunded',
            'pending_client_review', 'pending_contingency', 'in_process' => 'pending',
            default => null,
        };
    }
}
