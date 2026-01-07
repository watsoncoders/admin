<?php

$clubTags		= array("status", "firstname", "lastname", "username", "password", "email", "phone", "cellphone", "fax", "address", "birthDate",
						"gender", "mySite", "streetNo", "city", "zipcode", "country", "extraData3", "extraData6", "joinTime");
$tags			= array("fldDirectoryName", "fldExtentName", "fldGeneralLongevity", "fldPublicSurerAddditionalNote", "memberLanguage", 
						"fldPublicAdditionalNotes", "fldQualifications", "fldQualificationsAdditionalNotes", "fldStructuredQualifications", 
						"fldOrgenizations", "fldZone", "fldDialZone", "fldPublicPhone", "fldPublicFax", "fldPublicMobil", "fldPublicAddress", 
						"fldPublicEmail", "fldGraduatedLevel", 
						"fldGraduatedLevelAdv", "fldExternalGraduated", "fldNotYetGraduated", "fldMegasherHutzh", "fldMegasherMusmahMahon", 
						"fldDeclaration", "fldHamlathot", "fldLoyer", "fldJudge", "fldBorer", "fldAllowSendingPrivateMessages", 
						"fldReciveMessagesByEmail", "fldReceiveMessagesFromTheIII", "fldCreationDate", "fldAdminRemarks", 
						"fldSearchKeywords", "fldCategoriesSearchKeywords", "fldProfession", "fldSpecialization", "fldLongevity", "fldAdditionalNotes",
						"moreDetails", "hideContactDetails", "mailAddress", "extraDetails", "moreSchoolDetails", "langs", "currBiz", "licenseNo", 
						"workplace", "experience1", "experience2", "experience3", "experience4", "catsExtraDetails", "thanksLetter", "payFree");

