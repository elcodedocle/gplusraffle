<?php
/**
 * gplusraffle - Google API PHP OAuth 2.0 and FusionTables client based raffle 
 * management system
 * 
 * Raffle manager class. It handles the raffle operations.
 * 
 * @package gplusraffle
 * @copyright Gael Abadin 2014
 * @license MIT Expat
 * @version v0.1.4-beta
 * 
 */
class Raffle{

    /**
     * @var RaffleDAO $raffleDAO
     */
    private $raffleDAO;

    /**
     * @var string $userId
     */
    private $userId;

    /**
     * @var bool $isAdmin
     */
    private $isAdmin;

    /**
     * Sets the required parameters to handle raffle operations.
     * 
     * @param RaffleDAO $raffleDAO
     * @param string $userId
     * @param bool $isAdmin (optional, defaults to false)
     */
    public function __construct($raffleDAO, $userId, $isAdmin = false){
        
        $this->raffleDAO = $raffleDAO;
        $this->userId = $userId;
        if (is_bool($isAdmin)){
            $this->isAdmin = $isAdmin;
        }
        
    }

    /**
     * Checks if $userId is creator or $raffleId
     * 
     * @param string $raffleId
     * @param null|string $userId
     * @param null|RaffleDAO $raffleDAO
     * @return bool
     * @throws Exception
     */
    public function isCreator($raffleId, $userId = null, $raffleDAO = null){

        if ($userId === null){ $userId = $this->userId; }
        if ($raffleDAO === null) { $raffleDAO = $this->raffleDAO; }
        
        $raffle = $raffleDAO->getResultsForRaffleId($raffleId,'raffles');

        if (!isset($raffle->rows) 
            || !is_array($raffle->rows) 
            || count($raffle->rows) <= 0
        ){
            throw new Exception ('Raffle not found.', 404);
        }

        $creatorIndex = array_search('creatorid',$raffle->columns);
        $creatorId = $raffle->rows[0][$creatorIndex];

        if ($creatorId!==$userId){
            return false;
        } else {
            return true;
        }
        
    }

    /**
     * Gets a raffle's status.
     * 
     * @param string $raffleId
     * @param null|RaffleDAO $raffleDAO
     * @return string
     * @throws Exception
     */
    public function getStatus($raffleId, $raffleDAO = null){
        
        if ($raffleDAO === null) { $raffleDAO = $this->raffleDAO; }

        $simpleResultObject = $raffleDAO->getResultsForRaffleId(
            $raffleId, 
            'raffles'
        );

        if (!isset($simpleResultObject->rows) 
            || !is_array($simpleResultObject->rows) 
            || count($simpleResultObject->rows) <= 0
        ){
            throw new Exception ('Raffle not found.', 404);
        }

        $status = $simpleResultObject->rows[0][
            array_search('status',$simpleResultObject->columns)
        ];

        return $status;
        
    }

    /**
     * Creates a new raffle.
     *
     * @param $description
     * @param null|string $userId
     * @param null|RaffleDAO $raffleDAO
     * @return \stdClass
     */
    public function create($description, $userId = null, $raffleDAO = null){
        
        if ($userId === null){ $userId = $this->userId; }
        if ($raffleDAO === null){ $raffleDAO = $this->raffleDAO; }
        
        return $raffleDAO->addRaffle($description, $userId);
        
    }

    /**
     * Deletes an existing raffle.
     *
     * Only its creator or the admin can delete a raffle.
     *
     * All data (participants, winner, etc.) will be lost!
     *
     * @param string $raffleId
     * @param null|string $userId
     * @param null|bool $isAdmin
     * @param null|RaffleDAO $raffleDAO
     * @return bool
     * @throws Exception
     */
    public function delete(
        $raffleId, 
        $userId = null, 
        $isAdmin = null,
        $raffleDAO = null
    ){
        
        if ($userId === null){ $userId = $this->userId; }
        if ($isAdmin === null){ $isAdmin = $this->isAdmin; }
        if ($raffleDAO === null){ $raffleDAO = $this->raffleDAO; }
        
        if (!$isAdmin&&!$this->isCreator($raffleId,$userId)){
            throw new Exception('Operation not allowed',401);
        }
        
        return $raffleDAO->deleteRaffle($raffleId);
        
    }

