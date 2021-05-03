<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateParMovementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('par_movements', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('accounts');
            $table->unsignedDouble('par_amount');
            $table->unsignedInteger('office_id');
            $table->string('aging');
            $table->date('date');
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
        Schema::dropIfExists('par_movements');
    }
}
