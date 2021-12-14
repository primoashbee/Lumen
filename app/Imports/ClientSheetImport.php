<?php

namespace App\Imports;

use App\Log;
use App\Client;
use Carbon\Carbon;
use App\DataMigration;
use App\Traits\Loggable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Events\BeforeSheet;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\BeforeImport;
use Maatwebsite\Excel\Events\ImportFailed;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class ClientSheetImport implements ToModel, WithStartRow,  WithValidation, WithHeadingRow, SkipsEmptyRows, WithBatchInserts, WithEvents,WithChunkReading,ShouldQueue
{
    use Importable, RegistersEventListeners;
    
    protected $migration;
    public function __construct(DataMigration $migration){
        $this->migration =  $migration;
    }
    public function batchSize(): int
    {
        return 20;
    }
    public function headingRow() : int {
        return 1;
    }
    public function chunkSize(): int
    {
        return 20;
    }
    public function map($row) : array
    {
        return $row;
    }
    public function model(array $row){
        
        
        
        return new Client([
            'office_id' => $row['office_id'],
            'client_id' => $row['client_id'],
            'firstname'=> $row['first_name'],
            'middlename'=> $row['middle_name'],
            'lastname'=> $row['last_name'],
            'suffix'=> $row['suffix'],
            'nickname'=> $row['nickname'],
            'gender'=> $row['gender'],
            'birthday'=> \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['birthday']),
            'birthplace'=> $row['birthplace'],
            'civil_status'=> $row['civil_status'],
            'education'=> $row['education'],
            'contact_number'=> $row['contact_number'],

            'street_address'=> $row['street_address'],
            'barangay_address'=> $row['barangay_address'],
            'city_address'=> $row['city_address'],
            'province_address'=> $row['province_address'],
            'zipcode'=> $row['zipcode'],

            'spouse_name'=> $row['spouse_name'],
            'spouse_contact_number'=> $row['spouse_contact_number'],
            'spouse_birthday'=>\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['spouse_birthday']),

            'number_of_dependents' => $row['number_of_dependents'],
            'household_size'=> $row['household_size'],
            'years_of_stay_on_house'=> $row['years_of_stay_on_house'],
            'house_type'=> $row['house_type'],

            'tin'=> $row['tin'],
            'umid'=> $row['umid'],
            'sss'=> $row['sss'],
            'mother_maiden_name'=> $row['mother_maiden_name'],

            'notes'=> $row['notes'],
            'created_by' => 1,
            'created_at'=>\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['creation_date'])
        ]);
    }
    public function startRow(): int
    {
        return 2;
    }


    public function rules() : array 
    {
        return [
            'office_id' => ['required','exists:offices,id'],
            'client_id' =>['required','unique:clients,client_id'],

        ];
    }

    public function customValidationMessages() : array 
    {
        return [
            'office_id.required' => 'Office ID field is required (CLIENT SHEET)',
            'office_id.exists' => 'Invalid Office ID (CLIENT SHEET)',
            'client_id.required' => 'CLIENT ID field is required (CLIENT SHEET)',
            'client_id.unique' => 'CLIENT ID already exists (CLIENT SHEET)',
        ];
    }



    

}
