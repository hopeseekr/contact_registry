<?php
require_once('config.php');         // Needed for validateSession() and getDB()
session_start();

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

if (isset($_REQUEST['record']) && is_numeric($_REQUEST['record']))
{
    $start = mysql_real_escape_string($_REQUEST['record']);
}
else
{
    $start = 0;
}

function getCustomersCount($ConsultantID)
{
    $q1s = 'SELECT COUNT(tblcustomers.RollupID) AS CustomerCount ' .
           'FROM `tblallocation log` Inner Join tblcustomers ON `tblallocation log`.RollupID = tblcustomers.RollupID ' .
           'WHERE ConsultantID=' . $ConsultantID;
    
    if (isAdmin() == true)
    {
        $q1s = 'SELECT COUNT(tblcustomers.RollupID) AS CustomerCount FROM tblcustomers';
    }

    $q1q = mysql_query($q1s);
    $count = mysql_result($q1q, 0);
    
    return $count;
}

function getCustomerInformation($ConsultantID, $start = 0)
{
    if (isset($_GET['customer_err']) && $_GET['accounts_err'] == 'update failed')
    {
        $err_msg = 'Update of Customer failed: ' . $_GET['reason'] . '.';
    }

    $q1s = 'SELECT tblcustomers.* FROM `tblallocation log` Inner Join tblcustomers ON `tblallocation log`.RollupID = tblcustomers.RollupID ' .
           'WHERE ConsultantID=' . $ConsultantID . ' ORDER BY tblcustomers.RollupID LIMIT ' . $start . ', 1';

    if (isAdmin() == true)
    {
        $q1s = 'SELECT tblcustomers.* FROM tblcustomers LIMIT ' . $start . ', 1';
    }

    $q1q = mysql_query($q1s);
    $results = mysql_fetch_object($q1q);

    $q2s = 'SELECT SegmentID, SegmentName FROM tblsegment';
    $q2q = mysql_query($q2s);

    while ($q2r = mysql_fetch_object($q2q))
    {
        $segments[$q2r->SegmentID] = $q2r->SegmentName;
    }

    $q3s = 'SELECT LPBID, LPBType FROM tbllpbtypes';
    $q3q = mysql_query($q3s);

    while ($q3r = mysql_fetch_object($q3q))
    {
        $LPBIDs[$q3r->LPBID] = $q3r->LPBType;
    }

    $q4s = 'SELECT TerritoryID, TerritoryName FROM tblterritory';
    $q4q = mysql_query($q4s);

    while ($q4r = mysql_fetch_object($q4q))
    {
        $territories[$q4r->TerritoryID] = $q4r->TerritoryName;
    }
    
    return array('customer' => $results,
                 'segments' => $segments,
                 'LPBIDs' => $LPBIDs,
                 'territories' => $territories,
                 'err_msg' => $err_msg);
}

/* --- Due to a quirk in DB design, RollupID is called RollupNumber in tblcontractaccounts --- */
function getContractAccounts($RollupID)
{
    if (isset($_GET['deleteCA']) && isset($_GET['ContractAccount']) && isAdmin())
    {
        $qs = 'DELETE FROM tblcontractaccounts WHERE ContractAccount="' . mysql_real_escape_string($_GET['ContractAccount']) . '"';
        $qq = mysql_query($qs);

        if ($qq == false)
        {
            header("Location: http://" . $_SERVER['HTTP_HOST'] . '/customers.php?customer_err=update+failed&reason=Contract+Account+could+not+be+deleted' . "&$params");
        }
        else
        {
            header("Location: http://" . $_SERVER['HTTP_HOST'] . '/customers.php?customer_err=update+failed&reason=Contract+Account+deleted' . "&$params");
        }
    }

    if (isset($_GET['accounts_err']) && $_GET['accounts_err'] == 'insert failed')
    {
        $err_msg = 'Insert of Contract Account ' . $_GET['ContractAccount'] . ' failed: ' . $_GET['reason'] . '.';
    }

    $q1s = 'SELECT ContractAccount FROM tblcontractaccounts WHERE RollupNumber=' . $RollupID;
    $q1q = mysql_query($q1s);
    
    while ($q1r = mysql_fetch_object($q1q))
    {
        $accounts[] = $q1r->ContractAccount;
    }
    
    return array($accounts, $err_msg);
}

