<?php
define('LOGIN_BLANK_USER', 1);
define('LOGIN_BAD_CREDS', 2);
define('LOGIN_INACTIVE', 3);
define('LOGIN_PASSWORD_EXPIRED', 4);

require_once('DatabaseController.inc');

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

    private function login()
    {
        if (!isset($_POST['username']) || $_POST['username'] == '' || !isset($_POST['password']))
        {
            throw new Exception('No user credentials entered.', LOGIN_BLANK_USER);
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
            throw new Exception('Invalid login credentials.', LOGIN_BAD_CREDS);
        }
        
        /* --- For security reasons, only keep the password if it is already NULL --- */
        if ($results->Password != '')
        {
            unset($results->Password);
        }
    
        /* --- The user's access creds are either valid or NULL is returned --- */
        $this->consultant = $results;
        $this->consultant->UserName = $user;
    }
    
    public function validateUser()
    {
        /* If the user cannot log in, an exception is thrown, negating the need for
          further error handling in this scope.  Neat, huh? */
        $this->login();
        
        if (USER_AUTH == 'advanced' && $this->consultant->Active == 0)
        {
            throw new Exception('Your account is not active.  Please contact your administrator.', LOGIN_INACTIVE);
        }
         
        /* At this point, it is known for a fact that the user has access, so let's create their session. */
        $this->startSession();
        
        if ((isset($this->consultant->Password) && $this->consultant->Password == '') || (USER_AUTH == 'advanced' && strtotime($this->consultant->PasswordExpires) < time()))
        {
            throw new Exception('Your password has expired.  Please contact your administrator.', LOGIN_PASSWORD_EXPIRED);
        }
        
        /* At this point, the user has access and is good to go. */
    }
    
    public function getConsultant()
    {
        return $this->consultant;
    }
    
    private function startSession()
    {
        session_start();

        $_SESSION['username'] = $this->consultant->UserName;
        $_SESSION['ConsultantID'] = $this->consultant->ConsultantID;
        $_SESSION['AccountTypeID'] = $this->consultant->AccountTypeID;
        $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
    }
}
?>