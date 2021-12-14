<?php

namespace App\Imports;

use App\DataMigration;
use App\DepositAccount;
use App\Traits\Loggable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;

class DepositAccountSheetImport implements ToModel, WithStartRow, WithValidation,SkipsEmptyRows, WithBatchInserts, WithHeadingRow
// class DepositAccountSheetImport implements ToModel
{

    
    protected $migration;
    public function __construct(DataMigration $migration){
        $this->migration =  $migration;
    }


    public function batchSize(): int
    {
        return 50;
    }
    
    public function headingRow() : int 
    {
        return 1;
    }

    public function model(array $row){
        
        return new DepositAccount([
            'client_id'=>$row['client_id'],
            'deposit_id'=>$row['deposit_id'],
            'balance'=>$row['balance'],
            'accrued_interest'=>$row['accrued_interest'],
            'status'=>$row['status']
        ]);
   }

   public function startRow(): int
   {
       return 2;
   }

   public function rules() : array 
   {
       return [
           'client_id'=> ['required'],
           'deposit_id'=>['required', 'exists:deposits,id'],
           'balance' =>['required', 'gte:0'],
           'accrued_interest' =>['required', 'gte:0']
       ];
   }

   public function customValidationMessages() : array 
   {
       return [
           'client_id.required' => 'CLIENT ID field is required (DEPOSIT ACCOUNT SHEET)',
           'deposit_id.required' => 'DEPOSIT ID field is required  (DEPOSIT ACCOUNT SHEET)',
           'deposit_id.exists' => 'Invalid DEPOSIT ID  (DEPOSIT ACCOUNT SHEET)',
           'balance.required' => 'BALANCE field is required  (DEPOSIT ACCOUNT SHEET)',
           'balance.gte' => 'BALANCE must be greater than or equal to 0  (DEPOSIT ACCOUNT SHEET)',
           'accrued_interest.required' => 'ACCRUED INTEREST field is required  (DEPOSIT ACCOUNT SHEET)',
           'accrued_interest.gte' => 'ACCRUED INTEREST must be greater than or equal to 0 (DEPOSIT ACCOUNT SHEET)',
       ];
   }
}
