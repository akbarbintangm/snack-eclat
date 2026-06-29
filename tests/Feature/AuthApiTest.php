<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_signup_and_receives_bearer_token(): void
    {
        $response = $this->postJson('/api/v1/auth/signup', [
            'username' => 'new_admin',
            'name' => 'New Admin',
            'email' => 'new-admin@snack-eclat.local',
            'password' => 'password',
            'password_confirmation' => 'password',
            'level' => 'admin',
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.user.username', 'new_admin')
            ->assertJsonStructure(['data' => ['token', 'user' => ['id', 'username', 'level']]]);

        $user = User::query()->where('username', 'new_admin')->firstOrFail();
        $this->assertTrue(Hash::check('password', $user->password));
        $this->assertNotNull($user->api_token_hash);
    }

    public function test_user_can_login_read_profile_and_logout(): void
    {
        User::factory()->create([
            'username' => 'admin',
            'password' => Hash::make('password'),
            'level' => 'admin',
        ]);

        $login = $this->postJson('/api/v1/auth/login', [
            'username' => 'admin',
            'password' => 'password',
        ]);

        $login->assertOk()->assertJsonPath('data.user.username', 'admin');
        $token = $login->json('data.token');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/auth/me')
            ->assertOk()
            ->assertJsonPath('data.username', 'admin');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->postJson('/api/v1/auth/logout')
            ->assertOk();

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/auth/me')
            ->assertUnauthorized();
    }

    public function test_protected_api_requires_token(): void
    {
        $this->getJson('/api/v1/transactions')->assertUnauthorized();
    }

    public function test_owner_can_access_reports_but_not_operational_apis(): void
    {
        $owner = User::factory()->create([
            'level' => 'owner',
        ]);
        $token = 'owner-token';
        $owner->forceFill(['api_token_hash' => hash('sha256', $token)])->save();

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/reports/summary')
            ->assertOk();

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/transactions')
            ->assertForbidden();

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/snacks')
            ->assertForbidden();

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/eclat/results')
            ->assertForbidden();

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/reports/recommendations')
            ->assertOk();
    }

    public function test_admin_can_view_association_rules_but_not_report_recommendations(): void
    {
        $admin = User::factory()->create([
            'level' => 'admin',
        ]);
        $token = 'admin-token';
        $admin->forceFill(['api_token_hash' => hash('sha256', $token)])->save();

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/reports/summary')
            ->assertOk();

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/eclat/results')
            ->assertOk();

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/reports/recommendations')
            ->assertForbidden();
    }
}
