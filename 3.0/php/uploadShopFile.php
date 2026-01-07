<?php

include "commonAdmin.php";
include "picsTools.php";

// default color to fill in background is white
// FFU the application can set otherwise by POST
while (list($key, $val) = each($_GET)) 
{
	eval("$$key = '$val';");
}
while (list($key, $val) = each($_POST)) 
{
	eval("$$key = '$val';");
}

commonValidateSession();

// action (add | update)
// productId
// fileId (for update)
// name (by langs)
// fileType

$loadFile = false;

if ($_FILES['file']['name'])
{
	$origName 	= $_FILES['file']['name'];
	$splitName 	= split("\.",$origName);
	$suffix 	= "";
	if (count($splitName) > 0)
		$suffix	= "." . $splitName[count($splitName)-1];
	$suffix = strtolower($suffix);

	$loadFile = true;

	$sourceFile	= addslashes($sourceFile);
}

$picType = "";
if ($fileType != "video" && $fileType != "pdf" && $fileType != "doc")
{
	$picType  = $fileType;
	$fileType = "pic";
}

if ($action == "add")
{
	$queryStr   = "select max(fileId) from shopProductsFiles";
	$result		= commonDoQuery ($queryStr);
	$row		= commonQuery_fetchRow ($result);
	$fileId 		= $row[0] + 1;

	$file	= $productId . "_" . $fileId . "_size0" . $suffix;

	$queryStr   = "insert into shopProductsFiles (fileId, fileType, picType, productId, file, sourceFile)
				   values ('$fileId', '$fileType', '$picType', '$productId', '$file', '$sourceFile')";
	commonDoQuery ($queryStr);
}
else
{
	$queryStr = "delete from shopProductsFiles_byLang where fileId='$fileId'";
	commonDoQuery ($queryStr);

	$queryStr = "update shopProductsFiles set fileType = '$fileType',
		   									  picType  = '$picType'	";

	if ($loadFile)
	{
		$file	= $productId . "_" . $fileId . "_size0" . $suffix;
		
		$queryStr .= ", file = '$file', sourceFile = '$sourceFile'";
	}

	$queryStr .= " where fileId=$fileId";
	
	commonDoQuery ($queryStr);
}

# add languages rows for this fileId
# ------------------------------------------------------------------------------------------------------
$langsArray = split(",",$usedLangs);

for ($i=0; $i<count($langsArray); $i++)
{
	$language			= $langsArray[$i];

	eval ("\$fileText = \$fileText$language;");

	$fileText			= commonPrepareToDB($fileText);

	$queryStr		= "insert into shopProductsFiles_byLang (fileId, language, fileText)
					   values ('$fileId','$language','$fileText')";
	
	commonDoQuery ($queryStr);
}

if ($fileType == "pic")
{
	// Get user request image size
	$picSizeQuery	= "select wideDimension1, wideDimension2, wideDimension3, wideDimension4, 
					   		  longDimension1, longDimension2, longDimension3, longDimension4
		               from shopConfig";
	$resultPicSize	= commonDoQuery ($picSizeQuery);
	$picSizeRow		= commonQuery_fetchRow ($resultPicSize);

	$dims			= array();
	$sql			= "select * from dimensions";
	$result 		= commonDoQuery($sql);

	while ($dimRow = commonQuery_fetchRow($result))
	{
		$dims[$dimRow['id']] = $dimRow;
	}
}

if ($loadFile)
{
	$domainRow = commonGetDomainRow ();

	$connId    = commonFtpConnect    ($domainRow);

	$upload = ftp_put($connId, "shopFiles/$file", $_FILES['file']['tmp_name'], FTP_BINARY);

	if ($fileType == "pic")
	{
		list($width_orig, $height_orig) = getimagesize($_FILES['file']['tmp_name']);

		$file = str_replace("$suffix", ".jpg", $file);

		for ($i = 1; $i <= 4; $i++)
		{
			$resizedFileName = str_replace("size0", "size$i", $file);

			$toLoadResize = false;

			if ($width_orig >= $height_orig) 
			{
				$dim = $picSizeRow["wideDimension$i"];

				if ($dim == 0)
					$dim = $picSizeRow["longDimension$i"];
			}
			else
			{
				$dim = $picSizeRow["longDimension$i"];

				if ($dim == 0)
					$dim = $picSizeRow["wideDimension$i"];
			}

			if ($dim == 0) continue;

			// get dim details
			$dimRow = $dims[$dim];

			if ($dimRow['width'] != 0 || $dimRow['height'] != 0)
			{
				if ($dimRow['color'] == "") $dimRow['color'] = "#FFFFFF";

				picsToolsResize($_FILES['file']['tmp_name'], $suffix, $dimRow['width'], $dimRow['height'],
							   "/../../tmp/$resizedFileName", $dimRow['color']);

				$toLoadResize = true;
			} 

			if ($toLoadResize)
			{
				$upload = ftp_put($connId, "shopFiles/$resizedFileName",
								  "/../../tmp/$resizedFileName", FTP_BINARY);

				// check upload status
				if (!$upload) 
				{ 
   					echo "FTP upload has failed! (size $i)";
				}
			}
		}
	}

	commonFtpDisconnect($connId);
} // if

header ("Location: ../html/etrade/handleShopFiles.html");
exit;

?>
