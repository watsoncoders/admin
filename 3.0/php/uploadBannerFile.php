<?php

include "commonAdmin.php";

// default color to fill in background is white
// FFU the application can set otherwise by POST
$bgColor = "#FFFFFF";

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
// bannerId
// url
// name
// type
// bannerText
// isNewWin
// status
// startDate
// expireDate
// picWidth		- only for movie banner
// picHeight  	- only for movie banner

if ($theStartDate == "")
	$startDate = "0000-00-00";
else
{
	$startDate  = preg_replace("/^([0-9]{1,2})[\/\. -]+([0-9]{1,2})[\/\. -]+([0-9]{1,4})/", "\\2/\\1/\\3", $theStartDate); 
	$startDate  = strtotime($startDate);
	$startDate  = date("Y-m-d", $startDate);
}

if ($expireDate == "")
	$expireDate = "0000-00-00";
else
{
	$expireDate  = preg_replace("/^([0-9]{1,2})[\/\. -]+([0-9]{1,2})[\/\. -]+([0-9]{1,4})/", "\\2/\\1/\\3", $expireDate); 
	$expireDate  = strtotime($expireDate);
	$expireDate  = date("Y-m-d", $expireDate);
}

$url			= commonPrepareToDB($url);
$bannerText 	= commonPrepareToDB($bannerText);
$name 			= commonPrepareToDB($name);
$htmlCode		= commonPrepareToDB($htmlCode);
$textForBanner	= commonPrepareToDB($textForBanner);

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

	if ($picWidth == "" && $picHeight == "")
		list($picWidth, $picHeight) = getimagesize($_FILES['picFile']['tmp_name']);
}

$onlyInPages = trim($pages);
if ($onlyInPages != "")
	$onlyInPages = "," . join(",", split(" ", $pages));

if ($action == "add")
{
	$queryStr   = "select max(id) from banners";
	$result		= commonDoQuery ($queryStr);
	$row		= commonQuery_fetchRow ($result);
	$bannerId 	= $row[0] + 1;

	$picFile	= $bannerId . $suffix;

	$queryStr = "insert into banners (id, url, type, bannerText, isNewWin, status, picFile, sourceFile, picWidth, picHeight, htmlCode, onlyInPages, 
									  doCountViews, doCountClicks, maxViews, maxClicks, textForBanner, name, startDate, expireDate) 
				 values ('$bannerId', '$url', '$type', '$bannerText', '$isNewWin', '$status', '$picFile', '$sourceFile', '$picWidth', '$picHeight', 
						 '$htmlCode', '$onlyInPages', '$doCountViews', '$doCountClicks', '$maxViews', '$maxClicks', '$textForBanner', '$name', 
				 		 '$startDate', '$expireDate')";

	commonDoQuery ($queryStr);
}
else
{
	$queryStr = "update banners set url    	 		= '$url',
									type   	 		= '$type',
									bannerText		= '$bannerText',
									isNewWin 		= '$isNewWin',
									status 	 		= '$status',
									name			= '$name',
									htmlCode		= '$htmlCode',
									textForBanner 	= '$textForBanner',
									onlyInPages		= '$onlyInPages',
									doCountViews	= '$doCountViews',
									doCountClicks	= '$doCountClicks',
									maxViews		= '$maxViews',
									maxClicks		= '$maxClicks',
									startDate		= '$startDate',
									expireDate		= '$expireDate' ";
									
	if ($loadFile)
	{
		$picFile	= $bannerId . $suffix;
	
		$queryStr .= ", 			 picFile    = '$picFile',
									 sourceFile = '$sourceFile'";
	}
	
	if ($loadFile || $type == "movie" || $type == "htmlCode" || $type == "text")
	{
		$queryStr .= ",
									 picWidth   =  '$picWidth',
									 picHeight  =  '$picHeight'";
	}

	$queryStr .= " where id = $bannerId";
	
	commonDoQuery ($queryStr);
}

# ------------------------------------------------------------------------------------------------------

if ($loadFile)
{
	$domainRow = commonGetDomainRow ();

	$connId    = commonFtpConnect    ($domainRow);

	$upload = ftp_put($connId, "bannerFiles/$picFile",
					$_FILES['picFile']['tmp_name'], FTP_BINARY);

	// check upload status
	if (!$upload) 
	{ 
	   echo "FTP upload has failed!";
	}

	// check upload status
	if (!$upload) 
	{ 
		echo "FTP upload has failed!";
	}

	commonFtpDisconnect($connId);
} 

header ("Location: ../html/content_extand/handleBanners.html");
exit;

?>
