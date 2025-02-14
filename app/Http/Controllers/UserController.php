<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;



use App\Models\User;

class UserController extends Controller
{
    /**
     * Récupérer les informations de l'utilisateur connecté.
     */
    public function getUser(Request $request)
    {
        return response()->json([
            'user' => Auth::user()
        ], 200);
    }

    public function getUserSauf(Request $request)
    {
        $user = Auth::user();
    
        if (!$user) {
            return response()->json([
                'message' => 'Utilisateur non authentifié.'
            ], 401);
        }
    
        return response()->json([
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'bio' => $user->bio ?? '',
            'profile_image' => $user->profile_image 
                ? asset('storage/' . $user->profile_image) // URL complète de l'image
                : null, // Null si l'utilisateur n'a pas d'image de profil
        ], 200);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();
    
        // Log des données reçues
        Log::info('Données reçues pour mise à jour du profil : ', $request->all());
    
        // Validation des champs optionnels
        $validated = $request->validate([
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'bio' => 'nullable|string|max:1000',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
    
        Log::info('Données validées : ', $validated);
    
        // Mise à jour conditionnelle des champs fournis
        if (!empty($validated['first_name'])) {
            $user->first_name = $validated['first_name'];
            Log::info('Prénom modifié : ' . $validated['first_name']);
        }
    
        if (!empty($validated['last_name'])) {
            $user->last_name = $validated['last_name'];
            Log::info('Nom modifié : ' . $validated['last_name']);
        }
    
        if (!empty($validated['bio'])) {
            $user->bio = $validated['bio'];
            Log::info('Bio modifiée : ' . $validated['bio']);
        }
    
        // Gestion de l'image de profil
        if ($request->hasFile('profile_image')) {
            $file = $request->file('profile_image');
    
            if ($file->isValid()) {
                Log::info('Fichier reçu : ' . $file->getClientOriginalName());
    
                // Suppression de l'ancienne image si elle existe
                if ($user->profile_image) {
                    $oldImagePath = str_replace(asset('storage/'), '', $user->profile_image);
                    Storage::disk('public')->delete($oldImagePath);
                }
    
                // Sauvegarde de la nouvelle image
                $imagePath = $file->store('profile_images', 'public');
    
                // Générer l'URL complète
                $user->profile_image = asset('storage/' . $imagePath);
    
                Log::info('Nouvelle image enregistrée : ' . $user->profile_image);
            } else {
                Log::error('Le fichier n\'est pas valide.');
                return response()->json(['error' => 'Image invalide.'], 422);
            }
        }
    
        // Sauvegarde des modifications
        $user->save();
    
        // Log après la sauvegarde
        Log::info('Utilisateur après mise à jour : ', $user->toArray());
    
        return response()->json([
            'message' => 'Profil mis à jour avec succès.',
            'user' => $user,
        ]);
    }
    

public function updatePassword(Request $request)
{
    // Récupérer l'utilisateur connecté
    $user = $request->user();

    // Log: Afficher les détails de la requête
    \Log::info('Requête de mise à jour du mot de passe reçue');
    \Log::info('Détails de la requête:', [
        'current_password' => $request->current_password,
        'new_password' => $request->new_password,
        'new_password_confirmation' => $request->new_password_confirmation,
    ]);
    
    // Validation des champs
    $validator = Validator::make($request->all(), [
        'current_password' => 'required',
        'new_password' => 'required|min:6|confirmed',
    ]);

    // Log: Afficher les erreurs de validation si présentes
    if ($validator->fails()) {
        \Log::error('Erreur de validation:', $validator->errors()->toArray());
        return response()->json(['error' => $validator->errors()], 422);
    }

    // Vérifier que l'ancien mot de passe est correct
    if (!Hash::check($request->current_password, $user->password)) {
        \Log::error('L\'ancien mot de passe est incorrect pour l\'utilisateur:', ['user_id' => $user->id]);
        return response()->json(['error' => 'L\'ancien mot de passe est incorrect'], 401);
    }

    // Log: Afficher avant la mise à jour du mot de passe
    \Log::info('Ancien mot de passe vérifié, mise à jour en cours pour l\'utilisateur:', ['user_id' => $user->id]);

    // Mettre à jour le mot de passe
    $user->password = Hash::make($request->new_password);
    $user->save();

    // Log: Confirmation de la mise à jour
    \Log::info('Mot de passe mis à jour avec succès pour l\'utilisateur:', ['user_id' => $user->id]);

    return response()->json(['message' => 'Mot de passe mis à jour avec succès']);
}

public function getEmail(Request $request)
    {
        // Vérifier si l'utilisateur est authentifié
        if (Auth::check()) {
            return response()->json([
                'email' => Auth::user()->email
            ], 200);
        }

        return response()->json([
            'message' => 'Utilisateur non authentifié'
        ], 401);
    }

    public function deleteAccount(Request $request)
{
    $user = Auth::user(); // Récupère l'utilisateur actuellement authentifié

    // Log pour vérifier l'utilisateur
    Log::info('Utilisateur à supprimer : ' . $user->email);

    // Récupérer le paramètre de confirmation depuis l'URL
    $confirmation = $request->query('confirmation'); // Utilisation de query pour récupérer le paramètre

    // Validation du champ de confirmation
    $validator = Validator::make(['confirmation' => $confirmation], [
        'confirmation' => 'required|string|in:SUPPRIMER',
    ]);

    if ($validator->fails()) {
        // Log de l'échec de la validation
        Log::info('Validation échouée : ' . json_encode($validator->errors()));
        return response()->json([
            'error' => 'La confirmation est incorrecte.',
        ], 400);
    }

    // Log avant suppression
    Log::info('Suppression du compte de l\'utilisateur : ' . $user->email);
    $user->delete();

    // Log après suppression
    Log::info('Compte de l\'utilisateur supprimé : ' . $user->email);

    return response()->json([
        'message' => 'Votre compte a été supprimé avec succès.',
    ]);
}

}