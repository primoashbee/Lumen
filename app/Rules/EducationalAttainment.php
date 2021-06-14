<?php

namespace App\Rules;

use App\Client;
use Illuminate\Contracts\Validation\Rule;

class EducationalAttainment implements Rule
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
        $types = Client::$educational_attainment;
        if($this->is_array){
            if(collect($value)->count() > 0){
                $result = false;
                collect($value)->map(function($item) use ($types, &$result){
                    $result = in_array($item,$types) ? true : false;
                });
                return $result;
            }
            return true;
        }
        
        return in_array($value,$types) ?  true : false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Must select valid educational attainment.';
    }
}
