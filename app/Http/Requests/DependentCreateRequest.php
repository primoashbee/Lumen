<?php

namespace App\Http\Requests;

use Illuminate\Support\Carbon;
use App\Rules\hasUnusedDepedent;
use App\Rules\HasNoUnusedDependent;
use Illuminate\Foundation\Http\FormRequest;

class DependentCreateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    protected $adult_max_age = 70;
    protected $young_max_age = 21;
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $adult_max_age = Carbon::now()->subYear($this->adult_max_age);
        $young_max_age = Carbon::now()->subYear($this->young_max_age);
        return [
            "client_id"=>['required','exists:clients,client_id',new hasUnusedDepedent],
            "application_number"=>"required|unique:dependents,application_number",

            
            "mother.exists"=>"sometimes|boolean",
            "mother.firstname"=>"required_with:mother.exists|string",
            // "mother.middlename"=>"sometimes|required_with:mother.exists|string",
            "mother.lastname"=>"required_with:mother.exists|string",
            "mother.birthday"=>"required_with:mother.exists|date|after_or_equal:".$adult_max_age,
            
            "father.exists"=>"sometimes|boolean",
            "father.firstname"=>"required_with:father.exists|string",
            // "father.middlename"=>"sometimes|required_with:father.exists|string",
            "father.lastname"=>"required_with:father.exists|string",
            "father.birthday"=>"required_with:father.exists|date|after_or_equal:".$adult_max_age,


            "sibling_1.exists"=>"sometimes|boolean",
            "sibling_1.firstname"=>"required_with:sibling_1.exists|string",
            // "sibling_1.middlename"=>"sometimes|required_with:sibling_1.exists|string",
            "sibling_1.lastname"=>"required_with:sibling_1.exists|string",
            "sibling_1.birthday"=>"required_with:sibling_1.exists|date|after_or_equal:".$young_max_age,

            "sibling_2.exists"=>"sometimes|boolean",
            "sibling_2.firstname"=>"required_with:sibling_2.exists|string",
            // "sibling_2.middlename"=>"sometimes|required_with:sibling_2.exists|string",
            "sibling_2.lastname"=>"required_with:sibling_2.exists|string",
            "sibling_2.birthday"=>"required_with:sibling_2.exists|date|after_or_equal:".$young_max_age,
            
            "sibling_3.exists"=>"sometimes|boolean",
            "sibling_3.firstname"=>"required_with:sibling_3.exists|string",
            // "sibling_3.middlename"=>"sometimes|required_with:sibling_3.exists|string",
            "sibling_3.lastname"=>"required_with:sibling_3.exists|string",
            "sibling_3.birthday"=>"required_with:sibling_3.exists|date|after_or_equal:".$young_max_age,


            "child_1.exists"=>"sometimes|boolean",
            "child_1.firstname"=>"required_with:child_1.exists|string",
            // "child_1.middlename"=>"sometimes|required_with:child_1.exists|string",
            "child_1.lastname"=>"required_with:child_1.exists|string",
            "child_1.birthday"=>"required_with:child_1.exists|date|after_or_equal:".$young_max_age,
            
            "child_2.exists"=>"sometimes|boolean",
            "child_2.firstname"=>"required_with:child_2.exists|string",
            // "child_2.middlename"=>"sometimes|required_with:child_2.exists|string",
            "child_2.lastname"=>"required_with:child_2.exists|string",
            "child_2.birthday"=>"required_with:child_2.exists|date|after_or_equal:".$young_max_age,
            
            "child_3.exists"=>"sometimes|boolean",
            "child_3.firstname"=>"required_with:child_3.exists|string",
            // "child_3.middlename"=>"sometimes|required_with:child_3.exists|string",
            "child_3.lastname"=>"required_with:child_3.exists|string",
            "child_3.birthday"=>"required_with:child_3.exists|date|after_or_equal:".$young_max_age,

            #package #4
            "spouse.exists"=>"sometimes|boolean",
            "spouse.firstname"=>"required_with:spouse.exists|string",
            // "spouse.middlename"=>"sometimes|required_with:spouse.exists|string",
            "spouse.lastname"=>"required_with:spouse.exists|string",
            "spouse.birthday"=>"required_with:spouse.exists|date|after_or_equal:".$adult_max_age,

           'unit_of_plan' =>'required|integer|gt:0',
           'package'=>'required'
            
        ];
    }
    
    public function messages()
    {
        return [
            "application_number.unique"=>"Application number already exists",
            "mother.firstname.required_with" => 'Firstname is required',
            "mother.lastname.required_with" => 'Lastname is required',
            "mother.birthday.after_or_equal" => 'Max age for mother is: '.$this->adult_max_age,

            "father.firstname.required_with" => 'Firstname is required',
            "father.lastname.required_with" => 'Lastname is required',
            "father.birthday.after_or_equal" => 'Max age for father is: '.$this->adult_max_age,

            "sibling_1.firstname.required_with" => 'Firstname is required',
            "sibling_1.lastname.required_with" => 'Lastname is required',
            "sibling_1.birthday.after_or_equal" => 'Max age for sibling is: '.$this->young_max_age,

            "sibling_2.firstname.required_with" => 'Firstname is required',
            "sibling_2.lastname.required_with" => 'Lastname is required',
            "sibling_2.birthday.after_or_equal" => 'Max age for sibling is: '.$this->young_max_age,

            "sibling_3.firstname.required_with" => 'Firstname is required',
            "sibling_3.lastname.required_with" => 'Lastname is required',
            "sibling_3.birthday.after_or_equal" => 'Max age for sibling is: '.$this->young_max_age,

            "child_1.firstname.required_with" => 'Firstname is required',
            "child_1.lastname.required_with" => 'Lastname is required',
            "child_1.birthday.after_or_equal" => 'Max age for child is: '.$this->young_max_age,

            "child_2.firstname.required_with" => 'Firstname is required',
            "child_2.lastname.required_with" => 'Lastname is required',
            "child_2.birthday.after_or_equal" => 'Max age for child is: '.$this->young_max_age,
            
            "child_3.firstname.required_with" => 'Firstname is required',
            "child_3.lastname.required_with" => 'Lastname is required',
            "child_3.birthday.after_or_equal" => 'Max age for child is: '.$this->young_max_age,


        ];
    }

    
}