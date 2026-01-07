<?php

$tags		= Array(#tags#);
$langTags	= Array(#langTags#);

/* ----------------------------------------------------------------------------------------------------	*/
/* getXXXs																								*/
/* ----------------------------------------------------------------------------------------------------	*/
function getXXXs ($xmlRequest)
{
	global $usedLangs;
	$langsArray = split(",",$usedLangs);

	$conditions  = "";

	// get total
	$queryStr	 = "select count(*) 
					from #tableName#, #tableName#_byLang
					where #tableName#.id = #tableName#_byLang.xxxId and #tableName#_byLang.language = '$langsArray[0]'";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$total	     = $row[0];

	// get details
	$queryStr    = "select #tableName#.*, #tableName#_byLang.* 
					from #tableName#, #tableName#_byLang
					where #tableName#.id = #tableName#_byLang.xxxId and #tableName#_byLang.language  = '$langsArray[0]'
					order by #tableName#.id desc " . commonGetLimit ($xmlRequest);
	$result	     = commonDoQuery ($queryStr);

	$numRows    = commonQuery_numRows($result);

	$xmlResponse = "<items>";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		$xmlResponse .=	"<item>";

		for ($i=0; $i < count($tags); $i++)
		{
			eval ("\$$tags[$i] = \$row['$tags[$i]'];");

			eval ("\$$tags[$i] = commonValidXml(\$$tags[$i]);");

			eval ("\$xmlResponse .= \"<$tags[$i]>\$$tags[$i]</$tags[$i]>\";");
		}

		for ($i=0; $i < count($langTags); $i++)
		{
			eval ("\$$langTags[$i] = commonValidXml(\$row['$langTags[$i]']);");
			eval ("\$xmlResponse .=	\"<$langTags[$i]\$language>\$$langTags[$i]</$langTags[$i]\$language>\";");
		}
		
		$xmlResponse .= "</item>";
	}

	$xmlResponse .=	"</items>"												.
					commonGetTotalXml($xmlRequest,$numRows,$total);
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getXXXNextId																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function getXXXNextId ($xmlRequest)
{
	$queryStr	= "select max(id) from #tableName#";
	$result		= commonDoQuery ($queryStr);
	$row		= commonQuery_fetchRow ($result);
	$id 		= $row[0] + 1;
	
	return "<id>$id</id>";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getXXXDetails																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getXXXDetails ($xmlRequest)
{
	global $usedLangs, $tags, $langTags;

	$id		= xmlParser_getValue($xmlRequest, "id");

	if ($id == "")
		trigger_error ("חסר קוד מונח לביצוע הפעולה");

	$queryStr    = "select #tableName#.*, #tableName#_byLang.*
					from #tableName#, #tableName#_byLang
					where #tableName#.id = #tableName#_byLang.xxxId
				    and #tableName#.id=$id";

	$result		= commonDoQuery ($queryStr);

	if (commonQuery_numRows($result) == 0)
		trigger_error ("קוד זה ($id) לא קיים במערכת. לא ניתן לבצע את הפעולה");

	$langsArray = split(",",$usedLangs);

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

				eval ("\$$tags[$i] = commonValidXml(\$$tags[$i]);");

				eval ("\$xmlResponse .= \"<$tags[$i]>\$$tags[$i]</$tags[$i]>\";");
			}

			$xmlResponse .= "<xxxId>$id</xxxId>";
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
			eval ("\$xmlResponse .=	\"<$langTags[$j]\$language></$langTags[$j]\$language>\";");
		}
	}

	return $xmlResponse;
}

/* ----------------------------------------------------------------------------------------------------	*/
/* doesXXXExist																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function doesXXXExist ($id)
{
	$queryStr		= "select count(*) from #tableName# where id=$id";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$count	     = $row[0];

	return ($count > 0);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* editXXX																								*/
/* ----------------------------------------------------------------------------------------------------	*/
function editXXX ($xmlRequest, $editType)
{
	global $usedLangs, $tags, $langTags;

	for ($i=0; $i < count($tags); $i++)
	{
		eval ("\$$tags[$i] = commonDecode(xmlParser_getValue(\$xmlRequest,\"$tags[$i]\"));");	
	}

	$vals = Array();

	for ($i=0; $i < count($tags); $i++)
	{
		eval ("array_push (\$vals,\$$tags[$i]);");
	}
	
	if ($editType == "update")
	{
		if ($id == "")
			trigger_error ("חסר קוד לביצוע הפעולה");

		if (!doesXXXExist($id))
		{
			trigger_error ("קוד זה ($id) לא קיים במערכת. לא ניתן לבצע את העדכון");
		}
		
		$queryStr = "update #tableName# set ";

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
		$queryStr = "insert into #tableName# (" . join(",",$tags) . ") values ('" . join("','",$vals) . "')";
		commonDoQuery ($queryStr);
	}

	# delete all languages rows
	# ------------------------------------------------------------------------------------------------------
	$queryStr = "delete from #tableName#_byLang where xxxId='$id'";
	commonDoQuery ($queryStr);
	
	# add languages rows for this user
	# ------------------------------------------------------------------------------------------------------
	$langsArray = split(",",$usedLangs);

	for ($i=0; $i<count($langsArray); $i++)
	{
		$language		= $langsArray[$i];

		$vals = Array();
		for ($j=0; $j < count($langTags); $j++)
		{
			eval ("\$$langTags[$j] = addslashes(commonDecode(xmlParser_getValue(\$xmlRequest,\"$langTags[$j]\$language\")));");	
			eval ("array_push (\$vals,\$$langTags[$j]);");
		}		

		$queryStr		= "insert into #tableName#_byLang (xxxId, language," . join(",",$langTags) . ") 
						   values ($id, '$language', '" . join ("','", $vals) . "')";
	
		commonDoQuery ($queryStr);
	}
	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteXXX																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteXXX ($xmlRequest)
{
	$id = xmlParser_getValue ($xmlRequest, "id");

	if ($id == "")
		trigger_error ("חסר קוד לביצוע הפעולה");

	$queryStr = "delete from #tableName# where id = $id";
	commonDoQuery ($queryStr);

	$queryStr = "delete from #tableName#_byLang where xxxId = $id";
	commonDoQuery ($queryStr);

	return "";
}

?>

