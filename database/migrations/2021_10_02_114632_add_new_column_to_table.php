<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNewColumnToTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::table('holidays', function (Blueprint $table) {
        //     if (!Schema::hasColumn('holidays', 'product_code')){
        //         $table->boolean('implemented')->after('office_id');
        //       };
        // });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        
            // Schema::table('holidays', function (Blueprint $table) {
            //     $table->dropColumn(['implemented']);
            // });
        
    }
}
