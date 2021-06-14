<?php

namespace App\Rules;

use App\Client;
use Illuminate\Contracts\Validation\Rule;

class ServiceType implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $is_array;
    public function __construct($is_array = false)
    {
        $this->is_array = $is_array;
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
        $list = Client::$service_types;

        if($this->is_array){
            $value = collect($value);
            if ($value->count() > 0) {
                $result = false;
            
                $value->map(function ($item) use (&$result, $list) {
                    $result =in_array($item, $list) ? true : false;
                });
    
                return $result;
            }
            return true;
        }else{
            return in_array($value,$list) ? true : false;
        }
        
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Invalid value for service type';
    }
}
