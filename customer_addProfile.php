<?php

if (!isset($_POST['RollupID']) || !isset($_POST['CreatorID']))
{
    $err = 'Parameters were not set; invalid session.';
    print "<h2>$err</h2>";
    trigger_error($err, E_USER_ERROR);
    exit;
}

//print '<pre>' . print_r($_POST, true) . '</pre>'; exit;
require_once('config.php');
$db = getDB();

mysql_connect($db['server'], $db['user'], $db['pass']);
mysql_select_db($db['database']);
print mysql_error();

$q1s = sprintf('INSERT INTO tblprofiles (RollupID, CreationDate, CreatorID, Called, ' .
               'Visited, Question1, Question2, Question3, Question4, Question5, Question6) ' .
               'VALUES (%d, NOW(), %d, %d, %d, "%s", "%s", "%s", "%s", "%s", "%s")',
               mysql_real_escape_string($_POST['RollupID']),
               mysql_real_escape_string($_POST['CreatorID']),
               mysql_real_escape_string($_POST['Called'] == 'on' ? 1 : 0),
               mysql_real_escape_string($_POST['Visited'] == 'on' ? 1 : 0),
               mysql_real_escape_string($_POST['Question1']),
               mysql_real_escape_string($_POST['Question2']),
               mysql_real_escape_string($_POST['Question3']),
               mysql_real_escape_string($_POST['Question4']),
               mysql_real_escape_string($_POST['Question5']),
               mysql_real_escape_string($_POST['Question6']));

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
