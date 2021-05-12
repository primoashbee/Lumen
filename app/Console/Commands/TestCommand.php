<?php

namespace App\Console\Commands;

use App\Notification;
use Illuminate\Console\Command;

class TestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        return Notification::create([
            'link'=>'https://tutsforweb.com/how-to-set-up-task-scheduling-cron-job-in-laravel/',
            'msg'=>'You can change your mind at any time by clicking the unsubscribe link in the footer of any email you receive from us, or by contacting us at info@tutsforweb.com. We will treat your information with respect. For more information about our privacy practices please visit our website. By clicking below, you agree that we may process your information in accordance with these terms.',
            'from'=>1,
            'to'=>3,
        ]);
    }
}
