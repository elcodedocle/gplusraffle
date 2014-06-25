<?php
/**
 * gplusraffle - Google API PHP OAuth 2.0 and FusionTables client based raffle 
 * management system
 * 
 * Admin Data Access Object class
 * 
 * @package gplusraffle
 * @copyright Gael Abadin 2014
 * @license MIT Expat
 * @version v0.1.0-beta
 * 
 */
require_once 'vendor/google/apiclient/src/Google/Service/Fusiontables.php';
class AdminDAO {

    /**
     * @var bool $debug
     */
    private $debug = false;

    /**
     * @var string $adminId
     */
    private $adminId;

    /**
     * @var string $adminToken
     */
    private $accessToken = null;

    /**
     * Returns the access token for the logged in / admin user
     * 
     * @return string
     */
    public function getAccessToken(){
        
        return $this->accessToken;
        
    }

    /**
     * @var array $tableIds An array of strings containing the fusion table ids
     * where the app data is stored indexed by table names  (raffles, 
     * participants, winners)
     */
    private $tableIds;

    /**
     * @var string $fileName 'adminToken.php' by default. If you change it, 
     * don't forget to include it on .gitignore!
     */
    private $fileName = 'adminConfig.php';

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
     * @var string $client
     */
    private $client;

    /**
     * @var array $tableScheme An array (whose keys define table names) of
     * arrays whose keys define table field names and values define table
     * field types. It defines the schema of Fusion Tables to be set.
     */
    private $tableSchema;

    /**
     * Gets the current admin's Google id
     * 
     * @return string the google account admin id
     */
    public function getAdminId(){
        return $this->adminId;
    }

    /**
     * Gets the current Fusion Table ids (indexed by their names)
     *
     * @return string the admin refresh token.
     */
    public function getTableIds(){
        return $this->tableIds;
    }

    /**
     * sets the google client php api object
     *
     * @param null|string $accessToken
     * @param null $client_id
     * @param null $client_secret
     * @param null $redirect_uri
     * @param null|Google_Client $client If null a new instance will be created
     * using the given parameters (which will be ignored otherwise).
     */
    public function setClient(
        $accessToken = null, 
        $client_id = null, 
        $client_secret = null, 
        $redirect_uri = null, 
        $client = null
    ){

        if ($client !== null){
            $this->client = $client;
        } else {
            
            if ($accessToken === null) { $accessToken = $this->accessToken; }
            if ($client_id === null) { $client_id = $this->client_id; }
            if ($client_secret === null) { 
                $client_secret = $this->client_secret; 
            }
            if ($redirect_uri === null) { 
                $redirect_uri = $this->redirect_uri; 
            }
            
            $this->client = new Google_Client();
            $this->client->setClientId($client_id);
            $this->client->setClientSecret($client_secret);
            $this->client->setRedirectUri($redirect_uri);
            $this->client->setScopes(
                array(
                    'https://www.googleapis.com/auth/plus.login',
                    'https://www.googleapis.com/auth/fusiontables',
                )
            );
            $this->client->setApprovalPrompt('force');
            $this->client->setAccessType('offline');
            if (isset($accessToken)&&$accessToken){
                $this->client->setAccessToken($accessToken);
                if ($this->client->isAccessTokenExpired()){
                    $tokenArray = json_decode($accessToken,true);
                    $this->client->refreshToken($tokenArray['refresh_token']);
                    $this->accessToken = $this->client->getAccessToken();
                }
            }
            
        }
        
    }

