<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CommandeItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    /**
     * @OA\Get(
     *      path="/api/v1/commandes/{commandeId}/items",
     *      operationId="getCommandeItemsList",
     *      tags={"CommandeItems"},
     *      summary="Get list of commande items",
     *      @OA\Parameter(name="commandeId", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\Response(response=200, description="Successful operation")
     * )
     */
    public function index($commandeId)
    {
        $items = \App\Models\Commande::findOrFail($commandeId)->items;
        return response()->json(['success' => true, 'data' => $items]);
    }

    /**
     * @OA\Post(
     *      path="/api/v1/commandes/{commandeId}/items",
     *      operationId="storeCommandeItem",
     *      tags={"CommandeItems"},
     *      summary="Add item to commande",
     *      description="Guest requires X-Order-Token",
     *      @OA\Parameter(name="commandeId", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\Parameter(
     *          name="X-Order-Token",
     *          in="header",
     *          required=false,
     *          description="Guest Session Token",
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\RequestBody(
     *          required=true, 
     *          @OA\JsonContent(
     *              required={"item_type", "item_id", "quantite"},
     *              @OA\Property(property="item_type", type="string", enum={"plat", "boisson"}),
     *              @OA\Property(property="item_id", type="integer"),
     *              @OA\Property(property="quantite", type="integer")
     *          )
     *      ),
     *      @OA\Response(response=201, description="Created")
     * )
     */
    public function store(Request $request, $commandeId)
    {
        $request->validate([
            'item_type' => 'required|in:plat,boisson',
            'item_id' => 'required|integer',
            'quantite' => 'required|integer|min:1',
        ]);
        
        $commande = \App\Models\Commande::findOrFail($commandeId);
        
        $modelClass = $request->item_type === 'plat' ? \App\Models\Plat::class : \App\Models\Boisson::class;
        $itemModel = $modelClass::findOrFail($request->item_id);

        // Consistency Check: Item must belong to same restaurant as Commande (via Table)
        // Accessing relationships: Commande -> Table -> Restaurant
        // Item (Plat) -> Categorie -> Restaurant OR Item (Boisson) -> Restaurant
        
        $commandeRestaurantId = $commande->table->restaurant_id;
        $itemRestaurantId = null;

        if ($request->item_type === 'plat') {
            $itemRestaurantId = $itemModel->categorie->restaurant_id;
        } else {
            $itemRestaurantId = $itemModel->restaurant_id;
        }

        if ($commandeRestaurantId !== $itemRestaurantId) {
            return response()->json(['success' => false, 'message' => 'L\'item ne fait pas partie du restaurant de la commande.'], 403);
        }

        $item = $commande->items()->create([
            'itemable_id' => $itemModel->id,
            'itemable_type' => $modelClass,
            'quantite' => $request->quantite,
            'prix_unitaire' => $itemModel->prix // Price frozen here
        ]);

        // Update total commande
        $commande->calculerTotal();

        return response()->json(['success' => true, 'data' => $item], 201);
    }

    /**
     * @OA\Get(
     *      path="/api/v1/commandes/{commandeId}/items/{id}",
     *      operationId="getCommandeItemById",
     *      tags={"CommandeItems"},
     *      summary="Get commande item information",
     *      @OA\Parameter(name="commandeId", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\Parameter(
     *          name="X-Order-Token",
     *          in="header",
     *          required=false,
     *          description="Guest Session Token",
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Response(response=200, description="Successful operation")
     * )
     */
    public function show($commandeId, $id)
    {
        $item = \App\Models\CommandeItem::where('commande_id', $commandeId)->findOrFail($id);
        return response()->json(['success' => true, 'data' => $item]);
    }

    /**
     * @OA\Put(
     *      path="/api/v1/commandes/{commandeId}/items/{id}",
     *      operationId="updateCommandeItem",
     *      tags={"CommandeItems"},
     *      summary="Update commande item",
     *      security={{"sanctum":{}}},
     *      @OA\Parameter(name="commandeId", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/CommandeItem")),
     *      @OA\Response(response=200, description="Successful operation")
     * )
     */
    public function update(Request $request, $commandeId, $id)
    {
        $item = \App\Models\CommandeItem::where('commande_id', $commandeId)->findOrFail($id);
        
        if (request()->user()) {
             $this->authorize('update', $item->commande);
        }

        $item->update($request->all());
        
        $item->commande->calculerTotal();

        return response()->json(['success' => true, 'data' => $item]);
    }

    /**
     * @OA\Delete(
     *      path="/api/v1/commandes/{commandeId}/items/{id}",
     *      operationId="deleteCommandeItem",
     *      tags={"CommandeItems"},
     *      summary="Delete commande item",
     *      description="Guest requires X-Order-Token",
     *      @OA\Parameter(name="commandeId", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\Parameter(
     *          name="X-Order-Token",
     *          in="header",
     *          required=false,
     *          description="Guest Session Token",
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Response(response=200, description="Successful operation")
     * )
     */
    public function destroy($commandeId, $id)
    {
        $item = \App\Models\CommandeItem::where('commande_id', $commandeId)->findOrFail($id);
        
        if (request()->user()) {
             $this->authorize('update', $item->commande); // Deleting item = Updating order
        }
        
        $commande = $item->commande;
        $item->delete();
        
        $commande->calculerTotal();

        return response()->json(['success' => true, 'message' => 'Item supprimé']);
    }
}
