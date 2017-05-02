<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
            $table->increments('auto_id');
            $table->string('user_id',13)->unique();
            $table->string('name',5);
            $table->string('password', 32);
            $table->string('email',30)->unique();
            $table->rememberToken();
            $table->string('status',6);//标记注册用户的身份
            $table->string('last_login_ip', 15)->nullable();
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
        Schema::drop('users');
    }
}
