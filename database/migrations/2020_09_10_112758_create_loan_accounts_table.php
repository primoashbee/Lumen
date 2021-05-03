<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLoanAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loan_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('client_id');
            $table->unsignedInteger('loan_id');
            $table->unsignedDouble('amount');
            $table->unsignedDouble('principal');
            $table->unsignedDouble('interest');
            $table->unsignedDouble('total_loan_amount');
            $table->unsignedDouble('interest_rate');

            $table->integer('number_of_months');
            $table->integer('number_of_installments');
            $table->unsignedDouble('total_deductions'); 
            $table->unsignedDouble('disbursed_amount'); //net disbursement
            
            $table->unsignedDouble('total_balance'); 

            $table->unsignedDouble('principal_balance'); 
            $table->unsignedDouble('interest_balance'); 
            



            $table->unsignedInteger('approved_by')->nullable();
            $table->boolean('approved')->default(false);
            $table->timestamp('approved_at')->nullable();
            
            $table->unsignedInteger('disbursed_by')->nullable();
            $table->timestamp('disbursed_at')->nullable();
            $table->boolean('disbursed')->default(false);

            $table->date('disbursement_date');
            $table->date('first_payment_date');
            $table->date('last_payment_date');

            $table->date('closed_at')->nullable();
            $table->unsignedInteger('closed_by')->nullable();
            
            
            $table->unsignedInteger('created_by')->nullable();

            $table->longText('notes')->nullable();
            $table->string('status')->default('Pending Approval');

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
        Schema::dropIfExists('loan_accounts');
    }
}
