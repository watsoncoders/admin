<?php

include "$ibosHomeDir/php/picsTools.php";

$tags 	  = Array("pos");
$langTags = Array("isReady", "title", "subtitle", "linkText", "url", "target");

/* ----------------------------------------------------------------------------------------------------	*/
/* getPics																								*/
/* ----------------------------------------------------------------------------------------------------	*/
function getPics ($xmlRequest)
{	
	global $usedLangs;

	$langsArray = explode(",",$usedLangs);
	
	$sortBy		= xmlParser_getValue($xmlRequest,"sortBy");
	if ($sortBy == "")
		$sortBy = "id";

	$sortDir	= xmlParser_getValue($xmlRequest,"sortDir");
	if ($sortDir == "")
		$sortDir = "desc";

	$conditions = "";

	$isReady = xmlParser_getValue($xmlRequest, "isReady");
	if ($isReady != "")
	{
		$conditions	.= " and isReady = $isReady";
	}

	$sql	= "select * from picsSlider, picsSlider_byLang where id = picId and language = '$langsArray[0]' $conditions";
	$result	= commonDoQuery ($sql);
	$total	= commonQuery_numRows($result);

	$sql   .= " order by $sortBy $sortDir " . commonGetLimit ($xmlRequest);

	$result	     = commonDoQuery ($sql);

	$numRows    = commonQuery_numRows($result);

	$xmlResponse = "<items>";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row 			= commonQuery_fetchRow($result);
			
		$id   		  	= $row['id'];
		$pos   		  	= $row['pos'];
		$url		  	= commonValidXml($row['url']);
		$target	  		= formatUrlTarget($row['target']);
		$title  		= commonValidXml($row['title']);
		$isReady	= ($row['isReady'] == 1) ? "כן" : "לא";

		$xmlResponse .=	"<item>				
							 <id>$id</id>
							 <title>$title</title>
							 <pos>$pos</pos>
							 <url>$url</url>
							 <target>$target</target>
							 <isReady>$isReady</isReady>
						  </item>";
	}

	$xmlResponse .=	"</items>"												.
					commonGetTotalXml($xmlRequest,$numRows,$total);
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getPicDetails																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getPicDetails ($xmlRequest)
{
	global $usedLangs, $tags, $langTags;

	$id		= xmlParser_getValue($xmlRequest, "id");

	if ($id == "")
		trigger_error ("חסר קוד תמונה לביצוע הפעולה");

	$langsArray = explode(",",$usedLangs);

	$queryStr = "select * from picsSlider, picsSlider_byLang where id = picId and id = $id";
	$result   = commonDoQuery ($queryStr);

	if (commonQuery_numRows($result) == 0)
		trigger_error ("תמונה עם קוד זה ($id) לא קיימת במערכת. לא ניתן לבצע את הפעולה");

	$xmlResponse = "";

	$pressText     = "לחץ כאן";

	// siteUrl
	$domainRow   = commonGetDomainRow ();
	$siteUrl     = commonGetDomainName($domainRow);

	while ($row = commonQuery_fetchRow($result))
	{
		$language = $row['language'];

		$langsArray = commonArrayRemove ($langsArray, $language);	

		if ($xmlResponse == "")
		{
			for ($i=0; $i < count($tags); $i++)
			{
				eval ("\$$tags[$i] = \$row['$tags[$i]'];");
				eval ("\$$tags[$i] = commonValidXml(\$$tags[$i]);");
				eval ("\$xmlResponse .= \"<$tags[$i]>\$$tags[$i]</$tags[$i]>\";");
			}

			$sourceFile	  = commonValidXml(addslashes($row['sourceFile']));

			$show	 		= "";
			$delete	 		= "";

			if ($row['picFile'] != "")
			{
				$show 	= $pressText;
				$delete	= $pressText;
			}

			$fullFileName  = urlencode($row['picFile']);
			$fullFileName  = commonValidXml("$siteUrl/picsSlider/$fullFileName");

			$xmlResponse .= "<sourceFile>$sourceFile</sourceFile>
							 <formSourceFile>$sourceFile</formSourceFile>
							 <fullFileName>$fullFileName</fullFileName>
							 <usedLangs>$usedLangs</usedLangs>
							 <id>$id</id>
							 <siteUrl>$siteUrl/index2.php</siteUrl>
							 <show>$show</show>
							 <delete>$delete</delete>";
		}

		for ($i=0; $i < count($langTags); $i++)
		{
			eval ("\$$langTags[$i] = commonValidXml(\$row['$langTags[$i]']);");
			eval ("\$xmlResponse .=	\"<$langTags[$i]\$language>\$$langTags[$i]</$langTags[$i]\$language>\";");
		}

		list($title_yType, 	  $title_yVal, 	  $title_xType, 	$title_xVal)	= cssToPos($row['titlePos']);
		list($subtitle_yType, $subtitle_yVal, $subtitle_xType,  $subtitle_xVal)	= cssToPos($row['subtitlePos']);
		list($link_yType, 	  $link_yVal, 	  $link_xType, 		$link_xVal)		= cssToPos($row['linkPos']);

		$xmlResponse	.= "<title_yType$language>$title_yType</title_yType$language>
							<title_yVal$language>$title_yVal</title_yVal$language>
							<title_xType$language>$title_xType</title_xType$language>
							<title_xVal$language>$title_xVal</title_xVal$language>
							<subtitle_yType$language>$subtitle_yType</subtitle_yType$language>
							<subtitle_yVal$language>$subtitle_yVal</subtitle_yVal$language>
							<subtitle_xType$language>$subtitle_xType</subtitle_xType$language>
							<subtitle_xVal$language>$subtitle_xVal</subtitle_xVal$language>
							<link_yType$language>$link_yType</link_yType$language>
							<link_yVal$language>$link_yVal</link_yVal$language>
							<link_xType$language>$link_xType</link_xType$language>
							<link_xVal$language>$link_xVal</link_xVal$language>";
	}

	// add missing languages
	// ------------------------------------------------------------------------------------------------
	for ($i=0; $i<count($langsArray); $i++)
	{
		$language	  = $langsArray[$i];

		for ($i=0; $i < count($langTags); $i++)
		{
			eval ("\$xmlResponse .=	\"<$langTags[$i]\$language><![CDATA[]]></$langTags[$i]\$language>\";");
		}
	}

	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deletePic																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function deletePic ($xmlRequest)
{
	$id = xmlParser_getValue($xmlRequest, "id");

	if ($id == "")
		trigger_error ("חסר קוד תמונה לביצוע הפעולה");

	$sql = "delete from picsSlider where id = $id";
	commonDoQuery ($sql);

	$sql = "delete from picsSlider_byLang where picId = $id";
	commonDoQuery ($sql);

	return "";	
}

/* ----------------------------------------------------------------------------------------------------	*/
/* addPic																								*/
/* ----------------------------------------------------------------------------------------------------	*/
function addPic ($xmlRequest)
{
	return (editPic ($xmlRequest, "add"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updatePic																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function updatePic ($xmlRequest)
{
	editPic ($xmlRequest, "update");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* editPic																								*/
/* ----------------------------------------------------------------------------------------------------	*/
function editPic ($xmlRequest, $editType)
{
	global $usedLangs, $tags, $langTags;
	global $userId;
	global $ibosHomeDir;

	for ($i=0; $i < count($tags); $i++)
	{
		eval ("\$$tags[$i] = addslashes(commonDecode(xmlParser_getValue(\$xmlRequest,\"$tags[$i]\")));");	
	}

	$id   = xmlParser_getValue($xmlRequest, "id");

	if ($editType == "add")
	{
		$sql		= "select max(id) from picsSlider";
		$result		= commonDoQuery ($sql);
		$row		= commonQuery_fetchRow ($result);
		$id 		= $row[0] + 1;
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
		$picFile 	= "${id}_size0.jpg";
	}

	list($picWidth, $picHeight, $bgColor) = commonGetDimensionDetails ($dimensionId);

	$langsArray = explode(",",$usedLangs);

	if ($editType == "update")
	{
		if ($id == "")
			trigger_error ("חסר קוד לביצוע הפעולה");

		$sql		= "select count(*) from picsSlider where id=$id";
		$result	    = commonDoQuery ($sql);
		$row	    = commonQuery_fetchRow($result);

		if ($row[0] === 0)
		{
			trigger_error ("תמונה עם קוד זה ($id) לא קיימת במערכת. לא ניתן לבצע את העדכון");
		}
		
		$sql = "update picsSlider set ";

		for ($i=0; $i < count($tags); $i++)
		{
			$sql .= "$tags[$i] = '$vals[$i]',";
		}

		$sql = trim($sql, ",");

		if ($fileLoaded)
		{
			$sql .= ", picFile 		= '$picFile',
					   sourceFile	= '$sourceFile'	";
		}
		else if ($fileDeleted)
		{
			$sql .= ", picFile 		= '',
					   sourceFile	= ''	";
		}

		$sql .= " where id = $id ";
		commonDoQuery ($sql);

		for ($i=0; $i<count($langsArray); $i++)
		{
			$sql		= "update picsSlider_byLang set ";

			$language		= $langsArray[$i];

			for ($j=0; $j < count($langTags); $j++)
			{
				eval ("\$val = addslashes(commonDecode(xmlParser_getValue(\$xmlRequest,\"$langTags[$j]\$language\")));");	

				$sql	.= "$langTags[$j] = '$val',";
			}		

			$titlePos	= posToCss (xmlParser_getValue($xmlRequest, "title_yType$language"),
									xmlParser_getValue($xmlRequest, "title_yVal$language"),	
									xmlParser_getValue($xmlRequest, "title_xType$language"),
									xmlParser_getValue($xmlRequest, "title_xVal$language"));

			$subtitlePos= posToCss (xmlParser_getValue($xmlRequest, "subtitle_yType$language"),
									xmlParser_getValue($xmlRequest, "subtitle_yVal$language"),	
									xmlParser_getValue($xmlRequest, "subtitle_xType$language"),
									xmlParser_getValue($xmlRequest, "subtitle_xVal$language"));

			$linkPos	= posToCss (xmlParser_getValue($xmlRequest, "link_yType$language"),
								    xmlParser_getValue($xmlRequest, "link_yVal$language"),	
									xmlParser_getValue($xmlRequest, "link_xType$language"),
									xmlParser_getValue($xmlRequest, "link_xVal$language"));

			$sql	.= "linkPos = '$linkPos', titlePos = '$titlePos', subtitlePos = '$subtitlePos' where picId = $id and language = '$language'";
			commonDoQuery ($sql);
		}
	}
	else
	{
		$sql = "insert into picsSlider (id, " . join(",",$tags) . ", picFile, sourceFile) 
				values ($id, '" . join("','",$vals) . "', '$picFile', '$sourceFile')";
		commonDoQuery ($sql);

		for ($i=0; $i<count($langsArray); $i++)
		{
			$language		= $langsArray[$i];

			$vals = Array();
			for ($j=0; $j < count($langTags); $j++)
			{
				eval ("\$$langTags[$j] = addslashes(commonDecode(xmlParser_getValue(\$xmlRequest,\"$langTags[$j]\$language\")));");	
				eval ("array_push (\$vals,\$$langTags[$j]);");
			}		

			$titlePos	= posToCss (xmlParser_getValue($xmlRequest, "title_yType$language"),
									 xmlParser_getValue($xmlRequest, "title_yVal$language"),	
									 xmlParser_getValue($xmlRequest, "title_xType$language"),
									 xmlParser_getValue($xmlRequest, "title_xVal$language"));

			$subtitlePos= posToCss (xmlParser_getValue($xmlRequest, "subttitle_yType$language"),
									 xmlParser_getValue($xmlRequest, "subttitle_yVal$language"),	
									 xmlParser_getValue($xmlRequest, "subttitle_xType$language"),
									 xmlParser_getValue($xmlRequest, "subttitle_xVal$language"));

			$linkPos	= posToCss (xmlParser_getValue($xmlRequest, "link_yType$language"),
									 xmlParser_getValue($xmlRequest, "link_yVal$language"),	
									 xmlParser_getValue($xmlRequest, "link_xType$language"),
									 xmlParser_getValue($xmlRequest, "link_xVal$language"));

			$sql		= "insert into picsSlider_byLang (picId, language," . join(",",$langTags) . ", linkPos, titlePos, subtitlePos) 
						   values ($id, '$language', '" . join ("','", $vals) . "', '$linkPos', '$titlePos', '$subtitlePos')";
			commonDoQuery ($sql);
		}
	}

	// handle file
	$filePath 	= "$ibosHomeDir/html/SWFUpload/files/$userId";

	$domainRow	= commonGetDomainRow();

	$connId 	= commonFtpConnect($domainRow); 

	if ($fileLoaded)
	{
		$fileName = "${id}_size1.jpg";

		if ($picWidth != 0 && $picHeight != 0)
				commonPicResize("$filePath/$sourceFile", "/../../tmp/$fileName", $dimensionId);

		$upload = ftp_put($connId, "picsSlider/$picFile", "$filePath/$sourceFile", FTP_BINARY); 

		if ($picWidth == 0 && $picHeight == 0)
		{
			$upload = ftp_put($connId, "picsSlider/$fileName", "$filePath/$sourceFile", FTP_BINARY);
		}
		else
		{
			//picsToolsResize("$filePath/$sourceFile", $suffix, $picWidth, $picHeight, "/../../tmp/$fileName", $bgColor);
			$upload = ftp_put($connId, "picsSlider/$fileName", "/../../tmp/$fileName", FTP_BINARY);
		}
		unlink("$filePath/$sourceFile");

	}
	else if ($fileDeleted)
	{
		$fileName = "${id}_size0.jpg";

		commonFtpDelete($connId, "picsSlider/$fileName");
	}

	commonFtpDisconnect ($connId);

 	// delete old files
	commonDeleteOldFiles ($filePath, 3600);	// 1 hour

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* posToCss																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function posToCss ($yType, $yVal, $xType, $xVal)
{
	$cssPos	= "";

	if ($yType != "" && $xType != "")
	{
		$cssPos .= "$yType:${yVal}px;$xType:${xVal}px";
	}

	return $cssPos;
}

/* ----------------------------------------------------------------------------------------------------	*/
/* cssToPos																								*/
/* ----------------------------------------------------------------------------------------------------	*/
function cssToPos ($cssPos)
{
	$yType	= "";
	$yVal	= "";
	$xType	= "";
	$xVal	= "";

	if ($cssPos != "")
	{
		list($y, $x) = explode(";", $cssPos);

		$y	= trim($y, "px");
		$x	= trim($x, "px");

		list($yType, $yVal) = explode(":", $y);
		list($xType, $xVal) = explode(":", $x);
	}

	return array($yType, $yVal, $xType, $xVal);
}

?>