function getCustomerContacts($RollupID)
{
    $q1s = 'SELECT ContactNumberTypeID, ContactNumberType FROM tblcontactnumbertypes';
    $q1q = mysql_query($q1s);
    
    while ($q1r = mysql_fetch_object($q1q))
    {
        $types[$q1r->ContactNumberTypeID] = $q1r->ContactNumberType;
    }
    
    $q2s = 'SELECT ContactID, ContactFirstName, ContactLastName, ContactNumber, ContactNumberTypeID, ' .
           'ContactNumberType, ContactEmail, RecordDate FROM tblcustomercontacts ' .
           'LEFT JOIN tblcontactnumbertypes USING (ContactNumberTypeID) ' .
           'WHERE RollupID=' . $RollupID;
    $q2q = mysql_query($q2s);

    while ($q2r = mysql_fetch_object($q2q))
    {
        $contacts[$q2r->ContactID] = $q2r;
    }

    return array('types' => $types,
                 'contacts' => $contacts);
}

function constructOptions($values, $needle = null)
{
    $options = '';
    foreach ($values as $id => $value)
    {
        if ($id == $needle) { $sel = " selected=\"selected\""; } else { $sel = ""; }
        $options .= sprintf('<option value="%d"%s>%s</option>' . "\n", $id, $sel, $value);
    }
    
    return $options;
}

if (isset($_REQUEST['profile']) && is_numeric($_REQUEST['profile']))
{
    $pStart = mysql_real_escape_string($_REQUEST['profile']);
}
else
{
    $pStart = 0;
}

function getProfilesCount($RollupID)
{
    $q1s = 'SELECT COUNT(RollupID) AS CustomerCount FROM `tblprofiles` ' .
           'WHERE RollupID=' . $RollupID;
    $q1q = mysql_query($q1s);
    $count = mysql_result($q1q, 0);

    return $count;
}

function getCustomerProfile($ConsultantID, $RollupID, $pStart = 0)
{
    if (isset($_POST['deleteP']) && isset($_POST['ProfileID']) && isAdmin())
    {
        $qs = 'DELETE FROM tblprofiles WHERE ProfileID=' . mysql_real_escape_string($_POST['ProfileID']);
        $qq = mysql_query($qs);

        if ($qq == false)
        {
            header("Location: http://" . $_SERVER['HTTP_HOST'] . '/customers.php?customer_err=update+failed&reason=Profile+could+not+be+deleted' . "&$params");
        }
        else
        {
            header("Location: http://" . $_SERVER['HTTP_HOST'] . '/customers.php?customer_err=update+failed&reason=Profile+deleted' . "&$params");
        }
    }

    if (!isset($_GET['new']))
    {
        $q1s = 'SELECT *, UserName AS CreatorUsername FROM tblprofiles JOIN tblconsultants ON ConsultantID=CreatorID WHERE RollupID=' . mysql_real_escape_string($RollupID) .
               ' LIMIT ' . $pStart . ', 1';
        $q1q = mysql_query($q1s);
        
        $profile = new stdClass;
        if (mysql_numrows($q1q) > 0)
        {
            /* There should only ever be 1 profile per customer. */
            $profile = mysql_fetch_object($q1q);
            
            return $profile;
        }
    }    

    $_GET['new'] = true;

    $q2s = 'SELECT UserName FROM tblconsultants WHERE ConsultantID=' . $ConsultantID;
    $q2q = mysql_query($q2s);
    $username = mysql_result($q2q, 0);

    $profile->RollupID = $RollupID;
    $profile->CreationDate = 'null';
    $profile->CreatorID = $ConsultantID;
    $profile->Called = false;
    $profile->Visited = false;
    $profile->Question1 = '';
    $profile->Question2 = '';
    $profile->Question3 = '';
    $profile->Question4 = '';
    $profile->Question5 = '';
    $profile->Question6 = '';
    $profile->CreatorUsername = $username;

    return $profile;
}

