<?php

namespace App\Http\Controllers;

use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use SendGrid;
use App\User;
use Illuminate\Support\Facades;
use Illuminate\Support\Facades\DB;

class SendGridEmailController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */

    use SendsPasswordResetEmails;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function index(Request $request){
        $this->validateEmail($request);
        $tomail = $request->input('email');
        $password_token = DB::table('password_resets')->where('email', $tomail)->first();
        $token = $password_token->token; 
        $sendgrid_apikey = getenv('SENDGRID_API_KEY');
        $sendgrid = new SendGrid($sendgrid_apikey);
        $url = 'https://api.sendgrid.com/';
        $pass = $sendgrid_apikey;
        $template_id = '';
        $js = array(
          'sub' => array(':name' => array('Elmer'))
        );
        
        $params = array(
            'to'        => "",
            'toname'    => "Example User",
            'from'      => "KalleVinter@outlook.com",
            'fromname'  => "Your Name",
            'subject'   => "SendGrid Test",
            'text'      => "I'm text!",
            'html'      => "<a href='".$request->root()."/password/reset/".$token."'>Click</a> to reset password!",
            'x-smtpapi' => json_encode($js),
          );

        $request =  $url.'api/mail.send.json';

        // Generate curl request
        $session = curl_init($request);
        // Tell PHP not to use SSLv3 (instead opting for TLS)
        curl_setopt($session, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1_2);
        curl_setopt($session, CURLOPT_HTTPHEADER, array('Authorization: Bearer ' . $sendgrid_apikey));
        // Tell curl to use HTTP POST
        curl_setopt ($session, CURLOPT_POST, true);
        // Tell curl that this is the body of the POST
        curl_setopt ($session, CURLOPT_POSTFIELDS, $params);
        // Tell curl not to return headers, but do return the response
        curl_setopt($session, CURLOPT_HEADER, false);
        curl_setopt($session, CURLOPT_RETURNTRANSFER, true);

        // obtain response
        $response = curl_exec($session);
        curl_close($session);

        // print everything outl
        print_r($response);
        //return redirect('/login');
    }
}
