<?php
define('LOGIN_BLANK_USER', 1);
define('LOGIN_BAD_CREDS', 2);
define('LOGIN_INACTIVE', 3);
define('LOGIN_PASSWORD_EXPIRED', 4);

require_once('DatabaseManager.inc');

class LoginManager
{
    private static $instance;

    public function __construct()
    {
        if (self::$instance != null)
        {
            return self::$instance;
        }

        self::$instance = $this;
        DatabaseManager::init();
        
        return self::$instance;
    }

    public function login($user, $pass)
    {

        return $consultant;
    }

}
?>
