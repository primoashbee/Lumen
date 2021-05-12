<?php

namespace App\Http\Controllers;

use Exception;
use App\Client;
use App\Account;
use Carbon\Carbon;
use App\LoanAccount;
use App\PaymentMethod;
use App\DepositAccount;
use App\Rules\OfficeID;
use App\Events\LoanPayment;
use Illuminate\Http\Request;
use App\LoanAccountRepayment;
use App\Rules\PaymentMethodList;
use App\Rules\PreventFutureDate;
use App\Events\DepositTransaction;
use App\Events\LoanAccountPayment;
use App\Events\LoanAccountPaymentEvent;
use App\Rules\AccountMustBeActive;
use Illuminate\Support\Facades\DB;
use App\Rules\DepositAccountActive;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CollectionSheetExport;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\DownloadController;

class RepaymentController extends Controller
{
    
    public function accountPayment(Request $request){
        
        if($request->has('payment_method_id')){
            $isCTLP = PaymentMethod::find($request->payment_method_id)->isCTLP();
            $this->validator($request->all(),$isCTLP)->validate();
            $account = LoanAccount::find($request->loan_account_id);
            $request->request->add(['paid_by'=>auth()->user()->id]);
            $request->request->add(['user_id'=>auth()->user()->id]);
            \DB::beginTransaction();
            try {
                
                $payment_summary = [
                    'interest_paid'=>0,
                    'principal_paid'=>0,
                    'total_paid'=>0,
                ];
                $account_payment = $account->payV2($request->all(), true);

                $payment_summary['interest_paid'] += $account_payment['interest_paid'];
                $payment_summary['principal_paid'] += $account_payment['principal_paid'];
                $payment_summary['total_paid'] += $account_payment['total_paid'];
                
                $account->updateBalances();
                $loanPayload = [
                    'date'=>Carbon::parse($request->repayment_date)->format('d-F'),
                    'amount'=>$request->amount,
                    'summary'=>$payment_summary
                ];
                event(new LoanAccountPayment($loanPayload, $request->office_id, $request->paid_by, $request->payment_method_id));
                \DB::commit();
                return response()->json(['msg'=>'Payment Successfully Received!'],200);
            }catch(Exception $e){
                return response()->json(['msg'=>$e->getMessage()],500);
            }
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

    public function validator(array $data,$is_ctlp = false){
        if($is_ctlp){
            $rules = [
                'office_id' =>['required', new OfficeID()],
                'repayment_date'=>['required','date', new PreventFutureDate(),'prevent_previous_repayment_date','on_or_before_disbursement_date','deposit_last_transaction_date'],
                'payment_method_id'=>['required', new PaymentMethodList],
                'loan_account_id'=>['required', 'numeric','exists:loan_accounts,id',new AccountMustBeActive],
                'amount' => ['required','gt:0','maximum_loan_repayment','ctlp'],
                'jv_number'=>['required','unique:journal_vouchers,journal_voucher_number']
                
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
        }else{
            $rules = [
                'office_id' =>['required', new OfficeID()],
                'repayment_date'=>['required','date', new PreventFutureDate(),'prevent_previous_repayment_date','on_or_before_disbursement_date','deposit_last_transaction_date'],
                'payment_method_id'=>['required', new PaymentMethodList],
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
        }
        return Validator::make($data,$rules,$messages);
    }

    public function preTerminate(Request $request){
        $request->validate([
            'office_id' =>['required', new OfficeID()],
            'repayment_date'=>['required','date', new PreventFutureDate(),'prevent_previous_repayment_date'],
            'payment_method_id'=>['required', new PaymentMethodList],
            'loan_account_id'=>['required', 'numeric','exists:loan_accounts,id',new AccountMustBeActive, 'ctlp'],
            'amount'=>['ctlp']
        ]);

        \DB::beginTransaction();
        try{
            $user_id = auth()->user()->id;
            $request->request->add(['paid_by'=>$user_id]);
            $request->request->add(['user_id'=>$user_id]);
            $acc = LoanAccount::find($request->loan_account_id)->preTerminate($request->all(),true);
            
            $loanPayload = ['date'=>Carbon::parse($request->repayment_date),'amount'=>$request->amount];

            event(new LoanAccountPayment($loanPayload, $request->office_id, $user_id, $request->payment_method_id));
            
            \DB::commit();
            return response()->json(['msg'=>'Transaction Successful'],200);
        }catch(Exception $e){
            return response()->json(['msg'=>$e->getMessage()],422);
        }
    }
    public function showBulkForm(){
        return view('pages.bulk.repayments');
    }   

    public function scheduledListV2(Request $request){
        $this->scheduledListValidator($request->all());
        $data = [
            'office_id'=>$request->office_id,
            'date'=>$request->date,
            'loan_product_id'=>$request->loan_product_id,
            'deposit_product_ids'=>collect($request->deposit_product_ids)->pluck('id')
        ];

        $list = Account::ccrFromDate($data);
        session(['request'=>$request->all()]);
        session(['ccr'=>$list]);
        $for_ccr = $request->has('ccr') ? true : false;
        if($for_ccr){
            
            $file = DownloadController::ccr($request->all(), $list);
            return response()->download($file['file'],$file['filename'],$file['headers']);

        }
        return response()->json(['list'=>$list], 200);

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
        
        return response()->json(['list'=>$list,'msg'=>'success'],200);
    }

    public function scheduledListValidator(array $array){
        return Validator::make($array,
            [
                'office_id'=>['required','exists:offices,id'],
                'date'=>['required','date'],
                'loan_product_id'=>['required','exists:loans,id'],
                'deposit_product_ids'=>['sometimes']
            ],
            [
                'office_id.required'=>'Office level is required'
            ])->validate();
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
            
            $total_payment = [
                'interest'=>0,
                'principal'=>0,
                'total'=>0,
            ];
            

            
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
                $payment_summary = LoanAccount::find($loan['id'])->payV2($payment_info);
                
                $total_payment['interest'] += $payment_summary['interest_paid'];
                $total_payment['principal'] += $payment_summary['principal_paid'];
                $total_payment['total'] += $payment_summary['total'];

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
                            'posted_by' => $user,
                            'receipt_number' => $receipt_number,
                            'office_id'=>$data['office_id']
                        ];
                        DepositAccount::find($value['deposit_account_id'])->deposit($deposit_info);
                    }
                }
                
            }

            // $office
            // $msg = 'Repayment '. money($repayment,2) .' at ' . $office .' by ' . $by. ' ['.$payment.'].';
            
            
            // $loanPayload = [
            //     'date'=>'02-May',
            //     'amount'=>250000, 
            //     'payment_summary'=> ['interest'=>150000,'principal]];


            $loanPayload = ['date'=>$repayment_date->format('d-F'),'amount'=>$repayment, 'payment_summary'=>$payment_summary];
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

    public function bulkRepaymentV2(Request $request){

        $this->validateBulk($request->all())->validate();
        $repayment_date = Carbon::parse($request->repayment_date);
        $user = auth()->user()->id;
        $payment_method_id = $request->payment_method_id;
        $payment_info_template = [
            'payment_method_id'=>$payment_method_id,
            'repayment_date'=>$repayment_date,
            'notes'=> $request->notes,
            'receipt_number'=>$request->receipt_number,
            'paid_by'=>$user,
            'office_id'=>$request->office_id
        ]; 
        \DB::beginTransaction();
        try{
            $total_loan_payment = 0;
            $total_deposit_payment = 0;
            $payment_summary = [
                'interest_paid'=>0,
                'principal_paid'=>0,
                'total_paid'=>0,
            ];
            foreach($request->accounts as $item){
                $payment_info = $payment_info_template;
                $payment_info['amount'] = (float) $item['loan']['amount'];
                $total_loan_payment += $payment_info['amount'];
                $account_payment = LoanAccount::find( (int) $item['loan']['loan_account_id'])->payV2($payment_info);
                $payment_summary['interest_paid'] += $account_payment['interest_paid'];
                $payment_summary['principal_paid'] += $account_payment['principal_paid'];
                $payment_summary['total_paid'] += $account_payment['total_paid'];
                if (array_key_exists('deposits', $item)) {
                    $dep_accounts = Client::fcid($item['client_id'])->deposits;
                    foreach ($item['deposits'] as $deps) {
                        $payment_info = $payment_info_template;
                        $payment_info['amount'] = (float) $deps['amount'];
                        $payment_info['user_id'] = $user;
                        $total_deposit_payment += (float) $deps['amount'];
                        $deposit_account = $dep_accounts->where('deposit_id',$deps['deposit_id'])->first();
                        $deposit_account->deposit($payment_info);
                    }
                }
            }

            $loanPayload = ['date'=>$repayment_date->format('d-F'),'amount'=>$total_loan_payment,'summary'=>$payment_summary];
            event(new LoanAccountPayment($loanPayload, $request->office_id, $user , $payment_method_id));
            $has_deposit = $total_deposit_payment > 0;
            if ($has_deposit) {
                $depositPayload = ['date'=>$repayment_date->format('d-F'),'amount'=>$total_deposit_payment];
                event(new DepositTransaction($depositPayload, $request->office_id, $user, $payment_method_id, 'deposit'));
            }

            \DB::commit();
            return response()->json(['msg'=>'Payments Successfully Posted'],200);
        }catch(Exception $e){
            Log::alert($e->getMessage());
            return response()->json(['msg'=>'Something went wrong'],500);
        }

    }

    public function validateBulk(array $array){
        $hasDeposit = count($array['accounts'][0]['deposits']) > 0;
        $rules = [
            'office_id' => ['required','exists:offices,id'],
            'receipt_number'=>['required','unique:receipts,receipt_number'],
            'repayment_date'=>['required','date','before:tomorrow'],
            'accounts.*.loan.loan_account_id' =>['required', 'numeric','exists:loan_accounts,id',new AccountMustBeActive],
            'accounts.*.loan.amount'=>['required','gt:0','bulk_maximum_loan_repayment:accounts.*.loan.amount'],
            'accounts.*.loan.repayment_date'=>['required','date', new PreventFutureDate(),'bulk_prevent_previous_loan_repayment_date','bulk_on_or_before_disbursement_date'],
            'payment_method_id'=>['required', new PaymentMethodList]
        ];
        if($hasDeposit){
            $rules = [
                'office_id' => ['required','exists:offices,id'],
                'receipt_number'=>['required','unique:receipts,receipt_number'],
                'repayment_date'=>['required','date','before:tomorrow'],
                'accounts.*.loan.loan_account_id' =>['required', 'numeric','exists:loan_accounts,id',new AccountMustBeActive],
                'accounts.*.loan.amount'=>['required','bulk_maximum_loan_repayment:accounts.*.loan.amount'],
                'accounts.*.loan.repayment_date'=>['required','date', new PreventFutureDate(),'bulk_prevent_previous_loan_repayment_date','bulk_on_or_before_disbursement_date'],
                'payment_method_id'=>['required', new PaymentMethodList],

                'accounts.*.deposits.*.amount'=>['sometimes','nullable','gte:0','bulk_below_minimum_deposit_amount:accounts.*.deposit.*.amount'],
                'accounts.*.deposits.*.repayment_date'=>['required_with:accounts.*.deposits.*.amount','nullable','date', new PreventFutureDate,'bulk_prevent_previous_deposit_transaction_date:accounts.*.deposit.*.repayment_date'],

            ];
        }

        $messages = [
            'accounts.*.loan.amount.required'=>'Payment amount is required',
            'accounts.*.deposit.*.amount.gte'=>'Deposit must be greater than 0',
            
            
        ];

        return Validator::make($array,$rules,$messages);
    }
}
