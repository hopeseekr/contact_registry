<?php
class LoginView extends Blitz
{
    private $news = array();
    
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
        if (!isset($_REQUEST['username']))
        {
            $user = 'Guest';
        }
        else
        {
            $user = $_REQUEST['username'];
        }
        print 'asdf';

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

            $this->set(array('rawHTML' => $rawHTML));
        }
    }
}
?>