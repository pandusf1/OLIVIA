<?php

namespace Tests\Feature;

use App\Models\Report;
use App\Models\User;
use App\Models\TrustedContact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportChatAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_access_tracking_page_registers_in_session_and_grants_chat_access()
    {
        // 1. Submit report as guest
        $response = $this->post('/emergency', [
            'category' => 'Kekerasan',
            'latitude' => -6.980000,
            'longitude' => 110.420000,
            'anonymous' => true,
        ]);

        $response->assertRedirect();
        $report = Report::first();
        $this->assertNotNull($report);

        // Assert that the report ID is now registered in session
        $this->assertContains($report->id, session()->get('my_reports', []));
        
        // Assert that the cookie is queued
        $response->assertCookie('safora_my_reports');

        // 2. Access tracking page
        $trackingResponse = $this->get('/tracking/' . $report->id);
        $trackingResponse->assertOk();

        // 3. Access chat page
        $chatResponse = $this->get('/chat/report/' . $report->id);
        $chatResponse->assertOk();
    }

    public function test_trusted_contact_can_access_report_chat()
    {
        // Create reporter
        $reporter = User::factory()->create();

        // Create trusted contact user
        $trustedUser = User::factory()->create([
            'phone' => '6289999999999',
            'phone_is_verified' => true,
        ]);

        // Create trusted contact mapping
        TrustedContact::create([
            'user_id' => $reporter->id,
            'contact_name' => 'Trusted Guy',
            'contact_phone' => $trustedUser->phone,
            'is_verified' => true,
        ]);

        // Create report
        $report = Report::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => $reporter->id,
            'category' => 'Kekerasan',
            'status' => 'Submitted',
            'victim_name' => 'Reporter User',
            'phone' => '6281111111111',
            'anonymous' => false,
        ]);

        // Log in as the trusted contact
        $response = $this->actingAs($trustedUser)->get('/chat/report/' . $report->id);

        // Access should be allowed
        $response->assertOk();
    }
}
