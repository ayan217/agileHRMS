<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkSession extends Model
{
    protected $fillable = [
        'user_id',
        'work_date',
        'clock_in',
        'clock_out',
        'total_work_seconds',
        'total_break_seconds',
        'status',
        'is_late'
    ];


    public function breaks()
    {
        return $this->hasMany(BreakLog::class);
    }
}
