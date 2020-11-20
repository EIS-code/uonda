<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserReportQuestionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_report_questions', function (Blueprint $table) {
            $table->id();
            $table->text('question');
            $table->text('options')->nullable()->comment('Comma separated dropdown/radio/checkbox options.');
            $table->enum('question_type', ['0', '1', '2', '3', '4', '5'])->default('0')->comment('0: Boolean, 1: Radio, 2: Checkbox, 3: Textbox, 4: Textarea, 5: Multiselect');
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
        Schema::dropIfExists('user_report_questions');
    }
}
