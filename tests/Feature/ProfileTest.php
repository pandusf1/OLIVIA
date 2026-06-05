<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/settings');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/settings');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }

    public function test_google_user_setting_phone_redirects_to_dashboard_and_is_verified(): void
    {
        $user = User::factory()->create([
            'google_id' => '123456789',
            'phone' => null,
            'phone_is_verified' => false,
        ]);

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => '628123456789',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/dashboard');

        $user->refresh();
        $this->assertSame('628123456789', $user->phone);
        $this->assertTrue($user->phone_is_verified);
    }

    public function test_regular_user_setting_phone_requires_otp_verification(): void
    {
        $user = User::factory()->create([
            'phone' => null,
            'phone_is_verified' => false,
        ]);

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => '628123456789',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/settings');

        $user->refresh();
        $this->assertSame('628123456789', $user->phone);
        $this->assertFalse($user->phone_is_verified);
        $this->assertNotNull($user->phone_verification_code);

        // Verify phone with OTP
        $verifyResponse = $this
            ->actingAs($user)
            ->post('/profile/phone/verify', [
                'code' => $user->phone_verification_code,
            ]);

        $verifyResponse->assertRedirect('/dashboard');

        $user->refresh();
        $this->assertTrue($user->phone_is_verified);
    }
}
