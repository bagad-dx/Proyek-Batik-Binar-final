<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $code,
        public string $name,
        public ?string $customSubject = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            // Kode TIDAK ditaruh di subjek agar tidak terbaca di preview notifikasi.
            subject: $this->customSubject ?? 'Kode OTP Pendaftaran BATIK BINAR',
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.otp');
    }
}
