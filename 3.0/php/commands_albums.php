<?php

include "picsTools.php";

$pageTags 		= Array("id", "layoutId", "navParentId");

$pageLangTags	= Array("isReady", "title", "winTitle", "keywords", "description", "navTitle", "rewriteName");

$albumTags		= Array("id", "albumDate", "displayType", "numCols", "numRows", "withPaging", "numPages", "autoSwitch", "smallPicsSide", 
						"hSmallPicHeight", "hSmallPicWidth", "vSmallPicHeight", "vSmallPicWidth", 
						"hPicWidth", "hPicHeight", "vPicWidth", "vPicHeight", 
						"hSmallPicDimension", "vSmallPicDimension", "hPicDimension", "vPicDimension", "bgColor", "quality");

$albumLangTags	= Array("shortDesc", "txt");

$imageTags     	= Array("id", "fileType", "filename", "sourceFile", "filename2", "sourceFile2", "pos", "embedUrl");

$imageLangTags	= Array("title");

/* ----------------------------------------------------------------------------------------------------	*/
/* getAlbums																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function getAlbums ($xmlRequest)
{	
	global $usedLangs;
	$langsArray = explode(",",$usedLangs);

	$condition  = "";

	$category		= xmlParser_getValue($xmlRequest, "category");
	if ($category != "")
		$condition .= " and categoriesItems.categoryId = $category ";

	// get total
	$queryStr	 = "select count(*) from albums";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$total	     = $row[0];

	// get details
	$queryStr = "select albums.id, pages_byLang.title, count(albumImages.albumId) as countImages
				 from (albums, pages_byLang)
				 left join albumImages on albums.id = albumImages.albumId
				 left join categoriesItems on albums.id = itemId and categoriesItems.type = 'album' 
				 where albums.id = pages_byLang.pageId and language = '$langsArray[0]' 
				 $condition
				 group by albums.id
				 order by id desc " . commonGetLimit ($xmlRequest);

	$result	     = commonDoQuery ($queryStr);

	$numRows    = commonQuery_numRows($result);

	$xmlResponse = "<items>";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		$id   			= $row['id'];
		$title			= commonValidXml($row['title']);
		$countImages 	= $row['countImages'];

		$xmlResponse .=	"<item>
							 <id>$id</id>
							 <title>$title</title>
							 <countImages>$countImages</countImages>
						 </item>";
	}

	$xmlResponse .=	"</items>" .
					commonGetTotalXml($xmlRequest,$numRows,$total);
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getAlbumDetails																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getAlbumDetails ($xmlRequest)
{
	global $usedLangs, $pageTags, $pageLangTags, $albumTags, $albumLangTags;

	$id		= xmlParser_getValue($xmlRequest, "albumId");
	$action	= xmlParser_getValue($xmlRequest, "action");

	if ($id == "")
		trigger_error ("חסר קוד אלבום לביצוע הפעולה");

	$langsArray = explode(",",$usedLangs);

	$queryStr = "select albums.*, albums_byLang.*, pages.layoutId, pages_byLang.title, pages_byLang.isReady, pages.navParentId, navTitle, 
						pages_byLang.winTitle, pages_byLang.keywords, pages_byLang.description, pages_byLang.rewriteName 
		   		 from albums, albums_byLang, pages, pages_byLang
				 where albums.id = $id
				 and   albums.id = pages.id
				 and   pages.id  = pages_byLang.pageId and pages_byLang.language = albums_byLang.language
				 and   pages_byLang.pageId = albums_byLang.albumId";
	$result   = commonDoQuery ($queryStr);

	if (commonQuery_numRows($result) == 0)
		trigger_error ("אלבום קוד זה ($id) לא קיים במערכת. לא ניתן לבצע את הפעולה");

	$xmlResponse = "";

	// siteUrl
	$domainRow   = commonGetDomainRow ();
	$siteUrl     = commonGetDomainName($domainRow);

	while ($row = commonQuery_fetchRow($result))
	{
		$language = $row['language'];

		$langsArray = commonArrayRemove ($langsArray, $language);	

		if ($xmlResponse == "")
		{
			if ($row['hSmallPicDimension'] == 0) $row['hSmallPicDimension'] = "";
			if ($row['vSmallPicDimension'] == 0) $row['vSmallPicDimension'] = "";
			if ($row['hPicDimension'] 	   == 0) $row['hPicDimension'] 		= "";
			if ($row['vPicDimension'] 	   == 0) $row['vPicDimension'] 		= "";

			$row['albumDate']	= formatApplDate($row['albumDate']);

			for ($i=0; $i < count($albumTags); $i++)
			{
				eval ("\$$albumTags[$i] = \$row['$albumTags[$i]'];");
				eval ("\$$albumTags[$i] = commonValidXml(\$$albumTags[$i]);");
				eval ("\$xmlResponse .= \"<$albumTags[$i]>\$$albumTags[$i]</$albumTags[$i]>\";");
			}

			for ($i=0; $i < count($pageTags); $i++)
			{
				eval ("\$$pageTags[$i] = \$row['$pageTags[$i]'];");
				eval ("\$$pageTags[$i] = commonValidXml(\$$pageTags[$i]);");
				eval ("\$xmlResponse .= \"<$pageTags[$i]>\$$pageTags[$i]</$pageTags[$i]>\";");
			}
			
			$sourceFile	  =commonValidXml(addslashes($row['albumSourcePic']));

			$xmlResponse .= "<sourceFile>$sourceFile</sourceFile>
							 <formSourceFile>$sourceFile</formSourceFile>
							 <usedLangs>$usedLangs</usedLangs>
							 <albumId>$id</albumId>
							 <siteUrl>$siteUrl/index2.php</siteUrl>";
		}

		if ($action == "duplicateAlbum")
		{
			$row['title'] 		= "";
			$row['winTitle'] 	= "";
			$row['keywords']	= "";
			$row['description']	= "";
			$row['navTitle']	= "";
			$row['rewriteName']	= "";
			$row['shortDesc']	= "";
			$row['txt']			= "";
		}

		for ($i=0; $i < count($pageLangTags); $i++)
		{
			eval ("\$$pageLangTags[$i] = commonValidXml(\$row['$pageLangTags[$i]']);");
			eval ("\$xmlResponse .=	\"<$pageLangTags[$i]\$language>\$$pageLangTags[$i]</$pageLangTags[$i]\$language>\";");
		}


		for ($i=0; $i < count($albumLangTags); $i++)
		{
			eval ("\$$albumLangTags[$i] = commonValidXml(\$row['$albumLangTags[$i]']);");
			eval ("\$xmlResponse .=	\"<$albumLangTags[$i]\$language>\$$albumLangTags[$i]</$albumLangTags[$i]\$language>\";");
		}
	}

	// add missing languages
	// ------------------------------------------------------------------------------------------------
	for ($i=0; $i<count($langsArray); $i++)
	{
		$language	  = $langsArray[$i];

		for ($i=0; $i < count($pageLangTags); $i++)
		{
			eval ("\$xmlResponse .=	\"<$pageLangTags[$i]\$language><![CDATA[]]></$pageLangTags[$i]\$language>\";");
		}

		for ($j=0; $j < count($albumLangTags); $j++)
		{
			eval ("\$xmlResponse .=	\"<$albumLangTags[$j]\$language><![CDATA[]]></$albumLangTags[$j]\$language>\";");
		}
	}

	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* addAlbum																								*/
