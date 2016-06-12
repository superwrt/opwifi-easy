<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOwWebportalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ow_webportal_configs', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name',128);

            $table->string('mode', 32);
            $table->string('access_token', 128);
            $table->text('comment');
            $table->string('redirect',256);
            $table->string('success_redirect',256);
            $table->unsignedInteger('force_timeout');
            $table->unsignedInteger('idle_timeout');
            $table->text('white_ip');
            $table->text('white_domain');
            $table->unsignedInteger('period');
            $table->unsignedInteger('max_users');
            
            $table->timestamps();
        });
        Schema::create('ow_webportal_tokens', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigIncrements('id');
            $table->string('token', 64)->unique()->index();
            $table->string('mac', 20);
            $table->string('usermac', 20);
            $table->string('redirect',1024);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('ow_webportal_users')->onDelete('set null');

            $table->timestamps();
        });
        Schema::create('ow_webportal_station_status', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigIncrements('id');
            $table->string('mac', 20)->unique()->index();
            $table->string('ondev', 20)->index();
            $table->unsignedBigInteger('authdev_id')->nullable();
            $table->foreign('authdev_id')->references('id')->on('ow_devices')->onDelete('set null');

            $table->boolean('online');
            $table->boolean('auth');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('ow_webportal_users')->onDelete('set null');

            $table->string('ssid', 32);
            $table->string('bssid', 20);
            $table->string('gatewayip', 64);
            $table->timestamp('lastdeadline');
            $table->timestamp('lastonline');
            $table->timestamp('lastoffline');

            $table->unsignedInteger('online_time');
            $table->unsignedInteger('online_total');
            $table->unsignedInteger('tx_rate');
            $table->unsignedInteger('rx_rate');
            $table->unsignedBigInteger('trx_used');
            $table->unsignedBigInteger('trx_total');
            $table->unsignedBigInteger('lasttrx_total');

            $table->timestamps();
        });
        Schema::create('ow_webportal_devices', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->unsignedBigInteger('dev_id')->unique();
            $table->unsignedInteger('config_id')->nullable();
            $table->foreign('dev_id')->references('id')->on('ow_devices')->onDelete('cascade');
            $table->foreign('config_id')->references('id')->on('ow_webportal_configs')->onDelete('set null');
            $table->boolean('online');
            $table->timestamp('lastshow');
            $table->unsignedInteger('users');

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
        Schema::drop('ow_webportal_devices');
        Schema::drop('ow_webportal_station_status');
        Schema::drop('ow_webportal_tokens');
        Schema::drop('ow_webportal_configs');
    }
}
