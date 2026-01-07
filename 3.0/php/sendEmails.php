<?
mail("amir@interuse.co.il", "sendEmails @ i-Bos", var_export($_SERVER, true));

// don't limit the running time of the script
set_time_limit(0);

include "commonAdmin.php";
include "format.php";

$domainId 	= $_SERVER['argv'][1];
$emailId 	= $_SERVER['argv'][2];
$continue 	= ($_SERVER['argv'][3] == "continue");
$test		= ($_SERVER['argv'][3] == "test");

$mysqlHandle = commonConnectToDB();

$sql		= "select * from domains where id = $domainId";
$result 	= commonDoQuery($sql);
$domainRow	= commonQuery_fetchRow($result);

$isUTF8		= $domainRow['isUTF8'];

$websiteAddress = $domainRow['domainName'];
if ($pos = strpos($domainRow['homeDir'],"/"))
	$websiteAddress .= substr($domainRow['homeDir'],$pos);

commonDisconnect ($mysqlHandle);

commonConnectToUserDB ($domainRow);

// read email for sending
// ------------------------------------------------------------------------------------------------------------------------------------	
$sql		= "select * from clubEmails where id = $emailId";
$result		= commonDoQuery($sql);
$emailRow	= commonQuery_fetchRow($result);

if ($emailRow == "")
	die("Email not found in DB");

$origMailingList = $emailRow['mailingListId'];

if ($test)
{
	$emailRow['contactId'] 	   = 0;
	$emailRow['mailingListId'] = 8888;
}

// find email language
// ------------------------------------------------------------------------------------------------------------------------------------	
$sql	= "select defaultLanguage from clubMailingLists where id = $origMailingList";
$result	= commonDoQuery($sql);
$row	= commonQuery_fetchRow($result);

$lang	= $row['defaultLanguage'];

if ($lang == "")
{
	// get config
	$sql		= "select * from globalParms";
	$result		= commonDoQuery($sql);
	$configRow 	= commonQuery_fetchRow($result);
	$langs		= explode(",", $configRow['langs']);
	$lang 		= $langs[0];
}

// get clubConfig
// ------------------------------------------------------------------------------------------------------------------------------------	
$sql			= "select * from clubConfig";
$result			= commonDoQuery($sql);
$clubConfigRow 	= commonQuery_fetchRow($result);

// prepare text by lang
// ------------------------------------------------------------------------------------------------------------------------------------	
switch ($lang)
{
	case "HEB"	:
	case "HB2"	:
		$removeText			= commonEncode("לחץ/י כאן כדי להסיר מרשימת התפוצה ");
		$invalidText		= commonEncode("אם אינך רואה את האימייל בצורה תקינה ");
		$seeOnSiteText		= commonEncode("לחץ/י כאן לצפייה במייל דרך האתר");
		$whyOnSiteText		= commonEncode("אם אינך רואה את המייל בצורה תקינה - ");
		$fromDateText		= commonEncode("מתאריך");
		$yourDetailsText	= commonEncode("השארת פרטים ליצירת קשר באתר ");
		$noSpamText			= commonEncode("אימייל זה נשלח אליך בתגובה ולכן הוא אינו נחשב לספאם.");
		$noSpam2Text		= commonEncode("ולכן נשלח אליך האימייל הזה והוא אינו נחשב לספאם.");
		$siteManagerText	= commonEncode("אל בעל האתר לבדיקה");
		$yourAddressText	= commonEncode("כתובתך נמצאת ב");
		$usernameText		= commonEncode("בשם המשתמש");
		$removeLinkText		= commonEncode("להסרה מרשימת התפוצה");
		$newsletterText		= commonEncode("מייל זה נשלח לרשימת התפוצה באתר");
		$registrationText	= commonEncode(""); //אליה נרשמת
		$direction			= "rtl";

		break;

	case "ENG"	:
		$removeText			= "Press here to remove your E-Mail from this list ";
		$invalidText		= "If you can't see this E-Mail properly ";
		$seeOnSiteText		= "view this e-mail at the website";
		$whyOnSiteText		= "If you can't see this e-mail properly - ";
		$fromDateText		= "From";
		$yourDetailsText	= "You have filled contact form in our website ";
		$noSpamText			= "This E-Mail was been sent to you in response and therefore isn't a SPAM. ";
		$noSpam2Text		= "therefore you received this E-Mail and isn't a SPAM. ";
		$siteManagerText	= "Site manager testing";
		$yourAddressText	= "Your E-Mail address is in the ";
		$usernameText		= "Name";
		$removeLinkText		= "Remove yourself from this mailing list ";
		$newsletterText		= "this E-Mail has been sent to mailing list on our website ";
		$registrationText	= ""; //to which you registered
		$direction			= "ltr";

		break;
}

