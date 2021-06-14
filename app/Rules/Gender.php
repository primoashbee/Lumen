<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class Gender implements Rule
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
        $genders = ["MALE","FEMALE"];

        if ($this->is_array) {
            $value = collect($value);
            if ($value->count() > 0) {
                $result = false;
                $value->map(function ($item) use ($genders, &$result) {
                    $result =  in_array($item, $genders) ?  true : false;
                });
                return $result;
            }
            return true;
        }
        return in_array($value, $genders) ?  true : false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Must select valid gender.';
    }
}
