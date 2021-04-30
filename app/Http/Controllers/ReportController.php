<?php

namespace App\Http\Controllers;

use App\Report;
use App\BulkDisbursement;
use Illuminate\Http\Request;
use App\Exports\DisbursementExport;
use App\Rules\DepositTransactionType;
use App\Rules\IsArray;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;

class ReportController extends Controller
{
    

    public function rp(){
        return view('pages.reports.repayments');
    }
    public function index(){
        return view('pages.reports.index');
    }

    public function bulkDisbursementIndex(){
        return view('pages.reports.disbursements');
    }

    public function view($class,$type){
        return view('pages.reports.report-list',compact('class','type'));
        // if($type=='disbursements'){
        //     return view('pages.reports.report-list',compact('type'));
        // }
        // if($type=='repayments'){
        //     return view('pages.reports.report-list',compact('type'));
        // }
    }
    public function getReport(Request $request, $type){
        $data = $request->all();
        if($type=='disbursements'){
            $this->reportValidate($data,$type)->validate();
            
            $for_download = $request->has('export') ? true : false;
            if($for_download){
                $list = Report::disbursement($data,false);
                $file = DownloadController::disbursementReport($list);

                return response()->download($file['file'],$file['filename'],$file['headers'])->deleteFileAfterSend(true);
            }
            $list = Report::disbursement($data);
            return response()->json(compact('list'),200);
        }
        if($type=='repayments'){
            $this->reportValidate($data,$type)->validate();
            
            $for_download = $request->has('export') ? true : false;
            if($for_download){
                $list = Report::repayment($data,false);
                $file = DownloadController::repaymentReport($list);
                // return;
                return response()->download($file['file'],$file['filename'],$file['headers']);
            }
            $list = Report::repayment($data);
            
            return response()->json(compact('list'),200);
        }
        if($type=='deposit-transactions'){
            $this->reportValidate($data,$type)->validate();
            
            $for_download = $request->has('export') ? true : false;
            
            if($for_download){
                $list = Report::depositTransaction($data,false);
                $file = DownloadController::depositReport($list);
                return response()->download($file['file'],$file['filename'],$file['headers'])->deleteFileAfterSend(true);
            }

            $list = Report::depositTransaction($data);
            
            return response()->json($list,200);
        }
    }

    public function disbursements(array $data,$type){
        $this->reportValidate($data, $type);
    }

    public function reportValidate(array $data, $type){
        if($type=='disbursements'){
            return Validator::make($data,
                [
                    'office_id'=>['sometimes','nullable','exists:offices,id'],
                    'from_date'=>['sometimes','nullable','date','before:tomorrow'],
                    'to_date'=>['sometimes','nullable','date','before:tomorrow'],
                ],
            );
        }
        if($type=='repayments'){
            return Validator::make($data,
                [
                    'office_id'=>['sometimes','nullable','exists:offices,id'],
                    'from_date'=>['sometimes','nullable','date','before:tomorrow'],
                    'to_date'=>['sometimes','nullable','date','before:tomorrow'],
                ],
            );
        }
        if($type=='deposit-transactions'){
            return Validator::make($data,
                [
                    'office_id'=>['sometimes','nullable','exists:offices,id'],
                    'from_date'=>['required_with:to_date','nullable','date','before:tomorrow'],
                    'to_date'=>['required_with:from_date','nullable','date','before:tomorrow'],
                    'amount_from'=>['required_with:to_date','nullable','numeric','gt:0'],
                    'amount_to'=>['required_with:amount_from','nullable','numeric','gt:0'],
                    'transaction_by'=>['sometimes','nullable', new IsArray('users','id')],
                    'transaction_type'=>['sometimes','nullable', new DepositTransactionType],
                    'deposit_ids.*'=>['sometimes','nullable','exists:deposits,id']
                ],
            );
        }
    }
}
