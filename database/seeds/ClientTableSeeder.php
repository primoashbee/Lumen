cl<?php

use App\Client;
use Illuminate\Database\Seeder;

class ClientTableSeeder extends Seeder
{
    /** 
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        factory(Client::class,250)->create()->each(function($user){
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
        });
    }
}
