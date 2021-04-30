<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDepositInterestPostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('deposit_interest_posts', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_number')->unique();
            $table->unsignedInteger('deposit_account_id');
            $table->unsignedDouble('amount');
            $table->unsignedDouble('balance');
            $table->unsignedInteger('payment_method_id')->nullable();
            $table->date('repayment_date');
            $table->unsignedInteger('office_id');
            $table->unsignedInteger('paid_by');
            $table->boolean('reverted')->default(false);
            $table->unsignedInteger('reverted_by')->nullable();
            $table->boolean('revertion')->default(0);
            $table->string('notes')->nullable();
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
        Schema::dropIfExists('deposit_interest_posts');
    }
}
