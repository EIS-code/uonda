<?php

use Illuminate\Database\Seeder;
use App\Feed;

class DummyFeedsSeeder extends Seeder
{
    private $totalFeeds = 10000;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $confirmed = $this->command->confirm(__('Are you sure ?'));

        if ($confirmed) {
            /* DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            Feed::truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;'); */

            $feeds = [];

            for ($i = 1; $i <= $this->totalFeeds; $i++) {
                $feeds[] = [
                    'title'             => Str::random(15),
                    'sub_title'         => Str::random(20),
                    'attachment'        => '',
                    'description'       => Str::random(200),
                    'short_description' => Str::random(100),
                    'type'              => Feed::TYPE_NULL
                ];
            }

            if (!empty($feeds)) {
                Feed::insert($feeds);
            }
        }
    }
}
