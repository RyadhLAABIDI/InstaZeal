<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Share;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class ShareController extends Controller
{
    /**
     * Partager un post sur Messenger.
     */
    public function shareOnMessenger($postId)
    {
        $post = Post::findOrFail($postId);
        $link = route('post.show', ['post' => $post->id]);
        
        // Générer l'URL de partage sur Messenger
        $messengerUrl = "fb-messenger://share?link=" . urlencode($link);
        
        // Rediriger l'utilisateur vers l'interface de Messenger
        return redirect($messengerUrl);
    }

    /**
     * Partager un post sur WhatsApp.
     */
    public function shareOnWhatsApp($postId)
    {
        $post = Post::findOrFail($postId);
        $link = route('post.show', ['post' => $post->id]);

        // Générer l'URL de partage sur WhatsApp
        $whatsAppUrl = "https://wa.me/?text=" . urlencode($link);

        // Rediriger l'utilisateur vers WhatsApp
        return redirect($whatsAppUrl);
    }

    /**
     * Partager un post sur Facebook.
     */
    public function shareOnFacebook($postId)
    {
        $post = Post::findOrFail($postId);
        $link = route('post.show', ['post' => $post->id]);

        // Utiliser l'URL de partage de Facebook
        $facebookUrl = "https://www.facebook.com/sharer/sharer.php?u=" . urlencode($link);

        // Rediriger l'utilisateur vers Facebook pour partager
        return redirect($facebookUrl);
    }

    /**
     * Partager un post sur Twitter.
     */
    public function shareOnTwitter($postId)
    {
        $post = Post::findOrFail($postId);
        $link = route('post.show', ['post' => $post->id]);

        // Générer l'URL de partage sur Twitter
        $twitterUrl = "https://twitter.com/intent/tweet?url=" . urlencode($link);

        // Rediriger l'utilisateur vers l'interface Twitter
        return redirect($twitterUrl);
    }

    /**
     * Partager un post par email.
     */
    public function shareByEmail($postId, Request $request)
    {
        $post = Post::findOrFail($postId);
        $email = $request->input('email');
        $link = route('post.show', ['post' => $post->id]);

        // Envoyer l'email
        Mail::send('emails.share', ['link' => $link], function ($message) use ($email) {
            $message->to($email)
                    ->subject('Check this Post!');
        });

        return response()->json([
            'message' => 'Post envoyé par email.',
        ], 200);
    }

    /**
     * Partager un post dans l'application avec la liste des followers.
     */
    public function shareInApp($postId, Request $request)
    {
        $post = Post::findOrFail($postId);
        $user = Auth::user();
        $link = route('post.show', ['post' => $post->id]);

        // Récupérer la liste des followers de l'utilisateur
        $followers = User::whereIn('id', $user->followers()->pluck('follower_id'))->get();

        // Afficher la liste des followers et permettre de sélectionner un destinataire
        return view('share.inApp', compact('followers', 'link'));
    }

    /**
     * Copier le lien du post.
     */
    public function copyLink($postId)
    {
        $post = Post::findOrFail($postId);
        $link = route('post.show', ['post' => $post->id]);

        // Copier dans le presse-papiers (vous pouvez utiliser une bibliothèque JavaScript pour ce faire côté client)
        return response()->json([
            'message' => 'Le lien a été copié.',
            'link' => $link,
        ], 200);
    }
}
