<?php

include "$ibosHomeDir/php/picsTools.php";

/* ----------------------------------------------------------------------------------------------------	*/
/* getGalleries																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function getGalleries ($xmlRequest)
{
	global $usedLangs;
	$langsArray = explode(",",$usedLangs);

	$domainRow   = commonGetDomainRow ();

	commonValidateSession ();
	
	$condition  = commonAddIbosUserCondition();

	// get total
	$queryStr	 = "select count(*) from galleries, pages where pages.type = 'gallery' $condition";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$total	     = $row[0];

	// get details
	$queryStr    = "select galleries.*,
						   pages.staticname as staticname, pages.layoutId,
						   pages_byLang.title as title, 
						   count(galleryImages.galleryId) as countImages
						   from galleries
					left join pages 	    on galleries.id = pages.id and pages.type = 'gallery'
					left join pages_byLang  on galleries.id = pages_byLang.pageId and pages_byLang.language = '$langsArray[0]'
					left join galleryImages on galleries.id = galleryImages.galleryId
					where pages.type = 'gallery' $condition
					group by galleries.id
					order by galleries.id " . commonGetLimit ($xmlRequest);
	$result	     = commonDoQuery ($queryStr);

	$numRows    = commonQuery_numRows($result);

	$xmlResponse = "<items>";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		$galleryId   = $row['id'];
		$staticname  = commonValidXml ($row['staticname'],true);
		$title  	 = commonValidXml ($row['title'],true);
		$showThumbs  = $row['showThumbs'];
		$thumNumRows = $row['thumNumRows'];
		$thumNumCols = $row['thumNumCols'];
		$countImages = $row['countImages'];
		$AutoStart	 = $row['AutoStart'];
		$picWidth	 = $row['picWidth'];
		$picHeight 	 = $row['picHeight'];
		$navColor 	 = $row['navColor'];
		$picBgColor	 = $row['picBgColor'];
		$navPos		 = $row['navPos'];

		$layoutName = commonGetLayoutName ($row['layoutId']);
		
		$galleryPrefix  = commonGetDomainName($domainRow) . "/";
		$fullGalleryPath = $galleryPrefix . "galleryPreivew.php?gid=$galleryId";
		$fullGalleryPath = commonValidXml($fullGalleryPath);
		
		$xmlResponse .=	"<item>"											.
							"<galleryId>$galleryId</galleryId>"	 			. 
							"<staticname>$staticname</staticname>"			. 
							"<layoutName>$layoutName</layoutName>"			.
							"<title>$title</title>"							. 
							"<showThumbs>$showThumbs</showThumbs>"			.
							"<thumNumRows>$thumNumRows</thumNumRows>"		. 
							"<thumNumCols>$thumNumCols</thumNumCols>"		. 
							"<countImages>$countImages</countImages>"		.
							"<AutoStart>$AutoStart</AutoStart>"				.
							"<fullGalleryPath>$fullGalleryPath</fullGalleryPath>"	.
							"<picWidth>$picWidth</picWidth>"		. 
							"<picHeight>$picHeight</picHeight>"		. 
							"<navColor>$navColor</navColor>"		. 
							"<picBgColor>$picBgColor</picBgColor>"		. 
							"<navPos>$navPos</navPos>" .
						"</item>";
	}

	$xmlResponse .=	"</items>"												.
					commonGetTotalXml($xmlRequest,$numRows,$total);
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* addGallery																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function addGallery ($xmlRequest)
{
	return (editGallery ($xmlRequest, "add"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* doesGalleryExist																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function doesGalleryExist ($galleryId)
{
	$queryStr		= "select count(*) from galleries where id=$galleryId";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$count	     = $row[0];

	return ($count > 0);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getGalleryNextId																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getGalleryNextId ($xmlRequest)
{
	$queryStr	= "select max(id) from pages";
	$result		= commonDoQuery ($queryStr);
	$row		= commonQuery_fetchRow ($result);
	$id 		= $row[0] + 1;
	
	return "<galleryId>$id</galleryId>";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getGalleryDetails																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function getGalleryDetails ($xmlRequest)
{
	global $usedLangs;

	$id		= xmlParser_getValue($xmlRequest, "galleryId");
	$action	= xmlParser_getValue($xmlRequest, "action");

	if ($id == "")
		trigger_error ("חסר קוד גלריה לביצוע הפעולה");

	$queryStr	= "select galleries.*, galleries_byLang.txt, galleries_byLang.language,  pages.navParentId, navTitle,
						  pages.layoutId, pages.membersOnly, pages.staticname, pages.showOnSitemap, pages_byLang.title, pages_byLang.winTitle, isReady,
		   				  pages_byLang.keywords, pages_byLang.description, pages_byLang.rewriteName
				   from galleries, galleries_byLang, pages, pages_byLang
				   where galleries.id = galleries_byLang.galleryId and galleries_byLang.language = pages_byLang.language
				   and   galleries_byLang.galleryId = pages.id and pages.type = 'gallery'
				   and   pages.id = pages_byLang.pageId
				   and   galleries.id=$id";
	$result		= commonDoQuery ($queryStr);

	if (commonQuery_numRows($result) == 0)
		trigger_error ("גלריה קוד זה ($id) לא קיימת במערכת. לא ניתן לבצע את העדכון");

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
			if ($action == "duplicate")
			{
				$queryStr	= "select max(id) from pages";
				$result2	= commonDoQuery ($queryStr);
				$row2		= commonQuery_fetchRow ($result2);
				$id 		= $row2[0] + 1;
			}
			else
			{
				$id				= $row['id'];
			}

			$layoutId		= $row['layoutId'];
			$navParentId    = $row['navParentId'];
			$membersOnly	= $row['membersOnly'];
			$showThumbs		= $row['showThumbs'];
			$thumNumRows	= $row['thumNumRows'];
			$thumNumCols	= $row['thumNumCols'];
			$staticname		= commonValidXml($row['staticname']);
			$AutoStart		= $row['AutoStart'];
			$picWidth		= $row['picWidth'];
			$picHeight		= $row['picHeight'];
			$navColor		= $row['navColor'];
			$picBgColor		= $row['picBgColor'];
			$navPos			= $row['navPos'];
			$showOnSitemap  = $row['showOnSitemap'];
	
			$picFile   	   = commonValidXml($row['picFile']);
			$picSource	   = commonValidXml($row['picSource']);
	
			$linkToFile	    = "$siteUrl/galleriesFiles/$row[picFile]";

			$pressText     = commonPhpEncode("לחץ כאן");

			$show	 = "";
			$delete  = "";

			if ($row['picFile']  != "") 
			{
				$show   = $pressText;
				$delete = $pressText;
			}

			$xmlResponse = 	"<galleryId>$id</galleryId>"				. 
							"<layoutId>$layoutId</layoutId>"			. 
							"<navParentId>$navParentId</navParentId>"	.
							"<membersOnly>$membersOnly</membersOnly>"	. 
							"<showThumbs>$showThumbs</showThumbs>"		.
							"<thumNumRows>$thumNumRows</thumNumRows>"	. 
							"<thumNumCols>$thumNumCols</thumNumCols>"	.
							"<AutoStart>$AutoStart</AutoStart>"			.
							"<staticname>$staticname</staticname>"		.
							"<picWidth>$picWidth</picWidth>"	. 
							"<picHeight>$picHeight</picHeight>"	.
							"<navColor>$navColor</navColor>" .
							"<picBgColor>$picBgColor</picBgColor>" .
							"<showOnSitemap>$showOnSitemap</showOnSitemap>" .
							"<navPos>$navPos</navPos>" .
							"<siteUrl>$siteUrl/index2.php</siteUrl>
							 <dimensionId></dimensionId>
							 <picFile>$picFile</picFile>
							 <formSourceFile>$picSource</formSourceFile>
							 <show>$show</show>
							 <delete>$delete</delete>
							 <linkToFile>$linkToFile</linkToFile>";
		}

		$title			= commonValidXml($row['title']);
		$navTitle		= commonValidXml($row['navTitle']);
		$winTitle		= commonValidXml($row['winTitle']);
		$txt			= commonValidXml($row['txt']);
		$keywords		= commonValidXml($row['keywords']);
		$description	= commonValidXml($row['description']);
		$isReady		= $row['isReady'];
		$rewriteName   = commonValidXml($row['rewriteName']);

		$xmlResponse   .= "<title$language>$title</title$language>
						   <winTitle$language>$winTitle</winTitle$language>
						   <navTitle$language>$navTitle</navTitle$language>
						   <keywords$language>$keywords</keywords$language>
						   <description$language>$description</description$language>
						   <txt$language>$txt</txt$language>
						   <isReady$language>$isReady</isReady$language>
						   <rewriteName$language>$rewriteName</rewriteName$language>";
	}

	// add missing languages
	// ------------------------------------------------------------------------------------------------
	for ($i=0; $i<count($langsArray); $i++)
	{
		$language	  = $langsArray[$i];

		$xmlResponse .=	   "<title$language><![CDATA[]]></title$language>
						    <winTitle$language><![CDATA[]]></winTitle$language>
							<navTitle$language><![CDATA[]]></navTitle$language>
							<keywords$language><![CDATA[]]></keywords$language>
							<description$language><![CDATA[]]></description$language>
						    <txt$language><![CDATA[]]></txt$language>
						    <isReady$language><![CDATA[]]></isReady$language>
						    <rewriteName$language><![CDATA[]]></rewriteName$language>";
	}

	return $xmlResponse;
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateGallery																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateGallery ($xmlRequest)
{
	editGallery ($xmlRequest, "update");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* editGallery																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function editGallery ($xmlRequest, $editType)
{
	global $usedLangs;
	global $userId;
	global $ibosHomeDir;

	$id		= xmlParser_getValue($xmlRequest, "galleryId");

	if ($id == "")
		trigger_error ("חסר קוד גלריה לביצוע הפעולה");

	if ($editType == "add")
	{
		if (doesGalleryExist($id))
		{
			trigger_error ("גלריה עם קוד זהה ($id) כבר קיים במערכת");
		}
	}
	else	// update gallery
	{
		if (!doesGalleryExist($id))
		{
			trigger_error ("גלריה עם קוד זה ($id) לא קיימת במערכת. לא ניתן לבצע את העדכון");
		}
	}

	// handle picture 
	$picSource		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "picSource")));	
	$fileDeleted	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "fileDeleted")));	
	$dimensionId	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "dimensionId")));	

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

	if ($suffix == "." . $picSource)	// wrong file name - don't load it
	{
		$fileLoaded = false;
		$picFile    = "";
	}

	list($galleryPicWidth, $galleryPicHeight, $galleryBgColor) = commonGetDimensionDetails ($dimensionId);
	
//	trigger_error (xmlParser_getValue($xmlRequest, "isReadyENG"));

	# delete all languages rows
	# ------------------------------------------------------------------------------------------------------
	$queryStr = "delete from pages_byLang where pageId='$id'";
	commonDoQuery ($queryStr);
	
	$queryStr = "delete from galleries_byLang where galleryId='$id'";
	commonDoQuery ($queryStr);

	# add languages rows for this gallery
	# ------------------------------------------------------------------------------------------------------
	$langsArray = explode(",",$usedLangs);

	for ($i=0; $i<count($langsArray); $i++)
	{
		$language		= $langsArray[$i];

		$isReady		= xmlParser_getValue($xmlRequest, "isReady$language");
		$title			= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "title$language")));
		$winTitle		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "winTitle$language")));
		$navTitle		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "navTitle$language")));
		$txt			= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "txt$language")));
		$keywords		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "keywords$language")));
		$description	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "description$language")));
		$rewriteName	= str_replace(" ", "-", addslashes(commonDecode(xmlParser_getValue($xmlRequest, "rewriteName$language"))));

		$queryStr		= "insert into pages_byLang (pageId, language, title, winTitle, navTitle, isReady, keywords, description, rewriteName)
						   values ('$id','$language','$title', '$winTitle', '$navTitle', '$isReady', '$keywords', '$description', '$rewriteName')";
	
		commonDoQuery ($queryStr);

		$queryStr = "insert into galleries_byLang (galleryId, language, title, txt) values 
						($id, '$language', '$title', '$txt')";

		commonDoQuery ($queryStr);
	}

	$staticname		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "staticname")));
	$layoutId		= xmlParser_getValue($xmlRequest, "layoutId");
	$navParentId	= xmlParser_getValue($xmlRequest, "navParentId");
	$membersOnly	= xmlParser_getValue($xmlRequest, "membersOnly");
	$showOnSitemap	= xmlParser_getValue($xmlRequest, "showOnSitemap");
	$showThumbs		= xmlParser_getValue($xmlRequest, "showThumbs");
	$thumNumRows	= xmlParser_getValue($xmlRequest, "thumNumRows");
	$thumNumCols	= xmlParser_getValue($xmlRequest, "thumNumCols");
	$AutoStart		= xmlParser_getValue($xmlRequest, "AutoStart");
	$picWidth		= xmlParser_getValue($xmlRequest, "picWidth");
	$picHeight		= xmlParser_getValue($xmlRequest, "picHeight");
	$navColor		= xmlParser_getValue($xmlRequest, "navColor");
	$picBgColor		= xmlParser_getValue($xmlRequest, "picBgColor");
	$navPos			= xmlParser_getValue($xmlRequest, "navPos");

	if ($editType == "add")
	{
		$ibosUserId = commonGetIbosUserId ();

		$queryStr = "insert into pages (id,  ibosUserId, type, layoutId, navParentId, staticname, membersOnly, showOnSitemap) values
									   ($id, '$ibosUserId', 'gallery', '$layoutId', '$navParentId', '$staticname', '$membersOnly', '$showOnSitemap')";

		commonDoQuery ($queryStr);

		$queryStr = "insert into galleries (id, showThumbs, thumNumRows, thumNumCols, AutoStart, picWidth, picHeight, navColor, picBgColor, navPos,
											picFile, picSource) 
					 values ($id, '$showThumbs', '$thumNumRows', '$thumNumCols', '$AutoStart', '$picWidth', '$picHeight', 
							 '$navColor', '$picBgColor', '$navPos', '$picFile', '$picSource')";

		commonDoQuery ($queryStr);

	}
	else // update
	{
		$queryStr = "update pages set staticname	= '$staticname',
									  layoutId		= '$layoutId',
									  navParentId 	= '$navParentId',
									  membersOnly	= '$membersOnly',
									  showOnSitemap	= '$showOnSitemap'
					 where id=$id";

		commonDoQuery ($queryStr);

		$queryStr = "update galleries set showThumbs	= '$showThumbs',
										  thumNumRows	= '$thumNumRows',		
										  thumNumCols	= '$thumNumCols',
										  AutoStart		= '$AutoStart',
										  picWidth		= '$picWidth',		
										  picHeight		= '$picHeight',
										  navColor		= '$navColor',
										  picBgColor	= '$picBgColor',
										  navPos		= '$navPos'";

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

		$queryStr .= " where id=$id";

		commonDoQuery ($queryStr);
	}

	$domainRow	= commonGetDomainRow();

	// handle file
	$filePath = "$ibosHomeDir/html/SWFUpload/files/$userId/";

	if ($fileLoaded)
	{

		$connId = commonFtpConnect($domainRow); 

		ftp_chdir($connId, "galleriesFiles/");

		$upload = ftp_put($connId, $picFile, "$filePath/$picSource", FTP_BINARY); 

		if (!$upload) 
		   	echo "FTP upload has failed!";

		$fileName = "${id}_size0.jpg";

		picsToolsResize("$filePath/$picSource", $suffix, $galleryPicWidth, $galleryPicHeight, "/../../tmp/$fileName", $galleryBgColor);
		
		$upload = ftp_put($connId, "$fileName", "/../../tmp/$fileName", FTP_BINARY);

		unlink("$filePath/$picSource");

		commonFtpDisconnect ($connId);
	}
	else if ($fileDeleted)
	{
		$domainRow	= commonGetDomainRow();

		$connId = commonFtpConnect($domainRow); 

		ftp_chdir($connId, "galleriesFiles/");

		$fileName = "${id}_size0.jpg";

		@ftp_delete($connId, "$fileName");
	}

 	// delete old files
	commonDeleteOldFiles ($filePath, 3600);	// 1 hour

	# ------------------------------------------------------------------------------------------------------
	buildGalleryDirectory ($id);

# Call buildgallery.php on client server to build XML file
# ------------------------------------------------------------------------------------------------------

	// Build gallery
	if (!$handle = fopen(commonGetDomainName ($domainRow)."/galleries/gallery$id/buildgallery.php", "r"))
	{
		trigger_error ("בעייה בכתיבה של קובץ ה-xml");
	}
# ------------------------------------------------------------------------------------------------------

	fopen(commonGetDomainName($domainRow) . "/updateModRewrite.php","r");

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteGallery																						*/
/* ----------------------------------------------------------------------------------------------------	*/
// Function to delete a non-empty directory
function ftp_rmAll ($conn_id,$dst_dir)
{
	$ar_files = ftp_nlist($conn_id, $dst_dir);

	if (is_array($ar_files))
	{ 
		// makes sure there are files
		for ($i=0;$i<sizeof($ar_files);$i++)
		{ 
			$st_file = $ar_files[$i];
           	if ($st_file == "$dst_dir/." || $st_file == "$dst_dir/..") continue;
			
			// check if it is a directory
			if (ftp_size($conn_id, $st_file) == -1)
			{ 
               	ftp_rmAll ($conn_id,  $st_file); // if so, use recursion
           	} 
			else 
			{
            	ftp_delete($conn_id,  $st_file); // if not, delete the file
           	}
       	}
   	}

   	$flag = ftp_rmdir($conn_id, $dst_dir); // delete empty directories
   	//return $flag;
} 

