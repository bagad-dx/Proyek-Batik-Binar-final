<?php

namespace App\Console\Commands;

use App\Services\OtpService;
use Illuminate\Console\Command;

class TestOtpCommand extends Command
{
    protected $signature = 'otp:test
        {destination : Email tujuan atau nomor WhatsApp (08...)}
        {--channel=email : email atau wa}
        {--name=Admin : Nama penerima pada pesan}
        {--code= : Kode 6 digit khusus (opsional)}';

    protected $description = 'Kirim pesan uji OTP nyata via Email (SMTP) atau WhatsApp (Fonnte)';

    public function handle(): int
    {
        $channel = (string) $this->option('channel');
        $destination = (string) $this->argument('destination');

        if (! in_array($channel, ['email', 'wa'], true)) {
            $this->error('Channel harus "email" atau "wa".');
            return self::FAILURE;
        }

        if (! OtpService::channelAvailable($channel)) {
            $this->error($channel === 'wa'
                ? 'WhatsApp belum siap: isi token Fonnte di Panel Admin > OTP atau .env FONNTE_TOKEN.'
                : 'Email belum siap: isi username + App Password di Panel Admin > OTP atau MAIL_* di .env.');
            return self::FAILURE;
        }

        $code = (string) ($this->option('code') ?: OtpService::generateCode());
        $name = (string) $this->option('name');

        $this->line("Mengirim pesan uji via {$channel} ke {$destination} ...");
        $result = OtpService::send($channel, $name, $destination, $destination, $code);

        if ($result['sent']) {
            $this->info('Berhasil dikirim. Cek inbox/WhatsApp tujuan.');
            return self::SUCCESS;
        }

        $this->error('Gagal: ' . ($result['error'] ?? 'unknown') . ' [' . ($result['code'] ?? '-') . ']');
        return self::FAILURE;
    }
}
