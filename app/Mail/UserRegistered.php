<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\URL;

class UserRegistered extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Build the message.
     */
    public function build(): static
    {
        // URL firmada para verificación de correo (igual que la notificación por defecto)
        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            now()->addMinutes((int) Config::get('auth.verification.expire', 60)),
            [
                'id' => $this->user->getKey(),
                'hash' => sha1($this->user->email),
            ]
        );

        return $this
            ->subject('Bienvenido a ' . config('app.name'))
            ->markdown('site.emails.users.registered', [
                'user' => $this->user,
                'verificationUrl' => $verificationUrl,
            ]);
    }
}
