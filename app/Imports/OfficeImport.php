<?php

namespace App\Imports;

use App\Office;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class OfficeImport implements ToModel, WithHeadingRow,SkipsEmptyRows
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */

    public function map($row) : array
    {
        return $row;
    }
    public function headingRow() : int {
        return 1;
    }
    public function model(array $row)
    {
        return new Office([
            
            'code'=>$row['code'],
            'parent_id'=>$row['parent_id'],
            'level_in_number'=>$row['level_in_number'],
            'name'=>$row['name'],
            'level'=>$row['level']
        ]);
    }
}
