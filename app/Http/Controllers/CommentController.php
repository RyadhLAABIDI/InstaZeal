<?php
namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\CommentLike;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    /**
     * Récupérer les commentaires d'un post.
     */
    public function getComments($postId)
    {
        $comments = Comment::where('post_id', $postId)
            ->whereNull('parent_id')
            ->with(['user', 'replies.user', 'likes', 'likesCount'])
            ->get();

        return response()->json($comments);
    }

    /**
     * Ajouter un commentaire à un post.
     */
    public function addComment(Request $request, $postId)
    {
        $request->validate([
            'content' => 'required|string',
        ]);

        $comment = Comment::create([
            'user_id' => Auth::id(),
            'post_id' => $postId,
            'content' => $request->content,
        ]);

        return response()->json(['message' => 'Commentaire ajouté avec succès.', 'comment' => $comment]);
    }

    /**
     * Répondre à un commentaire.
     */
    public function replyToComment(Request $request, $commentId)
    {
        $request->validate([
            'content' => 'required|string',
        ]);

        $parentComment = Comment::findOrFail($commentId);

        $reply = Comment::create([
            'user_id' => Auth::id(),
            'post_id' => $parentComment->post_id,
            'content' => $request->content,
            'parent_id' => $commentId,
        ]);

        return response()->json(['message' => 'Réponse ajoutée avec succès.', 'reply' => $reply]);
    }

    /**
     * Supprimer un commentaire (et ses réponses).
     */
    public function deleteComment($commentId)
    {
        $comment = Comment::findOrFail($commentId);

        if ($comment->user_id !== Auth::id()) {
            return response()->json(['message' => 'Action non autorisée.'], 403);
        }

        $comment->replies()->delete(); // Suppression des réponses avant de supprimer le commentaire
        $comment->delete();

        return response()->json(['message' => 'Commentaire supprimé avec succès.']);
    }

    /**
     * Liker un commentaire.
     */
    public function likeComment($commentId)
    {
        $like = CommentLike::firstOrCreate([
            'user_id' => Auth::id(),
            'comment_id' => $commentId,
        ]);

        return response()->json(['message' => 'Commentaire liké avec succès.', 'like' => $like]);
    }

    /**
     * Annuler un like sur un commentaire.
     */
    public function unlikeComment($commentId)
    {
        CommentLike::where('user_id', Auth::id())->where('comment_id', $commentId)->delete();

        return response()->json(['message' => 'Like retiré avec succès.']);
    }

    /**
     * Vérifier si un utilisateur a liké un commentaire.
     */
    public function hasLikedComment($commentId)
    {
        $hasLiked = CommentLike::where('user_id', Auth::id())->where('comment_id', $commentId)->exists();

        return response()->json(['has_liked' => $hasLiked]);
    }

    /**
     * Récupérer le nombre de likes d'un commentaire.
     */
    public function getLikeCount($commentId)
    {
        $likeCount = CommentLike::where('comment_id', $commentId)->count();

        return response()->json(['like_count' => $likeCount]);
    }
}
