<?php

include "$ibosHomeDir/php/picsTools.php";

$tags 	  = Array("id", "type", "urlOrPageId", "date", "time", "isNewWin", "status", "membersOnly");
$langTags = Array("title", "source", "txt", "keywords");

/* ----------------------------------------------------------------------------------------------------	*/
/* getNews																								*/
/* ----------------------------------------------------------------------------------------------------	*/
function getNews ($xmlRequest)
{	
	global $usedLangs;
	$langsArray = explode(",",$usedLangs);

	$condition  = commonAddIbosUserCondition("news");

	$status		= xmlParser_getValue($xmlRequest, "status");
	if ($status != "")
		$condition .= " and status = '$status' ";

	$categoryId		= xmlParser_getValue($xmlRequest, "categoryId");
	if ($categoryId != "")
		$condition .= " and categoriesItems.categoryId = $categoryId ";

	// get total
	$queryStr	 = "select count(*) from news 
					left join categoriesItems on news.id = categoriesItems.itemId and categoriesItems.type = 'news'
					where 1 $condition";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$total	     = $row[0];

	// get details
	$queryStr = "select distinct news.*, news_byLang.title, forum.name as forumName, pages.staticname as staticname, pages_byLang.title as pageTitle
				 from news
				 left join news_byLang on news.id = news_byLang.newsId and news_byLang.language = '$langsArray[0]'
				 left join pages on news.urlOrPageId = pages.id
				 left join forum on news.urlOrPageId = forum.id
				 left join pages_byLang on news.urlOrPageId = pages_byLang.pageId  
						 				  and pages_byLang.language = '$langsArray[0]'
				 left join categoriesItems on news.id = categoriesItems.itemId and categoriesItems.type = 'news'
				 where 1 $condition
				 order by id desc " . commonGetLimit ($xmlRequest);

	$result	     = commonDoQuery ($queryStr);

	$numRows    = commonQuery_numRows($result);

	$xmlResponse = "<items>";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		$id   		  = $row['id'];
		$type  		  = $row['type'];
		$typeText	  = formatNewsType($type);
		$status 	  = formatNewsStatus($row['status']);
		$title		  = commonValidXml($row['title']);
		$urlOrPageId  = commonValidXml ($row['urlOrPageId'],true);

		if ($row['type'] == "forum")
		{
			$urlOrPageId = $row['urlOrPageId'] . " - " . commonValidXml ($row['forumName']);
		}
		else if ($row['type'] == "file")
		{
			$urlOrPageId = $row['urlOrPageId'];
		}
		else if ($row['type'] == "")
		{
			$urlOrPageId = "";
		}
		else if ($row['type'] != "url")
		{
			if ($row['pageTitle'] != "")
				$urlOrPageId = $row['urlOrPageId'] . " - " . commonValidXml ($row['pageTitle']);
			else
				$urlOrPageId = $row['urlOrPageId'] . " - " . commonValidXml ($row['staticname']);
		}

		$date 		= formatApplDate($row['date']);

		$xmlResponse .=	"<item>
							 <newsId>$id</newsId>
				 			 <type>$type</type>
							 <typeText>$typeText</typeText>
							 <status>$status</status>
				 			 <title>$title</title>
							 <urlOrPageId>$urlOrPageId</urlOrPageId>
							 <date>$date</date>
						 </item>";
	}

	$xmlResponse .=	"</items>" .
					commonGetTotalXml($xmlRequest,$numRows,$total);
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getNewsDetails																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getNewsDetails ($xmlRequest)
{
	global $usedLangs, $tags, $langTags;

	$id		= xmlParser_getValue($xmlRequest, "id");

	if ($id == "")
		trigger_error ("חסר קוד חדשה לביצוע הפעולה");


	$queryStr = "select * from news, news_byLang
				 where news.id = news_byLang.newsId
				 and   news.id = $id";
	$result   = commonDoQuery ($queryStr);

	if (commonQuery_numRows($result) == 0)
		trigger_error ("חדשה קוד זה ($id) לא קיים במערכת. לא ניתן לבצע את הפעולה");

	$langsArray = explode(",",$usedLangs);

	// siteUrl
	$domainRow   = commonGetDomainRow ();
	$siteUrl     = commonGetDomainName($domainRow);

	commonConnectToUserDB ($domainRow);

	$xmlResponse = "";

	while ($row = commonQuery_fetchRow($result))
	{
		$language = $row['language'];

		$langsArray = commonArrayRemove ($langsArray, $language);	

		if ($xmlResponse == "")
		{
			for ($i=0; $i < count($tags); $i++)
			{
				eval ("\$$tags[$i] = \$row['$tags[$i]'];");

			}

			$url		 = "";
			$page		 = "";
		
			if ($type == "url")
				$url  = commonValidXml($urlOrPageId);
			else
				$page = $urlOrPageId;
		
			$xmlResponse .= "<newsId>$id</newsId>
							 <url>$url</url>
							 <page>$page</page>";

			$date 		= formatApplDate($date);

			$time		= formatApplTime($row['time']);

			for ($i=0; $i < count($tags); $i++)
			{
				eval ("\$$tags[$i] = commonValidXml(\$$tags[$i]);");

				eval ("\$xmlResponse .= \"<$tags[$i]>\$$tags[$i]</$tags[$i]>\";");
			}
			
			$picFile   	   = commonValidXml($row['picFile']);
			$picSource	   = commonCData(commonEncode($row['picSource']));
			$linkToFile	    = "$siteUrl/newsFiles/$row[picFile]";

			$pressText     = commonPhpEncode("לחץ כאן");

			$show	 = "";
			$delete  = "";

			if ($row['picFile']  != "") 
			{
				$show   = $pressText;
				$delete = $pressText;
			}
			
			$xmlResponse .= "<siteUrl>$siteUrl/index2.php</siteUrl>
							 <dimensionId></dimensionId>
							 <picFile>$picFile</picFile>
							 <formSourceFile>$picSource</formSourceFile>
							 <show>$show</show>
							 <delete>$delete</delete>
							 <linkToFile>$linkToFile</linkToFile>";
		}

		for ($i=0; $i < count($langTags); $i++)
		{
			eval ("\$$langTags[$i] = commonValidXml(\$row['$langTags[$i]']);");
			eval ("\$xmlResponse .=	\"<$langTags[$i]\$language>\$$langTags[$i]</$langTags[$i]\$language>\";");
		}
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
	}

	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* addNews																								*/
/* ----------------------------------------------------------------------------------------------------	*/
function addNews ($xmlRequest)
{
	return (editNews ($xmlRequest, "add"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* doesNewsExist																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function doesNewsExist ($id)
{
	$queryStr		= "select count(*) from news where id=$id";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$count	     = $row[0];

	return ($count > 0);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getNewsNextId																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getNewsNextId ()
{
	$queryStr	= "select max(id) from news";
	$result		= commonDoQuery ($queryStr);
	$row		= commonQuery_fetchRow ($result);
	$id 		= $row[0] + 1;
	
	return "<newsId>$id</newsId>";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateNews																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateNews ($xmlRequest)
{
	editNews ($xmlRequest, "update");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* editNews																								*/
/* ----------------------------------------------------------------------------------------------------	*/
function editNews ($xmlRequest, $editType)
{
	global $usedLangs, $tags, $langTags;
	global $userId;
	global $ibosHomeDir;

	for ($i=0; $i < count($tags); $i++)
	{
		eval ("\$$tags[$i] = commonDecode(xmlParser_getValue(\$xmlRequest,\"$tags[$i]\"));");	
	}

	$id   = xmlParser_getValue($xmlRequest, "newsId");

	if ($editType == "add")
	{
		$queryStr	= "select max(id) from news";
		$result		= commonDoQuery ($queryStr);
		$row		= commonQuery_fetchRow ($result);
		$id 		= $row[0] + 1;
	}

	$date = formatApplToDB ("$date 00:00:00");

	$page = xmlParser_getValue($xmlRequest, "page");
	$url  = addslashes(commonDecode(xmlParser_getValue($xmlRequest, "url")));

	if ($type == "url" || $type == "onclick")
		$urlOrPageId = $url;
	else
		$urlOrPageId = $page;

	$vals = Array();

	for ($i=0; $i < count($tags); $i++)
	{
		eval ("array_push (\$vals,\$$tags[$i]);");
	}
	
	// handle picture 
	# ------------------------------------------------------------------------------------------------------
	$picSource		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "picSource")));	
	$fileDeleted	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "fileDeleted")));	
	$dimensionId	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "dimensionId")));	

	if ($dimensionId == "") $dimensionId = 0;

	$fileDeleted	= ($fileDeleted == "1");
	$fileLoaded  	= false;

	$picFile 		= "";

	$suffix 		= "";
	if ($picSource != "")
	{
		$fileLoaded = true;
		$suffix		= commonFileSuffix ($picSource);
		$picFile 	= "${id}_size0.jpg";
	}

	list($picWidth, $picHeight, $bgColor) = commonGetDimensionDetails ($dimensionId);
	
	if ($editType == "update")
	{
		if ($id == "")
			trigger_error ("חסר קוד חדשה לביצוע הפעולה");

		if (!doesNewsExist($id))
		{
			trigger_error ("חדשה עם קוד זה ($id) לא קיים במערכת. לא ניתן לבצע את העדכון");
		}
		
		$queryStr = "update news set ";

		for ($i=1; $i < count($tags); $i++)
		{
			$queryStr .= "$tags[$i] = '$vals[$i]',";
		}

		$queryStr = trim($queryStr, ",");

		if ($fileLoaded)
		{
			$queryStr .= ", picFile 	= '$picFile',
							picSource	= '$picSource'	";
		}
		else if ($fileDeleted)
		{
			$queryStr .= ", picFile 	= '',
							picSource	= ''	";
		}

		$queryStr .= " where id = $id ";

		commonDoQuery ($queryStr);

	}
	else
	{
		$ibosUserId = commonGetIbosUserId ();

		$queryStr = "insert into news (ibosUserId, " . join(",",$tags) . ", picFile, picSource) 
					 values ('$ibosUserId', '" . join("','",$vals) . "', '$picFile', '$picSource')";
		commonDoQuery ($queryStr);


		// handle category
		$categoryId = xmlParser_getValue($xmlRequest, "categoryId");

		if ($categoryId != "")
		{
			// get last pos
			$queryStr = "select max(pos) from categoriesItems where categoryId = $categoryId and type = 'news'";
			$result		= commonDoQuery ($queryStr);
			$row		= commonQuery_fetchRow ($result);
			$pos 		= $row[0] + 1;

			$queryStr = "insert into categoriesItems (itemId, categoryId, type, pos)
						 values ($id, $categoryId, 'news', $pos)";
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

		$queryStr		= "replace into news_byLang (newsId, language," . join(",",$langTags) . ") 
						   values ($id, '$language', '" . join ("','", $vals) . "')";
	
		commonDoQuery ($queryStr);
	}
	
	// handle file
	$filePath = "$ibosHomeDir/html/SWFUpload/files/$userId";

	if ($fileLoaded)
	{
		$domainRow	= commonGetDomainRow();

		$connId = commonFtpConnect($domainRow); 

		ftp_chdir ($connId, "newsFiles");

		$upload = ftp_put($connId, $picFile, "$filePath/$picSource", FTP_BINARY); 

		$fileName = "${id}_size0.jpg";

		if ($picWidth == 0 && $picHeight == 0)
		{
			$upload = ftp_put($connId, "$fileName", "$filePath/$picSource", FTP_BINARY);
		}
		else
		{
			picsToolsResize("$filePath/$picSource", $suffix, $picWidth, $picHeight, "/../../tmp/$fileName", $bgColor);
			$upload = ftp_put($connId, "$fileName", "/../../tmp/$fileName", FTP_BINARY);
		}
		unlink("$filePath/$picSource");

		commonFtpDisconnect ($connId);
	}
	else if ($fileDeleted)
	{
		$domainRow	= commonGetDomainRow();

		$connId = commonFtpConnect($domainRow); 

		$fileName = "${id}_size0.jpg";

		commonFtpDelete($connId, "$fileName");
	}

 	// delete old files
	commonDeleteOldFiles ($filePath, 3600);	// 1 hour

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteNews																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteNews ($xmlRequest)
{
	$id = xmlParser_getValue($xmlRequest, "id");

	if ($id == "")
		trigger_error ("חסר קוד חדשה לביצוע הפעולה");

	$queryStr = "delete from news where id = $id";
	commonDoQuery ($queryStr);

	$queryStr = "delete from news_byLang where newsId = $id";
	commonDoQuery ($queryStr);

	$queryStr = "delete from categoriesItems where itemId = $id and type = 'news'";
	commonDoQuery ($queryStr);

	return "";	
}

?>
