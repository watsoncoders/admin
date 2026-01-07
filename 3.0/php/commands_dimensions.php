<?php

/* ----------------------------------------------------------------------------------------------------	*/
/* getDimensions																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getDimensions ($xmlRequest)
{	
	// get total
	$queryStr	 = "select count(*) from dimensions";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$total	     = $row[0];

	// get details
	$queryStr    = "select * from dimensions order by id";
	$result	     = commonDoQuery ($queryStr);

	$numRows    = commonQuery_numRows($result);

	// siteUrl
	$domainRow   = commonGetDomainRow ();
	$siteUrl     = commonGetDomainName($domainRow);

	commonConnectToUserDB ($domainRow);

	$pressText   = commonPhpEncode("לחץ כאן");

	$xmlResponse = "<items>";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		$id   		  = $row['id'];
		$description  = commonValidXml ($row['description'],true);
		$width 		  = $row['width'];
		$height		  = $row['height'];
		$color		  = $row['color'];
		$forceSize	  = $row['forceSize'];
		$allowCrop	  = $row['allowCrop'];

		if ($forceSize == "1")
			$forceSizeText = commonPhpEncode ("כן");
		else
			$forceSizeText = commonPhpEncode ("לא");

		if ($allowCrop == "1")
			$allowCropText = commonPhpEncode ("כן");
		else
			$allowCropText = commonPhpEncode ("לא");

		$watermarkFile  = commonValidXml($row['watermarkFile']);
		$linkToFile	    = "$siteUrl/dimensionsFiles/$row[watermarkFile]";

		$show	 = "";
		$delete  = "";

		if ($row['watermarkFile']  != "") 
		{
			$show   = $pressText;
			$delete = $pressText;
		}
			
		$xmlResponse .=	"<item>
							<id>$id</id>
							<description>$description</description>
							<width>$width</width>
							<height>$height</height>
							<dimensions>   $width X $height</dimensions>
							<color>$color</color>
							<forceSize>$forceSize</forceSize>
							<forceSizeText>$forceSizeText</forceSizeText>
							<allowCrop>$allowCrop</allowCrop>
							<allowCropText>$allowCropText</allowCropText>
							<watermarkFile>$watermarkFile</watermarkFile>
							<formSourceFile>$watermarkFile</formSourceFile>
							<show>$show</show>
							<delete>$delete</delete>
							<linkToFile>$linkToFile</linkToFile>
						 </item>";
	}

	$xmlResponse .=	"</items>";
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* addDimension																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function addDimension ($xmlRequest)
{
	return (editDimension ($xmlRequest, "add"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* doesDimensionExist																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function doesDimensionExist ($id)
{
	$queryStr	= "select count(*) from dimensions where id=$id";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$count	     = $row[0];

	return ($count > 0);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getDimensionNextId																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function getDimensionNextId ()
{
	$queryStr	= "select max(id) from dimensions";
	$result		= commonDoQuery ($queryStr);
	$row		= commonQuery_fetchRow ($result);
	$id 		= $row[0] + 1;
	
	return $id;
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateDimension																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateDimension ($xmlRequest)
{
	editDimension ($xmlRequest, "update");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* editDimension																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function editDimension ($xmlRequest, $editType)
{
	global $usedLangs;
	global $userId;
	global $ibosHomeDir;

	$id		= xmlParser_getValue($xmlRequest, "id");

	if ($editType == "update")
	{
		if (!doesDimensionExist($id))
		{
			trigger_error ("ממד עם קוד זה ($id) לא קיימת במערכת. לא ניתן לבצע את העדכון");
		}
	}

	$description = addslashes(commonDecode(xmlParser_getValue($xmlRequest,"description")));
	$width		 = xmlParser_getValue($xmlRequest,"width");
	$height		 = xmlParser_getValue($xmlRequest,"height");
	$color		 = xmlParser_getValue($xmlRequest,"color");
	$forceSize	 = xmlParser_getValue($xmlRequest,"forceSize");
	$allowCrop	 = xmlParser_getValue($xmlRequest,"allowCrop");

	// handle picture 
	# ------------------------------------------------------------------------------------------------------
	$watermarkFile	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "watermarkFile")));	
	$fileDeleted	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "fileDeleted")));	

	$fileDeleted	= ($fileDeleted == "1");
	$fileLoaded  	= false;

	$suffix 		= "";
	$dbFile			= "";
	if ($watermarkFile != "")
	{
		$suffix		= commonFileSuffix ($watermarkFile);

		$fileLoaded = true;

		$dbFile		= "$id$suffix";
	}

	$oldFile = "";

	if ($editType == "add")
	{
		$id 	  = getDimensionNextId();

		$queryStr = "insert into dimensions (id, description, width, height, color, forceSize, watermarkFile, allowCrop) 
					 values ($id, '$description', $width, $height, '$color', '$forceSize', '$dbFile', '$allowCrop')";

	}
	else // update
	{
		// find old watermarkFile
		$queryStr = "select watermarkFile from dimensions where id = $id";
		$result	  = commonDoQuery($queryStr);
		$row	  = commonQuery_fetchRow($result);

		$oldFile  = $row['watermarkFile'];

		$queryStr = "update dimensions set description   = '$description',
					   					   width	     =  $width,
							   			   height      	 =  $height,
										   color       	 = '$color',
										   forceSize   	 = '$forceSize',
										   allowCrop   	 = '$allowCrop',
										   watermarkFile = '$dbFile'
					 where id=$id";
	}

	commonDoQuery ($queryStr);

	// handle file
	$filePath = "$ibosHomeDir/html/SWFUpload/files/$userId";

	if ($fileLoaded)
	{
		$domainRow	= commonGetDomainRow();

		$connId = commonFtpConnect($domainRow); 

		ftp_chdir ($connId, "dimensionsFiles");

		$upload = ftp_put($connId, $dbFile, "$filePath/$watermarkFile", FTP_BINARY); 

		unlink("$filePath/$watermarkFile");

		commonFtpDisconnect ($connId);
	}
	else if ($fileDeleted && $oldFile != "")
	{
		$domainRow	= commonGetDomainRow();

		$connId = commonFtpConnect($domainRow); 

		commonFtpDelete($connId, "$oldFile");
	}

 	// delete old files
	commonDeleteOldFiles ($filePath, 3600);	// 1 hour

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteDimension																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteDimension ($xmlRequest)
{
	$id  = xmlParser_getValue ($xmlRequest, "id");

	if ($id == "")
		trigger_error ("חסר קוד ממד לביצוע הפעולה");

	$queryStr =  "delete from dimensions where id=$id";
	commonDoQuery ($queryStr);

	return "";
}


?>
