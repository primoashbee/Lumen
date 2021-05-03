<?php

namespace App\Rules;

use App\Client;
use Illuminate\Contracts\Validation\Rule;

class ClientHasActiveDependent implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    protected $client_id;
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
        $this->client_id = $value;
        
        return Client::where('client_id',$value)->first()->hasActiveDependent();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return "Client ". $this->client_id ." doesn't have active dependent)";
    }
}
