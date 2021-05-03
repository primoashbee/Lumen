<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    public function office(){
        return $this->belongsTo(Office::class);
    }
}
