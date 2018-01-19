<?php

namespace App\Http\Controllers;

use App\Currency;
use App\Transaction;
use App\Wallet;
use App\ICOPhase;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class BlockchainController extends Controller
{

    private $send_key = "";

    private $ICOPhase;
    private $basic;

    public function __construct() {

        $this->middleware(function ($request, $next) {

               $this->ICOPhase = $this->currentICOPhase();
               $this->basic = $this->basicWallet();

               return $next($request);

           });

    }

    private function basicWallet() {

        $response = Wallet::where("currency_id", 3)->where("user_id", Auth::id())->first();
        return $response;

    }

    private function otherWallets() {

        $response['ETH'] = Wallet::where("currency_id", 2)->where("user_id", Auth::id())->first();
        $response['BTC'] = Wallet::where("currency_id", 1)->where("user_id", Auth::id())->first();
        return $response;
    }

    private function currentICOPhase() {

        $current_time = date('Y-m-d H:i:s');
        $response = ICOPhase::where('start_time', '<=', $current_time)->where('end_time', '>', $current_time)->first();
        if (isset($response->token_price)) {

            if ($response->tokens_sold < $response->tokens_amount) {

                return $response;

            }

        } else {

            $response = ICOPhase::where('end_time', '=', 0)->first();
            return $response;

        }

    }

    public function getStats() {

        $stats = json_decode(file_get_contents(env('BLOCKCHAIN_LINK') . '/stats'), true);
        $stats['contributors'] = $this->getContributors();
        $stats['phase'] = $this->ICOPhase;
        $stats['sold-tokens'] = self::tokensSold();

        return response()->json($stats);

    }

    public function check() {

        // if($this->isDisabled()) {
        //     return response()->json(['disabled' => true]);
        // }

        $response = array();
        $wallets = Auth::user()->wallets;

        foreach ($wallets as $wallet) {

            switch ($wallet->currency->symbol) {
                case 'ETH':
                    $response['ETH'] = $this->checkUpdates($this->checkETH($wallet->address)['balance'], $wallet);
                    break;
                case 'BTC':
                    $response['BTC'] = $this->checkUpdates($this->checkBTC($wallet->address)['balance'], $wallet);
                    break;
            }
        }

        $SWTBalance = $this->checkSWT($this->basic->address)['balance'];
        $response['SWT'] = $this->checkUpdates($SWTBalance, $this->basic);


        return response()->json($response);

    }

    public function getWalletStats() {

        $response = array();

        $swtBalance = $this->checkSWT($this->basic->address)['balance'];
        $uid = Auth::user();

        $wallets = $this->otherWallets();

        $response['ETH']['address'] = $wallets['ETH']->address;
        $response['BTC']['address'] = $wallets['BTC']->address;
        $response[$this->basic->currency->symbol]['address'] = $this->basic->address;
        $response[$this->basic->currency->symbol]['balance'] = $swtBalance;
        $response[$this->basic->currency->symbol]['stats'] = $this->SWTtoAll($swtBalance);
        $this->ICOPhase->token_price = $this->ICOPhase->token_price*$this->tokenPriceETH();
        $response['phase'] = $this->ICOPhase;
        // $response['tokens_sold'] = $this->tokensSold()['tokens_sold'];

        return response()->json($response);

    }

    public function tokenPrices() {

        $response = array();
        $response['ETH'] = $this->tokenPriceETH();
        $response['BTC'] = $this->tokenPriceBTC();
        $response['USD'] = $this->ICOPhase->token_price;

        return response()->json($response);

    }

    private function tokensSold() {

        return json_decode(file_get_contents(env('BLOCKCHAIN_LINK') . '/stats/sold'), true);

    }

    private function checkETH($address = null) {

        return json_decode(file_get_contents(env('BLOCKCHAIN_LINK') . '/wallet/check/eth/' . $address), true);

    }

    private function checkBTC($address = null) {

        return json_decode(file_get_contents(env('BLOCKCHAIN_LINK') . '/wallet/check/btc/' . $address), true);

    }

    private function checkSWT($address = null) {

        return json_decode(file_get_contents(env('BLOCKCHAIN_LINK') . '/wallet/check/swt/' . $address), true);

    }

    private function tokenPriceETH() {

        // return $this->ICOPhase->token_price;
        return json_decode(file_get_contents('https://min-api.cryptocompare.com/data/price?fsym=ETH&tsyms=USD'), true)['USD'];

    }

    private function tokenPriceBTC() {

        $BTCtoETH = json_decode(file_get_contents('https://min-api.cryptocompare.com/data/price?fsym=BTC&tsyms=ETH'), true)['ETH'];
        return $BTCtoETH * $this->tokenPriceETH();

    }

    private function getContributors() {

        $response = Transaction::distinct('user_id')->count('user_id');
        return $response;

    }

    private function SWTtoETH($tokens) {

        return $tokens / $this->tokenPriceETH();

    }

    private function SWTtoBTC($tokens) {

        return $tokens / $this->tokenPriceBTC();

    }

    private function SWTtoUSD($tokens) {

        $ETHtoUSD = json_decode(file_get_contents('https://min-api.cryptocompare.com/data/price?fsym=ETH&tsyms=USD'), true)['USD'];
        return $ETHtoUSD * $this->SWTtoETH($tokens);

    }

    private function SWTtoAll($tokens) {

        $response = array();

        $response['ETH'] = $this->SWTtoETH($tokens);
        $response['BTC'] = $this->SWTtoBTC($tokens);
        $response['USD'] = $this->SWTtoUSD($tokens);

        return $response;


    }

    private function sendSWT($address, $tokens) {

        return json_decode(file_get_contents(env('BLOCKCHAIN_LINK') . '/transaction/send/swt/' . $address . '/' . $tokens . '/' . $this->send_key), true);

    }

    private function sendETH($address, $private_key, $count) {

        return json_decode(file_get_contents(env('BLOCKCHAIN_LINK') . '/transaction/send/eth/' . $address . '/' . $private_key . '/' . $count), true);

    }

    private function sendBTC($address, $private_key, $count) {

        return json_decode(file_get_contents(env('BLOCKCHAIN_LINK') . '/transaction/send/btc/' . $address . '/' . $private_key . '/' . $count), true);

    }

    private function getTransactionReceipt($transaction_hash) {

        return json_decode(file_get_contents(env('BLOCKCHAIN_LINK') . '/transaction/receipt/' . $transaction_hash), true);

    }

    private function addTransaction($transaction_hash, $source_amount, $target_amount, $currency, $user_id = null) {

        $transaction = new Transaction;

        $transaction->hash = $transaction_hash;

        if ($user_id != null) {

            $transaction->user_id = $user_id;

        } else {

            $transaction->user_id = Auth::id();

        }

        $transaction->target_amount = $target_amount/pow(10, $currency->decimals);
        $transaction->source_amount = $source_amount/pow(10, $this->basic->currency->decimals);

        $transaction->currency_id = $currency->id;

        $transaction->save();

        return $transaction;

    }

    private function payReferral($ref_id, $tokens_count) {

        $wallet = Wallet::where('user_id', $ref_id)->where('currency_id', 3)->first();
        $tokens_count = $tokens_count * 0.1;
        $response = $this->sendSWT($wallet->address, $tokens_count);

        if (isset($response['transaction'])) {

            $transaction = $this->addTransaction($response['transaction'], $tokens_count, 0, Currency::find(5), $ref_id);
            // IMPORTANT pow(10,18)
            ICOPhase::find($this->ICOPhase->id)->update(['tokens_sold' => $this->ICOPhase->tokens_sold+($tokens_count/pow(10, $this->basic->currency->decimals))]);

        }

    }

    private function updateWallet($token_price, $balance, $wallet) {

        $tokens_count = ((($balance - $wallet->balance)/pow(10, $wallet->currency->decimals))*$token_price)*pow(10, $this->basic->currency->decimals);

        // Bounty +10%
        $tokens_count = $tokens_count + (0.15*$tokens_count);

        // Check if tokens_count for sending dont exceed tokens_amount-tokens-sold in phase
        if ($this->ICOPhase->tokens_sold+($tokens_count/pow(10, $this->basic->currency->decimals)) > $this->ICOPhase->tokens_amount-$this->ICOPhase->tokens_sold) {

            $tokens_count = ($this->ICOPhase->tokens_amount-$this->ICOPhase->tokens_sold)*pow(10, $this->basic->currency->decimals);

        }

        $response = $this->sendSWT($this->basic->address, $tokens_count);

        if (isset($response['transaction'])) {

            $transaction = $this->addTransaction($response['transaction'], $tokens_count, $balance - $wallet->balance, $wallet->currency);
            // IMPORTANT pow(10,18)
            ICOPhase::find($this->ICOPhase->id)->update(['tokens_sold' => $this->ICOPhase->tokens_sold+($tokens_count/pow(10, $this->basic->currency->decimals))]);
            Wallet::find($wallet->id)->update(['balance' => $balance]);

            if (Auth::user()->referral_id != null) {

                $this->payReferral(Auth::user()->referral_id, $tokens_count);

            }

            return $transaction;
        }

    }

    private function checkUpdates($balance, $wallet) {

        if ($balance > $wallet->balance) {

            switch ($wallet->currency->symbol) {
                case 'ETH':

                    $transaction = $this->updateWallet($this->tokenPriceETH(), $balance, $wallet);
                    if ($transaction != null) {
                        return $transaction;
                    }
                    break;

                case 'BTC':

                    $transaction = $this->updateWallet($this->tokenPriceBTC(), $balance, $wallet);
                    if ($transaction != null) {
                        return $transaction;
                    }
                    break;

                case 'SWT':

                    Wallet::find($wallet->id)->update(['balance' => $balance]);
                    break;
            }


        }

        return null;

    }

    private function isDisabled() {

        if(Auth::user()->flag < 3) {
            return true;
        }

        return false;

    }

}
