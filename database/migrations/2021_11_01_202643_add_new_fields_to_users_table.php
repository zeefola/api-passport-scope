<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewFieldsToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->after('email');
            $table->string('phone_number')->nullable()->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('confirm_code')->nullable();
            $table->boolean('active')->default(false);
            $table->timestamp('last_login')->nullable();
            $table->timestamp('activation_created')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            //
        });
    }
}