<?php

/* ----------------------------------------------------------------------------------------------------	*/
/* getCities																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function getCities ($xmlRequest)
{
	global $usedLangs;
	$langsArray = explode(",",$usedLangs);

	$sortBy		= xmlParser_getValue($xmlRequest,"sortBy");

	if ($sortBy == "")
		$sortBy = "name";

	if ($sortBy == "areaId")
		$sortBy = "areaName";

	$sortDir	= xmlParser_getValue($xmlRequest,"sortDir");
	if ($sortDir == "")
		$sortDir = "asc";

	$conditions = "";

	$countryId		= xmlParser_getValue($xmlRequest, "countryId");
	if ($countryId != "" && $countryId != "-1")
		$conditions = " and countries_byLang.countryId = $countryId ";
	
	$areaId		= xmlParser_getValue($xmlRequest, "areaId");
	if ($areaId != "" && $areaId != "-1")
		$conditions = " and areas_byLang.areaId = $areaId ";
	
	$fictive = xmlParser_getValue($xmlRequest, "fictive");
	if ($fictive != "")
		$conditions = " and cities.fictive = '$fictive' ";
	
	$display = xmlParser_getValue($xmlRequest, "display");
	if ($display != "")
		$conditions = " and cities.display = '$display' ";
	
	$name		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "name")));
	if ($name != "")
		$conditions .= " and cities_byLang.name like binary '%$name%' ";

	// get total
	$queryStr	 = "select count(*) from (cities)
					left join cities_byLang 	on cities.id = cities_byLang.cityId and cities_byLang.language = '$langsArray[0]'
					left join areas_byLang  on cities.areaId = areas_byLang.areaId and areas_byLang.language = '$langsArray[0]'
					left join countries_byLang  on cities.countryId = countries_byLang.countryId and countries_byLang.language = '$langsArray[0]'
					where 1 $conditions";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$total	     = $row[0];

	// get details
	$queryStr    = "select cities.*, cities_byLang.name as name, areas_byLang.name as areaName, countries_byLang.name as countryName
					from cities
					left join cities_byLang on cities.id = cities_byLang.cityId and cities_byLang.language = '$langsArray[0]'
					left join areas_byLang  on cities.areaId = areas_byLang.areaId and areas_byLang.language = '$langsArray[0]'
					left join countries_byLang  on cities.countryId = countries_byLang.countryId and countries_byLang.language = '$langsArray[0]'
					where 1 $conditions
					group by cities.id
					order by $sortBy $sortDir";

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
		$countryName		= commonValidXml ($row['countryName']);
		$areaName			= commonValidXml ($row['areaName']);
		$fictive			= formatBoolean  ($row['fictive']);
		$display			= formatBoolean  ($row['display']);

		$xmlResponse .=	"<item>
							<id>$id</id>
							<name>$name</name>
							<countryId>$countryName</countryId>
							<areaId>$areaName</areaId>					
							<fictive>$fictive</fictive>					
							<display>$display</display>
						</item>";
	}

	$xmlResponse .=	"</items>"												.
					commonGetTotalXml($xmlRequest,$numRows,$total);
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getCityDetails																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getCityDetails ($xmlRequest)
{
	global $usedLangs;

	$id		= xmlParser_getValue($xmlRequest, "id");

	if ($id == "")
		trigger_error ("חסר קוד יישוב לביצוע הפעולה");

	$queryStr	= "select cities.*, cities_byLang.*, st_x(location) as x, st_y(location) as y  
				   from cities, cities_byLang
				   where cities.id = cities_byLang.cityId
				   and cities.id=$id";
	$result		= commonDoQuery ($queryStr);

	if (commonQuery_numRows($result) == 0)
		trigger_error ("יישוב קוד זה ($id) לא קיים במערכת. לא ניתן לבצע את הפעולה");

	$langsArray = explode(",",$usedLangs);

	$xmlResponse = "";

	while ($row = commonQuery_fetchRow($result))
	{
		$language = $row['language'];

		$langsArray = commonArrayRemove ($langsArray, $language);	

		if ($xmlResponse == "")
		{
			$countryId 	= $row['countryId'];
			$areaId 	= $row['areaId'];
			$type	 	= $row['type'];
			$fictive	= $row['fictive'];
			$display	= $row['display'];

			$xmlResponse =	"<id>$id</id>
							 <type>$type</type>
							 <fictive>$fictive</fictive>					
							 <display>$display</display>
							 <areaId>$areaId</areaId>
							 <countryId>$countryId</countryId>
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
/* addCity																								*/
/* ----------------------------------------------------------------------------------------------------	*/
function addCity ($xmlRequest)
{
	return (editCity ($xmlRequest, "add"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* doesCityExist																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function doesCityExist ($id)
{
	$queryStr		= "select count(*) from cities where id=$id";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$count	     = $row[0];

	return ($count > 0);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateCity																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateCity ($xmlRequest)
{
	return editCity ($xmlRequest, "update");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* editCity																								*/
/* ----------------------------------------------------------------------------------------------------	*/
function editCity ($xmlRequest, $editType)
{
	global $usedLangs;

	$id			= xmlParser_getValue($xmlRequest, "id");
	$areaId		= xmlParser_getValue($xmlRequest, "areaId");
	$type		= xmlParser_getValue($xmlRequest, "type");
	$countryId	= xmlParser_getValue($xmlRequest, "countryId");
	$fictive	= xmlParser_getValue($xmlRequest, "fictive");
	$display	= xmlParser_getValue($xmlRequest, "display");
	$x			= xmlParser_getValue($xmlRequest, "x");
	$y			= xmlParser_getValue($xmlRequest, "y");

	$location	= "null";

	if ($editType == "update")
	{
		if ($id == "")
			trigger_error ("חסר קוד יישוב לביצוע הפעולה");

		if (!doesCityExist($id))
		{
			trigger_error ("יישוב עם קוד זה ($id) לא קיים במערכת. לא ניתן לבצע את העדכון");
		}
	}

	// check if city with the same name already exist
	$name 	  = addslashes(commonDecode(xmlParser_getValue($xmlRequest, "nameHEB")));
	$queryStr = "select count(*) from cities_byLang where cities_byLang.name like '$name' and language = 'HEB'";
    if ($editType == "update")
		$queryStr .= " and cityId != '$id'";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$count	     = $row[0];
	if ($count != 0)
	{
		trigger_error ("יישוב בשם זה כבר קיים במערכת");
	}

	if ($x != "" && $y != "")
	{
		$location	= "POINT($x,$y)";
	}

	if ($editType == "add")
	{
		$queryStr   = "select max(id) from cities";
		$result	     = commonDoQuery ($queryStr);
		$row	     = commonQuery_fetchRow($result);
		$id		     = $row[0]+1;

		$queryStr = "insert into cities (id, countryId, areaId, display, type, fictive, insertTime, location) values
										 ('$id', '$countryId', '$areaId', '$display', '$type', '$fictive', now(), $location)";
	}
	else
	{
		$queryStr = "update cities set countryId = '$countryId', 
									   areaId	 = '$areaId',
									   display	 = '$display',
									   type		 = '$type',
									   fictive	 = '$fictive',
									   location  =  $location
					 where id=$id";
	}
	commonDoQuery ($queryStr);

	# delete all languages rows
	# ------------------------------------------------------------------------------------------------------
	$queryStr = "delete from cities_byLang where cityId='$id'";
	commonDoQuery ($queryStr);
	
	# add languages rows for this user
	# ------------------------------------------------------------------------------------------------------
	$langsArray = explode(",",$usedLangs);

	for ($i=0; $i<count($langsArray); $i++)
	{
		$language		= $langsArray[$i];

		$name		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "name$language")));

		$queryStr		= "insert into cities_byLang (cityId, language, name)
						   values ('$id','$language','$name')";
	
		commonDoQuery ($queryStr);
	}
	return "<id>$id</id>";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteCity																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteCity ($xmlRequest)
{
	$ids = xmlParser_getValues ($xmlRequest, "id");

	if (count($ids) == 0)
		trigger_error ("חסר קוד יישוב לביצוע הפעולה");

	foreach ($ids as $id)
	{
		$queryStr = "delete from cities where id = $id";
		commonDoQuery ($queryStr);

		$queryStr = "delete from cities_byLang where cityId = $id";
		commonDoQuery ($queryStr);
	}

	return "";
}

?>
