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

    public function __construct(){

        $this->middleware('permission:view_deposit_account', ['only' => ['depositAccount']]);
        $this->middleware('permission:view_client', ['only' => ['index','list']]);
        $this->middleware('permission:edit_client', ['only' => ['editClient','update']]);
        $this->middleware('permission:create_client', ['only' => ['step','createV1']]);

    }

    //get branches of logged in user upon creating client
    public function index(){
        $branches = auth()->user()->scopesBranch();
        return view('pages.create-client',compact('branches'));

    }
    

    public function step(){
        return view('pages.create-client');
    }

    public function createV1(ClientRequest $request){
        
        $req = Client::clientExists($request);
        
        if($req['exists']){
            return response()->json($req,422);
        }

        $client_id = Office::makeClientID($request->office_id);

        $filename = $client_id.'.jpeg';
        checkClientPaths();

        DB::beginTransaction();
        try{

        $client = Client::create(array_merge($request->all(), 
                ['client_id' => $client_id, 
                'created_by' => auth()->user()->id]));

            
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
        //    dd($this->household_income_request());
            $client->household_income()->create($this->household_income_request());

            // if($request->hasFile('profile_picture_path')){
            //     ini_set('memory_limit','512M');
            //     $image = $request->file('profile_picture_path');
            //     // $filename = $image->getClientOriginalName();   
            //     $image_resize = Image::make($image->getRealPath());
            //     $image_resize->resize(600, 600);
            //     $image_resize->save(public_path($this->profile_path . $filename), 50);
            //     ini_set('memory_limit','128M');
            // }
            // if($request->hasFile('signature_path')){
            //     ini_set('memory_limit','512M');
            //     $image = $request->file('signature_path');
            //     // $filename = $image->getClientOriginalName();   
            //     $image_resize = Image::make($image->getRealPath());
            //     $image_resize->resize(600, 300);
            //     $image_resize->save(public_path($this->signature_path . $filename),50);
            //     ini_set('memory_limit','128M');
            // }
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

    public function household_income_request($update=false){
        request()->is_self_employed =  filter_var(request()->is_self_employed, FILTER_VALIDATE_BOOLEAN);
        
        request()->is_employed =  filter_var(request()->is_employed, FILTER_VALIDATE_BOOLEAN);
        request()->spouse_is_self_employed =  filter_var(request()->spouse_is_self_employed, FILTER_VALIDATE_BOOLEAN);
        request()->spouse_is_employed =  filter_var(request()->spouse_is_employed, FILTER_VALIDATE_BOOLEAN);
        request()->has_remittance =  filter_var(request()->has_remittance, FILTER_VALIDATE_BOOLEAN);
        request()->has_pension =  filter_var(request()->has_pension, FILTER_VALIDATE_BOOLEAN);

        if ($update = true) {
            
            $service_type_monthly_gross_income = intval(request()->service_type_monthly_gross_income);
            $employed_monthly_gross_income = intval(request()->employed_monthly_gross_income);
            $spouse_service_type_monthly_gross_income = intval(request()->spouse_service_type_monthly_gross_income);
            $spouse_employed_monthly_gross_income = intval(request()->spouse_employed_monthly_gross_income);
                
            $remittance = intval(request()->remittance_amount);
            $pension = intval(request()->pension_amount);

            $total_household_income = 
                $service_type_monthly_gross_income + 
                $employed_monthly_gross_income + 
                $spouse_service_type_monthly_gross_income + 
                $spouse_employed_monthly_gross_income + 
                $remittance + 
                $pension - request()->total_household_expense;
        }else{
        $service_type_monthly_gross_income = round(request()->service_type_monthly_gross_income,2);
        $employed_monthly_gross_income = round(request()->employed_monthly_gross_income,2);
        $spouse_service_type_monthly_gross_income = round(request()->spouse_service_type_monthly_gross_income,2);
        $spouse_employed_monthly_gross_income = round(request()->spouse_employed_monthly_gross_income,2);
            
        $remittance = round(request()->remittance_amount,2);
        $pension = round(request()->pension_amount,2);

        $total_household_income = 
            round($service_type_monthly_gross_income + 
            $employed_monthly_gross_income + 
            $spouse_service_type_monthly_gross_income + 
            $spouse_employed_monthly_gross_income + 
            $remittance + 
            $pension,2);
        }

        return [
            'is_self_employed'=>request()->is_self_employed,
            'service_type'=>request()->service_type,
            'service_type_monthly_gross_income'=>$service_type_monthly_gross_income,
            'is_employed'=>request()->is_employed,
            'employed_position'=>request()->employed_position,
            'employed_company_name'=>request()->employed_company_name,
            'employed_monthly_gross_income'=>$employed_monthly_gross_income,

            'spouse_is_self_employed'=>request()->spouse_is_self_employed,
            'spouse_service_type'=>request()->spouse_service_type,
            'spouse_service_type_monthly_gross_income'=>$spouse_service_type_monthly_gross_income,
            'spouse_is_employed'=>request()->spouse_is_employed,
            'spouse_employed_position'=>request()->spouse_employed_position,
            'spouse_employed_company_name'=>request()->spouse_employed_company_name,
            'spouse_employed_monthly_gross_income'=>$spouse_employed_monthly_gross_income,

            'has_remittance'=>request()->has_remittance,
            'remittance_amount' => $remittance,
            'has_pension'=>request()->has_pension,
            'pension_amount' => $pension,
            'total_household_expense' => request()->total_household_expense,
            
            'total_household_income'=>$total_household_income 
        ]; 

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

    
    public function view($client_id){
        $client = Client::fcid($client_id);
        if($client===null){
            abort(503);
            return response()->route('client.list');
        }
        $client->load('household_income','businesses');
        return view('pages.client-profile',compact('client'));
    }

    public function editClient($client_id){
        $client = Client::fcid($client_id);
        if($client===null){
            abort(503);
            return response()->route('client.list');
        }
        $client->load('household_income','businesses');
        
        return view('pages.update-client',compact('client'));
    }

    public function update(ClientRequest $request, $client_id){
        // $request = $this->antiNullStrings($request);
        $client = Client::fcid($client_id);
        
        $filename = $client->client_id.'.jpeg';

        $client->update($request->all());

        $client->businesses()->delete();
        
        foreach($request->businesses as $business){
            $client->businesses()->create([

                'business_address'=>$business['business_address'],
                'service_type'=>$business['service_type'],
                'monthly_gross_income'=>$business['monthly_gross_income'],
                'monthly_operating_expense'=>$business['monthly_operating_expense'],
                'monthly_net_income'=>round($business['monthly_gross_income'] - $business['monthly_operating_expense'],2)
            ]);
        }

        $client->household_income()->update($this->household_income_request(true));

        // if($request->hasFile('profile_picture_path')){
        //     ini_set('memory_limit','512M');
        //     $image = $request->file('profile_picture_path');
        //     // $filename = $image->getClientOriginalName();   
        //     $image_resize = Image::make($image->getRealPath());
        //     $image_resize->resize(600, 600);
        //     $image_resize->save(public_path($this->profile_path . $filename),50);
        //     ini_set('memory_limit','128M');
        // }
        // if($request->hasFile('signature_path')){
        //     ini_set('memory_limit','512M');
        //     $image = $request->file('signature_path');
        //     // $filename = $image->getClientOriginalName();   
        //     $image_resize = Image::make($image->getRealPath());
        //     $image_resize->resize(600, 300);
        //     $image_resize->save(public_path($this->signature_path . $filename));
        //     ini_set('memory_limit','128M');
        // }

        
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
            $account = DepositAccount::find($deposit_account_id);
            $data['transactions'] = [];
            if(count($account->transactions()->get()) > 0){
                $data['transactions'] = DepositAccount::find($deposit_account_id)->transactions()->get();    
            }
            
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


