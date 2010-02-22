<?php
$fetch_amount = 500;
require_once('config.inc');         // Needed for validateSession() and getDB()
session_start();
ob_start();

if (!isset($_SESSION['ConsultantID']) || !validateSession())
{
    logout();
    trigger_error('Parameters were not set; invalid login.', E_USER_ERROR);
}

$ConsultantID = $_SESSION['ConsultantID'];
$db = getDB();

mysql_connect($db['server'], $db['user'], $db['pass']);
mysql_select_db($db['database']);
print mysql_error();

function printRecordNav($record, $num_rows, $fetch_amount)
{
    if ($record >= $fetch_amount)
    {
        print '<strong><a href="viewRecords.php?record=' . ($record - $fetch_amount) . '">Prev ' . $fetch_amount . ' records</a></strong> | ';
    }

    if ($num_rows == $fetch_amount)
    {
        print '<strong><a href="viewRecords.php?record=' . ($record + $fetch_amount) . '">Next ' . $fetch_amount . ' records</a></strong>';
    }
}

$record = 0;
if (isset($_GET['record']) && is_numeric($_GET['record']))
{
    $record = $_GET['record'];
}

printHeader('View All Customers');

$qs = 'SELECT tblcustomers.*, SegmentName, LPBType, SegmentName, TerritoryName FROM `tblallocation log` ' .
      'Inner Join tblcustomers ON `tblallocation log`.RollupID = tblcustomers.RollupID ' .
      'JOIN tblsegment ON tblcustomers.Segment=tblsegment.SegmentID ' .
      'JOIN tbllpbtypes USING(LPBID) ' .
      'JOIN tblterritory ON tblcustomers.Territory=tblterritory.TerritoryID ' .
      'WHERE ConsultantID=' . $ConsultantID . ' ' .
      'ORDER BY tblcustomers.RollupID ' .
      'LIMIT ' . $record . ', ' . $fetch_amount;

if (isAdmin() == true)
{
    $qs = 'SELECT tblcustomers.*, SegmentName, LPBType, SegmentName, TerritoryName FROM `tblallocation log` ' .
          'Inner Join tblcustomers ON `tblallocation log`.RollupID = tblcustomers.RollupID ' .
          'JOIN tblsegment ON tblcustomers.Segment=tblsegment.SegmentID ' .
          'JOIN tbllpbtypes USING(LPBID) ' .
          'JOIN tblterritory ON tblcustomers.Territory=tblterritory.TerritoryID ' .
          'ORDER BY tblcustomers.RollupID ' .
          'LIMIT ' . $record . ', ' . $fetch_amount;
}

$qq = mysql_query($qs);
$num_rows = mysql_numrows($qq);

?>
        <script src="sorttable.js"></script>
        <div class="top" id="var_main">
            <h2>View All Customers</h2>
<?php
print printRecordNav($record, $num_rows, $fetch_amount); 
?>
            <table class="sortable" border="1">
                <tr>
                    <th>#</th>
                    <th>ID</th>
                    <th>Customer</th>
                    <th>Address</th>
                    <th>City</th>
                    <th>State</th>
                    <th>ZipCode</th>
                    <th>Zip4</th>
                    <th>&nbsp;&nbsp;TotalZip</th>
                    <th>Meter Qty.</th>
                    <th>Segment</th>
                    <th>LPBID</th>
                    <th>Territory</th>
                </tr>
<?php    
$count = $record; 
while ($r = mysql_fetch_object($qq))
{
?>                      <!--<a href="url">Text of Link that you click</a>-->
                <tr>
                    <td><a href="customers.php?record=<?php echo $count; ?>"><?php echo ($count+1); ?></a></td>
                    <td><?php echo $r->RollupID; ?></td>
                    <td><?php echo $r->CustomerName; ?></td>
                    <td><?php echo $r->Address; ?></td>
                    <td><?php echo $r->City; ?></td>
                    <td><?php echo $r->State; ?></td>
                    <td><?php echo $r->Zipcode; ?></td>
                    <td><?php echo $r->Zip4; ?></td>
                    <td class="TotalZip"><?php echo $r->NumberOfCompanyByZip; ?></td>
                    <td><?php echo $r->Meters; ?></td>
                    <td><?php echo $r->SegmentName; ?></td>
                    <td><?php echo $r->LPBType; ?></td>
                    <td><?php echo $r->TerritoryName; ?></td>
                </tr>
<?php
    ++$count;
}    
?>
            </table>
<?php            
print printRecordNav($record, $num_rows, $fetch_amount);
?>
        </div>
    </body>
</html>