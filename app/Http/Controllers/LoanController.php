<?php

namespace App\Http\Controllers;

use App\Loan;
use App\LoanFee;
use App\Rules\FeeIDExists;
use Illuminate\Http\Request;
use App\Rules\OnChartOfAccounts;
use Illuminate\Support\Facades\Validator;

class LoanController extends Controller
{
    //

    public function index(){
        return view('pages.settings.loan-list');
    }
    public function create(Request $request){
        $input = $request->all();
        $this->validator($input,'create')->validate();
        $input['created_by'] = auth()->user()->id;
        if($loan = Loan::create($input)){
            $data = array();
            if (count($request->fees) > 0) {
                
                foreach ($request->fees as $fee) {
                $loan->attach($fee['id']);
                //     $data[] = array(
                //         'loan_id'=>$loan['id'],
                //         'fee_id'=>$fee['id']
                //     );
                }
                // LoanFee::insert($data);
            }
            return response()->json(['msg'=>'Loan Sucessfully Created'],200);
        }
        
    }

    public function rules($type,$data=null){
        if($type=="create"){
            $rules = [
                'account_per_client' => 'required|gt:0',
                'code' => 'required|unique:loans,code',
                'default_installment'=>'required|gt:0',
                'description'=>'required',
                'grace_period'=>'required',
                'has_tranches'=>'required',
                'installment_length'=>'required',
                'installment_method'=>'required',

                'interest_interval'=>'required',
                'interest_rate'=>'required',

                'minimum_installment'=>'required',
                'default_installment'=>'required',
                'maximum_installment'=>'required',
                
                'loan_maximum_amount'=>'required',
                'loan_minimum_amount'=>'required',

                'name'=>'required',

                'number_of_tranches'=>'sometimes',

                'valid_until'=>'sometimes|required|date',

                'loan_interest_income_active'=> [new OnChartOfAccounts, 'required','integer'],
                'loan_interest_income_in_arrears'=> [new OnChartOfAccounts, 'required','integer'],
                'loan_interest_income_matured'=> [new OnChartOfAccounts, 'required','integer'],

                'loan_portfolio_active'=> [new OnChartOfAccounts, 'required','integer'],
                'loan_portfolio_in_arrears'=> [new OnChartOfAccounts, 'required','integer'],
                'loan_portfolio_matured'=> [new OnChartOfAccounts, 'required','integer'],

                'loan_recovery'=> [new OnChartOfAccounts, 'required','integer'],
                'loan_write_off'=> [new OnChartOfAccounts, 'required','integer'],
                'fees.*'=> [new FeeIDExists]
            ];
        }
        if($type=="update"){
            $rules = [
                'account_per_client' => 'required|gt:0',
                'code' => 'required|unique:loans,code,'.$data['id'],
                'default_installment'=>'required|gt:0',
                'description'=>'required',
                'grace_period'=>'required',
                'has_tranches'=>'required',
                'installment_length'=>'required',
                'installment_method'=>'required',

                'interest_interval'=>'required',
                'interest_rate'=>'required',

                'minimum_installment'=>'required',
                'default_installment'=>'required',
                'maximum_installment'=>'required',
                
                'loan_maximum_amount'=>'required',
                'loan_minimum_amount'=>'required',

                'name'=>'required',

                'number_of_tranches'=>'sometimes',

                'valid_until'=>'sometimes|date',

                'loan_interest_income_active'=> [new OnChartOfAccounts, 'required','integer'],
                'loan_interest_income_in_arrears'=> [new OnChartOfAccounts, 'required','integer'],
                'loan_interest_income_matured'=> [new OnChartOfAccounts, 'required','integer'],

                'loan_portfolio_active'=> [new OnChartOfAccounts, 'required','integer'],
                'loan_portfolio_in_arrears'=> [new OnChartOfAccounts, 'required','integer'],
                'loan_portfolio_matured'=> [new OnChartOfAccounts, 'required','integer'],

                'loan_recovery'=> [new OnChartOfAccounts, 'required','integer'],
                'loan_write_off'=> [new OnChartOfAccounts, 'required','integer'],
                'fees.*'=> [new FeeIDExists]
            ];
        }
        return $rules;
    }
    public function validationMessages($type){
        if($type=="create"){
            $msgs = [
                'fees.*.required' => 'You must select atleast one (1) fee.'
            ];
        }
        if($type=="update"){
            $msgs = [
                'fees.*.required' => 'You must select atleast one (1) fee.'
            ];
        }
        return $msgs;
    }
    public function validator(array $array,$type){

        return 
            Validator::make(
                $array,
                $this->rules($type,$array),
                $this->validationMessages($type)
            );
    }
    public function loanProducts(Request $request){
        if ($request->has('has_page')) {
            $loans = Loan::with('fees')->get(); 
            $rates = Loan::rates();
            $data = array('loans'=>$loans,'rates'=>$rates);
            return $data;
        }
        return Loan::paginate(10);
    }

    public function updateLoan(Loan $loan){
        $type = 'edit';
        return view('pages.settings.loan-product',compact('loan','type'));
    }
    public function viewLoan(Loan $loan){
        dd($loan);
    }
    public function loanProduct($id){

        $loan = Loan::find($id)->load('fees:id,name');

        return response()->json([
            'loan'=>$loan,200]);
    }
    public function updateLoanProduct(Request $request, $id){
        $input = $request->all();
        $this->validator($input,'update')->validate();
        $loan = Loan::find($id);
        $loan->update($input);
        $loan->fees()->detach();
        foreach($request->fees as $fee){
            $loan->fees()->attach($fee['id']);
        }
        return response()->json(['msg'=>'Loan Sucessfully Updated'],200);

    }

    
}

