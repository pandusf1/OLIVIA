<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

use App\Jobs\SendWhatsAppJob;

class FonnteService
{
    /**
     * Format nomor telepon ke standar internasional Indonesia (62...)
     */
    private static function formatPhoneNumber($phone)
    {
        return PhoneNumberService::normalize($phone);
    }

    public static function send($target, $message)
    {
        // Format nomor target
        $target = self::formatPhoneNumber($target);

        if (empty($target)) {
            return;
        }

        try {
            dispatch(new SendWhatsAppJob($target, $message));
        } catch (\Throwable $e) {
            Log::warning('Queue dispatch failed, sending WhatsApp synchronously: ' . $e->getMessage());
            self::sendNow($target, $message);
        }
    }

    public static function sendNow($target, $message)
    {
        $target = self::formatPhoneNumber($target);

        if (empty($target)) {
            return;
        }

        try {
            $token = config('services.fonnte.token');

            if (empty($token)) {
                Log::error('Fonnte API Error: missing FONNTE_TOKEN');
                return;
            }

            Http::timeout(3)
                ->connectTimeout(2)
                ->withHeaders([
                    'Authorization' => $token,
                ])
                ->post('https://api.fonnte.com/send', [
                    'target' => $target,
                    'message' => $message,
                ])->throw();
        } catch (\Throwable $e) {
            Log::error('Fonnte API Error: ' . $e->getMessage(), [
                'target' => $target,
            ]);
        }
    }
}
