<?php

namespace App\Http\Controllers;

use App\Loan;
use App\Client;
use App\Office;
use Carbon\Carbon;
use App\LoanAccount;
use App\PaymentMethod;
use Illuminate\Http\Request;
use App\Events\LoanDisbursed;
use App\Rules\DateIsWorkingDay;
use App\Rules\LoanAmountModulo;
use App\Rules\MaxLoanableAmount;
use App\Rules\PaymentMethodList;
use App\Events\BulkLoanDisbursed;
use Faker\Provider\zh_TW\Payment;
use App\Rules\HasNoUnusedDependent;
use App\Rules\HasNoPendingLoanAccount;
use App\Rules\ClientHasActiveDependent;
use App\Rules\LoanAccountCanBeApproved;
use App\Rules\LoanAccountCanBeDisbursed;
use Illuminate\Support\Facades\Validator;
use App\Rules\ClientHasAvailableDependent;

class LoanAccountController extends Controller
{

    public function __construct(){
        
        $this->middleware('permission:view_loan_account', ['only' => ['account']]);
        $this->middleware('permission:approve_loan', ['only' => ['bulkApproveForm']]);
        $this->middleware('permission:disburse_loan', ['only' => ['bulkDisburseForm','preDisbursementList','disburse']]);

    }


    public function index(Request $request){
        $client = Client::select('client_id','firstname','lastname')->where('client_id',$request->client_id)->firstOrFail();
        return view('pages.create-client-loan',compact('client'));
    }

    public function loanCreation(){
        $products = Loan::active();
        return response()->toJson(['data'=>array('loans'=>$products)],200);
    }

    public function calculate(Request $request){
        $this->validator($request->all())->validate();

        $client = Client::where('client_id',$request->client_id)->first();
        $loan =  Loan::find($request->loan_id);
        
        $fees = $loan->fees;
        $total_deductions = 0;

        $loan_amount = (double) $request->amount;
        $number_of_installments = $request->number_of_installments;
        $fee_repayments = array();
        $dependents = null;
        $unit_of_plan = 2;
        
        $dependents = $client->unUsedDependent()->pivotList();
        
        foreach($loan->fees as $fee){
            
            $fee_amount = $fee->calculateFeeAmount($loan_amount, $number_of_installments,$loan,$dependents);
            $total_deductions += $fee_amount;
            $fee_repayments[] = array(
                'name'=>$fee->name,
                'amount'=>$fee_amount
            );
        }
        
        $disbursed_amount = $loan_amount - $total_deductions;
        $annual_rate = (double) $loan->annual_rate;
        $start_date = $request->first_payment;
        
        $loan_interest_rate = Loan::rates($loan->id)->where('installments',$number_of_installments)->first()->rate;
        
        $data = array(
            'product' => $loan->code,
            'principal'=>$loan_amount,
            'annual_rate'=>$annual_rate,
            'interest_rate'=>$loan_interest_rate,
            'monthly_rate'=>(double) $loan->monthly_rate,
            'interest_interval'=>$loan->interest_interval,
            'disbursement_date'=>$loan->disbursement_date,
            'term'=>$loan->installment_method,
            'term_length'=>$number_of_installments,
            'start_date'=>$start_date,
            'office_id'=>$client->office->id
        );

        // $data = [
        //     'principal'=>10000,
        // ]
        
        $calculator = LoanAccount::calculate($data);
        

        //dependent on calculator result.
        $data = array(
            'amount'=>$loan_amount,
            'client'=>Client::where('client_id',$request->client_id)->get(['client_id','firstname','lastname'])->makeHidden(['active_dependent']),
            
            'principal'=>$loan_amount,
            'formatted_principal'=>money($request->amount,2),
            
            'interest'=>$calculator->total_interest,
            'formatted_interest'=>money($calculator->total_interest,2),

            'total_loan_amount'=>$calculator->total_loan_amount,
            'formatted_total_loan_amount'=>money($calculator->total_loan_amount,2),
            
            'installments'=>$calculator->installments,
            'loan'=>$loan->get('name'),
            'fees'=>$fee_repayments,
            
            'total_deductions'=>$total_deductions,
            'formatted_total_deductions'=>money($total_deductions,2),
            
            'disbursement_amount'=>$disbursed_amount,
            'formatted_disbursement_amount'=>money($disbursed_amount,2),
            
            'number_of_installments' => $number_of_installments,
            'start_date'=>$calculator->start_date,
            'end_date'=>$calculator->end_date,
        );
        // dd($data);
        return response()->json(['data'=>$data],200);

    }

