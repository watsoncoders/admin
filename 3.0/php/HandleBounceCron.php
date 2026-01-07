<?php

include "commonAdmin.php";

$mysqlHandle = commonConnectToDB();

// Prepare all domains array
$allDomainsByName	= array();
$allDomainsById 	= array();
$reports			= array();
$sql		= "select *, if( position('/' in homeDir), concat(domainName, substring(homeDir, position('/' in homeDir))), domainName) as cname from domains";
$result 	= commonDoQuery($sql);
while ($domainRow = commonQuery_fetchRow($result))
{
		$allDomainsByName[$domainRow['cname']] = $domainRow;
		$allDomainsById[$domainRow['id']] = $domainRow;
}

commonDisconnect ($mysqlHandle);
$lastDomainConnected = null;
$lastDomainIdConnected = null;

$mbox = imap_open ("{mail.i-bos.co.il:110/pop3/novalidate-cert}INBOX", "bounced@i-bos.co.il", "12344321");
//echo imap_last_error();

if (!$mbox)
		exit;

imap_headers($mbox); // This line suppose to increase performance

for ($i=1; $i <= imap_num_msg($mbox); $i++)
{
		$body		= imap_body($mbox, $i);
		$header		= imap_headerinfo($mbox, $i);
		$subject	= $header->subject;

		if (strpos($subject, "commonSendHtmlEmailBySMTP") !== false) // Bounce detected in commonAdmin::commonSendHtmlEmailBySMTP
		{
				$start = strrpos($body, '/mailingRemove.php?mlid=');
				if (!$start)
				{
						imap_delete($mbox, $i); // cannot identify this email hence has nothing to do with it
						continue;
				}
				$domainStart = strrpos($body, "http://")+7;
				if (!$domainStart)
				{
						imap_delete($mbox, $i); // cannot identify this email hence has nothing to do with it
						continue;
				}
				$domainName = substr($body, $domainStart, $start-$domainStart);

				if (!array_key_exists($domainName, $allDomainsByName))
				{
						imap_delete($mbox, $i); // cannot identify this email hence has nothing to do with it
						continue;
				}

				// Domain was identified in interuse DB - so we connect to the specific domain DB
				if ($lastDomainConnected != $domainName)
				{
						$isUTF8 = $allDomainsByName[$domainName]['isUTF8'];
						commonConnectToUserDB ($allDomainsByName[$domainName]);
						$lastDomainConnected = $domainName; // to save re-connecting
						$lastDomainIdConnected = $allDomainsByName[$domainName]['id'];
				}
		
				$next = strrpos($body, '&userid=', $start);
				$mlid = substr($body, $start+24, $next-$start-24);
				$nextnext = strrpos($body, '&', $next+8);
				$userId = substr($body, $next+8, $nextnext-$next-8);

				if (!is_numeric($mlid) || !is_numeric($userId))
				{
						imap_delete($mbox, $i); // cannot identify this email hence has nothing to do with it
						continue;
				}

				// Success - delete this member from mailing list
				commonDoQuery("delete from clubMailingListsMembers where memberId = $userId and mailingListId = $mlid");
				$userRes = commonDoQuery("select firstname, lastname, email from clubMembers where id = $userId");
				$userRow = commonQuery_fetchRow($userRes);
				$reports[$allDomainsByName[$domainName]['contactEmail']] .=
					   	"Unsubscribing user $userId: $userRow[firstname] $userRow[lastname] with email '$userRow[email]' from mailing list no. $mlid\n";
				imap_delete($mbox, $i);
		}
		else // Real bounce, returned from external server
		{
				$domainStart = strpos($body, "X-domainId: ");
				if (!$domainStart)
				{
						imap_delete($mbox, $i); // cannot identify this email hence has nothing to do with it
						continue;
				}
				$domainId = substr($body, $domainStart+12, strpos($body, ';', $domainStart)-$domainStart-12);
				if (!is_numeric($domainId))
				{
						imap_delete($mbox, $i); // cannot identify this email hence has nothing to do with it
						continue;
				}

				$mlidStart = strrpos($body, "X-mlid: ");
				if (!$mlidStart)
				{
						imap_delete($mbox, $i); // cannot identify this email hence has nothing to do with it
						continue;
				}
				$mlid = substr($body, $mlidStart+8, strpos($body, ';', $mlidStart)-$mlidStart-8);
				if (!is_numeric($mlid))
				{
						imap_delete($mbox, $i); // cannot identify this email hence has nothing to do with it
						continue;
				}

				// Domain was identified in interuse DB - so we connect to the specific domain DB
				if ($lastDomainIdConnected != $domainId)
				{
						$isUTF8 = $allDomainsById[$domainId]['isUTF8'];
						commonConnectToUserDB ($allDomainsById[$domainId]);
						$lastDomainConnected = $allDomainsById[$domainId]['cname'];
						$lastDomainIdConnected = $domainId; // to save re-connecting
				}

				// find an email address inside body of bounced email, start searching from its end backwards
				$matches = array();
				$pattern = '/[a-z\d._%+-]+@[a-z\d.-]+\.[a-z]{2,4}\b/i';
				$offset = strpos($body, "To: ");
				if ($offset && preg_match($pattern, $body, $matches, 0, strpos($body, "To: ")))
				{
						$email = $matches[0];
						$userRes = commonDoQuery("select id, firstname, lastname from clubMembers where email = '$email'");
						$userRow = commonQuery_fetchRow($userRes);
						if (!$userRow)
						{
								imap_delete($mbox, $i); // user with this email is missing hence has nothing to do with it
								continue;
						}

						commonDoQuery("update clubMembers set failedEmailsCounter = failedEmailsCounter + 1,
									   firstFailedEmailTime = if (firstFailedEmailTime = 0, NOW(), firstFailedEmailTime)
									   where id = $userRow[id]");
						imap_delete($mbox, $i);
						$reports[$allDomainsById[$domainId]['contactEmail']] .=
							   	"Failed sending to user $userRow[id]: $userRow[firstname] $userRow[lastname] with email '$email' from mailing list no. $mlid\n";
				}
		}
}

imap_expunge($mbox); // delete emails that were marked for deletion

imap_close($mbox);

foreach ($reports as $key => $val)
{
		mail($key, "Unsubsribers report from i-Bos", $val);
		mail("amir@interuse.co.il", "Unsubsribers report from i-Bos to $key", $val);
}
?>
