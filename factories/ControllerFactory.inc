<?php

require('lib/params.inc');

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
        $engine = 'blitz';
        if (isset($_GET['engine']))
        {
            if (!isset($engines[$_GET['engine']]) || $engines[$_GET['engine']] === false)
            {
                throw new Exception('Unsupported engine.');
            }

            $engine = $_GET['engine'];
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

        $controller = new $class_name($this->engine, $this->params);

        return $controller;
    }
}
?>