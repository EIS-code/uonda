<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        if (env('APP_ENV', '') === 'local' || env('APP_ENV', '') == 'development') {
            $this->call(DummyFeedsSeeder::class);
            $this->call(DummyUsersSeeder::class);
        }
        $this->call(LocationSeeder::class);
        $this->call(TextSeeder::class);
        $this->call(ConstantsSeeder::class);
        $this->call(UserReportQuestionsSeeder::class);
        // $this->call(ImageSeeder::class);
        $this->call(SuperadminUserSeeder::class);
        $this->call(ResetPasswordEmailTemplateSeeder::class);
        $this->call(WelcomeEmailTemplateSeeder::class);
        $this->call(VerifyEmailTemplateSeeder::class);
    }
}