/* ----------------------------------------------------------------------------------------------------	*/
/* getMembers																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function getMembers ($xmlRequest)
{	
	$domainRow   = commonGetDomainRow ();
	$siteUrl     = commonGetDomainName($domainRow);
	
	commonConnectToUserDB ($domainRow);

	$all		= xmlParser_getValue($xmlRequest,"all");

	$sortDir	= xmlParser_getValue($xmlRequest,"sortDir");
	if ($sortDir == "")
		$sortDir = "desc";

	$sortBy		= xmlParser_getValue($xmlRequest,"sortBy");

	if ($sortBy == "")
		$sortBy = "clubMembers.id";

	$conditions  = "";

	$memberLanguage	 	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "memberLanguage")));
	if ($memberLanguage != "")
	{
		$conditions	.= " and israeli_experts.memberLanguage = '$memberLanguage'";
	}

	$extraData3	 	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "extraData3")));
	if ($extraData3 != "")
	{
		$conditions	.= " and clubMembers.extraData3 = '$extraData3'";
	}

	$extraData4	 	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "extraData4")));

	if ($extraData4 != "")
	{
		$conditions	.= " and clubMembers.extraData4 = 'student'";
	}
/*
	else
	{
		$conditions	.= " and clubMembers.extraData4 = 'expert' and clubMembers.status != 'new'";
	}*/

	$status 	 	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "status")));
	if ($status != "")
	{
		if ($status == "promoted")
			$conditions	.= " and clubMembers.extraData6 >= '1'";
		else if ($status == "hidden")
			$conditions .= " and israeli_experts.hideContactDetails = '1'";
		else if ($status == "active")
			$conditions	.= " and clubMembers.status = '$status' and clubMembers.extraData6 < '1' and israeli_experts.hideContactDetails != '1'";
		else				
			$conditions	.= " and clubMembers.status = '$status'";
	}

	$byText 	 	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "byText")));
	if ($byText != "")
		$conditions .= " and (clubMembers.firstname like '%$byText%' or
							  clubMembers.lastname  like '%$byText%' or
							  israeli_experts.fldProfession  like '%$byText%' or
							  israeli_experts.fldSpecialization  like '%$byText%' or
							  israeli_experts.fldAdditionalNotes  like '%$byText%' or
							  israeli_experts.fldPublicAdditionalNotes  like '%$byText%' or
							  israeli_experts.fldOrgenizations  like '%$byText%' or
							  israeli_experts.fldPublicAddress  like '%$byText%' or
							  israeli_experts.licenseNo  like '%$byText%' or
							  concat(clubMembers.firstname, concat(' ', clubMembers.lastname))  like '%$byText%' or
							  concat(clubMembers.lastname,  concat(' ', clubMembers.firstname)) like '%$byText%') ";

	$name 	 	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "name")));
	if ($name != "")
	{
		$nameText	= join("%%", explode(" ", $name));

		$conditions .= " and (clubMembers.firstname like '%$nameText%' or
							  clubMembers.lastname  like '%$nameText%' or
							  concat(clubMembers.firstname, concat(' ', clubMembers.lastname))  like '%$nameText%' or
							  concat(clubMembers.lastname,  concat(' ', clubMembers.firstname)) like '%$nameText%') ";
	}

	$dialZone 	 	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "dialZone")));
	if ($dialZone != "")
		$conditions	.= " and israeli_experts.fldDialZone = '$dialZone'";

	// get total
	$sql	= "select clubMembers.*, israeli_experts.*, concat(clubMembers.lastname, clubMembers.firstname) as name, isReady,
					  israeli_courses.name as courseName
			   from (clubMembers, israeli_experts)
			   left join pages_byLang on memberPageId = pages_byLang.pageId and pages_byLang.language = clubMembers.memberLanguage
			   left join israeli_courses on israeli_courses.pageId = clubMembers.extraData3
			   where clubMembers.id = israeli_experts.memberId $conditions";
	$result	= commonDoQuery ($sql);
	$total	= commonQueryNumRows($result);

	// get details
	$sql  .= " order by $sortBy $sortDir ";
   
	if ($all != 1)
		$sql .= commonGetLimit ($xmlRequest);

	$result	    = commonDoQuery ($sql);
	$numRows    = commonQueryNumRows($result);

	$xmlResponse = "<items>";

	while ($row = commonQueryFetchRow($result))
	{
		$id		= $row['id'];
		$oldId	= $row['extraData2'];

		$status	= $row['status'];

//		$promoted = ($row['extraData6'] == "1") ? "promoted' title='מקודם" : "";

		switch ($status)
		{
			case "new"			: $status	= "<div class='yellow'>מבקש הרשמה</div>";	break;
			case "active"		: $status	= "<div class='green'>פעיל";
								  if ($row['extraData6'] >= "1")
										  $status .= " - מקודם";
								  else if ($row['hideContactDetails'] == "1")
										  $status .= " - ללא פרטים";
								  else
										  $status .= " - רגיל";
								  $status 	.= "</div>";
								  break;
			case "disabled"		: $status 	= "<div class='red'>לא פעיל - ישן</div>";	break;
		}
		$status			= commonValidXml($status);

		$extraData3		= commonValidXml($row['courseName']);

		$name			= trim(stripslashes($row['lastname']) . " " . stripslashes($row['firstname']));

		$pageLink		= $row['memberPageId'];
		$pageLink		= "<a href='$siteUrl/index2.php?id=$pageLink&lang=$row[memberLanguage]' target='_blank' title='צפייה בכרטיס'>$pageLink</a>";
		if ($row['email'])
			$pageLink	.= "&nbsp;&nbsp;&nbsp;<a href='mailto:$row[email]' title='שלח מייל'>@</a>";
		else if ($row['fldPublicEmail'])
			$pageLink	.= "&nbsp;&nbsp;&nbsp;<a href='mailto:$row[fldPublicEmail]' title='שלח מייל'>@</a>";
		$pageLink		= commonValidXml($pageLink);

		$username		= commonValidXml($row['username']);
		$fldProfession	= commonValidXml($row['fldProfession']);
		$longevity		= "";
		
		if ($row['fldGeneralLongevity'] != 0)
			$longevity	= commonValidXml("$row[fldGeneralLongevity] שנים");

		$memberLanguage	= commonValidXml(($row['memberLanguage'] == "ENG") ? "אנגלית" : "עברית");

		$xmlResponse .=	"<item>
							 <id>$id</id>
							 <oldId>$oldId</oldId>
							 <status>$status</status>
							 <extraData3>$extraData3</extraData3>
							 <name>$name</name>
							 <username>$username</username>
							 <code>$row[verifyCode]</code>
							 <fldProfession>$fldProfession</fldProfession>
							 <fldGeneralLongevity>$longevity</fldGeneralLongevity>
							 <pageLink>$pageLink</pageLink>
							 <memberLanguage>$memberLanguage</memberLanguage>
						 </item>";
	}

	$xmlResponse .=	"</items>";
	
	if ($all != 1)
		$xmlResponse .= commonGetTotalXml($xmlRequest,$numRows,$total);
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getMemberDetails																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getMemberDetails ($xmlRequest)
{
	global $tags, $clubTags;
	global $jobilikoTags;
	global $unspecified;

	$id		= xmlParser_getValue($xmlRequest, "id");

	if ($id == "")
		trigger_error ("חסר קוד מומחה לביצוע הפעולה");

	$sql 	= "select clubMembers.*, israeli_experts.*, pages_byLang.*
			   from clubMembers, israeli_experts, pages_byLang 
			   where id = $id and memberId = id and memberPageId = pageId";
	$result = commonDoQuery ($sql);
	$row	= commonQueryFetchRow($result);

	$row['birthDate'] 	= formatApplDate($row['birthDate']);
	$row['joinTime']  	= formatApplDate($row['joinTime']);
	$row['payFree'] 	= formatApplDate($row['payFree']);

	$xmlResponse = "<id>$id</id>";

	for ($i=0; $i < count($clubTags); $i++)
	{
		eval ("\$$clubTags[$i] = \$row['$clubTags[$i]'];");
		eval ("\$$clubTags[$i] = commonValidXml(\$$clubTags[$i]);");
		eval ("\$xmlResponse .= \"<$clubTags[$i]>\$$clubTags[$i]</$clubTags[$i]>\";");
	}

	for ($i=0; $i < count($tags); $i++)
	{
		eval ("\$$tags[$i] = \$row['$tags[$i]'];");
		eval ("\$$tags[$i] = commonValidXml(\$$tags[$i]);");
		eval ("\$xmlResponse .= \"<$tags[$i]>\$$tags[$i]</$tags[$i]>\";");
	}

	$row['sourceFile']	= commonValidXml(addslashes($row['sourceFile']));

	$xmlResponse .= "<joinTime>$row[joinTime]</joinTime>
					 <isReady>$row[isReady]</isReady>
					 <formFileSource>$row[sourceFile]</formFileSource>
					 <fileName>$row[picFile]</fileName>";

	switch ($row['status'])
	{
			case "new"			: $xmlResponse .= "<commonStatus>new</commonStatus>";	break;
			case "active"		: if ($row['extraData6'] >= "1")
										$xmlResponse .= "<commonStatus>promoted</commonStatus>";
								  else if ($row['hideContactDetails'] == "1")
										$xmlResponse .= "<commonStatus>hidden</commonStatus>";
								  else
								  		$xmlResponse .= "<commonStatus>active</commonStatus>";
								  break;
			case "disabled"		: $xmlResponse .= "<commonStatus>disabled</commonStatus>";	break;
	}

	$domainRow   = commonGetDomainRow ();
	$siteUrl     = commonGetDomainName($domainRow);
	
	$clickHere	 = "לחץ כאן";

	if ($row['picFile'] != "")
	{
		$xmlResponse	.= "<fileFullName>" . commonValidXml("$siteUrl/membersFiles/$row[picFile]") . "</fileFullName>
							<show>$clickHere</show>
							<delete>$clickHere</delete>";
	}
 
	// ------------------------------------------------------------------------------------------------

	// SEO tags
	$winTitle	= commonValidXml ($row['winTitle']);
	$keywords	= commonValidXml ($row['keywords']);
	$description= commonValidXml ($row['description']);
	$rewriteName= commonValidXml ($row['rewriteName']);
	$isReady	= $row['isReady'];

	$xmlResponse .= "<winTitle>$winTitle</winTitle>
					 <keywords>$keywords</keywords>
					 <description>$description</description>
					 <rewriteName>$rewriteName</rewriteName>
					 <status>$isReady</status>";

	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* addMember																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function addMember ($xmlRequest)
{
	return (editMember ($xmlRequest, "add"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateMember																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateMember ($xmlRequest)
{
	return (editMember ($xmlRequest, "update"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* editMember																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function editMember ($xmlRequest, $editType)
{
	global $tags, $clubTags;
	global $userId, $ibosHomeDir;

	$id			= xmlParser_getValue($xmlRequest, "id");

	for ($i=0; $i < count($clubTags); $i++)
	{
		eval ("\$$clubTags[$i] = commonDecode(addslashes(xmlParser_getValue(\$xmlRequest,\"$clubTags[$i]\")));");	
	}

	for ($i=0; $i < count($tags); $i++)
	{
		eval ("\$$tags[$i] = commonDecode(addslashes(xmlParser_getValue(\$xmlRequest,\"$tags[$i]\")));");	
	}

	$birthDate	= formatApplToDB($birthDate);
	$joinTime	= formatApplToDB($joinTime);
	$payFree	= formatApplToDB($payFree);

	// check that there is no such member username
	if ($username != "")
	{
		$sql	= "select * from clubMembers where username = '$username'";

		if ($editType == "update")
			$sql .= " and id != $id";

		$result		= commonDoQuery($sql);

		if (commonQueryNumRows($result) != 0)
		{
			trigger_error ("כבר קיים מומחה עם שם משתמש זהה");
		}
	}

	if ($editType == "add")
	{
		$sql		= "select max(id) from clubMembers";
		$result		= commonDoQuery($sql);
		$row		= commonQueryFetchRow($result);
		$id			= $row[0] + 1;

		$sql		= "select max(id) from pages";
		$result	    = commonDoQuery ($sql);
		$row	    = commonQueryFetchRow($result);
		$pageId	    = $row[0]+1;
	}

	if ($id == "")
		trigger_error ("חסר קוד מנוי לביצוע הפעולה");

	// handle file
	// ------------------------------------------------------------------------------------------------

	$sourceFile 	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "sourceFile")));	
	$fileDeleted	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "fileDeleted")));	
	
	$fileDeleted	= ($fileDeleted == "1");
	$fileLoaded  	= false;

	$file 			= "";
	$fileDB			= "";
	$suffix 		= "";

	if ($sourceFile != "")
	{
		$fileLoaded = true;
		
		$suffix		= commonFileSuffix ($sourceFile);

		$size0		= "${id}_size0$suffix";
		$size1		= "${id}_size1.jpg";
	}
	
	$commonStatus	= xmlParser_getValue($xmlRequest, "commonStatus");
	switch ($commonStatus)
	{
			case "new"			: $status = "new";		$isReady = 0; $extraData6 = "0";	$hideContactDetails = 0;	break;
			case "active"		: $status = "active";	$isReady = 1; $extraData6 = "0";	$hideContactDetails = 0;	break;
			case "promoted"		: $status = "active";	$isReady = 1; $extraData6 = "1";	$hideContactDetails = 0; 	break;
			case "hidden"		: $status = "active";	$isReady = 1; $extraData6 = "0";	$hideContactDetails = 1;	break;
			case "disabled"		: $status = "disabled";	$isReady = 0; $extraData6 = "0";	$hideContactDetails = 0;	break;
	}

	$clubVals = Array();
	for ($i=0; $i < count($clubTags); $i++)
	{
		eval ("array_push (\$clubVals,\$$clubTags[$i]);");
	}
	
	$vals = Array();
	for ($i=0; $i < count($tags); $i++)
	{
		eval ("array_push (\$vals,\$$tags[$i]);");
	}
	
	$extraData4		= xmlParser_getValue($xmlRequest, "extraData4");

	$memberLanguage	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "memberLanguage")));
	$title			= trim("$lastname $firstname");
	$winTitle		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "winTitle")));
	$keywords		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "keywords")));
	$metaDesc		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "description")));
	$rewriteName	= str_replace(" ", "-", addslashes(commonDecode(xmlParser_getValue($xmlRequest, "rewriteName"))));

	if ($editType == "add")
	{
		$vals = Array();
		for ($i=0; $i < count($clubTags); $i++)
			eval ("array_push (\$vals,\$$clubTags[$i]);");

		$code 		= randomCode(25);

		$sql	= "insert into clubMembers (id, verifyCode, " . join(",", $clubTags) . ", picFile, sourceFile, extraData4, memberLanguage) 
				   values ($id, '$code', '" . join("','",$vals) . "', '$size0', '$sourceFile', '$extraData4', '$memberLanguage')";
		commonDoQuery ($sql);

		$vals = Array();
		for ($i=0; $i < count($tags); $i++)
			eval ("array_push (\$vals,\$$tags[$i]);");

		$sql 	= "insert into israeli_experts (memberId, memberPageId, " . join(",",$tags) . ") values ($id, $pageId, '" . join("','",$vals) . "')";
		commonDoQuery ($sql);

		$sql 	= "insert into pages (id, type, typeText, layoutId, navParentId) values ($pageId, 'specific', 'expert', 4, 1)";
		commonDoQuery ($sql);

		$sql	= "insert into pages_byLang (pageId, language, title, winTitle, isReady, keywords, description, rewriteName)
				   values ('$pageId','$memberLanguage', '$title', '$winTitle', '$isReady', '$keywords', '$metaDesc', '$rewriteName')";
		commonDoQuery ($sql);
	}
	else
	{
		$sql 		= "select picFile, memberPageId from clubMembers, israeli_experts where id = memberId and id = $id";
		$result	  	= commonDoQuery($sql);
		$row	  	= commonQueryFetchRow($result);

		$oldFile	= $row['picFile'];
		$pageId		= $row['memberPageId'];

		$sql = "update clubMembers set extraData4 = '$extraData4', memberLanguage = '$memberLanguage', ";

		for ($i=0; $i < count($clubTags); $i++)
			$sql .= "$clubTags[$i] = '$clubVals[$i]',";

		$sql  = trim($sql, ",");

		if ($fileLoaded)
		{
			$sql .= ",	picFile	    = '$size0',
						sourceFile  = '$sourceFile' ";
		}
		else if ($fileDeleted)
		{
			$sql .= ",	picFile	    = '',
						sourceFile  = '' ";
		}

		$sql .= " where id = $id ";
		commonDoQuery ($sql);

		$sql = "update israeli_experts set ";

		for ($i=0; $i < count($tags); $i++)
			$sql .= "$tags[$i] = '$vals[$i]',";

		$sql  = trim($sql, ",");

		$sql .= " where memberId = $id ";
		commonDoQuery ($sql);

		$sql	= "update pages_byLang set title 		= '$title',
										   winTitle		= '$winTitle',
										   isReady		= '$isReady',
										   keywords		= '$keywords',
										   description	= '$metaDesc',
										   rewriteName	= '$rewriteName' 
					   where pageId = $pageId and language = '$memberLanguage'";
		commonDoQuery ($sql);
	}

	if ($extraData4 == "student")
	{
		$sql	= "select mailingList from israeli_courses where pageId = '$extraData3'";
		$result	= commonDoQuery($sql);
		$row	= commonQueryFetchRow($result);

		if ($row['mailingList'] != 0)
		{
			$mailingListId = $row['mailingList'];

			$sql	= "select * from clubMailingListsMembers where memberId = $id";
			$result	= commonDoQuery($sql);

			$currMailing	= 0;

			if (commonQueryNumRows($result) != 0)
			{
				$row			= commonQueryFetchRow($result);
				$currMailing	= $row['mailingListId'];
			}

			if ($currMailing != $mailingListId)
			{
				$sql	= "delete from clubMailingListsMembers where memberId = $id and mailingListId = $currMailing";
				commonDoQuery($sql);

				$sql	= "insert into clubMailingListsMembers (memberId, mailingListId, joinTime) values ($id, $mailingListId, now())";
				commonDoQuery($sql);
			}
		}
	}

	// handle file
	$filePath = "$ibosHomeDir/html/SWFUpload/files/$userId";

	if ($fileLoaded)
	{
		list ($width, $height, $bgColor) = commonGetDimensionDetails (2);

		include "$ibosHomeDir/php/picsTools.php";

		$domainRow	= commonGetDomainRow();
		$domainName = commonGetDomainName ($domainRow);

		$connId 	= commonFtpConnect($domainRow); 
		ftp_chdir ($connId, "membersFiles");

		$upload = ftp_put($connId, $size0, "$filePath/$sourceFile", FTP_BINARY);

		picsToolsResize("$filePath/$sourceFile", $suffix, $width, $height, "/../../tmp/$size1", $bgColor);
		$upload = ftp_put($connId, $size1, "/../../tmp/$size1", FTP_BINARY);

		commonFtpDisconnect ($connId);
	}
