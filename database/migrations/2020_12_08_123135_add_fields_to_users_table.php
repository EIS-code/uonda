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
            $table->boolean('is_enable')->default(1)->comment("1=Enable, 0=Disable")->after('remember_token');
            $table->boolean('is_accepted')->default(1)->comment("1=Accepted, 0=Rejected")->after('is_enable');
            $table->text('reason_for_rejection')->nullable()->after('is_accepted');
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
            $table->dropColumn('is_enable');
            $table->dropColumn('is_accepted');
            $table->dropColumn('reason_for_rejection');
        });
    }
}
