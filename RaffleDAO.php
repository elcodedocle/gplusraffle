<?php
/**
 * gplusraffle - Google API PHP OAuth 2.0 and FusionTables client based raffle
 * management system
 * 
 * @package gplusraffle
 * @copyright Gael Abadin 2014
 * @license MIT Expat
 * @version 0.1-beta
 * 
 * Raffle Data Access Object class
 */ 
use synapp\info\tools\uuid\uuid;
class RaffleDAO {

    private $tableIds;
    private $fusionTablesService;
    private $debug = false;
    
    private function escape_mysql_mbstring($mbstring){
        $mysqlEscapedMbString = '';
        for($i = 0; $i < strlen($mbstring); ++$i) {
            $char = $mbstring[$i];
            $ord = ord($char);
            if($char !== "'" && $char !== "\"" && $char !== '\\' && $ord >= 32 && $ord <= 126)
                $mysqlEscapedMbString .= $char;
            else
                $mysqlEscapedMbString .= '\\x' . dechex($ord);
        }
        return $mysqlEscapedMbString;
    }

    /**
     * @param \Google_Service_Fusiontables|null $fusionTablesService
     */
    public function setFusionTablesService($fusionTablesService){
        
        $this->fusionTablesService = $fusionTablesService;
        
    }

    /**
     * @param mixed $tableIds
     */
    public function setTableIds($tableIds){
        
        $this->tableIds = $tableIds;
        
    }

    /**
     * Class constructor.
     * 
     * If a Google_Service_Fusiontables is not specified an authenticated 
     * Google_Client $client with fusion tables scope must be provided.
     *
     * @param $tableIds
     * @param null $fusionTablesService
     * @param null $adminClient
     * @param bool $debug
     */
    public function __construct(
        $tableIds, 
        $fusionTablesService = null, 
        $adminClient = null,
        $debug = false
    ){
        $this->tableIds = $tableIds;
        $this->debug = $debug;
        $this->fusionTablesService = 
            ($fusionTablesService === null)?
                (new Google_Service_Fusiontables($adminClient)):
                $fusionTablesService;
    }

