<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOwDevicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ow_devices', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigIncrements('id');
            $table->string('mac',20)->unique()->index();//Add space for same mac address device!
            $table->string('uid',40);
            $table->boolean('shared');//Devices have same mac address!
            $table->string('name',128);

            $table->timestamps();
        });

        Schema::create('ow_dev_configs', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name', 64);
            $table->string('comment', 256);
            $table->text('pdata');
            $table->text('config');
            $table->string('md5',16);

            $table->timestamps();
        });
        Schema::create('ow_dev_firmwares', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name', 64);
            $table->string('version', 64);
            $table->string('filename', 64);
            $table->string('org_filename', 64);
            $table->string('url', 256);
            $table->char('sha1', 40);

            $table->timestamps();
        });

        Schema::create('ow_devicemeta', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigIncrements('id');
            $table->unsignedBigInteger('dev_id')->unique();
            $table->foreign('dev_id')->references('id')->on('ow_devices')->onDelete('cascade');

            $table->boolean('online');
            $table->timestamp('lastshow');
            $table->string('lastip',16);

            $table->string('address',256);
            $table->string('geo_longitude',16);
            $table->string('geo_latitude',16);
            $table->string('fwver',32);
            $table->string('fullver',64);
            $table->string('sbiinfo',64);
            $table->char('sbisha1',40);
            $table->string('sbiloc',16);
            $table->string('cpuinfo',64);
            $table->unsignedInteger('flashfwsize');
            $table->string('ramsize',16);
            $table->string('ramfree',16);

            $table->text('op_config');//Device spesific config.
            $table->string('op_configed_sha1',40);//If change op_config, clear it!
            $table->timestamp('op_configed_last');//To compare with config in ow_dev_configs.
            $table->unsignedInteger('op_config_id')->nullable();
            $table->foreign('op_config_id')->references('id')->on('ow_dev_configs')->onDelete('set null');

            $table->boolean('op_reboot');
            $table->unsignedInteger('op_upgrade_id')->nullable();
            $table->foreign('op_upgrade_id')->references('id')->on('ow_dev_firmwares')->onDelete('set null');

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
        Schema::drop('ow_devicemeta');
        Schema::drop('ow_dev_firmwares');
        Schema::drop('ow_dev_configs');
        Schema::drop('ow_devices');
    }
}
