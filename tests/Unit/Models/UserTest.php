<?php

namespace Tests\Unit\Models;

use App\Models\User;
use App\Models\Restaurant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Create roles
        Role::create(['name' => 'ADMIN_RESTAURANT']);
    }

    public function test_user_has_roles_trait()
    {
        $user = User::factory()->create();
        $this->assertTrue(method_exists($user, 'assignRole'));
    }

    public function test_user_can_be_assigned_role()
    {
        $user = User::factory()->create();
        $user->assignRole('ADMIN_RESTAURANT');
        
        $this->assertTrue($user->hasRole('ADMIN_RESTAURANT'));
    }

    public function test_is_manager_method()
    {
        $user = User::factory()->create();
        $this->assertFalse($user->isManager());

        $user->assignRole('ADMIN_RESTAURANT');
        $this->assertTrue($user->isManager());
    }

    public function test_owned_restaurant_relationship()
    {
        $user = User::factory()->create();
        $restaurant = Restaurant::create([
            'nom' => 'Test Restau',
            'adresse' => 'Test Address',
            'telephone' => '123456789',
            'proprietaire_id' => $user->id
        ]);

        $this->assertTrue($user->ownedRestaurant->is($restaurant));
    }
}
