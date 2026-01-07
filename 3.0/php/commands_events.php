<?php

include "picsTools.php";

$tags 	  		= Array("id", "status", "type", "url", "isNewWin", "categoryId", "contactPhone1", "contactPhone2", "fax", "email", "siteUrl", 
						"link1", "link2", "link3", "eventCode", "price", "canRegister", "maxRegisters", "earlyPrice", "earlyDate", "couponPrice", 
						"couponCode", "memberPrice", "albumId");
$langTags 		= Array("name", "description", "organization", "place", "instructor", "remarks", "contactName1", "contactName2", "contactDetails",
				 		"priceText", "linkName1", "linkName2", "linkName3");
$pageTags 		= Array("id", "layoutId", "membersOnly", "showOnSitemap", "navParentId");

$registerTags 	= Array("id", "eventId", "status", "verifyCode", "firstName", "lastName", "email", "phone", "cellphone", "city", "address", "zipcode",
	   				    "numRegistered", "registerTime", "orderNumber", "moreDetails", "workDetails");

$orderTags		= Array("status", "payMethod", "ccHolderTZ", "ccHolderName", "ccType", "ccNumber", "ccExpireDate", "ccCvv");

/* ----------------------------------------------------------------------------------------------------	*/
/* getEvents																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function getEvents ($xmlRequest)
{	
	global $usedLangs;

	$langsArray = explode(",",$usedLangs);

	$condition  = commonAddIbosUserCondition();

	// status
	$status		= xmlParser_getValue($xmlRequest, "status");
	if ($status != "")
		$condition .= " and status = '$status' ";

	// categoryId
	$categoryId		= xmlParser_getValue($xmlRequest, "categoryId");
	if ($categoryId != "")
		$condition .= " and categoryId = '$categoryId' ";

	// name
	$name		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "name")));
	if ($name != "")
		$condition .= " and name like '%$name%' ";

	$having = "";

	// by from & to date
	$fromDate = xmlParser_getValue($xmlRequest, "fromDate");
	$toDate   = xmlParser_getValue($xmlRequest, "toDate");
	if ($fromDate != "")
	{
		$fromDate = formatApplToDB ("$fromDate 00:00");
		$having .= " having minDate >= '$fromDate' ";
	}

	if ($toDate != "")
	{
		$toDate = formatApplToDB ("$toDate 23:59");

		if ($having == "")
			$having  = "having ";
		else
			$having .= "and ";

		$having .= "maxDate <= '$toDate' ";
	}
	
	$totalRegs	= array();

	$sql		= "select eventId, count(*) as counter from eventRegisters group by eventId";
	$result		= commonDoQuery($sql);

	while ($row = commonQuery_fetchRow($result))
	{
		$totalRegs[$row['eventId']] = $row['counter'];
	}

	// get total
	$queryStr	 = "select count(*) ,min(date) as minDate, max(date) as maxDate 
					from (events, pages)
					left join events_byLang on events.id = events_byLang.eventId and events_byLang.language = '$langsArray[0]'  
					left join eventsDates on events.id = eventsDates.eventId 
					where events.id = pages.id
					$condition
					group by events.id 
					$having";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$total	     = $row[0];

	// get details
	$queryStr 	 = "select events.*, events_byLang.* , min(date) as minDate, max(date) as maxDate 
				    from (events, pages)
					left join events_byLang on events.id = events_byLang.eventId and events_byLang.language = '$langsArray[0]'  
				    left join eventsDates on events.id = eventsDates.eventId 
				    where events.id = pages.id
				    $condition
				    group by events.id 
				    $having
				    order by id desc " . commonGetLimit ($xmlRequest);

	$result	     = commonDoQuery ($queryStr);

	$numRows    = commonQuery_numRows($result);

	$xmlResponse = "<items>";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		$id   		  	= $row['id'];
		$status 	  	= formatEventStatus($row['status']);
		$name		  	= commonValidXml($row['name']);
		$fromDate	  	= formatApplDate($row['minDate']);
		$toDate		  	= formatApplDate($row['maxDate']);
		$contactName1  	= commonValidXml($row['contactName1']);
		$maxRegisters	= $row['maxRegisters'];
		$totalRegisters	= (isset($totalRegs[$id]) ? $totalRegs[$id] : 0);

		$registers		= commonPhpEncode("$totalRegisters מתוך $maxRegisters");

		$xmlResponse .=	"<item>
							 <eventId>$id</eventId>
				 			 <name>$name</name>
							 <contactName1>$contactName1</contactName1>
							 <fromDate>$fromDate</fromDate>
							 <toDate>$toDate</toDate>
							 <status>$status</status>
							 <registers>$registers</registers>
						 </item>";
	}

	$xmlResponse .=	"</items>" .
					commonGetTotalXml($xmlRequest,$numRows,$total);
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getEventDetails																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getEventDetails ($xmlRequest)
{
	global $usedLangs, $tags, $langTags, $pageTags;

	$id		= xmlParser_getValue($xmlRequest, "eventId");

	if ($id == "")
		trigger_error ("חסר קוד אירוע לביצוע הפעולה");


	$queryStr = "select events.*, events_byLang.*, pages.layoutId, pages.membersOnly, pages.showOnSitemap, navParentId, navTitle
				 from events, events_byLang, pages, pages_byLang
				 where events.id = events_byLang.eventId
				 and   events.id = pages.id
				 and   events.id = pages_byLang.pageId
				 and   pages_byLang.language = events_byLang.language
				 and   events.id = $id";
	$result   = commonDoQuery ($queryStr);

	if (commonQuery_numRows($result) == 0)
		trigger_error ("אירוע קוד זה ($id) לא קיים במערכת. לא ניתן לבצע את הפעולה");

	$langsArray = explode(",",$usedLangs);

	$xmlResponse = "";

	$domainRow   = commonGetDomainRow ();
	$domainUrl   = commonGetDomainName($domainRow);
	
	$pressText     = commonPhpEncode("לחץ כאן");

	while ($row = commonQuery_fetchRow($result))
	{
		$language = $row['language'];

		$langsArray = commonArrayRemove ($langsArray, $language);	

		$row['earlyDate'] = formatApplDate($row['earlyDate']);

		if ($xmlResponse == "")
		{
			for ($i=0; $i < count($tags); $i++)
			{
				eval ("\$$tags[$i] = \$row['$tags[$i]'];");

			}

			for ($i=0; $i < count($tags); $i++)
			{
				eval ("\$$tags[$i] = commonValidXml(\$$tags[$i]);");

				eval ("\$xmlResponse .= \"<$tags[$i]>\$$tags[$i]</$tags[$i]>\";");
			}
			
			for ($i=1; $i < count($pageTags); $i++)
			{
				eval ("\$$pageTags[$i] = \$row['$pageTags[$i]'];");
				eval ("\$$pageTags[$i] = commonValidXml(\$$pageTags[$i]);");

				eval ("\$xmlResponse .= \"<$pageTags[$i]>\$$pageTags[$i]</$pageTags[$i]>\";");
			}
			
			$xmlResponse .= "<eventId>$id</eventId>";

			$sourceFile	   = commonValidXml($row['attachSourceFile']);
	
			$fullFileName  = urlencode($row['attachFile']);
			$fullFileName  = commonValidXml("$domainUrl/eventsFiles/$fullFileName");
	
			$show	 		= "";
			$delete	 		= "";

			if ($row['attachFile'] != "")
			{
				$show 	= $pressText;
				$delete	= $pressText;
			}

			$xmlResponse .= "<attachSource>$sourceFile</attachSource>
							 <formSourceFile>$sourceFile</formSourceFile>
							 <fullFileName>$fullFileName</fullFileName>
							 <show>$show</show>
							 <delete>$delete</delete>";

			$sourceFile	   = commonValidXml($row['picSourceFile']);
	
			$fullFileName  = urlencode($row['picFile']);
			$fullFileName  = commonValidXml("$domainUrl/eventsFiles/$fullFileName");
	
			$show	 		= "";
			$delete	 		= "";

			if ($row['picFile'] != "")
			{
				$show 	= $pressText;
				$delete	= $pressText;
			}

			$xmlResponse .= "<picSource>$sourceFile</picSource>
							 <formSourcePic>$sourceFile</formSourcePic>
							 <fullPicName>$fullFileName</fullPicName>
							 <showPic>$show</showPic>
							 <deletePic>$delete</deletePic>";
		}

		for ($i=0; $i < count($langTags); $i++)
		{
			eval ("\$$langTags[$i] = commonValidXml(\$row['$langTags[$i]']);");
			eval ("\$xmlResponse .=	\"<$langTags[$i]\$language>\$$langTags[$i]</$langTags[$i]\$language>\";");
		}

		$navTitle	  = commonValidXml($row['navTitle']);

		$xmlResponse .= "<navTitle$language>$navTitle</navTitle$language>";

/*		$winTitle	  = commonValidXml($row['winTitle']);
		$keywords	  = commonValidXml($row['keywords']);
		$description  = commonValidXml($row['description']);
		$rewriteName  = commonValidXml($row['rewriteName']);

		$xmlResponse .= "<winTitle$language>$winTitle</winTitle$language>
						 <keywords$language>$keywords</keywords$language>
						 <metaDescription$language>$description</metaDescription$language>
						 <rewriteName$language>$rewriteName</rewriteName$language>";*/
	}

	// add missing languages
	// ------------------------------------------------------------------------------------------------
	for ($i=0; $i<count($langsArray); $i++)
	{
		$language	  = $langsArray[$i];

		for ($j=0; $j < count($langTags); $j++)
		{
			eval ("\$xmlResponse .=	\"<$langTags[$j]\$language><![CDATA[]]></$langTags[$j]\$language>\";");
		}

/*		$xmlResponse .= "<keywords$language><![CDATA[]]></keywords$language>
						 <winTitle$language><![CDATA[]]></winTitle$language>
						 <metaDescription$language><![CDATA[]]></metaDescription$language>
						 <rewriteName$language><![CDATA[]]></rewriteName$language>";*/
	}

	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getEventDates																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getEventDates ($xmlRequest)
{	
	$id		= xmlParser_getValue($xmlRequest, "eventId");

	if ($id == "")
		trigger_error ("חסר קוד אירוע לביצוע הפעולה");

	$queryStr 	= "select * from eventsDates where eventId = $id order by date";
	$result	     = commonDoQuery ($queryStr);

	$numRows    = commonQuery_numRows($result);

	$xmlResponse = "<items>";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		$date	  = formatApplDate($row['date']);
		$day	  = formatDayOfWeek($row['date']);
		$fromTime = formatApplTime($row['fromTime']);
		$toTime   = formatApplTime($row['toTime']);

		$xmlResponse .=	"<item>
							 <date>$date</date>
							 <day>$day</day>
							 <fromTime>$fromTime</fromTime>
							 <toTime>$toTime</toTime>
						 </item>";
	}

	$xmlResponse .=	"</items>";
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteEventDate																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteEventDate ($xmlRequest)
{
	global $usedLangs;

	$id			= xmlParser_getValue($xmlRequest, "eventId");
	$date		= xmlParser_getValue($xmlRequest, "date");
	$fromTime	= xmlParser_getValue($xmlRequest, "fromTime");
	$toTime		= xmlParser_getValue($xmlRequest, "toTime");
	if ($id == "" || $date == "")
		trigger_error ("חסר קוד אירוע או תאריך לביצוע הפעולה");

	if ($fromTime == "") $fromTime = "00:00:00";
	if ($toTime   == "") $toTime   = "00:00:00";

	$date 		= substr(formatApplToDB ("$date 00:00:00"), 0, 10);

	$queryStr = "delete from eventsDates where eventId = $id and date = '$date' and toTime   = '$toTime' and fromTime = '$fromTime'";
	commonDoQuery ($queryStr);

	$langsArray = explode(",",$usedLangs);

	for ($i=0; $i<count($langsArray); $i++)
	{
		$language		= $langsArray[$i];
		$domainRow  = commonGetDomainRow ();
		$domainName = commonGetDomainName ($domainRow);
		$file = fopen ("$domainName/eventsRSS.php?lang=$language","r");
		fclose ($file);
	}

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* addEventDate																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function addEventDate ($xmlRequest)
{
	global	$usedLangs;

	$id			= xmlParser_getValue($xmlRequest, "eventId");
	$date		= formatApplToDB(xmlParser_getValue($xmlRequest, "date") . " 00:00:00");
	$fromTime	= xmlParser_getValue($xmlRequest, "fromTime");
	$toTime		= xmlParser_getValue($xmlRequest, "toTime");

	// check if date already exist
	$queryStr	= "select count(*) from eventsDates where eventId = $id and date = '$date'";
	$result		= commonDoQuery($queryStr);
	$row		= commonQuery_fetchRow($result);

	if ($row[0] != 0)
	{
		trigger_error ("תאריך זה כבר קיים עבור האירוע");
	}

	$queryStr   = "insert into eventsDates (eventId, date, fromTime, toTime)
				   values ($id, '$date', '$fromTime', '$toTime')";
	
	commonDoQuery ($queryStr);

	$langsArray = explode(",",$usedLangs);

	for ($i=0; $i<count($langsArray); $i++)
	{
		$language		= $langsArray[$i];
		$domainRow  = commonGetDomainRow ();
		$domainName = commonGetDomainName ($domainRow);
		$file = fopen ("$domainName/eventsRSS.php?lang=$language","r");
		fclose ($file);
	}

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateEventDate																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateEventDate ($xmlRequest)
{
	$id			= xmlParser_getValue($xmlRequest, "eventId");
	$date		= substr(formatApplToDB(xmlParser_getValue($xmlRequest, "date") . " 00:00:00"), 0, 10);
	$fromTime	= xmlParser_getValue($xmlRequest, "fromTime");
	$toTime		= xmlParser_getValue($xmlRequest, "toTime");

	$queryStr   = "update eventsDates set fromTime = '$fromTime',
										  toTime   = '$toTime'
				   where eventId  = $id 			and 
				   		 date     = '$date'";
	commonDoQuery ($queryStr);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* addEvent																								*/
/* ----------------------------------------------------------------------------------------------------	*/
function addEvent ($xmlRequest)
{
	return (editEvent ($xmlRequest, "add"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* doesEventsExist																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function doesEventsExist ($id)
{
	$queryStr		= "select count(*) from events where id=$id";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$count	     = $row[0];

	return ($count > 0);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateEvent																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateEvent ($xmlRequest)
{
	editEvent ($xmlRequest, "update");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* editEvent																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function editEvent ($xmlRequest, $editType)
{
	global $usedLangs, $tags, $langTags, $pageTags;
	global $userId;
	global $ibosHomeDir;

	for ($i=0; $i < count($pageTags); $i++)
	{
		eval ("\$$pageTags[$i] = commonDecode(xmlParser_getValue(\$xmlRequest,\"$pageTags[$i]\"));");	
	}

	for ($i=0; $i < count($tags); $i++)
	{
		eval ("\$$tags[$i] = commonDecode(xmlParser_getValue(\$xmlRequest,\"$tags[$i]\"));");	
	}

	$earlyDate = formatApplToDB ($earlyDate . " 00:00:00");
	
	$id   = xmlParser_getValue($xmlRequest, "eventId");

	if ($editType == "add")
	{
		$queryStr	= "select max(id) from pages";
		$result		= commonDoQuery ($queryStr);
		$row		= commonQuery_fetchRow ($result);
		$id 		= $row[0] + 1;
	}

	$pageVals = Array();

	for ($i=0; $i < count($pageTags); $i++)
	{
		eval ("array_push (\$pageVals,\$$pageTags[$i]);");
	}
	
	$vals = Array();

	for ($i=0; $i < count($tags); $i++)
	{
		eval ("array_push (\$vals,\$$tags[$i]);");
	}
	
	if ($status == "approved")
		$isReady = "1";
	else
		$isReady = "0";

	$attachSource	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "attachSource")));	
	$fileDeleted	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "fileDeleted")));	

	$fileDeleted	= ($fileDeleted == "1");
	$fileLoaded  	= false;

	$attachFile		= "";
	
	$suffix 		= "";
	if ($attachSource != "")
	{
		$fileLoaded = true;
		$suffix 	= commonFileSuffix($attachSource);
		$attachFile = "${id}_attach1$suffix";
	}

	if ($suffix == "." . $attachSource)	// wrong file name - don't load it
	{
		$fileLoaded 	= false;
		$attachFile    	= "";
	}

	# -- event pic
	$picSource 		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "picSource")));	
	$picDeleted		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "picDeleted")));	

	$picDeleted		= ($picDeleted == "1");
	$picLoaded  	= false;

	$picFile 		= "";
	$suffix2 		= "";

	if ($picSource != "")
	{
		$picLoaded 	= true;
		$suffix2 	= commonFileSuffix($picSource);
		$picFile 	= "${id}_pic_size0$suffix2";

		$dimensionId	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "dimensionId")));

		list ($picWidth, $picHeight, $bgColor, $forceSize) = commonGetDimensionDetails ($dimensionId);
	}

	if ($suffix2 == "." . $picSource)	// wrong file name - don't load it
	{
		$picDeleted 	= false;
		$picFile    	= "";
	}

	if ($editType == "update")
	{
		if ($id == "")
			trigger_error ("חסר קוד אירוע לביצוע הפעולה");

		if (!doesEventsExist($id))
		{
			trigger_error ("אירוע עם קוד זה ($id) לא קיים במערכת. לא ניתן לבצע את העדכון");
		}
		
		// pages table
		$queryStr = "update pages set ";

		for ($i=1; $i < count($pageTags); $i++)
		{
			$queryStr .= "$pageTags[$i] = '$pageVals[$i]',";
		}

		$queryStr = trim($queryStr, ",");

		$queryStr .= " where id = $id ";

		commonDoQuery ($queryStr);

		// update events tables
		$queryStr = "update events set ";

		for ($i=1; $i < count($tags); $i++)
		{
			$queryStr .= "$tags[$i] = '$vals[$i]',";
		}

		$queryStr = trim($queryStr, ",");

		if ($fileLoaded)
		{
			$queryStr .= ", attachFile  		= '$attachFile',
							attachSourceFile	= '$attachSource' ";
		}
		else if ($fileDeleted)
		{
			$queryStr .= ", attachFile 			= '',
							attachSourceFile	= '' ";
		}

		if ($picLoaded)
		{
			$queryStr .= ", picFile  			= '$picFile',
							picSourceFile		= '$picSource' ";
		}
		else if ($picDeleted)
		{
			$queryStr .= ", picFile 			= '',
							picSourceFile		= '' ";
		}

		$queryStr .= " where id = $id ";

		commonDoQuery ($queryStr);
	}
	else
	{
		$ibosUserId = commonGetIbosUserId ();

		// pages table
		$queryStr = "insert into pages (" . join(",",$pageTags) . ",type, ibosUserId) values ('" . join("','",$pageVals) . "','event', '$ibosUserId')";
		commonDoQuery ($queryStr);

		// events table
		$queryStr = "insert into events (" . join(",",$tags) . ", attachFile, attachSourceFile, picFile, picSourceFile) 
				values ('" . join("','",$vals) . "', '$attachFile', '$attachSource', '$picFile', '$picSource')";
		commonDoQuery ($queryStr);

		$byDate = xmlParser_getValue($xmlRequest, "byDate");

		switch ($byDate)
		{
			case "day"		:
					
				$date 		= formatApplToDB (xmlParser_getValue($xmlRequest,"day_date") . " 00:00:00");
				$fromTime	= xmlParser_getValue($xmlRequest, "day_fromTime");
				$toTime		= xmlParser_getValue($xmlRequest, "day_toTime");

				$queryStr   = "insert into eventsDates (eventId, date, fromTime, toTime)
							   values ($id, '$date', '$fromTime', '$toTime')";
				commonDoQuery ($queryStr);
				break;

			case "range"	:
				$fromDate	= formatApplToDB (xmlParser_getValue($xmlRequest,"range_fromDate") . " 00:00:00");
				$toDate		= formatApplToDB (xmlParser_getValue($xmlRequest,"range_toDate") . " 00:00:00");
				$fromTime	= xmlParser_getValue($xmlRequest, "range_fromTime");
				$toTime		= xmlParser_getValue($xmlRequest, "range_toTime");

				$addDate	= $fromDate;
				while ($addDate <= $toDate)
				{
					$queryStr   = "insert into eventsDates (eventId, date, fromTime, toTime)
								   values ($id, '$addDate', '$fromTime', '$toTime')";
					commonDoQuery ($queryStr);

					$addDate = date("Y-m-d", strtotime("$addDate +1 day"));
				}
				break;

			case "cycle"	:
				$date 		= formatApplToDB (xmlParser_getValue($xmlRequest,"cycle_date") . " 00:00:00");
				$cycleCount = xmlParser_getValue($xmlRequest,"cycleCount");
				$fromTime	= xmlParser_getValue($xmlRequest, "cycle_fromTime");
				$toTime		= xmlParser_getValue($xmlRequest, "cycle_toTime");

				$addDate	= $date;
				for ($i=0; $i<$cycleCount; $i++)
				{
					$queryStr   = "insert into eventsDates (eventId, date, fromTime, toTime)
								   values ($id, '$addDate', '$fromTime', '$toTime')";
					commonDoQuery ($queryStr);

					$addDate = date("Y-m-d", strtotime("$addDate +7 day"));
				}

				break;
		}
	}

	# delete all languages rows
	# ------------------------------------------------------------------------------------------------------
	$queryStr = "delete from events_byLang where eventId='$id'";
	commonDoQuery ($queryStr);
	
	$queryStr = "delete from pages_byLang where pageId='$id'";
	commonDoQuery ($queryStr);

	$domainRow  = commonGetDomainRow ();
	$domainName = commonGetDomainName ($domainRow);
	commonConnectToUserDB($domainRow);

	# add languages rows for this user
	# ------------------------------------------------------------------------------------------------------
	$langsArray = explode(",",$usedLangs);

	for ($i=0; $i<count($langsArray); $i++)
	{
		$language		= $langsArray[$i];

		$vals = Array();
		for ($j=0; $j < count($langTags); $j++)
		{
			eval ("\$$langTags[$j] = addslashes(commonDecode(xmlParser_getValue(\$xmlRequest,\"$langTags[$j]\$language\")));");	
			eval ("array_push (\$vals,\$$langTags[$j]);");
		}		

		$navTitle 	 		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "navTitle$language")));