    /**
     * Horrible method to select rows from raffles table matching their columns
     * to combinations of certain criteria
     *
     * Column matching criteria: is equal to '=', is not equal to '!='
     * Column combinators: 'AND', 'OR'
     *
     * @param null|string $raffleId
     * @param null|string $description
     * @param null|string $creatorId
     * @param null $participantId
     * @param null $winnerId
     * @param null|string $created
     * @param null|string $privacy
     * @param null|string $status
     * @param string $raffleIdOperator
     * @param string $descriptionOperator
     * @param string $creatorIdOperator
     * @param string $createdOperator
     * @param string $privacyOperator
     * @param string $statusOperator
     * @param string $raffleIdPostOperator
     * @param string $descriptionPostOperator
     * @param string $creatorIdPostOperator
     * @param string $createdPostOperator
     * @param string $privacyPostOperator
     * @param null|string $tableId
     * @param null|Google_Service_Fusiontables $fusionTablesService
     * @param null $debug
     * @throws Exception
     * @return stdClass a simple object containing numerically indexed column
     * names array in 'columns' property and numerically indexed rows array
     * of numerically indexed column values in 'rows' property
     */
    public function getRaffles(
        $raffleId = null,
        $description = null,
        $creatorId = null,
        $participantId = null,
        $winnerId = null,
        $created = null,
        $privacy = null,
        $status = null,
        $raffleIdOperator = null,
        $descriptionOperator = null,
        $creatorIdOperator = null,
        $createdOperator = null,
        $privacyOperator = null,
        $statusOperator = null,
        $raffleIdPostOperator = null,
        $descriptionPostOperator = null,
        $creatorIdPostOperator = null,
        $createdPostOperator = null,
        $privacyPostOperator = null,
        $tableId = null,
        $fusionTablesService = null,
        $debug = null
    ){
        // operators should be '=' or '!='
        // post operators should be 'AND' (Fusion Tables does not support 'OR')
        // (checks are not performed here)

        if ($raffleIdOperator === null){ $raffleIdOperator = '='; }
        if ($descriptionOperator === null){ $descriptionOperator = '='; }
        if ($creatorIdOperator === null){ $creatorIdOperator = '='; }
        if ($createdOperator === null){ $createdOperator = '='; }
        if ($privacyOperator === null){ $privacyOperator = '='; }
        if ($statusOperator === null){ $statusOperator = '='; }
        if ($raffleIdPostOperator === null){ $raffleIdPostOperator = 'AND'; }
        if ($descriptionPostOperator === null){ $descriptionPostOperator = 'AND'; }
        if ($creatorIdPostOperator === null){ $creatorIdPostOperator = 'AND'; }
        if ($createdPostOperator === null){ $createdPostOperator = 'AND'; }
        if ($privacyPostOperator === null){ $privacyPostOperator = 'AND'; }
        
        if ($debug === null) { $debug = $this->debug; }
        
        if ($tableId === null || $tableId === 'raffles') { $tableId = $this->tableIds['raffles']; }
        
        if ($fusionTablesService===null) {
            $fusionTablesService = $this->fusionTablesService;
        }
        // escaping does nothing or makes injection fail
        $tableId = $this->escape_mysql_mbstring($tableId);
        $sql = "SELECT * FROM {$tableId}";
        $preOperator = '';
        $where = " WHERE ";
        
        // I hate Fusion Tables with passion right now... No JOINS on plain SELECT queries? REALLY?
        if (isset ($participantId)){
            $simpleResultObject = $this->getParticipants(null, $participantId);
        }
        if (isset ($winnerId)){
            $simpleResultObject = $this->getWinners(null, $winnerId);
        }
        if (isset($simpleResultObject)){
            if (!isset($simpleResultObject->rows)
                || !is_array($simpleResultObject->rows)
                || count($simpleResultObject->rows) <= 0
            ){
                throw new Exception ('Raffle not found.', 404);
            }
            $raffleId = '';
            $columnIndex = (array_search('raffleid',$simpleResultObject->columns));
            foreach ($simpleResultObject->rows as $row){
                $raffleId .= "'{$row[$columnIndex]}', ";
            }
            $raffleId = '('.rtrim($raffleId,", ").')';
            $raffleIdOperator = 'IN';
        } else if (isset ($raffleId)){
            $raffleId = "'".$this->escape_mysql_mbstring($raffleId)."'";
        }
        // Now you do too, I hope.
        
        if (isset ($raffleId)) {
            $raffleIdOperator =  $this->escape_mysql_mbstring($raffleIdOperator);
            $raffleIdPostOperator =  $this->escape_mysql_mbstring(
                $raffleIdPostOperator
            );
            $where .= "raffleid {$raffleIdOperator} {$raffleId}";
            $preOperator = " {$raffleIdPostOperator} ";
        }
        if (isset ($description)) {
            $description = $this->escape_mysql_mbstring($description);
            $descriptionOperator = $this->escape_mysql_mbstring(
                $descriptionOperator
            );
            $descriptionPostOperator = $this->escape_mysql_mbstring(
                $descriptionPostOperator
            );
            $where .= $preOperator
                . "raffledescription {$descriptionOperator} '{$description}'";
            $preOperator = " {$descriptionPostOperator} "; 
        }
        if (isset ($creatorId)) {
            $creatorId = $this->escape_mysql_mbstring($creatorId);
            $creatorIdOperator = $this->escape_mysql_mbstring($creatorIdOperator);
            $where .= $preOperator
                . "creatorid {$creatorIdOperator} '{$creatorId}'";
            $preOperator = " {$creatorIdPostOperator} "; 
        }
        if (isset ($created)) {
            $created = $this->escape_mysql_mbstring($created);
            $createdOperator = $this->escape_mysql_mbstring($createdOperator);
            $createdPostOperator = $this->escape_mysql_mbstring(
                $createdPostOperator
            );
            $where .= $preOperator. "created {$createdOperator} '{$created}'";
            $preOperator = " {$createdPostOperator} "; 
        }
        if (isset ($privacy)) {
            $privacy = $this->escape_mysql_mbstring($privacy);
            $privacyOperator = $this->escape_mysql_mbstring($privacyOperator);
            $privacyPostOperator = $this->escape_mysql_mbstring(
                $privacyPostOperator
            );
            $where .= $preOperator. "privacy {$privacyOperator} '{$privacy}'";
            $preOperator = " {$privacyPostOperator} "; 
        }
        if (isset ($status)) {
            $status = $this->escape_mysql_mbstring($status);
            $statusOperator = $this->escape_mysql_mbstring($statusOperator);
            $where .= $preOperator. "status {$statusOperator} '{$status}'";
        }

        if ($debug){
            error_log($sql.$where);
        }
        $result = $fusionTablesService->query->sql($sql.$where);

        return $result->toSimpleObject();
    }

