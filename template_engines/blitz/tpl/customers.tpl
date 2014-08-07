<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" 
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
    <head>
        <title>
            {{ title }} | SBConsultants
        </title>
        <link rel="stylesheet" type="text/css" href="style.css"/>
    </head>
    <body>
    {{ BEGIN debug }}
        <pre>{{ $print_r }}</pre>
    {{ END }}
        <div id="header">
            <h1>Small Business Customer</h1>
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
{{ BEGIN genericError }}
            <div class="err_msg">
                {{ $err_msg }}
            </div>
{{ END }}
            <div class="summary">
{{ BEGIN prevRecord }}
                <a href="?record=1">&laquo; </a>
                <a href="?record={{ $id }}">&lt;</a>
{{ END }}
                Customer {{ $record }} of {{ $customerCount }} 
{{ BEGIN nextRecord }}
                <a href="?record={{ $id}}">&gt;</a>
                <a href="?record={{ $customerCount }}">&raquo;</a>
{{ END }}
            </div>
            <form method="post" action="customer_edit.php">
                <input type="hidden" name="record" value="{{ $record }}"/>
                <input type="hidden" name="profile" value="<?php echo $pStart; ?>"/>
                <input type="hidden" name="note" value="<?php echo $nStart; ?>"/>
                <input type="hidden" name="RollupID" value="<?php echo $customer->RollupID; ?>"/>
{{ BEGIN customer }}
                <table>
                    <tr>
                        <th>Rollup ID:</th>
                        <td style="width: 50%">{{ $RollupID; }}</td>
                    </tr>
                    <tr>
                        <th>Customer:</th>
                        <td><input type="text" name="CustomerName" value="{{ $CustomerName }}"/></td>
                    </tr>
                    <tr>
                        <th>Address:</th>
                        <td><input type="text" name="Address" value="{{ $Address }}"/></td>
                    </tr>
                    <tr>
                        <th>City:</th>
                        <td><input type="text" name="City" value="{{ $City }}"/></td>
                    </tr>
                    <tr>
                        <th>State:</th>
                        <td><input type="text" name="State" value="{{ $State }}"/></td>
                    </tr>
                    <tr>
                        <th>Zip code:</th>
                        <td><input type="text" name="Zipcode" value="{{ $Zipcode }}"/></td>
                    </tr>
                    <tr>
                        <th>Zip 4:</th>
                        <td><input type="text" name="Zip4" value="{{ $Zip4 }}"/></td>
                    </tr>
                    <tr>
                        <th>Total # by Zip:</th>
                        <td><input type="text" name="NumberOfCompanyByZip" value="{{ $NumberOfCompanyByZip }}"/></td>
                    </tr>
                    <tr>
                        <th>Meter Qty:</th>
                        <td><input type="text" name="Meters" value="{{ $Meters }}"/></td>
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
    {{ BEGIN LPB }}
                            <select name="LPBID"{{ $isDisabled }}>
        {{ BEGIN LPB }}<option value="{{ $id }}"{{ BEGIN selected }} selected="selected"{{ END }}>{{ $type }}</option>{{ END }}
                            </select>
    {{ END }}
                        </td>                    
                    </tr>
                    <tr>
                        <th>Segment:</th>
                        <td>
                            <select name="Segment" {{ $isDisabled }}>
                            </select>
                        </td>                    
                    </tr>
                    <tr>
                        <th>Territory:</th>
                        <td>
                            <select name="Territory"<?php echo $disabled2; ?>>
                            </select>
                        </td>                    
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        <td><input type="submit" value="Save Record"/></td>
                    </tr>
                </table>
{{ END customer }}
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
    print '<tr><td>(' . ($key+1).')</td><td>'  . $account . '</td></tr>' . "\n";
}
?>
                <tr>
                    <form method="post" action="customer_addAccount.php">
                        <input type="hidden" name="record" value="<?php echo $start; ?>"/>
                        <input type="hidden" name="profile" value="<?php echo $pStart; ?>"/>
                        <input type="hidden" name="note" value="<?php echo $nStart; ?>"/>
                        <input type="hidden" name="RollupNumber" value="<?php echo $customer->RollupID; ?>"/>
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
                        <input type="hidden" name="RollupID" value="<?php echo $customer->RollupID; ?>"/>
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
        print '<a href="?record=' . $_GET['record'] . '&profile=' . ($pStart - 1) . '&note=' . $_GET['note'] . '">&lt;</a>';
    }
    ?>
                Profile <?php echo ($pStart + 1); ?> of <?php echo $profileCount; ?> 
    <?php
    if ($pStart < $profileCount - 1)
    {
        print '<a href="?record=' . $_GET['record'] . '&profile=' . ($pStart + 1) . '&note=' . $_GET['note'] . '">&gt;</a>';
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
<?php
    if (isAdmin())
    {
?>
            <div id="delProfile" style="float: right">
                <form method="get" action="customers.php">
                    <input type="hidden" name="record" value="<?php echo $start; ?>"/>
                    <input type="hidden" name="profile" value="<?php echo $pStart; ?>"/>
                    <input type="hidden" name="note" value="<?php echo $nStart; ?>"/>
                    <input type="hidden" name="deleteP" value="true"/>
                    <input type="hidden" name="RollupID" value="<?php echo $profile->ProfileID; ?>"/>
                    <input type="submit" value="Delete Profile"/>
                </form>
            </div>
<?php
    }
?>
                <input type="submit" value="<?php echo isset($_GET['new']) ? 'Add' : 'Edit'; ?> Profile"/>
            </form>
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
        print '<a href="?record=' . $_GET['record'] . '&profile=' . $_GET['profile'] . '&note=' . ($nStart - 1) . '">&lt;</a>';
    }
?>
                Note <?php echo ($nStart + 1); ?> of <?php echo $noteCount; ?> 
<?php
    if ($nStart < $noteCount - 1)
    {
        print '<a href="?record=' . $_GET['record'] . '&profile=' . $_GET['profile'] . '&note=' . ($nStart + 1) . '">&gt;</a>';
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
                <input type="hidden" name="NoteID" value="<?php echo $note[0]; ?>"/>
                <input type="hidden" name="RollupID" value="<?php echo $note[1]; ?>"/>
                <input type="hidden" name="agentID" value="<?php echo $note[2]; ?>"/>
                <input type="hidden" name="CreationDate" value="<?php echo $note[5] ?>"/>
                <table>
                    <tr>
                        <th>Creator</th>
                        <th>Creation Date</th>
                    </tr>
                    <tr>
                        <td><?php echo $note[6]; ?></td>
                        <td><?php echo $note[5] ?></td>
                    </tr>
                </table>
                <p>
                    <div>Notes:</div>
                    <textarea name="Notes"><?php echo $note[3]; ?></textarea>
                </p>
                <input type="submit" value="<?php echo isset($_GET['newN']) ? 'Add' : 'Edit'; ?> Note"/>
            </form>
        </div>
    </body>
</html>