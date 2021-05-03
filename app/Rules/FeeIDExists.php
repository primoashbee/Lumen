<?php

namespace App\Rules;

use App\Fee;
use Illuminate\Contracts\Validation\Rule;

class FeeIDExists implements Rule
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
        return in_array($value['id'],Fee::get(['id'])->pluck('id')->toArray()) ? true : false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Fee does not exists';
    }
}
