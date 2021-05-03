<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBulkDisbursementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bulk_disbursements', function (Blueprint $table) {
            $table->id();
            $table->string('bulk_disbursement_id');
            $table->unsignedBigInteger('loan_account_id');
            $table->unsignedBigInteger('office_id');
            $table->date('disbursement_date');
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
        Schema::dropIfExists('bulk_disbursements');
    }
}