if (isset($_REQUEST['note']) && is_numeric($_REQUEST['note']))
{
    $nStart = mysql_real_escape_string($_REQUEST['note']);
}
else
{
    $nStart = 0;
}

function getNotesCount($RollupID, $ConsultantID)
{
    $q1s = 'SELECT COUNT(RollupID) FROM tblconsultantnotes ' .
           'WHERE RollupID=' . $RollupID . ' AND ConsultantID=' . $ConsultantID;
    $q1q = mysql_query($q1s);
    $count = mysql_result($q1q, 0);

    return $count;    
}

function getConsultantNote($RollupID, $ConsultantID, $nStart = 0)
{
    if (isset($_POST['deleteN']) && isset($_POST['NoteID']) && isAdmin())
    {
        $qs = 'DELETE FROM tblconsultantnotes WHERE NoteID=' . mysql_real_escape_string($_POST['NoteID']);
        $qq = mysql_query($qs);

        if ($qq == false)
        {
            header("Location: http://" . $_SERVER['HTTP_HOST'] . '/customers.php?customer_err=update+failed&reason=Note+could+not+be+deleted' . "&$params");
        }
        else
        {
            header("Location: http://" . $_SERVER['HTTP_HOST'] . '/customers.php?customer_err=update+failed&reason=Note+deleted' . "&$params");
        }
    }

    if (!isset($_GET['newN']))
    {
        $q1s = 'SELECT *, UserName AS CreatorUsername FROM tblconsultantnotes ' .
               'JOIN tblconsultants USING(ConsultantID) ' .
               'WHERE RollupID=' . $RollupID . ' AND ConsultantID=' . $ConsultantID .
               ' LIMIT ' . $nStart . ', 1';
        $q1q = mysql_query($q1s);

        $note = new stdClass;
        if (mysql_numrows($q1q) > 0)
        {
            /* There should only ever be 1 profile per query. */
            $note = mysql_fetch_object($q1q);
            
            return $note;
        }
    }    

    $_GET['newN'] = true;

    $q2s = 'SELECT UserName FROM tblconsultants WHERE ConsultantID=' . $ConsultantID;
    $q2q = mysql_query($q2s);
    $username = mysql_result($q2q, 0);

    $note->RollupID = $RollupID;
    $note->CreationDate = 'null';
    $note->ConsultantID = $ConsultantID;
    $note->Notes = '';
    $note->CreatorUsername = $username;

    return $note;
}

$customerCount = getCustomersCount($ConsultantID);
$r = getCustomerInformation($ConsultantID, $start);
$accounts = getContractAccounts($r['customer']->RollupID);
$contacts = getCustomerContacts($r['customer']->RollupID);
$profileCount = getProfilesCount($r['customer']->RollupID);
$profile = getCustomerProfile($ConsultantID, $r['customer']->RollupID, $pStart);
$noteCount = getNotesCount($r['customer']->RollupID, $ConsultantID);
$note = getConsultantNote($r['customer']->RollupID, $ConsultantID);
?>
<html>
    <head>
        <title>
            Customers
        </title>
        <link rel="stylesheet" type="text/css" href="style.css"/>
    </head>
    <body>
        <div id="header">
            <img src="reliant_logo.jpg" alt="Reliant Logo"/>
            <h1>Reliant Small Business Customer</h1>
            <div id="topnav">
                <a href="changePassword.php">Change Password</a> |
                <a href="logout.php">Logout</a>
            </div>
        </div>
        <br style="clear: both"/>
        <div class="top" id="customer_information">
            <div id= "view_records">
                <form method="get" action ="viewRecords.php">
                    <input type="submit" value="View All Records"/>            
                </form> 
            </div>
