<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDependentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dependents', function (Blueprint $table) {
            $table->id();
            $table->string('client_id');
            $table->string('application_number');
            $table->unsignedInteger('unit_of_plan');

            $table->string('member_firstname')->nullable();
            $table->string('member_middlename')->nullable();;
            $table->string('member_lastname')->nullable();;
            $table->date('member_birthday')->nullable();;

            
            $table->boolean('spouse_exists')->default(false);
            $table->string('spouse_firstname')->nullable();
            $table->string('spouse_middlename')->nullable();;
            $table->string('spouse_lastname')->nullable();;
            $table->date('spouse_birthday')->nullable();;


            $table->boolean('child_1_exists')->default(false);
            $table->string('child_1_firstname')->nullable();
            $table->string('child_1_middlename')->nullable();
            $table->string('child_1_lastname')->nullable();
            $table->date('child_1_birthday')->nullable();

            $table->boolean('child_2_exists')->default(false);
            $table->string('child_2_firstname')->nullable();
            $table->string('child_2_middlename')->nullable();
            $table->string('child_2_lastname')->nullable();
            $table->date('child_2_birthday')->nullable();

            $table->boolean('child_3_exists')->default(false);
            $table->string('child_3_firstname')->nullable();
            $table->string('child_3_middlename')->nullable();
            $table->string('child_3_lastname')->nullable();
            $table->date('child_3_birthday')->nullable();

            $table->boolean('father_exists')->default(false);
            $table->string('father_firstname')->nullable();
            $table->string('father_middlename')->nullable();
            $table->string('father_lastname')->nullable();
            $table->date('father_birthday')->nullable();

            $table->boolean('mother_exists')->default(false);
            $table->string('mother_firstname')->nullable();
            $table->string('mother_middlename')->nullable();
            $table->string('mother_lastname')->nullable();
            $table->date('mother_birthday')->nullable();
            
            $table->boolean('sibling_1_exists')->default(false);
            $table->string('sibling_1_firstname')->nullable();
            $table->string('sibling_1_middlename')->nullable();
            $table->string('sibling_1_lastname')->nullable();
            $table->date('sibling_1_birthday')->nullable();

            $table->boolean('sibling_2_exists')->default(false);
            $table->string('sibling_2_firstname')->nullable();
            $table->string('sibling_2_middlename')->nullable();
            $table->string('sibling_2_lastname')->nullable();
            $table->date('sibling_2_birthday')->nullable();

            $table->boolean('sibling_3_exists')->default(false);
            $table->string('sibling_3_firstname')->nullable();
            $table->string('sibling_3_middlename')->nullable();
            $table->string('sibling_3_lastname')->nullable();
            $table->date('sibling_3_birthday')->nullable();

            $table->string('common_illness')->nullable();
            $table->double('commonillness_rate')->nullable();
            
            $table->string('status')->default('Unused');
            $table->unsignedInteger('loan_account_id')->nullable();
            $table->dateTime('activated_at')->nullable();
            $table->date('expires_at')->nullable();
            $table->unsignedInteger('created_by');

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
        Schema::dropIfExists('dependents');
    }
}
