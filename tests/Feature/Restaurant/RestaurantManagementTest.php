<?php

namespace Tests\Feature\Restaurant;

use App\Models\User;
use App\Models\Restaurant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RestaurantManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Create roles
        Role::create(['name' => 'ADMIN_RESTAURANT']);
        Role::create(['name' => 'CLIENT']);
    }

    public function test_create_restaurant_assigns_admin_role_to_owner()
    {
        $owner = User::factory()->create();
        
        $response = $this->postJson('/api/v1/restaurants', [
            'nom' => 'New Restaurant',
            'adresse' => '123 Main St',
            'telephone' => '0123456789',
            'proprietaire_id' => $owner->id,
        ]);

        $response->assertStatus(201);
        
        $owner->refresh();
        $this->assertTrue($owner->hasRole('ADMIN_RESTAURANT'));
        $this->assertNotNull($owner->restaurant_id);
    }

    public function test_transfer_ownership_updates_roles_and_relations()
    {
        // Setup initial state
        $oldOwner = User::factory()->create();
        $oldOwner->assignRole('ADMIN_RESTAURANT');
        
        $restaurant = Restaurant::create([
            'nom' => 'Transfer Restaurant',
            'adresse' => '123 Main St',
            'telephone' => '0123456789',
            'proprietaire_id' => $oldOwner->id,
        ]);
        
        $oldOwner->restaurant_id = $restaurant->id;
        $oldOwner->save();

        $newOwner = User::factory()->create();

        // Perform transfer
        $response = $this->postJson("/api/v1/restaurants/{$restaurant->id}/transfer-ownership", [
            'new_proprietaire_id' => $newOwner->id,
        ]);

        $response->assertStatus(200);

        // Verify Old Owner
        $oldOwner->refresh();
        $this->assertFalse($oldOwner->hasRole('ADMIN_RESTAURANT')); // Should lose role if no other restaurants
        
        // Verify New Owner
        $newOwner->refresh();
        $this->assertTrue($newOwner->hasRole('ADMIN_RESTAURANT'));
        $this->assertEquals($restaurant->id, $newOwner->restaurant_id);
        
        // Verify Restaurant
        $restaurant->refresh();
        $this->assertEquals($newOwner->id, $restaurant->proprietaire_id);
    }
}
