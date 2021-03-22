<?php

namespace App\Http\Controllers;

use Exception;
use App\Account;
use Carbon\Carbon;
use App\LoanAccount;
use App\DepositAccount;
use App\Rules\OfficeID;
use App\Events\LoanPayment;
use Illuminate\Http\Request;
use App\LoanAccountRepayment;
use App\Rules\PaymentMethodList;
use App\Rules\PreventFutureDate;
use App\Events\DepositTransaction;
use App\Events\LoanAccountPayment;
use App\Rules\AccountMustBeActive;
use Illuminate\Support\Facades\DB;
use App\Rules\DepositAccountActive;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CollectionSheetExport;
use Illuminate\Support\Facades\Validator;

class RepaymentController extends Controller
{
    
    public function accountPayment(Request $request){
        
        $this->validator($request->all())->validate();
        
        $account = LoanAccount::find($request->loan_account_id);
        $request->request->add(['paid_by'=>auth()->user()->id]);
        $request->request->add(['user_id'=>auth()->user()->id]);
        // $request->request->add(['jv_number'=>uniqid()]);
        
        \DB::beginTransaction();
        try {
            $account->pay($request->all(), true);
            $account->updateBalances();
            $loanPayload = [
                'date'=>Carbon::parse($request->repayment_date)->format('d-F'),
                'amount'=>$request->amount
            ];
            event(new LoanAccountPayment($loanPayload, $request->office_id, $request->paid_by, $request->payment_method));
            // \DB::commit();
            return response()->json(['msg'=>'Payment Successfully Received!'],200);
        }catch(Exception $e){
            return response()->json(['msg'=>$e->getMessage()],500);
        }
        
        
    }

    public function distributePayment($payment,$installment){
        $interest_due = $installment->interest_due;
        $principal_due = $installment->principal_due;
        $due = $installment->amount_due;
        $distributed = [];

        if($payment >= $due){
            $payment -= $interest_due;
            $distributed['interest_due'] = $interest_due;
            $payment -= $principal_due;
            $distributed['principal_due'] = $principal_due;
            $distributed['remaining'] = $payment;
        }

        dd($distributed);
        return $distributed;
    }

    public function validator(array $data){
        $rules = [
            'office_id' =>['required', new OfficeID()],
            'repayment_date'=>['required','date', new PreventFutureDate(),'prevent_previous_repayment_date','on_or_before_disbursement_date','deposit_last_transaction_date'],
            'payment_method'=>['required', new PaymentMethodList],
            'loan_account_id'=>['required', 'numeric','exists:loan_accounts,id',new AccountMustBeActive],
            'amount' => ['required','gt:0','maximum_loan_repayment','ctlp'],
            'receipt_number'=>['required','unique:receipts,receipt_number']
            
        ];
        $messages =[
            'office_id.required'=>'Level is required',
            'repayment_date.required'=>'Repayment Date is required',
            'repayment_date.date'=>'Repayment Date must be a date',
            'loan_account_id.required'=>'Loan is invalid',
            'loan_account_id.exists'=>'Loan is invalid',
            'amount.required'=>'Amount is required',
            'amount.gt'=>'Amount must be greater than 0',
            'amount.numeric'=>'Invalid Amount Data Type',
        ];
        return Validator::make($data,$rules,$messages);
    }

    public function preTerminate(Request $request){
        $request->validate([
            'office_id' =>['required', new OfficeID()],
            'repayment_date'=>['required','date', new PreventFutureDate(),'prevent_previous_repayment_date'],
            'payment_method'=>['required', new PaymentMethodList],
            'loan_account_id'=>['required', 'numeric','exists:loan_accounts,id',new AccountMustBeActive, 'ctlp'],
            'amount'=>['ctlp']
        ]);

        \DB::beginTransaction();
        try{
            $request->request->add(['paid_by'=>auth()->user()->id]);
            $request->request->add(['user_id'=>auth()->user()->id]);
            $acc = LoanAccount::find($request->loan_account_id)->preTerminate($request->all(),true);
            \DB::commit();
            return response()->json(['msg'=>'Transaction Successful'],200);
        }catch(Exception $e){
            return response()->json(['msg'=>$e->getMessage()],422);
        }
    }
    public function showBulkForm(){
        return view('pages.bulk.repayments');
    }   

    public function scheduledList(Request $request){
        $this->scheduledListValidator($request->all());
        $data = [
            'office_id'=>$request->office_id,
            'date'=>$request->date,
            'loan_account_id'=>$request->loan_product_id,
            'deposit_product_ids'=>collect($request->deposit_products)->pluck('id')
        ];
        $list = Account::repaymentsFromDate($data,true);
        session(['ccr'=>$list]);
        return response()->json(['list'=>$list,'msg'=>'success'],200);
    }

