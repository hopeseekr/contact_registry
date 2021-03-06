<?php

class LoginController extends Controller implements ControllerInterface
{
	/**
	 * @var UserManager
	 */
	private $UserManager;
    private $agent;

    public function __construct($engine_in)
    {
    	$this->UserManager = new UserManager;
    	
    	return parent::__construct($engine_in);
    }
    
    public function execute()
    {
        $action = $this->fetchAction();

        if ($action == null || $action == 'show login screen')
        {
            $this->showLoginScreen();
        }
        else if ($action == 'login')
        {
            $this->login();
        }
    }

	private function showLoginScreen()
	{
		$view = $this->view->createView('login');
		echo $view->parse();
	}

	private function login()
	{
		if (!isset($_POST['username']) || $_POST['username'] == '' || !isset($_POST['password']))
		{
			$this->showLoginScreen();
			return;
		}

		$user = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
		$pass = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
		
		$guard = $this->UserManager;
		$signals = $guard->signals();
		$signals->connect(UserStates::EXPIRED_PASSWORD, array($this, 'handleChangePassword'));
		$signals->connect(UserStates::BAD_CREDS, array($this, 'handleLoginFailure'));
		$signals->connect(UserStates::INACTIVE_USER, array($this, 'handleLoginFailure'));
		$signals->connect(UserStates::LOGGED_IN, array($this, 'handleLoginSuccess'));
		$this->agent = $guard->validateUser($user, $pass);
	}

    public function handleChangePassword(stdClass $agent)
    {
    	$this->agent = $agent;

    	/* At this point, it is known for a fact that the user has access, so let's create their session. */
        $this->startSession();
        $_SESSION['expiredPassword'] = true;
        header('Location: ' . site_uri('profile', 'changePassword'));
        exit;
    }

    public function handleLoginSuccess(stdClass $agent)
    {
		$this->agent = $agent;
        $this->startSession();

        if (!isset($_SESSION['accountTypeID']) || !is_numeric($_SESSION['accountTypeID']))
        {
            throw new Exception('accountTypeID not passed in the _SESSION array.');
        }

        $accountTypeID = $_SESSION['accountTypeID'];

        if ($accountTypeID == 2)
        {
            header('Location: ' . site_uri('customers'));
            exit;
        }
        else if ($accountTypeID == 3)
        {
            $_SESSION['admin'] = true;
            header('Location: ' . site_uri('admin'));
            exit;
        }
    }

	public function handleLoginFailure($reason)
	{
		$view = $this->view->createView('login');
		$view->block('loginFailed', array('err_msg' => $reason));
		echo $view->parse();
	}
    
    private function startSession()
    {
        $_SESSION['username'] = $this->agent->username;
        $_SESSION['agentID'] = $this->agent->agentID;
        $_SESSION['accountTypeID'] = $this->agent->accountTypeID;
        $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
    }
}

?>