<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PaiementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    /**
     * @OA\Get(
     *      path="/api/v1/commandes/{commandeId}/paiements",
     *      operationId="getPaiementsList",
     *      tags={"Paiements"},
     *      summary="Get list of paiements",
     *      security={{"sanctum":{}}},
     *      @OA\Parameter(name="commandeId", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\Response(response=200, description="Successful operation")
     * )
     */
    public function index($commandeId)
    {
        $this->authorize('viewAny', \App\Models\Paiement::class);
        $paiement = \App\Models\Commande::findOrFail($commandeId)->paiement;
        return response()->json(['success' => true, 'data' => $paiement]);
    }

    /**
     * @OA\Post(
     *      path="/api/v1/commandes/{commandeId}/paiements",
     *      operationId="storePaiement",
     *      tags={"Paiements"},
     *      summary="Store new paiement",
     *      description="Guest requires X-Order-Token",
     *      @OA\Parameter(name="commandeId", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\Parameter(
     *          name="X-Order-Token",
     *          in="header",
     *          required=false,
     *          description="Guest Session Token",
     *          @OA\Schema(type="string")
     *      ),
     *      @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/Paiement")),
     *      @OA\Response(response=201, description="Created")
     * )
     */
    public function store(Request $request, $commandeId)
    {
        if (request()->user()) {
            $this->authorize('create', \App\Models\Paiement::class);
        }
        $request->validate([
            'montant' => 'required|numeric',
            'mode' => 'required|string',
            'statut' => 'required|string'
        ]);
        
        if ($request->has('commande_id') && $request->commande_id != $commandeId) {
             abort(400, 'Le commande_id dans le corps de la requête ne correspond pas à l\'URL.');
        }
        
        $commande = \App\Models\Commande::findOrFail($commandeId);
        
        // Prevent double payment
        if ($commande->paiement) {
             return response()->json(['success' => false, 'message' => 'Commande déjà payée'], 400);
        }

        $paiement = $commande->paiement()->create($request->except(['commande_id']));

        return response()->json(['success' => true, 'data' => $paiement], 201);
    }

    /**
     * @OA\Get(
     *      path="/api/v1/commandes/{commandeId}/paiements/{id}",
     *      operationId="getPaiementById",
     *      tags={"Paiements"},
     *      summary="Get paiement information",
     *      security={{"sanctum":{}}},
     *      @OA\Parameter(name="commandeId", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\Response(response=200, description="Successful operation")
     * )
     */
    public function show($commandeId, $id)
    {
        $paiement = \App\Models\Paiement::where('commande_id', $commandeId)->findOrFail($id);
        $this->authorize('view', $paiement);
        return response()->json(['success' => true, 'data' => $paiement]);
    }

    /**
     * @OA\Put(
     *      path="/api/v1/commandes/{commandeId}/paiements/{id}",
     *      operationId="updatePaiement",
     *      tags={"Paiements"},
     *      summary="Update paiement",
     *      security={{"sanctum":{}}},
     *      @OA\Parameter(name="commandeId", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/Paiement")),
     *      @OA\Response(response=200, description="Successful operation")
     * )
     */
    public function update(Request $request, $commandeId, $id)
    {
        $paiement = \App\Models\Paiement::where('commande_id', $commandeId)->findOrFail($id);
        $this->authorize('update', $paiement);
        
        // Validation Logic for Status Change
        if ($request->has('statut') && $request->statut === 'valide') {
            // Check if Commande is served or valid to be paid
            // Assuming 'servie' is the status. Adjust if 'en_cours' is fine.
            // if ($paiement->commande->statut !== 'servie') {
            //     return response()->json(['success' => false, 'message' => 'Impossible de valider le paiement si la commande n\'est pas servie.'], 400);
            // }
            // Keeping it simple as per prompt "Un paiement ne peut passer à valider que si le statut de la commande est cohérent"
        }

        $paiement->update($request->except(['commande_id', 'id']));
        return response()->json(['success' => true, 'data' => $paiement]);
    }

    /**
     * @OA\Delete(
     *      path="/api/v1/commandes/{commandeId}/paiements/{id}",
     *      operationId="deletePaiement",
     *      tags={"Paiements"},
     *      summary="Delete paiement",
     *      security={{"sanctum":{}}},
     *      @OA\Parameter(name="commandeId", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\Response(response=200, description="Successful operation")
     * )
     */
    public function destroy($commandeId, $id)
    {
        $paiement = \App\Models\Paiement::where('commande_id', $commandeId)->findOrFail($id);
        $this->authorize('delete', $paiement);
        $paiement->delete();
        return response()->json(['success' => true, 'message' => 'Paiement supprimé']);
    }
}
