<?
// This should run under the cron job of user: apache
// 1 */1 * * * /usr/local/bin/php /home/iboscoil/public_html/3.0/php/emailContinueCron.php

if (function_exists("date_default_timezone_set"))
	date_default_timezone_set ("Asia/Tel_Aviv");

include "commonAdmin.php";
include "xmlParser.php";
include "format.php";

foreach(array(294,407,413) as $domainId) // photour, landvo, smartnew
{
$mysqlHandle = commonConnectToDB ();

$sql = "select * from domains where id=$domainId"; 
$result 	= commonDoQuery($sql);
$domainRow	= commonQuery_fetchRow($result);

commonDisconnect ($mysqlHandle);

commonConnectToUserDB($domainRow);

	// get the last sent email that was sent to a mailing list
	$queryStr	= "select max(id) from clubEmails where whenSent != '0000-00-00 00:00:00' and contactId = 0";
	$result	  	= commonDoQuery($queryStr);
	$maxRow 	= commonQuery_fetchRow($result);

	$id = $maxRow[0];
	//echo "Last mail is $id<br />";

	// check when the last email was sent lately
	$queryStr	= "select max(joinTime) from clubEmailsMembers where emailId = $id";
	$result	  	= commonDoQuery($queryStr);
	$maxRow 	= commonQuery_fetchRow($result);

	//echo "Last time sent at $maxRow[0]<br />";
	if (strtotime($maxRow[0]) > strtotime("-1 hour") || strtotime($maxRow[0]) < strtotime("-4 hour"))
		exit; // nothing to do

	$queryStr	= "select * from clubEmails where id = $id";
	$result	  	= commonDoQuery($queryStr);
	$emailRow 	= commonQuery_fetchRow($result);

	// check how many didn't get it
	/*
	$resMembers = commonDoQuery("select clubMembers.* from clubMembers
				     join clubMailingListsMembers on clubMembers.id = clubMailingListsMembers.memberId
				     where clubMailingListsMembers.mailingListId = ". $emailRow['mailingListId'] . "
				     and clubMembers.status = 'active'
				     and clubMembers.id not in (select memberId from clubEmailsMembers where emailId = $id)");

	//echo "It wasn't sent to ".commonQuery_numRows($resMembers);
	if (commonQuery_numRows($resMembers) > 5)
	 */

	$resGotIt = commonDoQuery("select count(*) from clubEmailsMembers where emailId = $id");
	$rowGotIt = commonQuery_fetchRow($resGotIt);
	$resShouldGetIt = commonDoQuery("select count(*) from clubMailingListsMembers
									 left join clubMembers on clubMembers.id = clubMailingListsMembers.memberId
									 where clubMailingListsMembers.mailingListId = $emailRow[mailingListId] and clubMembers.status = 'active'");
	$rowShouldGetIt = commonQuery_fetchRow($resShouldGetIt);

	//echo "It should be sent to $rowShouldGetIt[0] but was sent only to $rowGotIt[0]";
	if ($rowShouldGetIt[0] - $rowGotIt[0] > 5)
	{
		mail("amir@interuse.co.il", "ibos running sendEmails.php",
		     "/usr/local/bin/php $ibosHomeDir/php/sendEmails.php $domainRow[id] $id continue > /dev/null &");
		exec("/usr/local/bin/php $ibosHomeDir/php/sendEmails.php ".$domainRow['id']." ".$id." continue > /dev/null &");
	}
}

?>
