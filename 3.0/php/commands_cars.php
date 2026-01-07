<?php

$pageTags 		= Array("id", "layoutId");

$carTags		= Array("id", "conversionRate", "confirmType", "hideEmail", "confirmPageId", "confirmDeletePageId", "errorPageId", "sendInformEmail",
						"numItemsInPage", "numPages", "numItemsBeforeBanner", "maxBannersInPage", "bannerWidth", "bannerHeight",
						"picDimension1", "picDimension2", "engineVolume");

$itemTags     	= Array("id", "carsTabletId", "code", "status", "verifyCode", "memberId", "isSelected", "boldLevel", "insertTime", "updateTime", 
						"expireTime", "deleteTime", "deleteReason", "hasAssessment", "assessmentPrice",
						"carType", "manufacturer", "model", "modelText", "engineVolume", "carYear",
						"price", "salePrice", "currency", "saleCurrency", "saleByBroker", 
						"description", "remarks", 
						"contactFromBook", "contactName", "contactPhone1", "contactPhone2", "contactEmail", 
						"contactCityId", "contactAddress", "contactSiteUrl", "hideEmail");

/* ----------------------------------------------------------------------------------------------------	*/
/* getCars																							 	*/
/* ----------------------------------------------------------------------------------------------------	*/
function getCars ($xmlRequest)
{	
	global $usedLangs;
	$langsArray = explode(",",$usedLangs);

	$condition  = "";

	// get total
	$queryStr	 = "select count(*) from cars";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$total	     = $row[0];

	// get details
	$queryStr = "select cars.id, pages_byLang.title
				 from cars, pages_byLang 
			     where cars.id = pages_byLang.pageId and language = '$langsArray[0]' 
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
/* getCarDetails																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getCarDetails ($xmlRequest)
{
	global $usedLangs, $carTags, $carLangTags;

	$id		= xmlParser_getValue($xmlRequest, "carsTabletId");

	if ($id == "")
		trigger_error ("חסר קוד לוח לביצוע הפעולה");


	$queryStr = "select * from cars, pages, pages_byLang
				 where cars.id = pages.id
				 and   cars.id = pages_byLang.pageId
				 and   cars.id = $id";
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
			for ($i=0; $i < count($carTags); $i++)
			{
				eval ("\$$carTags[$i] = \$row['$carTags[$i]'];");

			}

			for ($i=0; $i < count($carTags); $i++)
			{
				eval ("\$$carTags[$i] = commonValidXml(\$$carTags[$i]);");

				eval ("\$xmlResponse .= \"<$carTags[$i]>\$$carTags[$i]</$carTags[$i]>\";");
			}
			
			$xmlResponse .= "<carsTabletId>$id</carsTabletId>
							 <layoutId>$row[layoutId]</layoutId>
							 <oldConversionRate>$row[conversionRate]</oldConversionRate>";
		}

		for ($i=0; $i < count($carLangTags); $i++)
		{
			eval ("\$$carLangTags[$i] = commonValidXml(\$row['$carLangTags[$i]']);");
			eval ("\$xmlResponse .=	\"<$carLangTags[$i]\$language>\$$carLangTags[$i]</$carLangTags[$i]\$language>\";");
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

		for ($j=0; $j < count($carLangTags); $j++)
		{
			eval ("\$xmlResponse .=	\"<$carLangTags[$j]\$language><![CDATA[]]></$carLangTags[$j]\$language>\";");
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
/* addCar																								*/
/* ----------------------------------------------------------------------------------------------------	*/
function addCar ($xmlRequest)
{
	return (editCar ($xmlRequest, "add"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* doesCarExist																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function doesCarExist ($id)
{
	$queryStr		= "select count(*) from cars where id=$id";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$count	     = $row[0];

	return ($count > 0);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getCarNextId																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function getCarNextId ()
{
	$queryStr	= "select max(id) from pages";
	$result		= commonDoQuery ($queryStr);
	$row		= commonQuery_fetchRow ($result);
	$id 		= $row[0] + 1;
	
	return $id;
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateCar																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateCar ($xmlRequest)
{
	editCar ($xmlRequest, "update");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* editCar																								*/
/* ----------------------------------------------------------------------------------------------------	*/
function editCar ($xmlRequest, $editType)
{
	global $usedLangs, $carTags, $pageTags;

	for ($i=0; $i < count($pageTags); $i++)
	{
		eval ("\$$pageTags[$i] = commonDecode(xmlParser_getValue(\$xmlRequest,\"$pageTags[$i]\"));");	
	}

	for ($i=0; $i < count($carTags); $i++)
	{
		eval ("\$$carTags[$i] = commonDecode(xmlParser_getValue(\$xmlRequest,\"$carTags[$i]\"));");	
	}

	$id		= xmlParser_getValue($xmlRequest, "carsTabletId");

	if ($editType == "update")
	{
		if (!doesCarExist($id))
		{
			trigger_error ("לוח עם קוד זה ($id) לא קיימת במערכת. לא ניתן לבצע את העדכון");
		}
	}
	else
	{
		$id = getCarNextId ();
	}

	$pageVals = Array();

	for ($i=0; $i < count($pageTags); $i++)
	{
		eval ("array_push (\$pageVals,\$$pageTags[$i]);");
	}
	
	$vals = Array();

	for ($i=0; $i < count($carTags); $i++)
	{
		eval ("array_push (\$vals,\$$carTags[$i]);");
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
			$queryStr	= "update carsItems set priceByRate = price * $conversionRate
						   where currency != 'ILS' and status not in ('deleted', 'rejected')";

			commonDoQuery ($queryStr);
		}

		// cars table
		$queryStr = "update cars set ";

		for ($i=1; $i < count($carTags); $i++)
		{
			$queryStr .= "$carTags[$i] = '$vals[$i]',";
		}

		$queryStr = trim($queryStr, ",");

		$queryStr .= " where id = $id ";

		commonDoQuery ($queryStr);
	}
	else
	{
		$queryStr = "insert into pages (" . join(",",$pageTags) . ",type) values ('" . join("','",$pageVals) . "','car')";
		commonDoQuery ($queryStr);

		$queryStr = "insert into cars (" . join(",",$carTags) . ",insertTime) values ('" . join("','",$vals) . "',now())";
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

		$title = addslashes(commonDecode(xmlParser_getValue($xmlRequest, "title$language")));
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

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteCar																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteCar ($xmlRequest)
{
	$id  = xmlParser_getValue ($xmlRequest, "carsTabletId");

	if ($id == "")
		trigger_error ("חסר קוד לוח לביצוע הפעולה");

	// delete all car items
	$queryStr =  "delete from carsItems where carsTabletId=$id";
	commonDoQuery ($queryStr);

	$queryStr =  "delete from cars where id=$id";
	commonDoQuery ($queryStr);

	$queryStr =  "delete from pages where id=$id";
	commonDoQuery ($queryStr);

	$queryStr =  "delete from pages_byLang where pageId=$id";
	commonDoQuery ($queryStr);

	return "";
}


/* ----------------------------------------------------------------------------------------------------	*/
/* getCarItems																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function getCarItems ($xmlRequest)
{	
	global $usedLangs;
	$langsArray = explode(",",$usedLangs);

	$today 		 = date("Y-m-d", strtotime("now"));

	$condition  = "";

	$carsTabletId		= xmlParser_getValue($xmlRequest, "carsTabletId");

	if ($carsTabletId == "")
		return "<items></items>";

	$condition .= " and carsTabletId = $carsTabletId ";

	$status		= xmlParser_getValue($xmlRequest, "status");
	if ($status != "")
	{
		if ($status == "expire")
			$condition .= " and status = 'approved' and expireTime < '$today 00:00:00' ";
		else
			$condition .= " and status = '$status' ";
	}

	$carType		= xmlParser_getValue($xmlRequest, "carType");
	if ($carType != "")
		$condition .= " and carType = '$carType' ";

	$areaId		= xmlParser_getValue($xmlRequest, "areaId");
	if ($areaId != "")
		$condition .= " and cities.areaId = '$areaId' ";

	$cityId		= xmlParser_getValue($xmlRequest, "cityId");
	if ($cityId != "")
		$condition .= " and contactCityId = '$cityId' ";

	$contactName = addslashes(commonDecode(xmlParser_getValue($xmlRequest, "contactName")));
	if ($contactName != "")
		$condition .= " and (contactName like binary '%$contactName%' or 
			 				 phones.firstName like binary '%$contactName%' or 
							 phones.lastName like 'binary %$contactName%') ";

	$anyText = addslashes(commonDecode(xmlParser_getValue($xmlRequest, "anyText")));
	if ($anyText != "")
		$condition .= " and (carsItems.description 	  like binary '%$anyText%' 	 or 
							 carsItems.remarks 		  like binary '%$anyText%' 	 or 
							 carsItems.contactAddress like binary '%$anyText%') ";
	// get total
	$queryStr	 = "select count(*) 
					from carsItems 
					left join cities on contactCityId = cities.id
				 	left join phonesRecords_byLang phones on phones.recordId = carsItems.contactFromBook
					where 1 $condition";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$total	     = $row[0];

	// get details
	$queryStr = "select carsItems.*
				 from carsItems 
				 left join cities on contactCityId = cities.id
				 left join phonesRecords_byLang phones on phones.recordId = carsItems.contactFromBook
				 where 1 $condition
				 order by id desc" . commonGetLimit ($xmlRequest);

	$result	     = commonDoQuery ($queryStr);

	$numRows     = commonQuery_numRows($result);
	
	$today 		 = strtotime(date("Y-m-d", strtotime("now")));

	$xmlResponse = "<items>";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		$id   			= $row['id'];
		$status 		= commonPhpEncode(formatTabletItemStatus($row['status']));

		if ($row['expireTime'] != "0000-00-00 00:00:00" && strtotime($row['expireTime']) < $today)
			$status = commonPhpEncode ("פג תוקף");

		$carType		= commonValidXml($row['carType']);
		$manufacturer	= commonValidXml($row['manufacturer']);
		$model			= commonValidXml($row['model']);

		if ($row['model'] == "")
			$model		= commonValidXml($row['modelText']);

		$insertTime = formatApplDate($row['insertTime']);
		$updateTime	= formatApplDate($row['updateTime']);
		$expireTime	= formatApplDate($row['expireTime']);

		$price		= str_replace("$", "", money_format("%.0n", $row['price']));
		$currency	= formatCurrency ($row['currency']);

		$price	   .= " " . $currency;

		$statistic  = commonPhpEncode("הקלקות על טלפון 1: $row[countContactPhone1]\n הקלקות על טלפון 2: $row[countContactPhone2]");

		$xmlResponse .=	"<item>
							 <itemId>$id</itemId>
							 <status>$status</status>
							 <carType>$carType</carType>
							 <manufacturer>$manufacturer</manufacturer>
							 <price>$price</price>
							 <model>$model</model>
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
/* getCarItemDetails																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function getCarItemDetails ($xmlRequest)
{
	global $itemTags;

	$xmlResponse = "";

	$id		= xmlParser_getValue($xmlRequest, "id");

	if ($id == "")
		trigger_error ("חסר קוד רשומה לביצוע הפעולה");


	$queryStr = "select * from carsItems
				 where carsItems.id = $id";
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
/* addCarItem																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function addCarItem ($xmlRequest)
{
	return (editCarItem ($xmlRequest, "add"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* doesCarItemExist																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function doesCarItemExist ($id)
{
	$queryStr		= "select count(*) from carsItems where id=$id";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$count	     = $row[0];

	return ($count > 0);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateCarItem																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateCarItem ($xmlRequest)
{
	editCarItem ($xmlRequest, "update");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* editCarItem																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function editCarItem ($xmlRequest, $editType)
{
	global $usedLangs, $itemTags;

	for ($i=0; $i < count($itemTags); $i++)
	{
		eval ("\$$itemTags[$i] = addslashes(commonDecode(xmlParser_getValue(\$xmlRequest,\"$itemTags[$i]\")));");	
	}

	$id		= xmlParser_getValue($xmlRequest, "itemId");

	if ($editType == "update")
	{
		if (!doesCarItemExist($id))
		{
			trigger_error ("מודעת לוח עם קוד זה ($id) לא קיימת במערכת. לא ניתן לבצע את העדכון");
		}
	}
	else
	{
		$queryStr	= "select max(id) from carsItems";
		$result		= commonDoQuery ($queryStr);
		$row		= commonQuery_fetchRow ($result);
		$id 		= $row[0] + 1;
	}

	$insertTimeDB	= formatApplToDB(xmlParser_getValue($xmlRequest, "insertTime"));
	$deleteTimeDB	= formatApplToDB(xmlParser_getValue($xmlRequest, "deleteTime"));

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
				$insertTimeDB = date("Y-m-d 00:00:00");
		}

		$insertTime	= $insertTimeDB;

		if ($updateTime == "")
			$updateTime = $insertTimeDB;
		else
			$updateTime = date("Y-m-d 00:00:00");
	}

	if ($editType == "add")
	{
		$queryStr	= "select max(id) from carsItems";
		$result		= commonDoQuery ($queryStr);
		$row		= commonQuery_fetchRow ($result);
		$id 		= $row[0] + 1;

		$insertTime	= formatApplToDB($insertTime . " 00:00");
		$updateTime	= $insertTime;
	}

	if ($expireTime != "")
		$expireTime		= formatApplToDB($expireTime . " 00:00");

	$vals = Array();

	for ($i=0; $i < count($itemTags); $i++)
	{
		eval ("array_push (\$vals,\$$itemTags[$i]);");
	}
	
	if ($editType == "update")
	{
		// cars items table
		$queryStr = "update carsItems set ";

		for ($i=1; $i < count($itemTags); $i++)
		{
			$queryStr .= "$itemTags[$i] = '$vals[$i]',";
		}

		$queryStr = trim($queryStr, ",");

		$queryStr .= " where id = $id ";

		commonDoQuery ($queryStr);
	}
	else
	{
		if ($currency != "ILS")
		{
			$queryStr	= "select conversionRate from cars where id = $carsTabletId";
			$result		= commonDoQuery($queryStr);
			$row		= commonQuery_fetchRow($result);

			$priceByRate= $price * $row['conversionRate'];
		}
		else
		{
			$priceByRate= $price;
		}

		$queryStr = "insert into carsItems (" . join(",",$itemTags) . ", priceByRate) values ('" . join("','",$vals) . "', $priceByRate)";
		commonDoQuery ($queryStr);
	}

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteCarItem																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteCarItem ($xmlRequest)
{
	$ids = xmlParser_getValues($xmlRequest, "itemId");

	if (count($ids) == 0)
		trigger_error ("חסר קוד רשומה לביצוע הפעולה");

	$idsStr = join(",", $ids);

	$files	  = array();

	// delete item files
	$queryStr = "select file from carItemsFiles where itemId in ($idsStr)";
	$result   = commonDoQuery($queryStr);
	while ($row = commonQuery_fetchRow($result))
	{
		array_push($files, $row['file']);
	}

	$queryStr = "delete from carItemsFiles where itemId in ($idsStr)";
	commonDoQuery ($queryStr);

	$queryStr = "delete from carsItems where id in ($idsStr)";
	commonDoQuery ($queryStr);

	// delete files from directory
	$domainRow 	= commonGetDomainRow ();
	$connId 	= commonFtpConnect($domainRow);
	
	foreach ($files as $file)
	{
		commonFtpDelete($connId, "carsFiles/$file");

		$file 		= str_replace("size0", "size1", $file);
		commonFtpDelete($connId, "carsFiles/$file");

		$file 		= str_replace("size1", "size2", $file);
		commonFtpDelete($connId, "carsFiles/$file");
	}

	return "";	
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getCarFiles																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function getCarFiles ($xmlRequest)
{
	$itemId  = xmlParser_getValue($xmlRequest, "itemId");
		
	if ($itemId == "")
		trigger_error ("חסר מזהה מודעה לביצוע הפעולה");

	// get total
	$queryStr	 = "select count(*) 
					from carItemsFiles
					where itemId = $itemId";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$total	     = $row[0];

	// get details
	$queryStr    = "select *
					from carItemsFiles
					where itemId = $itemId
					order by pos " . commonGetLimit ($xmlRequest);
	$result	     = commonDoQuery ($queryStr);

	$numRows    = commonQuery_numRows($result);

	$xmlResponse = "<items>";

	$domainRow   = commonGetDomainRow ();
	$filePrefix  = commonGetDomainName($domainRow) . "/carsFiles/";

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
					from carItemsFiles
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
	$queryStr	= "select pos from carItemsFiles where id=$id";
	$result		= commonDoQuery ($queryStr);
	$row		= commonQuery_fetchRow($result);
	$currPos	= $row[0];

	if ($currPos > $pos)
	{
		$queryStr = "update carItemsFiles set pos = pos+1 where pos >= $pos and pos < $currPos and itemId = $itemId";
		commonDoQuery ($queryStr);
	}

	if ($currPos < $pos)
	{
		$queryStr = "update carItemsFiles set pos = pos-1 where pos > $currPos and pos <= $pos and itemId = $itemId";
		commonDoQuery ($queryStr);
	}

	// update details
	if ($currPos != $pos)
	{
		$queryStr = "update carItemsFiles set pos = $pos where id = $id";
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

	$queryStr = "select file from carItemsFiles where id = $id";
	$result	  = commonDoQuery($queryStr);
	$row	  = commonQuery_fetchRow($result);

	$queryStr = "delete from carItemsFiles where id = $id";
	commonDoQuery ($queryStr);
	
	// delete file from directory
	$domainRow 	= commonGetDomainRow ();
	$connId 	= commonFtpConnect($domainRow);
	
	$file = $row['file'];
	commonFtpDelete($connId, "carsFiles/$file");

	$file 		= str_replace("size0", "size1", $file);
	commonFtpDelete($connId, "carsFiles/$file");

	$file 		= str_replace("size1", "size2", $file);
	commonFtpDelete($connId, "carsFiles/$file");

	// TBD - delete file from directory
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getCarTypes																						 	*/
/* ----------------------------------------------------------------------------------------------------	*/
function getCarTypes ($xmlRequest)
{	
	global $usedLangs;
	$langsArray = explode(",",$usedLangs);

	$condition  = "";

	// get total
	$queryStr	 = "select count(*) from carTypes";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$total	     = $row[0];

	// get details
	$queryStr = "select *
				 from carTypes 
			     where 1
				 order by type, manufacturer, model " . commonGetLimit ($xmlRequest);

	$result	     = commonDoQuery ($queryStr);

	$numRows    = commonQuery_numRows($result);

	$xmlResponse = "<items>";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		$id   			= $row['id'];
		$type			= commonValidXml($row['type']);
		$manufacturer	= commonValidXml($row['manufacturer']);
		$model			= commonValidXml($row['model']);

		$xmlResponse .=	"<item>
							 <id>$id</id>
							 <type>$type</type>
							 <manufacturer>$manufacturer</manufacturer>
							 <model>$model</model>
						 </item>";
	}

	$xmlResponse .=	"</items>" .
					commonGetTotalXml($xmlRequest,$numRows,$total);
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getTypes																							 	*/
/* ----------------------------------------------------------------------------------------------------	*/
function getTypes ($xmlRequest)
{	
	global $usedLangs;
	$langsArray = explode(",",$usedLangs);

	$queryStr	 = "select distinct type from carTypes";
	$result	     = commonDoQuery ($queryStr);

	$numRows    = commonQuery_numRows($result);

	$xmlResponse = "<items>";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		$type			= commonValidXml($row['type']);

		$xmlResponse .=	"<item>
							 <type>$type</type>
						 </item>";
	}

	$xmlResponse .=	"</items>";
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* uploadCarTypesFile																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function uploadCarTypesFile ($xmlRequest)
{
	global $userId;
	global $ibosHomeDir;

	$file		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "file")));	

	// check suffix
	$suffix	= commonFileSuffix ($file);

	if ($suffix != ".csv")
		trigger_error ("ניתן לטעון רק קבצי CSV");

	$filePath 	= "$ibosHomeDir/html/SWFUpload/files/$userId";

	$f = fopen("$filePath/$file", "r");

	$queryStr	= "delete from carTypes";
	commonDoQuery ($queryStr);

	$row		 = 0;
	while ($line = fgets($f)) 
	{ 
		$row++;

		$splitLine = explode(",", $line);

		$type			= commonPrepareToDB(addslashes(trim($splitLine[0])));
		$manufacturer	= commonPrepareToDB(addslashes(trim($splitLine[1])));

		$model			= "";

		if (count($splitLine) > 2)
			$model		= commonPrepareToDB(addslashes(trim($splitLine[2])));

		$queryStr = "insert into carTypes (id, type, manufacturer, model) values ($row, '$type', '$manufacturer', '$model')";
		commonDoQuery ($queryStr);
	}

	fclose ($f);

	unlink ("$filePath/$file");

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getManufacturers																					 	*/
/* ----------------------------------------------------------------------------------------------------	*/
function getManufacturers ($xmlRequest)
{	
	global $usedLangs;
	$langsArray = explode(",",$usedLangs);

	$carType = addslashes(commonDecode(xmlParser_getValue($xmlRequest, "carType")));

	$queryStr	 = "select distinct manufacturer from carTypes where type = '$carType'";
	$result	     = commonDoQuery ($queryStr);

	$numRows    = commonQuery_numRows($result);

	$xmlResponse = "<items>";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		$manufacturer	= commonValidXml($row['manufacturer']);

		$xmlResponse .=	"<item>
							 <manufacturer>$manufacturer</manufacturer>
						 </item>";
	}

	$xmlResponse .=	"</items>";
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getModels																						 	*/
/* ----------------------------------------------------------------------------------------------------	*/
function getModels ($xmlRequest)
{	
	global $usedLangs;
	$langsArray = explode(",",$usedLangs);

	$manufacturer = addslashes(commonDecode(xmlParser_getValue($xmlRequest, "manufacturer")));

	$queryStr	 = "select distinct model from carTypes where manufacturer = '$manufacturer'";
	$result	     = commonDoQuery ($queryStr);

	$numRows    = commonQuery_numRows($result);

	$xmlResponse = "<items>";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		$model	= commonValidXml($row['model']);

		$xmlResponse .=	"<item>
							 <model>$model</model>
						 </item>";
	}

	$xmlResponse .=	"</items>";
	
	return ($xmlResponse);
}

?>