<?php
if (isset($_GET['customer_err']))
{
?>
            <div class="err_msg">
                <?php echo $_GET['reason']; ?>
            </div>
<?php
}
?>
            <div class="summary">
<?php
if ($start > 0)
{
    print '<a href="?record=' . ($start - 1) . '">&lt;</a>';
}
?>
                Customer <?php echo ($start + 1); ?> of <?php echo $customerCount; ?> 
<?php
if ($start < $customerCount - 1)
{
    print '<a href="?record=' . ($start + 1) . '">&gt;</a>';
}
?>
            </div>
            <form method="post" action="customer_edit.php">
                <input type="hidden" name="record" value="<?php echo $start; ?>"/>
                <input type="hidden" name="profile" value="<?php echo $pStart; ?>"/>
                <input type="hidden" name="note" value="<?php echo $nStart; ?>"/>
                <input type="hidden" name="RollupID" value="<?php echo $r['customer']->RollupID; ?>"/>
                <table>
                    <tr>
                        <th>Rollup ID:</th>
                        <td style="width: 50%"><?php echo $r['customer']->RollupID; ?></td>
                    </tr>
                    <tr>
                        <th>Customer:</th>
                        <td><input type="text" name="CustomerName" value="<?php echo $r['customer']->CustomerName; ?>"/></td>
                    </tr>
                    <tr>
                        <th>Address:</th>
                        <td><input type="text" name="Address" value="<?php echo $r['customer']->Address; ?>"/></td>
                    </tr>
                    <tr>
                        <th>City:</th>
                        <td><input type="text" name="City" value="<?php echo $r['customer']->City; ?>"/></td>
                    </tr>
                    <tr>
                        <th>State:</th>
                        <td><input type="text" name="State" value="<?php echo $r['customer']->State; ?>"/></td>
                    </tr>
                    <tr>
                        <th>Zip code:</th>
                        <td><input type="text" name="Zipcode" value="<?php echo $r['customer']->Zipcode; ?>"/></td>
                    </tr>
                    <tr>
                        <th>Zip 4:</th>
                        <td><input type="text" name="Zip4" value="<?php echo $r['customer']->Zip4; ?>"/></td>
                    </tr>
                    <tr>
                        <th>Total # by Zip:</th>
                        <td><input type="text" name="NumberOfCompanyByZip" value="<?php echo $r['customer']->NumberOfCompanyByZip; ?>"/></td>
                    </tr>
                    <tr>
                        <th>Meter Qty:</th>
                        <td><input type="text" name="Meters" value="<?php echo $r['customer']->Meters; ?>"/></td>
                    </tr>
                    <tr>
                        <th>LPBID:</th>
                        <td>
<?php
if (!isAdmin())
{
    $disabled2=' disabled="disabled"';
}
?>                        
                            <select name="LPBID"<?php echo $disabled2; ?>>
                                <?php print constructOptions($r['LPBIDs'], $r['customer']->LPBID); ?>                            
                            </select>
                        </td>                    
                    </tr>
                    <tr>
                        <th>Segment:</th>
                        <td>
                            <select name="Segment"<?php echo $disabled2; ?>>
                                <?php print constructOptions($r['segments'], $r['customer']->Segment); ?>
                            </select>
                        </td>                    
                    </tr>
                    <tr>
                        <th>Territory:</th>
                        <td>
                            <select name="Territory"<?php echo $disabled2; ?>>
                                <?php print constructOptions($r['territories'], $r['customer']->Territory); ?>
                            </select>
                        </td>                    
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        <td><input type="submit" value="Save Record"/></td>
                    </tr>
                </table>
            </form>
        </div>
        <div class="top" id="contract_accounts">
            <div class="summary">
                Contract Accounts
            </div>
