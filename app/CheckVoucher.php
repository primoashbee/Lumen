<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CheckVoucher extends Model
{

    protected $fillable = ['check_voucher_number','transaction_date','check_voucherable_id','check_voucherable_type'];

    public function check_voucherable(){
        return $this->morphTo();
    }
}
