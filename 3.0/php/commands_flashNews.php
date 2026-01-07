<?php

$tags 	  = Array("id", "type", "pos", "urlOrPageId", "titleColor", "contentColor", "status");
$langTags = Array("title", "content");

/* ----------------------------------------------------------------------------------------------------	*/
/* getFlashNews																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function getFlashNews ($xmlRequest)
{	
	global $usedLangs;
	$langsArray = explode(",",$usedLangs);

	$condition  = commonAddIbosUserCondition("flashNews");

	$status		= xmlParser_getValue($xmlRequest, "status");
	if ($status != "")
		$condition .= " and status = '$status' ";

	// get total
	$queryStr	 = "select count(*) from flashNews 
					where 1 $condition";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$total	     = $row[0];

	// get details
	$queryStr = "select distinct flashNews.*, flashNews_byLang.title, 
						forum.name as forumName, pages.staticname as staticname, pages_byLang.title as pageTitle
				 from flashNews
				 left join flashNews_byLang on flashNews.id = flashNews_byLang.newsId and flashNews_byLang.language = '$langsArray[0]'
				 left join pages on flashNews.urlOrPageId = pages.id
				 left join forum on flashNews.urlOrPageId = forum.id
				 left join pages_byLang on flashNews.urlOrPageId = pages_byLang.pageId  
						 				  and pages_byLang.language = '$langsArray[0]'
				 where 1 $condition
				 order by pos " . commonGetLimit ($xmlRequest);

	$result	     = commonDoQuery ($queryStr);

	$numRows    = commonQuery_numRows($result);

	$xmlResponse = "<items>";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		$id   		  = $row['id'];
		$pos   		  = $row['pos'];
		$type  		  = $row['type'];
		$typeText	  = formatNewsType($type);
		$status 	  = formatNewsStatus($row['status']);
		$title		  = commonValidXml($row['title']);
		$urlOrPageId  = commonValidXml ($row['urlOrPageId'],true);

		if ($row['type'] == "forum")
		{
			$urlOrPageId = $row['urlOrPageId'] . " - " . commonValidXml ($row['forumName']);
		}
		else if ($row['type'] == "file")
		{
			$urlOrPageId = $row['urlOrPageId'];
		}
		else if ($row['type'] == "")
		{
			$urlOrPageId = "";
		}
		else if ($row['type'] != "url")
		{
			if ($row['pageTitle'] != "")
				$urlOrPageId = $row['urlOrPageId'] . " - " . commonValidXml ($row['pageTitle']);
			else
				$urlOrPageId = $row['urlOrPageId'] . " - " . commonValidXml ($row['staticname']);
		}

		$titleColor		= $row['titleColor'];
		$contentColor	= $row['contentColor'];

		$xmlResponse .=	"<item>
							 <newsId>$id</newsId>
				 			 <type>$type</type>
							 <typeText>$typeText</typeText>
							 <status>$status</status>
				 			 <title>$title</title>
							 <urlOrPageId>$urlOrPageId</urlOrPageId>
							 <pos>$pos</pos>
							 <titleColor>$titleColor</titleColor>
							 <contentColor>$contentColor</contentColor>
						 </item>";
	}

	$xmlResponse .=	"</items>" .
					commonGetTotalXml($xmlRequest,$numRows,$total);
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getFlashNewsDetails																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function getFlashNewsDetails ($xmlRequest)
{
	global $usedLangs, $tags, $langTags;

	$id		= xmlParser_getValue($xmlRequest, "id");

	if ($id == "")
		trigger_error ("חסר קוד חדשה לביצוע הפעולה");


	$queryStr = "select * from flashNews, flashNews_byLang
				 where flashNews.id = flashNews_byLang.newsId
				 and   flashNews.id = $id";
	$result   = commonDoQuery ($queryStr);

	if (commonQuery_numRows($result) == 0)
		trigger_error ("קוד זה ($id) לא קיים במערכת. לא ניתן לבצע את הפעולה");

	$langsArray = explode(",",$usedLangs);

	$xmlResponse = "";

	while ($row = commonQuery_fetchRow($result))
	{
		$language = $row['language'];

		$langsArray = commonArrayRemove ($langsArray, $language);	

		if ($xmlResponse == "")
		{
			for ($i=0; $i < count($tags); $i++)
			{
				eval ("\$$tags[$i] = \$row['$tags[$i]'];");

			}

			$url		 = "";
			$page		 = "";
		
			if ($type == "url")
				$url  = commonValidXml($urlOrPageId);
			else
				$page = $urlOrPageId;
		
			$xmlResponse .= "<newsId>$id</newsId>
							 <url>$url</url>
							 <page>$page</page>";

			for ($i=0; $i < count($tags); $i++)
			{
				eval ("\$$tags[$i] = commonValidXml(\$$tags[$i]);");

				eval ("\$xmlResponse .= \"<$tags[$i]>\$$tags[$i]</$tags[$i]>\";");
			}
		}

		for ($i=0; $i < count($langTags); $i++)
		{
			eval ("\$$langTags[$i] = commonValidXml(\$row['$langTags[$i]']);");
			eval ("\$xmlResponse .=	\"<$langTags[$i]\$language>\$$langTags[$i]</$langTags[$i]\$language>\";");
		}
	}

	// add missing languages
	// ------------------------------------------------------------------------------------------------
	for ($i=0; $i<count($langsArray); $i++)
	{
		$language	  = $langsArray[$i];

		for ($j=0; $j < count($langTags); $j++)
		{
			eval ("\$xmlResponse .=	\"<$langTags[$j]\$language><![CDATA[]]></$langTags[$j]\$language>\";");
		}
	}

	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* addFlashNews																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function addFlashNews ($xmlRequest)
{
	return (editFlashNews ($xmlRequest, "add"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* doesFlashNewsExist																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function doesFlashNewsExist ($id)
{
	$queryStr		= "select count(*) from flashNews where id=$id";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$count	     = $row[0];

	return ($count > 0);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getFlashNewsNextId																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function getFlashNewsNextId ()
{
	$queryStr	= "select max(id) from flashNews";
	$result		= commonDoQuery ($queryStr);
	$row		= commonQuery_fetchRow ($result);
	$id 		= $row[0] + 1;
	
	return "<newsId>$id</newsId>";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateFlashNews																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateFlashNews ($xmlRequest)
{
	editFlashNews ($xmlRequest, "update");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* editFlashNews																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function editFlashNews ($xmlRequest, $editType)
{
	global $usedLangs, $tags, $langTags;
	global $userId;

	for ($i=0; $i < count($tags); $i++)
	{
		eval ("\$$tags[$i] = commonDecode(xmlParser_getValue(\$xmlRequest,\"$tags[$i]\"));");	
	}

	$id   = xmlParser_getValue($xmlRequest, "newsId");

	if ($editType == "add")
	{
		$queryStr	= "select max(id) from flashNews";
		$result		= commonDoQuery ($queryStr);
		$row		= commonQuery_fetchRow ($result);
		$id 		= $row[0] + 1;
	}

	$page = xmlParser_getValue($xmlRequest, "page");
	$url  = addslashes(commonDecode(xmlParser_getValue($xmlRequest, "url")));

	if ($type == "url" || $type == "onclick")
		$urlOrPageId = $url;
	else
		$urlOrPageId = $page;

	$vals = Array();

	for ($i=0; $i < count($tags); $i++)
	{
		eval ("array_push (\$vals,\$$tags[$i]);");
	}
	
	if ($editType == "update")
	{
		if ($id == "")
			trigger_error ("חסר קוד חדשה לביצוע הפעולה");

		if (!doesFlashNewsExist($id))
		{
			trigger_error ("חדשה עם קוד זה ($id) לא קיים במערכת. לא ניתן לבצע את העדכון");
		}
		
		// get curr pos
		$queryStr    = "select pos from flashNews where id=$id";
		$result	     = commonDoQuery ($queryStr);
		$row	     = commonQuery_fetchRow($result);
		$currPos	 = $row[0];

		if ($currPos > $pos)
		{
			$queryStr = "update flashNews set pos = pos+1 where pos >= $pos and pos < $currPos";
			$result	     = commonDoQuery ($queryStr);
		}

		if ($currPos < $pos)
		{
			$queryStr = "update flashNews set pos = pos-1 where pos > $currPos and pos <= $pos";
			$result	     = commonDoQuery ($queryStr);
		}

		$queryStr = "update flashNews set ";

		for ($i=1; $i < count($tags); $i++)
		{
			$queryStr .= "$tags[$i] = '$vals[$i]',";
		}

		$queryStr = trim($queryStr, ",");

		$queryStr .= " where id = $id ";

		commonDoQuery ($queryStr);

	}
	else
	{
		$queryStr = "update flashNews set pos = pos+1 where pos >= $pos";
		commonDoQuery ($queryStr);

		$queryStr = "insert into flashNews (" . join(",",$tags) . ") values ('" . join("','",$vals) . "')";
		commonDoQuery ($queryStr);
	}

	# add languages rows for this user
	# ------------------------------------------------------------------------------------------------------
	$langsArray = explode(",",$usedLangs);

	for ($i=0; $i<count($langsArray); $i++)
	{
		$language		= $langsArray[$i];

		$vals = Array();
		for ($j=0; $j < count($langTags); $j++)
		{
			eval ("\$$langTags[$j] = addslashes(commonDecode(xmlParser_getValue(\$xmlRequest,\"$langTags[$j]\$language\")));");	
			eval ("array_push (\$vals,\$$langTags[$j]);");
		}		

		if ($editType == "add")
		{
			$queryStr		= "insert into flashNews_byLang (newsId, language," . join(",",$langTags) . ") 
							   values ($id, '$language', '" . join ("','", $vals) . "')";
	
			commonDoQuery ($queryStr);
		}
		else
		{
			$queryStr = "update flashNews_byLang set ";

			for ($j=0; $j < count($langTags); $j++)
			{
				$queryStr .= "$langTags[$j] = '$vals[$j]',";
			}

			$queryStr = trim($queryStr, ",");

			$queryStr .= " where newsId = $id and language = '$language'";

			commonDoQuery ($queryStr);
		}
	}
	
	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteFlashNews																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteFlashNews ($xmlRequest)
{
	$id = xmlParser_getValue($xmlRequest, "id");

	if ($id == "")
		trigger_error ("חסר קוד חדשה לביצוע הפעולה");

	$queryStr = "delete from flashNews where id = $id";
	commonDoQuery ($queryStr);

	$queryStr = "delete from flashNews_byLang where newsId = $id";
	commonDoQuery ($queryStr);

	return "";	
}

?>
