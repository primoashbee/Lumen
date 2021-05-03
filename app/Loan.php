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
        "status"
        
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
                    (object) ['code'=>'GML',
                    'rates'=>
                        [
                            (object) [
                                'code'=>'MPL',
                                'installments'=>24,
                                'rate'=>5.475225,
                                'term'=>6
                            ]
                        ]
                    ]
                ];

        if($id!=null){
            $code = Loan::select('code')->find($id)->code;
            return collect($data)->where('code',$code)->first()->rates;
        }
        return collect($data);
    }

    public static function getRateFromInstallment($loan_id,$number_of_installments){
        return Loan::rates(1)->rates->where('installments', $number_of_installments)->first();

    }

    // public function repayments(){
    //     $this->ins
    // }

    

}

