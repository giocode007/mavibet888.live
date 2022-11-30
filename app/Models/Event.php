<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class Event extends Model
{
    use HasFactory;

    // Relationship To User
    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }
}
