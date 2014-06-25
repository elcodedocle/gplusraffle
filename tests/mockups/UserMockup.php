<?php
class UserMockup {

    public $userIdMockup;

    private $client_id = '';
    private $client_secret = '';
    private $redirect_uri = '';
    private $scopes = 'https://www.googleapis.com/auth/plus.login';
    private $client;
    private $token;
    private $debug;
    public function requestUserId($client = null){
    
        if ($client === null) { 
            //$client = $this->client; 
        }
        
        return $this->userIdMockup;
    
    }
    public function authenticate($client = null, $code = null, $token = null){
    
        if ($client === null) { $client = $this->client; }
        if ($token===null){ $token = $this->token; }
        
        if ($token!==null){
            $client->$token = $token;
        }
        if (isset($code)&&($token===null||(isset($client->expiredToken))&&$client->expiredToken)) {
            $client->token = '{"access_token":"mockupToken","token_type":"Bearer","expires_in":3600,"id_token":"mockupIdToken","created":'.date_timestamp_get(date_create()).'}';
            $token = $client->token;
        }
    
        $this->token = $token;
        if (isset($token) && $token && (!isset($client->expiredToken)||!$client->expiredToken)) {
            return array('access_token'=>$token);
        } else {
            return array('authUrl'=>'http://mockupauth.url');
        }
    
    }
    public function getAuthenticationWebView($authUrl,$debugData = null){
        if ($debugData){
            echo "called getAuthenticationView with parameter '{$authUrl}'";
        }
        return $authUrl;
    }
    public function __construct(
        $client_id,
        $client_secret,
        $redirect_uri,
        $token = null,
        $code = null,
        $scopes=null,
        $client = null,
        $debug = false
    ){
    
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
        $this->redirect_uri = $redirect_uri;
        $this->code = $code;
        $this->token = $token;
    
    
        if ($scopes!==null) { $this->scopes = $scopes; }
    
        $this->debug = $debug;
    
        if ($client === null) { 
            $this->client = new stdClass();
            $this->client->token = $token;
            $this->client->client_id = $client_id;
            $this->client->client_secret = $client_secret;
            $this->client->redirect_uri = $redirect_uri;
        }
    
    }
}