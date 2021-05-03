<?php

namespace App\Http\Requests;

use App\Rules\Businesses;
use App\Rules\Gender;
use App\Rules\HouseType;
use App\Rules\CivilStatus;
use App\Rules\ServiceType;
use App\Rules\EducationalAttainment;
use Illuminate\Foundation\Http\FormRequest;

class ClientRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    
    public function authorize()
    {
        return true;
    }

    // public function getValidatorInstance(){

    //     $z = $this->hasFile('profile_picture_path');
    //      $z;
    // }
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        
        return [
            'office_id'=>['required', 'exists:offices,id'],
            'firstname'=>['required'],
            'lastname'=>['required'],
            'gender'=>['required', new Gender],
            'contact_number'=>'required',
            'birthday'=>'required|date|before:today',
            'birthplace'=>'required',
            'education'=>['required', new EducationalAttainment],
            'civil_status'=>['required', new CivilStatus],
            'street_address'=>'required',
            'barangay_address'=>'required',
            'city_address'=>'required',
            'province_address'=>'required',
            'zipcode'=>'required',
            
            'number_of_dependents'=>'required|integer|gt:0',
            'household_size'=>'required|integer|gt:0',
            'years_of_stay_on_house'=>'required|integer|gt:0',
            'house_type'=>['required', new HouseType],
            'spouse_name' => 'sometimes',
            'spouse_contact_number' => 'sometimes',
            'spouse_birthday' => ['sometimes','nullable','date','before:today'],
            'tin'=>'required',
            'sss'=>'required',
            'umid'=>'required',
            'mother_maiden_name'=>'required',
            'mother_maiden_name'=>'required',

            'total_household_gross_income'=>'required|gt:0',
            'total_household_expense'=>'required|gt:0',
            'total_household_net_income'=>'required|gt:0',
            
            'total_businesses_gross_income'=>'required|gt:0',
            'total_businesses_expense'=>'required|gt:0',
            'total_businesses_net_income'=>'required|gt:0',


            // 'profile_picture_path' =>'sometimes|required|image|mimes:jpeg,png,jpg|max:9000',
            // 'signature_path' =>'sometimes|required|image|mimes:jpeg,png,jpg|max:5000',
            'businesses'=>[new Businesses],
            'businesses.*.business_address'=>['required'],
            'businesses.*.service_type'=>['required', new ServiceType],
            'businesses.*.monthly_gross_income'=>['required','numeric', 'gt:0'],
            'businesses.*.monthly_operating_expense'=>['required','numeric', 'gt:0']
        ];
    }

    public function messages(){

        return [
            'office_id.required' => 'The Linked to field is required',
            'profile_picture_path.required' => 'The profile photo is required',
            'profile_picture_path.image' => 'The profile photo  must be an image',
            'profile_picture_path.mimes' => 'The profile photo  must be a type of jpeg, png, jpg',
            
            'signature_path.required' => 'The signature photo is required',
            'signature_path.image' => 'The signature photo must be an image',
            'signature_path.mimes' => 'The signature photo must be a type of jpeg, png, jpg',
            
            'businesses.*.business_address.required' => 'The full business address is required',
            'businesses.*.service_type.required' => 'The service type is required',
            
            'businesses.*.monthly_gross_income.required' => 'The net income is required',
            'businesses.*.monthly_gross_income.gt' => 'The net income should be greater than 0',
            
            'businesses.*.monthly_operating_expense.required' => 'The monthly operating expense is required',
            'businesses.*.monthly_operating_expense.gt' => 'The monthly operating expense should be greater than 0',
        ];
    }
}
