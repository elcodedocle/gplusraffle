<?php

//AdminDAO Mockup class
class AdminDAOMockup {
    public $settingsMockup;
    public $userIdMockup;
    public $tableIdsMockup;
    private $debug = false;
    private $adminId;
    private $accessToken = null;
    public function getAccessToken(){
        return $this->accessToken;
    }
    private $tableIds;
    private $fileName = 'adminConfig.php';
    private $client_id;
    private $client_secret;
    private $redirect_uri;
    private $client;
    private $tableSchema;
    public function getAdminId(){
        return $this->adminId;
    }
    public function getTableIds(){
        return $this->tableIds;
    }
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
            $this->client = new stdClass();
            $this->client->token = $accessToken;
            $this->client->client_id = $client_id;
            $this->client->client_secret = $client_secret;
            $this->client->redirect_uri = $redirect_uri;
        }
    }
    public function getClient(){
        return $this->client;
    }
    public function authenticate(
        $client = null,
        $code = null,
        $token = null
    ){
        if ($client === null) { $client = $this->client; }
        if ($token!==null){
            $client->token = $token;
        } else if (isset($code)) {
            $client->token = '{"access_token":"mockupToken","token_type":"Bearer","expires_in":3600,"id_token":"mockupIdToken","refresh_token":"mockupRefreshToken","created":'.date_timestamp_get(date_create()).'}';
            $token = isset($client->token)?$client->token:null;
        }
        $this->accessToken = $token;
        if (isset($token) && $token) {
            if (isset($client->expiredToken)&&$client->expiredToken){
                $client->token = '{"access_token":"mockupToken","token_type":"Bearer","expires_in":3600,"id_token":"mockupIdToken","refresh_token":"mockupRefreshToken","created":'.date_timestamp_get(date_create()).'}';
                unset($client->expiredToken);
                $this->accessToken = $client->token;
            }
            return array('access_token'=>$this->accessToken);
        } else {
            return array('authUrl'=>'http://mockupauth.url');
        }
    }
    public function getUserId($client = null){
        if ($client === null) {
            //$client = $this->client; 
        }
        return $this->userIdMockup;
    }
    public function readAdminSettings($fileName = null){
        if ($fileName === null) {
            //$fileName = $this->fileName; 
        }
        if (isset($this->settingsMockup)){
            $settings = $this->settingsMockup;
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
    public function removeAdminSettings($fileName = null){
        if ($fileName === null) { $fileName = $this->fileName; }
        $this->adminId = null;
        $this->accessToken = null;
        $this->tableIds = null;
        if (isset($this->settingsMockup)){
            unset($this->settingsMockup);
        } else {
            error_log('Cannot unlink: File '.$fileName.' does not exist.');
        }
    }
    public function saveAdminSettings(
        $adminId = null,
        $adminToken = null,
        $tableIds = null,
        $fileName = null
    ){
        if ($fileName === null) {
            //$fileName = $this->fileName; 
        }
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
        $this->adminId = $adminId;
        $this->adminToken = $adminToken;
        $this->tableIds = $tableIds;
        $this->settingsMockup = $settings;
    }
    public function createTables(
        $client = null,
        $tableSchema = null,
        $debug = null
    ){
        if ($tableSchema === null){
            $tableSchema = $this->tableSchema;
        }
        if ($debug === null) {
            //$debug = $this->debug; 
        }
        if ($client === null) {
            //$client = $this->client; 
        }
        $tableIds = array();
        foreach ($tableSchema as $tableName=>$columns){
            $tableIds[$tableName] = $this->tableIdsMockup[$tableName];
            $this->tableIds = $tableIds;
        }
    }
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

        $this->setClient(
            $accessToken,
            $client_id,
            $client_secret,
            $redirect_uri,
            $client
        );
    }

}