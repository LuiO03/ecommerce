<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class TestEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function envelope(): Envelope
    {
        $company = company_setting();

        return new Envelope(
            from: new Address(
                config('mail.from.address'),
                $company?->name ?? config('app.name')
            ),

            subject: 'Prueba de correo ' .
                ($company?->name ?? config('app.name')),
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'site.emails.orders.test-summary',

            with: [
                'company' => company_setting(),
            ],
        );
    }
}