<?php
if (isset($_GET['accounts_err']))
{
?>
            <div class="err_msg">
                <?php echo $accounts[1]; ?>
            </div>
<?php
}
?>            
            <table>
<?php
foreach ($accounts[0] as $key => $account)
{
    print '<tr>';
    print '<td>(' . ($key+1).')</td><td>'  . $account . '</td>';
    
    if (isAdmin())
    {
        $params = sprintf('record=%d&profile=%d&note=%d&deleteCA=true&ContractAccount=%s',
                          $start,
                          $pStart,
                          $nStart,
                          $account);
?>
        <td>
            <a href="customers.php?<?php echo $params; ?>"><strong>delete</strong></a>
        </td>
<?php
    }
    
    print '</tr>';
}
?>
                <tr>
                    <form method="post" action="customer_addAccount.php">
                        <input type="hidden" name="record" value="<?php echo $start; ?>"/>
                        <input type="hidden" name="profile" value="<?php echo $pStart; ?>"/>
                        <input type="hidden" name="note" value="<?php echo $nStart; ?>"/>
                        <input type="hidden" name="RollupNumber" value="<?php echo $r['customer']->RollupID; ?>"/>
                        <td>
                            <input type="text" name="ContractAccount"/>
                        </td>
                        <td>
                            <input type="submit" value="Add"/>
                        </td>
                    </form>
                </tr>
            </table>
        </div>
        <br style="clear: both"/><br/>
        <div id="customer_contacts">
            <table style="width: 100%">
                <tr>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Number</th>
                    <th>Type</th>
                    <th>Email</th>
                </tr>
<?php
if (!is_null($contacts['contacts']))
{
    foreach ($contacts['contacts'] as $contact)
    {
?>
                <tr>
                    <form method="post" action="customer_editContact.php">
                        <input type="hidden" name="record" value="<?php echo $start; ?>"/>
                        <input type="hidden" name="profile" value="<?php echo $pStart; ?>"/>
                        <input type="hidden" name="note" value="<?php echo $nStart; ?>"/>
                        <input type="hidden" name="ContactID" value="<?php echo $contact->ContactID; ?>"/>
                        <td><input type="text" name="ContactFirstName" value="<?php echo $contact->ContactFirstName; ?>"/></td>
                        <td><input type="text" name="ContactLastName" value="<?php echo $contact->ContactLastName; ?>"/></td>
                        <td><input type="text" name="ContactNumber" value="<?php echo $contact->ContactNumber; ?>"/></td>
                        <td>
                            <select name="ContactNumberTypeID">
<?php
        print constructOptions($contacts['types'], $contact->ContactNumberTypeID);
?>
                            </select>
                        </td>                    
                        <td><input type="text" name="ContactEmail" value="<?php echo $contact->ContactEmail; ?>"/></td>
                        <td><input type="submit" value="Edit"/></td>
                    </form>
                </tr>
<?php
    }
}
?>
                <tr>
                    <form method="post" action="customer_addContact.php">
                        <input type="hidden" name="record" value="<?php echo $start; ?>"/>
                        <input type="hidden" name="profile" value="<?php echo $pStart; ?>"/>
                        <input type="hidden" name="note" value="<?php echo $nStart; ?>"/>
                        <input type="hidden" name="RollupID" value="<?php echo $r['customer']->RollupID; ?>"/>
                        <td><input type="text" name="ContactFirstName"/></td>
                        <td><input type="text" name="ContactLastName"/></td>
                        <td><input type="text" name="ContactNumber"/></td>
                        <td>
                            <select name="ContactNumberTypeID">
<?php
    print constructOptions($contacts['types'], $contact->ContactNumberTypeID);
?>
                            </select>
                        </td>
                        <td><input type="text" name="ContactEmail"/></td>
                        <td><input type="submit" value="Add"/></td>
                    </form>
                </tr>
            </table>
        </div>
        <div id="customer_profile">
            <div class="summary">
