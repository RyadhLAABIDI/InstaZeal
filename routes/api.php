<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AccountRecoveryController;
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

// Route protégée pour récupérer l'utilisateur connecté
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return response()->json($request->user());
});
