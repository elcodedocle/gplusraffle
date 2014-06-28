<?php
/**
 * gplusraffle - Google API PHP OAuth 2.0 and FusionTables client based raffle 
 * management system 
 * 
 * Main Controller Class
 * 
 * Parses the actions and parameters calling the appropriate controller, 
 * handling dependencies injection
 * 
 * @package gplusraffle
 * @copyright Gael Abadin 2014
 * @license MIT Expat
 * @version v0.1.4-beta
 * 
 */
use synapp\info\tools\uuid\uuid;

class MainController{

    /**
     * @var array $debug
     */
    private $debug;
    /**
     * @var array $webapp
     */
    private $webapp;
    /**
     * @var array $client_id
     */
    private $client_id;
    /**
     * @var array $client_secret
     */
    private $client_secret;
    /**
     * @var array $redirect_uri
     */
    private $redirect_uri;
    /**
     * @var array $admin_redirect_uri
     */
    private $admin_redirect_uri;
    /**
     * @var array $access_token
     */
    private $access_token;
    /**
     * @var array $user
     */
    private $user;
    /**
     * @var array $adminDAO
     */
    private $adminDAO;
    /**
     * @var array $admin
     */
    private $admin;
    /**
     * @var array $raffleDAO
     */
    private $raffleDAO;
    /**
     * @var array $raffle
     */
    private $raffle;

    /**
     * @return array
     */
    public function getAccessToken()
    {
        return $this->access_token;
    }
    /**
     * @var array $request
     */
    private $request;
    /**
     * @var float $startTime
     */
    private $startTime;

    /**
     * Processes an action on admin collection.
     *
     * @param null|array $request
     * @throws Exception
     * @return array
     */
    private function processAdminAction($request = null){
        
        if ($request === null){
            $request = $this->request;
        }
        
        $out = array();
        
        $this->adminDAO = isset($this->adminDAO)?$this->adminDAO:new AdminDAO(
            $this->client_id,
            $this->client_secret,
            $this->admin_redirect_uri,
            null,
            null,
            null,
            null,
            null,
            $this->debug
        );
        $this->admin = isset($this->admin)?$this->admin:new Admin($this->adminDAO);
        if ($request['action']!=='login'){
            if (isset($this->access_token)){
                $authResponse = $this->adminDAO->authenticate(
                    null,
                    null,
                    $this->access_token
                );
                if (isset($authResponse['authUrl'])){
                    $this->access_token = null;
                    if ($request['action']!=='logout'){
                        throw new Exception(
                            'Unauthenticated request.',
                            401
                        );
                    }
                }
                if (isset($authResponse['access_token'])){
                    $this->access_token = $authResponse['access_token'];
                }
            } else {
                throw new Exception(
                    'Unauthenticated request.',
                    401
                );
            }
        }
        switch ($request['action']){
            case 'login':
                $code = isset($request['code'])?
                    $request['code']:
                    null;
                $authResponse = $this->adminDAO->authenticate(
                    null,
                    $code,
                    $this->access_token
                );
                if (isset($authResponse['authUrl'])){
                    $out['html'] = $this->admin->getAuthenticationWebView(
                        $authResponse['authUrl']
                    );
                } else if (isset($authResponse['access_token'])){
                    $this->access_token = $authResponse['access_token'];
                }
                break;
            case 'logout':
                $this->access_token = null;
                break;
            case 'install':
                $this->admin->install();
                break;
            case 'uninstall':
                $this->admin->uninstall();
                break;
            default: throw new Exception(
                'The request does not contain a valid action.',
                404
            );
        }
        
        return $out;
    }

    /**
     * Processes an action on user collection.
     *
     * @param null|array $request
     * @throws Exception
     * @return array
     */
    private function processUserAction($request = null){

        if ($request === null){
            $request = $this->request;
        }
        
        $out = array();

        if (!isset($this->access_token)&&$request['action']!=='login'&&$request['action']!=='logout'){
            throw new Exception('Unauthenticated request.', 401);
        }
        switch ($request['action']){
            case 'login':
                $this->user = isset($this->user)?$this->user:new User(
                    $this->client_id,
                    $this->client_secret,
                    $this->redirect_uri,
                    isset($this->access_token)?$this->access_token:null,
                    null,
                    null,
                    null,
                    $this->debug
                );
                $authResponse = $this->user->authenticate(
                    null,
                    isset($request['code'])?$request['code']:null,
                    isset($this->access_token)?$this->access_token:null
                );
                if (isset($authResponse['authUrl'])){
                    $out['html'] = $this->user->getAuthenticationWebView(
                        $authResponse['authUrl']
                    );
                } else if (isset($authResponse['access_token'])){
                    $this->access_token = $authResponse['access_token'];
                    $out['loginSuccess'] = true;
                }
                break;
            case 'logout':
                $this->access_token = null;
                $out['logoutSuccess'] = true;
                break;
            default: throw new Exception(
                'The request does not contain a valid action.',
                404
            );
        }
        
        return $out;
    }

