<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Support\Carbon;
use Illuminate\Console\Command;
use App\Events\LoanAccountPayment;

class TestLoanAccountPayment extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'event:payment';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Payment for LoanA Account';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $payment_summary = [
            'interest_paid'=>12000,
            'principal_paid'=>33000,
            'total_paid'=>45000
        ];
        
        $payload = [
            'date'=>Carbon::parse(now())->format('d-F'),
            'amount'=>45000,
            'summary'=>$payment_summary
        ];

        $office_id = 21;
        event(new LoanAccountPayment($payload, $office_id, User::find(1)->id, 1));
        return 0;
    }
}
