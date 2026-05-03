<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Utilisateur;

class UtilisateurController extends Controller
{
    public function index()
    {
        return response()->json(Utilisateur::all());
    }

    public function show($id)
    {
        return response()->json(Utilisateur::findOrFail($id));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email|unique:utilisateurs,email',
            'mot_de_passe' => 'required|min:4',
            'role' => 'required|in:admin,client'
        ]);

        $user = Utilisateur::create($data);

        return response()->json($user, 201);
    }

    public function update(Request $request, $id)
    {
        $user = Utilisateur::findOrFail($id);

        $data = $request->validate([
            'email' => 'sometimes|email|unique:utilisateurs,email,' . $user->id,
            'mot_de_passe' => 'nullable|min:4',
            'role' => 'sometimes|in:admin,client',
            'is_blocked' => 'sometimes|boolean'
        ]);

        // Si mot de passe vide → ignorer
        if (isset($data['mot_de_passe']) && empty($data['mot_de_passe'])) {
    	    unset($data['mot_de_passe']);
	}

        $user->update($data);

        return response()->json(['message' => 'Utilisateur mis à jour']);
    }

    public function destroy($id)
    {
        $user = Utilisateur::findOrFail($id);
        $user->delete();
        return response()->json(null, 204);
    }

    // =========================
    // BLOQUER / DÉBLOQUER
    // =========================
    public function toggleBlock($id)
    {
        $user = Utilisateur::findOrFail($id);

        $user->is_blocked = !$user->is_blocked;
        $user->save();

        return response()->json([
            'message' => $user->is_blocked ? 'Utilisateur bloqué' : 'Utilisateur débloqué'
        ]);
    }
}