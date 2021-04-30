<?php

namespace App\Http\Controllers;

use Exception;
use App\Transaction;
use Illuminate\Http\Request;

use App\LoanAccountRepayment;
use App\Rules\TransactionType;
use App\LoanAccountDisbursement;
use App\Rules\LatestTransaction;
use App\Rules\ValidTransactionID;
use App\Rules\ValidTransactionNumber;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\Process\ExecutableFinder;

class RevertController extends Controller 
{
    public function revert(Request $request){
        $user_id = auth()->user()->id;
        $this->validator($request->all())->validate();
        // $type = $this->checkPaymentType($request->transaction_id);
        

        // $list = Transaction::where('transaction_number',$request->transaction_number)->orderBy('id','desc')->get();
        \DB::beginTransaction();
        
        try{
            $t = $request->transaction_number;
            $transaction = Transaction::get($request->transaction_number);
            if(is_array($transaction)){
                collect($transaction)->each->revert($user_id);
            }else{
                $transaction->revert($user_id);
            }
            \DB::commit();
            return response()->json(['msg'=>'Successfully Reverted!']);  
        }catch(Execption $e){
            return response()->json(['msg'=>$e->getMessage()],500);
        }
    }

    public function validator(array $data){
        return Validator::make($data,[
            // 'type'=>['required',new TransactionType],
            // 'user_id'=>['required','exists:users,id'],
            // 'transaction_id'=>['required', new ValidTransactionID],
            // 'loan_account_id'=>['required','exists:loan_accounts,id']
            // 'transaction_number'=>['required','exists:transactions,transaction_number',new LatestTransaction]
            // 'transaction_number'=>['required',new LatestTransaction]
            'transaction_number'=>['required',new ValidTransactionNumber, new LatestTransaction]
        ]);
    }


    public function checkPaymentType($transaction_id){
        //check if repayment
        
        if(LoanAccountRepayment::where('transaction_id',$transaction_id)->count() > 0){

            if(\Str::contains($transaction_id, 'R')){
                return 'repayment';
            }
            if(\Str::contains($transaction_id, 'D')){
                return 'disbursement';
            }
            if(\Str::contains($transaction_id, 'F')){
                return 'fee_payment';
            }
            if(\Str::contains($transaction_id, 'C')){
                return 'ctlp';
            }
            if(\Str::contains($transaction_id, 'P')){
                return 'pretermination';
            }
        }

        if(LoanAccountDisbursement::where('transaction_id',$transaction_id)->count() > 0){
            if(\Str::contains($transaction_id, 'D')){
                return 'disbursement';
            }
        }
    }

  
}
