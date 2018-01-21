<?php

namespace App\Http\Controllers;

use App\User;
use Config;

use Duo\Web;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

    include(app_path() . '/Web.php');

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */

    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {   
        return view('home');
    }

    public function registrated() {
        
        return view("auth.confirmation");
        
    }

    public function registratedVerify($token = null) {
        $this->middleware('guest');
        return view("auth.confirmation");
        
    }

    
    public function kyc() {

        return view("kyc.form");

    }

    public function verification() {

        return view("kyc.verification", ['user' => User::where("flag", "1")->first()]);

    }
    
    public function kycUploaded() {
        
        return view("kyc.conformation");

    }

    public function checkVerification(Request $request) {

        $id = $request->input('id');

        if ($request->has('not_verified')) {

            User::find($id)->update(['flag' => '3']);

        }

        if ($request->has('verified')) {

            User::find($id)->update(['flag' => '2']);

        }

        return self::verification();

    }

    public function duoGet() {
        $Duo_Web = new Web();

        $ikey = Config::get('constants.duoSetting.ikey');
        $skey = Config::get('constants.duoSetting.skey');
        $akey = Config::get('constants.duoSetting.akey');
        $host = Config::get('constants.duoSetting.host');

        $sig_request = $Duo_Web::signRequest($ikey, $skey, $akey, Auth::user()->email);
        return view("auth.duo", ['sig_request' => $sig_request, 'host' => $host]);
    }

    public function duoPost(Request $request) {
        $Duo_Web = new Web();
        $sig_response = $request->input('sig_response');

        $ikey = Config::get('constants.duoSetting.ikey');
        $skey = Config::get('constants.duoSetting.skey');
        $akey = Config::get('constants.duoSetting.akey');
        $host = Config::get('constants.duoSetting.host');

        $resp = $Duo_Web::verifyResponse($ikey, $skey, $akey, $sig_response);

        if($resp == Auth::user()->email){
            return view("auth.duo", ['sig_response' => $sig_response]);
        }else{
            Auth::logout();
        }
    }
}
