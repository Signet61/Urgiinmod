<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FamilyMember extends Model
{
    protected $fillable = ['user_id', 'name', 'rel', 'related_to_id', 'emoji', 'photo', 'bio'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
