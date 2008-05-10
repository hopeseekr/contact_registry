<?php
interface BasicViewInterface
{
    public function handleRawHTML();
}

interface LoginViewInterface
{
    public function registerUser();
    
}

class ViewFactory
{
    private $engine;
    
    public function __construct($engine)
    {
        $this->engine = $engine;
    }
    
    private function findView($class_name, $class_file)
    {
        if (!file_exists($class_file))
        {
            throw new Exception('Template Engine\'s Factory implementation was not found: ' . $class_file);
        }

        require_once($class_file);

        $viewFactory = new $class_name();
        
        return $viewFactory;
    }

    public function createView($view)
    {
        $class_name = ucfirst($this->engine) . 'ViewFactory';
        $class_file = 'template_engines/' . $this->engine . '/' . $class_name . '.inc';

        $viewFactory = $this->findView($class_name, $class_file);
        
        return $viewFactory->createView($view);
    }
}
?>