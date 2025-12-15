<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CategorieController extends Controller
{
    /**
     * @OA\Get(
     *      path="/api/v1/restaurants/{restaurantId}/categories",
     *      operationId="getCategoriesList",
     *      tags={"Categories"},
     *      summary="Get list of categories",
     *      @OA\Parameter(name="restaurantId", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\Response(response=200, description="Successful operation")
     * )
     */
    public function index($restaurantId)
    {
        if (request()->user()) {
             $this->authorize('viewAny', \App\Models\Categorie::class);
        }
        $categories = \App\Models\Restaurant::findOrFail($restaurantId)->categories;
        return response()->json(['success' => true, 'data' => $categories]);
    }

    /**
     * @OA\Post(
     *      path="/api/v1/restaurants/{restaurantId}/categories",
     *      operationId="storeCategorie",
     *      tags={"Categories"},
     *      summary="Store new categorie",
     *      security={{"sanctum":{}}},
     *      @OA\Parameter(name="restaurantId", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/Categorie")),
     *      @OA\Response(response=201, description="Created")
     * )
     */
    public function store(Request $request, $restaurantId)
    {
        $this->authorize('create', \App\Models\Categorie::class);
        $request->validate(['nom' => 'required|string|max:255']);
        $restaurant = \App\Models\Restaurant::findOrFail($restaurantId);
        $this->authorize('update', $restaurant);
        
        $categorie = $restaurant->categories()->create([
            'nom' => $request->nom,
            'restaurant_id' => $restaurantId
        ]);

        return response()->json(['success' => true, 'data' => $categorie], 201);
    }

    /**
     * @OA\Get(
     *      path="/api/v1/restaurants/{restaurantId}/categories/{id}",
     *      operationId="getCategorieById",
     *      tags={"Categories"},
     *      summary="Get categorie information",
     *      @OA\Parameter(name="restaurantId", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\Response(response=200, description="Successful operation")
     * )
     */
    public function show($restaurantId, $id)
    {
        $categorie = \App\Models\Categorie::where('restaurant_id', $restaurantId)->findOrFail($id);
        $this->authorize('view', $categorie);
        return response()->json(['success' => true, 'data' => $categorie->load('plats')]);
    }

    /**
     * @OA\Put(
     *      path="/api/v1/restaurants/{restaurantId}/categories/{id}",
     *      operationId="updateCategorie",
     *      tags={"Categories"},
     *      summary="Update categorie",
     *      security={{"sanctum":{}}},
     *      @OA\Parameter(name="restaurantId", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/Categorie")),
     *      @OA\Response(response=200, description="Successful operation")
     * )
     */
    public function update(Request $request, $restaurantId, $id)
    {
        $categorie = \App\Models\Categorie::where('restaurant_id', $restaurantId)->findOrFail($id);
        $this->authorize('update', $categorie);
        $request->validate(['nom' => 'required|string|max:255']);
        $categorie->update($request->except(['restaurant_id', 'id']));

        return response()->json(['success' => true, 'data' => $categorie]);
    }

    /**
     * @OA\Delete(
     *      path="/api/v1/restaurants/{restaurantId}/categories/{id}",
     *      operationId="deleteCategorie",
     *      tags={"Categories"},
     *      summary="Delete categorie",
     *      security={{"sanctum":{}}},
     *      @OA\Parameter(name="restaurantId", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\Response(response=200, description="Successful operation")
     * )
     */
    public function destroy($restaurantId, $id)
    {
        $categorie = \App\Models\Categorie::where('restaurant_id', $restaurantId)->findOrFail($id);
        $this->authorize('delete', $categorie);
        $categorie->delete();
        return response()->json(['success' => true, 'message' => 'Catégorie supprimée']);
    }
}
