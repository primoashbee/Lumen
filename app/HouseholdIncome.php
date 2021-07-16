<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HouseholdIncome extends Model
{

    protected $fillable =[
        'client_id',
        'is_self_employed',
        'service_type',
        'service_type_monthly_gross_income',
        'is_employed',
        'employed_position',
        'employed_company_name',
        'employed_monthly_gross_income',
        'spouse_is_self_employed',
        'spouse_service_type',
        'spouse_service_type_monthly_gross_income',
        'spouse_is_employed',
        'spouse_employed_position',
        'spouse_employed_company_name',
        'spouse_employed_monthly_gross_income',
        'has_remittance',
        'remittance_amount',
        'has_pension',
        'pension_amount',
        'total_household_income',
        'total_household_expense',
    ];

    protected $casts = [
        'is_self_employed' => 'boolean',
        'is_employed' => 'boolean',
        'spouse_is_self_employed' => 'boolean',
        'spouse_is_employed' => 'boolean',
        'has_remittance' => 'boolean',
        'has_pension' => 'boolean',

        'service_type_monthly_gross_income' =>'integer',
        'employed_monthly_gross_income' =>'integer',
        'spouse_service_type_monthly_gross_income' =>'integer',
        'spouse_employed_monthly_gross_income' =>'integer',

        'remittance_amount' =>'integer',
        'pension_amount' =>'integer',
        'total_household_income' =>'integer',
        'total_household_expense' => 'integer',

    ];

    public static function viewables(){
        $me = new static;
        $str = "";
        foreach($me->fillable as $value){
            $str.=$value.",";
        }
        return substr($str,0,-1);
    }

    public function getServiceTypeAttribute($value){
        if($value==null){
            return 'NONE';
        }
        return $value;
    }
}
