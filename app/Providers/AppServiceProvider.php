<?php

namespace App\Providers;

use App\Loan;
use App\Client;
use App\Deposit;
use Carbon\Carbon;
use App\LoanAccount;
use App\PaymentMethod;
use App\DepositAccount;
use Faker\Provider\Payment;
use App\Observers\LoanAccountObserver;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $error = ':custom_message.';        
        Validator::extendDependent('cbu_deposit', function ($attribute, $value, $parameters, $validator){
            // The $parameters passed from the validator below is ['*.provider'], when we imply that this
            // custom rule is dependent the validator tends to replace the asterisks with the current
            // indices as per the original attribute we're validating, so *.provider will be replaced
            // with 0.provider, now we can use array_get() to get the value of the other field.
            
                // So this custom rule validates that the attribute value contains the value of the other given
                // attribute.
            //  echo $error;
            $arr = explode('.', $attribute);
            
            $account = $validator->getData()[$arr[0]][$arr[1]];
            
            
            $type = $account['type'];
            $account_id = $account['id'];

            $customMessage = 
                "Mininum deposit for " .$type['product_id']. ' is '. env('CURRENCY_SIGN').' '.($type['minimum_deposit_per_transaction']);

            
            

            $validator->addReplacer('cbu_deposit', 
                function($message, $attribute, $rule, $parameters) use ($customMessage) {
                    return \str_replace(':custom_message', $customMessage, $message);
                }
            );
            // var_dump($attribute);
            // $account_id = $account['id'];
            // $attribute =$account_id;
            // $attributes = array('account.{$key}.amount'=>$attribute);

            // $validator->setAttributes($attributes);
            // $validator->addReplacer('cbu_deposit', 
            //     function($message, $attribute, $rule, $parameters) use ($account_id) {
            //         return \str_replace(':attribute', $account_id, $attribute);
            //     }
            // );

            if($value < $type['minimum_deposit_per_transaction']){
                return false;
            }
                return true;
            //  return str_contains($value, 
            //          array_get($validator->getData(), $parameters[0])
            //  );
            },$error);
        Validator::extendDependent('cbu_withdraw', function ($attribute, $value, $parameters, $validator){
            // The $parameters passed from the validator below is ['*.provider'], when we imply that this
            // custom rule is dependent the validator tends to replace the asterisks with the current
            // indices as per the original attribute we're validating, so *.provider will be replaced
            // with 0.provider, now we can use array_get() to get the value of the other field.
            
                // So this custom rule validates that the attribute value contains the value of the other given
                // attribute.
            //  echo $error;
            $arr = explode('.', $attribute);
            $account = $validator->getData()[$arr[0]][$arr[1]];
            
            
            $type = $account['type'];
            
            
            $customMessage = "The withdrawal amount is higher than the actual balance (".$account['balance_formatted'].")";

                "Mininum deposit for " .$type['product_id']. ' is '. env('CURRENCY_SIGN').' '.($type['minimum_deposit_per_transaction']);

            $validator->addReplacer('cbu_withdraw', 
                function($message, $attribute, $rule, $parameters) use ($customMessage) {
                    return \str_replace(':custom_message', $customMessage, $message);
                }
            );
            // var_dump($attribute);
            // $account_id = $account['id'];
            // $attribute =$account_id;
            // $attributes = array('account.{$key}.amount'=>$attribute);

            // $validator->setAttributes($attributes);
            // $validator->addReplacer('cbu_deposit', 
            //     function($message, $attribute, $rule, $parameters) use ($account_id) {
            //         return \str_replace(':attribute', $account_id, $attribute);
            //     }
            // );
            
            if($value > $account['balance']){
                return false;
            }
                return true;
            //  return str_contains($value, 
            //          array_get($validator->getData(), $parameters[0])
            //  );
            },$error);
        Validator::extendDependent('cbu_post_interest', function ($attribute, $value, $parameters, $validator){
           
            $arr = explode('.', $attribute);
            
            $account = $validator->getData()[$arr[0]][$arr[1]];
            
            
            $type = $account['type'];
            
            
            $customMessage = "Cannot post interest on accounts with the accrued Interest is 0";

                

            $validator->addReplacer('cbu_withdraw', 
                function($message, $attribute, $rule, $parameters) use ($customMessage) {
                    return \str_replace(':custom_message', $customMessage, $message);
                }
            );
    
            if($account['accrued_interst'] ==0){
                return false;
            }
                return true;
            },$error);
        
        Validator::extendDependent('bulk_below_minimum_deposit_amount',function ($attribute, $value, $parameters, $validator){


            
            $arr = explode('.', $attribute);
            $account_key= $arr[1];
            $deposit_key = $arr[3];
            $values = $validator->getData();
            $account = $validator->getData()['accounts'][$account_key];
            
            $deposit = $account['deposit'][$deposit_key];
            
            $payment = $deposit['amount'];
            $minimum_payment  = DepositAccount::find($deposit['deposit_account_id'])->type->minimumDepositAmount();
            $customMessage = "Minumum deposit amount is " . $minimum_payment['amount_formatted'];
            $validator->addReplacer('bulk_below_minimum_deposit_amount', 
                function($message, $attribute, $rule, $parameters) use ($customMessage) {
                    return \str_replace(':custom_message', $customMessage, $message);
                }
            );
    
            if($payment < $minimum_payment['amount']){
                return false;
            }
            return true;
            
        },$error);
        Validator::extendDependent('bulk_prevent_previous_deposit_transaction_date',function ($attribute, $value, $parameters, $validator){

            $arr = explode('.', $attribute);
            $account_key= $arr[1];
            $deposit_key = $arr[3];
            $values = $validator->getData();
            $account = $validator->getData()['accounts'][$account_key];
            
            $deposit = $account['deposit'][$deposit_key];
            
            $payment = $deposit['amount'];
            $repayment_date = Carbon::parse($deposit['repayment_date']);
            $lastTransaction  = DepositAccount::find($deposit['deposit_account_id'])->lastTransaction();
            if($lastTransaction == null){
                return true;
            }
            $customMessage = "Date should not earlier than  " . $lastTransaction->transaction_date->format('F d, Y');
            $validator->addReplacer('bulk_prevent_previous_deposit_transaction_date', 
                function($message, $attribute, $rule, $parameters) use ($customMessage) {
                    return \str_replace(':custom_message', $customMessage, $message);
                }
            );
            
  
            $diff = $lastTransaction->transaction_date->diffInDays($repayment_date,false);
            if($diff<0){
                return false;
            }
            return true;
            
        },$error);
        Validator::extendDependent('bulk_maximum_loan_repayment',function ($attribute, $value, $parameters, $validator){


            
            $arr = explode('.', $attribute);
            $key = $arr[1];
            $values = $validator->getData();
            $account = $validator->getData()['accounts'][$key];
            $loan = $account['loans'];
            $deposits = $account['deposit'];
            $payment = $loan['amount'];

            $maximum_payment = LoanAccount::find($loan['id'])->maximumPayment();
            $customMessage = "Maximum repayment amount is only: " . $maximum_payment->formatted_amount;
            $validator->addReplacer('bulk_maximum_loan_repayment', 
                function($message, $attribute, $rule, $parameters) use ($customMessage) {
                    return \str_replace(':custom_message', $customMessage, $message);
                }
            );
    
            if($payment > $maximum_payment->amount){
                return false;
            }
            return true;
            
        },$error);
        Validator::extendDependent('maximum_loan_repayment',function ($attribute, $value, $parameters, $validator){


            $values = $validator->getData();

            $acc = LoanAccount::find($values['loan_account_id']);
            $maximum_payment = $acc->maximumPayment();
            $payment = round($value,2);
            
            
            $customMessage = "Maximum repayment amount is only: " . $maximum_payment->formatted_amount;
            $validator->addReplacer('maximum_loan_repayment', 
                function($message, $attribute, $rule, $parameters) use ($customMessage) {
                    return \str_replace(':custom_message', $customMessage, $message);
                }
            );
    
            if($payment > $maximum_payment->amount){
                return false;
            }
            return true;
            
        },$error);
        Validator::extendDependent('bulk_prevent_previous_repayment_date',function ($attribute, $value, $parameters, $validator){



            $arr = explode('.', $attribute);
            $key = $arr[1];
            $account = $validator->getData()['accounts'][$key];
            $loan = $account['loans'];
            $deposits = $account['deposit'];
            $payment = $loan['amount'];
            
       

            $acc = LoanAccount::find($loan['id']);
            if($acc->latestRepayment()==null){
                return true;
            }
            $latest_payment = $acc->latestRepayment()->transaction_date;
            
            $repayment_date = Carbon::parse($value);
            
            $customMessage = "Cannot make repayment before " . $latest_payment->format('F d, Y');
            $validator->addReplacer('bulk_prevent_previous_repayment_date', 
                function($message, $attribute, $rule, $parameters) use ($customMessage) {
                    return \str_replace(':custom_message', $customMessage, $message);
                }
            );
            
            $diff = $latest_payment->diffInDays($repayment_date,false);
            if($diff>=0){
                return true;
            }
                return false;
            
        },$error);
        Validator::extendDependent('prevent_previous_repayment_date',function ($attribute, $value, $parameters, $validator){


            $values = $validator->getData();

            $acc = LoanAccount::find($values['loan_account_id']);
            if($acc->latestRepayment()==null){
                return true;
            }
            $latest_payment = $acc->latestRepayment()->transaction_date;
            
            $repayment_date = Carbon::parse($value);
            
            $customMessage = "Cannot make repayment before " . $latest_payment->format('F d, Y');
            $validator->addReplacer('prevent_previous_repayment_date', 
                function($message, $attribute, $rule, $parameters) use ($customMessage) {
                    return \str_replace(':custom_message', $customMessage, $message);
                }
            );
            
            $diff = $latest_payment->diffInDays($repayment_date,false);
            if($diff>=0){
                return true;
            }
                return false;
            
        },$error);
        Validator::extendDependent('bulk_on_or_before_disbursement_date',function ($attribute, $value, $parameters, $validator){

            $arr = explode('.', $attribute);
            $key = $arr[1];
            $account = $validator->getData()['accounts'][$key];
            $loan = $account['loans'];
            $deposits = $account['deposit'];
            $payment = $loan['amount'];
    

            $disbursed_date = LoanAccount::find($loan['id'])->disbursement_date;
            
            
            
            $repayment_date = Carbon::parse($loan['repayment_date']);
            
            $customMessage = "Cannot make repayment before disbursement date - " . $disbursed_date->format('F d, Y');
            $validator->addReplacer('bulk_on_or_before_disbursement_date', 
                function($message, $attribute, $rule, $parameters) use ($customMessage) {
                    return \str_replace(':custom_message', $customMessage, $message);
                }
            );
            
            $diff = $disbursed_date->diffInDays($repayment_date,false);
            if($diff >= 0){
                return true;
            }
                return false;
            
        },$error);
        
        Validator::extendDependent('on_or_before_disbursement_date',function ($attribute, $value, $parameters, $validator){


            $values = $validator->getData();

            $disbursed_date = LoanAccount::find($values['loan_account_id'])->disbursement_date;
            
            
            
            $repayment_date = Carbon::parse($values['repayment_date']);
            
            $customMessage = "Cannot make repayment before disbursement date - " . $disbursed_date->format('F d, Y');
            $validator->addReplacer('on_or_before_disbursement_date', 
                function($message, $attribute, $rule, $parameters) use ($customMessage) {
                    return \str_replace(':custom_message', $customMessage, $message);
                }
            );
            
            $diff = $disbursed_date->diffInDays($repayment_date,false);
            if($diff >= 0){
                return true;
            }
                return false;
            
        },$error);
        
        Validator::extendDependent('deposit_last_transaction_date',function ($attribute, $value, $parameters, $validator){

            $values = $validator->getData();
            $method = PaymentMethod::find($values['payment_method']);
            $repayment_amount = $values['amount'];
            if($method->isCTLP()){
                $deposit_account = LoanAccount::find($values['loan_account_id'])->client->ctlpAccount();
                $code = $deposit_account->type->product_id;
                $balance = $deposit_account->getRawOriginal('balance');
                $last_transaction = $deposit_account->lastTransaction();
            
                
                if(is_null($last_transaction)){
                    return true;
                }
                $customMessage = "Cannot make transactions before ". $last_transaction->transaction_date->format('F d, Y') ." - ". $deposit_account->type->product_id . "";
                $validator->addReplacer('deposit_last_transaction_date', 
                function($message, $attribute, $rule, $parameters) use ($customMessage) {
                    return \str_replace(':custom_message', $customMessage, $message);
                    }
                );
                $repayment_date = Carbon::parse($values['repayment_date'])->startOfDay();
                // $x = $repayment_date->diffInDays($last_transaction->transaction_date,false);
                return $last_transaction->transaction_date->startOfDay()->diffInDays($repayment_date,false) < 0 ? false : true;
            }

            return true;
            
        },$error);
        Validator::extendDependent('ctlp',function ($attribute, $value, $parameters, $validator){

            $values = $validator->getData();
            $method = PaymentMethod::find($values['payment_method']);
            $repayment_amount = $values['amount'];
            if($method->isCTLP()){
                $code = LoanAccount::find($values['loan_account_id'])->client->ctlpAccount()->type->product_id;
                $balance = LoanAccount::find($values['loan_account_id'])->client->ctlpAccount()->getRawOriginal('balance')  ;
                
                $customMessage = "Insufficient ".$code." Balance  (".money($balance,2).")";
                $validator->addReplacer('ctlp', 
                function($message, $attribute, $rule, $parameters) use ($customMessage) {
                    return \str_replace(':custom_message', $customMessage, $message);
                    }
                );    
                return $repayment_amount > $balance ? false : true;
            }

            return true;
            
        },$error);

        Validator::extendDependent('prevent_previous_deposit_transaction_date',function ($attribute, $value, $parameters, $validator){

            $arr = explode('.', $attribute);
            $account = $validator->getData()[$arr[0]][$arr[1]];

            $repayment_date = Carbon::parse($value);
            $account = DepositAccount::find($account['id']);
            
            if($account->lastTransaction() == null){
                return true;
            }
            $last_transaction_date = $account->lastTransaction()->transaction_date;
           
            $customMessage = "Cannot make transaction before " . $last_transaction_date->format('F d, Y');
            $validator->addReplacer('prevent_previous_deposit_transaction_date', 
                function($message, $attribute, $rule, $parameters) use ($customMessage) {
                    return \str_replace(':custom_message', $customMessage, $message);
                }
            );
            
            
            
            return $last_transaction_date->diffInDays($repayment_date,false) >= 0 ? true : false;
            // return  $last_transaction_date->diffInDays($_account['repayment_date'],false) < 0 ? false : true;

            
        },$error);
        
        Validator::extendDependent('bulk_with_loanable_amount',function ($attribute, $value, $parameters, $validator){


            $values = $validator->getData();
            $arr = explode('.', $attribute);
            $_account = $validator->getData()[$arr[0]][$arr[1]];
            
            $loan = Loan::select('loan_maximum_amount','loan_minimum_amount')->find($values['loan_id']);
            $maximum_loanable_amount = $loan->loan_maximum_amount;
            $minimum_loanable_amount = $loan->loan_minimum_amount;
            // $customMessage = '';
            $status = true;
            if($value < $minimum_loanable_amount){
                $status = false;
                $customMessage = "Minimum loanable amount is " . money($minimum_loanable_amount,2);
                $validator->addReplacer('bulk_with_loanable_amount', 
                function($message, $attribute, $rule, $parameters) use ($customMessage) {
                    return \str_replace(':custom_message', $customMessage, $message);
                }
            );

            }

            if($value > $maximum_loanable_amount){
                $status = false;
                $customMessage = "Maximum loanable amount is " . money($maximum_loanable_amount,2);
                $validator->addReplacer('bulk_with_loanable_amount', 
                function($message, $attribute, $rule, $parameters) use ($customMessage) {
                    return \str_replace(':custom_message', $customMessage, $message);
                }
            );

            }


            return $status;
            
        },$error);
        Validator::extendDependent('bulk_has_no_unused_dependent',function ($attribute, $value, $parameters, $validator){


            $values = $validator->getData();
            $arr = explode('.', $attribute);
            $_account = $validator->getData()[$arr[0]][$arr[1]];
            
            
            $customMessage = "Client has no applied dependent";
            $validator->addReplacer('bulk_has_no_unused_dependent', 
                function($message, $attribute, $rule, $parameters) use ($customMessage) {
                    return \str_replace(':custom_message', $customMessage, $message);
                }
            );

            $res = Client::where('client_id',$_account['client_id'])->first()->hasUnusedDependent();
            return $res;

            
        },$error);
        Validator::extendDependent('valid_loan_ids',function ($attribute, $value, $parameters, $validator){
            
            $values = $validator->getData();
            
            $loan_ids = json_decode($values[$attribute]);
            $customMessage = "Invalid Account ID";
            $validator->addReplacer('bulk_prevent_previous_deposit_transaction_date', 
                function($message, $attribute, $rule, $parameters) use ($customMessage) {
                    return \str_replace(':custom_message', $customMessage, $message);
                }
            );
            
       
            return true;
  
            
        },$error);
    }
}
