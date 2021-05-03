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

Route::get('/gg',function(){
    return \App\Dashboard::disbursementTrend(1,1);
    return;
    // $sesh = session('dashboard.par_movement');

    // return response()->json(['data'=>$sesh],200);
    $data = Dashboard::clientTrend(1,true);
    return response()->json(['data'=>$data],200);
    $k_ep =key($sesh['expected_repayment']);
    $k_ar =key($sesh['actual_repayment']);
    $k_lb =key($sesh['labels']);

    $sesh['expected_repayment'][$k_ep] = $data['expected_repayment'][0];
    $sesh['expected_repayment'][$k_ep] = $data['actual_repayment'][0];
    $sesh['labels'][$k_ep] = $data['labels'][0];
    
    return response()->json(['data'=>$sesh],200);

    return 'tae';
});
Route::get('rep',function(){

return (\App\ParMovement::dashboardReport('2021-04-06',now(),now(),1));
return (\App\ParMovement::dashboardReport('2021-04-06',now(),now(),1));
    $account  = LoanAccount::find(1);
    $pre_term_amount = $account->fresh()->preTermAmount();
    $installments =$account->fresh()->installments;
    
    $client = Client::select('firstname','lastname','client_id')->where('client_id','001DAG-PC00007')->first();
    
    $activity = $account->activity()->orderBy('created_at','desc')->get()->each->transactionable;
    return response()->json([
        'account'=>$account,
        'client'=>$client,
        'installments'=>$installments,
        'activity'=>$activity
    ],200);
    return LoanAccount::find(1)->load('pre_term_amount');
    $loanPayload = ['date'=>now(),'amount'=>5000];
    event(new LoanAccountPaymentEvent($loanPayload,  21, 2, 4));
    event(new TestEvent('hey'));
    return;
    $data = ['from_date'=>'2020-11-26','to_date'=>'2021-04-06'];
    $x = \App\Report::repayment($data,true);
    return $x;
    $filename = 'Disbursement Report.xlsx';
    $file = public_path('reports/').$filename;
    return response()->download($file);
    $data = ['from_date'=>now(),'to_date'=>now()];
    $list = \App\Report::repayment($data,false);
    return $list;
});

Route::get('dRep',function(){
    $data = ['date'=>carbon()->parse('2020-11-26'),'office_id'=>null,'users'=>[]];
    $list = \App\Report::disbursement($data,false);
    // Excel::store(new DisbursementExport($list),'dRep.xlsx');
    return Excel::store(new DisbursementExport($list),'dRep.xlsx','reports');
});
Route::get('/transactions', function(Request $request){
    if($request->has('type')){
        if($request->type == 'loan'){
            $data = [['type'=>'Transaction' , 'data'=>[['id'=>1,'name'=>'Loan Payment'],['id'=>2,'name'=>'CTLP']]]];
        }
        
        if($request->type == 'deposit'){
            $data = [['type'=>'Transaction' , 'data'=>[['id'=>1,'name'=>'Payment'],['id'=>2,'name'=>'Withdrawal'],['id'=>3,'name'=>'CTLP Withdrawal'],['id'=>4,'name'=>'Interest Posting']]]];
        }
    }
    
    return response()->json($data,200);
});
Route::get('/download/dst/{loan_account_id}','DownloadController@dst');

// Route::post('/download/dst/{id}','DownloadController@dst');
Route::get('/download/dst/bulk/{bulk_transaction_id}','DownloadController@dstBulk');
Route::get('/ccrdebug',function(Request $request){


    $summary = session('ccr');
    
    $file = public_path('temp/').$summary->office.' - '.$summary->repayment_date.'.pdf';            
    $pdf = App::make('snappy.pdf.wrapper');
    $headers = ['Content-Type'=> 'application/pdf','Content-Disposition'=> 'attachment;','filename'=>$summary->name];
    // return view('exports.test',compact('summary'));
    $pdf->loadView('exports.test',compact('summary'))->save($file,true);
    return $pdf->stream();
    return response()->download($file,$summary->name,$headers);

});

