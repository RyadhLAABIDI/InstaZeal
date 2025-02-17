<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LikeController extends Controller
{
    /**
     * Affiche les likes d'un post.
     */
    public function showLikesForPost($postId)
    {
        $post = Post::findOrFail($postId);
        return response()->json($post->likes);
    }

    /**
     * Affiche les likes d'un utilisateur.
     */
    public function showLikesForUser($userId)
    {
        $user = User::findOrFail($userId);

        // Récupère tous les likes de cet utilisateur
        $likes = $user->likes;

        return response()->json($likes);
    }

    /**
     * Liker un post.
     */
    public function likePost($postId)
    {
        $like = Like::firstOrCreate([
            'user_id' => Auth::id(),
            'post_id' => $postId,
        ]);

        return response()->json(['message' => 'Post liké avec succès.', 'like' => $like]);
    }

    /**
     * Annuler un like sur un post.
     */
    public function unlikePost($postId)
    {
        Like::where('user_id', Auth::id())
            ->where('post_id', $postId)
            ->delete();

        return response()->json(['message' => 'Like annulé avec succès.']);
    }

    /**
     * Vérifier si un utilisateur a liké un post.
     */
    public function hasLikedPost($postId)
    {
        $hasLiked = Like::where('user_id', Auth::id())
            ->where('post_id', $postId)
            ->exists();

        return response()->json(['has_liked' => $hasLiked]);
    }

    /**
     * Récupérer le nombre de likes d'un post.
     */
    public function getLikeCount($postId)
    {
        $likeCount = Like::where('post_id', $postId)->count();
        return response()->json(['like_count' => $likeCount]);
    }
}