<?php

namespace App\Observers;

use App\LoanAccount;

class LoanAccountObserver
{
    /**
     * Handle the loan account "created" event.
     *
     * @param  \App\LoanAccount  $loanAccount
     * @return void
     */
    public function created(LoanAccount $loanAccount)
    {
        //
    }

    /**
     * Handle the loan account "updated" event.
     *
     * @param  \App\LoanAccount  $loanAccount
     * @return void
     */
    public function updated(LoanAccount $loanAccount)
    {
        if($loanAccount->total_balance == 0){
            $loanAccount->status = 'Closed';
            $loanAccount->save();
        }
    }

    /**
     * Handle the loan account "deleted" event.
     *
     * @param  \App\LoanAccount  $loanAccount
     * @return void
     */
    public function deleted(LoanAccount $loanAccount)
    {
        //
    }

    /**
     * Handle the loan account "restored" event.
     *
     * @param  \App\LoanAccount  $loanAccount
     * @return void
     */
    public function restored(LoanAccount $loanAccount)
    {
        //
    }

    /**
     * Handle the loan account "force deleted" event.
     *
     * @param  \App\LoanAccount  $loanAccount
     * @return void
     */
    public function forceDeleted(LoanAccount $loanAccount)
    {
        //
    }
}
