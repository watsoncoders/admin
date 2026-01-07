<?php

/* ----------------------------------------------------------------------------------------------------	*/
/* getEnums																								*/
/* ----------------------------------------------------------------------------------------------------	*/
function getEnums ($xmlRequest)
{	
	$conditions	 = "";

	$withoutId	 = xmlParser_getValue($xmlRequest, "withoutId");

	if ($withoutId != "")
		$conditions .= " and enums.id != $withoutId";

	// get total
	$queryStr	 = "select count(*) from enums where 1 $conditions";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$total	     = $row[0];

	// get details
	$queryStr    = "select * from enums  where 1 $conditions order by id";
	$result	     = commonDoQuery ($queryStr);

	$numRows    = commonQuery_numRows($result);

	$xmlResponse = "<items>";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		$id   		  = $row['id'];
		$name 		  = commonValidXml ($row['name'],true);

		$xmlResponse .=	"<item>
							<enumId>$id</enumId>
							<name>$name</name>
						 </item>";
	}

	$xmlResponse .=	"</items>";
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* addEnum																								*/
/* ----------------------------------------------------------------------------------------------------	*/
function addEnum ($xmlRequest)
{
	return (editEnum ($xmlRequest, "add"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* doesEnumExist																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function doesEnumExist ($id)
{
	$queryStr		= "select count(*) from enums where id=$id";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$count	     = $row[0];

	return ($count > 0);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getEnumNextId																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getEnumNextId ($xmlRequest)
{
	$queryStr	= "select max(id) from enums";
	$result		= commonDoQuery ($queryStr);
	$row		= commonQuery_fetchRow ($result);
	$id 		= $row[0] + 1;
	
	return "<enumId>$id</enumId>";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getEnumDetails																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getEnumDetails ($xmlRequest)
{
	$enumId		= xmlParser_getValue($xmlRequest, "enumId");

	if ($enumId == "")
		return "";
//		trigger_error ("חסר קוד רשימה לביצוע הפעולה");

	$queryStr	= "select enums.*, enumsParent.name as parentName
				   from enums 
				   left join enums enumsParent on enums.parentId = enumsParent.id
				   where enums.id=$enumId";
	$result		= commonDoQuery ($queryStr);

	if (commonQuery_numRows($result) == 0)
		trigger_error ("קוד רשימה זו ($enumId) לא קיים במערכת. לא ניתן לבצע את העדכון");

	$row = commonQuery_fetchRow($result);
	
	$parentId			= $row['parentId'];
	$name 				= commonValidXml($row['name']);
	$parentName			= commonValidXml($row['parentName']);
		
	$xmlResponse	= 	"<enumId>$enumId</enumId>
						 <parentId>$parentId</parentId>
						 <parentName>$parentName</parentName>
						 <name>$name</name>";

	return $xmlResponse;
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateEnum																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateEnum ($xmlRequest)
{
	editEnum ($xmlRequest, "update");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* editEnum																								*/
/* ----------------------------------------------------------------------------------------------------	*/
function editEnum ($xmlRequest, $editType)
{
	global $usedLangs;

	$enumId		= xmlParser_getValue($xmlRequest, "enumId");

	if ($editType == "update")
	{
		if (!doesEnumExist($enumId))
		{
			trigger_error ("רשימה עם קוד זה ($enumId) לא קיימת במערכת. לא ניתן לבצע את העדכון");
		}
	}

	$parentId			= xmlParser_getValue($xmlRequest,"parentId");
	$name 				= addslashes(commonDecode(xmlParser_getValue($xmlRequest,"name")));

	if ($editType == "add")
	{
		$queryStr = "insert into enums (id, parentId, name) values ($enumId, '$parentId', '$name')";

	}
	else // update
	{
		$queryStr = "update enums set parentId = '$parentId', name = '$name' where id=$enumId";
	}

	commonDoQuery ($queryStr);

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteEnum																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteEnum ($xmlRequest)
{
	$enumId  = xmlParser_getValue ($xmlRequest, "enumId");

	if ($enumId == "")
		trigger_error ("חסר קוד רשימה לביצוע הפעולה");

	// delete all enum values
	$queryStr =  "delete from enumsValues_byLang where valueId in (select id from enumsValues where enumId = $enumId)";
	commonDoQuery ($queryStr);

	$queryStr =  "delete from enumsValues where enumId=$enumId";
	commonDoQuery ($queryStr);

	$queryStr =  "delete from enums where id=$enumId";
	commonDoQuery ($queryStr);

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getValues																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function getValues ($xmlRequest)
{	
	global $usedLangs;
	global $userId;
	$langsArray = explode(",",$usedLangs);

	$parentId 	= xmlParser_getValue ($xmlRequest, "parentId");
	$enumId   	= xmlParser_getValue ($xmlRequest, "enumId");
	$showParent = xmlParser_getValue ($xmlRequest, "showParent");

	if ($enumId == "" && $parentId == "")
		return "<items></items>";

	if ($showParent == "")
		$showParent = "1";

	$conditions = "";

	if ($enumId != "")
		$conditions .= " and enumsValues.enumId = $enumId ";

	$byText   	= xmlParser_getValue ($xmlRequest, "byText");
	if ($byText != "")
		$conditions .= " and (enumsValues_byLang.text like '%$byText%' or enumsValues.value like '%$byText%')";

	$orderBy   = xmlParser_getValue ($xmlRequest, "orderBy");

	if ($orderBy == "")
		$orderBy = "parentValueId, id";
	if ($parentId != "")
	{
		$conditions .= " and enumsValues.parentValueId = $parentId ";
		$orderBy = "id";
	}

	if ($userId == 3297)	// yelonmoreh
		$orderBy = "enumsValues_byLang.text";

	// get total
	$queryStr	 = "select count(*) from enumsValues, enumsValues_byLang 
				   where id = valueId and enumsValues_byLang.language = '$langsArray[0]' $conditions";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$total	     = $row[0];

	// get details
	$queryStr    = "select enumsValues.*, enumsValues_byLang.*, parentTable.text as parentText from (enumsValues, enumsValues_byLang)
				    left join enumsValues_byLang parentTable on enumsValues.parentValueId = parentTable.valueId
					where enumsValues.id = enumsValues_byLang.valueId and enumsValues_byLang.language = '$langsArray[0]'
		   			$conditions order by $orderBy";
	$result	     = commonDoQuery ($queryStr);

	$numRows    = commonQuery_numRows($result);

	$xmlResponse = "<items>";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		$id   		  = $row['id'];
		$parentValueId= $row['parentValueId'];

		if ($row['parentText'] != "" && $showParent == "1")
			$row['text'] = "$row[parentText] > $row[text]";

		$text 		  = commonValidXml ($row['text'],true);
		$value		  = commonValidXml ($row['value'],true);
		$pos   		  = $row['pos'];

		$xmlResponse .=	"<item>
							<enumId>$enumId</enumId>
							<parentValueId>$parentValueId</parentValueId>
							<valueId>$id</valueId>
							<text>$text</text>
							<value>$value</value>
							<pos>$pos</pos>
						 </item>";
	}

	$xmlResponse .=	"</items>" .
					commonGetTotalXml($xmlRequest,$i,$total);
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getAllValues																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function getAllValues ($xmlRequest)
{	
	global $usedLangs;
	$langsArray = explode(",",$usedLangs);

	// get enums
	$queryStr    = "select id from enums";
	$enums	     = commonDoQuery ($queryStr);
	
	$xmlResponse = "<all>";

	while ($enumRow = commonQuery_fetchRow($enums))
	{
		$enumId = $enumRow[0];

		$queryStr    = "select * from enumsValues, enumsValues_byLang 
						where enumsValues.id = enumsValues_byLang.valueId and enumsValues_byLang.language = '$langsArray[0]'
		   				and   enumsValues.enumId = $enumId order by pos";
		$result	     = commonDoQuery ($queryStr);

		$numRows     = commonQuery_numRows($result);

		$xmlResponse .= "<items$enumId>";

		for ($i = 0; $i < $numRows; $i++)
		{
			$row = commonQuery_fetchRow($result);
			
			$id   		  = $row['id'];
			$text 		  = commonValidXml ($row['text'],true);
			$value 		  = commonValidXml ($row['value'],true);
			$pos   		  = $row['pos'];

			$xmlResponse .=	"<item>
								<enumId>$enumId</enumId>
								<valueId>$id</valueId>
								<text>$text</text>
								<value>$value</value>
								<pos>$pos</pos>
							 </item>";
		}

		$xmlResponse .=	"</items$enumId>";
	}
	
	$xmlResponse .= "</all>";

	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* addValue																								*/
/* ----------------------------------------------------------------------------------------------------	*/
function addValue ($xmlRequest)
{
	return (editValue ($xmlRequest, "add"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateValue																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateValue ($xmlRequest)
{
	editValue ($xmlRequest, "update");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* editValue																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function editValue ($xmlRequest, $editType)
{
	global $usedLangs;
	global $userId;

	if ($editType == "add")
	{
		// get next value id
		$queryStr	= "select max(id) from enumsValues";
		$result		= commonDoQuery ($queryStr);
		$row		= commonQuery_fetchRow ($result);
		$valueId 	= $row[0] + 1;

		if ($userId == 3297)	// yelonmoreh
		{
			$enumId	= xmlParser_getValue($xmlRequest, "enumId");
			$text	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "textHEB")));

			$sql	= "select * from enumsValues, enumsValues_byLang where id = valueId and text = '$text' and enumId = $enumId";
			$result	= commonDoQuery($sql);

			if (commonQuery_numRows($result) != 0)
				trigger_error (iconv("windows-1255", "utf-8", "ערך זה כבר קיים ברשימה זו"));
		}
	}
	else // update
	{
		$valueId	= xmlParser_getValue($xmlRequest, "valueId");

		if ($valueId == "")
			trigger_error ("חסר קוד ערך לביצוע הפעולה");

		# delete all languages rows
		# ----------------------------------------------------------------------------------------------
		$queryStr = "delete from enumsValues_byLang where valueId=$valueId";
		commonDoQuery ($queryStr);
	}
	
	# add enum value
	# ------------------------------------------------------------------------------------------------------
	$langsArray = explode(",",$usedLangs);

	for ($i=0; $i<count($langsArray); $i++)
	{
		$language		= $langsArray[$i];

		$text			= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "text$language")));

		$queryStr		= "insert into enumsValues_byLang (valueId, language, text)
						   values ('$valueId', '$language','$text')";
						   
		commonDoQuery ($queryStr);
	}

	# ------------------------------------------------------------------------------------------------------
	
	$enumId			= xmlParser_getValue($xmlRequest, "enumId");
	$parentValueId	= xmlParser_getValue($xmlRequest, "parentValueId");
	$value			= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "value")));
	$pos			= xmlParser_getValue($xmlRequest, "pos");

	if ($parentValueId == "") $parentValueId = 0;
	if ($pos == "") $pos = 0;

	if ($editType == "add")
	{
		if ($parentValueId == 0)
		{
			$queryStr = "update enumsValues set pos = pos+1 where enumId = $enumId and pos >= $pos";
			commonDoQuery ($queryStr);
		}

		$queryStr = "insert into enumsValues (id, enumId, parentValueId, value, pos)
					 values ($valueId, $enumId, '$parentValueId', '$value', $pos)";
		commonDoQuery ($queryStr);
	}
	else // update
	{
		if ($parentValueId == 0)	// TBD - support pos with parentValueId
		{
			// get curr value pos
			$queryStr    = "select pos from enumsValues where id=$valueId";
			$result	     = commonDoQuery ($queryStr);
			$row	     = commonQuery_fetchRow($result);
			$currPos	 = $row[0];
	
			if ($currPos > $pos)
			{
				$queryStr = "update enumsValues set pos = pos+1 
							 where enumId = $enumId and pos >= $pos and pos < $currPos";
				$result	     = commonDoQuery ($queryStr);
			}
	
			if ($currPos < $pos)
			{
				$queryStr = "update enumsValues set pos = pos-1 
							 where enumId = $enumId and pos > $currPos and pos <= $pos";
				$result	     = commonDoQuery ($queryStr);
			}
		}

		$queryStr = "update enumsValues set	parentValueId	= $parentValueId,
											pos	  			= '$pos',
											value 			= '$value'
					 where id=$valueId";
		commonDoQuery ($queryStr);
	}

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteValue																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteValue ($xmlRequest)
{
	$enumId  = xmlParser_getValue ($xmlRequest, "enumId");
	$valueId = xmlParser_getValue ($xmlRequest, "valueId");

	if ($valueId == "")
		trigger_error ("חסר קוד ערך לביצוע הפעולה");

	// get value pos
	$queryStr	= "select pos from enumsValues where id=$valueId";
	$result		= commonDoQuery ($queryStr);
	$row		= commonQuery_fetchRow($result);
	$valuePos	= $row[0];

	if ($valuePos != "")
	{
		$queryStr = "update enumsValues set pos = pos-1 where enumId=$enumId and pos > $valuePos";
		commonDoQuery ($queryStr);
	}

	$queryStr =  "delete from enumsValues where id=$valueId";
	commonDoQuery ($queryStr);

	$queryStr =  "delete from enumsValues_byLang where valueId=$valueId";
	commonDoQuery ($queryStr);

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getValueDetails																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getValueDetails ($xmlRequest)
{
	global $usedLangs;

	$valueId		= xmlParser_getValue($xmlRequest, "valueId");

	if ($valueId == "")
		trigger_error ("חסר קוד ערך לביצוע הפעולה");

	$queryStr	= "select * from enumsValues, enumsValues_byLang 
				   where enumsValues.id = enumsValues_byLang.valueId
				   and id=$valueId";
	$result		= commonDoQuery ($queryStr);

	if (commonQuery_numRows($result) == 0)
		trigger_error ("קוד רשימה זו ($enumId) לא קיים במערכת. לא ניתן לבצע את העדכון");

	$langsArray = explode(",",$usedLangs);

	$xmlResponse = "";
	
	while ($row = commonQuery_fetchRow($result))
	{
		$language = $row['language'];

		$langsArray = commonArrayRemove ($langsArray, $language);	

		if ($xmlResponse == "")
		{
			$id			   = $row['id'];
			$parentValueId = $row['parentValueId'];
			$enumId		   = $row['enumId'];
			$value		   = commonValidXml($row['value']);
			$pos		   = $row['pos'];
	
			$xmlResponse .= "<valueId>$id</valueId>
							 <parentValueId>$parentValueId</parentValueId>
							 <enumId>$enumId</enumId>
							 <value>$value</value>
							 <pos>$pos</pos>";
		}

		$text		= commonValidXml($row['text']);
	
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


?>
