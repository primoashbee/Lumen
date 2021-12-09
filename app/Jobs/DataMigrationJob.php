<?php

namespace App\Jobs;

use Exception;
use App\DataMigration;
use Illuminate\Bus\Queueable;
use App\Imports\GeneralDataImport;
// use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\Notification;
use App\Notifications\DataMigrationJobFailed;

class DataMigrationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    protected $migration;
    protected $filepath;
    public $timeout = 0;

    public function __construct(DataMigration $migration, $filepath)
    {
        $this->migration = $migration;
        $this->filepath = $filepath;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        try {
            DB::beginTransaction();
            
            Excel::import(new GeneralDataImport($this->migration), $this->filepath);
            
        $this->migration->logs()->create([
                'status'=>200,
                'message'=>'Success'
            ]);
            DB::commit();
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            
             $failures = $e->failures();
             $errors = [];
             foreach ($failures as $failure) {
                 $error['row'] = $failure->row(); // row that went wrong
                 $error['attribute'] = $failure->attribute(); // either heading key (if using heading row concern) or column index
                 $error['errors'] = $failure->errors(); // Actual error messages from Laravel validator
                 $errors[] = $error;
                 
            }
            //  Log::alert('tae');
             Log::alert($errors);
             
             $this->migration->error()->create([
                'migration_id' => $this->migration->id,
                'errors'=>$errors
             ]);
             $this->migration->logs()->create([
                'status'=>422,
                'message'=>'Error'
            ]);
            DB::rollBack();
        }
    }

    public function failed($event){
        Log::alert($event);
        // Notification::route('slack', env('SLACK_WEBHOOK_URL'))->notify(new DataMigrationJobFailed($event));
    }

}
