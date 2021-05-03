<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // $this->call(UserTableSeeder::class);
        generateStucture();
        createAdminAccount();
        createDeposits();
        generatePaymentMethods();
        generateDefaultPaymentMethods();
        generateFees();
        generateLoanProducts();

        // $this->call(ClientTableSeeder::class);
        // createLoanAccount();
        // generateMPLLoan();
        
        
    }
}
