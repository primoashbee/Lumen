<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDepositTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deposit_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id');
            $table->string('deposit_account_id');
            $table->string('transaction_type');
            $table->unsignedDouble('amount');
            $table->unsignedDouble('balance');
            $table->string('payment_method');
            $table->date('repayment_date');
            $table->unsignedInteger('user_id');
            $table->longText('notes')->nullable();
            $table->string('identifiable_id');
            $table->string('identifiable_type');
            $table->string('receipt_number');
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
        Schema::dropIfExists('deposit_transactions');
    }
}
