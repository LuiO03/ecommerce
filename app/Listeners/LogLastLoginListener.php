<?php
namespace App\Listeners;

use Illuminate\Auth\Events\Login;

class LogLastLoginListener
{
    public function handle(Login $event)
    {
        $user = $event->user;
        // Actualizar último inicio de sesión sin disparar auditoría ni tocar updated_at
        $user->last_login_at = now();

        $originalTimestamps = $user->timestamps;
        $user->timestamps = false;
        $user->saveQuietly();
        $user->timestamps = $originalTimestamps;
    }
}