    /**
     * Updates an existing raffle's status.
     *
     * Only its creator or the admin can update a raffle's status.
     *
     * Valid status: open/closed (you can't manually set a raffle's status to
     * raffled unless you are admin)
     *
     * @param string $raffleId
     * @param string $status
     * @param null|string $userId
     * @param null|bool $isAdmin
     * @param null|RaffleDAO $raffleDAO
     * @return \stdClass
     * @throws Exception
     */
    public function updateStatus(
        $raffleId, 
        $status, 
        $userId = null, 
        $isAdmin = null, 
        $raffleDAO = null
    ){
        if (
            $status!=='open' &&
            $status!=='closed' && 
            (
                $status!=='raffled' ||
                !$isAdmin
            )
        ){
            throw new Exception ('Invalid status',400);
        }
        if ($userId === null) { $userId = $this->userId; }
        if ($isAdmin === null) { $isAdmin = $this->isAdmin; }
        if ($raffleDAO === null) { $raffleDAO = $this->raffleDAO; }

        if (!$isAdmin&&!$this->isCreator($raffleId,$userId)){
            throw new Exception('Operation not allowed',401);
        }
        
        return $raffleDAO->updateRaffleStatus($raffleId,$status);
    }

    /**
     * Picks a raffle's winner(s).
     *
     * Only its creator or the admin can pick a raffle's winner.
     *
     * The status of the raffle must be closed.
     *
     * The raffle must have one or more participants.
     *
     * @param string $raffleId
     * @param int $limit
     * @param null|string $userId
     * @param null|bool $isAdmin
     * @param null|RaffleDAO $raffleDAO
     * @throws Exception
     * @return \stdClass
     */
    public function raffle(
        $raffleId, 
        $limit = null, 
        $userId = null, 
        $isAdmin = null, 
        $raffleDAO = null
    ){
        
        if ($limit === null) { $limit = 1; }
        if ($userId === null) { $userId = $this->userId; }
        if ($isAdmin === null) { $isAdmin = $this->isAdmin; }
        if ($raffleDAO === null) { $raffleDAO = $this->raffleDAO; }

        if (!$isAdmin&&!$this->isCreator($raffleId,$userId)){
            throw new Exception('Operation not allowed',401);
        }
        
        return $raffleDAO->pickWinners($raffleId, $limit);
        
    }

    /**
     * Gets a list of raffles, filtered by 'filter'
     *
     * A null filter (default) selects all raffles
     *
     * @param array $filterArray translates to where
     * arraykey array[key][condition] mysql_real_escape_string(array[key][value])
     * arraykey must be a valid table column name and arraykey[condition] must
     * be a valid condition: '=', '!=' or '<>' E.g.
     * array('raffleid'=>array('condition'=>'=','value'=>'someraffleid'))
     *
     * @param null|RaffleDAO $raffleDAO
     * @return \stdClass
     */
    public function getList($filterArray = null, $raffleDAO = null){

        if ($raffleDAO === null) { $raffleDAO = $this->raffleDAO; }
        if ($filterArray === null) { $filterArray = array(); }

        if (isset($filterArray['raffleid'])){
            return $this->getParticipants($filterArray['raffleid']['value']);
        }
        return $raffleDAO->getRaffles(
            isset($filterArray['raffleid'])?$filterArray['raffleid']['value']:null,
            isset($filterArray['description'])?$filterArray['description']['value']:null,
            isset($filterArray['creatorid'])?$filterArray['creatorid']['value']:null,
            isset($filterArray['participantid'])?$filterArray['participantid']['value']:null,
            isset($filterArray['winnerid'])?$filterArray['winnerid']['value']:null,
            isset($filterArray['created'])?$filterArray['created']['value']:null,
            isset($filterArray['privacy'])?$filterArray['privacy']['value']:null,
            isset($filterArray['status'])?$filterArray['status']['value']:null,
            isset($filterArray['raffleid'])?$filterArray['raffleid']['condition']:null,
            isset($filterArray['description'])?$filterArray['description']['condition']:null,
            isset($filterArray['creatorid'])?$filterArray['creatorid']['condition']:null,
            isset($filterArray['created'])?$filterArray['created']['condition']:null,
            isset($filterArray['privacy'])?$filterArray['privacy']['condition']:null,
            isset($filterArray['status'])?$filterArray['status']['condition']:null,
            $raffleIdPostOperator = 'AND',
            $descriptionPostOperator = 'AND',
            $creatorIdPostOperator = 'AND',
            $createdPostOperator = 'AND',
            $privacyPostOperator = 'AND'
        );
    }

