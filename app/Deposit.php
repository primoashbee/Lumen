<?php

namespace App;

use App\Client;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Deposit extends Model
{
    protected $fillable = [
        'name',
        'product_id',
        'minimum_deposit_per_transaction',
        'description',
        'auto_create_on_new_client',
        'valid_until',
        'account_per_client',
        'interest_rate',
        'deposit_portfolio',
        'deposit_interest_expense',
    ];
    
    public function client(){
        return $this->belongsToMany(Client::class,'client_id');
    }

    public static function autoCreate(){
        $me = new static;
        return $me->where('auto_create_on_new_client',true)->get();
    }

    public function getInterestRateAttribute($val){
        return ($val/100 ).'%';
    }

    public function minimumDepositAmount(){
        return [
            'amount'=>$this->minimum_deposit_per_transaction,
            'amount_formatted'=> money($this->minimum_deposit_per_transaction,2)
        ];
    }
    

  
}
