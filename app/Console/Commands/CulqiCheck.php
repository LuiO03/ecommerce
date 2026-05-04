<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class CulqiCheck extends Command
{
    protected $signature = 'culqi:check {--timeout=20 : Timeout (segundos) para el request HTTP}';

    protected $description = 'Diagnostica credenciales de Culqi y valida autenticación contra el API (sandbox/producción)';

    public function handle(): int
    {
        $publicKey = (string) config('services.culqi.public_key');
        $secretKey = (string) config('services.culqi.secret_key');
        $baseUrl = rtrim((string) config('services.culqi.base_url'), '/');
        $checkoutUrl = (string) config('services.culqi.checkout_url');
        $webhookSecret = (string) config('services.culqi.webhook_secret');
        $timeout = (int) $this->option('timeout');

        $this->line('=== Culqi Check ===');
        $this->line('public_key: ' . ($publicKey !== '' ? $this->mask($publicKey) : '(vacio)'));
        $this->line('secret_key: ' . ($secretKey !== '' ? $this->mask($secretKey) : '(vacio)'));
        $this->line('base_url: ' . ($baseUrl !== '' ? $baseUrl : '(vacio)'));
        $this->line('checkout_url: ' . ($checkoutUrl !== '' ? $checkoutUrl : '(vacio)'));
        $this->line('webhook_secret: ' . ($webhookSecret !== '' ? '(configurado)' : '(no configurado)'));
        $this->newLine();

        if ($publicKey === '' || $secretKey === '' || $baseUrl === '') {
            $this->error('Faltan variables CULQI_* en .env o el config cache está desactualizado.');
            $this->warn('Requerido: CULQI_PUBLIC_KEY, CULQI_SECRET_KEY, CULQI_BASE_URL (opcional si usas el default).');
            $this->warn('Tip: ejecuta `php artisan config:clear` luego de editar el .env.');
            return self::FAILURE;
        }

        $endpoint = $baseUrl . '/v2/charges';
        $this->line('[1/1] Probando autenticación (POST /v2/charges con payload inválido)...');

        try {
            $response = Http::withToken($secretKey)
                ->acceptJson()
                ->asJson()
                ->timeout(max($timeout, 5))
                ->post($endpoint, []);
        } catch (\Throwable $e) {
            $this->error('Error de conexión: ' . $e->getMessage());
            $this->warn('Revisa salida HTTPS (443), DNS, firewall, proxy o el valor de CULQI_BASE_URL.');
            return self::FAILURE;
        }

        $this->line('culqi.status: ' . $response->status());

        if ($response->status() === 401) {
            $this->error('401 Unauthorized: la CULQI_SECRET_KEY parece inválida o no corresponde al entorno.');
            $this->line('culqi.body: ' . trim((string) $response->body()));
            return self::FAILURE;
        }

        // En una llamada con payload vacío, lo esperado es 400/422 (validación), lo cual confirma que la key autentica.
        if (in_array($response->status(), [400, 422], true)) {
            $this->info('Autenticación OK (la API respondió validación, no 401).');
            return self::SUCCESS;
        }

        if ($response->successful()) {
            $this->warn('Respuesta 2xx inesperada. Revisa el endpoint/base_url antes de asumir que todo está OK.');
            return self::SUCCESS;
        }

        $this->warn('Respuesta no esperada al validar Culqi.');
        $this->line('culqi.body: ' . trim((string) $response->body()));

        return self::FAILURE;
    }

    private function mask(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        $visible = 4;
        if (mb_strlen($value) <= $visible * 2) {
            return str_repeat('*', mb_strlen($value));
        }

        return mb_substr($value, 0, $visible) . str_repeat('*', max(mb_strlen($value) - ($visible * 2), 4)) . mb_substr($value, -$visible);
    }
}