    public function validator(array $data,$for_update=false){
        
        if(!$for_update){
            $rules = [
                'loan_id'=>'required|exists:loans,id',
                'client_id'=>['required','exists:clients,client_id',new HasNoUnusedDependent,new HasNoPendingLoanAccount],
                'amount'=>['required',new LoanAmountModulo($data['loan_id']), new MaxLoanableAmount($data['loan_id'])],
                // 'disbursement_date'=>['required','date', new DateIsWorkingDay],
                
                'first_payment'=>['required','date','after_or_equal:disbursement_date', new DateIsWorkingDay],
                'number_of_installments'=>'required|gt:0|integer',
                'interest_rate'=>'required', 
            ];
            return Validator::make(
                    $data,
                    $rules,
                );
        }
    }

    
    public function createLoan(Request $request){
        $this->validator($request->all())->validate();
        $client = Client::where('client_id',$request->client_id)->first();
        $loan =  Loan::find($request->loan_id);
        
        $fees = $loan->fees;
        $total_deductions = 0;

        $loan_amount = (double) $request->amount;
        $number_of_installments = $request->number_of_installments;
        $number_of_months = Loan::rates()->where('code',$loan->code)->first()->rates->where('installments',$number_of_installments)->first()->number_of_months;
        // dd($number_of_months);
        $fee_repayments = array();
        
        $dependents = $client->unUsedDependent()->pivotList();
        foreach($loan->fees as $fee){
            $fee_amount = $fee->calculateFeeAmount($loan_amount, $number_of_installments,$loan,$dependents);
            $total_deductions += $fee_amount;
            $fee_repayments[] = (object)[
                'id'=>$fee->id,
                'name'=>$fee->name,
                'amount'=>$fee_amount
            ];
        }
        
        $disbursed_amount = $loan_amount - $total_deductions;
        $annual_rate = $loan->annual_rate;
        $start_date = $request->first_payment;

        //get loan rates via loan and installment length
        $loan_interest_rate = Loan::rates($loan->id)->where('installments',$number_of_installments)->first()->rate;

        $data = array(
            'product' => $loan->code,
            'principal'=>$loan_amount,
            'monthly_rate'=>$loan->monthly_rate,
            'annual_rate'=>$annual_rate,
            'interest_rate'=>$loan_interest_rate,
            'monthly_rate'=>$loan->monthly_rate,
            'interest_interval'=>$loan->interest_interval,
            'term'=>$loan->installment_method,
            'term_length'=>$number_of_installments,
            'disbursement_date'=>$request->disbursement_date,
            'start_date'=>$start_date,
            'office_id'=>$client->office->id
        );
        
        
        $calculator = LoanAccount::calculate($data);
        
        //dependent on calculator result.

        \DB::beginTransaction();
        try{
            $loan_acc = $client->loanAccounts()->create([
                'loan_id'=>$loan->id,
                'amount'=>$loan_amount,
                'principal'=>$loan_amount,
                'interest'=>$calculator->total_interest,
                'total_loan_amount'=>$calculator->total_loan_amount,
                'interest_rate'=>$loan_interest_rate,
                'number_of_months'=>$number_of_months,
                'number_of_installments'=>$number_of_installments,

                'total_deductions'=>$total_deductions,
                'disbursed_amount'=>$disbursed_amount, //net disbursement
                
                'total_balance'=>$loan_amount + $calculator->total_interest,
                'principal_balance'=>$loan_amount,
                'interest_balance'=>0,

                'disbursement_date'=>$calculator->disbursement_date,
                'first_payment_date'=>$calculator->start_date,
                'last_payment_date'=>$calculator->end_date,
                'created_by'=>auth()->user()->id,
            ]);;
        
            $this->createFeePayments($loan_acc,$fee_repayments);
            
            $this->createInstallments($loan_acc,$calculator->installments);
            $client->unUsedDependent()->update(['status'=>'For Loan Disbursement','loan_account_id'=>$loan_acc->id]);
            // $loan_acc->account()->create([
            //     'status'=>'Pending Approval',
            //     'client_id'=>$client->client_id
            // ]);
            \DB::commit();
            
            
            return response()->json(['msg'=>'Loan Account successfully created'],200);
        }catch(\Exception $e){
            return response()->json(['msg'=>$e->getMessage()],500);

        }

        

        
    }

