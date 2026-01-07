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
// variationId
// groupId
// priceFunction
// priceValue
// index1 (by langs)
// index2 (by langs)
// index3 (by langs)
// desc (by langs)
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

	$picSource	= addslashes($picSource);
}

$picFile = "";

if ($action == "add")
{
	$queryStr 		= "select max(id) from shopVariations";
	$result	  		= commonDoQuery ($queryStr);
	$row	  		= commonQuery_fetchRow ($result);
	$variationId 	= $row[0] + 1;

	if ($loadFile)
		$picFile	 = $variationId . $suffix;

	$queryStr = "insert into shopVariations (id, groupId, type, priceFunction, priceValue, picFile, picSource) 
				 values ($variationId, '$groupId', 'public', '$priceFunction', '$priceValue', '$picFile', '$picSource')";
	commonDoQuery ($queryStr);
}
else
{
	$queryStr 	= "delete from shopVariations_byLang where variationId=$variationId";
	commonDoQuery ($queryStr);

	$queryStr = "update shopVariations set groupId			= '$groupId',
									   	   priceFunction 	= '$priceFunction',
									   	   priceValue 		= '$priceValue' ";

	if ($loadFile)
	{
		$picFile	= $variationId . $suffix;

		$queryStr	.= "			   ,
									   picFile 			= '$picFile',
									   picSource 		= '$picSource'";
	}
	else if ($picSource == "")
	{
		$queryStr	.= "			   ,
									   picFile 			= '',
									   picSource 		= ''";
	}


	$queryStr .= " where id=$variationId";
		
	commonDoQuery ($queryStr);
}

# add languages rows for this variation
# ------------------------------------------------------------------------------------------------------
$langsArray = split(",",$usedLangs);

for ($i=0; $i<count($langsArray); $i++)
{
		$language	= $langsArray[$i];

		eval ("\$index1 = \$index1$language;");
		eval ("\$index2 = \$index2$language;");
		eval ("\$index3 = \$index3$language;");
		eval ("\$desc   = \$desc$language;");

		$index1	= commonPrepareToDB($index1);
		$index2	= commonPrepareToDB($index2);
		$index3	= commonPrepareToDB($index3);
		$name	= commonPrepareToDB($name);
		$desc	= commonPrepareToDB($desc);

		$queryStr		= "insert into shopVariations_byLang (variationId, language, index1, index2, index3, `desc`)
						   values ('$variationId','$language','$index1', '$index2', '$index3', '$desc')";
	
		commonDoQuery ($queryStr);
}

# load / reload file
# ------------------------------------------------------------------------------------------------------

if ($loadFile)
{
	$domainRow = commonGetDomainRow ();

	$connId    = commonFtpConnect    ($domainRow);

	$upload = ftp_put($connId, "productVarFiles/$picFile", $_FILES['picFile']['tmp_name'], FTP_BINARY); 

	$fileName = "${variationId}_size0.jpg";

	if ($picWidth == 0 && $picHeight == 0)
	{
		// keep size
		$upload = ftp_put($connId, "productVarFiles/$fileName", $_FILES['picFile']['tmp_name'], FTP_BINARY);
	}
	else
	{
		picsToolsResize($_FILES['picFile']['tmp_name'], $suffix, $picWidth, $picHeight, "/../../tmp/$fileName", $bgColor);
		
		$upload = ftp_put($connId, "productVarFiles/$fileName", "/../../tmp/$fileName", FTP_BINARY);
	}
		
	commonFtpDisconnect($connId);
}

header ("Location: ../html/etrade/handleShopVariations.html");
exit;

?>
