<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CommentLike extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'comment_id',
    ];

    /**
     * Un like appartient à un utilisateur.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Un like appartient à un commentaire.
     */
    public function comment()
    {
        return $this->belongsTo(Comment::class);
    }

    /**
     * Récupérer le post lié au commentaire liké.
     */
    public function post()
    {
        return $this->hasOneThrough(Post::class, Comment::class, 'id', 'id', 'comment_id', 'post_id');
    }
}
