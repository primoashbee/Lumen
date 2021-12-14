<?php

namespace App\Imports;

use App\DataMigration;
use App\HouseholdIncome;
use App\Traits\Loggable;
use App\Rules\ServiceType;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Concerns\WithChunkReading;

// class HouseholdIncomeSheet implements ToModel, WithHeadingRow, WithStartRow
class HouseholdIncomeSheet implements ToModel, WithStartRow, WithHeadingRow,WithChunkReading, WithValidation,SkipsEmptyRows, WithBatchInserts, WithMapping,WithCalculatedFormulas,ShouldQueue

{
    
    protected $migration;
    public function __construct(DataMigration $migration){
        $this->migration =  $migration;
    }

   

    
    public function model(array $row)
    {
        return new HouseholdIncome([
            'client_id'=>$row['client_id'],
            'is_self_employed'=>$row['is_self_employed'],
            'service_type'=>$row['service_type'],
            'service_type_monthly_gross_income'=>$row['service_type_monthly_gross_income'],
            'is_employed'=>$row['is_employed'],
            'employed_position'=>$row['employed_position'],
            'employed_company_name'=>$row['employed_company_name'],
            'employed_monthly_gross_income'=>$row['employed_monthly_gross_income'],
            'spouse_is_self_employed'=>$row['spouse_is_self_employed'],
            'spouse_service_type'=>$row['spouse_service_type'],
            'spouse_service_type_monthly_gross_income'=>$row['spouse_service_type_monthly_gross_income'],
            'spouse_is_employed'=>$row['spouse_is_employed'],
            'spouse_employed_position'=>$row['spouse_employed_position'],
            'spouse_employed_company_name'=>$row['spouse_employed_company_name'],
            'spouse_employed_monthly_gross_income'=>$row['spouse_employed_monthly_gross_income'],
            'has_remittance'=>$row['has_remittance'],
            'remittance_amount'=>$row['remittance_amount'],
            'has_pension'=>$row['has_pension'],
            'pension_amount'=>$row['pension_amount'],
            'total_household_expense' => $row['total_household_expense'],
            'total_household_income'=>$row['total_household_income'],
        ]);
    }

    public function map($row) : array
    {
        // dd($row);
        return $row;
        // return [
        //     'is_self_employed' =>
        // ]
    }
    public function startRow(): int
    {
        return 2;
    }
    public function batchSize(): int
    {
        return 500;
    }
    public function chunkSize(): int
    {
        return 1000;
    }
    public function headingRow() : int {
        return 1;
    }

    public function rules() : array 
    {
        return [
            'client_id' =>['required','exists:clients,client_id'],
            "is_self_employed" => ['nullable','boolean'],
            "service_type" => ['nullable', new ServiceType],
            "service_type_monthly_gross_income" => ['nullable', new ServiceType],
            "is_employed" => ['nullable','boolean'],
            "employed_position" => ['nullable'],
            "employed_company_name" => ['nullable'],
            "employed_monthly_gross_income" => ['nullable','gt:0'],
            "spouse_is_self_employed" => ['nullable','boolean'],
            "spouse_service_type" => ['nullable', new ServiceType],
            "spouse_service_type_monthly_gross_income" => ['nullable','gt:0'],
            "spouse_is_employed" => ['nullable','boolean'],
            "spouse_employed_position" => ['nullable'],
            "spouse_employed_company_name" => ['nullable'],
            "spouse_employed_monthly_gross_income" => ['nullable','gt:0'],
            "has_remittance" => ['nullable','boolean'],
            'total_household_expense' => ['nullable','gt:0'],
            "remittance_amount" => ['nullable'],
            "remittance_amount" => ['nullable'],
            "has_pension" => ['nullable','boolean'],
            "pension_amount" => ['nullable','gt:0'],
            "total_household_income" => ['nullable','gt:0'],
        ];
    }

    public function customValidationMessages() : array 
    {
        return [
            'client_id.required' => 'CLIENT ID field is required (HOUSEHOLD INCOMES)',
            'client_id.exists' => 'CLIENT ID does not exists (HOUSEHOLD INCOMES)',
        ];
    }



}
