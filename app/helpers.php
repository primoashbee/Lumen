<?php 

use App\User;
use App\Client;
use App\Office;
use App\Cluster;
use Carbon\Carbon;
use App\OfficeUser;
use Illuminate\Support\Str;

use App\Imports\OfficeImport;

use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
  
    function createAdminAccount(){
        $user = User::create([
            'firstname' => 'Ashbee',
            'lastname' => 'Morgado',
            'middlename' => 'Allego',
            'gender' => 'Male',
            'birthday' => Carbon::parse('1994-11-26'),
            'email' => 'ashbee.morgado@icloud.com',
            'notes'=>'ajalksdjfdlksafjaldf',
            'password' => Hash::make('sv9h4pld')
        ]);
        
            OfficeUser::create([
                'user_id'=>$user->id,
                'office_id'=>2
            ]);    
    }
    function generateStucture   (){
        $structure = Excel::toCollection(new OfficeImport, "public/OFFICE STRUCTURE.xlsx");
        $data = array();
        $ctr = 0;
    
        foreach($structure[0] as $level){
            if ($ctr>0) {
                $data[] = array(
                'id'=>$level[0],
                'parent_id'=>$level[2],
                'level'=>$level[4],
                'name'=>$level[3],
                'code'=>$level[1],
                'created_at'=> Carbon::now(),
                'updated_at'=> Carbon::now(),
                );
                // echo $level[3].' : '.$level[4].'<br>';
            }
            $ctr++;
        }
     
        Office::insert($data);
    }
    
    function createUser($branches=5){
        $data = array(
            'firstname'=>'Angeles',
            'middlename'=>'-',
            'lastname'=>'Branch',
            'gender'=>'Male',
            'birthday'=>'1994-11-26',
            'notes'=>'Notes are here. Wala lang. Test notes.',
            'email'=>'angeles@light.org.ph',
            'password'=> Hash::make('sv9h4pld'),
            'created_by'=>0

        );

        $user = User::create($data);

        OfficeUser::create([
            'user_id'=>$user->id,
            'office_id'=>Office::where('name','ANGELES')->first()->id
        ]);
        
        $data = array(
            'firstname'=>'Ashbee',
            'middlename'=>'Allego',
            'lastname'=>'Morgado',
            'gender'=>'Male',
            'birthday'=>'1994-11-26',
            'notes'=>'Notes are here. Wala lang. Test notes.',
            'email'=>'ashbee.morgado@icloud.com',
            'password'=> Hash::make('sv9h4pld'),
            'created_by'=>0

        );

        $user = User::create($data);

        

        $offices = Office::where('level','branch')->get()->random($branches);
    
        foreach($offices as $office){
            OfficeUser::create([
                'user_id'=>$user->id,
                'office_id'=>$office->id
            ]);
        }
    }

    function unauthorized(){
        return (array('msg'=>'Unauthorized Request'));
    }

    function generateCluster(){
        $office = Office::where('name','ANGELES')->first();
        $user = User::all()->random(1)->first();
        

        for($x=1;$x<=100;$x++){
            $client = Client::all()->random(1)->first();
            Cluster::create([
                'officer_id'=>$user->id,
                'office_id'=>$office->id,
                'client_id'=>$client->id,
                'code'=> 'ANG'.pad($x,3),
                'notes'=> 'hahaha'
            ]);
        }
            
    }
    function pad($number, $character, $padder='0'){
        return str_pad($number, $character, $padder, STR_PAD_LEFT);
    }

    function generateClientID($count=100){
        $branch = "010ANG";
        $ids = [];
        
        for($x=1; $x<=1000;$x++){
            $client_id = $branch."-PC".pad($x,5);
            $ids[] = $client_id;
        }

        return $ids;
    }

    function makeClientID($office_id){

        $office = Office::find($office_id);

        if($office->level=="branch"){
            $code = $office->code;
            $count = Client::where('office_id',$office_id)->count();
            return $code . '-PC' . pad($count + 1, 5);
        }

        $office = $office->getTopOffice('branch');
        $code = $office->code;
        $count = Client::where('office_id',$office_id)->count();
    
        return $code . '-PC' . pad($count + 1, 5);

    }
    function getNextID($string){
        // substr("ASDASDAS",)
        return substr($string, -5, 5);
    }

    function hasString($string, $match){
        return  Str::contains($string, $match);
    }

    function checkClientPaths(){
        if(!Storage::disk('local')->exists('signatures')){
            Storage::makeDirectory('public/signatures');
        }
        if(!Storage::disk('local')->exists('profile_photos')){
            Storage::makeDirectory('public/profile_photos');
        }
    }
?>