<?php

namespace Tests\Feature;

use App\Models\Mitra;
use App\Models\Report;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class EmergencyReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_emergency_report_routing_and_call_phone_payload()
    {
        // 1. Create a mock mitra of type pppa
        $mitra = Mitra::create([
            'id' => (string) Str::uuid(),
            'mitra_name' => 'UPTD PPPA Test',
            'mitra_type' => 'pppa',
            'address' => 'Jl. Test No. 123',
            'phone' => '6281234567890',
            'email' => 'pppa@test.com',
            'verified' => true,
            'is_active' => true,
            'latitude' => -6.980000,
            'longitude' => 110.420000,
        ]);

        // 2. Mock a user to send the emergency report
        $user = User::factory()->create();

        // 3. Post an emergency report to the endpoint
        $response = $this->actingAs($user)->postJson('/emergency', [
            'category' => 'Pelecehan',
            'description' => 'Saya butuh bantuan darurat segera.',
            'latitude' => -6.980000,
            'longitude' => 110.420000,
            'anonymous' => false,
        ]);

        // 4. Assert response payload contains call_phone
        $response->assertStatus(200);
        $response->assertJson([
            'ok' => true,
            'call_phone' => '6281234567890',
        ]);

        // 5. Assert the report was created and correctly routed to the mitra
        $this->assertDatabaseHas('reports', [
            'category' => 'Pelecehan',
            'status' => 'Routed',
            'routed_mitra_id' => $mitra->id,
        ]);
    }
}
