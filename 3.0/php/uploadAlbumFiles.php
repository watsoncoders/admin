<?php

include "commonAdmin.php";
include "commands_albums.php";

/* Handle adding images to album (multi upload) */
set_time_limit(0);

$userId		 = $_GET['userId'];

$filePath = "$ibosHomeDir/html/SWFUpload/files/$userId/";
if (!file_exists($filePath))
	mkdir ("$filePath", 0777);

parse_str(file_get_contents("php://input"), $postdata);

file_put_contents($filePath.$postdata['name'], base64_decode(str_replace(' ', '+', substr($postdata['data'], strrpos($postdata['data'], ",")+1))));

//echo $postdata['fld']."|".$postdata['name'];

$albumId = $_GET['albumId'];

commonValidateSession();

// get album details
$sql 		= "select * from albums where id = $albumId";
$result		= commonDoQuery ($sql);
$albumRow	= commonQuery_fetchRow ($result);

if ($albumRow['bgColor'] == "")
	$albumRow['bgColor'] = "#FFFFFF";

if ($albumRow['quality'] == 0) $albumRow['quality'] = 90;

$sourceFile	= commonQuery_escapeStr($postdata['name']);

// get max pos image in this album
// ----------------------------------------------------------------------------------------------------------------------------
$sql   		= "select max(pos) from albumImages where albumId = $albumId";
$result		= commonDoQuery ($sql);
$row		= commonQuery_fetchRow ($result);
$pos		= $row[0] + 1;

// get max image id
// ----------------------------------------------------------------------------------------------------------------------------
$sql   		= "select max(id) from albumImages";
$result		= commonDoQuery ($sql);
$row		= commonQuery_fetchRow ($result);
$imageId	= $row[0] + 1;

// check file details
// ----------------------------------------------------------------------------------------------------------------------------
$suffix	= strtolower(commonFileSuffix($sourceFile));

$file	= "${albumId}_${imageId}_size0$suffix";

if (in_array(strtolower($suffix), array('.jpg', '.gif', '.png', '.bmp')))
{
	$fileType 	= "pic";
}
else
{
	$fileType 	= "video";
}

// add new album image
// ----------------------------------------------------------------------------------------------------------------------------
$sql   = "insert into albumImages (id, albumId, fileType, pos, filename, sourceFile) 
		  values ($imageId, '$albumId', '$fileType', '$pos', '$file', '$sourceFile')";
commonDoQuery ($sql);

# add languages rows for this image
# ------------------------------------------------------------------------------------------------------
$sql		= "select langs from globalParms";
$result		= commonDoQuery($sql);
$row		= commonQuery_fetchRow($result);
$usedLangs	= $row['langs'];

$langsArray = explode(",",$usedLangs);

for ($i=0; $i<count($langsArray); $i++)
{
	$language			= $langsArray[$i];

	$sql		= "insert into albumImages_byLang (imageId, albumId, language, title) values ('$imageId','$albumId','$language','')";
	commonDoQuery ($sql);
}

// copy file to site domain
// ----------------------------------------------------------------------------------------------------------------------------
$domainRow = commonGetDomainRow ();
$connId	   = commonFtpConnect($domainRow);

ftp_chdir($connId, "albumsFiles/");

if ($fileType == "pic")
	uploadPic ($connId, $albumRow, $sourceFile, $suffix, $file);
else
	ftp_put($connId, $file, "$filePath$sourceFile", FTP_BINARY); 

commonFtpDisconnect($connId);

commonDeleteOldFiles ($filePath, 3600);	// 1 hour

?>
