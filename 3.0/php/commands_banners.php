<?php

/* ----------------------------------------------------------------------------------------------------	*/
/* getBanners																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function getBanners ($xmlRequest)
{
	global $usedLangs;

	$sortBy		= xmlParser_getValue($xmlRequest,"sortBy");

	if ($sortBy == "")
		$sortBy = "id";

	$sortDir	= xmlParser_getValue($xmlRequest,"sortDir");
	if ($sortDir == "")
		$sortDir = "desc";

	if ($sortBy == "picSize")
		$sortBy = "picWidth $sortDir, picHeight";

	$condition  = "";

	$category = xmlParser_getValue($xmlRequest, "category");
	if ($category != "")
	{
		$condition .= " and categoriesItems.categoryId = $category ";
	}

	$bannerId = xmlParser_getValue($xmlRequest, "bannerId");
	if ($bannerId != "")
	{
		$condition .= " and banners.id = $bannerId ";
	}

	$picWidth = xmlParser_getValue($xmlRequest, "width");
	if ($picWidth != "")
	{
		$condition .= " and banners.picWidth = $picWidth ";
	}

	$picHeight = xmlParser_getValue($xmlRequest, "height");
	if ($picHeight != "")
	{
		$condition .= " and banners.picHeight = $picHeight ";
	}

	// get total
	$queryStr	 = "select count(distinct id) from banners 
					left join categoriesItems on banners.id = categoriesItems.itemId and categoriesItems.type = 'banner'
					where 1 $condition";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$total	     = $row[0];

	// get details
	$queryStr = "select distinct banners.* from banners
		   	     left join categoriesItems on banners.id = categoriesItems.itemId and categoriesItems.type = 'banner'
			 	 where 1 $condition 
				 order by $sortBy $sortDir " . commonGetLimit ($xmlRequest);

	$result	     = commonDoQuery ($queryStr);

	$numRows    = commonQuery_numRows($result);

	$xmlResponse = "<items>";

	$domainRow   = commonGetDomainRow ();
	$filePrefix  = commonGetDomainName($domainRow) . "/bannerFiles/";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		$id   	  	 = $row['id'];
		$url	   	 = commonValidXml ($row['url'],true);
		$name	   	 = commonValidXml ($row['name'],true);
		$type        = formatBannerType($row['type']);
		$picWidth  	 = $row['picWidth'];
		$picHeight 	 = $row['picHeight'];
		$picWidth 	 = $row['picWidth'];
		$countViews	 = $row['countViews'];
		$countClicks = $row['countClicks'];

		if ($countViews == 0) 
			$percent = "0";
		else
			$percent	 = round($countClicks / $countViews * 100);

		$today = strtotime(date("Y-m-d", strtotime("now")));

		$status  = formatActiveStatus($row['status']);

		if ($row['expireDate'] != "0000-00-00" && strtotime($row['expireDate']) < $today)
			$status = commonPhpEncode ("פג תוקף");
		elseif ($row['startDate'] != "0000-00-00" && strtotime($row['startDate']) > $today)
			$status = commonPhpEncode ("עתידי");
		elseif ($row['status'] == 'inactive')
				$status  = formatActiveStatus($row['status']);
		elseif ($row['expireDate'] != "0000-00-00")
				$status  = commonPhpEncode ("עד ".substr($row['expireDate'],8,2)."-".substr($row['expireDate'],5,2)."-".substr($row['expireDate'],2,2));

		$picSize	 = "$picWidth X $picHeight";

		$picFile   	  = commonValidXml($row['picFile']);
		$fullFileName = $filePrefix . urlencode($row['picFile']);
		$fullFileName = commonValidXml($fullFileName);

		if ($row['name'] != "")
		{
			$url  = $name;
			$name = commonValidXml ($row['url'],true);
		}
			
		$xmlResponse .=	"<item>
							<bannerId>$id</bannerId>
							<url>$url</url>
							<type>$type</type>
							<picSize>$picSize</picSize>
							<countViews>$countViews</countViews>
							<countClicks>$countClicks</countClicks>
							<percent>$percent</percent>
							<status>$status</status>
							<fullFileName>$fullFileName</fullFileName>
							<name>$name</name>
						 </item>";
	}

	$xmlResponse .=	"</items>"												.
					commonGetTotalXml($xmlRequest,$numRows,$total);
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* addBanner																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function addBanner ($xmlRequest)
{
	return (editBanner ($xmlRequest, "add"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateBanner																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateBanner ($xmlRequest)
{
	return (editBanner ($xmlRequest, "update"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* editBanner																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function editBanner ($xmlRequest, $editType)
{
	global $usedLangs;
	global $userId;
	global $ibosHomeDir;

	$bgColor = "#FFFFFF";

	$bannerId		= xmlParser_getValue($xmlRequest, "bannerId");
	$url			= addslashes(xmlParser_getValue($xmlRequest, "url"));
	$type			= xmlParser_getValue($xmlRequest, "type");
	$bannerText		= addslashes(xmlParser_getValue($xmlRequest, "bannerText"));
	$isNewWin		= xmlParser_getValue($xmlRequest, "isNewWin");
	$status			= xmlParser_getValue($xmlRequest, "status");
	$picWidth		= xmlParser_getValue($xmlRequest, "picWidth");
	$picHeight		= xmlParser_getValue($xmlRequest, "picHeight");
	$htmlCode		= addslashes(xmlParser_getValue($xmlRequest, "htmlCode"));
	$onlyInPages	= xmlParser_getValue($xmlRequest, "onlyInPages");
	$doCountViews	= xmlParser_getValue($xmlRequest, "doCountViews");
	$doCountClicks	= xmlParser_getValue($xmlRequest, "doCountClicks");
	$maxViews		= xmlParser_getValue($xmlRequest, "maxViews");
	$maxClicks		= xmlParser_getValue($xmlRequest, "maxClicks");
	$textForBanner	= addslashes(xmlParser_getValue($xmlRequest, "textForBanner"));
	$name			= addslashes(xmlParser_getValue($xmlRequest, "name"));
	$startDate		= xmlParser_getValue($xmlRequest, "theStartDate");
	$expireDate		= xmlParser_getValue($xmlRequest, "expireDate");

	if ($startDate  != "") $startDate  = formatApplToDB($startDate);
	if ($expireDate != "") $expireDate = formatApplToDB($expireDate);
	
	$onlyInPages = trim($onlyInPages);
	if ($onlyInPages != "")
		$onlyInPages = "," . join(",", explode(" ", $onlyInPages)) . ",";

	if ($editType == "add")
	{
		$queryStr   = "select max(id) from banners";
		$result		= commonDoQuery ($queryStr);
		$row		= commonQuery_fetchRow ($result);
		$bannerId 	= $row[0] + 1;
	}

	// handle link file
	# ------------------------------------------------------------------------------------------------------
	$filePath 		= "$ibosHomeDir/html/SWFUpload/files/$userId/";

	$sourceFile 	= addslashes(xmlParser_getValue($xmlRequest, "sourceFile"));	

	$fileLoaded  	= false;

	$picFile 		= "";
	$suffix 		= "";

	if ($sourceFile != "")
	{
		$fileLoaded = true;
		
		$suffix	= commonFileSuffix($sourceFile);

		$picFile = "${bannerId}$suffix";

		if ($picWidth == "" && $picHeight == "")
			list($picWidth, $picHeight) = getimagesize("$filePath/$sourceFile");
	}

	$oldFile	= "";

	# ------------------------------------------------------------------------------------------------------

	if ($editType == "add")
	{
		$queryStr = "insert into banners (id, url, type, bannerText, isNewWin, status, picFile, sourceFile, picWidth, picHeight, htmlCode, onlyInPages, 
									  	  doCountViews, doCountClicks, maxViews, maxClicks, textForBanner, name, startDate, expireDate) 
				 	 values ($bannerId, '$url', '$type', '$bannerText', '$isNewWin', '$status', '$picFile', '$sourceFile', '$picWidth', '$picHeight', 
							 '$htmlCode', '$onlyInPages', '$doCountViews', '$doCountClicks', '$maxViews', '$maxClicks', '$textForBanner', '$name', 
				 			 '$startDate', '$expireDate')";
		commonDoQuery ($queryStr);
		
		// handle category
		$categoryId = xmlParser_getValue($xmlRequest, "categoryId");

		if ($categoryId != "")
		{
			// get last pos
			$queryStr = "select max(pos) from categoriesItems where categoryId = $categoryId and type = 'banner'";
			$result		= commonDoQuery ($queryStr);
			$row		= commonQuery_fetchRow ($result);
			$pos 		= $row[0] + 1;

			$queryStr = "insert into categoriesItems (itemId, categoryId, type, pos)
						 values ($bannerId, $categoryId, 'banner', $pos)";
			commonDoQuery ($queryStr);
		}
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
		if ($fileLoaded)
		{
			$queryStr .= ", 			 picFile    = '$picFile',
										 sourceFile = '$sourceFile'";
		}
	
		if ($fileLoaded || $type == "movie" || $type == "htmlCode" || $type == "text")
		{
			$queryStr .= ", 			picWidth   =  '$picWidth',
									 	picHeight  =  '$picHeight'";
		}

		$queryStr .= " where id = $bannerId";
	
		commonDoQuery ($queryStr);
	}

	// handle file
	# ------------------------------------------------------------------------------------------------------
	$filePath = "$ibosHomeDir/html/SWFUpload/files/$userId/";

	if ($fileLoaded)
	{
		$domainRow	= commonGetDomainRow();
		$domainName = commonGetDomainName ($domainRow);

		$connId 	= commonFtpConnect($domainRow); 
		ftp_chdir ($connId, "bannerFiles");

		$upload = ftp_put($connId, $picFile, "$filePath/$sourceFile", FTP_BINARY);

		commonFtpDisconnect ($connId);
	}

 	// delete old files
	commonDeleteOldFiles ($filePath, 7200);	// 2 hour
}

/* ----------------------------------------------------------------------------------------------------	*/
/* doesBannerExist																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function doesBannerExist ($id)
{
	$queryStr	 = "select count(*) from banners where id=$id";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$count	     = $row[0];

	return ($count > 0);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getBannerDetails																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getBannerDetails ($xmlRequest)
{
	$id		= xmlParser_getValue($xmlRequest, "bannerId");

	if ($id == "")
		trigger_error ("חסר קוד באנר לביצוע הפעולה");

	$queryStr	= "select * from banners
				   where id=$id";
	$result		= commonDoQuery ($queryStr);

	if (commonQuery_numRows($result) == 0)
		trigger_error ("באנר עם קוד זה ($id) לא קיימת במערכת. לא ניתן לבצע את העדכון");

	$row = commonQuery_fetchRow($result);

	$xmlResponse = "";
	
	$status	   	  = $row['status'];
	$type	   	  = $row['type'];
	$isNewWin  	  = $row['isNewWin'];
	$picWidth  	  = $row['picWidth'];
	$picHeight 	  = $row['picHeight'];
	$bannerText	  = commonValidXml($row['bannerText']);
	$url	   	  = commonValidXml($row['url']);
	$name	   	  = commonValidXml($row['name']);
	$picFile   	  = commonValidXml($row['picFile']);
	$htmlCode     = commonValidXml($row['htmlCode']);
	$textForBanner= commonValidXml($row['textForBanner']);
	$sourceFile	  = commonCData(commonEncode($row['sourceFile']));
	$startDate	  = formatApplDate($row['startDate']);
	$expireDate	  = formatApplDate($row['expireDate']);
	
	$fullFileName = urlencode($row['picFile']);
	$fullFileName = commonValidXml($fullFileName);
			
	if ($row['type'] == "image")
	{
		$picWidth  = "";
		$picHeight = "";
	}

	$onlyInPages  = $row['onlyInPages'];
	$onlyInPages  = trim($onlyInPages, ",");
	$onlyInPages  = join(" ", explode(",", $onlyInPages));

	$xmlResponse .= "<bannerId>$id</bannerId>
					 <status>$status</status>
					 <type>$type</type>
					 <bannerText>$bannerText</bannerText>
					 <isNewWin>$isNewWin</isNewWin>
					 <url>$url</url>
					 <htmlCode>$htmlCode</htmlCode>
					 <textForBanner>$textForBanner</textForBanner>
					 <onlyInPages>$onlyInPages</onlyInPages>
					 <pages></pages>
					 <doCountViews>$row[doCountViews]</doCountViews>
					 <doCountClicks>$row[doCountClicks]</doCountClicks>
					 <maxViews>$row[maxViews]</maxViews>
					 <maxClicks>$row[maxClicks]</maxClicks>
					 <picWidth>$picWidth</picWidth>
					 <picHeight>$picHeight</picHeight>
					 <sourceFile>$sourceFile</sourceFile>
					 <formSourceFile>$sourceFile</formSourceFile>
					 <fullFileName>$fullFileName</fullFileName>
					 <name>$name</name>
					 <theStartDate>$startDate</theStartDate>
					 <expireDate>$expireDate</expireDate>";

	return $xmlResponse;
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteBanner																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteBanner ($xmlRequest)
{
	$id = xmlParser_getValue ($xmlRequest, "bannerId");

	if ($id == "")
		trigger_error ("חסר קוד באנר לביצוע הפעולה");

	$queryStr = "delete from banners where id = $id";
	commonDoQuery ($queryStr);

	$queryStr	= "select pos, categoryId from categoriesItems where itemId = $id and type = 'banner'";
	$result		= commonDoQuery($queryStr);

	$pos		= 0;
	$catId		= 0;
	if (commonQuery_numRows($result) != 0)
	{
		$row		= commonQuery_fetchRow($result);
		$pos		= $row['pos'];
		$catId		= $row['categoryId'];
	}

	$queryStr	= "delete from categoriesItems where itemId = $id and type = 'banner'";
	commonDoQuery ($queryStr);

	if ($catId != 0 && $pos != 0)
	{
		$queryStr	= "update categoriesItems set pos = pos - 1 where type = 'banner' and categoryId = $catId and pos > $pos";
		commonDoQuery($queryStr);
	}

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* resetBannerCounters																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function resetBannerCounters ($xmlRequest)
{
	$id = xmlParser_getValue ($xmlRequest, "bannerId");

	if ($id == "")
		trigger_error ("חסר קוד באנר לביצוע הפעולה");

	$queryStr = "update banners set countViews = 0, countClicks = 0 where id = $id";
	commonDoQuery ($queryStr);

	return "";
}

?>
