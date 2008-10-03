<?php
if (!isset($_POST['RollupID']) || !isset($_POST['NoteID']))
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

$q1s = sprintf('UPDATE tblconsultantnotes SET Notes="%s" ' .
               'WHERE NoteID=%d',
               mysql_real_escape_string($_POST['Notes']),
               mysql_real_escape_string($_POST['NoteID']));

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