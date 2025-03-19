<?php

namespace App\Http\Controllers;

use App\Models\Follow;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;


class FollowController extends Controller
{
   /**
 * Suivre un utilisateur.
 */
public function followUser(Request $request, $followedId)
{
    // Validation de la requête
    $request->validate([
        'relationship' => 'nullable|in:friend,close_friend',
    ]);

    // Récupération de l'utilisateur actuellement authentifié
    $user = Auth::user();
    Log::info('Utilisateur authentifié: ' . $user->id);

    // Recherche de l'utilisateur suivi
    $followedUser = User::findOrFail($followedId);
    Log::info('Utilisateur suivi trouvé: ' . $followedUser->id);

    // Vérification si l'utilisateur essaie de se suivre lui-même
    if ($user->id == $followedId) {
        Log::warning('L\'utilisateur essaie de se suivre lui-même.');
        return response()->json(['message' => 'Vous ne pouvez pas vous suivre vous-même.'], 400);
    }

    // Vérifier si une relation existe déjà
    $existingFollow = Follow::where('follower_id', $user->id)
        ->where('followed_id', $followedId)
        ->first();

    if ($existingFollow) {
        Log::warning('L\'utilisateur suit déjà cette personne.');
        return response()->json(['message' => 'Vous suivez déjà cet utilisateur.'], 400);
    }

    // Vérifier si l'utilisateur suivi a un compte privé
    if ($followedUser->is_private) {
        Log::info('Le compte de l\'utilisateur suivi est privé.');

        // Créer une demande d'abonnement
        $follow = Follow::create([
            'follower_id' => $user->id,
            'followed_id' => $followedId,
            'status' => 'pending', // Demande d'abonnement
            'relationship' => $request->relationship,
        ]);

        Log::info('Demande d\'abonnement envoyée pour le suivi: ' . $followedId);
        return response()->json([
            'message' => 'Demande d\'abonnement envoyée.',
            'follow' => $follow,
            'is_private' => true
        ]);
    } else {
        Log::info('Le compte de l\'utilisateur suivi est public.');

        // Créer un abonnement accepté automatiquement
        $follow = Follow::create([
            'follower_id' => $user->id,
            'followed_id' => $followedId,
            'status' => 'accepted', // Abonnement accepté
            'relationship' => $request->relationship,
        ]);

        Log::info('Abonnement accepté automatiquement pour le suivi: ' . $followedId);
        return response()->json([
            'message' => 'Abonnement accepté automatiquement.',
            'follow' => $follow,
            'is_private' => false
        ]);
    }
}



    /**
     * Modifier la relation entre les abonnés.
     */
    public function updateRelationship(Request $request, $followId)
    {
        // Validation de la requête
        $request->validate([
            'relationship' => 'required|in:friend,close_friend',
        ]);

        // Trouver l'abonnement
        $follow = Follow::findOrFail($followId);

        // Vérification si l'utilisateur est celui qui a fait la demande de suivi
        if ($follow->follower_id !== Auth::id()) {
            return response()->json(['message' => 'Action non autorisée.'], 403);
        }

        // Mettre à jour la relation
        $follow->relationship = $request->relationship;
        $follow->save();

        return response()->json(['message' => 'Relation mise à jour avec succès.', 'follow' => $follow]);
    }

    /**
     * Annuler un abonnement.
     */
    public function unfollowUser($followedId)
    {
        // Trouver l'abonnement existant
        $follow = Follow::where('follower_id', Auth::id())
            ->where('followed_id', $followedId)
            ->first();

        if (!$follow) {
            return response()->json(['message' => 'Vous ne suivez pas cet utilisateur.'], 400);
        }

        // Supprimer l'abonnement
        $follow->delete();

        return response()->json(['message' => 'Vous avez arrêté de suivre cet utilisateur.']);
    }

    /**
     * Récupérer les utilisateurs suivis par l'utilisateur connecté.
     */
    public function getFollowedUsers()
    {
        $followedUsers = Follow::where('follower_id', Auth::id())
            ->where('status', 'accepted')
            ->with('followed')
            ->get();

        return response()->json($followedUsers);
    }

    /**
     * Récupérer les abonnés de l'utilisateur connecté.
     */
    public function getFollowers()
    {
        $followers = Follow::where('followed_id', Auth::id())
            ->where('status', 'accepted')
            ->with('follower')
            ->get();

        return response()->json($followers);
    }


    /**
     * Vérifie si un utilisateur suit un autre utilisateur de manière mutuelle.
     *
     * @param  int  $followedId
     * @return \Illuminate\Http\Response
     */
    public function isMutualFollowing($followedId)
    {
        $userId = Auth::id();  // ID de l'utilisateur authentifié

        // Vérifier si l'utilisateur suit le profil suivi
        $isFollowing = Follow::where('follower_id', $userId)
            ->where('followed_id', $followedId)
            ->where('status', 'accepted')
            ->exists();

        // Vérifier si le profil suivi suit l'utilisateur
        $isFollowedBack = Follow::where('follower_id', $followedId)
            ->where('followed_id', $userId)
            ->where('status', 'accepted')
            ->exists();

        // Vérifier si c'est un suivi mutuel
        if ($isFollowing && $isFollowedBack) {
            return response()->json(['is_mutual_following' => true]);
        } else {
            return response()->json(['is_mutual_following' => false]);
        }
    }
}