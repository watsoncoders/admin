<?php

/* Handle adding images to gallery (multi upload) */

include "commonAdmin.php";
include "picsTools.php";

$filePath = "$ibosHomeDir/html/SWFUpload/files/";

move_uploaded_file($_FILES['Filedata']['tmp_name'], $filePath . $_FILES['Filedata']['name']);

chmod ($filePath . $_FILES['Filedata']['name'], 0755);

$galleryId = $_GET['galleryId'];

commonValidateSession();

$origName 	= $_FILES['Filedata']['name'];
$suffix		= commonFileSuffix($origName);

// get gallery details
$queryStr 		= "select picBgColor, picWidth, picHeight from galleries where id = $galleryId";
$galleryResult	= commonDoQuery ($queryStr);
$galleryRow		= commonQuery_fetchRow ($galleryResult);

$picBgColor = $galleryRow['picBgColor'];
if ($picBgColor == "") $picBgColor = "#FFFFFF";

/*$queryStr   = "select max(id) from galleryImages";
$result		= commonDoQuery ($queryStr);
$row		= commonQuery_fetchRow ($result);
$id			= $row[0] + 1;
 */

$sourceFile	= addslashes($origName);

// get max pos image in this gallery
// ----------------------------------------------------------------------------------------------------------------------------
$queryStr   = "select max(pos) from galleryImages where galleryId = $galleryId";
$result		= commonDoQuery ($queryStr);
$row		= commonQuery_fetchRow ($result);
$pos	 	= $row[0] + 1;

// add new gallery image
// ----------------------------------------------------------------------------------------------------------------------------
$queryStr   = "insert into galleryImages (id, galleryId, pos) values (null, '$galleryId', '$pos')";
commonDoQuery ($queryStr);

$id	= commonQuery_insertId();

$imageFile	= $galleryId . "_" . $id . ".jpg";

// update gallery image
// ----------------------------------------------------------------------------------------------------------------------------
$queryStr   = "update galleryImages set filename = '$imageFile', sourceFile = '$sourceFile' where id = $id";
commonDoQuery ($queryStr);


# add languages rows for this image
# ------------------------------------------------------------------------------------------------------
$queryStr	= "select langs from globalParms";
$result		= commonDoQuery($queryStr);
$row		= commonQuery_fetchRow($result);
$usedLangs	= $row['langs'];

$langsArray = explode(",",$usedLangs);

for ($i=0; $i<count($langsArray); $i++)
{
	$language			= $langsArray[$i];

	$queryStr		= "insert into galleryImages_byLang (galleryImageId, galleryId, language, title)
					   values ('$id','$galleryId','$language','')";
	
	commonDoQuery ($queryStr);
}

// copy file to site domain
// ----------------------------------------------------------------------------------------------------------------------------
$domainRow = commonGetDomainRow ();
$connId	   = commonFtpConnect($domainRow);

$file = "$filePath/$origName";

picsToolsResize($file, $suffix, $galleryRow['picWidth'], $galleryRow['picHeight'], "/../../tmp/$imageFile", $picBgColor);

$upload = ftp_put($connId, "galleries/gallery$galleryId/images/$imageFile", "/../../tmp/$imageFile", FTP_BINARY);

// check upload status
if (!$upload) 
{ 
	debugLog ("There was a problem when uploding the new file " . $filePath . $_FILES['Filedata']['name'] . 
			  " to $picFile");
}

// Call buildgallery.php on client server to build XML file
// ------------------------------------------------------------------------------------------------------

// Build gallery
if (!$handle = fopen(commonGetDomainName($domainRow)."/galleries/gallery$galleryId/buildgallery.php", "r"))
{
	trigger_error ("בעייה בכתיבה של קובץ ה-xml");
}

commonFtpDisconnect($connId);

unlink ($filePath . $_FILES['Filedata']['name']);

/* ----------------------------------------------------------------------------------------------------------------------------	*/
/* debugLog																														*/
/* ----------------------------------------------------------------------------------------------------------------------------	*/
function debugLog ($msg)
{
	$debug = fopen("/tmp/galleryUploadImage.log", "a");
	fwrite ($debug, "$msg\n");
	fclose ($debug);
}

echo "1";
?>

