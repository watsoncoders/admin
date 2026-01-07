<?php

/* ----------------------------------------------------------------------------------------------------	*/
/* getAreas																								*/
/* ----------------------------------------------------------------------------------------------------	*/
function getAreas ($xmlRequest)
{
	global $usedLangs;
	$langsArray = explode(",",$usedLangs);

	$sortBy		= xmlParser_getValue($xmlRequest,"sortBy");

	if ($sortBy == "")
		$sortBy = "id";

	$sortDir	= xmlParser_getValue($xmlRequest,"sortDir");
	if ($sortDir == "")
		$sortDir = "desc";

	$condition = "";

	$superArea		= xmlParser_getValue($xmlRequest, "superArea");
	if ($superArea != "")
		$condition .= " and areas.superArea = '$superArea' ";

	$countryId		= xmlParser_getValue($xmlRequest, "countryId");
	if ($countryId != "")
		$condition .= " and areas.countryId = '$countryId' ";

	// get total
	$queryStr	 = "select count(*) from areas where 1 $condition";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$total	     = $row[0];

	// get details
	$queryStr    = "select areas.*, areas_byLang.name, countries_byLang.name as countryName
				    from (areas, areas_byLang)
					left join countries_byLang on countries_byLang.countryId = areas.countryId and countries_byLang.language = '$langsArray[0]' 
					where areas.id = areas_byLang.areaId and areas_byLang.language = '$langsArray[0]' $condition
					group by areas.id
					order by $sortBy $sortDir " . commonGetLimit ($xmlRequest);
	$result	     = commonDoQuery ($queryStr);

	$numRows    = commonQuery_numRows($result);

	$xmlResponse = "<items>";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		$id    		 = $row['id'];
		$name		 = commonValidXml ($row['name']);
		$countryName = commonValidXml ($row['countryName']);
		$superArea	 = formatSuperArea($row['superArea']);

		$xmlResponse .=	"<item>
							<id>$id</id>
							<name>$name</name>
							<superArea>$superArea</superArea>
							<countryId>$countryName</countryId>
						</item>";
	}

	$xmlResponse .=	"</items>"												.
					commonGetTotalXml($xmlRequest,$numRows,$total);
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getAreaDetails																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getAreaDetails ($xmlRequest)
{
	global $usedLangs;

	$id		= xmlParser_getValue($xmlRequest, "id");

	if ($id == "")
		trigger_error ("חסר קוד אזור לביצוע הפעולה");

	$queryStr	= "select areas.*, areas_byLang.* from areas, areas_byLang
				   where areas.id = areas_byLang.areaId
				   and areas.id=$id";
	$result		= commonDoQuery ($queryStr);

	if (commonQuery_numRows($result) == 0)
		trigger_error ("אזור קוד זה ($id) לא קיים במערכת. לא ניתן לבצע את הפעולה");

	$langsArray = explode(",",$usedLangs);

	$xmlResponse = "";

	while ($row = commonQuery_fetchRow($result))
	{
		$language = $row['language'];

		$langsArray = commonArrayRemove ($langsArray, $language);	

		if ($xmlResponse == "")
		{
			$superArea = $row['superArea'];
			$countryId = $row['countryId'];
			
			$xmlResponse =	"<id>$id</id>
						 	 <superArea>$superArea</superArea>
						 	 <countryId>$countryId</countryId>";
		}

		$name		= commonValidXml($row['name']);

		$xmlResponse   .= "<name$language>$name</name$language>";
	}

	// add missing languages
	// ------------------------------------------------------------------------------------------------
	for ($i=0; $i<count($langsArray); $i++)
	{
		$language	  = $langsArray[$i];

		$xmlResponse .=	   "<name$language><![CDATA[]]></name$language>";
	}

	return $xmlResponse;
}

/* ----------------------------------------------------------------------------------------------------	*/
/* addArea																								*/
/* ----------------------------------------------------------------------------------------------------	*/
function addArea ($xmlRequest)
{
	return (editArea ($xmlRequest, "add"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* doesAreaExist																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function doesAreaExist ($id)
{
	$queryStr		= "select count(*) from areas where id=$id";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$count	     = $row[0];

	return ($count > 0);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateArea																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateArea ($xmlRequest)
{
	editArea ($xmlRequest, "update");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* editArea																								*/
/* ----------------------------------------------------------------------------------------------------	*/
function editArea ($xmlRequest, $editType)
{
	global $usedLangs;

	$id		= xmlParser_getValue($xmlRequest, "id");

	if ($editType == "update")
	{
		if ($id == "")
			trigger_error ("חסר קוד אזור לביצוע הפעולה");

		if (!doesAreaExist($id))
		{
			trigger_error ("אזור עם קוד זה ($id) לא קיים במערכת. לא ניתן לבצע את העדכון");
		}
	}

	$superArea		= xmlParser_getValue($xmlRequest, "superArea");
	$countryId		= xmlParser_getValue($xmlRequest, "countryId");

	if ($editType == "add")
	{
		$queryStr   = "select max(id) from areas";
		$result	     = commonDoQuery ($queryStr);
		$row	     = commonQuery_fetchRow($result);
		$id		     = $row[0]+1;

		$queryStr = "insert into areas (id, superArea, countryId) values ('$id', '$superArea', '$countryId')";
	}
	else
	{
		$queryStr = "update areas set superArea = '$superArea', countryId = '$countryId' where id = $id";
	}
	
	commonDoQuery ($queryStr);

	# delete all languages rows
	# ------------------------------------------------------------------------------------------------------
	$queryStr = "delete from areas_byLang where areaId='$id'";
	commonDoQuery ($queryStr);
	
	# add languages rows for this user
	# ------------------------------------------------------------------------------------------------------
	$langsArray = explode(",",$usedLangs);

	for ($i=0; $i<count($langsArray); $i++)
	{
		$language		= $langsArray[$i];

		$name		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "name$language")));

		$queryStr		= "insert into areas_byLang (areaId, language, name)
						   values ('$id','$language','$name')";
	
		commonDoQuery ($queryStr);
	}
	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteArea																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteArea ($xmlRequest)
{
	$id = xmlParser_getValue ($xmlRequest, "id");

	if ($id == "")
		trigger_error ("חסר קוד אזור לביצוע הפעולה");

	$queryStr    = "select count(*) from shopProducers where areaId = $id";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$total	     = $row[0];

	if ($total != 0)
		trigger_error ("לא ניתן למחוק אזור שמקושרים אליו יצרנים");
	
	$queryStr = "delete from areas where id = $id";
	commonDoQuery ($queryStr);

	$queryStr = "delete from areas_byLang where areaId = $id";
	commonDoQuery ($queryStr);

	return "";
}

?>
