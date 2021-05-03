<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCheckVouchersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('check_vouchers', function (Blueprint $table) {
            $table->id();
            // $table->string('office_id');
            // $table->string('payee');
            $table->string('check_voucher_number');
            // $table->string('check_number');
            $table->date('transaction_date');
            // $table->unsignedDouble('amount_paid');


            
            $table->unsignedBigInteger('check_voucherable_id');
            $table->string('check_voucherable_type');
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
        Schema::dropIfExists('check_vouchers');
    }
}
