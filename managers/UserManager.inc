<?php
require_once('lib/MyDatabase.inc.php');

class UserStates
{	
	const BLANK_USER = 'No user credentials entered.';
	const BAD_CREDS = 'Invalid login credentials.';
	const INACTIVE_USER = 'Your account is not active.  Please contact your administrator.';
	const EXPIRED_PASSWORD = 'Your password has expired.  Please contact your administrator.';
	const LOGGED_IN = 'You have successfully logged in.';
	
	private function __construct() { }
}

class UserManager extends Manager
{
	private static $instance;
	
    public function __construct()
    {
    	if (!is_null(self::$instance))
    	{
    		return self::$instance;
    	}
    	
    	self::$instance = $this;
    }

    public static function isAdmin()
    {
        if (isset($_SESSION['admin']) && $_SESSION['admin'] == 1 &&
            $_SESSION['accountTypeID'] == 3)
        {
            return true;
        }
        
        return false;
    }


	public function validateUser($user, $pass)
	{
		$DB = MyDB::loadDB();

		if ($pass == '')
		{
			$pass_cond = 'password IS NULL';
		}
		else
		{
			$pass = md5($pass);
			$pass_cond = 'password=?';
		}

        $qs = 'SELECT agentID, accountTypeID, password, isActive, passwordExpires ' .
              'FROM Agents WHERE username=? AND ' . $pass_cond;

		if ($pass == '')
		{
        	$DB->query($qs, array($user));
		}
		else
		{
			$DB->query($qs, array($user, $pass));
		}

		$agent = $DB->fetchObject();

        if ($agent === false)
        {
        	$this->signals->emit(UserStates::BAD_CREDS, UserStates::BAD_CREDS);
        	return false;
        }

        /* --- For security reasons, only keep the password if it is already NULL --- */
        if ($agent->password != '')
        {
            unset($agent->password);
        }

        /* --- The user's access creds are either valid or NULL is returned --- */
        $agent->username = $user;
    	
        if ($agent->isActive == 0)
        {
        	$this->signals->emit(UserStates::INACTIVE_USER, UserStates::INACTIVE_USER);
        	return false;
        }

        /* At this point, it is known for a fact that the user has access, so let's create their session. */
        if ((isset($agent->password) && $agent->password == '') || (strtotime($agent->passwordExpires) < time()))
        {
        	$this->signals->emit(UserStates::EXPIRED_PASSWORD, $agent);
        	return false;
        }

        /* At this point, the user has access and is good to go. */
        $this->signals->emit(UserStates::LOGGED_IN, $agent);

        return true;
    }
}
?>