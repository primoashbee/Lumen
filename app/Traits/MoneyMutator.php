<?php 
namespace App\Traits;

trait MoneyMutator {
    public function start(){
        $fields = $this->for_mutation;
        $prefix = $this->prefix;
        foreach($fields as $field){
            $attribute = $field;
            $this->attributes['mutated'][$attribute] = $this->mutate($this->$field);
        }
    }
    public function mutate($value){
        return $this->currency .' ' . number_format($value,2);
    }

    
}
