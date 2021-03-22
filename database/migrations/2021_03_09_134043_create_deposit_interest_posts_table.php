<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDepositInterestPostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deposit_interest_posts', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('deposit_account_id');
            $table->unsignedDouble('amount');
            $table->unsignedDouble('balance');
            $table->unsignedInteger('payment_method_id')->nullable();
            $table->string('notes')->nullable();
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
        Schema::dropIfExists('deposit_interest_posts');
    }
}
