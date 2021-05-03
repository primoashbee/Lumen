<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoanAccountInstallmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loan_account_installments', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('loan_account_id');
            $table->integer('installment');
            $table->date('date');
            $table->unsignedDouble('original_principal');
            $table->unsignedDouble('original_interest');

            $table->unsignedDouble('principal');
            $table->unsignedDouble('interest');
            
            $table->unsignedDouble('principal_due');
            $table->unsignedDouble('interest_due');
            $table->unsignedDouble('amount_due');
            $table->unsignedDouble('amortization');
            
            $table->unsignedDouble('principal_balance');
            $table->unsignedDouble('interest_balance');

            $table->unsignedDouble('interest_days_incurred')->default(0);

            $table->boolean('paid')->default(false);
            
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
        Schema::dropIfExists('loan_account_installments');
    }
}
