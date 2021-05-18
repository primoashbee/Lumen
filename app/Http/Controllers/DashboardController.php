<?php

namespace App\Http\Controllers;

use App\Dashboard;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    //

    public function index(){
        if (auth()->check()) {
            return redirect()->route('dashboard');
        }
        return redirect()->route('login');

    }
    public function dashboard(){
        return view('pages.dashboard');
    }

    public function type($reload=false, $office_id,$type){
        if($type=="repayment_trend"){
            return $this->repaymentTrend($office_id,$reload);
        }
        if($type=="disbursement_trend"){
            return $this->disbursementTrend($office_id,$reload);
        }
        if($type=="par_movement"){ 
            return $this->parMovement($office_id,$reload);
        }
        if($type=="client_trend"){
            return $this->clientTrend($office_id,$reload);
        }
        if($type=="client_outreach"){
            return $this->clientOutReach($office_id,$reload);
        }
        if($type=="summary"){
            return $this->summary($office_id,$reload);
        }
    }
    public function repaymentTrend($office_id,$reload){
        if($reload){
            $repayment_trend = session('dashboard.repayment_trend');
            
            $data = Dashboard::repaymentTrend($office_id,true);        
            $repayment_trend['expected_repayment'][6] = $data['expected_repayment'][0];
            $repayment_trend['actual_repayment'][6] = $data['actual_repayment'][0];
            $repayment_trend['labels'][6] = $data['labels'][0];

            return response()->json(compact('repayment_trend'),200);
        }
        $repayment_trend = session('dashboard.repayment_trend');
        return response()->json(compact('repayment_trend'),200);
    }

    public function disbursementTrend($office_id,$reload){
        if($reload){
            $disbursement_trend = session('dashboard.disbursement_trend');
            $data = Dashboard::disbursementTrend($office_id,$reload);
            $disbursement_trend['labels'][6] = $data['labels'][0];
            $disbursement_trend['disbursements'][6] = $data['disbursements'][0];
            $disbursement_trend['repayment_interest'][6] = $data['repayment_interest'][0];
            $disbursement_trend['repayment_principal'][6] = $data['repayment_principal'][0];
            return response()->json(compact('disbursement_trend'),200);
        }
        $disbursement_trend = session('dashboard.disbursement_trend');
        return response()->json(compact('disbursement_trend'),200);
    }
    public function parMovement($office_id,$reload){
        if($reload){
            $par_movement = session('dashboard.par_movement');
            $data =Dashboard::parMovement(now()->subDays(6),now()->subDay(),$office_id,$reload);
            
          
            $par_movement['labels'][6] = $data['labels'];
            $par_movement['par_amount']['1-30'][6] = $data['par_amount']['1-30'];
            $par_movement['par_amount']['31-60'][6] = $data['par_amount']['31-60'];
            $par_movement['par_amount']['61-90'][6] = $data['par_amount']['61-90'];
            $par_movement['par_amount']['91-180'][6] = $data['par_amount']['91-180'];
            $par_movement['par_amount']['181'][6] = $data['par_amount']['181'];
            
            return response()->json(compact('par_movement'),200);
        }
        $par_movement = session('dashboard.par_movement');
        return response()->json(compact('par_movement'),200);
    }
    public function clientOutreach($office_id,$reload){
        return response()->json(['outreach'=>Dashboard::clientOutreach($office_id)],200);
    }
    public function summary($office_id,$reload){
        return response()->json(['summary'=>Dashboard::summary($office_id)],200);
    }
    public function clientTrend($office_id,$reload){
        return response()->json(['client_trend'=>Dashboard::clientTrend($office_id)]);
    }
}
