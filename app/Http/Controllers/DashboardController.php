<?php

namespace App\Http\Controllers;

use App\Dashboard;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    //

    public function index(){

        return view('pages.dashboard');
    }



    public function type($type, $office_id){
        if($type=="repayment_trend"){
            return $this->repaymentTrend(($office_id));
        }
        if($type=="disbursement_trend"){
            // return Dashboard::disbursementTrend($office_id);
        }
    }
    public function repaymentTrend($office_id){
        
        $rt = Dashboard::repaymentTrend($office_id);

        // return response()->json(['labels'=>$rt['labels'],'expected_repayment'=>$rt['expected_repayment'],'actual_repayment'=>$rt['actual_repayment']],200);
        
        // $actual_repayments = [0,0,0,0,0,0];
        return response()->json($rt,200); 
    }

    public function disbursementTrend($office_id){
        $rt = Dashboard::disbursementTrend($office_id);
        $labels = $rt->pluck('date');
        $expected_repayment = $rt->pluck('total');
        $actual_repayments = [0,0,0,0,0,0];
        return response()->json(['labels'=>$labels,'expected_repayments'=>$expected_repayment,'actual_repayments'=>$actual_repayments],200);
    }
}
