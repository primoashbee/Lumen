<?php

namespace App;

use App\Events\NotifyUser;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'link',
        'msg',
        'from',
        'to',
        'seen',
        'seen_at'
    ];

    public function notify(){
        return event(new NotifyUser($this));
    }

    public function by(){
        return $this->belongsTo(User::class,'from','id');
    }
}
