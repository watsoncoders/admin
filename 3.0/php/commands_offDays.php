<?php

/* ----------------------------------------------------------------------------------------------------	*/
/* getConfig																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function getConfig ($xmlRequest)
{
	$queryStr    = "select * from offDays_config";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);

	$xmlResponse = "<config>
						<saturdayLevel>$row[saturdayLevel]</saturdayLevel>
						<saturdayMinStartHour>$row[saturdayMinStartHour]</saturdayMinStartHour>
						<saturdayMaxEndHour>$row[saturdayMaxEndHour]</saturdayMaxEndHour>
						<offPage>$row[offPage]</offPage>
					</config>";

	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateConfig																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateConfig ($xmlRequest)
{
	$saturdayLevel			= commonDecode(xmlParser_getValue($xmlRequest, "saturdayLevel"));
	$saturdayMinStartHour 	= commonDecode(xmlParser_getValue($xmlRequest, "saturdayMinStartHour"));
	$saturdayMaxEndHour	 	= commonDecode(xmlParser_getValue($xmlRequest, "saturdayMaxEndHour"));
	$offPage	 			= commonDecode(xmlParser_getValue($xmlRequest, "offPage"));

	$queryStr = "update offDays_config set  saturdayLevel 			= '$saturdayLevel', 
											saturdayMinStartHour 	= '$saturdayMinStartHour',
											saturdayMaxEndHour 		= '$saturdayMaxEndHour',
											offPage					= '$offPage'";
	commonDoQuery ($queryStr);

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getOffDays																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function getOffDays ($xmlRequest)
{
	// get total
	$queryStr	 = "select count(*) from offDays";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$total	     = $row[0];

	// get details
	$queryStr    = "select * from offDays order by offDay";
	$result		 = commonDoQuery($queryStr);
	$numRows     = commonQuery_numRows($result);

	$xmlResponse = "<items>";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		$id    			= $row['id'];
		$offDay			= formatApplDate ($row['offDay']);
		$dayDesc		= commonValidXml ($row['dayDesc']);


		$xmlResponse .=	"<item>
							<id>$id</id>
							<offDay>$offDay</offDay>
							<dayDesc>$dayDesc</dayDesc>
						</item>";
	}

	$xmlResponse .=	"</items>"												.
					commonGetTotalXml($xmlRequest,$numRows,$total);
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* addOffDay																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function addOffDay ($xmlRequest)
{
	return (editOffDay ($xmlRequest, "add"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateOffDay																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateOffDay ($xmlRequest)
{
	editOffDay ($xmlRequest, "update");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* editOffDay																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function editOffDay ($xmlRequest, $editType)
{
	$id			= xmlParser_getValue($xmlRequest, "id");
	$offDay		= formatApplToDB(xmlParser_getValue($xmlRequest, "offDay"));
	$dayDesc	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "dayDesc")));

	$queryStr	= "select * from offDays where offDay = '$offDay'";
	$result		= commonDoQuery($queryStr);

	if (commonQuery_numRows($result) != 0)
		trigger_error ("תאריך זה כבר קיים במערכת");

	$queryStr   = "select max(id) from offDays";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$id		     = $row[0]+1;

	$queryStr = "insert into offDays (id, offDay, dayDesc) values ('$id', '$offDay', '$dayDesc')";
	commonDoQuery ($queryStr);

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteOffDay																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteOffDay ($xmlRequest)
{
	$id = xmlParser_getValue ($xmlRequest, "id");

	if ($id == "")
		trigger_error ("חסר קוד לביצוע הפעולה");

	$queryStr = "delete from offDays where id = $id";
	commonDoQuery ($queryStr);

	return "";
}

?>