    public function bulkCreateLoan(Request $request){
        $this->bulkValidator($request->all())->validate();
        \DB::beginTransaction();
        try{
            foreach($request->accounts as $item){
                $client_id = $item['client_id'];
                
                $client = Client::where('client_id',$client_id)->first();
                $loan =  Loan::find($request->loan_id);
                $fees = $loan->fees;
                $total_deductions = 0;
        
                $loan_amount = (int) $item['amount'];
                
                $number_of_installments = $request->number_of_installments;
                $number_of_months = Loan::rates()->where('code',$loan->code)->first()->rates->where('installments',$number_of_installments)->first()->number_of_months;
                $fee_repayments = array();
                
                $dependents = $client->unUsedDependent()->pivotList();
                foreach($loan->fees as $fee){
                    $fee_amount = $fee->calculateFeeAmount($loan_amount, $number_of_installments,$loan,$dependents);
                    $total_deductions += $fee_amount;
                    $fee_repayments[] = (object)[
                        'id'=>$fee->id,
                        'name'=>$fee->name,
                        'amount'=>$fee_amount
                    ];
                }
                
                $disbursed_amount = $loan_amount - $total_deductions;
                $annual_rate = $loan->annual_rate;
                $start_date = $request->first_payment;
        
                //get loan rates via loan and installment length
                $loan_interest_rate = Loan::rates($loan->id)->where('installments',$number_of_installments)->first()->rate;
        
                $data = array(
                    'principal'=>$loan_amount,
                    'annual_rate'=>$annual_rate,
                    'interest_rate'=>$loan_interest_rate,
                    'interest_interval'=>$loan->interest_interval,
                    'term'=>$loan->installment_method,
                    'term_length'=>$number_of_installments,
                    'disbursement_date'=>$request->disbursement_date,
                    'start_date'=>$start_date,
                    'office_id'=>$client->office->id
                );
                
                
                $calculator = LoanAccount::calculate($data);
                
                //dependent on calculator result.
        
                
                $loan_acc = $client->loanAccounts()->create([
                    'loan_id'=>$loan->id,
                    'amount'=>$loan_amount,
                    'principal'=>$loan_amount,
                    'interest'=>$calculator->total_interest,
                    'total_loan_amount'=>$calculator->total_loan_amount,
                    'interest_rate'=>$loan_interest_rate,
                    'number_of_months'=>$number_of_months,
                    'number_of_installments'=>$number_of_installments,

                    'total_deductions'=>$total_deductions,
                    'disbursed_amount'=>$disbursed_amount, //net disbursement
                    
                                
                    'total_balance'=>$loan_amount + $calculator->total_interest,
                    'principal_balance'=>$loan_amount,
                    'interest_balance'=>0,

                    'disbursement_date'=>$calculator->disbursement_date,
                    'first_payment_date'=>$calculator->start_date,
                    'last_payment_date'=>$calculator->end_date,
                    'created_by'=>auth()->user()->id,
                ]);
            
                $this->createFeePayments($loan_acc,$fee_repayments);
                
                $this->createInstallments($loan_acc,$calculator->installments);
                $client->unUsedDependent()->update(['status'=>'For Loan Disbursement','loan_account_id'=>$loan_acc->id]);
                $loan_acc->account()->create([
                    'client_id'=>$client->client_id,
                    'status'=>'Pending Approval'
                ]);
            }
            \DB::commit();
            return response()->json(['msg'=>'Loan Account successfully created'],200);
        }catch(\Exception $e){
            return response()->json(['msg'=>$e->getMessage()],500);

        }
    }

