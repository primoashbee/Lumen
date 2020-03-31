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
        'employed_monthly_gross_income',
        'spouse_is_self_employed',
        'spouse_service_type',
        'spouse_service_type_monthly_gross_income',
        'spouse_is_employed',
        'spouse_employed_position',
        'spouse_employed_company_name',
        'spouse_employed_monthly_gross_income',
        'has_remittance',
        'has_pension',
        'total_household_income',
    ];
}
