<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

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

Route::group(['middleware' => ['install']], function () {

	Route::get('/', function () {
		return redirect('login');
	});

	Auth::routes();
	Route::get('/logout', 'Auth\LoginController@logout');

	Route::group(['middleware' => ['auth', 'verified']], function () {

		Route::get('dashboard', 'DashboardController@index')->name('dashboard.index');

		//Profile Controller
		Route::get('profile', 'ProfileController@index')->name('profile.index');
		Route::get('profile/edit', 'ProfileController@edit')->name('profile.edit');
		Route::post('profile/update', 'ProfileController@update')->name('profile.update')->middleware('demo');
		Route::get('profile/change_password', 'ProfileController@change_password')->name('profile.change_password');
		Route::post('profile/update_password', 'ProfileController@update_password')->name('profile.update_password')->middleware('demo');
		Route::get('profile/notification_mark_as_read/{id}', 'ProfileController@notification_mark_as_read')->name('profile.notification_mark_as_read');
		Route::get('profile/show_notification/{id}', 'ProfileController@show_notification')->name('profile.show_notification');

		/** Admin Only Route **/
		Route::group(['middleware' => ['admin', 'demo'], 'prefix' => 'admin'], function () {

			//User Management
			Route::resource('users', 'UserController');

			//User Roles
			Route::resource('roles', 'RoleController');

			//Payment Gateways
			Route::resource('payment_gateways', 'PaymentGatewayController')->except([
				'create', 'store', 'show', 'destroy',
			]);

			//Branch Controller
			Route::resource('branches', 'BranchController');

			//Savings Products
			Route::resource('savings_products', 'SavingsProductController');

			//Transaction Category
			Route::resource('transaction_categories', 'TransactionCategoryController');

			//Loan Products
			Route::resource('loan_products', 'LoanProductController');

			//Expense Categories
			Route::resource('expense_categories', 'ExpenseCategoryController')->except('show');

			//Currency List
			Route::resource('currency', 'CurrencyController');

			//Deposit Methods
			Route::resource('deposit_methods', 'DepositMethodController')->except([
				'show',
			]);

			//Withdraw Methods
			Route::resource('withdraw_methods', 'WithdrawMethodController')->except([
				'show',
			]);

			//Permission Controller
			Route::get('permission/access_control', 'PermissionController@index')->name('permission.index');
			Route::get('permission/access_control/{user_id?}', 'PermissionController@show')->name('permission.show');
			Route::post('permission/store', 'PermissionController@store')->name('permission.store');

			//Language Controller
			Route::resource('languages', 'LanguageController');

			//Utility Controller
			Route::match(['get', 'post'], 'administration/general_settings/{store?}', 'UtilityController@settings')->name('settings.update_settings');
			Route::post('administration/upload_logo', 'UtilityController@upload_logo')->name('settings.uplaod_logo');
			Route::get('administration/database_backup_list', 'UtilityController@database_backup_list')->name('database_backups.list');
			Route::get('administration/create_database_backup', 'UtilityController@create_database_backup')->name('database_backups.create');
			Route::delete('administration/destroy_database_backup/{id}', 'UtilityController@destroy_database_backup');
			Route::get('administration/download_database_backup/{id}', 'UtilityController@download_database_backup')->name('database_backups.download');
			Route::post('administration/remove_cache', 'UtilityController@remove_cache')->name('settings.remove_cache');
			Route::post('administration/send_test_email', 'UtilityController@send_test_email')->name('settings.send_test_email');

			//Email Template
			Route::resource('email_templates', 'EmailTemplateController')->only([
				'index', 'show', 'edit', 'update',
			]);

			//SMS Template
			Route::resource('sms_templates', 'SMSTemplateController')->only([
				'index', 'show', 'edit', 'update',
			]);

			//Notification Template
			Route::resource('notification_templates', 'NotificationTemplateController')->only([
				'index', 'show', 'edit', 'update',
			]);

		});

		/** Dynamic Permission **/
		Route::group(['middleware' => ['permission'], 'prefix' => 'admin'], function () {

			//Dashboard Widget
			Route::get('dashboard/total_customer_widget', 'DashboardController@total_customer_widget')->name('dashboard.total_customer_widget');
			Route::get('dashboard/deposit_requests_widget', 'DashboardController@deposit_requests_widget')->name('dashboard.deposit_requests_widget');
			Route::get('dashboard/withdraw_requests_widget', 'DashboardController@withdraw_requests_widget')->name('dashboard.withdraw_requests_widget');
			Route::get('dashboard/loan_requests_widget', 'DashboardController@loan_requests_widget')->name('dashboard.loan_requests_widget');
			Route::get('dashboard/expense_overview_widget', 'DashboardController@expense_overview_widget')->name('dashboard.expense_overview_widget');
			Route::get('dashboard/deposit_withdraw_analytics', 'DashboardController@deposit_withdraw_analytics')->name('dashboard.deposit_withdraw_analytics');
			Route::get('dashboard/recent_transaction_widget', 'DashboardController@recent_transaction_widget')->name('dashboard.recent_transaction_widget');

			//Member Controller
			Route::match(['get', 'post'], 'members/accept_request/{id}', 'MemberController@accept_request')->name('members.accept_request');
			Route::get('members/reject_request/{id}', 'MemberController@reject_request')->name('members.reject_request');
			Route::get('members/pending_requests', 'MemberController@pending_requests')->name('members.pending_requests');
			Route::get('members/get_member_transaction_data/{member_id}', 'MemberController@get_member_transaction_data');
			Route::get('members/get_table_data', 'MemberController@get_table_data');
			Route::post('members/send_email', 'MemberController@send_email')->name('members.send_email');
			Route::post('members/send_sms', 'MemberController@send_sms')->name('members.send_sms');
			Route::resource('members', 'MemberController')->middleware("demo:PUT|PATCH|DELETE");

			//Members Documents
			Route::get('member_documents/{member_id}', 'MemberDocumentController@index')->name('member_documents.index');
			Route::get('member_documents/create/{member_id}', 'MemberDocumentController@create')->name('member_documents.create');
			Route::resource('member_documents', 'MemberDocumentController')->except(['index', 'create', 'show']);

			//Savings Accounts
			Route::get('savings_accounts/get_account_by_member_id/{member_id}', 'SavingsAccountController@get_account_by_member_id');
			Route::get('savings_accounts/get_table_data', 'SavingsAccountController@get_table_data');
			Route::resource('savings_accounts', 'SavingsAccountController')->middleware("demo:PUT|PATCH|DELETE");

			//Interest Controller
			Route::get('interest_calculation/get_last_posting/{account_type_id?}', 'InterestController@get_last_posting')->name('interest_calculation.get_last_posting');
			Route::match(['get', 'post'], 'interest_calculation/calculator', 'InterestController@calculator')->name('interest_calculation.calculator');
			Route::post('interest_calculation/posting', 'InterestController@interest_posting')->name('interest_calculation.interest_posting');

			//Transaction
			Route::get('transactions/get_table_data', 'TransactionController@get_table_data');
			Route::resource('transactions', 'TransactionController');

			//Get Transaction Categories
			Route::get('transaction_categories/get_category_by_type/{type}', 'TransactionCategoryController@get_category_by_type');

			//Deposit Requests
			Route::post('deposit_requests/get_table_data', 'DepositRequestController@get_table_data');
			Route::get('deposit_requests/approve/{id}', 'DepositRequestController@approve')->name('deposit_requests.approve');
			Route::get('deposit_requests/reject/{id}', 'DepositRequestController@reject')->name('deposit_requests.reject');
			Route::delete('deposit_requests/{id}', 'DepositRequestController@destroy')->name('deposit_requests.destroy');
			Route::get('deposit_requests/{id}', 'DepositRequestController@show')->name('deposit_requests.show');
			Route::get('deposit_requests', 'DepositRequestController@index')->name('deposit_requests.index');

			//Withdraw Requests
			Route::post('withdraw_requests/get_table_data', 'WithdrawRequestController@get_table_data');
			Route::get('withdraw_requests/approve/{id}', 'WithdrawRequestController@approve')->name('withdraw_requests.approve');
			Route::get('withdraw_requests/reject/{id}', 'WithdrawRequestController@reject')->name('withdraw_requests.reject');
			Route::delete('withdraw_requests/{id}', 'WithdrawRequestController@destroy')->name('withdraw_requests.destroy');
			Route::get('withdraw_requests/{id}', 'WithdrawRequestController@show')->name('withdraw_requests.show');
			Route::get('withdraw_requests', 'WithdrawRequestController@index')->name('withdraw_requests.index');

			//Expense
			Route::get('expenses/get_table_data', 'ExpenseController@get_table_data');
			Route::resource('expenses', 'ExpenseController');

			//Loan Controller
			Route::post('loans/get_table_data', 'LoanController@get_table_data');
			Route::get('loans/calculator', 'LoanController@calculator')->name('loans.admin_calculator');
			Route::post('loans/calculator/calculate', 'LoanController@calculate')->name('loans.calculate');
			Route::get('loans/approve/{id}', 'LoanController@approve')->name('loans.approve');
			Route::get('loans/reject/{id}', 'LoanController@reject')->name('loans.reject');
			Route::get('loans/filter/{status?}', 'LoanController@index')->name('loans.filter')->where('status', '[A-Za-z]+');
			Route::resource('loans', 'LoanController');

			//Loan Collateral Controller
			Route::get('loan_collaterals/loan/{loan_id}', 'LoanCollateralController@index')->name('loan_collaterals.index');
			Route::resource('loan_collaterals', 'LoanCollateralController')->except('index');

			//Loan Guarantor Controller
			Route::resource('guarantors', 'GuarantorController')->except(['show', 'index']);

			//Loan Payment Controller
			Route::get('loan_payments/get_repayment_by_loan_id/{loan_id}', 'LoanPaymentController@get_repayment_by_loan_id');
			Route::get('loan_payments/get_table_data', 'LoanPaymentController@get_table_data');
			Route::resource('loan_payments', 'LoanPaymentController');

			//Report Controller
			Route::match(['get', 'post'], 'reports/account_statement', 'ReportController@account_statement')->name('reports.account_statement');
			Route::match(['get', 'post'], 'reports/account_balances', 'ReportController@account_balances')->name('reports.account_balances');
			Route::match(['get', 'post'], 'reports/transactions_report', 'ReportController@transactions_report')->name('reports.transactions_report');
			Route::match(['get', 'post'], 'reports/loan_report', 'ReportController@loan_report')->name('reports.loan_report');
			Route::get('reports/loan_due_report', 'ReportController@loan_due_report')->name('reports.loan_due_report');
			Route::match(['get', 'post'], 'reports/expense_report', 'ReportController@expense_report')->name('reports.expense_report');
			Route::match(['get', 'post'], 'reports/revenue_report', 'ReportController@revenue_report')->name('reports.revenue_report');
		});

		Route::group(['middleware' => ['customer'], 'prefix' => 'portal'], function () {

			//Membership Details
			Route::get('profile/membership_details', 'ProfileController@membership_details')->name('profile.membership_details');

			//Transfer Controller
			Route::match(['get', 'post'], 'transfer/own_account_transfer', 'Customer\TransferController@own_account_transfer')->name('transfer.own_account_transfer');
			Route::match(['get', 'post'], 'transfer/other_account_transfer', 'Customer\TransferController@other_account_transfer')->name('transfer.other_account_transfer');
			Route::get('transfer/transaction_details/{id}', 'Customer\TransferController@transaction_details')->name('trasnactions.details');
			Route::get('transfer/get_exchange_amount/{from?}/{to?}/{amount?}', 'Customer\TransferController@get_exchange_amount')->name('transfer.get_exchange_amount');
			Route::get('transfer/transaction_requests', 'Customer\TransferController@transaction_requests')->name('trasnactions.transaction_requests');

			//Loan Controller
			Route::match(['get', 'post'], 'loans/calculator', 'Customer\LoanController@calculator')->name('loans.calculator');
			Route::match(['get', 'post'], 'loans/apply_loan', 'Customer\LoanController@apply_loan')->name('loans.apply_loan');
			Route::get('loans/loan_details/{id}', 'Customer\LoanController@loan_details')->name('loans.loan_details');
			Route::match(['get', 'post'], 'loans/payment/{loan_id}', 'Customer\LoanController@loan_payment')->name('loans.loan_payment');
			Route::get('loans/my_loans', 'Customer\LoanController@index')->name('loans.my_loans');

			//Deposit Money
			Route::match(['get', 'post'], 'deposit/manual_deposit/{id}', 'Customer\DepositController@manual_deposit')->name('deposit.manual_deposit');
			Route::get('deposit/manual_methods', 'Customer\DepositController@manual_methods')->name('deposit.manual_methods');

			//Automatic Deposit
			Route::get('deposit/get_exchange_amount/{from?}/{to?}/{amount?}', 'Customer\DepositController@get_exchange_amount')->name('deposit.get_exchange_amount');
			Route::match(['get', 'post'], 'deposit/automatic_deposit/{id}', 'Customer\DepositController@automatic_deposit')->name('deposit.automatic_deposit');
			Route::get('deposit/automatic_methods', 'Customer\DepositController@automatic_methods')->name('deposit.automatic_methods');

			//Withdraw Money
			Route::match(['get', 'post'], 'withdraw/manual_withdraw/{id}/{otp?}', 'Customer\WithdrawController@manual_withdraw')->name('withdraw.manual_withdraw');
			Route::get('withdraw/manual_methods', 'Customer\WithdrawController@manual_methods')->name('withdraw.manual_methods');

			//Report Controller
			Route::match(['get', 'post'], 'reports/account_statement', 'Customer\ReportController@account_statement')->name('customer_reports.account_statement');
			Route::match(['get', 'post'], 'reports/transactions_report', 'Customer\ReportController@transactions_report')->name('customer_reports.transactions_report');
			Route::match(['get', 'post'], 'reports/account_balances', 'Customer\ReportController@account_balances')->name('customer_reports.account_balances');

		});

		Route::get('switch_language/', function () {
			if (isset($_GET['language'])) {
				session(['language' => $_GET['language']]);
				return back();
			}
		})->name('switch_language');

		Route::get('switch_branch/', function () {
			if (isset($_GET['branch']) && isset($_GET['branch_id'])) {
				session(['branch' => $_GET['branch'], 'branch_id' => $_GET['branch_id']]);
			} else {
				request()->session()->forget(['branch', 'branch_id']);
			}
			return back();
		})->name('switch_branch');

	});

});

