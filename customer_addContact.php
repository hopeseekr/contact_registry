<?php

if (!isset($_POST['RollupID']) || !isset($_POST['ContactFirstName']))
{
    $err = 'Parameters were not set; invalid session.';
    print "<h2>$err</h2>";
    trigger_error($err, E_USER_ERROR);
    exit;
}

require_once('config.php');

require_once('lib/db.php');
db_connect();

$q1s = sprintf('INSERT INTO tblcustomercontacts (RollupID, ContactFirstName, ContactLastName, ' .
               'ContactNumber, ContactNumberTypeID, ContactEmail, RecordDate) ' .
               'VALUES (%d, "%s", "%s", "%s", %d, "%s", "NOW()")',
               mysql_real_escape_string($_POST['RollupID']),
               mysql_real_escape_string($_POST['ContactFirstName']),
               mysql_real_escape_string($_POST['ContactLastName']),
               mysql_real_escape_string($_POST['ContactNumber']),
               mysql_real_escape_string($_POST['ContactNumberTypeID']),
               mysql_real_escape_string($_POST['ContactEmail']));


if (mysq$params  = constructParams();

l_query($q1s) === false)
{
    header("Location: http://" . $_SERVER['HTTP_HOST'] . '/customers.php?accounts_err=insert+failed&reason=' . urlencode(mysql_error()) . '&ContractAccount=' . urlencode($_POST['ContractAccount']) . "&$params");
}
else
{
    header("Location: http://" . $_SERVER['HTTP_HOST'] . '/customers.php' . "?$params");
}
?>
