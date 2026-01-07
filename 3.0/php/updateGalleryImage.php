<?php

include "commonAdmin.php";
include "picsTools.php";

set_time_limit(0);

commonValidateSession();

// ### ONLY UPDATE !!! (add is done by the swf component)

// action (add | update)
// galleryId
// id (for update)
// title (by langs)
// pos ?
// picBgColor

// get gallery details
$queryStr 		= "select picBgColor, picWidth, picHeight from galleries where id = $galleryId";
$galleryResult	= commonDoQuery ($queryStr);
$galleryRow		= commonQuery_fetchRow ($galleryResult);

//echo ">>> $galleryId<br/>$galleryRow[picWidth]X$galleryRow[picHeight]<br/>";

$picBgColor = $galleryRow['picBgColor'];

if ($picBgColor == "") $picBgColor = "#FFFFFF";

$loadFile = false;

if ($_FILES['filename']['name'])
{
	$origName 	= $_FILES['filename']['name'];
	$splitName 	= split("\.",$origName);
	$suffix 	= "";
	if (count($splitName) > 0)
		$suffix	= "." . $splitName[count($splitName)-1];

	$loadFile = true;

	$sourceFile	= addslashes($origName);
}

if ($action == "add")
{
	$queryStr   = "select max(id) from galleryImages";
	$result		= commonDoQuery ($queryStr);
	$row		= commonQuery_fetchRow ($result);
	$id			= $row[0] + 1;

	$imageFile	= $galleryId . "_" . $id . ".jpg";
	
	$queryStr   = "insert into galleryImages (id, galleryId, pos, filename, sourceFile)
				   values ('$id', '$galleryId', '$pos', '$imageFile', '$sourceFile')";
	commonDoQuery ($queryStr);
}
else
{
	$queryStr = "delete from galleryImages_byLang where galleryImageId='$id'";
	commonDoQuery ($queryStr);

	if ($loadFile)
	{
		$imageFile	= $galleryId . "_" . $id . ".jpg";
		
		$queryStr = "update galleryImages set pos 	 	 = '$pos',
										  	  filename   = '$imageFile',
										  	  sourceFile = '$sourceFile'
					 where id=$id";
	
		commonDoQuery ($queryStr);
	} else {
		$queryStr = "update galleryImages set pos = '$pos'
					 where id=$id";
	
		commonDoQuery ($queryStr);
	}
}

# add languages rows for this image
# ------------------------------------------------------------------------------------------------------
$langsArray = split(",",$usedLangs);

for ($i=0; $i<count($langsArray); $i++)
{
	$language			= $langsArray[$i];

	eval ("\$title = \$title$language;");

	$title			= commonPrepareToDB($title);

	$queryStr		= "insert into galleryImages_byLang (galleryImageId, galleryId, language, title)
					   values ('$id','$galleryId','$language','$title')";
	
	commonDoQuery ($queryStr);
}

$domainRow	= commonGetDomainRow ();

# load / reload file
# ------------------------------------------------------------------------------------------------------

if ($loadFile)
{
	/*list($imgWidth, $imgHeight) = getimagesize($_FILES['filename']['tmp_name']); */
	picsToolsResize($_FILES['filename']['tmp_name'], $suffix, $galleryRow['picWidth'], $galleryRow['picHeight'], 
					$_FILES['filename']['tmp_name'], $picBgColor, 95);

	$connId = commonFtpConnect ($domainRow);

	// delete the file first
	@ftp_delete ($connId, "galleries/gallery$galleryId/images/$imageFile");

	$upload = ftp_put($connId, "galleries/gallery$galleryId/images/$imageFile",
					  $_FILES['filename']['tmp_name'], FTP_BINARY); 

	// check upload status
	if (!$upload) 
	{ 
	   echo "FTP upload has failed!";
	}

	// delete thumb file in order to create new one
	@ftp_delete ($connId, "galleries/gallery$galleryId/thumbs/$imageFile");

	commonFtpDisconnect($connId);

}


// Build gallery
if (!$handle = fopen(commonGetDomainName($domainRow)."/galleries/gallery$galleryId/buildgallery.php", "r"))
{
	trigger_error ("בעייה בכתיבה של קובץ ה-xml");
}

	# ------------------------------------------------------------------------------------------------------

header ("Location: ../html/content/handleGalleryImages.html");
exit;

?>