/* ----------------------------------------------------------------------------------------------------	*/
function addAlbum ($xmlRequest)
{
	return (editAlbum ($xmlRequest, "add"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* doesAlbumExist																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function doesAlbumExist ($id)
{
	$queryStr		= "select count(*) from albums where id=$id";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$count	     = $row[0];

	return ($count > 0);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getAlbumNextId																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getAlbumNextId ()
{
	$queryStr	= "select max(id) from pages";
	$result		= commonDoQuery ($queryStr);
	$row		= commonQuery_fetchRow ($result);
	$id 		= $row[0] + 1;
	
	return $id;
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateAlbum																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateAlbum ($xmlRequest)
{
	return (editAlbum ($xmlRequest, "update"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* editAlbum																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function editAlbum ($xmlRequest, $editType)
{
	global $usedLangs, $albumTags, $pageTags;
	global $userId;
	global $ibosHomeDir;

	$langsArray = explode(",",$usedLangs);

	for ($i=0; $i < count($pageTags); $i++)
	{
		eval ("\$$pageTags[$i] = commonDecode(xmlParser_getValue(\$xmlRequest,\"$pageTags[$i]\"));");	
	}

	for ($i=0; $i < count($albumTags); $i++)
	{
		eval ("\$$albumTags[$i] = commonDecode(xmlParser_getValue(\$xmlRequest,\"$albumTags[$i]\"));");	
	}

	$id		= xmlParser_getValue($xmlRequest, "albumId");

	if ($editType == "update")
	{
		if (!doesAlbumExist($id))
		{
			trigger_error ("אלבום עם קוד זה ($id) לא קיימת במערכת. לא ניתן לבצע את העדכון");
		}
	}
	else
	{
		$id = getAlbumNextId ();
	}

	for ($i=0; $i<count($langsArray); $i++)
	{
		$language		= $langsArray[$i];
		$rewriteName	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "rewriteName$language")));

		if (!commonCheckRewriteName($rewriteName, $id))
			trigger_error ("כתובת סטטית זו כבר קיימת");
	}

	$albumDate	= formatApplToDB($albumDate);

	// handle picture 
	$picSource		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "picSource")));	
	$dimensionId	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "dimensionId")));	

	$fileLoaded  	= false;

	$picFile 		= "";

	$suffix 		= "";
	if ($picSource != "")
	{
		$fileLoaded = true;
		
		$suffix	= commonFileSuffix($picSource);

		$picFile = "${id}_size0$suffix";

		//list($picWidth, $picHeight, $bgColor) = commonGetDimensionDetails ($dimensionId);
	}

	if ($suffix == "." . $picSource)	// wrong file name - don't load it
	{
		$fileLoaded = false;
		$picFile    = "";
	}

	$pageVals = Array();

	for ($i=0; $i < count($pageTags); $i++)
	{
		eval ("array_push (\$pageVals,\$$pageTags[$i]);");
	}
	
	$vals = Array();

	for ($i=0; $i < count($albumTags); $i++)
	{
		eval ("array_push (\$vals,\$$albumTags[$i]);");
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

		// albums table
		$queryStr = "update albums set ";

		for ($i=1; $i < count($albumTags); $i++)
		{
			$queryStr .= "$albumTags[$i] = '$vals[$i]',";
		}

		$queryStr = trim($queryStr, ",");

		if ($fileLoaded)
		{
			$queryStr .= ",	  albumPic 	 	 = '$picFile',
							  albumSourcePic = '$picSource' ";
		}

		$queryStr .= " where id = $id ";

		commonDoQuery ($queryStr);
	}
	else
	{
		$categoryId	= xmlParser_getValue($xmlRequest, "categoryId");

		if ($categoryId != "")
		{
			// get last pos
			$queryStr 	= "select max(pos) from categoriesItems where categoryId = $categoryId and type = 'album'";
			$result		= commonDoQuery ($queryStr);
			$row		= commonQuery_fetchRow ($result);
			$pos 		= $row[0] + 1;

			$queryStr = "insert into categoriesItems (itemId, categoryId, type, pos)
						 values ($id, $categoryId, 'album', $pos)";
			commonDoQuery ($queryStr);
		}

		$queryStr = "insert into pages (" . join(",",$pageTags) . ",type) values ('" . join("','",$pageVals) . "','album')";
		commonDoQuery ($queryStr);

		$queryStr = "insert into albums (" . join(",",$albumTags) . ", albumPic, albumSourcePic) values ('" . join("','",$vals) . "'";
		
		if ($fileLoaded)
		{
			$queryStr .= ", '$picFile', '$picSource'";
		}
		else
		{
			$queryStr .= ", '', ''";
		}

		$queryStr	.= ")";
		commonDoQuery ($queryStr);
	}

	# delete all languages rows
	# ------------------------------------------------------------------------------------------------------
	$queryStr = "delete from pages_byLang where pageId='$id'";
	commonDoQuery ($queryStr);
	
	$queryStr = "delete from albums_byLang where albumId='$id'";
	commonDoQuery ($queryStr);
	
	# add languages rows for this user
	# ------------------------------------------------------------------------------------------------------

	// remove Word garbage before saving to db
	$word1 = chr(194).chr(160);
	$word2 = chr(226).chr(128).chr(147);

	for ($i=0; $i<count($langsArray); $i++)
	{
		$language		= $langsArray[$i];

		$title 			= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "title$language")));
		$winTitle 		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "winTitle$language")));
		$description	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "description$language")));
		$keywords		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "keywords$language")));
		$rewriteName 	= commonFixRewriteName(addslashes(commonDecode(xmlParser_getValue($xmlRequest, "rewriteName$language"))));
		$isReady 		= xmlParser_getValue($xmlRequest, "isReady$language");

		if ($isReady == "") $isReady = 0;

		$queryStr	= "insert into pages_byLang (pageId, winTitle, title, language, isReady, rewriteName, description, keywords) 
					   values ('$id', '$winTitle', '$title','$language',$isReady, '$rewriteName', '$description', '$keywords')";
		commonDoQuery ($queryStr);

		$shortDesc	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "shortDesc$language")));

		$txt		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "txt$language")));

		$txt = str_replace($word1, "", $txt);
		$txt = str_replace($word2, "-", $txt);

		$queryStr	= "insert into albums_byLang (albumId, language, txt, shortDesc) values ($id, '$language', '$txt', '$shortDesc')";
		commonDoQuery ($queryStr);

	}
	
	// handle file
	$filePath = "$ibosHomeDir/html/SWFUpload/files/$userId/";

	$domainRow	= commonGetDomainRow();

	if ($fileLoaded)
	{
		$fileName = "${id}_size1.jpg";

		commonPicResize("$filePath/$picSource", "/../../tmp/$fileName", $dimensionId);

		$connId = commonFtpConnect($domainRow); 

		ftp_chdir($connId, "albumsFiles/");

		$upload = ftp_put($connId, $picFile, "$filePath/$picSource", FTP_BINARY); 

		if (!$upload) 
		   	echo "FTP upload has failed!";

		if ($dimensionId == 0 || $dimensionId == "")
		{
			$upload = ftp_put($connId, "$fileName", "$filePath/$picSource", FTP_BINARY);
		}
		else
		{
			//picsToolsResize("$filePath/$picSource", $suffix, $picWidth, $picHeight, "/../../tmp/$fileName", $bgColor);

			$upload = ftp_put($connId, "$fileName", "/../../tmp/$fileName", FTP_BINARY);
		}
		

		unlink("$filePath/$picSource");

		commonFtpDisconnect ($connId);
	} 

 	// delete old files
	commonDeleteOldFiles ($filePath, 3600);	// 1 hour

	fopen(commonGetDomainName($domainRow) . "/updateModRewrite.php","r");

	return "<albumId>$id</albumId>";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteAlbum																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteAlbum ($xmlRequest)
{
	$id  = xmlParser_getValue ($xmlRequest, "albumId");

	if ($id == "")
		trigger_error ("חסר קוד אלבום לביצוע הפעולה");
	
	return doDeleteAlbum ($id);
}

