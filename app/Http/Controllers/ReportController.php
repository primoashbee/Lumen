<?php

namespace App\Http\Controllers;

use App\Report;
use App\BulkDisbursement;
use Illuminate\Http\Request;
use App\Exports\DisbursementExport;
use App\Rules\DepositTransactionType;
use App\Rules\EducationalAttainment;
use App\Rules\Gender;
use App\Rules\IsArray;
use App\Rules\ServiceType;
use App\Rules\ValidClientStatus;
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
        if($type=='client-status'){
            $this->reportValidate($data,$type)->validate();
            
            $for_download = $request->has('export') ? true : false;
            
            if($for_download){
                
                $list = Report::clientStatus($data,false);
                $file = DownloadController::clientReport($list);
                return response()->download($file['file'],$file['filename'],$file['headers'])->deleteFileAfterSend(true);
            }

            $list = Report::clientStatus($data);
            
            return response()->json($list,200);
        }
        
        if($type=='dst'){
            $this->reportValidate($data,$type)->validate();
            
            $for_download = $request->has('export') ? true : false;
            
            if($for_download){
                $list = Report::dst($data,false,true);
                $file = DownloadController::dstBulkV2($list);
                return response()->download($file['file'],$file['filename'],$file['headers'])->deleteFileAfterSend(true);
            }

            $list = Report::dst($data);
            
            return response()->json($list,200);
        }

        if ($type == 'loan_in_arrears_principal') {
            $for_download = $request->has('export') ? true : false;
            $list = Report::loanInArrearsPrincipal($data);
            
            if ($for_download) {
                $list = Report::loanInArrearsPrincipal($data,false,true);
                $file = DownloadController::loanInArrearsPrincipalReport($list);
                return response()->download($file['file'],$file['filename'],$file['headers'])->deleteFileAfterSend(true);
            }

            return response()->json($list, 200);
        }

        if ($type == 'writeoff') {
            
            $for_download = $request->has('export') ? true : false;
            $list = Report::writeOffAccounts($data);
            
            if ($for_download) {
                $list = Report::writeOffAccounts($data,false,true);
                $file = DownloadController::writeOffs($list);
                return response()->download($file['file'],$file['filename'],$file['headers'])->deleteFileAfterSend(true);
            }

            return response()->json($list, 200);
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
                    'amount_from'=>['required_with:amount_from','nullable','numeric','gt:0'],
                    'amount_to'=>['required_with:amount_from','nullable','numeric','gt:0'],
                    'transaction_by'=>['sometimes','nullable', new IsArray('users','id')],
                    'transaction_type'=>['sometimes','nullable', new DepositTransactionType],
                    'deposit_ids.*'=>['sometimes','nullable','exists:deposits,id']
                ],
            );
        }
        if($type=='client-status'){
            return Validator::make($data,
                [
                    'office_id'=>['sometimes','nullable','exists:offices,id'],
                    'status'=>['sometimes','nullable', new ValidClientStatus],
                    'type'=>['sometimes','nullable', new ServiceType(true)
                ],
                    'age_from'=>['sometimes','nullable','gte:0','required_with:age_to'],
                    'age_to'=>['sometimes','nullable','required_with:age_from','gte:age_from'],
                    'educational_attainment'=>['sometimes','nullable', new EducationalAttainment(true)],
                    'gender'=>['sometimes','nullable', new Gender(true)],
                ],
            );
        }
        if($type=='dst'){
            return Validator::make($data,
                [
                    'office_id'=>['sometimes','nullable','exists:offices,id'],
                    'disbursement_date'=>['sometimes','nullable', 'before:tomorrow'],
                    'per_page'=>["gte:25"],
                ],
            );
        }
    }
}
