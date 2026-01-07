<?php

include "picsTools.php";

/* ----------------------------------------------------------------------------------------------------	*/
/* getEssays																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function getEssays ($xmlRequest)
{
	global $usedLangs;
	$langsArray = explode(",",$usedLangs);

	$domainRow   = commonGetDomainRow ();
	$siteUrl     = commonGetDomainName($domainRow) . "/index2.php";
	commonConnectToUserDB ($domainRow);

	$sortBy		= xmlParser_getValue($xmlRequest,"sortBy");
	if ($sortBy == "")
		$sortBy = "id";

	$sortDir	= xmlParser_getValue($xmlRequest,"sortDir");
	if ($sortDir == "")
		$sortDir = "desc";

	$condition  = commonAddIbosUserCondition();

	$essayId = addslashes(xmlParser_getValue($xmlRequest, "essayId"));
	if ($essayId != "")
	{
		$condition .= " and essays.id = '$essayId' ";
	}

	$addCategories = "";
	$category = xmlParser_getValue($xmlRequest, "category");
	if ($category != "")
	{
		$addCategories = " left join categoriesItems on essays.id = categoriesItems.itemId ";
		$condition .= "  and categoriesItems.categoryId = $category  and categoriesItems.type = 'essay' ";
	}

	$anyText = addslashes(commonDecode(xmlParser_getValue($xmlRequest, "anyText")));
	if ($anyText != "")
	{
			$condition .= " and (pages_byLang.title like '%$anyText%' 
							  or essays_byLang.subtitle like '%$anyText%' 
							  or essays_byLang.headline like '%$anyText%' 
							  or pages_byLang.rewriteName like '%$anyText%')";
	}

	$limit = xmlParser_getValue($xmlRequest, "limit");
	if ($limit == "feedbacks")
	{
		$condition .= " and essays.id in (select distinct itemId from feedbacks where type = 'essay') ";
		$limit = "";
	}

	// get total
	$queryStr	 = "select count(*) from (essays, pages)
					left join essays_byLang on essays.id = essays_byLang.essayId and essays_byLang.language = '$langsArray[0]'  
				    left join pages_byLang  on essays.id = pages_byLang.pageId   and pages_byLang.language  = '$langsArray[0]' 
					$addCategories
					where essays.id = pages.id and pages.id > 0 $condition";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$total	     = $row[0];

	$columns	= "";
	$joins		= "";

	$langsArray = explode(",",$usedLangs);
	for ($i=0; $i<count($langsArray); $i++)
	{
		$language	 = $langsArray[$i];

		$columns	.= ",pl$i.isReady as isReady$language, pl$i.rewriteName as rewriteName$language ";

		$joins		.= " left join pages_byLang pl$i on pages_byLang.pageId=pl$i.pageId and pl$i.language='$language'";
	}

	// get details
	$queryStr = "select essays.*, essays_byLang.*, pages.layoutId, countViews, pages_byLang.title, pages_byLang.winTitle,
		   			    layouts_byLang.name	as layoutName $columns 
				 from (essays, pages, layouts_byLang)
				 left join essays_byLang on essays.id = essays_byLang.essayId and essays_byLang.language = '$langsArray[0]' 
				 left join pages_byLang  on essays.id = pages_byLang.pageId   and pages_byLang.language  = '$langsArray[0]' 
				 $addCategories
				 $joins 
				 where essays.id = pages.id and pages.id > 0 
				 and   layouts_byLang.layoutId = pages.layoutId and layouts_byLang.language = '$langsArray[0]' 
				 $condition 
				 order by $sortBy $sortDir " . commonGetLimit ($xmlRequest);

	$result	     = commonDoQuery ($queryStr);

	$numRows    = commonQuery_numRows($result);

	$xmlResponse = "<items>";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		$id   	  = $row['id'];
		$author   = commonValidXml ($row['author'],true);
		$title 	  = commonValidXml ($row['title'],true);
		$subtitle = commonValidXml ($row['subtitle'],true);
		$headline = commonValidXml ($row['headline'],true);
		$date	  = formatApplDate ($row['date']);

		$countViews = $row['countViews'];

		if ($countViews == "0") $countViews = "";

		$layoutId 	= $row['layoutId'];

		// get layout name
//		$layoutName = commonGetLayoutName ($layoutId);
		$layoutName = commonValidXml($row['layoutName']);

		if ($row['title'] == "")
		{
			$title = commonValidXml($row['winTitle']);
		}

		$rewriteName = "";
		for ($l=0; $l<count($langsArray); $l++)
		{
			$language	 = $langsArray[$l];

			if ($row['rewriteName' . $language] != "")
			{
				if ($rewriteName != "")
					$rewriteName .= " | ";

				$rewriteName .= $row['rewriteName' . $language];
			}
		}

		$xmlResponse .=	"<item>
							<essayId>$id</essayId>
							<layoutName>$layoutName</layoutName>
							<countViews>$countViews</countViews>
							<author>$author</author>
							<title>$title</title>
							<subtitle>$subtitle</subtitle>
							<headline>$headline</headline>
							<date>$date</date>
							<siteUrl>$siteUrl</siteUrl>";

		$langImgs	= "";

		for ($l=0; $l<count($langsArray); $l++)
		{
			$language	 = $langsArray[$l];

			$xmlResponse .=	"<isReady$language>" . $row['isReady'.$language] . "</isReady$language>";

			if ($row['isReady'.$language] == "0")
				$langImgs	.= "<img src=../../designFiles/lang/empty.jpg>";
			else if ($row['isReady'.$language] == "-1")
				$langImgs	.= "<img src=../../designFiles/lang/${language}_archive.jpg>";
			else
				$langImgs	.= "<img src=../../designFiles/lang/${language}.jpg>";

			$langImgs .= "&nbsp;";
		}

		$langImgs = trim($langImgs, "&nbsp;");

		$xmlResponse .= "	<langImgs>" . commonValidXml($langImgs) . "</langImgs>
							<rewriteName>" . commonValidXml($rewriteName) . "</rewriteName>
						</item>";
	}

	$xmlResponse .=	"</items>"												.
					commonGetTotalXml($xmlRequest,$numRows,$total);
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getExtraDataNames																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function getExtraDataNames ($xmlRequest)
{
	return (commonGetExtraDataNames("essaysExtraData"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* doesEssayExist																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function doesEssayExist ($id)
{
	$queryStr	 = "select count(*) from essays where id=$id";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$count	     = $row[0];

	return ($count > 0);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getEssayDetails																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getEssayDetails ($xmlRequest)
{
	global $usedLangs;
	global $cookie_guiLang;

	$id		= xmlParser_getValue($xmlRequest, "essayId");

	if ($id == "")
		trigger_error ("חסר קוד כתבה לביצוע הפעולה");

	$queryStr	= "select pages.id, date, essays.*, essays_byLang.*, layoutId, navParentId, staticname, membersOnly, 
						  pages_byLang.language, pages_byLang.keywords, pages_byLang.description, pages_byLang.robots, title, navTitle, winTitle, 
						  isReady, updated, showOnSitemap, rewriteName 
				   from pages left join pages_byLang on pages.id=pages_byLang.pageId
				   			  left join essays 		  on pages.id  = essays.id
				   			  left join essays_byLang on pages.id  = essays_byLang.essayId
				   where pages.id=$id
				   and pages_byLang.language = essays_byLang.language";
	$result		= commonDoQuery ($queryStr);

	if (commonQuery_numRows($result) == 0)
		trigger_error ("כתבה עם קוד זה ($id) לא קיימת במערכת. לא ניתן לבצע את העדכון");

	$langsArray = explode(",",$usedLangs);

	// siteUrl
	$domainRow   = commonGetDomainRow ();
	$siteUrl     = commonGetDomainName($domainRow);

	$xmlResponse = "";
	
	while ($row = commonQuery_fetchRow($result))
	{
		$language = $row['language'];

		$langsArray = commonArrayRemove ($langsArray, $language);	

		if ($xmlResponse == "")
		{
			$id			   = $row['id'];
			$layoutId	   = $row['layoutId'];
			$navParentId   = $row['navParentId'];
			$membersOnly   = $row['membersOnly'];
			$staticname	   = commonValidXml($row['staticname']);
			$showOnSitemap = $row['showOnSitemap'];

			$date		   = "";
			$time		   = "";

			if ($row['date'] != "")
			{
				$datetime  = explode(" , ", formatApplDateTime ($row['date']));

				if (count($datetime) == 2)
				{
					$date	   = $datetime[0];
					$time	   = $datetime[1];
				}
			}

			$link1		   = commonValidXml($row['link1']);
			$link2		   = commonValidXml($row['link2']);
			$link3		   = commonValidXml($row['link3']);
			$link4		   = commonValidXml($row['link4']);
			$link5		   = commonValidXml($row['link5']);
			$linkType1	   = (($row['link1'] == "") ? "" : $row['linkType1']);
			$linkType2	   = (($row['link2'] == "") ? "" : $row['linkType2']);
			$linkType3	   = (($row['link3'] == "") ? "" : $row['linkType3']);
			$linkType4	   = (($row['link4'] == "") ? "" : $row['linkType4']);
			$linkType5	   = (($row['link5'] == "") ? "" : $row['linkType5']);

			$url1		   = "<![CDATA[]]>";
			$url2		   = "<![CDATA[]]>";
			$url3		   = "<![CDATA[]]>";
			$url4		   = "<![CDATA[]]>";
			$url5		   = "<![CDATA[]]>";
			$page1		   = "";
			$page2		   = "";
			$page3		   = "";
			$page4		   = "";
			$page5		   = "";

			if ($linkType1 == "url" || $linkType1 == "urlNoFollow" || $linkType1 == "onclick")
				$url1  = $link1;
			else
				$page1 = $link1;
		
			if ($linkType2 == "url" || $linkType2 == "urlNoFollow" || $linkType2 == "onclick")
				$url2  = $link2;
			else
				$page2 = $link2;
		
			if ($linkType3 == "url" || $linkType3 == "urlNoFollow" || $linkType3 == "onclick")
				$url3  = $link3;
			else
				$page3 = $link3;
		
			if ($linkType4 == "url" || $linkType4 == "urlNoFollow" || $linkType4 == "onclick")
				$url4  = $link4;
			else
				$page4 = $link4;
		
			if ($linkType5 == "url" || $linkType5 == "urlNoFollow" || $linkType5 == "onclick")
				$url5  = $link5;
			else
				$page5 = $link5;
		
			$picFile   	   = commonValidXml($row['picFile']);
			$sourceFile	   = commonValidXml(addslashes($row['sourceFile']));
			$picFile2      = commonValidXml($row['picFile2']);
			$sourceFile2   = commonValidXml(addslashes($row['sourceFile2']));
			$picFile3  	   = commonValidXml($row['picFile3']);
			$sourceFile3   = commonValidXml(addslashes($row['sourceFile3']));
			$picFile4  	   = commonValidXml($row['picFile4']);
			$sourceFile4   = commonValidXml(addslashes($row['sourceFile4']));
			$picFile5  	   = commonValidXml($row['picFile5']);
			$sourceFile5   = commonValidXml(addslashes($row['sourceFile5']));
	
			$fullFileName  = "$siteUrl/essayFiles/$row[picFile]";
			$fullFileName2 = "$siteUrl/essayFiles/$row[picFile2]";
			$fullFileName3 = "$siteUrl/essayFiles/$row[picFile3]";
			$fullFileName4 = "$siteUrl/essayFiles/$row[picFile4]";
			$fullFileName5 = "$siteUrl/essayFiles/$row[picFile5]";
	

			if ($cookie_guiLang == "ENG")
				$pressText     = commonPhpEncode("Click here");
			else
				$pressText     = commonPhpEncode("לחץ כאן");

			$show	 = "";
			$show2   = "";
			$show3   = "";
			$show4   = "";
			$show5   = "";

			$delete  = "";
			$delete2 = "";
			$delete3 = "";
			$delete4 = "";
			$delete5 = "";

			if ($row['picFile']  != "") 
			{
				$show   = $pressText;
				$delete = $pressText;
			}

			if ($row['picFile2'] != "") 
			{
				$show2 	 = $pressText;
				$delete2 = $pressText;
			}

			if ($row['picFile3'] != "") 
			{
				$show3 	 = $pressText;
				$delete3 = $pressText;
			}

			if ($row['picFile4'] != "") 
			{
				$show4 	 = $pressText;
				$delete4 = $pressText;
			}

			if ($row['picFile5'] != "") 
			{
				$show5   = $pressText;
				$delete5 = $pressText;
			}

			$xmlResponse .= "<essayId>$id</essayId>
							 <isSave>0</isSave>
							 <layoutId>$layoutId</layoutId>
							 <navParentId>$navParentId</navParentId>
							 <staticname>$staticname</staticname>
							 <membersOnly>$membersOnly</membersOnly>
							 <date>$date</date>
							 <time>$time</time>
							 <link1>$link1</link1>
							 <link2>$link2</link2>
							 <link3>$link3</link3>
							 <link4>$link4</link4>
							 <link5>$link5</link5>
							 <linkType1>$linkType1</linkType1>
							 <linkType2>$linkType2</linkType2>
							 <linkType3>$linkType3</linkType3>
							 <linkType4>$linkType4</linkType4>
							 <linkType5>$linkType5</linkType5>
							 <url1>$url1</url1>
							 <url2>$url2</url2>
							 <url3>$url3</url3>
							 <url4>$url4</url4>
							 <url5>$url5</url5>
							 <page1>$page1</page1>
							 <page2>$page2</page2>
							 <page3>$page3</page3>
							 <page4>$page4</page4>
							 <page5>$page5</page5>
							 <showOnSitemap>$showOnSitemap</showOnSitemap>
							 <usedLangs>$usedLangs</usedLangs>
							 <sourceFile>$sourceFile</sourceFile>
							 <formSourceFile>$sourceFile</formSourceFile>
							 <fullFileName>$fullFileName</fullFileName>
							 <show>$show</show>
							 <delete>$delete</delete>
							 <sourceFile2>$sourceFile2</sourceFile2>
							 <formSourceFile2>$sourceFile2</formSourceFile2>
							 <fullFileName2>$fullFileName2</fullFileName2>
							 <show2>$show2</show2>
							 <delete2>$delete2</delete2>
							 <sourceFile3>$sourceFile3</sourceFile3>
							 <formSourceFile3>$sourceFile3</formSourceFile3>
							 <fullFileName3>$fullFileName3</fullFileName3>
							 <show3>$show3</show3>
							 <delete3>$delete3</delete3>
							 <sourceFile4>$sourceFile4</sourceFile4>
							 <formSourceFile4>$sourceFile4</formSourceFile4>
							 <fullFileName4>$fullFileName4</fullFileName4>
							 <show4>$show4</show4>
							 <delete4>$delete4</delete4>
							 <sourceFile5>$sourceFile5</sourceFile5>
							 <formSourceFile5>$sourceFile5</formSourceFile5>
							 <fullFileName5>$fullFileName5</fullFileName5>
							 <show5>$show5</show5>
							 <delete5>$delete5</delete5>
							 <dimensionId></dimensionId>
							 <dimensionId2></dimensionId2>
							 <dimensionId3></dimensionId3>
							 <dimensionId4></dimensionId4>
							 <dimensionId5></dimensionId5>
							 <siteUrl>$siteUrl/index2.php</siteUrl>";
		}

		$title		= commonValidXml($row['title']);
		$isReady	= $row['isReady'];
		$keywords	= commonValidXml($row['keywords']);
		$description= commonValidXml($row['description']);
		$robots		= commonValidXml($row['robots']);
		$rewriteName= commonValidXml($row['rewriteName']);
		$subtitle	= commonValidXml($row['subtitle']);
		$headline	= commonValidXml($row['headline']);
		$navTitle	= commonValidXml($row['navTitle']);
		$winTitle	= commonValidXml($row['winTitle']);
		$txt		= commonValidXml($row['txt']);
		$author 	= commonValidXml($row['author']);
		$extraData1	= commonValidXml($row['extraData1']);
		$extraData2	= commonValidXml($row['extraData2']);
		$extraData3	= commonValidXml($row['extraData3']);
		$extraData4	= commonValidXml($row['extraData4']);
		$extraData5	= commonValidXml($row['extraData5']);
		$picTitle   = commonValidXml($row['picTitle']);
		$picTitle2  = commonValidXml($row['picTitle2']);
		$picTitle3  = commonValidXml($row['picTitle3']);
		$picTitle4  = commonValidXml($row['picTitle4']);
		$picTitle5  = commonValidXml($row['picTitle5']);
		$linkName1  = commonValidXml($row['linkName1']);
		$linkName2  = commonValidXml($row['linkName2']);
		$linkName3  = commonValidXml($row['linkName3']);
		$linkName4  = commonValidXml($row['linkName4']);
		$linkName5  = commonValidXml($row['linkName5']);
	
		$xmlResponse .=	   "<title$language>$title</title$language>
							<isReady$language>$isReady</isReady$language>
							<keywords$language>$keywords</keywords$language>
							<description$language>$description</description$language>
							<robots$language>$robots</robots$language>
							<subtitle$language>$subtitle</subtitle$language>
							<headline$language>$headline</headline$language>
							<txt$language>$txt</txt$language>
							<navTitle$language>$navTitle</navTitle$language>
							<winTitle$language>$winTitle</winTitle$language>
							<author$language>$author</author$language>
							<picTitle$language>$picTitle</picTitle$language>
							<picTitle2$language>$picTitle2</picTitle2$language>
							<picTitle3$language>$picTitle3</picTitle3$language>
							<picTitle4$language>$picTitle4</picTitle4$language>
							<picTitle5$language>$picTitle5</picTitle5$language>
							<linkName1$language>$linkName1</linkName1$language>
							<linkName2$language>$linkName2</linkName2$language>
							<linkName3$language>$linkName3</linkName3$language>
							<linkName4$language>$linkName4</linkName4$language>
							<linkName5$language>$linkName5</linkName5$language>
							<extraData1$language>$extraData1</extraData1$language>
							<extraData2$language>$extraData2</extraData2$language>
							<extraData3$language>$extraData3</extraData3$language>
							<extraData4$language>$extraData4</extraData4$language>
							<extraData5$language>$extraData5</extraData5$language>
							<rewriteName$language>$rewriteName</rewriteName$language>";
	}

	// add missing languages
	// ------------------------------------------------------------------------------------------------
	for ($i=0; $i<count($langsArray); $i++)
	{
		$language	  = $langsArray[$i];

		$xmlResponse .=	   "<title$language><![CDATA[]]></title$language>
							<isReady$language>0</isReady$language>
							<keywords$language><![CDATA[]]></keywords$language>
							<description$language><![CDATA[]]></description$language>
							<robots$language><![CDATA[]]></robots$language>
							<subtitle$language><![CDATA[]]></subtitle$language>
							<headline$language><![CDATA[]]></headline$language>
							<txt$language><![CDATA[]]></txt$language>
							<navTitle$language><![CDATA[]]></navTitle$language>
							<winTitle$language><![CDATA[]]></winTitle$language>
							<author$language><![CDATA[]]></author$language>
							<picTitle$language><![CDATA[]]></picTitle$language>
							<picTitle2$language><![CDATA[]]></picTitle2$language>
							<picTitle3$language><![CDATA[]]></picTitle3$language>
							<picTitle4$language><![CDATA[]]></picTitle4$language>
							<picTitle5$language><![CDATA[]]></picTitle5$language>
							<linkName1$language><![CDATA[]]></linkName1$language>
							<linkName2$language><![CDATA[]]></linkName2$language>
							<linkName3$language><![CDATA[]]></linkName3$language>
							<linkName4$language><![CDATA[]]></linkName4$language>
							<linkName5$language><![CDATA[]]></linkName5$language>
							<extraData1$language><![CDATA[]]></extraData1$language>
							<extraData2$language><![CDATA[]]></extraData2$language>
							<extraData3$language><![CDATA[]]></extraData3$language>
							<extraData4$language><![CDATA[]]></extraData4$language>
							<extraData5$language><![CDATA[]]></extraData5$language>
							<rewriteName$language><![CDATA[]]></rewriteName$language>";
	}

	return $xmlResponse;
}

/* ----------------------------------------------------------------------------------------------------	*/
/* addEssay																								*/
/* ----------------------------------------------------------------------------------------------------	*/
function addEssay ($xmlRequest)
{
	return (editEssay ($xmlRequest, "add"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateEssay																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateEssay ($xmlRequest)
{
	editEssay ($xmlRequest, "update");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* previewEssay																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function previewEssay ($xmlRequest)
{
	return (editEssay ($xmlRequest, "preview"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* editEssay																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function editEssay ($xmlRequest, $editType)
{
	global $userId;
	global $usedLangs;
	global $ibosHomeDir;

	$langsArray = explode(",",$usedLangs);

	$previewEssay 	= false;

	$essayId		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "essayId")));

	if ($editType == "preview")
	{
		$previewEssay = true;

		$essayId = $essayId * -1;

		if (doesEssayExist($essayId))
			$editType = "update";
		else
			$editType = "add";
	}

	$layoutId		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "layoutId")));
	$navParentId	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "navParentId")));
	$staticname		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "staticname")));
	$membersOnly	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "membersOnly")));
	$date			= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "date")));
	$time			= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "time")));
	$linkType1		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "linkType1")));
	$linkType2		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "linkType2")));
	$linkType3		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "linkType3")));
	$linkType4		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "linkType4")));
	$linkType5		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "linkType5")));
	$url1			= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "url1")));
	$url2			= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "url2")));
	$url3			= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "url3")));
	$url4			= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "url4")));
	$url5			= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "url5")));
	$page1			= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "page1")));
	$page2			= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "page2")));
	$page3			= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "page3")));
	$page4			= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "page4")));
	$page5			= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "page5")));
	$showOnSitemap	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "showOnSitemap")));
	$categoryId		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "categoryId")));
	$dimensionId	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "dimensionId")));
	$dimensionId2	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "dimensionId2")));
	$dimensionId3	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "dimensionId3")));
	$dimensionId4	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "dimensionId4")));
	$dimensionId5	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "dimensionId5")));

	if ($linkType1 == "url" || $linkType1 == "urlNoFollow" || $linkType1 == "onclick")
		$link1 = $url1;
	else
		$link1 = $page1;

	if ($linkType2 == "url" || $linkType2 == "urlNoFollow" || $linkType2 == "onclick")
		$link2 = $url2;
	else
		$link2 = $page2;

	if ($linkType3 == "url" || $linkType3 == "urlNoFollow" || $linkType3 == "onclick")
		$link3 = $url3;
	else
		$link3 = $page3;

	if ($linkType4 == "url" || $linkType4 == "urlNoFollow" || $linkType4 == "onclick")
		$link4 = $url4;
	else
		$link4 = $page4;

	if ($linkType5 == "url" || $linkType5 == "urlNoFollow" || $linkType5 == "onclick")
		$link5 = $url5;
	else
		$link5 = $page5;

	if ($editType == "add")
	{
		if (!$previewEssay)
		{
			$queryStr   = "select max(id) from pages";
			$result		= commonDoQuery ($queryStr);
			$row		= commonQuery_fetchRow ($result);
			$essayId 	= $row[0] + 1;
		}

		$ibosUserId = commonGetIbosUserId ();
	}

	if ($date != "")
	{
		if ($time == "")
			$time = "00:00";

		$date	= formatApplToDB ($date . " " . $time);
	}

	// handle essay files
	# ------------------------------------------------------------------------------------------------------
	# -- pic file 1
	$sourceFile 	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "sourceFile")));	
	$fileDeleted	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "fileDeleted")));	

	$fileDeleted	= ($fileDeleted == "1");
	$fileLoaded  	= false;

	$picFile 		= "";
	$suffix 		= "";

	if ($sourceFile != "")
	{
		$fileLoaded = true;
		$suffix		= commonFileSuffix($sourceFile);
		$picFile 	= "${essayId}_size0$suffix";

		list ($picWidth, $picHeight, $bgColor, $forceSize, $allowCrop) = commonGetDimensionDetails ($dimensionId);

	}

	if ($suffix == "." . $sourceFile)	// wrong file name - don't load it
	{
		$fileLoaded = false;
		$picFile    = "";
	}

	# -- pic file 2
	$sourceFile2 	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "sourceFile2")));	
	$fileDeleted2	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "fileDeleted2")));	

	$fileDeleted2	= ($fileDeleted2 == "1");
	$fileLoaded2  	= false;

	$picFile2 		= "";
	$suffix2 		= "";

	if ($sourceFile2 != "")
	{
		$fileLoaded2 = true;
		$suffix2	 = commonFileSuffix($sourceFile2);
		$picFile2 	 = "${essayId}_2_size0$suffix2";

		list ($picWidth2, $picHeight2, $bgColor2, $forceSize2, $allowCrop2) = commonGetDimensionDetails ($dimensionId2);
	}

	if ($suffix2 == "." . $sourceFile2)	// wrong file name - don't load it
	{
		$fileLoaded2 = false;
		$picFile2    = "";
	}

	# -- pic file 3
	$sourceFile3 	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "sourceFile3")));	
	$fileDeleted3	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "fileDeleted3")));	

	$fileDeleted3	= ($fileDeleted3 == "1");
	$fileLoaded3  	= false;

	$picFile3 		= "";
	$suffix3 		= "";

	if ($sourceFile3 != "")
	{
		$fileLoaded3 = true;
		$suffix3	 = commonFileSuffix($sourceFile3);
		$picFile3 	 = "${essayId}_3_size0$suffix3";

		list ($picWidth3, $picHeight3, $bgColor3, $forceSize3, $allowCrop3) = commonGetDimensionDetails ($dimensionId3);
	}

	if ($suffix3 == "." . $sourceFile3)	// wrong file name - don't load it
	{
		$fileLoaded3 = false;
		$picFile3    = "";
	}

	# -- pic file 4
	$sourceFile4 	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "sourceFile4")));	
	$fileDeleted4	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "fileDeleted4")));	

	$fileDeleted4	= ($fileDeleted4 == "1");
	$fileLoaded4  	= false;

	$picFile4 		= "";
	$suffix4 		= "";

	if ($sourceFile4 != "")
	{
		$fileLoaded4 = true;
		$suffix4	 = commonFileSuffix($sourceFile4);
		$picFile4 	 = "${essayId}_4_size0$suffix4";

		list ($picWidth4, $picHeight4, $bgColor4, $forceSize4, $allowCrop4) = commonGetDimensionDetails ($dimensionId4);
	}

	if ($suffix4 == "." . $sourceFile4)	// wrong file name - don't load it
	{
		$fileLoaded4 = false;
		$picFile4    = "";
	}

	# -- pic file 5
	$sourceFile5 	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "sourceFile5")));	
	$fileDeleted5	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "fileDeleted5")));	

	$fileDeleted5	= ($fileDeleted5 == "1");
	$fileLoaded5  	= false;

	$picFile5 		= "";
	$suffix5 		= "";

	if ($sourceFile5 != "")
	{
		$fileLoaded5 = true;
		$suffix5	 = commonFileSuffix($sourceFile5);
		$picFile5    = "${essayId}_5_size0$suffix5";

		list ($picWidth5, $picHeight5, $bgColor5, $forceSize5, $allowCrop5) = commonGetDimensionDetails ($dimensionId5);
	}

	if ($suffix5 == "." . $sourceFile5)	// wrong file name - don't load it
	{
		$fileLoaded5 = false;
		$picFile5    = "";
	}

	for ($i=0; $i<count($langsArray); $i++)
	{
		$language		= $langsArray[$i];
		$rewriteName	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "rewriteName$language")));

		if (!commonCheckRewriteName($rewriteName, $essayId))
			trigger_error (iconv("windows-1255", "utf-8", "כתובת סטטית זו כבר קיימת"));
	}

	if ($editType == "add")
	{
		$queryStr = "insert into pages (id, ibosUserId, type, layoutId, navParentId, staticname, membersOnly, showOnSitemap) 
					 values ('$essayId', '$ibosUserId', 'essay', '$layoutId', '$navParentId', '$staticname', '$membersOnly', '$showOnSitemap')";

		commonDoQuery ($queryStr);

		$queryStr = "insert into essays (id, date, link1, link2, link3, link4, link5, linkType1, linkType2, linkType3, linkType4, linkType5,
										 picFile, sourceFile, picFile2, sourceFile2, picFile3, sourceFile3,picFile4, sourceFile4, picFile5, sourceFile5) 
					 values	('$essayId', '$date', '$link1', '$link2', '$link3', '$link4', '$link5', 
							 '$linkType1', '$linkType2', '$linkType3', '$linkType4', '$linkType5'";

		$queryStr .= ($fileLoaded  ?  ", '$picFile' , '$sourceFile' " : ", '', ''");
		$queryStr .= ($fileLoaded2 ?  ", '$picFile2', '$sourceFile2'" : ", '', ''");
		$queryStr .= ($fileLoaded3 ?  ", '$picFile3', '$sourceFile3'" : ", '', ''");
		$queryStr .= ($fileLoaded4 ?  ", '$picFile4', '$sourceFile4'" : ", '', ''");
		$queryStr .= ($fileLoaded5 ?  ", '$picFile5', '$sourceFile5'" : ", '', ''");

		$queryStr	.= ")";
		commonDoQuery ($queryStr);

		if ($categoryId != "")
		{
			// get last pos
			$queryStr = "select max(pos) from categoriesItems where categoryId = $categoryId and type = 'essay'";
			$result		= commonDoQuery ($queryStr);
			$row		= commonQuery_fetchRow ($result);
			$pos 		= $row[0] + 1;

			$queryStr = "insert into categoriesItems (itemId, categoryId, type, pos) values ($essayId, $categoryId, 'essay', $pos)";
			commonDoQuery ($queryStr);
		}
	}
	else
	{
		$queryStr = "select picFile, picFile2, picFile3, picFile4, picFile5 from essays where id = $essayId";
		$result	  = commonDoQuery($queryStr);
		$row	  = commonQuery_fetchRow($result);

		$oldFile	= $row['picFile'];
		$oldFile2	= $row['picFile2'];
		$oldFile3	= $row['picFile3'];
		$oldFile4	= $row['picFile4'];
		$oldFile5	= $row['picFile5'];

		$queryStr = "update essays set date  	   = '$date',
									   link1 	   = '$link1',
									   link2 	   = '$link2',
									   link3 	   = '$link3',
									   link4 	   = '$link4',
									   link5 	   = '$link5',
									   linkType1   = '$linkType1', 
									   linkType2   = '$linkType2', 
									   linkType3   = '$linkType3', 
									   linkType4   = '$linkType4', 
									   linkType5   = '$linkType5'";
		if ($fileLoaded)
		{
			$queryStr .= ",	  		   picFile 	   = '$picFile',
							  		   sourceFile  = '$sourceFile' ";
		}
		else if ($fileDeleted)
		{
			$queryStr .= ",	  	 	   picFile     = '',
								 	   sourceFile  = '' ";
		}

		if ($fileLoaded2)
		{
			$queryStr .= ",	  		   picFile2    = '$picFile2',
							  		   sourceFile2 = '$sourceFile2' ";
		}
		else if ($fileDeleted2)
		{
			$queryStr .= ",	  	 	   picFile2    = '',
								 	   sourceFile2 = '' ";
		}

		if ($fileLoaded3)
		{
			$queryStr .= ",	  		   picFile3    = '$picFile3',
							  		   sourceFile3 = '$sourceFile3' ";
		}
		else if ($fileDeleted3)
		{
			$queryStr .= ",	  	 	   picFile3    = '',
								 	   sourceFile3 = '' ";
		}

		if ($fileLoaded4)
		{
			$queryStr .= ",	  		   picFile4    = '$picFile4',
							  		   sourceFile4 = '$sourceFile4' ";
		}
		else if ($fileDeleted4)
		{
			$queryStr .= ",	  	 	   picFile4    = '',
								 	   sourceFile4 = '' ";
		}

		if ($fileLoaded5)
		{
			$queryStr .= ",	  		   picFile5    = '$picFile5',
							  		   sourceFile5 = '$sourceFile5' ";
		}
		else if ($fileDeleted5)
		{
			$queryStr .= ",	  	 	   picFile5    = '',
								 	   sourceFile5 = '' ";
		}

		$queryStr .= "where id = $essayId";
		commonDoQuery ($queryStr);

		$queryStr = "update pages set layoutId 		= '$layoutId',
									  navParentId 	= '$navParentId',
									  staticname 	= '$staticname',
									  membersOnly 	= '$membersOnly',
									  showOnSitemap = '$showOnSitemap'
					 where id=$essayId";

		commonDoQuery ($queryStr);
	}

	# add languages rows for this essay
	# ------------------------------------------------------------------------------------------------------
	for ($i=0; $i<count($langsArray); $i++)
	{
		$language		= $langsArray[$i];

		$title			= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "title$language")));
		$navTitle		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "navTitle$language")));
		$winTitle		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "winTitle$language")));

		$isReady		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "isReady$language")));

		if ($previewEssay)
			$isReady = 0;

		$keywords		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "keywords$language")));
		$description	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "description$language")));
		$robots			= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "robots$language")));
		$updated		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "updated$language")));
		$headline		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "headline$language")));
		$subtitle		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "subtitle$language")));
		$txt			= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "txt$language")));
		$author			= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "author$language")));
		$picTitle		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "picTitle$language")));
		$picTitle2		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "picTitle2$language")));
		$picTitle3		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "picTitle3$language")));
		$picTitle4		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "picTitle4$language")));
		$picTitle5		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "picTitle5$language")));
		$linkName1		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "linkName1$language")));
		$linkName2		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "linkName2$language")));
		$linkName3		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "linkName3$language")));
		$linkName4		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "linkName4$language")));
		$linkName5		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "linkName5$language")));
		$extraData1		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "extraData1$language")));
		$extraData2		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "extraData2$language")));
		$extraData3		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "extraData3$language")));
		$extraData4		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "extraData4$language")));
		$extraData5		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "extraData5$language")));
		$rewriteName	= commonFixRewriteName(addslashes(commonDecode(xmlParser_getValue($xmlRequest, "rewriteName$language"))));

		if ($editType == "add")
		{
			$queryStr	= "insert into pages_byLang (pageId, language, title, navTitle, winTitle, isReady, keywords, description, robots, 
													 rewriteName, updated)
						   values ('$essayId','$language', '$title', '$navTitle', '$winTitle', '$isReady', '$keywords', '$description', '$robots', 
								   '$rewriteName',";

			if ($updated == "" || $updated == "0000-00-00 00:00:00" || $previewEssay)
			{
				$queryStr  .= "now())";
			}
			else
			{
				$queryStr  .= "'$updated')";
			}

			commonDoQuery ($queryStr);

			$queryStr		= "insert into essays_byLang (essayId, language, headline, subtitle, txt, author, 
														  picTitle, picTitle2, picTitle3, picTitle4, picTitle5, 
														  linkName1, linkName2, linkName3, linkName4, linkName5, 
														  extraData1, extraData2, extraData3, extraData4, extraData5)
							   values ('$essayId','$language', '$headline', '$subtitle','$txt','$author', 
									   '$picTitle', '$picTitle2', '$picTitle3', '$picTitle4', '$picTitle5', 
									   '$linkName1', '$linkName2', '$linkName3', '$linkName4', '$linkName5', 
									   '$extraData1', '$extraData2','$extraData3', '$extraData4','$extraData5')";
			commonDoQuery ($queryStr);
		}
		else
		{
			$queryStr	= "replace pages_byLang set title			= '$title', 
													navTitle		= '$navTitle', 
													winTitle		= '$winTitle', 
													isReady			= '$isReady', 
													keywords		= '$keywords', 
													description		= '$description', 
													robots			= '$robots',
													rewriteName		= '$rewriteName', 
													updated			= ";
	
			if ($updated == "" || $updated == "0000-00-00 00:00:00" || $previewEssay)
			{
				$queryStr  .= "now()";
			}
			else
			{
				$queryStr  .= "'$updated'";
			}

			$queryStr .= ",pageId = $essayId, language = '$language'";
			commonDoQuery ($queryStr);
	
			$queryStr	= "replace essays_byLang set subtitle	= '$subtitle', 
													 headline	= '$headline', 
													 txt			= '$txt', 
													 author		= '$author', 
													 picTitle	= '$picTitle', 
													 picTitle2	= '$picTitle2', 
													 picTitle3	= '$picTitle3', 
													 picTitle4	= '$picTitle4', 
													 picTitle5	= '$picTitle5', 
													 linkName1	= '$linkName1',
													 linkName2	= '$linkName2',
													 linkName3	= '$linkName3',
													 linkName4	= '$linkName4',
													 linkName5	= '$linkName5',
													 extraData1	= '$extraData1', 
													 extraData2	= '$extraData2', 
													 extraData3	= '$extraData3', 
													 extraData4	= '$extraData4', 
													 extraData5	= '$extraData5',
													 essayId = $essayId,
													 language = '$language'";
			commonDoQuery ($queryStr);
		}
	}

	// handle RSS file update
	$domainRow	= commonGetDomainRow();
	$domainName = commonGetDomainName ($domainRow);

