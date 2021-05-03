<?php

namespace App\Imports;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\ToModel;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Maatwebsite\Excel\Concerns\ToCollection;

class TestImport implements ToModel
{
    /**
    * @param Collection $collection
    */
    
    public function model(array $row)
    {
        
        echo '<table><tbody>';
        $ctr = 0;
        foreach ($row as $item) {
            
            if($ctr==0){
                echo '<tr>';
                echo '<td>' . \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($item)->format('F d, Y') .'</td>';
                $ctr++;
            }elseif($ctr==1){
                echo '<td>' . $item .'</td>';
                $ctr++;
            }elseif($ctr==2){
                echo '<td>' . $item .'</td>
                </tr>';
                $ctr=0;
            }

            
            
        }
        echo '</table></tbody>';
    }
    
}
