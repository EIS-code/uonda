<?php

use Illuminate\Database\Seeder;
use App\UserReportQuestion;

class UserReportQuestionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $confirmed = $this->command->confirm(__('Are you sure ? Because script will remove all the old Questions and then add new.'));

        if ($confirmed) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            UserReportQuestion::truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            UserReportQuestion::create([
                'question' => 'Question - 01'
            ]);

            UserReportQuestion::create([
                'question' => 'Question - 02'
            ]);

            UserReportQuestion::create([
                'question' => 'Question - 03'
            ]);

            UserReportQuestion::create([
                'question' => 'Question - 04'
            ]);

            UserReportQuestion::create([
                'question' => 'Question - 05'
            ]);
        }
    }
}
