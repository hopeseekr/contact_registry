<?php
define('LOGIN_BLANK_USER', 1);
define('LOGIN_BAD_CREDS', 2);

require_once('lib/db.php');

class DatabaseController
{
    private static $instance;
    protected $dbh;
    public $id;
    
    protected function __construct()
    {
        $this->id = rand(5, 2000); 
        $this->dbh = db_connect();
    }
    
    public static function getInstance()
    {
        if (!isset(self::$instance))
        {
            self::$instance = new LoginController;
        }

        return self::$instance;
    }
}

class LoginController extends DatabaseController
{
    private static $instance;
    private $consultant;
    
    public static function getInstance()
    {
        if (!isset(self::$instance))
        {
            self::$instance = new LoginController;
        }

        return self::$instance;
    }

    private function handlExpiredPassword()
    {
        $_SESSION['expiredPassword'] = true;
        header("Location: http://" . $_SERVER['HTTP_HOST'] . '/changePassword.php');
        exit;
    }
    
    public function validateUser()
    {

        if (!isset($_POST['username']) || $_POST['username'] == '' || !isset($_POST['password']))
        {
            throw new Exception('No user credentials entered', LOGIN_BLANK_USER);
        }

        $user = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
        $pass = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);

        if ($pass != '')
        {
            $pass_cond = 'Password=?';
        }
        else
        {
            $pass_cond = 'Password IS NULL';
        }
    
        $dbh = $this->dbh;
    
        if (USER_AUTH == 'simple')
        {
            $qs = 'SELECT AccountTypeID, Password, ConsultantID ' .
                  'FROM Consultants WHERE UserName=? AND ' . $pass_cond;
        }
        else if (USER_AUTH == 'advanced')
        {
            $pass = md5($pass);
            $qs = 'SELECT AccountTypeID, Password, ConsultantID, Active, PasswordExpires ' .
                  'FROM Consultants WHERE UserName=? AND ' . $pass_cond;
        }
    
        $query = $dbh->prepare($qs);
        $query->execute(array($user, $pass));
        
        $results = $query->fetchObject();
        
        if ($results === false)
        {
            throw new Exception('Invalid login credentials', LOGIN_BAD_CREDS);
        }
        
        /* --- For security reasons, only keep the password if it is already NULL --- */
        if ($results->Password != '')
        {
            unset($results->Password);
        }
    
        /* --- The user has been validated or NULL is returned --- */
        $this->consultant = $results;
    }
    
    public function handleValidationExceptions($code, $message)
    {
        if ($code == LOGIN_BLANK_USER)
        {
            // A blank form is a distinct possibility; let's do nothing.
        }
        else if ($code == LOGIN_BAD_CREDS)
        {
            $this->handleLoginFailed($message);
        }
        else
        {
            /* Because any thing could happen */
            throw new Exception($message, $code);
        }
    }
}
?>