    public function scheduledListValidator(array $array){

    }
    public function bulkRepayment(Request $request){
      
        $this->validateBulk($request->all())->validate();

        $data = $request->all();
        $total_payment = $data['accounts'];
        \DB::beginTransaction();
        try {
            $payment_method = $request->payment_method;
            $repayment_date = Carbon::parse($request->repayment_date);
            // $repayment_date = $request->repayment_date;
            $receipt_number = $request->receipt_number;
            $notes = $request->notes;
            $user = auth()->user()->id;
            $repayment = 0;
            $deposit = 0;
            
            foreach($data['accounts'] as $key=>$value){
                $loan = $value['loans'];
                $repayment+=$loan['amount'];
                $payment_info = [
                    'amount'=> $loan['amount'],
                    'payment_method'=>$payment_method,
                    'repayment_date'=>$repayment_date,
                    'notes'=>$notes,
                    'receipt_number'=>$receipt_number,
                    'paid_by'=>$user,
                    'office_id'=>$data['office_id']
                ];
                LoanAccount::find($loan['id'])->pay($payment_info);
                $deposits = $value['deposit'];
                $has_deposit  = count($deposits) > 0;
                if($has_deposit){
                    foreach($deposits as $key=>$value){
                        $amount = $value['amount'];
                        $deposit+= $amount;
                        $deposit_info = [
                            'amount'=>$amount,
                            'payment_method'=>$payment_method,
                            'repayment_date'=>$repayment_date,
                            'user_id' => $user,
                            'receipt_number' => $receipt_number,
                            'office_id'=>$data['office_id']
                        ];
                        DepositAccount::find($value['deposit_account_id'])->deposit($deposit_info);
                    }
                }
                
            }

            // $office
            // $msg = 'Repayment '. money($repayment,2) .' at ' . $office .' by ' . $by. ' ['.$payment.'].';

            $loanPayload = ['date'=>$repayment_date->format('d-F'),'amount'=>$repayment];
            $depositPayload = ['date'=>$repayment_date->format('d-F'),'amount'=>$deposit];
            event(new LoanAccountPayment($loanPayload, $request->office_id, $user, $payment_method));
            if ($has_deposit) {
                event(new DepositTransaction($depositPayload, $request->office_id, $user, $payment_method, 'deposit'));
            }
            // \DB::commit();
            
        return response()->json(['msg'=>'Payment Successful','code'=>200],200);    
        } catch (\Exception $e){
            return response()->json(['msg'=>$e->getMessage()],404);
        }
        
        
        
    }

    public function validateBulk(array $array){
        $hasDeposit = count($array['accounts'][0]['deposit']) > 0;
        $rules = [
            'office_id' => ['required','exists:offices,id'],
            'receipt_number'=>['required','unique:receipts,receipt_number'],
            'repayment_date'=>['required','date','before:tomorrow'],
            'accounts.*.loans.id' =>['required', 'numeric','exists:loan_accounts,id',new AccountMustBeActive],
            'accounts.*.loans.amount'=>['required','bulk_maximum_loan_repayment:accounts.*.loans.amount'],
            'accounts.*.loans.repayment_date'=>['required','date', new PreventFutureDate(),'bulk_prevent_previous_repayment_date','bulk_on_or_before_disbursement_date'],
            'payment_method'=>['required', new PaymentMethodList]
        ];
        if($hasDeposit){
            $rules = [
                'office_id' => ['required','exists:offices,id'],
                'receipt_number'=>['required','unique:receipts,receipt_number'],
                'repayment_date'=>['required','date','before:tomorrow'],
                'accounts.*.loans.id' =>['required', 'numeric','exists:loan_accounts,id',new AccountMustBeActive],
                'accounts.*.loans.amount'=>['required','bulk_maximum_loan_repayment:accounts.*.loans.amount'],
                'accounts.*.loans.repayment_date'=>['required','date', new PreventFutureDate(),'bulk_prevent_previous_repayment_date','bulk_on_or_before_disbursement_date'],
                'payment_method'=>['required', new PaymentMethodList],

                'accounts.*.deposit.*.deposit_account_id'=>['required','exists:deposit_accounts,id', new DepositAccountActive],
                'accounts.*.deposit.*.amount'=>['required','gte:0','bulk_below_minimum_deposit_amount:accounts.*.deposit.*.amount'],
                'accounts.*.deposit.*.repayment_date'=>['required','date', new PreventFutureDate,'bulk_prevent_previous_deposit_transaction_date:accounts.*.deposit.*.repayment_date'],

            ];
        }

        $messages = [
            'accounts.*.loans.amount.required'=>'Payment amount is required',
            'accounts.*.deposit.*.amount.required'=>'Deposit amount is required',
            'accounts.*.deposit.*.amount.gte'=>'Deposit must be greater than 0',
            
            
        ];

        return Validator::make($array,$rules,$messages);
    }
}
