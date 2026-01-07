<?php

require_once "picsTools.php";

/* ----------------------------------------------------------------------------------------------------	*/
/* getCategoryTypes																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getCategoryTypes ($xmlRequest)
{
	$queryStr	= "select count(*), type  from categories group by type";
	$result		= commonDoQuery($queryStr);

	$numItems	= array("global"	=> "",
						"shop" 		=> "",
						"forum"		=> "",
						"page"		=> "",
						"url"		=> "",
						"essay"		=> "",
						"faq"		=> "",
						"gallery"	=> "",
						"album"		=> "",
						"banner"	=> "",
						"survey"	=> "",
						"blog"		=> "",
						"blogPost"	=> "",
						"tabletItem"=> "",
						"news"		=> "",
						"phonesBook"=> "",
						"event"		=> "",
						"member"	=> "",
						"hug"		=> "",
						"specific"	=> "",
						"specific2"	=> "");

	$mostItems = 0;
	$typeOfMostItems = 0;
	while ($row = commonQuery_fetchRow($result))
	{
		if ($row[0] >= $mostItems)
		{
			$mostItems = $row[0];
			$typeOfMostItems = $row['type'];
		}

		if ($row[0] == 0)
			$row[0] = "";
		else
			$row[0] = "(" . $row[0] . ")";

		$numItems[$row['type']] = $row[0];

	}

	$xmlResponse	= "<items>
						<item>
							<id>global</id>
							<name>". commonPhpEncode("כללי $numItems[global]") . "</name>
							<nameEng>". commonPhpEncode("General $numItems[global]") . "</nameEng>
						</item>
						<item>
							<id>shop</id>
							<name>". commonPhpEncode("מוצרים $numItems[shop]") . "</name>
							<nameEng>". commonPhpEncode("Products $numItems[shop]") . "</nameEng>
						</item>
						<item>
							<id>forum</id>
							<name>". commonPhpEncode("פורומים $numItems[forum]") . "</name>
							<nameEng>". commonPhpEncode("Forums $numItems[forum]") . "</nameEng>
						</item>
						<item>
							<id>page</id>
							<name>". commonPhpEncode("דפים $numItems[page]") . "</name>
							<nameEng>". commonPhpEncode("Pages $numItems[page]") . "</nameEng>
						</item>
						<item>
							<id>url</id>
							<name>". commonPhpEncode("קישורים $numItems[url]") . "</name>
							<nameEng>". commonPhpEncode("Links $numItems[url]") . "</nameEng>
						</item>
						<item>
							<id>essay</id>
							<name>". commonPhpEncode("כתבות $numItems[essay]") . "</name>
							<nameEng>". commonPhpEncode("Essays $numItems[essay]") . "</nameEng>
						</item>
						<item>
							<id>faq</id>
							<name>". commonPhpEncode("שות $numItems[faq]") . "</name>
							<nameEng>". commonPhpEncode("FAQs $numItems[faq]") . "</nameEng>
						</item>
						<item>
							<id>gallery</id>
							<name>". commonPhpEncode("גלריות $numItems[gallery]") . "</name>
							<nameEng>". commonPhpEncode("Galleries $numItems[gallery]") . "</nameEng>
						</item>
						<item>
							<id>album</id>
							<name>". commonPhpEncode("אלבומים $numItems[album]") . "</name>
							<nameEng>". commonPhpEncode("Albums $numItems[album]") . "</nameEng>
						</item>
						<item>
							<id>banner</id>
							<name>". commonPhpEncode("באנרים $numItems[banner]") . "</name>
							<nameEng>". commonPhpEncode("Banners $numItems[banner]") . "</nameEng>
						</item>
						<item>
							<id>survey</id>
							<name>". commonPhpEncode("סקרים $numItems[survey]") . "</name>
							<nameEng>". commonPhpEncode("Surveys $numItems[survey]") . "</nameEng>
						</item>
						<item>
							<id>blog</id>
							<name>". commonPhpEncode("בלוגים $numItems[blog]") . "</name>
							<nameEng>". commonPhpEncode("Blogs $numItems[blog]") . "</nameEng>
						</item>
						<item>
							<id>blogPost</id>
							<name>". commonPhpEncode("רשומות בלוגים $numItems[blogPost]") . "</name>
							<nameEng>". commonPhpEncode("Blog Posts $numItems[blogPost]") . "</nameEng>
						</item>
						<item>
							<id>tabletItem</id>
							<name>". commonPhpEncode("הודעות לוח $numItems[tabletItem]") . "</name>
							<nameEng>". commonPhpEncode("Tablet ads $numItems[tabletItem]") . "</nameEng>
						</item>
						<item>
							<id>news</id>
							<name>". commonPhpEncode("חדשות $numItems[news]") . "</name>
							<nameEng>". commonPhpEncode("News $numItems[news]") . "</nameEng>
						</item>
						<item>
							<id>phonesBook</id>
							<name>". commonPhpEncode("ספר טלפונים $numItems[phonesBook]") . "</name>
							<nameEng>". commonPhpEncode("Phone books $numItems[phonesBook]") . "</nameEng>
						</item>
						<item>
							<id>event</id>
							<name>". commonPhpEncode("אירועים $numItems[event]") . "</name>
							<nameEng>". commonPhpEncode("Events $numItems[event]") . "</nameEng>
						</item>
						<item>
							<id>member</id>
							<name>". commonPhpEncode("גולשים רשומים $numItems[member]") . "</name>
							<nameEng>". commonPhpEncode("Members $numItems[member]") . "</nameEng>
						</item>
						<item>
							<id>hug</id>
							<name>". commonPhpEncode("חוגים $numItems[hug]") . "</name>
							<nameEng>". commonPhpEncode("Classes $numItems[hug]") . "</nameEng>
						</item>";

	// get categories by domain id
	$domainRow = commonGetDomainRow();

	switch ($domainRow['id'])
	{
		case "136"	:	// arie
			$xmlResponse .= "<item>
								<id>specific</id>
								<name>". commonPhpEncode("קופונים $numItems[specific]") . "</name>
							 </item>";

			break;

		case "141"	:	// lorak
			$xmlResponse .= "<item>
								<id>specific</id>
								<name>". commonPhpEncode("יועצים $numItems[specific]") . "</name>
							 </item>";

			break;

		case "105"	:	// studio123
			$xmlResponse .= "<item>
								<id>specific</id>
								<name>". commonPhpEncode("קופונים $numItems[specific]") . "</name>
							 </item>";

			break;

		case "167"	:	// gs-marketing
			$xmlResponse .= "<item>
								<id>specific</id>
								<name>". commonPhpEncode("עבודות $numItems[specific]") . "</name>
							 </item>";

			break;

		case "191"	:	// inugim
		case "247"	:	// tzoona
		case "268"	: 	// made-in-israel
		case "289"	:	// vradim
		case "351"	:	// lagina
		case "409"	: 	// wbc-plus
			$xmlResponse .= "<item>
								<id>specific</id>
								<name>". commonPhpEncode("עסקים $numItems[specific]") . "</name>
							 </item>";

			break;

		case "254"	:	// hasulam
			$xmlResponse .= "<item>
								<id>specific</id>
								<name>". commonPhpEncode("תחומי לימוד $numItems[specific]") . "</name>
							 </item>
							 <item>
								<id>specific2</id>
								<name>". commonPhpEncode("קורסים $numItems[specific2]") . "</name>
							 </item>";

			break;

		case "295"	:	// hufsonet
			$xmlResponse .= "<item>
								<id>specific</id>
								<name>". commonPhpEncode("צימרים $numItems[specific]") . "</name>
							 </item>";

			break;

		case "347"	:	// israel exporter
			$xmlResponse .= "<item>
								<id>specific</id>
								<name>". commonPhpEncode("עסקים $numItems[specific]") . "</name>
							 </item>
							 <item>
								<id>specific2</id>
								<name>". commonPhpEncode("ספקי שירותים $numItems[specific2]") . "</name>
							 </item>";

			break;

		case "369"	:	// isrlist
			$xmlResponse .= "<item>
								<id>specific</id>
								<name>". commonPhpEncode("מונחים $numItems[specific]") . "</name>
							 </item>";

			break;

		case "407"	:	// land value
			$xmlResponse .= "<item>
								<id>specific</id>
								<name>". commonPhpEncode("פרסומים $numItems[specific]") . "</name>
							 </item>";

			break;

		case "415"	:	// otzar law
			$xmlResponse .= "<item>
								<id>specific</id>
								<name>". commonPhpEncode("ספרים $numItems[specific]") . "</name>
							 </item>";

			break;

		case "432"	:	// wannado
			$xmlResponse .= "<item>
								<id>specific</id>
								<name>". commonPhpEncode("יצרנים $numItems[specific]") . "</name>
							 </item>";

			break;

		case "442"	:	// metar
			$xmlResponse .= "<item>
								<id>specific</id>
								<name>". commonPhpEncode("קורסים $numItems[specific]") . "</name>
							 </item>";

			break;

		case "414"	: 	// oogle
			$xmlResponse .= "<item>
								<id>specific</id>
								<name>". commonPhpEncode("קמפיינים $numItems[specific]") . "</name>
							 </item>";

			break;

		case "512"	:	// tchumin
			$xmlResponse .= "<item>
								<id>specific</id>
								<name>". commonPhpEncode("מדורים $numItems[specific]") . "</name>
							 </item>";

			break;

		case "523"	:	// kosharot
			$xmlResponse .= "<item>
								<id>specific</id>
								<name>". commonPhpEncode("מומלצים $numItems[specific]") . "</name>
							 </item>";

			break;

		case "526"	:	// israeli-experts
			$xmlResponse .= "<item>
								<id>specific</id>
								<name>". commonPhpEncode("תחומים $numItems[specific]") . "</name>
							 </item>";

			break;

		case "530"	:	// loox
			$xmlResponse .= "<item>
								<id>specific</id>
								<name>". commonPhpEncode("מוצרי loox $numItems[specific]") . "</name>
							 </item>";

			break;

		case "540"	:	// yelonmoreh
			$xmlResponse .= "<item>
								<id>specific</id>
								<name>". commonPhpEncode("שיעורים $numItems[specific]") . "</name>
							 </item>";

			break;


	}

	$xmlResponse .=   "</items><typeOfMostItems>$typeOfMostItems</typeOfMostItems>";

	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getCategories																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getCategories ($xmlRequest)
{	
	global $cookie_guiLang;

	$type			= xmlParser_getValue($xmlRequest, "type");
	$withGlobal 	= xmlParser_getValue($xmlRequest, "withGlobal");
	$groupId 		= xmlParser_getValue($xmlRequest, "groupId");
	$isRecursive 	= xmlParser_getValue($xmlRequest, "isRecursive");
	$parentId	 	= xmlParser_getValue($xmlRequest, "parentId");
	$subCategory 	= xmlParser_getValue($xmlRequest, "subCategory");

	if ($withGlobal == "" || $withGlobal == "1")
	{
		$withGlobal = true;
		$condition = "(type='$type' || type = 'global')";
	}
	else
	{
		$withGlobal = false;
		$condition = "type = '$type'";
	}

	if ($groupId != "")
		$condition .= " and groupId = $groupId ";

	if ($isRecursive == "0")
		$isRecursive = false;
	else
		$isRecursive = true;

	if ($parentId == "")
		$parentId = 0;

	if ($subCategory != "")
		$parentId = $subCategory;

	// get num categories
	$queryStr	 = "select count(*) from categories where $condition";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$total	 	 = $row[0];

	$xmlResponse = "<items>";
	
	$xmlResponse .= getCategoriesByLevel ($type, $parentId, 0, $isRecursive,"count", $withGlobal, $groupId);

	if (strpos($xmlResponse, "class='indent'") === false)
	{
		// cancel strong
		$xmlResponse = str_replace("<strong>", "", $xmlResponse);
		$xmlResponse = str_replace("</strong>", "", $xmlResponse);
	}

	$total	= substr_count ($xmlResponse, "<item>");

	if ($cookie_guiLang == "ENG")
		$catsText = "Categories";
	else
		$catsText = commonPhpEncode("קטגוריות");

	$xmlResponse .=	"</items>"		.
					"<totals>"		.
						"<totalText>$total $catsText</totalText>"	.
					"</totals>";
	

	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getCategoriesByLevel																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function getCategoriesByLevel ($type, $parentId, $level, $isRecursive, $itemId, $withGlobal = true, $groupId = "")
{
	global $filePrefix;
	global $usedLangs;
	global $domainId;

	$langsArray = explode(",",$usedLangs);

	if (!isset($domainId))
	{
		$domainRow = commonGetDomainRow();
		$domainId  = $domainRow['id'];
		commonConnectToUserDB ($domainRow);
	}

	$catType = $type;
	//	if ($catType == "url") $catType = "page";

	if ($withGlobal)
		$condition = "(type='$catType' || type = 'global')";
	else
		$condition = "type = '$catType'";

	if ($groupId != "")
		$condition .= " and groupId = $groupId " ;

	// get details
	$queryStr		= "select id, parentId, pos, type, name, shortDescription, categoryFile, sourceFile, countViews
					   from categories sc left join categories_byLang scl on sc.id=scl.categoryId and language = '$langsArray[0]'
					   where parentId = $parentId and $condition
					   order by pos ";
	$result	     	= commonDoQuery ($queryStr);

	$xml = "";

	while ($row	= commonQuery_fetchRow($result))
	{
		$id				= $row['id'];
		$parentId		= $row['parentId'];
		$pos			= $row['pos'];
		$typeText		= formatCategoryType($row['type']);

		if ($row['name'] == "")
			$row['name'] = $row['shortDescription'];

		if ($level == 0)
			$row['name'] = "<strong>$row[name]</strong>";
		else
			$row['name'] = "<span class='indent'>$row[name]</span>";

		$indent = str_repeat("&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;",$level);
//		$name  = commonValidXml (commonCutText($indent . $row['name'], 100),true);
		$name  = commonValidXml ($indent . $row['name'],true);

		$countViews	= $row['countViews'];

		$selectName = commonValidXml(str_repeat("&nbsp;&nbsp;", $level) . $row['name'],true);

		$xml  .= "<item>"											.
						"<id>$id</id>"								.
						"<name>$name</name>"						.
						"<countViews>$countViews</countViews>";

		if ($itemId != "" && $itemId != "count")
		{
			$queryStr 	 = "select count(*) from categoriesItems
							where itemId=$itemId and categoryId=$id and type='$type'";
			$checkResult = commonDoQuery($queryStr);
			$checkRow	 = commonQuery_fetchRow($checkResult);
			$enable		 = $checkRow[0];

			$xml .=		"<enable>$enable</enable>";
		}
		else
		{
			$xml .=		"<type>$typeText</type>";

			if ($itemId == "count")
			{
				if ($domainId == 294 || $domainId == 412)	// photour - too much categories, gulfForex - abord hosting - too slow
				{
					$count = 0;
				}
				else
				{
					$queryStr 	 = "select count(*) from categoriesItems where categoryId=$id";
					$countResult = commonDoQuery($queryStr);
					$countRow	 = commonQuery_fetchRow($countResult);
					$count		 = $countRow[0];
				}

				if ($count == 0) $count = "";
				$xml .= "<countItems>$count</countItems>";
			}

			$xml .=		"<selectName>$selectName</selectName>"			. 
						"<parentId>$parentId</parentId>"				.
						"<pos>$pos</pos>";
		}

		$xml .= 	"</item>";
						

		if ($isRecursive)
			$xml  .= getCategoriesByLevel ($type,$id,$level+1, $isRecursive, $itemId, $withGlobal, $groupId);
	}

	return ($xml);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getCategoryDetails																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function getCategoryDetails ($xmlRequest)
{
	global $usedLangs;

	$id		= xmlParser_getValue($xmlRequest, "id");

	if ($id == "")
		trigger_error ("חסר קוד קטגוריה לביצוע הפעולה");

	$queryStr	= "select categories.*, scl1.language, scl1.name as name, scl1.description, scl1.shortDescription, scl1.isReady as isReady,
						  scl1.winTitle, scl1.metaKeywords, scl1.metaDescription, scl1.picTitle
				   from categories left join categories_byLang scl1 on id=scl1.categoryId
				   where id=$id";
	$result		= commonDoQuery ($queryStr);

	if (commonQuery_numRows($result) == 0)
		trigger_error ("קטגוריה קוד זה ($id) לא קיימת במערכת. לא ניתן לבצע את העדכון");

	$langsArray = explode(",",$usedLangs);

	$xmlResponse = "";

	while ($row = commonQuery_fetchRow($result))
	{
		$language = $row['language'];

		$langsArray = commonArrayRemove ($langsArray, $language);	

		if ($xmlResponse == "")
		{
			$pos		  = $row['pos'];
			$parentId	  = $row['parentId'];
			$type		  = $row['type'];
			$groupId  	  = $row['groupId'];
			$linkCode 		= commonValidXml ($row['linkCode'], true);
			$typeText	  = formatCategoryType ($type);
			$categoryFile = commonValidXml ($row['categoryFile'], true);
			$sourceFile	  = commonValidXml (addslashes($row['sourceFile']));
			$picsDisplay  = $row['picsDisplay'];

			$fullFileName = urlencode($row['categoryFile']);
			$fullFileName = commonValidXml($fullFileName);
			
			if ($parentId == "0")
				$parentName = commonPhpEncode("קטגוריה ראשית");
			else
			{
				$queryStr2  = "select name from categories_byLang where categoryId=$parentId and language='$language'";
				$result2    = commonDoQuery ($queryStr2);
				$row2	    = commonQuery_fetchRow($result2);

				$parentName = $row2['name'];
				$parentName = commonValidXml ($parentName);
			}

			$xmlResponse  =	"<id>$id</id>
							 <pos>$pos</pos>
							 <type>$type</type>
							 <groupId>$groupId</groupId>
							 <linkCode>$linkCode</linkCode>
							 <typeText>$typeText</typeText>
							 <parentName>$parentName</parentName>
							 <parentId>$parentId</parentId>
							 <usedLangs>$usedLangs</usedLangs>
							 <sourceFile>$sourceFile</sourceFile>
							 <formSourceFile>$sourceFile</formSourceFile>
							 <fullFileName>$fullFileName</fullFileName>
							 <picsDisplay>$picsDisplay</picsDisplay>
							 <items>" .
								getCategoriesByLevel ($type,$parentId,0,false,"")	.
							"</items>";

			$flags = commonGetItemFlags ($id, "category");

			$xmlResponse .= commonGetItemFlagsXml ($flags, "category");
		}

		$name				= commonValidXml($row['name']);
		$description		= commonValidXml($row['description']);
		$shortDescription	= commonValidXml($row['shortDescription']);
		$picTitle			= commonValidXml($row['picTitle']);
		$winTitle			= commonValidXml($row['winTitle']);
		$metaKeywords		= commonValidXml($row['metaKeywords']);
		$metaDescription	= commonValidXml($row['metaDescription']);
		$isReady  	  		= $row['isReady'];

		$xmlResponse   .= "<name$language>$name</name$language>
					       <description$language>$description</description$language>
					       <shortDescription$language>$shortDescription</shortDescription$language>
					       <picTitle$language>$picTitle</picTitle$language>
					       <winTitle$language>$winTitle</winTitle$language>
					       <metaKeywords$language>$metaKeywords</metaKeywords$language>
					       <metaDescription$language>$metaDescription</metaDescription$language>
						   <isReady$language>$isReady</isReady$language>";
	}

	// add missing languages
	// ------------------------------------------------------------------------------------------------
	for ($i=0; $i<count($langsArray); $i++)
	{
		$language	  = $langsArray[$i];

		$xmlResponse .=	   "<name$language><![CDATA[]]></name$language>
					        <description$language><![CDATA[]]></description$language>
					        <shortDescription$language><![CDATA[]]></shortDescription$language>
					        <picTitle$language><![CDATA[]]></picTitle$language>
					        <winTitle$language><![CDATA[]]></winTitle$language>
					        <metaKeywords$language><![CDATA[]]></metaKeywords$language>
					        <metaDescription$language><![CDATA[]]></metaDescription$language>
						    <isReady$language><![CDATA[]]></isReady$language>";
	}

	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteCategory																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteCategory ($xmlRequest)
{
	$id = xmlParser_getValue ($xmlRequest, "id");

	if ($id == "")
		trigger_error ("חסר קוד קטגוריה לביצוע הפעולה");

	# check if this category has sons
	$queryStr = "select count(*) from categories where parentId=$id";
	$result	  = commonDoQuery($queryStr);
	$row	  = commonQuery_fetchRow($result);
	$count	  = $row[0];

	if ($count != 0)
	{
		trigger_error ("לקטגוריה זו יש תתי קטגוריות ולכן לא ניתן למחוקה");
	}
	
	// get this category pos
	$queryStr    = "select parentId, pos, type from categories where id=$id";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$parentId 	 = $row['parentId'];
	$pos		 = $row['pos'];
	$type		 = $row['type'];

	// update places
	$queryStr 	 = "update categories set pos = pos-1 where parentId=$parentId and type='$type' and pos > $pos";
	commonDoQuery ($queryStr);

	$queryStr =  "delete from categories where id=$id";
	commonDoQuery ($queryStr);

	$queryStr =  "delete from categories_byLang where categoryId=$id";
	commonDoQuery ($queryStr);

	$queryStr =  "delete from categoriesItems where categoryId=$id";
	commonDoQuery ($queryStr);

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* addCategory																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function addCategory ($xmlRequest)
{
	return (editCategory ($xmlRequest, "add"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateCategory																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateCategory ($xmlRequest)
{
	editCategory ($xmlRequest, "update");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* editCategory																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function editCategory ($xmlRequest, $editType)
{
	global $usedLangs;
	global $userId;
	global $ibosHomeDir;

	$id 			= xmlParser_getValue($xmlRequest, "id");
	$parentId 		= xmlParser_getValue($xmlRequest, "parentId");
	$groupId 		= xmlParser_getValue($xmlRequest, "groupId");
	$type 			= xmlParser_getValue($xmlRequest, "type");
	$pos 			= xmlParser_getValue($xmlRequest, "pos");
	$picsDisplay 	= xmlParser_getValue($xmlRequest, "picsDisplay");
	$linkCode		= addslashes(xmlParser_getValue($xmlRequest, "linkCode"));

	if ($parentId == "") $parentId = 0;
	if ($groupId  == "") $groupId  = 0;

	if ($editType == "add")
	{
		# get new category id
		# --------------------------------------------------------------------------------------------------
		$queryStr 	 = "select max(id) from categories";
		$result	     = commonDoQuery ($queryStr);
		$row	     = commonQuery_fetchRow($result);
		$id		  	 = $row[0] + 1;
	}

	// handle link file
	# ------------------------------------------------------------------------------------------------------
	$sourceFile 	= addslashes(xmlParser_getValue($xmlRequest, "sourceFile"));	
	$dimensionId	= addslashes(xmlParser_getValue($xmlRequest, "dimensionId"));	

	$fileLoaded  	= false;

	$picFile 		= "";
	$suffix 		= "";

	if ($sourceFile != "")
	{
		$fileLoaded = true;
		
		$suffix		= commonFileSuffix($sourceFile);

		$picFile = "${id}$suffix";

		list ($picWidth, $picHeight, $bgColor) = commonGetDimensionDetails ($dimensionId);
	}

	$oldFile	= "";

	if ($editType == "add")
	{
		$queryStr 	 = "select max(pos) from categories where type = '$type' and parentId=$parentId";
		$result	     = commonDoQuery ($queryStr);
		$row	     = commonQuery_fetchRow($result);
		$pos		 = $row[0];
		
		if ($pos == "")
			$pos = 1;
		else
			$pos++;

		$queryStr = "insert into categories (id, parentId, pos, type, groupId, linkCode, categoryFile, sourceFile, picsDisplay)
					 values ('$id', '$parentId', '$pos', '$type', '$groupId', '$linkCode', '$picFile', '$sourceFile', '$picsDisplay')";
		commonDoQuery ($queryStr);
	}
	else
	{
		// get curr category pos & parent
		$queryStr		= "select * from categories where id=$id";
		$result			= commonDoQuery ($queryStr);
		$row			= commonQuery_fetchRow($result);
		$currPos		= $row['pos'];
		$type			= $row['type'];
		$currParentId	= $row['parentId'];
		$oldFile		= $row['categoryFile'];

//		trigger_error ("curr pos = $currPos ---- new pos = $pos");

		if ($currParentId == $parentId)
		{
			if ($currPos > $pos)
			{
				$queryStr = "update categories set pos = pos+1 where parentId=$parentId and type = '$type' and pos >= $pos and pos < $currPos";
				commonDoQuery ($queryStr);
			}

			if ($currPos < $pos)
			{
				$queryStr = "update categories set pos = pos-1 where parentId=$parentId and type = '$type' and pos > $currPos and pos <= $pos";
				commonDoQuery ($queryStr);
			}
		}
		else // change category parent
		{
			// update places for prev parent
			$queryStr 	 = "update categories set pos = pos-1 where parentId = $currParentId and type = '$type' and pos > $currPos";
			commonDoQuery ($queryStr);

			// update places for new parent
			$queryStr 	 = "update categories set pos = pos+1 where parentId = $parentId and type = '$type' and pos >= $pos";
			commonDoQuery ($queryStr);
		}	

		$queryStr 	= "delete from categories_byLang where categoryId='$id'";
		commonDoQuery ($queryStr);

		$queryStr = "update categories set parentId		= '$parentId',
										   pos	  		=  $pos,
										   groupId		= '$groupId', 
										   linkCode		= '$linkCode', 
										   picsDisplay	= '$picsDisplay'";
		if ($fileLoaded)
		{
			$queryStr	.= ",			   categoryFile = '$picFile',
										   sourceFile 	= '$sourceFile'";
		}
		$queryStr .= " where id=$id";
		commonDoQuery ($queryStr);

	}

	# add languages rows for this category
	# ------------------------------------------------------------------------------------------------------
	$langsArray = explode(",",$usedLangs);

	for ($i=0; $i<count($langsArray); $i++)
	{
		$language			= $langsArray[$i];

		$name				= addslashes(xmlParser_getValue($xmlRequest, "name$language"));
		$description		= addslashes(xmlParser_getValue($xmlRequest, "description$language"));
		$shortDescription	= addslashes(xmlParser_getValue($xmlRequest, "shortDescription$language"));
		$picTitle			= addslashes(xmlParser_getValue($xmlRequest, "picTitle$language"));
		$winTitle			= addslashes(xmlParser_getValue($xmlRequest, "winTitle$language"));
		$metaKeywords		= addslashes(xmlParser_getValue($xmlRequest, "metaKeywords$language"));
		$metaDescription	= addslashes(xmlParser_getValue($xmlRequest, "metaDescription$language"));
		$isReady			= addslashes(xmlParser_getValue($xmlRequest, "isReady$language"));

		if ($isReady == "") $isReady = "1";

		$queryStr		= "insert into categories_byLang (categoryId, language, name, description, shortDescription, isReady, picTitle,
														  winTitle, metaKeywords, metaDescription)
						   values ('$id','$language','$name', '$description', '$shortDescription', $isReady, '$picTitle',
								   '$winTitle', '$metaKeywords', '$metaDescription')";
		commonDoQuery ($queryStr);
	}

	commonSaveItemFlags ($id, "category", $xmlRequest);
	
	// handle file
	# ------------------------------------------------------------------------------------------------------
	$filePath = "$ibosHomeDir/html/SWFUpload/files/$userId/";

	if ($fileLoaded)
	{

		$domainRow	= commonGetDomainRow();
		$domainName = commonGetDomainName ($domainRow);

		$connId 	= commonFtpConnect($domainRow); 
		ftp_chdir ($connId, "shopFiles");

		if ($oldFile != "")
			commonFtpDelete ($connId, $oldFile);

		if ($picWidth == 0 && $picHeight == 0)
		{
			$upload = ftp_put($connId, $picFile, "$filePath/$sourceFile", FTP_BINARY);
		}
		else
		{
			picsToolsResize("$filePath/$sourceFile", $suffix, $picWidth, $picHeight, "/../../tmp/$picFile", $bgColor);
		
			$upload = ftp_put($connId, $picFile, "/../../tmp/$picFile", FTP_BINARY);
		}

		commonFtpDisconnect ($connId);
	}

 	// delete old files
	commonDeleteOldFiles ($filePath, 7200);	// 2 hour
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getCategoriesOfItem																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function getCategoriesOfItem ($xmlRequest)
{
	$domainRow = commonGetDomainRow();
	$domainId  = $domainRow['id'];
	commonConnectToUserDB ($domainRow);

	$theLangs	= xmlParser_getValue($xmlRequest, "theLangs");
	$langsArray = explode(",",$theLangs);

	$id	  	  	= xmlParser_getValue($xmlRequest, "itemId");
	$type  	  	= xmlParser_getValue($xmlRequest, "type");

	if ($id == "")
		trigger_error ("חסר קוד רכיב לביצוע הפעולה");

	$selected = xmlParser_getValue($xmlRequest, "selected");

	$xmlResponse = "<items>";

	if ($selected != "")
	{
		$rows	= array();

		if ($selected == "1")
		{
			$queryStr = "select categoriesItems.categoryId, categories_byLang.name, categoriesItems.pos, cbl.name as parentName
						 from categoriesItems
						 left join categories on categoriesItems.categoryId = categories.id
						 left join categories_byLang on categoriesItems.categoryId = categories_byLang.categoryId and 
						 								categories_byLang.language='$langsArray[0]'
						 left join categories_byLang cbl on categories.parentId = cbl.categoryId and cbl.language='$langsArray[0]' 
						 where categoriesItems.itemId = $id
						 and   categoriesItems.type   = '$type'";
			$result	    = commonDoQuery ($queryStr);

			while ($row = commonQuery_fetchRow($result))
			{
				$row['catLevel'] = 0;
	
				array_push ($rows, $row);
			}
		}
		else
		{
			$parentId	= 0;

			getCatsRows ($rows, 0, $type, $langsArray[0], $id, 0);
		}

		foreach ($rows as $row)
		{
			$categoryId   = $row['categoryId'];
			$parentName   = $row['parentName'];
			$name		  = $row['name'];
			$pos   	   	  = $row['pos'];
			$catLevel	  = $row['catLevel'];

//			if ($parentName != "")
//				$name = "$parentName - $name";

			$indent = str_repeat("&nbsp;&nbsp;&nbsp;",$catLevel);
			$name 		  = commonValidXml("$indent$name");

			$xmlResponse .= "<item>"						.
								"<id>$categoryId</id>"		.
								"<name>$name</name>"		.
								"<pos>$pos</pos>"			.
								"<enable>1</enable>"		.
							"</item>";
		}
	}
	else
	{
		$queryStr	  = "select count(*) from categories";
		$result	      = commonDoQuery ($queryStr);

		$xmlResponse .= getCategoriesByLevel ($type,0,0,true,$id);
	}

	$numRows	 = count($rows);

	$xmlResponse .=	"</items>
					 <totals>
					 	<totalText>$numRows</totalText>
					</totals>";

	return ($xmlResponse);
}

function getCatsRows (&$rows, $parentId, $type, $lang, $id, $level)
{
	$queryStr = "select distinct categories.id as categoryId, categories_byLang.name as name, 1 as pos, cbl.name as parentName
				 from categories
				 left join categories_byLang on categories.id = categories_byLang.categoryId and categories_byLang.language='$lang' 
				 left join categories_byLang cbl on categories.parentId = cbl.categoryId and cbl.language='$lang' 
				 where (categories.type = '$type' || categories.type = 'global')
				 and   categories.id not in (select categoryId from categoriesItems where itemId = $id && type='$type')
				 and   parentId = $parentId
				 order by categories_byLang.name";

	$result	= commonDoQuery($queryStr);

	while ($row = commonQuery_fetchRow($result))
	{
		$row['catLevel'] = $level;

		array_push($rows, $row);

		getCatsRows ($rows, $row['categoryId'], $type, $lang, $id, $level+1);
	}
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getCategoryItems																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getCategoryItems ($xmlRequest)
{
	global $usedLangs;

	$langs = xmlParser_getValue($xmlRequest, "langs");

	if ($langs != "")
	{
		$usedLangs = $langs;
	}

	$langsArray = explode(",",$usedLangs);


	$id	  	  = xmlParser_getValue($xmlRequest, "id");

	if ($id == "")
		trigger_error ("חסר קוד קטגוריה לביצוע הפעולה");

	$domainRow = commonGetDomainRow();
	commonConnectToUserDB ($domainRow);

	$xmlResponse = "<items>";

	$queryStr = "select itemId, type, pos 
				 from categoriesItems
			 	 where categoriesItems.categoryId = $id order by pos";

	if ($domainRow['id'] == 310)	// games
		$queryStr .= " limit 10";

	$result	     = commonDoQuery ($queryStr);
		
	$numRows	 = commonQuery_numRows($result);


	while ($row	= commonQuery_fetchRow($result))
	{
		$itemId 	= $row['itemId'];
		$pos		= $row['pos'];
		$typeText   = formatCategoryItemType($row['type'], $domainRow['id']);
		$itemRow	= commonCategoryItemRow ($itemId, $row['type'], $langsArray[0]);
		
		$name		= "";
		if ($itemRow != "")
		{
			if ($domainRow['id'] == 540)	// yelonmoreh
			{
				if ($itemRow['name'] != "")
					$itemRow['name'] = "($row[pos]) $itemRow[name]";
			}

			if ($itemRow['name'] == "")
				$name = $itemId;
			else
				$name = commonValidXml($itemRow['name']);
		}

		$xmlResponse .= "<item>
							<id>$itemId</id>
							<pos>$pos</pos>
							<itemType>$typeText</itemType>
							<name>$name</name>
						</item>";
	}

	$xmlResponse .= "</items>
					 <totals>
					 	<totalText>$numRows</totalText>
					 </totals>";

	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* addCategoryItem																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function addCategoryItem ($xmlRequest)
{
	$categoryId = xmlParser_getValue ($xmlRequest, "categoryId");
	$itemId 	= xmlParser_getValue ($xmlRequest, "id");
	$type	 	= xmlParser_getValue ($xmlRequest, "type");
	$pos	 	= xmlParser_getValue ($xmlRequest, "pos");
		
	if ($categoryId == "")
	{
		trigger_error ("חסר מזהה קטגוריה");
	}
	if ($itemId == "")
	{
		trigger_error ("חסר מזהה רכיב");
	}

	if ($pos == "")
		$pos = 1;

	$queryStr	= "select count(*) from categoriesItems where itemId = $itemId and categoryId = $categoryId and type = '$type'";
	$result		= commonDoQuery($queryStr);
	$row		= commonQuery_fetchRow($result);

	if ($row[0] == 0)
	{
		$queryStr   = "update categoriesItems set pos = pos+1 where type = '$type' and categoryId = $categoryId and pos >= $pos";
		commonDoQuery ($queryStr);

		$queryStr	= "insert into categoriesItems (itemId, categoryId, type, pos)
					   values ($itemId, $categoryId, '$type', $pos)";
		commonDoQuery ($queryStr);
	}

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateCategoryItem																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateCategoryItem ($xmlRequest)
{
	$categoryId = xmlParser_getValue ($xmlRequest, "categoryId");
	$itemId 	= xmlParser_getValue ($xmlRequest, "id");
	$type	 	= xmlParser_getValue ($xmlRequest, "type");
	$pos	 	= xmlParser_getValue ($xmlRequest, "pos");

	if ($categoryId == "")
	{
		trigger_error ("חסר מזהה קטגוריה");
	}
	if ($itemId == "")
	{
		trigger_error ("חסר מזהה רכיב");
	}
	if ($pos == "")
	{
		trigger_error ("חסר מיקום הרכיב בקטגוריה");
	}
	
	// get item curr pos
	$queryStr    = "select * from categoriesItems where categoryId=$categoryId and itemId=$itemId";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$currPos	 = $row['pos'];
	$type		 = $row['type'];

	if ($currPos > $pos)
	{
		$queryStr = "update categoriesItems set pos = pos+1 where type = '$type' and categoryId = $categoryId and pos >= $pos and pos < $currPos";
		commonDoQuery ($queryStr);
	}

	if ($currPos < $pos)
	{
		$queryStr = "update categoriesItems set pos = pos-1 where type = '$type' and categoryId = $categoryId and pos > $currPos and pos <= $pos";
//		mail ("liat@interuse.com", "xxx", $queryStr);
		commonDoQuery ($queryStr);
	}

	$queryStr = "update categoriesItems set pos=$pos where type = '$type' and categoryId=$categoryId and itemId=$itemId";
	commonDoQuery ($queryStr);

	$queryStr 	= "select min(pos), max(pos), count(*) from categoriesItems where categoryId = $categoryId";
	$result		= commonDoQuery($queryStr);
	$row		= commonQuery_fetchRow($result);

	if ($row[0] != 1 || $row[1] != $row[2])
	{
		$domainRow = commonGetDomainRow();
		$domainId  = $domainRow['id'];

		$fix	   = "$domainRow[domainName]/fixCatPoses.php";
		mail ("liat@interuse.com", "Category pos problem - domain $domainId", "catId = $categoryId\nitemId = $itemId\n$fix");
	}

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteCategoryItem																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteCategoryItem ($xmlRequest)
{
	$categoryId = xmlParser_getValue ($xmlRequest, "categoryId");
	$type	 	= xmlParser_getValue ($xmlRequest, "type");
	$itemId 	= xmlParser_getValue ($xmlRequest, "itemId");

	if ($categoryId == "" || $itemId == "")
		trigger_error ("חסרים פרטים לביצוע הפעולה");

	$queryStr 	= "select pos from categoriesItems where type = '$type' and categoryId=$categoryId and itemId=$itemId";
	$result   	= commonDoQuery($queryStr);
	$row		= commonQuery_fetchRow($result);

	if ($row)
	{
		$queryStr = "update categoriesItems set pos = pos-1 where type = '$type' and categoryId = $categoryId and pos > $row[pos]";
		commonDoQuery ($queryStr);

		$queryStr 	=  "delete from categoriesItems where type = '$type' and categoryId=$categoryId and itemId=$itemId";
		commonDoQuery ($queryStr);
	}

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getGroups																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function getGroups ($xmlRequest)
{
	// get total
	$queryStr	 = "select count(*) from groups";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$total	     = $row[0];

	// get details
	$queryStr    = "select * from groups order by id desc " . commonGetLimit ($xmlRequest);
	$result	     = commonDoQuery ($queryStr);

	$numRows    = commonQuery_numRows($result);

	$xmlResponse = "<items>";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		$id    		 = $row['id'];
		$description = commonValidXml ($row['description']);

		$xmlResponse .=	"<item>
							<id>$id</id>
							<description>$description</description>
						</item>";
	}

	$xmlResponse .=	"</items>"												.
					commonGetTotalXml($xmlRequest,$numRows,$total);
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* excelReport																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function excelReport ($xmlRequest)
{	
	global $cookie_guiLang;

	$type			= xmlParser_getValue($xmlRequest, "type");
	$withGlobal 	= xmlParser_getValue($xmlRequest, "withGlobal");
	$groupId 		= xmlParser_getValue($xmlRequest, "groupId");
	$isRecursive 	= xmlParser_getValue($xmlRequest, "isRecursive");
	$parentId	 	= xmlParser_getValue($xmlRequest, "parentId");
	$subCategory 	= xmlParser_getValue($xmlRequest, "subCategory");

	if ($withGlobal == "" || $withGlobal == "1")
	{
		$withGlobal = true;
		$condition = "(type='$type' || type = 'global')";
	}
	else
	{
		$withGlobal = false;
		$condition = "type = '$type'";
	}

	if ($groupId != "")
		$condition .= " and groupId = $groupId ";

	if ($isRecursive == "0")
		$isRecursive = false;
	else
		$isRecursive = true;

	if ($parentId == "")
		$parentId = 0;

	if ($subCategory != "")
		$parentId = $subCategory;

	// get num categories
	$queryStr	 = "select count(*) from categories where $condition";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$total	 	 = $row[0];

	$now	= commonPrepareToFile("תאריך הפקת הדוח " . date("d/m/Y H:i"));

    $excel = "<?xml version='1.0' encoding='ISO-8859-8' ?>												
				<Workbook xmlns=\"urn:schemas-microsoft-com:office:spreadsheet\"
				 	      xmlns:o=\"urn:schemas-microsoft-com:office:office\"
						  xmlns:x=\"urn:schemas-microsoft-com:office:excel\"
						  xmlns:ss=\"urn:schemas-microsoft-com:office:spreadsheet\"
						  xmlns:html=\"http://www.w3.org/TR/REC-html40\">
					<OfficeDocumentSettings xmlns=\"urn:schemas-microsoft-com:office:office\">
						<Colors>
							<Color>
								<Index>39</Index>
   								<RGB>#E3E3E3</RGB>
							</Color>
						</Colors>
					</OfficeDocumentSettings>
					<ExcelWorkbook xmlns=\"urn:schemas-microsoft-com:office:excel\">
						<WindowHeight>7860</WindowHeight>
						<WindowWidth>14040</WindowWidth>
			  			<WindowTopX>0</WindowTopX>
			  			<WindowTopY>1905</WindowTopY>
			  			<ProtectStructure>False</ProtectStructure>
			  			<ProtectWindows>False</ProtectWindows>
			 		</ExcelWorkbook>
 					<Styles>
  						<Style ss:ID=\"sTitle\">
							<Alignment ss:Horizontal=\"Center\" ss:Vertical=\"Bottom\"/>
							<Font x:Family=\"Swiss\" ss:Color=\"#0E3966\" ss:Size=\"16\" ss:Bold=\"1\"/>
					  	</Style>
  						<Style ss:ID=\"sSubTitle\">
							<Alignment ss:Horizontal=\"Center\" ss:Vertical=\"Bottom\"/>
							<Font x:Family=\"Swiss\" ss:Color=\"#53B2E8\" ss:Size=\"14\" ss:Bold=\"1\"/>
					  	</Style>
  						<Style ss:ID=\"sInTitle\">
							<Alignment ss:Horizontal=\"Right\" ss:Vertical=\"Bottom\"/>
							<Font x:Family=\"Swiss\" ss:Color=\"#53B2E8\" ss:Size=\"14\" ss:Bold=\"1\"/>
			   				<Borders>
		    					<Border ss:Position=\"Bottom\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
			   				</Borders>
					  	</Style>
  						<Style ss:ID=\"sReportDate\">
							<Alignment ss:Horizontal=\"Center\" ss:Vertical=\"Bottom\"/>
							<Font x:Family=\"Swiss\" ss:Color=\"#ABABAB\" ss:Size=\"12\" ss:Bold=\"1\"/>
					  	</Style>
  						<Style ss:ID=\"sTotal\">
							<Alignment ss:Horizontal=\"Center\" ss:Vertical=\"Bottom\"/>
							<Font x:Family=\"Swiss\" ss:Color=\"#333300\" ss:Size=\"13\" ss:Bold=\"1\"/>
					  	</Style>
 			 			<Style ss:ID=\"sHeader\">
			   				<Alignment ss:Horizontal=\"Center\" ss:Vertical=\"Bottom\"/>
			   				<Borders>
			   					<Border ss:Position=\"Bottom\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Left\"   ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Right\"  ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
			   					<Border ss:Position=\"Top\"    ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		   					</Borders>
			   				<Font x:Family=\"Swiss\" ss:Color=\"#505050\" ss:Bold=\"1\"/>
		   					<Interior ss:Color=\"#EEEEEE\" ss:Pattern=\"Solid\"/>
			  			</Style>
 			 			<Style ss:ID=\"sFooter\">
			   				<Alignment ss:Horizontal=\"Right\" ss:Vertical=\"Bottom\"/>
			   				<Borders>
			   					<Border ss:Position=\"Bottom\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Left\"   ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Right\"  ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
			   					<Border ss:Position=\"Top\"    ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		   					</Borders>
			   				<Font x:Family=\"Swiss\" ss:Color=\"#505050\" ss:Bold=\"1\"/>
		   					<Interior ss:Color=\"#EEEEEE\" ss:Pattern=\"Solid\"/>
			  			</Style>
 			 			<Style ss:ID=\"sFooterLeft\">
			   				<Alignment ss:Horizontal=\"Left\" ss:Vertical=\"Bottom\"/>
			   				<Borders>
			   					<Border ss:Position=\"Bottom\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Left\"   ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Right\"  ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
			   					<Border ss:Position=\"Top\"    ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		   					</Borders>
			   				<Font x:Family=\"Swiss\" ss:Color=\"#505050\" ss:Bold=\"1\"/>
		   					<Interior ss:Color=\"#EEEEEE\" ss:Pattern=\"Solid\"/>
			  			</Style>
			  			<Style ss:ID=\"sCell\">
			   				<Alignment ss:Horizontal=\"Right\" ss:Vertical=\"Bottom\"/>
			   				<Borders>
		    					<Border ss:Position=\"Bottom\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Left\"   ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Right\"  ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
			   				</Borders>
			   				<Font x:Family=\"Swiss\" ss:Bold=\"0\"/>
			 			</Style>
			  			<Style ss:ID=\"sCellEng\">
			   				<Alignment ss:Horizontal=\"Left\" ss:Vertical=\"Bottom\"/>
			   				<Borders>
		    					<Border ss:Position=\"Bottom\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Left\"   ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Right\"  ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
			   				</Borders>
			   				<Font x:Family=\"Swiss\" ss:Bold=\"0\"/>
			 			</Style>
						<Style ss:ID=\"Default\" ss:Name=\"Normal\">
			   				<Alignment ss:Vertical=\"Bottom\"/>
   							<Borders/>
							<Font x:CharSet=\"177\"/>
   							<Interior/>
			   				<NumberFormat/>
			   				<Protection/>
			  			</Style>
						<Style ss:ID=\"s32\">
			   				<Alignment ss:Horizontal=\"Center\" ss:Vertical=\"Bottom\"/>
			  			</Style>
			  			<Style ss:ID=\"s74\">
			   				<Alignment ss:Horizontal=\"Center\" ss:Vertical=\"Bottom\"/>
			  				<Borders>
								<Border ss:Position=\"Bottom\" ss:LineStyle=\"Continuous\" ss:Weight=\"2\"/>
								<Border ss:Position=\"Left\"   ss:LineStyle=\"Continuous\" ss:Weight=\"2\"/>
								<Border ss:Position=\"Right\"  ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
								<Border ss:Position=\"Top\"    ss:LineStyle=\"Continuous\" ss:Weight=\"2\"/>
							</Borders>
							<Font x:CharSet=\"177\" x:Family=\"Swiss\" ss:Color=\"#FFFFFF\" ss:Bold=\"1\"/>
							<Interior ss:Color=\"#969696\" ss:Pattern=\"Solid\"/>
			  			</Style>
			  			<Style ss:ID=\"s75\">
							<Alignment ss:Horizontal=\"Center\" ss:Vertical=\"Bottom\"/>
			   				<Borders>
   								<Border ss:Position=\"Bottom\" ss:LineStyle=\"Continuous\" ss:Weight=\"2\"/>
		    					<Border ss:Position=\"Left\"   ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Right\"  ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Top\"    ss:LineStyle=\"Continuous\" ss:Weight=\"2\"/>
				   			</Borders>
			   				<Font x:CharSet=\"177\" x:Family=\"Swiss\" ss:Color=\"#FFFFFF\" ss:Bold=\"1\"/>
							<Interior ss:Color=\"#969696\" ss:Pattern=\"Solid\"/>
			  			</Style>
			  			<Style ss:ID=\"s76\">
			   				<Alignment ss:Horizontal=\"Center\" ss:Vertical=\"Bottom\"/>
			   				<Borders>
		    					<Border ss:Position=\"Bottom\" ss:LineStyle=\"Continuous\" ss:Weight=\"2\"/>
		    					<Border ss:Position=\"Left\"   ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Right\"  ss:LineStyle=\"Continuous\" ss:Weight=\"2\"/>
		    					<Border ss:Position=\"Top\"    ss:LineStyle=\"Continuous\" ss:Weight=\"2\"/>
			  				</Borders>
			   				<Font x:CharSet=\"177\" x:Family=\"Swiss\" ss:Color=\"#FFFFFF\" ss:Bold=\"1\"/>
			   				<Interior ss:Color=\"#969696\" ss:Pattern=\"Solid\"/>
			  			</Style>
			  			<Style ss:ID=\"s77\">
			  				<Alignment ss:Horizontal=\"Left\" ss:Vertical=\"Bottom\"/>
			   				<Borders>
		    					<Border ss:Position=\"Left\"  ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Right\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
			   				</Borders>
			   				<Font x:Family=\"Swiss\" ss:Color=\"#0000FF\" ss:Bold=\"1\"/>
			  			</Style>
			  			<Style ss:ID=\"s88\">
   							<Font x:Family=\"Swiss\" ss:Color=\"#333300\" ss:Bold=\"1\"/>
			  			</Style>
			  			<Style ss:ID=\"s95\">
			   				<Alignment ss:Horizontal=\"Center\" ss:Vertical=\"Bottom\"/>
			   				<Borders>
		    					<Border ss:Position=\"Bottom\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Left\"   ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Right\"  ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Top\"    ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
			   				</Borders>
			   				<Interior ss:Color=\"#E3E3E3\" ss:Pattern=\"Solid\"/>
			  			</Style>
			  			<Style ss:ID=\"s96\">
			   				<Alignment ss:Horizontal=\"Center\" ss:Vertical=\"Bottom\"/>
			   				<Borders>
		    					<Border ss:Position=\"Bottom\" ss:LineStyle=\"Continuous\" ss:Weight=\"2\"/>
		    					<Border ss:Position=\"Left\"   ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Right\"  ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Top\"    ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
   							</Borders>
			   				<Interior ss:Color=\"#E3E3E3\" ss:Pattern=\"Solid\"/>
			  			</Style>
			  			<Style ss:ID=\"s98\">
			   				<Alignment ss:Horizontal=\"Left\" ss:Vertical=\"Bottom\"/>
			   				<Font x:Family=\"Swiss\" ss:Color=\"#0000FF\" ss:Italic=\"1\"/>
			 			</Style>
			  			<Style ss:ID=\"s99\">
			   				<Alignment ss:Horizontal=\"Left\" ss:Vertical=\"Bottom\"/>
			   				<Font x:Family=\"Swiss\" ss:Color=\"#0000FF\" ss:Italic=\"1\"/>
			   				<NumberFormat ss:Format=\"Short Date\"/>
			  			</Style>
			  			<Style ss:ID=\"s105\">
			   				<Borders>
		    					<Border ss:Position=\"Left\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
			   				</Borders>
			  			</Style>
			  			<Style ss:ID=\"s106\">
			   				<Alignment ss:Horizontal=\"Center\" ss:Vertical=\"Bottom\"/>
			   				<Borders>
		    					<Border ss:Position=\"Bottom\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Left\"   ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Right\"  ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
			   				</Borders>
			   				<Interior ss:Color=\"#E3E3E3\" ss:Pattern=\"Solid\"/>
			  			</Style>
 					</Styles>
					<Worksheet ss:Name=\"דוח חברי הלשכה\" ss:RightToLeft=\"1\">
					<Table x:FullColumns=\"1\" x:FullRows=\"1\">
   	        		<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"120\"/>
   	        		<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"120\"/>
   	        		<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"120\"/>
   	        		<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"120\"/>
   	        		<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"120\"/>
					<Row>
						<Cell ss:MergeAcross=\"3\" ss:StyleID=\"sTitle\"><Data ss:Type=\"String\">דוח קטגוריות</Data></Cell>
					</Row>
					<Row>
						<Cell ss:MergeAcross=\"3\" ss:StyleID=\"sReportDate\"><Data ss:Type=\"String\">$now</Data></Cell>
					</Row>
					<Row>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">רמה 1</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">רמה 2</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">רמה 3</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">רמה 4</Data></Cell>
					</Row>";

	$excel	.= getExcelRows ($type, $parentId, 0, $isRecursive, $withGlobal, $groupId);

	$excel .= 	 "</Table>
				</Worksheet>
			</Workbook>";

	$xmlResponse = commonDoExcel ($excel);

	return ($xmlResponse);
}

function getExcelRows ($type, $parentId, $level, $isRecursive, $withGlobal = true, $groupId = "")
{
	global $usedLangs;

	$langsArray = explode(",",$usedLangs);

	$catType = $type;

	if ($withGlobal)
		$condition = "(type='$catType' || type = 'global')";
	else
		$condition = "type = '$catType'";

	if ($groupId != "")
		$condition .= " and groupId = $groupId " ;

	$queryStr		= "select id, parentId, pos, type, name, shortDescription, categoryFile, sourceFile, countViews
					   from categories sc left join categories_byLang scl on sc.id=scl.categoryId and language = '$langsArray[0]'
					   where parentId = $parentId and $condition
					   order by pos ";
	$result	     	= commonDoQuery ($queryStr);

	$excel			= "";

	while ($row	= commonQuery_fetchRow($result))
	{
		$id				= $row['id'];
		$parentId		= $row['parentId'];
		$name			= $row['name'];

		if ($name == "")
			$name = $row['shortDescription'];

		$name			= commonPrepareToFile($name);

		$excel .= "<Row ss:Height=\"13.5\">" .
					str_repeat("<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\"></Data></Cell>", $level) . "
					<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$name</Data></Cell>" . 
					str_repeat("<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\"></Data></Cell>", 4 - $level - 1) . "
				   </Row>";

		if ($isRecursive)
			$excel	.= getExcelRows ($type, $id, $level+1, $isRecursive, $withGlobal, $groupId);
	}

	return $excel;
}

?>
