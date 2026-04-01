<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class TestEmail extends Mailable
{
    use Queueable, SerializesModels;

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Prueba de correo Geckommerce',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'site.emails.orders.test-summary',
        );
    }

}
