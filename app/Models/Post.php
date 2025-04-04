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
        'media', // Stocke le chemin relatif (ex: 'posts/fichier.jpg')
        'media_type',
        'visibility',
        'categorie', // Ajout du champ categorie
    ];

    protected $appends = ['media_url']; // Ajoute le champ calculé
    protected $hidden = ['media']; // Cache le chemin relatif dans les réponses JSON
    protected $dates = ['deleted_at'];

    /**
     * Accesseur pour l'URL complet du média
     */
    public function getMediaUrlAttribute()
    {
        return asset('storage/' . $this->media);
    }

    /**
     * Relation avec l'utilisateur
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relation avec les likes
     */
    public function likes()
    {
        return $this->hasMany(Like::class);
    }

    /**
     * Relation avec les commentaires
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * Relation avec les partages
     */
    public function shares()
    {
        return $this->hasMany(Share::class);
    }

    /**
     * Vérifie la visibilité du post
     */
    public function isVisibleTo(User $user)
    {
        if ($this->visibility == 'public') {
            return true;
        }

        if ($this->visibility == 'private') {
            return $this->user->followers()
                ->where('follower_id', $user->id)
                ->where('status', 'accepted')
                ->exists();
        }

        if ($this->visibility == 'close_friends') {
            return $this->user->followers()
                ->where('follower_id', $user->id)
                ->where('status', 'accepted')
                ->where('relationship', 'close_friend')
                ->exists();
        }

        if ($this->visibility == 'friends') {
            return $this->user->followers()
                ->where('follower_id', $user->id)
                ->where('status', 'accepted')
                ->exists();
        }

        return true;
    }
}