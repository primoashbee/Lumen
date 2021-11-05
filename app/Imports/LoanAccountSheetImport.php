<?php

namespace App\Imports;

use App\Fee;
use App\Log;
use App\Loan;
use App\Client;
use App\LoanAccount;
use App\DataMigration;
use App\Traits\Loggable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use App\Http\Controllers\LoanAccountController;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;

class LoanAccountSheetImport implements ToCollection, WithValidation, WithStartRow, WithMapping, WithHeadingRow,SkipsEmptyRows, WithBatchInserts
{
    protected $migration;
    public function __construct(DataMigration $migration){
        $this->migration =  $migration;
    }

    public function batchSize(): int
    {
        return 2000;
    }   
    public function collection(Collection $rows)
    {
        
        $user_id = auth()->check() ? auth()->user()->id : 1;
        $fees = Fee::all();
        $bulk_disbursement_id = sha1(time());
        $receipt_number = str_random(16);
        
        foreach ($rows as $row) 
        {
            $loan = Loan::find($row['loan_id']);
            $client = Client::fcid($row['client_id']);
            $number_of_installments = $row['number_of_installments'];
            $annual_rate = $loan->annual_rate;
            $loan_rate = Loan::rates($loan->id)->where('installments',$number_of_installments)->first();
            $loan_interest_rate = $loan_rate->rate;
            $number_of_months = $loan_rate->number_of_months;
            $loan_amount =(double) $row['loan_amount'];
            
            $loan_data  = [
                'product' => $loan->code,
                'principal'=> $loan_amount,
                'annual_rate'=> $annual_rate,
                'interest_rate'=> $loan_interest_rate,
                'monthly_rate'=>$loan->monthly_rate,
                'interest_interval'=>$loan->interest_interval,
                'disbursement_date'=>$row['disbursement_date'],
                'term'=>$loan->installment_method,
                'term_length'=>$number_of_installments,
                'start_date'=>$row['first_payment_date'],
                'office_id'=>(int) $client->office_id
            ];
            
            $calculator = LoanAccount::calculate($loan_data);

            //create loan
            $cgli_fee_amount = (double) $row['cgli_fee'];
            $cgli_premium = (double)$row['cgli_premium'];
            $dst = (double) $row['dst'];
            $mi_fee =(double)$row['mi_fee'];
            $mi_premium = (double) $row['mi_premium'];
            $processing_fee = (double) $row['processing_fee'];


            $total_deductions = $cgli_fee_amount + $cgli_premium + $dst + $mi_fee + $mi_premium + $processing_fee;
            $disbursed_amount = $loan_amount - $total_deductions;
            $loan_acc = $client->loanAccounts()->create([
                'loan_id'=>$loan->id,
                'amount'=>$loan_amount,
                'principal'=>$loan_amount,
                'interest'=>$calculator->total_interest,
                'total_loan_amount'=>$calculator->total_loan_amount,
                'interest_rate'=>$loan_interest_rate,
                'number_of_months'=>$number_of_months,
                'number_of_installments'=>$number_of_installments,

                'total_deductions'=>$total_deductions,
                'disbursed_amount'=>$disbursed_amount, //net disbursement
                
                'total_balance'=>$loan_amount + $calculator->total_interest,
                'principal_balance'=>$loan_amount,
                'interest_balance'=>0,

                'disbursement_date'=>$calculator->disbursement_date,
                'first_payment_date'=>$calculator->start_date,
                'last_payment_date'=>$calculator->end_date,
                'created_by'=>$user_id
            ]);
            $lac = new LoanAccountController;
            $lac->createInstallments($loan_acc,$calculator->installments);

            $dependents = $loan_acc->dependents;
            $installments = $loan_acc->installments;
            //create fees
            $fee_payments = [];
            if($cgli_fee_amount > 0){
                $fee_payments[] = [
                    'loan_account_id'=>$loan_acc->id,
                    'fee_id'=>$fees->where('name','CGLI Fee')->first()->id,
                    'amount'=>$cgli_fee_amount
                ];
            }
            if($cgli_premium > 0){
                $fee_payments[] = [
                    'loan_account_id'=>$loan_acc->id,
                    'fee_id'=>$fees->where('name','CGLI Premium')->first()->id,
                    'amount'=>$cgli_premium
                    
                ];
            }
            if($dst > 0){
                $fee_payments[] = [
                    'loan_account_id'=>$loan_acc->id,
                    'fee_id'=>$fees->where('name','Documentary Stamp Tax')->first()->id,
                    'amount'=>$dst
                ];
            }
            if($mi_fee > 0){
                $fee_payments[] = [
                    'loan_account_id'=>$loan_acc->id,
                    'fee_id'=>$fees->where('name','MI Fee')->first()->id,
                    'amount'=>$mi_fee
                ];
            }
            if($mi_premium > 0){
                $fee_payments[] = [
                    'loan_account_id'=>$loan_acc->id,
                    'fee_id'=>$fees->where('name','MI Premium')->first()->id,
                    'amount'=>$mi_premium
                ];
            }
            if($processing_fee > 0){
                if($loan_amount * 0.015 == $processing_fee) {
                    $fee_payments[] = [
                        'loan_account_id'=>$loan_acc->id,
                        'fee_id'=>$fees->where('name','Processing Fee 1.5%')->first()->id,
                        'amount'=>$processing_fee
                    ];
                }
                if($loan_amount * 0.03 == $processing_fee) {
                    $fee_payments[] = [
                        'loan_account_id'=>$loan_acc->id,
                        'fee_id'=>$fees->where('name','Processing Fee 3%')->first()->id,
                        'amount'=>$processing_fee
                    ];
                }
                if($loan_amount * 0.05 == $processing_fee) {
                    $fee_payments[] = [
                        'loan_account_id'=>$loan_acc->id,
                        'fee_id'=>$fees->where('name','Processing Fee 5%')->first()->id,
                        'amount'=>$processing_fee
                    ];
                }
               
            }
            collect($fee_payments)->map(function($item) use ($loan_acc){
                $loan_acc->feePayments()->create($item);
            });
            
            
            //approve
            $loan_acc->approve($user_id );
            //disburse
            $disbursement_info = [
                'disbursement_date'=>$row['disbursement_date'],
                'first_repayment_date'=>$row['first_payment_date'],
                'payment_method_id'=>1,
                'office_id'=>$client->office_id,
                'disbursed_by'=>$user_id,
                'cv_number'=>$receipt_number,
                'notes'=>'Migration Disbursement'
            ];
            
            $loan_acc->disburse($disbursement_info,true, $bulk_disbursement_id);


            //make payment
            $payment_info =[
                'amount'=>$row['amount_paid'],
                'paid_by'=>$user_id,
                'payment_method_id'=>1,
                'repayment_date'=>Carbon::now(),
                'office_id'=>$client->office_id,
                'receipt_number'=>'Migration Non Receiptable',
                'notes'=>'Migration Repayment'
            ];
            $loan_acc->payV2($payment_info,true);

        }
    }

