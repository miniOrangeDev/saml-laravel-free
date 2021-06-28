<?php

namespace MiniOrange\Classes\Actions;

use Illuminate\Support\Facades\Session;
use MiniOrange\Helper\DB;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Auth\User;
use MiniOrange\Helper\Lib\AESEncryption;
use MiniOrange\Helper\PluginSettings;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthFacadeController extends Controller
{
    public $mailid = '';
    public $name = '';
    protected $redirectTo = '/home';

    public function __construct()
    {
        $this->middleware('Illuminate\Session\Middleware\StartSession');
        $this->middleware('web');
    }

    public function start()
    {
        $request = request();
        $this->middleware('Illuminate\Session\Middleware\StartSession');
        $this->signin($request);
    }

    public function signin(Request $request)
    {
        $pluginSettings = PluginSettings::getPluginSettings();
        $encrypted_mail = $request->email;
        $encrypted_name = $request->name;
        $this->mailid = urldecode(AESEncryption::decrypt_data($encrypted_mail, "M12K19FV"));
        $this->name = urldecode(AESEncryption::decrypt_data($encrypted_name, "M12K19FV"));
        if ($this->mailid == '')
            return redirect('');
        $creds = array(
            '_token' => csrf_token(),
            'remember' => 'on',
            'email' => $this->mailid,
            'name' => $this->name
        );
        $request->merge($creds);
        return $this->login($request);
    }

    public function login(Request $request)
    {

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        return $this->attemptLogin($request);
        if ($this->attemptLogin($request)) {
            return $this->sendLoginResponse($request);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    protected function attemptLogin(Request $request)
    {
        /*
         * return $this->guard()->attempt(
         * $this->credentials($request), $request->filled('remember')
         * );
         */
        if (!isset($_SESSION))
            session_start();
        $user=User::where('email',$request['email'])->first();if($user==null){if(intval(base64_decode(AESEncryption::decrypt_data(PluginSettings::getPluginSettings()->getAcsSAMLFreeTokenVersion(),'M12K19FV')))<intval(((base64_decode('MTAwMA=='))/100))){$user=new User();$user->email=$request['email'];$user->name=$request['name'];$user->password=Hash::make(Str::random(8));try{$user->save();$value=intval(base64_decode(AESEncryption::decrypt_data(PluginSettings::getPluginSettings()->getAcsSAMLFreeTokenVersion(),'M12K19FV')));$value++;DB::update_option(base64_decode('bW9fc2FtbF9mcmVlX3ZlcnNpb24='),AESEncryption::encrypt_data(base64_encode(intval($value)),'M12K19FV'));}catch(\PDOException $e){dd($e);}}else{echo base64_decode('PGJyPjxocj48aDM+PGI+T25seSAxMCB1c2VycyBhcmUgYWxsb3dlZCB0byBsb2dpbiBpbiB0aGUgZnJlZSB2ZXJzaW9uLiBQbGVhc2UgY29udGFjdCB5b3VyIEFkbWluaXN0cmF0b3IuPC9iPjwvaDM+PGhyPg==');exit;}}$id=$user->id;$user=Auth::login($user,true);$pluginSettings=PluginSettings::getPluginSettings();return redirect($pluginSettings->getSiteBaseUrl());
    }

    protected function credentials(Request $request)
    {
        return $request->only($this->username());
    }
}