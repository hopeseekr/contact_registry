<?php

require_once('factories/ViewFactory.inc');

interface ControllerInterface
{
	public function __construct($engine_in);
    public function execute();
}

class Controller implements ControllerInterface
{
	/**
	 * @var ViewInterface
	 */
	protected $view;

    public function __construct($engine_in)
    {
        $this->view = new ViewFactory($engine_in);
    }

    public function execute()
    {
    }

    protected function fetchAction()
    {
        $action = null;
        if (isset($_POST['action']))
        {
            $action = filter_input(INPUT_POST, 'action', FILTER_SANITIZE_STRING);
        }
        else if (isset($_GET['action']))
        {
            $action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_STRING);
        }
        
        return $action;

/* --- Maybe I'll use this someday, but since i already did it, don't wanna del it
 * lol! and now, a year later, I have no fucking idea what the below code does >< -Ted 5/5/2009
        if ($action != null)
        {
            return $action;
        }
        else
        {
			// $defaults_in['POST']['username'] = 'login';
            if ($defaults_in == null) { return null; }
            
            if (isset($defaults_in['POST']))
            {
                $term = $defaults_in['POST'];
                $key = key($term);

                if (isset($_POST[$key]))
                {
                    return $term[$key];
                }
            }
            else if (isset($defaults_in['GET']))
            {
                $term = $defaults_in['GET'];
                $key = key($term);

                if (isset($_GET[$key]))
                {
                    return $term[$key];
                }
            }
            else if (isset($defaults_in['SESSION']))
            {
                $term = $defaults_in['SESSION'];
                $key = key($term);

                if (isset($_SESSION[$key]))
                {
                    return $term[$key];
                }
            }
        }
*/
    }
        
}

?>