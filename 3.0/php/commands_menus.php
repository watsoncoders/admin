<?php

include "picsTools.php";

/* ----------------------------------------------------------------------------------------------------	*/
/* getMenus																								*/
/* ----------------------------------------------------------------------------------------------------	*/
function getMenus ($xmlRequest)
{
	global $usedLangs;
	$langsArray = explode(",",$usedLangs);

	// get total
	$queryStr	 = "select count(*) from boxes where boxes.type = 'links'";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$total	     = $row[0];

	// get details
	$queryStr    = "select id, boxName 
					from boxes 
					where type = 'links' 
					order by id " . commonGetLimit ($xmlRequest);
	$result	     = commonDoQuery ($queryStr);

	$numRows    = commonQuery_numRows($result);

	$xmlResponse = "<items>";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		$id    	   = $row['id'];
		$menuName  = commonValidXml ($row['boxName'],true);

		$xmlResponse .=	"<item>
							<id>$id</id>
							<menuName>$menuName</menuName>
						</item>";
	}

	$xmlResponse .=	"</items>"												.
					commonGetTotalXml($xmlRequest,$numRows,$total);
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* addMenu																								*/
/* ----------------------------------------------------------------------------------------------------	*/
function addMenu ($xmlRequest)
{
	return (editMenu ($xmlRequest, "add"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* doesMenuExist																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function doesMenuExist ($id)
{
	$queryStr		= "select count(*) from boxes where id=$id";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$count	     = $row[0];

	return ($count > 0);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getItemNextId																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getItemNextId ($xmlRequest)
{
	$item		= xmlParser_getValue($xmlRequest, "item");

	if ($item == "menu")
	{
		$queryStr	= "select max(id) from boxes";
	}
	else
	{
		$queryStr	= "select max(id) from links";
	}

	$result		= commonDoQuery ($queryStr);
	$row		= commonQuery_fetchRow ($result);
	$id 		= $row[0] + 1;
	
	return "<id>$id</id>";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getMenuDetails																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getMenuDetails ($xmlRequest)
{
	global $usedLangs;
	$langsArray = explode(",",$usedLangs);

	$id		= xmlParser_getValue($xmlRequest, "menuId");

	if ($id == "")
		trigger_error ("חסר קוד תפריט לביצוע הפעולה");

	$queryStr	= "select boxes.*, boxes_byLang.title, language 
				   from boxes left join boxes_byLang on boxes.id = boxes_byLang.boxId
				   where id=$id";
	$result		= commonDoQuery ($queryStr);

	if (commonQuery_numRows($result) == 0)
		trigger_error ("תפריט קוד זה ($id) לא קיים במערכת. לא ניתן לבצע את העדכון");

	
	$langsArray = explode(",",$usedLangs);

	$xmlResponse = "";

	while ($row = commonQuery_fetchRow($result))
	{
		$language = $row['language'];

		$langsArray = commonArrayRemove ($langsArray, $language);	

		if ($xmlResponse == "")
		{
			$id			 = $row['id'];
			$menuName  = commonValidXml ($row['boxName'],true);
	
			$xmlResponse = 	"<menuId>$id</menuId>
							 <menuName>$menuName</menuName>";
		}

		$title = commonValidXml ($row['title'],true);

		$xmlResponse .= "<title$language>$title</title$language>";
	}

	return $xmlResponse;
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateMenu																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateMenu ($xmlRequest)
{
	editMenu ($xmlRequest, "update");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* editMenu																								*/
/* ----------------------------------------------------------------------------------------------------	*/
function editMenu ($xmlRequest, $editType)
{
	global $usedLangs;

	$id		= xmlParser_getValue($xmlRequest, "menuId");

	if ($editType == "add")
	{
		$queryStr	= "select max(id) from boxes";
		$result		= commonDoQuery ($queryStr);
		$row		= commonQuery_fetchRow ($result);
		$id 		= $row[0] + 1;
	}
	else	// update box
	{
		if ($id == "")
			trigger_error ("חסר קוד תפריט לביצוע הפעולה");

		if (!doesMenuExist($id))
		{
			trigger_error ("תפריט עם קוד זה ($id) לא קיים במערכת. לא ניתן לבצע את העדכון");
		}
	}

	$menuName	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "menuName")));

	if ($editType == "add")
	{
		$queryStr = "insert into boxes (id, type, boxName) values ($id, 'links', '$menuName')";
		commonDoQuery ($queryStr);
	}
	else // update
	{
		$queryStr = "update boxes set boxName		= '$menuName'
					 where id=$id";
		commonDoQuery ($queryStr);
		
		$queryStr = "delete from boxes_byLang where boxId='$id'";
		commonDoQuery ($queryStr);
	}

	# add languages rows for this link
	# ------------------------------------------------------------------------------------------------------
	$langsArray = explode(",",$usedLangs);

	for ($i=0; $i<count($langsArray); $i++)
	{
		$language		= $langsArray[$i];

		$title			= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "title$language")));

		$queryStr		= "insert into boxes_byLang (boxId, language, title)
						   values ('$id','$language', '$title')";
	
		commonDoQuery ($queryStr);
	}


	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteMenu																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteMenu ($xmlRequest)
{
	$id = xmlParser_getValue ($xmlRequest, "menuId");

	if ($id == "")
		trigger_error ("חסר קוד תפריט לביצוע הפעולה");

	$queryStr = "delete from boxes where id = $id";
	commonDoQuery ($queryStr);

	$queryStr = "delete from boxes_byLang where boxId='$id'";
	commonDoQuery ($queryStr);

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getSubMenus																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function getSubMenus ($xmlRequest)
{	
	global $usedLangs;
	$langsArray = explode(",",$usedLangs);

	$condition  = "";

	$menuId		= xmlParser_getValue($xmlRequest, "menuId");

	if ($menuId == "")
	{
		return "<items></items>";
	}

	// get total
	$queryStr	 = "select count(*) from links where boxId = $menuId and parentId = 0 ";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$total	     = $row[0];

	// get details
	$queryStr    = "select links.id as id, links_byLang.name as name 
					from links
					left join links_byLang on links.id = links_byLang.linkId and links_byLang.language = '$langsArray[0]'
					where boxId = $menuId and parentId = 0
					order by pos " . commonGetLimit ($xmlRequest);
	$result	     = commonDoQuery ($queryStr);

	$numRows    = commonQuery_numRows($result);

	$xmlResponse = "<items>";

	while ($row = commonQuery_fetchRow($result))
	{
		$id   		  = $row['id'];
		$name 		  = commonValidXml ($row['name'],true);

		$xmlResponse .=	"<item>
							<id>$id</id>
							<name>$name</name>
							<level>1</level>
						</item>";

		// get sub items
		$queryStr2	= str_replace("parentId = 0", "parentId = $id", $queryStr);
		$result2 	= commonDoQuery ($queryStr2);

		$numRows += commonQuery_numRows($result2);

		while ($row2 = commonQuery_fetchRow($result2))
		{
			$id   		  = $row2['id'];
			$name 		  = commonValidXml ("    " . $row2['name'],true);

			$xmlResponse .=	"<item>
								<id>$id</id>
								<name>$name</name>
								<level>2</level>
							</item>";
		}
	}

	$xmlResponse .=	"</items>"												.
					commonGetTotalXml($xmlRequest,$numRows,$total);
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getMenuItems																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function getMenuItems ($xmlRequest)
{	
	global $cookie_guiLang;
	global $usedLangs;
	$langsArray = explode(",",$usedLangs);

	$condition  = "";

	$menuId		= xmlParser_getValue($xmlRequest, "menuId");
	$parentId	= xmlParser_getValue($xmlRequest, "parentId");

	if ($menuId == "")
	{
		return "<items></items>";
	}

	if ($parentId != "")
	{
		$condition = " and parentId = $parentId ";
	}
	else
	{
		$condition = " and parentId = 0 ";
	}

	// get total
	$queryStr	 = "select count(*) from links where boxId = $menuId $condition";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$total	     = $row[0];

	// get details
	
	$sql 		 = "select links.id as id, links_byLang.name as name, links.type as type, links.boxId as boxId,
						   pos, urlOrPageId, boxes.boxName, forum.name as forumName, pages.staticname as staticname, pages_byLang.title
					from links
					left join pages on links.urlOrPageId = pages.id
					left join forum on links.urlOrPageId = forum.id and forum.language = '$langsArray[0]'
					left join pages_byLang on links.urlOrPageId = pages_byLang.pageId  
						 				  and pages_byLang.language = '$langsArray[0]'
					left join links_byLang on links.id = links_byLang.linkId and links_byLang.language = '$langsArray[0]'
					left join boxes on links.boxId = boxes.id";

	$queryStr    = "$sql
					where boxId = $menuId $condition
					order by boxes.id, pos";
	$result	     = commonDoQuery ($queryStr);

	$xmlResponse = "<items>";

	while ($row = commonQuery_fetchRow($result))
	{
		$xmlResponse .= addItemRow($row, 1);
			
		if ($parentId == "")
		{
			// get sub items
			$queryStr = "$sql
						 where parentId = $row[id]
						 order by pos";

			$result2 = commonDoQuery ($queryStr);

			$total += commonQuery_numRows($result2);

			while ($row2 = commonQuery_fetchRow($result2))
			{
				$xmlResponse .= addItemRow($row2, 2);

				// get sub sub items
				$queryStr = "$sql
							 where parentId = $row2[id]
							 order by pos";

				$result3 = commonDoQuery ($queryStr);

				$total += commonQuery_numRows($result3);
				
				while ($row3 = commonQuery_fetchRow($result3))
				{
					$xmlResponse .= addItemRow($row3, 3);
				}
			}
		}
	}

	if ($cookie_guiLang == "ENG")
		$totalText = "links";
	else
		$totalText = commonPhpEncode("קישורים");

	$xmlResponse .=	"</items>"												.
					"<totals>"		.
						"<totalText>$total $totalText</totalText>"	.
					"</totals>";
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* addItemRow																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function addItemRow ($row, $level)
{
	$xml = "";

	if ($level == 2)
		$row['name'] = "&nbsp;&nbsp;&nbsp;&nbsp;$row[name]";
	else if ($level == 3)
		$row['name'] = "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;$row[name]";

	$id   		  = $row['id'];
	$name 		  = commonValidXml ($row['name'],true);
	$type		  = $row['type'];
	$typeText	  = formatLinkType ($type);
	$pos   		  = $row['pos'];
	$urlOrPageId  = commonValidXml ($row['urlOrPageId'],true);
	$menuId		  = $row['boxId'];
	$menuName	  = commonValidXml ($row['boxName'],true);


	if ($row['urlOrPageId'] == "")
	{
		$urlOrPageId = "";
	}
	else
	{
		if ($row['type'] == "forum")
			$urlOrPageId = $row['urlOrPageId'] . " - " . commonValidXml ($row['forumName']);
		else if ($row['type'] == "url" || $row['type'] == "urlNoFollow" || $row['type'] == "file")
			$urlOrPageId = commonValidXml ($row['urlOrPageId']);
		else
		{
			if ($row['title'] != "")
			{
				$urlOrPageId = commonValidXml ($row['urlOrPageId'] . " - " . $row['title']);
			}
			else
			{
				$urlOrPageId = commonValidXml ($row['urlOrPageId'] . " - " . $row['staticname']);
			}
		}
	}

	if ($pos == 0) 
		$pos = "";

	$xml .=	"<item>
				<id>$id</id>
				<name>$name</name>
				<type>$type</type>
				<typeText>$typeText</typeText>
				<pos>$pos</pos>
				<urlOrPageId>$urlOrPageId</urlOrPageId>
				<menuId>$menuId</menuId>
				<menuName>$menuName</menuName>
			</item>";

	return $xml;
}

/* ----------------------------------------------------------------------------------------------------	*/
/* addMenuItem																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function addMenuItem ($xmlRequest)
{
	return (editMenuItem ($xmlRequest, "add"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* doesMenuItemExist																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function doesMenuItemExist ($id)
{
	$queryStr		= "select count(*) from links where id=$id";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$count	     = $row[0];

	return ($count > 0);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getMenuItemDetails																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function getMenuItemDetails ($xmlRequest)
{
	global $usedLangs;
	$langsArray = explode(",",$usedLangs);

	$id		= xmlParser_getValue($xmlRequest, "id");

	if ($id == "")
		trigger_error ("חסר קוד קישור או תת תפריט לביצוע הפעולה");

	$queryStr	= "select links.*, language, name, title, boxName, extraData1, extraData2, extraData3, extraData4 
				   from links, links_byLang, boxes 
				   where links.id=linkId
				   and   links.id=$id
				   and   links.boxId = boxes.id";

/*	$queryStr	= "select links.*, language, name, title, boxName 
				   from links, boxes 
				   left join links_byLang on links.id=linkId
				   where links.id=$id
				   and   links.boxId = boxes.id";*/
	$result		= commonDoQuery ($queryStr);

	if (commonQuery_numRows($result) == 0)
		trigger_error ("תת תפריט או קישור קוד זה ($id) לא קיים במערכת. לא ניתן לבצע את העדכון");

	$langsArray = explode(",",$usedLangs);

	$xmlResponse = "";
	
	// siteUrl
	$domainRow   = commonGetDomainRow ();
	$websiteUrl  = commonGetDomainName($domainRow);

	commonConnectToUserDB ($domainRow);

	while ($row = commonQuery_fetchRow($result))
	{
		$language = $row['language'];

		$langsArray = commonArrayRemove ($langsArray, $language);	

		if ($xmlResponse == "")
		{
			$id			 = $row['id'];
			$menuId		 = $row['boxId'];
			$pos		 = $row['pos'];
			$type		 = $row['type'];

			$urlOrPageId = commonValidXml($row['urlOrPageId']);
			$url		 = "<![CDATA[]]>";
			$page		 = "";
		
			if ($type == "url" || $type == "urlNoFollow" || $type == "onclick")
				$url  = $urlOrPageId;
			else
				$page = $urlOrPageId;
		
			$isNewWin		= $row['isNewWin'];
			$isBlink		= $row['isBlink'];
			$isUniqueStyle	= $row['isUniqueStyle'];
		
			$menuName		= commonValidXml($row['boxName']);
			$parentName     = "";

			$parentId		= $row['parentId'];

			if ($parentId != 0)
			{
				// get sub menu name
				$queryStr = "select name from links_byLang where linkId = $parentId and language = '$language'";
				$result2  = commonDoQuery($queryStr);
				$row2	  = commonQuery_fetchRow ($result2);

				$parentName = commonValidXml($row2['name']);
			}

			$xmlResponse	= 	"<id>$id</id>
								 <menuId>$menuId</menuId>
								 <pos>$pos</pos>
								 <type>$type</type>
								 <url>$url</url>
								 <page>$page</page>
								 <isNewWin>$isNewWin</isNewWin>
								 <isBlink>$isBlink</isBlink>
								 <isUniqueStyle>$isUniqueStyle</isUniqueStyle>
								 <menuName>$menuName</menuName>
								 <parentId>$parentId</parentId>
								 <parentName>$parentName</parentName>";

			$fileFullName  = commonValidXml("$websiteUrl/linksFiles/$row[picFile]");
			$sourceFile	   = commonValidXml(addslashes($row['sourceFile']));
			$picFile	   = commonValidXml(addslashes($row['picFile']));

			$pressText     = commonPhpEncode("לחץ כאן");

			$show	 = "";
			$delete  = "";

			if ($row['picFile']  != "") 
			{
				$show   = $pressText;
				$delete = $pressText;
			}

			$xmlResponse .= "<picFileName>$picFile</picFileName>
							 <formPicSource>$sourceFile</formPicSource>
							 <show>$show</show>
							 <delete>$delete</delete>
							 <fileFullName>$fileFullName</fileFullName>";
		}

		$name		  	= commonValidXml($row['name']);
		$title		  	= commonValidXml($row['title']);
		$extraData1		= commonValidXml($row['extraData1']);
		$extraData2		= commonValidXml($row['extraData2']);
		$extraData3		= commonValidXml($row['extraData3']);
		$extraData4		= commonValidXml($row['extraData4']);

		$xmlResponse .= "<name$language>$name</name$language> 
						 <title$language>$title</title$language>
						 <extraData1$language>$extraData1</extraData1$language>
						 <extraData2$language>$extraData2</extraData2$language>
						 <extraData3$language>$extraData3</extraData3$language>
						 <extraData4$language>$extraData4</extraData4$language>";
	}

	// add missing languages
	// ------------------------------------------------------------------------------------------------
	for ($i=0; $i<count($langsArray); $i++)
	{
		$language	  = $langsArray[$i];

		$xmlResponse .=	   "<name$language><![CDATA[]]></name$language>
							<title$language><![CDATA[]]></title$language>
						 	<extraData1$language><![CDATA[]]></extraData1$language>
						 	<extraData2$language><![CDATA[]]></extraData2$language>
						 	<extraData3$language><![CDATA[]]></extraData3$language>
						 	<extraData4$language><![CDATA[]]></extraData4$language>";
	}

	return $xmlResponse;
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateMenuItem																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateMenuItem ($xmlRequest)
{
	editMenuItem ($xmlRequest, "update");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* editMenuItem																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function editMenuItem ($xmlRequest, $editType)
{
	global $usedLangs;
	global $userId;
	global $ibosHomeDir;

	$id		= xmlParser_getValue($xmlRequest, "id");

	if ($id == "" && $editType == "update")
		trigger_error ("חסר קוד קישור לביצוע הפעולה");

	
	if ($editType == "add")
	{
		$queryStr	= "select max(id) from links";
		$result		= commonDoQuery ($queryStr);
		$row		= commonQuery_fetchRow ($result);
		$id 		= $row[0] + 1;
	}
	else	// update link
	{
		if (!doesMenuItemExist($id))
		{
			trigger_error ("קישור עם קוד זה ($id) לא קיים במערכת. לא ניתן לבצע את העדכון");
		}
	}

	# delete all languages rows
	# ------------------------------------------------------------------------------------------------------
	$queryStr = "delete from links_byLang where linkId='$id'";
	commonDoQuery ($queryStr);
	
	# add languages rows for this link
	# ------------------------------------------------------------------------------------------------------
	$langsArray = explode(",",$usedLangs);

	for ($i=0; $i<count($langsArray); $i++)
	{
		$language		= $langsArray[$i];

		$name			= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "name$language")));
		$title			= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "title$language")));
		$extraData1		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "extraData1$language")));
		$extraData2		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "extraData2$language")));
		$extraData3		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "extraData3$language")));
		$extraData4		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "extraData4$language")));

		$queryStr		= "insert into links_byLang (linkId, language, name, title, extraData1, extraData2, extraData3, extraData4)
						   values ('$id','$language','$name','$title', '$extraData1', '$extraData2', '$extraData3', '$extraData4')";
	
		commonDoQuery ($queryStr);
	}

	$menuId			= xmlParser_getValue($xmlRequest, "menuId");
	$parentId		= xmlParser_getValue($xmlRequest, "parentId");
	$pos			= xmlParser_getValue($xmlRequest, "pos");
	$type			= xmlParser_getValue($xmlRequest, "type");
	$page			= xmlParser_getValue($xmlRequest, "page");
	$url			= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "url")));
	$isNewWin		= xmlParser_getValue($xmlRequest, "isNewWin");
	$isBlink		= xmlParser_getValue($xmlRequest, "isBlink");
	$isUniqueStyle	= xmlParser_getValue($xmlRequest, "isUniqueStyle");

	if ($type == "url" || $type == "urlNoFollow" || $type == "onclick")
		$urlOrPageId = $url;
	else
		$urlOrPageId = $page;

	if ($parentId == "") $parentId = 0;

	// handle link file
	# ------------------------------------------------------------------------------------------------------
	$sourceFile 	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "sourceFile")));	
	$fileDeleted	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "fileDeleted")));	
	$dimensionId	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "dimensionId")));	

	$fileDeleted	= ($fileDeleted == "1");
	$fileLoaded  	= false;

	$picFile 		= "";
	$suffix 		= "";

	if ($sourceFile != "")
	{
		$fileLoaded = true;
		$suffix		= commonFileSuffix ($sourceFile);
		$picFile 	= "${id}_size0$suffix";

		list ($picWidth, $picHeight, $bgColor) = commonGetDimensionDetails ($dimensionId);
	}

	if ($editType == "add")
	{
		$queryStr = "update links set pos = pos+1 where boxId = $menuId and parentId = $parentId and pos >= $pos";
		commonDoQuery ($queryStr);

		$queryStr = "insert into links 
					 (id,  boxId,    parentId,   pos,    type,    urlOrPageId,    isNewWin,    isBlink,    isUniqueStyle, picFile, sourceFile) 
					 values 
						($id, $menuId, $parentId, '$pos', '$type', '$urlOrPageId', '$isNewWin', '$isBlink', '$isUniqueStyle'";
		$queryStr .= ($fileLoaded  ?  ", '$picFile' , '$sourceFile' " : ", '', ''");
		$queryStr .= ")";
	}
	else // update
	{
		// get curr link pos
		$queryStr    	= "select pos, parentId from links where id=$id";
		$result	     	= commonDoQuery ($queryStr);
		$row	     	= commonQuery_fetchRow($result);
		$currPos	 	= $row[0];
		$currParentId 	= $row[1];

		if ($currParentId != $parentId)
		{
			// delete link from prev parent
			$queryStr 	 = "update links set pos = pos-1 where boxId = $menuId and parentId = $currParentId and pos > $currPos";
			commonDoQuery ($queryStr);

			// add link to new parent
			$queryStr = "update links set pos = pos+1 where boxId = $menuId and parentId = $parentId and pos >= $pos";
			commonDoQuery ($queryStr);
		}
		else
		{
			if ($currPos > $pos)
			{
				$queryStr = "update links set pos = pos+1 where boxId = $menuId and parentId = $parentId and pos >= $pos and pos < $currPos";
				$result	     = commonDoQuery ($queryStr);
			}

			if ($currPos < $pos)
			{
				$queryStr = "update links set pos = pos-1 where boxId = $menuId and parentId = $parentId and pos > $currPos and pos < $pos";
				$result	     = commonDoQuery ($queryStr);

				$pos--;
			}
		}

		$queryStr = "update links set 	pos				= '$pos',		
										parentId		= $parentId,
										type			= '$type',		
										urlOrPageId		= '$urlOrPageId',		
										isNewWin		= '$isNewWin',		
										isBlink			= '$isBlink',		
										isUniqueStyle	= '$isUniqueStyle'";

		if ($fileLoaded)
		{
			$queryStr .= ",	  		   picFile 	   = '$picFile',
							  		   sourceFile  = '$sourceFile' ";
		}
		else if ($fileDeleted)
		{
			$queryStr .= ",	  	 	   picFile     = '',
								 	   sourceFile  = '' ";
		}

		$queryStr .= " where id=$id";
	}

	commonDoQuery ($queryStr);

	// handle files
	$filePath = "$ibosHomeDir/html/SWFUpload/files/$userId/";

	$domainRow	= commonGetDomainRow();
	$domainName = commonGetDomainName ($domainRow);

	$connId 	= commonFtpConnect($domainRow); 
	ftp_chdir ($connId, "linksFiles");

	if ($fileLoaded)
	{
		commonFtpDelete ($connId, $picFile);

		$upload = ftp_put($connId, $picFile, "$filePath/$sourceFile", FTP_BINARY);

		// size 1
		$resizedFileName = str_replace("size0","size1",$picFile);
		$destParts = explode(".",$resizedFileName);
		$destParts[count($destParts)-1] = "jpg";
		$resizedFileName = join(".", $destParts);

		if ($picWidth == 0 && $picHeight == 0)
		{
			$upload = ftp_put($connId, $resizedFileName, "$filePath/$sourceFile", FTP_BINARY);
		}
		else
		{
			picsToolsResize("$filePath/$sourceFile", $suffix, $picWidth, $picHeight, "/../../tmp/$resizedFileName", $bgColor);
		
			$upload = ftp_put($connId, $resizedFileName, "/../../tmp/$resizedFileName", FTP_BINARY);
		}
	}
	else if ($fileDeleted)
	{
		commonFtpDelete ($connId, $oldFile);

		$oldFile 	= str_replace("size0", "size1", $oldFile);
		$destParts 	= explode(".",$oldFile);
		$destParts[count($destParts)-1] = "jpg";
		$oldFile 	= join(".", $destParts);

		commonFtpDelete ($connId, $oldFile);
	}

	commonFtpDisconnect ($connId);

 	// delete old files
	commonDeleteOldFiles ($filePath, 7200);	// 2 hour

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteMenuItem																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteMenuItem ($xmlRequest)
{
	$id 	= xmlParser_getValue ($xmlRequest, "id");
	$menuId = xmlParser_getValue ($xmlRequest, "menuId");

	if ($id == "")
		trigger_error ("חסר קוד קישור לביצוע הפעולה");

	// get curr link pos
	$queryStr    = "select parentId, pos from links where id=$id";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$pos		 = $row['pos'];
	$parentId	 = $row['parentId'];

	$queryStr 	 = "delete from links where id = $id";
	commonDoQuery ($queryStr);

	// update places
	$queryStr 	 = "update links set pos = pos-1 where boxId = $menuId and parentId = $parentId and pos > $pos";
	commonDoQuery ($queryStr);

	$queryStr 	 = "delete from links_byLang where linkId = $id";
	commonDoQuery ($queryStr);

	$queryStr 	 = "delete from links_byLang where linkId in (select id from links where parentId = $id)";
	commonDoQuery ($queryStr);

	$queryStr 	 = "delete from links where parentId = $id";
	commonDoQuery ($queryStr);

	return "";
}

?>
