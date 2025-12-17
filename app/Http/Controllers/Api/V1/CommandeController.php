<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CommandeController extends Controller
{
    /**
     * @OA\Get(
     *      path="/api/v1/tables/{tableId}/commandes",
     *      operationId="getCommandesList",
     *      tags={"Commandes"},
     *      summary="Get list of commands",
     *      security={{"sanctum":{}}},
     *      @OA\Parameter(name="tableId", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\Response(response=200, description="Successful operation")
     * )
     */
    public function index($tableId)
    {
        // Index is usually for Waiters to see all orders of a table, or Admin.
        // If Guest needs to see their orders, they use 'show' with ID? 
        // For now, keep strict Auth for Index as per route split (Index was Protected).
        $this->authorize('viewAny', \App\Models\Commande::class);
        $commandes = \App\Models\Table::findOrFail($tableId)->commandes;
        return response()->json(['success' => true, 'data' => $commandes]);
    }

    /**
     * @OA\Post(
     *      path="/api/v1/tables/{tableId}/commandes",
     *      operationId="storeCommande",
     *      tags={"Commandes"},
     *      summary="Store new commande",
     *      description="Creates a new order. If guest, returns a session token.",
     *      @OA\Parameter(name="tableId", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/Commande")),
     *      @OA\Response(
     *          response=201, 
     *          description="Created",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="data", ref="#/components/schemas/Commande"),
     *              @OA\Property(property="token", type="string", description="Session token for guest (UUID)")
     *          )
     *      )
     * )
     */
    public function store(Request $request, $tableId)
    {
        if (request()->user()) {
            $this->authorize('create', \App\Models\Commande::class);
        }
        // Statut default 'pending' ?
        if ($request->has('table_id') && $request->table_id != $tableId) {
             abort(400, 'Le table_id dans le corps de la requête ne correspond pas à l\'URL.');
        }
        $table = \App\Models\Table::findOrFail($tableId);
        $commande = $table->commandes()->create([
            'statut' => $request->statut ?? 'en_attente',
            'total' => 0
        ]);

        $token = null;
        if (!request()->user()) {
             // Generate Guest Token
             $token = \Illuminate\Support\Str::uuid();
             \App\Models\OrderSession::create([
                 'commande_id' => $commande->id,
                 'token' => $token
             ]);
        }

        return response()->json([
            'success' => true, 
            'data' => $commande,
            'token' => $token
        ], 201);
    }

    /**
     * @OA\Get(
     *      path="/api/v1/tables/{tableId}/commandes/{id}",
     *      operationId="getCommandeById",
     *      tags={"Commandes"},
     *      summary="Get commande information",
     *      description="For guests, requires X-Order-Token header matching the order.",
     *      @OA\Parameter(name="tableId", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\Parameter(
     *          name="X-Order-Token",
     *          in="header",
     *          required=false,
     *          description="Guest Session Token (required if not authenticated)",
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\Response(response=200, description="Successful operation")
     * )
     */
    public function show($tableId, $id)
    {
        $commande = \App\Models\Commande::where('table_id', $tableId)->findOrFail($id);
        if (request()->user()) {
            $this->authorize('view', $commande);
        }
        return response()->json(['success' => true, 'data' => $commande->load('items', 'paiement')]);
    }

    /**
     * @OA\Put(
     *      path="/api/v1/tables/{tableId}/commandes/{id}",
     *      operationId="updateCommande",
     *      tags={"Commandes"},
     *      summary="Update commande",
     *      security={{"sanctum":{}}},
     *      @OA\Parameter(name="tableId", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/Commande")),
     *      @OA\Response(response=200, description="Successful operation")
     * )
     */
    public function update(Request $request, $tableId, $id)
    {
        $commande = \App\Models\Commande::where('table_id', $tableId)->findOrFail($id);
        $this->authorize('update', $commande);
        $commande->update($request->except(['table_id', 'id']));
        return response()->json(['success' => true, 'data' => $commande]);
    }

    /**
     * @OA\Delete(
     *      path="/api/v1/tables/{tableId}/commandes/{id}",
     *      operationId="deleteCommande",
     *      tags={"Commandes"},
     *      summary="Delete commande",
     *      security={{"sanctum":{}}},
     *      @OA\Parameter(name="tableId", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\Response(response=200, description="Successful operation")
     * )
     */
    public function destroy($tableId, $id)
    {
        $commande = \App\Models\Commande::where('table_id', $tableId)->findOrFail($id);
        $this->authorize('delete', $commande);
        $commande->delete();
        return response()->json(['success' => true, 'message' => 'Commande supprimée']);
    }
}
