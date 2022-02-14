<?php

namespace App\Imports;

use App\Holiday;
use App\DataMigration;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;

class HolidaysImport implements ToModel,WithStartRow, WithHeadingRow, SkipsEmptyRows,WithValidation
{
    use Importable, RegistersEventListeners;
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    protected $migration;
    public function __construct(DataMigration $migration){
        $this->migration =  $migration;
    }
    

    public function headingRow() : int {
        return 1;
    }
    public function map($row) : array
    {
        return $row;
    }
    public function startRow(): int
    {
        return 2;
    }
    public function model(array $row)
    {
        
        return new Holiday([
            'date'=> \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['date']),
            'name' => $row['name'],
            'office_id' => $row['office'],
            'implemented' => $row['implemented'],
        ]);
    }

    public function rules() : array {
       return [ "implemented" => ['nullable','boolean']];
    }
}
