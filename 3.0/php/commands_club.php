<?php

require_once "picsTools.php";

$configTags	= array("allowRememberPassword", "passwordExpiredDays", "withSessions", "keepAliveInterval", "memberLoginPageId", "registerSuccessPageId", 
					"registerFailedPageId", "registerCaptcha", "updateDetailsPageId", "updateFailedPageId", "sendPasswordPageId", "verifyMemberPageId",
					"unverifyMemberPageId", "removeMemberPageId", "removeFailedPageId", "confirmRegistration", "confirmGraceHours", "emailText", 
					"clubText", "clubMemberText", "afterLoginPageId", "afterFailedLoginPageId", "afterLogoutPageId", "contactEmailSubject", 
					"contactRedirect", "contactCaptcha", "supplyOldPassword", "emailsTestingEmail", "passwordChangeDays", "updatePasswordPageId", 
					"defaultMailingLayout", "resetPasswordSucessPageId", "maxSessionsPerMember", "maxCookiesPerMember", "maxSwitchCookiesPerMember",
					"superPassword");


/* ----------------------------------------------------------------------------------------------------	*/
/* getClubConfig																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getClubConfig ($xmlRequest)
{
	global $configTags;

	$queryStr 	 = "select * from clubConfig";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);

	if ($row['passwordExpiredDays']  		== "0") $row['passwordExpiredDays'] 			= "";
	if ($row['maxSessionsPerMember'] 		== "0") $row['maxSessionsPerMember'] 			= "";
	if ($row['maxCookiesPerMember']  		== "0") $row['maxCookiesPerMember'] 			= "";

	if ($row['securePasswords'] == "1")
		$row['superPassword'] = "";

	$xmlResponse = "<securePasswords>$row[securePasswords]</securePasswords>";

	foreach ($configTags as $tag)
	{
		$xmlResponse .= "<$tag>" . commonValidXml($row[$tag]) . "</$tag>";
	}

	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateClubConfig																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateClubConfig ($xmlRequest)
{
	global $configTags;

	$queryStr 	 = "select * from clubConfig";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);

	$queryStr = "update clubConfig set ";

	foreach ($configTags as $tag)
	{
		$val	= addslashes(xmlParser_getValue($xmlRequest, $tag));

		if ($tag == "superPassword")
		{
			if ($row['securePasswords'] == "1")
			{
				if ($val == "")
				{
					$val = $row['superPassword'];	// password is the same
				}
				else
				{
					$val = hash("sha256", $val);	// make password secure
				}
			}

		}

		$queryStr .= "$tag = '$val' ,";
	}

	$queryStr = trim($queryStr, ",");

	commonDoQuery ($queryStr);

	return ("");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* ----------------------------------------------------------------------------------------------------	*/

$CCobjectName = "clubMember"; $CCobjectsName = "clubMembers"; $CCHEBobjectName = "גולש רשום";
$CCfieldsList = array("username", "password", "firstname", "lastname", "nickname", "email", "phone", "phone2", "cellphone",
					  "fax", "country", "city", "address", "zipcode",  "joinTime", "occupation", "status", "mySite",
					  "extraData1", "extraData2", "extraData3", "extraData4", "extraData5", 
					  "extraData6", "extraData7", "extraData8", "extraData9", "extraData10", 
					  "verifyCode", "birthDate",
					  "expireTime", "gender", "maritalStatus", "memberLanguage", "maxCookies", "maxSessions", "maxSwitchCookies", "superMember");
include "commonCommands.php";

