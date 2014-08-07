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
    }
    catch(Exception $e)
    {
        $code = $e->getCode();
        $msg = $e->getMessage();

        if ($code == LOGIN_BLANK_USER)
        {
            // A blank form is a distinct possibility; let's do nothing.
        }
        else if ($code == LOGIN_BAD_CREDS ||
                 $code == LOGIN_INACTIVE)
        {
            $T->handleLoginFailure($msg);
        }
        else if ($code == LOGIN_PASSWORD_EXPIRED)
        {
            $T->handleChangePassword();
        }
        else
        {
            /* Because any thing could happen */
            throw new Exception($msg, $code);
        }
    }

    /* The user is now logged in */
//    $consultant = $guard->getConsultant();
//    $T->block('debug', array('print_r' => print_r($consultant, true)));
    $T->handleLoginSuccess();
}

echo $T->parse();

?>
