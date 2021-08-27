<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class Businesses implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->message = "Business information should contain atleast (1) and filled up properly";
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $businesses = json_decode($value);

        if (count($businesses) == 0){
            return false;
        }
        $z = is_null($value[0]);
        
        if($z){
            return false;
        }
        // dd($businesses);
        foreach($businesses as $business){
            if($business->business_address == "" || is_null($business->business_address)){
                return false;
            }
            if($business->service_type == "" || is_null($business->service_type)){
                return false;
            }
            if($business->monthly_gross_income <= 0){
                return false;
            }
            if($business->monthly_operating_expense <= 0){
                return false;
            }

        }
        return true;
        
        
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return $this->message;
    }
}
