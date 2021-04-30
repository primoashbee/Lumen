<?php

namespace App;

use Carbon\CarbonPeriod;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Model;

class ParMovement extends Model
{
    protected $fillable = [
        'accounts',
        'par_amount',
        'office_id',
        'aging',
        'date'
    ];

    public static $aging = [[1,30],[31,60],[61,90],[91,180],181];
    public static function generate($date){

        $insert_data = [];
        $date = Carbon::parse($date);

        //get aging of unpaid installments
        $aging_table = \DB::table('loan_account_installments')
                    ->select(
                        'loan_account_id',
                        'date as earliest_installment_date',
                        \DB::raw("ABS(DATEDIFF(date, DATE('{$date}'))) AS days"),
                        \DB::raw('row_number() OVER(partition BY loan_account_id ORDER BY date ASC) AS rowno')
                    )
                    ->whereDate('date','<',now())
                    ->where('paid',0)
                    ->orderBy('date','asc');
        
        $clients = \DB::table('clients')
                        ->select('client_id as c_id','office_id');
        
        

        $offices = collect(Office::find(1)->getLowerOfficeIDS())->sort()->values();
        $movements = [];
        foreach ($offices as $office_id) {
            $summary = \DB::table('loan_accounts as la')
                    ->select(
                        'la.id as loan_account_id',
                        'aging.earliest_installment_date',
                        'aging.days',
                        'principal_balance as par_amount',
                        'c_id',
                        'office_id',
                    )
                    ->leftJoinSub($aging_table, 'aging', function ($join) {
                        $join->on('aging.loan_account_id', '=', 'id');
                    })
                    // ->leftJoinSub($amount, 'amount', function ($join) {
                    //     $join->on('amount.loan_account_id', '=', 'id');
                    // })
                    ->leftJoinSub($clients, 'clients', function ($join) {
                        $join->on('clients.c_id', '=', 'client_id');
                    })
                    ->whereExists(function ($q) use ($office_id) {
                        $ids = Office::find($office_id)->getLowerOfficeIDS();
                        $q->whereIn('office_id', $ids);
                    })
                    ->where('aging.rowno', 1);
      
                $count = count(ParMovement::$aging);
                $ctr=1;
                foreach(ParMovement::$aging as $aging){
                    $value = $aging;
                    if(is_array($aging)){
                        $value = (string) $aging[0].'-'. (string) $aging[1];
                        // dd($value);
                    }
                    // return $summary->get();
                    $temp_movement = \DB::table('offices')
                        ->select(
                            \DB::raw("count(*) as accounts, IFNULL(sum(par_amount),0) as par_amount, IFNULL(office_id,'{$office_id}') as office_id"), 
                            \DB::raw("IF(1=1,'{$value}','0') as aging"),
                            \DB::raw("IF(1=1,'{$date->toDateString()}','0') as date")
                        )
                        ->leftJoinSub($summary, 'summary',function($join){
                            $join->on('summary.office_id','=','offices.id');
                        })
                        ->when($ctr,function($q,$data) use($aging, $count){
                            if($data==$count){
                                // dd($aging);
                                $q->where('days','>',$aging);
                            }else{
                                $q->whereBetween('days',$aging);
                            }
                        })
                        
                        ->where('offices.id','=',$office_id)
                        ->get();
                        // $movements[] = $temp_movement;
                        collect($temp_movement)->map(function($x) use(&$movements){
                            $movements[] = array(
                                'accounts' => $x->accounts,
                                'par_amount' => $x->par_amount,
                                'office_id' => $x->office_id,
                                'date' => $x->date,
                                'aging'=>$x->aging,
                                'created_at'=>now(),
                                'updated_at'=>now(),
                            );
                        });
                        $ctr++;
                }
                    
    
          
          
        }
        
        
        // return $movements[0];
        return ParMovement::insert($movements);
    }


