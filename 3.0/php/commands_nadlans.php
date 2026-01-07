<?php

$pageTags 		= Array("id", "layoutId");

$nadlanTags		= Array("id", "conversionRate", "confirmType", "hideEmail", "confirmPageId", "confirmDeletePageId", "errorPageId", "sendInformEmail",
						"numItemsInPage", "numPages", "numItemsBeforeBanner", "maxBannersInPage", "bannerWidth", "bannerHeight",
						"picWidth1", "picHeight1", "picWidth2", "picHeight2", "picDimension1", "picDimension2",
						"nadlanType", "itemType", "neighborhood", "numRooms", "numFloors", "floorNo", "kitchen", 
						"airDirections", "rentPeriod", "deleteReason", "numToilets", "numBathrooms", "numBalconies", "numHomeLevels", 
						"frontDirection", "numPartners", "propertyStatus", "furnishing");

$itemTags     	= Array("id", "nadlanId", "code", "status", "memberId", "isSelected", "boldLevel", "insertTime", "updateTime", 
						"expireTime", "deleteTime", "deleteReason", "hasAssessment", "assessmentPrice", 
						"nadlanType", "itemType", "nadlanYear", "cityId", "neighborhood", "street", "streetNo", "numRooms", "numFloors", "floorNo", 
						"price", "forSaleByBroker", "salePrice", "currency", "saleCurrency", "saleByBroker", "plottage", "buildPlottage", 
						"balconyPlottage", "gardenPlottage", "elevator", "parking", "airConditioning", "heating", "kitchen", "mamad", "parentsBedroom", 
						"store", "airDirections", "rentPeriod", 
						"immediateEviction", "flexibleEviction", "evictionDate", "evictionText", "description", "remarks", 
						"contactFromBook", "contactName", "contactPhone1", "contactPhone2", "contactEmail", "hideEmail",
						"contactAddress", "contactSiteUrl", "contactCityId",
						"numToilets", "numBathrooms", "numBalconies", "numHomeLevels", "frontDirection", "numPartners", "propertyStatus", 
						"garden", "solarHotWater", "trellises", "disabledAccess", "utilityRoom", "furnishing", "furnishingText", 
						"saturdayContact", "freeAddress", "numApartments");

