<?php

use Illuminate\Database\Seeder;
use App\User;

use Faker\Generator;
use Illuminate\Container\Container;

class DummyUsersSeeder extends Seeder
{
    protected $faker;

    public function __construct()
    {
        $this->faker = $this->withFaker();
    }

    /**
     * Get a new Faker instance.
     *
     * @return \Faker\Generator
     */
    protected function withFaker()
    {
        return Container::getInstance()->make(Generator::class);
    }


    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i = 0; $i < 500; $i++) {
            $countryId = random_int(\DB::table('countries')->min('id'), \DB::table('countries')->max('id'));

            $stateArray = \DB::table('states')->where('country_id', $countryId)->pluck('id')->toArray();
            if(empty($stateArray)) {
                continue;
            }
            $states = array_rand($stateArray);
            $stateId = $stateArray[$states];

            $cityArray = \DB::table('cities')->where('state_id', $stateId)->pluck('id')->toArray();
            $cities = array_rand($cityArray);
            $cityId = $cityArray[$cities];
            
            \DB::table('users')->insert([
                'name' => $this->faker->name,
                'email' => $this->faker->unique()->safeEmail,
                'password' => bcrypt('password@123'), // Can also be used Hash::make('password@123')
                'email_verified_at' => now(),
                'remember_token' => \Str::random(10),
                'country_id' => $countryId,
                'state_id' => $stateId,
                'city_id' => $cityId,
                'is_accepted' =>'1',
            ]);
        }
    }
}