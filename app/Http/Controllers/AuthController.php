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
   public function login(Request $request)
   {
       $request->validate([
           'email' => 'required|string|email|max:255',
           'password' => 'required|string|min:8',
       ]);
   
       if (!Auth::attempt($request->only('email', 'password'))) {
           throw ValidationException::withMessages([
               'email' => ['Invalid credentials'],
           ]);
       }
   
       $user = Auth::user();
   
       // Vérification de l'étape d'inscription
       if (!$user->hasCompletedRegistration()) {
           Auth::logout(); // Empêche la connexion
           return response()->json([
               'message' => 'Registration incomplete. Please complete category selection.',
               'next_step' => 'choose-categories'
           ], 403); // Code 403 Forbidden
       }
   
       $token = $user->createToken('auth_token')->plainTextToken;
   
       return response()->json([
           'message' => 'Login successful',
           'token' => $token,
           'user' => $user
       ], 200);
   }
   
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
           $existingUser->update(['registration_complete' => false]); // Réinitialisation
           $user = $existingUser;
       } else {
           $user = User::create([
               'first_name' => $request->first_name,
               'last_name' => $request->last_name,
               'email' => $request->email,
               'password' => Hash::make($request->password),
               'date_of_birth' => $request->date_of_birth,
               'gender' => $request->gender,
               'registration_complete' => false // Inscription incomplète
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
   
   public function chooseCategories(Request $request)
   {
       $request->validate([
           'category_ids' => 'required|array',
           'category_ids.*' => 'exists:categories,id'
       ]);
   
       $user = $request->user();
       
       if (!$user) {
           return response()->json(['message' => 'Unauthorized'], 401);
       }
   
       $user->categories()->sync($request->category_ids);
       $user->update(['registration_complete' => true]); // Finalisation
   
       return response()->json([
           'message' => 'Registration completed!',
           'user' => $user->load('categories')
       ], 200);
   }

   
   public function getCategories()
{
    $categories = Categorie::all(); // Récupère toutes les catégories (Sport, Éducation, etc.)

    return response()->json([
        'message' => 'Categories retrieved successfully',
        'categories' => $categories
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
