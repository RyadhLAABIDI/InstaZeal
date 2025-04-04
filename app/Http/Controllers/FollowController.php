<?php

namespace App\Http\Controllers;

use App\Models\Follow;
use App\Models\User;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\NotificationController;

class FollowController extends Controller
{
    public function followUser(Request $request, $followedId)
    {
        $request->validate([
            'relationship' => 'nullable|in:friend,close_friend',
        ]);
    
        $user = Auth::user();
        Log::info('Utilisateur authentifié: ' . $user->id);
    
        $followedUser = User::findOrFail($followedId);
        Log::info('Utilisateur suivi trouvé: ' . $followedUser->id);
    
        if ($user->id == $followedId) {
            Log::warning('L\'utilisateur essaie de se suivre lui-même.');
            return response()->json(['message' => 'Vous ne pouvez pas vous suivre vous-même.'], 400);
        }
    
        $existingFollow = Follow::where('follower_id', $user->id)
            ->where('followed_id', $followedId)
            ->first();
    
        if ($existingFollow) {
            Log::warning('L\'utilisateur suit déjà cette personne.');
            return response()->json(['message' => 'Vous suivez déjà cet utilisateur.'], 400);
        }
    
        if ($followedUser->is_private) {
            Log::info('Le compte de l\'utilisateur suivi est privé.');
    
            $follow = Follow::create([
                'follower_id' => $user->id,
                'followed_id' => $followedId,
                'status' => 'pending',
                'relationship' => $request->relationship,
            ]);
    
            Log::info('Demande d\'abonnement envoyée pour le suivi: ' . $followedId);
    
            $isMutual = Follow::isMutualFollow($user->id, $followedId);
    
            return response()->json([
                'message' => 'Demande d\'abonnement envoyée.',
                'follow' => $follow,
                'is_private' => true,
                'is_mutual' => $isMutual
            ]);
        } else {
            Log::info('Le compte de l\'utilisateur suivi est public.');
    
            $follow = Follow::create([
                'follower_id' => $user->id,
                'followed_id' => $followedId,
                'status' => 'accepted',
                'relationship' => $request->relationship,
            ]);
    
            Log::info('Abonnement accepté automatiquement pour le suivi: ' . $followedId);
    
            $isMutual = Follow::isMutualFollow($user->id, $followedId);
    
            return response()->json([
                'message' => 'Abonnement accepté automatiquement.',
                'follow' => $follow,
                'is_private' => false,
                'is_mutual' => $isMutual
            ]);
        }
    }

    public function unfollowUser($followedId)
    {
        $follow = Follow::where('follower_id', Auth::id())
                        ->where('followed_id', $followedId)
                        ->first();

        if (!$follow) {
            return response()->json(['message' => 'Vous ne suivez pas cet utilisateur.'], 400);
        }

        $follow->delete();

        Notification::where('user_id', Auth::id())
                    ->where('message', 'like', "%{$followedId}%")
                    ->delete();

        Notification::where('user_id', $followedId)
                    ->where('message', 'like', "%".Auth::id()."%")
                    ->delete();

        return response()->json(['message' => 'Vous avez arrêté de suivre cet utilisateur et les notifications ont été supprimées.']);
    }

    public function getFollowedUsers()
    {
        $followedUsers = Follow::where('follower_id', Auth::id())
            ->where('status', 'accepted')
            ->with('followed')
            ->get();

        return response()->json($followedUsers);
    }

    public function getFollowers()
    {
        $followers = Follow::where('followed_id', Auth::id())
            ->where('status', 'accepted')
            ->with('follower')
            ->get();

        return response()->json($followers);
    }

