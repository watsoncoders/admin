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
// parentId
// pos
// name (by langs)
// description (by langs)
// shortDescription (by langs)
// picTitle (by langs)
// winTitle (by langs)
// metaKeywords (by langs)
// metaDescription (by langs)
// source file
// picsDisplay

if ($groupId == "") $groupId = 0;

$linkCode = addslashes($linkCode);

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


if ($parentId == "") $parentId = 0;

if ($action == "add")
{
		$queryStr 	 = "select max(pos) from categories where parentId=$parentId and type = '$type'";
		$result	     = commonDoQuery ($queryStr);
		$row	     = commonQuery_fetchRow($result);
		$pos		 = $row[0];
		
		if ($pos == "")
			$pos = 1;
		else
			$pos++;

		# get new category id
		# --------------------------------------------------------------------------------------------------
		$queryStr 	 = "select max(id) from categories";
		$result	     = commonDoQuery ($queryStr);
		$row	     = commonQuery_fetchRow($result);
		$id		  	 = $row[0] + 1;

		$queryStr = "insert into categories (id, parentId, pos, type, groupId, linkCode, categoryFile, sourceFile, picsDisplay)
					 values ('$id', '$parentId', '$pos', '$type', '$groupId', '$linkCode', '$picFile', '$sourceFile', '$picsDisplay')";

		commonDoQuery ($queryStr);
}
else
{
		$queryStr 	= "delete from categories_byLang where categoryId='$id'";
		commonDoQuery ($queryStr);

		// get curr category pos
		$queryStr	= "select parentId, pos from categories where id=$id";
		$result		= commonDoQuery ($queryStr);
		$row		= commonQuery_fetchRow($result);
		$currPos	= $row['pos'];
		$parentId	= $row['parentId'];

		if ($currPos > $pos)
		{
			$queryStr = "update categories set pos = pos+1 where parentId=$parentId and pos >= $pos and pos < $currPos";

			commonDoQuery ($queryStr);
		}

		if ($currPos < $pos)
		{
			$queryStr = "update categories set pos = pos-1 where parentId=$parentId and pos > $currPos and pos <= $pos";
			commonDoQuery ($queryStr);
		}

		$queryStr = "update categories set pos	  	   =  $pos,
										   type		   = '$type',
										   groupId	   = '$groupId',
										   linkCode	   = '$linkCode',
										   picsDisplay = '$picsDisplay'";
		if ($loadFile)
		{
			$queryStr	.= ",			   categoryFile = '$picFile',
										   sourceFile 	= '$sourceFile'";
		}
		$queryStr .= " where id=$id";
	
		commonDoQuery ($queryStr);
}

# add languages rows for this category
# ------------------------------------------------------------------------------------------------------
$langsArray = split(",",$usedLangs);

for ($i=0; $i<count($langsArray); $i++)
{
		$language			= $langsArray[$i];

		eval ("\$name = \$name$language;");
		eval ("\$description = \$description$language;");
		eval ("\$shortDescription = \$shortDescription$language;");
		eval ("\$picTitle = \$picTitle$language;");
		eval ("\$winTitle = \$winTitle$language;");
		eval ("\$metaKeywords = \$metaKeywords$language;");
		eval ("\$metaDescription = \$metaDescription$language;");
		eval ("\$isReady = \$isReady$language;");

		if ($isReady == "") $isReady = "1";

		$name				= commonPrepareToDB($name);
		$description		= commonPrepareToDB($description);
		$shortDescription	= commonPrepareToDB($shortDescription);
		$picTitle			= commonPrepareToDB($picTitle);
		$winTitle			= commonPrepareToDB($winTitle);
		$metaKeywords		= commonPrepareToDB($metaKeywords);
		$metaDescription	= commonPrepareToDB($metaDescription);

		$queryStr		= "insert into categories_byLang (categoryId, language, name, description, shortDescription, isReady, picTitle,
														  winTitle, metaKeywords, metaDescription)
						   values ('$id','$language','$name', '$description', '$shortDescription', $isReady, '$picTitle',
								   '$winTitle', '$metaKeywords', '$metaDescription')";
	
		commonDoQuery ($queryStr);
}


commonSaveItemFlags ($id, "category");

# load / reload file
# ------------------------------------------------------------------------------------------------------

if ($loadFile)
{
	$domainRow = commonGetDomainRow ();

	$connId    = commonFtpConnect    ($domainRow);

	$tmpName = $_FILES["picFile"]["tmp_name"];

	if ($picWidth == 0)	// keep size
	{
		$upload = ftp_put($connId, "shopFiles/$catFile", $tmpName, FTP_BINARY);
	}
	else
	{
		picsToolsResize($tmpName, $suffix, $picWidth, $picHeight, "/../../tmp/$catFile", $bgColor);
	
		$upload = ftp_put($connId, "shopFiles/$catFile", "/../../tmp/$catFile", FTP_BINARY);
	}

	// check upload status
	if (!$upload) 
	{ 
	   echo "FTP upload has failed!";
	}

	commonFtpDisconnect($connId);
}

header ("Location: ../html/content/handleCategories.html");
exit;

?>
