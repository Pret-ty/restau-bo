<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Models\Restaurant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Create roles
        $roles = ['CLIENT', 'ADMIN_RESTAURANT'];
        foreach ($roles as $role) {
            Role::create(['name' => $role]);
        }
    }

    public function test_user_can_register_with_valid_data()
    {
        // $this->withoutExceptionHandling();

        $response = $this->postJson('/api/v1/register', [
            'nom' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'token',
                    'user' => ['id', 'nom', 'email', 'roles']
                ],
                'message'
            ]);

        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
    }

    public function test_user_gets_client_role_by_default()
    {
        $response = $this->postJson('/api/v1/register', [
            'nom' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $user = User::where('email', 'test@example.com')->first();
        $this->assertTrue($user->hasRole('CLIENT'));
    }

    public function test_user_can_login_with_valid_credentials()
    {
        $this->withoutExceptionHandling();
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'token',
                    'user'
                ],
                'message'
            ]);
    }

    public function test_authenticated_user_can_get_profile()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->getJson('/api/v1/user');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => ['id', 'nom', 'email']
            ]);
    }

    public function test_unauthenticated_user_cannot_get_profile()
    {
        $response = $this->getJson('/api/v1/user');

        $response->assertStatus(401);
    }
}
