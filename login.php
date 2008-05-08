<?php

ob_start();

require_once('views/LoginView.inc');
require_once('controllers/LoginController.inc');

$T = new LoginView('tpl/login.tpl');

if (isset($_POST['username']))
{    
    $guard = LoginController::getInstance();

    /* --- Make sure we have a valid, active user with an uptodate password --- */
    try
    {
        $guard->validateUser();
        $T->block('debug', array('print_r' => print_r($consultant, true)));
   }
    catch(Exception $e)
    {
        $guard->handleValidationExceptions($e->getCode(), $e->getMessage());
    }
}

echo $T->parse();


function login()
{
        $results = validateUser($_POST['username'], $_POST['password']);

        /* --- The user has been authenticated --- */
        if ($results != null)
        {
            if (USER_AUTH == 'advanced' && $results->active == 0)
            {
                print '<h2>Your account is not active.  Please contact your administrator.</h2>';
                
                return false;
            }

            session_start();

            $_SESSION['username'] = $_POST['username'];
            $_SESSION['ConsultantID'] = $results->ConsultantID;
            $_SESSION['AccountTypeID'] = $results->AccountTypeID;
            $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];

            if ((isset($results->Password) && $results->Password == '') || (USER_AUTH == 'advanced' && strtotime($results->PasswordExpires) < time()))
            {
                handleExpiredPassword();
            }
    
            if ($results->AccountTypeID == 2)
            {
                header("Location: http://" . $_SERVER['HTTP_HOST'] . '/customers.php');
                exit;
            }
            else if ($results->AccountTypeID == 3)
            {
                $_SESSION['admin'] = true;
                header("Location: http://" . $_SERVER['HTTP_HOST'] . '/admin/');
                exit;
            }
            else
            {
                trigger_error($_POST['username'] . ' has an invalid Account Type of ' . $results->AccountTypeID, E_USER_ERROR);
            }
        }
        else
        {
            print '<h1>Login failed!!</h1>';
            return false;
        }
}

?>