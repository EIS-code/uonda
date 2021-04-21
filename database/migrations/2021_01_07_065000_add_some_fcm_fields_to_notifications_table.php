<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSomeFcmFieldsToNotificationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->string('title')->nullable()->after('id');
            $table->text('payload')->nullable()->after('message');
            $table->text('device_token')->after('payload');
            $table->enum('is_success', ['0', '1'])->default('1')->comment('0: Nope, 1: Yes')->after('device_token');
            $table->string('apns_id')->nullable()->after('is_success');
            $table->text('error_infos')->nullable()->after('apns_id');
            $table->bigInteger('user_id')->unsigned()->after('error_infos');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->bigInteger('created_by')->unsigned()->after('user_id');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropColumn('title');
            $table->dropColumn('payload');
            $table->dropColumn('device_token');
            $table->dropColumn('is_success');
            $table->dropColumn('apns_id');
            $table->dropColumn('error_infos');
            $table->dropColumn('user_id');
            $table->dropColumn('created_by');
        });
    }
}
