<?php

/* ----------------------------------------------------------------------------------------------------	*/
/* getLayouts																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function getLayouts ($xmlRequest)
{
	global $usedLangs;
	$langsArray = explode(",",$usedLangs);

	$type		= xmlParser_getValue($xmlRequest, "type");

	$cond 		= "";
	if ($type != "")
		$cond = " and layouts.type = '$type'";

	$active		= xmlParser_getValue($xmlRequest, "active");
	if ($active != "")
		$cond .= " and layouts.active = '$active'";
	else
		$cond .= " and layouts.active = 1";

	// get total
	$queryStr	 = "select count(*) from layouts where 1 $cond";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$total	     = $row[0];

	if ($total == 0 && $type != "")	// if we don't find layouts of this type - get all types
	{
		$queryStr 	=  "select count(*) from layouts";
		$result	     = commonDoQuery ($queryStr);
		$row	     = commonQuery_fetchRow($result);
		$total	     = $row[0];
		$cond		= "";
	}

	// get details
	$queryStr = "select layouts.id, layouts.active, layouts.type, layouts_byLang.name, count(pages.id) as countPages 
				 from (layouts, layouts_byLang)
				 left join pages on pages.layoutId = layouts.id and pages.id > 0
				 where layouts.id = layouts_byLang.layoutId and language='$langsArray[0]'
				 $cond
				 group by layouts.id
				 order by layouts.id " . commonGetLimit ($xmlRequest);

	$result	     = commonDoQuery ($queryStr);

	$numRows    = commonQuery_numRows($result);

	$xmlResponse = "<items>";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		$id   		= $row['id'];
		$countPages = $row['countPages'];
		$type		= $row['type'];

		switch ($type)
		{
			case "page"			: $type	= "דף";			break;
			case "newsletter"	: $type	= "ניוזלטר";	break;
			case "essay"		: $type	= "כתבה";		break;
		}
		$type		= commonPhpEncode ($type);

		$active		= (($row['active'] == "1") ? "כן" : "לא");
		$active		= commonPhpEncode($active);

		$name 		= commonValidXml($row['name']);

		$pages		= "";
		if ($countPages != 0)
		{
			if ($countPages == 1)
			{
				$sql	 = "select id, title, winTitle from pages, pages_byLang 
						    where pages.id = pages_byLang.pageId 
							and   pages_byLang.language='$langsArray[0]'
							and   layoutId = $id and id > 0 order by id";
				$result2 = commonDoQuery($sql);
				$row2	 = commonQuery_fetchRow($result2);

				if ($row2['title'] == "")
					$row2['title'] = $row2['winTitle'];

				$countPages = commonValidXml("$row2[id] | $row2[title]");

				$pages	= $row2['id'];
			}
			else
			{
				$sql	 = "select id from pages where layoutId = $id and id > 0 order by id";
				$result2 = commonDoQuery($sql);

				$count	 = 0;
				while ($row2 = commonQuery_fetchRow($result2))
				{
					$count++;

					if ($count > 100)
					{
						$pages .= " ...";
						break;
					}

					$pages .= " | $row2[id]";
				}

				$pages = trim($pages, " | ");
			}
		}

		$xmlResponse .=	"<item>
							<layoutId>$id</layoutId>
							<countPages>$countPages</countPages>
							<name>$name</name>
							<type>$type</type>
							<pages>$pages</pages>
							<active>$active</active>
						</item>";
	}

	$xmlResponse .=	"</items>"												.
					commonGetTotalXml($xmlRequest,$numRows,$total);
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getLayoutNextId																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getLayoutNextId ()
{
	$queryStr	= "select max(id) from layouts";
	$result		= commonDoQuery ($queryStr);
	$row		= commonQuery_fetchRow ($result);
	$id 		= $row[0] + 1;
	
	return $id;
}

/* ----------------------------------------------------------------------------------------------------	*/
/* addLayout																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function addLayout ($xmlRequest)
{
	return (editLayout ($xmlRequest, "add"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* doesLayoutExist																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function doesLayoutExist ($id)
{
	$queryStr		= "select count(*) from layouts where id=$id";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$count	     = $row[0];

	return ($count > 0);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getLayoutDetails																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getLayoutDetails ($xmlRequest)
{
	global $usedLangs;

	$id		= xmlParser_getValue($xmlRequest, "layoutId");
	$action	= xmlParser_getValue($xmlRequest, "action");

	if ($id == "")
		trigger_error ("חסר קוד דף עיצוב לביצוע הפעולה");

	$queryStr	= "select layouts.*, layouts_byLang.*
				   from layouts left join layouts_byLang on layouts.id=layouts_byLang.layoutId
				   where layouts.id=$id";
	$result		= commonDoQuery ($queryStr);

	if (commonQuery_numRows($result) == 0)
		trigger_error ("דף עיצוב עם קוד זה ($id) לא קיים במערכת. לא ניתן לבצע את העדכון");

	$langsArray = explode(",",$usedLangs);

	if (commonHasMobileVersion ())
	{
		foreach ($langsArray as $lang)
		{
			array_push ($langsArray, "${lang}m");
		}
	}

	$xmlResponse = "";
	
	while ($row = commonQuery_fetchRow($result))
	{
		$language = $row['language'];

		$langsArray = commonArrayRemove ($langsArray, $language);	

		if ($xmlResponse == "")
		{
			if ($action == "duplicate")
			{
				$queryStr	= "select max(id) from layouts";
				$result2	= commonDoQuery ($queryStr);
				$row2		= commonQuery_fetchRow ($result2);
				$id 		= $row2[0] + 1;

			}
			else
			{
				$id			   = $row['id'];
			}

			$active		   	= $row['active'];
			$type			= $row['type'];
			$name		   	= commonValidXml($row['name']);
				
			$xmlResponse .= "<layoutId>$id</layoutId>
							 <active>$active</active>
							 <type>$type</type>
							 <name>$name</name>";
		}

		$text	= commonValidXml($row['text']);
	
		$xmlResponse .=	   "<text$language>$text</text$language>";
	}

	// add missing languages
	// ------------------------------------------------------------------------------------------------
	for ($i=0; $i<count($langsArray); $i++)
	{
		$language	  = $langsArray[$i];

		$xmlResponse .=	   "<text$language><![CDATA[]]></text$language>";
	}

	return $xmlResponse;
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateLayout																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateLayout ($xmlRequest)
{
	return (editLayout ($xmlRequest, "update"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* editLayout																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function editLayout ($xmlRequest, $editType)
{
	global $usedLangs;

	$id		= xmlParser_getValue($xmlRequest, "layoutId");

	if ($editType == "update" && $id == "")
		trigger_error ("חסר קוד דף עיצוב לביצוע הפעולה");


	if ($editType == "add")
	{
		$id = getLayoutNextId ();
	}
	else	// update layout
	{
		if (!doesLayoutExist($id))
		{
			trigger_error ("דף עיצוב עם קוד זה ($id) לא קיים במערכת. לא ניתן לבצע את העדכון");
		}
	}

	# delete all languages rows
	# ------------------------------------------------------------------------------------------------------
	$queryStr = "delete from layouts_byLang where layoutId='$id'";
	commonDoQuery ($queryStr);
	

	# add languages rows for this layout
	# ------------------------------------------------------------------------------------------------------
	$langsArray = explode(",",$usedLangs);

	if (commonHasMobileVersion ())
	{
		foreach ($langsArray as $lang)
		{
			array_push ($langsArray, "${lang}m");
		}
	}

	for ($i=0; $i<count($langsArray); $i++)
	{
		$language		= $langsArray[$i];

		$name		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "name")));
		$text		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "text$language")));

		$queryStr		= "insert into layouts_byLang (layoutId, language, name, text)
						   values ('$id','$language', '$name', '$text')";

		commonDoQuery ($queryStr);
	}

	$active			= xmlParser_getValue($xmlRequest, "active");
	$type			= xmlParser_getValue($xmlRequest, "type");
	
	if ($editType == "add")
	{
		$queryStr = "insert into layouts (id, type, active) values ('$id', '$type', '$active')";
		commonDoQuery ($queryStr);

	}
	else // update
	{
		$queryStr = "update layouts set active = '$active', type = '$type' where id = $id";
		commonDoQuery ($queryStr);
	}

	return "<layoutId>$id</layoutId>";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteLayout																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteLayout ($xmlRequest)
{
	$id = xmlParser_getValue ($xmlRequest, "layoutId");

	if ($id == "")
		trigger_error ("חסר קוד דף עיצוב לביצוע הפעולה");

	// check if there are any pages that using this layout
	$queryStr	= "select count(*) from pages where layoutId = $id";
	$result		= commonDoQuery($queryStr);
	$row		= commonQuery_fetchRow($result);

	if ($row[0] != 0)
		trigger_error ("לא ניתן למחוק תבנית שיש לה דפים");

	$queryStr = "delete from layouts where id = $id";
	commonDoQuery ($queryStr);

	$queryStr = "delete from layouts_byLang where layoutId = $id";
	commonDoQuery ($queryStr);

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getLayoutParams																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getLayoutParams ($xmlRequest)
{
	return (getLayoutSwitches($xmlRequest, "param"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getLayoutSwitches																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function getLayoutSwitches ($xmlRequest, $type = "section")
{
	global $usedLangs;
	$langsArray = explode(",",$usedLangs);

	// get total
	$queryStr	 = "select count(*) from layoutSwitches_byLang where type = '$type' and language = '$langsArray[0]'";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$total	     = $row[0];

	$domainRow   = commonGetDomainRow ();
	commonConnectToUserDB ($domainRow);

	global $maxRowsInPage;

	$sortDir = "";

	if ($domainRow['id'] == 412)	// gulf forex
	{
		$maxRowsInPage = 20;
		$sortDir	   = "desc";
	}

	// get details
	$queryStr = "select layoutSwitches_byLang.*
				 from layoutSwitches_byLang 
				 where type = '$type' and language = '$langsArray[0]'
				 order by id $sortDir" . commonGetLimit ($xmlRequest);

	$result	     = commonDoQuery ($queryStr);

	$numRows    = commonQuery_numRows($result);

	$xmlResponse = "<items>";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		$id   		 = $row['id'];
		$type  		 = $row['type'];
		$name 		 = commonValidXml($row['name']);
		$description = commonValidXml($row['description']);

		$xmlResponse .=	"<item>
							<switchId>$id</switchId>
							<type>$type</type>
							<name>$name</name>
							<description>$description</description>";

		if ($type == "param")
		{
			$text = commonValidXml($row['text']);
			$xmlResponse .= "<text>$text</text>";
		}
		else
		{
			$layouts = array();

			$sql	= "select layouts_byLang.name from layouts_byLang 
					   where text like '%@$row[name]@%' and layouts_byLang.language = '$langsArray[0]'
					   order by layouts_byLang.layoutId";
			$inRes	= commonDoQuery($sql);

			$layout = commonEncode ("תבנית");

			while ($inRow = commonQuery_fetchRow($inRes))
			{
				array_push ($layouts, trim(str_replace($layout, "", $inRow['name'])));
			}

			if (count($layouts) == 0)
				$layoutsStr = "";
			else
				$layoutsStr = commonValidXml(join(" | ", $layouts));

			$xmlResponse	.= "<layouts>$layoutsStr</layouts>";
		}

		$xmlResponse .= "</item>";
	}

	$xmlResponse .=	"</items>"												.
					commonGetTotalXml($xmlRequest,$numRows,$total);
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getLayoutSwitchNextId																				*/
/* ----------------------------------------------------------------------------------------------------	*/
function getLayoutSwitchNextId ()
{
	$queryStr	= "select max(id) from layoutSwitches_byLang";
	$result		= commonDoQuery ($queryStr);
	$row		= commonQuery_fetchRow ($result);
	$id 		= $row[0] + 1;
	
	return $id;
}

