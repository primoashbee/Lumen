<?php

namespace App\Http\Controllers;
use App\Deposit;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

use Illuminate\Http\Request;

class DepositController extends Controller
{
    public function index(){
      return view('pages.deposit-list');
    }

	public function getDepositProducts(){
		$deposit = Deposit::paginate(10);
		
		return response()->json($deposit);
	}

  	public function create(){
  		return view('pages.create-deposit');
  	}

    public function getDepositList(){
      $deposit = Deposit::paginate(10);
      return response()->json($deposit);
    }

  	public function store(Request $request){

  		$deposit = $this->validator($request->all())->validate();

  		$deposit = Deposit::create($deposit);

  		return response()->json(['msg'=>'Deposit product succesfully created'],200);

  	}

  	public function edit(Deposit $deposit){
  		return view('pages.edit-deposit',compact('deposit'));
  	}

  	public function update(Request $request, Deposit $deposit){
  		// $deposit = Deposit::where('product_id', $request->product_id)->first();

  		$validated = $this->validator($request->all(),true,$request->id)->validate();

  		$deposit->update(
  			$validated
  		);
  		return response()->json(['msg'=>'Deposit product succesfully updated'],200);
  	}

  	public function validator(array $data,$for_update=false,$id=null){
    	  
    	 if ($for_update) {
            return Validator::make(
                $data,
                [
                'product_id' => 'required|unique:deposits,product_id,'.$id,
                'name' => 'required|unique:deposits,name,'.$id,
          			'valid_until' => 'sometimes',
          			'account_per_client' => 'required|numeric',
          			'minimum_deposit_per_transaction' => 'required|numeric',
					'auto_create_on_new_client' => 'sometimes',
          			'interest_rate' => 'required|numeric',
          			'deposit_portfolio' => 'required|numeric',
          			'deposit_interest_expense' => 'required|numeric',
          			'description' => 'required|min:50|max:255',
                ]
            );
        }

        return Validator::make(
    		$data,
    		[
    			'product_id' => 'required|unique:deposits,product_id',
                'name' => 'required|unique:deposits,name',
    			'valid_until' => 'sometimes',
				'auto_create_on_new_client' => 'sometimes',
    			'account_per_client' => 'required|numeric',
    			'minimum_deposit_per_transaction' => 'required|numeric',
    			'interest_rate'  => 'required|numeric',
    			'deposit_portfolio' => 'required|numeric',
    			'deposit_interest_expense' => 'required|numeric',
    			'description' => 'required|min:50|max:255',
    		]
    	);
    }
}
