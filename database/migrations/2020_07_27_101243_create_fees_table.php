<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fees', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('automated')->default(false);
            $table->string('calculation_type');
            $table->unsignedDouble('fixed_amount')->nullable();
            $table->boolean('has_unit_of_plan')->default(false);
            $table->string('percentage')->nullable();
            $table->boolean('finance_charge');
            $table->unsignedInteger('gl_account');
            $table->boolean('disabled')->default(false);
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
        Schema::dropIfExists('fees');
    }
}
