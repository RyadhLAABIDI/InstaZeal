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
}