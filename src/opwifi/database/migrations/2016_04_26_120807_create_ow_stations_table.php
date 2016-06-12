<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOwStationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ow_stations', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigIncrements('id');
            $table->string('mac',20)->unique()->index();//Add space for same mac address device!
            $table->boolean('shared');//Devices have same mac address!
            $table->string('name',128);

            $table->timestamps();
        });
        Schema::create('ow_stationmeta', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigIncrements('id');
            $table->unsignedBigInteger('sta_id');
            $table->foreign('sta_id')->references('id')->on('ow_stations')->onDelete('cascade');;

            $table->timestamp('lastonline');
            $table->unsignedBigInteger('lastondev_id');
            $table->string('comment', 256);

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
        Schema::drop('ow_stationmeta');
        Schema::drop('ow_stations');
    }
}
