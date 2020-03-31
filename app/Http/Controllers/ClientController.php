<?php

namespace App\Http\Controllers;

use App\Client;
use Carbon\Carbon;
use App\Rules\Gender;
use App\Rules\OfficeID;
use App\HouseholdIncome;
use App\Rules\HouseType;
use App\Rules\CivilStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\MessageBag;
use App\Rules\EducationalAttainment;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ClientController extends Controller
{
    protected $profile_path = 'public/client_photos/';
    protected $signature_path = 'public/client_photos/';
    public function index(){
        $branches = auth()->user()->scopesBranch();
        return view('pages.create-client',compact('branches'));
    }
    
    public function view($client_id){
        $res = Client::find($client_id);
    }

    public function create(Request $request){
        $request->is_self_employed =  filter_var($request->is_self_employed, FILTER_VALIDATE_BOOLEAN);
        $request->is_employed =  filter_var($request->is_employed, FILTER_VALIDATE_BOOLEAN);
        $request->spouse_is_self_employed =  filter_var($request->spouse_is_self_employed, FILTER_VALIDATE_BOOLEAN);
        $request->spouse_is_employed =  filter_var($request->spouse_is_employed, FILTER_VALIDATE_BOOLEAN);
        $request->has_remittance =  filter_var($request->has_remittance, FILTER_VALIDATE_BOOLEAN);
        $request->has_pension =  filter_var($request->has_pension, FILTER_VALIDATE_BOOLEAN);
        // return Carbon::parse($request->birthday);
        // return(Carbon::createFromFormat('MMMM D, YYYY ',$request->birthday));
        $this->validator($request->all())->validate();
        
        $req = Client::clientExists($request);
        if($req['exists']){
            return response()->json($req,422);
        }

        $client_id = makeClientID($request->office_id);
        $filename = $client_id.'.jpeg';
        checkClientPaths();
        $profile_path = 'storage/profile_photos/';
        $signature_path = 'storage/signatures/';

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
                'profile_picture_path' => $profile_path . $filename,
                'signature_path' => $signature_path . $filename,
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
            
            $service_type_gross_income = intval($request->service_type_gross_income);
            $employed_monthly_gross_income = intval($request->employed_monthly_gross_income);
            $spouse_service_type_gross_income = intval($request->spouse_service_type_gross_income);
            $spouse_employed_monthly_gross_income = intval($request->spouse_employed_monthly_gross_income);
                
            $remittance = intval($request->remittance_amount);
            $pension = intval($request->pension_amount);

            $total_household_income = 
                $service_type_gross_income + 
                $employed_monthly_gross_income + 
                $spouse_service_type_gross_income + 
                $spouse_employed_monthly_gross_income + 
                $remittance + 
                $pension;
            
            HouseholdIncome::create([
                'client_id'=>$client_id,
    
                'is_self_employed'=>$request->is_self_employed,
                'service_type'=>$request->service_type,
                'service_type_monthly_gross_income'=>$service_type_gross_income,
                'is_employed'=>$request->is_employed,
                'employed_position'=>$request->employed_company_name,
                'employed_monthly_gross_income'=>$employed_monthly_gross_income,
    
                'spouse_is_self_employed'=>$request->spouse_is_self_employed,
                'spouse_service_type'=>$request->spouse_service_type,
                'spouse_service_type_monthly_gross_income'=>$spouse_service_type_gross_income,
                'spouse_is_employed'=>$request->spouse_is_employed,
                'spouse_employed_position'=>$request->spouse_employed_position,
                'spouse_employed_company_name'=>$request->spouse_employed_company_name,
                'spouse_employed_monthly_gross_income'=>$spouse_employed_monthly_gross_income,
    
                'has_remittance'=>$request->has_remittance,
                'has_pension'=>$request->has_pension,
                'total_household_income'=>$total_household_income
            ]);
            
            if($request->hasFile('profile_picture_path')){
                ini_set('memory_limit','512M');
                $image = $request->file('profile_picture_path');
                // $filename = $image->getClientOriginalName();   
                $image_resize = Image::make($image->getRealPath());
                $image_resize->resize(600, 600);
                $image_resize->save(public_path($profile_path . $filename));
                ini_set('memory_limit','128M');
            }
            if($request->hasFile('signature_path')){
                ini_set('memory_limit','512M');
                $image = $request->file('signature_path');
                // $filename = $image->getClientOriginalName();   
                $image_resize = Image::make($image->getRealPath());
                $image_resize->resize(600, 600);
                $image_resize->save(public_path($signature_path . $filename));
                ini_set('memory_limit','128M');
            }
            DB::commit();
            return response()->json(['msg'=>'Client succesfully created'],200);
        } catch(ValidationException $e)
        {
            // Rollback and then redirect
            // back to form with errors
            DB::rollback();
            
            return response()->json(['errors'=>$e->getErrors()],422);
        } catch(\Exception $e)
        {
            DB::rollback();
            throw $e;
        }

        
       
    }

    public function validator(array $data){

            return Validator::make($data, 
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
                'spouse_name' => 'required',
                'spouse_contact_number' => 'required',
                'spouse_birthday' => 'required|date',
                'tin'=>'required',
                'sss'=>'required',
                'umid'=>'required',
                'mother_maiden_name'=>'required',
                'mother_maiden_name'=>'required',
                'total_household_income'=>'required|integer|gt:0',
                'profile_picture_path' =>'required|image|mimes:jpeg,png,jpg|max:6000',
                'signature_path' =>'required|image|mimes:jpeg,png,jpg|max:2048',

            ],
            [
                'office_id.required' => 'The Linked to field is required',
                'profile_picture_path.required' => 'The profile photo is required',
                'profile_picture_path.image' => 'The profile photo  must be an image',
                'profile_picture_path.mimes' => 'The profile photo  must be a type of jpeg, png, jpg',
                
                'signature_path.required' => 'The signature photo is required',
                'signature_path.image' => 'The signature photo must be an image',
                'signature_path.mimes' => 'The signature photo must be a type of jpeg, png, jpg',

            ]);
    }


    public function list(){
        // $lists = Client::where('office_id',null)->get();
        // if($request->office_id!=null){
        //     $lists = Client::where('office_id',$request->office_id)->paginate();
        // }
        
        return view('pages.client-list');
    }
    public function getList(Request $request){
        // sleep(3);
        return response()->json(Client::with('office')->where('office_id',$request->office_id)->paginate(30));
    }

    public function query(Request $request){
        $q = $request->query;
        $res = Client::whereLike('firstname',$q)
            ->whereLike('lastname', $q)
            ->paginate(30);
        return response()->json($res);
    }

    public function getClient(Request $request){
        $client = Client::where('client_id',$request->client_id)->first();
        if($client===null){
            abort(503);
            return response()->route('client.list');
        }
        return view('pages.client-profile',compact('client'));
        
    }
}
