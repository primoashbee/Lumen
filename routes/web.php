<?php
// ini_set('xdebug.max_nesting_level', 9999);
use App\User;
use App\Client;
use App\Office;
use Carbon\Carbon;
use App\ParMovement;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\ClientRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Intervention\Image\Facades\Image;
use Illuminate\Validation\ValidationException;

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


Route::get('/', function(){
    return view('auth.login');
})->middleware('guest');

Auth::routes();
Route::group(['middleware' => ['auth']], function () {
    Route::get('/loan/products','LoanController@');
    Route::get('/fees','FeeController@getList');    
    Route::get('/transactions', 'TransactionController@list');
    Route::post('/user/changepass/{user}', 'UserController@changepass')->name('changepass');

    Route::post('/search','SearchController@search');
    Route::get('/generate/client/{client_id}', 'DownloadController@generateId');
    Route::get('/api/get/loans','LoanController@loanProducts')->name('settings.loan-list');
    Route::get('/logout','Auth\LoginController@logout')->name('logout');

    Route::prefix('/download')->group(function(){
        Route::post('/ccr','DownloadController@ccr');
        Route::get('/template/data-import','DownloadController@templateDataImport')->name('download.data-import');
        Route::get('/dst/{loan_account_id}','DownloadController@dst');
        Route::get('/dst/bulk/{bulk_transaction_id}','DownloadController@dstBulk');
    });
    Route::group(['middleware' => ['permission:extract_reports']], function () 
    {

        Route::get('/transactions', 'TransactionController@list');
        Route::get('/download/dst/{loan_account_id}','DownloadController@dst');
        Route::get('/download/soa/{loan_account_id}','DownloadController@soa');
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

    
    Route::prefix('settings')->middleware(['role:Super Admin'])->group(function () 
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



        Route::get('/create/fee', function(){
            return view('pages.create-fees');
        });
        Route::get('/settings/create/penalty', function(){
            return view('pages.create-penalty');
        });

        Route::get('/holidays', 'HolidayController@index');
        Route::get('/holiday/edit/{holiday}', 'HolidayController@edit');
        Route::get('/holidays/list', 'HolidayController@getHolidayList');
        Route::post('/post/holiday', 'HolidayController@createHoliday');
        Route::put('/holiday/edit/{holiday}', 'HolidayController@update');
        Route::delete('/delete/holiday/{holiday}', 'HolidayController@delete');

        Route::get('/', function(){
            return view('pages.settings');
        })->name('administration');
        Route::get('/create/office/{level}', 'OfficeController@createLevel')->name('create.office');
        Route::post('/create/office/', 'OfficeController@createOffice');

        Route::get('/office/{level}', 'OfficeController@viewOffice')->name('offices.view');
        Route::get('/office/list/{level}','OfficeController@getOfficeList');

        Route::get('/edit/office/{id}', 'OfficeController@editOffice');
        Route::post('/edit/office/{id}', 'OfficeController@updateOffice');

        Route::get('/payment/methods','PaymentMethodController@fetchPaymentMethods');

        Route::get('/import', 'MigrationController@index')->name('settings.import');
        Route::post('/import', 'MigrationController@upload')->name('settings.import.post');
        Route::get('/import/{migration}', 'MigrationController@logs')->name('settings.import.logs');
        Route::get('/create/fee', function(){
            return view('pages.create-fees');
        });
    
        Route::get('/create/penalty', function(){
            return view('pages.create-penalty');
        });
        Route::get('/loan','LoanController@index')->name('settings.loan-products');
        Route::get('/api/get/loans','LoanController@loanProducts')->name('settings.loan-list');
    
        Route::get('/create/office/{level}', 'OfficeController@createLevel')->name('create.office');

        Route::get('/create/loan', function(){
            return view('pages.create-loan');
        });
    
        Route::get('/loan/edit/{loan}','LoanController@updateLoan'); //render view
        Route::get('/loan/product/edit/{id}','LoanController@loanProduct'); //get product via id
    
        Route::post('/loan/edit/{id}','LoanController@updateLoanProduct'); //post view
        
        Route::get('/loan/view/{loan}','LoanController@viewLoan');
        
        Route::post('/create/loan','LoanController@create');

        // Deposit

        Route::get('/create/deposit', 'DepositController@create');
        Route::get('/deposit/edit/{deposit}', 'DepositController@edit');
        Route::put('/deposit/edit/{deposit}', 'DepositController@update');
        Route::get('/deposit', 'DepositController@index');
        Route::get('/deposit/list', 'DepositController@getDepositProducts');

    });


    Route::get('/create/office/cluster', 'ClusterController@create');
    Route::get('/fees','FeeController@getList');


    Route::get('/stepper','ClientController@step');
    Route::get('/pay','RepaymentController@repayLoan');
    Route::post('/loan/calculator', 'LoanAccountController@calculate')->name('loan.calculator');
    

    
   


    Route::prefix('/client')->middleware(['user_client_scope'])->group(function(){
        Route::get('/{client_id}/create/dependents', 'ClientController@toCreateDependents')->name('client.create.dependents');
        Route::post('/create/dependent', 'DependentController@createDependents')->name('create.dependents.post');
        Route::post('/create/dependent', 'DependentController@createDependents')->name('create.dependents.post');
        Route::get('/update/dependent', 'DependentController@updateDependentStatus')->name('create.dependents.activate');
        Route::get('/{client_id}/manage/dependents', 'ClientController@dependents')->name('client.manage.dependents');
        Route::get('/{client_id}/create/loan', 'LoanAccountController@index')->name('client.loan.create');
        Route::post('/create/loan', 'LoanAccountController@createLoan')->name('client.loan.create.post');
        Route::get('/{client_id}/loans', 'LoanAccountController@clientLoanList')->name('client.loan.list');
        Route::get('/{client_id}/loans/{loan_id}','LoanAccountController@account')->name('loan.account');
        Route::get('/{client_id}','ClientController@view')->name('client.profile');
        Route::get('/{clients:client_id}','ClientController@view')->name('client.profile');
        Route::get('/{client_id}/edit','ClientController@editClient')->name('edit.client');
        Route::post('/{client_id}/edit','ClientController@update');
        Route::get('/{client_id}/deposit/{deposit_account_id}', 'ClientController@depositAccount')->name('client.deposit'); 
        Route::get('/dependents/{client_id}', 'ClientController@listDependents')->name('client.dependents.list');

        Route::get('/{client_id}/create/deposit', 'DepositAccountController@createClientDepositAccount')->name('client.deposit.create');
        Route::post('/{client_id}/create/deposit', 'DepositAccountController@storeClientDepositAccount')->name('client.deposit.create');

        Route::get('/{client_id}/loan/{loan_id}','LoanAccountController@editAccount')->name('edit.loan.account');
        Route::get('/{client_id}/edit/loan/{loan_id}','LoanAccountController@getLoanAccount');
        Route::post('/{client_id}/edit/loan/{loan_id}','LoanAccountController@updateLoanAccount');
        Route::post('/change_status/{client_id}', 'ClientController@changeStatus');
    });

    Route::get('/create/client','ClientController@index')->name('precreate.client');
    Route::post('/create/client','ClientController@createV1')->name('create.client'); 
    Route::get('/clients','ClientController@list')->name('client.list');
    Route::get('/clients/list','ClientController@getList')->name('get.client.list');


    Route::prefix('/wApi')->group(function(){
        Route::prefix('/list')->group(function(){
            Route::post('/accounts','AccountController@getList');
        });
        
        Route::get('/client/list','ClientController@getList')->name('get.client.list');    
        Route::get('/product','ProductController@getItems');
        Route::get('/usr/branches','UserController@branches');
        Route::get('/deposits','DepositAccountController@showList');
        Route::post('/bulk/withdraw', 'DepositAccountController@bulkWithdraw')->name('bulk.deposit.withdraw.post');
        Route::post('/bulk/post_interest', 'DepositAccountController@bulkPostInterest')->name('bulk.deposit.interst_post.post');
        Route::post('/bulk/deposit', 'DepositAccountController@bulkDeposit')->name('bulk.deposit.deposit.post');
        Route::get('/payment/methods','PaymentMethodController@fetchPaymentMethods');

        Route::get('/loan/products','LoanController@loanProducts')->name('loan.products');
        
        //bulk create loan
        // Route::post('/bulk/create/loans', 'LoanAccountController@bulkCreateLoan')->name('bulk.create.loans.post');
        Route::post('/bulk/predisbursement/loans/list','LoanAccountController@preDisbursementList');
        Route::post('/bulk/{type}/loans','LoanAccountController@bulkLoanTransact');
        Route::get('/dashboard/{office_id}/{type}','DashboardController@type');


        Route::post('/scheduled/list','RepaymentController@scheduledListV2');
        Route::post('/bulk/repayments','RepaymentController@bulkRepaymentV2')->name('bulk.repayment.post');
        Route::post('/products','ProductController@index');

        Route::post('/reports/{type}','ReportController@getReport');

        
        

    });
    Route::get('/bulk/approve/loans','LoanAccountController@bulkApproveForm')->name('bulk.approve.loans');
    Route::get('/bulk/disburse/loans','LoanAccountController@bulkDisburseForm')->name('bulk.disburse.loans');
    Route::get('/bulk/writeoff/loans', 'LoanAccountController@bulkWriteoffList')->name('bulk.writeoff.loans');
    Route::post('/writeoff/loans', 'LoanAccountController@bulkWriteoffLoans');
    Route::post('/writeoff/loan/{loan_id}', 'LoanAccountController@writeoffAccount');

    
    
    
    
    
    
    Route::patch('/loan/reject/{loan_id}','LoanAccountController@abandoned')->name('loan.approve');
    Route::get('/loan/approve/{loan_id}','LoanAccountController@approve')->name('loan.approve');
    Route::post('/loan/disburse/{loan_id}','LoanAccountController@disburse')->name('loan.disburse');
    
    
    Route::post('/loans/repay','RepaymentController@accountPayment');
    Route::post('/loans/preterm','RepaymentController@preTerminate');
    Route::post('/revert','RevertController@revert')->name('revert.action');
    Route::get('/dashboard','DashboardController@dashboard')->name('dashboard');


    


    //Reports


    Route::get('/logout','Auth\LoginController@logout')->name('logout');
    Route::get('/scopes', function(){
        return auth()->user()->scopesBranch();
    });
    
    

  

    

    
    

    

    Route::post('/deposit/{deposit_account_id}','DepositAccountController@deposit')->name('client.make.deposit'); //make deposit transaction individually
    Route::post('/withdraw/{deposit_account_id}','DepositAccountController@withdraw')->name('client.make.withdrawal'); //make deposit transaction individually
    Route::put('/change_status/{depositaccount}/{client_id}', 'DepositAccountController@changeStatus');

    
    Route::get('/bulk/deposit', 'DepositAccountController@showBulkView')->name('bulk.deposit.deposit');
    Route::get('/bulk/withdraw', 'DepositAccountController@showBulkView')->name('bulk.deposit.withdraw');
    Route::get('/bulk/post_interest', 'DepositAccountController@showBulkView')->name('bulk.deposit.post_interest');
    
    Route::get('/bulk/create/loans', 'LoanAccountController@bulkCreateForm')->name('bulk.create.loans');
    // Route::post('/loans/pending/list', 'LoanAccountController@pendingLoans');
    Route::post('/bulk/create/loans', 'LoanAccountController@bulkCreateLoan')->name('bulk.create.loans.post');
    


    Route::prefix('/bulk')->group(function () {
        Route::get('/deposit', 'DepositAccountController@showBulkView')->name('bulk.deposit.deposit');
        Route::get('/withdraw', 'DepositAccountController@showBulkView')->name('bulk.deposit.withdraw');
        Route::get('/post_interest', 'DepositAccountController@showBulkView')->name('bulk.deposit.post_interest');
        Route::get('/create/loans', 'LoanAccountController@bulkCreateForm')->name('bulk.create.loans');
        // Route::post('/loans/pending/list', 'LoanAccountController@pendingLoans');
        
        
        Route::get('/repayment','RepaymentController@showBulkForm')->name('bulk.repayment');

    });

    Route::post('/loans/scheduled/list','RepaymentController@scheduledList');
    
    
    
    
    
    Route::post('/deposit/{deposit_account_id}','DepositAccountController@deposit')->name('client.make.deposit');
    Route::post('/deposit/account/post/interest','DepositAccountController@postInterest')->name('deposit.account.post.interest');


    Route::get('/accounts/{type}','AccountController@index')->name('accounts.list');

    Route::get('/auth/structure', 'UserController@authStructure')->name('auth.structure');
    Route::post('/search','SearchController@search');
    Route::get('/user/{user}','UserController@get');


    Route::prefix('/settings')->group(function () {
        
    });

    Route::prefix('reports')->group(function () {
        Route::get('/','ReportController@index')->name('reports.index');

        Route::get('/v2/repayments','ReportController@rp');
        Route::get('/{class}/{type}','ReportController@view')->name('reports.view');
    });


    Route::get('/usr/branches','UserController@branches');

    Route::get('/clusters', 'ClusterController@index');
    Route::get('/cluster/list/', 'ClusterController@getClustersList');
    Route::middleware(['edit_cluster'])->group(function(){
        
    });

    Route::group(['middleware' => ['permission:edit_cluster']], function(){
        Route::get('/edit/cluster/{id}/', 'OfficeController@editOffice');
        Route::post('/edit/cluster/{id}/', 'OfficeController@updateOffice');
    });
    Route::group(['permission:create_cluster'],function(){
        Route::get('/create/office/cluster', 'ClusterController@create');
        Route::post('/create/office/', 'OfficeController@createOffice');
    });
    
    
   Route::get('/xx', function(){
    
   });
   
   

});