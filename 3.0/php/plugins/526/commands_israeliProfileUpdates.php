<?php

/* ----------------------------------------------------------------------------------------------------	*/
/* getProfileUpdates																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function getProfileUpdates ($xmlRequest)
{	
	$domainRow   = commonGetDomainRow ();
	$siteUrl     = commonGetDomainName($domainRow);
	
	commonConnectToUserDB ($domainRow);

	$conditions = "";

	$sortBy		= xmlParser_getValue($xmlRequest,"sortBy");

	if ($sortBy == "" || $sortBy == "id")
		$sortBy = "israeli_profileUpdates.id";

	$sortDir	= xmlParser_getValue($xmlRequest,"sortDir");
	if ($sortDir == "")
		$sortDir = "asc";

	if ($sortBy == "name")
		$sortBy	= "clubMembers.lastname $sortDir, clubMembers.firstname";

	$onlyNew 		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "isNew")));
	$isNewForCourse = addslashes(commonDecode(xmlParser_getValue($xmlRequest, "isNewForCourse")));

	if ($onlyNew != "")
			$conditions	.= " and israeli_profileUpdates.isNew = $onlyNew and extraData3 = '' and clubMembers.status = 'new'";
	else if ($isNewForCourse == 1)
			$conditions	.= " and israeli_profileUpdates.isNew = 1 and extraData3 != '' and extraData4 != 'student'";
	else
			$conditions	.= " and israeli_profileUpdates.isNew = 0";

	$memberId 	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "memberId")));
	if ($memberId != "")
		$conditions	.= " and israeli_profileUpdates.memberId = $memberId ";
	else
		$conditions	.= " and israeli_profileUpdates.isDeleted = 0 ";

	$name 	 	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "name")));
	if ($name != "")
	{
		$nameText	= join("%%", explode(" ", $name));

		$conditions .= " and (clubMembers.firstname like '%$nameText%' or
							  clubMembers.lastname  like '%$nameText%' or
							  concat(clubMembers.firstname, concat(' ', clubMembers.lastname))  like '%$nameText%' or
							  concat(clubMembers.lastname,  concat(' ', clubMembers.firstname)) like '%$nameText%') ";
	}

	// get total
	$sql    	= "select clubMembers.*, clubMembers.id as memberId, israeli_experts.*, israeli_profileUpdates.* 
		   			from (israeli_profileUpdates, clubMembers, israeli_experts) 
					where israeli_profileUpdates.memberId = clubMembers.id
					and   israeli_profileUpdates.memberId = israeli_experts.memberId
					$conditions order by $sortBy $sortDir";
	//mail("amir@interuse.co.il", "commands_israeliProfileUpdates", $sql);
	$result		= commonDoQuery ($sql);
	$total		= commonQuery_numRows($result);

	// get details
	$sql       .= commonGetLimit ($xmlRequest);
	$result	    = commonDoQuery  ($sql);
	$numRows	= commonQuery_numRows ($result);

	$xmlResponse = "<items>";

	while ($row = commonQuery_fetchRow($result))
	{
		$id			= $row['id'];
		$name		= trim("$row[lastname] $row[firstname]");

		$memberId	= $row['memberId'];
		$extraData4	= $row['extraData4'];

		if ($extraData4 == "expert" && $row['status'] == 'active' && $onlyNew != 1) $name .= " <span class='gray'>(מומחה)</span>";
		if ($extraData4 == "student") $name .= " <span class='gray'>(משתלם)</span>";

		$name		= commonValidXml ($name);

		$insertTime	= formatApplDateTime ($row['insertTime']);
		$isNew		= (($row['isNew'] == 1) ? "&#9745;" : "");
		$isDeleted	= (($row['isDeleted'] == 1) ? "&#9745;" : "");

		$pageLink		= $row['memberPageId'];
		$pageLink		= "<a href='$siteUrl/index2.php?id=$pageLink&lang=HEB' target='_blank' title='צפייה בכרטיס'>$pageLink</a>";
		if ($row['email'])
			$pageLink	.= "&nbsp;&nbsp;&nbsp;<a href='mailto:$row[email]' title='שלח מייל'>@</a>";
		else if ($row['fldPublicEmail'])
			$pageLink	.= "&nbsp;&nbsp;&nbsp;<a href='mailto:$row[fldPublicEmail]' title='שלח מייל'>@</a>";
		$pageLink		= commonValidXml($pageLink);

		$xmlResponse .=	"<item>
							<id>$id</id>
							<name>$name</name>
							<insertTime>$insertTime</insertTime>
							<isNew>$isNew</isNew>
							<isDeleted>$isDeleted</isDeleted>
							<pageLink>$pageLink</pageLink>
							<extraData4>$extraData4</extraData4>
							<memberId>$memberId</memberId>
						 </item>";
	}

	$xmlResponse .=	"</items>"								.
					commonGetTotalXml($xmlRequest,$numRows,$total);
	
	return ($xmlResponse);
}

$confirmBtnHtml = "<div class='btn' onclick='confirmUpdateProfile(\"#sqlCode#\");this.parentNode.remove();'>
						<div class='btnText'>#btnText#</div>
				   </div>";

$rejectBtnHtml  = "<div class='btn' onclick='rejectUpdateProfile(#id#, \"#dbField#\");this.parentNode.remove();' style='width:50px'>
						<div class='btnText'>#btnText#</div>
				   </div>";

/* ----------------------------------------------------------------------------------------------------	*/
/* getProfileDetails																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function getProfileDetails ($xmlRequest)
{
	global $confirmBtnHtml, $rejectBtnHtml;

	$id			= xmlParser_getValue($xmlRequest,"id");

	$sql		= "select israeli_profileUpdates.*, firstname, lastname 
				   from   israeli_profileUpdates, clubMembers
			 	   where clubMembers.id = memberId and israeli_profileUpdates.id = $id";
	$result		= commonDoQuery($sql);
	$updateRow	= commonQuery_fetchRow($result);

	if (substr($updateRow['posted'],0, 5) != 'array')
	{
		$postArray = unserialize(base64_decode($updateRow['posted']));
	}
	else
	{
		eval("\$postArray = $updateRow[posted];");						
	}

	$sql 		= "select * from clubMembers, israeli_experts where id = memberId and id = $updateRow[memberId]";
	$result 	= commonDoQuery($sql);
	$expertRow 	= commonQuery_fetchRow($result);

	if ($postArray['accessDisabled'] == "") $postArray['accessDisabled'] = 0;
	if ($postArray['accessElevator'] == "") $postArray['accessElevator'] = 0;

	$details	= "<table class='headers'>
				   <tr>
						<th width='150'><div>סוג הנתון</div></th>
						<th width='325'><div>נתון נוכחי</div></th>
						<th width='325'><div>נתון חדש</div></th>
						<th width='120'><div>פעולות</div></th>
					</tr>
					</table>
					<div class='profileData'>
					<table>" .
					checking($updateRow, $expertRow, $postArray, "firstname",							"clubMembers", 		"שם פרטי") 				  	.
					checking($updateRow, $expertRow, $postArray, "lastname",							"clubMembers", 		"שם משפחה") 			  	.
					checking($updateRow, $expertRow, $postArray, "gender",								"clubMembers", 		"מגדר") 				  	.
					checking($updateRow, $expertRow, $postArray, "fldExtentName",						"israeli_experts", 	"תואר לפני השם") 		  	.
					checking($updateRow, $expertRow, $postArray, "birthDate", 							"clubMembers", 		"תאריך לידה") 			  	.
					checking($updateRow, $expertRow, $postArray, "email", 								"clubMembers", 		"דוא\"ל") 				  	.
					checking($updateRow, $expertRow, $postArray, "mySite", 								"clubMembers", 		"אתר אינטרנט") 			  	.
					checking($updateRow, $expertRow, $postArray, "fldProfession", 						"israeli_experts", 	"מקצוע") 				  	.
					checking($updateRow, $expertRow, $postArray, "fldSpecialization",					"israeli_experts",	"תחום התמחות מקצועית")	  	.
					checking($updateRow, $expertRow, $postArray, "fldGeneralLongevity",					"israeli_experts",	"מספר שנות עבודה במקצוע") 	.
					checking($updateRow, $expertRow, $postArray, "fldLongevity",						"israeli_experts",	"מספר שנות ותק")		  	.
					checking($updateRow, $expertRow, $postArray, "licenseNo",							"israeli_experts",	"מספר רשיון")			  	.
					checking($updateRow, $expertRow, $postArray, "fldQualifications",					"israeli_experts",	"תואר")					  	.
					checking($updateRow, $expertRow, $postArray, "fldQualificationsAdditionalNotes",	"israeli_experts",	"פירוט תארים והשכלה")	  	.
					checking($updateRow, $expertRow, $postArray, "fldStructuredQualifications",			"israeli_experts",	"השכלה")				  	.
					checking($updateRow, $expertRow, $postArray, "langs",								"israeli_experts",	"ידיעת שפות")			  	.
					checking($updateRow, $expertRow, $postArray, "address",								"clubMembers",		"כתובת")				  	.
					checking($updateRow, $expertRow, $postArray, "cellphone",							"clubMembers",		"מס' טלפון סלולרי")		  	.
					checking($updateRow, $expertRow, $postArray, "phone",								"clubMembers",		"מס' טלפון בבית")		  	.
					checking($updateRow, $expertRow, $postArray, "fax",									"clubMembers",		"פקס בבית")	 			  	.
					checking($updateRow, $expertRow, $postArray, "fldPublicAddress",					"israeli_experts",	"כתובת פומבית")	    	  	.
					checking($updateRow, $expertRow, $postArray, "fldPublicMobil",						"israeli_experts",	"מס' טלפון סלולרי פומבי") 	.
					checking($updateRow, $expertRow, $postArray, "fldPublicPhone",						"israeli_experts",	"מס' טלפון בבית פומבי")	  	.
					checking($updateRow, $expertRow, $postArray, "fldPublicFax",						"israeli_experts",	"פקס בבית פומבי")		  	.
					checking($updateRow, $expertRow, $postArray, "fldPublicEmail",						"israeli_experts",	"דוא\"ל פומבי")			  	.
					checking($updateRow, $expertRow, $postArray, "fldDialZone",							"israeli_experts",	"אזור חיוג")			  	.
					checking($updateRow, $expertRow, $postArray, "linkedinPage",						"israeli_experts",	"כתובת דף לינקדין")		  	.
					checking($updateRow, $expertRow, $postArray, "skype",								"israeli_experts",	"שם ב-Skype")			  	.
					checking($updateRow, $expertRow, $postArray, "facebookPage",						"israeli_experts",	"כתובת דף פייסבוק")		  	.
					checking($updateRow, $expertRow, $postArray, "twitterPage",							"israeli_experts",	"כתובת ב-טוויטר")		  	.
					checking($updateRow, $expertRow, $postArray, "workplace",							"israeli_experts", 	"שם מקום העבודה")		  	.
					checking($updateRow, $expertRow, $postArray, "workphone",							"israeli_experts", 	"טלפון מקום העבודה")	  	.
					checking($updateRow, $expertRow, $postArray, "workfax",								"israeli_experts", 	"פקס מקום העבודה")		  	.
					checking($updateRow, $expertRow, $postArray, "workweb",								"israeli_experts", 	"כתובת אתר מקום העבודה")  	.
					checking($updateRow, $expertRow, $postArray, "workaddress",							"israeli_experts", 	"כתובת מקום העבודה")		.
					checking($updateRow, $expertRow, $postArray, "workaddress1",						"israeli_experts", 	"סניף נוסף של מקום העבודה") .
					checking($updateRow, $expertRow, $postArray, "workaddress2",						"israeli_experts", 	"סניף נוסף של מקום העבודה")	.
					checking($updateRow, $expertRow, $postArray, "accessBuses",							"israeli_experts",	"מספרי קווי אוטובוס")		.
					checking($updateRow, $expertRow, $postArray, "accessTrain",							"israeli_experts",	"תחנת רכבת קרובה")			.
					checking($updateRow, $expertRow, $postArray, "accessPark",							"israeli_experts",	"חניון קרוב")				.
					checking($updateRow, $expertRow, $postArray, "accessCoffee",						"israeli_experts",	"בית קפה קרוב")				.
					checking($updateRow, $expertRow, $postArray, "accessDisabled",						"israeli_experts",	"האם הבניין מונגש לנכים?")	.
					checking($updateRow, $expertRow, $postArray, "accessElevator",						"israeli_experts",	"האם יש מעלית בבניין?")		.
					checking($updateRow, $expertRow, $postArray, "fldOrgenizations",					"israeli_experts",	"חברות בארגונים")			.
					checking($updateRow, $expertRow, $postArray, "fldPublicAdditionalNotes",			"israeli_experts",	"קורות חיים מקצועיים")		.
					checking($updateRow, $expertRow, $postArray, "picFile",								"clubMembers",		"תמונה");

	$rejected		= trim($updateRow['rejected'], ",");
	$rejectedArray 	= explode(',', $rejected);

	// categories change
	$existingCatsArray	= array();
	$newCats			= trim(commonQuery_escapeStr($postArray['catIds']), ",");
	
	if ($newCats)
	{
		$newCatsArray	= explode(',', $newCats);
	}
	else
	{
		$newCatsArray	= array();
		$newCats		= "0";
	}

	$sql			= "select categoriesItems.categoryId, name, pos from categoriesItems
					   left join categories_byLang on categoriesItems.categoryId = categories_byLang.categoryId and language = 'HEB'
					   where itemId = $expertRow[id] and type = 'specific'";
	$result = commonDoQuery($sql);

	while ($row = commonQuery_fetchRow($result))
	{
		if (in_array($row['categoryId'], $newCatsArray) || in_array("cat$row[categoryId]", $rejectedArray))
		{
			$existingCats .= "<div style='clear:both'>" . getCategoryPath($row['categoryId']). "</div>
						  <div style='clear:both; padding-top:10px; margin-bottom:10px; border-bottom:1px solid;'></div>";
		}
		else
		{
			$sqlCode = "update categoriesItems set pos = pos - 1 where itemId = $expertRow[id] and type = 'specific' and pos > $row[pos];" .
					   "delete from categoriesItems where itemId = $expertRow[id] and categoryId = $row[categoryId] and type = 'specific'";
			$sqlCode = base64_encode ($sqlCode);
			
			$confirmBtn = $confirmBtnHtml;
			$confirmBtn = str_replace("#sqlCode#",  $sqlCode, 		$confirmBtn);
			$confirmBtn = str_replace("#btnText#",  "אישור הפחתה", 		$confirmBtn);

			$rejectBtn	= $rejectBtnHtml;
			$rejectBtn 	= str_replace("#id#",	    $updateRow['id'], 		$rejectBtn);
			$rejectBtn 	= str_replace("#dbField#",	"cat$row[categoryId]",	$rejectBtn);
			$rejectBtn 	= str_replace("#btnText#",  "דחייה", 				$rejectBtn);

			$existingCats .= "<div style='color:red;clear:both'>" . getCategoryPath($row['categoryId']) . "</div>
							  <div class='rowOfButtons'>$confirmBtn$rejectBtn</div>
						  <div style='clear:both; padding-top:10px; margin-bottom:10px; border-bottom:1px solid;'></div>"; 
		}
	
		array_push($existingCatsArray, $row['categoryId']);
	}

	$sql	= "select categories_byLang.categoryId, name from categories
			   left join categories_byLang on categories_byLang.categoryId = categories.id and language = 'HEB'
			   where id in ($newCats) and type = 'specific'";
	$result = commonDoQuery($sql);

	while ($row = commonQuery_fetchRow($result))
	{
		if (in_array($row['categoryId'], $existingCatsArray) || in_array("cat$row[categoryId]", $rejectedArray))
		{
			$nextCats .= '<div style="clear:both">'.getCategoryPath($row['categoryId'])."</div>
						  <div style='clear:both; padding-top:10px; margin-bottom:10px; border-bottom:1px solid;'></div>";
		}
		else
		{
			$sqlCode = "replace into categoriesItems (itemId, categoryId, type, pos)
						select $expertRow[id], $row[categoryId], 'specific', IFNULL(max(pos)+1, 1) from categoriesItems
						where itemId = $expertRow[id] and type = 'specific'";
			$sqlCode = base64_encode ($sqlCode);
	
			$confirmBtn = $confirmBtnHtml;
			$confirmBtn = str_replace("#sqlCode#",  $sqlCode, 		$confirmBtn);
			$confirmBtn = str_replace("#btnText#",  "אישור הוספה", 		$confirmBtn);

			$rejectBtn	= $rejectBtnHtml;
			$rejectBtn 	= str_replace("#id#",	    $updateRow['id'], 		$rejectBtn);
			$rejectBtn 	= str_replace("#dbField#",	"cat$row[categoryId]",	$rejectBtn);
			$rejectBtn 	= str_replace("#btnText#",  "דחייה", 				$rejectBtn);

			$nextCats .= "<div style='color:red;clear:both'>". getCategoryPath($row['categoryId']) . "</div>
						  <div class='rowOfButtons'>$confirmBtn$rejectBtn</div>
						  <div style='clear:both; padding-top:10px; margin-bottom:10px; border-bottom:1px solid;'></div>";
		}
	}

	$details	.= "<tr>
						<td><div>קטגוריות</div></td>
						<td><div>$existingCats</div></td>
						<td><div>$nextCats</div></td>
						<td><div>";

	sort($existingCatsArray);
	sort($newCatsArray);
	if ($existingCatsArray != $newCatsArray)
	{
		$sqlCode = "delete from categoriesItems where itemId = $expertRow[id] and type = 'specific';";
		$pos = 1;

		foreach (explode(",", $newCats) as $c)
		{
			$sqlCode .= "insert into categoriesItems set itemId = $expertRow[id], categoryId = $c, type = 'specific', pos = ".($pos++).";";
		}

		$sqlCode = base64_encode (trim($sqlCode, ';'));

		$confirmBtn = "<div class='rowOfButtons'>$confirmBtnHtml</div>";
		$confirmBtn = str_replace("#sqlCode#",  $sqlCode, 		$confirmBtn);
		$confirmBtn = str_replace("#btnText#",  "אשר הכל", 		$confirmBtn);

		$details	.= $confirmBtn;
	}

	$details	.= "</div></td></tr>
					</table>
					</div>";

	$details	= commonValidXml($details);

	return "<details>$details</details>";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* checking																								*/
