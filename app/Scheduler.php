<?php

namespace App;

use App\Holiday;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Scheduler extends Model
{
    
    public static function hasHoliday($date,$office_id){
        $date = Carbon::parse($date);
        $office = Office::getUpperOfficesV2($office_id);
        
        $count = Holiday::where('date',$date)->where('implemented', true)->whereIn('office_id',$office)->count();
        if($count > 0){
            return true;
        }
        return false;
    }


    public static function getDate($date,$office_id, $interval=  7){
        $me = new static;
        $date = Carbon::parse($date);
        
        if(self::hasHoliday($date, $office_id)){
            // dd($date);
            
            return self::getDate($date->copy()->addDays($interval),$office_id,$interval);
        }
        return $date;
    }

    public static function getDateForDailyInstallment($date,$office_id, $interval = 1){
        $me = new static;
        $date = Carbon::parse($date);
        
        if(self::hasHoliday($date, $office_id)){
            return self::getDateForDailyInstallment($date->copy()->addDays($interval),$office_id, $interval)->isWeekend() ?
                    self::getDateForDailyInstallment($date->copy()->addWeekday(),$office_id, $interval) :
                    self::getDateForDailyInstallment($date->copy()->addDays($interval),$office_id, $interval);
        }
        return $date;
    }
}