function deleteGallery ($xmlRequest)
{
	$id = xmlParser_getValue ($xmlRequest, "galleryId");

	if ($id == "")
		trigger_error ("חסר קוד גלריה לביצוע הפעולה");
	
	
	return (doDeleteGallery ($id));
}

function doDeleteGallery ($id)
{
	$queryStr = "delete from galleryImages where galleryId = $id";
	commonDoQuery ($queryStr);
			
	$queryStr = "delete from galleryImages_byLang where galleryId = $id";
	commonDoQuery ($queryStr);
	
	$queryStr = "delete from galleries where id = $id";
	commonDoQuery ($queryStr);

	$queryStr = "delete from galleries_byLang where galleryId = $id";
	commonDoQuery ($queryStr);

	$queryStr = "delete from pages where id = $id";
	commonDoQuery ($queryStr);

	$queryStr = "delete from pages_byLang where pageId = $id";
	commonDoQuery ($queryStr);

	$queryStr = "delete from categoriesItems where itemId = $id and type = 'gallery'";
	commonDoQuery ($queryStr);

	$domainRow = commonGetDomainRow ();

	$connId    = commonFtpConnect	($domainRow);
	
	ftp_chdir($connId, "galleries");
	ftp_rmAll($connId, "gallery$id");

   	ftp_close  ($connId);
	
	return "";
}

