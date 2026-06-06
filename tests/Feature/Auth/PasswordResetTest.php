<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_reset_password_link_screen_can_be_rendered(): void
    {
        $response = $this->get('/forgot-password');

        $response->assertStatus(200);
    }

    public function test_reset_password_otp_can_be_requested(): void
    {
        Http::fake([
            'api.fonnte.com/*' => Http::response(['status' => true], 200),
        ]);

        $user = User::factory()->create([
            'phone' => '628123456789',
            'phone_is_verified' => true,
        ]);

        $response = $this->post('/forgot-password', ['identity' => $user->email]);

        $response->assertRedirect(route('password.otp-verify-view'));

        $this->assertTrue(Cache::has('password_reset_otp:' . $user->id));
        $this->assertEquals($user->id, session('password_reset_user_id'));

        Http::assertSent(function ($request) use ($user) {
            return $request->url() === 'https://api.fonnte.com/send' &&
                $request['target'] === $user->phone &&
                str_contains($request['message'], 'Kode OTP lupa sandi Safora Anda');
        });
    }

    public function test_otp_verification_screen_cannot_be_rendered_without_session(): void
    {
        $response = $this->get('/forgot-password/verify');

        $response->assertRedirect(route('password.request'));
    }

    public function test_otp_verification_screen_can_be_rendered_with_session(): void
    {
        $user = User::factory()->create([
            'phone' => '628123456789',
        ]);

        $response = $this->withSession(['password_reset_user_id' => $user->id])
            ->get('/forgot-password/verify');

        $response->assertStatus(200);
        $response->assertSee('Verifikasi OTP');
        $response->assertSee('62812****789');
    }

    public function test_otp_can_be_verified_and_redirects_with_token(): void
    {
        $user = User::factory()->create([
            'phone' => '628123456789',
        ]);

        Cache::put('password_reset_otp:' . $user->id, '12345', now()->addMinutes(10));

        $response = $this->withSession(['password_reset_user_id' => $user->id])
            ->post('/forgot-password/verify', ['code' => '12345']);

        // Check it redirected to reset-password with a token
        $response->assertRedirect();
        $location = $response->headers->get('Location');
        $this->assertStringContainsString('/reset-password/', $location);

        $token = last(explode('/', parse_url($location, PHP_URL_PATH)));

        $this->assertTrue(Cache::has('password_reset_token:' . $token));
        $this->assertEquals($user->id, Cache::get('password_reset_token:' . $token));
        $this->assertNull(session('password_reset_user_id'));
    }

    public function test_otp_verification_fails_with_invalid_code(): void
    {
        $user = User::factory()->create([
            'phone' => '628123456789',
        ]);

        Cache::put('password_reset_otp:' . $user->id, '12345', now()->addMinutes(10));

        $response = $this->withSession(['password_reset_user_id' => $user->id])
            ->post('/forgot-password/verify', ['code' => '54321']);

        $response->assertSessionHasErrors(['code']);
        $this->assertTrue(Cache::has('password_reset_otp:' . $user->id));
    }

    public function test_reset_password_screen_cannot_be_rendered_with_invalid_token(): void
    {
        $response = $this->get('/reset-password/invalid-token');

        $response->assertRedirect(route('password.request'));
    }

    public function test_reset_password_screen_can_be_rendered_with_valid_token(): void
    {
        $user = User::factory()->create();
        $token = 'test-token-123';
        Cache::put('password_reset_token:' . $token, $user->id, now()->addMinutes(10));

        $response = $this->get('/reset-password/' . $token);

        $response->assertStatus(200);
        $response->assertSee('Kata Sandi Baru');
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword'),
        ]);

        $token = 'test-token-123';
        Cache::put('password_reset_token:' . $token, $user->id, now()->addMinutes(10));

        $response = $this->post('/reset-password', [
            'token' => $token,
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('status');

        $this->assertFalse(Cache::has('password_reset_token:' . $token));
        
        $user->refresh();
        $this->assertTrue(Hash::check('newpassword123', $user->password));
    }
}
