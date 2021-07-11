<?php

namespace App\Http\Controllers;

use Exception;
use App\Office;
use App\PaymentMethod;
use App\DepositAccount;
use App\Rules\OfficeID;
use Illuminate\Http\Request;
use App\Rules\TransactionType;
use App\Rules\PaymentMethodList;
use App\Rules\PreventFutureDate;
use App\Events\DepositTransaction;
use App\Rules\AmountDepositBelowMinimum;
use Illuminate\Support\Facades\Validator;
use App\Rules\ValidDepositTransactionDate;
use App\Rules\WithdrawAmountLessThanBalance;
use App\Rules\NotBeforeOrAfterLastTransactionDate;
use App\Rules\PreventLaterThanLastTransactionDate;

class DepositAccountController extends Controller
{
    public function __construct(){

        $this->middleware('permission:enter_deposit|enter_withdraw|interest_posting', ['only' => ['showBulkView']]);
        $this->middleware('permission:interest_posting', ['only' => ['postInterest','bulkPostInterest']]);
    }


    public function deposit($deposit_account_id,Request $request){
        
        $this->validator($request->all())->validate();
        $request->request->add(['paid_by'=>auth()->user()->id]);
        $data = $request->all();
        \DB::beginTransaction();
        try{
            DepositAccount::find($deposit_account_id)->deposit($data,true);
            
            $payload = ['date'=>$data['repayment_date'],'amount'=>$data['amount']];
            event(new \App\Events\DepositTransaction($payload,$data['office_id'],$data['paid_by'],$data['payment_method_id'],'deposit'));
            
            \DB::commit();  
            return response()->json(['msg'=>'Deposit Transaction Succesful'],200);
        }catch(Execption $e){
            return response()->json(['msg'=>'Server Error'],500);
        }
        
    }

    public function withdraw($deposit_account_id, Request $request){
        
        $this->validator($request->all())->validate();
        $request->request->add(['paid_by'=>auth()->user()->id]);
        $data = $request->all();
        \DB::beginTransaction();
        try {
            DepositAccount::find($deposit_account_id)->withdraw($data, true);
            $payload = ['date'=>$data['repayment_date'],'amount'=>$data['amount']];
            event(new \App\Events\DepositTransaction($payload, $data['office_id'], $data['paid_by'], $data['payment_method_id'], 'withdraw'));
            \DB::commit();
            return response()->json(['msg'=>'Withdrawal Transaction Succesful'], 200);
        }catch(Exeception $e){
            return response()->json(['msg'=>'Server Error'],500);

        }
    }

    public function validator(array $data){
        
        $acc = DepositAccount::find($data['deposit_account_id']);
    
        if($data['type']=="withdraw"){
            $rules = [
                'office_id' =>['required', new OfficeID()],
                'amount'=>['required','numeric',new WithdrawAmountLessThanBalance($acc)],
                'payment_method_id'=>['required',new PaymentMethodList()],
                'deposit_account_id'=>['required','exists:deposit_accounts,id'],
                // 'repayment_date'=>['required','date', new PreventFutureDate(), new PreventLaterThanLastTransactionDate($data['deposit_account_id'])],
                'repayment_date'=>['required','before:tomorrow', new ValidDepositTransactionDate($data['deposit_account_id'])],
                'type'=>['required', new TransactionType()],
                'check_voucher_number'=>['required','unique:check_vouchers,check_voucher_number']
            ];
        }elseif($data['type']=="deposit"){
            $rules = [
                'office_id' =>['required', new OfficeID()],
                'amount'=>['required','numeric',new AmountDepositBelowMinimum($acc)],
                'payment_method_id'=>['required', new PaymentMethodList()],
                'deposit_account_id'=>['required','exists:deposit_accounts,id'],
                // 'repayment_date'=>['required','date', new PreventFutureDate(), new PreventLaterThanLastTransactionDate($data['deposit_account_id'])],
                // 'repayment_date'=>['required','before:tomorrow', new PreventLaterThanLastTransactionDate($data['deposit_account_id'])],
                'repayment_date'=>['required','before:tomorrow', new ValidDepositTransactionDate($data['deposit_account_id'])],
                'type'=>['required', new TransactionType()],
                'receipt_number'=>['required']
            ];
        }
        return Validator::make(
            $data, 
            $rules
        );
    }

