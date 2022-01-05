<?php

namespace App;

use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use stdClass;

class Fee extends Model
{
    protected $hidden = array('pivot');

    protected $fillable = [
        'name',
        'automated',
        'calculation_type',
        'gl_account',
        'fixed_amount',
        'finance_charge'
    ];


    public function loan(){
        return $this->belongsToMany(Loan::class,'loan_fee');
    }

    public function calculateFeeAmount($loan_amount,$installment,$loan_product,$dependent=null,$unit_of_plan=1){
        
        //cgli premium ok
        //cgli fee ok
        //dst ok
        $weeks = 0;
        // if($loan_product->installment_method=="weeks"){
        //     $weeks = $loan_product->installment_length * $installment;
        // }
        if($loan_product->installment_method=="weeks"){
            $weeks = $installment;
        }
        if($loan_product->installment_method=="days"){
            $weeks = $installment;
        }
        if($loan_product->installment_method=="months"){
            $weeks = $installment;
        }

        
        if($this->calculation_type=="fixed"){
           return $this->fixed_amount;
        }
        if ($this->calculation_type=="percentage") {
            return $loan_amount * (double) $this->percentage;
        }
        if ($this->calculation_type=="matrix") {
            if($this->name=="Documentary Stamp Tax"){
                $months = $this->weekToMonth($weeks);
                $days = $this->monthToDays($months);
                
                return round(($loan_amount/200) * ($days / 365) * 1.5, 2);
                
            }
            if($this->name=="MI Premium"){
                $months = $this->weekToMonth($weeks);
                $amount = $this->calculateMiPremiumAmount($months,$dependent);
                return round($amount * $unit_of_plan,2);
            }
            if($this->name=="CGLI Fee"){
                $months = $this->weekToMonth($weeks);
                
                $monthly_rate = $this->cgliRates($months);
                
                $cgli_premium_remittance = $loan_amount / 1000 * $monthly_rate;
                $cgli_premium_payable = $loan_amount * 0.005;
                $cgli_fee = $cgli_premium_payable - $cgli_premium_remittance ;
                return round($cgli_fee,2);
                
            }
            if($this->name=="CGLI Premium"){
                $months = $this->weekToMonth($weeks);
                $monthly_rate = $this->cgliRates($months);
                $cgli_premium_remittance = $loan_amount / 1000 * $monthly_rate;
                $cgli_premium_payable = $loan_amount * 0.005;
                return round($cgli_premium_remittance,2);
                
            }
            if($this->name=="PHIC Premium"){
                return 0;
            }
        }

        Log::critical('Fee name non existing: '.$this);

    }

    public function weekToMonth($number_of_weeks){
        return (int) round($number_of_weeks / 4);
    }
    public function monthToDays($months){
        if($months == 12){
            return 365;
        }
        return $months * 30;
    }
    public function cgliRates($months){   
        if($months == 1){
            return 0.45;
        }
        if($months == 2){
            return 0.9;
        }
        if($months == 3){
            return 1.35;
        }
        if($months == 4){
            return 1.8;
        }
        if($months == 5){
            return 2.5;
        }
        if($months == 6){
            return 2.7;
        }
        if($months == 7){
            return 3.15;
        }
        if($months == 8){
            return 3.65;
        }
        if($months == 9){
            return 4.1;
        }
        if($months == 10){
            return 4.5;
        }
        if($months == 11){
            return 4.75;
        }
        if($months == 12){
            return 4.85;
        }
        return 0.45 * $months;
    }

    public static function miPremiumRates(){
        
        $rates[] = (object) array(
            'months'=>1,
            'member'=>125,
            'adult'=>115,
            'young'=>15
        );
        $rates[] = (object) array(
            'months'=>2,
            'member'=>125,
            'adult'=>115,
            'young'=>15
        );

        $rates[] = (object) array(
            'months'=>3,
            'member'=>125,
            'adult'=>115,
            'young'=>15
        );

        $rates[] = (object) array(
            'months'=>4,
            'member'=>275,
            'adult'=>170,
            'young'=>19
        );

        $rates[] = (object) array(
            'months'=>5,
            'member'=>320,
            'adult'=>211,
            'young'=>22
        );

        $rates[] = (object) array(
            'months'=>6,
            'member'=>330,
            'adult'=>218,
            'young'=>23
        );

        $rates[] = (object) array(
            'months'=>7,
            'member'=>365,
            'adult'=>253,
            'young'=>27
        );

        $rates[] = (object) array(
            'months'=>8,
            'member'=>410,
            'adult'=>295,
            'young'=>31
        );

        $rates[] = (object) array(
            'months'=>9,
            'member'=>455,
            'adult'=>335,
            'young'=>35
        );

        $rates[] = (object) array(
            'months'=>10,
            'member'=>500,
            'adult'=>378,
            'young'=>38
        );

        $rates[] = (object) array(
            'months'=>11,
            'member'=>524,
            'adult'=>417,
            'young'=>40
        );

        $rates[] = (object) array(
            'months'=>12,
            'member'=>544,
            'adult'=>417,
            'young'=>43
        );
        return collect($rates);
    }

    public function calculateMiPremiumAmount($term,$dependent){
        $rates = $this->miPremiumRates();
        // var_dump($term);
        
        $rate = $rates->where('months',$term)->first();
        $amount = $rate->member;
        $unit_of_plan =1;
        
        if ($dependent != null) {
            foreach ($dependent as $item) {
                if (!$item->is_member) {
                    $level = $item->level;
                    $amount += $rate->$level;
                    $unit_of_plan = $item->unit_of_plan;
                }
            }
        }
        
        $total = round($amount*$unit_of_plan,2);
        return $total;
    }
    

    

    
}
