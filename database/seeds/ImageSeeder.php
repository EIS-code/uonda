<?php

use Illuminate\Database\Seeder;
use App\Image;

class ImageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $files = File::allFiles(public_path('storage\\test'));

        foreach ($files as $image) {
            if (!is_dir(storage_path('test') . '/' . $image->getPath())) {
                if (!empty($image->getFileName())) {
                    $images[] = [
                        'path' => $image->getFileName()
                    ];
                }
            }
        }

        Image::truncate();
        Image::insert($images);
    }
}
