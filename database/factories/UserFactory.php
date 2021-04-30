<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\User;
use App\Client;
use App\Office;
use App\Cluster;
use App\HouseholdIncome;
use App\Notification;
use Illuminate\Support\Str;
use Faker\Generator as Faker;
use Illuminate\Support\Facades\Hash;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(User::class, function (Faker $faker) {
    // return [
    //     'name' => $faker->name,
    //     'email' => $faker->unique()->safeEmail,
    //     'email_verified_at' => now(),
    //     'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
    //     'remember_token' => Str::random(10),
    // ];
    $gender = $faker->randomElement(['MALE', 'FEMALE']);
    return [ 
        'firstname'=> $faker->firstName,
        'middlename'=>$faker->lastName,
        'lastname'=>$faker->lastName,
        'gender'=>$gender,
        'birthday'=>$faker->dateTimeThisCentury->format('Y-m-d'),
        'notes'=>'Notes are here. Wala lang. Test notes.',
        'email'=>$faker->unique()->safeEmail,
        'password'=> Hash::make('sv9h4pld'),
        'created_by'=>0
    ];
});

$factory->define(Cluster::class, function(Faker $faker){
    $office = Office::where('name','ANGELES')->first();
    $user = User::all()->random(1)->first();
    $client = Client::all()->random(1)->first();
    return[
        'officer_id'=>$user->id,
        'office_id'=>$office->id,
        'client_id'=>$client->id,
        'code'=> $office->code."-A".pad(rand(0,100),3),
        'notes'=> $faker->realText($faker->numberBetween(10,200))
    ];
});

$factory->define(Client::class,function (Faker $faker) {
    $office = new Office();
    $gender = $faker->randomElement(['MALE', 'FEMALE']);
    $civil_status = $faker->randomElement(['SINGLE', 'MARRIED','DIVORCED']);
    $education = $faker->randomElement(['ELEMENTARY', 'HIGH SCHOOL','COLLEGE','VOCATIONAL']);
    $barangay = $faker->randomElement(['San Jose', 'Sta. Rita','Gordon Heights','Pag-asa']);
    $province = $faker->randomElement(['Zambales', 'Pampanga','Bataan']);
    $dependents = rand(1,5);
    $house_type = $faker->randomElement(['RENTED','OWNED']);
    $mobile_number = '09'.rand(100000000,199999999);
    $office = $faker->randomElement(Office::where('level','branch')->take(10)->get());
    // $office = Office::where('name', '')->first();
    static $id = 1;
    return [    
        'client_id' => $office->code."-PC".pad($id++,5),
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
        'tin' => rand(100000,199999),
        'umid' => rand(10000,19999),
        'sss' =>rand(10000,19999),
        'mother_maiden_name' => $faker->firstNameFemale.' '.$faker->lastname,
        'notes' => $faker->realText($faker->numberBetween(10,200)),
        'office_id' => $office->id,
        'created_by' => 0
    ];

});

$factory->define(HouseholdIncome::class, function (Faker $faker, $client_id){
    $is_self_employed = $faker->randomElement([true,false]);
    $is_employed = null;
    $service_type = null;
    $position=null;
    $company_name=null;
    
    $spouse_is_self_employed = $faker->randomElement([true,false]);
    $spouse_is_employed = null;
    $spouse_service_type = null;
    $spouse_position=null;
    $spouse_company_name=null;

    $service_type_monthly_gross_income = 0;
    $employed_monthly_gross_income = 0;
    $spouse_service_type_monthly_gross_income = 0;
    $spouse_employed_monthly_gross_income = 0;
    //if self-emplyoed service type should be not null
    if($is_self_employed){
        $is_employed = false;
        $service_type = $faker->randomElement(['AGRICULTURE','TRADING/MERCHANDISING','MANUFACTURING','SERVICE','OTHERS']);
        $service_type_monthly_gross_income = rand(100,200) * 500;
    }else{
        $is_employed = true;
        $position = $faker->randomElement(['Assistant','Officer','Supervisor','Manager','Executive','Board Member']);
        $company_name=$faker->randomElement(['LIGHT Microfinance','TPKI','KMBI','Valve','Activision','Marvel','Netflix','Apple']);
        $employed_monthly_gross_income = rand(100,200) * 500;
    }
    
    if($spouse_is_self_employed){
        $spouse_is_employed = false;
        $spouse_service_type = $faker->randomElement(['Agriculture','Trading/Merchandising','Manufacturing','Service','Others']);
        $spouse_service_type_monthly_gross_income = rand(100,200) * 500;
    }else{
        $spouse_is_employed = true;
        $spouse_position = $faker->randomElement(['Assistant','Officer','Supervisor','Manager','Executive','Board Member']);
        $spouse_company_name=$faker->randomElement(['LIGHT Microfinance','TPKI','KMBI','Valve','Activision','Marvel','Netflix','Apple']);
        $spouse_employed_monthly_gross_income = rand(100,200) * 500;
    }
    
    
    $spouse_monthly_gross_income = rand(100,200) * 500;
    

    $has_remittance = $faker->randomElement([true,false]);
    $remittance_amount =null;
    if($has_remittance){
        $remittance_amount=rand(100,200) * 500;
    }

    $has_pension = $faker->randomElement([true,false]);
    $pension_amount =null;
    if($has_pension){
        $pension_amount=rand(100,200) * 500;
    }

    $total_household_income = 
            $service_type_monthly_gross_income + 
            $employed_monthly_gross_income + 
            $spouse_service_type_monthly_gross_income + 
            $spouse_employed_monthly_gross_income + 
            $remittance_amount +
            $pension_amount;

    return[
        'client_id' => $client_id,
        'is_self_employed' => $is_self_employed,
        
        'service_type' => $service_type,
        'service_type_monthly_gross_income' => $service_type_monthly_gross_income,
        
        'client_id' => $client_id,

        'is_employed' => $is_employed,
        'employed_position' => $position,
        'employed_company_name' => $company_name,
        'employed_monthly_gross_income' => $employed_monthly_gross_income,


        'spouse_is_self_employed' => $spouse_is_self_employed,
        
        'spouse_service_type' => $spouse_service_type,
        'spouse_service_type_monthly_gross_income' => $spouse_service_type_monthly_gross_income,
        
        'spouse_is_employed' => $spouse_is_employed,
        'spouse_employed_position' => $spouse_position,
        'spouse_employed_company_name' => $spouse_company_name,
        'spouse_employed_monthly_gross_income' => $spouse_employed_monthly_gross_income,

        'has_remittance' => $has_remittance,
        'remittance_amount' => $remittance_amount,

        'has_pension' => $has_pension,
        'pension_amount' => $pension_amount,

        'total_household_income' => $total_household_income,

    ];
});

$factory->afterMaking(Client::class, function($client, $user){

    factory(App\HouseholdIncome::class)->create(['client_id'=>$client->client_id]);
    //$client->househould_income()->create(factory(App\HouseholdIncome::class,$client)->make());
});
