<?php

namespace App;

use App\Office;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    protected $fillable=['date','name','office_id'];
    protected $dates = ['date'];
    public static function today(){
        return Holiday::whereDate('date', now())->get();
    }

    public function affectedAccounts(){
        $ids = Office::lowerOffices($this->office_id,true,true);
        $date = $this->date;
        $accounts = LoanAccount::active()->whereExists(function($q) use ($ids){
            $q->from('clients')
            ->whereIn('office_id',$ids)
            ->whereColumn('loan_accounts.client_id','clients.client_id');
        })->whereExists(function($q2) use ($date){
            $q2->from('loan_account_installments')
                ->whereDate('date',$date)
                ->whereColumn('loan_accounts.id','loan_account_installments.loan_account_id');
        });
        return $accounts;
    }

    public function office(){
        return $this->belongsTo(Office::class,'office_id');
    }

    public function implement(){
        $accounts = $this->affectedAccounts();
        $date = $this->date;
        $total = $accounts->count();
        $accounts->orderBy('id')->chunk(1000,function($list) use ($date){
            $list->map(function($item) use ($date){
                $days_adjustment = $item->type->installmentInterval();
                $installment = collect($item->installments()->whereDate('date','>=',$date)->orderBy('date','asc')->get());
                $installment->map(function($installment) use ($days_adjustment){
                    $installment->update([
                        'date'=>$installment->date->addDays($days_adjustment)
                    ]);
                });
            });
        });
        echo 'Rescheduled ' . $total .' accounts succesfully';
    }

    public static function list(Carbon $date){
        return Holiday::whereDate('date',$date);
    }
}
