<?php
namespace MiniOrange\Classes\Actions;

use Illuminate\Support\Facades\Session;
use MiniOrange\Helper\DB;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Routing\Controller;
use Illuminate\Foundation\Auth\User;
use MiniOrange\Helper\Lib\AESEncryption;
use MiniOrange\Helper\PluginSettings;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthFacadeController extends Controller
{

    use AuthenticatesUsers;

    protected $redirectTo = '/start';

    public $mailid = '';

    public $name = '';

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
        $this->mailid = urldecode(AESEncryption::decrypt_data($encrypted_mail, "secret"));
        $this->name = urldecode(AESEncryption::decrypt_data($encrypted_name, "secret"));
        if ($this->mailid == '')
            return redirect('');
        $creds = array(
            '_token' => csrf_token(),
            'remember' => 'on',
            'email' => $this->mailid,
            'name' => $this->name
        );
        $request->merge($creds);
        $this->login($request);
        return redirect($pluginSettings->getRelayStateUrl());
    }

    public function login(Request $request)
    {

        // If the class is using the ThrottlesLogins trait, we can automatically throttle
        // the login attempts for this application. We'll key this by the username and
        // the IP address of the client making these requests into this application.
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }

        if ($this->attemptLogin($request)) {
            return $this->sendLoginResponse($request);
        }

        // If the login attempt was unsuccessful we will increment the number of attempts
        // to login and redirect the user back to the login form. Of course, when this
        // user surpasses their maximum number of attempts they will get locked out.
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }

    protected function credentials(Request $request)
    {
        return $request->only($this->username());
    }

    protected function attemptLogin(Request $request)
    {
        /*
         * return $this->guard()->attempt(
         * $this->credentials($request), $request->filled('remember')
         * );
         */
        if(!isset($_SESSION))
            session_start();
        $user = User::where('email', $request['email'])->first();
        if ($user == null) { // Create User if not existing
            $user = new User();
            $user->email = $request['email'];
            $user->name = $request['name'];
            $user->password = Hash::make(Str::random(8));
            try{$user->save();}
            catch(\PDOException $e){
                if($e->getCode() == '42S22')
                    echo "<b>".$e->getCode()." : Database > Table > Column not found.</b> It seems your <b>Users table</b> does not have column(s) <b>".implode(", ",array_keys($custom))."</b> as mapped in <a href=/setup.php>Custom Attribute Mapping</a>. Please check your <a href=/setup.php>Custom Attribute Mapping</a> and <b>Users table</b>";
                exit;
            }
        }
        $id = $user->id;
        return $this->guard()->loginUsingId($id, True);
    }
}