    public function bulkValidator(array $data){
        $rules = [
            'loan_id'=>'required|exists:loans,id',
            'accounts.*.client_id'=>['required','exists:clients,client_id','bulk_has_no_unused_dependent'],
            // 'accounts.*.client_id'=>['required','exists:clients,client_id'],
            'accounts.*.amount'=>['required','bulk_with_loanable_amount',new LoanAmountModulo],
            'disbursement_date'=>'required|date',
            'first_payment'=>'required|date|after_or_equal:disbursement_date',
            'number_of_installments'=>'required|gt:0|integer',
            'accounts' => 'required'
        ];
        return Validator::make(
            $data,
            $rules,
            [
                'accounts.*.amount.required'=>'Loan amount is required'
            ]
        );
    }

    public function createFeePayments($loan_acc, $fee_repayments){
        $fee_payments = array();
        foreach($fee_repayments as $fee){
            $fee_payments[] = $loan_acc->feePayments()->create([
                'fee_id'=> $fee->id,
                'amount'=> $fee->amount,
            ]);
        }
        return $fee_payments;
    }
    
    public function payFeePayments($fees,$payment_method_id,$disbursed_by,$transaction_id){
        $x = 1;
        $now  = Carbon::now();        
        foreach($fees as $fee){
            $res = $fee->update([
                'loan_account_disbursement_transaction_id'=>$transaction_id,
                'transaction_id'=>$fee->generateTransactionID($x),
                'paid_at'=>Carbon::now(),
                'paid_by'=>$disbursed_by,
                'payment_method_id'=>$payment_method_id,
                'paid'=>true,
                'created_at'=>$now
            ]);
            $x++;
        }
        return $res;
    }
    public function createSchedules(array $data){
        
        $calculator = LoanAccount::calculate($data);
    }
    public function createInstallments($loan_acc,object $installments){
        $x=0;   //skip first installment
        $list = array();
        foreach($installments as $item){
            if ($x>0) {
                $list[] = $loan_acc->installments()->create([
                    'installment'=>$item->installment,
                    'date'=>$item->date,
                    'original_principal'=>$item->principal,
                    'original_interest'=>$item->interest,
                    'principal'=>$item->principal,
                    'interest'=>$item->interest,
                    
                    'principal_due'=>$item->principal,
                    'interest_due'=>$item->interest_due,
                    'amount_due'=>$item->amount_due,
                    'amortization'=>$item->amortization,
                    'principal_balance'=>$item->principal_balance,
                    'interest_balance'=>$item->interest_balance,
                    'interest_days_incurred'=>$item->interest_days_incurred
                ]);
            }
            $x++;
        }
        return $list;
    }

    public function clientLoanList($client_id){
        $client =  Client::with(['loanAccounts'=>function($q){
            return $q->orderBy('created_at','desc');
        }])->select('firstname','lastname','client_id')->where('client_id',$client_id)->firstOrFail();

        return view('pages.client-loans-list',compact('client'));
    }

    public function disburse($loan_id=null){
        
        if($loan_id!=null){
            $id = $loan_id; 
        }else{
            $id = $this->id;
        }
        $account = LoanAccount::findOrFail($id);
        $fee_payments = $account->feePayments;
        $payment_method_id = 1;
        $disbursed_by = auth()->user()->id;
        \DB::beginTransaction();
        
        try {
            $transaction_id = $account->generateDisbursementTransactionNumber();
            $this->payFeePayments($fee_payments,$payment_method_id,$disbursed_by,$transaction_id);

            $account->update([
                'disbursed_at'=>Carbon::now(),
                'status'=>'Active',
                'disbursed'=>true
            ]);
            
            $account->disbursement()->create([
                'transaction_id'=>$transaction_id,
                'disbursed_amount'=>$account->disbursed_amount,
                'disbursed_by'=>auth()->user()->id,
                'payment_method_id'=>$payment_method_id 
            ]);
        
        $account->updateStatus();
        $account->dependents->update([
            'status'=>'Used',
            'loan_account_id'=>$account->id,
            'activated_at'=>Carbon::now(),
            'expires_at'=>Carbon::now()->addDays(env('INSURANCE_MATURITY_DAYS'))
            ]);
        
        $account->accountable->update([
            'status'=>$account->status
        ]);
        
        \DB::commit();
        return redirect()->back();
        }catch(\Exception $e){
            return response()->json(['msg'=>$e->getMessage()],500);
    
        }
        return redirect()->back();
    }

