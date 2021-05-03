<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;

class CollectionSheetExport implements 
        FromView, 
        ShouldAutoSize, 
        WithEvents, 
        WithDrawings,
        WithStartRow,
        WithHeadingRow
{
    public $data;
    public $max_column;
    use Exportable;

    /**
    * @return \Illuminate\Support\Collection
    */
    public function __construct($data)
    {   
        $this->data = $data;
        $this->max_column = 7;
        if($data->has_deposit){
            $this->max_column = 7 + count($data->deposit_list);
        }
    }
    public function view() : View
    {
        // // dd($this->data->data->first());
        // foreach($this->data->data as $value){
        //     dd($value);
        // }
        // dd($this->data);
        
        return view('exports.ccr',['data'=>$this->data]);
    }

    public function collection(){
        return User::all();
    }
    public function registerEvents() : array 
    {
        $max_column = $this->max_column + 1;
        $column = range('A','Z');
        
        return [
            AfterSheet::class => function(AfterSheet $event) use ($max_column, $column){
                $event->sheet->getStyle('A8:'.$column[$max_column].'8')->applyFromArray([
                    'font'=> [
                        'bold'=>true,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_MEDIUM,
                            'color' => ['argb' => '000000'],

                        ],
                    ],
                    'fill' => [
                        'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_GRADIENT_LINEAR,
                        'rotation' => 90,
                        'startColor' => [
                            'argb' => 'FFA0A0A0',
                        ],
                        'endColor' => [
                            'argb' => 'FFFFFFFF',
                        ],
                    ],
                ]);
            }
        ];
      
    }
    public function drawings()
    {
        $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
        $drawing->setName('Logo');
        $drawing->setDescription('This is my logo');
        $drawing->setPath(public_path('logo.png'));
        $drawing->setHeight(90);
        $drawing->setCoordinates('B1');
        return $drawing;
        
    }

    public function startRow(): int
    {
        return 9;
    }

    public function headingRow(): int{
        return 8;
    }
}
