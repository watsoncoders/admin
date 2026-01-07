<?php

/* ----------------------------------------------------------------------------------------------------	*/
/* getFlags																								*/
/* ----------------------------------------------------------------------------------------------------	*/
function getFlags ($xmlRequest)
{
	$type		= xmlParser_getValue($xmlRequest, "type");

	if ($type == "")
		trigger_error ("חסר סוג רכיב הדגלים.");

	$queryStr    = "select * from flagsConfig where type = '$type' order by id";
	$result	     = commonDoQuery ($queryStr);
	$numRows     = commonQuery_numRows($result);

	$xmlResponse = "<items>";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		$id    		 = $row['id'];
		$name		 = commonValidXml ($row['name']);

		$xmlResponse .=	"<item>
							<id>$id</id>
							<name>$name</name>
						</item>";
	}

	$xmlResponse .=	"</items>";
	
	return ($xmlResponse);
}
?>
