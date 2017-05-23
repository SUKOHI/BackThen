<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRevisionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('revisions', function ($table) {
            $table->increments('id');
            $table->string('model');
            $table->integer('model_id')->unsigned();
            $table->string('column_name');
            $table->string('revision_type');
            $table->integer('revision_id')->unsigned();
            $table->string('unique_id');
            $table->text('old_value')->nullable();
            $table->text('new_value')->nullable();
            $table->integer('user_id')->nullable()->unsigned();
            $table->timestamps();
            $table->index(['model', 'model_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('revisions');
    }
}
