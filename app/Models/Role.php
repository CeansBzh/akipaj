<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Role extends Model
{
    use HasFactory;

    // TODO Importance des rôles (niveau hiérachique)

    /**
     * Get the users for the role.
     */
    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
