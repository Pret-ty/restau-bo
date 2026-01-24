<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\UserResource;

class UtilisateurController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', User::class);
        $restaurantId = auth()->user()->restaurant_id;
        
        $users = User::where('restaurant_id', $restaurantId)
            ->with('roles')
            ->get();

        return response()->json(['success' => true, 'data' => UserResource::collection($users)]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', User::class);

        $request->validate([
            'nom' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'string', 'in:SERVEUR,CUISINIER,CAISSIER,ADMIN'],
        ]);

        $restaurantId = auth()->user()->restaurant_id;

        $user = User::create([
            'nom' => $request->nom,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'restaurant_id' => $restaurantId,
        ]);

        $role = $request->role;
        $user->assignRole($role);

        return response()->json([
            'success' => true,
            'data' => UserResource::make($user->load('roles')),
            'message' => 'Utilisateur créé avec succès.'
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $user = User::findOrFail($id);
        $this->authorize('view', $user);
        return response()->json(['success' => true, 'data' => UserResource::make($user->load('roles'))]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = User::findOrFail($id);
        $this->authorize('update', $user);

        $request->validate([
            'nom' => ['sometimes', 'string', 'max:255'],
            'email' => ['sometimes', 'string', 'email', 'max:255', 'unique:users,email,' . $id],
            'role' => ['sometimes', 'string', 'in:SERVEUR,CUISINIER,CAISSIER,ADMIN'],
        ]);

        $user->update($request->only(['nom', 'email']));

        if ($request->has('role')) {
            $user->syncRoles([$request->role]);
        }

        return response()->json(['success' => true, 'data' => UserResource::make($user->load('roles'))]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $user = User::findOrFail($id);
        $this->authorize('delete', $user);
        $user->delete();
        return response()->json(['success' => true, 'message' => 'Utilisateur supprimé']);
    }
}
