<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HolidayPreset extends Model
{
    protected $fillable = ['name', 'user_id'];

    public function dates()
    {
        return $this->hasMany(HolidayPresetDate::class);
    }
}