//php4 equivalent of the php5 function
/* --------------------------------------------- */
function php4_ftp_chmod($ftpstream,$chmod,$file)
{
	$old=error_reporting();//save old
	error_reporting(0);//set to none
	$result=ftp_site($ftpstream, "CHMOD ".$chmod." ".$file);
	error_reporting($old);//reset to old
	return $result;//will result TRUE or FALSE
}


/* ----------------------------------------------------------------------------------------------------	*/
/* buildGalleryDirectory																				*/
/* ----------------------------------------------------------------------------------------------------	*/
function buildGalleryDirectory ($gid, $backDir = ".")
{
	$domainRow = commonGetDomainRow ();

	$conn_id   = commonFtpConnect    ($domainRow);
	
	if (! in_array("galleries", ftp_nlist($conn_id, "."))) { // Creating galleries directory
		ftp_mkdir($conn_id, "galleries");
		ftp_chmod($conn_id, 0777, "galleries");
		ftp_chdir($conn_id, "galleries");
		ftp_mkdir($conn_id, "!NULL!");
	} else {
		ftp_chdir($conn_id, "galleries");
	}

	// check if updating or craeting a gallery
	if (! in_array("gallery".$gid, ftp_nlist($conn_id, "."))) { // Creating gallery
		ftp_mkdir($conn_id, "gallery".$gid);
		ftp_chmod($conn_id, 0777, "gallery".$gid);
		ftp_chdir($conn_id, "gallery".$gid);
		ftp_mkdir($conn_id, "images");
		ftp_chmod($conn_id, 0777, "images");
		ftp_mkdir($conn_id, "thumbs");
		ftp_chmod($conn_id, 0777, "thumbs");
		//ftp_mkdir($conn_id, "ConvertTables");		
		//ftp_chmod($conn_id, 0777, "ConvertTables");

		// send gallery files
		chdir ("$backDir/../simpleviewer/");
		ftp_put($conn_id, "buildgallery.php", "buildgallery.php", FTP_BINARY);
		//ftp_put($conn_id, "../../swfobject.js", "swfobject.js", FTP_BINARY);
		//ftp_put($conn_id, "gallery.xml", "gallery.xml", FTP_BINARY);
		ftp_put($conn_id, "viewer.swf", "viewer.swf", FTP_BINARY);
		//ftp_put($conn_id, "ConvertCharset.class.php", "ConvertCharset.class.php", FTP_BINARY);
		//ftp_put($conn_id, "ConvertTables/windows-1255", "ConvertTables/windows-1255", FTP_BINARY);
		
		//php4 equivalent of the php5 function
		if (!function_exists('ftp_chmod')) {
		   function ftp_chmod($ftpstream,$chmod,$file)
		   {
		       $old=error_reporting();//save old
		       error_reporting(0);//set to none
		       $result=ftp_site($ftpstream, "CHMOD ".$chmod." ".$file);
		       error_reporting($old);//reset to old
		       return $result;//will result TRUE or FALSE
		   }
		}
		
		if (ftp_chmod($conn_id,0777,"thumbs") == FALSE)
		{
			print ("ERROR FTP_CHMOD: can't change chmod of thumbs");
		}
		
		if (ftp_chmod($conn_id,0777,"images") == FALSE)
		{
			print ("ERROR FTP_CHMOD: can't change chmod of images");
		}
/*		if (ftp_chmod($conn_id,"0666","gallery.xml") == FALSE)
		{
			print ("ERROR FTP_CHMOD: can't change chmod of gallery.xml");
		}*/
	}

	// close the FTP stream 
	ftp_close($conn_id); 
}


?>
