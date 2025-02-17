<?php

namespace App\Http\Controllers;

use App\Models\Follow;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        // Vérification si l'utilisateur essaie de se suivre lui-même
        if (Auth::id() == $followedId) {
            return response()->json(['message' => 'Vous ne pouvez pas vous suivre vous-même.'], 400);
        }

        // Vérifier si la relation existe déjà
        $existingFollow = Follow::where('follower_id', Auth::id())
            ->where('followed_id', $followedId)
            ->first();

        if ($existingFollow) {
            return response()->json(['message' => 'Vous suivez déjà cet utilisateur.'], 400);
        }

        // Créer un nouvel abonnement
        $follow = Follow::create([
            'follower_id' => Auth::id(),
            'followed_id' => $followedId,
            'status' => 'pending', // Statut par défaut
            'relationship' => $request->relationship,
        ]);

        return response()->json(['message' => 'Demande d\'abonnement envoyée.', 'follow' => $follow]);
    }

    /**
     * Accepter ou refuser une demande de suivi.
     */
    public function updateFollowStatus(Request $request, $followId)
    {
        // Validation de la requête
        $request->validate([
            'status' => 'required|in:accepted,rejected',
        ]);

        // Trouver l'abonnement
        $follow = Follow::findOrFail($followId);

        // Vérification si l'utilisateur est celui qui a reçu la demande
        if ($follow->followed_id !== Auth::id()) {
            return response()->json(['message' => 'Action non autorisée.'], 403);
        }

        // Mettre à jour le statut de l'abonnement
        $follow->status = $request->status;
        $follow->save();

        return response()->json(['message' => 'Demande d\'abonnement ' . $request->status . '.', 'follow' => $follow]);
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
}