<?php
if (isset($_GET['new']))
{
    print 'New Profile';
}
else
{
    if ($pStart > 0)
    {
        print '<a href="?record=' . $_REQUEST['record'] . '&profile=' . ($pStart - 1) . '&note=' . $_REQUEST['note'] . '">&lt;</a>';
    }
    ?>
                Profile <?php echo ($pStart + 1); ?> of <?php echo $profileCount; ?> 
    <?php
    if ($pStart < $profileCount - 1)
    {
        print '<a href="?record=' . $_REQUEST['record'] . '&profile=' . ($pStart + 1) . '&note=' . $_REQUEST['note'] . '">&gt;</a>';
    }
}
?>
            </div>
<?php
if (!isset($_GET['new']))
{
?>
            <div id="addProfile">
                <form method="get" action="customers.php">
                    <input type="hidden" name="record" value="<?php echo $start; ?>"/>
                    <input type="hidden" name="profile" value="<?php echo $pStart; ?>"/>
                    <input type="hidden" name="note" value="<?php echo $nStart; ?>"/>
                    <input type="hidden" name="new" value="true"/>
                    <input type="submit" value="Create New Profile"/>
                </form>
            </div>
<?php
}
?>
            <form method="post" action="customer_<?php echo isset($_GET['new']) ? 'add' : 'edit'; ?>Profile.php">
                <input type="hidden" name="record" value="<?php echo $start; ?>"/>
                <input type="hidden" name="profile" value="<?php echo $pStart; ?>"/>
                <input type="hidden" name="note" value="<?php echo $nStart; ?>"/>
                <input type="hidden" name="ProfileID" value="<?php echo $profile->ProfileID; ?>"/>
                <input type="hidden" name="RollupID" value="<?php echo $profile->RollupID; ?>"/>
                <input type="hidden" name="CreatorID" value="<?php echo $profile->CreatorID; ?>"/>
                <input type="hidden" name="CreationDate" value="<?php echo $profile->CreationDate; ?>"/>
                <table>
                    <tr>
                        <th>Creator</th>
                        <th>Creation Date</th>
                        <th>Called?</th>
                        <th>Visited?</th>
                    </tr>
                    <tr>
                        <td><?php echo $profile->CreatorUsername; ?></td>
                        <td><?php echo $profile->CreationDate; ?></td>
                        <td><input type="checkbox" name="Called"<?php if ($profile->Called) { echo ' checked="checked"'; } ?>/></td>
                        <td><input type="checkbox" name="Visited"<?php if ($profile->Visited) { echo ' checked="checked"'; } ?>/></td>
                    </tr>
                </table>
                <ul>
                    <li>
                        <div>Needs they feel are not currently being met.</div>
                        <textarea name="Question1"><?php echo $profile->Question1; ?></textarea>
                    </li>
                    <li>
                        <div>What they do/do not like about Reliant.</div>
                        <textarea name="Question2"><?php echo $profile->Question2; ?></textarea>
                    </li>
                    <li>
                        <div>How we can better serve them.</div>
                        <textarea name="Question3"><?php echo $profile->Question3; ?></textarea>
                    </li>
                    <li>
                        <div>How they perceive our competitors, etc.</div>
                        <textarea name="Question4"><?php echo $profile->Question4; ?></textarea>
                    </li>
                    <li>
                        <div>Topics they wish to discuss.</div>
                        <textarea name="Question5"><?php echo $profile->Question5; ?></textarea>
                    </li>
                    <li>
                        <div>Products and Services most interested in/signed up for.</div>
                        <textarea name="Question6"><?php echo $profile->Question6; ?></textarea>
                    </li>
                </ul>
                <input type="submit" class="addEditRecord" value="<?php echo isset($_GET['new']) ? 'Add' : 'Edit'; ?> Profile"/>
            </form>
