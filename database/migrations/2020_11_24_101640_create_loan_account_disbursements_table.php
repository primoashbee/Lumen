<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoanAccountDisbursementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loan_account_disbursements', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->unique();
            $table->unsignedInteger('loan_account_id');
            $table->unsignedDouble('disbursed_amount');
            $table->unsignedInteger('disbursed_by');
            $table->unsignedInteger('payment_method_id');
            
            $table->boolean('reverted')->default(false);
            
            $table->unsignedInteger('reverted_by')->nullable();
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
        Schema::dropIfExists('loan_account_disbursements');
    }
}
