<?php

namespace App\Http\Controllers;

use stdClass;
use App\Office;
use App\LoanAccount;
use App\BulkDisbursement;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Exports\DisbursementExport;
use Illuminate\Support\Facades\App;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class DownloadController extends Controller
{
    
    public function dst($loan_account_id=1){

        $loan_account = LoanAccount::find($loan_account_id);
        $file = public_path('templates/DSTv1.xlsx');
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
        $sheet =$spreadsheet->getSheet(0);
        $cw = clone $sheet;
        
        $type = $loan_account->type;
        $feePayments  = $loan_account->feePayments->sortBy('fee_id');

        $ctr = 1;
        $cw->setTitle('#'.$ctr.' '.$loan_account->client->full_name);
        $dst = $spreadsheet->addSheet($cw);
        $dst->getCell('C18')->setValueExplicit($loan_account->amount,\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
        $dst->setCellValue('D8',$type->code);
        $dst->getCell('D9')->setValueExplicit($loan_account->amount, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
        
        $dst->getCell('D10')->setValueExplicit($loan_account->installments->first()->amortization, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
        $dst->getCell('D11')->setValueExplicit($type->interest_rate,\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
        $dst->getStyle('D11')->getNumberFormat()->setFormatCode('0.00'); 
        $dst->getCell('D12')->setValueExplicit($type->interest_rate / 4,\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
        $dst->getStyle('D12')->getNumberFormat()->setFormatCode('0.00'); 
        if ($type->code == "MPL") {
            $dst->getCell('D13')->setValueExplicit($feePayments->where('fee_id', 6)->first()->fee->percentage, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
        }
        $dst->setCellValue('D14',$loan_account->number_of_installments);
        
        $dst->setCellValue('F19','=ROUND((H11*D13),2)');
        $dst->setCellValue('G19','=C18-F19');
        
        
        $dst->getCell('H9')->setValueExplicit($loan_account->interest, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
        $dst->setCellValue('H10',$loan_account->number_of_months);
        $dst->getCell('H11')->setValueExplicit($loan_account->amount, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
        $dst->setCellValue('H12',$loan_account->client->loanCycle());

        // $dst->setCellValue('H19',$loan_account->number_of_months);

        $dst->getCell('I19')->setValueExplicit($loan_account->total_loan_amount, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);

        $dst->getCell('J19')->setValueExplicit($loan_account->interest, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);

        $row = 20;
        $amortizartion_schedule_row = 5;


        $dst->getCell('AC4')->setValueExplicit($loan_account->amount, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
        $dst->getCell('AD4')->setValueExplicit($loan_account->interest, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
        $dst->getCell('AE4')->setValueExplicit($loan_account->total_loan_amount, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
        $dst->getCell('AF4')->setValueExplicit($loan_account->total_loan_amount, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);

        foreach($loan_account->installments as $item){
            $dst->setCellValue('C'.$row , $item->date->toDateString());
            $dst->getCell('D'.$row)->setValueExplicit($item->original_principal, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            $dst->getCell('E'.$row)->setValueExplicit($item->original_interest, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            $dst->getCell('G'.$row)->setValueExplicit(($item->amortization) * (-1), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            $dst->getCell('H'.$row)->setValueExplicit($item->principal_balance, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            $dst->getCell('I'.$row)->setValueExplicit(round($item->principal_balance + $item->interest_balance,2), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            $dst->getCell('I'.$row)->setValueExplicit($item->interest_balance, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            
            

            $dst->setCellValue('AB'.$amortizartion_schedule_row, $item->date->toDateString());
            $dst->getCell('AC'.$amortizartion_schedule_row)->setValueExplicit($item->original_principal, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            $dst->getCell('AD'.$amortizartion_schedule_row)->setValueExplicit($item->original_interest, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            $dst->getCell('AE'.$amortizartion_schedule_row)->setValueExplicit($item->amortization, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            $dst->getCell('AF'.$amortizartion_schedule_row)->setValueExplicit($item->principal_balance, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);

            $amortizartion_schedule_row++;
            $row++;
        }

        $dst->setCellValue('Q5',$loan_account->client->full_name);
        $dst->setCellValue('Q6',$loan_account->client->address());
        $dst->setCellValue('Y7','=D9');
        


        
        $str = "(  ) Weekly               (  ) Semi-monthly          (  ) Monthly ";
        $str2 = "(  ) Quarterly           (  ) Semi-Annual            (  ) Annually";
        if($type->installment_method == 'weeks'){
            $str = "(X) Weekly               (  ) Semi-monthly          (  ) Monthly ";
        }
        $dst->setCellValue('M10',$str);
        $dst->setCellValue('M11',$str2);

        if($type->code == "MPL"){
            $dst->setCellValue('N14', $feePayments->where('fee_id', 6)->first()->fee->name);
            $dst->getCell('Y14')->setValueExplicit($feePayments->where('fee_id', 6)->first()->amount, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            $dst->getCell('Y16')->setValueExplicit($feePayments->where('fee_id', 6)->first()->amount, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
        }

        $row = 19;
        
        $non_finance_charges = $loan_account->nonFinanceCharges();
        $non_finance_charges->map(function($fee) use (&$row, &$dst){
            $dst->setCellValue('N'.$row,$fee->fee->name);
            $dst->getCell('Y'.$row)->setValueExplicit($fee->amount, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);

            $row++;
        });
        
        $dst->setCellValue('Y24','=SUM(Y19:Y23)');
        $dst->setCellValue('Y26','=Y16+Y24');
        $dst->setCellValue('Y28','=Y7-Y26');
        $dst->setCellValue('Y30','=D13');
        $dst->setCellValue('Y32','=H80');
        $dst->setCellValue('Y38','=I19');

        $dst->setCellValue('R36',$loan_account->installments->first()->date->format('F d, Y'));
        $dst->getCell('Y36')->setValueExplicit($loan_account->installments->first()->amortization, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
        $dst->getCell('O39')->setValueExplicit($loan_account->number_of_installments, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
        $dst->getCell('O40')->setValueExplicit($loan_account->installments->first()->amortization, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
        

        $dst->setCellValue('D69','=SUM(D19:D67)');
        $dst->setCellValue('E69','=SUM(E19:E67)');
        $dst->setCellValue('F69','=SUM(F19:F67)');
        $dst->setCellValue('G69','=IF($D$9>0,IRR(G18:G67,0.09),1)');
        $dst->setCellValue('H76','=(1+G69)^52-1');
        $dst->setCellValue('H80','=((1+G69)^(52/12)-1)');



        $spreadsheet->removeSheetByIndex(0);
        $spreadsheet->setActiveSheetIndex(0);
        
        $writer = new Xlsx($spreadsheet);
        $writer->setPreCalculateFormulas(false);
        $newFile = public_path('templates/test.xlsx');
        $writer->save($newFile);
        $filename = 'DST - '.$loan_account->client->full_name . '.xlsx';
        $headers = ['Content-Type'=> 'application/pdf','Content-Disposition'=> 'attachment;','filename'=>$filename];
        return response()->download($newFile,$filename,$headers)->deleteFileAfterSend(true);
    }

    public static function dstBulk($bulk_transaction_id){
        $file = public_path('templates/DSTv1.xlsx');
    
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
        // $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
        // $spreadsheet = $reader->load($file);
        $sheet =$spreadsheet->getSheet(0);
        $bulk_transaction_id = collect($bulk_transaction_id);
        $accounts = BulkDisbursement::whereIn('bulk_disbursement_id',$bulk_transaction_id)->get();
        $ctr = 1;
        $accounts->map(function($acc) use ($sheet,$spreadsheet,&$ctr){
            $cw = clone $sheet;
            $loan_account = $acc->loanAccount;
            $type = $loan_account->type;
            $feePayments  = $loan_account->feePayments->sortBy('fee_id');


            $cw->setTitle('#'.$ctr.' '.$acc->loanAccount->client->full_name);
            $dst = $spreadsheet->addSheet($cw);
            $dst->getCell('C18')->setValueExplicit($loan_account->amount,\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            $dst->setCellValue('D8',$type->code);
            $dst->getCell('D9')->setValueExplicit($loan_account->amount, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            
            $dst->getCell('D10')->setValueExplicit($loan_account->installments->first()->amortization, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            $dst->getCell('D11')->setValueExplicit($type->interest_rate,\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            $dst->getStyle('D11')->getNumberFormat()->setFormatCode('0.00'); 
            $dst->getCell('D12')->setValueExplicit($type->interest_rate / 4,\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            $dst->getStyle('D12')->getNumberFormat()->setFormatCode('0.00'); 
            if ($type->code == "MPL") {
                $dst->getCell('D13')->setValueExplicit($feePayments->where('fee_id', 6)->first()->fee->percentage, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            }
            $dst->setCellValue('D14',$loan_account->number_of_installments);
            
            $dst->setCellValue('F19','=ROUND((H11*D13),2)');
            $dst->setCellValue('G19','=C18-F19');
            
            
            $dst->getCell('H9')->setValueExplicit($loan_account->interest, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            $dst->setCellValue('H10',$loan_account->number_of_months);
            $dst->getCell('H11')->setValueExplicit($loan_account->amount, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            $dst->setCellValue('H12',$loan_account->client->loanCycle());

            // $dst->setCellValue('H19',$loan_account->number_of_months);
    
            $dst->getCell('I19')->setValueExplicit($loan_account->total_loan_amount, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);

            $dst->getCell('J19')->setValueExplicit($loan_account->interest, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);

            $row = 20;
            $amortizartion_schedule_row = 5;


            $dst->getCell('AC4')->setValueExplicit($loan_account->amount, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            $dst->getCell('AD4')->setValueExplicit($loan_account->interest, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            $dst->getCell('AE4')->setValueExplicit($loan_account->total_loan_amount, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            $dst->getCell('AF4')->setValueExplicit($loan_account->total_loan_amount, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);

            foreach($loan_account->installments as $item){
                $dst->setCellValue('C'.$row , $item->date->toDateString());
                $dst->getCell('D'.$row)->setValueExplicit($item->original_principal, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                $dst->getCell('E'.$row)->setValueExplicit($item->original_interest, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                $dst->getCell('G'.$row)->setValueExplicit(($item->amortization) * (-1), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                $dst->getCell('H'.$row)->setValueExplicit($item->principal_balance, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                $dst->getCell('I'.$row)->setValueExplicit(round($item->principal_balance + $item->interest_balance,2), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                $dst->getCell('I'.$row)->setValueExplicit($item->interest_balance, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                
                

                $dst->setCellValue('AB'.$amortizartion_schedule_row, $item->date->toDateString());
                $dst->getCell('AC'.$amortizartion_schedule_row)->setValueExplicit($item->original_principal, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                $dst->getCell('AD'.$amortizartion_schedule_row)->setValueExplicit($item->original_interest, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                $dst->getCell('AE'.$amortizartion_schedule_row)->setValueExplicit($item->amortization, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                $dst->getCell('AF'.$amortizartion_schedule_row)->setValueExplicit($item->principal_balance, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);

                $amortizartion_schedule_row++;
                $row++;
            }

            $dst->setCellValue('Q5',$loan_account->client->full_name);
            $dst->setCellValue('Q6',$loan_account->client->address());
            $dst->setCellValue('Y7','=D9');
            


            $ctr++;
            $str = "(  ) Weekly               (  ) Semi-monthly          (  ) Monthly ";
            $str2 = "(  ) Quarterly           (  ) Semi-Annual            (  ) Annually";
            if($type->installment_method == 'weeks'){
                $str = "(X) Weekly               (  ) Semi-monthly          (  ) Monthly ";
            }
            if($type->installment_method == 'days'){
                $str = "() Weekly               ( X ) Semi-monthly          (  ) Monthly ";
            }
            if($type->installment_method == 'months'){
                $str = "() Weekly               ( X ) Semi-monthly          ( X ) Monthly ";
            }
            $dst->setCellValue('M10',$str);
            $dst->setCellValue('M11',$str2);

            if($type->code == "MPL"){
                $dst->setCellValue('N14', $feePayments->where('fee_id', 6)->first()->fee->name);
                $dst->getCell('Y14')->setValueExplicit($feePayments->where('fee_id', 6)->first()->amount, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                $dst->getCell('Y16')->setValueExplicit($feePayments->where('fee_id', 6)->first()->amount, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            }

            $row = 19;
            
            $non_finance_charges = $loan_account->nonFinanceCharges();
            $non_finance_charges->map(function($fee) use (&$row, &$dst){
                $dst->setCellValue('N'.$row,$fee->fee->name);
                $dst->getCell('Y'.$row)->setValueExplicit($fee->amount, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);

                $row++;
            });
            
            $dst->setCellValue('Y24','=SUM(Y19:Y23)');
            $dst->setCellValue('Y26','=Y16+Y24');
            $dst->setCellValue('Y28','=Y7-Y26');
            $dst->setCellValue('Y30','=D13');
            $dst->setCellValue('Y32','=H80');
            $dst->setCellValue('Y38','=I19');

            $dst->setCellValue('R36',$loan_account->installments->first()->date->format('F d, Y'));
            $dst->getCell('Y36')->setValueExplicit($loan_account->installments->first()->amortization, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            $dst->getCell('O39')->setValueExplicit($loan_account->number_of_installments, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            $dst->getCell('O40')->setValueExplicit($loan_account->installments->first()->amortization, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            

            $dst->setCellValue('D69','=SUM(D19:D67)');
            $dst->setCellValue('E69','=SUM(E19:E67)');
            $dst->setCellValue('F69','=SUM(F19:F67)');
            $dst->setCellValue('H76','=(1+G69)^52-1');
            $dst->setCellValue('H80','=((1+G69)^(52/12)-1)');


        });
        $spreadsheet->removeSheetByIndex(0);
        $spreadsheet->setActiveSheetIndex(0);
        
        $writer = new Xlsx($spreadsheet);
        $writer->setPreCalculateFormulas(false);
        $newFile = public_path('templates/test.xlsx');
        $writer->save($newFile);
        $filename = 'DST - ' . $accounts->first()->loanAccount->client->office->code. ' ' . $accounts->first()->disbursement_date->format('F d, Y') .'.xlsx';
        $headers = ['Content-Type'=> 'application/pdf','Content-Disposition'=> 'attachment;','filename'=>$filename];
        return response()->download($newFile,$filename ,$headers)->deleteFileAfterSend(true);
    }

    public static function dstBulkV2($bulk_transaction_id){
        $file = public_path('templates/DSTv1.xlsx');
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
        // $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
        // $spreadsheet = $reader->load($file);
        $sheet =$spreadsheet->getSheet(0);
        $bulk_transaction_id = collect($bulk_transaction_id);
        $accounts = BulkDisbursement::whereIn('bulk_disbursement_id',$bulk_transaction_id['data']->toArray())->get();
        $ctr = 1;
        
        $accounts->map(function($acc) use ($sheet,$spreadsheet,&$ctr){
            $cw = clone $sheet;
            $loan_account = $acc->loanAccount;
            $type = $loan_account->type;
            $feePayments  = $loan_account->feePayments->sortBy('fee_id');

            $cw->setTitle('#'.$ctr.' '.$acc->loanAccount->client->full_name);
            $dst = $spreadsheet->addSheet($cw);
            $dst->getCell('C18')->setValueExplicit($loan_account->amount,\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            $dst->setCellValue('D8',$type->code);
            $dst->getCell('D9')->setValueExplicit($loan_account->amount, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            $dst->getCell('D10')->setValueExplicit($loan_account->installments->first()->amortization, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            $dst->getCell('D11')->setValueExplicit($type->interest_rate,\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            $dst->getStyle('D11')->getNumberFormat()->setFormatCode('0.00'); 
            $dst->getCell('D12')->setValueExplicit($type->interest_rate / 4,\PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            $dst->getStyle('D12')->getNumberFormat()->setFormatCode('0.00'); 
            if ($type->code == "MPL") {
                $dst->getCell('D13')->setValueExplicit($feePayments->where('fee_id', 6)->first()->fee->percentage, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            }
            $dst->setCellValue('D14',$loan_account->number_of_installments);
            
            $dst->setCellValue('F19','=ROUND((H11*D13),2)');
            $dst->setCellValue('G19','=C18-F19');
            
            
            $dst->getCell('H9')->setValueExplicit($loan_account->interest, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            $dst->setCellValue('H10',$loan_account->number_of_months);
            $dst->getCell('H11')->setValueExplicit($loan_account->amount, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            $dst->setCellValue('H12',$loan_account->client->loanCycle());

            // $dst->setCellValue('H19',$loan_account->number_of_months);
    
            $dst->getCell('I19')->setValueExplicit($loan_account->total_loan_amount, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);

            $dst->getCell('J19')->setValueExplicit($loan_account->interest, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);

            $row = 20;
            $amortizartion_schedule_row = 5;


            $dst->getCell('AC4')->setValueExplicit($loan_account->amount, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            $dst->getCell('AD4')->setValueExplicit($loan_account->interest, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            $dst->getCell('AE4')->setValueExplicit($loan_account->total_loan_amount, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            $dst->getCell('AF4')->setValueExplicit($loan_account->total_loan_amount, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);

            foreach($loan_account->installments as $item){
                $dst->setCellValue('C'.$row , $item->date->toDateString());
                $dst->getCell('D'.$row)->setValueExplicit($item->original_principal, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                $dst->getCell('E'.$row)->setValueExplicit($item->original_interest, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                $dst->getCell('G'.$row)->setValueExplicit(($item->amortization) * (-1), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                $dst->getCell('H'.$row)->setValueExplicit($item->principal_balance, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                $dst->getCell('I'.$row)->setValueExplicit(round($item->principal_balance + $item->interest_balance,2), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                $dst->getCell('I'.$row)->setValueExplicit($item->interest_balance, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                
                

                $dst->setCellValue('AB'.$amortizartion_schedule_row, $item->date->toDateString());
                $dst->getCell('AC'.$amortizartion_schedule_row)->setValueExplicit($item->original_principal, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                $dst->getCell('AD'.$amortizartion_schedule_row)->setValueExplicit($item->original_interest, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                $dst->getCell('AE'.$amortizartion_schedule_row)->setValueExplicit($item->amortization, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                $dst->getCell('AF'.$amortizartion_schedule_row)->setValueExplicit($item->principal_balance, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);

                $amortizartion_schedule_row++;
                $row++;
            }
            
            $dst->setCellValue('Q5',$loan_account->client->full_name);
            $dst->setCellValue('Q6',$loan_account->client->address());
            $dst->setCellValue('Y7','=D9');
            


            $ctr++;
            $str = "(  ) Weekly               (  ) Semi-monthly          (  ) Monthly ";
            $str2 = "(  ) Quarterly           (  ) Semi-Annual            (  ) Annually";
            if($type->installment_method == 'weeks'){
                $str = "(X) Weekly               (  ) Semi-monthly          (  ) Monthly ";
            }
            $dst->setCellValue('M10',$str);
            $dst->setCellValue('M11',$str2);

            if($type->code == "MPL"){
                $dst->setCellValue('N14', $feePayments->where('fee_id', 6)->first()->fee->name);
                $dst->getCell('Y14')->setValueExplicit($feePayments->where('fee_id', 6)->first()->amount, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
                $dst->getCell('Y16')->setValueExplicit($feePayments->where('fee_id', 6)->first()->amount, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            }

            $row = 19;
            
            $non_finance_charges = $loan_account->nonFinanceCharges();
            $non_finance_charges->map(function($fee) use (&$row, &$dst){
                $dst->setCellValue('N'.$row,$fee->fee->name);
                $dst->getCell('Y'.$row)->setValueExplicit($fee->amount, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);

                $row++;
            });
            
            $dst->setCellValue('Y24','=SUM(Y19:Y23)');
            $dst->setCellValue('Y26','=Y16+Y24');
            $dst->setCellValue('Y28','=Y7-Y26');
            $dst->setCellValue('Y30','=D13');
            $dst->setCellValue('Y32','=H80');
            $dst->setCellValue('Y38','=I19');

            $dst->setCellValue('R36',$loan_account->installments->first()->date->format('F d, Y'));
            $dst->getCell('Y36')->setValueExplicit($loan_account->installments->first()->amortization, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            $dst->getCell('O39')->setValueExplicit($loan_account->number_of_installments, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            
            $dst->getCell('O40')->setValueExplicit($loan_account->installments->first()->amortization, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
            

            $dst->setCellValue('D69','=SUM(D19:D67)');
            $dst->setCellValue('E69','=SUM(E19:E67)');
            $dst->setCellValue('F69','=SUM(F19:F67)');
            $dst->setCellValue('H76','=(1+G69)^52-1');
            $dst->setCellValue('H80','=((1+G69)^(52/12)-1)');
            

        });
        
        $spreadsheet->removeSheetByIndex(0);
        $spreadsheet->setActiveSheetIndex(0);
        
        $writer = new Xlsx($spreadsheet);
        $writer->setPreCalculateFormulas(false);
        $newFile = public_path('templates/test.xlsx');
        $writer->save($newFile);
        
        $filename = 'DST - ' . $accounts->first()->disbursement_date->format('F d, Y') .'.xlsx';
        $headers = ['Content-Type'=> 'application/pdf','Content-Disposition'=> 'attachment;','filename'=>$filename];
        
        return ['file'=>$newFile, 'filename' => $filename, 'headers'=>$headers];
        
    }

    public static function ccr($request, $data){
        $office = Office::find($request['office_id']);
        $repayment_date = Carbon::parse($request['date']);
        $printed_by = auth()->user()->fullname;

        $summary = new stdClass;
        $summary->office = $office->name;
        $summary->printed_by = $printed_by;
        $summary->printed_at = \Carbon\Carbon::now()->format('F j, Y, g:i a');
        $summary->repayment_date = $repayment_date->format('F d, Y');
        $summary->has_deposit = array_key_exists('deposit_product_ids',$request);
        $summary->has_loan = array_key_exists('loan_product_id',$request);
        if ($summary->has_deposit) {
            $summary->deposit_types = $request['deposit_product_ids'];
        }
        $summary->loan_accounts = $data;

        $summary->name = 'Collection Sheet - ' . $summary->office . ' for ' . $summary->repayment_date.'.pdf';
 
        $file = public_path('temp/'). $summary->name;
        $pdf = App::make('snappy.pdf.wrapper');
        $headers = ['Content-Type'=> 'application/pdf','Content-Disposition'=> 'attachment;','filename'=>$summary->name];
    
        $pdf->loadView('exports.test',compact('summary'))->save($file,true);
        
        return ['file'=>$file, 'filename' => $summary->name, 'headers'=>$headers];
    }

    public static function disbursementReport($data){
        $id = str_replace('.','',microtime(true));

        if($data['is_summarized']){
            $filename = 'Disbursement Report - Summarized ('.$id.').xlsx';
            $file = public_path('templates/Reports/Disbursement Report - Summary.xlsx');
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
            $sheet =$spreadsheet->getSheet(0);
        
            $start_row = 3  ;
            // foreach ($data['data'] as $key=>$value) {

            // }
            $data['data']->orderBy('office_level','desc')->chunk(1000, function($list) use (&$start_row, &$sheet){
                foreach($list as $key=>$value){
                    $sheet->setCellValue('A'.$start_row, $key + 1);
                    $sheet->setCellValue('B'.$start_row, $value->office_level);
                    $sheet->setCellValue('C'.$start_row, $value->number_of_disbursements);
                    $sheet->setCellValue('D'.$start_row, $value->loan_type);
                    $sheet->setCellValue('E'.$start_row, $value->principal);
                    $sheet->setCellValue('F'.$start_row, $value->principal);
                    $sheet->setCellValue('G'.$start_row, $value->interest);
                    $sheet->setCellValue('H'.$start_row, $value->total_loan_amount);
                    $sheet->setCellValue('I'.$start_row, $value->disbursed_amount);
                    $sheet->setCellValue('J'.$start_row, $value->total_deductions);
                    $start_row++;
                }
            });
            $writer = new Xlsx($spreadsheet);
            $writer->setPreCalculateFormulas(false);
            $newFile = public_path('created_reports/').$filename;
            $writer->save($newFile);
        }else{
            $filename = 'Disbursement Report - Detailed ('.$id.').xlsx';
            $file = public_path('templates/Reports/Disbursement Report - Detailed.xlsx');
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
            $sheet =$spreadsheet->getSheet(0);
        
            $start_row = 3  ;
            $data['data']->orderBy('id','desc')->chunk(1000, function($list) use (&$start_row, &$sheet) {
                foreach($list as $key=>$value){
                    $sheet->setCellValue('A'.$start_row, $key + 1);
                    $sheet->setCellValue('B'.$start_row, $value->client_id);
                    $sheet->setCellValue('C'.$start_row, $value->fullname);
                    $sheet->setCellValue('D'.$start_row, $value->code);
                    $sheet->setCellValue('E'.$start_row, $value->principal);
                    $sheet->setCellValue('F'.$start_row, $value->principal);
                    $sheet->setCellValue('G'.$start_row, $value->interest);
                    $sheet->setCellValue('H'.$start_row, $value->total_loan_amount);
                    $sheet->setCellValue('I'.$start_row, $value->interest_rate);
                    $sheet->setCellValue('J'.$start_row, $value->number_of_months);
                    $sheet->setCellValue('K'.$start_row, $value->number_of_installments);
                    $sheet->setCellValue('L'.$start_row, $value->installment_method);
                    $sheet->setCellValue('M'.$start_row, $value->total_deductions);
                    $sheet->setCellValue('N'.$start_row, $value->disbursed_amount);
                    $sheet->setCellValue('O'.$start_row, $value->disbursed_by);
                    $sheet->setCellValue('P'.$start_row, $value->disbursement_date);
                    $sheet->setCellValue('Q'.$start_row, $value->first_payment_date);
                    $sheet->setCellValue('R'.$start_row, $value->last_payment_date);
                    $sheet->setCellValue('S'.$start_row, $value->rcbu);
                    $sheet->setCellValue('T'.$start_row, $value->mcbu);
                    $sheet->setCellValue('U'.$start_row, $value->notes);
                    $sheet->setCellValue('V'.$start_row, $value->office_level);
                    $start_row++;
                }
            });
            $writer = new Xlsx($spreadsheet);
            $writer->setPreCalculateFormulas(false);
            $newFile = public_path('created_reports/').$filename;
            $writer->save($newFile);
        }
        $headers = ['Content-Type'=> 'application/vnd.ms-excel','Content-Disposition'=> 'attachment;','filename'=>$filename];
        return ['file'=>$newFile, 'filename' => $filename, 'headers'=>$headers];
        
    }

    public static function repaymentReport($data){
        $id = str_replace('.','',microtime(true));

        if($data['is_summarized']){
                $filename = 'Repayment Report - Summary ('.$id.').xlsx';
                $file = public_path('templates/Reports/Repayment Report - Summary.xlsx');
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
                $sheet =$spreadsheet->getSheet(0);
            
                $start_row = 3;
       
                $data['data']->orderBy('office_code')->chunk(1000, function($list) use (&$sheet, &$start_row){
                    foreach ($list as $key=>$value) {
                        $sheet->setCellValue('A'.$start_row, $key + 1);
                        $sheet->setCellValue('B'.$start_row, $value->office_code);
                        $sheet->setCellValue('C'.$start_row, $value->number_of_repayments);
                        $sheet->setCellValue('D'.$start_row, $value->payment_method_name);
                        $sheet->setCellValue('E'.$start_row, $value->loan_code);
                        $sheet->setCellValue('F'.$start_row, $value->principal_paid);
                        $sheet->setCellValue('G'.$start_row, $value->interest_paid);
                        $sheet->setCellValue('H'.$start_row, $value->total_paid);

        
                        $start_row++;
                    }
                });

        }else{
                $filename = 'Repayment Report - Detailed ('.$id.').xlsx';
                $file = public_path('templates/Reports/Repayment Report - Detailed.xlsx');
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
                $sheet =$spreadsheet->getSheet(0);
                $z = $data['data']->get();
                $start_row = 3  ;
                $data['data']->orderBy('repayment_date','desc')->chunk(1000, function($list) use (&$sheet, &$start_row){
                    foreach($list as $key=>$value){
                        $sheet->setCellValue('A'.$start_row, $key + 1);
                        $sheet->setCellValue('B'.$start_row, $value->client_id);
                        $sheet->setCellValue('C'.$start_row, $value->client_name);
                        $sheet->setCellValue('D'.$start_row, $value->loan_code);
                        $sheet->setCellValue('E'.$start_row, $value->principal_paid);
                        $sheet->setCellValue('F'.$start_row, $value->interest_paid);
                        $sheet->setCellValue('G'.$start_row, $value->total_paid);
                        $sheet->setCellValue('H'.$start_row, $value->payment_method_name);
                        $sheet->setCellValue('I'.$start_row, $value->paid_by);
                        $sheet->setCellValue('J'.$start_row, $value->repayment_date);
                        // $sheet->setCellValue('K'.$start_row, $value->paid_on);
                        $sheet->setCellValue('L'.$start_row, $value->timestamp);
        
                        $start_row++;
                    }
                });
            }

            $writer = new Xlsx($spreadsheet);
            $writer->setPreCalculateFormulas(false);
            $newFile = public_path('created_reports/').$filename;
            $writer->save($newFile);

            $headers = 
            [
                'Content-Type'=> 'application/vnd.ms-excel',
                'Content-Disposition'=> 'attachment;',
                'filename'=>$filename
            ];
            return ['file'=>$newFile, 'filename' => $filename, 'headers'=>$headers]; 
        }


    public static function depositReport($data){
        $id = str_replace('.','',microtime(true));

        if($data['is_summarized']){
            $filename = 'Deposit Report - Summary ('.$id.').xlsx';
            // $file = public_path('templates/Reports/Detailed Deposit Report.xlsx');
            $file = public_path('templates/Reports/Deposit Report - Summary.xlsx');
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
            $sheet =$spreadsheet->getSheet(0);
            
            $start_row = 3  ;
            
            // foreach ($data['data']->chunk as $key=>$value) {
            $data['data']->orderBy('office_name')->chunk(200, function ($records) use (&$sheet, &$start_row){
                foreach($records as $key=>$value){
                    $sheet->setCellValue('A'.$start_row, $key + 1);
                    $sheet->setCellValue('B'.$start_row, $value->office_name);
                    $sheet->setCellValue('C'.$start_row, $value->number_of_transactions);
                    $sheet->setCellValue('D'.$start_row, $value->transaction_type);
                    $sheet->setCellValue('E'.$start_row, $value->amount);
                    $sheet->setCellValue('F'.$start_row, $value->deposit_type);
                    $sheet->setCellValue('G'.$start_row, $value->balance);
                    $start_row++;
                }
            });
            // }
            $writer = new Xlsx($spreadsheet);
            $writer->setPreCalculateFormulas(false);
            $newFile = public_path('created_reports/').$filename;
            $writer->save($newFile);
        }else{
            $filename = 'Deposit Report - Detailed ('.$id.').xlsx';
            // $file = public_path('templates/Reports/Detailed Deposit Report.xlsx');
            $file = public_path('templates/Reports/Deposit Report - Detailed.xlsx');
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
            $sheet =$spreadsheet->getSheet(0);
        
            $start_row = 3  ;
            $data['data']->orderBy('office_name')->chunk(200, function ($records) use (&$sheet, &$start_row){
                foreach ($records as $key=>$value) {
                    $sheet->setCellValue('A'.$start_row, $key + 1);
                    $sheet->setCellValue('B'.$start_row, $value->client_id);
                    $sheet->setCellValue('C'.$start_row, $value->client_name);
                    $sheet->setCellValue('D'.$start_row, $value->deposit_type);
                    $sheet->setCellValue('E'.$start_row, $value->amount);
                    $sheet->setCellValue('F'.$start_row, $value->transaction_type);
                    $sheet->setCellValue('G'.$start_row, $value->balance);
                    $sheet->setCellValue('H'.$start_row, $value->paid_by);
                    $sheet->setCellValue('I'.$start_row, $value->payment_method_name);
                    $sheet->setCellValue('J'.$start_row, $value->repayment_date);
                    $sheet->setCellValue('K'.$start_row, $value->timestamp);
                    $sheet->setCellValue('L'.$start_row, $value->notes);
                    $start_row++;
                }
            });
            $writer = new Xlsx($spreadsheet);
            $writer->setPreCalculateFormulas(false);
            $newFile = public_path('created_reports/').$filename;
            $writer->save($newFile);
        }

        $headers = ['Content-Type'=> 'application/vnd.ms-excel','Content-Disposition'=> 'attachment;','filename'=>$filename];
        return ['file'=>$newFile, 'filename' => $filename, 'headers'=>$headers];
    }

    public static function transaction(){

        // $list = \DB::table('transactions')->get();

        $filename = 'Repayment Report - Detailed.xlsx';
            // $file = public_path('templates/Reports/Detailed Deposit Report.xlsx');
        $file = public_path('templates/Reports/Deposit Report - Detailed.xlsx');
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
        $sheet =$spreadsheet->getSheet(0);
    
        $start_row = 3  ;
        \DB::table('transactions')->chunkById(200, function($items) use (&$sheet, &$start_row){
            foreach ($items as $key=>$value) {
                $sheet->setCellValue('A'.$start_row, $key + 1);
                $sheet->setCellValue('B'.$start_row, $value->transaction_number);
                $sheet->setCellValue('C'.$start_row, $value->type);
                $sheet->setCellValue('D'.$start_row, $value->transactionable_id);
                $sheet->setCellValue('E'.$start_row, $value->office_id);
                $sheet->setCellValue('F'.$start_row, $value->transaction_date);
                $sheet->setCellValue('G'.$start_row, $value->transactionable_type);
                $sheet->setCellValue('H'.$start_row, $value->reverted);
                $sheet->setCellValue('I'.$start_row, $value->reverted_by);
                $sheet->setCellValue('J'.$start_row, $value->reverted_at);
                $sheet->setCellValue('K'.$start_row, $value->revertion);
                $sheet->setCellValue('L'.$start_row, $value->posted_by);
                $sheet->setCellValue('L'.$start_row, $value->created_at);
                $sheet->setCellValue('L'.$start_row, $value->updated_at);
                $start_row++;
            }
        });
        
        $writer = new Xlsx($spreadsheet);
        $writer->setPreCalculateFormulas(false);
        $newFile = public_path('created_reports/').$filename;
        $writer->save($newFile);
    

        $headers = ['Content-Type'=> 'application/vnd.ms-excel','Content-Disposition'=> 'attachment;','filename'=>$filename];
        return ['file'=>$newFile, 'filename' => $filename, 'headers'=>$headers];
    }

    public static function depositAccounts($data){
        
        $ts = str_replace('.','',microtime(true));
        $filename = 'Deposit Accounts ('.$ts.').xlsx';
            // $file = public_path('templates/Reports/Detailed Deposit Report.xlsx');
        $file = public_path('templates/Reports/Deposit Accounts.xlsx');
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
        $sheet =$spreadsheet->getSheet(0);
        
        $start_row = 3  ;
        $data['accounts']->orderBy('id','desc')->chunk(200, function($items) use (&$sheet, &$start_row){
            
            foreach ($items as $key=>$value) {
                $sheet->setCellValue('A'.$start_row, $key + 1);
                $sheet->setCellValue('B'.$start_row, $value->office);
                $sheet->setCellValue('C'.$start_row, $value->client_id);
                $sheet->setCellValue('D'.$start_row, $value->fullname);
                $sheet->setCellValue('E'.$start_row, $value->code);
                $sheet->setCellValue('F'.$start_row, $value->accrued_interest);
                $sheet->setCellValue('G'.$start_row, $value->balance);
                $sheet->setCellValue('H'.$start_row, $value->status);
                $start_row++;
            }
        });
        
        
        $writer = new Xlsx($spreadsheet);
        $writer->setPreCalculateFormulas(false);
        $newFile = public_path('created_reports/').$filename;
        $writer->save($newFile);
    

        $headers = ['Content-Type'=> 'application/vnd.ms-excel','Content-Disposition'=> 'attachment;','filename'=>$filename];
        return ['file'=>$newFile, 'filename' => $filename, 'headers'=>$headers];
    }
    public static function loanAccounts($data){
        $ts = str_replace('.','',microtime(true));
        $filename = 'Loan Accounts ('.$ts.').xlsx';
            // $file = public_path('templates/Reports/Detailed Deposit Report.xlsx');
        $file = public_path('templates/Reports/Loan Accounts.xlsx');
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
        $sheet =$spreadsheet->getSheet(0);
    
        $start_row = 3  ;
        
        $data['accounts']->orderBy('id','desc')->chunkById(200, function($items) use (&$sheet, &$start_row){
            foreach ($items as $key=>$value) {
                
                $sheet->setCellValue('A'.$start_row, $value->id);
                $sheet->setCellValue('B'.$start_row, $value->office);
                $sheet->setCellValue('C'.$start_row, $value->client_id);
                $sheet->setCellValue('D'.$start_row, $value->fullname);
                $sheet->setCellValue('E'.$start_row, $value->code);
                $sheet->setCellValue('F'.$start_row, $value->principal);
                $sheet->setCellValue('G'.$start_row, $value->interest);
                $sheet->setCellValue('H'.$start_row, $value->total_loan_amount);
                $sheet->setCellValue('I'.$start_row, $value->interest_balance);
                $sheet->setCellValue('J'.$start_row, $value->principal_balance);
                $sheet->setCellValue('K'.$start_row, $value->total_balance);
                $sheet->setCellValue('L'.$start_row, $value->first_payment_date);
                $sheet->setCellValue('M'.$start_row, $value->last_payment_date);
                $sheet->setCellValue('N'.$start_row, $value->disbursed_at);
                $sheet->setCellValue('O'.$start_row, $value->disbursed_amount);
                $sheet->setCellValue('P'.$start_row, $value->total_deductions);
                $sheet->setCellValue('Q'.$start_row, $value->number_of_months);
                $sheet->setCellValue('R'.$start_row, $value->number_of_installments);
                $sheet->setCellValue('S'.$start_row, $value->interest_rate);
                $sheet->setCellValue('T'.$start_row, $value->status);
                $start_row++;
            }
        },'id','id');
        
        $writer = new Xlsx($spreadsheet);
        $writer->setPreCalculateFormulas(false);
        $newFile = public_path('created_reports/').$filename;
        $writer->save($newFile);
    

        $headers = ['Content-Type'=> 'application/vnd.ms-excel','Content-Disposition'=> 'attachment;','filename'=>$filename];
        return ['file'=>$newFile, 'filename' => $filename, 'headers'=>$headers];
    }
    public static function clientReport($data){
        $ts = str_replace('.','',microtime(true));
        $filename = 'Clients Details ('.$ts.').xlsx';
            // $file = public_path('templates/Reports/Detailed Deposit Report.xlsx');
        $file = public_path('templates/Reports/Client Report.xlsx');
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
        $sheet =$spreadsheet->getSheet(0);
        $start_row = 3  ;
        $data['data']->orderBy('id','desc')->chunk(200, function($items) use (&$sheet, &$start_row){
            
            foreach ($items as $key=>$value) {
                $sheet->setCellValue('A'.$start_row, $key + 1);
                $sheet->setCellValue('B'.$start_row, $value->level);
                $sheet->setCellValue('C'.$start_row, $value->client_id);
                $sheet->setCellValue('D'.$start_row, $value->firstname);
                $sheet->setCellValue('E'.$start_row, $value->middlename);
                $sheet->setCellValue('F'.$start_row, $value->lastname);
                $sheet->setCellValue('G'.$start_row, $value->birthday);
                $sheet->setCellValue('H'.$start_row, $value->civil_status);
                $sheet->setCellValue('I'.$start_row, $value->education);
                $sheet->setCellValue('J'.$start_row, $value->contact_number);
                $sheet->setCellValue('K'.$start_row, $value->street_address);
                $sheet->setCellValue('L'.$start_row, $value->barangay_address);
                $sheet->setCellValue('M'.$start_row, $value->city_address);
                $sheet->setCellValue('N'.$start_row, $value->province_address);
                $sheet->setCellValue('O'.$start_row, $value->zipcode);
                $sheet->setCellValue('P'.$start_row, $value->economic_activity);
                $sheet->setCellValue('Q'.$start_row, $value->status);
                $sheet->setCellValue('R'.$start_row, $value->created_at);
                $start_row++;
            }
            
        },'clients.id','id');
        
        $writer = new Xlsx($spreadsheet);
        $writer->setPreCalculateFormulas(false);
        $newFile = public_path('created_reports/').$filename;
        $writer->save($newFile);
    
        
        $headers = ['Content-Type'=> 'application/vnd.ms-excel','Content-Disposition'=> 'attachment;','filename'=>$filename];
        return ['file'=>$newFile, 'filename' => $filename, 'headers'=>$headers];
    }

    public static function loanInArrearsPrincipalReport($data){
        
        $ts = str_replace('.','',microtime(true));
        $filename = 'LoanInArrears ('.$ts.').xlsx';
        
        $file = public_path('templates/Reports/Loan In Arrears Principal.xlsx');
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
        $sheet =$spreadsheet->getSheet(0);
        
        $data['data']->orderBy('la_id','desc')->chunk(200, function($items) use (&$sheet, &$start_row){
        $start_row = 3;
            foreach ($items as $key=>$value) {
                
                $sheet->setCellValue('A'.$start_row, $key + 1);
                $sheet->setCellValue('B'.$start_row, $value->level);
                $sheet->setCellValue('C'.$start_row, $value->client_id);
                $sheet->setCellValue('D'.$start_row, $value->fullname);
                $sheet->setCellValue('E'.$start_row, $value->code);
                $sheet->setCellValue('F'.$start_row, $value->par_amount);
                $start_row++;
            }
            
        },'la_id');
        
        $writer = new Xlsx($spreadsheet);
        $writer->setPreCalculateFormulas(false);
        $newFile = public_path('created_reports/').$filename;
        $writer->save($newFile);
    
        
        $headers = ['Content-Type'=> 'application/vnd.ms-excel','Content-Disposition'=> 'attachment;','filename'=>$filename];
        return ['file'=>$newFile, 'filename' => $filename, 'headers'=>$headers];
    }


    public static function templateDataImport(){
        return 'hey';
    }

}