    /**
     * returns the google client php api object
     *
     * @return string
     */
    public function getClient(){
        return $this->client;
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
    public function authenticate(
        $client = null, 
        $code = null, 
        $token = null
    ){

        if ($client === null) { $client = $this->client; }

        if (!($client instanceof Google_Client)){
            throw new Exception('Not an instance of Google_Client', 500);
        }

        /**
         * TODO: Some exception throwing! Throw, throw, throw!
         */

        if ($token!==null){
            $client->setAccessToken($token);
        } else if (isset($code)) {
            $client->authenticate($code);
            $token = $client->getAccessToken();
        }
        $this->accessToken = $token;
        // careful here, or any user may end up being identified as admin!
        // (to avoid ungranted access saving token must require positive 
        // admin id match)
        if (isset($token) && $token) {
            if ($client->isAccessTokenExpired()){
                $tokenArray = json_decode($token,true);
                $client->refreshToken($tokenArray['refresh_token']);
                $this->accessToken = $client->getAccessToken();
            }

            // to be kept for future requests (e.g. on $_SESSION['access_token']) 
            // P.S. Google is the one mixing camel case and underscores, not me
            return array('access_token'=>$this->accessToken);
        } else {
            // the key will tell us what it is
            return array('authUrl'=>$client->createAuthUrl());
        }

    }

    /**
     * Requests the google plus id of an user
     * 
     * @param $client null|Google_Client The client
     */
    public function getUserId($client = null){
        
        if ($client === null) { $client = $this->client; }
        
        $plusService = new Google_Service_Plus($client);
        $person = $plusService->people->get('me');
        return $person->id;
        
    }


    /**
     * Reads and the admin and token contained in the specified PHP script and 
     * sets the corresponding properties
     * 
     * @param null|string $fileName the full path and filename to read the 
     * parameters from.
     * @return array|bool an array with the read parameters or false if file 
     * does not exist.
     * @throws Exception on error
     */
    public function readAdminSettings($fileName = null){
        if ($fileName === null) { $fileName = $this->fileName; }
        if (file_exists($fileName)){
            require $fileName;
            if (!isset($settings)||!is_array($settings)){
                throw new Exception(
                    'Wrong AdminDAO settings file format (No $settings array).',
                    500
                );
            }
            if (isset($settings['adminId'])){
                $this->adminId = $settings['adminId'];
            } else {
                throw new Exception(
                    'Wrong AdminDAO settings file format (No $settings["adminId"]).',
                    500
                );
            }
            if (isset($settings['adminToken'])){
                $this->accessToken = $settings['adminToken'];
            } else {
                throw new Exception(
                    'Wrong AdminDAO settings file format (No $settings["adminToken"]).',
                    500
                );
            }
            if (isset($settings['tableIds'])){
                $this->tableIds = $settings['tableIds'];
            }
            return $settings;
        }
        return false;
    }

    /**
     * Unlinks (deletes) the specified PHP file containing the token and id and 
     * sets to null the corresponding properties.
     * 
     * @param null|string $fileName
     */
    public function removeAdminSettings($fileName = null){
        if ($fileName === null) { $fileName = $this->fileName; }
        $this->adminId = null;
        $this->accessToken = null;
        $this->tableIds = null;
        if (file_exists($fileName)){
            unlink($fileName);
        } else {
            error_log('Cannot unlink: File '.$fileName.' does not exist.');
        }
    }

    /**
     * Saves admin settings file
     * 
     * These settings include offline data fusion tables access tokens to the 
     * admin's account. In case the file is leaked the only access gained to 
     * the admin account is writing to any exportable fusion tables, but just 
     * in case you may be safer if you create a google user account for use 
     * only by this web app, so the only info that can be leaked or altered 
     * by gaining access to this admin settings file is the web app's tables, 
     * which only contains info about the raffles and the google account id 
     * of participants (no emails or personal data is stored: an attacker 
     * would need to gain writing access to the app files to be able to 
     * retrieve that info, and if it's more than the basic profile any user 
     * should have to consent to give the extra access permisions to the app) 
     * 
     * @param null|string $adminId
     * @param null|string $adminToken
     * @param null $tableIds
     * @param null|string $fileName
     * @throws Exception
     */
    public function saveAdminSettings(
        $adminId = null, 
        $adminToken = null, 
        $tableIds = null, 
        $fileName = null
    ){
        if ($fileName === null) { $fileName = $this->fileName; }
        if ($adminId === null) { $adminId = $this->adminId; }
        if ($adminToken === null) { $adminToken = $this->accessToken; }
        if ($tableIds === null) { $tableIds = $this->tableIds; }
        if (!isset($adminToken) || $adminToken ==='') {
            throw new Exception('Invalid $adminToken.',500);
        }
        if (!isset($adminId) || $adminId ==='') { 
            $adminId = $this->getUserId();
            if (!isset($adminId) || $adminId ==='') {
                throw new Exception('Invalid $adminId.',500); 
            }
        }
        $settings = array(
            'adminId'=>$adminId,
            'adminToken'=>$adminToken,
        );
        if (isset($tableIds) && is_array($tableIds) && count($tableIds) > 0) {
            $settings['tableIds'] = $tableIds;
        }
        if (($fh = fopen($fileName,'w'))===false) {
            throw new Exception('Cannot open admin DAO file for writing.',500);
        }
        fwrite(
            $fh,
            '<?php'
            . PHP_EOL 
            . '$settings = '
            . var_export(
                $settings
                ,true
            ).';'.PHP_EOL
        );
        fclose($fh);
        $this->adminId = $adminId;
        $this->adminToken = $adminToken;
        $this->tableIds = $tableIds;
    }

    /**
     * Creates the given Schema of fusion tables
     *
     * @param null $client
     * @param null|array $tableSchema tables and structures
     * @param null $debug
     * @throws Exception
     */
    public function createTables(
        $client = null,
        $tableSchema = null,
        $debug = null
    ){
        if ($tableSchema === null){
            $tableSchema = $this->tableSchema;
        }
        if ($debug === null) { $debug = $this->debug; }
        if ($client === null) { $client = $this->client; }
        if (!($client instanceof Google_Client)) { 
            throw new Exception(
                '$client is not an instance of Google_Client.',
                500
            ); 
        }
        $service = new Google_Service_Fusiontables($client);
        $tableIds = array();
        foreach ($tableSchema as $tableName=>$columns){
            $tableColumns = array();
            foreach ($columns as $columnName=>$columnType){
                $column = new Google_Service_Fusiontables_Column();
                $column->setName($columnName);
                $column->setType($columnType);
                $tableColumns[]=$column;
            }
            $table = new Google_Service_Fusiontables_Table();
            $table->setName($tableName);
            $table->setColumns($tableColumns);
            $table->setIsExportable('true');
            try {
                $response = $service->table->insert($table);
                $tableIds[$tableName] = $response->tableId;
                if ($debug){
                    error_log(var_export($tableIds,true));
                }
            } catch (Exception $e) {
                error_log($e->getMessage());
            }
            $this->tableIds = $tableIds;
        }
    }

    /**
     * Although null checks are not performed here, either a client to AdminDAO 
     * or the config parameters to create one must be provided for the class to 
     * function properly in all cases.
     * 
     * @param null $client_id
     * @param null $client_secret
     * @param null $redirect_uri
     * @param null|string $adminId
     * @param null|string $accessToken
     * @param null|string $fileName
     * @param null|array $tableSchema
     * @param null $client
     * @param null $debug
     * @throws Exception
     */
    public function __construct(
        $client_id = null,
        $client_secret = null,
        $redirect_uri = null,
        $adminId = null, 
        $accessToken = null, 
        $fileName = null, 
        $tableSchema = null, 
        $client = null,
        $debug = null
    ){
        if ($debug !== null) { $this->debug = $debug; }
        if ($tableSchema !== null) {
            $this->tableSchema = $tableSchema;
        } else {
            $this->tableSchema = array(
                'raffles' => array(
                    'raffleid'=>'STRING',
                    'raffledescription'=>'STRING',
                    'creatorid'=>'STRING',
                    'created'=>'DATETIME',
                    'privacy'=>'STRING',
                    'status'=>'STRING',
                ),
                'participants' => array(
                    'raffleid'=>'STRING',
                    'participantid'=>'STRING',
                    'comment'=>'STRING',
                    'joined'=>'DATETIME',
                ),
                'winners' => array(
                    'raffleid'=>'STRING',
                    'winnerid'=>'STRING',
                    'raffled'=>'DATETIME',
                ),
            );
        }
        if ($fileName !== null) { $this->fileName = $fileName; } 
        else {
            // If you change it, don't forget to include it on .gitignore!
            $this->fileName = realpath(dirname(__FILE__)).'/adminConfig.php'; 
        }
        // we need this if we don't have a client
        if ($client_id !== null) { $this->client_id = $client_id; }
        if ($client_secret !== null) { $this->client_secret = $client_secret; }
        if ($redirect_uri !== null) { $this->redirect_uri = $redirect_uri; }

        if ($adminId === null || $accessToken===null) { 
            $this->readAdminSettings($this->fileName); 
        } 
        // overrides what we've just set:
        if ($adminId !== null) { $this->adminId = $adminId; }
        if ($accessToken !== null) { $this->accessToken = $accessToken; }
        
        /**
         * 
         * TODO: having a $client !== null may break the app if the $adminId, 
         * $accessToken or $adminRefreshToken are also !== null and don't match
         * the client. The same goes for conflicting config parameters.
         * 
         * Solution: override them with values taken from $client.
         * 
         * The priority then would be $client params override explicit construct 
         * params which override params read from $fileName. Although maybe 
         * it'd be saner that explicit params override all...
         * 
         */
        $this->setClient(
            $accessToken,
            $client_id,
            $client_secret,
            $redirect_uri,
            $client
        );
    }
} 