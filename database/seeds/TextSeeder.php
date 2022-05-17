<?php

use Illuminate\Database\Seeder;
use App\AppText;

class TextSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $confirmed = $this->command->confirm(__('Are you sure ? Because script will remove all the old text data and then add new.'));

        if ($confirmed) {
            AppText::truncate();

            //notification
            $notificationJson = Storage::disk('public')->get('notification.json');
            $notificationsArray = !empty($notificationJson) ? json_decode($notificationJson, true) : [];
            if (json_last_error() === JSON_ERROR_NONE && !empty($notificationsArray)) {
                foreach ((array)$notificationsArray as $notification) {
                    AppText::insert($notification);
                }
            }
            
            //API response
            $apiJson = Storage::disk('public')->get('apiResponse.json');
            $apiArray = !empty($apiJson) ? json_decode($apiJson, true) : [];
            if (json_last_error() === JSON_ERROR_NONE && !empty($apiArray)) {
                foreach ((array)$apiArray as $response) {
                    AppText::insert($response);
                }
            }
        }
    }
}
