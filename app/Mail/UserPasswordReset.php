<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserPasswordReset extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;
    public string $resetUrl;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, string $resetUrl)
    {
        $this->user = $user;
        $this->resetUrl = $resetUrl;
    }

    /**
     * Build the message.
     */
    public function build(): static
    {
        return $this
            ->subject('Restablecer tu contraseña en ' . config('app.name'))
            ->markdown('site.emails.users.password-reset', [
                'user' => $this->user,
                'resetUrl' => $this->resetUrl,
            ]);
    }
}
