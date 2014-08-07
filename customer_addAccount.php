<?php

if (!isset($_POST['RollupNumber']) || !isset($_POST['ContractAccount']))
{
    $err = 'Parameters were not set; invalid session.';
    print "<h2>$err</h2>";
    trigger_error($err, E_USER_ERROR);
    exit;
}
++$_POST['profile'];
require_once('config.inc');

require_once('lib/db.php');
db_connect();

$q1s = sprintf('INSERT INTO tblcontractaccounts (ContractAccount, RollupNumber) VALUES ("%s", %d)',
               mysql_real_escape_string($_POST['ContractAccount']),
               mysql_real_escape_string($_POST['RollupNumber']));

$params  = constructParams();

if (mysql_query($q1s) === false)
{
    header("Location: http://" . $_SERVER['HTTP_HOST'] . '/customers.php?accounts_err=insert+failed&reason=' . urlencode(mysql_error()) . '&ContractAccount=' . urlencode($_POST['ContractAccount']) . "&$params");
}
else
{
    header("Location: http://" . $_SERVER['HTTP_HOST'] . '/customers.php' . "?$params");
}
?>
