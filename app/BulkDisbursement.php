<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BulkDisbursement extends Model
{
    protected $fillable = ['bulk_disbursement_id','loan_account_id','office_id','disbursement_date'];
    protected $dates = [
        'created_at',
        'updated_at',
        'disbursement_date'
    ];
    public function loanAccount(){
        return $this->belongsTo(LoanAccount::class);
    }

}
