<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Business extends Model
{
    protected $fillable = [
        'client_id',
        'business_address',
        'service_type',
        'monthly_gross_income',
        'monthly_operating_expense',
        'monthly_net_income'
    ];

    public function client(){
        return $this->belongsTo(Client::class,'client_id');
    }
}
