<?php

include "commonAdmin.php";
include "picsTools.php";

// default color to fill in background is white
// FFU the application can set otherwise by POST

if (!commonValidateSession())
{
	echo "<b>System Error</b> - please, call support<br/>
		  <br/><br/><font color='blue'><u>cookies</u>:</font><br/>";
	
	print_r ($_COOKIE);

	echo "<br/><br/><font color='blue'><u>get</u>:</font><br/>";
	print_r ($_GET);

	echo "<br/><br/><font color='blue'><u>post</u>:</font><br/>";
	print_r ($_POST);

	exit;
}

// action (add | update)
// id

$pageTags 		= Array("id", "layoutId", "navParentId");
$albumTags		= Array("id", "displayType", "numCols", "numRows", "withPaging", "numPages", "autoSwitch", "smallPicsSide", 
						"hSmallPicHeight", "hSmallPicWidth", "vSmallPicHeight", "vSmallPicWidth", 
						"hPicWidth", "hPicHeight", "vPicWidth", "vPicHeight", "bgColor", "quality");
$albumLangTags	= Array("txt", "shortDesc");

$loadFile		= false;

// find suffix and source file
if ($_FILES['picFile']['name'])
{
	$origName 	= $_FILES["picFile"]["name"];
	$splitName 	= split("\.",$origName);
	$suffix 	= "";
	if (count($splitName) > 0)
		$suffix	= "." . strtolower($splitName[count($splitName)-1]);

	$loadFile 	= true;
}

$sourceFile	= addslashes($sourceFile);

if ($action == "duplicateAlbum") $action = "addAlbum";

if ($action == "addAlbum")
{
	$queryStr   = "select max(id) from pages";
	$result		= commonDoQuery ($queryStr);
	$row		= commonQuery_fetchRow ($result);
	$id 		= $row[0] + 1;
}
else
{
	$id			= $albumId;
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

if ($action == "addAlbum")
{
	$queryStr = "insert into pages (" . join(",",$pageTags) . ",type) values ('" . join("','",$pageVals) . "','album')";
	commonDoQuery ($queryStr);

	$queryStr = "insert into albums (" . join(",",$albumTags) . ", albumPic, albumSourcePic) values ('" . join("','",$vals) . "'";

	if ($loadFile)
	{
		$picFile	= $id . "_size0.jpg";
		$queryStr .= ", '$picFile', '$sourceFile'";
	}
	else
	{
		$queryStr .= ", '', ''";
	}

	$queryStr	.= ")";
	commonDoQuery ($queryStr);

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
}
else
{
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

	$queryStr	= trim($queryStr, ",");

	if ($loadFile)
	{
		$picFile	= $id . "_size0.jpg";

		$queryStr .= ",	  albumPic 	 	 = '$picFile',
						  albumSourcePic = '$sourceFile' ";
	}
	else if ($sourceFile == "")
	{
		$queryStr .= ",	  albumPic     	 = '',
						  albumSourcePic = '' ";
	}

	$queryStr .= "where id = $id";
	commonDoQuery ($queryStr);

	$queryStr = "delete from pages_byLang where pageId='$id'";
	commonDoQuery ($queryStr);
	
	$queryStr = "delete from albums_byLang where albumId='$id'";
	commonDoQuery ($queryStr);
}

# add languages rows for this image
# ------------------------------------------------------------------------------------------------------
$langsArray = split(",",$usedLangs);

for ($i=0; $i<count($langsArray); $i++)
{
	$language			= $langsArray[$i];

	eval ("\$title 	     = \$title$language;");
	eval ("\$winTitle    = \$winTitle$language;");
	eval ("\$shortDesc   = \$shortDesc$language;");
	eval ("\$txt 	     = \$txt$language;");
	eval ("\$isReady     = \$isReady$language;");
	eval ("\$keywords    = \$keywords$language;");
	eval ("\$description = \$description$language;");

	$title			= commonPrepareToDB($title);
	$winTitle		= commonPrepareToDB($winTitle);
	$shortDesc		= commonPrepareToDB($shortDesc);
	$txt			= commonPrepareToDB($txt);
	$keywords		= commonPrepareToDB($keywords);
	$description	= commonPrepareToDB($description);
	$navTitle		= commonPrepareToDB($navTitle);

	$queryStr	= "insert into pages_byLang (pageId, language, title, winTitle, isReady, keywords, description, navTitle)
   				   values ('$id','$language', '$title', '$winTitle', '$isReady', '$keywords', '$description', '$navTitle')";
	commonDoQuery ($queryStr);

	$queryStr	= "insert into albums_byLang (albumId, language, txt, shortDesc) values ($id, '$language', '$txt', '$shortDesc')";
	commonDoQuery ($queryStr);
}

# upload files
# ------------------------------------------------------------------------------------------------------

if ($dimensionId == 0)
{
	$picWidth  = 0;
	$picHeight = 0;
	$bgColor   = "#FFFFFF";
}
else if ($dimensionId != "")
{
	$queryStr   = "select width, height, color, allowCrop from dimensions where id = $dimensionId";
	$result		= commonDoQuery ($queryStr);
	$row		= commonQuery_fetchRow ($result);

	$picWidth 	= $row['width'];
	$picHeight 	= $row['height'];
	$bgColor 	= $row['color'];
	$allowCrop	= $row['allowCrop'];
}
else
{
	$picWidth  = "200";
	$picHeight = "100";
	$bgColor   = "#FFFFFF";
	$allowCrop = false;
}

$connId = "";

if ($loadFile)
{
	$domainRow = commonGetDomainRow ();
	$connId	   = commonFtpConnect($domainRow);

	$tmpName = $_FILES["picFile"]["tmp_name"];

	if ($picHeight != 0 || $picWidth != 0)
	{
		@ftp_delete($connId, "albumsFiles/$picFile");
		picsToolsResize($tmpName, $suffix, $picWidth, $picHeight, "/../../tmp/$picFile", $bgColor, 80, $allowCrop);
		$upload = ftp_put($connId, "albumsFiles/$picFile", "/../../tmp/$picFile", FTP_BINARY);
		if (!$upload) echo "FTP upload has failed!";
	}
	else
	{
		$upload = ftp_put($connId, "albumsFiles/$picFile", $tmpName, FTP_BINARY);
		if (!$upload) echo "FTP upload has failed!";
	}
} 
else if ($action == "updateAlbum")
{
	if ($sourceFile == "")
	{
		if ($connId == "")
		{
			// first connect
			$domainRow = commonGetDomainRow ();

			$connId	   = commonFtpConnect($domainRow);
		}

		$fileName = split("/", $fullFileName);
		$fileName = $fileName[count($fileName)-1];

		// delete file
		@ftp_delete($connId, "albumsFiles/$fileName");
	}
}

if ($connId != "")
	commonFtpDisconnect ($connId);

header ("Location: ../html/content_extand/handleAlbums.html");
exit;

?>
