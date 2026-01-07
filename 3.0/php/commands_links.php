<?php

/* ----------------------------------------------------------------------------------------------------	*/
/* getLinks																								*/
/* ----------------------------------------------------------------------------------------------------	*/
function getLinks ($xmlRequest)
{	
	global $usedLangs;
	$langsArray = explode(",",$usedLangs);

	$condition  = "";

	$boxId		= xmlParser_getValue($xmlRequest, "boxId");

	if ($boxId != "")
		$condition = " where links.boxId = $boxId";

	// get total
	$queryStr	 = "select count(*) from links $condition";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$total	     = $row[0];

	// get details
	$queryStr    = "select links.id as id, links_byLang.name as name, links.type as type, links.boxId as boxId,
						   pos, urlOrPageId, boxes.boxName, forum.name as forumName, pages.staticname as staticname
					from links
					left join pages on links.urlOrPageId = pages.id
					left join forum on links.urlOrPageId = forum.id
					left join pages_byLang on links.urlOrPageId = pages_byLang.pageId  
						 				  and pages_byLang.language = '$langsArray[0]'
					left join links_byLang on links.id = links_byLang.linkId and links_byLang.language = '$langsArray[0]'
					left join boxes on links.boxId = boxes.id
					$condition
					order by boxes.id, pos " . commonGetLimit ($xmlRequest);
	$result	     = commonDoQuery ($queryStr);

	$numRows    = commonQuery_numRows($result);

	$xmlResponse = "<items>";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		$id   		  = $row['id'];
		$name 		  = commonValidXml ($row['name'],true);
		$type		  = $row['type'];
		$typeText	  = formatLinkType ($type);
		$pos   		  = $row['pos'];
		$urlOrPageId  = commonValidXml ($row['urlOrPageId'],true);
		$boxId		  = $row['boxId'];
		$boxName	  = commonValidXml ($row['boxName'],true);

		if ($row['type'] == "page")
			$urlOrPageId = $row['urlOrPageId'] . " - " . commonValidXml ($row['staticname']);

		if ($row['type'] == "forum")
			$urlOrPageId = $row['urlOrPageId'] . " - " . commonValidXml ($row['forumName']);

		if ($pos == 0) 
			$pos = "";

		$xmlResponse .=	"<item>"											.
							"<id>$id</id>"	 								. 
							"<name>$name</name>"							. 
							"<type>$type</type>"							. 
							"<typeText>$typeText</typeText>"				. 
							"<pos>$pos</pos>"								. 
							"<urlOrPageId>$urlOrPageId</urlOrPageId>"		.
							"<boxId>$boxId</boxId>"							.
							"<boxName>$boxName</boxName>"					.
						"</item>";
	}

	$xmlResponse .=	"</items>"												.
					commonGetTotalXml($xmlRequest,$numRows,$total);
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* addLink																								*/
/* ----------------------------------------------------------------------------------------------------	*/
function addLink ($xmlRequest)
{
	return (editLink ($xmlRequest, "add"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* doesLinkExist																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function doesLinkExist ($id)
{
	$queryStr		= "select count(*) from links where id=$id";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$count	     = $row[0];

	return ($count > 0);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getLinkNextId																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getLinkNextId ($xmlRequest)
{
	$queryStr	= "select max(id) from links";
	$result		= commonDoQuery ($queryStr);
	$row		= commonQuery_fetchRow ($result);
	$id 		= $row[0] + 1;
	
	return "<id>$id</id>";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getLinkDetails																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getLinkDetails ($xmlRequest)
{
	global $usedLangs;

	$id		= xmlParser_getValue($xmlRequest, "id");

	if ($id == "")
		trigger_error ("חסר קוד קישור לביצוע הפעולה");

	$queryStr	= "select links.*, language, name, title 
				   from links 
				   left join links_byLang on id=linkId
				   where id=$id";
	$result		= commonDoQuery ($queryStr);

	if (commonQuery_numRows($result) == 0)
		trigger_error ("קישור קוד זה ($id) לא קיים במערכת. לא ניתן לבצע את העדכון");

	$langsArray = explode(",",$usedLangs);

	$xmlResponse = "";
	
	while ($row = commonQuery_fetchRow($result))
	{
		$language = $row['language'];

		$langsArray = commonArrayRemove ($langsArray, $language);	

		if ($xmlResponse == "")
		{
			$id			 = $row['id'];
			$boxId		 = $row['boxId'];
			$pos		 = $row['pos'];
			$type		 = $row['type'];

			$urlOrPageId = commonValidXml($row['urlOrPageId']);
			$url		 = "";
			$page		 = "";
		
			if ($type == "url" || $type == "onclick")
				$url  = $urlOrPageId;
			else
				$page = $urlOrPageId;
		
			$isNewWin		= $row['isNewWin'];
			$isBlink		= $row['isBlink'];
			$isUniqueStyle	= $row['isUniqueStyle'];
		
			$xmlResponse	= 	"<id>$id</id>
								 <boxId>$boxId</boxId>
								 <pos>$pos</pos>
								 <type>$type</type>
								 <url>$url</url>
								 <page>$page</page>
								 <isNewWin>$isNewWin</isNewWin>
								 <isBlink>$isBlink</isBlink>
								 <isUniqueStyle>$isUniqueStyle</isUniqueStyle>";
		}

		$name		  = commonValidXml($row['name']);
		$title		  = commonValidXml($row['title']);

		$xmlResponse .= "<name$language>$name</name$language>"		. 
						"<title$language>$title</title$language>";
	}

	// add missing languages
	// ------------------------------------------------------------------------------------------------
	for ($i=0; $i<count($langsArray); $i++)
	{
		$language	  = $langsArray[$i];

		$xmlResponse .=	   "<name$language><![CDATA[]]></name$language>
							<title$language><![CDATA[]]></title$language>";
	}

	return $xmlResponse;
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateLink																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateLink ($xmlRequest)
{
	editLink ($xmlRequest, "update");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* editLink																								*/
/* ----------------------------------------------------------------------------------------------------	*/
function editLink ($xmlRequest, $editType)
{
	global $usedLangs;

	$id		= xmlParser_getValue($xmlRequest, "id");

	if ($id == "")
		trigger_error ("חסר קוד קישור לביצוע הפעולה");

	if ($editType == "add")
	{
		if (doesLinkExist($id))
		{
			trigger_error ("קישור עם קוד זהה ($id) כבר קיים במערכת");
		}
	}
	else	// update link
	{
		if (!doesLinkExist($id))
		{
			trigger_error ("קישור עם קוד זה ($id) לא קיים במערכת. לא ניתן לבצע את העדכון");
		}
	}

	# delete all languages rows
	# ------------------------------------------------------------------------------------------------------
	$queryStr = "delete from links_byLang where linkId='$id'";
	commonDoQuery ($queryStr);
	
	# add languages rows for this link
	# ------------------------------------------------------------------------------------------------------
	$langsArray = explode(",",$usedLangs);

	for ($i=0; $i<count($langsArray); $i++)
	{
		$language		= $langsArray[$i];

		$name			= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "name$language")));
		$title			= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "title$language")));

		$queryStr		= "insert into links_byLang (linkId, language, name, title)
						   values ('$id','$language','$name','$title')";
	
		commonDoQuery ($queryStr);
	}

	$boxId			= xmlParser_getValue($xmlRequest, "boxId");
	$pos			= xmlParser_getValue($xmlRequest, "pos");
	$type			= xmlParser_getValue($xmlRequest, "type");
	$page			= xmlParser_getValue($xmlRequest, "page");
	$url			= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "url")));
	$isNewWin		= xmlParser_getValue($xmlRequest, "isNewWin");
	$isBlink		= xmlParser_getValue($xmlRequest, "isBlink");
	$isUniqueStyle	= xmlParser_getValue($xmlRequest, "isUniqueStyle");

	if ($type == "url" || $type == "onclick")
		$urlOrPageId = $url;
	else
		$urlOrPageId = $page;


	if ($editType == "add")
	{
		$queryStr = "update links set pos = pos+1 where boxId = $boxId and pos >= $pos";
		commonDoQuery ($queryStr);

		$queryStr = "insert into links 
						(id,  boxId,    pos,    type,    urlOrPageId,    isNewWin, isBlink, isUniqueStyle) values 
						($id, $boxId, '$pos', '$type', '$urlOrPageId', '$isNewWin', '$isBlink', '$isUniqueStyle')";
	}
	else // update
	{
		// get curr link pos
		$queryStr    = "select pos from links where id=$id";
		$result	     = commonDoQuery ($queryStr);
		$row	     = commonQuery_fetchRow($result);
		$currPos	 = $row[0];

		if ($currPos > $pos)
		{
			$queryStr = "update links set pos = pos+1 where boxId = $boxId and pos >= $pos and pos < $currPos";
			$result	     = commonDoQuery ($queryStr);
		}

		if ($currPos < $pos)
		{
			$queryStr = "update links set pos = pos-1 where boxId = $boxId and pos > $currPos and pos <= $pos";
			$result	     = commonDoQuery ($queryStr);
		}

		$queryStr = "update links set 	boxId			= '$boxId',
										pos				= '$pos',		
										type			= '$type',		
										urlOrPageId		= '$urlOrPageId',		
										isNewWin		= '$isNewWin',		
										isBlink			= '$isBlink',		
										isUniqueStyle	= '$isUniqueStyle'		
					 where id=$id";
	}

	commonDoQuery ($queryStr);

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* duplicateLink																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function duplicateLink ($xmlRequest)
{
	$id		= xmlParser_getValue($xmlRequest, "id");
	$boxId	= xmlParser_getValue($xmlRequest, "boxId");


	if ($id == "")
		trigger_error ("חסר קוד קישור לביצוע הפעולה");

	if ($boxId == "")
		trigger_error ("חסר קוד תיבה לביצוע הפעולה");

	# get link details
	# ------------------------------------------------------------------------------------------------------
	$queryStr 	 	= "select * from links where id=$id";
	$result		 	= commonDoQuery ($queryStr);
	$row	     	= commonQuery_fetchRow($result);
	$type		 	= $row['type'];
	$urlOrPageId 	= $row['urlOrPageId'];
	$isNewWin	 	= $row['isNewWin'];
	$isBlink	 	= $row['isBlink'];
	$isUniqueStyle	= $row['isUniqueStyle'];

	# get last pos on this box id
	# ------------------------------------------------------------------------------------------------------
	$queryStr 	 = "select max(pos) from links where boxId=$boxId";
	$result	     = commonDoQuery ($queryStr);
	$pos		 = 1;
	if (commonQuery_numRows($result) == 1)
	{
		$row	     = commonQuery_fetchRow($result);
		$pos	  	 = $row[0]+1;
	}

	# insert new link to box
	# ------------------------------------------------------------------------------------------------------
	$queryStr = "insert into links (id, boxId, pos, type, urlOrPageId, isNewWin, isBlink, isUniqueStyle)
				 values (NULL, $boxId, $pos, '$type', '$urlOrPageId', '$isNewWin', '$isBlink', '$isUniqueStyle')";
	commonDoQuery ($queryStr);
	$queryStr = "select max(id) from links where boxId=$boxId";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$linkId	  	 = $row[0];
	
	# copy all languages rows
	# ------------------------------------------------------------------------------------------------------
	$queryStr = "select * from links_byLang where linkId='$id'";
	$result		= commonDoQuery ($queryStr);
	while ($row = commonQuery_fetchRow($result))
	{
		$queryStr = "insert into links_byLang (linkId, language, name, title)
					 values ('$linkId', '$row[language]', '$row[name]', '$row[title]')";
		commonDoQuery ($queryStr);
	}

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteLink																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteLink ($xmlRequest)
{
	$id 	= xmlParser_getValue ($xmlRequest, "id");
	$boxId  = xmlParser_getValue ($xmlRequest, "boxId");

	if ($id == "")
		trigger_error ("חסר קוד קישור לביצוע הפעולה");

	// get curr link pos
	$queryStr    = "select pos from links where id=$id";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$pos		 = $row[0];

	$queryStr 	 = "delete from links where id = $id";
	commonDoQuery ($queryStr);

	// update places
	$queryStr 	 = "update links set pos = pos-1 where boxId = $boxId and pos > $pos";
	commonDoQuery ($queryStr);

	$queryStr 	 = "delete from links_byLang where linkId = $id";
	commonDoQuery ($queryStr);

	return "";
}

?>
