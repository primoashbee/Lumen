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
            return $this->disbursementTrend($office_id);
        }
    }
    public function repaymentTrend($office_id){
        $rt = Dashboard::repaymentTrend($office_id);
        return response()->json($rt,200); 
    }

    public function disbursementTrend($office_id){
        $rt = Dashboard::disbursementTrend($office_id);
        return response()->json($rt,200);
    }
}
