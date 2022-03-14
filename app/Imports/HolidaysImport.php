<?php

namespace App\Imports;

use App\Holiday;
use App\DataMigration;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithEvents;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class HolidaysImport implements  ToModel, WithHeadingRow, SkipsEmptyRows,WithChunkReading,ShouldQueue
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */


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
    public function startRow(): int
    {
        return 2;
    }
    public function model(array $row)
    {
        return new Holiday([
            'date' => \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['date']),
            'name' => $row['name'],
            'implemented' => $row['implemented'],
            'office_id' => $row['office_id']
        ]);
    }
}
