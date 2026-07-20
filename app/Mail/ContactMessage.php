<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactMessage extends Mailable
{
    use Queueable, SerializesModels;

    public array $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function build(): static
    {
        $company = company_setting();

        $fromEmail = (string) ($this->data['email'] ?? '');
        $fromName = (string) ($this->data['name'] ?? '');

        $mailable = $this
            ->subject(
                'Nuevo mensaje de contacto - ' .
                ($company?->name ?? config('app.name'))
            );

        if ($fromEmail !== '') {
            $mailable->replyTo($fromEmail, $fromName !== '' ? $fromName : null);
        }

        return $mailable->markdown('site.emails.messages.contact-message', [
                'data' => $this->data,
                'company' => $company,
            ]);
    }
}
