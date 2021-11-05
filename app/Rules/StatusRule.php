<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class StatusRule implements Rule
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
        $status = ["Active","Closed"];
        
        
            $value = collect($value);
            if ($value->count() > 0) {
                $result = false;
                $value->map(function ($item) use ($status, &$result) {
                    $result =  in_array($item, $status) ?  true : false;
                });
                return $result;
            }
            return true;
        
        return in_array($value, $status) ?  true : false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The validation error message.';
    }
}
