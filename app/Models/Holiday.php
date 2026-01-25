<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    protected $fillable = ['user_id', 'holiday_date'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
