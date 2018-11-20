<?php namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;

use App\User;
use App\Http\Controllers\EmailController;

class LoginController extends Controller
{
    use AuthenticatesUsers;
            
    /**
     * EmailController service instance.
     *
     * @var EmailController
     */
    private $imapClient;
    
    /**
     * Create a new controller instance.     *     
     */
    public function __construct(EmailController $imapClient)
    {
        $this->middleware('guest', ['except' => 'logout']);
        
        $this->imapClient = $imapClient;
    }
    
     /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function login(Request $request)
    {                   
        $this->validateLogin($request);            
                
        if ($this->hasTooManyLoginAttempts($request)) {
            $this->fireLockoutEvent($request);

            return $this->sendLockoutResponse($request);
        }
        
        $response = $this->imapClient->checkEmail($request);
        
        if(isset($response['error'])){
            $this->incrementLoginAttempts($request);
            
            return $this->sendFailedLoginResponse($request);            
        }else{
            $user = User::where('email',$request->get('email'))->first();
            if(!$user){
                $user = User::forceCreate($this->formatParams($request));
            }
        }
        
        if ($this->attemptLogin($request)) 
            return $this->sendLoginResponse($request);
        
        $this->incrementLoginAttempts($request);

        return $this->sendFailedLoginResponse($request);
    }
    
    private function formatParams(Request $request)
    {
        $formatted = [            
            'name'          => $request->get('email'),
            'email'         => $request->get('email'),            
            'password'      => bcrypt($request->get('password')),
        ];

        return $formatted;
    }
    
    /**
    * Send the response after the user was authenticated.
    * Remove the other sessions of this user
    *
    * @param  \Illuminate\Http\Request  $request
    * @return \Illuminate\Http\Response
    */
        
    protected function sendLoginResponse(Request $request)
    {
        $request->session()->regenerate();
        
        Auth::user()->email_pw = $request->get('password');
        Auth::user()->save();
       
        $this->clearLoginAttempts($request);

        return $this->authenticated($request, $this->guard()->user())
            ?: redirect()->intended($this->redirectPath());
    }
               
    /**
     * Get the needed authorization credentials from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function credentials(Request $request)
    {                
        return $request->only($this->username(), 'password');
    }
    
    /**
     * Validate the user login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return void
     */
    protected function validateLogin(Request $request)
    {
        $this->validate($request, [
            $this->username() => 'required|string',
            'password' => 'required|string',
        ]);
    }
        
}