Route::namespace ('Gateway')->prefix('callback')->name('callback.')->group(function () {
	//Fiat Currency
	Route::get('paypal', 'PayPal\ProcessController@callback')->name('PayPal')->middleware('auth');
	Route::post('stripe', 'Stripe\ProcessController@callback')->name('Stripe')->middleware('auth');
	Route::post('razorpay', 'Razorpay\ProcessController@callback')->name('Razorpay')->middleware('auth');
	Route::get('paystack', 'Paystack\ProcessController@callback')->name('Paystack')->middleware('auth');
	Route::get('flutterwave', 'Flutterwave\ProcessController@callback')->name('Flutterwave')->middleware('auth');
	Route::match(['get', 'post'], 'voguepay', 'VoguePay\ProcessController@callback')->name('VoguePay');
	Route::get('mollie', 'Mollie\ProcessController@callback')->name('Mollie')->middleware('auth');
	Route::match(['get', 'post'], 'instamojo', 'Instamojo\ProcessController@callback')->name('Instamojo');

	//Crypto Currency
	Route::get('blockchain', 'BlockChain\ProcessController@callback')->name('BlockChain');
	Route::post('coinpayments', 'CoinPayments\ProcessController@callback')->name('CoinPayments');
});

Route::get('dashboard/json_expense_by_category', 'DashboardController@json_expense_by_category')->middleware('auth');
Route::get('dashboard/json_deposit_withdraw_analytics/{currency_id?}', 'DashboardController@json_deposit_withdraw_analytics')->middleware('auth');

//Social Login
Route::get('/login/{provider}', 'Auth\SocialController@redirect');
Route::get('/login/{provider}/callback', 'Auth\SocialController@callback');

//Ajax Select2 Controller
Route::get('ajax/get_table_data', 'Select2Controller@get_table_data');

Route::get('/installation', 'Install\InstallController@index');
Route::get('install/database', 'Install\InstallController@database');
Route::post('install/process_install', 'Install\InstallController@process_install');
Route::get('install/create_user', 'Install\InstallController@create_user');
Route::post('install/store_user', 'Install\InstallController@store_user');
Route::get('install/system_settings', 'Install\InstallController@system_settings');
Route::post('install/finish', 'Install\InstallController@final_touch');

//Update System
Route::get('migration/update', 'Install\UpdateController@update_migration');