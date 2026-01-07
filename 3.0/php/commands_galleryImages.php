<?php

include "$ibosHomeDir/php/picsTools.php";

/* ----------------------------------------------------------------------------------------------------	*/
/* getGalleryImages																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getGalleryImages ($xmlRequest)
{	
	$condition  = "";

	$sortBy		= xmlParser_getValue($xmlRequest,"sortBy");

	if ($sortBy == "")
		$sortBy = "id";

	$sortDir	= xmlParser_getValue($xmlRequest,"sortDir");
	if ($sortDir == "")
		$sortDir = "desc";

	$galleryId		= xmlParser_getValue($xmlRequest, "galleryId");

	if ($galleryId != "")
		$condition = " where galleryImages.galleryId = $galleryId";

	// get total
	$queryStr	 = "select count(*) from galleryImages $condition";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$total	     = $row[0];

	// get details
	$queryStr    = "select galleryImages.id as id, galleryImages.filename as filename, 
						   galleryImages.sourceFile as sourceFile, galleryImages.pos as pos, 
				galleryImages_byLang.title as title
				from galleryImages
				left join galleryImages_byLang on galleryImages.id = galleryImages_byLang.galleryImageId and galleryImages_byLang.language = 'HEB'
				$condition
				order by $sortBy $sortDir " . commonGetLimit ($xmlRequest);
	$result	     = commonDoQuery ($queryStr);

	$numRows    = commonQuery_numRows($result);
	
	$domainRow   = commonGetDomainRow ();
	$filePrefix  = commonGetDomainName($domainRow) . "/galleries/gallery$galleryId/images/";
	
	$showPicText = commonEncode("לחץ להצגה");

	$xmlResponse = "<items>";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row 		  = commonQuery_fetchRow($result);
			
		$id   		  = $row['id'];
		$title 		  = commonValidXml ($row['title'],true);
		$pos   		  = $row['pos'];
		$filename  	  = commonValidXml ($row['filename'],true);
		$sourceFile	  = commonValidXml (addslashes($row['sourceFile']));

		$fullFilePath = $filePrefix . urlencode($row['filename']);

		$pic		 = str_replace("images/", "thumbs/", $fullFilePath);
		
		$showPic	  = "<span class='styleLink' onclick='$.fancybox (\"<img src=$pic width=130 height=130 />\", {padding: 0, hideOnContentClick: true, overlayShow: false, width: 130, height: 130, margin: 0})'>$showPicText</span>";

		$showPic	  = commonValidXml($showPic);

		$fullFilePath = commonValidXml($fullFilePath);

		if ($pos == 0) 
			$pos = "";

		$xmlResponse .=	"<item>"											.
							"<id>$id</id>"	 								. 
							"<title>$title</title>"							. 
							"<sourceFile>$sourceFile</sourceFile>"			. 
							"<filename>$filename</filename>"				. 
							"<pos>$pos</pos>"								.
						    "<fullFilePath>$fullFilePath</fullFilePath>"	.
							"<showPic>$showPic</showPic>"				.
						"</item>";
	}

	$xmlResponse .=	"</items>"												.
					commonGetTotalXml($xmlRequest,$numRows,$total);
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* doesGalleryImageExist																				*/
/* ----------------------------------------------------------------------------------------------------	*/
function doesGalleryImageExist ($id)
{
	$queryStr		= "select count(*) from galleryImages where id=$id";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$count	     = $row[0];

	return ($count > 0);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getGalleryImageDetails																				*/
/* ----------------------------------------------------------------------------------------------------	*/
function getGalleryImageDetails ($xmlRequest)
{
	global $usedLangs;

	$id		= xmlParser_getValue($xmlRequest, "id");

	if ($id == "")
		trigger_error ("חסר קוד תמונה לביצוע הפעולה");

	$queryStr	= "select galleryImages.*, language, title 
				   from galleryImages 
				   left join galleryImages_byLang on id=galleryImageId
				   where id=$id";
	$result		= commonDoQuery ($queryStr);

	if (commonQuery_numRows($result) == 0)
		trigger_error ("תמונה בקוד זה ($id) לא קיימת במערכת. לא ניתן לבצע את העדכון");

	$langsArray = explode(",",$usedLangs);

	$xmlResponse = "";
	
	while ($row = commonQuery_fetchRow($result))
	{
		$language = $row['language'];

		$langsArray = commonArrayRemove ($langsArray, $language);	

		if ($xmlResponse == "")
		{
			$id			 = $row['id'];
			$galleryId	 = $row['galleryId'];
			$pos		 = $row['pos'];
			$sourceFile	 = commonValidXml(addslashes($row['sourceFile']));
			$title		 = commonValidXml($row['title']);
		
			$xmlResponse	= 	"<id>$id</id>"	 								. 
								"<galleryId>$galleryId</galleryId>"				.
								"<pos>$pos</pos>"								. 
								"<sourceFile>$sourceFile</sourceFile>"			.
								"<formSourceFile>$sourceFile</formSourceFile>"	.
								"<usedLangs>$usedLangs</usedLangs>";
		}

		$title		  = commonValidXml($row['title']);

		$xmlResponse .= "<title$language>$title</title$language>";
	}

	// add missing languages
	// ------------------------------------------------------------------------------------------------
	for ($i=0; $i<count($langsArray); $i++)
	{
		$language	  = $langsArray[$i];

		$xmlResponse .=	   "<title$language></title$language>";
	}

	return $xmlResponse;
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteGalleryImage																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteGalleryImage ($xmlRequest)
{
	$id 		= xmlParser_getValue ($xmlRequest, "id");
	$galleryId	= xmlParser_getValue ($xmlRequest, "galleryId");
	$filename  	= xmlParser_getValue ($xmlRequest, "filename");

	if ($id == "")
	{
		trigger_error ("חסר קוד תמונה לביצוע הפעולה");
	}
	elseif ($galleryId == "")
	{
		trigger_error ("חסר קוד גלריה לביצוע הפעולה");
	}
	elseif ($filename == "")
	{
		trigger_error ("חסר שם קובץ לביצוע הפעולה");
	}

	$queryStr = "delete from galleryImages where id = $id";
	commonDoQuery ($queryStr);

	$queryStr = "delete from galleryImages_byLang where galleryImageId = $id";
	commonDoQuery ($queryStr);
	
	$domainRow = commonGetDomainRow ();

	$connId    = commonFtpConnect	($domainRow);

	commonFtpDelete ($connId, "galleries/gallery$galleryId/images/$filename");
	commonFtpDelete ($connId, "galleries/gallery$galleryId/thumbs/$filename");

	 commonFtpDisconnect($connId);

	if (!$handle = fopen(commonGetDomainName ($domainRow)."/galleries/gallery$galleryId/buildgallery.php", "r"))
	{
		trigger_error ("בעייה בכתיבה של קובץ ה-xml");
	}
	
	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateGalleryImage																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateGalleryImage ($xmlRequest)
{
	global $usedLangs;
	global $userId;
	global $ibosHomeDir;

	$id				= xmlParser_getValue($xmlRequest, "id");
	$galleryId		= xmlParser_getValue($xmlRequest, "galleryId");
	$pos			= xmlParser_getValue($xmlRequest, "pos");
	$sourceFile 	= addslashes(xmlParser_getValue($xmlRequest, "sourceFile"));	

	// get gallery details
	$queryStr 		= "select picBgColor, picWidth, picHeight from galleries where id = $galleryId";
	$galleryResult	= commonDoQuery ($queryStr);
	$galleryRow		= commonQuery_fetchRow ($galleryResult);

	$picBgColor 	= $galleryRow['picBgColor'];

	if ($picBgColor == "") $picBgColor = "#FFFFFF";


	$fileLoaded = false;

	$picFile 		= "";
	$suffix 		= "";

	if ($sourceFile != "")
	{
		$fileLoaded = true;
		$suffix		= commonFileSuffix ($sourceFile);
	}

	if ($fileLoaded)
	{
		$imageFile	= $galleryId . "_" . $id . ".jpg";
		
		$queryStr = "update galleryImages set pos 	 	 = '$pos',
										  	  filename   = '$imageFile',
										  	  sourceFile = '$sourceFile'
					 where id=$id";
	
		commonDoQuery ($queryStr);
	} 
	else 
	{
		$queryStr = "update galleryImages set pos = '$pos' where id=$id";
	
		commonDoQuery ($queryStr);
	}

	# add languages rows for this category
	# ------------------------------------------------------------------------------------------------------
	$langsArray = explode(",",$usedLangs);

	for ($i=0; $i<count($langsArray); $i++)
	{
		$language		= $langsArray[$i];

		$title			= addslashes(xmlParser_getValue($xmlRequest, "title$language"));

		$queryStr		= "update galleryImages_byLang set title = '$title'
						   where galleryImageId = $id and galleryId = $galleryId and language = '$language'";
		commonDoQuery ($queryStr);
	}

	$filePath = "$ibosHomeDir/html/SWFUpload/files/$userId/";

	$domainRow	= commonGetDomainRow ();

	if ($fileLoaded)
	{
		$connId = commonFtpConnect ($domainRow);

		picsToolsResize("$filePath/$sourceFile", $suffix, $galleryRow['picWidth'], $galleryRow['picHeight'], 
						"$filePath/$sourceFile", $picBgColor, 95);

		// delete the file first
		$contents_on_server = ftp_nlist($connId, "galleries/gallery$galleryId/images/");
		if (in_array($imageFile, $contents_on_server))
			ftp_delete ($connId, "galleries/gallery$galleryId/images/$imageFile");
	
		$upload = ftp_put($connId, "galleries/gallery$galleryId/images/$imageFile", "$filePath/$sourceFile", FTP_BINARY); 

		// delete thumb file in order to create new one
		$contents_on_server = ftp_nlist($connId, "galleries/gallery$galleryId/thumbs/");
		if (in_array($imageFile, $contents_on_server))
			ftp_delete ($connId, "galleries/gallery$galleryId/thumbs/$imageFile");

		commonFtpDisconnect($connId);
	}

	// Build gallery
	if (!$handle = fopen(commonGetDomainName($domainRow)."/galleries/gallery$galleryId/buildgallery.php", "r"))
	{
		trigger_error ("בעייה בכתיבה של קובץ ה-xml");
	}
	
 	// delete old files
	commonDeleteOldFiles ($filePath, 7200);	// 2 hour
}

?>
