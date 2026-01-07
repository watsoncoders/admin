<?php

include "picsTools.php";

/* ----------------------------------------------------------------------------------------------------	*/
/* getCountries																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function getCountries ($xmlRequest)
{	
	global $usedLangs;
	$langsArray = explode(",",$usedLangs);

	$sortBy		= xmlParser_getValue($xmlRequest,"sortBy");

	if ($sortBy == "")
		$sortBy = "id";

	$sortDir	= xmlParser_getValue($xmlRequest,"sortDir");
	if ($sortDir == "")
		$sortDir = "asc";

	$conditions = "";

	$parentId	= xmlParser_getValue($xmlRequest, "parentId");
	if ($parentId != "")
		$conditions .= " and parentId = '$parentId' ";

	$withoutId	= xmlParser_getValue($xmlRequest, "withoutId");
	if ($withoutId != "")
		$conditions .= " and countries.id != $withoutId ";

	$continent	= xmlParser_getValue($xmlRequest, "continent");
	if ($continent != "")
		$conditions .= " and continent = '$continent' ";

	$name	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "name")));
	if ($name != "")
		$conditions .= " and countries_byLang.name like '%$name%' ";


	// get total
	$queryStr	 = "select count(*) 
					from (countries, countries_byLang)
					left join countries_byLang b on countries.parentId = b.countryId and b.language = '$langsArray[0]'
					where countries.id = countries_byLang.countryId and countries_byLang.language = '$langsArray[0]' $conditions";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$total	     = $row[0];

	// get details
	$queryStr	 = str_replace("count(*)", "countries.*, countries_byLang.*, b.name as parent", $queryStr);
	$queryStr	.= "order by $sortBy $sortDir " . commonGetLimit ($xmlRequest);
	$result	     = commonDoQuery ($queryStr);

	$numRows    = commonQuery_numRows($result);

	$xmlResponse = "<items>";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		$id   		  = $row['id'];
		$name		  = commonValidXml($row['name']);
		$parent		  = commonValidXml($row['parent']);
		$phonePrefix  = commonValidXml($row['phonePrefix']);
		$continent	  = formatContinent($row['continent']);

		$xmlResponse .=	"<item>
							<id>$id</id>
							<name>$name</name>
							<parentId>$parent</parentId>
							<continent>$continent</continent>
							<phonePrefix>$phonePrefix</phonePrefix>
						 </item>";
	}

	$xmlResponse .=	"</items>"												.
					commonGetTotalXml($xmlRequest,$numRows,$total);
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getCountryDetails																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function getCountryDetails ($xmlRequest)
{
	global $usedLangs;

	$id		= xmlParser_getValue($xmlRequest, "id");

	if ($id == "")
		trigger_error ("חסר קוד מדינה לביצוע הפעולה");

	$queryStr	= "select countries.*, countries_byLang.* 
				   from countries, countries_byLang
				   where countries.id = countries_byLang.countryId and id=$id";
	$result		= commonDoQuery ($queryStr);

	if (commonQuery_numRows($result) == 0)
		trigger_error ("מדינה עם קוד זה ($id) לא קיימת במערכת. לא ניתן לבצע את העדכון");

	$langsArray = explode(",",$usedLangs);

	// siteUrl
	$domainRow   = commonGetDomainRow ();
	$siteUrl     = commonGetDomainName($domainRow);

	$xmlResponse = "";

	while ($row = commonQuery_fetchRow($result))
	{
		$language = $row['language'];

		$langsArray = commonArrayRemove ($langsArray, $language);	

		if ($xmlResponse == "")
		{
			$continent	  = $row['continent'];
			$parentId	  = $row['parentId'];
			$phonePrefix  = $row['phonePrefix'];
			$picFile 	  = commonValidXml ($row['picFile'], true);
			$sourceFile	  = commonCData(commonEncode($row['sourceFile']));

			$fullFileName  = "$siteUrl/countriesFiles/$row[picFile]";
			
			$pressText     = commonPhpEncode("לחץ כאן");

			$show	 = "";
			$delete  = "";

			if ($row['picFile']  != "") 
			{
				$show   = $pressText;
				$delete = $pressText;
			}


			$xmlResponse  =	"<id>$id</id>
							 <continent>$continent</continent>
							 <parentId>$parentId</parentId>
							 <phonePrefix>$phonePrefix</phonePrefix>
							 <usedLangs>$usedLangs</usedLangs>
							 <sourceFile>$sourceFile</sourceFile>
							 <formSourceFile>$sourceFile</formSourceFile>
							 <fullFileName>$fullFileName</fullFileName>
							 <show>$show</show>
							 <delete>$delete</delete>";
		}

		$name			  = commonValidXml($row['name']);
		$description	  = commonValidXml($row['description']);

		$xmlResponse     .= "<name$language>$name</name$language>
					         <description$language>$description</description$language>";
	}

	// add missing languages
	// ------------------------------------------------------------------------------------------------
	for ($i=0; $i<count($langsArray); $i++)
	{
		$language	  = $langsArray[$i];

		$xmlResponse .=	   "<name$language><![CDATA[]]></name$language>
					        <description$language><![CDATA[]]></description$language>";
	}

	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteCountry																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteCountry ($xmlRequest)
{
	$id = xmlParser_getValue($xmlRequest, "id");

	if ($id == "")
		trigger_error ("חסר קוד מדינה לביצוע הפעולה");

	$queryStr = "delete from countries where id = $id";
	commonDoQuery ($queryStr);

	$queryStr = "delete from countries_byLang where countryId = $id";
	commonDoQuery ($queryStr);

	return "";	
}

/* ----------------------------------------------------------------------------------------------------	*/
/* addCountry																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function addCountry ($xmlRequest)
{
	return (editCountry ($xmlRequest, "add"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateCountry																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateCountry ($xmlRequest)
{
	editCountry ($xmlRequest, "update");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* editCountry																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function editCountry ($xmlRequest, $editType)
{
	global $usedLangs;
	global $userId;
	global $ibosHomeDir;

	$id				= xmlParser_getValue($xmlRequest, "id");
	$parentId		= xmlParser_getValue($xmlRequest, "parentId");
	$continent		= xmlParser_getValue($xmlRequest, "continent");
	$phonePrefix	= xmlParser_getValue($xmlRequest, "phonePrefix");

	$oldFile		= "";

	if ($editType == "add")
	{
		$queryStr = "select max(id) from countries";
		$result	  = commonDoQuery ($queryStr);
		$row	  = commonQuery_fetchRow ($result);
		$id 	  = $row[0] + 1;
	}
	else
	{
		if ($id == "")
			trigger_error ("חסר קוד מדינה");

		$queryStr	= "select * from countries where id = $id";
		$result	  	= commonDoQuery ($queryStr);
		$row	  	= commonQuery_fetchRow ($result);

		$oldFile	= $row['picFile'];

	}

	// handle picture 
	$sourceFile		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "sourceFile")));	
	$dimensionId	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "dimensionId")));	
	$fileDeleted	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "fileDeleted")));	

	$fileLoaded  	= false;
	$fileDeleted	= ($fileDeleted == "1");

	$picFile 		= "";

	$suffix 		= "";
	if ($sourceFile != "")
	{
		$fileLoaded = true;
		
		$suffix		= commonFileSuffix ($sourceFile);

		$picFile 	= $id . $suffix;

		if ($dimensionId == 0)
		{
			$picWidth  = 0;
			$picHeight = 0;
			$bgColor   = "#FFFFFF";
		}
		else
		{
			list($picWidth, $picHeight, $bgColor) = commonGetDimensionDetails ($dimensionId);
		}
	}

	if ($suffix == "." . $sourceFile)	// wrong file name - don't load it
	{
		$fileLoaded = false;
		$picFile    = "";
	}

	if ($editType == "add")
	{
		$queryStr = "insert into countries (id, continent, phonePrefix, parentId, picFile, sourceFile) 
					 values ($id, '$continent', '$phonePrefix', '$parentId', '$picFile', '$sourceFile')";
		commonDoQuery ($queryStr);
	}
	else
	{
			$queryStr 	= "delete from countries_byLang where countryId=$id";
			commonDoQuery ($queryStr);

			$queryStr = "update countries set continent  = '$continent', phonePrefix = '$phonePrefix', parentId = '$parentId' ";

			if ($fileLoaded)
			{
				$queryStr	.= ",		   picFile 		= '$picFile',
										   sourceFile 	= '$sourceFile'";
			}
			else if ($fileDeleted)
			{
				$queryStr .= ",	  	 	   picFile     = '',
									 	   sourceFile  = '' ";
			}

			$queryStr .= " where id=$id";
	
			commonDoQuery ($queryStr);
	}
	
	# add languages rows for this country
	# ------------------------------------------------------------------------------------------------------
	$langsArray = explode(",",$usedLangs);

	for ($i=0; $i<count($langsArray); $i++)
	{
		$language	= $langsArray[$i];

		$name 		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "name$language")));
		$description= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "description$language")));

		$queryStr		= "insert into countries_byLang (countryId, language, name, description)
						   values ('$id', '$language','$name', '$description')";
		commonDoQuery ($queryStr);
	}
	
	// handle file
	$filePath = "$ibosHomeDir/html/SWFUpload/files/$userId/";

	$domainRow	= commonGetDomainRow();

	$connId = commonFtpConnect($domainRow); 

	ftp_chdir($connId, "countriesFiles/");

	if ($fileLoaded)
	{

		if ($picWidth == 0)	// keep size
		{
			$upload = ftp_put($connId, $picFile, "$filePath/$sourceFile", FTP_BINARY);
		}
		else
		{
			picsToolsResize("$filePath/$sourceFile", $suffix, $picWidth, $picHeight, "/../../tmp/$picFile", $bgColor);
	
			$upload = ftp_put($connId, $picFile, "/../../tmp/$picFile", FTP_BINARY);
		}

		unlink("$filePath/$sourceFile");

	}
	else if ($fileDeleted)
	{
		commonFtpDelete ($connId, $oldFile);
	}

	commonFtpDisconnect ($connId);

 	// delete old files
	commonDeleteOldFiles ($filePath, 3600);	// 1 hour
}



?>
