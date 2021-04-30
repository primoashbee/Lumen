<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Model;
use App\Notification;
use Faker\Generator as Faker;


$factory->define(Notification::class, function (Faker $faker){
    return [
        'link'=>"https://www.youtube.com/watch?v=tG3HDRKK2Vc",
        'msg'=>$faker->text(55),
        'from'=>1,
        'to'=>3,
    ];
});
