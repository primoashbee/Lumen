<?php

namespace App\Imports;

use App\DataMigration;

use App\Imports\ClientSheetImport;
use Illuminate\Support\Facades\Log;
use App\Imports\DepositAccountSheetImport;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Events\ImportFailed;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithProgressBar;

class GeneralDataImport implements WithMultipleSheets, WithProgressBar
{
    use Importable, SkipsFailures;

    protected $migration;
    public function __construct(DataMigration $migration)
    {
      $this->migration = $migration;
    }
    public function sheets() : array {

        $migration = $this->migration;
    
        return [
           'CLIENT' => new ClientSheetImport($migration),
           'HOUSEHOLD INCOMES' => new HouseholdIncomeSheet($migration),
           'BUSINESSES' => new BusinessSheetImport($migration),
           'LOAN ACCOUNTS' => new LoanAccountSheetImport($migration),
           'DEPOSIT ACCOUNTS' => new DepositAccountSheetImport($migration),
        ];
    }

    public function registerEvents(): array
    {
        return [
            ImportFailed::class => function(ImportFailed $event) {
                Log::alert($event->getException());
            },
        ];
    }
}
