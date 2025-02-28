<?php

namespace App\Rules;

use App\Client;
use Illuminate\Contracts\Validation\Rule;

class hasUnusedDepedent implements Rule
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
        return Client::where('client_id',$value)->first()->dependents->where('status','Unused')->count() == 0;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Client has already applied dependent';
    }
}
