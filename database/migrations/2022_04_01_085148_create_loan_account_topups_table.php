<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoanAccountTopupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loan_account_topups', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number')->unique();
            $table->unsignedInteger('loan_account_id');
            $table->unsignedDouble('interest_topup');
            $table->unsignedDouble('principal_topup');
            $table->unsignedDouble('total_topup');
            $table->unsignedInteger('disbursed_by');
            $table->unsignedInteger('payment_method_id');
            $table->unsignedInteger('office_id');

            $table->boolean('reverted')->default(false);
            $table->boolean('revertion')->default(false);
            $table->unsignedInteger('reverted_by')->nullable();
            $table->date('topup_date');
            $table->mediumText('notes')->nullable();
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
        Schema::dropIfExists('loan_account_topups');
    }
}
