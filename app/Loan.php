<?php

namespace App;


use App\Fee;
use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    //
    protected $hidden = array('pivot');
    protected $fillable = [
        "name",
        "code",
        "description",
        "valid_until",
        "account_per_client",
        "interest_calculation_method_id",

        "minimum_installment",
        "default_installment",
        "maximum_installment",

        "installment_length",
        "installment_method",

        "interest_interval",
        
        "monthly_rate",
        "annual_rate",
        "interest_rate",
        

        "loan_minimum_amount",
        "loan_maximum_amount",

        "grace_period",
        "has_tranches",
        "number_of_tranches",

        "loan_portfolio_active",
        "loan_portfolio_in_arrears",
        "loan_portfolio_matured",

        "loan_interest_income_active",
        "loan_interest_income_in_arrears",
        "loan_interest_income_matured",

        "loan_write_off",
        "loan_recovery",
        "created_by",
        "status",
        
        "has_optional_fees",
        "type",

    ];
    public function fees(){
        return $this->belongsToMany(Fee::class,'loan_fee')->withTimestamps();
    }
    public static function active(){
        return Loan::with('fees')->where('status',1)->get();
    }

    public static function rates($id=null){
        $me = new static;
        $data =  [
                    (object) [
                    'code'=>'MPL',
                    'rates'=>
                        collect([
                            (object) [
                                'code'=>'MPL',
                                'installments'=>22,
                                'rate'=>5.1097,
                                'number_of_months'=>6
                            ],
                            (object) [
                                'code'=>'MPL',
                                'installments'=>24,
                                'rate'=>5.475225,
                                'number_of_months'=>6
                            ],
                            (object) [
                                'code'=>'MPL',
                                'installments'=>44,
                                'rate'=>5.80480,
                                'number_of_months'=>8,

                            ],
                            (object) [
                                'code'=>'MPL',
                                'installments'=>48,
                                'rate'=>5.32911,
                                'number_of_months'=>8
                            ]
                        ]),
                    ],
                    (object) [
                    'code'=>'RS-MPL',
                    'rates'=>
                        collect([
                            (object) [
                                'code'=>'MPL',
                                'installments'=>4,
                                'rate'=>3.19740,
                                'number_of_months'=>1
                            ],
                            (object) [
                                'code'=>'MPL',
                                'installments'=>8,
                                'rate'=>3.51958,
                                'number_of_months'=>2
                            ],
                            (object) [
                                'code'=>'MPL',
                                'installments'=>12,
                                'rate'=> 3.63215 ,
                                'number_of_months'=>3
                            ],
                            (object) [
                                'code'=>'MPL',
                                'installments'=>16,
                                'rate'=>  3.68050 ,
                                'number_of_months'=>4
                            ],
                            (object) [
                                'code'=>'MPL',
                                'installments'=>20,
                                'rate'=>   3.70160  ,
                                'number_of_months'=>5
                            ],
                            
                            (object) [
                                'code'=>'MPL',
                                'installments'=>24,
                                'rate'=>3.70890 ,
                                'number_of_months'=>6
                            ],
                            (object) [
                                'code'=>'MPL',
                                'installments'=>28,
                                'rate'=>3.70832,
                                'number_of_months'=>7
                            ],
                            (object) [
                                'code'=>'MPL',
                                'installments'=>32,
                                'rate'=> 3.70286,
                                'number_of_months'=>8
                            ],
                            (object) [
                                'code'=>'MPL',
                                'installments'=>36,
                                'rate'=>3.69417 ,
                                'number_of_months'=>9
                            ],
                            (object) [
                                'code'=>'MPL',
                                'installments'=>40,
                                'rate'=> 3.68358,
                                'number_of_months'=>10
                            ],
                            (object) [
                                'code'=>'MPL',
                                'installments'=>44,
                                'rate'=>  3.67000 ,
                                'number_of_months'=>11
                            ],
                            (object) [
                                'code'=>'MPL',
                                'installments'=>48,
                                'rate'=> 3.65839 ,
                                'number_of_months'=>12
                            ],
                            (object) [
                                'code'=>'MPL',
                                'installments'=>52,
                                'rate'=> 3.64461 ,
                                'number_of_months'=>13
                            ],
                            (object) [
                                'code'=>'MPL',
                                'installments'=>56,
                                'rate'=> 3.630551,
                                'number_of_months'=>14,
                            ],
                            (object) [
                                'code'=>'MPL',
                                'installments'=>60,
                                'rate'=>3.616035,
                                'number_of_months'=>15,
                            ],
                            (object) [
                                'code'=>'MPL',
                                'installments'=>64,
                                'rate'=>3.601544,
                                'number_of_months'=>16,
                            ],
                            (object) [
                                'code'=>'MPL',
                                'installments'=>68,
                                'rate'=>3.586879,
                                'number_of_months'=>17,
                            ],
                            (object) [
                                'code'=>'MPL',
                                'installments'=>72,
                                'rate'=>3.572176,
                                'number_of_months'=>18,
                            ],
                            (object) [
                                'code'=>'MPL',
                                'installments'=>76,
                                'rate'=>3.557311,
                                'number_of_months'=>19,
                            ],
                            (object) [
                                'code'=>'MPL',
                                'installments'=>80,
                                'rate'=>3.542881,
                                'number_of_months'=>20,
                            ],
                            (object) [
                                'code'=>'MPL',
                                'installments'=>84,
                                'rate'=>3.528221,
                                'number_of_months'=>21,
                            ],
                            (object) [
                                'code'=>'MPL',
                                'installments'=>88,
                                'rate'=>3.514071,
                                'number_of_months'=>22,
                            ],
                            (object) [
                                'code'=>'MPL',
                                'installments'=>92,
                                'rate'=>3.499841,
                                'number_of_months'=>23,
                            ],
                            (object) [
                                'code'=>'MPL',
                                'installments'=>96,
                                'rate'=> 3.48550 ,
                                'number_of_months'=>24,
                            ],
                            
                        ]),
                    ],
                    (object) [
                    'code'=>'RF-MPL',
                    'rates'=>
                        collect([
                            (object) [
                                'code'=>'MPL',
                                'installments'=>4,
                                'rate'=>3.18740,
                                'number_of_months'=>1
                            ],
                            (object) [
                                'code'=>'MPL',
                                'installments'=>22,
                                'rate'=>5.1097,
                                'number_of_months'=>6
                            ],
                            (object) [
                                'code'=>'MPL',
                                'installments'=>24,
                                'rate'=>5.475225,
                                'number_of_months'=>6
                            ],
                            (object) [
                                'code'=>'MPL',
                                'installments'=>44,
                                'rate'=>5.80480,
                                'number_of_months'=>8,

                            ],
                            (object) [
                                'code'=>'MPL',
                                'installments'=>48,
                                'rate'=>5.32911,
                                'number_of_months'=>8
                            ]
                        ]),
                    ],
                    (object) [
                    'code'=>'GML',
                    'rates'=>
                        collect([
                            (object) [
                                'code'=>'GML',
                                'installments'=>12,
                                'rate'=>5.210394,
                                'term'=>6
                            ],
                            (object) [
                                'code'=>'GML',
                                'installments'=>24,
                                'rate'=>5.184610,
                                'term'=>12
                            ],
                        ])
                        
                    ]
                ];

        if($id!=null){
            $code = Loan::select('code')->find($id)->code;
            return collect($data)->where('code',$code)->first()->rates;
            
        }
        return collect($data);
    }

    public function sample(){

    }
    public static function getRateFromInstallment($loan_id,$number_of_installments){
        return Loan::rates(1)->rates->where('installments', $number_of_installments)->first();

    }

    public function isDRP(){
        return $this->type == 'DRP' ? true : false;
    }

    public function installmentInterval(){
        if($this->installment_method==='weeks'){
            return 7 * $this->installment_length;
        }
        if($this->installment_length==='days'){
            return (int) $this->installment_length;
        }
    }
    

}

