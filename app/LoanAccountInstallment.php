<?php

namespace App;

use App\Traits\MoneyMutator;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use stdClass;

class LoanAccountInstallment extends Model
{
    
    protected $fillable = [
        'loan_account_id',
        'installment',
        'date',
        'original_principal',
        'original_interest',
        'principal',
        'interest',
        'principal_due',
        'interest_due',
        'amount_due',
        'amortization',
        'paid', 
        'principal_balance',
        'interest_balance',
        
        'interest_days_incurred'
        
    ];

    protected $for_mutation  = [
        'original_principal',
        'original_interest',
        'principal',
        'interest',
        'principal_due',
        'interest_due',
        'amount_due',
        'amortization',
    ];

    protected $dates = ['date','created_at','updated_at'];
    // protected $appends = ['is_due','interest_paid','principal_paid','mutated'];
    // protected $appends = ['is_due','mutated'];

    public function loanAccount(){
        return $this->belongsTo(LoanAccount::class);
    }
    public function repayments(){
        return $this->hasMany(LoanAccountInstallmentRepayment::class);
    }
    public function ctlpRepayments(){
        return $this->hasMany(DepositToLoanInstallmentRepayment::class);
    }
    public function pay($amount,$paid_by,$loan_account_repayment_id,$is_ctlp=false,$ctlp_account_id=false,$deposit_to_loan_repayment_id=false){
        
        $payment = $amount;
        $principal = $this->principal_due;

        $amount_paid = new stdClass;
        $amount_paid->interest = 0;
        $amount_paid->principal = 0;
        $amount_paid->total_paid = 0;
        $fully_paid = false;
        $is_due = $this->isDue();
        $amount_due = 0;
        if($is_due){
            $interest = $this->interest_due;
        }else{
            $interest = $this->interest;
        }
        
        //payment =250 interest 500
        if($payment >= $interest){
            $amount_paid->interest =  $interest;
            $payment = round($payment - $interest,2);
            
        }else{
            $amount_paid->interest = $payment;
            $payment = 0;
        }
      
        if($payment >= $principal){
            $amount_paid->principal =  $principal;
            $payment = round($payment - $principal,2);
            $fully_paid = true;
        }else{
            $amount_paid->principal = $payment;
            $payment = 0;
        }
        $amount_paid->total_paid = round($amount_paid->principal  + $amount_paid->interest,2);
        if($is_due){
            
            $amount_due = round($this->amount_due - ($amount_paid->interest + $amount_paid->principal),2);
        }
        
        
        if($is_due){
            $this->update([
                'interest_due'=>round($this->interest_due - $amount_paid->interest,2),
                'principal_due'=>round($this->principal_due - $amount_paid->principal,2),
                'paid'=>$fully_paid,
                'amount_due'=>$amount_due
            ]);

        }else{
            $this->update([
                'interest'=>round($this->interest - $amount_paid->interest,2),
                'principal_due'=>round($this->principal_due - $amount_paid->principal,2),
                'paid'=>$fully_paid,
                'amount_due'=>$amount_due
            ]);
        }
        if ($is_ctlp) {
            $this->ctlpRepayments()->create([
                'principal_paid'=>$amount_paid->principal,
                'interest_paid'=>$amount_paid->interest,
                'total_paid'=>round($amount_paid->principal + $amount_paid->interest, 2),
                'paid_by'=>$paid_by,
                'deposit_to_loan_repayment_id'=>$deposit_to_loan_repayment_id,
                'deposit_account_id'=>$ctlp_account_id
            ]);
        }else{
            $this->repayments()->create([
                'principal_paid'=>$amount_paid->principal,
                'interest_paid'=>$amount_paid->interest,
                'total_paid'=>round($amount_paid->principal + $amount_paid->interest, 2),
                'paid_by'=>$paid_by,
                'loan_account_repayment_id'=>$loan_account_repayment_id
            ]);
        }
        if($payment > 0){
            // $temp = $this->pay($amount,$transaction_id);
            
            if($this->loanAccount->remainingInstallments()->count() > 0){
                $temp = $this->loanAccount->remainingInstallments()->first()->pay($payment,$paid_by,$loan_account_repayment_id,$is_ctlp,$ctlp_account_id,$deposit_to_loan_repayment_id);
                
            }else{
                

                return $amount_paid;
            }
            
            $amount_paid->interest += $temp->interest;
            $amount_paid->principal += $temp->principal;
            $amount_paid->total_paid += $temp->interest + $temp->principal;
            // $amount_paid->total = round($amount_paid->principal + $amount_paid->interest,2);
        }
        return $amount_paid;
    }
    public function isDue(){
        
        return $this->date <= Carbon::now()->startOfDay() && $this->paid == 0;
    }