if ($emailRow['contactId'] != 0)
{
	// send email to a contact
	// ================================================================================================================================
	
	if ($emailRow['mailingListId'] == 0)
	{
		$sql		= "select * from contacts where id = $emailRow[contactId]";
		$result		= commonDoQuery();
		$contactRow	= commonQuery_fetchRow($result);

		if ($contactRow == "")
			die("Contact not found in DB");

		$contactRow['verifyCode'] = "00000";
	}
	else
	{
		$sql    	= "select clubMembers.id, clubMembers.verifyCode, firstname, lastname, email
					   from clubMembers, clubMailingListsMembers
					   where clubMembers.id = clubMailingListsMembers.memberId
					   and   clubMailingListsMembers.memberId = $emailRow[contactId]
					   and   mailingListId = $emailRow[mailingListId]";
		$result	     = commonDoQuery ($sql);
		$contactRow = commonQuery_fetchRow($result);

		if ($contactRow == "")
			die("Contact not found in DB");

		$contactRow['fullname']  = "$contactRow[firstname] $contactRow[lastname]";
	}

	$verifyCode = date("HiYmd", strtotime($contactRow['insertTime']));

	// intro html
	$myName	= "";
	if ($contactRow['fullname'])
		$myName = stripslashes($contactRow['fullname']);

	$introHtml = "<p dir='$direction' align='center' style='font-size: 12px'>
					$myName
					<div id='newsletterYourDetails'>
						$yourDetailsText
						<a rel='nofollow' target='_blank' href='http://$websiteAddress'>$domainRow[domainName]</a>
					</div>
					$noSpamText
					<div id='newsletterReomove'>
						<a rel='nofollow' target='_blank' 
						  href='http://$websiteAddress/mailingRemove.php?userid=$contactRow[id]&verifyCode=$verifyCode'>
						  $removeText
					   </a>
				   </div>
				   <div id='newsletterSeeOnSite' style='direction:$direction'>
				   		$invalidText
						<a rel='nofollow' target='_blank' 
						   href='http://$websiteAddress/index2.php?emailid=$emailId&lang=$lang&firstname=" . urlencode($contactRow['fullname']) . "'>
					   		$seeOnSiteText
				   		</a>
					</div>
					<br/>
				   </p>";
	
	// read the page to send from the website
	$pageHtml = "";
	$f = fopen("http://$websiteAddress/index2.php?emailid=".$emailId . "&lang=$lang", "r");
	while(!feof($f))
	{
	   $pageHtml .= fread($f, 512);
	} 
	fclose($f);

	$sendHtml = str_replace("&lang", "&amp;lang", $pageHtml);

	// replace params
	$sendHtml = str_replace("#firstname#", $contactRow['fullname'], 				$sendHtml);
	$sendHtml = str_replace("#shortCode#", substr($contactRow['verifyCode'],0,5), 	$sendHtml);

	$sendHtml = "$introHtml$sendHtml";

	commonSendHtmlEmail($domainRow['contactName'], $domainRow['contactEmail'], $contactRow['email'],
		   				$emailRow['subject'], $sendHtml);

	if ($domainId == 153)	// freedomfromfood
	{
		commonSendHtmlEmail($domainRow['contactName'], $domainRow['contactEmail'], "info.gsmarketing@gmail.com",
			   				$emailRow['subject'], $sendHtml);
	}

	$sql	= "insert into clubEmailsMembers values(0, 0, $emailRow[contactId], $emailId, now())";
	commonDoQuery($sql);
}
else
{
	// send email to mailing list
	// ================================================================================================================================

	// read mailing list to send to
	if ($emailRow['mailingListId'] == 8888)	// test
	{
		$sql		= "select * from clubMailingLists where id in ($origMailingList)";
		$result 	= commonDoQuery($sql);
		$listRow	= commonQuery_fetchRow($result);

		$mailingListRow['id']   		= $emailRow['mailingListId'];
		$mailingListRow['name'] 		= $siteManagerText;
		$mailingListRow['senderName'] 	= commonEncode($domainRow['contactName']);
		$mailingListRow['senderEmail'] 	= $domainRow['contactEmail'];
		$mailingListRow['testingEmail']	= $listRow['testingEmail'];
	}
	else
	{
		$sql			= "select * from clubMailingLists where id in ($emailRow[mailingListId])";
		$result 		= commonDoQuery($sql);
		$mailingListRow	= commonQuery_fetchRow($result);

		if ($mailingListRow == "")
			die("Mailing List not found in DB");

		$mailingListRow['id']	= $emailRow['mailingListId'];
	}

	$websiteUrl = "<a rel='nofollow' target='_blank' href='http://$websiteAddress'>$domainRow[domainName]</a> ";

	// get one of the members of this mailing list
	$sql		= "select clubMembers.* from clubMembers
				   join clubMailingListsMembers on clubMembers.id = clubMailingListsMembers.memberId
				   where clubMailingListsMembers.mailingListId = $origMailingList and clubMembers.status = 'active' limit 1";
	$result 	= commonDoQuery($sql);
	$row 		= commonQuery_fetchRow($result);
	$memberCode	= $row['verifyCode'];

	// read the page to send from the website
	// --------------------------------------------------------------------------------------------------------------------------------

	$addParams = "";

	if ($domainId == 324)	// aepi
	{
		$electionId = 0;

		// check if this is an election newsletter
		$sql	= "select pageId from aepi_elections where emailId = $emailId";
		$result	= commonDoQuery($sql);

		if (commonQuery_numRows($result) != 0)
		{
			$row		= commonQuery_fetchRow($result);
			$electionId = $row['pageId'];
	
			$addParams  = "&toReplace=0";	// don't send verifyCode param when getting newsletter html
		}
	}

	$emailUrl = "http://$websiteAddress/index2.php?emailid=$emailId&lang=$lang&firstname=^^^firstname^^^&verifyCode=$memberCode$addParams";

	if ($domainId == 180) // yazamut.technion
		$emailUrl = "http://$websiteAddress/index2-old-site.php?emailid=$emailId&lang=$lang&firstname=^^^firstname^^^&verifyCode=$memberCode$addParams";

	$pageHtml = "";

	/*
	$f = fopen($emailUrl, "r");
	while(!feof($f))
	{
   		$pageHtml .= fread($f, 512);
	}
	fclose($f);*/
	$pageHtml = file_get_contents($emailUrl);

	$pageHtml = str_replace("&lang", "&amp;lang", $pageHtml);
	$pageHtml = str_replace("^^^", "#", $pageHtml);

	// get members
	// --------------------------------------------------------------------------------------------------------------------------------
	$memberRows = array();

	if ($emailRow['mailingListId'] == 8888)	// test
	{
		$memberRow = array();
		$memberRow['firstname'] = $domainRow['contactName'];

		if ($isUTF8)
			$memberRow['firstname'] = iconv("windows-1255", "utf-8", $memberRow['firstname']);

		$memberRow['email']  = $domainRow['contactEmail'];

		if ($mailingListRow['testingEmail'] != "")
			$memberRow['email'] = $mailingListRow['testingEmail'];

		$memberRow['email'] .= ",liat@interuse.com,amir@interuse.co.il";

		if ($clubConfigRow['emailsTestingEmail'])
		{
			$memberRow['email'] .= ",".$clubConfigRow['emailsTestingEmail'];
		}

		$memberRow['verifyCode'] = 1234;
		$memberRow['id'] 		 = 1234;
		$memberRow['username'] 	 = "test";
		$memberRow['password'] 	 = "test-password";

		array_push($memberRows, $memberRow);
	}
	else
	{
		$cols	= "clubMembers.id, clubMembers.username, clubMembers.password, clubMembers.email, 
				   clubMembers.firstname, clubMembers.verifyCode ";

		if ($continue == false)
		{
			// read members to send to
			$sql	= "select distinct $cols
					   from clubMembers
					   join clubMailingListsMembers on clubMembers.id = clubMailingListsMembers.memberId
					   where clubMailingListsMembers.mailingListId in ($emailRow[mailingListId])
					   and   clubMembers.status = 'active'";
		}
		else
		{
			// send to members that still didn't got this email
			$sql	= "select distinct $cols 
					   from clubMembers
					   join clubMailingListsMembers on clubMembers.id = clubMailingListsMembers.memberId
					   where clubMailingListsMembers.mailingListId in ($emailRow[mailingListId])
					   and clubMembers.status = 'active'
					   and clubMembers.id not in (select memberId from clubEmailsMembers where emailId = $emailId)";
		}

		$result	= commonDoQuery($sql);

		while ($memberRow = commonQuery_fetchRow($result))
		{
			array_push($memberRows, $memberRow);
		}
	}

	// loop on members
	// --------------------------------------------------------------------------------------------------------------------------------
	$memberIds = array();

	for ($count=0; $memberRow = array_pop($memberRows); $count++)
	{
		if ($domainId == 485 && $emailRow['mailingListId'] != 8888) $memberRow['email'] = $memberRow['username'];		// panya

		if ($memberRow['email'] == "")
			continue;

		$introHtml = "<div id='newsletterSeeOnSite'>
						<p style='text-align:center;font-size: 12px'>
								$whyOnSiteText
							<a rel='nofollow' target='_blank' 
							   href='http://$websiteAddress/index2.php?emailid=$emailId&lang=$lang&firstname=" . 
							   urlencode($memberRow['firstname']) . "&verifyCode=$memberRow[verifyCode]'>$seeOnSiteText</a>
						</p>
					  </div>";

		$removeLink = "";
		if ($emailRow['addRemoveLink'] == "1")
		{
			$removeUrl	= "http://$websiteAddress/mailingRemove.php?mlid=$mailingListRow[id]&userid=$memberRow[id]&verifyCode=$memberRow[verifyCode]";

			$removeLink = "<div id='newsletterReomove' style='direction:$direction'>
							<p style='text-align:center;font-size: 12px'><a rel='nofollow' target='_blank' href='$removeUrl'>$removeLinkText</a></p>
						   </div>";
	
			$sendHtml = str_replace("#removeUrl#",  $removeUrl, $pageHtml);
		}

		$finaleHtml = "<div id='newsletterYourDetails' style='direction:$direction'>
						<p style='text-align:center;font-size: 12px'>
						<br /><br />
						$newsletterText $websiteUrl $registrationText
						</p>
						$removeLink
					   </div>";

		$sendHtml	= $pageHtml;

		// replace params
		$sendHtml = str_replace("#firstname#",  $memberRow['firstname'], 			 	$sendHtml);
		$sendHtml = str_replace("#shortCode#",  substr($memberRow['verifyCode'],0,5), 	$sendHtml);
		$sendHtml = str_replace("#verifyCode#", $memberRow['verifyCode'], 				$sendHtml);
		$sendHtml = str_replace("#memberId#", 	$memberRow['id'], 						$sendHtml);
		$sendHtml = str_replace("#username#", 	$memberRow['username'], 				$sendHtml);
		$sendHtml = str_replace("#password#",   $memberRow['password'], 				$sendHtml);

		$subject  = str_replace("#firstname#",  $memberRow['firstname'], 				$emailRow['subject']);

		if ($domainId == 324 && $electionId != 0)	// aepi & election newsletter
		{
			$url		= "http://www.aepi.org.il/index2.php?id=$electionId&lang=HEB&code=$memberRow[verifyCode]";

			$sendHtml	= str_replace("#electionUrl#", "<a href='$url'>$url</a>", $sendHtml);

			// get member election password
			$inSql		= "select password from aepi_electionVoters where memberId = $memberRow[id] and electionId = $electionId";
			$inResult	=  commonDoQuery($inSql);
			$inRow		= commonQuery_fetchRow($inResult);

			$sendHtml = str_replace("#electionPassword#", $inRow['password'], $sendHtml);
		}

		$start = strpos($sendHtml, "<body");
		if ($start !== false)
		{
				$start = strpos($sendHtml, ">", $start) + 1;
				$sendHtml = substr($sendHtml, 0, $start).$introHtml.substr($sendHtml, $start);
				$end = strpos($sendHtml, "</body>");
				$sendHtml = substr($sendHtml, 0, $end).$finaleHtml.substr($sendHtml, $end);
		} else
			$sendHtml	= "$introHtml$sendHtml$finaleHtml";

		if ($domainRow['outsideDomain'] == 1)
		{
			// outside account 
			if ($domainId == 485)
			{
					$url 	= "http://www.panya.co.il/privateSendSMTPMail.php";

					$name	= "$memberRow[firstname] $memberRow[lastname]";

					$params = array("mailingListId" => $mailingListRow['id'], 
									"fromName" 		=> $mailingListRow['senderName'], 
									"fromEmail" 	=> $mailingListRow['senderEmail'], 
									"toEmail" 		=> $memberRow['email'], 
									"toName" 		=> $name, 
									"subject" 		=> $subject, 
									"message" 		=> $sendHtml);

					$ch = curl_init($url);
					curl_setopt($ch, CURLOPT_POST, 1);
					curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
					curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
					$response = curl_exec($ch);
					curl_close($ch);						

			}
			else
			{
				commonSendHtmlEmail($mailingListRow['senderName'], $mailingListRow['senderEmail'], $memberRow['email'], $subject, $sendHtml);
			}
		}
		else 
		{
			// local account - send with SMTP
				
			$embedImages	= ($domainId == 294 /*photour*/ || $domainId == 281 /*mayor*/ || $domainId == 288);

			commonSendHtmlEmailBySMTP ($domainRow['ftpUsername'], $domainRow['ftpPassword'], $mailingListRow['senderName'], 
									   $mailingListRow['senderEmail'], $memberRow['email'], $memberRow['firstname'], $subject, $sendHtml, $embedImages);
		}
		
		array_push ($memberIds, $memberRow['id']);

		// pause from time to time, in order not to crash the server
		if (($count % 75) == 74) 
		{
			// update DB about mail sent
			while ($memberId = array_pop($memberIds))
			{
				$sql	= "insert into clubEmailsMembers values (0, $memberId, 0, $emailId, now())";
				commonDoQuery($sql);
			}
			sleep(60);
		}
	}

	// update DB about mail sent
	while ($memberId = array_pop($memberIds))
	{
		$sql	= "insert into clubEmailsMembers values (0, $memberId, 0, $emailId, now())";
		commonDoQuery($sql);
	}
}

?>
