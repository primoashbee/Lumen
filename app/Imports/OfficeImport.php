<?php

namespace App\Imports;

use App\Office;
use Maatwebsite\Excel\Concerns\ToModel;

class OfficeImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Office([
            'id' => $row['id'],
            'code'=>$row['code'],
            'parent_id'=>$row['parent_id'],
            'name'=>$row['name'],
            'level'=>$row['level']
        ]);
    }
}
