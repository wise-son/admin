<?php
namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\DepositRequest;
use App\Models\SavingsAccount;
use App\Models\Transaction;
use App\Models\WithdrawRequest;
use App\Notifications\TransferMoney;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TransferController extends Controller {

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct() {
		date_default_timezone_set(get_option('timezone', 'Asia/Dhaka'));
	}

	public function own_account_transfer(Request $request) {
		if ($request->isMethod('get')) {
			$alert_col = 'col-lg-8 offset-lg-2';
			$accounts = SavingsAccount::with('savings_type')
				->where('member_id', auth()->user()->member->id)
				->get();
			return view('backend.customer_portal.transfer.own_account_transfer', compact('accounts', 'alert_col'));
		} else {
			$validator = Validator::make($request->all(), [
				'from_account' => 'required',
				'to_account' => 'required|different:from_account',
				'amount' => 'required|numeric',
			], [
				'to_account.different' => _lang('From account and to account must be different'),
			]);

			if ($validator->fails()) {
				if ($request->ajax()) {
					return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
				} else {
					return redirect()->route('transfer.own_account_transfer')
						->withErrors($validator)
						->withInput();
				}
			}

			$memeber = auth()->user()->member;
			$senderAccount = SavingsAccount::where('id', $request->from_account)
				->where('member_id', $memeber->id)
				->first();
			$receiverAccount = SavingsAccount::where('id', $request->to_account)
				->where('member_id', $memeber->id)
				->first();

			//Check Withdraw is allowed or not
			if ($senderAccount->savings_type->allow_withdraw == 0) {
				return back()
					->with('error', _lang('Withdraw and transfer is not allowed for') . ' ' . $senderAccount->savings_type->name)
					->withInput();
			}

			//Check Available Balance
			if (get_account_balance($request->from_account, $memeber->id) < $request->amount) {
				return back()->with('error', _lang('Insufficient balance !'))->withInput();
			}

			DB::beginTransaction();

			//Create Debit Transactions
			$debit = new Transaction();
			$debit->trans_date = now();
			$debit->member_id = $memeber->id;
			$debit->savings_account_id = $request->from_account;
			$debit->amount = $request->amount;
			$debit->dr_cr = 'dr';
			$debit->type = 'Transfer';
			$debit->method = 'Online';
			$debit->status = 2;
			$debit->note = $request->note;
			$debit->description = _lang('Transfer Money from A/C') . ' ' . $debit->account->account_number . ' ' . _lang('to A/C') . ' ' . $receiverAccount->account_number;
			$debit->created_user_id = auth()->id();
			$debit->branch_id = $memeber->branch_id;
			$debit->save();

			//Create Credit Transactions
			$credit = new Transaction();
			$credit->trans_date = now();
			$credit->member_id = $memeber->id;
			$credit->savings_account_id = $request->to_account;
			$credit->amount = convert_currency($debit->account->savings_type->currency->name, $credit->account->savings_type->currency->name, $request->amount);
			$credit->dr_cr = 'cr';
			$credit->type = 'Transfer';
			$credit->method = 'Online';
			$credit->status = 2;
			$credit->note = $request->note;
			$credit->description = _lang('Received Money from A/C') . ' ' . $debit->account->account_number . ' ' . _lang('to A/C') . ' ' . $receiverAccount->account_number;
			$credit->created_user_id = auth()->id();
			$credit->branch_id = $memeber->branch_id;
			$credit->save();

			DB::commit();

			if ($credit->id > 0) {
				return redirect()->route('transfer.own_account_transfer')->with('success', _lang('Money transfered successfully'));
			} else {
				return redirect()->route('transfer.own_account_transfer')->with('error', _lang('Something went wrong, Please try again!'));
			}

		}
	}

	public function other_account_transfer(Request $request) {
		if ($request->isMethod('get')) {
			$alert_col = 'col-lg-8 offset-lg-2';
			$accounts = SavingsAccount::with('savings_type')
				->whereHas('savings_type', function (Builder $query) {
					$query->where('allow_withdraw', 1);
				})
				->where('member_id', auth()->user()->member->id)
				->get();
			return view('backend.customer_portal.transfer.other_account_transfer', compact('alert_col', 'accounts'));
		} else {
			$validator = Validator::make($request->all(), [
				'debit_account' => 'required',
				'credit_account' => 'required',
				'amount' => 'required|numeric',
			]);

			if ($validator->fails()) {
				if ($request->ajax()) {
					return response()->json(['result' => 'error', 'message' => $validator->errors()->all()]);
				} else {
					return redirect()->route('transfer.other_account_transfer')
						->withErrors($validator)
						->withInput();
				}
			}

			$member = auth()->user()->member;

			$senderAccount = SavingsAccount::where('id', $request->debit_account)
				->where('member_id', $member->id)
				->first();

			$receiverAccount = SavingsAccount::where('account_number', $request->credit_account)->first();

			if(!$receiverAccount){
				return back()->with('error', _lang('Invalid Account Number !'))->withInput();
			}

			//Check Account
			if ($senderAccount->account_number == $receiverAccount->account_number) {
				return back()->with('error', _lang('Sender account and receiver account must be different !'))->withInput();
			}

			//Check Withdraw is allowed or not
			if ($senderAccount->savings_type->allow_withdraw == 0) {
				return back()
					->with('error', _lang('Withdraw and transfer is not allowed for') . ' ' . $senderAccount->savings_type->name)
					->withInput();
			}

			//Check Available Balance
			if (get_account_balance($senderAccount->id, $senderAccount->member_id) < $request->amount) {
				return back()->with('error', _lang('Insufficient balance !'))->withInput();
			}

			DB::beginTransaction();

			//Create Debit Transactions
			$debit = new Transaction();
			$debit->trans_date = now();
			$debit->member_id = $senderAccount->member_id;
			$debit->savings_account_id = $senderAccount->id;
			$debit->amount = $request->amount;
			$debit->dr_cr = 'dr';
			$debit->type = 'Transfer';
			$debit->method = 'Online';
			$debit->status = 2;
			$debit->note = $request->note;
			$debit->description = _lang('Transfer Money from A/C') . ' ' . $senderAccount->account_number . ' ' . _lang('to A/C') . ' ' . $receiverAccount->account_number;
			$debit->created_user_id = auth()->id();
			$debit->branch_id = $senderAccount->member->branch_id;
			$debit->save();

			//Create Credit Transactions
			$credit = new Transaction();
			$credit->trans_date = now();
			$credit->member_id = $receiverAccount->member_id;
			$credit->savings_account_id = $receiverAccount->id;
			$credit->amount = convert_currency($debit->account->savings_type->currency->name, $credit->account->savings_type->currency->name, $request->amount);
			$credit->dr_cr = 'cr';
			$credit->type = 'Transfer';
			$credit->method = 'Online';
			$credit->status = 2;
			$credit->parent_id = $debit->id;
			$credit->note = $request->note;
			$credit->description = _lang('Received Money from A/C') . ' ' . $senderAccount->account_number . ' ' . _lang('to A/C') . ' ' . $receiverAccount->account_number;
			$credit->created_user_id = auth()->id();
			$credit->branch_id = $senderAccount->member->branch_id;
			$credit->save();

			DB::commit();

			try {
				$credit->member->notify(new TransferMoney($credit));
			} catch (\Exception $e) {}

			if ($credit->id > 0) {
				return redirect()->route('transfer.other_account_transfer')->with('success', _lang('Money transfered successfully'));
			} else {
				return redirect()->route('transfer.other_account_transfer')->with('error', _lang('Something went wrong, Please try again!'));
			}
		}
	}

	/**
	 * Display Transaction details.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function transaction_details(Request $request, $id) {
		$transaction = Transaction::find($id);
		if (!$request->ajax()) {
			return view('backend.transaction.view', compact('transaction', 'id'));
		} else {
			return view('backend.transaction.modal.view', compact('transaction', 'id'));
		}
	}

	/**
	 * Display Transaction details.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function transaction_requests(Request $request) {
		$member_id = auth()->user()->member->id;
		$deposit_requests = DepositRequest::where('member_id', $member_id)->get();
		$withdraw_requests = WithdrawRequest::where('member_id', $member_id)->get();

		return view('backend.customer_portal.transaction-requests', compact('deposit_requests', 'withdraw_requests'));
	}

	public function get_exchange_amount($from, $to, $amount) {
		$amount = convert_currency($from, $to, $amount);
		return response()->json(['amount' => $amount]);
	}

}