/* ----------------------------------------------------------------------------------------------------	*/
/* getNadlans																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function getNadlans ($xmlRequest)
{	
	global $usedLangs;
	$langsArray = explode(",",$usedLangs);

	$condition  = "";

	// get total
	$queryStr	 = "select count(*) from nadlans";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$total	     = $row[0];

	// get details
	$queryStr = "select nadlans.id, pages_byLang.title
				 from nadlans, pages_byLang 
			     where nadlans.id = pages_byLang.pageId and language = '$langsArray[0]' 
				 order by id desc " . commonGetLimit ($xmlRequest);

	$result	     = commonDoQuery ($queryStr);

	$numRows    = commonQuery_numRows($result);

	$xmlResponse = "<items>";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		$id   		= $row['id'];
		$title		= commonValidXml($row['title']);

		$xmlResponse .=	"<item>
							 <id>$id</id>
							 <title>$title</title>
						 </item>";
	}

	$xmlResponse .=	"</items>" .
					commonGetTotalXml($xmlRequest,$numRows,$total);
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getNadlanDetails																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getNadlanDetails ($xmlRequest)
{
	global $usedLangs, $nadlanTags, $nadlanLangTags;

	$id		= xmlParser_getValue($xmlRequest, "nadlanId");

	if ($id == "")
		trigger_error ("חסר קוד לוח לביצוע הפעולה");


	$queryStr = "select * from nadlans, pages, pages_byLang
				 where nadlans.id = pages.id
				 and   nadlans.id = pages_byLang.pageId
				 and   nadlans.id = $id";
	$result   = commonDoQuery ($queryStr);

	if (commonQuery_numRows($result) == 0)
		trigger_error ("לוח קוד זה ($id) לא קיים במערכת. לא ניתן לבצע את הפעולה");

	$langsArray = explode(",",$usedLangs);

	$xmlResponse = "";

	while ($row = commonQuery_fetchRow($result))
	{
		$language = $row['language'];

		$langsArray = commonArrayRemove ($langsArray, $language);	

		if ($xmlResponse == "")
		{
			for ($i=0; $i < count($nadlanTags); $i++)
			{
				eval ("\$$nadlanTags[$i] = \$row['$nadlanTags[$i]'];");

			}

			for ($i=0; $i < count($nadlanTags); $i++)
			{
				eval ("\$$nadlanTags[$i] = commonValidXml(\$$nadlanTags[$i]);");

				eval ("\$xmlResponse .= \"<$nadlanTags[$i]>\$$nadlanTags[$i]</$nadlanTags[$i]>\";");
			}
			
			$xmlResponse .= "<nadlanId>$id</nadlanId>
							 <layoutId>$row[layoutId]</layoutId>
							 <oldConversionRate>$row[conversionRate]</oldConversionRate>";
		}

		for ($i=0; $i < count($nadlanLangTags); $i++)
		{
			eval ("\$$nadlanLangTags[$i] = commonValidXml(\$row['$nadlanLangTags[$i]']);");
			eval ("\$xmlResponse .=	\"<$nadlanLangTags[$i]\$language>\$$nadlanLangTags[$i]</$nadlanLangTags[$i]\$language>\";");
		}

		$title		  = commonValidXml($row['title']);
		$winTitle	  = commonValidXml($row['winTitle']);
		$keywords	  = commonValidXml($row['keywords']);
		$description  = commonValidXml($row['description']);
		$rewriteName  = commonValidXml($row['rewriteName']);

		$xmlResponse .= "<title$language>$title</title$language>
						 <winTitle$language>$winTitle</winTitle$language>
						 <keywords$language>$keywords</keywords$language>
						 <description$language>$description</description$language>
						 <rewriteName$language>$rewriteName</rewriteName$language>";
	}

	// add missing languages
	// ------------------------------------------------------------------------------------------------
	for ($i=0; $i<count($langsArray); $i++)
	{
		$language	  = $langsArray[$i];

		for ($j=0; $j < count($nadlanLangTags); $j++)
		{
			eval ("\$xmlResponse .=	\"<$nadlanLangTags[$j]\$language><![CDATA[]]></$nadlanLangTags[$j]\$language>\";");
		}

		$xmlResponse .= "<title$language><![CDATA[]]></title$language>
						 <keywords$language><![CDATA[]]></keywords$language>
						 <winTitle$language><![CDATA[]]></winTitle$language>
						 <description$language><![CDATA[]]></description$language>
						 <rewriteName$language><![CDATA[]]></rewriteName$language>";
	}

	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* addNadlan																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function addNadlan ($xmlRequest)
{
	return (editNadlan ($xmlRequest, "add"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* doesNadlanExist																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function doesNadlanExist ($id)
{
	$queryStr		= "select count(*) from nadlans where id=$id";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$count	     = $row[0];

	return ($count > 0);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getNadlanNextId																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getNadlanNextId ()
{
	$queryStr	= "select max(id) from pages";
	$result		= commonDoQuery ($queryStr);
	$row		= commonQuery_fetchRow ($result);
	$id 		= $row[0] + 1;
	
	return $id;
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateNadlan																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateNadlan ($xmlRequest)
{
	editNadlan ($xmlRequest, "update");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* editNadlan																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function editNadlan ($xmlRequest, $editType)
{
	global $usedLangs, $nadlanTags, $pageTags;

	for ($i=0; $i < count($pageTags); $i++)
	{
		eval ("\$$pageTags[$i] = commonDecode(xmlParser_getValue(\$xmlRequest,\"$pageTags[$i]\"));");	
	}

	for ($i=0; $i < count($nadlanTags); $i++)
	{
		eval ("\$$nadlanTags[$i] = commonDecode(xmlParser_getValue(\$xmlRequest,\"$nadlanTags[$i]\"));");	
	}

	$id		= xmlParser_getValue($xmlRequest, "nadlanId");

	if ($editType == "update")
	{
		if (!doesNadlanExist($id))
		{
			trigger_error ("לוח עם קוד זה ($id) לא קיימת במערכת. לא ניתן לבצע את העדכון");
		}
	}
	else
	{
		$id = getNadlanNextId ();
	}

	$pageVals = Array();

	for ($i=0; $i < count($pageTags); $i++)
	{
		eval ("array_push (\$pageVals,\$$pageTags[$i]);");
	}
	
	$vals = Array();

	for ($i=0; $i < count($nadlanTags); $i++)
	{
		eval ("array_push (\$vals,\$$nadlanTags[$i]);");
	}
	
	if ($editType == "update")
	{
		// pages table
		$queryStr = "update pages set ";

		for ($i=1; $i < count($pageTags); $i++)
		{
			$queryStr .= "$pageTags[$i] = '$pageVals[$i]',";
		}

		$queryStr = trim($queryStr, ",");

		$queryStr .= " where id = $id ";

		commonDoQuery ($queryStr);

		// check if conversionRate was changed
		if (xmlParser_getValue($xmlRequest,"oldConversionRate") != $conversionRate)
		{
			$queryStr	= "update nadlansItems set priceByRate = price * $conversionRate
						   where currency != 'ILS' and status not in ('deleted', 'rejected')";

			commonDoQuery ($queryStr);
		}

		// nadlans table
		$queryStr = "update nadlans set ";

		for ($i=1; $i < count($nadlanTags); $i++)
		{
			$queryStr .= "$nadlanTags[$i] = '$vals[$i]',";
		}

		$queryStr = trim($queryStr, ",");

		$queryStr .= " where id = $id ";

		commonDoQuery ($queryStr);
	}
	else
	{
		$queryStr = "insert into pages (" . join(",",$pageTags) . ",type) values ('" . join("','",$pageVals) . "','nadlan')";
		commonDoQuery ($queryStr);

		$queryStr = "insert into nadlans (" . join(",",$nadlanTags) . ",insertTime) values ('" . join("','",$vals) . "',now())";
		commonDoQuery ($queryStr);
	}

	# delete all languages rows
	# ------------------------------------------------------------------------------------------------------
	$queryStr = "delete from pages_byLang where pageId='$id'";
	commonDoQuery ($queryStr);
	
	# add languages rows for this user
	# ------------------------------------------------------------------------------------------------------
	$langsArray = explode(",",$usedLangs);

	for ($i=0; $i<count($langsArray); $i++)
	{
		$language		= $langsArray[$i];

		$title 		 = addslashes(commonDecode(xmlParser_getValue($xmlRequest, "title$language")));
		$winTitle 	 = addslashes(commonDecode(xmlParser_getValue($xmlRequest, "winTitle$language")));
		$keywords 	 = addslashes(commonDecode(xmlParser_getValue($xmlRequest, "keywords$language")));
		$description = addslashes(commonDecode(xmlParser_getValue($xmlRequest, "description$language")));
		$rewriteName = str_replace(" ", "-", addslashes(commonDecode(xmlParser_getValue($xmlRequest, "rewriteName$language"))));

		$queryStr	= "insert into pages_byLang (pageId, winTitle, title, language, isReady, keywords, description, rewriteName) 
					   values ('$id','$winTitle', '$title', '$language', '1', '$keywords', '$description', '$rewriteName')";
		commonDoQuery ($queryStr);
	}

	$domainRow  = commonGetDomainRow ();
	$domainName = commonGetDomainName ($domainRow);

	fopen("$domainName/updateModRewrite.php","r");

	return "<nadlanId>$nadlanId</nadlanId>";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteNadlan																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteNadlan ($xmlRequest)
{
	$id  = xmlParser_getValue ($xmlRequest, "nadlanId");

	if ($id == "")
		trigger_error ("חסר קוד לוח לביצוע הפעולה");

	// delete all nadlan items
	$queryStr =  "delete from nadlansItems where nadlanId=$id";
	commonDoQuery ($queryStr);

	$queryStr =  "delete from nadlans where id=$id";
	commonDoQuery ($queryStr);

	$queryStr =  "delete from pages where id=$id";
	commonDoQuery ($queryStr);

	$queryStr =  "delete from pages_byLang where pageId=$id";
	commonDoQuery ($queryStr);

	return "";
}


/* ----------------------------------------------------------------------------------------------------	*/
/* getNadlanItems																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getNadlanItems ($xmlRequest)
{	
	global $usedLangs;
	$langsArray = explode(",",$usedLangs);

	$today 		 = date("Y-m-d", strtotime("now"));

	$condition  = "";

	$nadlanId		= xmlParser_getValue($xmlRequest, "nadlanId");

	if ($nadlanId == "")
		return "<items></items>";

	$condition .= " and nadlanId = $nadlanId ";

	$status		= xmlParser_getValue($xmlRequest, "status");
	if ($status != "")
	{
		if ($status == "expire")
			$condition .= " and status = 'approved' and expireTime < '$today 00:00:00' ";
		else
			$condition .= " and status = '$status' ";
	}

	$contactFromBook = xmlParser_getValue($xmlRequest, "contactFromBook");
	if ($contactFromBook != "")
		$condition .= " and contactFromBook = '$contactFromBook' ";

	$nadlanType		= xmlParser_getValue($xmlRequest, "nadlanType");
	if ($nadlanType != "")
		$condition .= " and nadlanType = '$nadlanType' ";

	$cityId		= xmlParser_getValue($xmlRequest, "cityId");
	if ($cityId != "")
		$condition .= " and cityId = '$cityId' ";

	$contactName = addslashes(commonDecode(xmlParser_getValue($xmlRequest, "contactName")));
	if ($contactName != "")
		$condition .= " and (contactName like binary '%$contactName%' or 
			 				 phones.firstName like binary '%$contactName%' or 
							 phones.lastName like 'binary %$contactName%') ";

	$fromUpdateDate		= xmlParser_getValue($xmlRequest, "fromUpdateDate");
	if ($fromUpdateDate != "")
	{
		$fromUpdateDate = formatApplToDB ($fromUpdateDate . " 00:00:00");
		$condition .= " and updateTime >= '$fromUpdateDate' ";
	}

	$toUpdateDate		= xmlParser_getValue($xmlRequest, "toUpdateDate");
	if ($toUpdateDate != "")
	{
		$toUpdateDate = formatApplToDB ($toUpdateDate . " 00:00:00");
		$condition .= " and updateTime <= '$toUpdateDate' ";
	}

	$queryStr = "select * from nadlans, pages, pages_byLang
				 where nadlans.id = pages.id
				 and   nadlans.id = pages_byLang.pageId
				 and   nadlans.id = $nadlanId";
	$result   = commonDoQuery ($queryStr);
	$nadlanRow= commonQuery_fetchRow($result);

	// get total
	$queryStr	 = "select count(*) 
					from nadlansItems 
				 	left join phonesRecords_byLang phones on phones.recordId = nadlansItems.contactFromBook
					where 1 $condition";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$total	     = $row[0];

	// get details
	$queryStr = "select nadlansItems.*
				 from nadlansItems
				 left join phonesRecords_byLang phones on phones.recordId = nadlansItems.contactFromBook
				 where 1 $condition
				 order by id desc" . commonGetLimit ($xmlRequest);

	$result	     = commonDoQuery ($queryStr);

	$numRows    = commonQuery_numRows($result);

	$today 		 = strtotime(date("Y-m-d", strtotime("now")));

	$xmlResponse = "<items>";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		$id   		= $row['id'];
		$status 	= formatNadlanItemStatus($row['status']);

		if ($row['expireTime'] != "0000-00-00 00:00:00" && strtotime($row['expireTime']) < $today)
			$status = commonPhpEncode ("פג תוקף");

		if ($nadlanRow['nadlanType'] == 0)
			$nadlanType = commonValidXml($row['nadlanType']);
		else
			$nadlanType	= commonGetEnumValue($row['nadlanType']);

		$nadlanType	= commonGetEnumValue($row['nadlanType']);
		$itemType	= commonGetEnumValue($row['itemType']);
		$numRooms	= commonGetEnumValue($row['numRooms']);

		$insertTime = formatApplDate($row['insertTime']);
		$updateTime	= formatApplDate($row['updateTime']);
		$expireTime	= formatApplDate($row['expireTime']);

		$price		= str_replace("$", "", money_format("%.0n", $row['price']));
		$currency	= formatCurrency ($row['currency']);

		$price	   .= " " . $currency;

		if ($row['countContactPhone1'] == "") $row['countContactPhone1'] = "0";
		if ($row['countContactPhone2'] == "") $row['countContactPhone2'] = "0";

		$statistic  = commonPhpEncode("מונה צפיות: $row[countViews]<br/>הקלקות על טלפון 1: $row[countContactPhone1]<br/> הקלקות על טלפון 2: $row[countContactPhone2]");

		$xmlResponse .=	"<item>
							 <itemId>$id</itemId>
							 <status>$status</status>
							 <nadlanType>$nadlanType</nadlanType>
							 <itemType>$itemType</itemType>
							 <price>$price</price>
							 <numRooms>$numRooms</numRooms>
							 <insertTime>$insertTime</insertTime>
							 <updateTime>$updateTime</updateTime>
							 <expireTime>$expireTime</expireTime>
							 <statistic>$statistic</statistic>
						 </item>";
	}

	$xmlResponse .=	"</items>" .
					commonGetTotalXml($xmlRequest,$numRows,$total);
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getNadlanItemDetails																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function getNadlanItemDetails ($xmlRequest)
{
	global $itemTags;

	$xmlResponse = "";

	$id		= xmlParser_getValue($xmlRequest, "id");

	if ($id == "")
		trigger_error ("חסר קוד רשומה לביצוע הפעולה");


	$queryStr = "select * from nadlansItems
				 where nadlansItems.id = $id";
	$result   = commonDoQuery ($queryStr);

	if (commonQuery_numRows($result) == 0)
		trigger_error ("רשומה קוד זה ($id) לא קיים במערכת. לא ניתן לבצע את הפעולה");

	$row	  = commonQuery_fetchRow($result);

	for ($i=0; $i < count($itemTags); $i++)
	{
		eval ("\$$itemTags[$i] = \$row['$itemTags[$i]'];");
	}

	$insertTimeDB	= $insertTime;
	$insertTime 	= formatApplDate($insertTime);


	$updateTime 	= formatApplDate($updateTime);

	$deleteTimeDB	= $deleteTime;

	$deleteTime 	= formatApplDate($deleteTime);

	$evictionDate	= formatApplDate($evictionDate);
	$expireTime 	= formatApplDate($expireTime);

	for ($i=0; $i < count($itemTags); $i++)
	{
		eval ("\$$itemTags[$i] = commonValidXml(\$$itemTags[$i]);");

		eval ("\$xmlResponse .= \"<$itemTags[$i]>\$$itemTags[$i]</$itemTags[$i]>\";");
	}
		
	$xmlResponse .= "<itemId>$id</itemId>
					 <insertTimeDB>$insertTimeDB</insertTimeDB>
					 <deleteTimeDB>$deleteTimeDB</deleteTimeDB>
					 <countContactPhone1>$row[countContactPhone1]</countContactPhone1>
					 <countContactPhone2>$row[countContactPhone2]</countContactPhone2>";

	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getNadlanItemNextId																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function getNadlanItemNextId ()
{
	$queryStr	= "select max(id) from nadlansItems";
	$result		= commonDoQuery ($queryStr);
	$row		= commonQuery_fetchRow ($result);
	$id 		= $row[0] + 1;
	
	return "<itemId>$id</itemId>";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* addNadlanItem																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function addNadlanItem ($xmlRequest)
{
	return (editNadlanItem ($xmlRequest, "add"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* doesNadlanItemExist																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function doesNadlanItemExist ($id)
{
	$queryStr		= "select count(*) from nadlansItems where id=$id";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$count	     = $row[0];

	return ($count > 0);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateNadlanItem																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateNadlanItem ($xmlRequest)
{
	editNadlanItem ($xmlRequest, "update");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* editNadlanItem																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function editNadlanItem ($xmlRequest, $editType)
{
	global $usedLangs, $itemTags;

	for ($i=0; $i < count($itemTags); $i++)
	{
		eval ("\$$itemTags[$i] = addslashes(commonDecode(xmlParser_getValue(\$xmlRequest,\"$itemTags[$i]\")));");	
	}

	$id		= xmlParser_getValue($xmlRequest, "itemId");

	if ($editType == "update")
	{
		if (!doesNadlanItemExist($id))
		{
			trigger_error ("מודעת לוח עם קוד זה ($id) לא קיימת במערכת. לא ניתן לבצע את העדכון");
		}
	}
	else
	{
		$queryStr	= "select max(id) from nadlansItems";
		$result		= commonDoQuery ($queryStr);
		$row		= commonQuery_fetchRow ($result);
		$id 		= $row[0] + 1;
	}

	$insertTimeDB	= formatApplToDB(xmlParser_getValue($xmlRequest, "insertTime"));
	$deleteTimeDB	= formatApplToDB(xmlParser_getValue($xmlRequest, "deleteTime"));

	$callAgents = false;

	if ($status == "delete")
	{
		$deleteTime = date("Y-m-d 00:00:00");
	}
	else
	{
		$deleteTime	= $deleteTimeDB;
	}

	if ($editType == "update")
	{
		// check if we approve this item now
		if ($status == "approved")
		{
			$queryStr	= "select status from nadlansItems where id = $id";
			$result		= commonDoQuery($queryStr);
			$row		= commonQuery_fetchRow($result);

			if ($row['status'] != "approved")
			{
				$insertTimeDB = date("Y-m-d 00:00:00");
				$callAgents = true;
			}
		}

		$insertTime	= $insertTimeDB;

		$updateTime = date("Y-m-d 00:00:00");
	}

	if ($editType == "add")
	{
		$queryStr	= "select max(id) from nadlansItems";
		$result		= commonDoQuery ($queryStr);
		$row		= commonQuery_fetchRow ($result);
		$id 		= $row[0] + 1;

		$insertTime	= formatApplToDB($insertTime . " 00:00");
		$updateTime	= $insertTime;

		if ($status == "approved")
			$callAgents = true;
	}

	if ($evictionDate != "")
		$evictionDate	= formatApplToDB($evictionDate . " 00:00");

	if ($expireTime != "")
		$expireTime		= formatApplToDB($expireTime . " 00:00");

	$vals = Array();

	for ($i=0; $i < count($itemTags); $i++)
	{
		eval ("array_push (\$vals,\$$itemTags[$i]);");
	}
	
	if ($currency != "ILS")
	{
		$queryStr	= "select conversionRate from nadlans where id = $nadlanId";
		$result		= commonDoQuery($queryStr);
		$row		= commonQuery_fetchRow($result);

		$priceByRate= $price * $row['conversionRate'];
	}
	else
	{
		$priceByRate= $price;
	}

	if ($editType == "update")
	{
		// nadlans items table
		$queryStr = "update nadlansItems set ";

		for ($i=1; $i < count($itemTags); $i++)
		{
			$queryStr .= "$itemTags[$i] = '$vals[$i]',";
		}

		$queryStr .= "priceByRate = '$priceByRate' where id = $id";

		commonDoQuery ($queryStr);
	}
	else
	{
		$queryStr = "insert into nadlansItems (" . join(",",$itemTags) . ", priceByRate) values ('" . join("','",$vals) . "', $priceByRate)";
		commonDoQuery ($queryStr);
	}

	if ($callAgents)
	{
		$domainRow  = commonGetDomainRow ();
		$domainName = commonGetDomainName ($domainRow);
		@fopen ("$domainName/privateAgentSender.php?itemId=$id&itemType=nadlan", "r");
	}

	return "<itemId>$id</itemId>";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteNadlanItem																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteNadlanItem ($xmlRequest)
{
	$ids = xmlParser_getValues($xmlRequest, "itemId");

	if (count($ids) == 0)
		trigger_error ("חסר קוד רשומה לביצוע הפעולה");

	$idsStr = join(",", $ids);

	$files	  = array();

	// get items files
	$queryStr = "select file, fileType from nadlanItemsFiles where itemId in ($idsStr)";
	$result   = commonDoQuery($queryStr);
	while ($row = commonQuery_fetchRow($result))
	{
		array_push($files, $row);
	}

	$queryStr = "delete from nadlanItemsFiles where itemId in ($idsStr)";
	commonDoQuery ($queryStr);

	$queryStr = "delete from nadlansItems where id in ($idsStr)";
	commonDoQuery ($queryStr);

	// delete files from directory
	$domainRow 	= commonGetDomainRow ();
	$connId 	= commonFtpConnect($domainRow);
	
	foreach ($files as $fileRow)
	{
		$file = $fileRow['file'];

		commonFtpDelete($connId, "nadlansFiles/$file");

		if ($fileRow['fileType'] == "pic")
		{
			$file 		= str_replace("size0", "size1", $file);
			commonFtpDelete($connId, "nadlansFiles/$file");

			$file 		= str_replace("size1", "size2", $file);
			commonFtpDelete($connId, "nadlansFiles/$file");
		}
	}

	return "";	
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getNadlanFiles																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getNadlanFiles ($xmlRequest)
{
	$itemId  = xmlParser_getValue($xmlRequest, "itemId");
		
	if ($itemId == "")
		trigger_error ("חסר מזהה מודעה לביצוע הפעולה");

	// get total
	$queryStr	 = "select count(*) 
					from nadlanItemsFiles
					where itemId = $itemId";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$total	     = $row[0];

	// get details
	$queryStr    = "select *
					from nadlanItemsFiles
					where itemId = $itemId
					order by pos " . commonGetLimit ($xmlRequest);
	$result	     = commonDoQuery ($queryStr);

	$numRows    = commonQuery_numRows($result);

	$xmlResponse = "<items>";

	$domainRow   = commonGetDomainRow ();
	$filePrefix  = commonGetDomainName($domainRow) . "/nadlansFiles/";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		$id   		= $row['id'];
		$sourceFile	= commonValidXml ($row['sourceFile']);
		$fileSize	= $row['fileSize'];
		$pos		= $row['pos'];
		$fileType	= formatFileType  ($row['fileType']);

		$fullFileName = $filePrefix . urlencode($row['file']);
		$fullFileName = commonValidXml($fullFileName);

		$xmlResponse .=	"<item>
							<id>$id</id>
							<sourceFile>$sourceFile</sourceFile>
							<fileSize>$fileSize</fileSize>
							<fileType>$fileType</fileType>
							<pos>$pos</pos>
							<fullFileName>$fullFileName</fullFileName>
						</item>";
	}

	$xmlResponse .=	"</items>"												.
					commonGetTotalXml($xmlRequest,$numRows,$total);
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getFileDetails																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getFileDetails ($xmlRequest)
{
	global $usedLangs; 

	$id		= xmlParser_getValue($xmlRequest, "id");

	if ($id == "")
		trigger_error ("חסר קוד קובץ לביצוע הפעולה");

	$queryStr    = "select *
					from nadlanItemsFiles
					where id=$id";

	$result		= commonDoQuery ($queryStr);
	$row		= commonQuery_fetchRow($result);

	if (commonQuery_numRows($result) == 0)
		trigger_error ("מודעה קוד זה ($id) לא קיים במערכת. לא ניתן לבצע את הפעולה");

	$xmlResponse = "";

	$sourceFile	= commonValidXml ($row['sourceFile']);
	$fileSize	= $row['fileSize'];
	$pos		= $row['pos'];
	$fileType	= formatFileType  ($row['fileType']);

	$xmlResponse .= "<id>$id</id>
					 <sourceFile>$sourceFile</sourceFile>
					 <fileSize>$fileSize</fileSize>
					 <pos>$pos</pos>
					 <fileType>$fileType</fileType>";

	return $xmlResponse;
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateFile																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateFile ($xmlRequest)
{
	global $usedLangs; 

	$id  = xmlParser_getValue($xmlRequest, "id");

	if ($id == "")
		trigger_error ("חסר קוד קובץ לביצוע הפעולה");

	$itemId		 = xmlParser_getValue($xmlRequest, "itemId");
	if ($itemId == "")
		trigger_error ("חסר קוד מודעה לביצוע עדכון קובץ");

	$pos = xmlParser_getValue($xmlRequest, "pos");
	
	// get curr file pos
	$queryStr	= "select pos from nadlanItemsFiles where id=$id";
	$result		= commonDoQuery ($queryStr);
	$row		= commonQuery_fetchRow($result);
	$currPos	= $row[0];

	if ($currPos > $pos)
	{
		$queryStr = "update nadlanItemsFiles set pos = pos+1 where pos >= $pos and pos < $currPos and itemId = $itemId";
		commonDoQuery ($queryStr);
	}

	if ($currPos < $pos)
	{
		$queryStr = "update nadlanItemsFiles set pos = pos-1 where pos > $currPos and pos <= $pos and itemId = $itemId";
		commonDoQuery ($queryStr);
	}

	// update details
	if ($currPos != $pos)
	{
		$queryStr = "update nadlanItemsFiles set pos = $pos where id = $id";
		commonDoQuery ($queryStr);
	}

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteFile																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteFile ($xmlRequest)
{
	global $usedLangs; 

	$id  = xmlParser_getValue($xmlRequest, "id");

	$queryStr = "select file from nadlanItemsFiles where id = $id";
	$result	  = commonDoQuery($queryStr);
	$row	  = commonQuery_fetchRow($result);

	$queryStr = "delete from nadlanItemsFiles where id = $id";
	commonDoQuery ($queryStr);
	
	// delete file from directory
	$domainRow 	= commonGetDomainRow ();
	$connId 	= commonFtpConnect($domainRow);
	
	$file = $row['file'];
	commonFtpDelete($connId, "nadlansFiles/$file");

	$file 		= str_replace("size0", "size1", $file);
	commonFtpDelete($connId, "nadlansFiles/$file");

	$file 		= str_replace("size1", "size2", $file);
	commonFtpDelete($connId, "nadlansFiles/$file");

	// TBD - delete file from directory
}

?>
