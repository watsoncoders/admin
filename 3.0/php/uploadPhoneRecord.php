<?php

include "commonAdmin.php";
include "format.php";
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

// action (add | update)

$tags 	  = Array("id", "bookId", "status", "email", "hideEmail", "siteUrl", "facebookLink", "homePhone", "workPhone", 
				  "extraPhone1", "extraPhone2", "cellphone", "fax", "workFax", "cityId", "areaId", "workCityId", "workAreaId", 
				   "zipcode", "workZipcode", "birthDate", "gender", "picFile", "sourceFile", 
				  "enumValue1", "enumValue2", "enumValue3", "enumValue4");
$langTags = Array("firstName", "lastName", "city", "address", "workCity", "workAddress", "occupation", "description", "extraData1", 
				  "extraData2", "extraData3", "extraData4", "extraData5");

$id = $recordId;

if ($action == "add")
{
	$queryStr	= "select max(id) from phonesRecords";
	$result		= commonDoQuery ($queryStr);
	$row		= commonQuery_fetchRow ($result);
	$id 		= $row[0] + 1;
}

$birthDate = formatApplToDB($birthDate . " 00:00:00");

$loadFile = false;

$sql	= "select picDimension from phonesBooks where id = $bookId";
$result	= commonDoQuery($sql);
$row	= commonQuery_fetchRow($result);

$picDimension = $row['picDimension'];

if ($picDimension != "" || $picDimension != 0)
{
	$sql   			= "select width, height, color from dimensions where id = $picDimension";
	$result			= commonDoQuery ($sql);
	$dimensionRow	= commonQuery_fetchRow ($result);

	$resize = true;
}
else
{
	$resize = false;
}

if ($_FILES['picFile']['name'])
{
	$origName 	= $_FILES['picFile']['name'];
	$splitName 	= split("\.",$origName);
	$suffix 	= "";
	if (count($splitName) > 0)
		$suffix	= "." . $splitName[count($splitName)-1];

	$loadFile = true;

	$sourceFile	= addslashes($sourceFile);
}

if (!$loadFile && $toDelete == "1")
{
	$picFile 	= "";
	$sourceFile = "";
}

$picFile	= $id . $suffix;

if ($resize)
	$resizedFileName = $id . ".jpg";
else
	$resizedFileName = $picFile;
		

$vals = Array();

for ($i=0; $i < count($tags); $i++)
{
	eval ("array_push (\$vals,\$$tags[$i]);");
}
	
if ($action == "add")
{
	$queryStr = "insert into phonesRecords (" . join(",",$tags) . ") values ('" . join("','",$vals) . "')";
	commonDoQuery ($queryStr);
}
else
{
	$queryStr = "update phonesRecords set ";

	for ($i=1; $i < count($tags); $i++)
	{
		if (!$loadFile && $toDelete != "1" && ($tags[$i] == "sourceFile" || $tags[$i] == "picFile")) continue;

		$queryStr .= "$tags[$i] = '$vals[$i]',";
	}

	$queryStr = trim($queryStr, ",");

	$queryStr .= " where id = $id ";

	commonDoQuery ($queryStr);

	$queryStr = "delete from phonesRecords_byLang where recordId='$id'";
	commonDoQuery ($queryStr);
}

# add languages rows for this producer
# ------------------------------------------------------------------------------------------------------
$langsArray = split(",",$usedLangs);

for ($i=0; $i<count($langsArray); $i++)
{
	$language		= $langsArray[$i];

	$vals = Array();
	for ($j=0; $j < count($langTags); $j++)
	{
		eval ("\$$langTags[$j] = commonPrepareToDB(\$$langTags[$j]$language);");	
		eval ("array_push (\$vals,\$$langTags[$j]);");
	}		

	$queryStr		= "insert into phonesRecords_byLang (recordId, language," . join(",",$langTags) . ") 
					   values ($id, '$language', '" . join ("','", $vals) . "')";
	
	commonDoQuery ($queryStr);
}

if ($loadFile || $toDelete == "1")
{
	$domainRow = commonGetDomainRow ();

	$connId    = commonFtpConnect    ($domainRow);
 
	if ($loadFile)
	{
		if ($resize)
		{
			picsToolsResize($_FILES['picFile']['tmp_name'], $suffix, $dimensionRow['width'], $dimensionRow['height'], "/../../tmp/$resizedFileName", 
							$dimensionRow['color']);
		
			$upload = ftp_put($connId, "phonesBookFiles/$resizedFileName", "/../../tmp/$resizedFileName", FTP_BINARY);
		}
		else
		{
			$upload = ftp_put($connId, "phonesBookFiles/$picFile", $_FILES['picFile']['tmp_name'], FTP_BINARY);
		}

		// check upload status
		if (!$upload) 
		{ 
		   echo "FTP upload has failed!";
		}
	}
	else
	{
		@ftp_delete ($connId, "phonesBookFiles/$origPicFile");
	}

	commonFtpDisconnect($connId);
} 

header ("Location: ../html/content_tablets/handlePhonesBook.html");
exit;

?>
