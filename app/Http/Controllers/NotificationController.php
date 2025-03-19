<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Follow;
use Illuminate\Support\Facades\Auth;
use App\Jobs\DeleteFollowRequest;
use Carbon\Carbon;

class NotificationController extends Controller
{
    public function getFollowRequests()
{
    $userId = Auth::id();  // Récupère l'ID de l'utilisateur authentifié

    // Filtre les demandes où l'utilisateur est la personne suivie (followed_id)
    $followRequests = Follow::where('followed_id', $userId)
        ->with(['follower:id,first_name,last_name,profile_image'])
        ->orderBy('created_at', 'desc')
        ->get();

    // Retourne les demandes filtrées dans une réponse JSON
    return response()->json($followRequests);
}


    public function updateFollowStatus(Request $request, $followId)
    {
        $request->validate([
            'status' => 'required|in:accepted,rejected',
        ]);

        $follow = Follow::findOrFail($followId);

        if ($follow->followed_id !== Auth::id()) {
            return response()->json(['message' => 'Action non autorisée.'], 403);
        }

        // NE PLUS vérifier le statut précédent
        $follow->status = $request->status;
        $follow->save();

        DeleteFollowRequest::dispatch($follow->id)->delay(now()->addDays(20));

        return response()->json([
            'message' => 'Demande d\'abonnement ' . $request->status . '.',
            'follow' => $follow
        ]);
    }
}