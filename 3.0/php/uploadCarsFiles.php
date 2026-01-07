<?php

include "picsTools.php";
include "commonAdmin.php";

$filePath = "$ibosHomeDir/html/SWFUpload/files/";

move_uploaded_file($_FILES['Filedata']['tmp_name'], $filePath . $_FILES['Filedata']['name']);

chmod ($filePath . $_FILES['Filedata']['name'], 0755);

$itemId = $_GET['itemId'];


commonValidateSession();

$origName 	= $_FILES['Filedata']['name'];
$suffix		= commonFileSuffix ($origName);

// get next file id
// ----------------------------------------------------------------------------------------------------------------------------
$queryStr = "select max(id) from carItemsFiles";
$result		= commonDoQuery ($queryStr);
$row		= commonQuery_fetchRow ($result);
$fileId 	= $row[0] + 1;

$picFile	= $fileId . "_size0" . $suffix;

//debugLog ("fileId => $fileId");

if (strtolower($suffix) == ".swf" || strtolower($suffix) == ".avi")
{
	$fileType = "video";
}
else if (strtolower($suffix) == ".pdf")
{
	$fileType = "pdf";
}
else if (strtolower($suffix) == ".doc")
{
	$fileType = "doc";
}
else
{
	$queryStr	= "select carsTabletId from carsItems where id = $itemId";
	$result		= commonDoQuery($queryStr);
	$row		= commonQuery_fetchRow($result);
	$carId	= $row[0];

	$query		= "select picDimension1, picDimension2
				   from cars where id = $carId";
	$result		= commonDoQuery($query);
	$picDimsRow	= commonQuery_fetchRow($result);

	if ($picDimsRow['picDimension1'] != 0)
	{
		$sql	 = "select * from dimensions where id = $picDimsRow[picDimension1]";
		$result  = commonDoQuery($sql);
		$dimRow1 = commonQuery_fetchRow($result);
	}

	if ($picDimsRow['picDimension2'] != 0)
	{
		$sql	 = "select * from dimensions where id = $picDimsRow[picDimension2]";
		$result  = commonDoQuery($sql);
		$dimRow2 = commonQuery_fetchRow($result);
	}

	$fileType = "pic";
}

$fileSize = filesize($filePath . $_FILES['Filedata']['name']);

// get max pos file for this lessaon
// ----------------------------------------------------------------------------------------------------------------------------
$queryStr   = "select max(pos) from carItemsFiles where itemId = $itemId";
$result		= commonDoQuery ($queryStr);
$row		= commonQuery_fetchRow ($result);
$pos	 	= $row[0] + 1;

// add new file of this lesson
// ----------------------------------------------------------------------------------------------------------------------------
$queryStr   = "insert into carItemsFiles (id, itemId, fileType, fileSize, pos, file, sourceFile)
			   values ($fileId, $itemId, '$fileType', $fileSize, $pos, '$picFile', '$origName')";
commonDoQuery ($queryStr);

// copy file to site domain
// ----------------------------------------------------------------------------------------------------------------------------
$domainRow = commonGetDomainRow ();

$connId    = commonFtpConnect    ($domainRow);
	
$upload = ftp_put($connId, "carsFiles/$picFile",
				  $filePath . $_FILES['Filedata']['name'], FTP_BINARY);

// check upload status
if (!$upload) 
{ 
	debugLog ("There was a problem when uploding the new file " . $filePath . $_FILES['Filedata']['name'] . 
			  " to carsFiles/$picFile");
}

if ($fileType == "pic")
{
	$i = 1;

	if ($picDimsRow['picDimension1'] != 0)
	{
		$resizedFileName = $fileId . "_size$i.jpg";

		picsToolsResize($filePath . $_FILES['Filedata']['name'], $suffix, $dimRow1['width'], $dimRow1['height'],
					    "/../../tmp/$resizedFileName", $dimRow1['color']);

		$upload = ftp_put($connId, "carsFiles/$resizedFileName",
						  "/../../tmp/$resizedFileName", FTP_BINARY);

		$i++;
	}

	if ($picDimsRow['picDimension2'] != 0)
	{
		$resizedFileName = $fileId . "_size$i.jpg";

		picsToolsResize($filePath . $_FILES['Filedata']['name'], $suffix, $dimRow2['width'], $dimRow2['height'],
					    "/../../tmp/$resizedFileName", $dimRow2['color']);

		$upload = ftp_put($connId, "carsFiles/$resizedFileName",
						  "/../../tmp/$resizedFileName", FTP_BINARY);
	}
}

ftp_close($connId);

unlink ($filePath . $_FILES['Filedata']['name']);

/* ----------------------------------------------------------------------------------------------------------------------------	*/
/* debugLog																														*/
/* ----------------------------------------------------------------------------------------------------------------------------	*/
function debugLog ($msg)
{
	$debug = fopen("/tmp/carsFiles.log", "a");
	fwrite ($debug, "$msg\n");
	fclose ($debug);
}

echo "1";
?>