/*	else if ($fileDeleted)
	{
		$domainRow	= commonGetDomainRow();
		$domainName = commonGetDomainName ($domainRow);

		$connId 	= commonFtpConnect($domainRow); 
		ftp_chdir ($connId, "membersFiles");

		commonFtpDelete ($connId, $oldFile);

		$oldFile 	= str_replace("size0", "size1", $oldFile);
		$destParts 	= explode(".",$oldFile);
		$destParts[count($destParts)-1] = "jpg";
		$oldFile 	= join(".", $destParts);

		commonFtpDelete ($connId, $oldFile);

		commonFtpDisconnect ($connId);
 	}
 */

 	// delete old files
	commonDeleteOldFiles ($filePath, 3600);	// 1 hour

	return "<id>$id</id>";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* cancelMember																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function cancelMember ($xmlRequest)
{
	$id = xmlParser_getValue($xmlRequest, "id");

	if ($id == "")
		trigger_error ("חסר קוד לביצוע הפעולה");

	$sql	= "select memberPageId from israeli_experts where memberId = $id";
	$result	= commonDoQuery($sql);
	$row	= commonQueryFetchRow($result);
	$pageId	= $row['memberPageId'];

	$sql	= "update clubMembers set extraData4 = '', status = 'active' where id = $id";
	commonDoQuery ($sql);

	$sql	= "delete from israeli_experts where memberId = $id";
	commonDoQuery ($sql);

	// delete member page
	$sql	= "delete from pages where id = '$pageId' and type = 'specific' and typeText = 'expert'";
	commonDoQuery ($sql);

	$sql	= "delete from pages_byLang where pageId = '$pageId'";
	commonDoQuery ($sql);

	$sql	= "delete from sessions where memberId = $id";
	commonDoQuery($sql);

	$sql	= "delete from clubMailingListsMembers where memberId = $id";
	commonDoQuery($sql);

	$sql	= "insert into clubMailingListsMembers (memberId, mailingListId, joinTime) values ($id, 11, now())";
	commonDoQuery($sql);

	return "";	
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteMember																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteMember ($xmlRequest)
{
	$id = xmlParser_getValue($xmlRequest, "id");

	if ($id == "")
		trigger_error ("חסר קוד לביצוע הפעולה");

	$sql	= "select memberPageId from israeli_experts where memberId = $id";
	$result	= commonDoQuery($sql);
	$row	= commonQueryFetchRow($result);
	$pageId	= $row['memberPageId'];

	$sql	= "delete from clubMembers where id = $id";
	commonDoQuery ($sql);

	$sql	= "delete from israeli_experts where memberId = $id";
	commonDoQuery ($sql);

	// delete member page
	$sql	= "delete from pages where id = '$pageId' and type = 'specific' and typeText = 'expert'";
	commonDoQuery ($sql);

	$sql	= "delete from pages_byLang where pageId = '$pageId'";
	commonDoQuery ($sql);

	$sql	= "delete from sessions where memberId = $id";
	commonDoQuery($sql);

	$sql	= "delete from clubMailingListsMembers where memberId = $id";
	commonDoQuery($sql);

	return "";	
}

/* ----------------------------------------------------------------------------------------------------	*/
/* sendMemberPasswordEmail																				*/
/* ----------------------------------------------------------------------------------------------------	*/
function sendMemberPasswordEmail ($xmlRequest)
{
	$ids = xmlParser_getValues ($xmlRequest, "id");

	if (count($ids) == 0)
		trigger_error ("חסר קוד לביצוע הפעולה");

	foreach ($ids as $id)
	{
		$sql 		= "select clubMembers.*, israeli_experts.*, pages_byLang.*
					   from clubMembers, israeli_experts, pages_byLang 
					   where id = $id and memberId = id and memberPageId = pageId";
		$result 	= commonDoQuery ($sql);
		$row		= commonQueryFetchRow($result);

		$memberLang	= $row['memberLanguage'];

		$email		= $row['email'];
	
		if ($email == "")
			$email	= $row['fldPublicEmail'];

		if ($email == "")
			trigger_error ("למומחה אין כתובת מייל במערכת");

		$fullname	= trim("$row[fldExtentName] $row[lastname] $row[firstname]");
	
		$subject    = (($memberLang == "HEB") ? "המכון הישראלי לחוות דעת מומחים - סיסמתך באתר" : "Court expert - Your password");

		$boxName	= (strtotime($row['payFree']) >= strtotime("now") ? "sendPasswordEmailNoPay" : "sendPasswordEmail");

		$expireDate	= ($row['payFree'] ? substr($row['payFree'], 8, 2)."/".substr($row['payFree'], 5, 2)."/".substr($row['payFree'], 0, 4) : '');

		$content	= commonGetBoxContent ($boxName, $memberLang);

		$domain		= commonGetLayoutSwitchHtml ("domain", $memberLang);
		$html		= commonGetLayoutHtml (9, $memberLang);

		$content	= str_replace("#fullname#", 	$fullname, 			$content);
		$content	= str_replace("#username#", 	$row['username'], 	$content);
		$content	= str_replace("#password#",  	$row['password'],  	$content);
		$content	= str_replace("#expireDate#",  	$expireDate,  	$content);

		$html		= str_replace("#emailSubject#" ,	$subject, 	$html);
		$html		= str_replace("#emailContent#" ,	$content,	$html);
		$html		= str_replace("@domain@",			$domain,  	$html);

		$from		= (($memberLang == "HEB") ? "המכון הישראלי לחוות דעת מומחים" : "Court expert");

		//commonSendHtmlEmail ($from, "info@israeli-expert.co.il", $email, $subject, $html);
		commonSendHtmlEmailBySMTP("info1@israeli-expert.co.il", "v7ybpHyWxH", $from, "info@israeli-expert.co.il", $email, $fullname, $subject, $html, true);
	}
}

/* ----------------------------------------------------------------------------------------------------	*/
/* makeExpert																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function makeExpert ($xmlRequest)
{
	$id = xmlParser_getValue($xmlRequest, "id");

	if ($id == "")
		trigger_error ("חסר קוד לביצוע הפעולה");

	$sql			= "update clubMembers set status='active', extraData4 = 'expert' where id = $id";
	commonDoQuery($sql);

	$sql			= "select memberPageId, memberLanguage from israeli_experts where memberId = $id";
	$result			= commonDoQuery($sql);
	$row			= commonQueryFetchRow($result);
	$pageId			= $row['memberPageId'];
	$memberLanguage	= $row['memberLanguage'];

	$sql	= "update pages_byLang set isReady = 1 where pageId = $pageId and language = '$memberLanguage'";
	commonDoQuery ($sql);

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* makeStudent																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function makeStudent ($xmlRequest)
{
	$id 	= xmlParser_getValue($xmlRequest, "id");
	$course = xmlParser_getValue($xmlRequest, "course");

	if ($id == "" || $course == "")
		trigger_error ("חסר קוד לביצוע הפעולה");

	$sql	= "select mailingList from israeli_courses where pageId = $course";
	$result	= commonDoQuery($sql);
	$row	= commonQueryFetchRow($result);
	$listId	= $row['mailingList'];

	if ($listId != 0)
	{
		$sql = "insert into clubMailingListsMembers (memberId, mailingListId, joinTime) values ($id, $listId, now())";
		commonDoQuery ($sql);
	}
 
	$sql	= "update clubMembers set extraData3='$course', extraData4 = 'student' where id = $id";
//	mail ("amir@interuse.co.il", "commands_israeliExperts", $sql);
	
	commonDoQuery($sql);

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* excelReport																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function excelReport ($xmlRequest)
{
	global $ibosHomeDir;

	$now			= "תאריך הפקת הדוח:" . date("d/m/Y H:i");
	$reportTitle	= "דוח " . (($extraData4 == "student") ? "משתלמים" : "מומחים");

	$conditions  = "";

	$memberLanguage	 	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "memberLanguage")));
	if ($memberLanguage != "")
	{
		$conditions	.= " and israeli_experts.memberLanguage = '$memberLanguage'";
	}

	$extraData3	 	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "extraData3")));
	if ($extraData3 != "")
	{
		$conditions	.= " and clubMembers.extraData3 = '$extraData3'";
	}

	$extraData4	 	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "extraData4")));

	if ($extraData4 != "")
	{
		$conditions	.= " and clubMembers.extraData4 = 'student'";
	}
/*
	else
	{
		$conditions	.= " and clubMembers.extraData4 = 'expert' and clubMembers.status != 'new'";
	}*/

	$status 	 	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "status")));
	if ($status != "")
	{
		if ($status == "promoted")
			$conditions	.= " and clubMembers.extraData6 >= '1'";
		else if ($status == "hidden")
			$conditions .= " and israeli_experts.hideContactDetails = '1'";
		else if ($status == "active")
			$conditions	.= " and clubMembers.status = '$status' and clubMembers.extraData6 < '1' and israeli_experts.hideContactDetails != '1'";
		else				
			$conditions	.= " and clubMembers.status = '$status'";
	}

	$byText 	 	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "byText")));
	if ($byText != "")
		$conditions .= " and (clubMembers.firstname like '%$byText%' or
							  clubMembers.lastname  like '%$byText%' or
							  israeli_experts.fldProfession  like '%$byText%' or
							  israeli_experts.fldSpecialization  like '%$byText%' or
							  israeli_experts.fldAdditionalNotes  like '%$byText%' or
							  israeli_experts.fldPublicAdditionalNotes  like '%$byText%' or
							  israeli_experts.fldOrgenizations  like '%$byText%' or
							  israeli_experts.fldPublicAddress  like '%$byText%' or
							  israeli_experts.licenseNo  like '%$byText%' or
							  concat(clubMembers.firstname, concat(' ', clubMembers.lastname))  like '%$byText%' or
							  concat(clubMembers.lastname,  concat(' ', clubMembers.firstname)) like '%$byText%') ";

	$name 	 	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "name")));
	if ($name != "")
	{
		$nameText	= join("%%", explode(" ", $name));

		$conditions .= " and (clubMembers.firstname like '%$nameText%' or
							  clubMembers.lastname  like '%$nameText%' or
							  concat(clubMembers.firstname, concat(' ', clubMembers.lastname))  like '%$nameText%' or
							  concat(clubMembers.lastname,  concat(' ', clubMembers.firstname)) like '%$nameText%') ";
	}

	$dialZone 	 	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "dialZone")));
	if ($dialZone != "")
		$conditions	.= " and israeli_experts.fldDialZone = '$dialZone'";

	$sortBy		= xmlParser_getValue($xmlRequest,"sortBy");

	if ($sortBy == "")
		$sortBy = "clubMembers.id";

	$sortDir	= xmlParser_getValue($xmlRequest,"sortDir");
	if ($sortDir == "")
		$sortDir = "desc";

	$sql	= "select clubMembers.*, israeli_experts.*, concat(clubMembers.lastname, clubMembers.firstname) as name
			   from (clubMembers, israeli_experts)
			   where clubMembers.id = israeli_experts.memberId $conditions order by $sortBy $sortDir";
	$result	= commonDoQuery ($sql);

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
						<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"70\"/>
						<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"70\"/>
						<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"70\"/>
						<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"70\"/>
						<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"70\"/>
						<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"70\"/>
						<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"120\"/>
						<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"300\"/>
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
						<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"70\"/>
						<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"120\"/>
						<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"120\"/>
						<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"120\"/>
						<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"180\"/>
						<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"180\"/>
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
						<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"120\"/>
						<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"120\"/>
						<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"300\"/>
					<Row>
						<Cell ss:MergeAcross=\"60\" ss:StyleID=\"sTitle\">
							<Data ss:Type=\"String\">$reportTitle</Data>
						</Cell>
					</Row>
					<Row>
						<Cell ss:MergeAcross=\"60\" ss:StyleID=\"sReportDate\"><Data ss:Type=\"String\">$now</Data></Cell>
					</Row>
					<Row ss:Height=\"18\">
						<Cell ss:MergeAcross=\"8\" ss:StyleID=\"sSection\"><Data ss:Type=\"String\">פרטי זיהוי</Data></Cell>
						<Cell ss:MergeAcross=\"9\" ss:StyleID=\"sSection\"><Data ss:Type=\"String\">תכונות</Data></Cell>
						<Cell ss:MergeAcross=\"6\" ss:StyleID=\"sSection\"><Data ss:Type=\"String\">השכלה והתמחות</Data></Cell>
						<Cell ss:MergeAcross=\"8\" ss:StyleID=\"sSection\"><Data ss:Type=\"String\">פרטי התקשרות</Data></Cell>
						<Cell ss:MergeAcross=\"11\" ss:StyleID=\"sSection\"><Data ss:Type=\"String\">פרטי מקצוע ועיסוק</Data></Cell>
						<Cell ss:MergeAcross=\"11\" ss:StyleID=\"sSection\"><Data ss:Type=\"String\">לשימוש פנימי</Data></Cell>
						<Cell ss:StyleID=\"sSection\"><Data ss:Type=\"String\">תחומי עיסוק</Data></Cell>
					</Row>
					<Row ss:Height=\"35\">
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">קוד</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">שם משפחה</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">שם פרטי</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">שם משתמש</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">סיסמא</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">תאריך לידה</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">סטטוס</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">פטור מתשלום עד</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">התמחות ראשית</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">מומחה מוסמך המכון&#10;מסלול מורחב</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">מומחה מוסמך המכון&#10;מסלול מצומצם</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">מומחה חוץ</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">מגשר מוסמך מכון&#10;בפיקוח ועדת גדות</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">מגשר מוסמך המכון</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">נתמך בתצהיר</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">נתקבלו המלצות</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">עורך דין</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">שופט בדימוס</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">בורר</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">תואר</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">פירוט</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">חבר בארגון</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">התמחות מקצועית</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">ידיעת שפות</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">מידע נוסף</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">קורות חיים&#10;מקצועיים</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">אזור חיוג</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">נייד</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">טלפון</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">פקס</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">דוא\"ל</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">כתובת</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">כתובת&#10;למשלוח דואר</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">אתר אינטרנט</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">מידע נוסף</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">מקצוע</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">ותק</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">פרטי מידע&#10;מיקומי אחר</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">מספר רישיון</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">עיסוק נוכחי&#10;והגדרת תפקיד</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">ותק בעיסוק נוכחי</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">שם המוסד&#10;בו מועסק</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">הכנת חוות דעת</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">נושאים חוות דעת</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">הופעת כעד מומחה</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">באילו משפטים הופעת</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">הסבר בקשה&#10;להוספת קטגוריה</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">טלפון 1</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">טלפון 2</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">נייד</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">פקס</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">רחוב</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">מספר בית</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">יישוב</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">מיקוד</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">אימייל</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">מדינה</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">הצטרפות</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">נתן תעודת הוקרה?</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">תחומי עיסוק</Data></Cell>
					</Row>";

