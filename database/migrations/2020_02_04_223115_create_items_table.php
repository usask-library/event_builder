<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;


class CreateItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /**
         *  id   -  Autoincrement ID. In practice, items will never be created by the application.
         *          These records will be imported from another database and the ID values will always be set there
         *  name - The name imported from the external database
         */
        //Schema::create('item_detail', function (Blueprint $table) {
        Schema::create('items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 255)->nullable();
            $table->timestamps();
        });

        /**
         *  id          - Autoincrement ID. Managed by this application
         *  alias_id    - object_matching_id value from the external database, if a matching record exists
         *  item_id     - Foreign key into the items table, if a record there exists for this alias/identifier
         *  identifier  - Alias/identifier. Can be inserted by the application, or sync'd from the external database
         */
        Schema::create('item_identifier', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('alias_id')->nullable()->index();
            $table->unsignedBigInteger('item_id')->nullable()->index();
            $table->string('identifier', 50)->unique();
            $table->timestamps();

            $table->foreign('item_id')->nullable()->references('id')->on('items');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //Schema::dropIfExists('item_identifier');
        Schema::dropIfExists('item_identifier');
        Schema::dropIfExists('items');
    }
}
