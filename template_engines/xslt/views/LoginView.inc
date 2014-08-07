<?php
require_once('lib/QuickDOM.inc');

class LoginView extends XSLTView
{
    public function __construct($tmpl_file)
    {
        $tmpl_file = realpath(dirname(__FILE__) . '/../tpl/' . $tmpl_file);

        if (!file_exists($tmpl_file))
        {
            throw new Exception('Cannot find XSLT template file: ' . $tmpl_file);
        }

        $this->xsl_file = $tmpl_file;
        $dom = new QuickDOM(); 

        $node1 = $dom->addQuickNode('root');
            $dom->addQuickNode('form_action');
                $dom->addQuickText('?' . $_SERVER['QUERY_STRING'] . '&action=login');
        $dom->addQuickNode('username', $node1);
                $dom->addQuickText('hopeseekr');
                
        $this->validateUser();
        $this->handleRawHTML();

        $this->doc = $dom->getDoc();
        
        return parent::__construct();
    }

    private function validateUser()
    {
        if (isset($_POST['username']))
        {
            $guard = new LoginManager;
            
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
            $this->handleLoginSuccess();
        }
    }

    private function handleChangePassword()
    {
        $_SESSION['expiredPassword'] = true;
        header("Location: https://" . $_SERVER['HTTP_HOST'] . '/changePassword.php');
        exit;
    }

    private function handleLoginFailure($err_msg = null)
    {
        $this->block('loginFailed', array('err_msg' => $err_msg));
    }

    private function handleLoginSuccess()
    {
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
}
?>