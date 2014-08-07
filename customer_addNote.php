<?php

if (!isset($_POST['RollupID']) || !isset($_POST['ConsultantID']))
{
    $err = 'Parameters were not set; invalid session.';
    print "<h2>$err</h2>";
    trigger_error($err, E_USER_ERROR);
    exit;
}

//print '<pre>' . print_r($_POST, true) . '</pre>';
require_once('config.php');
$db = getDB();

mysql_connect($db['server'], $db['user'], $db['pass']);
mysql_select_db($db['database']);
print mysql_error();

$q1s = sprintf('INSERT INTO tblconsultantnotes (RollupID, CreationDate, ConsultantID, Notes) ' .
               'VALUES (%d, NOW(), %d, "%s")',
               mysql_real_escape_string($_POST['RollupID']),
               mysql_real_escape_string($_POST['ConsultantID']),
               mysql_real_escape_string($_POST['Notes']));

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