    /**
     * Horrible method to select rows from winners table matching their columns
     * to combinations of certain criteria
     *
     * Column matching criteria: is equal to '=', is not equal to '!='
     * Column combinators: 'AND', 'OR'
     *
     * @param null|string $raffleId
     * @param null|string $winnerId
     * @param null|string $raffled
     * @param string $raffleIdOperator
     * @param string $winnerIdOperator
     * @param string $raffledOperator
     * @param string $raffleIdPostOperator
     * @param string $winnerIdPostOperator
     * @param null|string $tableId
     * @param null|Google_Service_Fusiontables $fusionTablesService
     * @return stdClass
     */
    public function getWinners(
        $raffleId = null,
        $winnerId = null,
        $raffled = null,
        $raffleIdOperator = null,
        $winnerIdOperator = null,
        $raffledOperator = null,
        $raffleIdPostOperator = null,
        $winnerIdPostOperator = null,
        $tableId = null,
        $fusionTablesService = null
    ){
        // operators should be '=' or '!='
        // post operators should be 'AND' (Fusion Tables does not support 'OR')
        // (checks are not performed here)

        if ($raffleIdOperator === null){ $raffleIdOperator = '='; }
        if ($winnerIdOperator === null){ $winnerIdOperator = '='; }
        if ($raffledOperator === null){ $raffledOperator = '='; }
        if ($raffleIdPostOperator === null){ $raffleIdPostOperator = 'AND'; }
        if ($winnerIdPostOperator === null){ $winnerIdPostOperator = 'AND'; }
        
        if ($tableId === null || $tableId === 'winners') { $tableId = $this->tableIds['winners']; }
        if ($fusionTablesService===null) {
            $fusionTablesService = $this->fusionTablesService;
        }
        // escaping does nothing or makes injection fail
        $tableId = $this->escape_mysql_mbstring($tableId);
        $sql = "SELECT * FROM {$tableId}";
        $preOperator = '';
        $where = " WHERE ";
        if (isset ($raffleId)) {
            $raffleId = $this->escape_mysql_mbstring($raffleId);
            $raffleIdOperator =  $this->escape_mysql_mbstring($raffleIdOperator);
            $raffleIdPostOperator =  $this->escape_mysql_mbstring(
                $raffleIdPostOperator
            );
            $where .= "raffleid {$raffleIdOperator} '{$raffleId}'";
            $preOperator = " {$raffleIdPostOperator} ";
        }
        if (isset ($winnerId)) {
            $winnerId = $this->escape_mysql_mbstring($winnerId);
            $winnerIdOperator = $this->escape_mysql_mbstring(
                $winnerIdOperator
            );
            $winnerIdPostOperator = $this->escape_mysql_mbstring(
                $winnerIdPostOperator
            );
            $where .= $preOperator
                . "winnerid {$winnerIdOperator} '{$winnerId}'";
            $preOperator = " {$winnerIdPostOperator} ";
        }
        if (isset ($raffled)) {
            $raffled = $this->escape_mysql_mbstring($raffled);
            $raffledOperator = $this->escape_mysql_mbstring($raffledOperator);
            $where .= $preOperator. "raffled {$raffledOperator} '{$raffled}'";
        }

        $result = $fusionTablesService->query->sql($sql.$where);

        return $result->toSimpleObject();
    }

