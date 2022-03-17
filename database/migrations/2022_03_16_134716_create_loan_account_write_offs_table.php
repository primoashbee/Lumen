<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoanAccountWriteOffsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('loan_account_write_offs', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number')->unique();
            $table->unsignedInteger('loan_account_id');
            
            $table->unsignedDouble('interest_written_off');
            $table->unsignedDouble('principal_written_off');
            $table->unsignedDouble('penalty_written_off')->default(0);
            
            $table->unsignedDouble('total_write_off');
            $table->unsignedInteger('writtenoff_by');
            $table->unsignedInteger('office_id');

            $table->boolean('reverted')->default(false);
            $table->boolean('revertion')->default(false);
            $table->unsignedInteger('reverted_by')->nullable();

            

            
            $table->date('written_off_date');
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
        Schema::dropIfExists('loan_account_write_offs');
    }
}
