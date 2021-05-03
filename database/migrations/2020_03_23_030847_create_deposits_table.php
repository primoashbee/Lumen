<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDepositsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deposits', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('product_id')->unique();
            $table->mediumText('description')->nullable();
            $table->double('minimum_deposit_per_transaction');
            $table->date('valid_until')->nullable();
            $table->integer('account_per_client');
            $table->boolean('auto_create_on_new_client')->default(false);
            $table->unsignedDouble('interest_rate');
            $table->unsignedInteger('deposit_portfolio');
            $table->unsignedInteger('deposit_interest_expense');
            $table->boolean('is_active')->default(true);

            
        
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
        Schema::dropIfExists('deposits');
    }
}
