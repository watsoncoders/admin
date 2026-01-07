<?php

include "commonAdmin.php";
include "picsTools.php";

while (list($key, $val) = each($_GET)) 
{
	eval("$$key = '$val';");
}
while (list($key, $val) = each($_POST)) 
{
	eval("$$key = '$val';");
}

commonValidateSession();

# load / reload file
# ------------------------------------------------------------------------------------------------------
// action (add | update)
// id
// parentId
// name (by langs)
// continent
// phonePrefix
// description (by langs)
// source file

$loadFile = false;
$picFile  = "";

if ($_FILES['picFile']['name'])
{
	$origName 	= $_FILES['picFile']['name'];
	$splitName 	= split("\.",$origName);
	$suffix 	= "";
	if (count($splitName) > 0)
		$suffix	= "." . $splitName[count($splitName)-1];

	$loadFile = true;

	$picFile	 = $id . $suffix;
	$sourceFile	= addslashes($sourceFile);

	if ($dimensionId == 0)
	{
		$picWidth  = 0;
		$picHeight = 0;
		$bgColor   = "#FFFFFF";
	}
	else if ($dimensionId != "")
	{
		$queryStr   = "select width, height, color from dimensions where id = $dimensionId";
		$result		= commonDoQuery ($queryStr);
		$row		= commonQuery_fetchRow ($result);

		$picWidth 	= $row['width'];
		$picHeight 	= $row['height'];
		$bgColor 	= $row['color'];
	}
	else
	{
		$picWidth  = "200";
		$picHeight = "100";
		$bgColor   = "#FFFFFF";
	}
}


if ($action == "add")
{
	$queryStr = "select max(id) from countries";
	$result	  = commonDoQuery ($queryStr);
	$row	  = commonQuery_fetchRow ($result);
	$id 	  = $row[0] + 1;

	$queryStr = "insert into countries (id, continent, phonePrefix, parentId, picFile, sourceFile) 
				 values ($id, '$continent', '$phonePrefix', '$parentId', '$picFile', '$sourceFile')";
	commonDoQuery ($queryStr);
}
else
{
		$queryStr 	= "delete from countries_byLang where countryId=$id";
		commonDoQuery ($queryStr);

		$queryStr = "update countries set continent  = '$continent', phonePrefix = '$phonePrefix', parentId = '$parentId' ";

		if ($loadFile)
		{
			$queryStr	.= ",			   picFile 		= '$picFile',
										   sourceFile 	= '$sourceFile'";
		}
		$queryStr .= " where id=$id";
	
		commonDoQuery ($queryStr);
}

# add languages rows for this country
# ------------------------------------------------------------------------------------------------------
$langsArray = split(",",$usedLangs);

for ($i=0; $i<count($langsArray); $i++)
{
		$language	= $langsArray[$i];

		eval ("\$name = \$name$language;");
		eval ("\$description = \$description$language;");

		$name			= commonPrepareToDB($name);
		$description	= commonPrepareToDB($description);

		$queryStr		= "insert into countries_byLang (countryId, language, name, description)
						   values ('$id', '$language','$name', '$description')";
	
		commonDoQuery ($queryStr);
}

# load / reload file
# ------------------------------------------------------------------------------------------------------

if ($loadFile)
{
	$domainRow = commonGetDomainRow ();

	$connId    = commonFtpConnect    ($domainRow);

	$tmpName = $_FILES["picFile"]["tmp_name"];

	if ($picWidth == 0)	// keep size
	{
		$upload = ftp_put($connId, "countriesFiles/$picFile", $tmpName, FTP_BINARY);
	}
	else
	{
		picsToolsResize($tmpName, $suffix, $picWidth, $picHeight, "/../../tmp/$picFile", $bgColor);
	
		$upload = ftp_put($connId, "countriesFiles/$picFile", "/../../tmp/$picFile", FTP_BINARY);
	}

	// check upload status
	if (!$upload) 
	{ 
	   echo "FTP upload has failed!";
	}

	commonFtpDisconnect($connId);
}

header ("Location: ../html/content_enums/handleCountries.html");
exit;

?>
