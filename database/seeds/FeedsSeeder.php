<?php

use Illuminate\Database\Seeder;
use App\Feed;

class FeedsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $confirmed = $this->command->confirm(__('Are you sure ? Because script will remove all the old Feeds and then add new.'));

        if ($confirmed) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            Feed::truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            Feed::create([
                'title'       => 'Feed - 1',
                'sub_title'   => 'Lorem ipsum dolor sit',
                'attachment'  => 'demo.jpg',
                'description' => 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.',
                'type'        => Feed::TYPE_IMAGE
            ]);

            Feed::create([
                'title'       => 'Feed - 2',
                'sub_title'   => 'Lorem ipsum dolor sit',
                'attachment'  => 'demo1.mp4',
                'description' => 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.',
                'type'        => Feed::TYPE_VIDEO
            ]);

            Feed::create([
                'title'       => 'Feed - 3',
                'sub_title'   => 'Lorem ipsum dolor sit',
                'attachment'  => '',
                'description' => 'Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.'
            ]);
        }
    }
}
