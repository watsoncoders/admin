<?php

require_once "picsTools.php";

$tags 	  = Array("id", "status", "areaId", "phone", "mobile", "fax", "email", "siteUrl", 
				  "extraData1", "extraData2", "extraData3", "extraData4", "extraData5");

$langTags = Array("name", "bizName", "address", "description");

/* ----------------------------------------------------------------------------------------------------	*/
/* getProducers																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function getProducers ($xmlRequest)
{
	global $usedLangs;
	$langsArray = explode(",",$usedLangs);

	$conditions  = "";

	$name 		 = commonDecode(xmlParser_getValue($xmlRequest, "name"));
	if ($name != "")
		$conditions .= " and shopProducers_byLang.name like '%$name%' ";
	
	$areaId		 = xmlParser_getValue($xmlRequest, "areaId");
	if ($areaId != "" && $areaId != "-1")
		$conditions .= " and shopProducers.areaId = $areaId ";
		
	$status 		 = commonDecode(xmlParser_getValue($xmlRequest, "status"));
	if ($status != "")
		$conditions .= " and shopProducers.status = '$status' ";
	
	$areaId		 = xmlParser_getValue($xmlRequest, "areaId");
	// get total
	$queryStr	 = "select count(*) 
					from shopProducers, shopProducers_byLang
					where shopProducers.id     	    = shopProducers_byLang.producerId and shopProducers_byLang.language  = '$langsArray[0]'
					$conditions";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$total	     = $row[0];

	// get details
	$queryStr    = "select shopProducers.*, shopProducers_byLang.*, 
						   areas_byLang.name as areaName
					from shopProducers
					left join shopProducers_byLang on shopProducers.id=shopProducers_byLang.producerId and shopProducers_byLang.language='$langsArray[0]'
					left join areas_byLang on shopProducers.areaId = areas_byLang.areaId and areas_byLang.language = '$langsArray[0]'
					where 1
					$conditions
					order by shopProducers.id desc " . commonGetLimit ($xmlRequest);
	$result	     = commonDoQuery ($queryStr);

	$numRows    = commonQuery_numRows($result);

	$xmlResponse = "<items>";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		$id    			= $row['id'];
		$name			= commonValidXml ($row['name']);

		if ($row['areaId'] == -1)
			$areaName = commonPhpEncode("כל האזורים");
		else
			$areaName		= commonValidXml ($row['areaName']);

		$status = formatActiveStatus($row['status']);
		
		$xmlResponse .=	"<item>
							<id>$id</id>
							<status>$status</status>
							<name>$name</name>
							<area>$areaName</area>					
						</item>";
	}

	$xmlResponse .=	"</items>"												.
					commonGetTotalXml($xmlRequest,$numRows,$total);
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getExtraDataNames																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function getExtraDataNames ($xmlRequest)
{
	return (commonGetExtraDataNames("shopProducersExtraData"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getProducerDetails																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function getProducerDetails ($xmlRequest)
{
	global $usedLangs, $tags, $langTags;

	$id		= xmlParser_getValue($xmlRequest, "id");

	if ($id == "")
		trigger_error ("חסר קוד צימר לביצוע הפעולה");

	$queryStr    = "select shopProducers.*, shopProducers_byLang.*
					from shopProducers, shopProducers_byLang
					where shopProducers.id = shopProducers_byLang.producerId
				   and shopProducers.id=$id";

	$result		= commonDoQuery ($queryStr);

	if (commonQuery_numRows($result) == 0)
		trigger_error ("יצרן קוד זה ($id) לא קיים במערכת. לא ניתן לבצע את הפעולה");

	$langsArray = explode(",",$usedLangs);

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

			$picFile   	   = commonValidXml($row['picFile']);
			$sourceFile	   = commonCData(commonEncode($row['sourceFile']));
	
			$fullFileName  = urlencode($row['picFile']);
			$fullFileName  = commonValidXml($fullFileName);
	
			$xmlResponse .= "<usedLangs>$usedLangs</usedLangs>
							 <sourceFile>$sourceFile</sourceFile>
							 <formSourceFile>$sourceFile</formSourceFile>
							 <fullFileName>$fullFileName</fullFileName>";
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
			eval ("\$xmlResponse .=	\"<$langTags[$j]\$language><![CDATA[]]></$langTags[$j]\$language>\";");
		}
	}

	return $xmlResponse;
}

/* ----------------------------------------------------------------------------------------------------	*/
/* addProducer																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function addProducer ($xmlRequest)
{
	return (editProducer ($xmlRequest, "add"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateProducer																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateProducer ($xmlRequest)
{
	editProducer ($xmlRequest, "update");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* editProducer																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function editProducer ($xmlRequest, $editType)
{
	global $usedLangs, $tags, $langTags;
	global $userId;
	global $ibosHomeDir;

	for ($i=0; $i < count($tags); $i++)
	{
		eval ("\$$tags[$i] = commonDecode(xmlParser_getValue(\$xmlRequest,\"$tags[$i]\"));");	
	}

	$id   = xmlParser_getValue($xmlRequest, "id");

	if ($editType == "add")
	{
		$queryStr   = "select max(id) from shopProducers";
		$result		= commonDoQuery ($queryStr);
		$row		= commonQuery_fetchRow ($result);
		$id 		= $row[0] + 1;
	}

	// handle picture 
	# ------------------------------------------------------------------------------------------------------
	$picSource		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "picSource")));	
	$fileDeleted	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "fileDeleted")));	
	$dimensionId	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "dimensionId")));	

	if ($dimensionId == "") $dimensionId = 0;

	$fileDeleted	= ($fileDeleted == "1");
	$fileLoaded  	= false;

	$picFile 		= "";

	$suffix 		= "";
	if ($picSource != "")
	{
		$fileLoaded = true;
		$suffix		= commonFileSuffix ($picSource);
		$picFile 	= "${id}.jpg";
	}

	$vals = Array();

	for ($i=0; $i < count($tags); $i++)
	{
		eval ("array_push (\$vals,\$$tags[$i]);");
	}
	
	if ($editType == "add")
	{
		$queryStr = "insert into shopProducers (" . join(",",$tags) . ", picFile, sourceFile) values ('" . join("','",$vals) . "', ";
		
		if ($fileLoaded)
			$queryStr .= "'$picFile', '$picSource'";
		else
			$queryStr .= "'', ''";

		$queryStr .= ")";

		commonDoQuery ($queryStr);
	}
	else
	{
		$queryStr = "update shopProducers set ";

		for ($i=1; $i < count($tags); $i++)
		{
			$queryStr .= "$tags[$i] = '$vals[$i]',";
		}

		if ($fileLoaded)
		{
			$queryStr .= "picFile 	 = '$picFile',
						  sourceFile = '$picSource'";
		}

		$queryStr = trim($queryStr, ",");

		$queryStr .= " where id = $id ";

		commonDoQuery ($queryStr);
	}
	
	# add languages rows for this producer
	# ------------------------------------------------------------------------------------------------------
	$langsArray = explode(",",$usedLangs);

	for ($i=0; $i<count($langsArray); $i++)
	{
		$language		= $langsArray[$i];

		$vals = Array();
		for ($j=0; $j < count($langTags); $j++)
		{
			eval ("\$$langTags[$j] = addslashes(commonDecode(xmlParser_getValue(\$xmlRequest,\"$langTags[$j]\$language\")));");	
			eval ("array_push (\$vals,\$$langTags[$j]);");
		}		

		$queryStr		= "replace into shopProducers_byLang (producerId, language," . join(",",$langTags) . ") 
						   values ($id, '$language', '" . join ("','", $vals) . "')";
		commonDoQuery ($queryStr);
	}

	if ($dimensionId == 0)
	{
		$picWidth  = 0;
		$picHeight = 0;
		$bgColor   = "#FFFFFF";
	}
	else if ($dimensionId != "")
	{
		list($picWidth, $picHeight, $bgColor) = commonGetDimensionDetails ($dimensionId);
	}

	// handle file
	$filePath = "$ibosHomeDir/html/SWFUpload/files/$userId";

	if ($fileLoaded)
	{
		$domainRow	= commonGetDomainRow();

		$connId = commonFtpConnect($domainRow); 

		ftp_chdir ($connId, "producersFiles");

		if ($picWidth == 0 && $picHeight == 0)
		{
			$upload = ftp_put($connId, $picFile, "$filePath/$picSource", FTP_BINARY);
		}
		else
		{
			picsToolsResize("$filePath/$picSource", $suffix, $picWidth, $picHeight, "/../../tmp/$picFile", $bgColor);
			$upload = ftp_put($connId, $picFile, "/../../tmp/$picFile", FTP_BINARY);
		}

		unlink("$filePath/$picSource");

		commonFtpDisconnect ($connId);
	}

 	// delete old files
	commonDeleteOldFiles ($filePath, 3600);	// 1 hour

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteProducer																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteProducer ($xmlRequest)
{
	$id = xmlParser_getValue ($xmlRequest, "id");

	if ($id == "")
		trigger_error ("חסר קוד יצרן לביצוע הפעולה");

	$queryStr = "delete from shopProducers where id = $id";
	commonDoQuery ($queryStr);

	$queryStr = "delete from shopProducers_byLang where producerId = $id";
	commonDoQuery ($queryStr);

	return "";
}

?>
