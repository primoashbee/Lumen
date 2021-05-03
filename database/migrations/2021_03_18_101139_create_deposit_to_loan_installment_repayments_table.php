<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDepositToLoanInstallmentRepaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deposit_to_loan_installment_repayments', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('loan_account_installment_id');
            $table->unsignedInteger('deposit_to_loan_repayment_id');
            $table->unsignedDouble('principal_paid');
            $table->unsignedDouble('interest_paid');
            $table->unsignedDouble('total_paid');
            $table->unsignedDouble('paid_by');
            $table->unsignedDouble('deposit_account_id');
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
        Schema::dropIfExists('deposit_to_loan_installment_repayments');
    }
}
