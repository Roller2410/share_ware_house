<?php

namespace App\Http\Controllers;

use App\Transaction;

use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Carbon\Carbon;

class TransactionController extends Controller
{

    public function getTotalInvestments() {
        $stats = array();

        $stats['users'] = self::getDailyUsersCount();
        $stats['daily'] = round(self::getTotalDailyInvestments(), 2);
        $stats['BTC'] = round(self::getTotalBTCInvestments(), 5);
        $stats['ETH'] = round(self::getTotalETHInvestments(), 5);
        $stats['USD'] = round(self::getTotalUSDInvestments(), 2);
        $stats['SWT'] = round(self::getTotalTokesSold(), 2);

        return $stats;
    }

    private function getDailyUsersCount() {
        return Transaction::select('user_id')->where('created_at', '>=', date('Y-m-d'))->distinct('user_id')->count('user_id');
    }

    private function getTotalDailyInvestments() {

        $currentDay = date('Y-m-d');
        $totalInvestments = 0;
        $dailyInvestments = Transaction::where('created_at', '>=', $currentDay)->get()->toArray();

        for($i = 0; $i < count($dailyInvestments); $i++) {
            if($dailyInvestments[$i]['currency_id'] == 1) {
                $totalInvestments += self::getBTCPrice() * $dailyInvestments[$i]['target_amount'];
            } else if($dailyInvestments[$i]['currency_id'] == 2) {
                $totalInvestments += self::getETHPrice() * $dailyInvestments[$i]['target_amount'];
            } else if($dailyInvestments[$i]['currency_id'] == 4) {
                $totalInvestments += $dailyInvestments[$i]['target_amount'];
            }
        }

        return $totalInvestments;
    }

    private function getBTCPrice() {
        return json_decode(file_get_contents('https://min-api.cryptocompare.com/data/price?fsym=BTC&tsyms=USD'), true)['USD'];
    }

    private function getETHPrice() {
        return json_decode(file_get_contents('https://min-api.cryptocompare.com/data/price?fsym=ETH&tsyms=USD'), true)['USD'];
    }

    private function getTotalBTCInvestments() {
        return Transaction::select('target_amount')->where('currency_id', '1')->sum('target_amount');
    }

    private function getTotalETHInvestments() {
        return Transaction::select('target_amount')->where('currency_id', '2')->sum('target_amount');
    }

    private function getTotalUSDInvestments() {
        return Transaction::select('target_amount')->where('currency_id', '4')->sum('target_amount');
    }

    private function getTotalTokesSold() {
        return Transaction::select('source_amount')->whereIn('currency_id', [1, 2, 4])->sum('source_amount');
    }

}
