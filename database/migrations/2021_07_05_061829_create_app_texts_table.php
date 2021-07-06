<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAppTextsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('app_texts', function (Blueprint $table) {
            $table->id();
            $table->string('key');
            $table->string('english_text');
            $table->string('show_text');
            $table->enum('type',[0,1])->comment('0: Notification, 1: API response');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('app_texts');
    }
}
