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

// action (add | update)

$tags 	  = Array("id", "status", "areaId", "phone", "mobile", "fax", "email", "siteUrl", "sourceFile", "picFile",
				  "extraData1", "extraData2", "extraData3", "extraData4", "extraData5");
$langTags = Array("name", "bizName", "address", "description");

$loadFile = false;

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

if ($action == "add")
{
	$queryStr   = "select max(id) from shopProducers";
	$result		= commonDoQuery ($queryStr);
	$row		= commonQuery_fetchRow ($result);
	$id 		= $row[0] + 1;
}

$picFile	= $id . ".jpg";

$vals = Array();

for ($i=0; $i < count($tags); $i++)
{
	eval ("array_push (\$vals,\$$tags[$i]);");
}
	
if ($action == "add")
{
	$queryStr = "insert into shopProducers (" . join(",",$tags) . ") values ('" . join("','",$vals) . "')";
	commonDoQuery ($queryStr);
}
else
{
	$queryStr = "update shopProducers set ";

	for ($i=1; $i < count($tags); $i++)
	{
		if (!$loadFile && ($tags[$i] == "sourceFile" || $tags[$i] == "picFile")) continue;

		$queryStr .= "$tags[$i] = '$vals[$i]',";
	}


	$queryStr = trim($queryStr, ",");

	$queryStr .= " where id = $id ";

	commonDoQuery ($queryStr);

	$queryStr = "delete from shopProducers_byLang where producerId='$id'";
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

	$queryStr		= "insert into shopProducers_byLang (producerId, language," . join(",",$langTags) . ") 
					   values ($id, '$language', '" . join ("','", $vals) . "')";
	
	commonDoQuery ($queryStr);
}

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

if ($loadFile)
{
	$domainRow = commonGetDomainRow ();
	$connId	   = commonFtpConnect($domainRow);

	$tmpName = $_FILES["picFile"]["tmp_name"];
	
	@ftp_delete($connId, "producersFiles/$picFile");
	picsToolsResize($tmpName, $suffix, $picWidth, $picHeight, "/../../tmp/$picFile", $bgColor);
	$upload = ftp_put($connId, "producersFiles/$picFile", "/../../tmp/$picFile", FTP_BINARY);
	if (!$upload) echo "FTP upload has failed!";
} 

header ("Location: ../html/etrade/handleShopProducers.html");
exit;

?>