/* ----------------------------------------------------------------------------------------------------	*/
function checking ($updateRow, $expertRow, $postArray, $dbField, $dbTable, $title)
{
	global $confirmBtnHtml, $rejectBtnHtml;

	if ($dbField == "picFile")
	{
		$safeData = $updateRow['picFile'];
	}
	else
	{
		$safeData	= $postArray[$dbField];
	}

	$rejected		= trim($updateRow['rejected'], ",");
	$rejectedArray 	= explode(',', $rejected);

	if ($safeData == "on")
			$safeData = 1;

	$isDiff		= (preg_replace('/\s+/', ' ', $safeData) != str_replace('&#39;', "'", preg_replace('/\s+/', ' ', $expertRow[$dbField])));

	if ($isDiff)
	{
		$safeData = str_replace("'", "&#39;", $safeData);
		$sqlCode = "update $dbTable set $dbField = '$safeData' where ";

		if ($dbTable == "clubMembers")
			$sqlCode .= "id = $expertRow[id]";
		else
			$sqlCode .= "memberId = $expertRow[id]";
	}

	$sqlCode = base64_encode ($sqlCode);

	if ($dbField == "picFile")
	{
		if ($safeData)
		{
			$safeData = "<img src='https://www.israeli-expert.co.il/membersFiles/".str_replace("size0", "size1", $safeData)."' height='80'>";
			$expertRow[$dbField]= "<img src='https://www.israeli-expert.co.il/membersFiles/$expertRow[memberId]_size1.jpg' height='80'>";
		}
		else
		{
			$safeData = "";
			$expertRow[$dbField]= "<img src='https://www.israeli-expert.co.il/membersFiles/$expertRow[memberId]_size1.jpg' height='80'>";
			$isDiff = false;
		}
	}
	
	if ($dbField == "gender")
	{
			$safeData				= ($safeData == 'm' ? "זכר" : ($safeData == 'f' ? "נקבה" : ""));
			$expertRow[$dbField]	= ($expertRow[$dbField] == 'm' ? "זכר" : ($expertRow[$dbField] == 'f' ? "נקבה" : ""));
			if ($safeData == "")
					$isDiff = false;
	}

	$confirmBtn	= "";

	if ($isDiff && !in_array($dbField, $rejectedArray))
	{
		$confirmBtn	= $confirmBtnHtml;
		$confirmBtn = str_replace("#sqlCode#",  $sqlCode, 			$confirmBtn);
		$confirmBtn = str_replace("#btnText#",  "אישור", 			$confirmBtn);

		$rejectBtn	= $rejectBtnHtml;
		$rejectBtn 	= str_replace("#id#",	    $updateRow['id'], 	$rejectBtn);
		$rejectBtn 	= str_replace("#dbField#",	$dbField,	    	$rejectBtn);
		$rejectBtn 	= str_replace("#btnText#",  "דחייה", 			$rejectBtn);

		$btns	= "<div class='rowOfButtons'>$confirmBtn$rejectBtn</div>";

	}

	if ($dbField == "accessElevator" || $dbField == "accessDisabled")
	{
			$safeData				= ($safeData == 1 ? "כן" : "לא");
			$expertRow[$dbField]	= ($expertRow[$dbField] == 1 ? "כן" : "לא");
	}

	return "<tr>
				<td width='150'><div>$title</div></td>
				<td width='325'><div>" . $expertRow[$dbField]. "</div></td>
				<td width='325' " . ($isDiff ? " style='color:red'" : "") . "><div>$safeData</div></td>
				<td width='120'><div>$btns</div></td>
			</tr>";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getCategoryPath																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getCategoryPath ($catId)
{
	$sql	= "select parentId, name from categories
			   left join categories_byLang on id = categoryId and language = 'HEB'
			   where type = 'specific' and id = '$catId'";
	$result = commonDoQuery($sql);
	$row	= commonQuery_fetchRow($result);

	return ($row['parentId'] ? getCategoryPath($row['parentId']) . " &#10095; " : "") . $row['name'];
}

/* ----------------------------------------------------------------------------------------------------	*/
/* confirmUpdateProfile																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function confirmUpdateProfile ($xmlRequest)
{
	$sqlCode		= commonQuery_escapeStr(xmlParser_getValue($xmlRequest,"sqlCode"));

	if ($sqlCode != "")
	{
		$decodedSql = base64_decode($sqlCode);

		//mail ("amir@interuse.co.il", "commands_israeliProfileUpdates", $decodedSql);

		if (strpos($decodedSql, 'categoriesItems'))
		{
			foreach(explode(";", $decodedSql) as $aSql)
			{
				commonDoQuery($aSql);
			}
		}
		else
		{
			commonDoQuery($decodedSql);
		}

		if (strpos($decodedSql, 'picFile'))
		{
				$start = strpos($decodedSql, "'")+1;
				$end = strrpos($decodedSql, "'");
				$pic = substr($decodedSql, $start, $end-$start);

				$start = strpos($pic, "_");
				$end = strrpos($pic, "_")+1;
				$id = substr($pic, $start, $end-$start);

				$start = strrpos($decodedSql, "= ")+2;
				$memberId = substr($decodedSql, $start);

				$domainRow	= commonGetDomainRow();
				$domainName = commonGetDomainName ($domainRow);

				$connId 	= commonFtpConnect($domainRow); 

				ftp_chdir ($connId, "membersFiles");

				ftp_get	($connId, "/tmp/$pic", $pic, FTP_BINARY);
				ftp_put ($connId, str_replace("${id}", "_", "$pic"), "/tmp/".$pic, FTP_BINARY);
				unlink	("/tmp/$pic");
				ftp_get	($connId, "/tmp/${memberId}${id}size1.jpg", "${memberId}${id}size1.jpg", FTP_BINARY);
				ftp_put ($connId, "${memberId}_size1.jpg", "/tmp/"."${memberId}${id}size1.jpg", FTP_BINARY);
				unlink	("/tmp/${memberId}${id}size1.jpg");

//				ftp_rename ($connId, "$pic", 						str_replace("${id}", "_", "$pic"));
//				ftp_rename ($connId, "${memberId}${id}size1.jpg", "${memberId}_size1.jpg");

				commonFtpDisconnect ($connId);
		}
	}

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* rejectUpdateProfile																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function rejectUpdateProfile ($xmlRequest)
{
	$id				= commonQuery_escapeStr(xmlParser_getValue($xmlRequest,"id"));
	$dbField		= commonQuery_escapeStr(xmlParser_getValue($xmlRequest,"dbField"));

	$sql 			= "select * from israeli_profileUpdates where id = $id";
	$result 		= commonDoQuery($sql);
	$row 			= commonQuery_fetchRow($result);

	$rejected		= trim($row['rejected'], ",");
	$rejectedArray 	= explode(',', $rejected);

	if (!in_array($dbField, $rejectedArray))
	{
		$rejected	.= ",$dbField";

		$sql	= "update israeli_profileUpdates set rejected = '$rejected' where id = $id";
		commonDoQuery($sql);
	}

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateAll																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateAll ($xmlRequest)
{
	$id			= commonQuery_escapeStr(xmlParser_getValue($xmlRequest,"id"));

	$sql 		= "select * from israeli_profileUpdates where id = $id";
	$result 	= commonDoQuery($sql);
	$changesRow = commonQuery_fetchRow($result);

	foreach (explode(";", $changesRow['sqlCode']) as $sql)
	{
		if ($sql != "")
			commonDoQuery($sql);
	}

	// pic change
	if ($changesRow['picFile'])
	{
		$domainRow	= commonGetDomainRow();
		$domainName = commonGetDomainName ($domainRow);

		$connId 	= commonFtpConnect($domainRow); 

		ftp_chdir ($connId, "membersFiles");

		ftp_get	($connId, "/tmp/$changesRow[picFile]", "$changesRow[picFile]", FTP_BINARY);
		ftp_put ($connId,  str_replace("_${id}_", "_", "$changesRow[picFile]"), "/tmp/$changesRow[picFile]", FTP_BINARY);
		unlink("/tmp/$changesRow[picFile]");
		ftp_get	($connId, "/tmp/$changesRow[memberId]_${id}_size1.jpg", "$changesRow[memberId]_${id}_size1.jpg", FTP_BINARY);
		ftp_put ($connId, "$changesRow[memberId]_size1.jpg", "/tmp/$changesRow[memberId]_${id}_size1.jpg", FTP_BINARY);
		unlink("/tmp/$changesRow[memberId]_${id}_size1.jpg");

		//ftp_rename ($connId, "$changesRow[picFile]", 				   str_replace("_${id}_", "_", "$changesRow[picFile]"));
		//ftp_rename ($connId, "$changesRow[memberId]_${id}_size1.jpg", "$changesRow[memberId]_size1.jpg");

		commonFtpDisconnect ($connId);
	}

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteUpdate																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteUpdate ($xmlRequest)
{
	$id			= commonQuery_escapeStr(xmlParser_getValue($xmlRequest,"id"));

	$sql 		= "update israeli_profileUpdates set isDeleted = 1 where id = $id";
	commonDoQuery($sql);

	return "";
}

