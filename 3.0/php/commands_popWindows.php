<?php

$tags	    = Array("id", "type", "status", "counter", "counterBefore", "showOnPageId", "popPageId", "popWidth", "popHeight", "popLeft", "popTop");


/* ----------------------------------------------------------------------------------------------------	*/
/* getPopWindows																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getPopWindows ($xmlRequest)
{
	global $usedLangs;
	$langsArray = explode(",",$usedLangs);

	// conditions
	$condition  = "";

	// - popWindow id
	$id	= xmlParser_getValue($xmlRequest, "id");
	if ($id != "")
		$condition = " and popWindows.id = $id";

	// - type
	$type	= xmlParser_getValue($xmlRequest, "type");
	if ($type != "")
		$condition .= " and popWindows.type = '$type' ";

	// - showOnPageId
	$showOnPageId	= xmlParser_getValue($xmlRequest, "showOnPageId");
	if ($showOnPageId != "")
		$condition .= " and popWindows.showOnPageId = $showOnPageId ";

	
	// get total
	$queryStr	 = "select count(*) from popWindows where 1 $condition";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$total	     = $row[0];

	// get details
	$queryStr    = "select popWindows.*, popPages.title as popPage, showOnPages.staticname as showOnPage
					from popWindows
			   		left join pages showOnPages on popWindows.showOnPageId = showOnPages.id
			   		left join pages_byLang popPages on popWindows.popPageId = popPages.pageId and language = '$langsArray[0]' 
					where 1 $condition
					order by popWindows.id desc " . commonGetLimit ($xmlRequest);
	$result	     = commonDoQuery ($queryStr);

	$numRows    = commonQuery_numRows($result);

	$xmlResponse = "<items>";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		$id   			= $row['id'];
		$popPage   		= $row['popPage'];
		$showOnPage   	= $row['showOnPage'];
		$type			= formatPopType   ($row['type']);

		if ($showOnPage == "") $showOnPage = commonPhpEncode("בכל הדפים");

		$xmlResponse .=	"<item>
							<id>$id</id>
							<popPage>$popPage</popPage>
							<showOnPage>$showOnPage</showOnPage>
							<type>$type</type>
						 </item>";
	}

	$xmlResponse .=	"</items>"												.
					commonGetTotalXml($xmlRequest,$numRows,$total);
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getPopWindowNextId																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function getPopWindowNextId ($xmlRequest)
{
	$queryStr	= "select max(id) from popWindows";
	$result		= commonDoQuery ($queryStr);
	$row		= commonQuery_fetchRow ($result);
	$id 		= $row[0] + 1;
	
	return "<id>$id</id>";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getPopWindowDetails																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function getPopWindowDetails ($xmlRequest)
{
	global $tags;

	$id		= xmlParser_getValue($xmlRequest, "id");
	$action	= xmlParser_getValue($xmlRequest, "action");

	if ($id == "")
		trigger_error ("חסר קוד חלון קופץ לביצוע הפעולה");

	$queryStr   = "select * from popWindows where id = $id";
	$result		= commonDoQuery ($queryStr);

	if (commonQuery_numRows($result) == 0)
		trigger_error ("חלון קופץ קוד זה ($id) לא קיים במערכת. לא ניתן לבצע את הפעולה");

	$xmlResponse = "";

	$row = commonQuery_fetchRow($result);
	
	if ($action == "duplicate")
	{
		$queryStr	= "select max(id) from popWindows";
		$result2	= commonDoQuery ($queryStr);
		$row2		= commonQuery_fetchRow ($result2);
		$row['id']	= $row2[0] + 1;
	}

	if ($row['counter']  	 	 == "0") $row['counter']  	 	 = "";
	if ($row['counterBefore'] 	 == "0") $row['counterBefore']   = "";
	if ($row['popWidth']  		 == "0") $row['popWidth'] 	  	 = "";
	if ($row['popHeight'] 	 	== "0") $row['popHeight'] 	  	 = "";
	if ($row['showOnPageId'] 	== "0") $row['showOnPageId'] 	 = "";

	for ($i=0; $i < count($tags); $i++)
	{
		eval ("\$$tags[$i] = \$row['$tags[$i]'];");
		eval ("\$$tags[$i] = commonValidXml(\$$tags[$i]);");
		eval ("\$xmlResponse .= \"<$tags[$i]>\$$tags[$i]</$tags[$i]>\";");
	}

	return $xmlResponse;
}

/* ----------------------------------------------------------------------------------------------------	*/
/* addPopWindow																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function addPopWindow ($xmlRequest)
{
	return (editPopWindow ($xmlRequest, "add"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updatePopWindow																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function updatePopWindow ($xmlRequest)
{
	return (editPopWindow ($xmlRequest, "update"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* doesPopWindowExist																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function doesPopWindowExist ($id)
{
	$queryStr		= "select count(*) from popWindows where id=$id";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$count	     = $row[0];

	return ($count > 0);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* editPopWindow																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function editPopWindow ($xmlRequest, $editType)
{
	global $tags;

	for ($i=0; $i < count($tags); $i++)
	{
		eval ("\$$tags[$i] = commonDecode(xmlParser_getValue(\$xmlRequest,\"$tags[$i]\"));");	
	}

	if ($showOnPageId == "") $showOnPageId = 0;

	if ($type != "chooseLang")
	{
		// check there is no pop for the same page
		$queryStr = "select count(*) from popWindows 
					 where (showOnPageId = $showOnPageId or showOnPageId = 0) and id != $id and status = 'active' and type != 'chooseLang'";
		$result	     = commonDoQuery ($queryStr);
		$row	     = commonQuery_fetchRow($result);

		if ($row[0] > 0)
		{
			trigger_error ("לא ניתן להגדיר חלון קופץ נוסף לדף זה");
		}
	}

	$vals = Array();

	for ($i=0; $i < count($tags); $i++)
	{
		eval ("array_push (\$vals,\$$tags[$i]);");
	}
	
	if ($editType == "update")
	{
		if ($id == "")
			trigger_error ("חסר קוד חלון קופץ לביצוע הפעולה");

		if (!doesPopWindowExist($id))
		{
			trigger_error ("חלון קופץ עם קוד זה ($id) לא קיים במערכת. לא ניתן לבצע את העדכון");
		}
		
		$queryStr = "update popWindows set ";

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
		$queryStr = "insert into popWindows (" . join(",",$tags) . ") values ('" . join("','",$vals) . "')";
		commonDoQuery ($queryStr);
	}

	return "<id>$id</id>";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deletePopWindow																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function deletePopWindow ($xmlRequest)
{
	$id = xmlParser_getValue ($xmlRequest, "id");

	if ($id == "")
		trigger_error ("חסר קוד חלון קופץ לביצוע הפעולה");

	$queryStr = "delete from popWindows where id = $id";
	commonDoQuery ($queryStr);

	return "";
}

?>
