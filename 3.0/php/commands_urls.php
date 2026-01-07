<?php

include "$ibosHomeDir/php/picsTools.php";

$tags 	  = Array("url", "displayUrl", "target", "date");
$langTags = Array("isReady", "picTitle", "title", "txt");

/* ----------------------------------------------------------------------------------------------------	*/
/* getUrls																								*/
/* ----------------------------------------------------------------------------------------------------	*/
function getUrls ($xmlRequest)
{	
	global $usedLangs;

	$langsArray = explode(",",$usedLangs);
	
	$sortBy		= xmlParser_getValue($xmlRequest,"sortBy");
	if ($sortBy == "" || $sortBy == "urlId")
		$sortBy = "id";

	$sortDir	= xmlParser_getValue($xmlRequest,"sortDir");
	if ($sortDir == "")
		$sortDir = "desc";

	$conditions = commonAddIbosUserCondition("urls");

	$urlId = xmlParser_getValue($xmlRequest, "urlId");
	if ($urlId != "")
		$conditions .= " and urls.id = '$urlId' ";

	$addCategories = "";
	$category = xmlParser_getValue($xmlRequest, "category");
	if ($category != "")
	{
		$addCategories = " left join categoriesItems on urls.id = categoriesItems.itemId ";
		$conditions .= "  and categoriesItems.categoryId = $category ";
	}

	$anyText = addslashes(commonDecode(xmlParser_getValue($xmlRequest, "anyText")));
	if ($anyText != "")
	{
		$textCond = "";
		for ($i=0; $i<count($langsArray); $i++)
		{
			$language	 = $langsArray[$i];

			if ($textCond != "") $textCond .= " or ";

			$textCond .= " ul$i.title like '%$anyText%' or ul$i.txt like '%$anyText%' ";
		}
	
		if ($textCond != "")
		$conditions .= "and ($textCond) ";
	}

	$columns	 = "urls.*";
	$joins		= "";

	for ($i=0; $i<count($langsArray); $i++)
	{
		$language	 = $langsArray[$i];

		$columns	.= ",ul$i.title as title$language, ul$i.txt as txt$language, ul$i.isReady as isReady$language, ul$i.picTitle as picTitle$language ";

		$joins		.= " left join urls_byLang ul$i on id=ul$i.urlId and ul$i.language='$language'";
	}

	// get total
	$queryStr	 = "select $columns from urls $joins $addCategories where 1 $conditions";
	$result	     = commonDoQuery ($queryStr);
	$total	     = commonQuery_numRows($result);

	// get details
	$queryStr = "select $columns from urls $joins $addCategories where 1 $conditions order by $sortBy $sortDir " . commonGetLimit ($xmlRequest);

	$result	     = commonDoQuery ($queryStr);

	// siteUrl
	$domainRow   = commonGetDomainRow ();
	$siteUrl     = commonGetDomainName($domainRow);

	$numRows    = commonQuery_numRows($result);

	$xmlResponse = "<items>";

	$pressText     = commonPhpEncode("לחץ כאן");

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		$id   		  = $row['id'];
		$url		  = commonValidXml($row['url']);
		$displayUrl	  = commonValidXml($row['displayUrl']);
		$target		  = $row['target'];
		$targetText	  = formatUrlTarget($target);
		$date		  = formatApplDate ($row['date']);

		$picFile   	   = commonValidXml($row['picFile']);
		$sourceFile	   = commonValidXml($row['sourceFile']);
	
		$fullFileName  = urlencode($row['picFile']);
		$fullFileName  = commonValidXml("$siteUrl/urlsFiles/$fullFileName");
	
		$show	 		= "";
		$delete	 		= "";

		if ($row['picFile'] != "")
		{
			$show 	= $pressText;
			$delete	= $pressText;
		}

		$isReady	= commonPhpEncode(($row['isReady' . $langsArray[0]] == 1) ? "כן" : "לא");

		$xmlResponse .=	"<item>				
							 <urlId>$id</urlId>
							 <url>$url</url>
							 <displayUrl>$displayUrl</displayUrl>
							 <target>$target</target>
							 <targetText>$targetText</targetText>
							 <date>$date</date>
							 <usedLangs>$usedLangs</usedLangs>
							 <isReadyText>$isReady</isReadyText>
							 <dimensionId></dimensionId>
							 <sourceFile>$sourceFile</sourceFile>
							 <formSourceFile>$sourceFile</formSourceFile>
							 <fullFileName>$fullFileName</fullFileName>
							 <show>$show</show>
							 <delete>$delete</delete>";

		$title  = "";
		$txt	= "";
		for ($l=0; $l<count($langsArray); $l++)
		{
			$language	 = $langsArray[$l];

			if ($row['title' . $language] != "" && $title == "")
				$title   = commonValidXml($row['title' . $language]);

			if ($row['txt' . $language] != "" && $txt == "")
				$txt   = commonValidXml(strip_tags($row['txt' . $language], "<br><br/>"));

		}
		$xmlResponse .=	"<title>$title</title>
						 <txt>$txt</txt>";

		for ($l=0; $l<count($langsArray); $l++)
		{
			$language	  = $langsArray[$l];

			$isReady	  = commonValidXml($row['isReady'.$language]);
			$picTitle	  = commonValidXml($row['picTitle'.$language]);
			$title		  = commonValidXml($row['title'.$language]);
			$txt		  = commonValidXml($row['txt'.$language]);
			
			$xmlResponse .=	"<isReady$language>$isReady</isReady$language>
							 <picTitle$language>$picTitle</picTitle$language>
							 <title$language>$title</title$language>
							 <txt$language>$txt</txt$language>";
		}

		$xmlResponse .= "</item>";
	}

	$xmlResponse .=	"</items>"												.
					commonGetTotalXml($xmlRequest,$numRows,$total);
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteUrl																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteUrl ($xmlRequest)
{
	$id = xmlParser_getValue($xmlRequest, "urlId");

	if ($id == "")
		trigger_error ("חסר קוד קישור לביצוע הפעולה");

	$queryStr = "select * from  categoriesItems where itemId = $id and type = 'url'";
	$result	  = commonDoQuery($queryStr);
	$row	  = commonQuery_fetchRow($result);
	$pos	  = $row['pos'];
	$catId	  = $row['categoryId'];

	$queryStr = "delete from urls where id = $id";
	commonDoQuery ($queryStr);

	$queryStr = "delete from urls_byLang where urlId = $id";
	commonDoQuery ($queryStr);

	$queryStr = "delete from categoriesItems where itemId = $id and type = 'url'";
	commonDoQuery ($queryStr);

	if ($pos != "")
	{
		$queryStr	= "update categoriesItems set pos = pos - 1 where categoryId = $catId and type = 'url' and pos > $pos";
		commonDoQuery($queryStr);
	}

	return "";	
}