/*
	for ($i=0; $i<count($langsArray); $i++)
	{
		$language	= $langsArray[$i];
		$file 		= fopen ("$domainName/essaysRSS.php?lang=$language","r");
		fclose ($file);
	}
 */
	// handle files
	$filePath = "$ibosHomeDir/html/SWFUpload/files/$userId/";

	$connId 	= commonFtpConnect($domainRow); 
	ftp_chdir ($connId, "essayFiles");

	// pic file 1
	if ($fileLoaded)
	{
		commonFtpDelete ($connId, $picFile);

		if ($domainRow['id'] == 310)	// games.co.il patch
			$picFile = str_replace("size0","size1",$picFile);

		$upload = ftp_put($connId, $picFile, "$filePath/$sourceFile", FTP_BINARY);

		// size 1
		if ($domainRow['id'] == 310)	// games.co.il patch
			$resizedFileName = str_replace("size1","size0",$picFile);
		else
			$resizedFileName = str_replace("size0","size1",$picFile);

		$destParts = explode(".",$resizedFileName);
		$destParts[count($destParts)-1] = "jpg";
		$resizedFileName = join(".", $destParts);

		if ($picWidth == 0 && $picHeight == 0)
		{
			$upload = ftp_put($connId, $resizedFileName, "$filePath/$sourceFile", FTP_BINARY);
		}
		else
		{
			if ($forceSize == "1")
				picsToolsForceResize("$filePath/$sourceFile", $suffix, $picWidth, $picHeight, "/../../tmp/$resizedFileName", $bgColor);
			else
				picsToolsResize("$filePath/$sourceFile", $suffix, $picWidth, $picHeight, "/../../tmp/$resizedFileName", $bgColor, 98, $allowCrop);
		
			$upload = ftp_put($connId, $resizedFileName, "/../../tmp/$resizedFileName", FTP_BINARY);
		}
	}
	else if ($fileDeleted)
	{
		commonFtpDelete ($connId, $oldFile);

		$oldFile 	= str_replace("size0", "size1", $oldFile);
		$destParts 	= explode(".",$oldFile);
		$destParts[count($destParts)-1] = "jpg";
		$oldFile 	= join(".", $destParts);

		commonFtpDelete ($connId, $oldFile);
	}

	// pic file 2
	if ($fileLoaded2)
	{
		commonFtpDelete ($connId, $picFile2);

		$upload = ftp_put($connId, $picFile2, "$filePath/$sourceFile2", FTP_BINARY);

		// size 1
		$resizedFileName = str_replace("size0","size1",$picFile2);
		$destParts = explode(".",$resizedFileName);
		$destParts[count($destParts)-1] = "jpg";
		$resizedFileName = join(".", $destParts);

		if ($picWidth2 == 0 && $picHeight2 == 0)
		{
			$upload = ftp_put($connId, $resizedFileName, "$filePath/$sourceFile2", FTP_BINARY);
		}
		else
		{
			picsToolsResize("$filePath/$sourceFile2", $suffix2, $picWidth2, $picHeight2, "/../../tmp/$resizedFileName", $bgColor2, 98, $allowCrop2);
		
			$upload = ftp_put($connId, $resizedFileName, "/../../tmp/$resizedFileName", FTP_BINARY);
		}
	}
	else if ($fileDeleted2)
	{
		commonFtpDelete ($connId, $oldFile2);
	}

	// pic file 3
	if ($fileLoaded3)
	{
		commonFtpDelete ($connId, $picFile3);

		$upload = ftp_put($connId, $picFile3, "$filePath/$sourceFile3", FTP_BINARY);

		// size 1
		$resizedFileName = str_replace("size0","size1",$picFile3);
		$destParts = explode(".",$resizedFileName);
		$destParts[count($destParts)-1] = "jpg";
		$resizedFileName = join(".", $destParts);

		if ($picWidth3 == 0 && $picHeight3 == 0)
		{
			$upload = ftp_put($connId, $resizedFileName, "$filePath/$sourceFile3", FTP_BINARY);
		}
		else
		{
			picsToolsResize("$filePath/$sourceFile3", $suffix3, $picWidth3, $picHeight3, "/../../tmp/$resizedFileName", $bgColor3, 98, $allowCrop3);
		
			$upload = ftp_put($connId, $resizedFileName, "/../../tmp/$resizedFileName", FTP_BINARY);
		}
	}
	else if ($fileDeleted3)
	{
		commonFtpDelete ($connId, $oldFile3);
	}

	// pic file 4
	if ($fileLoaded4)
	{
		commonFtpDelete ($connId, $picFile4);

		$upload = ftp_put($connId, $picFile4, "$filePath/$sourceFile4", FTP_BINARY);

		// size 1
		$resizedFileName = str_replace("size0","size1",$picFile4);
		$destParts = explode(".",$resizedFileName);
		$destParts[count($destParts)-1] = "jpg";
		$resizedFileName = join(".", $destParts);

		if ($picWidth4 == 0 && $picHeight4 == 0)
		{
			$upload = ftp_put($connId, $resizedFileName, "$filePath/$sourceFile4", FTP_BINARY);
		}
		else
		{
			picsToolsResize("$filePath/$sourceFile4", $suffix4, $picWidth4, $picHeight4, "/../../tmp/$resizedFileName", $bgColor4, 98, $allowCrop4);
		
			$upload = ftp_put($connId, $resizedFileName, "/../../tmp/$resizedFileName", FTP_BINARY);
		}
	}
	else if ($fileDeleted4)
	{
		commonFtpDelete ($connId, $oldFile4);
	}

	// pic file 5
	if ($fileLoaded5)
	{
		commonFtpDelete ($connId, $picFile5);

		$upload = ftp_put($connId, $picFile5, "$filePath/$sourceFile5", FTP_BINARY);

		// size 1
		$resizedFileName = str_replace("size0","size1",$picFile5);
		$destParts = explode(".",$resizedFileName);
		$destParts[count($destParts)-1] = "jpg";
		$resizedFileName = join(".", $destParts);

		if ($picWidth5 == 0 && $picHeight5 == 0)
		{
			$upload = ftp_put($connId, $resizedFileName, "$filePath/$sourceFile5", FTP_BINARY);
		}
		else
		{
			picsToolsResize("$filePath/$sourceFile5", $suffix5, $picWidth5, $picHeight5, "/../../tmp/$resizedFileName", $bgColor5, 98, $allowCrop5);
		
			$upload = ftp_put($connId, $resizedFileName, "/../../tmp/$resizedFileName", FTP_BINARY);
		}
	}
	else if ($fileDeleted5)
	{
		commonFtpDelete ($connId, $oldFile5);
	}

	commonFtpDisconnect ($connId);

	if (!$previewEssay)
	{
		fopen("$domainName/updateModRewrite.php","r");
	}

 	// delete old files
	commonDeleteOldFiles ($filePath, 7200);	// 2 hour

	$siteUrl     = commonGetDomainName($domainRow) . "/index2.php";

	return "<siteUrl>$siteUrl</siteUrl><essayId>$essayId</essayId>";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteEssay																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteEssay ($xmlRequest)
{
	global $usedLangs;

	$ids = xmlParser_getValues ($xmlRequest, "essayId");

	if (count($ids) == 0)
		trigger_error ("חסר קוד כתבה לביצוע הפעולה");

	foreach ($ids as $id)
	{
		$queryStr = "delete from pages where id = $id";
		commonDoQuery ($queryStr);

		$queryStr = "delete from pages_byLang where pageId = $id";
		commonDoQuery ($queryStr);

		$queryStr = "delete from categoriesItems where itemId = $id and type = 'essay'";
		commonDoQuery ($queryStr);

		$queryStr = "delete from essays where id = $id";
		commonDoQuery ($queryStr);

		$queryStr = "delete from essays_byLang where essayId = $id";
		commonDoQuery ($queryStr);
	}

	/*
	# recreate RSS updated in website 
	# --------------------------------------------------------------------------------------------------
	$domainRow  = commonGetDomainRow ();
	$domainName = commonGetDomainName ($domainRow);
	
	$langsArray = explode(",",$usedLangs);
	for ($i=0; $i<count($langsArray); $i++)
	{
		$language		= $langsArray[$i];

		$file = fopen ("$domainName/essaysRSS.php?lang=$language","r");
		fclose ($file);
	}
	 */
	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* resetCounters																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function resetCounters ($xmlRequest)
{
	$queryStr = "update pages set countViews = 0 where type = 'essay'";
	commonDoQuery ($queryStr);

	return "";
}

?>
