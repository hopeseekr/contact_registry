<?php

class ProfileController extends Controller implements ControllerInterface
{
	/**
	 * @var UserManager
	 */
	private $UserManager;

	public function __construct($engine_in)
    {
    	$this->UserManager = new UserManager;
    	
    	return parent::__construct($engine_in);
    }
    
    public function execute()
    {
        $action = $this->fetchAction();

        if ($action == null || $action == 'show profile')
        {
            $this->showProfileScreen();

            return;
        }

		$functionName = 'show' . ucfirst($action) . 'Screen';

		if (method_exists($this, $functionName))
		{
			$this->$functionName();
		}
        else
        {
        	$this->showProfileScreen();
        }
    }

	private function showProfileScreen()
	{
		print_r($_SESSION);
		$view = $this->view->createView('profile');
//		$view->block('changePassword', array('username' => $this->UserManager));
		echo $view->parse();
	}

	private function showChangePasswordScreen()
	{
		print '<pre>' . print_r($_SESSION, true) . '</pre>';
		$view = $this->view->createView('profile');
//		$view->block('rawHTML', array('rawHTML2' => print_r($_SESSION, true)));
		$view->block('changePassword', array('username' => $_SESSION['username']));
		echo $view->parse();
	}
}