    /**
     * Gets the filter array from the $request array
     *
     * This array is required to perform a list action over the raffle
     * collection.
     *
     * @param string $userId
     * @param boolean $isAdmin defaults to false
     * @param null $request
     * @throws Exception
     * @return array
     */
    private function getListRaffleFilterArray($userId, $isAdmin = false, $request = null){

        if ($request === null){
            $request = $this->request;
        }
        
        $filterArray = array();
        $filterArray['privacy'] = array(
            'value'=>'public',
        );
        if (isset($request['status'])&&$request['status']!==''){
            switch ($request['status']){
                case 'open':
                    $filterArray ['status'] = array(
                        'value'=>'open',
                    );
                    break;
                case 'closed':
                    $filterArray['status'] = array(
                        'value'=>'closed',
                    );
                    break;
                case 'raffled':
                    $filterArray['status'] = array(
                        'value'=>'raffled',
                    );
                    break;
                default:
                    throw new Exception(
                        "Invalid status filter.",
                        400
                    );
            }
        }
        $accessTestIds = array();
        if (isset($request['userid'])&&$request['userid']!==''){ 
            $accessTestIds['participantid'] = $request['userid']; 
        }
        if (isset($request['creatorid'])&&$request['creatorid']!==''){
            $accessTestIds['creatorid'] = $request['creatorid']; 
        }
        foreach($accessTestIds as $fieldName=>$id){
            switch ($id){
                case 'me':
                case $userId:
                    $filterArray[$fieldName] = array(
                        'value'=>$userId,
                    );
                    break;
                default:
                    if ($isAdmin){
                        $filterArray[$fieldName] = array(
                            'value'=>$userId,
                        );
                    } else {
                        throw new Exception(
                            'Access to other users data allowed only to admin',
                            403
                        );
                    }
            }
        }
        if (isset($request['raffleid'])&&$request['raffleid']!==''){
            $filterArray ['raffleid'] = array(
                'value'=>$request['raffleid'],
            );
        }
        return $filterArray;
    }

    /**
     * Processes an action on raffle collection.
     *
     * @param null|array $request
     * @throws Exception
     * @return array
     */
    private function processRaffleAction($request = null){

        if ($request === null){
            $request = $this->request;
        }
        
        $out = array();
        $out['subtitle'] = _('(Listing raffles)');
        
        $data = null;
        
        //check login
        if (!isset($this->access_token)){
            throw new Exception('Unauthenticated request.', 401);
        }
        $this->adminDAO = isset($this->adminDAO)?$this->adminDAO:new AdminDAO(
            $this->client_id,
            $this->client_secret,
            $this->admin_redirect_uri,
            null,
            null,
            null,
            null,
            null,
            $this->debug
        );
        $authResponse = $this->adminDAO->authenticate(
            null,
            null,
            $this->adminDAO->getAccessToken()
        );
        if (isset($authResponse['authUrl'])){
            throw new Exception(
                "Auth error: Invalid admin access token.",
                500
            );
        }
        $adminClient = $this->adminDAO->getClient();
        $tableIds = $this->adminDAO->getTableIds();
        $adminId = $this->adminDAO->getAdminId();
        $this->user = isset($this->user)?$this->user:new User(
            $this->client_id,
            $this->client_secret,
            $this->redirect_uri,
            $this->access_token,
            null,
            null,
            null,
            $this->debug
        );
        $authResponse = $this->user->authenticate(
            null,
            null,
            $this->access_token
        );
        if (isset($authResponse['authUrl'])){
            if (!$this->webapp){
                throw new Exception(
                    "Authentication required (token found but expired/revoked).",
                    401
                );
            } else {
                return $this->processUserAction(
                    array('collection'=>'user','action'=>'logout')
                );
            }
        }
        $userId = $this->user->requestUserId();
        $isAdmin = ($adminId===$userId)?true:false;
        $this->raffleDAO = isset($this->raffleDAO)?$this->raffleDAO:new RaffleDAO($tableIds,null,$adminClient,$this->debug);
        $this->raffle = isset($this->raffle)?$this->raffle:new Raffle($this->raffleDAO, $userId, $isAdmin);
        
        $request['raffleid'] = isset($request['raffleid'])?
            $request['raffleid']:
            (isset($request['resource'])?
                trim($request['resource'],'/'):
                null
            );
        
        switch (isset($request['action'])?$request['action']:''){
            
            case 'list':
                $filterArray = $this->getListRaffleFilterArray(
                    $userId, 
                    $isAdmin, 
                    $request
                );
                $data = $this->raffle->getList($filterArray);
                break;

            case 'create':
                if (
                    !isset($request['description'])
                ){
                    throw new Exception(
                        'Must specify a raffle description',
                        400
                    );
                }
                $data = $this->raffle->create($request['description'], $userId);
                break;
            case 'delete':
                if (
                    !isset($request['raffleid'])
                    ||!uuid::is_valid($request['raffleid'])
                ){
                    throw new Exception(
                        'Must specify a raffleid to delete',
                        400
                    );
                }
                $data = $this->raffle->delete(
                    $request['raffleid']
                );
                break;
            case 'join':
                if (
                    !isset($request['raffleid'])
                    ||!uuid::is_valid($request['raffleid'])
                ){
                    throw new Exception(
                        'Must specify a raffleid to join',
                        400
                    );
                }
                $data = $this->raffle->join(
                    $request['raffleid'],
                    isset($request['comment'])?$request['comment']:'',
                    $userId
                );
                break;
            case 'leave':
                if (
                    !isset($request['raffleid'])
                    ||!uuid::is_valid($request['raffleid'])
                ){
                    throw new Exception(
                        'Must specify a raffleid to leave',
                        400
                    );
                }
                $data = $this->raffle->leave(
                    $request['raffleid'],
                    $userId
                );
                break;
            case 'check':
                if (
                    !isset($request['raffleid'])
                    ||!uuid::is_valid($request['raffleid'])
                ){
                    throw new Exception(
                        'Must specify a raffleid to check',
                        400
                    );
                }
                $data = $this->raffle->check($request['raffleid']);
                break;
            case 'open':
                if (
                    !isset($request['raffleid'])
                    ||!uuid::is_valid($request['raffleid'])
                ){
                    throw new Exception(
                        'Must specify a raffleid to open',
                        400
                    );
                }
                $data = $this->raffle->updateStatus(
                    $request['raffleid'],
                    'open'
                );
                break;
            case 'close':
                if (
                    !isset($request['raffleid'])
                    ||!uuid::is_valid($request['raffleid'])
                ){
                    throw new Exception(
                        'Must specify a raffleid to close',
                        400
                    );
                }
                $data = $this->raffle->updateStatus(
                    $request['raffleid'],
                    'closed'
                );
                break;
            case 'raffle':
                if (
                    !isset($request['raffleid'])
                    ||!uuid::is_valid($request['raffleid'])
                ){
                    throw new Exception(
                        'Must specify a raffleid to raffle',
                        400
                    );
                }
                $data = $this->raffle->raffle(
                    $request['raffleid'],
                    isset($request['limit']) && 
                    intval($request['limit']) > 0?
                        intval($request['limit']):
                        null
                );
                break;
            default: throw new Exception(
                'The request does not contain a valid action.',
                404
            );
            
        }
        
        $out['data'] = $data;
        return $out;
        
    }