/* ----------------------------------------------------------------------------------------------------	*/
/* addUrl																								*/
/* ----------------------------------------------------------------------------------------------------	*/
function addUrl ($xmlRequest)
{
	return (editUrl ($xmlRequest, "add"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* doesUrlExist																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function doesUrlExist ($id)
{
	$queryStr		= "select count(*) from urls where id=$id";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$count	     = $row[0];

	return ($count > 0);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getUrlNextId																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function getUrlNextId ()
{
	$queryStr	= "select max(id) from urls";
	$result		= commonDoQuery ($queryStr);
	$row		= commonQuery_fetchRow ($result);
	$id 		= $row[0] + 1;
	
	return "<urlId>$id</urlId>";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateUrl																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateUrl ($xmlRequest)
{
	editUrl ($xmlRequest, "update");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* editUrl																								*/
/* ----------------------------------------------------------------------------------------------------	*/
function editUrl ($xmlRequest, $editType)
{
	global $usedLangs, $tags, $langTags;
	global $userId;
	global $ibosHomeDir;

	for ($i=0; $i < count($tags); $i++)
	{
		eval ("\$$tags[$i] = addslashes(commonDecode(xmlParser_getValue(\$xmlRequest,\"$tags[$i]\")));");	
	}

	$date	 = formatApplToDB ($date);
	$urlId   = xmlParser_getValue($xmlRequest, "urlId");

	if ($editType == "add")
	{
		$queryStr	= "select max(id) from urls";
		$result		= commonDoQuery ($queryStr);
		$row		= commonQuery_fetchRow ($result);
		$urlId 		= $row[0] + 1;
	}

	$vals = Array();

	for ($i=0; $i < count($tags); $i++)
	{
		eval ("array_push (\$vals,\$$tags[$i]);");
	}
	
	// handle picture 
	# ------------------------------------------------------------------------------------------------------
	$sourceFile		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "sourceFile")));	
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
		$picFile 	= "${urlId}_size0.jpg";
	}

	list($picWidth, $picHeight, $bgColor) = commonGetDimensionDetails ($dimensionId);

	if ($editType == "update")
	{
		$loadedFile = "";

		if ($urlId == "")
			trigger_error ("חסר קוד חדשה לביצוע הפעולה");

		if (!doesUrlExist($urlId))
		{
			trigger_error ("קישור עם קוד זה ($urlId) לא קיים במערכת. לא ניתן לבצע את העדכון");
		}
		
		$queryStr = "update urls set ";

		for ($i=0; $i < count($tags); $i++)
		{
			$queryStr .= "$tags[$i] = '$vals[$i]',";
		}

		$queryStr = trim($queryStr, ",");

		if ($fileLoaded)
		{
			$queryStr .= ", picFile 	= '$picFile',
							sourceFile	= '$sourceFile'	";
		}
		else if ($fileDeleted)
		{
			$queryStr .= ", picFile 	= '',
							sourceFile	= ''	";
		}

		$queryStr .= " where id = $urlId ";

		commonDoQuery ($queryStr);

	}
	else
	{
		$loadedFile		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "loadedFile")));	

		$ibosUserId = commonGetIbosUserId ();

		$queryStr = "insert into urls (id, ibosUserId, " . join(",",$tags) . ", picFile, sourceFile) 
					 values ($urlId, '$ibosUserId', '" . join("','",$vals) . "', '$picFile', '$sourceFile')";
		commonDoQuery ($queryStr);


		// handle category
		$categoryId = xmlParser_getValue($xmlRequest, "categoryId");

		if ($categoryId != "")
		{
			$pos = xmlParser_getValue($xmlRequest, "pos");

			if ($pos == "") $pos = 1;

			$queryStr 	= "update categoriesItems set pos = pos + 1 where categoryId = $categoryId and type = 'url' and pos >= $pos";
			commonDoQuery ($queryStr);

			$queryStr = "insert into categoriesItems (itemId, categoryId, type, pos)
						 values ($urlId, $categoryId, 'url', $pos)";
			commonDoQuery ($queryStr);
		}
	}

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

		$queryStr		= "replace into urls_byLang (urlId, language," . join(",",$langTags) . ") 
						   values ($urlId, '$language', '" . join ("','", $vals) . "')";
	
		commonDoQuery ($queryStr);
	}
	
	// handle file
	$filePath 	= "$ibosHomeDir/html/SWFUpload/files/$userId";

	$domainRow	= commonGetDomainRow();

	$connId 	= commonFtpConnect($domainRow); 

	if ($fileLoaded)
	{
		$fileName = "${urlId}_size1.jpg";

		if ($picWidth != 0 && $picHeight != 0)
				commonPicResize("$filePath/$sourceFile", "/../../tmp/$fileName", $dimensionId);

		$upload = ftp_put($connId, "urlsFiles/$picFile", "$filePath/$sourceFile", FTP_BINARY); 

		if ($picWidth == 0 && $picHeight == 0)
		{
			$upload = ftp_put($connId, "urlsFiles/$fileName", "$filePath/$sourceFile", FTP_BINARY);
		}
		else
		{
			//picsToolsResize("$filePath/$sourceFile", $suffix, $picWidth, $picHeight, "/../../tmp/$fileName", $bgColor);
			$upload = ftp_put($connId, "urlsFiles/$fileName", "/../../tmp/$fileName", FTP_BINARY);
		}
		unlink("$filePath/$sourceFile");

	}
	else if ($fileDeleted)
	{
		$fileName = "${urlId}_size0.jpg";

		commonFtpDelete($connId, "urlsFiles/$fileName");
	}

	if ($loadedFile != "")
	{
		$upload = ftp_put($connId, "loadedFiles/$loadedFile", "$filePath/$loadedFile", FTP_BINARY);
	}

	commonFtpDisconnect ($connId);

 	// delete old files
	commonDeleteOldFiles ($filePath, 3600);	// 1 hour

	return "";
}

?>
