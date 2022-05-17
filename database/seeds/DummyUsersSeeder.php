<?php

use Illuminate\Database\Seeder;
use App\User;

class DummyUsersSeeder extends Seeder
{
    private $totalUsers = 1000;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $confirmed = $this->command->confirm(__('Are you sure ?'));

        if ($confirmed) {
            $create = [];

            for ($i = 1; $i <= $this->totalUsers; $i++) {
                $create[] = [
                    'name'      => Str::random(10),
                    'email'     => Str::random(10).'@gmail.com',
                    'password'  => Hash::make('123456')
                ];
            }

            if (!empty($create)) {
                User::insert($create);
            }
        }
    }
}
