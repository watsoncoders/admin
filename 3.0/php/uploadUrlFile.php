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

echo ">> $dimensionId<br/>";
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


# load / reload file
# ------------------------------------------------------------------------------------------------------
// action (add | update)
// urlId
// url
// target
// title (by langs)
// txt (by langs)
// source file

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
	$queryStr = "select max(id) from urls";
	$result	  = commonDoQuery ($queryStr);
	$row	  = commonQuery_fetchRow ($result);
	$urlId 	  = $row[0] + 1;

	$picFile	 = $urlId . $suffix;

	$ibosUserId = commonGetIbosUserId ();

	$queryStr = "insert into urls (id, ibosUserId, url, displayUrl, target, picFile, sourceFile) 
				 values ($urlId, '$ibosUserId', '$url', '$displayUrl', '$target', '$picFile', '$sourceFile')";
	commonDoQuery ($queryStr);

	if ($categoryId != "")
	{
		// get last pos
		$queryStr   = "select max(pos) from categoriesItems where categoryId = $categoryId and type = 'url'";
		$result		= commonDoQuery ($queryStr);
		$row		= commonQuery_fetchRow ($result);
		$pos 		= $row[0] + 1;

		$queryStr = "insert into categoriesItems (itemId, categoryId, type, pos)
					 values ($urlId, $categoryId, 'url', $pos)";
		commonDoQuery ($queryStr);
	}
}
else
{
		$queryStr 	= "delete from urls_byLang where urlId=$urlId";
		commonDoQuery ($queryStr);

		$queryStr = "update urls set 	url	   	   = '$url',
										displayUrl = '$displayUrl',
										target     = '$target' ";

		if ($loadFile)
		{
			$picFile	= $urlId . $suffix;
			$queryStr	.= ",			   picFile 		= '$picFile',
										   sourceFile 	= '$sourceFile'";
		}
		else if ($sourceFile == "")
		{
			$queryStr	.= ",			   picFile 		= '',
										   sourceFile 	= ''";
		}

		$queryStr .= " where id=$urlId";
	
		commonDoQuery ($queryStr);
}

# add languages rows for this url
# ------------------------------------------------------------------------------------------------------
$langsArray = split(",",$usedLangs);

for ($i=0; $i<count($langsArray); $i++)
{
		$language	= $langsArray[$i];

		eval ("\$isReady = \$isReady$language;");
		eval ("\$picTitle = \$picTitle$language;");
		eval ("\$title = \$title$language;");
		eval ("\$txt = \$txt$language;");

		$picTitle	= commonPrepareToDB($picTitle);
		$title		= commonPrepareToDB($title);
		$txt		= commonPrepareToDB($txt);

		$queryStr		= "insert into urls_byLang (urlId, language, isReady, picTitle, title, txt)
						   values ('$urlId','$language', '$isReady', '$picTitle', '$title', '$txt')";
	
		commonDoQuery ($queryStr);
}

# load / reload file
# ------------------------------------------------------------------------------------------------------

if ($loadFile)
{
	$domainRow = commonGetDomainRow ();

	$connId    = commonFtpConnect    ($domainRow);

	$upload = ftp_put($connId, "urlsFiles/$picFile", $_FILES['picFile']['tmp_name'], FTP_BINARY); 

	$fileName = "${urlId}_size1.jpg";

	if ($picWidth == 0 && $picHeight == 0)
	{
		// keep size
		$upload = ftp_put($connId, "urlsFiles/$fileName", $_FILES['picFile']['tmp_name'], FTP_BINARY);
	}
	else
	{
		picsToolsResize($_FILES['picFile']['tmp_name'], $suffix, $picWidth, $picHeight, "/../../tmp/$fileName", $bgColor);
		
		$upload = ftp_put($connId, "urlsFiles/$fileName", "/../../tmp/$fileName", FTP_BINARY);
	}
		
	commonFtpDisconnect($connId);
}

header ("Location: http://www.i-bos.net/admin/2.0/html/content/handleUrls.html");
exit;

?>