/* ----------------------------------------------------------------------------------------------------	*/
/* getMembers																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function getMembers ($xmlRequest)
{	
	$condition  = "";
	
	$sortBy		= xmlParser_getValue($xmlRequest,"sortBy");

	if ($sortBy == "" || $sortBy == "memberId")
		$sortBy = "id";

	$sortDir	= xmlParser_getValue($xmlRequest,"sortDir");
	if ($sortDir == "")
		$sortDir = "desc";

	$anyText		= addslashes(xmlParser_getValue($xmlRequest, "anyText"));
	if ($anyText != "")
	{
		if (strpos($anyText, "mlid") === false)
		{
			$condition .= " and (username like '%$anyText%' or firstname like '%$anyText%' or lastname like '%$anyText%' or nickname like '%$anyText%' 
							     or clubMembers.id = '$anyText') ";
		}
	}

	$email		= commonDecode(xmlParser_getValue($xmlRequest, "email"));
	if ($email != "")
		$condition .= " and email like '%$email%' ";

	$join				= "";
	$mailingListId		= commonDecode(xmlParser_getValue($xmlRequest, "mailingListId"));
	if ($mailingListId != "" || strpos($anyText, "mlid") !== false)
	{
		$lists	= $mailingListId;

		if (strpos($anyText, "mlid") !== false)
		{
			if ($lists != "") $lists .= ",";

			$lists	.= str_replace("mlid", "", $anyText);
		}

		$join = ", clubMailingListsMembers";
		$condition .= " and clubMailingListsMembers.mailingListId in ($lists)
						and clubMailingListsMembers.memberId = clubMembers.id ";
	}

	// get total
	$queryStr     = "select count(*) from clubMembers $join where 1 $condition";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$total	     = $row[0];

	// get details
	$queryStr    = "select distinct clubMembers.id, username, firstname, lastname, nickname, email, clubMembers.joinTime, status, referer, expireTime
				    from clubMembers $join
					where 1 $condition
					order by $sortBy $sortDir" . commonGetLimit ($xmlRequest);
	$result	     = commonDoQuery ($queryStr);
	$numRows    = commonQuery_numRows($result);

	$xmlResponse = "<items>";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		$id   			= $row['id'];
		$username 		= commonValidXml ($row['username'],true);
		$firstname 		= commonValidXml ($row['firstname'],true);
		$lastname 		= commonValidXml ($row['lastname'],true);
		$nickname 		= commonValidXml ($row['nickname'],true);
		$email  		= commonValidXml ($row['email'],true);
		$joinTime		= formatApplDate($row['joinTime']);
		$status			= $row['status'];
		$referer		= commonValidXml ($row['referer']);

		if ($row['expireTime'] != "0000-00-00 00:00:00" && strtotime($row['expireTime']) < strtotime(date("Y-m-d")))
		{
			$status	= "פג תוקף";
		}

		switch ($status)
		{
			case "new"		: $status = "חדש";		break;
			case "active"	: $status = "פעיל";		break;
			case "disabled" : $status = "חסום";		break;
		}

		$xmlResponse .=	"<item>
							<memberId>$id</memberId>
							<username>$username</username>
							<firstname>$firstname</firstname>
							<lastname>$lastname</lastname>
							<nickname>$nickname</nickname>
							<email>$email</email>
							<joinTime>$joinTime</joinTime>
							<status>$status</status>
							<referer>$referer</referer>
						 </item>";
	}

	$xmlResponse .=	"</items>"								.
							commonGetTotalXml($xmlRequest,$numRows,$total);
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getMemberNextId											*/
/* ----------------------------------------------------------------------------------------------------	*/
function getMemberNextId ($xmlRequest)
{
	return CC_getClubMemberNextId($xmlRequest);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* addMember												*/
/* ----------------------------------------------------------------------------------------------------	*/
function addMember ($xmlRequest)
{
	return (editMember ($xmlRequest, "add"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateMember												*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateMember ($xmlRequest)
{
	editMember ($xmlRequest, "update");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* doesMemberExist																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function doesMemberExist ($id)
{
	return CC_doesClubMemberExist($id);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* editMember																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function editMember ($xmlRequest, $editType)
{
	global $ibosHomeDir, $userId;

	global $status, $username, $password, $firstname, $lastname, $nickname, $email, 
		   $phone, $phone2, $cellphone, $fax, $city, $country, $address, $zipcode, $joinTime, $occupation, $mySite,
		   $extraData1, $extraData2, $extraData3, $extraData4, $extraData5, 
		   $extraData6, $extraData7, $extraData8, $extraData9, $extraData10, 
		   $birthDate, $verifyCode, $expireTime, $gender, $maritalStatus, $memberLanguage,
		   $maxCookies, $maxSessions, $maxSwitchCookies, $superMember;

	if ($editType == "update")
	{
		$memberId	= xmlParser_getValue($xmlRequest, "memberId");

		$sql		= "select * from clubMembers where id = $memberId";
		$result		= commonDoQuery($sql);
		$memberRow	= commonQuery_fetchRow($result);

		$joinTime	= $memberRow['joinTime'];
		$verifyCode	= $memberRow['verifyCode'];
	}
	else
	{
		$verifyCode = randomCode(25);
		$joinTime 	= date("Y-m-d H:i:00", strtotime("now"));
	}


	$status				= xmlParser_getValue($xmlRequest, "status");
	$username			= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "username")));
	$password 			= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "password")));
	$firstname 			= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "firstname")));
	$lastname			= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "lastname")));
	$nickname 			= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "nickname")));
	$email				= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "email")));
	$phone				= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "phone")));
	$phone2				= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "phone2")));
	$cellphone 			= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "cellphone")));
	$fax				= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "fax")));
	$city				= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "city")));
	$country 			= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "country")));
	$address 			= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "address")));
	$zipcode 			= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "zipcode")));
	$occupation			= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "occupation")));
	$mySite				= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "mySite")));
	$extraData1 		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "extraData1")));
	$extraData2 		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "extraData2")));
	$extraData3 		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "extraData3")));
	$extraData4 		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "extraData4")));
	$extraData5 		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "extraData5")));
	$extraData6 		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "extraData6")));
	$extraData7 		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "extraData7")));
	$extraData8 		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "extraData8")));
	$extraData9 		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "extraData9")));
	$extraData10		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "extraData10")));
	$birthDate  		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "birthDate")));
	$expireTime 		= formatApplToDB(commonDecode(xmlParser_getValue($xmlRequest, "expireTime")));
	$gender				= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "gender")));
	$maritalStatus		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "maritalStatus")));
	$memberLanguage		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "memberLanguage")));
	$maxCookies			= xmlParser_getValue($xmlRequest, "maxCookies");
	$maxSwitchCookies	= xmlParser_getValue($xmlRequest, "maxSwitchCookies");
	$maxSessions		= xmlParser_getValue($xmlRequest, "maxSessions");
	$superMember		= xmlParser_getValue($xmlRequest, "superMember");

	$xmlResponse = CC_editClubMember($xmlRequest, $editType);

	if ($editType == "update")
	{
		// update latestCookies by maxCookies
		$latestCookies		= $memberRow['latestCookies'];
		
		if ($latestCookies != "")
		{
			if ($maxCookies == "")
			{
				// get config maxCookies
				$sql				= "select maxCookiesPerMember from clubConfig";
				$result				= commonDoQuery($sql);
				$row				= commonQuery_fetchRow($result);
				$maxCookies			= $row['maxCookiesPerMember'];
			}

			$latestCookies	= explode(",", trim($latestCookies, ","));

			if ($maxCookies != "" && count($latestCookies) > $maxCookies)
			{
				if ($maxCookies == 0)
				{
					$latestCookies 		= "";
					$maxSwitchCookies	= "NULL";
				}
				else
				{
					$latestCookies	= "," . join(",", array_slice($latestCookies, 0, $maxCookies));
				}

				$sql	= "update clubMembers set latestCookies = '$latestCookies' where id = $memberId";
				commonDoQuery($sql);
			}

		}

		if ($maxSwitchCookies == "")
			$maxSwitchCookies = "NULL";

		$sql	= "update clubMembers set maxSwitchCookies = $maxSwitchCookies where id = $memberId";
		commonDoQuery($sql);

		if (xmlParser_getValue($xmlRequest, "maxCookies") == 0)
		{
			// empty max cookies
			$sql	= "update clubMembers set maxCookies = null where id = $memberId";
			commonDoQuery($sql);
		}
	}
	else
	{
		$queryStr	= "select max(id) from clubMembers";
		$result		= commonDoQuery($queryStr);
		$row		= commonQuery_fetchRow($result);
		$memberId 	= $row[0];
	}

	$listIds = trim(addslashes(commonDecode(xmlParser_getValue($xmlRequest, "mailingList"))));

	$mailingListIds = explode(" ", $listIds);

	// delete old ones
	$queryStr	= "delete from clubMailingListsMembers where memberId = $memberId";
	if ($listIds != "")
		$queryStr .= " and mailingListId not in (" . join(",", $mailingListIds) . ")";
	
	commonDoQuery ($queryStr);

	if ($listIds != "")
	{
		// add new ones
		foreach ($mailingListIds as $mailingListId)
		{
			$queryStr = "select * from clubMailingListsMembers where memberId = $memberId and mailingListId = $mailingListId";
			$result	  = commonDoQuery($queryStr);

			if (commonQuery_numRows($result) == 0)
			{
				$queryStr = "insert into clubMailingListsMembers (memberId, mailingListId, joinTime) values ($memberId, $mailingListId, now())";
				commonDoQuery ($queryStr);
			}
		}
	}
 
	commonSaveItemFlags ($memberId, "member", $xmlRequest);

	// handle file
	$filePath 		= "$ibosHomeDir/html/SWFUpload/files/$userId/";

	$sourceFile 	= addslashes(xmlParser_getValue($xmlRequest, "sourceFile"));	
	$dimensionId 	= xmlParser_getValue($xmlRequest, "dimensionId");	
	$fileDeleted 	= xmlParser_getValue($xmlRequest, "fileDeleted");	

	$fileDeleted	= ($fileDeleted == "1");
	$fileLoaded		= false;

	$picFile		= "";
	$suffix			= "";

	if ($sourceFile != "")
	{
		$fileLoaded	= true;

		$suffix	= commonFileSuffix($sourceFile);

		$picFile = time() . $suffix;

		list ($picWidth, $picHeight, $bgColor) = commonGetDimensionDetails ($dimensionId);
	
		$sql	= "update clubMembers set picFile = 'uploadedFiles/$picFile', sourceFile = '$sourceFile' where id = $memberId";
		commonDoQuery($sql);

		$domainRow	= commonGetDomainRow();
		$domainName = commonGetDomainName ($domainRow);

		$connId 	= commonFtpConnect($domainRow); 
		ftp_chdir ($connId, "uploadedFiles");

		if ($picWidth == 0 && $picHeight == 0)
		{
			$upload = ftp_put($connId, $picFile, "$filePath/$sourceFile", FTP_BINARY);
		}
		else
		{
			picsToolsResize("$filePath/$sourceFile", $suffix, $picWidth, $picHeight, "/../../tmp/$picFile", $bgColor);
		
			$upload = ftp_put($connId, $picFile, "/../../tmp/$picFile", FTP_BINARY);
		}

		commonFtpDisconnect ($connId);
	}
	else if ($fileDeleted)
	{
		$sql	= "update clubMembers set picFile = '', sourceFile = '' where id = $memberId";
		commonDoQuery($sql);
	}

	commonDeleteOldFiles ($filePath, 7200);	// 2 hour

	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getExtraDataNames																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function getExtraDataNames ($xmlRequest)
{
	return (commonGetExtraDataNames("clubMembersExtraData"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getMemberDetails																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getMemberDetails ($xmlRequest)
{
	global $usedLangs;

	$id = xmlParser_getValue($xmlRequest, "memberId");
		
	if ($id == "")
		trigger_error ("חסר קוד גולש לביצוע הפעולה");

	$queryStr = "select * from clubMembers where id='$id'";
	$result	  = commonDoQuery($queryStr);

	if (commonQuery_numRows($result) == 0)
		trigger_error ("גולש זה ($id) לא קיים במערכת. לא ניתן לבצע את העדכון");

	$xmlResponse = "";

	$row = commonQuery_fetchRow($result);

	if (is_numeric($row['city']))
	{
		$queryStr	= "select name from cities_byLang where cityId = $row[city]";
		$result		= commonDoQuery($queryStr);

		if (commonQuery_numRows($result) != 0)
		{
			$inRow	= commonQuery_fetchRow($result);
			$row['city'] = $inRow['name'];
		}
	}

	// siteUrl
	$domainRow   	= commonGetDomainRow ();
	$siteUrl    	= commonGetDomainName($domainRow);

	$pressText   	= commonPhpEncode("לחץ כאן");
	$show		 	= "";
	$delete			= "";
	if ($row['picFile'] != "")
	{
		$show			= commonValidXml($row['sourceFile']);
		$row['picFile'] = "$siteUrl/$row[picFile]";
		$delete			= $pressText;
	}

	$status			= $row['status'];
	$username		= commonValidXml($row['username'], true);
	$password 		= commonValidXml($row['password'], true);
	$firstname 		= commonValidXml($row['firstname'], true);
	$lastname		= commonValidXml($row['lastname'], true);
	$nickname 		= commonValidXml($row['nickname'], true);
	$email			= commonValidXml($row['email'], true);
	$phone			= commonValidXml($row['phone'], true);
	$phone2			= commonValidXml($row['phone2'], true);
	$cellphone 		= commonValidXml($row['cellphone'], true);
	$fax			= commonValidXml($row['fax'], true);
	$messenger		= commonValidXml($row['messenger'], true);
	$icq			= commonValidXml($row['icq'], true);
	$picFile		= commonValidXml($row['picFile'], true);
	$city			= commonValidXml($row['city'], true);
	$country 		= commonValidXml($row['country'], true);
	$address 		= commonValidXml($row['address'], true);
	$zipcode 		= commonValidXml($row['zipcode'], true);
	$joinTimeDB		= $row['joinTime'];
	$joinTime 		= formatApplDateTime($row['joinTime'], true);
	$occupation		= commonValidXml($row['occupation'], true);
	$mySite			= commonValidXml($row['mySite'], true);

	$extraData1		= commonValidXml($row['extraData1']);
	$extraData2		= commonValidXml($row['extraData2']);
	$extraData3		= commonValidXml($row['extraData3']);
	$extraData4		= commonValidXml($row['extraData4']);
	$extraData5		= commonValidXml($row['extraData5']);
	$extraData6		= commonValidXml($row['extraData6']);
	$extraData7		= commonValidXml($row['extraData7']);
	$extraData8		= commonValidXml($row['extraData8']);
	$extraData9		= commonValidXml($row['extraData9']);
	$extraData10	= commonValidXml($row['extraData10']);

	$birthDate		= commonValidXml($row['birthDate']);
	$expireTime		= formatApplDate($row['expireTime']);
	$gender 		= commonValidXml($row['gender']);
	$maritalStatus 	= commonValidXml($row['maritalStatus']);
	$memberLanguage = commonValidXml($row['memberLanguage']);

	$verifyCode 	= $row['verifyCode'];

	$verifyCode 	= $row['verifyCode'];
	$latestCookies 	= commonCData("<div style='padding-bottom:5px'>" . str_replace(",", "<br/>", trim($row['latestCookies'], ",")) . "</div>");
	$maxCookies	 	= (($row['maxCookies']   == "0") ? "" : $row['maxCookies']);
	$maxSessions 	= (($row['maxSessions']  == "0") ? "" : $row['maxSessions']);
	$maxSwitches	= $row['maxSwitchCookies'];
	$superMember	= $row['superMember'];

	$xmlResponse .=	"<id>$id</id>"	.
			"<memberId>$id</memberId>"	 			. 
			"<status>$status</status>"			.
			"<username>$username</username>"	.
			"<password>$password</password>"	.
			"<firstname>$firstname</firstname>"	.
			"<lastname>$lastname</lastname>"	. 
			"<nickname>$nickname</nickname>"	.
			"<email>$email</email>"				. 
			"<phone>$phone</phone>"				. 
			"<phone2>$phone2</phone2>"			. 
			"<cellphone>$cellphone</cellphone>"	. 
			"<fax>$fax</fax>"					. 
			"<messenger>$messenger</messenger>"	. 
			"<icq>$icq</icq>"					. 
			"<picFile>$picFile</picFile>"		. 
			"<show>$show</show>"				.
			"<delete>$delete</delete>"			.
			"<city>$city</city>"				. 
			"<country>$country</country>"		. 
			"<address>$address</address>"		. 
			"<zipcode>$zipcode</zipcode>"		. 
			"<joinTime>$joinTime</joinTime>"	.
			"<joinTimeDB>$joinTimeDB</joinTimeDB>"	.
			"<occupation>$occupation</occupation>"	.
			"<mySite>$mySite</mySite>"	.
			"<extraData1>$extraData1</extraData1>"	.
			"<extraData2>$extraData2</extraData2>"	.
			"<extraData3>$extraData3</extraData3>"	.
			"<extraData4>$extraData4</extraData4>"	.
			"<extraData5>$extraData5</extraData5>"  .
			"<extraData6>$extraData6</extraData6>"  .
			"<extraData7>$extraData7</extraData7>"  .
			"<extraData8>$extraData8</extraData8>"  .
			"<extraData9>$extraData9</extraData9>"  .
			"<extraData10>$extraData10</extraData10>"  .
			"<birthDate>$birthDate</birthDate>"  .
			"<expireTime>$expireTime</expireTime>"  .
			"<gender>$gender</gender>"  .
			"<maritalStatus>$maritalStatus</maritalStatus>"  .
			"<memberLanguage>$memberLanguage</memberLanguage>"  .
			"<verifyCode>$verifyCode</verifyCode>" . 
			"<maxCookies>$maxCookies</maxCookies>" .
			"<maxSessions>$maxSessions</maxSessions>" .
			"<maxSwitchCookies>$maxSwitches</maxSwitchCookies>" .
			"<superMember>$superMember</superMember>" . 
			"<latestCookies>$latestCookies</latestCookies>";

	commonConnectToUserDB ($domainRow);

	$flags = commonGetItemFlags ($id, "member");

	$xmlResponse .= commonGetItemFlagsXml ($flags, "member");

	$mailingList = "";
	$sql	= "select mailingListId from clubMailingListsMembers where memberId = $id";
	$result = commonDoQuery($sql);

	while ($row = commonQuery_fetchRow($result))
	{
		$mailingList .= " " . $row['mailingListId'];
	}

	$mailingList = trim($mailingList);

	$xmlResponse .= "<mailingList>$mailingList</mailingList>";

	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* addMembersToMailingList																				*/
/* ----------------------------------------------------------------------------------------------------	*/
function addMembersToMailingList ($xmlRequest)
{
	$ids 	= xmlParser_getValues ($xmlRequest, "memberId");
	$listId = xmlParser_getValue($xmlRequest, "mailingListId");

	if (count($ids) == 0)
		trigger_error ("חסר קוד לביצוע הפעולה");

	foreach ($ids as $id)
	{
		$sql	= "select * from clubMailingListsMembers where memberId = $id and mailingListId = $listId";
		$result	= commonDoQuery($sql);

		if (commonQuery_numRows($result) == 0)
		{
			$sql = "insert into clubMailingListsMembers (memberId, mailingListId, joinTime) values ($id, $listId, now())";
			commonDoQuery ($sql);
		}
	}

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteMember																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteMember ($xmlRequest)
{
	$ids = xmlParser_getValues ($xmlRequest, "memberId");

	if (count($ids) == 0)
		trigger_error ("חסר קוד לביצוע הפעולה");

	foreach ($ids as $id)
	{
		$queryStr = "delete from clubMembers where id = $id";
		commonDoQuery ($queryStr);

		$queryStr = "delete from clubMailingListsMembers where memberId = $id";
		commonDoQuery ($queryStr);

		$queryStr = "delete from clubMembersWeb2 where memberId = $id";
		commonDoQuery ($queryStr);
	}

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* ----------------------------------------------------------------------------------------------------	*/

$CCobjectName = "clubMailingList"; $CCobjectsName = "clubMailingLists"; $CCHEBobjectName = "רשימת התפוצה";
$CCfieldsList = array("name", "senderName", "senderEmail", "membersOnly", "defaultLanguage", "testingEmail");
include "commonCommands.php";

/* ----------------------------------------------------------------------------------------------------	*/
/* getClubMailingLists											*/
/* ----------------------------------------------------------------------------------------------------	*/
function getClubMailingLists ($xmlRequest)
{	
	$condition  = "";
	/*
	$name		= commonDecode(xmlParser_getValue($xmlRequest, "name"));

	if ($name != "")
		$condition = " where name like '%$name%' ";

	$addCategories = "";

	$category 	= xmlParser_getValue($xmlRequest, "category");

	if ($category != "")
	{
		$addCategories = " join categoriesItems spc ";

		if ($condition == "") 
			$condition .= " where ";
		else
			$condition .= " and ";

		$condition .= " spc.itemId = id and spc.categoryId = $category ";
	}

	$status	 	= xmlParser_getValue($xmlRequest, "status");

	if ($status != "")
	{
		if ($condition == "")
			$condition .= " where ";
		else
			$condition .= " and ";

		$condition .= " status = '$status' ";
	}
	*/

	$addSpecials		= commonDecode(xmlParser_getValue($xmlRequest, "addSpecials"));

	// get total
	$queryStr     = "select count(*) from clubMailingLists";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$total	     = $row[0];

	$limit		 = commonGetLimit ($xmlRequest);

	$sortBy		= xmlParser_getValue($xmlRequest,"sortBy");

	if ($sortBy == "" || $sortBy == "memberId")
		$sortBy = "name";

	$sortDir	= xmlParser_getValue($xmlRequest,"sortDir");
	if ($sortDir == "")
		$sortDir = "asc";

	// get details
	$queryStr    = "select clubMailingLists.id, name, senderName, senderEmail, membersOnly, clubMailingLists.defaultLanguage, testingEmail, 
						   count(memberId) as countMembers
		   			from clubMailingLists
				    left join clubMailingListsMembers on clubMailingListsMembers.mailingListId = clubMailingLists.id
					group by clubMailingLists.id
					order by $sortBy $sortDir $limit";
	$result	     = commonDoQuery ($queryStr);

	$numRows    = commonQuery_numRows($result);

	$xmlResponse = "<items>";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		$id   			= $row['id'];
		$language		= $row['defaultLanguage'];
		$testingEmail	= $row['testingEmail'];
		$name 			= commonValidXml ($row['name'],true);
		$senderName 	= commonValidXml ($row['senderName'],true);
		$email  		= commonValidXml ($row['senderEmail'],true);
		$membersOnly  	= commonValidXml ($row['membersOnly'],true);
		$countMembers	= $row['countMembers'];

		$xmlResponse .=	"<item>
							<id>$id</id>
							<name>$name</name>
							<defaultLanguage>$language</defaultLanguage>
							<testingEmail>$testingEmail</testingEmail>
							<senderName>$senderName</senderName>
							<senderEmail>$email</senderEmail>
							<countMembers>$countMembers</countMembers>
							<membersOnly>$membersOnly</membersOnly>
						</item>";
	}

	if ($limit == "" && $addSpecials != "0")
	{
/*		$total += 2;
		$xmlResponse .=	"<item>"							.
							"<id>8888</id>"	 			. 
							"<name>".commonPhpEncode("אל בעל האתר לבדיקה",true)."</name>"			.
							"<senderName></senderName>"	.
							"<senderEmail></senderEmail>"	. 
							"<membersOnly>0</membersOnly>"	.
						"</item>";
		$xmlResponse .=	"<item>"							.
							"<id>9999</id>"	 			. 
							"<name>".commonPhpEncode("כל היצרנים הפעילים",true)."</name>"			.
							"<senderName></senderName>"	.
							"<senderEmail></senderEmail>"	. 
							"<membersOnly>0</membersOnly>"	.
							"</item>";
 */
		$domainRow = commonGetDomainRow ();
		if ($domainRow['domainName'] == "interuse.co.il")
		{
			$total++;
			$xmlResponse .=	"<item>"							.
								"<id>9900</id>"	 			. 
								"<name>".commonPhpEncode("לקוחות אינטריוז",true)."</name>"			.
								"<senderName></senderName>"	.
								"<senderEmail></senderEmail>"	. 
								"</item>";
		}
		/*
		if ($domainRow['domainName'] == "muni2008.co.il")
		{
			$total++;
			$xmlResponse .=	"<item>"							.
								"<id>9911</id>"	 			. 
								"<name>".commonPhpEncode("כל המועמדים",true)."</name>"			.
								"<senderName></senderName>"	.
								"<senderEmail></senderEmail>"	. 
								"</item>";
		}
		 */
	}

	$xmlResponse .=	"</items>"								.
							commonGetTotalXml($xmlRequest,$numRows,$total);
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getClubMailingListNextId										*/
/* ----------------------------------------------------------------------------------------------------	*/
function getClubMailingListNextId ($xmlRequest)
{
	return CC_getClubMailingListNextId($xmlRequest);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* addMailingList											*/
/* ----------------------------------------------------------------------------------------------------	*/
function addClubMailingList ($xmlRequest)
{
	return (editClubMailingList ($xmlRequest, "add"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateClubMailingList																				*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateClubMailingList ($xmlRequest)
{
	editClubMailingList ($xmlRequest, "update");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* doesClubMailingListExist																				*/
/* ----------------------------------------------------------------------------------------------------	*/
function doesClubMailingListExist ($id)
{
	return CC_doesClubMailingListExist($id);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* editClubMailingList																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function editClubMailingList ($xmlRequest, $editType)
{
	global $name, $senderName, $senderEmail, $membersOnly, $defaultLanguage, $testingEmail;

	$name			= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "name")));
	$defaultLanguage= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "defaultLanguage")));
	$senderName 	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "senderName")));
	$senderEmail	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "senderEmail")));
	$membersOnly	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "membersOnly")));
	$testingEmail	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "testingEmail")));

	return CC_editClubMailingList($xmlRequest, $editType);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getClubMailingListDetails																			*/
