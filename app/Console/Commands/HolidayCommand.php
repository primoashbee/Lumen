<?php

namespace App\Console\Commands;

use App\Jobs\HolidayJob;
use Illuminate\Support\Carbon;
use Illuminate\Console\Command;

class HolidayCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'holiday:implement';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Implement holiday on loan accounts - adjust installments of affected loan accounts';

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
        echo 'Starting....';
        $date = Carbon::parse('2021-08-30');
        dispatch(new HolidayJob($date));

        echo 'done';
    }
}
