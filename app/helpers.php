<?php 

use App\Fee;
use App\Loan;
use App\Room;
use App\User;
use App\Client;
use App\Office;
use App\Cluster;
use App\Deposit;

use App\Scheduler;

use Carbon\Carbon;
use App\OfficeUser;
use App\LoanAccount;
use App\PaymentMethod;
use Faker\Factory as Faker;
use Illuminate\Support\Str;
use App\DefaultPaymentMethod;
use App\Imports\OfficeImport;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\LoanAccountController;

    function seed($office_id,$count=20,$with_loans=false, $start_date = null){

        \DB::beginTransaction();
        try {
            $offices = Office::find($office_id)->children;
            $ids = [];
            if($offices->count() == 0){
                $office = Office::find($office_id);
                for ($x=1; $x<= $count; $x++) {
                    $faker = Faker::create();
                
                    $gender = $faker->randomElement(['MALE', 'FEMALE']);
                    $civil_status = $faker->randomElement(['SINGLE', 'MARRIED','DIVORCED']);
                    $education = $faker->randomElement(['ELEMENTARY', 'HIGH SCHOOL','COLLEGE','VOCATIONAL']);
                    $barangay = $faker->randomElement(['San Jose', 'Sta. Rita','Gordon Heights','Pag-asa']);
                    $province = $faker->randomElement(['Zambales', 'Pampanga','Bataan']);
                    $dependents = rand(1, 5);
                    $house_type = $faker->randomElement(['RENTED','OWNED']);
                    $mobile_number = '09'.rand(100000000, 199999999);
                    // $office = Office::where('name', '')->first();
                    static $id = 1;
                    $user  = Client::create([
                        'client_id' => Office::makeClientID($office->id),
                        'firstname' => $faker->firstName,
                        'middlename'=>$faker->lastname,
                        'lastname'  =>$faker->lastname,
                        'suffix'=>$faker->suffix,
                        'nickname'=>$faker->firstname,
                        'gender'=> $gender,
                        'profile_picture_path' => 'https://via.placeholder.com/150',
                        'signature_path' => 'https://via.placeholder.com/150',
                        'birthday' => $faker->dateTimeThisCentury->format('Y-m-d'),
                        'birthplace' => $faker->city,
                        'civil_status' => $civil_status,
                        'education' => $education,
                        'fb_account' => 'fb.com/primoashbee',
                        'contact_number'=>$mobile_number,
                        'street_address'=> $faker->address,
                        'barangay_address' => $barangay,
                        'city_address' => $faker->city,
                        'province_address' => $province,
                        'zipcode' => $faker->postCode,
                        'spouse_name' => $faker->name,
                        'spouse_contact_number' => $mobile_number,
                        'spouse_birthday' =>  $faker->dateTimeThisCentury->format('Y-m-d'),
                        'number_of_dependents' => $dependents,
                        'household_size' =>$dependents +2,
                        'years_of_stay_on_house' => $dependents + 5,
                        'house_type' => $house_type,
                        'tin' => rand(100000, 199999),
                        'umid' => rand(10000, 19999),
                        'sss' =>rand(10000, 19999),
                        'mother_maiden_name' => $faker->firstNameFemale.' '.$faker->lastname,
                        'notes' => $faker->realText($faker->numberBetween(10, 200)),
                        'office_id' => $office->id,
                        'created_by' => 0
                    ]);
                    $ids[]=  $user->id;
                    $application_number = rand(1000000,2);

                    $unit_of_plan = rand(1,2);
                    $member_first = $user->firstname;
                    $member_middle = $user->middlename;
                    $member_last = $user->lastname;
                    $birthday= $user->getRawOriginal('birthday');
                    $user->dependents()->create([
                        'application_number'=>$application_number,
                        'unit_of_plan'=>$unit_of_plan,
                        'member_firstname'=>$member_first,
                        'member_middlename'=>$member_middle,
                        'member_lastname'=>$member_last,
                        'created_by'=>2,
                        'member_birthday'=>$birthday
                    ]);
                }
            }
            $offices->map(function($office) use($count, $with_loans){
                for ($x=1; $x<= $count; $x++) {
                    $faker = Faker::create();
                
                    $gender = $faker->randomElement(['MALE', 'FEMALE']);
                    $civil_status = $faker->randomElement(['SINGLE', 'MARRIED','DIVORCED']);
                    $education = $faker->randomElement(['ELEMENTARY', 'HIGH SCHOOL','COLLEGE','VOCATIONAL']);
                    $barangay = $faker->randomElement(['San Jose', 'Sta. Rita','Gordon Heights','Pag-asa']);
                    $province = $faker->randomElement(['Zambales', 'Pampanga','Bataan']);
                    $dependents = rand(1, 5);
                    $house_type = $faker->randomElement(['RENTED','OWNED']);
                    $mobile_number = '09'.rand(100000000, 199999999);
                    // $office = Office::where('name', '')->first();
                    static $id = 1;
                    $user  = Client::create([
                        'client_id' => Office::makeClientID($office->id),
                        'firstname' => $faker->firstName,
                        'middlename'=>$faker->lastname,
                        'lastname'  =>$faker->lastname,
                        'suffix'=>$faker->suffix,
                        'nickname'=>$faker->firstname,
                        'gender'=> $gender,
                        'profile_picture_path' => 'https://via.placeholder.com/150',
                        'signature_path' => 'https://via.placeholder.com/150',
                        'birthday' => $faker->dateTimeThisCentury->format('Y-m-d'),
                        'birthplace' => $faker->city,
                        'civil_status' => $civil_status,
                        'education' => $education,
                        'fb_account' => 'fb.com/primoashbee',
                        'contact_number'=>$mobile_number,
                        'street_address'=> $faker->address,
                        'barangay_address' => $barangay,
                        'city_address' => $faker->city,
                        'province_address' => $province,
                        'zipcode' => $faker->postCode,
                        'spouse_name' => $faker->name,
                        'spouse_contact_number' => $mobile_number,
                        'spouse_birthday' =>  $faker->dateTimeThisCentury->format('Y-m-d'),
                        'number_of_dependents' => $dependents,
                        'household_size' =>$dependents +2,
                        'years_of_stay_on_house' => $dependents + 5,
                        'house_type' => $house_type,
                        'tin' => rand(100000, 199999),
                        'umid' => rand(10000, 19999),
                        'sss' =>rand(10000, 19999),
                        'mother_maiden_name' => $faker->firstNameFemale.' '.$faker->lastname,
                        'notes' => $faker->realText($faker->numberBetween(10, 200)),
                        'office_id' => $office->id,
                        'created_by' => 0
                    ]);
                    $application_number = rand(1000000,2);

                    $unit_of_plan = rand(1,2);
                    $member_first = $user->firstname;
                    $member_middle = $user->middlename;
                    $member_last = $user->lastname;
                    $birthday= $user->getRawOriginal('birthday');
                    $user->dependents()->create([
                        'application_number'=>$application_number,
                        'unit_of_plan'=>$unit_of_plan,
                        'member_firstname'=>$member_first,
                        'member_middlename'=>$member_middle,
                        'member_lastname'=>$member_last,
                        'created_by'=>2,
                        'member_birthday'=>$birthday
                    ]);
                }
            });

            if($with_loans){
                
                $bulk_disbursement_id = sha1(time());
                if(is_null($start_date)){
                    $start = now()->startOfDay()->subDays(6);
                    for($x=0;$x<=6;$x++){
                        $dates[] = $start->copy()->addDays($x);
                    }
                    $disbursement_date =  $dates[rand(0,count($dates)-1)];
                }else {
                    $disbursement_date = $start_date;
                }

                foreach(Client::whereIn('id',$ids)->get() as $client){
                    createLoan($client, $bulk_disbursement_id, uniqid(),$disbursement_date, $start_date);
                }
            }
            \DB::commit();
        }catch(Exception $e){
            return $e->getMessage();
        }
    }
    function createLoan($client , $bulk_disbursement_id, $cv_number,$disbursement_date, $start_date){
                $client = $client;
                $loan =  Loan::find(2);
                $fees = $loan->fees;
                $total_deductions = 0;


                $loan_amount = rand(2,99) * 1000;
                
                $number_of_installments = 24;
                $number_of_months = Loan::rates()->where('code',$loan->code)->first()->rates->where('installments',$number_of_installments)->first()->number_of_months;
                $fee_repayments = array();
                
                $dependents = $client->unUsedDependent()->pivotList();
                foreach($loan->fees as $fee){
                    $fee_amount = $fee->calculateFeeAmount($loan_amount, $number_of_installments,$loan,$dependents);
                    $total_deductions += $fee_amount;
                    $fee_repayments[] = (object)[
                        'id'=>$fee->id,
                        'name'=>$fee->name,
                        'amount'=>$fee_amount
                    ];
                }
                
                $disbursed_amount = $loan_amount - $total_deductions;
                $annual_rate = $loan->annual_rate;
                $start_date = $start_date;
        
                //get loan rates via loan and installment length
                $loan_interest_rate = Loan::rates($loan->id)->where('installments',$number_of_installments)->first()->rate;
        
                $data = array(
                    'principal'=>$loan_amount,
                    'annual_rate'=>$annual_rate,
                    'interest_rate'=>$loan_interest_rate,
                    'interest_interval'=>$loan->interest_interval,
                    'monthly_rate'=>$loan->monthly_rate,
                    'term'=>$loan->installment_method,
                    'term_length'=>$number_of_installments,
                    'disbursement_date'=>$disbursement_date,
                    'start_date'=>$start_date,
                    'office_id'=>$client->office->id
                );

                Log::info($data);
                
                $calculator = LoanAccount::calculate($data);
                Log::info(['calc'=>$calculator]);
                //dependent on calculator result.
        
                
                $loan_acc = $client->loanAccounts()->create([
                    'loan_id'=>$loan->id,
                    'amount'=>$loan_amount,
                    'principal'=>$loan_amount,
                    'interest'=>$calculator->total_interest,
                    'total_loan_amount'=>$calculator->total_loan_amount,
                    'interest_rate'=>$loan_interest_rate,
                    'number_of_months'=>$number_of_months,
                    'number_of_installments'=>$number_of_installments,

                    'total_deductions'=>$total_deductions,
                    'disbursed_amount'=>$disbursed_amount, //net disbursement
                    
                                
                    'total_balance'=>$loan_amount + $calculator->total_interest,
                    'principal_balance'=>$loan_amount,
                    'interest_balance'=>0,

                    'disbursement_date'=>$calculator->disbursement_date,
                    'first_payment_date'=>$calculator->start_date,
                    'last_payment_date'=>$calculator->end_date,
                    'created_by'=>3,
                ]);
            
                
                $lac = new LoanAccountController;
                $lac->createFeePayments($loan_acc,$fee_repayments);
                
                $lac->createInstallments($loan_acc,$calculator->installments);
                $client->unUsedDependent()->update(['status'=>'For Loan Disbursement','loan_account_id'=>$loan_acc->id]);
                // $account = $loan_acc->account()->create([
                $account = $loan_acc->update([
                    // 'client_id'=>$client->client_id,
                    'status'=>'Pending Approval'
                ]);
                $loan_acc->approve(3);
                $payment_info = [
                    'disbursement_date'=>$disbursement_date,
                    'first_repayment_date'=>$start_date,
                    'payment_method_id'=>2,
                    'office_id'=>21,
                    'disbursed_by'=>3,
                    'cv_number'=>$cv_number
                ];
                $loan_acc->disburse($payment_info,true,$bulk_disbursement_id);


    }
    function carbon(){
        return new \Carbon\Carbon();
    }
    function structure(){
        $struc = [
            "level"=>'main_office',
            "parent_level"=>null,
            "child"=> [
                "parent_level"=>"main_office",
                "level"=>"region",
                "child"=> [
                    "parent_level"=>"region",
                    "level"=>"area",
                    "child"=> [
                        "parent_level"=>"area",
                        "level"=>"branch"
                    ]
                ]
            ]
        ];
       
        
        return collect($struc);
        
    }

    function createPermission(){
        $permission = array(
            [
                'name' => 'view_client',
                'guard_name' => 'web'
            ],
            [
                'name' => 'create_client',
                'guard_name' => 'web'
            ],
            [
                'name' => 'edit_client',
                'guard_name' => 'web'
            ],
            [
                'name' => 'change_status_client',
                'guard_name' => 'web'
            ],
            [
                'name' => 'view_user',
                'guard_name' => 'web'
            ],
            [
                'name' => 'create_user',
                'guard_name' => 'web'
            ],
            [
                'name' => 'edit_user',
                'guard_name' => 'web'
            ],
            [
                'name' => 'change_status_user',
                'guard_name' => 'web'
            ],
            [
                'name' => 'change_status_deposit_account',
                'guard_name' => 'web'
            ],
            [
                'name' => 'view_deposit_account',
                'guard_name' => 'web'
            ],
            [
                'name' => 'create_deposit_account',
                'guard_name' => 'web'
            ],
            [
                'name' => 'edit_deposit_account',
                'guard_name' => 'web'
            ],
            [
                'name' => 'enter_deposit',
                'guard_name' => 'web'
            ],
            [
                'name' => 'enter_withdrawal',
                'guard_name' => 'web'
            ],
            [
                'name' => 'interest_posting',
                'guard_name' => 'web'
            ],
            [
                'name' => 'edit_loan_account',
                'guard_name' => 'web'
            ],
            [
                'name' => 'view_loan_account',
                'guard_name' => 'web'
            ],
            [
                'name' => 'create_loan_account',
                'guard_name' => 'web'
            ],
            [
                'name' => 'enter_repayment',
                'guard_name' => 'web'
            ],
            [
                'name' => 'approve_loan',
                'guard_name' => 'web'
            ],
            [
                'name' => 'disburse_loan',
                'guard_name' => 'web'
            ],   
            [
                'name' => 'change_status_loan_account',
                'guard_name' => 'web'
            ],
            [
                'name' => 'view_dashboard',
                'guard_name' => 'web'
            ],
            [
                'name' => 'view_reports',
                'guard_name' => 'web'
            ],
            [
                'name' => 'view_transactions',
                'guard_name' => 'web'
            ],
            [
                'name' => 'revert_transactions',
                'guard_name' => 'web'
            ],
            [
              'name' => 'create_cluster',
              'guard_name' => 'web'  
            ],
            [
              'name' => 'edit_cluster',
              'guard_name' => 'web'  
            ],
            [
              'name' => 'view_cluster',
              'guard_name' => 'web'  
            ],
            [
              'name' => 'extract_reports',
              'guard_name' => 'web'  
            ],
        );
        Permission::insert($permission);
    }

    function createRole(){
        $role = array(
            [
                'name' => 'Super Admin',
                'guard_name' => 'web'
            ],
            [
                'name' => 'Branch Manager',
                'guard_name' => 'web'
            ],
            [
                'name' => 'Branch Accountant',
                'guard_name' => 'web'
            ],
            [
                'name' => 'Area Manager',
                'guard_name' => 'web'
            ],
            [
                'name' => 'Area Accountant',
                'guard_name' => 'web'
            ],
            [
                'name' => 'Unit Supervisor',
                'guard_name' => 'web'
            ]
        );
        Role::insert($role);

        $user = User::find(4)->syncRoles(1);
        $user = User::find(3)->syncRoles(1);
        
        $role = Role::find(2)->syncPermissions([1,4,9,10,17,20,21,22,23,24,25,26,27]);
        $role = Role::find(3)->syncPermissions([1,2,3,4,10,11,12,13,14,15,16,17,18,19,20,24,25,26,28,29,3]);
        $role = Role::find(5)->syncPermissions([1,2,3,4,10,11,12,13,14,15,16,17,18,19,20,24,25,26,28,29,30]);
        $role = Role::find(4)->syncPermissions([1,2,3,4,10,11,12,13,14,15,16,17,18,19,20,24,25,26,28,29,30]);
        $role = Role::find(5)->syncPermissions([1,2,3,10,17]);

    }


    function createAdminAccount(){
        $user = User::create([
            'firstname' => 'Scheduler',
            'lastname' => 'Scheduler',
            'middlename' => 'Scheduler',
            'gender' => 'Male',
            'birthday' => Carbon::parse('1994-11-26'),
            'email' => 'scheduler@icloud.com',
            'notes'=>'scheduler account',
            'password' => Hash::make('sv9h4pld')
        ]);
    
        $user->assignToOffice(1);
        // $user->rooms()->attach(1);
    
        $user = User::create([
            'firstname' => 'BM',
            'lastname' => 'Dagupan',
            'middlename' => '-',
            'gender' => 'Male',
            'birthday' => \Carbon\Carbon::parse('1994-11-26'),
            'email' => 'bm.dagupan@light.org',
            'notes'=>'wala lang',
            'password' => Hash::make('sv9h4pld')
        ]);
        $user->assignToOffice(21);
        // $user->rooms()->attach(21);

        $user = User::create([
            'firstname' => 'Ashbee',
            'lastname' => 'Morgado',
            'middlename' => 'Allego',
            'gender' => 'Male',
            'birthday' => Carbon::parse('1994-11-26'),
            'email' => 'ashbee.morgado@icloud.com',
            'notes'=>'ajalksdjfdlksafjaldf',
            'password' => Hash::make('sv9h4pld')
        ]);
        $user->assignToOffice(1);
        // $user->rooms()->attach(1);
        $user = User::create([
            'firstname' => 'Nelson',
            'lastname' => 'Abilgos',
            'middlename' => 'Tan',
            'gender' => 'Male',
            'birthday' => Carbon::parse('1995-11-28'),
            'email' => 'nelsontan1128@gmail.com',
            'notes'=>'ajalksdjfdlksafjaldf',
            'password' => Hash::make('tannelsona')
        ]);
    
        $user->assignToOffice(1);
        // $user->rooms()->attach(1);
        $user = User::create([
            'firstname' => 'Hannah Arien',
            'lastname' => 'Mangalindan',
            'middlename' => 'Morgado',
            'gender' => 'Female',
            'birthday' => Carbon::parse('1997-05-31'),
            'email' => 'arien@morgado.com',
            'notes'=>'ajalksdjfdlksafjaldf',
            'password' => Hash::make('sv9h4pld')
        ]);
    
        $user->assignToOffice(21);
        // $user->rooms()->attach(21);
    }

    function createDeposits(){
        Deposit::create([
            'name'=>'RESTRICTED CBU',
            'product_id'=>'RCBU',
            'description'=>'aba ewan ko sa inyo',
            'minimum_deposit_per_transaction'=>0,
            'account_per_client'=>1,
            'auto_create_on_new_client'=>true,
            'interest_rate'=>2,
            'deposit_portfolio' => 0,
            'deposit_interest_expense' => 0,
        ]);
        Deposit::create([
            'name'=>'MANDATORY CBU',
            'product_id'=>'MCBU',
            'description'=>'aba ewan ko sa inyo',
            'minimum_deposit_per_transaction'=>50,
            'account_per_client'=>1,
            'auto_create_on_new_client'=>true,
            'interest_rate'=>2,
            'deposit_portfolio' => 0,
            'deposit_interest_expense' => 0,
        ]);
    }
    function generateFees(){
        Fee::create([
            'name'=>'CGLI Fee',
            'automated'=>true,
            'calculation_type'=>'matrix',
            'gl_account'=>526,
            'finance_charge'=>false
        ]);
        Fee::create([
            'name'=>'CGLI Premium',
            'automated'=>true,
            'calculation_type'=>'matrix',
            'gl_account'=>526,
            'finance_charge'=>false
        ]);

        Fee::create([
            'name'=>'MI Fee',
            'automated'=>true,
            'calculation_type'=>'fixed',
            'fixed_amount'=>90,
            'gl_account'=>523,
            'finance_charge'=>false
        ]);
        Fee::create([
            'name'=>'MI Premium',
            'automated'=>true,
            'calculation_type'=>'matrix',
            'has_unit_of_plan'=>true,
            'gl_account'=>526,
            'finance_charge'=>false
        ]);

        Fee::create([
            'name'=>'Documentary Stamp Tax',
            'automated'=>true,
            'calculation_type'=>'matrix',
            'gl_account'=>523,
            'finance_charge'=>false
        ]);

        Fee::create([
            'name'=>'Processing Fee 1.5%',
            'automated'=>true,
            'calculation_type'=>'percentage',
            'percentage' => 0.015,
            'gl_account'=>523,
            'finance_charge'=>true
        ]);
        Fee::create([
            'name'=>'Processing Fee 3%',
            'automated'=>true,
            'calculation_type'=>'percentage',
            'percentage' => 0.03,
            'gl_account'=>523,
            'finance_charge'=>true
        ]);
        Fee::create([
            'name'=>'Processing Fee 5%',
            'automated'=>true,
            'calculation_type'=>'percentage',
            'percentage' => 0.05,
            'gl_account'=>523,
            'finance_charge'=>true
        ]);


        Fee::create([
            'name'=>'PHIC Premium',
            'automated'=>true,
            'calculation_type'=>'matrix',
            'gl_account'=>526,
            'finance_charge'=>false
        ]);
      
    }
    function generateLoanProducts(){
        
        $id = Loan::create([
            "name"=>'MULTI-PURPOSE LOAN - Refinanced',
            "code"=>'RF-MPL',
            "description"=>"Refinanced - Multi-Purpose Loan is a flexible Microfinance Loan for growth and expansion of business, for education, housing, asset acquisitions and farm needs amounting to 4k-99k and must qualify based on credit limit and loan performance criteria. Payable in 6 or 12 months only on a weekly cash basis. This is an individual yet CLUSTERED loan with minimum of 20 PARTNER CLIENTS. Pre-termination is allowed if 50% of loan is paid and with either the following reason: (1) Resigning from the program; (2) Transferring to another product",
            "account_per_client"=>2,
            "interest_calculation_method_id"=>103,

            "minimum_installment"=>1,
            "default_installment"=>22,
            "maximum_installment"=>24,

            "installment_length"=>1,
            "installment_method"=>'weeks',

            "interest_interval"=>'Monthly',

            "monthly_rate"=>0.02,
            "annual_rate"=>0.24,
            "interest_rate"=>5.475225,
            

            "loan_minimum_amount"=>0,
            "loan_maximum_amount"=>99000,

            "grace_period"=>'NO GRACE PERIOD',
            "has_tranches"=>false,
        
            "loan_portfolio_active"=>26,
            "loan_portfolio_in_arrears"=>26,
            "loan_portfolio_matured"=>26,

            "loan_interest_income_active"=>26,
            "loan_interest_income_in_arrears"=>26,
            "loan_interest_income_matured"=>26,

            "loan_write_off"=>26,
            "loan_recovery"=>26,
            "created_by"=>2,
            "status"=>1,
            "has_optional_fees"=>true,
            "type"=>'DRP'
        ])->id;
        Loan::find($id)->fees()->attach([Fee::find(3)->id]); //MI FEE
        Loan::find($id)->fees()->attach([Fee::find(4)->id]); // MI PREMIUM
        Loan::find($id)->fees()->attach([Fee::find(6)->id]); // PF 1.5%
        Loan::find($id)->fees()->attach([Fee::find(5)->id]); //DST
        Loan::find($id)->fees()->attach([Fee::find(2)->id]); //CGLI 
        Loan::find($id)->fees()->attach([Fee::find(1)->id]); //CGLI PREMIUM
        
        $id = Loan::create([
            "name"=>'MULTI-PURPOSE LOAN - Restructured',
            "code"=>'RS-MPL',
            "description"=>"Restructured - Multi-Purpose Loan is a flexible Microfinance Loan for growth and expansion of business, for education, housing, asset acquisitions and farm needs amounting to 4k-99k and must qualify based on credit limit and loan performance criteria. Payable in 6 or 12 months only on a weekly cash basis. This is an individual yet CLUSTERED loan with minimum of 20 PARTNER CLIENTS. Pre-termination is allowed if 50% of loan is paid and with either the following reason: (1) Resigning from the program; (2) Transferring to another product",
            "account_per_client"=>2,
            "interest_calculation_method_id"=>103,

            "minimum_installment"=>1,
            "default_installment"=>22,
            "maximum_installment"=>24,

            "installment_length"=>1,
            "installment_method"=>'weeks',

            "interest_interval"=>'Monthly',
                
            'monthly_rate'=>0.02,
            "annual_rate"=>0.24,
            "interest_rate"=>5.475225,
            

            "loan_minimum_amount"=>0,
            "loan_maximum_amount"=>99000,

            "grace_period"=>'NO GRACE PERIOD',
            "has_tranches"=>false,
        
            "loan_portfolio_active"=>26,
            "loan_portfolio_in_arrears"=>26,
            "loan_portfolio_matured"=>26,

            "loan_interest_income_active"=>26,
            "loan_interest_income_in_arrears"=>26,
            "loan_interest_income_matured"=>26,

            "loan_write_off"=>26,
            "loan_recovery"=>26,
            "created_by"=>2,
            "status"=>1,
            "has_optional_fees"=>true,
            "type"=>'DRP'
        ])->id;
        Loan::find($id)->fees()->attach([Fee::find(3)->id]); //MI FEE
        Loan::find($id)->fees()->attach([Fee::find(4)->id]); // MI PREMIUM
        Loan::find($id)->fees()->attach([Fee::find(2)->id]); //CGLI 
        Loan::find($id)->fees()->attach([Fee::find(1)->id]); //CGLI PREMIUM
        
        $id = Loan::create([
            "name"=>'MULTI-PURPOSE LOAN',
            "code"=>'MPL',
            "description"=>"Multi-Purpose Loan is a flexible Microfinance Loan for growth and expansion of business, for education, housing, asset acquisitions and farm needs amounting to 4k-99k and must qualify based on credit limit and loan performance criteria. Payable in 6 or 12 months only on a weekly cash basis. This is an individual yet CLUSTERED loan with minimum of 20 PARTNER CLIENTS. Pre-termination is allowed if 50% of loan is paid and with either the following reason: (1) Resigning from the program; (2) Transferring to another product",
            "account_per_client"=>2,
            "interest_calculation_method_id"=>103,

            "minimum_installment"=>12,
            "default_installment"=>22,
            "maximum_installment"=>24,

            "installment_length"=>1,
            "installment_method"=>'weeks',

            "interest_interval"=>'Monthly',
            
            'monthly_rate'=>0.03,
            'annual_rate'=>0.36,
            "interest_rate"=>5.475225,
            

            "loan_minimum_amount"=>2000,
            "loan_maximum_amount"=>99000,

            "grace_period"=>'NO GRACE PERIOD',
            "has_tranches"=>false,
        
            "loan_portfolio_active"=>26,
            "loan_portfolio_in_arrears"=>26,
            "loan_portfolio_matured"=>26,

            "loan_interest_income_active"=>26,
            "loan_interest_income_in_arrears"=>26,
            "loan_interest_income_matured"=>26,

            "loan_write_off"=>26,
            "loan_recovery"=>26,
            "created_by"=>2,
            "status"=>1,
            "type"=>'NORMAL'
        ])->id;
        
        Loan::find($id)->fees()->attach([Fee::find(3)->id]); //MI FEE
        Loan::find($id)->fees()->attach([Fee::find(4)->id]); // MI PREMIUM
        Loan::find($id)->fees()->attach([Fee::find(6)->id]); // PF 1.5%
        Loan::find($id)->fees()->attach([Fee::find(5)->id]); //DST
        Loan::find($id)->fees()->attach([Fee::find(2)->id]); //CGLI 
        Loan::find($id)->fees()->attach([Fee::find(1)->id]); //CGLI PREMIUM
        $id = Loan::create([
            "code"=>'AGL',
            "name"=>'AGRICULTURAL LOAN',
            "description"=>"An individual agricultural production loan of 5k-150k for income rice farming households intended for production inputs and/or labor expenditures only. Loan term is from 3-6 months with monthly payment of interest and balloon payment of principal upon harvest and within maturity date. Pre-termination is allowed if 50% of loan is paid when yield happens in advance of the scheduled harvest but within the loan term applied.",
            
            "account_per_client"=>1,
            "interest_calculation_method_id"=>101,

            "minimum_installment"=>1,
            "default_installment"=>1,
            "maximum_installment"=>1,

            "installment_length"=>4,
            "installment_method"=>'weeks',

            "interest_interval"=>'Monthly',
            
            "monthly_rate"=>0.03,
            "interest_rate"=>2.5,

            "loan_minimum_amount"=>5000,
            "loan_maximum_amount"=>150000,

            "grace_period"=>'NO GRACE PERIOD',
            "has_tranches"=>true,
            "number_of_tranches"=>2,

            "loan_portfolio_active"=>26,
            "loan_portfolio_in_arrears"=>26,
            "loan_portfolio_matured"=>26,

            "loan_interest_income_active"=>26,
            "loan_interest_income_in_arrears"=>26,
            "loan_interest_income_matured"=>26,

            "loan_write_off"=>26,
            "loan_recovery"=>26,
            "created_by"=>2,
            "status"=>1,
            "type"=>'NORMAL'
        ])->id;
        Loan::find($id)->fees()->attach([Fee::find(1)->id]);
        Loan::find($id)->fees()->attach([Fee::find(3)->id]);
        Loan::find($id)->fees()->attach([Fee::find(4)->id]);
        Loan::find($id)->fees()->attach([Fee::find(5)->id]);
        $id = Loan::create([
            "code"=>'GML',
            "name"=>'GROWTH ORIENTED MICROFINANCE ENTERPRISE LOAN',
            "description"=>"Growth Oriented Microfinance Enterprise Loan or GML is an individual productive loan for the growth and expansion of micro-enterprise sectors with loan amount of 100k-150k and must qualify based on credit limit and loan performance criteria. Payable in 6 or 12 months only on a Bi-monthly basis thru PDC (Loan and CBU). Pre-termination is allowed if 50% of loan is paid and with either of the following reason: (1) Resigning from the program; (2) Business expansion; (3) Transferring to another product.",

            "account_per_client"=>1,
            "interest_calculation_method_id"=>101,

            "minimum_installment"=>12,
            "default_installment"=>22,
            "maximum_installment"=>24,

            "installment_length"=>14,
            "installment_method"=>'days',

            "interest_interval"=>'Monthly',
            "monthly_rate"=>0.03,
            "interest_rate"=>5.18461,

            "loan_minimum_amount"=>100000,
            "loan_maximum_amount"=>150000,

            "grace_period"=>'NO GRACE PERIOD',
            "has_tranches"=>false,


            "loan_portfolio_active"=>26,
            "loan_portfolio_in_arrears"=>26,
            "loan_portfolio_matured"=>26,

            "loan_interest_income_active"=>26,
            "loan_interest_income_in_arrears"=>26,
            "loan_interest_income_matured"=>26,

            "loan_write_off"=>26,
            "loan_recovery"=>26,
            "created_by"=>2,
            "status"=>1,
            "type"=>'NORMAL'
        ])->id;
        Loan::find($id)->fees()->attach([Fee::find(3)->id]); //MI FEE
        Loan::find($id)->fees()->attach([Fee::find(4)->id]); // MI PREMIUM
        Loan::find($id)->fees()->attach([Fee::find(6)->id]); // PF 1.5%
        Loan::find($id)->fees()->attach([Fee::find(5)->id]); //DST
        Loan::find($id)->fees()->attach([Fee::find(2)->id]); //CGLI 
        Loan::find($id)->fees()->attach([Fee::find(1)->id]); //CGLI PREMIUM
        
    }
    function generatePaymentMethods(){
        $methods = array(
            [
            'name'=>'MIGRATION PAYMENT',
            'for_disbursement'=>0,
            'for_repayment'=>0,
            'for_deposit'=>0,
            'for_withdrawal'=>0,
            'for_recovery'=>0,
            'gl_account_code'=>1,
            'created_at'=>Carbon::now(),
            'updated_at'=>Carbon::now()
            ],
            [
            'name'=>'CASH ON HAND',
            'for_disbursement'=>true,
            'for_repayment'=>true,
            'for_deposit'=>true,
            'for_withdrawal'=>true,
            'for_recovery'=>true,
            'gl_account_code'=>1,
            'created_at'=>Carbon::now(),
            'updated_at'=>Carbon::now()
            ],
            [
            'name'=>'CASH IN BANK - BANK OF COMMERCE',
            'for_disbursement'=>true,
            'for_repayment'=>true,
            'for_deposit'=>true,
            'for_withdrawal'=>true,
            'for_recovery'=>true,
            'gl_account_code'=>1,
            'created_at'=>Carbon::now(),
            'updated_at'=>Carbon::now()
            ],
            [
            'name'=>'CASH IN BANK - BDO',
            'for_disbursement'=>true,
            'for_repayment'=>true,
            'for_deposit'=>true,
            'for_withdrawal'=>true,
            'for_recovery'=>true,
            'gl_account_code'=>1,
            'created_at'=>Carbon::now(),
            'updated_at'=>Carbon::now()
            ],
            [
            'name'=>'CASH IN BANK - BPI',
            'for_disbursement'=>true,
            'for_repayment'=>true,
            'for_deposit'=>true,
            'for_withdrawal'=>true,
            'for_recovery'=>true,
            'gl_account_code'=>1,
            'created_at'=>Carbon::now(),
            'updated_at'=>Carbon::now()
            ],
            [
            'name'=>'CASH IN BANK - CHINA BANK',
            'for_disbursement'=>true,
            'for_repayment'=>true,
            'for_deposit'=>true,
            'for_withdrawal'=>true,
            'for_recovery'=>true,
            'gl_account_code'=>1,
            'created_at'=>Carbon::now(),
            'updated_at'=>Carbon::now()
            ],
            [
            'name'=>'CASH IN BANK - EAST WEST',
            'for_disbursement'=>true,
            'for_repayment'=>true,
            'for_deposit'=>true,
            'for_withdrawal'=>true,
            'for_recovery'=>true,
            'gl_account_code'=>1,
            'created_at'=>Carbon::now(),
            'updated_at'=>Carbon::now()
            ],
            [
            'name'=>'CASH IN BANK - EAST WEST (RURAL)',
            'for_disbursement'=>true,
            'for_repayment'=>true,
            'for_deposit'=>true,
            'for_withdrawal'=>true,
            'for_recovery'=>true,
            'gl_account_code'=>1,
            'created_at'=>Carbon::now(),
            'updated_at'=>Carbon::now()
            ],
            [
            'name'=>'CASH IN BANK - LAND BANK',
            'for_disbursement'=>true,
            'for_repayment'=>true,
            'for_deposit'=>true,
            'for_withdrawal'=>true,
            'for_recovery'=>true,
            'gl_account_code'=>1,
            'created_at'=>Carbon::now(),
            'updated_at'=>Carbon::now()
            ],
            [
            'name'=>'CASH IN BANK - PBB',
            'for_disbursement'=>true,
            'for_repayment'=>true,
            'for_deposit'=>true,
            'for_withdrawal'=>true,
            'for_recovery'=>true,
            'gl_account_code'=>1,
            'created_at'=>Carbon::now(),
            'updated_at'=>Carbon::now()
            ],
            [
            'name'=>'CASH IN BANK - PNB',
            'for_disbursement'=>true,
            'for_repayment'=>true,
            'for_deposit'=>true,
            'for_withdrawal'=>true,
            'for_recovery'=>true,
            'gl_account_code'=>1,
            'created_at'=>Carbon::now(),
            'updated_at'=>Carbon::now()
            ],
            [
            'name'=>'CASH IN BANK - PNB (SAVINGS)',
            'for_disbursement'=>true,
            'for_repayment'=>true,
            'for_deposit'=>true,
            'for_withdrawal'=>true,
            'for_recovery'=>true,
            'gl_account_code'=>1,
            'created_at'=>Carbon::now(),
            'updated_at'=>Carbon::now()
            ],
            [
            'name'=>'CASH IN BANK - RCBC SAVINGS',
            'for_disbursement'=>true,
            'for_repayment'=>true,
            'for_deposit'=>true,
            'for_withdrawal'=>true,
            'for_recovery'=>true,
            'gl_account_code'=>1,
            'created_at'=>Carbon::now(),
            'updated_at'=>Carbon::now()
            ],
            [
            'name'=>'CASH IN BANK - SECURITY BANK',
            'for_disbursement'=>true,
            'for_repayment'=>true,
            'for_deposit'=>true,
            'for_withdrawal'=>true,
            'for_recovery'=>true,
            'gl_account_code'=>1,
            'created_at'=>Carbon::now(),
            'updated_at'=>Carbon::now()
            ],
            [
            'name'=>'CASH IN BANK - UCPB',
            'for_disbursement'=>true,
            'for_repayment'=>true,
            'for_deposit'=>true,
            'for_withdrawal'=>true,
            'for_recovery'=>true,
            'gl_account_code'=>1,
            'created_at'=>Carbon::now(),
            'updated_at'=>Carbon::now()
            ],
            [
            'name'=>'CTLP',
            'for_disbursement'=>true,
            'for_repayment'=>true,
            'for_deposit'=>true,
            'for_withdrawal'=>true,
            'for_recovery'=>true,
            'gl_account_code'=>1,
            'created_at'=>Carbon::now(),
            'updated_at'=>Carbon::now()
            ],
            [
            'name'=>'INTEREST POSTING',
            'for_disbursement'=>false,
            'for_repayment'=>false,
            'for_deposit'=>false,
            'for_withdrawal'=>false,
            'for_recovery'=>false,
            'gl_account_code'=>1,
            'created_at'=>Carbon::now(),
            'updated_at'=>Carbon::now()
            ]
        );
        PaymentMethod::insert($methods);
    }

    function generateDefaultPaymentMethods(){
        $list = Office::where('level','branch')->get();

        $list->map(function($branch){
            DefaultPaymentMethod::create([
                'office_id'=>$branch->id,
                'disbursement_payment_method_id'=>1,
                'repayment_payment_method_id'=>1,
                'deposit_payment_method_id'=>1,
                'withdrawal_payment_method_id'=>1,
                'recovery_payment_method_id'=>1,
                ]);
        });
    }

    function generateStucture   (){
        $structure = Excel::toCollection(new OfficeImport, "public/OFFICE STRUCTURE.xlsx");
        $data = array();
        $ctr = 0;
    
        foreach($structure[0] as $level){
            if ($ctr>0) {
                $data[] = array(
                'id'=>$level[0],
                'parent_id'=>$level[2],
                'level'=>$level[4],
                'name'=>$level[3],
                'code'=>$level[1],
                'created_at'=> Carbon::now(),
                'updated_at'=> Carbon::now(),
                'level_in_number'=>$level[5]
                );
                $rooms[] = array(
                    'name'=>$level[3],
                    'office_id'=>$level[0]
                );
                // echo $level[3].' : '.$level[4].'<br>';
            }
            $ctr++;
        }
     
        Office::insert($data);
        Room::insert($rooms);
    }
    
    function createUser($branches=5){
        $data = array(
            'firstname'=>'Angeles',
            'middlename'=>'-',
            'lastname'=>'Branch',
            'gender'=>'Male',
            'birthday'=>'1994-11-26',
            'notes'=>'Notes are here. Wala lang. Test notes.',
            'email'=>'angeles@light.org.ph',
            'password'=> Hash::make('sv9h4pld'),
            'created_by'=>0

        );

        $user = User::create($data);
        $user->office()->attach([
            'user_id'=>$user->id,
            'office_id'=>Office::where('name','ANGELES')->first()->id
        ]);
        // OfficeUser::create([
        //     'user_id'=>$user->id,
        //     'office_id'=>Office::where('name','ANGELES')->first()->id
        // ]);
        
        $data = array(
            'firstname'=>'Ashbee',
            'middlename'=>'Allego',
            'lastname'=>'Morgado',
            'gender'=>'Male',
            'birthday'=>'1994-11-26',
            'notes'=>'Notes are here. Wala lang. Test notes.',
            'email'=>'ashbee.morgado@icloud.com',
            'password'=> Hash::make('sv9h4pld'),
            'created_by'=>0
        );

        $user = User::create($data);
        $user->office()->attach([
            'user_id'=>$user->id,
            'office_id'=>Office::where('name','ANGELES')->first()->id
        ]);
        

        $offices = Office::where('level','branch')->get()->random($branches);
    
        foreach($offices as $office){
            OfficeUser::create([
                'user_id'=>$user->id,
                'office_id'=>$office->id
            ]);
        }
    }

    function unauthorized(){
        return (array('msg'=>'Unauthorized Request'));
    }

    function generateCluster(){
        $office = Office::where('name','ANGELES')->first();
        $user = User::all()->random(1)->first();
        

        for($x=1;$x<=100;$x++){
            $client = Client::all()->random(1)->first();
            Cluster::create([
                'officer_id'=>$user->id,
                'office_id'=>$office->id,
                'client_id'=>$client->id,
                'code'=> 'ANG'.pad($x,3),
                'notes'=> 'hahaha'
            ]);
        }
            
    }
    function pad($number, $character, $padder='0'){
        return str_pad($number, $character, $padder, STR_PAD_LEFT);
    }
    function numberFormat($number, $decimals = 2, $sep = ".", $k = ","){
        $number = bcdiv($number, 1, $decimals); // Truncate decimals without rounding
        return number_format($number, $decimals, $sep, $k); // Format the number
    }

    function generateClientID($count=100){
        $branch = "010ANG";
        $ids = [];
        
        for($x=1; $x<=1000;$x++){
            $client_id = $branch."-PC".pad($x,5);
            $ids[] = $client_id;
        }

        return $ids;
    }

    function makeClientID($office_id){
        
        $office = Office::find($office_id);
        
        if($office->level=="branch"){
            $code = $office->code;
            $office_ids = $office->getLowerOfficeIDS();
            $count = Client::whereIn('office_id',$office_ids)->count();
            return $code . '-PC' . pad($count + 1, 5);
        }
        
        $office = $office->getTopOffice('branch');
        $code = $office->code;
        $office_ids = $office->getLowerOfficeIDS();
        $count = Client::whereIn('office_id',$office_ids)->count();
        return $code . '-PC' . pad($count + 1, 5);

    }
    function getNextID($string){
        // substr("ASDASDAS",)
        return substr($string, -5, 5);
    }

    function hasString($string, $match){
        return  Str::contains($string, $match);
    }

    function checkClientPaths(){
        if(!Storage::disk('local')->exists('signatures')){
            Storage::makeDirectory('public/signatures');
        }
        if(!Storage::disk('local')->exists('profile_photos')){
            Storage::makeDirectory('public/profile_photos');
        }
    }

    function breadcrumbize($string){
        $str = str_replace('/',' / ',$string);
        return str_replace('_',' ',$str);
    }

    function createLoanAccount($amt=10000){
    
        // $acc = Client::whereClientId('049SLU-PC00001')->first();
        // $product = Loan::first();
        // $fees = $product->fees;
        // $total_deductions = 0;

        // $loan_amount = $amt;
        // $number_of_installments = 24;
        // $dependents = null;
        // if($acc->hasActiveDependent()){
        //     $acc->activeDependent->pivotList();
        // }
        // foreach($product->fees as $fee){
        //     echo $fee->name."=".$fee->calculateFeeAmount($loan_amount, $number_of_installments,$product,$dependents). " | ";
        //     $total_deductions += $fee->calculateFeeAmount($loan_amount, $number_of_installments,$product,$dependents);
        // }
        
        // $disbursed_amount = $loan_amount - $total_deductions;
        
        // return $acc->loanAccounts()->create([
        //     'loan_id'=>$product->id,
        //     'amount'=>$loan_amount,
        //     'principal'=>10000,
        //     'interest'=>18000,
        //     'interest_rate'=>5.475225,
        //     'number_of_installments'=>$number_of_installments,

        //     'total_deductions'=>$total_deductions,
        //     'disbursed_amount'=>$disbursed_amount, //net disbursement
            
        //     'disbursement_date'=>Carbon::now(),
        //     'first_payment_date'=>Carbon::now(),
        //     'la∑st_payment_date'=>Carbon::now()->addWeeks(24),

        //     'created_by'=>1,
        // ]);
        
        
    }

    function money($item,$decimal){
        return env('CURRENCY_SIGN') . ' ' . number_format($item,$decimal);
    }
    function createHoliday(){
        Holiday::create(['date'=>\Carbon\Carbon::now(),'name'=>'Sample holiday','office_id'=>'1']);
    }

    function testHoliday(){
        $date = \Carbon\Carbon::parse("2020-09-29");
        $office_id  = 24;
        
        $scheduler = new \Scheduler($date,$office_id);
        return $scheduler;
        
    }

    function addWeek($date){
        return $date = \Carbon\Carbon::parse($date)->addWeek();
    }
?>