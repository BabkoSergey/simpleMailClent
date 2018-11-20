<?php namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Webklex\IMAP\Client;


class EmailController extends Controller
{
    /**
     * Phoneveryfy model instance.
     *
     * @var Phoneveryfy
     */
    private $phoneveryfy;
    
    /**
     * Twilio instance.
     *
     * @var $twilioClient
     */    
    private $oClient;
            
    /**
     * EmailController constructor.     *
     */
    public function __construct()
    {   
        $this->oClient = new Client([
            'host'          => 'imap.ukr.net',
            'port'          => 993,
            'encryption'    => 'ssl',
            'validate_cert' => true,
            'username'      => '',
            'password'      => '',
            'protocol'      => 'imap'
        ]);
    }
    
    public function checkEmail(Request $request)
    {          
        $this->oClient->username = $request->get('email');
        $this->oClient->password = $request->get('password');
        
        try {
            $this->oClient->connect();   
            return ['success'=> 'ok'];
        } catch (\Exception $e) {            
            return ['error'=> ['email'=>'Connection rejected by '.$this->oClient->host]];
        }
    }
    
    public function init($params)
    {   
        
        foreach($params as $key=>$value){
            if(isset($this->oClient->$key))
                $this->oClient->$key = $value;
        }
                
        try {
            $this->oClient->connect();   
            return true;
        } catch (\Exception $e) {            
            return false;
        }
    }
    
    public function getFolders()
    {   
        
        $aFolder = $this->oClient->getFolders();
        $folderList = [];    
        foreach($aFolder as $oFolder){                          
            $folderList[] = [
                    'name'   => $oFolder->name,
                    'unseen' => $oFolder->search()->leaveUnread()->setFetchBody(false)->setFetchAttachment(false)->get()->count(),
                    'path'   => substr($oFolder->path, stripos($oFolder->path, "}")+1),
                ];
        }
        
        return $folderList;
    }
    
       
    public function getMessageList($name){
        
        $oFolder = $this->oClient->getFolder($name);
        
        $messagesList = [
            'folder'    => $oFolder->name,
            'messages'  => [],
            'from'      => 'From',
            ];
        
        $aMessage = $oFolder->messages()->all()->get();        
        foreach($aMessage as $oMessage){  
            if($oMessage->getFrom()[0]->mail == $this->oClient->username)
                $messagesList['from'] = 'To';
                        
            $messagesList['messages'][] = [
                'from'          => $oMessage->getFrom()[0]->full,
                'to'            => $oMessage->getTo()[0]->full,
                'subject'       => $oMessage->getSubject(),
                'attachments'   => $oMessage->getAttachments()->count(),
                'date'          => $oMessage->getDate()->toDateTimeString(),
                'body'          => $oMessage->hasHTMLBody() ? mb_strimwidth(strip_tags($oMessage->getHTMLBody()), 0, 25, "...") : mb_strimwidth($oMessage->getTextBody(), 0, 25, "..."),
                'uid'           => $oMessage->getUid(),
            ];                    
        }
        
        return $messagesList;
    }
    
    public function getMessage($folder, $uid){
        
        $oFolder = $this->oClient->getFolder($folder);
        
        $oMessage = $oFolder->getMessage($uid, $oFolder->messages()->all()->get(), null, true, true );            
        $message = [
                'from'          => $oMessage->getFrom()[0]->full,
                'to'            => $oMessage->getTo()[0]->full,
                'subject'       => $oMessage->getSubject(),
                'attachments'   => $oMessage->getAttachments(),
                'date'          => $oMessage->getDate()->toDateTimeString(),
                'body'          => $oMessage->hasHTMLBody() ? strip_tags($oMessage->getHTMLBody()) : $oMessage->getTextBody(),
                'uid'           => $oMessage->getUid(),
            ];
                       
        return $message;
    }
    
    public function appendMessage($user, $mesForm){
        
        $aFolder = $this->oClient->getFolders();                
        $folderOut = '';    
        foreach($aFolder as $oFolder){                   
            if($oFolder->name == 'Outgoing')
                $folderOut = $oFolder->name;
        }
        
        if($folderOut != 'Outgoing'){
            $this->oClient->createFolder('Outgoing');
        }
        
        $oFolder =  $this->oClient->getFolder($folderOut);
        
        try {
            $oFolder->appendMessage("From: ".$user->email."\r\n"."To: ".$mesForm['to']."\r\n"."Subject: ".$mesForm['subject']."\r\n".$mesForm['body']);
            return true;
        } catch (\Exception $e) {            
            return false;
        }
        
        return 'ok';
    }
    
    public function deleteMessage($folder, $uid){
        
        $oFolder = $this->oClient->getFolder($folder);
        
        $oMessage = $oFolder->getMessage($uid, $oFolder->messages()->all()->get(), null, true, true )->delete();        
                       
        return $oMessage;
    }
    
}
