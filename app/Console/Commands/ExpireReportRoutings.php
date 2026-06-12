<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\Report;
use App\Models\ReportMitraRouting;
use App\Models\ReportTimelineEvent;
use App\Services\FonnteService;
use Illuminate\Console\Command;

class ExpireReportRoutings extends Command
{
    protected $signature = 'reports:expire-routings';

    protected $description = 'Menandai routing laporan mitra yang melewati batas respons sebagai expired.';

    public function handle(): int
    {
        $expiredRoutings = ReportMitraRouting::query()
            ->where('status', 'pending')
            ->whereNotNull('expires_at')
            ->where('expires_at', '<', now())
            ->get();

        foreach ($expiredRoutings as $routing) {
            $routing->update(['status' => 'expired']);

            try {
                AuditLog::log('expire_report_routing', 'report', $routing->report_id);
            } catch (\Exception $e) {
                // Audit tidak boleh menggagalkan scheduler.
            }
        }

        $emergencyReports = Report::query()
            ->where('report_type', 'Emergency')
            ->whereNull('handler_mitra_id')
            ->whereIn('status', ['Submitted', 'Routed', 'Viewed'])
            ->get();

        $escalatedReportsCount = 0;
        foreach ($emergencyReports as $report) {
            $retryCountKey = "report_{$report->id}_retry_count";
            $lastRetryAtKey = "report_{$report->id}_last_retry_at";

            $retryCount = (int) \Illuminate\Support\Facades\Cache::store('database')->get($retryCountKey, 0);
            $lastRetryAt = \Illuminate\Support\Facades\Cache::store('database')->get($lastRetryAtKey);

            if ($retryCount >= 3) {
                continue;
            }

            $shouldRetry = false;
            if ($retryCount === 0) {
                // Percobaan pertama setelah 5 menit laporan dibuat
                if ($report->created_at->lte(now()->subMinutes(5))) {
                    $shouldRetry = true;
                }
            } else {
                // Percobaan berikutnya setelah 5 menit dari percobaan terakhir
                if ($lastRetryAt && \Carbon\Carbon::parse($lastRetryAt)->lte(now()->subMinutes(5))) {
                    $shouldRetry = true;
                }
            }

            if ($shouldRetry) {
                $newRetryCount = $retryCount + 1;
                \Illuminate\Support\Facades\Cache::store('database')->put($retryCountKey, $newRetryCount, now()->addDays(7));
                \Illuminate\Support\Facades\Cache::store('database')->put($lastRetryAtKey, now()->toDateTimeString(), now()->addDays(7));

                // Pastikan status routing tetap pending dan expires_at = null (tidak expired statis)
                ReportMitraRouting::query()
                    ->where('report_id', $report->id)
                    ->whereIn('status', ['pending', 'expired'])
                    ->update([
                        'status' => 'pending',
                        'expires_at' => null,
                        'routed_at' => now(),
                    ]);

                // Hubungi mitra-mitra terhubung
                $pendingRoutings = ReportMitraRouting::query()
                    ->where('report_id', $report->id)
                    ->where('status', 'pending')
                    ->get();

                $mapsLink = $report->latitude
                    ? "https://maps.google.com/?q={$report->latitude},{$report->longitude}"
                    : 'Lokasi tidak tersedia';
                $trackingLink = url('/tracking/' . $report->id);

                foreach ($pendingRoutings as $routing) {
                    $mitra = $routing->mitra;
                    if ($mitra && $mitra->phone) {
                        $mitraMessage =
                            "🚨 *ALERT PENGINGAT (Mencari Bantuan - Percobaan {$newRetryCount}/3)*\n\n" .
                            "Laporan darurat belum di-acc oleh mitra mana pun!\n" .
                            "Kategori: *{$report->category}*\n\n" .
                            "📍 Lokasi:\n{$mapsLink}\n\n" .
                            "🔗 Tracking:\n{$trackingLink}\n\n" .
                            "Mohon segera buka dashboard Safora dan terima laporan ini jika berada di dekat lokasi.";

                        try {
                            FonnteService::send($mitra->phone, $mitraMessage);
                        } catch (\Exception $e) {
                            \Log::error("Failed to send scheduler retry WA to mitra", [
                                'mitra_id' => $mitra->id,
                                'error' => $e->getMessage()
                            ]);
                        }
                    }
                }

                // Juga update waktu aktivitas terakhir di laporan
                $report->update([
                    'last_activity_at' => now(),
                    'escalated_at' => now(), // Menandai ada aktivitas eskalasi scheduler
                ]);

                // Kirim notifikasi ke Admin
                if (env('ADMIN_PHONE') || env('ADMIN_PHONE') === '6285124019353') {
                    $adminMessage =
                        "🚨 *ALERT PENGINGAT ADMIN (Percobaan {$newRetryCount}/3)*\n\n" .
                        "Laporan darurat belum di-acc mitra selama " . ($newRetryCount * 5) . " menit.\n" .
                        "Kategori: *{$report->category}*\n" .
                        "📍 Lokasi:\n{$mapsLink}\n\n" .
                        "🔗 Tracking:\n{$trackingLink}";

                    try {
                        FonnteService::send(env('ADMIN_PHONE', '6285124019353'), $adminMessage);
                    } catch (\Exception $e) {
                        // abaikan error pengiriman WhatsApp admin
                    }
                }

                // Tambahkan event ke timeline
                ReportTimelineEvent::create([
                    'report_id' => $report->id,
                    'event_type' => 'mitra_retry_alert',
                    'event_message' => "Sistem mengirimkan ulang alert WhatsApp pengingat ke mitra terdekat (Percobaan ke-{$newRetryCount} dari 3).",
                    'actor_type' => 'system',
                ]);

                try {
                    AuditLog::log('escalate_retry_report', 'report', $report->id);
                } catch (\Exception $e) {
                    // abaikan error log audit
                }

                $escalatedReportsCount++;
            }
        }

        $this->info($expiredRoutings->count() . ' routing laporan ditandai expired.');
        $this->info($escalatedReportsCount . ' Laporan darurat dikirim ulang alert pengingat.');

        return self::SUCCESS;
    }
}