    public function approve($loan_id=null){
        
        if($loan_id!=null){
            $id = $loan_id;
        }else{
            $id = $this->id;
        }
        \DB::beginTransaction();
        
        try{
            $account = LoanAccount::findOrFail($id);
            $account->approve(auth()->user()->id);
            \DB::commit();
            return redirect()->back();
        }catch(\Exception $e){ 
            return response()->json(['msg'=>$e->getMessage()],500);
        }

        
    }
    public function account(Request $request, $client_id,$loan_id){

        if($request->wantsJson()){
            $account  = LoanAccount::find($loan_id);
            $loan_type = $account->type->name;
            $account_1 = clone $account;
            $activity = $account_1->transactions()->orderBy('transaction_date','DESC')->get();
            $pre_term_amount = $account_1->preTermAmount();
            $installment_repayments = \DB::table('loan_account_installment_repayments');
            $installments = \DB::table('loan_account_installments')
                                ->select('installment','original_principal','original_interest','date','amortization','principal','interest','principal_due','interest_due','amount_due',
                                \DB::raw("IF(paid=false, (
                                    CASE 
                                        WHEN `date` > DATE(CURRENT_TIMESTAMP) THEN 'Not Due'
                                        WHEN `date` = DATE(CURRENT_TIMESTAMP) THEN 'Due'
                                        WHEN `date` < DATE(CURRENT_TIMESTAMP) THEN 'In Arrears'
                                        END),'Paid') as status"),
                                \DB::raw('SUM(installment_repayments.interest_paid) as interest_paid'),
                                \DB::raw('SUM(installment_repayments.principal_paid) as principal_paid'),
                                \DB::raw('SUM(installment_repayments.total_paid) as total_paid'),
                            
                                )
                                ->leftJoinSub($installment_repayments,'installment_repayments',function($join){
                                    $join->on('installment_repayments.loan_account_installment_id','loan_account_installments.id');
                                })
                                ->where('loan_account_id',$loan_id)
                                ->orderBy('installment','asc')
                                ->groupBy('loan_account_installments.id')
                                ->get();
            $fees = $account_1->feePayments;
            $total_paid = $account_1->totalPaid();
            $amount_due = $account_1->amountDue();
            
            $client = Client::select('firstname','lastname','client_id')->where('client_id',$client_id)->first();
            
            // dd($amount_due);
            return response()->json([
                'account'=>$account,
                'loan_type'=>$loan_type,
                'client'=>$client,
                'installments'=>$installments,
                'activity'=>$activity,
                'fees'=>$fees,
                'total_paid'=>$total_paid,
                'pre_term_amount'=>$pre_term_amount,
                'amount_due'=>$amount_due
            ],200);
        }
        
        $account =  LoanAccount::findOrFail($loan_id);
        
