<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\Report;
use App\Models\ReportPartnerRouting;
use App\Models\ReportTimelineEvent;
use App\Services\FonnteService;
use Illuminate\Console\Command;

class ExpireReportRoutings extends Command
{
    protected $signature = 'reports:expire-routings';

    protected $description = 'Menandai routing laporan partner yang melewati batas respons sebagai expired.';

    public function handle(): int
    {
        $expiredRoutings = ReportPartnerRouting::query()
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

        $escalatedReports = Report::query()
            ->where('report_type', 'Emergency')
            ->whereNull('handler_partner_id')
            ->whereNull('escalated_at')
            ->where('created_at', '<=', now()->subMinutes((int) env('REPORT_ESCALATION_MINUTES', 3)))
            ->whereIn('status', ['Submitted', 'Routed', 'Viewed'])
            ->get();

        foreach ($escalatedReports as $report) {
            $report->update([
                'escalated_at' => now(),
                'last_activity_at' => now(),
            ]);

            ReportPartnerRouting::query()
                ->where('report_id', $report->id)
                ->whereIn('status', ['pending', 'expired'])
                ->update([
                    'status' => 'pending',
                    'expires_at' => now()->addMinutes(max(3, (int) env('REPORT_ROUTING_EXPIRY_MINUTES', 3))),
                    'routed_at' => now(),
                ]);

            ReportTimelineEvent::create([
                'report_id' => $report->id,
                'event_type' => 'escalated',
                'event_message' => 'Belum ada partner yang menerima dalam batas awal. Safora sedang mencoba ulang dan memberi peringatan ke admin.',
                'actor_type' => 'system',
            ]);

            try {
                AuditLog::log('escalate_unhandled_report', 'report', $report->id);
            } catch (\Exception $e) {
                // Audit tidak boleh menggagalkan scheduler.
            }

            try {
                FonnteService::send(
                    env('ADMIN_PHONE', '6285124019353'),
                    "Safora: laporan darurat belum tertangani lebih dari 3 menit.\nKategori: {$report->category}\nTracking: " . url('/tracking/' . $report->id)
                );
            } catch (\Exception $e) {
                // Notifikasi eksternal tidak boleh menggagalkan scheduler.
            }
        }

        $this->info($expiredRoutings->count() . ' routing laporan ditandai expired.');
        $this->info($escalatedReports->count() . ' laporan darurat dieskalasi.');

        return self::SUCCESS;
    }
}
