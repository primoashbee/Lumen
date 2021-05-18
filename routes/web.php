<?php
// ini_set('xdebug.max_nesting_level', 9999);

use App\User;
use App\Client;
use App\Office;
use App\Deposit;
use App\Dashboard;
use App\LoanAccount;
use App\Transaction;
use App\BulkDisbursement;
use App\Events\TestEvent;
use App\Events\LoanPayment;
use App\Imports\TestImport;
use App\LoanAccountDisbursement;
use App\Events\BulkLoanDisbursed;
use App\Events\PresenceTestEvent;
use App\Events\LoanAccountPayment;
use Spatie\Permission\Models\Role;
use App\Exports\DisbursementExport;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Route;
use App\Events\LoanAccountPaymentEvent;
use Spatie\Permission\Models\Permission;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Request;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/




// Route::post('/download/dst/{id}','DownloadController@dst');


Route::get('/z', function(){
    return 2000;
});

Route::get('/', 'DashboardController@index');


Auth::routes();
Route::group(['middleware' => ['auth']], function () {
    Route::get('/loan/products','LoanController@');
    Route::get('/fees','FeeController@getList');    
    Route::get('/transactions', 'TransactionController@list');
    Route::prefix('/download')->group(function(){
        Route::post('/ccr','DownloadController@ccr');
        Route::get('/dst/{loan_account_id}','DownloadController@dst');
        Route::get('/dst/bulk/{bulk_transaction_id}','DownloadController@dstBulk');
    });
    
    Route::get('/stepper','ClientController@step');
    Route::get('/pay','RepaymentController@repayLoan');
    Route::post('/loan/calculator', 'LoanAccountController@calculate')->name('loan.calculator');
    Route::post('/products','ProductController@index');

    Route::prefix('/client')->group(function(){
        Route::get('/{client_id}/create/dependents', 'ClientController@toCreateDependents')->name('client.create.dependents');
        Route::post('/create/dependent', 'DependentController@createDependents')->name('create.dependents.post');
        Route::get('/update/dependent', 'DependentController@updateDependentStatus')->name('create.dependents.activate');
        Route::get('/{client_id}/manage/dependents', 'ClientController@dependents')->name('client.manage.dependents');
        Route::get('/{client_id}/create/loan', 'LoanAccountController@index')->name('client.loan.create');
        Route::post('/create/loan', 'LoanAccountController@createLoan')->name('client.loan.create.post');
        Route::get('/{client_id}/loans', 'LoanAccountController@clientLoanList')->name('client.loan.list');
        Route::get('/{client_id}/loans/{loan_id}','LoanAccountController@account')->name('loan.account');
        Route::get('/list','ClientController@getList')->name('get.client.list');    
        Route::get('/{client_id}','ClientController@view')->name('client.profile');
    });

    Route::prefix('/wApi')->group(function(){
        Route::prefix('/list')->group(function(){
            Route::post('/accounts','AccountController@getList');
        });
    });
    Route::get('/clients','ClientController@list')->name('client.list');
    
    Route::get('/dependents/{client_id}', 'ClientController@listDependents')->name('client.dependents.list');
    Route::get('/loan/approve/{loan_id}','LoanAccountController@approve')->name('loan.approve');
    Route::get('/loan/disburse/{loan_id}','LoanAccountController@disburse')->name('loan.disburse');
    
    
    Route::post('/loans/repay','RepaymentController@accountPayment');
    Route::post('/loans/preterm','RepaymentController@preTerminate');
    Route::post('/revert','RevertController@revert')->name('revert.action');
    Route::get('/dashboard','DashboardController@dashboard')->name('dashboard');
    Route::get('/dashboard/v1/{reload?}/{office_id}/{type}','DashboardController@type');


    //Reports



    Route::group(['middleware' => []], function () { 
        Route::get('/create/client','ClientController@index')->name('precreate.client');
        Route::post('/create/client','ClientController@createV1')->name('create.client'); 
    });
    Route::get('/logout','Auth\LoginController@logout')->name('logout');
    Route::get('/scopes', function(){
        return auth()->user()->scopesBranch();
    });
    Route::get('/usr/branches','UserController@branches');
    Route::get('/edit/client/{client}','ClientController@editClient');
    Route::post('/edit/client/{client}','ClientController@update');
    
    Route::post('/create/office/', 'OfficeController@createOffice');

    Route::get('/office/{level}', 'OfficeController@viewOffice')->name('offices.view');
    Route::get('/office/list/{level}','OfficeController@getOfficeList');

    Route::get('/edit/office/{id}', 'OfficeController@editOffice');
    Route::post('/edit/office/{id}', 'OfficeController@updateOffice');

    Route::get('/client/{client_id}/deposit/{deposit_account_id}', 'ClientController@depositAccount')->name('client.deposit'); 

    Route::post('/deposit/{deposit_account_id}','DepositAccountController@deposit')->name('client.make.deposit'); //make deposit transaction individually
    Route::post('/withdraw/{deposit_account_id}','DepositAccountController@withdraw')->name('client.make.withdrawal'); //make deposit transaction individually
    Route::get('/payment/methods','PaymentMethodController@fetchPaymentMethods');


    Route::prefix('/bulk')->group(function () {
        Route::get('/deposit', 'DepositAccountController@showBulkView')->name('bulk.deposit.deposit');
        Route::get('/withdraw', 'DepositAccountController@showBulkView')->name('bulk.deposit.withdraw');
        Route::get('/post_interest', 'DepositAccountController@showBulkView')->name('bulk.deposit.post_interest');
        Route::get('/create/loans', 'LoanAccountController@bulkCreateForm')->name('bulk.create.loans');
        // Route::post('/loans/pending/list', 'LoanAccountController@pendingLoans');
        Route::post('/create/loans', 'LoanAccountController@bulkCreateLoan')->name('bulk.create.loans.post');
        Route::post('/predisbursement/loans/list','LoanAccountController@preDisbursementList');
        Route::get('/approve/loans','LoanAccountController@bulkApproveForm')->name('bulk.approve.loans');
        Route::get('/disburse/loans','LoanAccountController@bulkDisburseForm')->name('bulk.disburse.loans');
        Route::post('/{type}/loans','LoanAccountController@bulkLoanTransact');
        Route::post('/deposit', 'DepositAccountController@bulkDeposit')->name('bulk.deposit.deposit.post');
        Route::post('/withdraw', 'DepositAccountController@bulkWithdraw')->name('bulk.deposit.withdraw.post');
        Route::post('/post_interest', 'DepositAccountController@bulkPostInterest')->name('bulk.deposit.interst_post.post');
        
        Route::get('/repayment','RepaymentController@showBulkForm')->name('bulk.repayment');
        Route::post('/repayments','RepaymentController@bulkRepaymentV2')->name('bulk.repayment.post');
    });

    Route::post('/loans/scheduled/list','RepaymentController@scheduledList');
    Route::post('/scheduled/list','RepaymentController@scheduledListV2');
    
    
    Route::get('/deposits','DepositAccountController@showList');
    Route::get('/product','ProductController@getItems');
    Route::post('/deposit/{deposit_account_id}','DepositAccountController@deposit')->name('client.make.deposit');
    Route::post('/deposit/account/post/interest','DepositAccountController@postInterest')->name('deposit.account.post.interest');


    Route::get('/accounts/{type}','AccountController@index')->name('accounts.list');

    Route::get('/loan/products','LoanController@loanProducts')->name('loan.products');
    Route::get('/auth/structure', 'UserController@authStructure')->name('auth.structure');
    Route::post('/search','SearchController@search');
    Route::get('/user/{user}','UserController@get');


    Route::prefix('/settings')->group(function () {
        Route::get('/create/role', function(){
            return view('pages.create-role');
        });
        Route::get('/create/user', function(){
            return view('pages.create-user');
        })->name('create.user');
    
        Route::get('/create/fee', function(){
            return view('pages.create-fees');
        });
    
        Route::get('/create/penalty', function(){
            return view('pages.create-penalty');
        });
        Route::get('/loan','LoanController@index')->name('settings.loan-products');
        Route::get('/api/get/loans','LoanController@loanProducts')->name('settings.loan-list');
    
        Route::get('/create/office/{level}', 'OfficeController@createLevel')->name('create.office');
        Route::get('/', function(){
            return view('pages.settings');
        })->name('administration');

        Route::get('/create/loan', function(){
            return view('pages.create-loan');
        });
    
        Route::get('/loan/edit/{loan}','LoanController@updateLoan'); //render view
        Route::get('/loan/product/edit/{id}','LoanController@loanProduct'); //get product via id
    
        Route::post('/loan/edit/{id}','LoanController@updateLoanProduct'); //post view
        
        Route::get('/loan/view/{loan}','LoanController@viewLoan');
        
        Route::post('/create/loan','LoanController@create');
    });

    Route::prefix('reports')->group(function () {
        Route::get('/','ReportController@index')->name('reports.index');
    
        Route::get('/v2/repayments','ReportController@rp');
        Route::get('/{class}/{type}','ReportController@view')->name('reports.view');
        Route::post('/{type}','ReportController@getReport');
    });




});
 

