<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoanAccountTopupInstallmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loan_account_topup_installments', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('loan_account_installment_id');
            $table->unsignedInteger('loan_account_topup_id');
            $table->unsignedDouble('principal_topup');
            $table->unsignedDouble('interest_topup');
            $table->unsignedDouble('total_topup');
            $table->unsignedDouble('topup_by');
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
        Schema::dropIfExists('loan_account_topup_installments');
    }
}
