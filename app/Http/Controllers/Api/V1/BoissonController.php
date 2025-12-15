<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BoissonController extends Controller
{
    /**
     * @OA\Get(
     *      path="/api/v1/restaurants/{restaurantId}/boissons",
     *      operationId="getBoissonsList",
     *      tags={"Boissons"},
     *      summary="Get list of boissons",
     *      @OA\Parameter(name="restaurantId", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\Response(response=200, description="Successful operation")
     * )
     */
    public function index($restaurantId)
    {
        $boissons = \App\Models\Boisson::where('restaurant_id', $restaurantId)->get();
        return response()->json(['success' => true, 'data' => $boissons]);
    }

    /**
     * @OA\Post(
     *      path="/api/v1/restaurants/{restaurantId}/boissons",
     *      operationId="storeBoisson",
     *      tags={"Boissons"},
     *      summary="Store new boisson",
     *      security={{"sanctum":{}}},
     *      @OA\Parameter(name="restaurantId", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/Boisson")),
     *      @OA\Response(response=201, description="Created")
     * )
     */
    public function store(Request $request, $restaurantId)
    {
        $request->validate([
            'nom' => 'required|string',
            'prix' => 'required|numeric'
        ]);
        
        $restaurant = \App\Models\Restaurant::findOrFail($restaurantId);
        $this->authorize('update', $restaurant);

        $boisson = \App\Models\Boisson::create(array_merge(
            $request->except(['restaurant_id']), 
            ['restaurant_id' => $restaurantId]
        ));

        return response()->json(['success' => true, 'data' => $boisson], 201);
    }

    /**
     * @OA\Get(
     *      path="/api/v1/restaurants/{restaurantId}/boissons/{id}",
     *      operationId="getBoissonById",
     *      tags={"Boissons"},
     *      summary="Get boisson information",
     *      security={{"sanctum":{}}},
     *      @OA\Parameter(name="restaurantId", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\Response(response=200, description="Successful operation")
     * )
     */
    public function show($restaurantId, $id)
    {
        $boisson = \App\Models\Boisson::where('restaurant_id', $restaurantId)->findOrFail($id);
        if (request()->user()) {
             $this->authorize('view', $boisson->restaurant); 
        }
        return response()->json(['success' => true, 'data' => $boisson]);
    }

    /**
     * @OA\Put(
     *      path="/api/v1/restaurants/{restaurantId}/boissons/{id}",
     *      operationId="updateBoisson",
     *      tags={"Boissons"},
     *      summary="Update boisson",
     *      security={{"sanctum":{}}},
     *      @OA\Parameter(name="restaurantId", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/Boisson")),
     *      @OA\Response(response=200, description="Successful operation")
     * )
     */
    public function update(Request $request, $restaurantId, $id)
    {
        $boisson = \App\Models\Boisson::where('restaurant_id', $restaurantId)->findOrFail($id);
        $this->authorize('update', $boisson->restaurant);
        $boisson->update($request->except(['restaurant_id', 'id']));
        return response()->json(['success' => true, 'data' => $boisson]);
    }

    /**
     * @OA\Delete(
     *      path="/api/v1/restaurants/{restaurantId}/boissons/{id}",
     *      operationId="deleteBoisson",
     *      tags={"Boissons"},
     *      summary="Delete boisson",
     *      security={{"sanctum":{}}},
     *      @OA\Parameter(name="restaurantId", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\Response(response=200, description="Successful operation")
     * )
     */
    public function destroy($restaurantId, $id)
    {
        $boisson = \App\Models\Boisson::where('restaurant_id', $restaurantId)->findOrFail($id);
        $this->authorize('delete', $boisson->restaurant);
        $boisson->delete();
        return response()->json(['success' => true, 'message' => 'Boisson supprimée']);
    }
}
