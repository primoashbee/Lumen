<?php

namespace App\Http\Controllers;

use App\Client;
use App\Office;
use Carbon\Carbon;
use App\Rules\Gender;
use App\DepositAccount;
use App\Rules\OfficeID;
use App\HouseholdIncome;
use App\Rules\HouseType;
use App\Rules\CivilStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\ClientRequest;
use App\Rules\EducationalAttainment;

use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
{
  
    protected $profile_path = 'storage/profile_photos/';
    protected $signature_path = 'storage/signatures/';

    //get branches of logged in user upon creating client
    public function index(){
        $branches = auth()->user()->scopesBranch();
        return view('pages.create-client',compact('branches'));
    }
    

    public function step(){
        return view('pages.create-client');
    }

    public function createV1(ClientRequest  $request){
        $req = Client::clientExists($request);
            
        if($req['exists']){
            
            return response()->json($req,422);
        }

        $client_id = Office::makeClientID($request->office_id);
        $filename = $client_id.'.jpeg';
        checkClientPaths();

        
        
        DB::beginTransaction();
        try{
            $client = Client::create([
                'client_id' => $client_id,
                'firstname' => $request->firstname,
                'middlename'=>$request->middlename,
                'lastname'  =>$request->lastname,
                'suffix'=>$request->suffix,
                'nickname'=>$request->nickname,
                'gender'=> $request->gender,
                'profile_picture_path' => $this->profile_path . $filename,
                'signature_path' => $this->signature_path . $filename,
                'birthday' => Carbon::parse($request->birthday),
                'birthplace' => $request->birthplace,
                'civil_status' => $request->civil_status,
                'education' => $request->education,
                'fb_account' => $request->fb_account,
                'contact_number'=>$request->contact_number,
                'street_address'=> $request->street_address,
                'barangay_address' => $request->barangay_address,
                'city_address' => $request->city_address,
                'province_address' => $request->province_address,
                'zipcode' => $request->zipcode,
        
                'spouse_name' => $request->spouse_name,
                'spouse_contact_number' => $request->spouse_contact_number,
                'spouse_birthday' =>  Carbon::parse($request->spouse_birthday),
                'number_of_dependents' => $request->number_of_dependents,
                'household_size' =>$request->household_size,
                'years_of_stay_on_house' => $request->years_of_stay_on_house,
                'house_type' => $request->house_type,
                'tin' => $request->tin,
                'umid' => $request->umid,
                'sss' => $request->sss,
                'mother_maiden_name' => $request->mother_maiden_name,
                'notes' => $request->notes,
                'office_id' => $request->office_id,
                'created_by' => auth()->user()->id,
                
            ]);
            $b = $request->businesses;
            foreach($request->businesses as $business){
                $client->businesses()->create([
                    'business_address'=>$business['business_address'],
                    'service_type'=>$business['service_type'],
                    'monthly_gross_income'=>$business['monthly_gross_income'],
                    'monthly_operating_expense'=>$business['monthly_operating_expense'],
                    'monthly_net_income'=>round($business['monthly_gross_income'] - $business['monthly_operating_expense'],2)
                ]);
            }
          $service_type_monthly_gross_income = round($request->service_type_monthly_gross_income,2);
            $employed_monthly_gross_income = round($request->employed_monthly_gross_income,2);
            $spouse_service_type_monthly_gross_income = round($request->spouse_service_type_monthly_gross_income,2);
            $spouse_employed_monthly_gross_income = round($request->spouse_employed_monthly_gross_income,2);
                
            $remittance = round($request->remittance_amount,2);
            $pension = round($request->pension_amount,2);

            

            $total_household_income = 
                round($service_type_monthly_gross_income + 
                $employed_monthly_gross_income + 
                $spouse_service_type_monthly_gross_income + 
                $spouse_employed_monthly_gross_income + 
                $remittance + 
                $pension,2);
            $client->household_income()->create([
                'is_self_employed'=>$request->is_self_employed,
                'service_type'=>$request->service_type,
                'service_type_monthly_gross_income'=>$service_type_monthly_gross_income,
                'is_employed'=>$request->is_employed,
                'employed_position'=>$request->employed_position,
                'employed_company_name'=>$request->employed_company_name,
                'employed_monthly_gross_income'=>$employed_monthly_gross_income,
    
                'spouse_is_self_employed'=>$request->spouse_is_self_employed,
                'spouse_service_type'=>$request->spouse_service_type,
                'spouse_service_type_monthly_gross_income'=>$spouse_service_type_monthly_gross_income,
                'spouse_is_employed'=>$request->spouse_is_employed,
                'spouse_employed_position'=>$request->spouse_employed_position,
                'spouse_employed_company_name'=>$request->spouse_employed_company_name,
                'spouse_employed_monthly_gross_income'=>$spouse_employed_monthly_gross_income,
    
                'has_remittance'=>$request->has_remittance,
                'remittance_amount' => $remittance,
                'has_pension'=>$request->has_pension,
                'pension_amount' => $pension,

                
                'total_household_income'=>$total_household_income 
            ]);

            if($request->hasFile('profile_picture_path')){
                ini_set('memory_limit','512M');
                $image = $request->file('profile_picture_path');
                // $filename = $image->getClientOriginalName();   
                $image_resize = Image::make($image->getRealPath());
                $image_resize->resize(600, 600);
                $image_resize->save(public_path($this->profile_path . $filename), 50);
                ini_set('memory_limit','128M');
            }
            if($request->hasFile('signature_path')){
                ini_set('memory_limit','512M');
                $image = $request->file('signature_path');
                // $filename = $image->getClientOriginalName();   
                $image_resize = Image::make($image->getRealPath());
                $image_resize->resize(600, 300);
                $image_resize->save(public_path($this->signature_path . $filename),50);
                ini_set('memory_limit','128M');
            }
            DB::commit();
            return response()->json(['msg'=>'Client succesfully created'],200);
        }catch(ValidationException $e){
            // Rollback and then redirect
            // back to form with errors
            DB::rollback();   
            return response()->json(['errors'=>$e->getErrors()],422);
        }catch(\Exception $e){
            DB::rollback();
            throw $e;
        }

    }
    //create client using post
    public function create(Request $request){
        
        // var_dump($request->spouse_name);
        $request->is_self_employed =  filter_var($request->is_self_employed, FILTER_VALIDATE_BOOLEAN);
        $request->is_employed =  filter_var($request->is_employed, FILTER_VALIDATE_BOOLEAN);
        $request->spouse_is_self_employed =  filter_var($request->spouse_is_self_employed, FILTER_VALIDATE_BOOLEAN);
        $request->spouse_is_employed =  filter_var($request->spouse_is_employed, FILTER_VALIDATE_BOOLEAN);
        $request->has_remittance =  filter_var($request->has_remittance, FILTER_VALIDATE_BOOLEAN);
        $request->has_pension =  filter_var($request->has_pension, FILTER_VALIDATE_BOOLEAN);
        
        $req = Client::clientExists($request);
        
        if($req['exists']){
            
            return response()->json($req,422);
        }
        $this->validator($request->all())->validate();
        $client_id = Office::makeClientID($request->office_id);
        

        $client_id = makeClientID($request->office_id);
        $filename = $client_id.'.jpeg';
        checkClientPaths();
        

        DB::beginTransaction();
        try {
            Client::create([
                'client_id' => $client_id,
                'firstname' => $request->firstname,
                'middlename'=>$request->middlename,
                'lastname'  =>$request->lastname,
                'suffix'=>$request->suffix,
                'nickname'=>$request->firstname,
                'gender'=> $request->gender,
                'profile_picture_path' => $this->profile_path . $filename,
                'signature_path' => $this->signature_path . $filename,
                'birthday' => Carbon::parse($request->birthday),
                'birthplace' => $request->birthplace,
                'civil_status' => $request->civil_status,
                'education' => $request->education,
                'fb_account' => $request->fb_account,
                'contact_number'=>$request->contact_number,
                'street_address'=> $request->street_address,
                'barangay_address' => $request->barangay_address,
                'city_address' => $request->city_address,
                'province_address' => $request->province_address,
                'zipcode' => $request->zipcode,
                'business_address' =>$request->business_address,
                'spouse_name' => $request->spouse_name,
                'spouse_contact_number' => $request->spouse_contact_number,
                'spouse_birthday' =>  Carbon::parse($request->spouse_birthday),
                'number_of_dependents' => $request->number_of_dependents,
                'household_size' =>$request->household_size,
                'years_of_stay_on_house' => $request->years_of_stay_on_house,
                'house_type' => $request->house_type,
                'tin' => $request->tin,
                'umid' => $request->umid,
                'sss' => $request->sss,
                'mother_maiden_name' => $request->mother_maiden_name,
                'notes' => $request->notes,
                'office_id' => $request->office_id,
                'created_by' => auth()->user()->id,
                
            ]);
            
            $service_type_monthly_gross_income = round($request->service_type_monthly_gross_income);
            $employed_monthly_gross_income = round($request->employed_monthly_gross_income);
            $spouse_service_type_monthly_gross_income = round($request->spouse_service_type_monthly_gross_income);
            $spouse_employed_monthly_gross_income = round($request->spouse_employed_monthly_gross_income);
                
            $remittance = round($request->remittance_amount);
            $pension = round($request->pension_amount);

            

            $total_household_income = 
                $service_type_monthly_gross_income + 
                $employed_monthly_gross_income + 
                $spouse_service_type_monthly_gross_income + 
                $spouse_employed_monthly_gross_income + 
                $remittance + 
                $pension;
            
            HouseholdIncome::create([
                'client_id'=>$client_id,
    
                'is_self_employed'=>$request->is_self_employed,
                'service_type'=>$request->service_type,
                'service_type_monthly_gross_income'=>$service_type_monthly_gross_income,
                'is_employed'=>$request->is_employed,
                'employed_position'=>$request->employed_position,
                'employed_company_name'=>$request->employed_company_name,
                'employed_monthly_gross_income'=>$employed_monthly_gross_income,
    
                'spouse_is_self_employed'=>$request->spouse_is_self_employed,
                'spouse_service_type'=>$request->spouse_service_type,
                'spouse_service_type_monthly_gross_income'=>$spouse_service_type_monthly_gross_income,
                'spouse_is_employed'=>$request->spouse_is_employed,
                'spouse_employed_position'=>$request->spouse_employed_position,
                'spouse_employed_company_name'=>$request->spouse_employed_company_name,
                'spouse_employed_monthly_gross_income'=>$spouse_employed_monthly_gross_income,
    
                'has_remittance'=>$request->has_remittance,
                'remittance_amount' => $remittance,
                'has_pension'=>$request->has_pension,
                'pension_amount' => $pension,

                
                'total_household_income'=>$total_household_income
            ]);
            
            if($request->hasFile('profile_picture_path')){
                ini_set('memory_limit','512M');
                $image = $request->file('profile_picture_path');
                // $filename = $image->getClientOriginalName();   
                $image_resize = Image::make($image->getRealPath());
                $image_resize->resize(600, 600);
                $image_resize->save(public_path($this->profile_path . $filename), 50);
                ini_set('memory_limit','128M');
            }
            if($request->hasFile('signature_path')){
                ini_set('memory_limit','512M');
                $image = $request->file('signature_path');
                // $filename = $image->getClientOriginalName();   
                $image_resize = Image::make($image->getRealPath());
                $image_resize->resize(600, 300);
                $image_resize->save(public_path($this->signature_path . $filename),50);
                ini_set('memory_limit','128M');
            }
            DB::commit();
            return response()->json(['msg'=>'Client succesfully created'],200);
        }catch(ValidationException $e){
            // Rollback and then redirect
            // back to form with errors
            DB::rollback();   
            return response()->json(['errors'=>$e->getErrors()],422);
        }catch(\Exception $e){
            DB::rollback();
            throw $e;
        }

        
       
    }

    //validator for creating clien
    public function validator(array $data, $for_update = false){
        
        $msgs = [
            'office_id.required' => 'The Linked to field is required',
            'profile_picture_path.required' => 'The profile photo is required',
            'profile_picture_path.image' => 'The profile photo  must be an image',
            'profile_picture_path.mimes' => 'The profile photo  must be a type of jpeg, png, jpg',
            
            'signature_path.required' => 'The signature photo is required',
            'signature_path.image' => 'The signature photo must be an image',
            'signature_path.mimes' => 'The signature photo must be a type of jpeg, png, jpg',

        ];
        if ($for_update) {
           
            return Validator::make(
                $data,
                [
                    'office_id'=>['required', new OfficeID],
                    'firstname'=>['required'],
                    'lastname'=>['required'],
                    'gender'=>['required', new Gender],
                    'contact_number'=>'required',
                    'birthday'=>'required|date',
                    'birthplace'=>'required',
                    'education'=>['required', new EducationalAttainment],
                    'civil_status'=>['required', new CivilStatus],
                    'street_address'=>'required',
                    'barangay_address'=>'required',
                    'city_address'=>'required',
                    'province_address'=>'required',
                    'zipcode'=>'required',
                    'business_address'=>'required',
                    'number_of_dependents'=>'required|integer|gt:0',
                    'household_size'=>'required|integer|gt:0',
                    'years_of_stay_on_house'=>'required|integer|gt:0',
                    'house_type'=>['required', new HouseType],
                    'spouse_name' => 'sometimes',
                    'spouse_contact_number' => 'sometimes',
                    'spouse_birthday' => 'sometimes|date',
                    'tin'=>'required',
                    'sss'=>'required',
                    'umid'=>'required',
                    'mother_maiden_name'=>'required',
                    'mother_maiden_name'=>'required',
                    'total_household_income'=>'required|integer|gt:0',
                    'profile_picture_path' =>'sometimes|required|image|mimes:jpeg,png,jpg|max:9000',
                    'signature_path' =>'sometimes|required|image|mimes:jpeg,png,jpg|max:5000',
                ],
                $msgs
            );            
        }
        return Validator::make(
            $data,
            [
                'office_id'=>['required', new OfficeID],
                'firstname'=>['required'],
                'lastname'=>['required'],
                'gender'=>['required', new Gender],
                'contact_number'=>'required',
                'birthday'=>'required|date',
                'birthplace'=>'required',
                'education'=>['required', new EducationalAttainment],
                'civil_status'=>['required', new CivilStatus],
                'street_address'=>'required',
                'barangay_address'=>'required',
                'city_address'=>'required',
                'province_address'=>'required',
                'zipcode'=>'required',
                'business_address'=>'required',
                'number_of_dependents'=>'required|integer|gt:0',
                'household_size'=>'required|integer|gt:0',
                'years_of_stay_on_house'=>'required|integer|gt:0',
                'house_type'=>['required', new HouseType],
                'spouse_name' => 'sometimes',
                'spouse_contact_number' => 'sometimes',
                'spouse_birthday' => 'sometimes',
                'tin'=>'required',
                'sss'=>'required',
                'umid'=>'required',
                'mother_maiden_name'=>'required',
                'mother_maiden_name'=>'required',
                'total_household_income'=>'required|integer|gt:0',
                'profile_picture_path' =>'required|image|mimes:jpeg,png,jpg|max:9000',
                'signature_path' =>'required|image|mimes:jpeg,png,jpg|max:5000',
            ],
            $msgs
        );



    }

    //return client-list page for viewing
    public function list(){
        return view('pages.client-list');
    }

    //return JSON data when filtering the list via component
    public function getList(Request $request){
        if($request->has('limited')){
            $clients = Client::like($request->office_id, $request->search,true)->paginate(30);
            return response()->json($clients);
        }
        $clients = Client::like($request->office_id, $request->search)->paginate(30);
        return response()->json($clients);
    }

    public function getClient(Request $request){
        $client = Client::where('client_id',$request->client_id)->first();
        if($client===null){
            abort(503);
            return response()->route('client.list');
        }
        return view('pages.client-profile',compact('client'));
    }

    public function view($client_id){
        $client = Client::with('household_income')->where('client_id',$client_id)->first();
        if($client===null){
            abort(503);
            return response()->route('client.list');
        }
        return view('pages.client-profile',compact('client'));
        
    }

    public function editClient(Request $request){
        $household_fields = HouseholdIncome::viewables();
        $client = Client::with('household_income:'.$household_fields)->where('client_id',$request->client_id)->first();

        if($client===null){
            abort(503);
            return response()->route('client.list');
        }

        return view('pages.update-client',compact('client'));
    }

    public function clientInfo($client_id){
        
        $client = Client::where('client_id',$client_id)->first();
        $client_household_income = HouseholdIncome::where('client_id', $client_id)->first();

        return response()->json([$client,$client_household_income],);   
    }

    public function update(Request $request){
        
        $request = $this->antiNullStrings($request);
         
        $request->is_self_employed =  filter_var($request->is_self_employed, FILTER_VALIDATE_BOOLEAN);
        $request->is_employed =  filter_var($request->is_employed, FILTER_VALIDATE_BOOLEAN);
        $request->spouse_is_self_employed =  filter_var($request->spouse_is_self_employed, FILTER_VALIDATE_BOOLEAN);
        $request->spouse_is_employed =  filter_var($request->spouse_is_employed, FILTER_VALIDATE_BOOLEAN);
        $request->has_remittance =  filter_var($request->has_remittance, FILTER_VALIDATE_BOOLEAN);
        $request->has_pension =  filter_var($request->has_pension, FILTER_VALIDATE_BOOLEAN);
       
        $client = $this->validator($request->all(),true)->validate();
      
        $client_id = $request->client_id;

        $client = Client::where('client_id', $client_id)->first();
        $filename = $client->client_id.'.jpeg';

        if(!$request->has('profile_picture_path') && !$request->has('signature_path')){
            $client->update(
                [
                    'office_id' => $request->office_id,
                    'firstname' => $request->firstname,
                    'middlename'=>$request->middlename,
                    'lastname'  =>$request->lastname,
                    'suffix'=>$request->suffix,
                    'nickname'=>$request->firstname,
                    'gender'=> $request->gender,
                    'birthday' => Carbon::parse($request->birthday),
                    'birthplace' => $request->birthplace,
                    'civil_status' => $request->civil_status,
                    'education' => $request->education,
                    'fb_account' => $request->fb_account,
                    'contact_number'=>$request->contact_number,
                    'street_address'=> $request->street_address,
                    'barangay_address' => $request->barangay_address,
                    'city_address' => $request->city_address,
                    'province_address' => $request->province_address,
                    'zipcode' => $request->zipcode,
                    'business_address' =>$request->business_address,
                    'spouse_name' => $request->spouse_name,
                    'spouse_contact_number' => $request->spouse_contact_number,
                    'spouse_birthday' =>  Carbon::parse($request->spouse_birthday),
                    'number_of_dependents' => $request->number_of_dependents,
                    'household_size' =>$request->household_size,
                    'years_of_stay_on_house' => $request->years_of_stay_on_house,
                    'house_type' => $request->house_type,
                    'tin' => $request->tin,
                    'umid' => $request->umid,
                    'sss' => $request->sss,
                    'mother_maiden_name' => $request->mother_maiden_name,
                    'notes' => $request->notes,
                    // 'created_by' => auth()->user()->id
                ]);
        }
        if($request->has('profile_picture_path')){
           
            $client->update(
                [
                    'office_id' => $request->office_id,
                    'firstname' => $request->firstname,
                    'middlename'=>$request->middlename,
                    'lastname'  =>$request->lastname,
                    'suffix'=>$request->suffix,
                    'profile_picture_path'=> $this->profile_path.$filename,
                    'nickname'=>$request->firstname,
                    'gender'=> $request->gender,
                    'birthday' => Carbon::parse($request->birthday),
                    'birthplace' => $request->birthplace,
                    'civil_status' => $request->civil_status,
                    'education' => $request->education,
                    'fb_account' => $request->fb_account,
                    'contact_number'=>$request->contact_number,
                    'street_address'=> $request->street_address,
                    'barangay_address' => $request->barangay_address,
                    'city_address' => $request->city_address,
                    'province_address' => $request->province_address,
                    'zipcode' => $request->zipcode,
                    'business_address' =>$request->business_address,
                    'spouse_name' => $request->spouse_name,
                    'spouse_contact_number' => $request->spouse_contact_number,
                    'spouse_birthday' =>  Carbon::parse($request->spouse_birthday),
                    'number_of_dependents' => $request->number_of_dependents,
                    'household_size' =>$request->household_size,
                    'years_of_stay_on_house' => $request->years_of_stay_on_house,
                    'house_type' => $request->house_type,
                    'tin' => $request->tin,
                    'umid' => $request->umid,
                    'sss' => $request->sss,
                    'mother_maiden_name' => $request->mother_maiden_name,
                    'notes' => $request->notes, 
                    // 'created_by' => auth()->user()->id
                ]
            );
        }

        if ($request->has('signature_path')) {
            $client->update(
            [
                'office_id' => $request->office_id,
                'firstname' => $request->firstname,
                'middlename'=>$request->middlename,
                'lastname'  =>$request->lastname,
                'suffix'=>$request->suffix,
                'nickname'=>$request->firstname,
                'gender'=> $request->gender,
                'signature_path'=> $this->signature_path. $filename,
                'birthday' => Carbon::parse($request->birthday),
                'birthplace' => $request->birthplace,
                'civil_status' => $request->civil_status,
                'education' => $request->education,
                'fb_account' => $request->fb_account,
                'contact_number'=>$request->contact_number,
                'street_address'=> $request->street_address,
                'barangay_address' => $request->barangay_address,
                'city_address' => $request->city_address,
                'province_address' => $request->province_address,
                'zipcode' => $request->zipcode,
                'business_address' =>$request->business_address,
                'spouse_name' => $request->spouse_name,
                'spouse_contact_number' => $request->spouse_contact_number,
                'spouse_birthday' =>  Carbon::parse($request->spouse_birthday),
                'number_of_dependents' => $request->number_of_dependents,
                'household_size' =>$request->household_size,
                'years_of_stay_on_house' => $request->years_of_stay_on_house,
                'house_type' => $request->house_type,
                'tin' => $request->tin,
                'umid' => $request->umid,
                'sss' => $request->sss,
                'mother_maiden_name' => $request->mother_maiden_name,
                'notes' => $request->notes,
                // 'created_by' => auth()->user()->id
            ]
        );
        }

        $service_type_monthly_gross_income = intval($request->service_type_monthly_gross_income);
        $employed_monthly_gross_income = intval($request->employed_monthly_gross_income);
        $spouse_service_type_monthly_gross_income = intval($request->spouse_service_type_monthly_gross_income);
        $spouse_employed_monthly_gross_income = intval($request->spouse_employed_monthly_gross_income);
            
        $remittance = intval($request->remittance_amount);
        $pension = intval($request->pension_amount);

        

        $total_household_income = 
            $service_type_monthly_gross_income + 
            $employed_monthly_gross_income + 
            $spouse_service_type_monthly_gross_income + 
            $spouse_employed_monthly_gross_income + 
            $remittance + 
            $pension;

        $client->household_income->updateOrCreate([
                'is_self_employed'=>$request->is_self_employed,
                'service_type'=>$request->service_type,
                'service_type_monthly_gross_income'=>$service_type_monthly_gross_income,
                'is_employed'=>$request->is_employed,
                'employed_position'=>$request->employed_position,
                'employed_company_name'=>$request->employed_company_name,
                'employed_monthly_gross_income'=>$employed_monthly_gross_income,
    
                'spouse_is_self_employed'=>$request->spouse_is_self_employed,
                'spouse_service_type'=>$request->spouse_service_type,
                'spouse_service_type_monthly_gross_income'=>$spouse_service_type_monthly_gross_income,
                'spouse_is_employed'=>$request->spouse_is_employed,
                'spouse_employed_position'=>$request->spouse_employed_position,
                'spouse_employed_company_name'=>$request->spouse_employed_company_name,
                'spouse_employed_monthly_gross_income'=>$spouse_employed_monthly_gross_income,
    
                'has_remittance'=>$request->has_remittance,
                'remittance_amount' => $remittance,
                'has_pension'=>$request->has_pension,
                'pension_amount' => $pension,

                
                'total_household_income'=>$total_household_income

        ]);

        if($request->hasFile('profile_picture_path')){
            ini_set('memory_limit','512M');
            $image = $request->file('profile_picture_path');
            // $filename = $image->getClientOriginalName();   
            $image_resize = Image::make($image->getRealPath());
            $image_resize->resize(600, 600);
            $image_resize->save(public_path($this->profile_path . $filename),50);
            ini_set('memory_limit','128M');
        }
        if($request->hasFile('signature_path')){
            ini_set('memory_limit','512M');
            $image = $request->file('signature_path');
            // $filename = $image->getClientOriginalName();   
            $image_resize = Image::make($image->getRealPath());
            $image_resize->resize(600, 300);
            $image_resize->save(public_path($this->signature_path . $filename));
            ini_set('memory_limit','128M');
        }

        
        return response()->json($client);

    }

    public function antiNullStrings($request){
        
        foreach($request->all() as $key => $value){
            if($value=="null"){
                $request[$key] = NULL;
            }
        }

        return $request;
    }
    public function depositAccount(Request $request, $client_id,$deposit_account_id){
        if($request->wantsJson()){
            // $data = DepositAccount::find($deposit_account_id)
            //     ->load([
            //         // 'type:id,name,product_id,description,interest_rate',
            //         'type'=>function($q){
            //             $q->select('id','name','product_id','description','interest_rate');
            //         },
            //         'client'=>function($q){
            //             $q->select('client_id', 'firstname', 'lastname');
            //         },
            //     ]);
            //     $data['transactions'] = 'ggwp';
            $space = ' ';
            $clients = \DB::table('clients');
            $deposits = \DB::table('deposits');
            $deposit = \DB::table('deposit_accounts')
                        ->select(
                            'deposits.name as deposit_name',
                            'deposits.product_id as deposit_type',
                            'deposits.description as deposit_description',
                            'deposits.interest_rate as deposit_interest_rate',
                            'deposit_accounts.accrued_interest as accrued_interest',
                            'deposit_accounts.status as status',
                            'deposit_accounts.balance as balance',
                            'deposit_accounts.created_at as created_at',
                            \DB::raw("CONCAT(clients.firstname, '{$space}', clients.lastname) as client_name"),
                            'clients.client_id as client_id',
                        )
                        ->where('deposit_accounts.id',$deposit_account_id)
                        ->joinSub($clients,'clients',function($join){
                            $join->on('clients.client_id','deposit_accounts.client_id');
                        })
                        ->joinSub($deposits,'deposits',function($join){
                            $join->on('deposits.id','deposit_accounts.deposit_id');
                        })
                        ->first();

            $data['summary'] = $deposit;
            $data['transactions'] = DepositAccount::find($deposit_account_id)->transactions()->get();
            return response()->json(['data'=>$data],200);
        }
        

        return view('pages.deposit-dashboard',compact('deposit_account_id','client_id'));
    }

    public function dependents($client_id){
        
        $client = Client::select('firstname','middlename','lastname','client_id')->where('client_id',$client_id)->firstOrFail();
        
        return view('pages.client-dependents',compact('client'));
    }
    public function toCreateDependents($client_id){
        $client = Client::select('id','firstname','lastname','civil_status','client_id')->where('client_id',$client_id)->firstOrFail();
        $civil_status = strtolower($client->civil_status);
        return view('pages.create-client-dependents',compact('client','civil_status'));
    }

    public function listDependents($client_id){
        $client = Client::fcid($client_id);
        if($client!=null){
            $list = $client->dependents->each->append('pivotList','count'); 
            return response()->json(['msg'=>'Success','list'=>$list],200);
        }
        return response()->json(['msg'=>'Invalid Request'],422);
    }

}


