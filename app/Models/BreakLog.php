<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BreakLog extends Model
{
    protected $fillable = [
        'work_session_id',
        'break_start',
        'break_end',
        'duration_seconds'
    ];
}
