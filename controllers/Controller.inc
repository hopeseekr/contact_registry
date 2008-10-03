<?php

require_once('factories/ViewFactory.inc');

interface ControllerInterface
{
    public function execute($action = null);
}

class Controller implements ControllerInterface
{
    protected $engine;
    protected $params;

    protected $view;
    protected $manager;

    public function __construct($engine_in, $params_in)
    {
        $this->engine = $engine_in;
        $this->params = $params_in;

        $this->view = new ViewFactory($this->engine);
    }

    public function execute($action = null)
    {
    }

    protected function fetchAction(array $defaults_in = null)
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