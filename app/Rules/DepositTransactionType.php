<?php

namespace App\Rules;

use App\Transaction;
use App\DepositAccount;
use Illuminate\Contracts\Validation\Rule;

class DepositTransactionType implements Rule
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
        $value = collect($value);
        if($value->count() == 0){
            return true;
        }
        $list = collect(DepositAccount::$deposit_transactions_report);
        $result = false;
        
        $value->each(function($item, $key) use ($list, &$result){
            $list->each(function($l_item, $l_key) use (&$result, $item){
                if($l_item == $item){
                    $result = true;
                    return false;
                }else{
                    $result = false;
                }
            });
        });
        return $result;
        
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Invalid Transaction Type';
    }
}