Route::post('/download/ccr','DownloadController@ccr');
Route::get('/', function () {
    return redirect()->route('dashboard');
});
Route::get('/z',function(){
    $role = Role::firstOrCreate(['name' => 'Branch Accountant']);

    Permission::firstOrCreate(['name' => 'create client']);
    Permission::firstOrCreate(['name' => 'view dashboard']);

    $role->givePermissionTo(['view dashboard']);
    $role->givePermissionTo(['create client']);
    // $role->revokePermissionTo(['create client']);

    auth()->user()->assignRole($role);

});


Route::get('/loan/products','LoanController@');
Route::get('/random',function(){
    return view('random-picker');
});
Auth::routes();
Route::get('/fees','FeeController@getList');
Route::get('/ssss',function(){
    // \App\LoanAccount::first()->updateStatus();
});
Route::group(['middleware' => ['auth']], function () {

    Route::get('/stepper','ClientController@step');
    Route::get('/pay','RepaymentController@repayLoan');
    Route::post('/loan/calculator', 'LoanAccountController@calculate')->name('loan.calculator');
    Route::post('/products','ProductController@index');

    Route::get('/client/{client_id}/create/dependents', 'ClientController@toCreateDependents')->name('client.create.dependents');
    Route::post('/client/create/dependent', 'DependentController@createDependents')->name('create.dependents.post');
    Route::get('/client/update/dependent', 'DependentController@updateDependentStatus')->name('create.dependents.activate');
    Route::get('/client/{client_id}/manage/dependents', 'ClientController@dependents')->name('client.manage.dependents');
    Route::get('/dependents/{client_id}', 'ClientController@listDependents')->name('client.dependents.list');
    Route::get('/client/{client_id}/create/loan', 'LoanAccountController@index')->name('client.loan.create');
    Route::post('/client/create/loan', 'LoanAccountController@createLoan')->name('client.loan.create.post');
    Route::get('/client/{client_id}/loans', 'LoanAccountController@clientLoanList')->name('client.loan.list');
    Route::get('/loan/approve/{loan_id}','LoanAccountController@approve')->name('loan.approve');
    Route::get('/loan/disburse/{loan_id}','LoanAccountController@disburse')->name('loan.disburse');
    
    Route::get('/client/{client_id}/loans/{loan_id}','LoanAccountController@account')->name('loan.account');
    Route::post('/loans/repay','RepaymentController@accountPayment');
    Route::post('/loans/preterm','RepaymentController@preTerminate');
    Route::post('/revert','RevertController@revert')->name('revert.action');
    Route::get('/dashboard','DashboardController@index')->name('dashboard');
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
    Route::get('/clients','ClientController@list')->name('client.list');
    Route::get('/clients/list','ClientController@getList')->name('get.client.list');
    Route::get('/client/{client_id}','ClientController@view')->name('client.profile');
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


    Route::get('/settings/create/role', function(){
        return view('pages.create-role');
    });
    Route::get('/settings/create/user', function(){
        return view('pages.create-user');
    })->name('create.user');

    Route::get('/settings/create/fee', function(){
        return view('pages.create-fees');
    });

    Route::get('/settings/create/penalty', function(){
        return view('pages.create-penalty');
    });

    Route::get('/settings/create/office/{level}', 'OfficeController@createLevel')->name('create.office');

    Route::post('/search','SearchController@search');

    Route::get('/settings', function(){
        return view('pages.settings');
    })->name('administration');

    Route::get('/user/{user}','UserController@get');
    Route::get('/settings/create/loan', function(){
        return view('pages.create-loan');
    });

    Route::get('/settings/loan/edit/{loan}','LoanController@updateLoan'); //render view
    Route::get('/settings/loan/product/edit/{id}','LoanController@loanProduct'); //get product via id

    Route::post('/settings/loan/edit/{id}','LoanController@updateLoanProduct'); //post view
    
    Route::get('/settings/loan/view/{loan}','LoanController@viewLoan');
    
    Route::post('/settings/create/loan','LoanController@create');
    Route::get('/reports','ReportController@index')->name('reports.index');
    
    Route::get('/reports/v2/repayments','ReportController@rp');
    Route::get('/reports/{class}/{type}','ReportController@view')->name('reports.view');
    Route::post('/reports/{type}','ReportController@getReport');

});
 

