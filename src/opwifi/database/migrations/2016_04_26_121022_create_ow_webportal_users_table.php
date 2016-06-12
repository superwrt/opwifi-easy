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
            $table->string('phone',32);
            $table->timestamp('lastonline');
            $table->string('lastdevmac', 20);
            $table->string('laststamac', 20);
            $table->unsignedInteger('login_count');
            $table->unsignedInteger('tx_rate');
            $table->unsignedInteger('rx_rate');
            $table->unsignedBigInteger('trx_used');
            $table->unsignedBigInteger('trx_limit');
            $table->unsignedInteger('time_limit');
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
