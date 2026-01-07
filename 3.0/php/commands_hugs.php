<?php

$tags 	  		= Array("id", "status", "contactPhone1", "contactPhone2", "fax", "email", "siteUrl", "link1", "link2", "link3");
$langTags 		= Array("name", "description", "place", "instructor", "remarks", "activityHours", "contactName1", "contactName2", "contactDetails",
				 		"priceText", "linkName1", "linkName2", "linkName3");
$pageTags 		= Array("id", "layoutId", "membersOnly", "navParentId");

/* ----------------------------------------------------------------------------------------------------	*/
/* getHugs																								*/
/* ----------------------------------------------------------------------------------------------------	*/
function getHugs ($xmlRequest)
{	
	global $usedLangs;
	$langsArray = explode(",",$usedLangs);

	$condition  = "";

	// status
	$status		= xmlParser_getValue($xmlRequest, "status");
	if ($status != "")
		$condition = " and status = '$status' ";

	// categoryId
	$categoryId		= xmlParser_getValue($xmlRequest, "categoryId");
	if ($categoryId != "")
		$condition = " and categoriesItems.categoryId = '$categoryId' ";

	// name
	$name		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "name")));
	if ($name != "")
		$condition .= " and name like '%$name%' ";

	// get total
	$queryStr	 = "select count(distinct hugs.id)
					from (hugs, hugs_byLang)
					left join categoriesItems on hugs.id = categoriesItems.itemId and categoriesItems.type = 'hug'
					where hugs.id = hugs_byLang.hugId and hugs_byLang.language = '$langsArray[0]'
					$condition";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$total	     = $row[0];

	// get details
	$queryStr 	 = "select distinct hugs.*, hugs_byLang.* 
					from (hugs, hugs_byLang)
					left join categoriesItems on hugs.id = categoriesItems.itemId and categoriesItems.type = 'hug'
					where hugs.id = hugs_byLang.hugId and hugs_byLang.language = '$langsArray[0]'
				    $condition
				    order by id desc " . commonGetLimit ($xmlRequest);

	$result	     = commonDoQuery ($queryStr);

	$numRows    = commonQuery_numRows($result);

	$xmlResponse = "<items>";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		$id   		  = $row['id'];
		$status 	  = formatActiveStatus($row['status']);
		$name		  = commonValidXml($row['name']);

		$xmlResponse .=	"<item>
							 <hugId>$id</hugId>
				 			 <name>$name</name>
							 <status>$status</status>
						 </item>";
	}

	$xmlResponse .=	"</items>" .
					commonGetTotalXml($xmlRequest,$numRows,$total);
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getHugDetails																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getHugDetails ($xmlRequest)
{
	global $usedLangs, $tags, $langTags, $pageTags;

	$id		= xmlParser_getValue($xmlRequest, "hugId");

	if ($id == "")
		trigger_error ("חסר קוד חוג לביצוע הפעולה");


	$queryStr = "select hugs.*, hugs_byLang.*, pages.layoutId, pages.membersOnly, pages.navParentId
				 from hugs, hugs_byLang, pages
				 where hugs.id = hugs_byLang.hugId
				 and   hugs.id = pages.id
				 and   hugs.id = $id";
	$result   = commonDoQuery ($queryStr);

	if (commonQuery_numRows($result) == 0)
		trigger_error ("חוג קוד זה ($id) לא קיים במערכת. לא ניתן לבצע את הפעולה");

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

			for ($i=0; $i < count($tags); $i++)
			{
				eval ("\$$tags[$i] = commonValidXml(\$$tags[$i]);");

				eval ("\$xmlResponse .= \"<$tags[$i]>\$$tags[$i]</$tags[$i]>\";");
			}
			
			for ($i=1; $i < count($pageTags); $i++)
			{
				eval ("\$$pageTags[$i] = \$row['$pageTags[$i]'];");
				eval ("\$$pageTags[$i] = commonValidXml(\$$pageTags[$i]);");

				eval ("\$xmlResponse .= \"<$pageTags[$i]>\$$pageTags[$i]</$pageTags[$i]>\";");
			}
			
			$xmlResponse .= "<hugId>$id</hugId>";
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
/* addHug																								*/
/* ----------------------------------------------------------------------------------------------------	*/
function addHug ($xmlRequest)
{
	return (editHug ($xmlRequest, "add"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* doesHugsExist																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function doesHugsExist ($id)
{
	$queryStr		= "select count(*) from hugs where id=$id";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$count	     = $row[0];

	return ($count > 0);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateHug																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateHug ($xmlRequest)
{
	editHug ($xmlRequest, "update");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* editHug																								*/
/* ----------------------------------------------------------------------------------------------------	*/
function editHug ($xmlRequest, $editType)
{
	global $usedLangs, $tags, $langTags, $pageTags;

	for ($i=0; $i < count($pageTags); $i++)
	{
		eval ("\$$pageTags[$i] = commonDecode(xmlParser_getValue(\$xmlRequest,\"$pageTags[$i]\"));");	
	}


	for ($i=0; $i < count($tags); $i++)
	{
		eval ("\$$tags[$i] = commonDecode(xmlParser_getValue(\$xmlRequest,\"$tags[$i]\"));");	
	}

	$id   = xmlParser_getValue($xmlRequest, "hugId");

	if ($editType == "add")
	{
		$queryStr	= "select max(id) from pages";
		$result		= commonDoQuery ($queryStr);
		$row		= commonQuery_fetchRow ($result);
		$id 		= $row[0] + 1;
	}

	$pageVals = Array();

	for ($i=0; $i < count($pageTags); $i++)
	{
		eval ("array_push (\$pageVals,\$$pageTags[$i]);");
	}
	
	$vals = Array();

	for ($i=0; $i < count($tags); $i++)
	{
		eval ("array_push (\$vals,\$$tags[$i]);");
	}
	
	if ($status == "active")
		$isReady = "1";
	else
		$isReady = "0";

	if ($editType == "update")
	{
		if ($id == "")
			trigger_error ("חסר קוד חוג לביצוע הפעולה");

		if (!doesHugsExist($id))
		{
			trigger_error ("חוג עם קוד זה ($id) לא קיים במערכת. לא ניתן לבצע את העדכון");
		}
		
		// pages table
		$queryStr = "update pages set ";

		for ($i=1; $i < count($pageTags); $i++)
		{
			$queryStr .= "$pageTags[$i] = '$pageVals[$i]',";
		}

		$queryStr = trim($queryStr, ",");

		$queryStr .= " where id = $id ";

		commonDoQuery ($queryStr);

		// update hugs tables
		$queryStr = "update hugs set ";

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
		$categoryId = xmlParser_getValue($xmlRequest, "categoryId");

		if ($categoryId != "")
		{
			// get last pos
			$queryStr = "select max(pos) from categoriesItems where categoryId = $categoryId and type = 'hug'";
			$result		= commonDoQuery ($queryStr);
			$row		= commonQuery_fetchRow ($result);
			$pos 		= $row[0] + 1;

			$queryStr = "insert into categoriesItems (itemId, categoryId, type, pos)
						 values ($id, $categoryId, 'hug', $pos)";
			commonDoQuery ($queryStr);
		}

		// pages table
		$queryStr = "insert into pages (" . join(",",$pageTags) . ",type) values ('" . join("','",$pageVals) . "','hug')";
		commonDoQuery ($queryStr);

		// hugs table
		$queryStr = "insert into hugs (" . join(",",$tags) . ") values ('" . join("','",$vals) . "')";
		commonDoQuery ($queryStr);
	}

	# delete all languages rows
	# ------------------------------------------------------------------------------------------------------
	$queryStr = "delete from hugs_byLang where hugId='$id'";
	commonDoQuery ($queryStr);
	
	$queryStr = "delete from pages_byLang where pageId='$id'";
	commonDoQuery ($queryStr);

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

		$navTitle = addslashes(commonDecode(xmlParser_getValue($xmlRequest, "navTitle$language")));

		// pages by lang table
		$queryStr	= "insert into pages_byLang (pageId, winTitle, title, language, isReady, navTitle) 
					   values ('$id','$name', '$name', '$language', '$isReady', '$navTitle')";
		commonDoQuery ($queryStr);

		$queryStr		= "insert into hugs_byLang (hugId, language," . join(",",$langTags) . ") 
						   values ($id, '$language', '" . join ("','", $vals) . "')";
	
		commonDoQuery ($queryStr);
	}
	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteHug																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteHug ($xmlRequest)
{
	$id = xmlParser_getValue($xmlRequest, "hugId");

	if ($id == "")
		trigger_error ("חסר קוד חוג לביצוע הפעולה");

	$queryStr = "delete from pages where id = $id";
	commonDoQuery ($queryStr);

	$queryStr = "delete from pages_byLang where pageId = $id";
	commonDoQuery ($queryStr);

	$queryStr = "delete from hugs where id = $id";
	commonDoQuery ($queryStr);

	$queryStr = "delete from hugs_byLang where hugId = $id";
	commonDoQuery ($queryStr);

	return "";	
}

?>
