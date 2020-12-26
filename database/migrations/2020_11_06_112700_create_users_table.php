<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('user_name')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('referral_code')->nullable();
            $table->text('current_location')->nullable();
            $table->string('nation')->nullable();
            $table->enum('gender', ['m', 'f'])->nullable()->comment('m: Male, f: Female');
            $table->timestamp('birthday')->nullable();
            $table->bigInteger('school_id')->unsigned()->nullable();
            $table->foreign('school_id')->references('id')->on('schools')->onDelete('cascade');
            $table->bigInteger('country_id')->unsigned()->nullable();
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('cascade');
            $table->bigInteger('state_id')->unsigned();
            $table->foreign('state_id')->references('id')->on('states')->onDelete('cascade');
            $table->bigInteger('city_id')->unsigned()->nullable();
            $table->foreign('city_id')->references('id')->on('cities')->onDelete('cascade');
            $table->enum('current_status', ['0', '1', '2', '3'])->nullable()->default('0')->comment('0: None, 1: Working, 2: Studying, 3: Chilling');
            $table->string('company')->nullable();
            $table->string('job_position')->nullable();
            $table->string('university')->nullable();
            $table->string('field_of_study')->nullable();
            $table->enum('personal_flag', ['0', '1'])->nullable()->default('0')->comment('0: Nope, 1: Done');
            $table->enum('school_flag', ['0', '1'])->nullable()->default('0')->comment('0: Nope, 1: Done');
            $table->enum('other_flag', ['0', '1'])->nullable()->default('0')->comment('0: Nope, 1: Done');
            $table->decimal('longitude', 11, 8)->nullable();
            $table->decimal('latitude', 11, 8)->nullable();
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
