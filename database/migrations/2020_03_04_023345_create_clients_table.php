<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('client_id')->unique();
            $table->string('firstname');
            $table->string('middlename')->nullable();
            $table->string('lastname');
            $table->string('suffix')->nullable();
            $table->string('nickname')->nullable();
            $table->string('gender');
            $table->string('profile_picture_path')->nullable();
            $table->string('signature_path')->nullable();
            $table->date('birthday');
            $table->string('birthplace');
            $table->string('civil_status');
            $table->string('education');
            $table->string('fb_account')->nullable();
            $table->string('contact_number');            

            //Address information
            $table->string('street_address');
            $table->string('barangay_address');
            $table->string('city_address');
            $table->string('province_address');
            $table->string('zipcode');

            $table->string('spouse_name')->nullable();
            $table->string('spouse_contact_number')->nullable();
            $table->date('spouse_birthday')->nullable();

            $table->integer('number_of_dependents');
            $table->integer('household_size');
            $table->integer('years_of_stay_on_house');
            $table->string('house_type');
        
            $table->string('tin')->nullable();
            $table->string('umid')->nullable();
            $table->string('sss')->nullable();
            $table->string('mother_maiden_name');

            $table->string('notes')->nullable();
            $table->unsignedInteger('office_id');
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
        Schema::dropIfExists('clients');
    }
}
