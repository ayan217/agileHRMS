<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserHolidayPlan extends Model
{
     protected $fillable = ['user_id','holiday_preset_id','year'];
}
