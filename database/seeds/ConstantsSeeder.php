<?php

use Illuminate\Database\Seeder;
use App\Constant;

class ConstantsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $confirmed = $this->command->confirm(__('Are you sure ? Because script will remove all the old Constants and then add new.'));

        if ($confirmed) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            Constant::truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            Constant::create([
                'key'   => 'TERMS_AND_CONDITIONS',
                'value' => ''
            ]);

            Constant::create([
                'key'   => 'ABOUT_US',
                'value' => ''
            ]);

            Constant::create([
                'key'   => 'EXPLORE_DISTANCE',
                'value' => '500'
            ]);

            Constant::create([
                'key'   => 'EMAIL_DYNAMIC_FIELDS',
                'value' => json_encode(['name' => 'for user name', 'user_name' => 'for username', 'sur_name' => 'for surname'])
            ]);

            Constant::create([
                'key'   => 'APP_PRIVACY_POLICY',
                'value' => ''
            ]);
        }
    }
}
