<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AccountRecoveryController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\ShareController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\FollowController;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| Authentication Routes
|--------------------------------------------------------------------------
*/

// Route pour l'inscription
Route::post('register', [AuthController::class, 'register']);

// Route pour la connexion
Route::post('login', [AuthController::class, 'login'])->name('login');

// Route pour la déconnexion
Route::middleware('auth:sanctum')->post('logout', [AuthController::class, 'logout']);

// Récupérer l'utilisateur connecté
Route::get('/getuser', [UserController::class, 'getUser']); 

// Récupérer l'utilisateur connecté (name , last name , Bio)
Route::get('/getusersauf', [UserController::class, 'getUserSauf']); 

// Modifier le profil
Route::middleware('auth:sanctum')->post('/user/update', [UserController::class, 'updateProfile']);

// Modifier le mot de passe
Route::middleware('auth:sanctum')->post('/update-password', [UserController::class, 'updatePassword']);

// Afficher l'email
Route::middleware('auth:sanctum')->get('/getemail', [UserController::class, 'getEmail']);

// Supprimer le compte
Route::middleware('auth:sanctum')->delete('/delete-account', [UserController::class, 'deleteAccount']);

// Route pour récupération de compte
Route::post('recover/send-email', [AccountRecoveryController::class, 'sendRecoveryEmail']);
Route::get('recover/{token}', [AccountRecoveryController::class, 'showRecoveryForm']);
Route::post('recover', [AccountRecoveryController::class, 'recoverAccount']);

/*
|--------------------------------------------------------------------------
| Root Route
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return "Serveur Laravel fonctionne !";
});


///////////////////////
/*
|--------------------------------------------------------------------------
| Post Routes
|--------------------------------------------------------------------------
*/

// Routes protégées par authentification pour la gestion des posts
Route::middleware('auth:sanctum')->group(function () {

    // Récupérer tous les posts de l'utilisateur connecté
    Route::get('posts/user', [PostController::class, 'getUserPosts'])->middleware('auth:sanctum');
    // Commentaire : Cette route permet à un utilisateur connecté de récupérer tous ses posts.

    // Créer un nouveau post
    Route::post('posts', [PostController::class, 'createPost'])->middleware('auth:sanctum');
    // Commentaire : Cette route permet à un utilisateur connecté de créer un nouveau post.

    // Récupérer un post spécifique par son ID
    Route::get('posts/{postId}', [PostController::class, 'getPost']);
    // Commentaire : Cette route permet de récupérer un post spécifique en utilisant son ID.

    // Mettre à jour un post spécifique
    Route::put('posts/{postId}', [PostController::class, 'updatePost']);
    // Commentaire : Cette route permet de mettre à jour un post spécifique en utilisant son ID. L'utilisateur doit être l'auteur du post.

    // Supprimer un post
    Route::delete('posts/{postId}', [PostController::class, 'deletePost']);
    // Commentaire : Cette route permet de supprimer un post spécifique. L'utilisateur doit être l'auteur du post.

    // Récupérer tous les posts visibles selon la visibilité définie (public, private, friends, close_friends)
    Route::get('posts', [PostController::class, 'index']);
    // Commentaire : Cette route permet de récupérer tous les posts visibles selon la visibilité définie et les relations d'abonnement de l'utilisateur connecté.
});


///////////////////////
/*
|--------------------------------------------------------------------------
| Follow Routes
|--------------------------------------------------------------------------
*/

// Routes protégées par authentification pour gérer les abonnements
Route::middleware('auth:sanctum')->group(function () {

    // Suivre un utilisateur
    Route::post('follow/{followedId}', [FollowController::class, 'followUser']);
    // Commentaire : Permet à un utilisateur de suivre un autre utilisateur avec option de relation.

    // Accepter ou refuser une demande de suivi
    Route::put('follow/status/{followId}', [FollowController::class, 'updateFollowStatus']);
    // Commentaire : Permet d'accepter ou de refuser une demande de suivi par l'utilisateur suivi.

    // Modifier la relation entre un utilisateur et un autre (ami ou ami proche)
    Route::put('follow/relationship/{followId}', [FollowController::class, 'updateRelationship']);
    // Commentaire : Permet de modifier la relation (ami, ami proche) après acceptation de l'abonnement.

    // Annuler un abonnement
    Route::delete('follow/{followedId}', [FollowController::class, 'unfollowUser']);
    // Commentaire : Permet de se désabonner d'un utilisateur.

    // Récupérer les utilisateurs suivis
    Route::get('followed-users', [FollowController::class, 'getFollowedUsers']);
    // Commentaire : Permet de récupérer tous les utilisateurs suivis par l'utilisateur connecté.

    // Récupérer les abonnés
    Route::get('followers', [FollowController::class, 'getFollowers']);
    // Commentaire : Permet de récupérer tous les abonnés de l'utilisateur connecté.
});