    public function showList(Request $request){
        if($request->product_id=="ALL"){
            $request->product_id = null;
        }
        $accounts = Office::depositAccounts($request->office_id,$request->product_id)->paginate(50);
        return response()->json(['accounts' => $accounts], 200);

    }
    public function validateBulk(array $data,$type=null){
        $data = $data;
        
        $rules = [];
        
        if($type=='deposit'){
            
            $rules = [
                'office_id' =>['required', new OfficeID()],
                'accounts.*.repayment_date'=>['required','before:tomorrow' ,'prevent_previous_deposit_transaction_date:accounts.*.repayment_date'],
                'payment_method_id'=>['required', new PaymentMethodList()],
                'type'=>['required', new TransactionType()],        
                'accounts.*.id'=> ['required','exists:deposit_accounts,id'],
                'accounts.*.amount'=> ['required', 'cbu_deposit:accounts.*.amount','gt:0'],
                'receipt_number'=>['required']
                ];

            $messages = [
                'accounts.*.amount.gt' => 'Amount must be greater than zero (0)'
            ];
        }else if($type=='withdraw'){
            $rules = [
                'accounts.*.amount'=> ['gt:0','cbu_withdraw:accounts.*.amount'],
                'office_id' =>['required', new OfficeID()],
                'accounts.*.repayment_date'=>['required','date','before:tomorrow','prevent_previous_deposit_transaction_date:accounts.*.repayment_date'],
                // 'accounts.*.repayment_date'=>['required','date', new PreventFutureDate(), 'prevent_previous_deposit_transaction_date:accounts.*.repayment_date'],
                'payment_method_id'=>['required', new PaymentMethodList()],
                'type'=>['required', new TransactionType()],        
                'accounts.*.id'=> ['required','exists:deposit_accounts,id'],
                'receipt_number'=>['required']
                
            ];

            $messages = [];
        }else if($type=="post_interest"){
            $rules = [
                'office_id' =>['required', new OfficeID()],
                // 'accounts.*.repayment_date'=>['required','date', new PreventFutureDate()],
                // 'payment_method'=>['required', new PaymentMethodList()],
                'type'=>['required', new TransactionType()],        
                'accounts.*.id'=> ['required','exists:deposit_accounts,id'],
                'journal_voucher_number'=>['required','unique:journal_vouchers,journal_voucher_number']
            ];
            $messages = [];
        }
        return Validator::make($data, $rules,$messages);
    }
    public function postInterest(Request $request){
        $validator = Validator::make($request->all(),
            [
                'deposit_account_id'=>['required','exists:deposit_accounts,id'],
                'jv_number'=>['required','unique:journal_vouchers,journal_voucher_number'],
                'office_id'=>['required','exists:offices,id']
            ]
        )->validate();

        $data = ['user_id'=>auth()->user()->id, 'journal_voucher_number'=>$request->jv_number,'office_id'=>$request->office_id];
        \DB::beginTransaction();
        try{
            DepositAccount::find($request->deposit_account_id)->postInterest($data,true);
            $response = ['msg'=>'Successfully Posted'];
            \DB::commit();
            
            return response()->json($response, 200);

        }catch(Exception $e){
            return $e->getMessage();
        }
        
        
    }
    public function showBulkView(Request $request){
        return view('pages.bulk.deposit');
    }