<?php
    if (!isset($_GET['new']) && isAdmin())
    {
?>
            <div class="deleteRecord">
                <form method="post" action="customers.php">
                    <input type="hidden" name="record" value="<?php echo $start; ?>"/>
                    <input type="hidden" name="profile" value="<?php echo $pStart; ?>"/>
                    <input type="hidden" name="note" value="<?php echo $nStart; ?>"/>
                    <input type="hidden" name="deleteP" value="true"/>
                    <input type="hidden" name="ProfileID" value="<?php echo $profile->ProfileID; ?>"/>
                    <input type="submit" value="Delete Profile"/>
                </form>
            </div>
<?php
    }
?>
        </div>
        <div id="consultant_notes">
            <div class="summary">
<?php
if (isset($_GET['newN']))
{
    print 'New Note';
}
else
{
    if ($nStart > 0)
    {
        print '<a href="?record=' . $_REQUEST['record'] . '&profile=' . $_REQUEST['profile'] . '&note=' . ($nStart - 1) . '">&lt;</a>';
    }
?>
                Note <?php echo ($nStart + 1); ?> of <?php echo $noteCount; ?> 
<?php
    if ($nStart < $noteCount - 1)
    {
        print '<a href="?record=' . $_REQUEST['record'] . '&profile=' . $_REQUEST['profile'] . '&note=' . ($nStart + 1) . '">&gt;</a>';
    }
}
?>
            </div>
<?php
if (!isset($_GET['newN']))
{
?>
            <div id="addNote">
                <form method="get" action="customers.php">
                    <input type="hidden" name="record" value="<?php echo $start; ?>"/>
                    <input type="hidden" name="profile" value="<?php echo $pStart; ?>"/>
                    <input type="hidden" name="note" value="<?php echo $nStart; ?>"/>
                    <input type="hidden" name="newN" value="true"/>
                    <input type="submit" value="Create New Note"/>
                </form>
            </div>
<?php
}
?>
            <form method="post" action="customer_<?php echo isset($_GET['newN']) ? 'add' : 'edit'; ?>Note.php">
                <input type="hidden" name="record" value="<?php echo $start; ?>"/>
                <input type="hidden" name="profile" value="<?php echo $pStart; ?>"/>
                <input type="hidden" name="note" value="<?php echo $nStart; ?>"/>
                <input type="hidden" name="NoteID" value="<?php echo $note->NoteID; ?>"/>
                <input type="hidden" name="RollupID" value="<?php echo $note->RollupID; ?>"/>
                <input type="hidden" name="ConsultantID" value="<?php echo $note->ConsultantID; ?>"/>
                <input type="hidden" name="CreationDate" value="<?php echo $note->CreationDate; ?>"/>
                <table>
                    <tr>
                        <th>Creator</th>
                        <th>Creation Date</th>
                    </tr>
                    <tr>
                        <td><?php echo $note->CreatorUsername; ?></td>
                        <td><?php echo $note->CreationDate; ?></td>
                    </tr>
                </table>
                <p>
                    <div>Notes:</div>
                    <textarea name="Notes"><?php echo $note->Notes; ?></textarea>
                </p>
                <input type="submit" class="addEditRecord" value="<?php echo isset($_GET['newN']) ? 'Add' : 'Edit'; ?> Note"/>
            </form>
<?php
    if (!isset($_GET['newN']) && isAdmin())
    {
?>
            <div class="deleteRecord">
                <form method="post" action="customers.php">
                    <input type="hidden" name="record" value="<?php echo $start; ?>"/>
                    <input type="hidden" name="profile" value="<?php echo $pStart; ?>"/>
                    <input type="hidden" name="note" value="<?php echo $nStart; ?>"/>
                    <input type="hidden" name="deleteN" value="true"/>
                    <input type="hidden" name="NoteID" value="<?php echo $note->NoteID; ?>"/>
                    <input type="submit" value="Delete Note"/>
                </form>
            </div>
<?php
    }
?>
        </div>
    </body>
</html