<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use App\User;
use App\Http\Controllers\EmailController;

use Mail;

use App;
use Config;

class HomeController extends Controller
{
 
    /**
     * EmailController service instance.
     *
     * @var EmailController
     */
    private $imapClient;
    
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(EmailController $imapClient)
    {
        $this->middleware('auth');
        
        $this->imapClient = $imapClient;        
    }

    /**
     * Show mail list.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = User::find(Auth::user()->id);        
        $this->imapClient->init(['username'  => $user->email,'password'  => $user->email_pw,]);
        
        $folders = $this->imapClient->getFolders();        
        $curFolder = $request->get('folder') ? $request->get('folder') : (!empty($folders) ? $folders[0]['path'] : '');        
        $messages = $this->imapClient->getMessageList($curFolder);
        
        return view('home', compact('curFolder' , 'folders', 'messages'));
           
    }
    
    /**
     * Show current mail.
     *
     * @return \Illuminate\Http\Response
     */
    public function message(Request $request)
    {
        $user = User::find(Auth::user()->id);        
        $this->imapClient->init(['username'  => $user->email,'password'  => $user->email_pw,]);
        
        $folders = $this->imapClient->getFolders();                        
        $message = $this->imapClient->getMessage($request->get('folder'),$request->get('uid'));        
        
        return view('message', compact('folders', 'message'));
           
    }
    
    /**
     * Send new mail.
     *
     * @return \Illuminate\Http\Response
     */
    
    public function sendMessage(Request $request)
    {
        $user = User::find(Auth::user()->id);        
        $mesForm = $request->all();
        
        extract(Config::get('mail'));
	$transport = (new \Swift_SmtpTransport('smtp.ukr.net', 465))
                       ->setUsername($user->email)
                       ->setPassword($user->email_pw)
                       ->setEncryption('ssl');

	\Mail::setSwiftMailer(new \Swift_Mailer($transport));
        
        Mail::raw($mesForm['body'], function($message) use($user, $mesForm) {
            $message->to($mesForm['to'])->from($user->email)->subject($mesForm['subject']);                                        
        });
        
        $this->imapClient->init(['username'  => $user->email,'password'  => $user->email_pw,]);
        
        $this->imapClient->appendMessage($user, $mesForm);      
                        
        return response()->json('ok');    
           
    }
    
    /**
     * Delete mail.
     *
     * @return \Illuminate\Http\Response
     */
    
    public function deleteMessage(Request $request)
    {
        $user = User::find(Auth::user()->id);        
        
        $this->imapClient->init(['username'  => $user->email,'password'  => $user->email_pw,]);
        
        $this->imapClient->deleteMessage($request->get('folder'),$request->get('uid'));      
                        
        return redirect('/?folder='.$request->get('folder'));
           
    }
       
}
