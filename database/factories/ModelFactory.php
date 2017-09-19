<?php

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| Here you may define all of your model factories. Model factories give
| you a convenient way to create models for testing and seeding your
| database. Just tell the factory how a default model should look.
|
*/

$factory->define(App\User::class, function (Faker\Generator $faker) {
    return [
      'uid'  => str_random(32),
      'first_name' => $faker->firstName,
      'last_name' => $faker->lastName,
      'email' => $faker->email,
      'gender' => $faker->randomElement($array = array ('male', 'female')),
      'country_code' => rand(200,299),
      'password' => \Illuminate\Support\Facades\Hash::make('test-password'),
      'phone' => $faker->phoneNumber,
      'isActive' => rand(0,1)
    ];
});