    /**
     * Processes a request, invoking the required action on the specified
     * collection
     *
     * @throws Exception
     * @return array the output array
     */
    public function process(){
        
        if (!isset($this->request['collection'])){
            throw new Exception(
                'The request does not contain a collection.', 
                404
            );
        }
        if (!isset($this->request['action'])){
            throw new Exception(
                'The request does not contain an action.',
                404
            );
        }
        switch ($this->request['collection']){
                case 'raffle':
                    $out = $this->processRaffleAction();
                    break;
                case 'user':
                    $out = $this->processUserAction();
                    break;
                case 'admin':
                    $out = $this->processAdminAction();
                    break;
                default: throw new Exception(
                    'The request does not contain a valid collection.', 
                    404
                );
        }
        $out['execTime']=(microtime(true)-$this->startTime);
        
        return $out;
        
    }

    /**
     * @param array $request
     * @param null|string $client_id
     * @param null|string $client_secret
     * @param null|string $redirect_uri
     * @param null|string $admin_redirect_uri
     * @param null|string $access_token
     * @param null|float $startTime
     * @param null|User $user
     * @param null|AdminDAO $adminDAO
     * @param null|Admin $admin
     * @param null|RaffleDAO $raffleDAO
     * @param null|Raffle $raffle
     * @param bool $webapp
     * @param bool $debug
     */
    public function __construct(
        $request,
        $client_id = null,
        $client_secret = null,
        $redirect_uri = null,
        $admin_redirect_uri = null,
        $access_token = null,
        $startTime = null,
        $user = null,
        $adminDAO = null,
        $admin = null,
        $raffleDAO = null,
        $raffle = null,
        $webapp = false,
        $debug = false
    ){
        
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
        $this->redirect_uri = $redirect_uri;
        $this->admin_redirect_uri = $admin_redirect_uri;
        $this->request = $request;
        $this->access_token = $access_token;
        $this->startTime = isset($startTime)?$startTime:microtime(true);
        if (is_bool($webapp)) { $this->webapp = $webapp; } else { $this->webapp = false; }
        if (is_bool($debug)) { $this->debug = $debug; } else { $this->debug = false; }
        
    }

}