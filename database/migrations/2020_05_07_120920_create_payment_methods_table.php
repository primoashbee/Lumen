<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentMethodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->boolean('is_disabled')->default(false);
            $table->boolean('for_disbursement');
            $table->boolean('for_repayment');
            $table->boolean('for_deposit');
            $table->boolean('for_withdrawal');
            $table->boolean('for_recovery');
            $table->unsignedInteger('gl_account_code');
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
        Schema::dropIfExists('payment_methods');
    }
}
