<?php

class BlitzViewFactory extends ViewFactory
{
    public function __construct()
    {
        parent::__construct('blitz');
    }

    public function createView($view)
    {
        $class_name = $view . 'View';
        $class_file = dirname(__FILE__) . '/views/' . $class_name . '.inc';

        if (!file_exists($class_file))
        {
            throw new Exception('Blitz ' . $class_name . ' implementation was not found: ' . $class_file);
        }

        require_once($class_file);

        $blitzView = new $class_name();
        
        return $blitzView;
    }
}

?>