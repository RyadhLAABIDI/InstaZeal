<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;


class PostController extends Controller
{
    public function getUserPosts()
    {
        try {
            $user = Auth::user();
            if (!$user) {
                return response()->json(['error' => 'Non authentifié'], 401);
            }

            $posts = Post::where('user_id', $user->id)
                ->with(['user:id,first_name,last_name,profile_image'])
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json($posts);

        } catch (\Exception $e) {
            Log::error('Erreur récupération posts : ' . $e->getMessage());
            return response()->json(['error' => 'Erreur interne'], 500);
        }
    }

    public function createPost(Request $request)
    {
        $request->validate([
            'title' => 'nullable|string|max:2200',
            'media' => 'required|file|mimes:jpg,jpeg,png,mp4,mov|max:51200',
            'media_type' => 'required|in:image,video',
            'visibility' => 'required|in:public,private,friends,close_friends'
        ]);

        try {
            $file = $request->file('media');
            $filename = time() . '_' . Str::random(10) . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('posts', $filename, 'public');

            $post = Auth::user()->posts()->create([
                'title' => $request->title,
                'media' => $path,
                'media_type' => $request->media_type,
                'visibility' => $request->visibility
            ]);

            return response()->json([
                'message' => 'Post créé avec succès',
                'post' => $post->load('user:id,first_name,last_name,profile_image')
            ], 201);

        } catch (ValidationException $e) {
            return response()->json(['errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Erreur création post : ' . $e->getMessage());
            return response()->json(['message' => 'Erreur interne du serveur'], 500);
        }
    }

    public function getPost($postId)
    {
        try {
            $post = Post::with([
                'user:id,first_name,last_name,profile_image',
                'comments.user:id,first_name,last_name',
                'likes.user:id,first_name,last_name',
                'shares.user:id,first_name,last_name'
            ])->findOrFail($postId);

            return response()->json($post);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Post non trouvé'], 404);
        }
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
