<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Follow extends Model
{
    use HasFactory;

    protected $fillable = [
        'follower_id',
        'followed_id',
        'status',
        'relationship', // Ajout du champ relationship
    ];

    /**
     * Un abonnement appartient à un utilisateur (celui qui suit).
     */
    public function follower()
    {
        return $this->belongsTo(User::class, 'follower_id');
    }

    /**
     * Un abonnement concerne un utilisateur suivi.
     */
    public function followed()
    {
        return $this->belongsTo(User::class, 'followed_id');
    }

    /**
     * Vérifie si la relation est un ami proche.
     */
    public function isCloseFriend()
    {
        return $this->relationship === 'close_friend';
    }
}
