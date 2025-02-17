<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    /**
     * Récupérer tous les posts de l'utilisateur connecté.
     */
    public function getUserPosts()
    {
        $posts = Post::where('user_id', Auth::id())
            ->with(['user', 'comments', 'comments.user', 'likes', 'shares'])
            ->get();

        return response()->json($posts);
    }

    /**
     * Créer un nouveau post.
     */
    public function createPost(Request $request)
    {
        $request->validate([
            'title' => 'nullable|string',
            'media' => 'nullable|string',
            'media_type' => 'required|in:image,video',
            'visibility' => 'required|in:public,private,friends,close_friends',
        ]);

        $post = Post::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'media' => $request->media,
            'media_type' => $request->media_type,
            'visibility' => $request->visibility,
        ]);

        return response()->json(['message' => 'Post créé avec succès.', 'post' => $post]);
    }

    /**
     * Récupérer un post spécifique par son ID.
     */
    public function getPost($postId)
    {
        $post = Post::with(['user', 'comments', 'comments.user', 'likes', 'shares'])->findOrFail($postId);

        return response()->json($post);
    }

    /**
     * Mettre à jour un post spécifique.
     */
    public function updatePost(Request $request, $postId)
    {
        $post = Post::findOrFail($postId);

        // Vérification si l'utilisateur est l'auteur du post
        if ($post->user_id !== Auth::id()) {
            return response()->json(['message' => 'Action non autorisée.'], 403);
        }

        $request->validate([
            'title' => 'nullable|string',
            'media' => 'nullable|string',
            'media_type' => 'nullable|in:image,video',
            'visibility' => 'nullable|in:public,private,friends,close_friends',
        ]);

        $post->update($request->only('title', 'media', 'media_type', 'visibility'));

        return response()->json(['message' => 'Post mis à jour avec succès.', 'post' => $post]);
    }

    /**
     * Supprimer un post.
     */
    public function deletePost($postId)
    {
        $post = Post::findOrFail($postId);

        // Vérification si l'utilisateur est l'auteur du post
        if ($post->user_id !== Auth::id()) {
            return response()->json(['message' => 'Action non autorisée.'], 403);
        }

        $post->delete();

        return response()->json(['message' => 'Post supprimé avec succès.']);
    }

    /**
     * Récupérer tous les posts visibles selon la visibilité définie.
     */
    public function index(Request $request)
    {
        $posts = Post::where('visibility', 'public')
            ->orWhere(function ($query) use ($request) {
                $query->where('visibility', 'private')
                    ->whereHas('user.followers', function ($query) use ($request) {
                        $query->where('follower_id', Auth::id())
                            ->where('status', 'accepted');
                    });
            })
            ->orWhere(function ($query) use ($request) {
                $query->where('visibility', 'friends')
                    ->whereHas('user.followers', function ($query) use ($request) {
                        $query->where('follower_id', Auth::id())
                            ->where('status', 'accepted');
                    });
            })
            ->orWhere(function ($query) use ($request) {
                $query->where('visibility', 'close_friends')
                    ->whereHas('user.followers', function ($query) use ($request) {
                        $query->where('follower_id', Auth::id())
                            ->where('status', 'accepted')
                            ->where('relationship', 'close_friend');
                    });
            })
            ->get();

        return response()->json(['posts' => $posts]);
    }
}
