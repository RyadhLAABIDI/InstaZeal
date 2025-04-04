<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Categorie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;


class AuthController extends Controller
{
    // Inscription
   // Inscription
   public function register(Request $request)
   {
       $request->validate([
           'first_name' => 'required|string|max:255',
           'last_name' => 'required|string|max:255',
           'email' => 'required|string|email|max:255|unique:users,email',
           'password' => 'required|string|min:8|confirmed',
           'date_of_birth' => 'required|date',
           'gender' => 'required|string',
       ]);
   
       $existingUser = User::onlyTrashed()->where('email', $request->email)->first();
       if ($existingUser) {
           $existingUser->restore();
           $user = $existingUser;
       } else {
           $user = User::create([
               'first_name' => $request->first_name,
               'last_name' => $request->last_name,
               'email' => $request->email,
               'password' => Hash::make($request->password),
               'date_of_birth' => $request->date_of_birth,
               'gender' => $request->gender,
           ]);
       }
   
       $token = $user->createToken('auth_token')->plainTextToken;
   
       return response()->json([
           'message' => 'User registered successfully. Please choose your categories to complete registration.',
           'token' => $token,
           'user' => $user,
           'next_step' => 'choose-categories'
       ], 201);
   }


   public function getCategories()
{
    $categories = Categorie::all(); // Récupère toutes les catégories (Sport, Éducation, etc.)

    return response()->json([
        'message' => 'Categories retrieved successfully',
        'categories' => $categories
    ], 200);
}


public function chooseCategories(Request $request)
{
    $request->validate([
        'category_ids' => 'required|array',
        'category_ids.*' => 'exists:categories,id' // Vérifie que chaque ID existe dans la table categories
    ]);

    $user = $request->user();

    if (!$user) {
        return response()->json([
            'message' => 'Unauthorized'
        ], 401);
    }

    $user->categories()->sync($request->category_ids);

    return response()->json([
        'message' => 'Categories selected successfully. Registration completed!',
        'user' => $user->load('categories')
    ], 200);
}


   
    // Connexion
    public function login(Request $request)
{
    // Validation des entrées
    $request->validate([
        'email' => 'required|string|email|max:255',
        'password' => 'required|string|min:8',
    ]);

    // Tentative de connexion
    if (!Auth::attempt($request->only('email', 'password'))) {
        throw ValidationException::withMessages([
            'email' => ['Invalid credentials'],
        ]);
    }

    // Récupérer l'utilisateur authentifié
    $user = Auth::user();

    // Créer le token
    $token = $user->createToken('auth_token')->plainTextToken;

    // Renvoyer la réponse avec le token et les informations de l'utilisateur
    return response()->json([
        'message' => 'Login successful',
        'token' => $token,
        'user' => $user // Vous renvoyez l'utilisateur complet ici
    ], 200);
}





public function logout(Request $request)
{
    // Log de début de déconnexion
    Log::info('Tentative de déconnexion de l\'utilisateur', [
        'user_id' => $request->user()->id,  // ID de l'utilisateur
        'email' => $request->user()->email, // Email de l'utilisateur
    ]);

    // Déconnexion de l'utilisateur sans supprimer les tokens
    $user = $request->user();  // Récupère l'utilisateur authentifié
    $user->tokens->each(function ($token) {
        $token->delete();  // Supprimer tous les tokens associés à l'utilisateur
    });

    // Log après déconnexion réussie
    Log::info('Utilisateur déconnecté avec succès', [
        'user_id' => $request->user()->id,  // ID de l'utilisateur
        'email' => $request->user()->email, // Email de l'utilisateur
    ]);

    return response()->json([
        'message' => 'Déconnexion réussie.'
    ], 200);
}



 
}