        $client = Client::where('client_id',$client_id)->firstOrFail();
        return view('pages.client-loan-account',compact('account','client'));
    }

    // public function wantsJson(Request $request){
    //     return $request->wantsJson();
    // }
    
    public function bulkCreateForm(){
        return view('pages.bulk.create-loan-accounts');
    }

    public function bulkApproveForm(){
        return view('pages.bulk.approve-loan-accounts');
    }
    public function bulkDisburseForm(){
        return view('pages.bulk.disburse-loan-accounts');
    }


    
    public function bulkLoanTransact(Request $request, $type){
        if($type=='approve'){
            $rules = [
                'accounts.*' => ['required', 'exists:loan_accounts,id',new LoanAccountCanBeApproved]
            ];
            Validator::make(
                $request->all(),
                $rules,
            )->validate();
    
            \DB::beginTransaction();
            $ctr = 0;
            try {
                foreach($request->accounts as $account){
                    LoanAccount::find($account)->approve(auth()->user()->id);
                    $ctr++;
                }
                \DB::commit();
                return response()->json(['msg'=>'Account/s ('.$ctr.'/'.count($request->accounts).') Succesfully Approved'],200);
            }catch(\Exception $e){ 
                return response()->json(['msg'=>$e->getMessage()],500);
            }
        }elseif($type=="disburse"){
            $rules = [
                'office_id' =>'required|exists:offices,id',
                'disbursement_date'=>'required|date',
                'first_repayment_date'=>'required|after_or_equal:disbursement_date',
                'cv_number'=>'required|unique:check_vouchers,check_voucher_number',
                'accounts.*' => ['required', 'exists:loan_accounts,id',new LoanAccountCanBeDisbursed],
                'payment_method' =>['required',new PaymentMethodList]
            ];
            $msgs = [
                'office_id.required' => 'Branch level is required'
            ];
            
            Validator::make(
                $request->all(),
                $rules,
                $msgs
            )->validate();
            \DB::beginTransaction();
            
            try {
                
                $payment_info = [
                    'disbursement_date'=>$request->disbursement_date,
                    'first_repayment_date'=>$request->first_repayment_date,
                    'payment_method_id'=>$request->payment_method,
                    'office_id'=>$request->office_id,
                    'disbursed_by'=>auth()->user()->id,
                    'cv_number'=>$request->cv_number
                ];
                $disbursed_amount = 0;
                $bulk_disbursement_id = sha1(time());
                $first_payment = null;
                foreach ($request->accounts as $account) {
                    
                    $account =  LoanAccount::find($account);
                    //get first payment of 1st loan account
                    
                    $account->disburse($payment_info,true,$bulk_disbursement_id);
                    if (is_null($first_payment)) {
                        $first_payment = $account->installments->first()->date->format('d-F');
                    }
                    $disbursed_amount+= $account->disbursed_amount;
                    // dd($disbursed_amount);    
                }
                
                
                $office = Office::select('name','level')->find($request->office_id)->name;
                $by = auth()->user()->fullname;
                $payment = PaymentMethod::find($request->payment_method)->name;
                $msg = 'Disbursed '. money($disbursed_amount,2) .' at ' . $office .' by ' . $by. ' ['.$payment.'].';
                $payload = [
                    'msg'=>$msg,
                    'office_id'=>$request->office_id,
                    'amount'=>(string) $disbursed_amount,
                    'date'=>$first_payment,
                ];
                event(new LoanDisbursed($payload));
    
                \DB::commit();
                return response()->json(['msg'=>'Loan Account successfully created','bulk_disbursement_id'=>$bulk_disbursement_id], 200);
            } catch (\Exception $e) {
                return response()->json(['msg'=>$e->getMessage()], 500);
            }
        }elseif($type=='create'){
            return $this->bulkCreateLoan($request);
        }
        
        
    }


    public function preDisbursementList(Request $request){
        $rules = [
            'office_id' =>'required|exists:offices,id',
            'type'=> []
        ];
        Validator::make(
            $request->all(),
            $rules,
        )->validate();
        
        $list = LoanAccount::bulkList($request->type,$request->office_id)->get();
        return response()->json([
            'msg'=>'Success',
            'list'=>$list
        ],200);
    }
    public function pendingLoans(Request $request){

        $rules = [
            'office_id' =>'required|exists:offices,id'
        ];
        Validator::make(
            $request->all(),
            $rules,
        )->validate();
        
        $office = Office::find($request->office_id);

        if(is_null($request->loan_id)){
            $list = $office->getLoanAccounts('pending')->each->append('basic_client','mutated');
        }else{
            $list = $office->getLoanAccounts('pending',$request->loan_id)->each->append('basic_client','mutated');
        }
        return response()->json([
            'msg'=>'Success',
            'list'=>$list
        ],200);
        
    }
    public function approvedLoans(Request $request){

        $rules = [
            'office_id' =>'required|exists:offices,id'
        ];
        Validator::make(
            $request->all(),
            $rules,
        )->validate();
        
        $list = LoanAccount::bulkList('Approved',$request->office_id)->get();
        return response()->json([
            'msg'=>'Success',
            'list'=>$list
        ],200);
        
    }

}
