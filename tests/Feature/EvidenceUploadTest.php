<?php

namespace Tests\Feature;

use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class EvidenceUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_evidence_upload_success()
    {
        $user = User::factory()->create(['role' => 'user']);
        $report = Report::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => $user->id,
            'report_type' => 'Emergency',
            'category' => 'Pelecehan',
            'status' => 'Submitted',
        ]);

        $file = UploadedFile::fake()->create('evidence.png', 100, 'image/png');

        $response = $this->actingAs($user)
            ->postJson("/tracking/{$report->id}/evidence", [
                'evidence' => [$file]
            ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'ok',
            'evidences' => [
                '*' => ['id', 'file_url', 'file_type', 'uploader_role']
            ]
        ]);
    }
}
