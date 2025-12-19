<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Restaurant;
use App\Models\Table;
use App\Models\Categorie;
use App\Models\Plat;
use Spatie\Permission\Models\Role;

class ResourceControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        if (!Role::where('name', 'ADMIN_RESTAURANT')->exists()) {
            Role::create(['name' => 'ADMIN_RESTAURANT', 'guard_name' => 'web']);
        }
    }

    // --- TABLE TESTS ---
    public function test_admin_can_manage_own_table()
    {
        $owner = User::factory()->create();
        $owner->assignRole('ADMIN_RESTAURANT');
        $restaurant = Restaurant::factory()->create(['proprietaire_id' => $owner->id]);
        $owner->restaurant_id = $restaurant->id; $owner->save();

        // STORE
        $response = $this->actingAs($owner, 'sanctum')->postJson("/api/v1/restaurants/{$restaurant->id}/tables", ['numero' => 'T1']);
        $response->assertStatus(201);
        $tableId = $response->json('data.id');

        // UPDATE
        $response = $this->actingAs($owner, 'sanctum')->putJson("/api/v1/restaurants/{$restaurant->id}/tables/{$tableId}", ['numero' => 'T1-Updated']);
        $response->assertStatus(200);

        // DELETE
        $response = $this->actingAs($owner, 'sanctum')->deleteJson("/api/v1/restaurants/{$restaurant->id}/tables/{$tableId}");
        $response->assertStatus(200);
    }
    
    public function test_admin_cannot_access_other_restaurant_table()
    {
        $owner = User::factory()->create();
        $owner->assignRole('ADMIN_RESTAURANT');
        $restaurant = Restaurant::factory()->create(['proprietaire_id' => $owner->id]);
        $owner->restaurant_id = $restaurant->id; $owner->save();

        $other = Restaurant::factory()->create();
        $otherTable = Table::factory()->create(['restaurant_id' => $other->id]);

        // Try to update other table using MY restaurant context (should be 404 due to isolation)
        $response = $this->actingAs($owner, 'sanctum')->putJson("/api/v1/restaurants/{$restaurant->id}/tables/{$otherTable->id}", ['numero' => 'Hacked']);
        $response->assertStatus(404);

        // Try to update other table using OTHER restaurant context (should be 404 due to Scope visibility)
        $response = $this->actingAs($owner, 'sanctum')->putJson("/api/v1/restaurants/{$other->id}/tables/{$otherTable->id}", ['numero' => 'Hacked']);
        $response->assertStatus(404);
    }

    // --- CATEGORIE TESTS ---
    public function test_admin_can_manage_own_categorie()
    {
        $owner = User::factory()->create();
        $owner->assignRole('ADMIN_RESTAURANT');
        $restaurant = Restaurant::factory()->create(['proprietaire_id' => $owner->id]);
        $owner->restaurant_id = $restaurant->id; $owner->save();

        // STORE
        $response = $this->actingAs($owner, 'sanctum')->postJson("/api/v1/restaurants/{$restaurant->id}/categories", ['nom' => 'Desserts']);
        $response->assertStatus(201);
        $catId = $response->json('data.id');

        // UPDATE
        $response = $this->actingAs($owner, 'sanctum')->putJson("/api/v1/restaurants/{$restaurant->id}/categories/{$catId}", ['nom' => 'Desserts Deluxe']);
        $response->assertStatus(200);

        // DELETE
        $response = $this->actingAs($owner, 'sanctum')->deleteJson("/api/v1/restaurants/{$restaurant->id}/categories/{$catId}");
        $response->assertStatus(200);
    }

    public function test_admin_cannot_manage_other_categorie()
    {
        $owner = User::factory()->create();
        $owner->assignRole('ADMIN_RESTAURANT');
        $restaurant = Restaurant::factory()->create(['proprietaire_id' => $owner->id]);
        $owner->restaurant_id = $restaurant->id; $owner->save();

        $other = Restaurant::factory()->create();
        $otherCat = Categorie::create(['nom' => 'Other Cat', 'restaurant_id' => $other->id]);

        // 404 on Cross-Tenant (Scope hides it)
        $response = $this->actingAs($owner, 'sanctum')->deleteJson("/api/v1/restaurants/{$other->id}/categories/{$otherCat->id}");
        $response->assertStatus(404);
    }

    // --- PLAT TESTS ---
    public function test_admin_can_manage_own_plat()
    {
        $owner = User::factory()->create();
        $owner->assignRole('ADMIN_RESTAURANT');
        $restaurant = Restaurant::factory()->create(['proprietaire_id' => $owner->id]);
        $owner->restaurant_id = $restaurant->id; $owner->save();

        $cat = Categorie::create(['nom' => 'Main', 'restaurant_id' => $restaurant->id]);

        // STORE
        $response = $this->actingAs($owner, 'sanctum')->postJson("/api/v1/categories/{$cat->id}/plats", ['nom' => 'Steak', 'prix' => 20]);
        $response->assertStatus(201);
        $platId = $response->json('data.id');

        // UPDATE
        $response = $this->actingAs($owner, 'sanctum')->putJson("/api/v1/categories/{$cat->id}/plats/{$platId}", ['nom' => 'Steak Frites']);
        $response->assertStatus(200);

        // DELETE
        $response = $this->actingAs($owner, 'sanctum')->deleteJson("/api/v1/categories/{$cat->id}/plats/{$platId}");
        $response->assertStatus(200);
    }

    public function test_admin_cannot_manage_other_plat()
    {
        $owner = User::factory()->create();
        $owner->assignRole('ADMIN_RESTAURANT');
        $restaurant = Restaurant::factory()->create(['proprietaire_id' => $owner->id]);
        $owner->restaurant_id = $restaurant->id; $owner->save();

        $other = Restaurant::factory()->create();
        $otherCat = Categorie::create(['nom' => 'Other Cat', 'restaurant_id' => $other->id]);
        $otherPlat = Plat::create(['nom' => 'Other Plat', 'prix' => 10, 'categorie_id' => $otherCat->id]);

        // Cross-Tenant via Categorie
        $response = $this->actingAs($owner, 'sanctum')->postJson("/api/v1/categories/{$otherCat->id}/plats", ['nom' => 'Intruder', 'prix' => 0]);
        $response->assertStatus(404); // Scope hides Categorie

        // Cross-Tenant Delete
        $response = $this->actingAs($owner, 'sanctum')->deleteJson("/api/v1/categories/{$otherCat->id}/plats/{$otherPlat->id}");
        $response->assertStatus(404); // Scope hides Plat
    }
}