/* ----------------------------------------------------------------------------------------------------	*/
function getClubMailingListDetails ($xmlRequest)
{
	$id 	= xmlParser_getValue($xmlRequest, "id");
	$type 	= xmlParser_getValue($xmlRequest, "type");
		
	if ($id == "")
		trigger_error ("חסר קוד רשימת התפוצה לביצוע הפעולה");

	$queryStr = "select * from clubMailingLists where id='$id'";
	$result	  = commonDoQuery($queryStr);

	if (commonQuery_numRows($result) == 0)
		trigger_error ("רשימת התפוצה זו ($id) לא קיימת במערכת. לא ניתן לבצע את העדכון");

	$xmlResponse = "";

	$row = commonQuery_fetchRow($result);

	$name			= commonValidXml($row['name'], true);
	$language		= $row['defaultLanguage'];
	$testingEmail	= $row['testingEmail'];
	$senderName 	= commonValidXml($row['senderName'], true);
	$senderEmail 	= commonValidXml($row['senderEmail'], true);
	$membersOnly 	= commonValidXml($row['membersOnly'], true);

	if ($type == "duplicate")
	{
		$sql	= "select max(id) from clubMailingLists";
		$result	= commonDoQuery($sql);
		$row	= commonQuery_fetchRow($result);

		$id		= $row[0] + 1;
	}

	$xmlResponse .=	"<id>$id</id>"	 			. 
			"<name>$name</name>"	.
			"<defaultLanguage>$language</defaultLanguage>" .
			"<testingEmail>$testingEmail</testingEmail>" .
			"<senderName>$senderName</senderName>"	.
			"<senderEmail>$senderEmail</senderEmail>".
			"<membersOnly>$membersOnly</membersOnly>";

	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteClubMailingList												*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteClubMailingList ($xmlRequest)
{
	return CC_deleteClubMailingList ($xmlRequest);
}


/* ----------------------------------------------------------------------------------------------------	*/
/* ----------------------------------------------------------------------------------------------------	*/
$CCobjectName = "clubEmail"; $CCobjectsName = "clubEmails"; $CCHEBobjectName = "דואל";
$CCfieldsList = array("id", "mailingListId", "contactId", "layoutId", "subject", "htmlCode", "whenSent");
include "commonCommands.php";


/* ----------------------------------------------------------------------------------------------------	*/
/* getEmails											*/
/* ----------------------------------------------------------------------------------------------------	*/
function getEmails ($xmlRequest)
{	
	global $maxRowsInPage;

	$maxRowsInPage = 100;

	$condition  = "";

	// get all mailing lists
	$sql	= "select id, name from clubMailingLists";
	$result = commonDoQuery($sql);

	$lists	= array();

	while ($row = commonQuery_fetchRow($result))
	{
		$lists[$row['id']] = $row['name'];
	}

	// get total
	$queryStr     = "select count(*) from clubEmails";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$total	     = $row[0];

	// get details
	$queryStr    = "select clubEmails.* from clubEmails order by id desc " . commonGetLimit ($xmlRequest);
	$result	     = commonDoQuery ($queryStr);

	$numRows    = commonQuery_numRows($result);

	$xmlResponse = "<items>";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		$id   				= $row['id'];
		$mailingListId 		= commonValidXml ($row['mailingListId'],true);
		$layoutId 			= commonValidXml ($row['layoutId'],true);
		$subject 			= commonValidXml ($row['subject'],true);
		if ($row['whenSent'] == "0000-00-00 00:00:00")
		{
			$countMembers = 0;
			$whenSent 	  = "טרם נשלח";
			$statusCode   = "unsent";	
		}
		else
		{
/*			$sql	 		= "select count(*) from clubEmailsMembers where emailId = $id";
			$result2 		= commonDoQuery($sql);
			$row2	 		= commonQuery_fetchRow($result2);
			$countMembers 	= $row2[0];
 */
			$countMembers 	= 0;
			$whenSent		= formatApplDateTime($row['whenSent']);
			$statusCode 	= "sent";	
		}

		$mailingListName	= "";
		if ($row['mailingListId'] != "")
		{
			$listIds	= explode(",", trim($row['mailingListId']));

			foreach ($listIds as $listId)
			{
				if ($mailingListName != "") $mailingListName .= " | ";

				if (isset($lists[$listId]))
					$mailingListName .= $lists[$listId];
			}
		}

		$mailingListTitle	= str_replace("'", "`", $mailingListName);
		$mailingListCut		= commonCutText($mailingListName,80);
		if ($mailingListCut != $mailingListName)
			$mailingListCut .= "...";

		$mailingListName 	= commonValidXml("<span title='$mailingListTitle'>$mailingListCut</span>");

		$xmlResponse .=	"<item>"							.
							"<id>$id</id>"	 			. 
							"<mailingListId>$mailingListId</mailingListId>"	.
							"<mailingListName>$mailingListName</mailingListName>" .
							"<subject>$subject</subject>"	.
							"<whenSent>$whenSent</whenSent>"	.
							"<statusCode>$statusCode</statusCode>" . 
						"</item>";
	}

	$xmlResponse .=	"</items>"								.
							commonGetTotalXml($xmlRequest,$numRows,$total);
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getEmailNextId																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getEmailNextId ($xmlRequest)
{
	return CC_getClubEmailNextId($xmlRequest);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* addEmail																								*/
/* ----------------------------------------------------------------------------------------------------	*/
function addEmail ($xmlRequest)
{
	return (editEmail ($xmlRequest, "add"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateEmail																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateEmail ($xmlRequest)
{
	editEmail ($xmlRequest, "update");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* doesEmailExist																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function doesEmailExist ($id)
{
	return CC_doesClubEmailExist($id);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* editEmail																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function editEmail ($xmlRequest, $editType)
{
	global $mailingListId, $contactId, $layoutId, $subject, $htmlCode, $whenSent;

	$id 			= xmlParser_getValue($xmlRequest, "id");

	if ($editType == "update" && $id == "")
		trigger_error ("חסר קוד לביצוע הפעולה");

	$mailingListId	= str_replace(" ", ",", trim(commonDecode(xmlParser_getValue($xmlRequest, "mailingListId"))));
	$contactId		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "contactId")));
	$layoutId		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "layoutId")));
	$subject 		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "subject")));
	$htmlCode 		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "htmlCode")));
	$sendType 		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "sendType")));

	// remove Word garbage before saving to db
	$word1 = chr(194).chr(160);
	$word2 = chr(226).chr(128).chr(147);

	$htmlCode = str_replace($word1, "", $htmlCode);
	$htmlCode = str_replace($word2, "-", $htmlCode);

	$whenSent		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "whenSent")));

	if ($editType == "add")
	{
		$queryStr	= "select max(id) from clubEmails";
		$result		= commonDoQuery ($queryStr);
		$row		= commonQuery_fetchRow ($result);
		$id 		= $row[0] + 1;

		$queryStr	= "insert into clubEmails (id, mailingListId, contactId, layoutId, subject, htmlCode, whenSent)
					   values ($id, '$mailingListId', '$contactId', '$layoutId', '$subject', '$htmlCode', '$whenSent')";
		commonDoQuery ($queryStr);
	}
	else
	{
		$queryStr	= "update clubEmails set mailingListId  = '$mailingListId',
											 contactId		= '$contactId',
											 layoutId		= '$layoutId',
											 subject		= '$subject',
											 htmlCode		= '$htmlCode',
											 whenSent		= '$whenSent'
					   where id = $id";
		commonDoQuery ($queryStr);
	}

	if ($sendType == "test")
	{
		$isTest = "1";
	}
	else if ($sendType == "real")
	{
		$isTest	= "0";
	}

	if ($sendType != "")
	{
		doSendEmail ($id, $isTest);
	}

	return "<id>$id</id>";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getEmailDetails																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getEmailDetails ($xmlRequest)
{
	global $usedLangs;
	$langsArray = explode(",",$usedLangs);

	$id = xmlParser_getValue($xmlRequest, "id");
	$action = xmlParser_getValue($xmlRequest, "action");
		
	if ($id == "")
		trigger_error ("חסר קוד דוא\"ל לביצוע הפעולה");

	$queryStr = "select clubEmails.*, 
						contacts.fullname as contactFullname, contacts.email as contactEmail, layouts_byLang.name as layoutName,
						concat(clubMembers.firstname, concat(' ', clubMembers.lastname)) as memberName, clubMembers.email as memberEmail
			     from   (clubEmails, layouts_byLang)
				 left join contacts on contacts.id = clubEmails.contactId
				 left join clubMembers on clubMembers.id = clubEmails.contactId
				 where	clubEmails.layoutId = layouts_byLang.layoutId and layouts_byLang.language = '$langsArray[0]'
		   		 and    clubEmails.id='$id'";
	$result	  = commonDoQuery($queryStr);

	if (commonQuery_numRows($result) == 0)
		trigger_error ("דוא\"ל זה ($id) לא קיים במערכת. לא ניתן לבצע את העדכון");

	$xmlResponse = "";

	$row = commonQuery_fetchRow($result);

	// Support fictive mailing lists
	if ($row['mailingListId'] == 8888)
		$row['listName'] = commonEncode("אל בעל האתר לבדיקה");

	$mailingListId	= $row['mailingListId'];

	if ($mailingListId != "")
	{
		$sql	= "select id, name from clubMailingLists where id in ($mailingListId)";
		$result	= commonDoQuery($sql);

		$mailingListIdText = "";
		while ($mRow = commonQuery_fetchRow($result))
		{
			$mailingListIdText	.= "<li>$mRow[id] - $mRow[name] &laquo;</li>";
		}
	}

	if ($mailingListIdText != "")
		$mailingListIdText = "<ul class='formSpanData' style='height:60px;overflow-y:auto;'>$mailingListIdText</ul>";

	$mailingListIdText = commonValidXml($mailingListIdText);

	$mailingListId	   = str_replace(",", " ", $mailingListId);

	$contactId	= $row['contactId'];
		$contactIdText = "";
	if ($contactId != 0)
	{
		if ($row['mailingListId'] == 0)
		{
			$contactIdText = commonValidXml ($row['contactFullname'] . " <" . $row['contactEmail'] . ">");
		}
		else
		{
			$contactIdText = commonValidXml ($row['memberName'] . " <" . $row['memberEmail'] . ">");
		}
	}

	$layoutId	= $row['layoutId'];
	$layoutIdText = $layoutId . " - " . commonValidXml($row['layoutName']);
	$subject 	= commonValidXml($row['subject'], true);
	$htmlCode 	= commonValidXml($row['htmlCode'], true);
	if ($row['whenSent'] == "0000-00-00 00:00:00") {
			$whenSent = "טרם נשלח";
			$wasSentAlready = 0;
	} else {
			$whenSent	= formatApplDateTime($row['whenSent']);
			$wasSentAlready = 1;
	}

	if ($action == "duplicate")
	{
		$queryStr		= "select max(id) from clubEmails";
		$result			= commonDoQuery($queryStr);
		$row			= commonQuery_fetchRow($result);
		$id 			= $row[0] + 1;

		$mailingListId		= "";
		$mailingListIdText 	= "";
		$contactId			= "";
		$contactIdText		= "";
	}

	$sql	 		= "select count(*) from clubEmailsMembers where emailId = $id and memberId != 1234";
	$result2 		= commonDoQuery($sql);
	$row2	 		= commonQuery_fetchRow($result2);
	$countMembers 	= $row2[0];

	$xmlResponse .=	"<id>$id</id>"	 			. 
			"<mailingListId>$mailingListId</mailingListId>"	.
			"<mailingListIdText>$mailingListIdText</mailingListIdText>"	.
			"<contactId>$contactId</contactId>" .
			"<contactIdText>$contactIdText</contactIdText>".
			"<layoutId>$layoutId</layoutId>"	.
			"<layoutIdText>$layoutIdText</layoutIdText>"	.
			"<subject>$subject</subject>"	.
			"<htmlCode>$htmlCode</htmlCode>"	.
			"<whenSent>$whenSent</whenSent>"	.
			"<wasSentAlready>$wasSentAlready</wasSentAlready>
			<countMembers>$countMembers</countMembers>";
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteEmail																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteEmail ($xmlRequest)
{
	return CC_deleteClubEmail ($xmlRequest);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* sendEmail																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function sendEmail ($xmlRequest)
{
	$id 	= xmlParser_getValue($xmlRequest, "id");
	$isTest = xmlParser_getValue ($xmlRequest, "isTest");
		
	if ($id == "")
		trigger_error ("חסר קוד דוא\"ל לביצוע הפעולה");

	return doSendEmail ($id, $isTest);
}

function doSendEmail ($id, $isTest)
{
	global $ibosHomeDir;

	$queryStr = "select whenSent,mailingListId, contactId from clubEmails where id='$id'";
	$result	  = commonDoQuery($queryStr);

	if (commonQuery_numRows($result) == 0)
		trigger_error ("דוא\"ל זה ($id) לא קיים במערכת. לא ניתן לבצע את השליחה");

	$row = commonQuery_fetchRow($result);

	if ($row['whenSent'] != "0000-00-00 00:00:00")
			trigger_error ("דוא\"ל זה ($id) כבר נשלח בעבר. לא ניתן לבצע את השליחה");

	if ($row['contactId'] != 0 && $row['mailingListId'] == 0)
	{
		// check that contact is still active
		$queryStr = "select status from contacts where id = $row[contactId]";
		$result2	  = commonDoQuery($queryStr);

		if (commonQuery_numRows($result2) == 0)
			trigger_error ("פרטי ההתקשרות של דוא\"ל זה אינם קיימים במערכת. לא ניתן לבצע את השליחה");

		$row2 = commonQuery_fetchRow($result2);

		if ($row2['status'] == "inactive")
			trigger_error ("פרטי ההתקשרות אינם זמינים יותר לשליחה");
	}


	if ($isTest == "1")
	{
 		$domainRow = commonGetDomainRow ();
	
		exec("/usr/local/bin/php $ibosHomeDir/php/sendEmails.php ".$domainRow['id']." ".$id." test > /dev/null &");
	}
	else
	{
		// If a self-test email - do not mark as sent
		if ($row['mailingListId'] != 8888)
		{
			$queryStr = "update clubEmails set whenSent = now() where id='$id'";
			commonDoQuery($queryStr);
		}

 		$domainRow = commonGetDomainRow ();

		exec("/usr/local/bin/php $ibosHomeDir/php/sendEmails.php ".$domainRow['id']." ".$id." > /dev/null &");
	}

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* continueSendEmail																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function continueSendEmail ($xmlRequest)
{
	global $ibosHomeDir;

	$id = xmlParser_getValue($xmlRequest, "id");
		
	if ($id == "")
		trigger_error ("חסר קוד דוא\"ל לביצוע הפעולה");

	$queryStr = "select whenSent,mailingListId, contactId from clubEmails where id='$id'";
	$result	  = commonDoQuery($queryStr);

	if (commonQuery_numRows($result) == 0)
		trigger_error ("דוא\"ל זה ($id) לא קיים במערכת. לא ניתן לבצע את השליחה");

	$row = commonQuery_fetchRow($result);

	if ($row['whenSent'] == "0000-00-00 00:00:00")
			trigger_error ("דוא\"ל זה עדיין לא נשלח");

	// check if this is the last sent email
	$queryStr	= "select max(id) from clubEmails where whenSent != '0000-00-00 00:00:00'";
	$result	  	= commonDoQuery($queryStr);
	$maxRow 	= commonQuery_fetchRow($result);

	if ($maxRow[0] - 2 >= $id)
	{
		trigger_error ("ניתן לבצע שליחה חוזרת רק בשלושת המכתבים האחרונים שנשלחו");
	}


	if ($row['contactId'] != 0 && $row['mailingListId'] == 0)
	{
		// check that contact is still active
		$queryStr = "select status from contacts where id = $row[contactId]";
		$result2	  = commonDoQuery($queryStr);

		if (commonQuery_numRows($result2) == 0)
			trigger_error ("פרטי ההתקשרות של דוא\"ל זה אינם קיימים במערכת. לא ניתן לבצע את השליחה");

		$row2 = commonQuery_fetchRow($result2);

		if ($row2['status'] == "inactive")
			trigger_error ("פרטי ההתקשרות אינם זמינים יותר לשליחה");
	}

	// check when the last email was sent
	$queryStr	= "select max(joinTime) from clubEmailsMembers where emailId = $id";
	$result	  	= commonDoQuery($queryStr);
	$maxRow 	= commonQuery_fetchRow($result);

	if (strtotime($maxRow[0]) > strtotime("-1 hour"))
	{
		trigger_error ("ניתן לבצע שליחה חוזרת רק עבור דוא\"ל שנשלח לפני יותר משעה");
	}

 	$domainRow = commonGetDomainRow ();

	exec("/usr/local/bin/php $ibosHomeDir/php/sendEmails.php ".$domainRow['id']." ".$id." continue > /dev/null &");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getMailingListMembers																				*/
/* ----------------------------------------------------------------------------------------------------	*/
function getMailingListMembers ($xmlRequest)
{	
	$mailingListId = trim(xmlParser_getValue($xmlRequest, "mailingListId"));
	if ($mailingListId == "")
		trigger_error ("חסר מזהה רשימת תפוצה לשם קבלת גולשי הרשימה");

	if ($mailingListId == "8888")
	{
		$domainRow = commonGetDomainRow ();

		$xmlResponse = "<items>";
		$xmlResponse .=	"<item>"							.
						"<id>1</id>"	 			. 
						"<firstname>".commonPhpEncode($domainRow['contactName'])."</firstname>"	.
						"<lastname></lastname>"	. 
						"<email>".commonValidXml($domainRow['contactEmail'])."</email>"			. 
						"</item>";
		$xmlResponse .=	"</items>"								.
		commonGetTotalXml($xmlRequest,1,1);
		return ($xmlResponse);
	}

	if ($mailingListId == "9999")
	{
		// read producers to send to
			$result = commonDoQuery("select shopProducers.*, shopProducers_byLang.bizName as lastname
			from shopProducers, shopProducers_byLang
			where shopProducers.id = shopProducers_byLang.producerId and language = 'HEB'
			and shopProducers.status = 'active'");
		$numRows = $total = commonQuery_numRows($result);
		
		$xmlResponse = "<items>";

		for ($i = 0; $i < $numRows; $i++)
		{
			$row = commonQuery_fetchRow($result);
			
			$id   		= $row['id'];
			$lastname 	= commonValidXml ($row['lastname'],true);
			$email  	= commonValidXml ($row['email'],true);

			$xmlResponse .=	"<item>"							.
								"<id>$id</id>"	 			. 
								"<firstname></firstname>"	.
								"<lastname>$lastname</lastname>"	. 
								"<email>$email</email>"			. 
							"</item>";
		}

		$xmlResponse .=	"</items>"								.
						commonGetTotalXml($xmlRequest,$numRows,$total);
	
		return ($xmlResponse);
	} 
	else 
	{
		$sortBy		= xmlParser_getValue($xmlRequest,"sortBy");

		if ($sortBy == "")
			$sortBy = "lastname, firstname";	// "id"

		$sortDir	= xmlParser_getValue($xmlRequest,"sortDir");
		if ($sortDir == "")
			$sortDir = "asc";

		// get total
		$queryStr     = "select count(*) from clubMailingListsMembers where mailingListId in (" . str_replace(" ", ",", $mailingListId) . ")";
		$result	     = commonDoQuery ($queryStr);
		$row	     = commonQuery_fetchRow($result);
		$total	     = $row[0];

		// get details
		$queryStr    = "select clubMembers.id, username, firstname, lastname, email, clubMailingListsMembers.joinTime, status 
						from clubMembers, clubMailingListsMembers
						where clubMembers.id = clubMailingListsMembers.memberId
						and   mailingListId  in (" . str_replace(" ", ",", $mailingListId) . ")
						order by $sortBy $sortDir" . commonGetLimit ($xmlRequest);
		$result	     = commonDoQuery ($queryStr);

		$numRows    = commonQuery_numRows($result);
	}

	$xmlResponse = "<items>";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		$id   		= $row['id'];
		$username 	= commonValidXml ($row['username'],true);
		$firstname 	= commonValidXml ($row['firstname'],true);
		$lastname 	= commonValidXml ($row['lastname'],true);
		$email  	= commonValidXml ($row['email'],true);
		$joinTime	= formatApplDate($row['joinTime']);
		$status		= $row['status'];

		switch ($status)
		{
			case "new"		: $status = "חדש";		break;
			case "active"	: $status = "פעיל";		break;
			case "disabled" : $status = "חסום";		break;
		}
//		$status = commonPhpEncode($status);

		$xmlResponse .=	"<item>"							.
							"<id>$id</id>"	 			. 
							"<username>$username</username>"	.
							"<firstname>$firstname</firstname>"	.
							"<lastname>$lastname</lastname>"	. 
							"<email>$email</email>"			. 
							"<joinTime>$joinTime</joinTime>"	.
							"<status>$status</status>"	.
						"</item>";
	}

	$xmlResponse .=	"</items>"								.
							commonGetTotalXml($xmlRequest,$numRows,$total);
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getRestMembers																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getRestMembers ($xmlRequest)
{	
	$mailingListId = xmlParser_getValue($xmlRequest, "mailingListId");
	if ($mailingListId == "")
		trigger_error ("חסר מזהה רשימת תפוצה לשם קבלת גולשים שאינם ברשימה");


	// get details
	$queryStr    = "select id, firstname, lastname, email
					from clubMembers
					where  id not in (select memberId from clubMailingListsMembers where mailingListId = $mailingListId)
					order by concat(lastname, firstname), email";
	$result	     = commonDoQuery ($queryStr);

	$numRows    = commonQuery_numRows($result);

	$xmlResponse = "<items>";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		$id   			= $row['id'];
		$memberDetails 	= commonValidXml ($row['lastname'] . " " . $row['firstname'] . " [" . $row['email'] . "]" ,true);

		$xmlResponse .=	"<item>
							<id>$id</id>
							<memberDetails>$memberDetails</memberDetails>
						 </item>";
	}

	$xmlResponse .=	"</items>";
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* addMemberToMailingList																				*/
/* ----------------------------------------------------------------------------------------------------	*/
function addMemberToMailingList ($xmlRequest)
{
	$memberIds 	   = xmlParser_getValue($xmlRequest, "id");
	$mailingListId = xmlParser_getValue($xmlRequest, "mailingListId");

	if ($memberIds == "" || $mailingListId == "")
		trigger_error ("חסרים פרטים לביצוע הפעולה");

	$memberIds = explode(" ", trim($memberIds));

	foreach ($memberIds as $memberId)
	{
		$queryStr = "insert into clubMailingListsMembers (memberId, mailingListId, joinTime) values ($memberId, $mailingListId, now())";
		commonDoQuery ($queryStr);
	}

	return ("");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* removeMemberFromMailingList																			*/
/* ----------------------------------------------------------------------------------------------------	*/
function removeMemberFromMailingList ($xmlRequest)
{
	$memberIds 	   = xmlParser_getValues($xmlRequest, "id");
	$mailingListId = xmlParser_getValue ($xmlRequest, "mailingListId");

	if (count($memberIds) == 0 || $mailingListId == "")
		trigger_error ("חסרים פרטים לביצוע הפעולה");

	foreach ($memberIds as $memberId)
	{
		$queryStr = "delete from clubMailingListsMembers where memberId=$memberId and mailingListId=$mailingListId";
		commonDoQuery ($queryStr);
	}

	return ("");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* checkImportPassword																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function checkImportPassword ($xmlRequest)
{
	$actionPassword	= xmlParser_getValue($xmlRequest, "actionPassword");

	$status = "OK";
	if ($actionPassword != "testIt")
		$status = "FAILED";

	return "<status>$status</status>";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* excelExport																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function excelExport ($xmlRequest)
{
	global $usedLangs;

	set_time_limit(0);

	$now			= "תאריך הפקת הדוח:" . date("d/m/Y H:i");
	$reportTitle	= "דוח גולשים רשומים";

	$langsArray = explode(",",$usedLangs);

	$titles			= array ("שם משתמש","סיסמא","שם פרטי","שם משפחה","כינוי","מין","אימייל","טלפון","טלפון נוסף","סלולרי","פקס","ICQ","מסנג'ר","מדינה","עיר","כתובת","מיקוד","תחום העיסוק","האתר שלי","תאריך לידה","תאריך הצטרפות","תאריך תוקף","סטטוס","קוד זיהוי","מקור ההרשמה");

	$headers		= "";
	foreach ($titles as $title)
	{
		$headers .= "<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">$title</Data></Cell>";
	}

	$countHeaders	= count($headers);

	$queryStr	 = "select * from clubMembersExtraData";
	$result	     = commonDoQuery ($queryStr);

	$extraData1  = "";
	$extraData2  = "";
	$extraData3  = "";
	$extraData4  = "";
	$extraData5  = "";
	$extraData6  = "";
	$extraData7  = "";
	$extraData8  = "";
	$extraData9  = "";
	$extraData10 = "";
	if (commonQuery_numRows($result) != 0)
	{
		$row	     = commonQuery_fetchRow($result);
		$extraData1	 = stripslashes($row['extraData1']);
		$extraData2	 = stripslashes($row['extraData2']);
		$extraData3	 = stripslashes($row['extraData3']);
		$extraData4	 = stripslashes($row['extraData4']);
		$extraData5	 = stripslashes($row['extraData5']);
		$extraData6	 = stripslashes($row['extraData6']);
		$extraData7	 = stripslashes($row['extraData7']);
		$extraData8	 = stripslashes($row['extraData8']);
		$extraData9	 = stripslashes($row['extraData9']);
		$extraData10 = stripslashes($row['extraData10']);

		if ($extraData1  != "") 	
		{
			$headers .= "<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">$extraData1</Data></Cell>";
			$countHeaders++;
		}

		if ($extraData2  != "") 	
		{
			$headers .= "<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">$extraData2</Data></Cell>";
			$countHeaders++;
		}

		if ($extraData3  != "") 	
		{
			$headers .= "<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">$extraData3</Data></Cell>";
			$countHeaders++;
		}

		if ($extraData4  != "") 	
		{
			$headers .= "<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">$extraData4</Data></Cell>";
			$countHeaders++;
		}

		if ($extraData5  != "") 	
		{
			$headers .= "<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">$extraData5</Data></Cell>";
			$countHeaders++;
		}

		if ($extraData6  != "") 	
		{
			$headers .= "<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">$extraData6</Data></Cell>";
			$countHeaders++;
		}

		if ($extraData7  != "") 	
		{
			$headers .= "<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">$extraData7</Data></Cell>";
			$countHeaders++;
		}

		if ($extraData8  != "") 	
		{
			$headers .= "<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">$extraData8</Data></Cell>";
			$countHeaders++;
		}

		if ($extraData9  != "") 	
		{
			$headers .= "<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">$extraData9</Data></Cell>";
			$countHeaders++;
		}

		if ($extraData10  != "") 	
		{
			$headers .= "<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">$extraData10</Data></Cell>";
			$countHeaders++;
		}
	}

	$countHeaders--;

    $excelHd = "<?xml version='1.0' encoding='ISO-8859-8' ?>												
				<Workbook xmlns=\"urn:schemas-microsoft-com:office:spreadsheet\"
				 	      xmlns:o=\"urn:schemas-microsoft-com:office:office\"
						  xmlns:x=\"urn:schemas-microsoft-com:office:excel\"
						  xmlns:ss=\"urn:schemas-microsoft-com:office:spreadsheet\"
						  xmlns:html=\"http://www.w3.org/TR/REC-html40\">
					<OfficeDocumentSettings xmlns=\"urn:schemas-microsoft-com:office:office\">
						<Colors>
							<Color>
								<Index>39</Index>
   								<RGB>#E3E3E3</RGB>
							</Color>
						</Colors>
					</OfficeDocumentSettings>
					<ExcelWorkbook xmlns=\"urn:schemas-microsoft-com:office:excel\">
						<WindowHeight>7860</WindowHeight>
						<WindowWidth>14040</WindowWidth>
			  			<WindowTopX>0</WindowTopX>
			  			<WindowTopY>1905</WindowTopY>
			  			<ProtectStructure>False</ProtectStructure>
			  			<ProtectWindows>False</ProtectWindows>
			 		</ExcelWorkbook>
 					<Styles>
  						<Style ss:ID=\"sTitle\">
							<Alignment ss:Horizontal=\"Right\" ss:Vertical=\"Center\"/>
							<Font x:Family=\"Swiss\" ss:Color=\"#FF5F00\" ss:Size=\"16\" ss:Bold=\"1\"/>
					  	</Style>
  						<Style ss:ID=\"sReportDate\">
							<Alignment ss:Horizontal=\"Right\" ss:Vertical=\"Center\"/>
							<Font x:Family=\"Swiss\" ss:Color=\"#597FA3\" ss:Size=\"12\" ss:Bold=\"1\"/>
					  	</Style>
  						<Style ss:ID=\"sTotal\">
							<Alignment ss:Horizontal=\"Center\" ss:Vertical=\"Center\"/>
			   				<Borders>
		    					<Border ss:Position=\"Bottom\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Left\"   ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Right\"  ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Top\"    ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
			   				</Borders>
		   					<Interior ss:Color=\"#EEEEEE\" ss:Pattern=\"Solid\"/>
					  	</Style>
 			 			<Style ss:ID=\"sSection\">
			   				<Alignment ss:Horizontal=\"Center\" ss:Vertical=\"Center\"/>
			   				<Borders>
			   					<Border ss:Position=\"Bottom\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Left\"   ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Right\"  ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
			   					<Border ss:Position=\"Top\"    ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		   					</Borders>
			   				<Font x:Family=\"Swiss\" ss:Color=\"#000000\" ss:Bold=\"1\"/>
		   					<Interior ss:Color=\"#FBBE55\" ss:Pattern=\"Solid\"/>
			  			</Style>
 			 			<Style ss:ID=\"sHeader\">
			   				<Alignment ss:Horizontal=\"Center\" ss:Vertical=\"Center\" ss:WrapText=\"1\"/>
			   				<Borders>
			   					<Border ss:Position=\"Bottom\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Left\"   ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Right\"  ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
			   					<Border ss:Position=\"Top\"    ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		   					</Borders>
			   				<Font x:Family=\"Swiss\" ss:Color=\"#000000\" ss:Bold=\"1\"/>
		   					<Interior ss:Color=\"#DEECFC\" ss:Pattern=\"Solid\"/>
			  			</Style>
 			 			<Style ss:ID=\"sFooter\">
			   				<Alignment ss:Horizontal=\"Right\" ss:Vertical=\"Bottom\"/>
			   				<Borders>
			   					<Border ss:Position=\"Bottom\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Left\"   ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Right\"  ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
			   					<Border ss:Position=\"Top\"    ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		   					</Borders>
			   				<Font x:Family=\"Swiss\" ss:Color=\"#000000\" ss:Bold=\"1\"/>
		   					<Interior ss:Color=\"#EEEEEE\" ss:Pattern=\"Solid\"/>
			  			</Style>
 			 			<Style ss:ID=\"sFooterLeft\">
			   				<Alignment ss:Horizontal=\"Left\" ss:Vertical=\"Bottom\"/>
			   				<Borders>
			   					<Border ss:Position=\"Bottom\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Left\"   ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Right\"  ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
			   					<Border ss:Position=\"Top\"    ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		   					</Borders>
			   				<Font x:Family=\"Swiss\" ss:Color=\"#000000\" ss:Bold=\"1\"/>
		   					<Interior ss:Color=\"#EEEEEE\" ss:Pattern=\"Solid\"/>
			  			</Style>
			  			<Style ss:ID=\"sCell\">
			   				<Alignment ss:Horizontal=\"Center\" ss:Vertical=\"Center\" ss:WrapText=\"1\"/>
			   				<Borders>
		    					<Border ss:Position=\"Bottom\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Left\"   ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Right\"  ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
			   				</Borders>
			   				<Font x:Family=\"Swiss\" ss:Bold=\"0\"/>
			 			</Style>
			  			<Style ss:ID=\"sCellRight\">
			   				<Alignment ss:Horizontal=\"Right\" ss:Vertical=\"Center\"/>
			   				<Borders>
		    					<Border ss:Position=\"Bottom\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Left\"   ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Right\"  ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
			   				</Borders>
			   				<Font x:Family=\"Swiss\" ss:Bold=\"0\"/>
			 			</Style>
			  			<Style ss:ID=\"sCellEng\">
			   				<Alignment ss:Horizontal=\"Left\" ss:Vertical=\"Bottom\"/>
			   				<Borders>
		    					<Border ss:Position=\"Bottom\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Left\"   ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Right\"  ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
			   				</Borders>
			   				<Font x:Family=\"Swiss\" ss:Bold=\"0\"/>
			 			</Style>
						<Style ss:ID=\"Default\" ss:Name=\"Normal\">
			   				<Alignment ss:Vertical=\"Bottom\"/>
   							<Borders/>
							<Font x:CharSet=\"177\"/>
   							<Interior/>
			   				<NumberFormat/>
			   				<Protection/>
			  			</Style>
						<Style ss:ID=\"s32\">
			   				<Alignment ss:Horizontal=\"Center\" ss:Vertical=\"Bottom\"/>
			  			</Style>
			  			<Style ss:ID=\"s74\">
			   				<Alignment ss:Horizontal=\"Center\" ss:Vertical=\"Center\"  ss:WrapText=\"1\"/>
			  				<Borders>
								<Border ss:Position=\"Bottom\" ss:LineStyle=\"Continuous\" ss:Weight=\"2\"/>
								<Border ss:Position=\"Left\"   ss:LineStyle=\"Continuous\" ss:Weight=\"2\"/>
								<Border ss:Position=\"Right\"  ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
								<Border ss:Position=\"Top\"    ss:LineStyle=\"Continuous\" ss:Weight=\"2\"/>
							</Borders>
							<Font x:CharSet=\"177\" x:Family=\"Swiss\" ss:Color=\"#FFFFFF\" ss:Bold=\"1\"/>
							<Interior ss:Color=\"#969696\" ss:Pattern=\"Solid\"/>
			  			</Style>
			  			<Style ss:ID=\"s75\">
							<Alignment ss:Horizontal=\"Center\" ss:Vertical=\"Bottom\"/>
			   				<Borders>
   								<Border ss:Position=\"Bottom\" ss:LineStyle=\"Continuous\" ss:Weight=\"2\"/>
		    					<Border ss:Position=\"Left\"   ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Right\"  ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Top\"    ss:LineStyle=\"Continuous\" ss:Weight=\"2\"/>
				   			</Borders>
			   				<Font x:CharSet=\"177\" x:Family=\"Swiss\" ss:Color=\"#FFFFFF\" ss:Bold=\"1\"/>
							<Interior ss:Color=\"#969696\" ss:Pattern=\"Solid\"/>
			  			</Style>
			  			<Style ss:ID=\"s76\">
			   				<Alignment ss:Horizontal=\"Center\" ss:Vertical=\"Bottom\"/>
			   				<Borders>
		    					<Border ss:Position=\"Bottom\" ss:LineStyle=\"Continuous\" ss:Weight=\"2\"/>
		    					<Border ss:Position=\"Left\"   ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Right\"  ss:LineStyle=\"Continuous\" ss:Weight=\"2\"/>
		    					<Border ss:Position=\"Top\"    ss:LineStyle=\"Continuous\" ss:Weight=\"2\"/>
			  				</Borders>
			   				<Font x:CharSet=\"177\" x:Family=\"Swiss\" ss:Color=\"#FFFFFF\" ss:Bold=\"1\"/>
			   				<Interior ss:Color=\"#969696\" ss:Pattern=\"Solid\"/>
			  			</Style>
			  			<Style ss:ID=\"s77\">
			  				<Alignment ss:Horizontal=\"Left\" ss:Vertical=\"Bottom\"/>
			   				<Borders>
		    					<Border ss:Position=\"Left\"  ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Right\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
			   				</Borders>
			   				<Font x:Family=\"Swiss\" ss:Color=\"#0000FF\" ss:Bold=\"1\"/>
			  			</Style>
			  			<Style ss:ID=\"s88\">
   							<Font x:Family=\"Swiss\" ss:Color=\"#333300\" ss:Bold=\"1\"/>
			  			</Style>
			  			<Style ss:ID=\"s95\">
			   				<Alignment ss:Horizontal=\"Center\" ss:Vertical=\"Bottom\"/>
			   				<Borders>
		    					<Border ss:Position=\"Bottom\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Left\"   ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Right\"  ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Top\"    ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
			   				</Borders>
			   				<Interior ss:Color=\"#E3E3E3\" ss:Pattern=\"Solid\"/>
			  			</Style>
			  			<Style ss:ID=\"s96\">
			   				<Alignment ss:Horizontal=\"Center\" ss:Vertical=\"Bottom\"/>
			   				<Borders>
		    					<Border ss:Position=\"Bottom\" ss:LineStyle=\"Continuous\" ss:Weight=\"2\"/>
		    					<Border ss:Position=\"Left\"   ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Right\"  ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Top\"    ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
   							</Borders>
			   				<Interior ss:Color=\"#E3E3E3\" ss:Pattern=\"Solid\"/>
			  			</Style>
			  			<Style ss:ID=\"s98\">
			   				<Alignment ss:Horizontal=\"Left\" ss:Vertical=\"Bottom\"/>
			   				<Font x:Family=\"Swiss\" ss:Color=\"#0000FF\" ss:Italic=\"1\"/>
			 			</Style>
			  			<Style ss:ID=\"s99\">
			   				<Alignment ss:Horizontal=\"Left\" ss:Vertical=\"Bottom\"/>
			   				<Font x:Family=\"Swiss\" ss:Color=\"#0000FF\" ss:Italic=\"1\"/>
			   				<NumberFormat ss:Format=\"Short Date\"/>
			  			</Style>
			  			<Style ss:ID=\"s105\">
			   				<Borders>
		    					<Border ss:Position=\"Left\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
			   				</Borders>
			  			</Style>
			  			<Style ss:ID=\"s106\">
			   				<Alignment ss:Horizontal=\"Center\" ss:Vertical=\"Bottom\"/>
			   				<Borders>
		    					<Border ss:Position=\"Bottom\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Left\"   ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Right\"  ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
			   				</Borders>
			   				<Interior ss:Color=\"#E3E3E3\" ss:Pattern=\"Solid\"/>
			  			</Style>
 					</Styles>
					<Worksheet ss:Name=\"$reportTitle\" ss:RightToLeft=\"1\">
					<Table x:FullColumns=\"1\" x:FullRows=\"1\">
   	        			<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"50\"/>
						<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"120\"/>
						<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"120\"/>
						<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"120\"/>
						<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"120\"/>
						<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"120\"/>
						<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"120\"/>
						<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"120\"/>
						<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"120\"/>
						<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"120\"/>
						<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"120\"/>
						<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"120\"/>
						<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"120\"/>
						<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"120\"/>
						<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"120\"/>
						<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"120\"/>
						<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"120\"/>
						<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"120\"/>
						<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"120\"/>
						<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"120\"/>
						<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"120\"/>
						<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"120\"/>
						<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"120\"/>
						<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"120\"/>
						<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"120\"/>
						<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"120\"/>
						<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"300\"/>
					<Row>
						<Cell ss:MergeAcross=\"$countHeaders\" ss:StyleID=\"sTitle\">
							<Data ss:Type=\"String\">$reportTitle</Data>
						</Cell>
					</Row>
					<Row>
						<Cell ss:MergeAcross=\"$countHeaders\" ss:StyleID=\"sReportDate\"><Data ss:Type=\"String\">$now</Data></Cell>
					</Row>
					<Row ss:Height=\"35\">$headers</Row>";

	$excelHd = iconv("UTF-8", "Windows-1255", $excelHd);

	$excelFt = 	 "</Table>
				</Worksheet>
			</Workbook>";

	$condition  = "";
	
	$anyText		= commonDecode(xmlParser_getValue($xmlRequest, "anyText"));
	if ($anyText != "")
		$condition .= " and (username like '%$anyText%' or firstname like '%$anyText%' or lastname like '%$anyText%' or nickname like '%nickname%') ";

	$email		= commonDecode(xmlParser_getValue($xmlRequest, "email"));
	if ($email != "")
		$condition .= " and email like '%$email%' ";

	$join				= "";
	$mailingListId		= commonDecode(xmlParser_getValue($xmlRequest, "mailingListId"));
	if ($mailingListId != "")
	{
		$join = ", clubMailingListsMembers";
		$condition .= " and clubMailingListsMembers.mailingListId = $mailingListId 
						and clubMailingListsMembers.memberId = clubMembers.id ";
	}

	$queryStr    = "select count(*) from (clubMembers $join) where 1 $condition";
	$result		 = commonDoQuery($queryStr);
	$row		 = commonQuery_fetchRow($result);
	$total		 = $row[0];

  	$excel		 = "";

	$limit		 = 10000;

	$max		 = floor($total / $limit);

	if (($total % $limit) != 0)
		$max++;

	for ($i = 0; $i <= $max; $i++)
	{
	  $from	= $limit * $i;

	  $queryStr  = "select clubMembers.*, cities_byLang.name as cityName
					from (clubMembers $join)
					left join cities_byLang on clubMembers.city = cities_byLang.cityId and language = '$langsArray[0]'
				    where 1 $condition order by clubMembers.id limit $from, $limit";
  
	  $result	  = commonDoQuery ($queryStr);

	  while ($row = commonQuery_fetchRow($result))
	  {
		$username 		= commonPrepareToFile(stripslashes($row['username']));
		$password 		= commonPrepareToFile(stripslashes($row['password']));
		$firstname 		= commonPrepareToFile(stripslashes($row['firstname']));
		$lastname 		= commonPrepareToFile(stripslashes($row['lastname']));
		$nickname 		= commonPrepareToFile(stripslashes($row['nickname']));
		$email  		= commonPrepareToFile(stripslashes($row['email']));
		$phone			= commonPrepareToFile(stripslashes($row['phone']));
		$phone2			= commonPrepareToFile(stripslashes($row['phone2']));
		$cellphone		= commonPrepareToFile(stripslashes($row['cellphone']));
		$fax			= commonPrepareToFile(stripslashes($row['fax']));
		$icq			= commonPrepareToFile(stripslashes($row['icq']));
		$messenger		= commonPrepareToFile(stripslashes($row['messenger']));
		$city			= commonPrepareToFile(stripslashes($row['cityName']));

		if ($row['cityName'] == "")
			$city		= commonPrepareToFile(stripslashes($row['city']));

		$country		= commonPrepareToFile(stripslashes($row['country']));
		$address		= commonPrepareToFile(stripslashes($row['address']));
		$zipcode		= commonPrepareToFile(stripslashes($row['zipcode']));
		$occupation		= commonPrepareToFile(stripslashes($row['occupation']));
		$mySite			= commonPrepareToFile(stripslashes($row['mySite']));
		$birthDate		= commonPrepareToFile(stripslashes($row['birthDate']));
		$expireTime		= commonPrepareToFile(stripslashes($row['expireTime']));
		$referer 		= commonPrepareToFile(stripslashes($row['referer']));
		$joinTime		= formatApplDate($row['joinTime']);
		$status			= $row['status'];
		$verifyCode		= $row['verifyCode'];

		if ($row['expireTime'] != "0000-00-00 00:00:00" && strtotime($row['expireTime']) < strtotime(date("Y-m-d")))
		{
			$status	= "פג תוקף";
		}

		switch ($status)
		{
			case "new"		: $status = "חדש";		break;
			case "active"	: $status = "פעיל";		break;
			case "disabled" : $status = "חסום";		break;
		}

		$status			= commonPrepareToFile($status);

		$gender			= $row['gender'];

		switch ($gender)
		{
			case "f"	:	$gender	= "נקבה";	break;
			case "m"	:	$gender	= "זכר";	break;
		}

		$gender				= commonPrepareToFile($gender);

		$row['extraData1']  = commonPrepareToFile(stripslashes($row['extraData1']));
		$row['extraData2']  = commonPrepareToFile(stripslashes($row['extraData2']));
		$row['extraData3']  = commonPrepareToFile(stripslashes($row['extraData3']));
		$row['extraData4']  = commonPrepareToFile(stripslashes($row['extraData4']));
		$row['extraData5']  = commonPrepareToFile(stripslashes($row['extraData5']));
		$row['extraData6']  = commonPrepareToFile(stripslashes($row['extraData6']));
		$row['extraData7']  = commonPrepareToFile(stripslashes($row['extraData7']));
		$row['extraData8']  = commonPrepareToFile(stripslashes($row['extraData8']));
		$row['extraData9']  = commonPrepareToFile(stripslashes($row['extraData9']));
		$row['extraData10'] = commonPrepareToFile(stripslashes($row['extraData10']));

		$excel .= "<Row ss:Height=\"13.5\">
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$username</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$password</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$firstname</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$lastname</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$nickname</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$gender</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$email</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$phone</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$phone2</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$cellphone</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$fax</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$icq</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$messenger</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$country</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$city</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$address</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$zipcode</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$occupation</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$mySite</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$birthDate</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$joinTime</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$expireTime</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$status</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$verifyCode</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$referer</Data></Cell>";

		if ($extraData1   != "") $excel .= "<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$row[extraData1]</Data></Cell>";
		if ($extraData2   != "") $excel .= "<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$row[extraData2]</Data></Cell>";
		if ($extraData3   != "") $excel .= "<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$row[extraData3]</Data></Cell>";
		if ($extraData4   != "") $excel .= "<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$row[extraData4]</Data></Cell>";
		if ($extraData5   != "") $excel .= "<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$row[extraData5]</Data></Cell>";
		if ($extraData6   != "") $excel .= "<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$row[extraData6]</Data></Cell>";
		if ($extraData7   != "") $excel .= "<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$row[extraData7]</Data></Cell>";
		if ($extraData8   != "") $excel .= "<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$row[extraData8]</Data></Cell>";
		if ($extraData9   != "") $excel .= "<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$row[extraData9]</Data></Cell>";
		if ($extraData10  != "") $excel .= "<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$row[extraData10]</Data></Cell>";

		$excel	.= "</Row>";
	  }
	}

	$excel	= "$excelHd$excel$excelFt";

	$xmlResponse = commonDoExcel ($excel);

	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* importFile																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function importFile ($xmlRequest)
{
	global $userId;
	global $ibosHomeDir;

	set_time_limit(0);

	$file			= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "file")));	
	$importOption	= xmlParser_getValue($xmlRequest, "importOption");	

	// check suffix
	$suffix	= commonFileSuffix ($file);

	if ($suffix != ".csv")
		trigger_error ("ניתן לטעון רק קבצי CSV");

	// Make a list of all users
	$allUsers	= array();
	$sql		= "select id, username, email from clubMembers";
	$result		= commonDoQuery($sql);
	while ($row = commonQuery_fetchRow($result))
			if ($row['username'])
					$allUsers[$row['username']] = $row['id'];
			else if ($row['email'])
					$allUsers[$row['email']] = $row['id'];

	// Make a list of all members in MailingLists
	$allUsersInMailingList = array();
	$sql		= "select memberId, mailingListId from clubMailingListsMembers";
	$result		= commonDoQuery($sql);
	while ($row = commonQuery_fetchRow($result))
			array_push($allUsersInMailingList, $row['memberId'].'-'.$row['mailingListId']);

	// find new member id
	$sql			= "select max(id) from clubMembers";
	$result			= commonDoQuery($sql);
	$row			= commonQuery_fetchRow($result);
	$nextMemberId	= $row[0]+1;

	$filePath 	= "$ibosHomeDir/html/SWFUpload/files/$userId";

	$f = fopen("$filePath/$file", "r");

	$line = fgets($f);	// read headers row

	while ($line = fgets($f)) 
	{ 
		$splitLine = explode(",", $line);

		$username		= commonPrepareToDB(trim($splitLine[0]));
		$password		= commonPrepareToDB(trim($splitLine[1]));
		$firstname		= commonPrepareToDB(trim($splitLine[2]));
		$lastname		= commonPrepareToDB(trim($splitLine[3]));
		$email			= commonPrepareToDB(trim($splitLine[4]));
		$joinTime		= commonPrepareToDB(trim($splitLine[5]));
		$mailingList	= commonPrepareToDB(trim($splitLine[6]));
		if (isset($splitLine[7])) // optional
				$phone			= commonPrepareToDB(trim($splitLine[7]));
		else
				$phone 			= "";
		if (isset($splitLine[8])) // optional
				$country		= commonPrepareToDB(trim($splitLine[8]));
		else
				$country 		= "";
		if (isset($splitLine[9])) // optional
				$occupation		= commonPrepareToDB(trim($splitLine[9]));
		else
				$occupation		= "";
	
		if ($joinTime)
		{
			$joinTime 		= preg_replace("/^([0-9]{1,2})[\/\. -]+([0-9]{1,2})[\/\. -]+([0-9]{1,4})/", "\\2/\\1/\\3", $joinTime);
			$joinTime 		= date("Y-m-d H:i:00", strtotime($joinTime));
		}

		// check if member already exist
		$newMember = false;
		if ($username != "" && array_key_exists($username, $allUsers))
				$memberId = $allUsers[$username];
		else if ($email != "" && array_key_exists($email, $allUsers))
				$memberId = $allUsers[$email];
		else
		{
			$newMember = true;

			$code 		= randomCode(25);

			$memberId	= $nextMemberId;
			$nextMemberId++;

			$sql		= "insert into clubMembers (id, memberType, status, username, password, firstname, lastname, email, joinTime, 
													verifyCode, gender, phone, country, occupation)
						   values ($memberId, 'member', 'active', '".addslashes($username)."', '$password', '".addslashes($firstname)."', '".
								   addslashes($lastname)."', '$email', '$joinTime', '$code', '', '$phone', '$country', '$occupation')";
			commonDoQuery ($sql);
		}

		if ($mailingList != "")
		{
			$addToList = true;

			if (!$newMember)
			{
				if ($importOption == "onlyNewMembers")
				{
					$addToList = false;
				}
				else
					$addToList = !in_array($memberId.'-'.$mailingList, $allUsersInMailingList);
			}
	
			if ($addToList)
			{
				$sql		= "insert into clubMailingListsMembers (memberId, mailingListId, joinTime)
							   values ($memberId, $mailingList, '$joinTime')";
				commonDoQuery ($sql);
			}
		}
	} 

	fclose ($f);

	unlink ("$filePath/$file");

	return "";
}

?>
