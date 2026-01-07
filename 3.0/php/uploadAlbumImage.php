<?php

include "commonAdmin.php";
include "picsTools.php";

set_time_limit(0);

commonValidateSession();

// action 	(add | update)
// albumId
// fileType (pic | video | embed)
// pos ?
// imageId	(for update)
// title 	(by langs)

if ($fileType == "") $fileType = "pic";

// get album details
$queryStr 		= "select * from albums where id = $albumId";
$albumResult	= commonDoQuery ($queryStr);
$albumRow		= commonQuery_fetchRow ($albumResult);

$picBgColor		= $albumRow['bgColor'];
if ($picBgColor == "")
	$picBgColor = "#FFFFFF";

$quality = 80;
if ($albumRow['quality'] && is_numeric($albumRow['quality']) && $albumRow['quality'] > 0 && $albumRow['quality'] <= 100)
		$quality = $albumRow['quality'];

$loadFile = false;

if ($_FILES['filename']['name'])
{
	$origName 	= $_FILES['filename']['name'];
	$splitName 	= split("\.",$origName);
	$suffix 	= "";
	if (count($splitName) > 0)
		$suffix	= "." . $splitName[count($splitName)-1];

	$suffix   = strtolower($suffix);

	$loadFile = true;

	$sourceFile	= addslashes($sourceFile);
}

$loadFile2 = false;

if ($_FILES['filename2']['name'])
{
	$origName 	= $_FILES['filename2']['name'];
	$splitName 	= split("\.",$origName);
	$suffix2 	= "";
	if (count($splitName) > 0)
		$suffix2	= "." . $splitName[count($splitName)-1];

	$suffix2  = strtolower($suffix2);

	$loadFile2 = true;

	$sourceFile2	= addslashes($sourceFile2);
}

if ($action == "add")
{
	$queryStr   = "select max(id) from albumImages";
	$result		= commonDoQuery ($queryStr);
	$row		= commonQuery_fetchRow ($result);
	$imageId	= $row[0] + 1;

	$file1	= $albumId . "_" . $imageId . "_size0$suffix";

	if ($fileType == "video")
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

	if ($loadFile)
	{
		$file1	= $albumId . "_" . $imageId . "_size0$suffix";
		
		$queryStr .= ",	filename    = '$file1',
						sourceFile  = '$sourceFile' ";
	} 

	if ($loadFile2)
	{
		if ($fileType == "video")
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
$langsArray = split(",",$usedLangs);

for ($i=0; $i<count($langsArray); $i++)
{
	$language			= $langsArray[$i];

	eval ("\$title = \$title$language;");

	$title			= commonPrepareToDB($title);

	$queryStr		= "insert into albumImages_byLang (imageId, albumId, language, title)
					   values ('$imageId','$albumId','$language','$title')";
	
	commonDoQuery ($queryStr);
}

# load / reload file
# ------------------------------------------------------------------------------------------------------

if ($loadFile || $loadFile2)
{
	$domainRow = commonGetDomainRow ();

	$connId    = commonFtpConnect    ($domainRow);
}

if ($loadFile)
{
	$tmpName = $_FILES['filename']['tmp_name'];

	// upload orig file (size0)
	$upload = ftp_put($connId, "albumsFiles/$file1", $tmpName, FTP_BINARY); 

	// check upload status
	if (!$upload) 
	{ 
	   echo "FTP upload has failed!";
	}

	list($widthOrig, $heightOrig) = getimagesize($tmpName);

	// Destination must be jpg
	$destParts = split("\.",$file1);
	$destParts[count($destParts)-1] = "jpg";
	$file1 = join(".", $destParts);

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
		$resizedFileName = str_replace("size0","small",$file1);
		picsToolsResize($tmpName, $suffix, $smallWidth, $smallHeight, "/../../tmp/$resizedFileName", $picBgColor, $quality);
		$upload = ftp_put($connId, "albumsFiles/$resizedFileName", "/../../tmp/$resizedFileName", FTP_BINARY);
		if (!$upload) 
			echo "FTP upload has failed!";
	}

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
		$resizedFileName = str_replace("size0","big", $file1);
		picsToolsResize($tmpName, $suffix, $bigWidth, $bigHeight, "/../../tmp/$resizedFileName", $picBgColor, $quality);
		$upload = ftp_put($connId, "albumsFiles/$resizedFileName", "/../../tmp/$resizedFileName", FTP_BINARY);
		if (!$upload) 
			echo "FTP upload has failed!";
	}
}

if ($loadFile2 && $fileType == "video")
{
		$tmpName = $_FILES['filename2']['tmp_name'];

		// upload orig file (size0)
		$upload = ftp_put($connId, "albumsFiles/$file2", $tmpName, FTP_BINARY); 

		// check upload status
		if (!$upload) 
	   	echo "FTP upload has failed!";

		list($widthOrig, $heightOrig) = getimagesize($tmpName);

		// Destination must be jpg
		$destParts = split("\.",$file2);
		$destParts[count($destParts)-1] = "jpg";
		$file2 = join(".", $destParts);

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
			$resizedFileName = str_replace("size0","small",$file2);
			picsToolsResize($tmpName, $suffix2, $smallWidth, $smallHeight, "/../../tmp/$resizedFileName", $picBgColor, $quality);
			$upload = ftp_put($connId, "albumsFiles/$resizedFileName", "/../../tmp/$resizedFileName", FTP_BINARY);
			if (!$upload) 
				echo "FTP upload has failed!";
		}

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
			$resizedFileName = str_replace("size0","big", $file2);
			picsToolsResize($tmpName, $suffix2, $bigWidth, $bigHeight, "/../../tmp/$resizedFileName", $picBgColor, $quality);
			$upload = ftp_put($connId, "albumsFiles/$resizedFileName", "/../../tmp/$resizedFileName", FTP_BINARY);
			if (!$upload) 
				echo "FTP upload has failed!";
		}
}

header ("Location: ../html/content_extand/handleAlbums.html");
exit;

?>
