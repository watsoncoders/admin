<?php

/* ----------------------------------------------------------------------------------------------------	*/
/* getPages																								*/
/* ----------------------------------------------------------------------------------------------------	*/
function getPages ($xmlRequest)
{
	global $usedLangs;
	global $domainId;

	$domainRow   = commonGetDomainRow ();
	$siteUrl     = commonGetDomainName($domainRow) . "/index2.php";
	commonConnectToUserDB ($domainRow);

	$sortBy		= xmlParser_getValue($xmlRequest,"sortBy");

	if ($sortBy == "" || $sortBy == "pageId")
		$sortBy = "id";

	if ($sortBy == "title")
		$sortBy = "pl0.title";

	if ($sortBy == "layoutName")
		$sortBy = "layoutId";

	$sortDir	= xmlParser_getValue($xmlRequest,"sortDir");
	if ($sortDir == "")
		$sortDir = "desc";

	$condition  = commonAddIbosUserCondition();

	$pageId = xmlParser_getValue($xmlRequest, "pageId");
	if ($pageId != "")
	{
		$condition .= " and id = '$pageId' ";
	}
	
	$layoutId = xmlParser_getValue($xmlRequest, "layoutId");
	if ($layoutId != "")
	{
		$condition .= " and layoutId = $layoutId ";
	}
	
	$anyText = addslashes(commonDecode(xmlParser_getValue($xmlRequest, "anyText")));

	$addCategories = "";
	$category = xmlParser_getValue($xmlRequest, "category");
	if ($category != "")
	{
		$addCategories = " left join categoriesItems on pages.id = categoriesItems.itemId ";
		$condition .= "  and categoriesItems.categoryId = $category  and categoriesItems.type = 'page' ";
	}

	$textCond = "";

	$columns	= "id, countViews, layoutId, staticname";
	$joins		= "";
	$joins2		= "";

	$langsArray = explode(",",$usedLangs);

	
	for ($i=0; $i<count($langsArray); $i++)
	{
		$language	 = $langsArray[$i];

		$columns	.= ",pl$i.isReady as isReady$language, pl$i.winTitle as winTitle$language, 
						 pl$i.title as title$language, pl$i.rewriteName as rewriteName$language ";

		$joins		.= " left join pages_byLang pl$i on id=pl$i.pageId and pl$i.language='$language'";

		if ($anyText != "")
		{
			$joins2		.= " left join htmlPages_byLang hpl$i on id=hpl$i.pageId and hpl$i.language='$language'";
			if ($textCond == "")
			{
				$textCond = " and (";
			}
			else
			{
				$textCond .= " or ";
			}
			$textCond .= " hpl$i.txt like '%$anyText%' or pl$i.winTitle like '%$anyText%' 
						or pl$i.title like '%$anyText%' or pl$i.rewriteName like '%$anyText%'";
		}
	}

	if ($textCond != "")
	{
		$condition .= $textCond . ")";
	}

	// just fill in the Layout Names cache, so we won't need to do commonDoQuery's in the loop ahead
	commonGetLayoutName (1);

	// get total
	$queryStr	 = "select count(*) from pages $joins $joins2 $addCategories where pages.type = 'html' and pages.id > 0 $condition";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$total	     = $row[0];

	// get details
	$queryStr = "select $columns from pages $joins $joins2 $addCategories
				 where pages.type = 'html' and pages.id > 0 $condition order by $sortBy $sortDir " . commonGetLimit ($xmlRequest);

	if ($domainId == 316)	// patch for jobPortal site
		$result	     = commonDoQuery ($queryStr);
	else
		$result	     = commonDoUnbufferedQuery ($queryStr);

	$xmlResponse = "<items>";

	for ($i = 0; $row = commonQuery_fetchRow($result); $i++)
	{
		$id   	  	= $row['id'];
		$countViews = $row['countViews'];

		$staticname = commonValidXml($row['staticname']);
		$layoutId 	= $row['layoutId'];

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

		$winTitle = "";
		for ($l=0; $l<count($langsArray); $l++)
		{
			$language	 = $langsArray[$l];

			if ($row['winTitle' . $language] != "")
			{
				$winTitle   = commonValidXml($row['winTitle' . $language]);
				break;
			}
		}

		$title = "";
		for ($l=0; $l<count($langsArray); $l++)
		{
			$language	 = $langsArray[$l];

			if ($row['title' . $language] != "")
			{
				$title   = commonValidXml($row['title' . $language]);
				break;
			}
		}

		if ($title == "")
			$title = $winTitle;

		// get layout name
		$layoutName = commonGetLayoutName ($layoutId);

		$xmlResponse .=	"<item>
							<pageId>$id</pageId>
							<layoutName>$layoutName</layoutName>
							<countViews>$countViews</countViews>
							<name>$staticname</name>
							<title>$title</title>
							<winTitle>$winTitle</winTitle>
							<siteUrl>$siteUrl</siteUrl>";

		$langImgs	= "";

		for ($l=0; $l<count($langsArray); $l++)
		{
			$language	 = $langsArray[$l];

			$xmlResponse .=	"<isReady$language>" . $row['isReady'.$language] . "</isReady$language>";

			if ($row['isReady'.$language] == "1")
				$langImgs	.= "<img src=../../designFiles/lang/${language}.jpg>";
			else
				$langImgs	.= "<img src=../../designFiles/lang/empty.jpg>";

			$langImgs .= "&nbsp;";
		}

		$langImgs = trim($langImgs, "&nbsp;");

		$xmlResponse .= "	<langImgs>" . commonValidXml($langImgs) . "</langImgs>
							<rewriteName>" . commonValidXml($rewriteName) . "</rewriteName>
						</item>";
	}

	$xmlResponse .=	"</items>"												.
					commonGetTotalXml($xmlRequest,$i,$total);
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getPagesNames																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getPagesNames ($xmlRequest)
{
	$domainRow  = commonGetDomainRow ();

	commonConnectToUserDB($domainRow);

	global $usedLangs;

	$addType = "pages.type = 'html' or pages.type = 'essay' or pages.type = 'specific'";

	if ($domainRow['id'] == 135)	// leida
	{
		$addType .= " or pages.type = 'forum' ";
	}
	else
	{
		$pageType	= xmlParser_getValue($xmlRequest, "pageType");

		if ($pageType != "" || $domainRow['id'] == 523)
			$addType = "pages.type = 'html'";
	}

	$sortBy = "id";

	$sortDir = "desc";

	$langsArray = explode(",",$usedLangs);
	$language	= $langsArray[0];

	// get total
	$queryStr	 = "select count(*) from pages where ($addType) and pages.id > 0";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$total	     = $row[0];

	// set limit by browser
	$limit		 = 5000;

	if (strpos($_SERVER['HTTP_USER_AGENT'], 'Trident/7.0; rv:11.0') !== false)
		$limit = 3000;

	// get details
	$queryStr = "select id, winTitle, title from pages 
				 left join pages_byLang on id=pageId and pages_byLang.language='$language'
				 where ($addType) and pages.id > 0 order by $sortBy $sortDir 
				 limit $limit";
//				 commonGetLimit ($xmlRequest);

	$result	     = commonDoUnbufferedQuery ($queryStr);

	$xmlResponse = "<items>";

	for ($i = 0; $row = commonQuery_fetchRow($result); $i++)
	{
		$id   	  	= $row['id'];
		$winTitle   = commonValidXml($row['winTitle']);
		$title   = commonValidXml($row['title']);
		if ($title == "")
			$title = $winTitle;

		$xmlResponse .=	"<item>
							<pageId>$id</pageId>
							<title>$title</title>
							<winTitle>$winTitle</winTitle>
						</item>";
	}

	$xmlResponse .=	"</items>"												.
					commonGetTotalXml($xmlRequest,$i,$total);
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getAllPages																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function getAllPages ($xmlRequest)
{
	global $usedLangs;
	$langsArray = explode(",",$usedLangs);

	$condition  = "";

	$queryStr = "select id, staticname, navTitle, winTitle, title
				 from pages left join pages_byLang on pages.id = pages_byLang.pageId and pages_byLang.language = '$langsArray[0]'
				 where type != 'specific' and pages.id > 0
				 order by id";

	$result	     = commonDoQuery ($queryStr);

	$numRows    = commonQuery_numRows($result);

	$xmlResponse = "<items>";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		$id   	  	= $row['id'];
		$staticname = commonValidXml($row['staticname']);
		$winTitle	= commonValidXml($row['winTitle']);
		$title		= commonValidXml($row['title']);

		if ($row['navTitle'] != "")
			$title = commonValidXml($row['navTitle']);

		$xmlResponse .=	"<item>
							<pageId>$id</pageId>
							<staticname>$staticname</staticname>
							<name>$title</name>
							<winTitle>$winTitle</winTitle>
						 </item>";
	}

	$xmlResponse .=	"</items>";
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getRewriteNames																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getRewriteNames ($xmlRequest)
{
	global $usedLangs;
	$langsArray = explode(",",$usedLangs);

	$condition  = "";

	$queryStr = "select id, rewriteName
				 from pages, pages_byLang
		   	     where pages.id = pages_byLang.pageId and pages_byLang.language = '$langsArray[0]'
				 and rewriteName != ''
				 and pages.id > 0
				 order by binary rewriteName";

	$result	     = commonDoQuery ($queryStr);

	$numRows    = commonQuery_numRows($result);

	$xmlResponse = "<items>";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		$id   	  	 = $row['id'];
		$rewriteName = commonValidXml($row['rewriteName']);

		$xmlResponse .=	"<item>
							<pageId>$id</pageId>
							<rewriteName>$rewriteName</rewriteName>
						 </item>";
	}

	$xmlResponse .=	"</items>";
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getMembersPages																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getMembersPages ($xmlRequest)
{
	global $usedLangs;
	$langsArray = explode(",",$usedLangs);

	$condition  = "";

	$queryStr = "select id, staticname, navTitle, winTitle, title
				 from pages left join pages_byLang on pages.id = pages_byLang.pageId and pages_byLang.language = '$langsArray[0]'
				 where type = 'member'
				 and  pages.id > 0
				 order by id";

	$result	     = commonDoQuery ($queryStr);

	$numRows    = commonQuery_numRows($result);

	$xmlResponse = "<items>";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		$id   	  	= $row['id'];
		$staticname = commonValidXml($row['staticname']);
		$winTitle	= commonValidXml($row['winTitle']);
		$title		= commonValidXml($row['title']);

		if ($row['navTitle'] != "")
			$title = commonValidXml($row['navTitle']);

		$xmlResponse .=	"<item>
							<pageId>$id</pageId>
							<staticname>$staticname</staticname>
							<name>$title</name>
							<winTitle>$winTitle</winTitle>
						 </item>";
	}

	$xmlResponse .=	"</items>";
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getPageNextId																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getPageNextId ($xmlRequest)
{
	$queryStr	= "select max(id) from pages";
	$result		= commonDoQuery ($queryStr);
	$row		= commonQuery_fetchRow ($result);
	$id 		= $row[0] + 1;
	
	return "<pageId>$id</pageId>";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* addPage																								*/
/* ----------------------------------------------------------------------------------------------------	*/
function addPage ($xmlRequest)
{
	return (editPage ($xmlRequest, "add"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* doesPageExist																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function doesPageExist ($id)
{
	$queryStr		= "select count(*) from pages where id=$id";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$count	     = $row[0];

	return ($count > 0);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getPageDetails																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getPageDetails ($xmlRequest)
{
	global $usedLangs;

	$id		= xmlParser_getValue($xmlRequest, "pageId");
	$action	= xmlParser_getValue($xmlRequest, "action");

	if ($id == "")
		trigger_error ("חסר קוד דף לביצוע הפעולה");

	$queryStr	= "select pages.id, layoutId, navParentId, urlExtraParams, withPopup, isHtml, staticname, membersOnly,
						  pages_byLang.language, pages_byLang.keywords, pages_byLang.description, pages_byLang.robots, 
						  title, navTitle, txt, winTitle, isReady, updated, musicBgFile, showOnSitemap, rewriteName 
				   from pages, htmlPages, htmlPages_byLang
				   left join pages_byLang on htmlPages_byLang.pageId=pages_byLang.pageId and htmlPages_byLang.language=pages_byLang.language
				   where pages.id=$id
				   and pages.id = htmlPages_byLang.pageId
   			  	   and pages.id = htmlPages.id";
	$result		= commonDoQuery ($queryStr);

	if (commonQuery_numRows($result) == 0)
		trigger_error (iconv("windows-1255", "utf-8", "דף עם קוד זה ($id) לא קיים במערכת. לא ניתן לבצע את העדכון"));

	$langsArray = explode(",",$usedLangs);

	$xmlResponse = "";
	
	while ($row = commonQuery_fetchRow($result))
	{
		$language = $row['language'];

		$langsArray = commonArrayRemove ($langsArray, $language);	

		if ($xmlResponse == "")
		{
			if ($action == "duplicate")
			{
				$queryStr	= "select max(id) from pages";
				$result2	= commonDoQuery ($queryStr);
				$row2		= commonQuery_fetchRow ($result2);
				$id 		= $row2[0] + 1;

				$staticname = "";

				$siteUrl	= "";
			}
			else
			{
				$staticname	   = commonValidXml($row['staticname']);
				
				// siteUrl
				$domainRow   = commonGetDomainRow ();
				$siteUrl     = commonGetDomainName($domainRow) . "/index2.php";

			}

			$layoutId	   = $row['layoutId'];
			$navParentId   = $row['navParentId'];
			$urlExtraParams= commonValidXml($row['urlExtraParams']);
			$withPopup	   = $row['withPopup'];
			$isHtml		   = $row['isHtml'];
			$membersOnly   = $row['membersOnly'];
			$showOnSitemap = $row['showOnSitemap'];
	
			if ($withPopup == "1")
				$withoutPopup = 0;
			else
				$withoutPopup = 1;
				
			$xmlResponse .= "<pageId>$id</pageId>
							 <layoutId>$layoutId</layoutId>
							 <navParentId>$navParentId</navParentId>
							 <urlExtraParams>$urlExtraParams</urlExtraParams>
							 <withoutPopup>$withoutPopup</withoutPopup>
							 <isHtml>$isHtml</isHtml>
							 <membersOnly>$membersOnly</membersOnly>
							 <staticname>$staticname</staticname>
							 <showOnSitemap>$showOnSitemap</showOnSitemap>
							 <siteUrl>$siteUrl</siteUrl>";
		}

		$title		= commonValidXml($row['title']);
		$navTitle	= commonValidXml($row['navTitle']);
		$isReady	= $row['isReady'];
		$txt		= commonValidXml($row['txt']);
		$keywords	= commonValidXml($row['keywords']);
		$description= commonValidXml($row['description']);
		$robots		= commonValidXml($row['robots']);
		$winTitle	= commonValidXml($row['winTitle']);
		$updated	= $row['updated'];
		$musicBgFile= $row['musicBgFile'];
		$rewriteName   = commonValidXml($row['rewriteName']);
	
		$xmlResponse .=	   "<title$language>$title</title$language>
							<navTitle$language>$navTitle</navTitle$language>
							<isReady$language>$isReady</isReady$language>
							<txt$language>$txt</txt$language>
							<keywords$language>$keywords</keywords$language>
							<description$language>$description</description$language>
							<robots$language>$robots</robots$language>
							<winTitle$language>$winTitle</winTitle$language>
							<updated$language>$updated</updated$language>
							<musicBgFile$language>$musicBgFile</musicBgFile$language>
							<rewriteName$language>$rewriteName</rewriteName$language>";

		/*
		if ($id == 1)
		{
			$file = fopen ("/tmp/liat.log", "w");
			fwrite ($file, $row['txt']);
			fclose ($file);
		}
		*/
	}

	// add missing languages
	// ------------------------------------------------------------------------------------------------
	for ($i=0; $i<count($langsArray); $i++)
	{
		$language	  = $langsArray[$i];

		$xmlResponse .=	   "<title$language><![CDATA[]]></title$language>
							<navTitle$language><![CDATA[]]></navTitle$language>
							<isReady$language>0</isReady$language>
							<txt$language><![CDATA[]]></txt$language>
							<keywords$language><![CDATA[]]></keywords$language>
							<robots$language><![CDATA[]]></robots$language>
							<description$language><![CDATA[]]></description$language>
							<winTitle$language><![CDATA[]]></winTitle$language>
							<updated$language><![CDATA[]]></updated$language>
							<musicBgFile$language><![CDATA[]]></musicBgFile$language>
							<rewriteName$language><![CDATA[]]></rewriteName$language>";
	}

	return $xmlResponse;
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updatePage																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function updatePage ($xmlRequest)
{
	return (editPage ($xmlRequest, "update"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* previewPage																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function previewPage ($xmlRequest)
{
	return (editPage ($xmlRequest, "preview"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* editPage																								*/
/* ----------------------------------------------------------------------------------------------------	*/
function editPage ($xmlRequest, $editType)
{
	global $usedLangs;
	global $domainId;

	$langsArray = explode(",",$usedLangs);

	$id		= xmlParser_getValue($xmlRequest, "pageId");

	if ($id == "")
		trigger_error ("חסר קוד דף לביצוע הפעולה");

	$previewPage = false;

	if ($editType == "preview")
	{
		$previewPage = true;

		$id = $id * -1;

		if (doesPageExist($id))
			$editType = "update";
		else
			$editType = "add";
	}

	if ($editType == "add")
	{
		if (doesPageExist($id))
		{
			trigger_error (iconv("windows-1255", "utf-8", "דף עם קוד זהה ($id) כבר קיים במערכת"));
		}
	}
	else	// update page
	{
		if (!doesPageExist($id))
		{
			trigger_error (iconv("windows-1255", "utf-8", "דף עם קוד זה ($id) לא קיים במערכת. לא ניתן לבצע את העדכון"));
		}
	}

	for ($i=0; $i<count($langsArray); $i++)
	{
		$language		= $langsArray[$i];
		$rewriteName	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "rewriteName$language")));

		if (!commonCheckRewriteName($rewriteName, $id))
			trigger_error (iconv("windows-1255", "utf-8", "כתובת סטטית זו כבר קיימת"));
	}

	$withoutPopup	= xmlParser_getValue($xmlRequest, "withoutPopup");
	$layoutId		= xmlParser_getValue($xmlRequest, "layoutId");
	$navParentId	= xmlParser_getValue($xmlRequest, "navParentId");
	if ($withoutPopup == "1")
		$withPopup = 0;
	else
		$withPopup = 1;
	$isHtml			= xmlParser_getValue($xmlRequest, "isHtml");
	$membersOnly	= xmlParser_getValue($xmlRequest, "membersOnly");
	$staticname		= urlencode(commonDecode(xmlParser_getValue($xmlRequest, "staticname")));
	$showOnSitemap	= xmlParser_getValue($xmlRequest, "showOnSitemap");
	$urlExtraParams = addslashes(commonDecode(xmlParser_getValue($xmlRequest, "urlExtraParams")));

	if ($editType == "add")
	{
		$ibosUserId = commonGetIbosUserId ();

		$queryStr = "insert into pages (id, ibosUserId, layoutId, navParentId, urlExtraParams, withPopup, staticname, showOnSitemap, membersOnly) values 
						('$id','$ibosUserId', '$layoutId', '$navParentId', '$urlExtraParams', '$withPopup','$staticname', '$showOnSitemap', '$membersOnly')";

		commonDoQuery ($queryStr);

		$queryStr = "insert into htmlPages (id, isHtml) values ('$id','$isHtml')";

		commonDoQuery ($queryStr);
	}
	else // update
	{
		$queryStr = "update pages set  withPopup	 = '$withPopup',
									   layoutId	 	 = '$layoutId',
									   navParentId 	 = '$navParentId',
									   urlExtraParams= '$urlExtraParams', 
									   staticname	 = '$staticname',
									   showOnSitemap = '$showOnSitemap',
									   membersOnly   = '$membersOnly'
					 where id=$id";

		commonDoQuery ($queryStr);
	
		$queryStr = "update htmlPages set  isHtml		 = '$isHtml'
					 where id=$id";
	
		commonDoQuery ($queryStr);
	}
	# for later use - RSS
	$domainRow  = commonGetDomainRow ();
	$domainName = commonGetDomainName ($domainRow);
	
	commonConnectToUserDB($domainRow);

	$siteUrl     = commonGetDomainName($domainRow) . "/index2.php";

	# add languages rows for this page
	# ------------------------------------------------------------------------------------------------------
	for ($i=0; $i<count($langsArray); $i++)
	{
		$language		= $langsArray[$i];

		$title			= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "title$language")));
		$navTitle		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "navTitle$language")));
		$winTitle		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "winTitle$language")));

		$isReady		= xmlParser_getValue($xmlRequest, "isReady$language");

		if ($previewPage)
			$isReady = 0;

		$musicBgFile	= xmlParser_getValue($xmlRequest, "musicBgFile$language");
		$updated		= xmlParser_getValue($xmlRequest, "updated$language");
		$keywords		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "keywords$language")));
		$description	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "description$language")));
		$robots			= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "robots$language")));
		$txt			= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "txt$language")));
		$rewriteName	= commonFixRewriteName(commonDecode(xmlParser_getValue($xmlRequest, "rewriteName$language")));

		if ($editType == "add")
		{
			$queryStr		= "insert into pages_byLang (pageId, language, title, navTitle, winTitle, isReady, musicBgFile, 
														 keywords, description, robots, rewriteName, updated)
						       values ('$id','$language', '$title', '$navTitle', '$winTitle', '$isReady', '$musicBgFile', '$keywords', '$description', 
						   			   '$robots', '$rewriteName', ";
						   
			if ($updated == "" || $updated == "0000-00-00 00:00:00" || $previewPage)
			{
				$queryStr  .= "now())";
			}
			else
			{
				$queryStr  .= "'$updated')";
			}
		
			commonDoQuery ($queryStr);

			$queryStr		= "insert into htmlPages_byLang (pageId, language, txt)
							   values ('$id','$language', '$txt')";

			commonDoQuery ($queryStr);
		}
		else
		{
			$queryStr	= "replace pages_byLang set pageId		= $id,
													language 	= '$language',
													title 		= '$title',
												    navTitle	= '$navTitle',
												    winTitle	= '$winTitle',
												    isReady		= '$isReady',
												    musicBgFile	= '$musicBgFile',
												    keywords	= '$keywords',
												    description	= '$description',
													robots		= '$robots',
												    rewriteName	= '$rewriteName',
												    updated		= ";
			if ($updated == "" || $updated == "0000-00-00 00:00:00" || $previewPage)
			{
				$queryStr  .= "now()";
			}
			else
			{
				$queryStr  .= "'$updated'";
			}


			commonDoQuery ($queryStr);

			$queryStr	= "replace htmlPages_byLang set pageId = $id, language = '$language', txt = '$txt'";
			commonDoQuery ($queryStr);
		}


		# --------------------------------------------------------------------------------------------------
	}


	if (!$previewPage)
	{
		// Update .htaccess with mod_rewrite rules
		if ($domainId != 345 && $domainRow['domainName'] != 'metargo.com') // soragim
			fopen(commonGetDomainName($domainRow) . "/updateModRewrite.php","r");
	}

	return "<siteUrl>$siteUrl</siteUrl>
			<pageId>$id</pageId>";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deletePage																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function deletePage ($xmlRequest)
{
	global $usedLangs;
	global $domainId;

	$ids = xmlParser_getValues ($xmlRequest, "pageId");

	if (count($ids) == 0)
		trigger_error ("חסר קוד דף לביצוע הפעולה");

	foreach ($ids as $id)
	{
		$queryStr = "delete from pages where id = $id";
		commonDoQuery ($queryStr);

		$queryStr = "delete from pages_byLang where pageId = $id";
		commonDoQuery ($queryStr);

		$queryStr = "delete from categoriesItems where itemId = $id and type = 'page'";
		commonDoQuery ($queryStr);

		$queryStr = "delete from htmlPages where id = $id";
		commonDoQuery ($queryStr);

		$queryStr = "delete from htmlPages_byLang where pageId = $id";
		commonDoQuery ($queryStr);
	}

	# recreate RSS updated in website 
	# --------------------------------------------------------------------------------------------------
	$domainRow  = commonGetDomainRow ();
	$domainName = commonGetDomainName ($domainRow);
	
	$langsArray = explode(",",$usedLangs);
	for ($i=0; $i<count($langsArray); $i++)
	{
		$language		= $langsArray[$i];

//		if ($domainId != 345 && $domainRow['domainName'] != 'metargo.com') // soragim
//			file_get_contents("$domainName/pagesRSS.php?lang=$language");
		//old $file = fopen ("$domainName/pagesRSS.php?lang=$language","r");
		//old fclose ($file);
	}

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* resetCounters																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function resetCounters ($xmlRequest)
{
	$queryStr = "update pages set countViews = 0 where type = 'html'";
	commonDoQuery ($queryStr);

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getMusicFiles																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getMusicFiles ($xmlRequest)
{
	$domainRow = commonGetDomainRow ();

	$connId    = commonFtpConnect	($domainRow);
	
	ftp_chdir ($connId, "loadedFiles");

	$files  = ftp_nlist($connId, "*.mp3");
	$files2 = ftp_nlist($connId, "*.wav");

	commonFtpDisconnect($connId);

	$xmlResponse = "<items>";

	for ($i=0; $i<count($files); $i++)
	{
		$xmlResponse .= "<item>"								.
							"<fileName>" . commonValidXml(commonEncode($files[$i])) . "</fileName>"	.
						"</item>";	
	}

	for ($i=0; $i<count($files2); $i++)
	{
		$xmlResponse .= "<item>"								.
							"<fileName>" . commonValidXml(commonEncode($files2[$i])) . "</fileName>"	.
						"</item>";	
	}

	$xmlResponse .= "</items>";

	return $xmlResponse;
}

?>
