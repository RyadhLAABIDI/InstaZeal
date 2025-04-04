<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\AccountRecoveryToken;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\AccountRecoveryMail;

class AccountRecoveryController extends Controller
{
    // Envoi du mail avec le token de récupération
    public function sendRecoveryEmail(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        // Trouver l'utilisateur supprimé (soft delete)
        $user = User::onlyTrashed()->where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['message' => 'Email not found or not deleted.'], 404);
        }

        // Générer un token de récupération
        $token = Str::random(60);

        // Créer un enregistrement dans la table des tokens de récupération
        AccountRecoveryToken::create([
            'user_id' => $user->id,
            'token' => $token,
            'expires_at' => Carbon::now()->addHours(24), // Expiration dans 24h
        ]);

        // Envoyer le token par email
        Mail::to($user->email)->send(new AccountRecoveryMail($token));

        return response()->json(['message' => 'Recovery email sent successfully.']);
    }

    // Affichage du formulaire pour la récupération
    public function showRecoveryForm($token)
    {
        $recoveryToken = AccountRecoveryToken::where('token', $token)
                                              ->where('expires_at', '>', Carbon::now())
                                              ->first();

        if (!$recoveryToken) {
            return response()->json(['message' => 'Invalid or expired token.'], 404);
        }

        return view('auth.recover', ['token' => $token]);
    }

   // Récupérer le compte de l'utilisateur
public function recoverAccount(Request $request)
{
    $request->validate([
        'token' => 'required|exists:account_recovery_tokens,token',
        'password' => 'required|confirmed|min:8',
    ]);

    // Vérifier la validité du token
    $recoveryToken = AccountRecoveryToken::where('token', $request->token)
                                          ->where('expires_at', '>', Carbon::now())
                                          ->first();

    // Debug: Log du token et de son expiration
    \Log::info('Token trouvé:', ['token' => $recoveryToken]);

    if (!$recoveryToken) {
        return response()->json(['message' => 'Invalid or expired token.'], 404);
    }

    // Récupérer l'utilisateur associé au token
    $user = $recoveryToken->user;

    // Debug: Vérification de l'utilisateur
    \Log::info('Utilisateur associé au token:', ['user' => $user]);

    if (!$user) {
        return response()->json(['message' => 'User not found.'], 404);
    }

    // Restaurer l'utilisateur si nécessaire (s'il est soft deleted)
    if ($user->trashed()) {
        $user->restore();
    }

    // Mettre à jour le mot de passe de l'utilisateur
    $user->password = bcrypt($request->password);
    $user->save();

    // Supprimer le token de récupération
    $recoveryToken->delete();

    return response()->json(['message' => 'Account recovered successfully.']);
}
}