    /**
     * Horrible method to select rows from participants table matching their 
     * columns to combinations of certain criteria
     * 
     * It is a clone of getWinners, duplicated for decoupling.
     *
     * Column matching criteria: is equal to '=', is not equal to '!='
     * Column combinators: 'AND', 'OR'
     *
     * @param null|string $raffleId
     * @param null|string $participantId
     * @param null|string $joined
     * @param string $raffleIdOperator
     * @param string $participantIdOperator
     * @param string $joinedOperator
     * @param string $raffleIdPostOperator
     * @param string $participantIdPostOperator
     * @param null|string $tableId
     * @param null|Google_Service_Fusiontables $fusionTablesService
     * @return stdClass
     */
    public function getParticipants(
        $raffleId = null,
        $participantId = null,
        $joined = null,
        $raffleIdOperator = null,
        $participantIdOperator = null,
        $joinedOperator = null,
        $raffleIdPostOperator = null,
        $participantIdPostOperator = null,
        $tableId = null,
        $fusionTablesService = null
    ){
        // operators should be '=' or '!=' or 'IN'
        // post operators should be 'AND' (Fusion Tables does not support 'OR')
        // (checks are not performed here)

        if ($raffleIdOperator === null){ $raffleIdOperator = '='; }
        if ($participantIdOperator === null){ $participantIdOperator = '='; }
        if ($joinedOperator === null){ $joinedOperator = '='; }
        if ($raffleIdPostOperator === null){ $raffleIdPostOperator = 'AND'; }
        if ($participantIdPostOperator === null){ $participantIdPostOperator = 'AND'; }
        
        if ($tableId === null || $tableId === 'participants') { $tableId = $this->tableIds['participants']; }
        if ($fusionTablesService===null) {
            $fusionTablesService = $this->fusionTablesService;
        }
        // escaping does nothing or makes injection fail
        $tableId = $this->escape_mysql_mbstring($tableId);
        $sql = "SELECT * FROM {$tableId}";
        $preOperator = '';
        $where = " WHERE ";
        if (isset ($raffleId)) {
            $raffleId = $this->escape_mysql_mbstring($raffleId);
            $raffleIdOperator =  $this->escape_mysql_mbstring($raffleIdOperator);
            $raffleIdPostOperator =  $this->escape_mysql_mbstring(
                $raffleIdPostOperator
            );
            $where .= "raffleid {$raffleIdOperator} '{$raffleId}'";
            $preOperator = " {$raffleIdPostOperator} ";
        }
        if (isset ($participantId)) {
            $participantId = $this->escape_mysql_mbstring($participantId);
            $participantIdOperator = $this->escape_mysql_mbstring(
                $participantIdOperator
            );
            $participantIdPostOperator = $this->escape_mysql_mbstring(
                $participantIdPostOperator
            );
            $where .= $preOperator
                . "participantid {$participantIdOperator} '{$participantId}'";
            $preOperator = " {$participantIdPostOperator} ";
        }
        if (isset ($joined)) {
            $joined = $this->escape_mysql_mbstring($joined);
            $joinedOperator = $this->escape_mysql_mbstring($joinedOperator);
            $where .= $preOperator. "joined {$joinedOperator} '{$joined}'";
        }

        $result = $fusionTablesService->query->sql($sql.$where);

        return $result->toSimpleObject();
    }

