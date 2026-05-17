<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FonnteService
{
    public static function send($target, $message)
    {
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