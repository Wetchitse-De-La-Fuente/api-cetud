<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Stop;
use App\Models\Schedule;

class Line extends Model
{
    protected $fillable = ['name','start_point','end_point'];

    public function stops() {
        return $this->hasMany(Stop::class);
    }

    public function schedules() {
        return $this->hasMany(Schedule::class);
    }
}