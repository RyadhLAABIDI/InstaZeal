<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AccountRecoveryController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\ShareController;
use App\Http\Controllers\CommentController;
use Illuminate\Http\Request;

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
//Route::post('/update', [UserController::class, 'updateProfile']); 
Route::middleware('auth:sanctum')->post('/user/update', [UserController::class, 'updateProfile']);

// Modifier Password 
Route::middleware('auth:sanctum')->post('/update-password', [UserController::class, 'updatePassword']);

/// Afficher Email
Route::middleware('auth:sanctum')->get('/getemail', [UserController::class, 'getEmail']);

// Supprimer Compte 
Route::middleware('auth:sanctum')->delete('/delete-account', [UserController::class, 'deleteAccount']);

/// Route pour recuperation compte
Route::post('recover/send-email', [AccountRecoveryController::class, 'sendRecoveryEmail']);
Route::get('recover/{token}', [AccountRecoveryController::class, 'showRecoveryForm']);
Route::post('recover', [AccountRecoveryController::class, 'recoverAccount']);

////////////////////////////
Route::get('/', function () {
    return "Serveur Laravel fonctionne !";
});

///////

Route::middleware('auth:sanctum')->group(function () {
    Route::get('post/{postId}/likes', [LikeController::class, 'showLikesForPost']);
    Route::get('user/{userId}/likes', [LikeController::class, 'showLikesForUser']);
    Route::post('post/{postId}/like', [LikeController::class, 'likePost']);
    Route::delete('post/{postId}/unlike', [LikeController::class, 'unlikePost']);
    Route::get('post/{postId}/has-liked', [LikeController::class, 'hasLikedPost']);
    Route::get('post/{postId}/like-count', [LikeController::class, 'getLikeCount']);
});

////////////////

Route::middleware('auth:sanctum')->group(function () {
    Route::get('post/{postId}/share/messenger', [ShareController::class, 'shareOnMessenger']);
    Route::get('post/{postId}/share/whatsapp', [ShareController::class, 'shareOnWhatsApp']);
    Route::get('post/{postId}/share/facebook', [ShareController::class, 'shareOnFacebook']);
    Route::get('post/{postId}/share/twitter', [ShareController::class, 'shareOnTwitter']);
    Route::post('post/{postId}/share/email', [ShareController::class, 'shareByEmail']);
    Route::get('post/{postId}/share/copy', [ShareController::class, 'copyLink']);
    Route::get('post/{postId}/share/inapp', [ShareController::class, 'shareInApp']);
});

//////////////////


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




// Route protégée pour récupérer l'utilisateur connecté
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return response()->json($request->user());
});
