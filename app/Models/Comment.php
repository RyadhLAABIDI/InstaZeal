<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'post_id',
        'content',
        'parent_id', // Gestion des réponses
    ];

    /**
     * Un commentaire appartient à un utilisateur.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Un commentaire appartient à un post.
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Un commentaire peut avoir plusieurs réponses.
     */
    public function replies()
    {
        return $this->hasMany(Comment::class, 'parent_id');
    }

    /**
     * Un commentaire peut être une réponse à un autre commentaire.
     */
    public function parent()
    {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    /**
     * Un commentaire peut avoir plusieurs likes.
     */
    public function likes()
    {
        return $this->hasMany(CommentLike::class)->with('user');
    }

    // Accesseur pour obtenir le nombre de likes d'un commentaire
    public function getLikesCountAttribute()
    {
        return $this->likes()->count();
    }
}
