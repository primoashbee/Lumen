<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function list(Request $request){
        
        // $request->validate([
        //     ''
        // ])

        $type = $request->type;
        if($type == 'loan'){
            $data = [['type'=>'Transaction' , 'data'=>[['id'=>1,'name'=>'Loan Payment'],['id'=>2,'name'=>'CTLP']]]];
        }
        
        if($type == 'deposit'){
            $data = [['type'=>'Transaction' , 'data'=>[['id'=>1,'name'=>'Payment'],['id'=>2,'name'=>'Withdrawal'],['id'=>3,'name'=>'CTLP Withdrawal'],['id'=>4,'name'=>'Interest Posting']]]];
        }

        return $data;
    }
}
