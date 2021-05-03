<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class IsArray implements Rule
{
    /**
     * Create a new rule instance.
     *
     * @return void
     */
    protected $table;
    protected $field;
    public function __construct($table,$field)
    {
        $this->table = $table;
        $this->field = $field;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $result = false;
        if(is_array($value)){
            $result = true;
            foreach($value as $id){
                if((\DB::table($this->table)->where($this->field,$id)->count()) > 0){
                    $result = true;
                }else{
                    $result = false;
                }

            }
        }
        return $result;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Invalid user list';
    }
}
