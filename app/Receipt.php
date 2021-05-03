<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Receipt extends Model
{
    
    protected $fillable = ['receipt_number','receiptable_id','receiptable_type'];

    public function receiptable(){
        return $this->morphTo();
    }
}
