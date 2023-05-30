<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 100);
        });

        Schema::create('person_role', function (Blueprint $table) {
            $table->unsignedBigInteger('person_id')->index();
            $table->unsignedBigInteger('role_id')->index();

            $table->foreign('person_id')->references('id')->on('people');
            $table->foreign('role_id')->references('id')->on('roles');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('person_role');
        Schema::dropIfExists('roles');
    }
}