/*
|--------------------------------------------------------------------------
| Like Routes
|--------------------------------------------------------------------------
*/

// Routes protégées par authentification pour la gestion des likes
Route::middleware('auth:sanctum')->group(function () {
    // Récupérer la liste des utilisateurs ayant liké un post spécifique
    Route::get('post/{postId}/likes', [LikeController::class, 'showLikesForPost']);
    
    // Récupérer la liste des posts likés par un utilisateur spécifique
    Route::get('user/{userId}/likes', [LikeController::class, 'showLikesForUser']);
    
    // Liker un post spécifique
    Route::post('post/{postId}/like', [LikeController::class, 'likePost']);
    
    // Retirer un like sur un post spécifique
    Route::delete('post/{postId}/unlike', [LikeController::class, 'unlikePost']);
    
    // Vérifier si un utilisateur a liké un post spécifique
    Route::get('post/{postId}/has-liked', [LikeController::class, 'hasLikedPost']);
    
    // Récupérer le nombre total de likes d'un post spécifique
    Route::get('post/{postId}/like-count', [LikeController::class, 'getLikeCount']);
});

/*
|--------------------------------------------------------------------------
| Share Routes
|--------------------------------------------------------------------------
*/

// Routes protégées par authentification pour le partage des posts
Route::middleware('auth:sanctum')->group(function () {
    // Partager un post sur Messenger
    Route::get('post/{postId}/share/messenger', [ShareController::class, 'shareOnMessenger']);
    
    // Partager un post sur WhatsApp
    Route::get('post/{postId}/share/whatsapp', [ShareController::class, 'shareOnWhatsApp']);
    
    // Partager un post sur Facebook
    Route::get('post/{postId}/share/facebook', [ShareController::class, 'shareOnFacebook']);
    
    // Partager un post sur Twitter
    Route::get('post/{postId}/share/twitter', [ShareController::class, 'shareOnTwitter']);
    
    // Partager un post par email
    Route::post('post/{postId}/share/email', [ShareController::class, 'shareByEmail']);
    
    // Copier le lien du post pour le partager ailleurs
    Route::get('post/{postId}/share/copy', [ShareController::class, 'copyLink']);
    
    // Partager un post directement dans l'application avec les abonnés
    Route::get('post/{postId}/share/inapp', [ShareController::class, 'shareInApp']);
});

/*
|--------------------------------------------------------------------------
| Comment Routes
|--------------------------------------------------------------------------
*/

// Routes protégées par authentification pour la gestion des commentaires
Route::middleware('auth:sanctum')->group(function () {
    // Récupérer les commentaires d'un post
    Route::get('posts/{postId}/comments', [CommentController::class, 'getComments']);
    
    // Ajouter un commentaire à un post
    Route::post('posts/{postId}/comments', [CommentController::class, 'addComment']);
    
    // Répondre à un commentaire
    Route::post('comments/{commentId}/reply', [CommentController::class, 'replyToComment']);
    
    // Supprimer un commentaire
    Route::delete('comments/{commentId}', [CommentController::class, 'deleteComment']);
    
    // Liker un commentaire
    Route::post('comments/{commentId}/like', [CommentController::class, 'likeComment']);
    
    // Annuler un like sur un commentaire
    Route::delete('comments/{commentId}/unlike', [CommentController::class, 'unlikeComment']);
    
    // Vérifier si un utilisateur a liké un commentaire
    Route::get('comments/{commentId}/has-liked', [CommentController::class, 'hasLikedComment']);
    
    // Récupérer le nombre de likes d'un commentaire
    Route::get('comments/{commentId}/like-count', [CommentController::class, 'getLikeCount']);
});

/*
|--------------------------------------------------------------------------
| User Route
|--------------------------------------------------------------------------
*/

// Route protégée pour récupérer l'utilisateur connecté
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return response()->json($request->user());
});
