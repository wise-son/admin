<?php

namespace App\Http\Controllers\Gateway\PayPal;

use App\Models\Transaction;
use Illuminate\Http\Request;
use PayPalHttp\HttpException;
use App\Notifications\DepositMoney;
use App\Http\Controllers\Controller;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use PayPalCheckoutSdk\Core\ProductionEnvironment;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;

class ProcessController extends Controller {

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        ini_set('error_reporting', E_ALL);
        ini_set('display_errors', '1');
        ini_set('display_startup_errors', '1');

        date_default_timezone_set(get_option('timezone', 'Asia/Dhaka'));
    }

    /**
     * Process Payment Gateway
     *
     * @return \Illuminate\Http\Response
     */
    public static function process($deposit) {
        $data                 = array();
        $data['callback_url'] = route('callback.' . $deposit->gateway->slug);
        $data['custom']       = $deposit->id;
        $data['view']         = 'backend.customer_portal.gateway.' . $deposit->gateway->slug;

        return json_encode($data);
    }

    /**
     * Callback function from Payment Gateway
     *
     * @return \Illuminate\Http\Response
     */
    public function callback(Request $request) {
        @ini_set('max_execution_time', 0);
        @set_time_limit(0);

        $transaction = Transaction::find($request->deposit_id);

        // Creating an environment
        $clientId     = $transaction->gateway->parameters->client_id;
        $clientSecret = $transaction->gateway->parameters->client_secret;

        if ($transaction->gateway->parameters->environment == 'sandbox') {
            $environment = new SandboxEnvironment($clientId, $clientSecret);
        } else {
            $environment = new ProductionEnvironment($clientId, $clientSecret);
        }

        $client = new PayPalHttpClient($environment);

        $request = new OrdersCaptureRequest($request->order_id);
        $request->prefer('return=representation');

        try {
            $response = $client->execute($request);

            if ($response->result->status == 'COMPLETED') {
                $amount = $response->result->purchase_units[0]->amount->value;

                //Update Transaction
                if ($transaction->gateway_amount <= $amount) {
                    $transaction->status = 2; // Completed
                    $transaction->transaction_details = json_encode($response);
                    $transaction->save();
                }

                //Trigger Deposit Money notifications
                try {
                    $transaction->member->notify(new DepositMoney($transaction));
                } catch (\Exception $e) {}

                return redirect()->route('dashboard.index')->with('success', _lang('Money Deposited Successfully'));
            }

        } catch (HttpException $ex) {
            return redirect()->route('deposit.automatic_methods')->with('error', _lang('Sorry, Payment not completed !'));
        }
    }

}