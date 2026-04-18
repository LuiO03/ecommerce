<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class NiubizCheck extends Command
{
    protected $signature = 'niubiz:check {--amount=10.00 : Monto para probar session token}';

    protected $description = 'Diagnostica credenciales y session token de Niubiz (sandbox)';

    public function handle(): int
    {
        $merchantId = (string) config('services.niubiz.merchant_id');
        $user = (string) config('services.niubiz.user');
        $password = (string) config('services.niubiz.password');
        $baseUrl = rtrim((string) config('services.niubiz.url_api'), '/');
        $amount = (float) $this->option('amount');

        $this->line('=== Niubiz Check ===');
        $this->line('merchant_id: ' . ($merchantId !== '' ? $merchantId : '(vacio)'));
        $this->line('user: ' . ($user !== '' ? $user : '(vacio)'));
        $this->line('url_api: ' . ($baseUrl !== '' ? $baseUrl : '(vacio)'));
        $this->newLine();

        if ($merchantId === '' || $user === '' || $password === '' || $baseUrl === '') {
            $this->error('Faltan variables NIUBIZ_* en .env o config cache desactualizado.');
            return self::FAILURE;
        }

        $securityUrl = $baseUrl . '/api.security/v1/security';
        $auth = base64_encode($user . ':' . $password);

        $this->line('[1/2] Probando access token (security)...');

        try {
            $securityResponse = Http::withHeaders([
                'Authorization' => 'Basic ' . $auth,
                'Accept' => 'text/plain, application/json',
            ])->timeout(20)->get($securityUrl);
        } catch (\Throwable $e) {
            $this->error('Error de conexion a security endpoint: ' . $e->getMessage());
            $this->warn('Revisa DNS, firewall, proxy o salida HTTPS (443).');
            return self::FAILURE;
        }

        $this->line('security.status: ' . $securityResponse->status());
        if (!$securityResponse->successful()) {
            $this->line('security.body: ' . trim((string) $securityResponse->body()));

            if ($securityResponse->status() === 401) {
                $this->error('Credenciales invalidas para sandbox (401).');
                $this->warn('Verifica NIUBIZ_USER y NIUBIZ_PASSWORD en el portal de Niubiz para el merchant indicado.');
            } else {
                $this->error('Niubiz rechazo el security endpoint con estado ' . $securityResponse->status() . '.');
            }

            return self::FAILURE;
        }

        $accessToken = trim((string) $securityResponse->body());
        $this->info('Access token OK.');

        $sessionUrl = $baseUrl . '/api.ecommerce/v2/ecommerce/token/session/' . $merchantId;

        $this->line('[2/2] Probando session token...');

        try {
            $sessionResponse = Http::withHeaders([
                'Authorization' => $accessToken,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->timeout(20)->post($sessionUrl, [
                'channel' => 'web',
                'amount' => $amount,
                'antifraud' => [
                    'clientIp' => '127.0.0.1',
                    'merchantDefineData' => [
                        'MDD4' => 'diagnostic-user',
                        'MDD21' => 'diagnostic@example.com',
                        'MDD32' => '',
                        'MDD33' => '999999999',
                        'MDD75' => (string) $amount,
                        'MDD76' => 'PEN',
                        'MDD77' => '1',
                        'MDD89' => '1',
                    ],
                ],
            ]);
        } catch (\Throwable $e) {
            $this->error('Error de conexion a session endpoint: ' . $e->getMessage());
            return self::FAILURE;
        }

        $this->line('session.status: ' . $sessionResponse->status());

        if (!$sessionResponse->successful()) {
            $this->line('session.body: ' . trim((string) $sessionResponse->body()));
            $this->error('No se pudo obtener session token.');
            return self::FAILURE;
        }

        $sessionData = $sessionResponse->json();
        $sessionKey = $sessionData['sessionKey'] ?? $sessionData['token'] ?? null;

        if (!$sessionKey) {
            $this->line('session.body: ' . trim((string) $sessionResponse->body()));
            $this->error('Respuesta exitosa pero sin sessionKey/token.');
            return self::FAILURE;
        }

        $this->info('Session token OK. Integracion Niubiz operativa.');
        return self::SUCCESS;
    }
}
