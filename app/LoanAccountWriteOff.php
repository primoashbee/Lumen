<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class LoanAccountWriteOff extends Model
{
    protected $fillable = [
        'transaction_number',
        'loan_account_id',
        'interest_written_off',
        'principal_written_off',
        'penalty_written_off',
        'total_write_off',
        'writtenoff_by',
        'office_id',
        'written_off_date',
        'reverted',
        'reverted_by',
        'revertion',
    ];

    public function jv(){
        return $this->morphOne(JournalVoucher::class,'journal_voucherable');
    }    
}
