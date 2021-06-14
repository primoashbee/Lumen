<?php 
namespace App\Traits;

use App\Log;

trait Loggable {
    public function log($message, $status){
        return $this->logs()->create(['message'=>$message,'status'=>$status]);
    }

    public function logs(){
        return $this->morphMany(Log::class,'loggable');
    }
}
?>