<?php

namespace App\Rules;

use App\Client;
use Illuminate\Contracts\Validation\Rule;

class ClientHasBusiness implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public $client_id;
    public function __construct($client_id)
    {
        $this->client_id = $client_id;
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
        $client_id = $this->client_id;
        
        $client_business = Client::fcid($this->client_id)->businesses->count();
        
        return $client_business < 1 && $value != 5000 ? false : true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Maximum Loan is'. money(5000,2).' for start up loan.';
    }
}
