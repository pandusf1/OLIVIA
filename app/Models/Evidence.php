<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Evidence extends Model
{
    use HasUuids;

    protected $table = 'evidences';

    protected $fillable = [
        'report_id',
        'file_url',
        'file_type',
        'file_hash',
        'uploaded_by',
        'uploaded_at',
        'uploaded_ip',
        'device_info',
        'uploader_role'
    ];

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $casts = [
        'uploaded_at' => 'datetime',
    ];

    /**
     * Membuat hash SHA-256 hybrid cepat untuk file kecil dan besar.
     * Menjaga proses unggah tetap cepat untuk file berukuran besar.
     */
    public static function generateFastHash(string $filePath, string $originalName, int $fileSize): ?string
    {
        if (!file_exists($filePath)) {
            return null;
        }

        try {
            // Jika ukuran file kurang dari 5MB, lakukan hash secara utuh
            if ($fileSize < 5 * 1024 * 1024) {
                return hash_file('sha256', $filePath);
            }

            // Untuk file besar, hash hanya 1MB awal dan 1MB akhir konten
            // digabung dengan metadata agar proses sangat cepat dan aman.
            $fp = fopen($filePath, 'rb');
            if (!$fp) {
                return hash('sha256', $originalName . $fileSize);
            }
            
            $firstChunk = fread($fp, 1024 * 1024);
            fseek($fp, -1024 * 1024, SEEK_END);
            $lastChunk = fread($fp, 1024 * 1024);
            fclose($fp);

            return hash('sha256', $firstChunk . $lastChunk . $originalName . $fileSize);
        } catch (\Throwable $e) {
            // Fallback aman jika terjadi error pembacaan file
            return hash('sha256', $originalName . $fileSize . microtime(true));
        }
    }

    /**
     * Mengompres gambar menggunakan API TinyPNG jika key dikonfigurasi dan file berupa JPEG/PNG.
     * Mengembalikan data biner terkompresi, atau konten asli jika gagal/dilewati.
     */
    public static function compressImageIfNeeded(string $filePath, string $mimeType): string
    {
        $originalData = file_get_contents($filePath);
        $apiKey = env('TINYPNG_API_KEY');

        if (empty($apiKey)) {
            return $originalData;
        }

        $allowedMimes = ['image/jpeg', 'image/png', 'image/jpg'];
        if (!in_array(strtolower($mimeType), $allowedMimes)) {
            return $originalData;
        }

        try {
            $response = Http::withBasicAuth($apiKey, '')
                ->withBody($originalData, $mimeType)
                ->timeout(5)
                ->post('https://api.tinify.com/shrink');

            if ($response->successful()) {
                $outputUrl = $response->json('output.url');
                if ($outputUrl) {
                    $compressedResponse = Http::timeout(5)->get($outputUrl);
                    if ($compressedResponse->successful()) {
                        Log::info("TinyPNG compression succeeded. Original size: " . strlen($originalData) . " bytes, Compressed size: " . strlen($compressedResponse->body()) . " bytes");
                        return $compressedResponse->body();
                    }
                }
            } else {
                Log::warning("TinyPNG API error: status code " . $response->status() . ", body: " . $response->body());
            }
        } catch (\Throwable $e) {
            Log::error("TinyPNG compression failed: " . $e->getMessage());
        }

        return $originalData;
    }

    /**
     * Menyesuaikan representasi JSON untuk menghindari kebocoran data base64 ke payload API.
     */
    public function toArray()
    {
        $array = parent::toArray();
        if (isset($array['file_url'])) {
            $array['file_url'] = url('/evidences/view/' . $this->id);
        }
        return $array;
    }
}