    /**
     * @param $raffleId
     * @param null $tableId
     * @param null|array $userIdAndIdField array('idField'=>$idFieldName,'userId'=>$userId)
     * @param null $fusionTablesService
     * @throws Exception
     * @return stdClass
     */
    public function getResultsForRaffleId(
        $raffleId, 
        $tableId = null,
        $userIdAndIdField = null,
        $fusionTablesService = null
    ){
        
        if ($tableId === null || $tableId === 'raffles') { 
            $tableId = $this->tableIds['raffles']; 
        } else if ($tableId === 'participants') { 
            $tableId = $this->tableIds['participants'];
        } else if ($tableId === 'winners') { 
            $tableId = $this->tableIds['winners'];
        }
        
        if ($fusionTablesService===null) { 
            $fusionTablesService = $this->fusionTablesService; 
        }
        // escaping does nothing or makes injection fail
        $tableId = $this->escape_mysql_mbstring($tableId); 
        $raffleId = $this->escape_mysql_mbstring($raffleId);

        $sql = "SELECT * FROM {$tableId} WHERE raffleid = '{$raffleId}'"
            .(
            isset($userIdAndIdField)?
                " AND {$userIdAndIdField['idField']} = '{$userIdAndIdField['userId']}'"
                :""
            );
        $result = $fusionTablesService->query->sql($sql);

        if (
            $tableId !== $this->tableIds['raffles'] &&
            isset($result->rows) && 
            is_array($result->rows) &&
            count ($result->rows) <= 0
        ){
            $result = $fusionTablesService->query->sql(
                "SELECT * FROM {$this->tableIds['raffles']} WHERE raffleid = '{$raffleId}'"
            );
            if (
                isset($result->rows) &&
                is_array($result->rows) &&
                count ($result->rows) <= 0
            ){
                throw new Exception(
                    "Raffle {$raffleId} does not exist.",
                    404
                );
            }
        }

        return $result->toSimpleObject();
        
    }

    /**
     * @param $description
     * @param $userId
     * @param null $created
     * @param string $privacy
     * @param string $status
     * @param null $raffleId
     * @param null $tableId
     * @param null $fusionTablesService
     * @throws Exception
     * @return stdClass
     */
    public function addRaffle(
        $description, 
        $userId, 
        $created = null, 
        $privacy = null, 
        $status = null, 
        $raffleId = null, 
        $tableId = null, 
        $fusionTablesService = null
    ){


        if ($privacy === null) { $privacy = 'public'; }  else {
            $privacy = $this->escape_mysql_mbstring($privacy);
        }
        if ($status === null) { $status = 'closed'; } else {
            $status = $this->escape_mysql_mbstring($status);
        }
        
        if ($created === null) { 
            $created = date("Y-m-d H:i:s"); 
        } else {
            $created = $this->escape_mysql_mbstring($created);
        }
        if ($raffleId === null) { 
            $raffleId = uuid::v5(
                uuid::v4(), 'synapp\\info\\tools\\gplusraffle'
            ); 
        } else {
            $raffleId = $this->escape_mysql_mbstring($raffleId);
        }
        if ($tableId === null || $tableId === 'raffles') { 
            $tableId = $this->escape_mysql_mbstring(
                $this->tableIds['raffles']
            ); 
        } else {
            $tableId = $this->escape_mysql_mbstring($tableId);
        }
        $userId = $this->escape_mysql_mbstring($userId);
        $description = $this->escape_mysql_mbstring($description);
        if ($fusionTablesService===null) {
            $fusionTablesService = $this->fusionTablesService; 
        }
        // create new raffle with creatorid = $userId
        $sql = "INSERT INTO {$tableId} "
            ."('raffleid','raffledescription','creatorid','created','privacy','status') "
            ."VALUES ('{$raffleId}', '{$description}', '{$userId}', '{$created}', '{$privacy}', '{$status}')";
        $result = $fusionTablesService->query->sql($sql);
        
        if (
            isset($result->columns)&&
            is_array($result->columns)&&
            $result->columns[0]==='rowid'&&
            isset($result->rows)&&
            is_array($result->rows)&&
            count($result->rows) === 1
        ){
            $resultObject = new stdClass();
            $resultObject->columns = array(
                'raffleid',
                'raffledescription',
                'creatorid',
                'created',
                'privacy',
                'status'
            );
            $resultObject->rows = array(
                array(
                    $raffleId, 
                    $description, 
                    $userId, 
                    $created, 
                    $privacy, 
                    $status
                )
            );
            return $resultObject;
        } else {
            throw new Exception(
                "Couldn't create raffle with id {$raffleId}",
                500
            );
        }
        
    }

