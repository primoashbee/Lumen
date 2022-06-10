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
        $client = Client::fcid($this->client_id);

        if ($client->businesses->count() > 0) {
            return true;
        }else{
            return $value == 5000 ? true : false;
        }
        
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
