<?php
if (!isset($_POST['RollupID']) || !isset($_POST['CreatorID']))
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

$q1s = sprintf('UPDATE tblprofiles SET Called=%d, Visited=%d, ' .
               'Question1="%s", Question2="%s", Question3="%s", ' .
               'Question4="%s", Question5="%s", Question6="%s" ' .
               'WHERE ProfileID=%d',
               mysql_real_escape_string($_POST['Called'] == 'on' ? 1 : 0),
               mysql_real_escape_string($_POST['Visited'] == 'on' ? 1 : 0),
               mysql_real_escape_string($_POST['Question1']),
               mysql_real_escape_string($_POST['Question2']),
               mysql_real_escape_string($_POST['Question3']),
               mysql_real_escape_string($_POST['Question4']),
               mysql_real_escape_string($_POST['Question5']),
               mysql_real_escape_string($_POST['Question6']),
               mysql_real_escape_string($_POST['ProfileID']));

$params  = constructParams();

if (mysql_query($q1s) === false)
{
    header("Location: http://" . $_SERVER['HTTP_HOST'] . '/customers.php?customer_err=update+failed&reason=' . urlencode(mysql_error()) . "&$params");
}
else
{
    header("Location: http://" . $_SERVER['HTTP_HOST'] . '/customers.php' . "?$params");
}
?>