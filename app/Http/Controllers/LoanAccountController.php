<?php

namespace App\Http\Controllers;

use App\Loan;
use App\Client;
use App\Office;
use Carbon\Carbon;
use App\LoanAccount;
use App\PaymentMethod;
use App\Rules\CreditLimit;
use Illuminate\Http\Request;
use App\Events\LoanDisbursed;
use App\Rules\DateIsWorkingDay;
use App\Rules\LoanAmountModulo;
use App\Rules\ClientHasBusiness;
use App\Rules\MaxLoanableAmount;
use App\Rules\PaymentMethodList;
use App\Events\BulkLoanDisbursed;
use Faker\Provider\zh_TW\Payment;
use Illuminate\Support\Facades\DB;
use App\Rules\HasNoUnusedDependent;
use App\Rules\HasNoPendingLoanAccount;
use App\Rules\ClientHasActiveDependent;
use App\Rules\LoanAccountCanBeApproved;
use App\Rules\LoanAccountCanBeDisbursed;
use Illuminate\Support\Facades\Validator;
use App\Rules\ClientHasAvailableDependent;
use App\Rules\PreventTopupFromLastTransaction;
use Illuminate\Validation\ValidationException;

class LoanAccountController extends Controller
{

    public function __construct(){
        
        $this->middleware('permission:view_loan_account', ['only' => ['account']]);
        $this->middleware('is_client_loan', ['only' => ['account']]);
        $this->middleware('permission:approve_loan', ['only' => ['bulkApproveForm']]);
        $this->middleware('permission:disburse_loan', ['only' => ['bulkDisburseForm','preDisbursementList','disburse']]);

    }


    public function index(Request $request){
        $client = Client::with(['businesses','household_income'])->select('client_id','firstname','lastname')->where('client_id',$request->client_id)->firstOrFail();
        return view('pages.create-client-loan',compact('client'));
    }

    public function loanCreation(){
        $products = Loan::active();
        return response()->toJson(['data'=>array('loans'=>$products)],200);
    }

