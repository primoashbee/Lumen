<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHouseholdIncomesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('household_incomes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('client_id')->unique();
            $table->boolean('is_self_employed');
            $table->string('service_type')->nullable();
            $table->string('service_type_monthly_gross_income')->nullable();
            $table->boolean('is_employed');
            $table->string('employed_position')->nullable();
            $table->string('employed_company_name')->nullable();
            $table->unsignedDouble('employed_monthly_gross_income')->nullable();

            //spouse
            $table->boolean('spouse_is_self_employed');
            $table->string('spouse_service_type')->nullable();
            $table->string('spouse_service_type_monthly_gross_income')->nullable();
            $table->boolean('spouse_is_employed');
            $table->string('spouse_employed_position')->nullable();
            $table->string('spouse_employed_company_name')->nullable();
            $table->unsignedDouble('spouse_employed_monthly_gross_income')->nullable();

            $table->boolean('has_remittance')->default(false);
            $table->unsignedDouble('remittance_amount')->nullable();

            $table->boolean('has_pension')->default(false);
            $table->unsignedDouble('pension_amount')->nullable();

            $table->unsignedDouble('total_household_income');

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
        Schema::dropIfExists('household_incomes');
    }
}
