<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'date_of_birth',
        'gender',
        'bio',
        'profile_image',
        'is_private',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'date_of_birth' => 'date',
        'is_private' => 'boolean',
    ];

    protected $dates = ['deleted_at'];

    /**
     * Un utilisateur peut avoir plusieurs posts.
     */
    public function posts()
    {
        return $this->hasMany(Post::class, 'user_id');
    }

    /**
     * Un utilisateur peut aimer plusieurs posts.
     */
    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    /**
     * Un utilisateur peut commenter plusieurs posts.
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Un utilisateur peut partager plusieurs posts.
     */
    public function shares()
    {
        return $this->hasMany(Share::class);
    }

    /**
     * Un utilisateur peut avoir plusieurs abonnés (followers).
     */
    public function followers()
    {
        return $this->hasMany(Follow::class, 'followed_id')->where('status', 'accepted');
    }

    /**
     * Un utilisateur peut avoir plusieurs demandes d’abonnement en attente.
     */
    public function pendingFollowers()
    {
        return $this->hasMany(Follow::class, 'followed_id')->where('status', 'pending');
    }

    /**
     * Un utilisateur peut suivre plusieurs autres utilisateurs.
     */
    public function following()
    {
        return $this->hasMany(Follow::class, 'follower_id')->where('status', 'accepted');
    }

    /**
     * Un utilisateur peut avoir plusieurs amis proches.
     */
    public function closeFriends()
    {
        // Utilisation du scope pour filtrer les amis proches (close_friend)
        return $this->hasMany(Follow::class, 'followed_id')
            ->where('relationship', 'close_friend')  // Filtrage des amis proches
            ->where('status', 'accepted');
    }

    /**
     * Vérifie si un utilisateur est suivi par un autre utilisateur.
     */
    public function isFollowedBy(User $user)
    {
        return $this->followers()->where('follower_id', $user->id)->exists();
    }

    /**
     * Vérifie si un utilisateur a envoyé une demande de suivi.
     */
    public function hasRequestedToFollow(User $user)
    {
        return $this->pendingFollowers()->where('follower_id', $user->id)->exists();
    }

    /**
     * Vérifie si un utilisateur est ami proche.
     */
    public function isCloseFriend(User $user)
    {
        return $this->closeFriends()->where('follower_id', $user->id)->exists();
    }
}
