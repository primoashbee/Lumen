<?php

namespace App\Http\Controllers;

use App\Office;
use App\LoanAccount;
use App\Rules\AccountStatus;
use App\Rules\ArrayNotEmpty;
use Illuminate\Http\Request;
use App\Http\Controllers\DownloadController;

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

    public function getList(Request $request){

        $request->validate([
            'office_id'=>'required|exists:offices,id',
            'product'=>[new ArrayNotEmpty],
            'product.*'=>'required|valid_product_ids',
            'status'=>['nullable',new AccountStatus]
        ]);
        
        $data = $request->all();
        $products = collect($request->products);
        
        // $loan = $products->filter(function($v){
        //     return $v['type'] == 'loan';
        // });
        // $deposit = $products->filter(function($v){
        //     return $v['type'] == 'deposit';
        // });
        
        $q = $request->all();
        if($request->has('export')){
            $accounts  = Office::find($request->office_id)->accounts($q, false);
            if ($request->type=='loan') {
                $file = DownloadController::loanAccounts($accounts);
            }else{
                $file = DownloadController::depositAccounts($accounts);
            }
            return response()->download($file['file'],$file['filename'],$file['headers']);

        }
        // dd($q);
        $accounts  = Office::find($request->office_id)->accounts($q);
        
        $summary = $accounts['summary'];
        $accounts = $accounts['accounts']->paginate(25);
        return response()->json(['msg'=>'nice','data'=>$accounts,'summary'=>$summary],200);
    }
}
