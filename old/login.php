<?php
ob_start();

require_once('config.inc');
require_once('views/ViewFactory.inc');

$viewFactory = new ViewFactory('xslt');
//$viewFactory = new ViewFactory('blitz');
$T = $viewFactory->createView('login');

echo $T->parse();

?>
