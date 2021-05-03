<?php

namespace App;

use App\Holiday;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Scheduler extends Model
{
    
    public function hasHoliday($date,$office_id){
        $date = Carbon::parse($date);
        $count = Holiday::where('date',$date)->where('office_id',$office_id)->count();
        if($count > 0){
            return true;
        }
        return false;
    }


    public static function getDate($date,$office_id){
        $me = new static;
        $date = Carbon::parse($date);
        
        if($me->hasHoliday($date, $office_id)){
            return $me->getDate($date->copy()->addWeek(),$office_id);
        }
        return $date;
    }
}
