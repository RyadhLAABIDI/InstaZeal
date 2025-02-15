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
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'date_of_birth' => 'date',
    ];

    protected $dates = ['deleted_at'];

    /**
     * Un utilisateur peut avoir plusieurs posts.
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
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
}
