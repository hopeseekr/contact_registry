<?php
session_start();

ob_start();

require_once('config.php');
require_once('factories/ControllerFactory.inc');

$views = array('login' => true,
               'customers' => true);

if (!isset($_GET['view']))
{
    throw new Exception('No view parameter passed');
}
else
{
    if (!isset($views[$_GET['view']]) || $views[$_GET['view']] === false)
    {
        throw new Exception('Unsupported view');
    }

    $controller = $_GET['view'];
}
//xdebug_break();
$controllerFactory = new ControllerFactory();
$c = $controllerFactory->loadController($controller);
$c->execute();
exit;


$viewFactory = new ViewFactory($engine);
$T = $viewFactory->createView($view);
//require_once('template_engines/blitz/views/CustomerView.inc');

echo $T->parse();
?>