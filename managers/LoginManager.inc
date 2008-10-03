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
        if ($pass != '')
        {
            $pass_cond = 'Password=?';
        }
        else
        {
            $pass_cond = 'Password IS NULL';
        }

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

        $consultant = DatabaseManager::fetchObject($qs, array($user, $pass));

        if ($consultant === false)
        {
            throw new Exception('Invalid login credentials.', LOGIN_BAD_CREDS);
        }

        /* --- For security reasons, only keep the password if it is already NULL --- */
        if ($consultant->Password != '')
        {
            unset($consultant->Password);
        }

        /* --- The user's access creds are either valid or NULL is returned --- */
        $consultant->UserName = $user;

        return $consultant;
    }

    public function validateUser($user, $pass)
    {
        /* If the user cannot log in, an exception is thrown, negating the need for
          further error handling in this scope.  Neat, huh? */
        $consultant = $this->login($user, $pass);

        if (USER_AUTH == 'advanced' && $consultant->Active == 0)
        {
            throw new Exception('Your account is not active.  Please contact your administrator.', LOGIN_INACTIVE);
        }

        /* At this point, it is known for a fact that the user has access, so let's create their session. */
        if ((isset($consultant->Password) && $consultant->Password == '') || (USER_AUTH == 'advanced' && strtotime($consultant->PasswordExpires) < time()))
        {
            throw new Exception('Your password has expired.  Please contact your administrator.', LOGIN_PASSWORD_EXPIRED);
        }

        /* At this point, the user has access and is good to go. */
        return $consultant;
    }
}
?>
