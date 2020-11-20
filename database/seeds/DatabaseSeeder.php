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
        // $this->call(LocationSeeder::class);
        // $this->call(ConstantsSeeder::class);
        // $this->call(FeedsSeeder::class);
        $this->call(UserReportQuestionsSeeder::class);
    }
}
