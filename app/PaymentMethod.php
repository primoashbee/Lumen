<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = [
        'name',
        'is_disabled',
        'for_disbursement',
        'for_repayment',
        'for_deposit',
        'for_withdrawal',
        'for_recovery',
        'gl_account_code',
    ];

    protected $dates = ['created_at','updated_at'];


    public function isCTLP(){
        return $this->name == 'CTLP' ? true : false;
    }

    public static function interestPosting(){
        return PaymentMethod::where('name','INTEREST POSTING')->first();
    }
}
