<?php
if (!isset($_POST['ContactID']) || !isset($_POST['ContactFirstName']))
{
    $err = 'Parameters were not set; invalid session.';
    print "<h2>$err</h2>";
    trigger_error($err, E_USER_ERROR);
    exit;
}

require_once('config.php');
$db = getDB();

mysql_connect($db['server'], $db['user'], $db['pass']);
mysql_select_db($db['database']);
print mysql_error();

$q1s = sprintf('UPDATE tblcustomercontacts SET ContactFirstName="%s", ContactLastName="%s", ' .
               'ContactNumber="%s", ContactNumberTypeID=%d, ContactEmail="%s", RecordDate=NOW() ' .
               'WHERE ContactID=%d',
               mysql_real_escape_string($_POST['ContactFirstName']),
               mysql_real_escape_string($_POST['ContactLastName']),
               mysql_real_escape_string($_POST['ContactNumber']),
               mysql_real_escape_string($_POST['ContactNumberTypeID']),
               mysql_real_escape_string($_POST['ContactEmail']),
               mysql_real_escape_string($_POST['ContactID']));

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