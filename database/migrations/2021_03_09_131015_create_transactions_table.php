<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::create('transactions', function (Blueprint $table) {
        //     $table->id();
        //     $table->string('transaction_number');
        //     $table->string('type');
        //     $table->unsignedBigInteger('transactionable_id');
        //     $table->unsignedInteger('office_id');
        //     $table->date('transaction_date');
        //     $table->string('transactionable_type');
        //     $table->boolean('reverted')->nullable()->default(false);
        //     $table->boolean('reverted_by')->nullable();
        //     $table->dateTime('reverted_at')->nullable();
        //     $table->boolean('revertion')->nullable();
        //     $table->unsignedInteger('posted_by');
        //     $table->timestamps();
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Schema::dropIfExists('transactions');
    }
}
