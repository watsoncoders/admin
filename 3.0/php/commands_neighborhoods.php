<?php

/* ----------------------------------------------------------------------------------------------------	*/
/* getNeighborhoods																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getNeighborhoods ($xmlRequest)
{
	global $usedLangs;
	$langsArray = explode(",",$usedLangs);

	$cityId		= xmlParser_getValue($xmlRequest, "cityId");

	$conditions = "";

	if ($cityId != "" && $cityId != "-1")
		$conditions = " and neighborhoods.cityId = $cityId ";
	
	$name		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "name")));

	if ($name != "")
		$conditions .= " and neighborhoods_byLang.name like '%$name%' ";

	// get total
	$queryStr	 = "select count(*) 
				    from (neighborhoods, neighborhoods_byLang, cities_byLang)
					where neighborhoods.id = neighborhoods_byLang.neighborhoodId and neighborhoods_byLang.language = '$langsArray[0]'
					and   neighborhoods.cityId = cities_byLang.cityId and cities_byLang.language = 'HEB' $conditions";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$total	     = $row[0];

	// get details
	$queryStr    = "select neighborhoods.*, neighborhoods_byLang.*, cities_byLang.name as cityName
				    from (neighborhoods, neighborhoods_byLang, cities_byLang)
					where neighborhoods.id = neighborhoods_byLang.neighborhoodId and neighborhoods_byLang.language = '$langsArray[0]'
					and   neighborhoods.cityId = cities_byLang.cityId and cities_byLang.language = 'HEB' $conditions
					order by neighborhoods.id";
					
	$all = xmlParser_getValue($xmlRequest, "all");
	if ($all != "1")
		$queryStr .= commonGetLimit ($xmlRequest);

	$result	     = commonDoQuery ($queryStr);

	$numRows    = commonQuery_numRows($result);

	$xmlResponse = "<items>";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		$id    				= $row['id'];
		$name				= commonValidXml ($row['name']);
		$cityName			= commonValidXml ($row['cityName']);

		$xmlResponse .=	"<item>
							<id>$id</id>
							<name>$name</name>
							<cityName>$cityName</cityName>					
						</item>";
	}

	$xmlResponse .=	"</items>"												.
					commonGetTotalXml($xmlRequest,$numRows,$total);
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getNeighborhoodDetails																				*/
/* ----------------------------------------------------------------------------------------------------	*/
function getNeighborhoodDetails ($xmlRequest)
{
	global $usedLangs;

	$id		= xmlParser_getValue($xmlRequest, "id");

	if ($id == "")
		trigger_error ("חסר קוד שכונה לביצוע הפעולה");

	$queryStr	= "select *, st_x(location) as x, st_y(location) as y 
				   from neighborhoods, neighborhoods_byLang where neighborhoods.id = neighborhoods_byLang.neighborhoodId and id=$id";
	$result		= commonDoQuery ($queryStr);

	if (commonQuery_numRows($result) == 0)
		trigger_error ("שכונה קוד זה ($id) לא קיים במערכת. לא ניתן לבצע את הפעולה");

	$langsArray = explode(",",$usedLangs);

	$xmlResponse = "";

	while ($row = commonQuery_fetchRow($result))
	{
		$language = $row['language'];

		$langsArray = commonArrayRemove ($langsArray, $language);	

		if ($xmlResponse == "")
		{
			$cityId 	= $row['cityId'];
			
			$xmlResponse   .= "<id>$id</id>
							   <cityId>$cityId</cityId>
							   <x>$row[x]</x>
							   <y>$row[y]</y>";
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
/* addNeighborhood																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function addNeighborhood ($xmlRequest)
{
	return (editNeighborhood ($xmlRequest, "add"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* doesNeighborhoodExist																				*/
/* ----------------------------------------------------------------------------------------------------	*/
function doesNeighborhoodExist ($id)
{
	$queryStr		= "select count(*) from neighborhoods where id=$id";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$count	     = $row[0];

	return ($count > 0);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateNeighborhood																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateNeighborhood ($xmlRequest)
{
	editNeighborhood ($xmlRequest, "update");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* editNeighborhood																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function editNeighborhood ($xmlRequest, $editType)
{
	global $usedLangs;

	$id			= xmlParser_getValue($xmlRequest, "id");
	$name		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "name")));
	$cityId		= xmlParser_getValue($xmlRequest, "cityId");
	$x			= xmlParser_getValue($xmlRequest, "x");
	$y			= xmlParser_getValue($xmlRequest, "y");

	$location	= "null";

	if ($editType == "update" && $id == "")
		trigger_error ("חסר קוד שכונה לביצוע הפעולה");

	$queryStr	= "select count(*) from neighborhoods, neighborhoods_byLang 
				   where neighborhoods.id = neighborhoods_byLang.neighborhoodId and name like binary '$name' and cityId = $cityId";

	if ($editType == "update")
	{
		$queryStr	.= " and id != $id";
	}

	if ($x != "" && $y != "")
	{
		$location	= "POINT($x,$y)";
	}

	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$count	     = $row[0];

	if ($count > 0)
		trigger_error ("שכונה זו כבר קיימת ביישוב זה");

	if ($editType == "add")
	{
		$queryStr   = "select max(id) from neighborhoods";
		$result	     = commonDoQuery ($queryStr);
		$row	     = commonQuery_fetchRow($result);
		$id		     = $row[0]+1;

		$queryStr = "insert into neighborhoods (id, cityId, location) values ('$id', '$cityId', $location)";
	}
	else
	{
		$queryStr = "update neighborhoods set cityId='$cityId', location = $location where id=$id";
	}

	commonDoQuery ($queryStr);

	# delete all languages rows
	# ------------------------------------------------------------------------------------------------------
	$queryStr = "delete from neighborhoods_byLang where neighborhoodId='$id'";
	commonDoQuery ($queryStr);
	
	# add languages rows for this user
	# ------------------------------------------------------------------------------------------------------
	$langsArray = explode(",",$usedLangs);

	for ($i=0; $i<count($langsArray); $i++)
	{
		$language		= $langsArray[$i];

		$name		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "name$language")));

		$queryStr		= "insert into neighborhoods_byLang (neighborhoodId, language, name)
						   values ('$id','$language','$name')";
	
		commonDoQuery ($queryStr);
	}
	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteNeighborhood																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteNeighborhood ($xmlRequest)
{
	$id = xmlParser_getValue ($xmlRequest, "id");

	if ($id == "")
		trigger_error ("חסר קוד שכונה לביצוע הפעולה");

	$queryStr = "delete from neighborhoods where id = $id";
	commonDoQuery ($queryStr);

	$queryStr = "delete from neighborhoods_byLang where neighborhoodId = $id";
	commonDoQuery ($queryStr);

	return "";
}

?>