    /**
     * @param $raffleId
     * @param null $tableIds
     * @param null $fusionTablesService
     * @return bool
     */
    public function deleteRaffle(
        $raffleId, 
        $tableIds = null, 
        $fusionTablesService = null
    ){
        
        // only admin or creator should do this, but check is not performed here.
        // deletes raffle $raffleid from raffles, winners, participants
        if ($tableIds === null) { $tableIds = $this->tableIds; }
        if ($fusionTablesService===null) {
            $fusionTablesService = $this->fusionTablesService; 
        }
        // escaping does nothing or makes injection fail
        $rafflesTableId = $this->escape_mysql_mbstring(
            $tableIds['raffles']
        ); 
        $participantsTableId = $this->escape_mysql_mbstring(
            $tableIds['participants']
        ); 
        $winnersTableId = $this->escape_mysql_mbstring(
            $tableIds['winners']
        ); 
        $raffleId = $this->escape_mysql_mbstring($raffleId);


        $sql = "SELECT ROWID FROM {$rafflesTableId} WHERE raffleid = '{$raffleId}'";
        $result = $fusionTablesService->query->sql($sql);

        if (isset($result->rows)&&is_array($result->rows)&&count($result->rows)>0){
            $sql = "DELETE FROM {$rafflesTableId} WHERE ROWID = '{$result->rows[0][0]}'";
            $fusionTablesService->query->sql($sql);
        }

        $sql = "SELECT ROWID FROM {$participantsTableId} WHERE raffleid = '{$raffleId}'";
        $result = $fusionTablesService->query->sql($sql);

        if (isset($result->rows)&&is_array($result->rows)&&count($result->rows)>0){
            $sql = "DELETE FROM {$participantsTableId} WHERE ROWID = '{$result->rows[0][0]}'";
            $fusionTablesService->query->sql($sql);
        }

        $sql = "SELECT ROWID FROM {$winnersTableId} WHERE raffleid = '{$raffleId}'";
        $result = $fusionTablesService->query->sql($sql);

        if (isset($result->rows)&&is_array($result->rows)&&count($result->rows)>0){
            $sql = "DELETE FROM {$winnersTableId} WHERE ROWID = '{$result->rows[0][0]}'";
            $fusionTablesService->query->sql($sql);
        }
        
        return true;

    }

    /**
     * Adds participant $userId to $raffleId participants table
     *
     * @param string $userId
     * @param string $raffleId
     * @param $comment
     * @param null|string $joined
     * @param null|string $tableId
     * @param null|Google_Service_Fusiontables $fusionTablesService
     * @return stdClass
     */
    public function addParticipant(
        $userId, 
        $raffleId,
        $comment = null,
        $joined =  null, 
        $tableId = null, 
        $fusionTablesService = null
    ){
        
        // should not add to closed or raffled, and should avoid 
        // ($userId,$raffleId) duplicates, but checks are not performed here.
        if ($fusionTablesService===null) {
            $fusionTablesService = $this->fusionTablesService; 
        }
        if ($comment === null) { $comment = ''; }
        if ($tableId === null || $tableId === 'participants') { $tableId = $this->tableIds['participants']; }
        if ($joined === null) { $joined = date("Y-m-d H:i:s"); }

        $userId = $this->escape_mysql_mbstring($userId);
        $raffleId = $this->escape_mysql_mbstring($raffleId);
        $joined = $this->escape_mysql_mbstring($joined);
        $tableId = $this->escape_mysql_mbstring($tableId);
        // add $userId to $raffleId participants on participants table
        $sql = "INSERT INTO {$tableId} "
            ."('raffleid','participantid','comment','joined') "
            ."VALUES ('{$raffleId}', '{$userId}', '{$comment}', '{$joined}')";
        $result = $fusionTablesService->query->sql($sql);
        return $result->toSimpleObject();
        
    }

