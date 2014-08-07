<?php

require_once('controllers/Controller.inc');

class LoginController extends Controller implements ControllerInterface
{
    private $consultant;

    public function execute($action = null)
    {
/*
        $manager_name = $this->params->views->$view->name . 'Manager';
        $manager_file = realpath('managers/' . $manager_name . '.inc');

        require_once($manager_file);

        $this->manager = new $manager_name($params);
 */
        $action = $this->fetchAction();

        if ($action == null || $action == 'show login screen')
        {
            $this->showLoginScreen();
        }
        else if ($action == 'login')
        {
            $this->validateUser();
        }
    }

    private function showLoginScreen()
    {
        $view = $this->view->createView('login');
        echo $view->parse();
    }

    private function validateUser()
    {
        if (!isset($_POST['username']) || $_POST['username'] == '' || !isset($_POST['password']))
        {
            throw new Exception('No user credentials entered.', LOGIN_BLANK_USER);
        }

        $user = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
        $pass = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);

        /* --- Make sure we have a valid, active user with an uptodate password --- */
        try
        {
            $guard = $this->manager;
            $this->consultant = $guard->validateUser($user, $pass);
        }
        catch(Exception $e)
        {
            $code = $e->getCode();
            $msg = $e->getMessage();

            if ($code == LOGIN_BLANK_USER)
            {
                // A blank form is a distinct possibility; let's do nothing.
                $this->showLoginScreen();
            }
            else if ($code == LOGIN_BAD_CREDS ||
                     $code == LOGIN_INACTIVE)
            {
                $this->handleLoginFailure($msg);
            }
            else if ($code == LOGIN_PASSWORD_EXPIRED)
            {
                $this->handleChangePassword();
            }
            else
            {
                /* Because any thing could happen */
                throw new Exception($msg, $code);
            }
        }

        /* At this point, it is known for a fact that the user has access, so let's create their session. */
        $this->handleLoginSuccess();
    }

    private function handleChangePassword()
    {
        /* At this point, it is known for a fact that the user has access, so let's create their session. */
        $this->startSession();
        $_SESSION['expiredPassword'] = true;
        header("Location: https://" . $_SERVER['HTTP_HOST'] . '/changePassword.php');
        exit;
    }

    private function handleLoginSuccess()
    {
        $this->startSession();

        if (!isset($_SESSION['AccountTypeID']) || !is_numeric($_SESSION['AccountTypeID']))
        {
            throw new Exception('AccountTypeID not passed in the _SESSION array.');
        }

        $accountTypeID = $_SESSION['AccountTypeID'];

        if ($accountTypeID == 2)
        {
            header("Location: https://" . $_SERVER['HTTP_HOST'] . '/page.php?view=customers');
            exit;
        }
        else if ($accountTypeID == 3)
        {
            $_SESSION['admin'] = true;
            header("Location: https://" . $_SERVER['HTTP_HOST'] . '/admin/');
            exit;
        }
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