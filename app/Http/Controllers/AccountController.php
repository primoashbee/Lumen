<?php

namespace App\Http\Controllers;

use App\Office;
use App\LoanAccount;
use App\Rules\AccountStatus;
use App\Rules\ArrayNotEmpty;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    
    public function index($type, Request $request){
        $list = ['all','loan','deposit'];
        if(!in_array($type,$list)){
            abort(404);
        }
        $request->request->add(['loan_ids'=>json_decode($request->loan_ids)]);
        $request->request->add(['deposit_ids'=>json_decode($request->deposit_ids)]);

        if($request->expectsJson()){
            $request->validate([
                'office_id'=>'required|exists:offices,id',
                'loan_ids.*'=>'required|exists:loans,id',
                'deposit_ids.*'=>'required|exists:deposits,id',
                'status'=>['required', new AccountStatus]
            ]);
        
            return  Office::find($request->office_id)->accounts($request->all())->paginate($request->per_page);
        }
        return view('pages.accounts-list');
    }

    public function filter(Request $request, $type){

        $request->validate([
            'office_id'=>'required|exists:offices,id',
            'product'=>[new ArrayNotEmpty],
            'product.*'=>'required|valid_product_ids',
            'status'=>['required', new AccountStatus]
        ]);

        $products = collect($request->products);
        
        $loan = $products->filter(function($v){
            return $v['type'] == 'loan';
        });
        $deposit = $products->filter(function($v){
            return $v['type'] == 'deposit';
        });
        
        $q = $request->all();
        $accounts  = Office::find($request->office_id)->accounts($q)->paginate(25);
        // $deposit_accounts  = collect();
        // if($loan->count() > 0){
        //     $loan_filter = ['loan_id'=>$loan->pluck('id')->toArray(),'status'=>$request->status];
        //     $loan_accounts = Office::find($request->office_id)->loanAccounts($loan_filter)->paginate(5);
        // }
        
        // if($deposit->count() > 0){
        //     $deposit_filter = ['deposit_id'=>$deposit->pluck('id')->toArray(),'status'=>$request->status];        
        //     $deposit_accounts = Office::find($request->office_id)->depositAccountsV2($deposit_filter)->paginate(5);
        // }
            
        return response()->json(['msg'=>'nice','data'=>$accounts],200);
    }
}
