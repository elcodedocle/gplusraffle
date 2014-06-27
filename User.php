<?php
/**
 * gplusraffle - Google API PHP OAuth 2.0 and FusionTables client based raffle 
 * management system
 * 
 * User class file
 * 
 * @package gplusraffle
 * @copyright Gael Abadin 2014
 * @license MIT Expat
 * @version v0.1
 * 
 */
    
/**
 * User class
 * 
 * @package gplusraffle - Google API PHP OAuth 2.0 and FusionTables client 
 * based raffle management system
 * @copyright Gael Abadin 2014
 * @license MIT Expat
 * @version v0.1.4-beta
 * 
 * This class handles user authentications.
 * 
 */
class User {
    /**
     * @var string $client_id
     */
    private $client_id = '';
    /**
     * @var string $client_secret
     */
    private $client_secret = '';
    /**
     * @var string $redirect_uri
     */
    private $redirect_uri = '';
    /**
     * @var null|string $scopes
     */
    private $scopes = 'https://www.googleapis.com/auth/plus.login';
    /**
     * @var Google_Client $client
     */
    private $client;
    /**
     * @var $token
     */
    private $token;
    /**
     * @var bool $debug
     */
    private $debug;



    /**
     * Requests the google plus id of an user
     *
     * @param $client null|Google_Client The client
     */
    public function requestUserId($client = null){
    
        if ($client === null) { $client = $this->client; }
    
        $plusService = new Google_Service_Plus($client);
        $person = $plusService->people->get('me');
        return $person->id;
    
    }
    
    /**
     * Returns an OAuth 2 token authenticating a client through a code or an 
     * authentication URL to get one
     * 
     * @param null $client
     * @param null $code
     * @param null $token
     * @return array
     * @throws Exception
     */
    public function authenticate($client = null, $code = null, $token = null){

        if ($client === null) { $client = $this->client; }
        if ($token===null){ $token = $this->token; }
        
        if (!($client instanceof Google_Client)){
            throw new Exception('Not an instance of Google_Client', 500);
        }

        /**
         * TODO: Some exception throwing
         */

        if ($token!==null){
            $client->setAccessToken($token);
        } 
        if (isset($code)&&($token===null||$client->isAccessTokenExpired())) {
            $client->authenticate($code);
            $token = $client->getAccessToken();
        }

        $this->token = $token;
        if (isset($token) && $token && !$client->isAccessTokenExpired()) {
            // to be kept for future requests (e.g. on $_SESSION['access_token']) 
            // P.S. Google is the one mixing camel case and underscores, not me
            return array('access_token'=>$token);
        } else {
            // the key will tell us what it is
            return array('authUrl'=>$client->createAuthUrl());
        }
        
    }
    /**
     * @param string $authUrl
     * @param mixed $debugData
     * @return string html user login page
     */
    public function getAuthenticationWebView($authUrl,$debugData = null){
        ob_start();
?><!doctype html>
<html>
<head>
    <title>
        google plus raffle app user login
    </title>
    <link 
        href='vendor/google/apiclient/examples/styles/style.css' 
        rel='stylesheet' 
        type='text/css' 
    />
</head>
<body>
<div class="box">
    <div class="request">
        <?php if (isset($authUrl)): ?>
            <a class='login' href='<?php echo $authUrl; ?>'>Connect Me!</a>
        <?php else: ?>
            <a class='logout' href='?logout'>Logout</a>
        <?php endif ?>
    </div>

    <?php if (isset($debugData)&&$this->debug): ?>
        <div class="data">
            <?php var_export($debugData); ?>
        </div>
    <?php endif ?>
</div>
</body>
</html>
<?php
        $out = ob_get_contents();
        ob_end_clean();
        return $out;
    }
    
    /**
     * Sets the required params for authentication 
     * 
     * @param string $client_id our app's client id
     * @param string $client_secret our app's client secret 
     * @param string $redirect_uri where will the auth server send the client 
     * after generating an authorization code to authenticate
     * @param null|string $token token obtained when a code is authenticated
     * @param null|string $code response code to authenticate
     * @param null|array $scopes array of strings with OAuth2/google scopes 
     * to requested
     * @param null|Google_Client $client
     * @param bool $debug defaults to false
     */
    public function __construct(
        $client_id,
        $client_secret,
        $redirect_uri,
        $token = null,
        $code = null,
        $scopes = null,
        $client = null,
        $debug = null 
    ){
        
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
        $this->redirect_uri = $redirect_uri;
        $this->code = $code;
        $this->token = $token;


        if ($scopes!==null) { $this->scopes = $scopes; }

        if (is_bool($this->debug)){
            $this->debug = $debug;
        } else {
            $this->debug = false;
        }

        if ($client === null) { 
            
            $this->client = new Google_Client(); 

            $this->client->setClientId($this->client_id);
            $this->client->setClientSecret($this->client_secret);
            $this->client->setRedirectUri($this->redirect_uri);
            if ($token !== null){
                $this->client->setAccessToken($token);
            }
            $this->client->setScopes($this->scopes);
        
        }
        
    }
}