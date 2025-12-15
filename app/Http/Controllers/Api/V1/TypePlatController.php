<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TypePlatController extends Controller
{
    /**
     * @OA\Get(
     *      path="/api/v1/restaurants/{restaurantId}/type_plats",
     *      operationId="getTypePlatsList",
     *      tags={"TypePlats"},
     *      summary="Get list of type plats",
     *      @OA\Parameter(name="restaurantId", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\Response(response=200, description="Successful operation")
     * )
     */
    public function index($restaurantId)
    {
        // $types = \App\Models\Restaurant::findOrFail($restaurantId)->typePlats; 
        $types = \App\Models\TypePlat::where('restaurant_id', $restaurantId)->get();
        return response()->json(['success' => true, 'data' => $types]);
    }

    /**
     * @OA\Post(
     *      path="/api/v1/restaurants/{restaurantId}/type_plats",
     *      operationId="storeTypePlat",
     *      tags={"TypePlats"},
     *      summary="Store new type plat",
     *      security={{"sanctum":{}}},
     *      @OA\Parameter(name="restaurantId", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/TypePlat")),
     *      @OA\Response(response=201, description="Created")
     * )
     */
    public function store(Request $request, $restaurantId)
    {
        $request->validate(['nom' => 'required|string|max:255']);
        
        $restaurant = \App\Models\Restaurant::findOrFail($restaurantId);
        $this->authorize('update', $restaurant);

        $type = \App\Models\TypePlat::create([
            'nom' => $request->nom,
            'restaurant_id' => $restaurantId
        ]);

        return response()->json(['success' => true, 'data' => $type], 201);
    }

    /**
     * @OA\Get(
     *      path="/api/v1/restaurants/{restaurantId}/type_plats/{id}",
     *      operationId="getTypePlatById",
     *      tags={"TypePlats"},
     *      summary="Get type plat information",
     *      security={{"sanctum":{}}},
     *      @OA\Parameter(name="restaurantId", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\Response(response=200, description="Successful operation")
     * )
     */
    public function show($restaurantId, $id)
    {
        $type = \App\Models\TypePlat::where('restaurant_id', $restaurantId)->findOrFail($id);
        return response()->json(['success' => true, 'data' => $type]);
    }

    /**
     * @OA\Put(
     *      path="/api/v1/restaurants/{restaurantId}/type_plats/{id}",
     *      operationId="updateTypePlat",
     *      tags={"TypePlats"},
     *      summary="Update type plat",
     *      security={{"sanctum":{}}},
     *      @OA\Parameter(name="restaurantId", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/TypePlat")),
     *      @OA\Response(response=200, description="Successful operation")
     * )
     */
    public function update(Request $request, $restaurantId, $id)
    {
        $type = \App\Models\TypePlat::where('restaurant_id', $restaurantId)->findOrFail($id);
        $type->update($request->all());
        return response()->json(['success' => true, 'data' => $type]);
    }

    /**
     * @OA\Delete(
     *      path="/api/v1/restaurants/{restaurantId}/type_plats/{id}",
     *      operationId="deleteTypePlat",
     *      tags={"TypePlats"},
     *      summary="Delete type plat",
     *      security={{"sanctum":{}}},
     *      @OA\Parameter(name="restaurantId", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\Response(response=200, description="Successful operation")
     * )
     */
    public function destroy($restaurantId, $id)
    {
        $type = \App\Models\TypePlat::where('restaurant_id', $restaurantId)->findOrFail($id);
        $type->delete();
        return response()->json(['success' => true, 'message' => 'Type supprimé']);
    }
}