    public function calculate(Request $request){
        
        if($request->has('account')){
            $this->validator($request->all(),true)->validate();    
        }else{
            $this->validator($request->all())->validate();
        }
        
        
        
        $client = Client::where('client_id',$request->client_id)->first();
        $loan =  Loan::find($request->loan_id);
        
        $fees = $loan->fees;
        $total_deductions = 0;

        $loan_amount = (double) $request->amount;
        $number_of_installments = $request->number_of_installments;
        $fee_repayments = array();
        $dependents = null;
        $unit_of_plan = 2;
        if ($request->status == 'Pending Approval') {
            $dependents =  $client->dependents->where('loan_account_id',$request->loan_account_id)->first()->pivotList();
        }else{
            $dependents = $client->unUsedDependent()->pivotList();
        }
        
        
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
            'disbursement_date'=>$request->disbursement_date,
            'term'=>$loan->installment_method,
            'term_length'=>$number_of_installments,
            'start_date'=>$start_date,
            'office_id'=>$client->office->id,
            'client_id' => $request->client_id
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
        
        if($for_update){
            
            $rules = 
            [
                'credit_limit' => new CreditLimit($data['credit_limit'],$data['amount']),
                'loan_id'=>'required|exists:loans,id',
                'client_id'=>['required','exists:clients,client_id'],
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
        
        
        $rules = [
            
            'credit_limit' => new CreditLimit($data['credit_limit'],$data['amount']),
            'loan_id'=>'required|exists:loans,id',
            'client_id'=>['required','exists:clients,client_id',new HasNoUnusedDependent,new HasNoPendingLoanAccount],
            'amount'=>['required',new LoanAmountModulo($data['loan_id']), new MaxLoanableAmount($data['loan_id']),new ClientHasBusiness($data['client_id'])],
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
                'interest_balance'=>$calculator->total_interest,

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
                    'product' => $loan->code,
                    'monthly_rate'=>$loan->monthly_rate,
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
            'accounts.*.amount'=>['required','bulk_with_loanable_amount',new LoanAmountModulo($data['loan_id'])],
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
    
    public function payFeePayments($fees,array $data){
        $x = 1;
        $now  = Carbon::now();        
        foreach($fees as $fee){
            $transaction_number = 'F'.str_replace('.','',microtime(true));
            $res = $fee->update([
                'loan_account_disbursement_transaction_id'=>$data['transaction_id'],
                'transaction_number'=>$transaction_number,
                'repayment_date'=>$data['repayment_date'],
                'office_id' => $data['office_id'],
                'paid_by'=>$data['disbursed_by'],
                'payment_method_id'=>$data['payment_method_id'],
                'paid'=>true,
                'reverted'=>false,
                'reverted_by' => null,
                'reverted_at' => null,
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

    public function disburse(Request $request,$loan_id=null){
        
        $request->validate([
            'office_id' => 'required|exists:offices,id',
            'disbursement_date'=>'required|date|before:tomorrow',
            'first_repayment_date'=>'required|after_or_equal:disbursement_date',
            'cv_number'=>'required|unique:check_vouchers,check_voucher_number',
            'accounts.*' => ['required', 'exists:loan_accounts,id',new LoanAccountCanBeDisbursed],
            'paymentSelected' =>['required',new PaymentMethodList]
        ]);
        if($loan_id!=null){
            $id = $loan_id; 
        }else{
            $id = $this->id;
        }
        $account = LoanAccount::findOrFail($id);
        $fee_payments = $account->feePayments;
        
        $feePayments =[
            'fees' => $fee_payments,
            'payment_method_id' => $request->paymentSelected,
            'disbursed_by' => auth()->user()->id,
            'repayment_date' => $request->disbursement_date,
            'office_id' => $request->office_id,
            'transaction_id' => $account->generateDisbursementTransactionNumber()
        ];
        
        $disbursed_by = auth()->user()->id;
        $disbursement_date = Carbon::parse($request->disbursement_date)->startOfDay();
        $start_date = Carbon::parse($request->first_repayment_date)->startOfDay();
        $original_disbursement_date = $account->disbursement_date;
        $diff = $original_disbursement_date->diffInDays($disbursement_date, false);
        \DB::beginTransaction();
        
        try {

            if ($diff != 0) {
                $account->disbursement_date = $disbursement_date;
                $account->save();
            }

            $original_start_date_date = $account->installments()->first()->date;
            $diff = $original_start_date_date->diffInDays($start_date, false);
            
            if ($diff != 0) {
                //create new installments
                $account->installments()->delete();
                
                $product = $account->product;
                
                $annual_rate = $product->annual_date;
                $monthly_rate = $product->monthly_rate;
                $loan_interest_rate = Loan::rates($account->product->id)->where('installments', $account->number_of_installments)->first()->rate;
                
                $data = array(
                    'product' => $product->code,
                    'principal'=>$account->amount,
                    'annual_rate'=>$annual_rate,
                    'monthly_rate'=>$monthly_rate,
                    'interest_rate'=>$loan_interest_rate,
                    'interest_interval'=>$product->interest_interval,
                    'term'=>$product->installment_method,
                    'term_length'=>$account->number_of_installments,
                    'disbursement_date'=>$disbursement_date,
                    'start_date'=>$start_date,
                    'office_id'=>$request->office_id,
                    'client_id' => $request->client_id
                );
                  
                $calculator = LoanAccount::calculate($data);
                
                $account->createInstallments($account, $calculator->installments);
                
            }



            $account->payFeePayments($feePayments);

            

            $account->update([
                'disbursed_at'=>Carbon::now(),
                'status' => $account->overdue()->total == 0 ? 'Active' : 'In Arrears',
                'disbursed'=>true,
                'disbursed_by'=>auth()->user()->id,
            ]);
            
            $account->disbursement()->create([
                'transaction_id'=>$feePayments['transaction_id'],
                'disbursed_amount'=>$account->disbursed_amount,
                'disbursed_by'=>auth()->user()->id,
                'payment_method_id'=>$feePayments['payment_method_id'] 
            ]);
            
        $account->updateStatus();
        
        $account->dependents->update([
            'status'=>'Used',
            'loan_account_id'=>$account->id,
            'activated_at'=>Carbon::parse($request->disbursement_date),
            'expires_at'=>Carbon::parse($request->disbursement_date)->addDays(env('INSURANCE_MATURITY_DAYS'))
        ]);
        
        
        \DB::commit();
        return response()->json(['msg' => "Loan Disburse Successfully"], 200);
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

    public function abandoned($loan_id=null){
        
        if($loan_id!=null){
            $id = $loan_id;
        }else{
            $id = $this->id;
        }
        \DB::beginTransaction();
        
        try{
            $account = LoanAccount::findOrFail($id);
            $account->abandoned();
            
            \DB::commit();
            return response()->json(['msg'=> 'Sucess!'],200);
        }catch(\Exception $e){ 
            return response()->json(['msg'=>$e->getMessage()],500);
        }

        
    }

    public function account(Request $request, $client_id,$loan_id){

    if($request->wantsJson()){
    $account  = LoanAccount::find($loan_id);
    $loan_type = $account->type->name;
    $loan_account = DB::table('loan_accounts')->select('id','status');
    $account_1 = clone $account;
    $activity = $account_1->transactions()->orderBy('transaction_date','DESC')->get();
    $pre_term_amount = $account_1->preTermAmount();
    $installment_repayments = \DB::table('loan_account_installment_repayments');
    $status = $account->status;
    $ctlp =  DB::table('loan_account_installments')
            ->where('loan_account_id', $loan_id)
            ->leftJoin('deposit_to_loan_installment_repayments', 'deposit_to_loan_installment_repayments.loan_account_installment_id', '=', 'loan_account_installments.id')
            ->groupBy('loan_account_installments.id')
            ->select(
            'installment',
            DB::raw('SUM(deposit_to_loan_installment_repayments.interest_paid) AS interest_paid'),
            DB::raw('SUM(deposit_to_loan_installment_repayments.principal_paid) AS principal_paid')
            )
            ->get();


    $installments= DB::table('loan_account_installments')
        ->where('loan_account_id', $loan_id)
        ->leftJoin('loan_account_installment_repayments', 'loan_account_installment_repayments.loan_account_installment_id', '=', 'loan_account_installments.id')
        ->groupBy('loan_account_installments.id')
        ->select(
            'installment',
            'penalty',
            'original_principal',
            'original_interest',
            'date','amortization',
            'principal','interest',
            'principal_due',
            'interest_due',
            'amount_due',
            DB::raw("IF(paid=false, (
                        CASE 
                            WHEN `date` > DATE(CURRENT_TIMESTAMP) THEN 'Not Due'
                            WHEN `date` = DATE(CURRENT_TIMESTAMP) THEN 'Due'
                            WHEN `date` < DATE(CURRENT_TIMESTAMP) THEN 'In Arrears'
                            END),'Paid') as status"),
            DB::raw('SUM(loan_account_installment_repayments.interest_paid) AS interest_paid'),
            DB::raw('SUM(loan_account_installment_repayments.penalty_paid) AS penalty_paid'),
            DB::raw('SUM(loan_account_installment_repayments.principal_paid) AS principal_paid')
        )
        ->orderBy('installment','asc')
        ->get();
    
    
    for($x = 0;$x < $installments->count();$x++){
        $installments[$x]->interest_paid += $ctlp[$x]->interest_paid;
        $installments[$x]->principal_paid += $ctlp[$x]->principal_paid;
        $installments[$x]->total_paid = $installments[$x]->principal_paid + $installments[$x]->interest_paid + $installments[$x]->penalty_paid;
    }
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
        
        // $request->office_id = $request->office_id ? $request->office_id : auth()->user()->office->id;
        
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

    public function editAccount($client_id, $loan_id){
        
        $client = Client::fcid($client_id);
        return view('pages.client-edit-loan-account', compact(['client_id','loan_id','client']));

    }

    public function getLoanAccount($client_id, $loan_id){

        $client = Client::fcid($client_id);
        $account = LoanAccount::with('product')->find($loan_id);
        $loan_accounts = DB::table('loan_accounts');
        $fees = $account->FeePayments;
        $installments= DB::table('loan_account_installments')

        ->where('loan_account_id', $loan_id)
        ->groupBy('loan_account_installments.id')
        ->select(
            'installment',
            'original_principal',
            'original_interest',
            'date','amortization',
            'principal','interest',
            'interest_balance',
            'principal_balance',
            'principal_due',
            'interest_due',
            'amount_due',
        )
        ->orderBy('installment','asc')
        ->get();
        
        return response()->json(
            [
                'installments' => $installments,
                'client' => $client,
                'loan_account' => $account,
                'fees' => $fees
            ]);

    }

    public function updateLoanAccount(Request $request,  $client_id,$loan_id){
        
        $loan_account = LoanAccount::find($loan_id);
        $client = Client::where('client_id',$request->client_id)->first();
        $loan =  Loan::find($request->loan_id);
        
        $fees = $loan->fees;
        $total_deductions = 0;

        $loan_amount = (double) $request->account['amount'];
        
        $number_of_installments = $request->account['number_of_installments'];
        $number_of_months = Loan::rates()->where('code',$loan->code)->first()->rates->where('installments',$number_of_installments)->first()->number_of_months;
        // dd($number_of_months);
        $fee_repayments = array();
        
        $dependents =  $client->dependents->where('loan_account_id',$loan_id)->first()->pivotList();
        
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
        $start_date = $request->account['first_payment'];

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
            'disbursement_date'=>$request->account['disbursement_date'],
            'start_date'=>$start_date,
            'office_id'=>$client->office->id
        );

        $calculator = LoanAccount::calculate($data);
        
        \DB::beginTransaction();
        try{
            $loan_account->update([
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
            
            $loan_account->feePayments()->delete();
            $this->createFeePayments($loan_account,$fee_repayments);
            $loan_account->installments()->delete();
            $this->createInstallments($loan_account,$calculator->installments);
            // $client->unUsedDependent()->update(['status'=>'For Loan Disbursement','loan_account_id'=>$loan_acc->id]);
            // $loan_acc->account()->create([
            //     'status'=>'Pending Approval',
            //     'client_id'=>$client->client_id
            // ]);
            \DB::commit();
            
            
            return response()->json(['msg'=>'Loan Account successfully updated'],200);
        }catch(\Exception $e){
            return response()->json(['msg'=>$e->getMessage()],500);

        }
    }


    public function bulkWriteoffList(Request $request){
        return view('pages.bulk.writeoff-loan-accounts');

    }

    public function writeoffAccount(Request $request){

        $rules = [
            'office_id' =>'required|exists:offices,id',
            'journal_voucher' => 'required',
            'date'=> 'before:now|required'

        ];

        $request->validate($rules);

        try {
            
        $loan_account = LoanAccount::findOrfail($request->loan_id);
        
        $loan_account->writeOffAccount($request->date,$request->office_id);
        $loan_account->writeoff->jv()->create([
            'journal_voucher_number'=>$request->journal_voucher,
            'transaction_date'=>$request->date,
            'office_id'=>$request->office_id
        ]);
        return response()->json(['msg' => 'success!']);
        } catch (\Exception $e) {
            return response()->json(['msg'=>$e->getMessage()],500);
        }

    }

    public function bulkWriteoffLoans(Request $request){
        $rules = [
            'office_id' =>'required|exists:offices,id',
            'journal_voucher' => 'required',
            'date'=> 'before:now|required'

        ];
        Validator::make(
            $request->all(),
            $rules,
        )->validate();

        \DB::beginTransaction();
        try {
            foreach($request->accounts as $item){
                $account = LoanAccount::find($item);
                $account->writeoffAccount($request->date,$request->office_id);
                $account->writeoff->jv()->create([
                    'journal_voucher_number'=>$request->journal_voucher,
                    'transaction_date'=>$request->date,
                    'office_id'=>$request->office_id
                ]);
            }

            
            \DB::commit();

            
            
            return response()->json(['msg'=>'Loan Accounts successfully written off'],200);

        } catch (\Exception $e) {
            return response()->json(['msg'=>$e->getMessage()],500);
        }
    }

    public function topUpCalculation(Request $request){
        
        
        $rules = [
            'office_id' =>'required|exists:offices,id',
            'disbursement_date'=> ['required', new PreventTopupFromLastTransaction($request->loan_id)],
            'amount' => 'required'
        ];
        $request->validate($rules);
        $loan_account = LoanAccount::findOrfail($request->loan_id);
        
        
        try {
            
            $data = array(
                'interest_balance' => $loan_account->interest_balance,
                'product' => $request->code,
                'principal'=>$request->amount,
                'monthly_rate'=>$request->interest_rate,
                'annual_rate'=>$request->annual_rate,
                'interest_rate'=>$request->interest_rate,
                'interest_interval'=>$request->interest_interval,
                'term'=>$request->term,
                'term_length'=>$request->number_of_installment,
                'disbursement_date'=>$request->disbursement_date,
                'start_date'=>$request->first_repayment_date,
                'office_id'=>$request->office_id,
            );
    
            
        
            $loan_account = LoanAccount::find($request->loan_id);
            
            $topup_data = $loan_account->topup($data);

            
        } catch (\Exception $e) {
            return response()->json(['msg'=>$e->getMessage()],500);
        }

        return response()->json($topup_data,200);

    }
    
    public function topUp(Request $request,$loan_id){
        
        $loan_account = LoanAccount::findOrfail($request->loan_id);
        
        $user = auth()->user()->id;
        

        
            $loan_account->update(
                [
                    'amount' => $loan_account->principal + $request->amount,
                    'principal' => $loan_account->principal + $request->amount,
                    'interest' => $loan_account->interest + $request->topup_interest,
                    'total_loan_amount' => $request->total_loan_amount,
                    'interest_balance' => $loan_account->interest_balance + $request->total_interest_balance,
                    'principal_balance' => $loan_account->principal + $request->amount,
                    'disbursed_amount' => $request->amount + $loan_account->disburse_amount
                ]
            );
    
            $loan_installments = $loan_account->installments;
            $ctr=0;
            
            $transaction_number = 'T'.str_replace('.','',microtime(true));

            $topup = $loan_account->loan_topup()->create([
                'payment_method_id' => 1,
                'transaction_number' => $transaction_number,
                'loan_account_id' => $loan_account->id,
                'interest_topup' =>  $request->topup_interest, 
                'principal_topup' => $request->amount,
                'total_topup' => $request->total_loan_amount - $loan_account->total_loan_amount,
                'disbursed_by' => $user,
                'office_id' => $request->office_id,
                'topup_date' => $request->disbursement_date
            ]);
            
            
            foreach ($loan_installments as $installment) {
               
                $ctr++;
                $topup_installment = $request->installments[$ctr];
                
                $topup_installment['loan_account_installment_id'] = $installment->id;
                
                $original_interest = $topup_installment['add_on_interest'] + $installment->original_interest;
                
                $installment->update([
                    'original_interest' =>  $original_interest,
                    'interest' => $topup_installment['interest'],
                    'principal' => $topup_installment['principal'],
                    'original_principal' => $topup_installment['principal'],
                    'amount_due' => $topup_installment['amount_due'],
                    'amortization'=> $topup_installment['amortization'],
                    'principal_balance' => $topup_installment['principal_balance'],
                    'interest_balance' => $topup_installment['interest_balance']
                ]);

                $installment->topup_installment()->create(
                    [
                        'loan_account_installment_id' => $installment->id,
                        'loan_account_topup_id' => $topup->id,
                        'interest_topup' => $topup_installment['add_on_interest'],
                        'principal_topup' => $topup_installment['add_on_principal'],
                        'total_topup' => $topup_installment['add_on_interest'] + $topup_installment['principal'],
                        'topup_by' => $user,
                    ]
                );
            }
       
            return response()->json(['msg' => 'Success'],200);
        

    }

}
