<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoanAccountInstallmentRepaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loan_account_installment_repayments', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('loan_account_installment_id');
            $table->unsignedInteger('loan_account_repayment_id');
            $table->unsignedDouble('principal_paid');
            $table->unsignedDouble('interest_paid');
            $table->unsignedDouble('penalty_paid')->default(0);
            $table->unsignedDouble('total_paid');
            $table->unsignedDouble('paid_by');
            // $table->string('transaction_id');
            // $table->boolean('reverted')->default(false);
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
        Schema::dropIfExists('loan_account_installment_repayments');
    }
}
