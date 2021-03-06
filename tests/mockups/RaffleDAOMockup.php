<?php
class RaffleDAOMockup {

    public $tablesMockup;
    public $offsetMockup = 0;
    private $tableIds;
    private $fusionTablesService;
    private $debug = false;
    
    public function setFusionTablesService($fusionTablesService){

        $this->fusionTablesService = $fusionTablesService;

    }
    public function setTableIds($tableIds){

        $this->tableIds = $tableIds;

    }
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
                null:
                $fusionTablesService;
    }
    public function getRaffles(
        $raffleId = null,
        $description = null,
        $creatorId = null,
        $participantId = null,
        $winnerId = null,
        $privacy = null,
        $status = null
    ){

        $tableId = $this->tableIds['raffles'];

        $raffles = $this->tablesMockup[$tableId];
        $participants = $this->tablesMockup['mockupParticipantsId'];
        $winners = $this->tablesMockup['mockupWinnersId'];

        $filteredRaffles = new stdClass();
        $filteredRaffles->columns = $raffles->columns;

        $rafflesRows = $raffles->rows;
        if (isset ($participantId)){
            $raffleIds = array();
            $participantIdIndex = array_search('participantid',$participants->columns);
            $raffleIdIndex = array_search('raffleid',$participants->columns);
            foreach ($participants->rows as $row){
                if ($row[$participantIdIndex] === $participantId){
                    $raffleIds[]=$row[$raffleIdIndex];
                }
            }
            $raffleIdIndex = array_search('raffleid',$raffles->columns);
            $filteredRows = array();
            foreach ($rafflesRows as $row){
                if (in_array($row[$raffleIdIndex],$raffleIds)){
                    $filteredRows[]=$row;
                }
            }
            $rafflesRows = $filteredRows;
        }
        if (isset ($winnerId)){
            $raffleIds = array();
            $winnerIdIndex = array_search('winnerid',$winners->columns);
            $raffleIdIndex = array_search('raffleid',$winners->columns);
            foreach ($winners->rows as $row){
                if ($row[$winnerIdIndex] === $winnerId){
                    $raffleIds[]=$row[$raffleIdIndex];
                }
            }
            $raffleIdIndex = array_search('raffleid',$raffles->columns);
            $filteredRows = array();
            foreach ($rafflesRows as $row){
                if (in_array($row[$raffleIdIndex],$raffleIds)){
                    $filteredRows[]=$row;
                }
            }
            $rafflesRows = $filteredRows;
        }
        if (isset ($raffleId)) {
            $raffleIdIndex = array_search('raffleid',$raffles->columns);
            $filteredRows = array();
            foreach ($rafflesRows as $row){
                if (
                    (
                        $row[$raffleIdIndex] === $raffleId
                    )
                ){
                    $filteredRows[]=$row;
                }
            }
            $rafflesRows = $filteredRows;
        }
        if (isset ($description)) {
            $descriptionIndex = array_search('raffledescription',$raffles->columns);
            $filteredRows = array();
            foreach ($rafflesRows as $row){
                if (
                    (
                        $row[$descriptionIndex] === $description
                    )
                ){
                    $filteredRows[]=$row;
                }
            }
            $rafflesRows = $filteredRows;
        }
        if (isset ($creatorId)) {
            $creatorIdIndex = array_search('creatorid',$raffles->columns);
            $filteredRows = array();
            foreach ($rafflesRows as $row){
                if (
                    (
                        $row[$creatorIdIndex] === $creatorId
                    )
                ){
                    $filteredRows[]=$row;
                }
            }
            $rafflesRows = $filteredRows;
        }
        if (isset ($privacy)) {
            $privacyIndex = array_search('privacy',$raffles->columns);
            $filteredRows = array();
            foreach ($rafflesRows as $row){
                if (
                    (
                        $row[$privacyIndex] === $privacy
                    )
                ){
                    $filteredRows[]=$row;
                }
            }
            $rafflesRows = $filteredRows;
        }
        if (isset ($status)) {
            $statusIndex = array_search('status',$raffles->columns);
            $filteredRows = array();
            foreach ($rafflesRows as $row){
                if (
                    (
                        $row[$statusIndex] === $status
                    )
                ){
                    $filteredRows[]=$row;
                }
            }
            $rafflesRows = $filteredRows;
        }
        
        $filteredRaffles->rows = $rafflesRows;

        return $filteredRaffles;
    }
    
    public function getFilteredDataFromTable(
        $tableIdOrName,
        $raffleId = null,
        $id = null
    ){

        $idFieldName = '';
        if ($tableIdOrName === 'winners') {
            $this->tableIds['winners'];
            $idFieldName = 'winnerid';
        } else if ($tableIdOrName === 'participants') {
            $this->tableIds['participants'];
            $idFieldName = 'participantid';
         }

        $table = $this->tablesMockup[$tableIdOrName];

        $filteredWinnersOrParticipants = new stdClass();
        $filteredWinnersOrParticipants->columns = $table->columns;

        $winnersOrParticipantsRows = $table->rows;
        
        if (isset ($raffleId)) {
            $raffleIdIndex = array_search($idFieldName,$table->columns);
            $filteredRows = array();
            foreach ($winnersOrParticipantsRows as $row){
                if (
                    (
                        $row[$raffleIdIndex] === $raffleId
                    )
                ){
                    $filteredRows[]=$row;
                }
            }
            $winnersOrParticipantsRows = $filteredRows;
        }
        if (isset ($id)) {
            $winnerOrParticipantIdIndex = array_search($idFieldName,$table->columns);
            $filteredRows = array();
            foreach ($winnersOrParticipantsRows as $row){
                if (
                    (
                        $row[$winnerOrParticipantIdIndex] === $id
                    )
                ){
                    $filteredRows[]=$row;
                }
            }
            $winnersOrParticipantsRows = $filteredRows;
        }

        $filteredWinnersOrParticipants->rows = $winnersOrParticipantsRows;

        return $filteredWinnersOrParticipants;
    }
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
            //$fusionTablesService = $this->fusionTablesService;
        }

        $table = $this->tablesMockup[$tableId];

        $filteredEntries = new stdClass();
        $filteredEntries->columns = $table->columns;

        $tableRows = $table->rows;
        if (isset ($raffleId)) {
            $raffleIdIndex = array_search('raffleid',$table->columns);
            $filteredRows = array();
            foreach ($tableRows as $row){
                if (
                    (
                        $row[$raffleIdIndex] === $raffleId
                    )
                ){
                    $filteredRows[]=$row;
                }
            }
            $tableRows = $filteredRows;
        }
        if (
            count ($tableRows) === 0 &&
            $raffleId !== $this->tableIds['raffles']
        ){
            $raffleIdRafflesIndex = array_search(
                'raffleid',
                $this->tablesMockup[$this->tableIds['raffles']]->columns
            );
            $found = false;
            foreach (
                $this->tablesMockup[$this->tableIds['raffles']]->rows as $row
            ){
                if ($row[$raffleIdRafflesIndex] === $raffleId){
                    $found = true;
                }
            }
            if (!$found){
                throw new Exception(
                    "Raffle {$raffleId} not found.",
                    404
                );
            }
        }
        if (isset($userIdAndIdField)){
            $raffleIdIndex = array_search($userIdAndIdField['idField'],$table->columns);
            $filteredRows = array();
            foreach ($tableRows as $row){
                if (
                (
                    $row[$raffleIdIndex] === $userIdAndIdField['userId']
                )
                ){
                    $filteredRows[]=$row;
                }
            }
            $tableRows = $filteredRows;
        }

        $filteredEntries->rows = $tableRows;
        return $filteredEntries;

    }
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

        if ($privacy === null) { $privacy = 'public'; }
        if ($status === null) { $status = 'closed'; }

        if ($created === null) { $created = date("Y-m-d H:i:s"); }
        if ($tableId === null) { $tableId = $this->tableIds['raffles']; }
        if ($raffleId === null) {
            // the $tableId-$created-$rowIndex is the ROWID in the mockup table
            $raffleId = $tableId.'-'.$created.'-'.(count($this->tablesMockup[$tableId]->rows));
        }
        if ($fusionTablesService===null) {
            //$fusionTablesService = $this->fusionTablesService;
        }
        // create new raffle with creatorid = $userId
        $newRow = array(
            $raffleId,
            $description,
            $userId,
            $created,
            $privacy,
            $status
        );
        $this->tablesMockup[$tableId]->rows[] = $newRow;
        $result = new stdClass();
        $result->columns = $this->tablesMockup[$tableId]->columns;
        // the $tableId-$rowIndex is the ROWID in the mockup table
        $result->rows = array($newRow);
        
        return $result;

    }
    public function deleteRaffle(

        $raffleId,
        $tableIds = null,
        $fusionTablesService = null
    ){

        // only admin or creator should do this, but check is not performed here.
        // deletes raffle $raffleid from raffles, winners, participants
        if ($tableIds === null) { $tableIds = $this->tableIds; }
        if ($fusionTablesService===null) {
            //$fusionTablesService = $this->fusionTablesService;
        }

        $raffleIdIndex = array_search('raffleid',$this->tablesMockup[$tableIds['raffles']]->columns);
        foreach ($this->tablesMockup[$tableIds['raffles']]->rows as $index=>$row){
            if ($row[$index][$raffleIdIndex] === $raffleId){
                array_splice($this->tablesMockup[$tableIds['raffles']],$index,1);
            }
        }

        $raffleIdIndex = array_search('raffleid',$this->tablesMockup[$tableIds['participants']]->columns);
        foreach ($this->tablesMockup[$tableIds['participants']]->rows as $index=>$row){
            if ($row[$index][$raffleIdIndex] === $raffleId){
                array_splice($this->tablesMockup[$tableIds['participants']],$index,1);
            }
        }

        $raffleIdIndex = array_search('raffleid',$this->tablesMockup[$tableIds['winners']]->columns);
        foreach ($this->tablesMockup[$tableIds['winners']]->rows as $index=>$row){
            if ($row[$index][$raffleIdIndex] === $raffleId){
                array_splice($this->tablesMockup[$tableIds['winners']],$index,1);
            }
        }

        return true;

    }
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
            //$fusionTablesService = $this->fusionTablesService;
        }
        if ($comment === null) { $comment = ''; }
        if ($tableId === null) { $tableId = $this->tableIds['participants']; }
        if ($joined === null) { $joined = date("Y-m-d H:i:s"); }
        
        // add $userId to $raffleId participants table
        $row = array(
            $raffleId,
            $userId,
            $comment,
            $joined,
        );
        $this->tablesMockup[$tableId]->rows[] = $row;
        $result = new stdClass();
        $result->columns = array('rowid');
        // the $tableId-$rowIndex is the ROWID in the mockup table
        $result->rows = array(array($tableId.'-'.(count($this->tablesMockup[$tableId]->rows)-1)));
        return $result;

    }
    public function deleteParticipant(
        $userId,
        $raffleId,
        $tableId = null,
        $fusionTablesService = null
    ){

        if ($fusionTablesService===null) {
            //$fusionTablesService = $this->fusionTablesService;
        }
        if ($tableId === null || $tableId === 'participants') { 
            $tableId = $this->tableIds['participants']; 
        }

        $raffleIdIndex = array_search('raffleid',$this->tablesMockup[$tableId]->columns);
        $participantIdIndex = array_search('participantid',$this->tablesMockup[$tableId]->columns);
        $result = false;
        foreach ($this->tablesMockup[$tableId]->rows as $index=>$row){
            if (
                $row[$raffleIdIndex] === $raffleId &&
                $row[$participantIdIndex] === $userId
            ){
                $result = true;
                array_splice($this->tablesMockup[$tableId]->rows,$index,1);
                break;
            }
        }
        if ($result === false){
            throw new Exception(
                'Raffle not found.',
                404
            );
        }
        // TODO: check if real scenario returns false on not found
        return $result;

    }
    public function addWinner(
        $userId,
        $raffleId,
        $raffled,
        $tableId = null,
        $fusionTablesService = null
    ){
        if ($fusionTablesService===null) {
            //$fusionTablesService = $this->fusionTablesService; 
        }
        if ($tableId === null) { $tableId = $this->tableIds['winners']; }
        if ($raffled === null) { $raffled = date("Y-m-d H:i:s"); }
        
        // add $userId to $raffleId participants on participants table
        $row = array(
            $raffleId,
            $userId,
            $raffled
        );
        $this->tablesMockup[$tableId]->rows[] = $row;
        $result = new stdClass();
        $result->columns = array('rowid');
        // the $tableId-$rowIndex is the ROWID in the mockup table
        $result->rows = array(array($tableId.'-'.(count($this->tablesMockup[$tableId]->rows)-1)));
        return $result;
    }
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

        $matchedRows = array();
        $table = $this->tablesMockup[$this->tableIds['participants']];
        $raffleIdColumn = array_search('raffleId',$table->columns);
        foreach ($table->rows as $row){
            if ($row[$raffleIdColumn] === $raffleId){
                $matchedRows[]=$row;
            }
        }
        $rowCount = count($matchedRows);
        if ($rowCount<=0){
            throw new Exception(
                "No participants found.",
                404
            );
        }
        $offset = $this->offsetMockup;
        $result = new stdClass();
        $result->columns = $table->columns;
        $result->rows = array_slice($matchedRows,$offset,$limit);
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
                    null
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
    public function updateRaffleStatus(
        $raffleId,
        $status,
        $tableId = null,
        $fusionTablesService = null
    ){
        if ($fusionTablesService===null) {
            //$fusionTablesService = $this->fusionTablesService;
        }
        if ($tableId === null || $tableId === 'raffles') {
            $tableId = $this->tableIds['raffles'];
        }

        if (!isset($raffleId)){
            throw new Exception('Must specify a raffle',400);
        }
        
        $found = false;
        $table = $this->tablesMockup[$tableId];
        $statusColumn = array_search('status',$table->columns);
        $raffleIdColumn = array_search('raffleId',$table->columns);
        foreach ($table->rows as $index=>$row){
            if ($row[$raffleIdColumn] === $raffleId){
                $this->tablesMockup[$tableId]->rows[$index][$statusColumn] = $status;
                $found = true;
                break;
            }
        }
        if (!$found) {
            throw new Exception(
                "updateRaffleStatus: ROWID for raffleid = {$raffleId} not "
                ."found on table {$tableId}.",
                404
            );
        }


        $result = new stdClass();
        $result->columns = array('affected_rows');
        if ($found){
            $result->rows = array(array(1));
        } else {
            $result->rows = array(array(0));
        }
        return $result;

    }

}