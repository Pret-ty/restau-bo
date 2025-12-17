<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\StoreRestaurantRequest;
use App\Http\Requests\UpdateRestaurantRequest;
use App\Http\Resources\RestaurantResource;
use App\Models\Restaurant;
use App\Models\User;
use Essa\APIToolKit\Api\ApiResponse;
use Illuminate\Support\Facades\DB;

class RestaurantController extends Controller
{
    public function __construct()
    {
        // Apply Policy to resource actions
        $this->authorizeResource(Restaurant::class, 'restaurant');
    }

    // use ApiResponse;
    /**
     * Display a listing of the resource.
     */
    /**
     * @OA\Get(
     *      path="/api/v1/restaurants",
     *      operationId="getRestaurantList",
     *      tags={"Restaurants"},
     *      summary="Get list of restaurants",
     *      description="Returns list of restaurants with owner",
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/Restaurant"))
     *          )
     *      )
     * )
     */
    public function index()
    {
        //Afficher la liste des restaurants
        $restaurants = Restaurant::with('proprietaire')->get();

        return response()->json([
            'success' => true,
            'data' => RestaurantResource::collection($restaurants)
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    /**
     * @OA\Post(
     *      path="/api/v1/restaurants",
     *      operationId="storeRestaurant",
     *      tags={"Restaurants"},
     *      summary="Create new restaurant",
     *      description="Creates a new restaurant and assigns owner",
     *      security={{"sanctum":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"nom", "proprietaire_id"},
     *              @OA\Property(property="nom", type="string", example="Mon Restaurant"),
     *              @OA\Property(property="adresse", type="string", example="123 Rue Principale"),
     *              @OA\Property(property="telephone", type="string", example="0102030405"),
     *              @OA\Property(property="proprietaire_id", type="integer", example=1)
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Restaurant created",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="data", ref="#/components/schemas/Restaurant"),
     *              @OA\Property(property="message", type="string", example="Restaurant créé avec succès")
     *          )
     *      )
     * )
     */
    public function store(StoreRestaurantRequest $request)
    {
        DB::beginTransaction();
        try {
            // 1. Créer le restaurant
            $restaurant = Restaurant::create([
                'nom' => $request->nom,
                'adresse' => $request->adresse,
                'telephone' => $request->telephone,
                'proprietaire_id' => $request->proprietaire_id,
            ]);

            // 2. Assigner le rôle ADMIN_RESTAURANT au propriétaire
            $proprietaire = User::findOrFail($request->proprietaire_id);
            if (!$proprietaire->hasRole('ADMIN_RESTAURANT')) {
                $proprietaire->assignRole('ADMIN_RESTAURANT');
            }

            // 3. Lier le propriétaire au restaurant
            $proprietaire->restaurant_id = $restaurant->id;
            $proprietaire->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => RestaurantResource::make($restaurant->load('proprietaire')),
                'message' => 'Restaurant créé avec succès'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Display the specified resource.
     */
    /**
     * @OA\Get(
     *      path="/api/v1/restaurants/{id}",
     *      operationId="getRestaurantById",
     *      tags={"Restaurants"},
     *      summary="Get restaurant information",
     *      description="Returns restaurant data",
     *      @OA\Parameter(
     *          name="id",
     *          description="Restaurant id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="data", ref="#/components/schemas/Restaurant")
     *          )
     *      ),
     *      @OA\Response(response=404, description="Restaurant not found")
     * )
     */
    public function show(string $id)
    {
        //Afficher un restaurant
        $restaurant = Restaurant::with(['proprietaire', 'employes', 'categories'])
            ->find($id);

        if (!$restaurant) {
            return response()->json([
                'success' => false,
                'message' => 'Restaurant non trouvé'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => RestaurantResource::make($restaurant)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    /**
     * @OA\Put(
     *      path="/api/v1/restaurants/{id}",
     *      operationId="updateRestaurant",
     *      tags={"Restaurants"},
     *      summary="Update existing restaurant",
     *      description="Updates restaurant data",
     *      security={{"sanctum":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="Restaurant id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              @OA\Property(property="nom", type="string", example="Nouveau Nom"),
     *              @OA\Property(property="adresse", type="string", example="Nouvelle Adresse"),
     *              @OA\Property(property="telephone", type="string", example="0102030405"),
     *              @OA\Property(property="proprietaire_id", type="integer", example=1)
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful update",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="data", ref="#/components/schemas/Restaurant"),
     *              @OA\Property(property="message", type="string", example="Restaurant mis à jour avec succès")
     *          )
     *      ),
     *      @OA\Response(response=404, description="Restaurant not found")
     * )
     */
    public function update(UpdateRestaurantRequest $request, string $id)
    {
        //Update un restaurant
        $restaurant = Restaurant::find($id);

        if (!$restaurant) {
            return response()->json([
                'success' => false,
                'message' => 'Restaurant non trouvé'
            ], 404);
        }

        $this->authorize('update', $restaurant);

        $data = $request->validated();
        unset($data['proprietaire_id'], $data['id']);
        $restaurant->update($data);

        return response()->json([
            'success' => true,
            'data' => RestaurantResource::make($restaurant),
            'message' => 'Restaurant mis à jour avec succès'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    /**
     * @OA\Delete(
     *      path="/api/v1/restaurants/{id}",
     *      operationId="deleteRestaurant",
     *      tags={"Restaurants"},
     *      summary="Delete existing restaurant",
     *      description="Deletes a record and returns no content",
     *      security={{"sanctum":{}}},
     *      @OA\Parameter(
     *          name="id",
     *          description="Restaurant id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful deletion",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="message", type="string", example="Restaurant supprimé avec succès")
     *          )
     *      ),
     *      @OA\Response(response=404, description="Restaurant not found")
     * )
     */
    public function destroy(string $id)
    {
        //Supprimer un restaurant
        $restaurant = Restaurant::find($id);

        if (!$restaurant) {
            return response()->json([
                'success' => false,
                'message' => 'Restaurant non trouvé'
            ], 404);
        }

        $this->authorize('delete', $restaurant);

        $restaurant->delete();

        return response()->json([
            'success' => true,
            'message' => 'Restaurant supprimé avec succès'
        ]);
    }

    /**
     * @OA\Post(
     *      path="/api/v1/restaurants/{restaurant}/transfer-ownership",
     *      operationId="transferRestaurantOwnership",
     *      tags={"Restaurants"},
     *      summary="Transfer restaurant ownership",
     *      description="Transfers ownership to another user",
     *      security={{"sanctum":{}}},
     *      @OA\Parameter(
     *          name="restaurant",
     *          description="Restaurant id",
     *          required=true,
     *          in="path",
     *          @OA\Schema(type="integer")
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"new_proprietaire_id"},
     *              @OA\Property(property="new_proprietaire_id", type="integer", example=2)
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful transfer",
     *          @OA\JsonContent(
     *              @OA\Property(property="success", type="boolean", example=true),
     *              @OA\Property(property="data", ref="#/components/schemas/Restaurant"),
     *              @OA\Property(property="message", type="string", example="Propriété transférée avec succès")
     *          )
     *      )
     * )
     */
    public function transferOwnership(Request $request, Restaurant $restaurant)
    {
        $request->validate([
            'new_proprietaire_id' => 'required|exists:users,id',
        ]);

        DB::beginTransaction();
        try {
            $oldOwner = $restaurant->proprietaire;
            $newOwner = User::findOrFail($request->new_proprietaire_id);

            // Retirer le rôle de l'ancien propriétaire si nécessaire
            // (seulement s'il ne possède pas d'autre restaurant)
            if ($oldOwner && !$oldOwner->ownedRestaurant()->where('id', '!=', $restaurant->id)->exists()) {
                $oldOwner->removeRole('ADMIN_RESTAURANT');
            }

            // Assigner au nouveau propriétaire
            $restaurant->proprietaire_id = $newOwner->id;
            $restaurant->save();

            $newOwner->assignRole('ADMIN_RESTAURANT');
            $newOwner->restaurant_id = $restaurant->id;
            $newOwner->save();

            DB::commit();

            return response()->json([
                'success' => true,
                'data' => RestaurantResource::make($restaurant->load('proprietaire')),
                'message' => 'Propriété transférée avec succès'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
