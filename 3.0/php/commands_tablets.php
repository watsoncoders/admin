<?php

$tabletTags	    = Array("id", "status", "confirmType", "confirmPageId", "confirmDeletePageId", "errorPageId", "maxDays",
						"primarySortBy", "secondarySortBy", "hideMsgEmail", 
						"extraData1", "extraData2", "extraData3", "extraData4", "extraData5", "extraData6", "extraData7", "extraData8", 
						"extraData9", "extraData10",
						"enumId1", "enumId2", "enumId3", "enumId4", "enumId5", "enumId6", "enumId7", "enumId8", "enumId9", "enumId10",
						"numItemsInPage", "numPages", "numItemsBeforeBanner", "maxBannersInPage", "bannerWidth", "bannerHeight", 
						"picDimension1", "picDimension2");
$tabletLangTags = Array("name");

$itemTags		= Array("id", "tabletId", "status", "isSelected", "code", "startPublishDate", "endPublishDate", "areaId", "cityId",
						"contactEmail", "siteUrl", "contactPhone1", "contactPhone2", "fax", "expireDays", "enumId1", "enumId2", "enumId3", "enumId4", 
						"enumValue1", "enumValue2", "enumValue3", "enumValue4", "enumValue5", "enumValue6", "enumValue7", "enumValue8",
						"enumValue9", "enumValue10");
$itemLangTags	= Array("title", "txt", "address", "contactName", "role", "price", "remarks", "extraData1", "extraData2", "extraData3", "extraData4",
						"extraData5", "extraData6", "extraData7", "extraData8", "extraData9", "extraData10");

