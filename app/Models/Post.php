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
        'title',     // Texte du post
        'media',   // Image ou vidéo
        'media_type',  // "image" ou "video"
        'visibility',  // public, privé, amis seulement, etc.
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
}
