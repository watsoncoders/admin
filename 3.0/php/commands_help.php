<?php

/* ----------------------------------------------------------------------------------------------------	*/
/* getHelpRows																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function getHelpRows ($xmlRequest)
{
	global $usedLangs;
	$langsArray = explode(",",$usedLangs);

	// get total
	$queryStr	 = "select count(*) from help";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$total	     = $row[0];

	// get details
	$queryStr    = "select * from help, help_byLang where help.id = help_byLang.helpId and help_byLang.language = '$langsArray[0]' order by id";
	$result		 = commonDoQuery($queryStr);
	$numRows     = commonQuery_numRows($result);

	$xmlResponse = "<items>";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		$id    			= $row['id'];
		$code			= commonValidXml ($row['code']);
		$name			= commonValidXml ($row['name']);
		$helpText		= commonValidXml (commonCutText($row['helpText'],50));


		$xmlResponse .=	"<item>
							<id>$id</id>
							<name>$name</name>
							<code>$code</code>
							<helpText>$helpText</helpText>
						</item>";
	}

	$xmlResponse .=	"</items>"												.
					commonGetTotalXml($xmlRequest,$numRows,$total);
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getHelpRowDetails																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function getHelpRowDetails ($xmlRequest)
{
	global $usedLangs;
	$langsArray = explode(",",$usedLangs);

	$id		= xmlParser_getValue($xmlRequest, "id");

	if ($id == "")
		trigger_error ("חסר קוד לביצוע הפעולה");

	$queryStr    = "select * from help, help_byLang where help.id = help_byLang.helpId and id=$id";
	$result		= commonDoQuery ($queryStr);

	if (commonQuery_numRows($result) == 0)
		trigger_error ("שורת עזרה עם קוד זה ($id) לא קיימת במערכת. לא ניתן לבצע את הפעולה");

	$xmlResponse = "";

	while ($row = commonQuery_fetchRow($result))
	{
		$language = $row['language'];

		$langsArray = commonArrayRemove ($langsArray, $language);	

		if ($xmlResponse == "")
		{
			$code		= commonValidXml($row['code']);

			$xmlResponse =	"<id>$id</id>
							 <code>$code</code>";
		}

		$name		= commonValidXml($row['name']);
		$helpText	= commonValidXml($row['helpText']);

		$xmlResponse  .= "<name$language>$name</name$language>
						  <helpText$language>$helpText</helpText$language>";
	}

	return $xmlResponse;
}

/* ----------------------------------------------------------------------------------------------------	*/
/* addHelpRow																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function addHelpRow ($xmlRequest)
{
	return (editHelpRow ($xmlRequest, "add"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* doesHelpRowExist																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function doesHelpRowExist ($id)
{
	$queryStr	= "select count(*) from help where id=$id";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$count	     = $row[0];

	return ($count > 0);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateHelpRow																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateHelpRow ($xmlRequest)
{
	editHelpRow ($xmlRequest, "update");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* editHelpRow																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function editHelpRow ($xmlRequest, $editType)
{
	global $usedLangs;

	$id			= xmlParser_getValue($xmlRequest, "id");

	if ($editType == "update")
	{
		if ($id == "")
			trigger_error ("חסר קוד לביצוע הפעולה");

		if (!doesHelpRowExist($id))
		{
			trigger_error ("שורת עזרה עם קוד זה ($id) לא קיים במערכת. לא ניתן לבצע את העדכון");
		}
	}

	$code		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "code")));
	$name		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "name")));
	$helpText	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "helpText")));

	if ($editType == "add")
	{
		$queryStr   = "select max(id) from help";
		$result	     = commonDoQuery ($queryStr);
		$row	     = commonQuery_fetchRow($result);
		$id		     = $row[0]+1;

		$queryStr = "insert into help (id, code) values ('$id', '$code')";
	}
	else
	{
		$queryStr = "update help set code = '$code' where id=$id";
	}
	commonDoQuery ($queryStr);

	# delete all languages rows
	# ------------------------------------------------------------------------------------------------------
	$queryStr = "delete from help_byLang where helpId='$id'";
	commonDoQuery ($queryStr);
	
	# add languages rows for this user
	# ------------------------------------------------------------------------------------------------------
	$langsArray = explode(",",$usedLangs);

	for ($i=0; $i<count($langsArray); $i++)
	{
		$language		= $langsArray[$i];

		$name		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "name$language")));
		$helpText	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "helpText$language")));

		$queryStr		= "insert into help_byLang (helpId, language, name, helpText)
						   values ('$id','$language','$name', '$helpText')";
	
		commonDoQuery ($queryStr);
	}

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteHelpRow																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteHelpRow ($xmlRequest)
{
	$id = xmlParser_getValue ($xmlRequest, "id");

	if ($id == "")
		trigger_error ("חסר קוד לביצוע הפעולה");

	$queryStr = "delete from help where id = $id";
	commonDoQuery ($queryStr);

	$queryStr = "delete from help_byLang where helpId = $id";
	commonDoQuery ($queryStr);

	return "";
}

?>
