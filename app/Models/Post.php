<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'media',
        'media_type',
        'visibility',
    ];

    protected $dates = ['deleted_at'];

    /**
     * Un post appartient à un utilisateur.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Un post peut avoir plusieurs likes.
     */
    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    /**
     * Un post peut avoir plusieurs commentaires.
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Un post peut être partagé plusieurs fois.
     */
    public function shares()
    {
        return $this->hasMany(Share::class);
    }

    /**
     * Vérifie si le post est visible pour l'utilisateur donné.
     *
     * @param User $user
     * @return bool
     */
    public function isVisibleTo(User $user)
    {
        // Si le post est public, il est visible pour tout le monde
        if ($this->visibility == 'public') {
            return true;
        }

        // Si le post est privé, il est visible uniquement pour les abonnés acceptés
        if ($this->visibility == 'private') {
            return $this->user->followers()->where('follower_id', $user->id)->where('status', 'accepted')->exists();
        }

        // Si le post est pour les amis proches, il est visible uniquement pour les amis proches
        if ($this->visibility == 'close_friends') {
            return $this->user->followers()->where('follower_id', $user->id)
                ->where('status', 'accepted')
                ->where('relationship', 'close_friend') // Vérifier si la relation est "ami proche"
                ->exists();
        }

        // Si le post est pour les amis, il est visible uniquement pour les abonnés acceptés
        if ($this->visibility == 'friends') {
            return $this->user->followers()->where('follower_id', $user->id)
                ->where('status', 'accepted')
                ->exists();
        }

        // Par défaut, on considère le post comme visible (cela peut être ajusté selon tes besoins)
        return true;
    }
}
