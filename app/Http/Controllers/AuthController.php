<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    // Créer un utilisateur
    public function register(Request $request)
    {
        // Validation des données reçues
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'firstname' => 'required|string|max:255',
            'phone' => 'required|string|max:15',  
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',  
            'sexe' => 'required|in:homme,femme,autre',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048' 
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 400);
        }

        // Création de l'utilisateur
        $user = User::create([
            'name' => $request->name,
            'firstname' => $request->firstname,
            'phone' => $request->phone,
            'email' => $request->email,
            'sexe' => $request->sexe,
            'password' => Hash::make($request->password),  
        ]);

        // Gestion de la photo si présente
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('photos', 'public');
            $user->photo = $path;  // Sauvegarder le chemin de l'image
            $user->save();
        }

        return response()->json(['message' => 'Utilisateur créé avec succès', 'user' => $user], 201);
    }

    // Connectez un utilisateur et renvoyez un jeton.
    public function login(Request $request)
    {
        // Valider les entrées
        $validated = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        // Vérifier l'utilisateur dans la base de données
        $user = User::where('email', $validated['email'])->first();

        // Vérifier si l'utilisateur existe et si le mot de passe correspond
        if (!$user || !Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Les informations d\'identification fournies sont incorrectes.'],
            ]);
        }

        // Générer un token d'authentification avec Sanctum
        $token = $user->createToken('auth_token')->plainTextToken;

        // Retourner une réponse avec le token et les informations de l'utilisateur
        return response()->json(['token' => $token, 'user' => $user]);
    }   

    // Obtenir les détails de l'utilisateur
    public function me(Request $request)
    {
        $user = $request->user();
        
        // Ajout de l'URL de l'image, si l'image existe
        if ($user->photo) {
            $user->photo = url('storage/' . $user->photo);
        }
    
        return response()->json($user);
    }

    // Modifier les informations de l'utilisateur
    public function updateUserProfile(Request $request)
    {
        $user = $request->user();
    
        // Validation des données de la requête
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'firstname' => 'required|string|max:255',
            'phone' => 'required|string|max:15',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'sexe' => 'required|string',
        ]);
    
        // Mise à jour des autres informations de l'utilisateur (sans photo)
        $user->update($validatedData);
    
        // Gestion de la photo si elle existe
        if ($request->hasFile('photo')) {
            $path = $request->file('photo')->store('photos', 'public');
            $user->photo = $path;  
            $user->save();
        }
    
        // Réponse avec l'utilisateur mis à jour
        return response()->json($user);
    }    
}
