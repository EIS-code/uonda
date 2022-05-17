<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLatLongFieldsCountryCityStateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->string('latitude')->nullable()->after('state_id');
            $table->string('longitude')->nullable()->after('latitude');
        });
        Schema::table('states', function (Blueprint $table) {
            $table->string('latitude')->nullable()->after('country_id');
            $table->string('longitude')->nullable()->after('latitude');
        });
        Schema::table('countries', function (Blueprint $table) {
            $table->string('latitude')->nullable()->after('phone_code');
            $table->string('longitude')->nullable()->after('latitude');
            $table->string('phone_code')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cities', function (Blueprint $table) {
            $table->dropColumn('latitude');
            $table->dropColumn('longitude');
        });
        Schema::table('states', function (Blueprint $table) {
            $table->dropColumn('latitude');
            $table->dropColumn('longitude');
        });
        Schema::table('countries', function (Blueprint $table) {
            $table->dropColumn('latitude');
            $table->dropColumn('longitude');
        });
    }
}
