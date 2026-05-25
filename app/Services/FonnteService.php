<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FonnteService
{
    /**
     * Format nomor telepon ke standar internasional Indonesia (62...)
     */
    private static function formatPhoneNumber($phone)
    {
        // Hapus karakter selain angka dan tanda +
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        // Jika diawali dengan +62, ubah jadi 62
        if (str_starts_with($phone, '+62')) {
            $phone = '62' . substr($phone, 3);
        }
        // Jika diawali dengan 0, ubah jadi 62
        elseif (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }

        return $phone;
    }

    public static function send($target, $message)
    {
        // Format nomor target
        $target = self::formatPhoneNumber($target);

        // Jalankan request HTTP setelah response dikirim ke user (background)
        app()->terminating(function () use ($target, $message) {
            try {
                $token = env('FONNTE_TOKEN');
                
                Http::timeout(10)->withHeaders([
                    'Authorization' => $token
                ])->post('https://api.fonnte.com/send', [
                    'target' => $target,
                    'message' => $message,
                ]);
            } catch (\Exception $e) {
                Log::error('Fonnte API Error: ' . $e->getMessage());
            }
        });
    }
}