    public function getFollowStatus(User $user)
    {
        try {
            $authenticatedUser = auth()->user();
            
            $followRelationship = Follow::where([
                ['follower_id', $authenticatedUser->id],
                ['followed_id', $user->id]
            ])->first();

            $status = 'not_following';
            
            if ($followRelationship) {
                $status = $followRelationship->status;
                
                if ($user->is_private && $status === 'pending') {
                    $status = 'requested';
                }
            }

            $isMutual = Follow::isMutualFollow($authenticatedUser->id, $user->id);

            return response()->json([
                'status' => $status,
                'is_private' => $user->is_private,
                'is_mutual' => $isMutual
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erreur lors de la vérification du statut',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    public function acceptFollowRequest(Request $request, $followId)
    {
        try {
            $user = Auth::user();
            $followRequest = Follow::where('id', $followId)
                ->where('followed_id', $user->id)
                ->where('status', 'pending')
                ->first();

            if (!$followRequest) {
                return response()->json(['message' => 'Demande non trouvée'], 404);
            }

            $followRequest->update(['status' => 'accepted']);

            $isMutual = Follow::isMutualFollow($user->id, $followRequest->follower_id);

            return response()->json([
                'message' => 'Demande acceptée',
                'user_id' => $followRequest->follower_id,
                'follow_back_available' => true,
                'is_mutual' => $isMutual
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Erreur serveur'], 500);
        }
    }

    public function followBack(Request $request)
    {
        try {
            $user = Auth::user();
            $followedId = $request->input('followed_id');
    
            $targetUser = User::findOrFail($followedId);
    
            $existingFollow = Follow::where('follower_id', $user->id)
                ->where('followed_id', $followedId)
                ->first();
    
            if ($existingFollow) {
                return response()->json([
                    'message' => 'Demande déjà existante',
                    'request_status' => $existingFollow->status,
                    'follow_id' => $existingFollow->id,
                ], 409);
            }
    
            $status = $targetUser->is_private ? 'pending' : 'accepted';
    
            $follow = Follow::create([
                'follower_id' => $user->id,
                'followed_id' => $followedId,
                'status' => $status,
            ]);
    
            $isMutual = Follow::isMutualFollow($user->id, $followedId);
    
            return response()->json([
                'message' => $status === 'pending' 
                    ? 'Demande envoyée' 
                    : 'Abonnement réussi',
                'request_status' => $status,
                'is_private' => $targetUser->is_private,
                'is_mutual' => $isMutual,
                'follow_id' => $follow->id,
            ], 200);
        } catch (\Exception $e) {
            \Log::error("Erreur dans followBack : " . $e->getMessage());
            return response()->json(['message' => 'Erreur serveur'], 500);
        }
    }
    
    public function getPendingFollows(Request $request)
    {
        $user = $request->user();
        $pendingFollows = $user->following()->where('status', 'pending')->get();
        return response()->json($pendingFollows);
    }

    public function getFollowing(Request $request)
    {
        $user = $request->user();
        $following = $user->following()->where('status', 'accepted')->get();
        return response()->json($following);
    }

    public function getOutgoingFollowRequests(Request $request)
    {
        $user = $request->user();
        
        $outgoingFollowRequests = $user->pendingFollowing()
            ->with(['followed' => function($query) {
                $query->select('id', 'first_name', 'last_name', 'profile_image');
            }])
            ->get();
    
        return response()->json($outgoingFollowRequests);
    }

    public function isMutualFollow($userId)
    {
        $authUserId = Auth::id();
        $isMutual = Follow::isMutualFollow($authUserId, $userId);
        return response()->json(['is_mutual' => $isMutual]);
    }

    public function getFollowerss(Request $request)
    {
        try {
            $user = Auth::user();

            $followers = $user->followers()
                ->with(['follower' => function ($query) {
                    $query->select('id', 'first_name', 'last_name', 'profile_image', 'is_private');
                }])
                ->get()
                ->map(function ($follow) {
                    return [
                        'id' => $follow->follower->id,
                        'first_name' => $follow->follower->first_name,
                        'last_name' => $follow->follower->last_name,
                        'profile_image' => $follow->follower->profile_image,
                        'is_private' => $follow->follower->is_private,
                    ];
                });

            return response()->json([
                'success' => true,
                'followers' => $followers,
                'count' => $followers->count(),
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des followers: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des abonnés',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getFollowingg(Request $request)
    {
        try {
            $user = Auth::user();

            $following = $user->following()
                ->with(['followed' => function ($query) {
                    $query->select('id', 'first_name', 'last_name', 'profile_image', 'is_private');
                }])
                ->get()
                ->map(function ($follow) {
                    return [
                        'id' => $follow->followed->id,
                        'first_name' => $follow->followed->first_name,
                        'last_name' => $follow->followed->last_name,
                        'profile_image' => $follow->followed->profile_image,
                        'is_private' => $follow->followed->is_private,
                    ];
                });

            return response()->json([
                'success' => true,
                'following' => $following,
                'count' => $following->count(),
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des following: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération des utilisateurs suivis',
                'error' => $e->getMessage(),
            ], 500);
        }
    } 

    public function searchFollowers(Request $request)
    {
        $request->validate([
            'query' => 'required|string|max:255'
        ]);

        $user = auth()->user();
        
        $searchTerms = explode(' ', $request->query);
        
        $followers = $user->followers()
            ->where(function ($query) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $query->where(function ($q) use ($term) {
                        $q->whereRaw('LOWER(first_name) LIKE ?', ['%' . strtolower($term) . '%'])
                          ->orWhereRaw('LOWER(last_name) LIKE ?', ['%' . strtolower($term) . '%']);
                    });
                }
            })
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name', 'profile_image']);

        return response()->json($followers);
    }

    public function searchFollowing(Request $request)
    {
        $request->validate([
            'query' => 'required|string|max:255'
        ]);

        $user = auth()->user();
        
        $searchTerms = explode(' ', $request->query);
        
        $following = $user->following()
            ->where(function ($query) use ($searchTerms) {
                foreach ($searchTerms as $term) {
                    $query->where(function ($q) use ($term) {
                        $q->whereRaw('LOWER(first_name) LIKE ?', ['%' . strtolower($term) . '%'])
                          ->orWhereRaw('LOWER(last_name) LIKE ?', ['%' . strtolower($term) . '%']);
                    });
                }
            })
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->get(['id', 'first_name', 'last_name', 'profile_image']);

        return response()->json($following);
    }

    public function rejectFollowRequest($followId)
    {
        try {
            $follow = Follow::find($followId);

            if (!$follow) {
                return response()->json([
                    'success' => false,
                    'message' => 'Demande de suivi non trouvée',
                ], 404);
            }

            if ($follow->followed_id != Auth::id() && $follow->follower_id != Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Action non autorisée',
                ], 403);
            }

            $follow->delete();

            return response()->json([
                'success' => true,
                'message' => 'Demande annulée avec succès',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de l\'annulation: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Vérifie si un utilisateur spécifique suit l'utilisateur authentifié.
     *
     * @param  int  $userId
     * @return \Illuminate\Http\JsonResponse
     */
    public function isFollowedBy($userId)
    {
        // Récupérer l'utilisateur authentifié
        $authUser = Auth::user();
        if (!$authUser) {
            return response()->json(['error' => 'Non authentifié'], 401);
        }

        // Vérifier si l'utilisateur $userId suit l'utilisateur authentifié
        $isFollowed = $authUser->followers()->where('follower_id', $userId)->exists();

        return response()->json(['is_followed' => $isFollowed]);
    }

}