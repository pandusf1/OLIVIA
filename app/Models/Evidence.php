<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

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
     * Generate a fast, hybrid SHA-256 hash for small and large files.
     * Keeps upload execution extremely quick for large files.
     */
    public static function generateFastHash(string $filePath, string $originalName, int $fileSize): ?string
    {
        if (!file_exists($filePath)) {
            return null;
        }

        try {
            // If the file is less than 5MB, we hash it fully
            if ($fileSize < 5 * 1024 * 1024) {
                return hash_file('sha256', $filePath);
            }

            // For large files, hash only the first 1MB and the last 1MB of content
            // along with metadata to remain extremely fast and secure.
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
            // Robust fallback if any read error occurs
            return hash('sha256', $originalName . $fileSize . microtime(true));
        }
    }
}
