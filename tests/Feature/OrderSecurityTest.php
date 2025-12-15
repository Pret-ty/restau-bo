<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Restaurant;
use App\Models\Table;
use App\Models\Commande;
use App\Models\CommandeItem;
use App\Models\Paiement;
use Spatie\Permission\Models\Role;

class OrderSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Create basic roles
        foreach (['ADMIN_RESTAURANT', 'SERVEUR', 'CUISINIER', 'CAISSIER'] as $role) {
            if (!Role::where('name', $role)->exists()) {
                Role::create(['name' => $role, 'guard_name' => 'web']);
            }
        }
    }

    public function test_admin_can_manage_own_order()
    {
        // Setup
        $owner = User::factory()->create();
        $owner->assignRole('ADMIN_RESTAURANT');
        $restaurant = Restaurant::factory()->create(['proprietaire_id' => $owner->id]);
        $owner->restaurant_id = $restaurant->id; $owner->save();
        $table = Table::factory()->create(['restaurant_id' => $restaurant->id]);
        
        // STORE Commande
        $response = $this->actingAs($owner, 'sanctum')->postJson("/api/v1/tables/{$table->id}/commandes", ['statut' => 'en_attente']);
        $response->assertStatus(201);
        $commandeId = $response->json('data.id');

        // UPDATE Commande
        $response = $this->actingAs($owner, 'sanctum')->putJson("/api/v1/tables/{$table->id}/commandes/{$commandeId}", ['statut' => 'en_preparation']);
        $response->assertStatus(200);

        // DELETE Commande
        $response = $this->actingAs($owner, 'sanctum')->deleteJson("/api/v1/tables/{$table->id}/commandes/{$commandeId}");
        $response->assertStatus(200);
    }

    public function test_admin_cannot_access_other_restaurant_order()
    {
        // Owner A
        $ownerA = User::factory()->create();
        $ownerA->assignRole('ADMIN_RESTAURANT');
        $restaurantA = Restaurant::factory()->create(['proprietaire_id' => $ownerA->id]);
        $ownerA->restaurant_id = $restaurantA->id; $ownerA->save();

        // Restaurant B and Order
        $restaurantB = Restaurant::factory()->create();
        $tableB = Table::factory()->create(['restaurant_id' => $restaurantB->id]);
        $commandeB = Commande::create(['table_id' => $tableB->id, 'statut' => 'en_attente', 'total' => 0]);

        // Attempt Access
        // GET returns 403 (Policy Deny) - implies Scope might be loose or bypassable on GET? but Policy catches it.
        $response = $this->actingAs($ownerA, 'sanctum')->getJson("/api/v1/tables/{$tableB->id}/commandes/{$commandeB->id}");
        $response->assertStatus(403);

        // PUT returns 404 (Not Found) - implies Scope works or Model binding fails?
        $response = $this->actingAs($ownerA, 'sanctum')->putJson("/api/v1/tables/{$tableB->id}/commandes/{$commandeB->id}", ['statut' => 'hack']);
        $response->assertStatus(404);
    }

    public function test_admin_cannot_update_items_of_other_restaurant()
    {
        $ownerA = User::factory()->create();
        $ownerA->assignRole('ADMIN_RESTAURANT');
        $restaurantA = Restaurant::factory()->create(['proprietaire_id' => $ownerA->id]);
        $ownerA->restaurant_id = $restaurantA->id; $ownerA->save();

        $restaurantB = Restaurant::factory()->create();
        $tableB = Table::factory()->create(['restaurant_id' => $restaurantB->id]);
        $commandeB = Commande::create(['table_id' => $tableB->id, 'statut' => 'en_attente']);
        // Create dummy item
        // Needs proper models to avoid constraint failures if strict
        $catB = \App\Models\Categorie::factory()->create(['restaurant_id' => $restaurantB->id]);
        $platB = \App\Models\Plat::factory()->create(['categorie_id' => $catB->id]);
        
        $itemB = $commandeB->items()->create([
            'itemable_id' => $platB->id,
            'itemable_type' => \App\Models\Plat::class,
            'quantite' => 1, 
            'prix_unitaire' => 10
        ]);

        // Attempt to Update Item B (Owner A)
        // With Scope: Commande is invisible -> 404 (via related check or if using relationship)
        // CommandeItemController uses: CommandeItem::where('commande_id', ...)->findOrFail()
        // CommandeItem has NO scope?
        // But we added authorize('update', $item->commande).
        // $item->commande fetches Commande via relationship. Commande HAS scope. 
        // So accessing $item->commande might return null? Or finds it but Scope filters?
        // Actually, internal relationship access usually bypasses Global Scopes? No, default is apply.
        // If Model Instance A vs B?
        // Wait, if I fetch Item B directly, I get it (no scope on Item).
        // Then I call $item->commande.
        // Then I call authorize('update', $commande).
        // Policy checks $user->rest_id vs $commande->table->rest_id.
        // It SHOULD fail (return false).
        // So 403.
        // UNLESS... I search by `commande_id` which I can't see?
        // `CommandeItem::where('commande_id', $commandeB->id)` -> Works (no validation on commande existence for user yet).
        // So I find the item.
        // Then `authorize` fails. So 403.
        
        $response = $this->actingAs($ownerA, 'sanctum')->putJson("/api/v1/commandes/{$commandeB->id}/items/{$itemB->id}", ['quantite' => 999]);
        $response->assertStatus(403);

        $response = $this->actingAs($ownerA, 'sanctum')->deleteJson("/api/v1/commandes/{$commandeB->id}/items/{$itemB->id}");
        $response->assertStatus(403);
    }
    
    public function test_admin_cannot_access_other_paiement()
    {
        $ownerA = User::factory()->create();
        $ownerA->assignRole('ADMIN_RESTAURANT');
        $restaurantA = Restaurant::factory()->create(['proprietaire_id' => $ownerA->id]);
        $ownerA->restaurant_id = $restaurantA->id; $ownerA->save();

        $restaurantB = Restaurant::factory()->create();
        $tableB = Table::factory()->create(['restaurant_id' => $restaurantB->id]);
        $commandeB = Commande::create(['table_id' => $tableB->id, 'total' => 100, 'statut' => 'servie']);
        $paiementB = \App\Models\Paiement::create([
             'commande_id' => $commandeB->id,
             'montant' => 100,
             'mode' => 'carte',
             'statut' => 'en_attente'
        ]);

        // Paiement Controller uses Paiement::where...
        // Paiement has NO scope?
        // authorize('view', $paiement) checks ownership.
        // Should be 403.
        
        $response = $this->actingAs($ownerA, 'sanctum')->getJson("/api/v1/commandes/{$commandeB->id}/paiements/{$paiementB->id}");
        $response->assertStatus(403);
    }
}