    public function bulkDeposit(Request $request){
        $this->validateBulk($request->all(),'deposit')->validate();

        $total_amount = 0;
        $accounts_total = 0;    
        \DB::beginTransaction();
        $office_id = $request->office_id;
        $user_id = auth()->user()->id;
        $payment_method_id = $request->payment_method_id;
        $repayment_date = $request->repayment_date;
        $notes = $request->notes;
        $receipt_number = $request->receipt_number;
        try {
            foreach ($request->accounts as $account) {
                $current = DepositAccount::find($account['id']);
                $transaction_number = 'D'.str_replace(',','',microtime(true));
                $deposit_info = array(
                'amount' => $account['amount'],
                'payment_method_id'=>$payment_method_id,
                'repayment_date'=>$repayment_date,
                'paid_by'=>$user_id,
                'receipt_number'=>$receipt_number,
                'office_id'=>$office_id,
                'transaction_number'=>$transaction_number,
                'notes'=>$notes
                
                
            );
                $current->deposit($deposit_info);
                $total_amount = $total_amount + $account['amount'];
                $accounts_total = $accounts_total + 1;
            }
            $response = array(
                'total_amount' => env('CURRENCY_SIGN').' '.number_format($total_amount, 2, '.', ','),
                'payment_method'=>PaymentMethod::find($payment_method_id)->name,
                'office' => Office::find($office_id)->name,
                'accounts_total' => number_format($accounts_total)
            );
            $depositPayload = ['date'=>$repayment_date,'amount'=>$total_amount];
            event(new DepositTransaction($depositPayload, $office_id, $user_id , $payment_method_id, 'deposit'));
            \DB::commit();
            return response()->json([$response ], 200);
        }catch(Exception $e){
            return response()->json([$e->getMessage() ], 200);

        }
        
    }
    public function bulkWithdraw(Request $request){
        $this->validateBulk($request->all(),'withdraw')->validate();
        $total_amount = 0;
        $accounts_total = 0;
        $repayment_date = $request->repayment_date;
        $payment_method_id = $request->payment_method_id;
        $notes = $request->notes;
        $user_id = auth()->user()->id;
        $office_id = $request->office_id;
        foreach($request->accounts as $account){
            $transaction_number = 'D'.str_replace(',','',microtime(true));

            $current = DepositAccount::find($account['id']);
            $withdrawal_info = array(
                'amount' => $account['amount'],
                'payment_method_id'=>$payment_method_id,
                'repayment_date'=>$repayment_date,
                'paid_by'=>$user_id,
                'transaction_number'=>$transaction_number,
                'notes'=>$notes,
                'office_id'=>$office_id,
            );
            $current->withdraw($withdrawal_info);
            $total_amount = $total_amount + $account['amount'];
            $accounts_total = $accounts_total + 1;
        }
        $response = array(
            'total_amount' => env('CURRENCY_SIGN').' '.number_format($total_amount,2,'.',','),
            'payment_method'=>PaymentMethod::find($payment_method_id)->name,
            'office' => Office::find($office_id)->name,
            'accounts_total' => number_format($accounts_total)
        );

        $depositPayload = ['date'=>$repayment_date,'amount'=>$total_amount];
        event(new DepositTransaction($depositPayload, $office_id, $user_id , $payment_method_id, 'deposit'));

        return response()->json([$response ], 200);
    }
    public function bulkPostInterest(Request $request){
        $this->validateBulk($request->all(),'post_interest')->validate();
        $total_amount = 0;
        $accounts_total = 0;
        // $request->request->add(['posted_by'=>]);
        $user = auth()->user()->id;
        $office_id = $request->office_id;
        \DB::beginTransaction();
        try {
            foreach ($request->accounts as $account) {
                $dep = DepositAccount::find($account['id']);
                $accrued_interest = $dep->accrued_interest;
                $transaction_number = 'P'.str_replace(',','',microtime(true));
                $total_amount = round($total_amount+$accrued_interest, 2);
                $data = [
                    'office_id'=>$request->office_id,
                    'user_id'=>$user,
                    'journal_voucher_number'=>$request->journal_voucher_number
                ];
                $dep->postInterest($data);
            }
        
            $repayment = now()->toDateString();
            $payload = ['date'=>$repayment,'amount'=>$total_amount];
            $payment_method = PaymentMethod::interestPosting()->id;
            $user_id = auth()->user()->id;
            event(new \App\Events\DepositTransaction($payload, $request->office_id, $user_id, $payment_method, 'interest_posting'));

            $response = array(
            'total_amount' => env('CURRENCY_SIGN').' '.number_format($total_amount, 2, '.', ','),
            'payment_method'=>PaymentMethod::find($payment_method)->name,
            'office' => Office::find($office_id)->name,
            'accounts_total' => number_format($accounts_total)
            );

            \DB::commit();

            return response()->json($response, 200);
        } catch (Exception $e){
            return response()->json(['msg'=>$e->getMessage()], 500);
        }
    }
}
