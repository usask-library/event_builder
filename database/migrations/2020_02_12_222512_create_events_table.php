<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->enum('class', ['acquisition', 'production', 'manipulation', 'observation'])->index();
            $table->string('type', 20)->nullable()->index();
            $table->string('year', 25)->nullable()->index();
            $table->string('document', 255)->nullable();
            $table->timestamps();
        });

        // Pivot table for the 1 to many relationship between Event and Item
        Schema::create('event_item', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('event_id')->index();
            $table->string('item_identifier', 50);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->foreign('event_id')->references('id')->on('events');
            $table->foreign('item_identifier')->references('identifier')->on('item_identifier');

            $table->unique(['event_id', 'item_identifier']);
        });

        // Pivot table for the 1 to many relationship between Event and Person
        Schema::create('event_person', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('event_id')->index();
            $table->unsignedBigInteger('person_id')->index();
            $table->string('role', 50)->index();
            $table->string('description', 255)->nullable();
            $table->timestamps();

            $table->foreign('event_id')->references('id')->on('events');
            $table->foreign('person_id')->references('id')->on('people');
        });

        // Pivot table for the 1 to many relationship between Event and Place
        Schema::create('event_place', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('event_id')->index();
            $table->unsignedBigInteger('place_id')->index();
            $table->string('role', 50)->default('place')->index();
            $table->string('description', 255)->nullable();
            $table->timestamps();

            $table->foreign('event_id')->references('id')->on('events');
            $table->foreign('place_id')->references('id')->on('places');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('event_place');
        Schema::dropIfExists('event_person');
        Schema::dropIfExists('event_item');
        Schema::dropIfExists('events');
    }
}
