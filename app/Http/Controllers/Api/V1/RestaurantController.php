<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Restaurant;

class RestaurantController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //Afficher la liste des restaurants
        $restaurants = Restaurant::with('proprietaire')->get();

        return response()->json([
            'success' => true,
            'data' => $restaurants
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //Créer un nouveau restaurant
        $validated = $request->validate([
            'nom' => 'required|string|max:255',
            'adresse' => 'nullable|string|max:255',
            'telephone' => 'nullable|string|max:20',
            'proprietaire_id' => 'required|exists:users,id',
        ]);

        $restaurant = Restaurant::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Restaurant créé avec succès',
            'data' => $restaurant
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //Afficher un restaurant
        $restaurant = Restaurant::with(['proprietaire', 'employes', 'tables', 'categories'])
                                ->find($id);

        if (!$restaurant) {
            return response()->json([
                'success' => false,
                'message' => 'Restaurant non trouvé'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $restaurant
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //Update un restaurant
        $restaurant = Restaurant::find($id);

        if (!$restaurant) {
            return response()->json([
                'success' => false,
                'message' => 'Restaurant non trouvé'
            ], 404);
        }

        $validated = $request->validate([
            'nom' => 'sometimes|string|max:255',
            'adresse' => 'nullable|string|max:255',
            'telephone' => 'nullable|string|max:20',
            'proprietaire_id' => 'sometimes|exists:users,id',
        ]);

        $restaurant->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Restaurant mis à jour avec succès',
            'data' => $restaurant
        ]);
    }

    /**
     * Remove the specified resource from storage.
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

        $restaurant->delete();

        return response()->json([
            'success' => true,
            'message' => 'Restaurant supprimé avec succès'
        ]);
    }
}
