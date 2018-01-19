<?php

namespace App\Http\Controllers\Auth;

use App\User;
use App\Currency;

use Cookie;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Http\Request;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Str;
use SendGrid;

class RegisterController extends Controller
{
    
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/registrated';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'eth-wallet' => 'required|string|size:42|unique:users,eth_wallet',
        ]);
        
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {

        $referral_id = 0;

        if(Cookie::get('referral') != null) {
           $referral_id = Cookie::get('referral');
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'eth_wallet' => $data['eth-wallet'],
            'flag' => 6,
            'info_id' => 0,
            'password' => Hash::make($data['password']),
            'referral_id' => $referral_id,
        ]);

        return $user;
    }

    protected function register(Request $request)
    {
        $this->validator($request->all())->validate();

        event(new Registered($user = $this->create($request->all())));

        $this->sendVerifyMail($request, $user);
        return back()->with('verify', 1);
                        
    }

    protected function sendVerifyMail(Request $request, $user)
    { 

        $tomail = $request->input('email');

        $sendgrid_apikey = getenv('SENDGRID_API_KEY');
        $sendgrid = new SendGrid($sendgrid_apikey);
        $url = 'https://api.sendgrid.com/';
        $pass = $sendgrid_apikey;
        $template_id = '';
        $js = array(
          'sub' => array(':name' => array('Welcome'))
        );
        $token_str = Str::random(60)."###".$user->email."###".$user->password."###".$user->id."###".Str::random(60);
        
        $token = base64_encode($token_str);
        $html = "<p>Welcome</p>
        <p>Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet. Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua. At vero eos et accusam et justo duo dolores et ea rebum. Stet clita kasd gubergren, no sea takimata sanctus est Lorem ipsum dolor sit amet.</p><a href='".$request->root()."/registrated/".$token."'>Click</a> to verify!";
        $params = array(
            'to'        => $tomail,
            'toname'    => "Example User",
            'from'      => "KalleVinter@outlook.com",
            'fromname'  => "Your Name",
            'subject'   => "SendGrid Test",
            'text'      => "I'm text!",
            'html'      => $html,
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

        // print everything out
        //print_r($response);
        return 1;
    }
}