<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDepositAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('deposit_accounts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('client_id');
            $table->integer('deposit_id');
            $table->unsignedDouble('balance')->default(0);
            $table->unsignedDouble('accrued_interest')->default(0);
            $table->string('status')->default('Active');
            // $table->dateTime('last_transaction_date')->nullable();
            $table->dateTime('closed_at')->nullable();
            $table->unsignedDouble('closed_by')->nullable();
            $table->timestamps();
        });
        // Schema::create('client_deposit', function (Blueprint $table) {
        //     $table->bigIncrements('id');
        //     $table->string('client_id');
        //     $table->integer('deposit_id');
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
        Schema::dropIfExists('deposit_accounts');
        // Schema::dropIfExists('client_deposit');
    }
}
