<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOwWebportalUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ow_webportal_users', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigIncrements('id');
            $table->string('username',64)->unique()->index();
            $table->string('shadow',64);
            $table->string('comment', 256);
            $table->boolean('disable');
            $table->boolean('roaming');
            $table->unsignedInteger('multi');
            $table->string('phone',32);

            $table->unsignedInteger('login_count');
            $table->timestamp('last_online');
            $table->string('last_devmac', 20);
            $table->string('last_stamac', 20);

            $table->unsignedInteger('tx_rate');
            $table->unsignedInteger('rx_rate');
            $table->unsignedBigInteger('trx_limit');
            $table->unsignedInteger('time_limit');
            $table->timestamp('limit_start');

            $table->unsignedBigInteger('trx_used');
            $table->unsignedInteger('time_used');

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
        Schema::drop('ow_webportal_users');
    }
}