/* ----------------------------------------------------------------------------------------------------	*/
/* addLayoutSwitch																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function addLayoutSwitch ($xmlRequest)
{
	return (editLayoutSwitch ($xmlRequest, "add", "section"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* addLayoutParam																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function addLayoutParam ($xmlRequest)
{
	return (editLayoutSwitch ($xmlRequest, "add", "param"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* doesLayoutSwitchExist																				*/
/* ----------------------------------------------------------------------------------------------------	*/
function doesLayoutSwitchExist ($id)
{
	$queryStr		= "select count(*) from layoutSwitches_byLang where id=$id";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$count	     = $row[0];

	return ($count > 0);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getLayoutParamDetails																				*/
/* ----------------------------------------------------------------------------------------------------	*/
function getLayoutParamDetails ($xmlRequest)
{
	return (getLayoutSwitchDetails($xmlRequest, "param"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getLayoutSwitchDetails																				*/
/* ----------------------------------------------------------------------------------------------------	*/
function getLayoutSwitchDetails ($xmlRequest, $type = "section")
{
	global $usedLangs;

	$id		= xmlParser_getValue($xmlRequest, "switchId");

	if ($id == "")
		trigger_error ("חסר קוד מקטע עיצוב לביצוע הפעולה");

	$queryStr	= "select *
				   from layoutSwitches_byLang
				   where id=$id";
	$result		= commonDoQuery ($queryStr);

	if (commonQuery_numRows($result) == 0)
		trigger_error ("מקטע עיצוב עם קוד זה ($id) לא קיים במערכת. לא ניתן לבצע את העדכון");

	$langsArray = explode(",",$usedLangs);

	if (commonHasMobileVersion ())
	{
		foreach ($langsArray as $lang)
		{
			array_push ($langsArray, "${lang}m");
		}
	}

	$xmlResponse = "";
	
	while ($row = commonQuery_fetchRow($result))
	{
		$language = $row['language'];

		$langsArray = commonArrayRemove ($langsArray, $language);	

		$text	= commonValidXml($row['text']);
	
		if ($xmlResponse == "")
		{
			$id	   			= $row['id'];
			$name			= commonValidXml($row['name']);
			$description	= commonValidXml($row['description']);
				
			$xmlResponse .= "<switchId>$id</switchId>
							 <name>$name</name>
							 <description>$description</description>";

			if ($type == "param")
				$xmlResponse .=	   "<text>$text</text>";
		}

		$xmlResponse .=	   "<text$language>$text</text$language>";
	}

	// add missing languages
	// ------------------------------------------------------------------------------------------------
	for ($i=0; $i<count($langsArray); $i++)
	{
		$language	  = $langsArray[$i];

		$xmlResponse .=	   "<text$language><![CDATA[]]></text$language>";
	}

	return $xmlResponse;
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateLayoutSwitch																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateLayoutSwitch ($xmlRequest)
{
	return (editLayoutSwitch ($xmlRequest, "update", "section"));
}
/* ----------------------------------------------------------------------------------------------------	*/
/* updateLayoutParam																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateLayoutParam ($xmlRequest)
{
	return (editLayoutSwitch ($xmlRequest, "update", "param"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* editLayoutSwitch																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function editLayoutSwitch ($xmlRequest, $editType, $type)
{
	global $usedLangs;

	$id		= xmlParser_getValue($xmlRequest, "switchId");

	if ($editType == "update" && $id == "")
		trigger_error ("חסר קוד מקטע עיצוב לביצוע הפעולה");


	if ($editType == "add")
	{
		$id = getLayoutSwitchNextId ();
	}
	else	// update layout
	{
		if (!doesLayoutSwitchExist($id))
		{
			trigger_error ("מקטע עיצוב עם קוד זה ($id) לא קיים במערכת. לא ניתן לבצע את העדכון");
		}
	}

	# delete all languages rows
	# ------------------------------------------------------------------------------------------------------
	$queryStr = "delete from layoutSwitches_byLang where id=$id";
	commonDoQuery ($queryStr);
	

	# add languages rows for this layout
	# ------------------------------------------------------------------------------------------------------
	$langsArray = explode(",",$usedLangs);

	if (commonHasMobileVersion ())
	{
		foreach ($langsArray as $lang)
		{
			array_push ($langsArray, "${lang}m");
		}
	}

	$name		 = addslashes(commonDecode(xmlParser_getValue($xmlRequest, "name")));
	$description = addslashes(commonDecode(xmlParser_getValue($xmlRequest, "description")));

	for ($i=0; $i<count($langsArray); $i++)
	{
		$language		= $langsArray[$i];

		if ($type == "section")
			$text		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "text$language")));
		else
			$text		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "text")));

		$queryStr		= "insert into layoutSwitches_byLang (id, language, type, name, description, text)
						   values ('$id','$language', '$type', '$name', '$description', '$text')";

		commonDoQuery ($queryStr);
	}

	if ($name == "footer")
	{
		$domainRow   = commonGetDomainRow ();

		if (strpos($domainRow['domainName'], "62.90.141.80") === false)
		{
//			mail ("amir@interuse.co.il", "I-Bos Footer update - $domainRow[domainName]", "", "");
			mail ("liat@interuse.com", "I-Bos Footer update - $domainRow[domainName]", "", "");
		}
	}

	return "<switchId>$id</switchId>";
}

function deleteLayoutParam ($xmlRequest)
{
	return deleteLayoutSwitch ($xmlRequest);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteLayoutSwitch																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteLayoutSwitch ($xmlRequest)
{
	$id = xmlParser_getValue ($xmlRequest, "switchId");

	if ($id == "")
		trigger_error ("חסר קוד מקטע עיצוב לביצוע הפעולה");

	$queryStr = "delete from layoutSwitches_byLang where id = $id";
	commonDoQuery ($queryStr);

	return "";
}

?>
