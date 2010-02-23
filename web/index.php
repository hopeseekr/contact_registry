<?php
session_start();
ob_start();

chdir('..');

require_once('config.inc');

require_once "ezc/Base/src/base.php"; // dependent on installation method, see below
function __autoload( $className )
{
	if (strpos($className, 'Factory') !== false)
	{
		require_once "factories/$className.inc";
	}
	else if (strpos($className, 'Controller') !== false)
	{
		require_once "controllers/$className.inc";
	}
	else if (strpos($className, 'Manager') !== false)
	{
		require_once "managers/$className.inc";
	}
	else
	{
		ezcBase::autoload( $className );
	}	
}

//require_once 'controllers/Controller.inc';
//require_once 'managers/UserManager.inc';

// FIXME: views should come directly from <template_engine>/params.json.
$views = array('login' => true,
               'customers' => true,
               'profile' => true);

// Default to the login view if none is selected.
if (!isset($_GET['view']))
{
    $_GET['view'] = 'login';
}

if (!isset($views[$_GET['view']]) || $views[$_GET['view']] === false)
{
	throw new Exception('Unsupported view');
}

$controller = $_GET['view'];

$controllerFactory = new ControllerFactory();
$c = $controllerFactory->loadController($controller);
$c->execute();
