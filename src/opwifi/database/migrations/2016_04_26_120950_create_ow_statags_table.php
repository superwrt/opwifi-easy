<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOwStatagsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ow_stagroups', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name',128)->unique()->index();
            $table->string('comment',128);
            $table->unsignedInteger('parent_id');
            $table->timestamps();
        });
        Schema::create('ow_statags', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name',128)->unique()->index();
            $table->string('comment',128);
            $table->unsignedInteger('group_id');
            $table->timestamps();
        });
        Schema::create('ow_statag_relationships', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigIncrements('id');
            $table->unsignedBigInteger('sta_id')->index();
            $table->foreign('sta_id')->references('id')->on('ow_stations')->onDelete('cascade');;
            $table->unsignedInteger('tag_id')->index();
            $table->foreign('tag_id')->references('id')->on('ow_statags')->onDelete('cascade');;
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('ow_statag_relationships');
        Schema::drop('ow_statags');
        Schema::drop('ow_stagroups');
    }
}
