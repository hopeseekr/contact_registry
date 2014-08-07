<?php

require('lib/utilities.inc');

class ControllerFactory
{
    private $engine;
    private $params;
    private static $instance;

    public function __construct()
    {
        // Singleton pattern
        if (isset(self::$instance))
        {
            return self::$instance;
        }

        self::$instance = $this;
   
        $engines = array('blitz' => true,
                         'xslt' => true);
        $engine = 'xslt';
        if (isset($_SESSION['template_engine']))
        {
            if (!isset($engines[$_SESSION['template_engine']]) || $engines[$_SESSION['template_engine']] === false)
            {
                throw new Exception('Unsupported engine.');
            }

            $engine = $_SESSION['template_engine'];
        }

        $this->engine = $engine;
        $this->params = fetchParams($engine);

        return self::$instance;
    }

    public function loadController($controller)
    {
        $class_name = $this->params->views->$controller->name . 'Controller';
        $class_file = realpath('controllers/' . $class_name . '.inc');

        require($class_file);

        $controller = new $class_name($this->engine);

        return $controller;
    }
}
?>
