<?php

namespace App\Http\Controllers;

use App\Info;
use App\User;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use SendGrid;

class AdminController extends Controller
{

    private $offset = 50;
    private $stats;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    public function updateUsers($id = null, Request $request) {

        if(is_numeric($id) && $id>0) {

            $user = User::find($id);
//            $validator = Validator::make($request->all(),$user->rules);

//            if ($validator->fails()) {
//                return redirect()->back()->withErrors($validator);
//            }

            $user->update($request->except('_token'));

            $flag = $request->input('flag');
            if($flag == 10){
                $this->sendKYCMail($request, $user);
            }

            return view('admin.edit.users', ['user' => $user]);

        } else {

            return redirect()->back();

        }

    }

    public function editUsers($id = null) {

        if(is_numeric($id) && $id>0) {

            return view('admin.edit.users', ['user' => User::find($id)]);

        } else {

            return redirect()->back();

        }

    }

    public function searchUsers(Request $request) {

        if($request->input("by") != "" && $request->input("search_text") != "") {

            return $this->users(1, User::where($request->input("by"), "=", $request->input("search_text"))->get());

        } else {

            return redirect()->back();

        }

    }
    /**
     * Show users admin view.
     *
     * @return \Illuminate\Http\Response
     */
    public function users($page = 1, $users = null)
    {
        if ($users != null) {

            return view('admin.users', ['users' => $users]);

        }

        

        return view('admin.users', ['users' => User::take($this->offset)->skip($this->getOffset($page))->get(), 'page' => $page]);
    }
    
    
    public function deleteUser($id = null) {
        
        if(is_numeric($id) && $id>0) {
            
            User::where("id", "=", $id)->delete();
            
        }
        
        return redirect('/admin');
        
    }

    public function updateInfo($id = null, Request $request) {

        if(is_numeric($id) && $id>0) {

            $info = Info::find($id);
            $validator = Validator::make($request->all(),$info->rules);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator);
            }

            $info->update($request->except('_token'));

            return view('admin.edit.info', ['info' => $info]);

        } else {

            return redirect()->back();

        }

    }

    public function editInfo($id = null) {

        if(is_numeric($id) && $id>0) {

            return view('admin.edit.info', ['info' => Info::find($id)]);

        } else {

            return redirect()->back();

        }

    }

    public function searchInfo(Request $request) {

        if($request->input("by") != "" && $request->input("search_text") != "") {

            return $this->info(1, Info::where($request->input("by"), "=", $request->input("search_text"))->get());

        } else {

            return redirect()->back();

        }

    }
    /**
     * Show info admin view.
     *
     * @return \Illuminate\Http\Response
     */
    public function info($page = 1, $info = null)
    {

        if ($info != null) {

            return view('admin.info', ['info' => $info]);

        }

        

        return view('admin.info', ['info' => Info::take($this->offset)->skip($this->getOffset($page))->get(), 'page' => $page]);
    }

    private function getOffset($page) {

        if($page != null && is_numeric($page)) {
            return (($page * $this->offset)-$this->offset);
        }

        return 0;
    }

    public function registratedVerify($token = null) {
        $token = base64_decode($token);
        $token_array = explode("###", $token);
        $email = $token_array[1];
        $id = $token_array[3];

        $user = User::find($id);
        if($user->email == $email){
            $user->update(['flag' => '0']);  
        }
        return redirect('/')->with('verify', 1);
    }

    protected function sendKYCMail(Request $request, $user)
    { 

        $tomail = $user->email;

        $sendgrid_apikey = getenv('SENDGRID_API_KEY');
        $sendgrid = new SendGrid($sendgrid_apikey);
        $url = 'https://api.sendgrid.com/';
        $pass = $sendgrid_apikey;
        $template_id = '';
        $js = array(
          'sub' => array(':name' => array('Welcome'))
        );
        
        $html = "Congratulations, ".$user->name." you have passed KYC verification, you may now contribute to our project.";
        $params = array(
            'to'        => $tomail,
            'toname'    => "Example User",
            'from'      => "KalleVinter@outlook.com",
            'fromname'  => "Your Name",
            'subject'   => "SendGrid Test",
            'text'      => "",
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
        //return 1;
    }

}
