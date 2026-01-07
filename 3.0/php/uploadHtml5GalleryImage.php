<?php

/* Handle adding images to gallery (multi upload) */
set_time_limit(0);

$filePath = "/home/iboscoil/public_html/3.0/html/SWFUpload/files/" . $_GET['userId'] . "/";
if (!file_exists($filePath))
		mkdir ("$filePath", 0777);

parse_str(file_get_contents("php://input"), $postdata);

file_put_contents($filePath.$postdata['name'], base64_decode(str_replace(' ', '+', substr($postdata['data'], strrpos($postdata['data'], ",")+1))));

echo $postdata['fld']."|".$postdata['name'];

include "commonAdmin.php";
include "picsTools.php";

$galleryId = $_GET['galleryId'];

commonValidateSession();

// get gallery details
$queryStr 		= "select picBgColor, picWidth, picHeight from galleries where id = $galleryId";
$galleryResult	= commonDoQuery ($queryStr);
$galleryRow		= commonQuery_fetchRow ($galleryResult);

$picBgColor = $galleryRow['picBgColor'];
if ($picBgColor == "")
	$picBgColor = "#FFFFFF";

$sourceFile	= commonQuery_escapeStr($postdata['name']);

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

$file = $filePath.$postdata['name'];

picsToolsResize($file, "", $galleryRow['picWidth'], $galleryRow['picHeight'], "$filePath$imageFile", $picBgColor);

$upload = ftp_put($connId, "galleries/gallery$galleryId/images/$imageFile", "$filePath$imageFile", FTP_BINARY);

// check upload status
if (!$upload) 
{ 
	debugLog ("There was a problem when uploding the new image " . $file);
}

commonFtpDisconnect($connId);

file_get_contents(commonGetDomainName($domainRow)."/galleries/gallery$galleryId/buildgallery.php");

unlink("$filePath$imageFile");
unlink($file);

?>