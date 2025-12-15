<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TableController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    /**
     * @OA\Get(
     *      path="/api/v1/restaurants/{restaurantId}/tables",
     *      operationId="getTablesList",
     *      tags={"Tables"},
     *      summary="Get list of tables",
     *      @OA\Parameter(name="restaurantId", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\Response(response=200, description="Successful operation")
     * )
     */
    public function index($restaurantId)
    {
        $this->authorize('viewAny', \App\Models\Table::class);
        $tables = \App\Models\Restaurant::findOrFail($restaurantId)->tables;
        return response()->json(['success' => true, 'data' => $tables]);
    }

    /**
     * @OA\Post(
     *      path="/api/v1/restaurants/{restaurantId}/tables",
     *      operationId="storeTable",
     *      tags={"Tables"},
     *      summary="Store new table",
     *      security={{"sanctum":{}}},
     *      @OA\Parameter(name="restaurantId", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/Table")),
     *      @OA\Response(response=201, description="Created")
     * )
     */
    public function store(Request $request, $restaurantId)
    {
        $this->authorize('create', \App\Models\Table::class);
        $request->validate(['numero' => 'required|string']);
        $restaurant = \App\Models\Restaurant::findOrFail($restaurantId);
        $this->authorize('update', $restaurant);
        
        $table = $restaurant->tables()->create([
            'numero' => $request->numero,
            'qr_code_url' => $request->qr_code_url // Optional
        ]);

        return response()->json(['success' => true, 'data' => $table], 201);
    }

    /**
     * @OA\Get(
     *      path="/api/v1/restaurants/{restaurantId}/tables/{id}",
     *      operationId="getTableById",
     *      tags={"Tables"},
     *      summary="Get table information",
     *      security={{"sanctum":{}}},
     *      @OA\Parameter(name="restaurantId", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\Response(response=200, description="Successful operation")
     * )
     */
    public function show($restaurantId, $id)
    {
        $table = \App\Models\Table::where('restaurant_id', $restaurantId)->findOrFail($id);
        $this->authorize('view', $table);
        return response()->json(['success' => true, 'data' => $table]);
    }

    /**
     * @OA\Put(
     *      path="/api/v1/restaurants/{restaurantId}/tables/{id}",
     *      operationId="updateTable",
     *      tags={"Tables"},
     *      summary="Update table",
     *      security={{"sanctum":{}}},
     *      @OA\Parameter(name="restaurantId", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\RequestBody(required=true, @OA\JsonContent(ref="#/components/schemas/Table")),
     *      @OA\Response(response=200, description="Successful operation")
     * )
     */
    public function update(Request $request, $restaurantId, $id)
    {
        $table = \App\Models\Table::where('restaurant_id', $restaurantId)->findOrFail($id);
        $this->authorize('update', $table);
        $table->update($request->all());
        return response()->json(['success' => true, 'data' => $table]);
    }

    /**
     * @OA\Delete(
     *      path="/api/v1/restaurants/{restaurantId}/tables/{id}",
     *      operationId="deleteTable",
     *      tags={"Tables"},
     *      summary="Delete table",
     *      security={{"sanctum":{}}},
     *      @OA\Parameter(name="restaurantId", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *      @OA\Response(response=200, description="Successful operation")
     * )
     */
    public function destroy($restaurantId, $id)
    {
        $table = \App\Models\Table::where('restaurant_id', $restaurantId)->findOrFail($id);
        $this->authorize('delete', $table);
        $table->delete();
        return response()->json(['success' => true, 'message' => 'Table supprimée']);
    }
}
