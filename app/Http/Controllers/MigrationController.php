<?php

namespace App\Http\Controllers;

use App\DataMigration;
use App\Events\TestEvent;
use Illuminate\Http\Request;
use App\Jobs\DataMigrationJob;
use App\Imports\ClientSheetImport;
use App\Imports\GeneralDataImport;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use PHPUnit\Util\Test;

class MigrationController extends Controller
{
    public function index(){
        $data = DataMigration::orderBy('id','desc')->get();
        return view('pages.settings.migration',compact('data'));
    }

    public function upload(Request $request){
        $request->validate([
            'file'=>['required','mimes:xlsx']
        ]);

        // DB::beginTransaction();

        try {
            $id = str_replace('.','',microtime(true));

            // $file = $request->file('file');
            $filename = 'Data Migration  - ' . $id. '.xlsx';
            $file = $request->file('file')->storeAs('migrations',$filename);
            $path = storage_path('app') . '/' . $file;
            
            $data = [
                'name'=>$filename,
                'link'=>'/migrations',
                'user_id'=>auth()->user()->id
            ];

            
            $migration = DataMigration::create($data);
            // dd('titi');
            // dispatch(new TestEvent('wassup dawg'));
            dispatch(new DataMigrationJob($migration, $path));
            // Excel::import(new GeneralDataImport($migration), $path);
            
            // DB::commit();
            return redirect()->route('settings.import')->with('message','File succesfully uploaded');
        }catch (Exception $e){
            dd($e->getMessage());
        }

    }

    public function logs(DataMigration $migration){
            
        $logs = !is_null($migration->error) ? collect($migration->error->errors) : collect([]);
        
        
        return view('pages.settings.migration-errors', compact('logs'));
    }
}
