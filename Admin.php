<?php
/**
 * gplusraffle - Google API PHP OAuth 2.0 and FusionTables client based raffle 
 * management system
 * 
 * @package gplusraffle
 * @copyright Gael Abadin 2014
 * @license MIT Expat
 * @version v0.1-beta
 * 
 * This class will use AdminDAO to set, store and retrieve the admin's google 
 * account token required for managing the fusion tables where the raffles' 
 * data is stored.
 * 
 * If the token expires (unlikely) or is invalidated, the admin must run this 
 * script again to set a new token
 * 
 * In order to change the google account to the app's fusion tables operations,
 * uninstall must be executed with valid adminId, then logout and login with 
 * the new credentials, and finally execute install again so the new adminId
 * and accessToken are set.
 * 
 * Actions:
 * 
 * /admin/install - sets the current admin google account id and a new token 
 * to handle fusion tables operations
 * /admin/uninstall - removes the current admin google account id and token
 * 
 */
class Admin{

    /**
     * @var object $adminDAO
     */
    private $adminDAO;

    /**
     * @var array $debug
     */
    private $debug = false;

    /**
     * @param object $adminDAO
     */
    public function setAdminDAO($adminDAO)
    {
        $this->adminDAO = $adminDAO;
    }

    /**
     * @return object
     */
    public function getAdminDAO()
    {
        return $this->adminDAO;
    }

    /**
     * Sets the current admin google account id and a new token to handle fusion
     * tables operations, creating any tables that do not exist
     *
     * @param null|object $adminDAO
     * @throws Exception
     */
    public function install($adminDAO = null){
        if ($adminDAO===null) { $adminDAO = $this->adminDAO; }
        $adminId = $adminDAO->getAdminId();
        $userId = $adminDAO->getUserId();
        if (isset($adminId) && $adminId !== $userId){
            throw new Exception('Authorization error.',401);
        }
        $adminDAO->createTables();
        $adminDAO->saveAdminSettings();
    }

    /**
     * Removes the current admin google account id and token
     * 
     * @param null|object $adminDAO
     * @throws Exception on error
     */
    public function uninstall($adminDAO = null){
        if ($adminDAO===null) { $adminDAO = $this->adminDAO; }
        if ($adminDAO->getAdminId()!==$adminDAO->getUserId()){
            throw new Exception('Authorization error.',401);
        }
        $adminDAO->removeAdminSettings();
    }

/**
 * Class contructor
 *
 * Sets the required properties.
 *
 * @param null|object $adminDAO
 * @param bool $debug
 */
    public function __construct($adminDAO = null, $debug = false){
        
        if ($adminDAO!==null) { $this->adminDAO = $adminDAO; } 
        else { $this->adminDAO = new AdminDAO(); }

        if (is_bool($debug)){ $this->debug = $debug; }

    }

    /**
     * @param $authUrl
     * @param $debugData
     * @return string
     */
    public function getAuthenticationWebView($authUrl,$debugData = null){
    ob_start();
    ?><!doctype html>
    <html>
    <head>
        <title>google plus raffle app admin login</title>
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

}