function doDeleteAlbum ($id)
{
	// find album images and delete them
	$queryStr	= "select id from albumImages where albumId=$id";
	$result		= commonDoQuery($queryStr);

	$ids	= array();
	while ($row = commonQuery_fetchRow($result))
	{
		array_push ($ids, $row['id']);
	}

	doDeleteAlbumImages ($ids);

	$domainRow = commonGetDomainRow ();
	commonConnectToUserDB ($domainRow);

	$queryStr =  "delete from albums where id=$id";
	commonDoQuery ($queryStr);

	$queryStr =  "delete from albums_byLang where albumId=$id";
	commonDoQuery ($queryStr);

	$queryStr =  "delete from pages where id=$id";
	commonDoQuery ($queryStr);

	$queryStr =  "delete from pages_byLang where pageId=$id";
	commonDoQuery ($queryStr);

	$queryStr =  "delete from categoriesItems where itemId=$id and type = 'album'";
	commonDoQuery ($queryStr);

	return "";
}


/* ----------------------------------------------------------------------------------------------------	*/
/* getAlbumImages																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getAlbumImages ($xmlRequest)
{	
	global $usedLangs;
	$langsArray = explode(",",$usedLangs);

	$albumId		= xmlParser_getValue($xmlRequest, "albumId");

	if ($albumId == "")
		return "<items></items>";

	// get total
	$queryStr	 = "select count(*) 
					from albumImages where albumImages.albumId = $albumId";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$total	     = $row[0];

	// get details
	$queryStr = "select albumImages.*, albumImages_byLang.*
				 from albumImages, albumImages_byLang
				 where albumImages.id = albumImages_byLang.imageId 
				 and   albumImages.albumId = $albumId and albumImages_byLang.language = '$langsArray[0]' 
				 order by id desc" . commonGetLimit ($xmlRequest);

	$result	     = commonDoQuery ($queryStr);

	$numRows    = commonQuery_numRows($result);

	$langsArray = explode(",",$usedLangs);

	$showPicText = commonEncode("לחץ להצגה");

	$xmlResponse = "<items>";

	$domainRow   = commonGetDomainRow ();
	$filePrefix  = commonGetDomainName($domainRow) . "/albumsFiles/";

	while ($row = commonQuery_fetchRow($result))
	{
		$language = $row['language'];

		$langsArray = commonArrayRemove ($langsArray, $language);	

		$id   		= $row['id'];
		$pos		= $row['pos'];
		$title		= commonValidXml($row['title']);
		$filename 	= commonValidXml(addslashes($row['filename']));
		$sourceFile = commonValidXml(addslashes($row['sourceFile']));

		$fullFilePath = $filePrefix . urlencode($row['filename']);
		$fullFilePath = commonValidXml($fullFilePath);

		$pic		= "$filePrefix/${albumId}_${id}_small.jpg";

		$showPic	= "";
		if ($row['fileType'] == "pic")
		{
			$showPic	  = "<span class='styleLink' onclick='$.fancybox (\"<img src=$pic width=130 />\", {padding: 0, hideOnContentClick: true, overlayShow: false, width: 130, height: 130, margin: 0})'>$showPicText</span>";

			$showPic	  = commonValidXml($showPic);
		}

		$xmlResponse .=	"<item>
							 <imageId>$id</imageId>
							 <pos>$pos</pos>
							 <title>$title</title>
							 <filename>$filename</filename>
							 <sourceFile>$sourceFile</sourceFile>
							 <fullFilePath>$fullFilePath</fullFilePath>
							 <showPic>$showPic</showPic>
						 </item>";
	}

	$xmlResponse .=	"</items>" .
					commonGetTotalXml($xmlRequest,$numRows,$total);
	
	// add missing languages
	// ------------------------------------------------------------------------------------------------
	for ($i=0; $i<count($langsArray); $i++)
	{
		$language	  = $langsArray[$i];

		$xmlResponse .= "<title$language><![CDATA[]]></title$language>";
	}

	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getAlbumImageDetails																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function getAlbumImageDetails ($xmlRequest)
{
	global $imageTags, $imageLangTags;
	global $usedLangs;

	$langsArray = explode(",",$usedLangs);

	$xmlResponse = "";

	$id		= xmlParser_getValue($xmlRequest, "imageId");

	if ($id == "")
		trigger_error ("חסר קוד תמונה לביצוע הפעולה");


	$queryStr = "select * from albumImages, albumImages_byLang
				 where albumImages.id = albumImages_byLang.imageId and albumImages.id = $id";
	$result   = commonDoQuery ($queryStr);

	if (commonQuery_numRows($result) == 0)
		trigger_error ("תמונה קוד זה ($id) לא קיים במערכת. לא ניתן לבצע את הפעולה");

	while ($row = commonQuery_fetchRow($result))
	{
		$language = $row['language'];

		$langsArray = commonArrayRemove ($langsArray, $language);	

		if ($xmlResponse == "")
		{
			$albumId = $row['albumId'];

			for ($i=0; $i < count($imageTags); $i++)
			{
				eval ("\$$imageTags[$i] = commonValidXml(addslashes(\$row['$imageTags[$i]']));");
				eval ("\$xmlResponse .= \"<$imageTags[$i]>\$$imageTags[$i]</$imageTags[$i]>\";");
			}

			$sourceFile = commonValidXml(addslashes($row['sourceFile']));
			$xmlResponse .= "<formSourceFile>$sourceFile</formSourceFile>";

			$sourceFile = commonValidXml(addslashes($row['sourceFile2']));
			$xmlResponse .= "<formSourceFile2>$sourceFile</formSourceFile2>";
		}

		for ($i=0; $i < count($imageLangTags); $i++)
		{
			eval ("\$$imageLangTags[$i] = commonValidXml(\$row['$imageLangTags[$i]']);");
			eval ("\$xmlResponse .= \"<$imageLangTags[$i]\$language>\$$imageLangTags[$i]</$imageLangTags[$i]\$language>\";");
		}
	}

	$xmlResponse	.= "<albumId>$albumId</albumId>
						<imageId>$id</imageId>
						<usedLangs>$usedLangs</usedLangs>";

	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getAlbumItemNextId																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function getAlbumItemNextId ()
{
	$queryStr	= "select max(id) from albumImages";
	$result		= commonDoQuery ($queryStr);
	$row		= commonQuery_fetchRow ($result);
	$id 		= $row[0] + 1;
	
	return "<itemId>$id</itemId>";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteAlbumImage																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteAlbumImage ($xmlRequest)
{
	$ids 	= xmlParser_getValues($xmlRequest, "imageId");

	if (count($ids) == 0)
		trigger_error ("חסר קוד תמונה לביצוע הפעולה");


	doDeleteAlbumImages ($ids);

	return "";	
}

function doDeleteAlbumImages ($ids)
{
	$files	= array();

	foreach ($ids as $id)
	{
		$queryStr = "select id, fileType, filename from albumImages where id = $id";
		$result	  = commonDoQuery($queryStr);
		$row	  = commonQuery_fetchRow($result);

		array_push ($files, $row);

		$queryStr = "delete from albumImages where id = $id";
		commonDoQuery ($queryStr);

		$queryStr = "delete from albumImages_byLang where imageId = $id";
		commonDoQuery ($queryStr);
	}

	$domainRow = commonGetDomainRow ();
	$connId    = commonFtpConnect    ($domainRow);

	foreach ($files as $fileRow)
	{
		$id		  = $fileRow['id'];
		$filename = $fileRow['filename'];
		$fileType = $fileRow['fileType'];

		commonFtpDelete ($connId, "albumsFiles/$filename");

		if ($fileType == "pic")
		{
			// Destination must be jpg
			$destParts = explode(".",$filename);
			$destParts[count($destParts)-1] = "jpg";
			$filename = join(".", $destParts);
			$filename = str_replace("size0","small",$filename);

			commonFtpDelete ($connId, "albumsFiles/$filename");

			$filename = str_replace("small","big", $filename);

			commonFtpDelete ($connId, "albumsFiles/$filename");
		}
	}
	
	commonFtpDisconnect($connId);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* addAlbumImage																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function addAlbumImage ($xmlRequest)
{
	return (editAlbumImage ($xmlRequest, "add"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateAlbumImage																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateAlbumImage ($xmlRequest)
{
	editAlbumImage ($xmlRequest, "update");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* editAlbumImage																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function editAlbumImage ($xmlRequest, $editType)
{
	global $usedLangs;
	global $userId;
	global $ibosHomeDir;

	$albumId		= xmlParser_getValue($xmlRequest, "albumId");
	$imageId		= xmlParser_getValue($xmlRequest, "imageId");
	$fileType		= xmlParser_getValue($xmlRequest, "fileType");
	$pos			= xmlParser_getValue($xmlRequest, "pos");
	$embedUrl		= addslashes(xmlParser_getValue($xmlRequest, "embedUrl"));

	if ($albumId == "")
		trigger_error ("חסר קוד אלבום");

	if ($fileType == "") $fileType = "pic";

	if ($editType == "add")
	{
		$queryStr   = "select max(id) from albumImages";
		$result		= commonDoQuery ($queryStr);
		$row		= commonQuery_fetchRow ($result);
		$imageId	= $row[0] + 1;
	}
	else
	{
		if ($imageId == "")
			trigger_error ("חסר קוד תמונה");
	}

	// handle files
	$sourceFile		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "sourceFile")));	
	$sourceFile2	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "sourceFile2")));	

	$file1			= "";
	$suffix 		= "";
	if ($sourceFile != "")
	{
		$suffix	= strtolower(commonFileSuffix($sourceFile));
	}

	$file2			= "";
	$suffix2 		= "";
	if ($sourceFile2 != "")
	{
		$suffix2 = strtolower(commonFileSuffix($sourceFile2));
	}

	// get album details
	$queryStr 		= "select * from albums where id = $albumId";
	$albumResult	= commonDoQuery ($queryStr);
	$albumRow		= commonQuery_fetchRow ($albumResult);

	if ($albumRow['bgColor'] == "")
		$albumRow['bgColor'] = "#FFFFFF";

	if ($albumRow['quality'] == 0) $albumRow['quality'] = 90;

	if ($editType == "add")
	{
		$file1	= $albumId . "_" . $imageId . "_size0$suffix";

		if (($fileType == "video" || $fileType == "embed") && $suffix2 != "")
			$file2	= $albumId . "_" . $imageId . "_size0$suffix2";
	
		$queryStr   = "insert into albumImages (id, albumId, fileType, pos, filename, sourceFile, filename2, sourceFile2, embedUrl)
					   values ('$imageId', '$albumId', '$fileType', '$pos', '$file1', '$sourceFile', '$file2', '$sourceFile2', '$embedUrl')";
		commonDoQuery ($queryStr);
	}
	else
	{
		$queryStr = "delete from albumImages_byLang where imageId='$imageId'";
		commonDoQuery ($queryStr);

		$queryStr = "update albumImages set fileType	= '$fileType',
											pos 	    = '$pos',
		   									embedUrl	= '$embedUrl'";

		if ($suffix != "")
		{
			$file1	= $albumId . "_" . $imageId . "_size0$suffix";
			
			$queryStr .= ",	filename    = '$file1',
							sourceFile  = '$sourceFile' ";
		} 

		if ($suffix2)
		{
			if ($fileType == "video" || $fileType == "embed")
				$file2	= $albumId . "_" . $imageId . "_size0$suffix2";
			else
				$file2  = "";
	
			$queryStr .= ",	filename2    = '$file2',
							sourceFile2  = '$sourceFile2' ";
		} 
	
		$queryStr .= " where id=$imageId";
		
		commonDoQuery ($queryStr);
	}

	# add languages rows for this image
	# ------------------------------------------------------------------------------------------------------
	$langsArray = explode(",",$usedLangs);

	for ($i=0; $i<count($langsArray); $i++)
	{
		$language	= $langsArray[$i];

		$title 		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "title$language")));

		$queryStr	= "insert into albumImages_byLang (imageId, albumId, language, title) values ('$imageId','$albumId','$language','$title')";
	
		commonDoQuery ($queryStr);
	}
	
	// handle file
	$filePath = "$ibosHomeDir/html/SWFUpload/files/$userId/";

	if ($suffix != "" || $suffix2 != "")
	{
		$domainRow	= commonGetDomainRow();

		commonConnectToUserDB ($domainRow);

		$connId = commonFtpConnect($domainRow); 

		ftp_chdir($connId, "albumsFiles/");

		if ($file1 != "")
		{
			if ($fileType == "pic")
			{
				uploadPic ($connId, $albumRow, $sourceFile, $suffix, $file1);
			}
			else
			{
				$upload = ftp_put($connId, $file1, "$filePath$sourceFile", FTP_BINARY); 
			}

		}

		if ($file2 != "")
		{
			uploadPic ($connId, $albumRow, $sourceFile2, $suffix2, $file2);
		}

		commonFtpDisconnect ($connId);
	}

 	// delete old files
	commonDeleteOldFiles ($filePath, 3600);	// 1 hour
}

function uploadPic ($connId, $albumRow, $sourceFile, $suffix, $destFile)
{
	global $ibosHomeDir;
	global $userId;

	$filePath = "$ibosHomeDir/html/SWFUpload/files/$userId/";

	list($widthOrig, $heightOrig) = getimagesize("$filePath$sourceFile");

	// Destination must be jpg
	$destParts = explode(".",$destFile);
	$destParts[count($destParts)-1] = "jpg";
	$resizedDestFile = join(".", $destParts);

	$resizedFileName = str_replace("size0","small",$resizedDestFile);

	// create small pic
	if ($albumRow['vSmallPicDimension'] != 0 || $albumRow['hSmallPicDimension'] != 0)
	{
		if ($widthOrig >= $heightOrig)
		{
			$dimensionId = $albumRow['hSmallPicDimension'];
		}
		else
		{
			$dimensionId = $albumRow['vSmallPicDimension'];
		}

		if ($dimensionId != 0)
		{
			commonPicResize ("$filePath$sourceFile", "/../../tmp/$resizedFileName", $dimensionId);

			$upload = ftp_put($connId, $resizedFileName, "/../../tmp/$resizedFileName", FTP_BINARY);
		}
	}
	else
	{
		$smallWidth  = -1;
		$smallHeight = -1;

		if (($albumRow['hSmallPicWidth'] != 0 || $albumRow['hSmallPicHeight'] != 0) && $widthOrig >= $heightOrig)
		{
			$smallWidth	 = $albumRow['hSmallPicWidth'];
			$smallHeight = $albumRow['hSmallPicHeight'];
		}
		else if (($albumRow['vSmallPicWidth'] != 0 || $albumRow['vSmallPicHeight'] != 0)  && $widthOrig < $heightOrig)
		{
			$smallWidth	 = $albumRow['vSmallPicWidth'];
			$smallHeight = $albumRow['vSmallPicHeight'];
		}

		if ($smallHeight != -1)
		{
			picsToolsResize("$filePath$sourceFile", $suffix, $smallWidth, $smallHeight, 
							"/../../tmp/$resizedFileName", $albumRow['bgColor'], $albumRow['quality']);

			$upload = ftp_put($connId, $resizedFileName, "/../../tmp/$resizedFileName", FTP_BINARY);
		}
	}

	// create big pic
	$resizedFileName = str_replace("size0","big", $resizedDestFile);

	if ($albumRow['vPicDimension'] != 0 || $albumRow['hPicDimension'] != 0)
	{
		if ($widthOrig >= $heightOrig)
		{
			$dimensionId = $albumRow['hPicDimension'];
		}
		else
		{
			$dimensionId = $albumRow['vPicDimension'];
		}

		if ($dimensionId != 0)
		{
			commonPicResize ("$filePath$sourceFile", "/../../tmp/$resizedFileName", $dimensionId);

			$upload = ftp_put($connId, $resizedFileName, "/../../tmp/$resizedFileName", FTP_BINARY);
		}
	}
	else
	{
		$bigWidth  = -1;
		$bigHeight = -1;

		if (($albumRow['hPicWidth'] != 0 || $albumRow['hPicHeight'] != 0) && $widthOrig >= $heightOrig)
		{	
			$bigWidth  = $albumRow['hPicWidth'];
			$bigHeight = $albumRow['hPicHeight'];
		}
		else if (($albumRow['vPicWidth'] != 0 || $albumRow['vPicHeight'] != 0)  && $widthOrig < $heightOrig)
		{
			$bigWidth  = $albumRow['vPicWidth'];
			$bigHeight = $albumRow['vPicHeight'];
		}

		if ($bigHeight != -1)
		{
			picsToolsResize("$filePath$sourceFile", $suffix, $bigWidth, $bigHeight, 
							"/../../tmp/$resizedFileName", $albumRow['bgColor'], $albumRow['quality']);
			$upload = ftp_put($connId, $resizedFileName, "/../../tmp/$resizedFileName", FTP_BINARY);
		}
	}

	// [22/6/2015 Amir]
	// We upload the original file at the end since commonPicResize may change the original image,
	// by adding watermark to it. If both small and big resized images have watermark - then both
	// watermarks will be added to the original.

	// upload orig file (size0)
	$upload = ftp_put($connId, $destFile, "$filePath$sourceFile", FTP_BINARY); 
}

?>