/*		$winTitle 	 		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "winTitle$language")));
		$keywords 	 		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "keywords$language")));
		$metaDescription 	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "metaDescription$language")));
		$rewriteName 		= str_replace(" ", "-", addslashes(commonDecode(xmlParser_getValue($xmlRequest, "rewriteName$language"))));
 */
		// pages by lang table
		$queryStr	= "insert into pages_byLang (pageId, winTitle, title, language, isReady, navTitle) 
					   values ('$id','$name', '$name', '$language', '$isReady', '$navTitle')";
//		$queryStr	= "insert into pages_byLang (pageId, winTitle, title, language, isReady, winTitle, keywords, description, rewriteName) 
//					   values ('$id','$name', '$name', '$language', '$isReady', '$winTitle, '$keywords', '$metaDescription', '$rewriteName')";
		commonDoQuery ($queryStr);

		$queryStr		= "insert into events_byLang (eventId, language," . join(",",$langTags) . ") 
						   values ($id, '$language', '" . join ("','", $vals) . "')";
	
		commonDoQuery ($queryStr);

		$file = fopen ("$domainName/eventsRSS.php?lang=$language","r");
		fclose ($file);
	}

	// handle file
	$filePath = "$ibosHomeDir/html/SWFUpload/files/$userId/";

	$connId = commonFtpConnect($domainRow); 

	ftp_chdir($connId, "eventsFiles/");

	if ($fileLoaded)
	{

		$upload = ftp_put($connId, $attachFile, "$filePath/$attachSource", FTP_BINARY); 

		unlink("$filePath/$attachSource");

		commonFtpDisconnect ($connId);
	}
	else if ($fileDeleted)
	{
		ftp_chdir($connId, "eventsFiles/");

		// find old file name !!! TBD
//		$fileName = "${id}_size0.jpg";

//		@ftp_delete($connId, "$fileName");
	}

	// event pic
	if ($picLoaded)
	{
		commonFtpDelete ($connId, $picFile);

		$upload = ftp_put($connId, $picFile, "$filePath/$picSource", FTP_BINARY);

		$resizedFileName = "${id}_pic_size1.jpg";

		if ($picWidth == 0 && $picHeight == 0)
		{
			$upload = ftp_put($connId, $resizedFileName, "$filePath/$picSource", FTP_BINARY);
		}
		else
		{
			if ($forceSize == "1")
				picsToolsForceResize("$filePath/$picSource", $suffix2, $picWidth, $picHeight, "/../../tmp/$resizedFileName", $bgColor);
			else
				picsToolsResize("$filePath/$picSource", $suffix2, $picWidth, $picHeight, "/../../tmp/$resizedFileName", $bgColor);
		
			$upload = ftp_put($connId, $resizedFileName, "/../../tmp/$resizedFileName", FTP_BINARY);
		}
	}
	else if ($picDeleted)
	{
		// find old file name !!! TBD
	}

 	// delete old files
	commonDeleteOldFiles ($filePath, 3600);	// 1 hour

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteEvent																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteEvent ($xmlRequest)
{
	global $usedLangs;
	
	$id = xmlParser_getValue($xmlRequest, "eventId");

	if ($id == "")
		trigger_error ("חסר קוד אירוע לביצוע הפעולה");

	$queryStr = "delete from pages where id = $id";
	commonDoQuery ($queryStr);

	$queryStr = "delete from pages_byLang where pageId = $id";
	commonDoQuery ($queryStr);

	$queryStr = "delete from events where id = $id";
	commonDoQuery ($queryStr);

	$queryStr = "delete from events_byLang where eventId = $id";
	commonDoQuery ($queryStr);

	$queryStr = "delete from eventsDates where eventId = $id";
	commonDoQuery ($queryStr);

	$langsArray = explode(",",$usedLangs);

	for ($i=0; $i<count($langsArray); $i++)
	{
		$language		= $langsArray[$i];
		$domainRow  = commonGetDomainRow ();
		$domainName = commonGetDomainName ($domainRow);
		$file = fopen ("$domainName/eventsRSS.php?lang=$language","r");
		fclose ($file);
	}

	return "";	
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getEventRegisters																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function getEventRegisters ($xmlRequest)
{	
	$eventId = xmlParser_getValue($xmlRequest, "eventId");
	if ($eventId == "")
		trigger_error ("חסר מזהה ארוע לשם קבלת הנרשמים");


	// get total
	$queryStr     = "select count(*) from eventRegisters where eventId = $eventId";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$total	     = $row[0];

	// get details
	$queryStr    = "select eventRegisters.*, 
						   clubMembers.firstname as memberFirstName, clubMembers.lastname as memberLastName, clubMembers.email as memberEmail,
						   if(eventRegisters.memberId = 0, eventRegisters.firstName, clubMembers.firstname) as sortName
					from eventRegisters
					left join clubMembers on eventRegisters.memberId = clubMembers.id
					where eventId = $eventId
					order by sortName " . commonGetLimit ($xmlRequest);
	$result	     = commonDoQuery ($queryStr);

	$numRows    = commonQuery_numRows($result);

	$xmlResponse = "<items>";

	$totalRegistered	= 0;

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		if ($row['memberId'] != 0)
		{
			$row['firstName'] 	= $row['memberFirstName'];
			$row['lastName'] 	= $row['memberLastName'];
			$row['email']		= $row['memberEmail'];
		}

		$id   			= $row['id'];
		$firstName 		= commonValidXml ($row['firstName'],true);
		$lastName 		= commonValidXml ($row['lastName'],true);
		$email  		= commonValidXml ($row['email'],true);
		$registerTime	= formatApplDate($row['registerTime']);
		$numRegistered	= $row['numRegistered'];
		$status			= $row['status'];

		$totalRegistered += $numRegistered;

		switch ($status)
		{
			case "new"		: $status = "חדש";		break;
			case "active"	: $status = "פעיל";		break;
			case "disabled" : $status = "חסום";		break;
			case "paid" 	: $status = "שולם";		break;
		}
		$status = commonPhpEncode($status);

		$emailLink	= "";

		if ($row['email'] != "")
			$emailLink = commonValidXml("<a href='mailto:$row[email]'>$row[email]</a>");

		$xmlResponse .=	"<item>
							<id>$id</id>
							<firstName>$firstName</firstName>
							<lastName>$lastName</lastName>
							<email>$email</email>
							<emailLink>$emailLink</emailLink>
							<registerTime>$registerTime</registerTime>
							<status>$status</status>
							<numRegistered>$numRegistered</numRegistered>
						</item>";
	}

	$totalText = commonGetTotalXml($xmlRequest,$numRows,$total);
	$totalText = str_replace("</totalText>", commonPhpEncode("&nbsp; &nbsp; &nbsp; סה\"כ נרשמים: $totalRegistered") . "</totalText>", $totalText);

	$xmlResponse .=	"</items>" . $totalText;
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getRegisterDetails																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function getRegisterDetails ($xmlRequest)
{
	global $registerTags, $orderTags;

	$id		= xmlParser_getValue($xmlRequest, "id");

	if ($id == "")
		trigger_error ("חסר קוד נרשם לביצוע הפעולה");


	$queryStr    = "select eventRegisters.*, 
						   clubMembers.firstname as memberFirstName, clubMembers.lastname as memberLastName, clubMembers.email as memberEmail
					from eventRegisters
					left join clubMembers on eventRegisters.memberId = clubMembers.id
					where eventRegisters.id = $id";
	$result   = commonDoQuery ($queryStr);
	$row 	  = commonQuery_fetchRow($result);

	if (commonQuery_numRows($result) == 0)
		trigger_error ("נשרם עם קוד זה ($id) לא קיים במערכת. לא ניתן לבצע את הפעולה");

	if ($row['memberId'] != 0)
	{
		$row['firstName'] 	= $row['memberFirstName'];
		$row['lastName'] 	= $row['memberLastName'];
		$row['email']		= $row['memberEmail'];
	}

	$xmlResponse = "";

	for ($i=0; $i < count($registerTags); $i++)
	{
		eval ("\$$registerTags[$i] = \$row['$registerTags[$i]'];");
	}

	for ($i=0; $i < count($registerTags); $i++)
	{
		eval ("\$$registerTags[$i] = commonValidXml(\$$registerTags[$i]);");
		eval ("\$xmlResponse .= \"<$registerTags[$i]>\$$registerTags[$i]</$registerTags[$i]>\";");
	}

	if ($row['orderNumber'] != "")
	{
		// add order details
		$queryStr 	= "select * from orders where orderNumber = $row[orderNumber]";
		$result		= commonDoQuery ($queryStr);
		$row 		= commonQuery_fetchRow($result);

		for ($i=0; $i < count($orderTags); $i++)
		{
			eval ("\$$orderTags[$i] = \$row['$orderTags[$i]'];");
		}

		if ($ccCvv == "0") $ccCvv = "";

		for ($i=0; $i < count($orderTags); $i++)
		{
			eval ("\$$orderTags[$i] = commonValidXml(\$$orderTags[$i]);");
			eval ("\$xmlResponse .= \"<$orderTags[$i]>\$$orderTags[$i]</$orderTags[$i]>\";");
		}
	}


	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateEventRegister																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateEventRegister ($xmlRequest)
{
	global $registerTags, $orderTags;

	$id 	= xmlParser_getValue($xmlRequest, "id");

	if ($id == "")
		trigger_error ("חסר קוד נרשם לביצוע הפעולה");

	for ($i=0; $i < count($registerTags); $i++)
	{
		eval ("\$$registerTags[$i] = addslashes(commonDecode(xmlParser_getValue(\$xmlRequest,\"$registerTags[$i]\")));");	
	}

	for ($i=0; $i < count($orderTags); $i++)
	{
		eval ("\$$orderTags[$i] = addslashes(commonDecode(xmlParser_getValue(\$xmlRequest,\"$orderTags[$i]\")));");	
	}

	$registerVals = Array();

	for ($i=0; $i < count($registerTags); $i++)
	{
		eval ("array_push (\$registerVals,\$$registerTags[$i]);");
	}
	
	$orderVals = Array();

	for ($i=0; $i < count($orderTags); $i++)
	{
		eval ("array_push (\$orderVals,\$$orderTags[$i]);");
	}
	
	// eventRegisters table
	$queryStr = "update eventRegisters set ";

	for ($i=4; $i < count($registerTags); $i++)
	{
		$queryStr .= "$registerTags[$i] = '$registerVals[$i]',";
	}

	$queryStr = trim($queryStr, ",");

	$queryStr .= " where id = $id ";

	commonDoQuery ($queryStr);

	// orders table
	$queryStr = "update orders set ";

	for ($i=0; $i < count($orderTags); $i++)
	{
		$queryStr .= "$orderTags[$i] = '$orderVals[$i]',";
	}

	$queryStr = trim($queryStr, ",");

	$queryStr .= " where orderNumber = $orderNumber ";

	commonDoQuery ($queryStr);

	return ("");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteEventRegister																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteEventRegister ($xmlRequest)
{
	$id 		= xmlParser_getValue($xmlRequest, "id");
	$eventId 	= xmlParser_getValue($xmlRequest, "eventId");

	if ($id == "" || $eventId == "")
		trigger_error ("חסר קוד נרשם או אירוע לביצוע הפעולה");

	$queryStr = "delete from eventRegisters where id = $id and eventId = $eventId";
	commonDoQuery ($queryStr);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* excelExport																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function excelExport ($xmlRequest)
{
	global $usedLangs;
	global $userId;

	$langsArray = explode(",",$usedLangs);

	$eventId = xmlParser_getValue($xmlRequest, "eventId");
	if ($eventId == "")
		trigger_error ("חסר מזהה ארוע לשם קבלת הנרשמים");

	$queryStr	= "select name from events_byLang where eventId = $eventId and language = '$langsArray[0]'";
	$result		= commonDoQuery($queryStr);
	$row		= commonQuery_fetchRow($result);
	$eventName	= commonPrepareToFile($row['name']);

	$excelTitle = "דוח נרשמים לאירוע '$eventName'";
	$sheetTitle = "נרשמים";
	$now		= "תאריך הפקת הדוח: " . date("d/m/Y H:i");
	$total		= "מספר נרשמים: #totalRegistered#";

	if ($userId == 1865) // land value
	{
		$merge = 8;
	}
	else
	{
		$merge = 11;
	}

    $excel = "<?xml version='1.0' encoding='ISO-8859-8' ?>												
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
							<Alignment ss:Horizontal=\"Center\" ss:Vertical=\"Bottom\"/>
							<Font x:Family=\"Swiss\" ss:Color=\"#A4C916\" ss:Size=\"16\" ss:Bold=\"1\"/>
					  	</Style>
  						<Style ss:ID=\"sReportDate\">
							<Alignment ss:Horizontal=\"Center\" ss:Vertical=\"Bottom\"/>
							<Font x:Family=\"Swiss\" ss:Color=\"#597FA3\" ss:Size=\"12\" ss:Bold=\"1\"/>
					  	</Style>
  						<Style ss:ID=\"sTotal\">
							<Alignment ss:Horizontal=\"Center\" ss:Vertical=\"Bottom\"/>
							<Font x:Family=\"Swiss\" ss:Color=\"#000000\" ss:Size=\"13\" ss:Bold=\"1\"/>
					  	</Style>
 			 			<Style ss:ID=\"sHeader\">
			   				<Alignment ss:Horizontal=\"Center\" ss:Vertical=\"Bottom\"/>
			   				<Borders>
			   					<Border ss:Position=\"Bottom\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Left\"   ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Right\"  ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
			   					<Border ss:Position=\"Top\"    ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		   					</Borders>
			   				<Font x:Family=\"Swiss\" ss:Color=\"#505050\" ss:Bold=\"1\"/>
		   					<Interior ss:Color=\"#EEEEEE\" ss:Pattern=\"Solid\"/>
			  			</Style>
 			 			<Style ss:ID=\"sFooter\">
			   				<Alignment ss:Horizontal=\"Right\" ss:Vertical=\"Bottom\"/>
			   				<Borders>
			   					<Border ss:Position=\"Bottom\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Left\"   ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Right\"  ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
			   					<Border ss:Position=\"Top\"    ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		   					</Borders>
			   				<Font x:Family=\"Swiss\" ss:Color=\"#505050\" ss:Bold=\"1\"/>
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
			   				<Font x:Family=\"Swiss\" ss:Color=\"#505050\" ss:Bold=\"1\"/>
		   					<Interior ss:Color=\"#EEEEEE\" ss:Pattern=\"Solid\"/>
			  			</Style>
			  			<Style ss:ID=\"sCell\">
			   				<Alignment ss:Horizontal=\"Right\" ss:Vertical=\"Bottom\"/>
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
			   				<Alignment ss:Horizontal=\"Center\" ss:Vertical=\"Bottom\"/>
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
					<Worksheet ss:Name=\"$sheetTitle\" ss:RightToLeft=\"1\">
					<Table x:FullColumns=\"1\" x:FullRows=\"1\">
   	        		<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"40\"/>
   	        		<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"100\"/>
   	        		<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"100\"/>
   	        		<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"100\"/>
   	        		<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"100\"/>
   	        		<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"100\"/>
   	        		<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"100\"/>
   	        		<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"100\"/>
					<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"100\"/>";

	if ($userId != 1865)
	{
		$excel .=  "<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"100\"/>
   	        		<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"100\"/>
					<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"100\"/>";
	}

	$excel .= 	   "<Row>
						<Cell ss:MergeAcross=\"$merge\" ss:StyleID=\"sTitle\"><Data ss:Type=\"String\">$excelTitle</Data></Cell>
					</Row>
					<Row>
						<Cell ss:MergeAcross=\"$merge\" ss:StyleID=\"sTotal\"><Data ss:Type=\"String\">$total</Data></Cell>
					</Row>
					<Row>
						<Cell ss:MergeAcross=\"$merge\" ss:StyleID=\"sReportDate\"><Data ss:Type=\"String\">$now</Data></Cell>
					</Row>
					<Row ss:Height=\"13.5\"/>
					<Row>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">מזהה</Data></Cell>";

	if ($userId == 1865) // land value
	{
		$excel .=      "<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">שם מלא</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">אימייל</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">טלפון</Data></Cell>";

		$workTitle	= "שם המשרד";
	}
	else
	{
		$excel .=      "<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">שם פרטי</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">שם משפחה</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">אימייל</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">טלפון</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">נייד</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">כתובת</Data></Cell>";

		$workTitle = "מקום עבודה";
	}

	$excel .= 		   "<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">$workTitle</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">מספר נרשמים</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">הערות</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">תאריך הרשמה</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">סטטוס</Data></Cell>
					</Row>";

	$queryStr    = "select eventRegisters.*, clubMembers.firstname, clubMembers.lastname, clubMembers.email as memberEmail,
						if(eventRegisters.memberId = 0, eventRegisters.firstName, clubMembers.firstname) as sortName,
						clubMembers.cellphone as cellphone2
					from eventRegisters
					left join clubMembers on eventRegisters.memberId = clubMembers.id
					where eventId = $eventId
					order by sortName";

	if ($userId == 1865) // land value
	{
		$queryStr = str_replace("where", "left join landvalue_members on eventRegisters.memberId = landvalue_members.memberId where", $queryStr);
		$queryStr = str_replace("from",  ", landvalue_members.status as landvalueStatus from", $queryStr);
	}

	$result	     = commonDoQuery ($queryStr);

	$totalRegistered = 0;
	while ($row = commonQuery_fetchRow($result))
	{
		$id   			= $row['id'];

		if ($row['memberId'] != 0)
		{
			$row['firstName'] 	= $row['firstname'];
			$row['lastName'] 	= $row['lastname'];
			$row['email']		= $row['memberEmail'];
			$row['phone']		= $row['cellphone2'];
		}

		$totalRegistered += $row['numRegistered'];

		$firstName 		= commonPrepareToFile($row['firstName']);
		$lastName 		= commonPrepareToFile($row['lastName']);
		$email  		= commonPrepareToFile($row['email']);
		$address  		= commonPrepareToFile($row['address']);
		$phone			= commonPrepareToFile($row['phone']);
		$cellphone		= commonPrepareToFile($row['cellphone']);
		$numRegistered	= commonPrepareToFile($row['numRegistered']);
		$moreDetails	= commonPrepareToFile($row['moreDetails']);
		$workDetails	= commonPrepareToFile($row['workDetails']);
		$registerTime	= formatApplDate($row['registerTime']);
		$status			= $row['status'];

		if ($userId == 1865)
		{
			$status 	= $row['landvalueStatus'];

			if ($status == "")
			{
				$status = "אורח";

				// find member in clubMembers table
				$inSql		= "select landvalue_members.status from landvalue_members, clubMembers 
							   where  landvalue_members.memberId = clubMembers.id
							   and    (clubMembers.firstname = '" . addslashes($row['firstName']) . "' or clubMembers.email = '$row[email]')";
				$inResult	= commonDoQuery($inSql);

				if (commonQuery_numRows($inResult) != 0)
				{
					$inRow 	= commonQuery_fetchRow($inResult);
					$status	= $inRow['status'];
				}
			}
		}

		switch ($status)
		{
			case "new"		: $status 	= "חדש";		break;
			case "active"	: $status 	= "פעיל";		break;
			case "disabled" : $status 	= "חסום";		break;
			case "paid" 	: $status 	= "שולם";		break;
			case "inactive"	: $status	= "לא פעיל";	break;
			case "attached"	: $status 	= "נספח";		break;
			case "intern"	: $status 	= "מתמחה";		break;
			case "student"	: $status 	= "סטודנט";		break;
			case "regular"	: $status	= "חבר לשכה";	break;
		}

		$excel .= "<Row ss:Height=\"13.5\">
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"Number\">$id</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$firstName</Data></Cell>";
		
		if ($userId != 1865) // land value
		{
			$excel .=  "<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$lastName</Data></Cell>";
		}

		$excel .= 	   "<Cell ss:StyleID=\"sCellEng\"><Data ss:Type=\"String\">$email</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$phone</Data></Cell>";

		if ($userId != 1865) // land value
		{
			$excel .=  "<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$cellphone</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$address</Data></Cell>";
		}

		$excel .= 	   "<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$workDetails</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"Number\">$numRegistered</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$moreDetails</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$registerTime</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$status</Data></Cell>
				   </Row>";
	}

	$excel .= 	 "</Table>
				</Worksheet>
			</Workbook>";

	$excel = str_replace("#totalRegistered#", $totalRegistered, $excel);

	return (commonDoExcel($excel));
}

?>
