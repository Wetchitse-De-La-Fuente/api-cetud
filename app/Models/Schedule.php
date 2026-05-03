<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Line;
use App\Models\Stop;

class Schedule extends Model
{
    protected $fillable = ['line_id','stop_id','departure_time'];

    public function line() {
        return $this->belongsTo(Line::class);
    }

    public function stop() {
    	return $this->belongsTo(Stop::class);
    }
}