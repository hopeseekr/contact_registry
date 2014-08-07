<?php

if (!isset($_POST['RollupID']) || !isset($_POST['ConsultantID']))
{
    $err = 'Parameters were not set; invalid session.';
    print "<h2>$err</h2>";
    trigger_error($err, E_USER_ERROR);
    exit;
}

//print '<pre>' . print_r($_POST, true) . '</pre>';
require_once('config.inc');

require_once('lib/db.php');
db_connect();
$dbh = $GLOBALS['dbh'];

$q1s = 'INSERT INTO tblconsultantnotes (RollupID, CreationDate, ConsultantID, Notes) VALUES (?, "' . date('c') . '", ?, ?)';
printf('INSERT INTO tblconsultantnotes (RollupID, CreationDate, ConsultantID, Notes) VALUES (%d, "' . date('c') . '", %d, "%s")',
       $_POST['RollupID'],
       $_POST['ConsultantID'],
       $_POST['Notes']);
$qs = $dbh->prepare($q1s);

//$qs->execute(array($_POST['RollupID'], $_POST['ConsultantID'], $_POST['Notes']));
print_r($_POST['RollupID']);
exit;
$params  = constructParams();

if ($qs->rowCount() == 0)
{
    $err_msg = $qs->errorInfo();

    header("Location: http://" . $_SERVER['HTTP_HOST'] . '/customers.php?accounts_err=insert+failed&reason=' . urlencode($err_msg[2]) . '&ContractAccount=' . urlencode($_POST['ContractAccount']) . "&$params");
}
else
{
    header("Location: http://" . $_SERVER['HTTP_HOST'] . '/customers.php' . "?$params");
}
?>
