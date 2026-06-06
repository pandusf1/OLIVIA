<?php

namespace Tests\Feature;

use App\Models\Mitra;
use App\Models\User;
use App\Models\UserLocation;
use App\Models\PriceList;
use App\Models\UserMitraPayment;
use App\Models\ChatThread;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MitraFeaturesTest extends TestCase
{
    use RefreshDatabase;

    public function test_mitra_can_update_profile_and_payment_info()
    {
        $mitra = Mitra::create([
            'mitra_name' => 'Legal Counselor Group',
            'mitra_type' => 'legal',
            'city' => 'Jakarta',
            'address' => 'Jl. Merdeka No. 12',
            'email' => 'mitra@example.com',
            'verified' => true,
            'is_active' => true,
        ]);

        $mitraUser = User::factory()->create([
            'role' => 'mitra',
            'mitra_id' => $mitra->id,
        ]);

        $response = $this->actingAs($mitraUser)->post('/mitra/profile', [
            'catatan' => 'Jam kerja: 09:00 - 17:00 WIB',
            'bank_name' => 'BCA',
            'nomor_rekening' => '1234567890',
            'ewallet_name' => 'DANA',
            'nomor_ewallet' => '08123456789',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('mitras', [
            'id' => $mitra->id,
            'catatan' => 'Jam kerja: 09:00 - 17:00 WIB',
            'bank_name' => 'BCA',
            'nomor_rekening' => '1234567890',
            'ewallet_name' => 'DANA',
            'nomor_ewallet' => '08123456789',
        ]);
    }

    public function test_mitra_can_view_client_details_with_map_if_available()
    {
        $mitra = Mitra::create([
            'mitra_name' => 'Legal Counselor Group',
            'mitra_type' => 'legal',
            'city' => 'Jakarta',
            'address' => 'Jl. Merdeka No. 12',
            'email' => 'mitra@example.com',
            'verified' => true,
            'is_active' => true,
        ]);

        $mitraUser = User::factory()->create([
            'role' => 'mitra',
            'mitra_id' => $mitra->id,
        ]);

        $client = User::factory()->create([
            'role' => 'user',
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'phone' => '628123456789',
        ]);

        UserLocation::create([
            'user_id' => $client->id,
            'latitude' => -6.200000,
            'longitude' => 106.816666,
        ]);

        $priceList = PriceList::create([
            'mitra_id' => $mitra->id,
            'service_name' => 'Konsultasi Hukum Perdata',
            'price' => 500000,
            'duration' => '60 Menit',
            'currency' => 'IDR',
        ]);

        UserMitraPayment::create([
            'user_id' => $client->id,
            'mitra_id' => $mitra->id,
            'price_list_id' => $priceList->id,
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        $response = $this->actingAs($mitraUser)->get("/mitra/client/{$client->id}");

        $response->assertOk();
        $response->assertSee('Budi Santoso');
        $response->assertSee('budi@example.com');
        $response->assertSee('Konsultasi Hukum Perdata');
        $response->assertSee('Rp 500.000');
        $response->assertSee('-6.2');
        $response->assertSee('106.816666');
    }

    public function test_mitra_can_access_direct_chat_threads_with_clients()
    {
        $mitra = Mitra::create([
            'mitra_name' => 'Legal Counselor Group',
            'mitra_type' => 'legal',
            'city' => 'Jakarta',
            'address' => 'Jl. Merdeka No. 12',
            'email' => 'mitra@example.com',
            'verified' => true,
            'is_active' => true,
        ]);

        $mitraUser = User::factory()->create([
            'role' => 'mitra',
            'mitra_id' => $mitra->id,
        ]);

        $client = User::factory()->create([
            'role' => 'user',
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
        ]);

        $thread = ChatThread::create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'user_id' => $client->id,
            'mitra_id' => $mitra->id,
            'last_message_at' => now(),
        ]);

        $response = $this->actingAs($mitraUser)->get("/chat/threads", ['Accept' => 'application/json']);

        $response->assertOk();
        $json = $response->json();
        
        $this->assertEquals('mitra', $json['viewerType']);
        $this->assertNotEmpty($json['threads']);
        $this->assertEquals($client->id, $json['threads'][0]['mitra_id']); // For mitra, mitra_id in thread list points to the user/client
    }
}