    /**
     * Removes participant $userId to $raffleId participants table
     *
     * The status of $raffleId should be 'open', although that's not checked here.
     *
     * @param string $userId
     * @param string $raffleId
     * @param null|string $tableId
     * @param null|Google_Service_Fusiontables $fusionTablesService
     * @throws Exception
     * @return stdClass
     */
    public function deleteParticipant(
        $userId, 
        $raffleId, 
        $tableId = null, 
        $fusionTablesService = null
    ){

        if ($fusionTablesService===null) {
            $fusionTablesService = $this->fusionTablesService; 
        }
        if ($tableId === null || $tableId === 'participants') { $tableId = $this->tableIds['participants']; }

        $userId = $this->escape_mysql_mbstring($userId);
        $raffleId = $this->escape_mysql_mbstring($raffleId);
        $tableId = $this->escape_mysql_mbstring($tableId);
        // delete $userId where raffleid = $raffleId on participants table

        $sql = "SELECT ROWID FROM {$tableId} WHERE raffleid = '{$raffleId}' AND participantid = '{$userId}'";
        $result = $fusionTablesService->query->sql($sql);

        if (isset($result->rows)&&is_array($result->rows)&&count($result->rows)>0){
            $sql = "DELETE FROM {$tableId} WHERE ROWID = '{$result->rows[0][0]}'";
            $result = $fusionTablesService->query->sql($sql);
        } else {
            throw new Exception (
                "Raffle or participant not found.",
                404
            );
        }
        return $result->toSimpleObject();
        
    }

    /**
     * Adds the specified ($raffleId,$userId) to winners table
     * 
     * $raffled (raffle datetime) can also be specified (NOW() by default)
     * 
     * should not add to open or raffled, and should avoid ($userId,$raffleId)
     * duplicates, but checks are not performed here.
     * (also, it's the same as addParticipant with just a field name change, 
     * but will be keep as separate functions for decoupling)
     * 
     * @param $userId
     * @param $raffleId
     * @param $raffled
     * @param null $tableId
     * @param null $fusionTablesService
     * @return stdClass
     */
    public function addWinner(
        $userId,
        $raffleId,
        $raffled, 
        $tableId = null, 
        $fusionTablesService = null
    ){

        if ($fusionTablesService===null) {$fusionTablesService = $this->fusionTablesService; }
        if ($tableId === null) { $tableId = $this->tableIds['winners']; }
        if ($raffled === null) { $raffled = date("Y-m-d H:i:s"); }

        $userId = $this->escape_mysql_mbstring($userId);
        $raffleId = $this->escape_mysql_mbstring($raffleId);
        $raffled = $this->escape_mysql_mbstring($raffled);
        $tableId = $this->escape_mysql_mbstring($tableId);
        // add $userId to $raffleId participants table
        $sql = "INSERT INTO {$tableId} "
            ."('raffleid','winnerid','raffled') "
            ."VALUES ('{$raffleId}', '{$userId}', '{$raffled}')";
        $result = $fusionTablesService->query->sql($sql);
        return $result->toSimpleObject();

    }