//						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">הערות</Data></Cell>

	$excelHd		= iconv("UTF-8", "Windows-1255", $excelHd);

	$excelFt .= 	 "</Table>
				</Worksheet>
			</Workbook>";

	$files = array();

	// ------------------------------------------------------------------------------------------------
	
	while ($row = commonQueryFetchRow($result))
	{
		$id   					= $row['id'];
		$lastname				= commonPrepareToFile($row['lastname']);
		$firstname				= commonPrepareToFile($row['firstname']);
		$username				= commonPrepareToFile($row['username']);
		$password				= commonPrepareToFile($row['password']);
		
		$status					= $row['status'];

		switch ($status)
		{
			case "new"			: $status	= "חדש";		break;
			case "active"		: $status	= "פעיל";		break;
			case "disabled"		: $status 	= "לא פעיל";	break;
		}
		$status					= commonPrepareToFile($status);

		$birthDate 				= formatApplDate($row['birthDate']);
		$payFree 				= formatApplDate($row['payFree']);
		$fldSpecialization		= commonPrepareToFile($row['fldSpecialization']);

		$fldGraduatedLevelAdv	= commonPrepareToFile(($row['fldGraduatedLevelAdv']   == 0) ? "לא" : "כן");
		$fldGraduatedLevel		= commonPrepareToFile(($row['fldGraduatedLevel'] 	  == 0) ? "לא" : "כן");
		$fldExternalGraduated	= commonPrepareToFile(($row['fldExternalGraduated']   == 0) ? "לא" : "כן");
		$fldMegasherHutzh		= commonPrepareToFile(($row['fldMegasherHutzh'] 	  == 0) ? "לא" : "כן");
		$fldMegasherMusmahMahon	= commonPrepareToFile(($row['fldMegasherMusmahMahon'] == 0) ? "לא" : "כן");
		$fldDeclaration			= commonPrepareToFile(($row['fldDeclaration'] 		  == 0) ? "לא" : "כן");
		$fldHamlathot			= commonPrepareToFile(($row['fldHamlathot'] 		  == 0) ? "לא" : "כן");
		$fldLoyer				= commonPrepareToFile(($row['fldLoyer'] 			  == 0) ? "לא" : "כן");
		$fldJudge				= commonPrepareToFile(($row['fldJudge'] 			  == 0) ? "לא" : "כן");
		$fldBorer				= commonPrepareToFile(($row['fldBorer'] 			  == 0) ? "לא" : "כן");

		$fldQualifications		= commonPrepareToFile(strip_tags($row['fldQualifications']));
		$additionalNotes		= commonPrepareToFile(strip_tags($row['fldQualificationsAdditionalNotes']));
		$fldOrgenizations		= commonPrepareToFile(strip_tags($row['fldOrgenizations']));
		$fldAdditionalNotes		= commonPrepareToFile(strip_tags($row['fldAdditionalNotes']));
		$langs					= commonPrepareToFile(strip_tags($row['langs']));
		$moreSchoolDetails		= commonPrepareToFile(strip_tags($row['moreSchoolDetails']));
		$publicNotes			= commonPrepareToFile(strip_tags($row['fldPublicAdditionalNotes']));

		$fldDialZone			= commonPrepareToFile($row['fldDialZone']);
		$fldPublicMobil			= commonPrepareToFile($row['fldPublicMobil']);
		$fldPublicPhone			= commonPrepareToFile($row['fldPublicPhone']);
		$fldPublicFax			= commonPrepareToFile($row['fldPublicFax']);
		$fldPublicEmail			= commonPrepareToFile($row['fldPublicEmail']);
		$fldPublicAddress		= commonPrepareToFile($row['fldPublicAddress']);
		$mailAddress			= commonPrepareToFile($row['mailAddress']);
		$mySite					= commonPrepareToFile($row['mySite']);
		$moreDetails			= commonPrepareToFile($row['moreDetails']);

		$fldProfession			= commonPrepareToFile($row['fldProfession']);
		$fldGeneralLongevity	= commonPrepareToFile($row['fldGeneralLongevity']);
		$extraDetails			= commonPrepareToFile($row['extraDetails']);
		$licenseNo				= commonPrepareToFile($row['licenseNo']);
		$currBiz				= commonPrepareToFile($row['currBiz']);
		$fldLongevity			= commonPrepareToFile($row['fldLongevity']);
		$workplace				= commonPrepareToFile($row['workplace']);
		$experience1			= commonPrepareToFile($row['experience1']);
		$experience2			= commonPrepareToFile($row['experience2']);
		$experience3			= commonPrepareToFile($row['experience3']);
		$experience4			= commonPrepareToFile($row['experience4']);
		$catsExtraDetails		= commonPrepareToFile($row['catsExtraDetails']);

		$phone					= commonPrepareToFile($row['phone']);
		$phone2					= commonPrepareToFile($row['phone2']);
		$cellphone				= commonPrepareToFile($row['cellphone']);
		$fax					= commonPrepareToFile($row['fax']);
		$address				= commonPrepareToFile($row['address']);
		$streetNo				= commonPrepareToFile($row['streetNo']);
		$city					= commonPrepareToFile($row['city']);
		$zipcode				= commonPrepareToFile($row['zipcode']);
		$email					= commonPrepareToFile($row['email']);
		$country				= commonPrepareToFile($row['country']);
		$joinTime				= formatApplDate($row['joinTime']);
		$thanksLetter			= commonPrepareToFile(($row['thanksLetter'] == 0) ? "לא" : "כן");
//		$fldAdminRemarks		= commonPrepareToFile($row['fldAdminRemarks']);

		$sql					= "select name from categoriesItems
								   left join categories_byLang on categoriesItems.categoryId = categories_byLang.categoryId and language = 'HEB'
								   where itemId = $id and type = 'specific' order by pos asc";
		$inResult				= commonDoQuery($sql);

		$cats					= array();
		while ($inRow = commonQueryFetchRow($inResult))
		{
			array_push ($cats, $inRow['name']);
		}

		$cats					= commonPrepareToFile(join(" | ", $cats));

		$excel .= "<Row ss:Height=\"13.5\">
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"Number\">$id</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$lastname</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$firstname</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$username</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$password</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$birthDate</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$status</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$payFree</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$fldSpecialization</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$fldGraduatedLevelAdv</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$fldGraduatedLevel</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$fldExternalGraduated</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$fldMegasherHutzh</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$fldMegasherMusmahMahon</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$fldDeclaration</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$fldHamlathot</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$fldLoyer</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$fldJudge</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$fldBorer</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$fldQualifications</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$additionalNotes</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$fldOrgenizations</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$fldAdditionalNotes</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$langs</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$moreSchoolDetails</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$publicNotes</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$fldDialZone</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$fldPublicMobil</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$fldPublicPhone</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$fldPublicFax</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$fldPublicEmail</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$fldPublicAddress</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$mailAddress</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$mySite</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$moreDetails</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$fldProfession</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$fldGeneralLongevity</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$extraDetails</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$licenseNo</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$currBiz</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$fldLongevity</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$workplace</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$experience1</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$experience2</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$experience3</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$experience4</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$catsExtraDetails</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$phone</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$phone2</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$cellphone</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$fax</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$address</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$streetNo</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$city</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$zipcode</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$email</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$country</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$joinTime</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$thanksLetter</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$cats</Data></Cell>
					</Row>";

//						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$fldAdminRemarks</Data></Cell>
		/*
		if (strlen($excel) > 10000000) // 10MB
		{
				$xmlResponse = commonDoExcel ($excelHd.$excel.$excelFt);

				// my xml parser
				$start		= strpos($xmlResponse, '>')+1;
				$end 		= strrpos($xmlResponse, '<');
				$fileName	= substr($xmlResponse, $start, $end - $start);

				array_push($files, $fileName);

				$excel = "";
		}*/
	}

	$xmlResponse = commonDoExcel ($excelHd.$excel.$excelFt);

	/*
	// my xml parser
	$start		= strpos($xmlResponse, '>')+1;
	$end 		= strrpos($xmlResponse, '<');
	$fileName	= substr($xmlResponse, $start, $end - $start);

	array_push($files, $fileName);

	$zipFileName = time().".zip";
	$zip = new ZipArchive;
	$zip->open("$ibosHomeDir/tempExcels/".$zipFileName, ZipArchive::CREATE);
	foreach ($files as $file) {
			$zip->addFromString($file,  file_get_contents("$ibosHomeDir/tempExcels/".$file)); 
	}
	$zip->close();

	return ("<excelFileName>$zipFileName</excelFileName>");
	 */
	
	return ($xmlResponse);
}
?>
