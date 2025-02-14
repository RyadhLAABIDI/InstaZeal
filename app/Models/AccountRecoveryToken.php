<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccountRecoveryToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 
        'token',
        'expires_at',
    ];

    // Relation avec l'utilisateur
    public function user()
    {
        // Inclure les utilisateurs supprimés (soft delete)
        return $this->belongsTo(User::class)->withTrashed();
    }
}
