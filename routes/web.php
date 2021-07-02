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

Route::get('/log', function(){
    return view('pages.login-page');
});


Route::get('/', 'DashboardController@index');


Auth::routes();
Route::get("/gg",function(){
    return abort(403);
});
Route::group(['middleware' => ['auth']], function () {

        Route::post('/user/changepass/{user}', 'UserController@changepass')->name('changepass');

    Route::group(['middleware' => ['permission:extract_reports']], function () 
    {

        Route::get('/transactions', 'TransactionController@list');
        Route::get('/download/dst/{loan_account_id}','DownloadController@dst');
        Route::get('/download/dst/bulk/{bulk_transaction_id}','DownloadController@dstBulk');
        Route::post('/download/ccr','DownloadController@ccr');
        
    });


    Route::group(['middleware' => ['permission:view_reports']], function()
    {
        Route::get('/reports/v2/repayments','ReportController@rp');
        Route::get('/reports/{class}/{type}','ReportController@view')->name('reports.view');
        Route::post('/reports/{type}','ReportController@getReport');
        Route::get('/reports','ReportController@index')->name('reports.index');
    });

    
    Route::group(['middleware' => ['role:Super Admin']], function () 
    {
        // Users

        Route::get('/create/user', 'UserController@create')->name('create.user');
        Route::post('/create/user', 'UserController@storeUser')->name('create.user');
        Route::get('/users', 'UserController@users')->name('users.list');
        Route::get('/users/list', 'UserController@getUsers')->name('users.list');
        Route::post('/edit/user/{user}', 'UserController@update')->name('update.user');
        Route::get('/edit/user/{user}', 'UserController@edit')->name('update.user');

        // Roles and Permissions

        Route::get('/create/roles', 'RoleController@create');
        Route::post('/create/role', 'RoleController@store');
        Route::get('/edit/role/{role}', 'RoleController@edit');
        Route::post('/edit/role/{role}', 'RoleController@update');
        Route::get('/roles','RoleController@index');
        Route::get('/roles/list','RoleController@getRoles');

        Route::get('/permissions','PermissionController@index');
        Route::get('/permissions/list','PermissionController@getPermissions');
        Route::post('/create/permission','PermissionController@store');



        Route::get('/settings/create/fee', function(){
            return view('pages.create-fees');
        });
        Route::get('/settings/create/penalty', function(){
            return view('pages.create-penalty');
        });
        
        Route::get('/settings', function(){
            return view('pages.settings');
        })->name('administration');
        Route::get('/settings/create/office/{level}', 'OfficeController@createLevel')->name('create.office');
        Route::post('/create/office/', 'OfficeController@createOffice');

        Route::get('/office/{level}', 'OfficeController@viewOffice')->name('offices.view');
        Route::get('/office/list/{level}','OfficeController@getOfficeList');

        Route::get('/edit/office/{id}', 'OfficeController@editOffice');
        Route::post('/edit/office/{id}', 'OfficeController@updateOffice');

        Route::get('/payment/methods','PaymentMethodController@fetchPaymentMethods');

    });


    Route::get('/create/office/cluster', 'ClusterController@create');


    Route::get('/loan/products','LoanController@');
    Route::get('/fees','FeeController@getList');


    Route::get('/stepper','ClientController@step');
    Route::get('/pay','RepaymentController@repayLoan');
    Route::post('/loan/calculator', 'LoanAccountController@calculate')->name('loan.calculator');
    Route::post('/products','ProductController@index');


    Route::post('/client/create/dependent', 'DependentController@createDependents')->name('create.dependents.post');
    Route::get('/client/update/dependent', 'DependentController@updateDependentStatus')->name('create.dependents.activate');
    
    
    
    Route::post('/client/create/loan', 'LoanAccountController@createLoan')->name('client.loan.create.post');
    
    Route::get('/loan/approve/{loan_id}','LoanAccountController@approve')->name('loan.approve');
    Route::get('/loan/disburse/{loan_id}','LoanAccountController@disburse')->name('loan.disburse');
    
    
    Route::post('/loans/repay','RepaymentController@accountPayment');
    Route::post('/loans/preterm','RepaymentController@preTerminate');
    Route::post('/revert','RevertController@revert')->name('revert.action');
    Route::get('/dashboard','DashboardController@dashboard')->name('dashboard');
    Route::get('/dashboard/v1/{reload?}/{office_id}/{type}','DashboardController@type');

    
    Route::get('/create/client','ClientController@index')->name('precreate.client');
    Route::post('/create/client','ClientController@createV1')->name('create.client'); 
    Route::get('/clients','ClientController@list')->name('client.list');
    Route::get('/clients/list','ClientController@getList')->name('get.client.list');

    Route::middleware(['user_client_scope'])->group(function() {
        
        Route::get('/client/{client_id}','ClientController@view')->name('client.profile');
        Route::get('/client/{client_id}/create/loan', 'LoanAccountController@index')->name('client.loan.create');
        Route::get('/client/{client_id}/loans', 'LoanAccountController@clientLoanList')->name('client.loan.list');
        Route::get('/client/{client_id}/deposit/{deposit_account_id}', 'ClientController@depositAccount')->name('client.deposit'); 
        Route::get('/client/{client_id}/loans/{loan_id}','LoanAccountController@account')->name('loan.account');   
        Route::get('/client/{client_id}/create/dependents', 'ClientController@toCreateDependents')->name('client.create.dependents');
        Route::get('/dependents/{client_id}', 'ClientController@listDependents')->name('client.dependents.list');
        Route::get('/client/{client_id}/manage/dependents', 'ClientController@dependents')->name('client.manage.dependents');
        Route::get('/edit/client/{client}','ClientController@editClient');
        Route::post('/edit/client/{client}','ClientController@update');

    });

    
    

    

    Route::post('/deposit/{deposit_account_id}','DepositAccountController@deposit')->name('client.make.deposit'); //make deposit transaction individually
    Route::post('/withdraw/{deposit_account_id}','DepositAccountController@withdraw')->name('client.make.withdrawal'); //make deposit transaction individually
    

    
    Route::get('/bulk/deposit', 'DepositAccountController@showBulkView')->name('bulk.deposit.deposit');
    Route::get('/bulk/withdraw', 'DepositAccountController@showBulkView')->name('bulk.deposit.withdraw');
    Route::get('/bulk/post_interest', 'DepositAccountController@showBulkView')->name('bulk.deposit.post_interest');
    
    Route::get('/bulk/create/loans', 'LoanAccountController@bulkCreateForm')->name('bulk.create.loans');
    // Route::post('/loans/pending/list', 'LoanAccountController@pendingLoans');
    Route::post('/bulk/create/loans', 'LoanAccountController@bulkCreateLoan')->name('bulk.create.loans.post');
    

    Route::post('/bulk/predisbursement/loans/list','LoanAccountController@preDisbursementList');
    Route::get('/bulk/approve/loans','LoanAccountController@bulkApproveForm')->name('bulk.approve.loans');
    // Route::post('/bulk/approve/loans','LoanAccountController@bulkApprove')->name('bulk.approve.loans.post');
    
    // Route::post('/loans/approved/list','LoanAccountController@approvedLoans');
    Route::get('/bulk/disburse/loans','LoanAccountController@bulkDisburseForm')->name('bulk.disburse.loans');
    // Route::post('/bulk/disburse/loans','LoanAccountController@bulkDisburse')->name('bulk.disburse.loans.post');
    
    Route::post('/bulk/{type}/loans','LoanAccountController@bulkLoanTransact');
    Route::post('/bulk/deposit', 'DepositAccountController@bulkDeposit')->name('bulk.deposit.deposit.post');
    Route::post('/bulk/withdraw', 'DepositAccountController@bulkWithdraw')->name('bulk.deposit.withdraw.post');
    Route::post('/bulk/post_interest', 'DepositAccountController@bulkPostInterest')->name('bulk.deposit.interst_post.post');
    
    Route::get('/bulk/repayment','RepaymentController@showBulkForm')->name('bulk.repayment');
    Route::post('/bulk/repayments','RepaymentController@bulkRepaymentV2')->name('bulk.repayment.post');
    // Route::post('/bulk/repayments','RepaymentController@bulkRepayment')->name('bulk.repayment.post');
    Route::post('/loans/scheduled/list','RepaymentController@scheduledList');
    Route::post('/scheduled/list','RepaymentController@scheduledListV2');
    
    
    Route::get('/deposits','DepositAccountController@showList');
    Route::get('/product','ProductController@getItems');
    Route::post('/deposit/{deposit_account_id}','DepositAccountController@deposit')->name('client.make.deposit');
    Route::post('/deposit/account/post/interest','DepositAccountController@postInterest')->name('deposit.account.post.interest');


    Route::get('/accounts/{type}','AccountController@index')->name('accounts.list');

    // Route::post('/accounts/{type}','AccountController@filter')->name('accounts.all');

    // Route::post('/loans/list','LoanController@postInterestByUser')->name('deposit.account.post.interest');


    Route::get('/loan/products','LoanController@loanProducts')->name('loan.products');
    Route::get('/settings/loan','LoanController@index')->name('settings.loan-products');
    Route::get('/settings/api/get/loans','LoanController@loanProducts')->name('settings.loan-list');
    Route::get('/auth/structure', 'UserController@authStructure')->name('auth.structure');


    Route::post('/search','SearchController@search');

    Route::get('/user/{user}','UserController@get');
    Route::get('/settings/create/loan', function(){
        return view('pages.create-loan');
    });

    Route::get('/settings/loan/edit/{loan}','LoanController@updateLoan'); //render view
    Route::get('/settings/loan/product/edit/{id}','LoanController@loanProduct'); //get product via id
    Route::post('/settings/loan/edit/{id}','LoanController@updateLoanProduct'); //post view
    Route::get('/settings/loan/view/{loan}','LoanController@viewLoan');
    Route::post('/settings/create/loan','LoanController@create');
    
    
    


    Route::get('/logout','Auth\LoginController@logout')->name('logout');
    Route::get('/scopes', function(){
        return auth()->user()->scopesBranch();
    });
    Route::get('/usr/branches','UserController@branches');

});
 

