<?php

use App\User;
use Illuminate\Database\Seeder;

class SuperadminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $confirmed = $this->command->confirm(__('Are you sure ? Because script will remove the old Superadmin credentials data and then add new.'));

        if ($confirmed) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');

            $oldSuperadmins = User::where('is_admin', User::IS_ADMIN)->get();

            if (!empty($oldSuperadmins) && !$oldSuperadmins->isEmpty()) {
                User::where('is_admin', User::IS_ADMIN)->delete();
            }

            User::create([
                'id'            => User::ADMIN_ID,
                'name'          => 'Superadmin',
                'email'         => 'community@uonda.com',
                'password'      => Hash::make('uk@2021'),
                'is_admin'      => User::IS_ADMIN,
                'device_type'   => 'web'
            ]);

            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }
    }
}
