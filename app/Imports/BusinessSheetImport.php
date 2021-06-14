<?php

namespace App\Imports;

use App\Log;
use App\Business;
use App\DataMigration;
use App\Traits\Loggable;
use App\Rules\ServiceType;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;

class BusinessSheetImport implements ToModel, WithStartRow, WithValidation,SkipsEmptyRows, WithBatchInserts
{
    use Loggable;

    protected $migration;
    public function __construct(DataMigration $migration){
        $this->migration =  $migration;
    }


    public static function beforeSheet(BeforeSheet $event){
        $migration = DataMigration::where('name',$event->getConcernable()->migration->name)->first();
        self::log($migration,'Processing Household Incomes Sheet',200);
    }

    public static function afterSheet(AfterSheet $event){
        $migration = DataMigration::where('name',$event->getConcernable()->migration->name)->first();
        self::log($migration,'Household Incomes Processed',200);
    }


    

    public function batchSize(): int
    {
        return 2000;
    }
    public function model(array $row){
        
        
        return new Business([
            'client_id'=>$row[0],
            'business_address'=>$row[1],
            'service_type'=>$row[2],
            'monthly_gross_income'=>$row[3],
            'monthly_operating_expense'=>$row[4],
            'monthly_net_income'=>$row[5],
        ]);
        
    } 

    public function startRow(): int 
    {
        return 2;
    }

    public function rules(): array
    {
        return [
            0=>['required','exists:clients,client_id'],
            1=>['required'],
            2=>['required',new ServiceType],
            3=>['required','gte:0'],
            4=>['required','gte:0'],
            5=>['required','gte:0'],
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            '0.required'=>'CLIENT ID field is required (BUSINESS SHEET)',
            '0.exists'=>'CLIENT ID does not exists in our current records (BUSINESS SHEET)',
            '1.required'=>'BUSINESS ADDRESS field is required (BUSINESS SHEET)',
            '2.required'=>'SERVICE TYPE field is required (BUSINESS SHEET)',
            '2.required'=>'SERVICE TYPE field is required (BUSINESS SHEET)',
            '3.required'=>'MONTHLY GROSS INCOME field is required (BUSINESS SHEET)',
            '3.gte'=>'MONTHLY GROSS INCOME should be greater or equal to 0 (BUSINESS SHEET)',
            '4.required'=>'MONTHLY GROSS INCOME field is required (BUSINESS SHEET)',
            '4.gte'=>'MONTHLY OPERATING EXPENSE should be greater or equal to 0 (BUSINESS SHEET)',
            '3.required'=>'MONTHLY NET INCOME field is required (BUSINESS SHEET)',
            '3.gte'=>'MONTHLY NET INCOME should be greater or equal to 0 (BUSINESS SHEET)',
        
        ];
    }
}