    public function dateIsDue(){
        return $this->date->diffInDays(Carbon::now()->startOfDay(),false) >= 0;
    }

    
    public function getIsDueAttribute(){
        return $this->date < Carbon::now()->startOfDay() && $this->paid == 0;
    }
 
    public function getStatusAttribute(){
        $now = now()->startOfDay();
        $date = $this->date;
        $diff = $now->diffInDays($date,false);
        $paid = $this->paid;
        if($paid){
            return 'Paid';
        }
        if( $diff < 0  ){
            return 'In Arrears';
        }

        if($diff == 0){
            return 'Due';
        }

        if($diff > 0 ){
            return 'Not Due';
        }
    }

    public function getMutatedAttribute(){
        $fields = $this->for_mutation;
        
        foreach($fields as $field){
            $attribute = $field;
            $mutated[$attribute] = env('CURRENCY_SIGN') . ' ' . number_format($this->$field,2);
        }
        $interest_paid = $this->repayments->sum('interest_paid');
        $principal_paid = $this->repayments->sum('principal_paid');

        // if(!$this->paid){
        //     $interest_paid = abs(round($this->interest - $this->original_interest,2));    
        //     $principal_paid = abs(round($this->principal - $this->original_principal,2));
        // }
        
        
        $mutated['interest_paid'] = env('CURRENCY_SIGN') . ' ' . number_format($interest_paid,2);
        $mutated['principal_paid'] = env('CURRENCY_SIGN') . ' ' . number_format($principal_paid,2);
        
        return $mutated;
        
    }
    
    public function reset(){

        if($this->isDue()){
            return $this->update([
                'interest'=>$this->original_interest,
                'principal'=>$this->original_principal,
                'interest_due'=>$this->original_interest,
                'principal_due'=>$this->original_principal,
                'amount_due'=>$this->amortization,
                'paid'=>false,
                'reverted'=>false
            ]);
        }

        return $this->update([
            'interest'=>$this->original_interest,
            'principal'=>$this->original_principal,
            'interest_due'=>0,
            'principal_due'=>$this->original_principal,
            'paid'=>false,
            'amount_due'=>0,
            'reverted'=>false
        ]);
        
    }

    public function updateInstallmentStatus(){
        if($this->date <= Carbon::now() && $this->amount_due == 0){
            $this->paid = true;
            return $this->save();
        }
        $this->paid = false;
        return $this->save();
    }

    public function isFuture(){
        return  $this->date->diffInDays(Carbon::now()) > 0;
    }

    public function updatePaymentFromPreterm($interest_paid,$principal_paid,$revert=false){
        $interest_paid = $interest_paid;
        $principal_paid = $principal_paid;

        $current_interest_paid = $this->interest_paid;
        $current_principal_paid = $this->principal_paid;

        $new_interest_paid = round($interest_paid + $current_interest_paid, 2);
        $new_principal_paid = round($principal_paid + $current_principal_paid, 2);
        $paid = true;
        if($revert){
            $new_interest_paid = round($current_interest_paid - $interest_paid, 2);
            $new_principal_paid = round($current_principal_paid - $principal_paid, 2);
            $paid = false;
        }
        return $this->update([
            'interest_paid'=>$new_interest_paid,
            'principal_paid'=>$new_principal_paid,
            'paid'=>false
        ]);
    }

    public function fullyPaid(){
        return $this->repayments->sum('total_paid') == $this->amortization;
    }
    public function refresh(){
        
        $diff = $this->date->diffInDays(Carbon::now(),false) ;

        //negative means future date
        if($diff >= 0) {
            if($this->fullyPaid()){
                return $this->update([
                    'interest'=>$this->interest,
                    'principal_due'=>$this->principal_due,
                    'amount_due'=>round($this->interest + $this->principal_due, 2),
                ]);
            }else{
                return $this->update([
                    'interest_due'=>$this->interest,
                    'amount_due'=>round($this->interest + $this->principal_due, 2),
                ]);

            }
            
        }else{
            if($this->fullyPaid()){

            }else{
                return $this->update([
                    'interest_due'=>0,
                    'amount_due'=>0,
                ]);
            }
        }
    }

    public function status(){
        $date = $this->date;
        
    }
}
