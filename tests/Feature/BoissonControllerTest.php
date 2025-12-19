<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Restaurant;
use App\Models\Boisson;
use Spatie\Permission\Models\Role;

class BoissonControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Create Role if not exists
        if (!Role::where('name', 'ADMIN_RESTAURANT')->exists()) {
            Role::create(['name' => 'ADMIN_RESTAURANT', 'guard_name' => 'web']);
        }
    }

    public function test_admin_can_add_boisson_to_own_restaurant()
    {
        $owner = User::factory()->create();
        $owner->assignRole('ADMIN_RESTAURANT');
        $restaurant = Restaurant::factory()->create(['proprietaire_id' => $owner->id]);
        $owner->restaurant_id = $restaurant->id;
        $owner->save();

        $response = $this->actingAs($owner, 'sanctum')->postJson("/api/v1/restaurants/{$restaurant->id}/boissons", [
            'nom' => 'Coca Cola',
            'prix' => 2.5
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('boissons', ['nom' => 'Coca Cola', 'restaurant_id' => $restaurant->id]);
    }

    public function test_admin_cannot_add_boisson_to_other_restaurant()
    {
        $owner = User::factory()->create();
        $owner->assignRole('ADMIN_RESTAURANT');
        $restaurant = Restaurant::factory()->create(['proprietaire_id' => $owner->id]);
        $owner->restaurant_id = $restaurant->id;
        $owner->save();

        $otherOwner = User::factory()->create(); // Just another user/owner
        $otherRestaurant = Restaurant::factory()->create(['proprietaire_id' => $otherOwner->id]);

        // Attempt to add boisson to OTHER restaurant
        $response = $this->actingAs($owner, 'sanctum')->postJson("/api/v1/restaurants/{$otherRestaurant->id}/boissons", [
            'nom' => 'Pepsi',
            'prix' => 2.5
        ]);

        $response->assertStatus(403); // Forbidden
    }

    public function test_admin_can_update_own_boisson()
    {
        $owner = User::factory()->create();
        $owner->assignRole('ADMIN_RESTAURANT');
        $restaurant = Restaurant::factory()->create(['proprietaire_id' => $owner->id]);
        $owner->restaurant_id = $restaurant->id;
        $owner->save();

        $boisson = Boisson::create(['nom' => 'Water', 'prix' => 1.0, 'restaurant_id' => $restaurant->id]);

        $response = $this->actingAs($owner, 'sanctum')->putJson("/api/v1/restaurants/{$restaurant->id}/boissons/{$boisson->id}", [
            'nom' => 'Sparkling Water'
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('boissons', ['nom' => 'Sparkling Water']);
    }

    public function test_admin_cannot_update_other_restaurant_boisson()
    {
        $owner = User::factory()->create();
        $owner->assignRole('ADMIN_RESTAURANT');
        $restaurant = Restaurant::factory()->create(['proprietaire_id' => $owner->id]);
        $owner->restaurant_id = $restaurant->id;
        $owner->save();

        // Other restaurant boisson
        $otherRestaurant = Restaurant::factory()->create();
        $boisson = Boisson::create(['nom' => 'Other Drink', 'prix' => 5.0, 'restaurant_id' => $otherRestaurant->id]);

        // Try to update it using Owner A's token, even if aiming at correct URL?
        // Wait, URL structure is /restaurants/{id}/boissons.
        // If I use my own restaurant ID but update boisson ID of another? 
        // Logic: Controller -> where('restaurant_id', $restaurantId)->findOrFail($id).
        // So if I pass MY restaurant ID, findOrFail will fail (404) because Boisson belongs to OTHER.
        // This is implicit security (Tenant Isolation).
        
        $response = $this->actingAs($owner, 'sanctum')->putJson("/api/v1/restaurants/{$restaurant->id}/boissons/{$boisson->id}", [
            'nom' => 'Hacked Drink'
        ]);
        
        $response->assertStatus(404); // Should be Not Found because scope/where clause filters it out.
        
        // Try passing OTHER restaurant ID (Cross-Tenant access)
        $response = $this->actingAs($owner, 'sanctum')->putJson("/api/v1/restaurants/{$otherRestaurant->id}/boissons/{$boisson->id}", [
            'nom' => 'Hacked Drink'
        ]);

        $response->assertStatus(404); // Not Found because RestaurantScope hides it
    }

    public function test_admin_can_delete_own_boisson()
    {
         $owner = User::factory()->create();
        $owner->assignRole('ADMIN_RESTAURANT');
        $restaurant = Restaurant::factory()->create(['proprietaire_id' => $owner->id]);
        $owner->restaurant_id = $restaurant->id;
        $owner->save();

        $boisson = Boisson::create(['nom' => 'Soda', 'prix' => 2.0, 'restaurant_id' => $restaurant->id]);

        $response = $this->actingAs($owner, 'sanctum')->deleteJson("/api/v1/restaurants/{$restaurant->id}/boissons/{$boisson->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('boissons', ['id' => $boisson->id]);
    }
    public function test_admin_cannot_change_boisson_restaurant_via_update()
    {
        $owner = User::factory()->create();
        $owner->assignRole('ADMIN_RESTAURANT');
        $restaurant = Restaurant::factory()->create(['proprietaire_id' => $owner->id]);
        $owner->restaurant_id = $restaurant->id; $owner->save();

        $boisson = Boisson::create([
            'nom' => 'Soda Fixed',
            'prix' => 2.5,
            'restaurant_id' => $restaurant->id
        ]);

        $otherRestaurant = Restaurant::factory()->create();

        // Attempt to move to other restaurant
        $response = $this->actingAs($owner, 'sanctum')->putJson("/api/v1/restaurants/{$restaurant->id}/boissons/{$boisson->id}", [
            'nom' => 'Soda Moved?',
            'restaurant_id' => $otherRestaurant->id
        ]);

        $response->assertStatus(200); // Should succeed in updating name
        
        $boisson->refresh();
        $this->assertEquals('Soda Moved?', $boisson->nom);
        $this->assertEquals($restaurant->id, $boisson->restaurant_id); // Must NOT change
        $this->assertNotEquals($otherRestaurant->id, $boisson->restaurant_id);
    }
}
