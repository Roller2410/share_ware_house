<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use SendGrid;

class ForgotPasswordController extends Controller
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

    public function sendResetLinkEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email'
        ]);

        if( ! $validator->fails() )
        {
            if( $user = User::where('email', $request->input('email') )->first() )
            {
                $token = str_random(64);
                DB::table('password_resets')->where('email', '=', $user->email)->delete();
                DB::table(config('auth.passwords.users.table'))->insert([
                    'email' => $user->email,
                    'token' => Hash::make($token)
                ]);
                
                $this->sendgridmail($request, $token, $user->email);


                return redirect('/login');
            }
        }

        return redirect()->back()->withErrors(['email' => trans(Password::INVALID_USER)]);
    }

    public function sendgridmail(Request $request, $token, $email){
        
        $sendgrid_apikey = getenv('SENDGRID_API_KEY');
        $sendgrid = new SendGrid($sendgrid_apikey);
        $url = 'https://api.sendgrid.com/';
        $pass = $sendgrid_apikey;
        $template_id = '';
        $js = array(
          'sub' => array(':name' => array('Elmer'))
        );
        
        $params = array(
            'to'        => $email,
            'toname'    => "Example User",
            'from'      => "",
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
        //print_r($response);
        //return redirect('/login');
        return 1;
    }
}