    public function rules() : array 
    {

       return [
        'client_id'=>['required','exists:clients,client_id'],
        'loan_id'=>['required','exists:loans,id'],
        'loan_amount'=>['required','gt:0'],
        'amount_paid'=>['required','gte:0'],
        'number_of_installments'=>['required','gte:1'],
        'disbursement_date'=>['required','date'],
        'first_payment_date'=>['required','date','after_or_equal:disbursement_date'],
        'processing_fee'=>['nullable','gt:0','possible_processing_fees'],
       ];
    }
    
    public function headingRow() : int 
    {
        return 1;
    }

    public function map($row) : array
    {
        
        // dd($row);
        $row['disbursement_date'] = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['disbursement_date']);
        $row['first_payment_date'] = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['first_payment_date']);
        
        
        return $row;
    }
    public function startRow(): int
    {
        return 2;
    }
    public function customValidationMessages() : array 
    {
        return [
            'client_id.required'=>'CLIENT ID field is required (LOAN ACCOUNTS SHEET).',
            'client_id.exists'=>'CLIENT ID does not exists (LOAN ACCOUNTS SHEET).',

            'loan_id.required'=>'LOAN ID field is required (LOAN ACCOUNTS SHEET).',
            'loan_id.exists'=>'LOAN ID does not exists (LOAN ACCOUNTS SHEET).',
            
            'loan_amount.required'=>'LOAN AMOUNT field is required (LOAN ACCOUNTS SHEET).',
            'loan_amount.gt'=>'LOAN AMOUNT must be greater than 0 (LOAN ACCOUNTS SHEET).',
            
            'amount_paid.required'=>'AMOUNT PAID field is required (LOAN ACCOUNTS SHEET).',
            'amount_paid.gt'=>'AMOUNT PAID must be greater than 0 (LOAN ACCOUNTS SHEET).',

            
            'number_of_installments.required'=>'NUMBER OF INSTALLMENTS field is required (LOAN ACCOUNTS SHEET).',
            'number_of_installments.gt'=>'NUMBER OF INSTALLMENTS must be greater than 0 (LOAN ACCOUNTS SHEET).',
            
            'disbursement_date.required'=>'NUMBER OF INSTALLMENTS field is required (LOAN ACCOUNTS SHEET).',
            'disbursement_date.gt'=>'NUMBER OF INSTALLMENTS must be greater than 0 (LOAN ACCOUNTS SHEET).',

            'first_payment_date.required'=>'FIRST PAYMENT DATE field is required (LOAN ACCOUNTS SHEET).',
            'first_payment_date.date'=>'FIRST PAYMENT DATE should be a valid date (LOAN ACCOUNTS SHEET).',
            'first_payment_date.after_or_equal'=>'FIRST PAYMENT DATE should be on or before DISBURSEMENT DATE (LOAN ACCOUNTS SHEET).',

            'processing_fee.possible_processing_fees'=>'Invalid processing fee amount (LOAN ACCOUNTS SHEET).',

        ];
    }


}
