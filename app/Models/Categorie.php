<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categorie extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    /**
     * Relation avec les utilisateurs qui ont choisi cette catégorie.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'categorie_user');
    }
}