<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoanAccountFeePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loan_account_fee_payments', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number')->unique()->nullable();
            $table->string('loan_account_disbursement_transaction_id')->nullable();
            $table->unsignedInteger('loan_account_id');
            $table->unsignedInteger('fee_id');
            $table->unsignedDouble('amount');
            $table->unsignedDouble('payment_method_id')->nullable();
            $table->boolean('paid')->default(false);
            $table->dateTime('repayment_date')->nullable();
            $table->unsignedInteger('paid_by')->nullable();
            $table->boolean('reverted')->default(false);
            $table->unsignedInteger('office_id')->nullable();
            $table->unsignedInteger('reverted_by')->nullable();
            $table->date('reverted_at')->nullable();
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
        Schema::dropIfExists('loan_account_fee_payments');
    }
}
