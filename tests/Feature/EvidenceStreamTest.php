<?php

namespace Tests\Feature;

use App\Models\Report;
use App\Models\Evidence;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EvidenceStreamTest extends TestCase
{
    use RefreshDatabase;

    public function test_evidence_stream_decodes_base64_from_database()
    {
        $user = User::factory()->create(['role' => 'user']);
        $report = Report::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => $user->id,
            'report_type' => 'Emergency',
            'category' => 'Pelecehan',
            'status' => 'Submitted',
        ]);

        $base64Image = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';
        $binaryData = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=');

        $evidence = Evidence::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'report_id' => $report->id,
            'file_url' => $base64Image,
            'file_type' => 'image/png',
            'file_hash' => hash('sha256', $binaryData),
            'uploaded_by' => $user->id,
            'uploaded_at' => now(),
            'uploaded_ip' => '127.0.0.1',
            'device_info' => 'PHPUnit',
            'uploader_role' => 'Korban',
        ]);

        // Request the view evidence route
        $response = $this->get("/evidences/view/{$evidence->id}");

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'image/png');
        $this->assertEquals($binaryData, $response->getContent());
    }

    public function test_evidence_to_array_uses_stream_url()
    {
        $evidence = new Evidence();
        $evidence->id = 'test-uuid';
        $evidence->file_url = 'data:image/png;base64,iVBORw0K';
        
        $array = $evidence->toArray();
        $this->assertEquals(url('/evidences/view/test-uuid'), $array['file_url']);
    }
}
