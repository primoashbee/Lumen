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
        //
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
        $y = count($value);
        if($y == 0){
            return false;
        }
        $z = is_null($value[0]);

        if($z ){
            return false;
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
        return 'Business information should contain atleast (1)';
    }
}
