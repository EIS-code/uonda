<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSocialFieldsInUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('oauth_uid')->nullable()->after('is_admin');
            $table->enum('oauth_provider', ['0', '1', '2', '3'])->default('0')->after('oauth_uid')->comment('0: None, 1: Google, 2: Facebook, 3: Apple');
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
            $table->dropColumn('oauth_uid');
            $table->dropColumn('oauth_provider');
        });
    }
}
