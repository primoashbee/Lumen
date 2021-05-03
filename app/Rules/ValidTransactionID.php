<?php

namespace App\Rules;

use App\LoanAccountRepayment;
use App\LoanAccountDisbursement;
use Illuminate\Contracts\Validation\Rule;

class ValidTransactionID implements Rule
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
        $repayment = LoanAccountRepayment::where('transaction_id',$value);
        if($repayment->count() > 0){
           return true;
        }
        $disbursement = LoanAccountDisbursement::where('transaction_id',$value);
        if($disbursement->count() > 0){
            return true;
        }
        return false;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Invalid Transaction ID';
    }
}
