<?php

namespace App;

use App\Office;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
        'client_id',
        'firstname',
        'middlename',
        'lastname',
        'suffix',
        'nickname',
        'gender',
        'profile_picture_path',
        'signature_path',
        'birthday',
        'birthplace',
        'civil_status',
        'education',
        'fb_account',
        'contact_number',

        'street_address',
        'barangay_address',
        'city_address',
        'province_address',
        'zipcode',

        'business_address',
        'spouse_name',
        'spouse_contact_number',
        'spouse_birthday',

        'number_of_dependents',
        'household_size',
        'years_of_stay_on_house',
        'house_type',

        'tin',
        'umid',
        'sss',
        'mother_maiden_name',

        'notes',
        'office_id',
        'created_by',
    ];

    public function household_income(){
        return $this->hasOne(HouseholdIncome::class, 'client_id','client_id');
    }

    public static function clientExists($request){
            
        $client = Client::where('firstname',$request->firstname)
                ->where('lastname',$request->lastname)
                ->where('birthday',Carbon::parse($request->birthday)->toDateString());
        
        if($client->count() > 0 ){
            
            return ['msg' => 'Already exists','exists' => true, 'errors' => ['client' => ['msg'=>'Client Already Exists','client_id'=>$client->first()->client_id,'exists_at' => $client->first()->branch()->name]]];
        }

        return ['msg' => 'Does not exists','exists' => false, 'client_info'=>null];
    }

    public function office(){
        return $this->belongsTo(Office::class);
    }
    
    public function branch(){
        return $this->office->getTopOffice('branch');
    }
    public function name(){
        return $this->firstname.' '.$this->lastname;
    }
    public function officeGet(){
        return ['id'=>$this->branch()->id,'name'=>$this->branch()->name];
    }
    
}