/* ----------------------------------------------------------------------------------------------------	*/
/* getTablets																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function getTablets ($xmlRequest)
{
	global $usedLangs;
	$langsArray = explode(",",$usedLangs);

	// get total
	$queryStr	 = "select count(*) 
					from tablets, tablets_byLang
					where tablets.id = tablets_byLang.tabletId and tablets_byLang.language = '$langsArray[0]'";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$total	     = $row[0];

	// get details
	$queryStr    = "select tablets.*, tablets_byLang.*, count(tabletItems.id) as countItems
					from tablets
					left join tablets_byLang on tablets.id = tablets_byLang.tabletId and tablets_byLang.language  = '$langsArray[0]'
					left join tabletItems on tabletItems.tabletId = tablets.id
					group by tablets.id
					order by tablets.id desc " . commonGetLimit ($xmlRequest);
	$result	     = commonDoQuery ($queryStr);

	$numRows    = commonQuery_numRows($result);

	$xmlResponse = "<items>";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		$id   			= $row['id'];
		$name			= commonValidXml 	 ($row['name']);
//		$type			= formatTabletType   ($row['type']);
		$status			= formatActiveStatus ($row['status']);
		$countItems		= $row['countItems'];

		$xmlResponse .=	"<item>
							<tabletId>$id</tabletId>
							<name>$name</name>
							<status>$status</status>
							<countItems>$countItems</countItems>
						 </item>";
	}

	$xmlResponse .=	"</items>"												.
					commonGetTotalXml($xmlRequest,$numRows,$total);
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getTabletDetails																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getTabletDetails ($xmlRequest)
{
	global $usedLangs, $tabletTags, $tabletLangTags;

	$id		= xmlParser_getValue($xmlRequest, "tabletId");

	if ($id == "")
		trigger_error ("חסר קוד לוח לביצוע הפעולה");

	$queryStr    = "select tablets.*, tablets_byLang.*
					from tablets, tablets_byLang
					where tablets.id = tablets_byLang.tabletId
				    and tablets.id=$id";

	$result		= commonDoQuery ($queryStr);

	if (commonQuery_numRows($result) == 0)
		trigger_error ("לוח קוד זה ($id) לא קיים במערכת. לא ניתן לבצע את הפעולה");

	$langsArray = explode(",",$usedLangs);

	$xmlResponse = "";

	while ($row = commonQuery_fetchRow($result))
	{
		$language = $row['language'];

		$row['maxDays'] 	   	 = ($row['maxDays'] 		 == 0) ? "" : $row['maxDays'];
		$row['numItemsInPage'] 	 = ($row['numItemsInPage']   == 0) ? "" : $row['numItemsInPage'];
		$row['maxBannersInPage'] = ($row['maxBannersInPage'] == 0) ? "" : $row['maxBannersInPage'];

		$langsArray = commonArrayRemove ($langsArray, $language);	

		if ($xmlResponse == "")
		{
			for ($i=0; $i < count($tabletTags); $i++)
			{
				eval ("\$$tabletTags[$i] = \$row['$tabletTags[$i]'];");

				eval ("\$$tabletTags[$i] = commonValidXml(\$$tabletTags[$i]);");

				eval ("\$xmlResponse .= \"<$tabletTags[$i]>\$$tabletTags[$i]</$tabletTags[$i]>\";");
			}

			$xmlResponse .= "<tabletId>$id</tabletId>";
		}

		for ($i=0; $i < count($tabletLangTags); $i++)
		{
			eval ("\$$tabletLangTags[$i] = commonValidXml(\$row['$tabletLangTags[$i]']);");
			eval ("\$xmlResponse .=	\"<$tabletLangTags[$i]\$language>\$$tabletLangTags[$i]</$tabletLangTags[$i]\$language>\";");
		}
	}

	// add missing languages
	// ------------------------------------------------------------------------------------------------
	for ($i=0; $i<count($langsArray); $i++)
	{
		$language	  = $langsArray[$i];

		for ($j=0; $j < count($tabletLangTags); $j++)
		{
			eval ("\$xmlResponse .=	\"<$tabletLangTags[$j]\$language><![CDATA[]]></$tabletLangTags[$j]\$language>\";");
		}
	}

	return $xmlResponse;
}

/* ----------------------------------------------------------------------------------------------------	*/
/* addTablet																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function addTablet ($xmlRequest)
{
	return (editTablet ($xmlRequest, "add"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateTablet																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateTablet ($xmlRequest)
{
	return (editTablet ($xmlRequest, "update"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* doesTabletExist																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function doesTabletExist ($id)
{
	$queryStr		= "select count(*) from tablets where id=$id";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$count	     = $row[0];

	return ($count > 0);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* editTablet																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function editTablet ($xmlRequest, $editType)
{
	global $usedLangs, $tabletTags, $tabletLangTags;

	for ($i=0; $i < count($tabletTags); $i++)
	{
		eval ("\$$tabletTags[$i] = commonDecode(xmlParser_getValue(\$xmlRequest,\"$tabletTags[$i]\"));");	
	}

	if ($editType == "add")
	{
		$queryStr	= "select max(id) from tablets";
		$result		= commonDoQuery ($queryStr);
		$row		= commonQuery_fetchRow ($result);
		$id 		= $row[0] + 1;
	}
	else
	{
		$id = xmlParser_getValue($xmlRequest, "tabletId");
	}

	$vals = Array();

	for ($i=0; $i < count($tabletTags); $i++)
	{
		eval ("array_push (\$vals,\$$tabletTags[$i]);");
	}
	
	if ($editType == "update")
	{
		if ($id == "")
			trigger_error ("חסר קוד לוח לביצוע הפעולה");

		if (!doesTabletExist($id))
		{
			trigger_error ("לוח עם קוד זה ($id) לא קיים במערכת. לא ניתן לבצע את העדכון");
		}
		
		$queryStr = "update tablets set ";

		for ($i=1; $i < count($tabletTags); $i++)
		{
			$queryStr .= "$tabletTags[$i] = '$vals[$i]',";
		}

		$queryStr = trim($queryStr, ",");

		$queryStr .= " where id = $id ";

		commonDoQuery ($queryStr);
	}
	else
	{
		$queryStr = "insert into tablets (" . join(",",$tabletTags) . ",insertTime) values ('" . join("','",$vals) . "',now())";
		commonDoQuery ($queryStr);
	}

	# delete all languages rows
	# ------------------------------------------------------------------------------------------------------
	$queryStr = "delete from tablets_byLang where tabletId='$id'";
	commonDoQuery ($queryStr);
	
	# add languages rows for this user
	# ------------------------------------------------------------------------------------------------------
	$langsArray = explode(",",$usedLangs);

	for ($i=0; $i<count($langsArray); $i++)
	{
		$language		= $langsArray[$i];

		$vals = Array();
		for ($j=0; $j < count($tabletLangTags); $j++)
		{
			eval ("\$$tabletLangTags[$j] = addslashes(commonDecode(xmlParser_getValue(\$xmlRequest,\"$tabletLangTags[$j]\$language\")));");	
			eval ("array_push (\$vals,\$$tabletLangTags[$j]);");
		}		

		$queryStr		= "insert into tablets_byLang (tabletId, language," . join(",",$tabletLangTags) . ") 
						   values ($id, '$language', '" . join ("','", $vals) . "')";
	
		commonDoQuery ($queryStr);
	}
	return "<tabletId>$id</tabletId>";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteTablet																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteTablet ($xmlRequest)
{
	$id = xmlParser_getValue ($xmlRequest, "tabletId");

	if ($id == "")
		trigger_error ("חסר קוד לוח לביצוע הפעולה");

	$queryStr = "delete from tablets where id = $id";
	commonDoQuery ($queryStr);

	$queryStr = "delete from tablets_byLang where tabletId = $id";
	commonDoQuery ($queryStr);

	$queryStr = "delete from tabletItems_byLang where itemId in (select id from tabletItems where tabletId = $id)";
	commonDoQuery ($queryStr);

	$queryStr = "delete from tabletItems where tabletId = $id";
	commonDoQuery ($queryStr);

	return "";
}

// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
// Tablet's Items
// ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

/* ----------------------------------------------------------------------------------------------------	*/
/* getTabletItems																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getTabletItems ($xmlRequest)
{
	global $usedLangs;
	$langsArray = explode(",",$usedLangs);

	// conditions
	$condition  = "";

	// - tablet id
	$tabletId	= xmlParser_getValue($xmlRequest, "tabletId");
	if ($tabletId != "")
		$condition = " and tabletItems.tabletId = $tabletId";

	// - status
	$status	= xmlParser_getValue($xmlRequest, "status");
	if ($status != "")
		$condition .= " and tabletItems.status = '$status' ";

	// - id
	$id	= xmlParser_getValue($xmlRequest, "itemId");
	if ($id != "")
		$condition .= " and tabletItems.id = $id ";


	// get total
	$queryStr	 = "select count(*) 
					from tabletItems, tabletItems_byLang
					where tabletItems.id = tabletItems_byLang.itemId and tabletItems_byLang.language = '$langsArray[0]'
					$condition";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$total	     = $row[0];

	// get details
	$queryStr    = "select *
					from tabletItems, tabletItems_byLang
					where tabletItems.id = tabletItems_byLang.itemId and tabletItems_byLang.language = '$langsArray[0]'
					$condition
					order by tabletItems.insertTime desc " . commonGetLimit ($xmlRequest);
	$result	     = commonDoQuery ($queryStr);

	$numRows    = commonQuery_numRows($result);

	$xmlResponse = "<items>";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		$id   			= $row['id'];
		$status			= commonPhpEncode(formatTabletItemStatus ($row['status']));
		$insertTime		= formatApplDateTime	 ($row['insertTime']);

		$title			= $row['title'];
		if ($title == "")
			$title = commonCutText(strip_tags($row['txt']),65);

		$title			= commonValidXml 	 	 ($title);
		$xmlResponse .=	"<item>
							<itemId>$id</itemId>
							<title>$title</title>
							<status>$status</status>
							<insertTime>$insertTime</insertTime>
						 </item>";
	}

	$xmlResponse .=	"</items>"												.
					commonGetTotalXml($xmlRequest,$numRows,$total);
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getTabletItemDetails																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function getTabletItemDetails ($xmlRequest)
{
	global $usedLangs, $itemTags, $itemLangTags;

	$id		= xmlParser_getValue($xmlRequest, "itemId");

	if ($id == "")
		trigger_error ("חסר קוד הודעה לביצוע הפעולה");

	$queryStr    = "select *
					from tabletItems, tabletItems_byLang
					where tabletItems.id = tabletItems_byLang.itemId
				    and tabletItems.id=$id";

	$result		= commonDoQuery ($queryStr);

	if (commonQuery_numRows($result) == 0)
		trigger_error ("הודעה קוד זה ($id) לא קיימת במערכת. לא ניתן לבצע את הפעולה");

	$langsArray = explode(",",$usedLangs);

	$xmlResponse = "";

	while ($row = commonQuery_fetchRow($result))
	{
		$language = $row['language'];

		$langsArray = commonArrayRemove ($langsArray, $language);	

		if ($xmlResponse == "")
		{
			$row['startPublishDate'] = formatApplDate($row['startPublishDate']);
			$row['endPublishDate']   = formatApplDate($row['endPublishDate']);
			if ($row['expireDays'] == "0") $row['expireDays'] = "";


			for ($i=0; $i < count($itemTags); $i++)
			{
				eval ("\$$itemTags[$i] = \$row['$itemTags[$i]'];");

				eval ("\$$itemTags[$i] = commonValidXml(\$$itemTags[$i]);");

				eval ("\$xmlResponse .= \"<$itemTags[$i]>\$$itemTags[$i]</$itemTags[$i]>\";");
			}

			$xmlResponse .= "<itemId>$id</itemId>";
		}

		for ($i=0; $i < count($itemLangTags); $i++)
		{
			eval ("\$$itemLangTags[$i] = commonValidXml(\$row['$itemLangTags[$i]']);");
			eval ("\$xmlResponse .=	\"<$itemLangTags[$i]\$language>\$$itemLangTags[$i]</$itemLangTags[$i]\$language>\";");
		}
	}

	// add missing languages
	// ------------------------------------------------------------------------------------------------
	for ($i=0; $i<count($langsArray); $i++)
	{
		$language	  = $langsArray[$i];

		for ($j=0; $j < count($itemLangTags); $j++)
		{
			eval ("\$xmlResponse .=	\"<$itemLangTags[$j]\$language></$itemLangTags[$j]\$language>\";");
		}
	}

	return $xmlResponse;
}

/* ----------------------------------------------------------------------------------------------------	*/
/* jumpTabletItem																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function jumpTabletItem ($xmlRequest)
{
	$id		= xmlParser_getValue($xmlRequest, "itemId");

	if ($id == "")
		trigger_error ("חסר קוד הודעה לביצוע הפעולה");

	$queryStr    = "select *
					from tabletItems, tabletItems_byLang
					where tabletItems.id = tabletItems_byLang.itemId
				    and tabletItems.id=$id";

	$result		= commonDoQuery ($queryStr);

	if (commonQuery_numRows($result) == 0)
		trigger_error ("הודעה קוד זה ($id) לא קיימת במערכת. לא ניתן לבצע את הפעולה");

	$queryStr	= "update tabletItems set insertTime = now() where id = $id";
	commonDoQuery ($queryStr);

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* addTabletItem																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function addTabletItem ($xmlRequest)
{
	return (editTabletItem ($xmlRequest, "add"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateTabletItem																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateTabletItem ($xmlRequest)
{
	return (editTabletItem ($xmlRequest, "update"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* doesTabletItemExist																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function doesTabletItemExist ($id)
{
	$queryStr		= "select count(*) from tabletItems where id=$id";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$count	     = $row[0];

	return ($count > 0);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* editTabletItem																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function editTabletItem ($xmlRequest, $editType)
{
	global $usedLangs, $itemTags, $itemLangTags;

	for ($i=0; $i < count($itemTags); $i++)
	{
		eval ("\$$itemTags[$i] = commonDecode(xmlParser_getValue(\$xmlRequest,\"$itemTags[$i]\"));");	
	}

	if ($startPublishDate != "")
		$startPublishDate = formatApplToDB ("$startPublishDate 00:00");

	if ($endPublishDate != "")
		$endPublishDate   = formatApplToDB ("$endPublishDate 00:00");

	if ($editType == "update")
	{
		$id = xmlParser_getValue($xmlRequest, "itemId");
	}
	else
	{
		$queryStr	= "select max(id) from tabletItems";
		$result		= commonDoQuery ($queryStr);
		$row		= commonQuery_fetchRow ($result);
		$id 		= $row[0] + 1;

		// get enum ids
		$queryStr	= "select enumId1, enumId2, enumId3, enumId4, enumId5, enumId6, enumId7, enumId8, enumId9, enumId10 
					   from tablets where id = $tabletId";
		$result		= commonDoQuery ($queryStr);
		$row		= commonQuery_fetchRow($result);

		$enumId1	= $row['enumId1'];
		$enumId2	= $row['enumId2'];
		$enumId3	= $row['enumId3'];
		$enumId4	= $row['enumId4'];
		$enumId5	= $row['enumId5'];
		$enumId6	= $row['enumId6'];
		$enumId7	= $row['enumId7'];
		$enumId8	= $row['enumId8'];
		$enumId9	= $row['enumId9'];
		$enumId10	= $row['enumId10'];
	}

	$vals = Array();

	for ($i=0; $i < count($itemTags); $i++)
	{
		eval ("array_push (\$vals,\$$itemTags[$i]);");
	}
	
	if ($editType == "update")
	{
		if ($id == "")
			trigger_error ("חסר קוד הודעה לביצוע הפעולה");

		if (!doesTabletItemExist($id))
		{
			trigger_error ("הודעה עם קוד זה ($id) לא קיימת במערכת. לא ניתן לבצע את העדכון");
		}
		
		$queryStr = "update tabletItems set ";

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
		$queryStr = "insert into tabletItems (" . join(",",$itemTags) . ",insertTime) values ('" . join("','",$vals) . "',now())";
		commonDoQuery ($queryStr);
	}

	# delete all languages rows
	# ------------------------------------------------------------------------------------------------------
	$queryStr = "delete from tabletItems_byLang where itemId='$id'";
	commonDoQuery ($queryStr);
	
	# add languages rows for this user
	# ------------------------------------------------------------------------------------------------------
	$langsArray = explode(",",$usedLangs);

	for ($i=0; $i<count($langsArray); $i++)
	{
		$language		= $langsArray[$i];

		$vals = Array();
		for ($j=0; $j < count($itemLangTags); $j++)
		{
			eval ("\$$itemLangTags[$j] = addslashes(commonDecode(xmlParser_getValue(\$xmlRequest,\"$itemLangTags[$j]\$language\")));");	
			eval ("array_push (\$vals,\$$itemLangTags[$j]);");
		}		

		$queryStr		= "insert into tabletItems_byLang (itemId, language," . join(",",$itemLangTags) . ") 
						   values ($id, '$language', '" . join ("','", $vals) . "')";
	
		commonDoQuery ($queryStr);
	}
	return "<itemId>$id</itemId>";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteTabletItem																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteTabletItem ($xmlRequest)
{
	$id = xmlParser_getValue ($xmlRequest, "itemId");

	if ($id == "")
		trigger_error ("חסר קוד לוח לביצוע הפעולה");

	$files	  = array();

	// get items files
	$queryStr = "select file, fileType from tabletItemsFiles where itemId = $id";
	$result   = commonDoQuery($queryStr);
	while ($row = commonQuery_fetchRow($result))
	{
		array_push($files, $row);
	}

	$queryStr = "delete from tabletItems where id = $id";
	commonDoQuery ($queryStr);

	$queryStr = "delete from tabletItems_byLang where itemId = $id";
	commonDoQuery ($queryStr);

	$queryStr = "select pos from categoriesItems where itemId = $id and type = 'tabletItem'";
	$result	  = commonDoQuery($queryStr);

	if (commonQuery_numRows($result) != 0)
	{
		$row	  = commonQuery_fetchRow($result);
		$pos	  = $row['pos'];

		$queryStr = "update categoriesItems set pos = pos - 1 where type = 'tabletItem' and pos > $pos";
		commonDoQuery ($queryStr);

		$queryStr = "delete from categoriesItems where itemId = $id and type = 'tabletItem'";
		commonDoQuery ($queryStr);
	}

	// delete files from directory
	$domainRow 	= commonGetDomainRow ();
	$connId 	= commonFtpConnect($domainRow);
	
	foreach ($files as $fileRow)
	{
		$file = $fileRow['file'];

		commonFtpDelete($connId, "tabletsFiles/$file");

		if ($fileRow['fileType'] == "pic")
		{
			$file 		= str_replace("size0", "size1", $file);
			commonFtpDelete($connId, "tabletsFiles/$file");

			$file 		= str_replace("size1", "size2", $file);
			commonFtpDelete($connId, "tabletsFiles/$file");
		}
	}

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getExtraDataNames																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function getExtraDataNames ($xmlRequest)
{
	$tabletId	= xmlParser_getValue($xmlRequest, "tabletId");

	$queryStr 	= "select * from tablets where id = $tabletId";
	$result	    = commonDoQuery ($queryStr);
	$row	    = commonQuery_fetchRow($result);

	$xmlResponse = "<extraData1>"  . commonValidXml($row['extraData1'])  . "</extraData1>
					<extraData2>"  . commonValidXml($row['extraData2'])  . "</extraData2>
					<extraData3>"  . commonValidXml($row['extraData3'])  . "</extraData3>
					<extraData4>"  . commonValidXml($row['extraData4'])  . "</extraData4>
					<extraData5>"  . commonValidXml($row['extraData5'])  . "</extraData5>
					<extraData6>"  . commonValidXml($row['extraData6'])  . "</extraData6>
					<extraData7>"  . commonValidXml($row['extraData7'])  . "</extraData7>
					<extraData8>"  . commonValidXml($row['extraData8'])  . "</extraData8>
					<extraData9>"  . commonValidXml($row['extraData9'])  . "</extraData9>
					<extraData10>" . commonValidXml($row['extraData10']) . "</extraData10>
					<enumId1>$row[enumId1]</enumId1>
					<enumId2>$row[enumId2]</enumId2>
					<enumId3>$row[enumId3]</enumId3>
					<enumId4>$row[enumId4]</enumId4>
					<enumId5>$row[enumId5]</enumId5>
					<enumId7>$row[enumId7]</enumId7>
					<enumId7>$row[enumId7]</enumId7>
					<enumId8>$row[enumId8]</enumId8>
					<enumId9>$row[enumId9]</enumId9>
					<enumId10>$row[enumId10]</enumId10>";

	for ($i=1; $i<=10; $i++)
	{
		eval ("\$enumId = \$row[\"enumId$i\"];");

		if ($enumId == 0) 
		{
			$xmlResponse .= "<enumName$i></enumName$i>";
		}
		else
		{
			// get enum  name
			$queryStr = "select name from enums where id = $enumId";
			$result		= commonDoQuery ($queryStr);
			$enumRow	= commonQuery_fetchRow ($result);

			$xmlResponse .= "<enumName$i>" . commonValidXml($enumRow['name']) . "</enumName$i>";
		}
	}

	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getTabletFiles																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getTabletFiles ($xmlRequest)
{
	$itemId  = xmlParser_getValue($xmlRequest, "itemId");
		
	if ($itemId == "")
		trigger_error ("חסר מזהה מודעה לביצוע הפעולה");

	// get total
	$queryStr	 = "select count(*) 
					from tabletItemsFiles
					where itemId = $itemId";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$total	     = $row[0];

	// get details
	$queryStr    = "select *
					from tabletItemsFiles
					where itemId = $itemId
					order by pos " . commonGetLimit ($xmlRequest);
	$result	     = commonDoQuery ($queryStr);

	$numRows    = commonQuery_numRows($result);

	$xmlResponse = "<items>";

	$domainRow   = commonGetDomainRow ();
	$filePrefix  = commonGetDomainName($domainRow) . "/tabletsFiles/";

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
					from tabletItemsFiles
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
	$queryStr	= "select pos from tabletItemsFiles where id=$id";
	$result		= commonDoQuery ($queryStr);
	$row		= commonQuery_fetchRow($result);
	$currPos	= $row[0];

	if ($currPos > $pos)
	{
		$queryStr = "update tabletItemsFiles set pos = pos+1 where pos >= $pos and pos < $currPos and itemId = $itemId";
		commonDoQuery ($queryStr);
	}

	if ($currPos < $pos)
	{
		$queryStr = "update tabletItemsFiles set pos = pos-1 where pos > $currPos and pos <= $pos and itemId = $itemId";
		commonDoQuery ($queryStr);
	}

	// update details
	if ($currPos != $pos)
	{
		$queryStr = "update tabletItemsFiles set pos = $pos where id = $id";
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

	$queryStr = "select file from tabletItemsFiles where id = $id";
	$result	  = commonDoQuery($queryStr);
	$row	  = commonQuery_fetchRow($result);

	$queryStr = "delete from tabletItemsFiles where id = $id";
	commonDoQuery ($queryStr);
	
	// delete file from directory
	$domainRow 	= commonGetDomainRow ();
	$connId 	= commonFtpConnect($domainRow);
	
	$file = $row['file'];
	commonFtpDelete($connId, "tabletsFiles/$file");

	$file 		= str_replace("size0", "size1", $file);
	commonFtpDelete($connId, "tabletsFiles/$file");

	$file 		= str_replace("size1", "size2", $file);
	commonFtpDelete($connId, "tabletsFiles/$file");

	// TBD - delete file from directory
}

?>