    /**
     * Gets a list of participants for a raffleid
     *
     * A null filter (default) selects all raffles
     *
     * @param string $raffleId
     * @param null $participantId
     * @param null|RaffleDAO $raffleDAO
     * @return \stdClass
     */
    public function getParticipants(
        $raffleId, 
        $participantId = null, 
        $raffleDAO = null
    ){
        
        if ($raffleDAO === null) { $raffleDAO = $this->raffleDAO; }

        return $raffleDAO->getResultsForRaffleId(
            $raffleId,
            'participants', 
            isset($participantId)?
                array(
                    'idField'=>'participantid',
                    'userId'=>$participantId
                ):
                null
        );
        
    }

    /**
     * Joins an open raffle's list of participants.
     *
     * The raffle must exist (raffleid must be a valid, existing id).
     *
     * The raffle status must be opened.
     *
     * @param string $raffleId
     * @param null|string $comment
     * @param null|string $userId
     * @param null|RaffleDAO $raffleDAO
     * @throws Exception
     * @return \stdClass
     */
    public function join(
        $raffleId, 
        $comment = null, 
        $userId = null, 
        $raffleDAO = null
    ){

        if ($userId === null) { $userId = $this->userId; }
        if ($raffleDAO === null) { $raffleDAO = $this->raffleDAO; }
        if ($comment === null) { $comment = ''; }

        if ($this->getStatus($raffleId) !== 'open'){
            throw new Exception('Raffle must be open',403);
        }

        $result = $this->getParticipants($raffleId, $userId);
        if (
            isset($result->rows)
            &&is_array($result->rows)
            &&count($result->rows) > 0
        ){
            throw new Exception(
                'User has already joined this raffle',
                400
            );
        }
        
        return $raffleDAO->addParticipant(
            $userId, 
            $raffleId, 
            $comment, 
            null,
            'participants'
        );
        
    }

    /**
     * Leaves a raffle's list of participants.
     *
     * The raffle must exist (raffleid must be a valid, existing id).
     *
     * The user must be a participant on that raffle.
     *
     * @param string $raffleId
     * @param null|string $userId
     * @param null|RaffleDAO $raffleDAO
     * @return \stdClass
     */
    public function leave($raffleId, $userId = null, $raffleDAO = null){

        if ($userId === null) { $userId = $this->userId; }
        if ($raffleDAO === null) { $raffleDAO = $this->raffleDAO; }

        return $raffleDAO->deleteParticipant(
            $userId, 
            $raffleId,
            'participants'
        );

    }

    /**
     * Returns the winner(s) of a raffled raffle.
     *
     * The raffle must have been raffled.
     *
     * @param string $raffleId
     * @param null|RaffleDAO $raffleDAO
     * @throws Exception
     * @internal param null|string $userId
     * @return \stdClass
     */
    public function check($raffleId, $raffleDAO = null){

        if ($raffleDAO === null) { $raffleDAO = $this->raffleDAO; }
        
        $simpleResultObject = $raffleDAO->getResultsForRaffleId(
            $raffleId,
            'winners'
        );
        
        if (!isset($simpleResultObject->rows)
            || !is_array($simpleResultObject->rows)
            || count($simpleResultObject->rows) <= 0
        ){
            throw new Exception ('Raffle not found.', 404);
        }
        
        return $simpleResultObject;

    }
}
