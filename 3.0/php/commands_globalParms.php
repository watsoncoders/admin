<?php

$allLangsCode = array("HEB",  "ENG", "FRN", "ESP", "ARB", "RUS", "SWE", "GER", "ROM", "HUN", "POR",  
					  "CHN", "THI", "IND", "BEL", "ITA", "BLG", "TUR", "KOR");
$allLangsText = array("עברית", "אנגלית", "צרפתית", "ספרדית", "ערבית", "רוסית", "שוודית", "גרמנית", "רומנית", "הונגרית", "פורטוגזית",
	   				  "סינית", "תאילנדית", "הודית", "בלגית", "איטלקית", "בולגרית", "תורכית", "קוריאנית");

/* ----------------------------------------------------------------------------------------------------	*/
/* getLangs																								*/
/* ----------------------------------------------------------------------------------------------------	*/
function getLangs ($xmlRequest)
{
	global $allLangsCode, $allLangsText;

	$queryStr    = "select langs from globalParms";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);

	$langs		 = $row['langs'];
	
	$langsArray = explode(",",$langs);

	$langsText = "";
	if ($langs == "")
		$langsText = "אין";
	else
	{
		for ($i=0; $i < count($langsArray); $i++)
		{
			$textIndex = array_search($langsArray[$i], $allLangsCode);

			if ($textIndex >= 0 || $langsArray[$i] == "HB2")
			{
				if ($langsText != "")
					$langsText .= ", ";

				if ($langsArray[$i] == "HB2")
					$langsText .= "עברית 2";
				else
					$langsText .= $allLangsText[$textIndex];
			}
		}
	}

	$langsText = commonPhpEncode($langsText);
	
	$xmlResponse	= "<clientLangs>
							<langs>$langs</langs>
							<langsText>$langsText</langsText>
							<newLang></newLang>
							<otherLangs>";
							

	for ($i=0; $i < count($allLangsCode); $i++)
	{
		if (!in_array($allLangsCode[$i], $langsArray))
		{
			$xmlResponse .= 	"<otherLang>
									<lang>" . $allLangsCode[$i] . "</lang>
									<name>" . commonPhpEncode($allLangsText[$i]) . "</name>
								</otherLang>";
		}
	}
	$xmlResponse .=		"</otherLangs>
					   </clientLangs>";

	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getSiteLangs																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function getSiteLangs ($xmlRequest)
{
	global $allLangsCode, $allLangsText;

	$queryStr    = "select langs from globalParms";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);

	$langs		 = $row['langs'];
	
	$langsArray  = explode(",",$langs);

	$xmlResponse = "<items>";
							

	for ($i=0; $i < count($langsArray); $i++)
	{
		if ($langsArray[$i] == "HB2") continue;

		$textIndex = array_search($langsArray[$i], $allLangsCode);

		$xmlResponse .= 	"<item>
								<lang>" . $langsArray[$i] . "</lang>
								<name>" . commonPhpEncode($allLangsText[$textIndex]) . "</name>
							 </item>";
	}

	$xmlResponse .=		"</items>";

	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getSiteName																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function getSiteName ($xmlRequest)
{
	$lang		= xmlParser_getValue($xmlRequest, "lang");

	$queryStr   = "select siteName from globalParms_byLang ";
	if ($lang != "") $queryStr .= "where language = '$lang'";

	$result	    = commonDoQuery ($queryStr);
	$row	    = commonQuery_fetchRow($result);

	$siteName	= commonValidXml($row['siteName']);

	return "<siteName>$siteName</siteName>";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getSiteNames																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function getSiteNames ($xmlRequest)
{
	$queryStr   = "select siteName, language from globalParms_byLang";
	$result	    = commonDoQuery ($queryStr);

	$xmlResponse = "";
	while ($row = commonQuery_fetchRow($result))
	{
		$siteName	= commonValidXml($row['siteName']);
		$lang		= $row['language'];

		if ($xmlResponse == "")
			$xmlResponse = "<siteName>$siteName</siteName>";

		$xmlResponse .= "<siteName$lang>$siteName</siteName$lang>";
	}

	return $xmlResponse;
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getGlobalParms																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getGlobalParms ($xmlRequest)
{
	global $usedLangs;

	$queryStr    = "select * from globalParms";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);

	$row['siteEmail']	= commonValidXml($row['siteEmail']);

	$xmlResponse = "<parms>
						<essayPicWidth>$row[essayPicWidth]</essayPicWidth>
						<essayPicHeight>$row[essayPicHeight]</essayPicHeight>
						<siteEmail>$row[siteEmail]</siteEmail>
						<notReadyPageId>$row[notReadyPageId]</notReadyPageId>
						<countViewsPolicy>$row[countViewsPolicy]</countViewsPolicy>
						<emailLayout>$row[emailLayout]</emailLayout>
						<hasMobileVersion>$row[hasMobileVersion]</hasMobileVersion>";

	# get global parms by language 
	# -------------------------------------------------------------------------------------------------
	$queryStr	 = "select * from globalParms_byLang";
	$result	     = commonDoQuery ($queryStr);

	$langsArray = explode(",",$usedLangs);

	while ($row = commonQuery_fetchRow($result))
	{
		$language = $row['language'];

		$langsArray = commonArrayRemove ($langsArray, $language);	

		$bottomText	 = commonValidXml($row['bottomText']);
		$siteName	 = commonValidXml($row['siteName']);
		$keywords	 = commonValidXml($row['keywords']);
		$description = commonValidXml($row['description']);

		$xmlResponse .= "<bottomText$language>$bottomText</bottomText$language>
						 <siteName$language>$siteName</siteName$language>
						 <keywords$language>$keywords</keywords$language>
						 <description$language>$description</description$language>";
	}

	// add missing languages
	// ------------------------------------------------------------------------------------------------
	for ($i=0; $i<count($langsArray); $i++)
	{
		$language	  = $langsArray[$i];

		$xmlResponse .= "<bottomText$language><![CDATA[]]></bottomText$language>
						 <siteName$language><![CDATA[]]></siteName$language>
						 <keywords$language><![CDATA[]]></keywords$language>
						 <description$language><![CDATA[]]></description$language>";
	}

	$xmlResponse .= "</parms>";

	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateGlobalParms																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateGlobalParms ($xmlRequest)
{
	global $usedLangs;	// language cookie

	$loadMain = 0;

	$update			= xmlParser_getValue($xmlRequest, "update");
	if ($update == "langs")
	{
		$langs			= xmlParser_getValue($xmlRequest, "langs");
		$newLang		= xmlParser_getValue($xmlRequest, "newLang");

		if ($langs == "" && $newLang != "")
			$loadMain = 1;
				
		if ($newLang != "")
		{
			if ($langs != "")
				$langs .= ",";

			$langs .= $newLang;
		}

		$queryStr = "update globalParms set langs = '$langs'";

		commonDoQuery ($queryStr);

		setcookie ("goToPage", "langs");

		$langsArray = explode(",", $langs);
		$firstLang  = $langsArray[0];

		if ($loadMain)
		{
			// home layout
			$queryStr	= "insert into layouts (id, active) values (1, 1)";
			commonDoQuery ($queryStr);

			$layout		= "<!DOCTYPE html>
<html lang=\"he\" dir=\"rtl\">
<head>
	@commonHead@
</head>
<body>
	<div class=\"container\" id=\"homeContainer\">
		@header@
		<div id=\"content\" divType=\"page\" page=\"id1\">
			<div class=\"pageTitle\"><h1>#title#</h1></div>
			<div class=\"pageText\">#pageHtml#</div>
		</div>
		@footer@
	</div>
</body>
</html>";

			$name		= commonPrepareToDB("תבנית דף הבית");
			$queryStr	= "insert into layouts_byLang (layoutId, language, name, text) values (1, '$newLang', '$name', '$layout')";
			commonDoQuery ($queryStr);

			// head switch
			$switch		= "<meta http-equiv=\"content-type\" content=\"text/html; charset=utf-8\" />
<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\" />
<title>#winTitle#</title>
<link href=\"css/layouts.css\" rel=\"stylesheet\" type=\"text/css\" />
<link href=\"common.css\" rel=\"stylesheet\" type=\"text/css\" />
@googleAnalytics@";

			$name		= commonPrepareToDB("מקטע head הכולל כותרת (title), קישור ל-css ו-javascripts");
			$queryStr	= "insert into layoutSwitches_byLang (id, language, type, name, description, text)
						   values (1, '$newLang', 'section', 'commonHead', '$name', '$switch')";
			commonDoQuery ($queryStr);

			// footer switch
			$switch		= "<footer>
	<div id=\"footer_in\">
		<div id=\"copyrights\"><div>#bottomText#</div></div>
		<div id=\"interuseLogo\">
			<div><a href=\"http://www.interuse.co.il/\"><img src=\"designFiles/InteruseAnimation.gif\" alt=\"אינטריוז\" /></a></div>
		</div>
		<div id=\"interuseText\"><a href=\"http://www.interuse.co.il/\">בניית אתרים</a></div>
	</div>
</footer>";
			$switch		= commonPrepareToDB($switch);

			$name		= commonPrepareToDB("שורה תחתונה: זכויות יוצרים ולוגו של אינטריוז");
			$queryStr	= "insert into layoutSwitches_byLang (id, language, type, name, description, text)
						   values (2, '$newLang', 'section', 'footer', '$name', '$switch')";
			commonDoQuery ($queryStr);

			// googleAnalytics switch
			$switch		= "";
			$name		= commonPrepareToDB("קוד של תוכנות סטטיסטיקה");
			$queryStr	= "insert into layoutSwitches_byLang (id, language, type, name, description, text)
						   values (3, '$newLang', 'section', 'googleAnalytics', '$name', '$switch')";
			commonDoQuery ($queryStr);

			// domain switch
			$switch		= "";
			$name		= commonPrepareToDB("כתובת הדומיין");
			$queryStr	= "insert into layoutSwitches_byLang (id, language, type, name, description, text)
						   values (4, '$newLang', 'section', 'domain', '$name', '$switch')";
			commonDoQuery ($queryStr);

			// header switch
			$switch		= "<div id=\"header\">
</div>";
			$name		= commonPrepareToDB("חלק עליון: לוגו, תפריט עליון...");
			$queryStr	= "insert into layoutSwitches_byLang (id, language, type, name, description, text)
						   values (5, '$newLang', 'section', 'header', '$name', '$switch')";
			commonDoQuery ($queryStr);
		}
		else
		{
			// copy layouts and layoutSwitches from firstLang to newLang
			$queryStr	= "insert into layouts_byLang 
							select layoutId, '$newLang', name, text from layouts_byLang where language = '$firstLang'";
			commonDoQuery ($queryStr);

			$queryStr	= "insert into layoutSwitches_byLang 
							select id, '$newLang', type, name, description, text from layoutSwitches_byLang where language = '$firstLang'";
			commonDoQuery ($queryStr);
		}
	}
	else
	{
		$siteEmail			= commonDecode(xmlParser_getValue($xmlRequest, "siteEmail"));
		$notReadyPageId 	= commonDecode(xmlParser_getValue($xmlRequest, "notReadyPageId"));
		$countViewsPolicy 	= commonDecode(xmlParser_getValue($xmlRequest, "countViewsPolicy"));
		$emailLayout	 	= commonDecode(xmlParser_getValue($xmlRequest, "emailLayout"));

		$queryStr = "update globalParms set siteEmail 		 = '$siteEmail', 
											notReadyPageId 	 = '$notReadyPageId', 
											countViewsPolicy = '$countViewsPolicy',
											emailLayout		 = '$emailLayout'";
		commonDoQuery ($queryStr);

		# delete all languages rows
		# --------------------------------------------------------------------------------------------------
		$queryStr = "delete from globalParms_byLang";
		commonDoQuery ($queryStr);

		# add languages rows for this page
		# --------------------------------------------------------------------------------------------------
		$langsArray = explode(",",$usedLangs);

		for ($i=0; $i<count($langsArray); $i++)
		{
			$language		= $langsArray[$i];
			$bottomText		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "bottomText$language")));
			$siteName		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "siteName$language")));
			$keywords		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "keywords$language")));
			$description	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "description$language")));

			$queryStr		= "insert into globalParms_byLang
		   				   (language, bottomText, siteName, keywords, description)
						   values ('$language', '$bottomText', '$siteName', '$keywords', '$description')";

			commonDoQuery ($queryStr);		
		}
	}


	return "<loadMain>$loadMain</loadMain>";
}

?>
