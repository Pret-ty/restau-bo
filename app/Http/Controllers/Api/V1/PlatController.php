<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PlatController extends Controller
{
    /**
     * @OA\Get(
     *      path="/api/v1/categories/{categorieId}/plats",
     *      operationId="getPlatsList",
     *      tags={"Plats"},
     *      summary="Get list of plats",
     *      @OA\Parameter(name="categorieId", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\Response(response=200, description="Successful operation")
     * )
     */
    public function index($categorieId)
    {
        if (request()->user()) {
            $this->authorize('viewAny', \App\Models\Plat::class);
        }
        $plats = \App\Models\Categorie::findOrFail($categorieId)->plats;
        return response()->json(['success' => true, 'data' => $plats]);
    }

    /**
     * @OA\Post(
     *      path="/api/v1/categories/{categorieId}/plats",
     *      operationId="storePlat",
     *      tags={"Plats"},
     *      summary="Store new plat",
     *      security={{"sanctum":{}}},
     *      @OA\Parameter(name="categorieId", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/Plat")),
     *      @OA\Response(response=201, description="Created")
     * )
     */
    public function store(Request $request, $categorieId)
    {
        $this->authorize('create', \App\Models\Plat::class);
        $request->validate([
            'nom' => 'required|string',
            'prix' => 'required|numeric',
        ]);
        
        $categorie = \App\Models\Categorie::findOrFail($categorieId);
        // Authorize via the restaurant of the category
        $this->authorize('update', $categorie->restaurant);
        
        $plat = $categorie->plats()->create($request->all());

        return response()->json(['success' => true, 'data' => $plat], 201);
    }

    /**
     * @OA\Get(
     *      path="/api/v1/categories/{categorieId}/plats/{id}",
     *      operationId="getPlatById",
     *      tags={"Plats"},
     *      summary="Get plat information",
     *      @OA\Parameter(name="categorieId", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\Response(response=200, description="Successful operation")
     * )
     */
    public function show($categorieId, $id)
    {
        $plat = \App\Models\Plat::where('categorie_id', $categorieId)->findOrFail($id);
        $this->authorize('view', $plat);
        return response()->json(['success' => true, 'data' => $plat]);
    }

    /**
     * @OA\Put(
     *      path="/api/v1/categories/{categorieId}/plats/{id}",
     *      operationId="updatePlat",
     *      tags={"Plats"},
     *      summary="Update plat",
     *      security={{"sanctum":{}}},
     *      @OA\Parameter(name="categorieId", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/Plat")),
     *      @OA\Response(response=200, description="Successful operation")
     * )
     */
    public function update(Request $request, $categorieId, $id)
    {
        $plat = \App\Models\Plat::where('categorie_id', $categorieId)->findOrFail($id);
        $this->authorize('update', $plat);
        $plat->update($request->all());
        return response()->json(['success' => true, 'data' => $plat]);
    }

    /**
     * @OA\Delete(
     *      path="/api/v1/categories/{categorieId}/plats/{id}",
     *      operationId="deletePlat",
     *      tags={"Plats"},
     *      summary="Delete plat",
     *      security={{"sanctum":{}}},
     *      @OA\Parameter(name="categorieId", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\Response(response=200, description="Successful operation")
     * )
     */
    public function destroy($categorieId, $id)
    {
        $plat = \App\Models\Plat::where('categorie_id', $categorieId)->findOrFail($id);
        $this->authorize('delete', $plat);
        $plat->delete();
        return response()->json(['success' => true, 'message' => 'Plat supprimé']);
    }
}
