<?php

namespace App\Http\Controllers;

use App\Client;
use App\Dependent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\DependentCreateRequest;
use App\Rules\DependentStatus;

class DependentController extends Controller
{
    public function createDependents(DependentCreateRequest $request){
     
        $data = $request->all();
        $client = Client::select('firstname','lastname','middlename','birthday')->whereClientId($request->client_id)->first();
        
        $member = [
            'firstname'=>$client->firstname,
            'middlename'=>$client->middlename,
            'lastname'=>$client->lastname,
            'birthday'=>$client->getRawOriginal('birthday'),
        ];
        $data['member'] = $member;
        $data = $this->formatRequest($data);
        if(Client::where('client_id',$request->client_id)->firstOrFail()->dependents()->create($data)){
            return response()->json(['msg'=>'Dependent Created'], 200);
        }
            return response()->json(['msg'=>'Something Went Wrong'], 404);
    }

    public function formatRequest(array $data){
        $values = [];
        foreach($data as $key=>$value){
            
             if(is_array($value)){
                 foreach($value as $sub_key=>$sub_value){
                    $values[$key.'_'.$sub_key]=$sub_value;
                 }
             }else{
                $values[$key]=$value;
             }
        }
        $values['created_by']=auth()->user()->id;
        return $values;
        
    }

    public function updateDependentStatus(Request $request){
    
        $types = ['activate','deactivate'];
        
        Validator::make($request->all(),[
            'application_number'=>'required|unique:dependents,application_number|exists:dependents,application_number',
            'type'=> [new DependentStatus]
        ])->validate();
        Dependent::where('application_number',$request->application_number)->first()->client_id;
        if($request->type=="activate"){
            return Dependent::where('application_number',$request->application_number)->update(['active'=>true]);    
        }
        return Dependent::where('application_number',$request->application_number)->update(['active'=>false]);
    }

}
