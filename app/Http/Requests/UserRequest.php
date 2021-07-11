<?php

namespace App\Http\Requests;
use App\Rules\Gender;
use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
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

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            
            'firstname' => 'required|string|max:255',
            'lastname' => 'required|string|max:255',
            'middlename' => 'sometimes|max:255',
            'gender' => ['required', new Gender],
            'birthday' => 'required|date',
            'email' => 'required|string|email|max:255|unique:users,email,'.request()->id,
            'office_ids'=> 'required'
        ];
    }

    public function messages(){

        return [
            'office_id.required' => 'Office field is required. Please Select atleast one.',
        ];
    }
}