    /**
     * Picks N random winner(s) for a raffle among its participants.
     *
     * If one or more winners are already set, it returns the current winner(s).
     *
     * (If you want to redo the raffle you must either create a new one or
     * log in as admin to reset the winners for you using resetWinners method)
     *
     * @param $raffleId
     * @param int $limit
     * @param null|string $raffled must have a valid Fusion Tables DATETIME format
     * @param null|string $tableId
     * @param null|Google_Service_Fusiontables $fusionTablesService
     * @throws Exception the raffle must be closed
     * @return stdClass
     */
    public function pickWinners(
        $raffleId, 
        $limit = null, 
        $raffled = null, 
        $tableId = null, 
        $fusionTablesService = null
    ){

        if ($fusionTablesService===null) {
            $fusionTablesService = $this->fusionTablesService; 
        }
        if ($limit === null) { $limit = 1; }
        if ($raffled === null) { $raffled = date("Y-m-d H:i:s"); }
        if ($tableId === null || $tableId === 'winners') { 
            $tableId = $this->tableIds['winners']; 
        }


        $raffleId = $this->escape_mysql_mbstring($raffleId);
        $tableId = $this->escape_mysql_mbstring($tableId);

        $simpleResultObject = $this->getResultsForRaffleId(
            $raffleId, 
            $this->tableIds['raffles'], 
            null,
            $fusionTablesService
        );

        if (
            !isset($simpleResultObject->rows) 
            || !is_array($simpleResultObject->rows) 
            || count($simpleResultObject->rows) <=0
        ){
            throw new Exception ('Raffle not found.', 404);
        }

        $status = $simpleResultObject->rows[0][
            array_search('status',$simpleResultObject->columns)
        ];

        if ($status !== 'closed'){
            throw new Exception('Raffle must be closed',400);
        }

        $simpleResultObject = $this->getResultsForRaffleId(
            $raffleId, 
            $tableId, 
            null,
            $fusionTablesService
        );

        if (
            isset($simpleResultObject->rows)
            && is_array($simpleResultObject->rows)
            && count($simpleResultObject->rows) > 0
        ){
            return $simpleResultObject;
        }
        
        $sql = "SELECT * FROM {$this->tableIds['participants']} WHERE raffleid = '{$raffleId}'";
        $result = $fusionTablesService->query->sql($sql);
        $rowCount = isset($result->rows)&&is_array($result->rows)?count($result->rows):0;
        if ($rowCount<=0){
            throw new Exception(
                "No participants found.",
                404
            );
        }
        $offset = mt_rand(0,max(0,$rowCount-$limit));
        $sql = "SELECT * FROM {$this->tableIds['participants']} OFFSET {$offset} LIMIT {$limit}";
        $result = $fusionTablesService->query->sql($sql);
        $this->updateRaffleStatus(
            $raffleId, 
            'raffled', 
            $this->tableIds['raffles'], 
            $fusionTablesService
        );
        if (
            isset($result->rows)
            && is_array($result->rows)
            && count($result->rows) > 0
        ){
            $columnIndex = (array_search('participantid',$result->columns));
            foreach ($result->rows as $row){
                $this->addWinner(
                    $row[$columnIndex], 
                    $raffleId,
                    $raffled, 
                    null,
                    $fusionTablesService
                );
            }
            return  $this->getResultsForRaffleId(
                $raffleId, 
                $tableId, 
                null,
                $fusionTablesService
            );
        } else {
            throw new Exception(
                'No participants found.',
                404
            );
        }
    }

    /**
     * Sets the raffles table raffleid status to the specified status.
     * 
     * A $raffleId and a $status must be specified. 
     * 
     * It should be a valid status and should be updated by admin or creator, 
     * but checks are not performed here.
     * 
     * @param $raffleId
     * @param $status
     * @param null $tableId
     * @param null $fusionTablesService
     * @return stdClass
     * @throws Exception
     */
    public function updateRaffleStatus(
        $raffleId, 
        $status, 
        $tableId = null, 
        $fusionTablesService = null
    ){

        // should be a valid status and should be updated by admin or creator,
        // but checks are not performed here.
        if ($fusionTablesService===null) {
            $fusionTablesService = $this->fusionTablesService; 
        }
        if ($tableId === null || $tableId === 'raffles') { 
            $tableId = $this->tableIds['raffles']; 
        }

        $raffleId = $this->escape_mysql_mbstring($raffleId);
        $status = $this->escape_mysql_mbstring($status);

        if (!isset($raffleId)){
            throw new Exception('Must specify a raffle',400);
        }

        $sql = "SELECT ROWID FROM {$tableId} WHERE raffleid = '{$raffleId}'";
        $result = $fusionTablesService->query->sql($sql);

        if (isset($result->rows)&&is_array($result->rows)&&count($result->rows)>0){
            $sql = "UPDATE {$tableId} SET status = '{$status}' WHERE ROWID = '{$result->rows[0][0]}'";
            $result = $fusionTablesService->query->sql($sql);
        } else {
            throw new Exception(
                "updateRaffleStatus: ROWID for raffleid = {$raffleId} not "
                ."found on table {$tableId}.",
                404
            );
        }
        return $result->toSimpleObject();
        
    }

}