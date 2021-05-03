<?php

namespace App\Http\Controllers;

use App\Loan;
use App\Deposit;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function getItems(Request $request){
        if($request->product_type=="deposit"){
            if(!$request->has('product_code')){

                $accs = Deposit::all()->toArray();
                $all = array("id"=>"ALL","name"=>"ALL");
                array_unshift($accs, $all);

                return $accs;
            }
            return Deposit::where('product_id',$request->product_code)->first();
        }
    }

    public function index(Request $request){
        
        $list = $request->list;
        $status = $request->status;
        $loan = Loan::select('id','name','code')->where('status',$status)->get();
        $deposit = Deposit::select('id','name','product_id')->where('is_active',$status)->get();

        $loans = [];
        if ($list=="all") {
            $loans = $loan->map(function ($item) {
                $item['id'] = $item->id;
                $item['name'] = $item->name;
                $item['code'] = $item->code;
                $item['type'] = 'loan';
                return $item;
            });
            $deposits = [];
            $deposits = $deposit->map(function ($item) {
                $item['id'] = $item->id;
                $item['name'] = $item->name;
                $item['code'] = $item->product_id;
                $item['type'] = 'deposit';
                return $item;
            });

            return $filtered = [
                ['type'=>'Loan', 'data'=>$loans],
                ['type'=>'Deposit', 'data'=>$deposits],
            ];
        }elseif($list=="loan"){
            $loans = $loan->map(function ($item) {
                $item['id'] = $item->id;
                $item['name'] = $item->name;
                $item['code'] = $item->code;
                $item['type'] = 'loan';
                return $item;
            });
            return $filtered = [
                ['type'=>'Loan', 'data'=>$loans],
            ];
        }elseif($list=="deposit"){
            $deposits = [];
            $deposits = $deposit->map(function ($item) {
                $item['id'] = $item->id;
                $item['name'] = $item->name;
                $item['code'] = $item->product_id;
                $item['type'] = 'deposit';
                return $item;
            });

            return $filtered = [
                ['type'=>'Deposit', 'data'=>$deposits],
            ];
        }

    }

    public function filter(Request $request){
        
    }
}
