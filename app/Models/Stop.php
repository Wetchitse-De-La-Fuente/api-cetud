<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Line;

class Stop extends Model
{
    protected $fillable = ['name','latitude','longitude','line_id','order'];

    public function line() {
        return $this->belongsTo(Line::class);
    }
}