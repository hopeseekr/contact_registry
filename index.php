<?php
//header("Location: http://" . $_SERVER['HTTP_HOST'] . '/login.php');
ob_start();

require_once('views/LoginView.inc');
$T = new LoginView('tpl/login.tpl');
echo $T->parse();

?>