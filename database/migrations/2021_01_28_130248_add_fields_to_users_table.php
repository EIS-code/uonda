<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('sur_name')->nullable()->after('user_name');
            $table->bigInteger('origin_country_id')->unsigned()->nullable()->after('country_id');
            $table->foreign('origin_country_id')->references('id')->on('countries')->onDelete('cascade');
            $table->bigInteger('origin_city_id')->unsigned()->nullable()->after('city_id');
            $table->foreign('origin_city_id')->references('id')->on('cities')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('sur_name');
            $table->dropColumn('origin_country_id');
            $table->dropColumn('origin_city_id');
        });
    }
}
