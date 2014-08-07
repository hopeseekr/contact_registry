<?php

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
    }

    public function execute($action = null)
    {
    }

    protected function initialize($view, $params = null)
    {
        require_once('factories/ViewFactory.inc');
        $this->view = new ViewFactory($this->engine);

        $manager_name = $this->params->views->$view->name . 'Manager';
        $manager_file = realpath('managers/' . $manager_name . '.inc');

        require_once($manager_file);

        $this->manager = new $manager_name($params);
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
    }
}

?>