    public static function dashboardReport($from,$to,$office_id,$now_only = false){

        if($now_only){
            $from = Carbon::parse(now())->toDateString();
            $to = Carbon::parse(now())->toDateString();
            // $date = Carbon::parse($date)->toDateString();
            // $ids = Office::find($office_id)->getLowerOfficeIDS();
            $ids = Office::lowerOffices($office_id,true,true);
            // return $x;
            $date_list  = CarbonPeriod::create($from, $to)->toArray();
            $movements= [];
            foreach ($date_list as $item) {
                //per day
                $tables  = [];
                foreach (ParMovement::$aging as $value) {
                    //per age
                    if (is_array($value)) {
                        $value = (string) $value[0].'-'. (string) $value[1];
                    }
                    $tables[] = \DB::table('par_movements')
                    ->select(
                        \DB::raw('SUM(par_amount) as total_par, aging,date'),
                        \DB::raw("IF(1=1,'{$office_id}',0) as office_id")
                    )
                    ->whereIn('office_id', $ids)
                    ->whereDate('date', $item)
                    ->where('aging', $value)
                    ->groupBy('aging', 'date')
                    ->get();
                }
                $movements[] = ['date'=>$item->format('d-F'), 'tables'=>collect($tables)->flatten()];
            }

        
            $par_amount = [];
            $movements;
            $collection = collect([1, 2, 3, 4]);

            $filtered = $collection->filter(function ($value, $key) {
                return $value > 2;
            });
            $labels = [];
            collect($date_list)->map(function ($value, $key) use (&$labels) {
                $labels[] = $value->format('d-F');
            });
        
            foreach (ParMovement::$aging as $age) {
                // $par_amount['age'] =[$age];
                if (is_array($age)) {
                    $age = (string) $age[0].'-'. (string) $age[1];
                }
                collect($movements)->map(function ($item) use ($age, &$par_amount) {
                    $par_amount[$age][] = collect($item['tables'])->where('aging', $age)->first()->total_par;
                });
            }
        
            return ['labels'=>$labels, 'par_amount'=>$par_amount];
        }else{
            $from = Carbon::parse($from)->toDateString();
            $to = Carbon::parse($to)->toDateString();
            // $date = Carbon::parse($date)->toDateString();
            // $ids = Office::find($office_id)->getLowerOfficeIDS();
            $ids = Office::lowerOffices($office_id,true,true);
            // return $x;
            $date_list  = CarbonPeriod::create($from, $to)->toArray();
            $movements= [];
            foreach ($date_list as $item) {
                //per day
                $tables  = [];
                foreach (ParMovement::$aging as $value) {
                    //per age
                    if (is_array($value)) {
                        $value = (string) $value[0].'-'. (string) $value[1];
                    }
                    $tables[] = \DB::table('par_movements')
                    ->select(
                        \DB::raw('SUM(par_amount) as total_par, aging, date'),
                        \DB::raw("IF(1=1,'{$office_id}',0) as office_id")
                    )
                    ->whereIn('office_id', $ids)
                    ->whereDate('date', $item)
                    ->where('aging', $value)
                    ->groupBy('aging', 'date')
                    ->get();
                }
                $movements[] = ['date'=>$item->format('d-F'), 'tables'=>collect($tables)->flatten()];
            }

        
            $par_amount = [];
            $movements;

            $labels = [];
            collect($date_list)->map(function ($value, $key) use (&$labels) {
                $labels[] = $value->format('d-F');
            });
        
            foreach (ParMovement::$aging as $age) {
                // $par_amount['age'] =[$age];
                if (is_array($age)) {
                    $age = (string) $age[0].'-'. (string) $age[1];
                }
                collect($movements)->map(function ($movement) use ($age, &$par_amount) {
                    $par_amount[$age][] = collect($movement['tables'])->where('aging', $age)->first()->total_par;
                });
            }
        
            return ['labels'=>$labels, 'par_amount'=>$par_amount];
        }
    }

    
}
