<?php
class LoginView extends Blitz
{
    public function __construct($tmpl_name)
    {
        $status = parent::Blitz($tmpl_name);
        $this->registerUser();
        $this->set(array('title' => 'Consultant Login'));
        $this->handleRawHTML();
        
        return $status;
    }
    
    public function registerUser()
    {
        $user = '';
        if (isset($_REQUEST['username']))
        {
            $user = filter_input(INPUT_GET, 'username', FILTER_SANITIZE_STRING);
        }

        $this->set(array('username' => $user));
        return $user;
    }
    
    public function handleRawHTML()
    {
        /* --- See if there is non-templated output. --- */
        if (ob_get_length() > 0)
        {
            trigger_error('There is raw, non-templated HTML output!', E_USER_NOTICE);
            $rawHTML = ob_get_clean();
            // Restart ob_start()
            ob_start();

            $this->block('rawHTML', array('rawHTML' => $rawHTML));
        }
    }
    
    public function handleChangePassword()
    {
        $_SESSION['expiredPassword'] = true;
        header("Location: https://" . $_SERVER['HTTP_HOST'] . '/changePassword.php');
        exit;
    }

    public function handleLoginFailure($err_msg = null)
    {
        $this->block('loginFailed', array('err_msg' => $err_msg));
    }

    public function handleLoginSuccess()
    {
        if (!isset($_SESSION['AccountTypeID']) || !is_numeric($_SESSION['AccountTypeID']))
        {
            throw new Exception('AccountTypeID not passed in the _SESSION array.');
        }
        
        $accountTypeID = $_SESSION['AccountTypeID'];

        if ($accountTypeID == 2)
        {
            header("Location: https://" . $_SERVER['HTTP_HOST'] . '/customers.php');
            exit;
        }
        else if ($accountTypeID == 3)
        {
            $_SESSION['admin'] = true;
            header("Location: https://" . $_SERVER['HTTP_HOST'] . '/admin/');
            exit;
        }
    }
}
?>