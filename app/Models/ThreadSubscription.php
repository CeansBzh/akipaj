<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ThreadSubscription extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
    ];

    /**
     * Get the user that is subscribed.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the parent subscribeable model.
     */
    public function subscribeable()
    {
        return $this->morphTo();
    }
}
