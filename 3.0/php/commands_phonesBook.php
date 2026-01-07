<?php

include "picsTools.php";

$bookTags		= Array("id", "picDimension", "confirmType", "hideMsgEmail", "confirmPageId", "confirmDeletePageId", "errorPageId", 
						"numItemsInPage", "numPages", "numItemsBeforeBanner", "maxBannersInPage", "bannerWidth", "bannerHeight");
$bookLangTags	= Array("name");

$recordTags     = Array("id", "bookId", "status", "email", "hideEmail", "siteUrl", "facebookLink", "homePhone", "workPhone", 
						"extraPhone1", "extraPhone2", "cellphone", "fax", "workFax",
						"cityId", "areaId", "workCityId", "workAreaId", "zipcode", "workZipcode", "birthDate", "gender", 
						"enumValue1", "enumValue2", "enumValue3", "enumValue4");
$recordLangTags = Array("firstName", "lastName", "city", "address", "workCity", "workAddress", "occupation", "description", "extraData1", 
				  "extraData2", "extraData3", "extraData4", "extraData5");

/* ----------------------------------------------------------------------------------------------------	*/
/* getPhonesBooks																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getPhonesBooks ($xmlRequest)
{	
	global $usedLangs;
	$langsArray = explode(",",$usedLangs);

	$condition  = "";

	// get total
	$queryStr	 = "select count(*) from phonesBooks";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$total	     = $row[0];

	// get details
	$queryStr = "select * from phonesBooks, phonesBooks_byLang 
			     where phonesBooks.id = phonesBooks_byLang.bookId and language = '$langsArray[0]' 
				 order by id desc " . commonGetLimit ($xmlRequest);

	$result	     = commonDoQuery ($queryStr);

	$numRows    = commonQuery_numRows($result);

	$xmlResponse = "<items>";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		$id   		= $row['id'];
		$name		= commonValidXml($row['name']);

		$xmlResponse .=	"<item>
							 <id>$id</id>
							 <name>$name</name>
						 </item>";
	}

	$xmlResponse .=	"</items>" .
					commonGetTotalXml($xmlRequest,$numRows,$total);
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getBookDetails																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getBookDetails ($xmlRequest)
{
	global $usedLangs, $bookTags, $bookLangTags;

	$id		= xmlParser_getValue($xmlRequest, "bookId");

	if ($id == "")
		trigger_error ("חסר קוד ספר לביצוע הפעולה");


	$queryStr = "select * from phonesBooks, phonesBooks_byLang
				 where phonesBooks.id = phonesBooks_byLang.bookId
				 and   id = $id";
	$result   = commonDoQuery ($queryStr);

	if (commonQuery_numRows($result) == 0)
		trigger_error ("ספר קוד זה ($id) לא קיים במערכת. לא ניתן לבצע את הפעולה");

	$langsArray = explode(",",$usedLangs);

	$xmlResponse = "";

	while ($row = commonQuery_fetchRow($result))
	{
		$language = $row['language'];

		$langsArray = commonArrayRemove ($langsArray, $language);	

		if ($xmlResponse == "")
		{
			for ($i=0; $i < count($bookTags); $i++)
			{
				eval ("\$$bookTags[$i] = \$row['$bookTags[$i]'];");

			}

			for ($i=0; $i < count($bookTags); $i++)
			{
				eval ("\$$bookTags[$i] = commonValidXml(\$$bookTags[$i]);");

				eval ("\$xmlResponse .= \"<$bookTags[$i]>\$$bookTags[$i]</$bookTags[$i]>\";");
			}
			
			$xmlResponse .= "<bookId>$id</bookId>";
		}

		for ($i=0; $i < count($bookLangTags); $i++)
		{
			eval ("\$$bookLangTags[$i] = commonValidXml(\$row['$bookLangTags[$i]']);");
			eval ("\$xmlResponse .=	\"<$bookLangTags[$i]\$language>\$$bookLangTags[$i]</$bookLangTags[$i]\$language>\";");
		}
	}

	// add missing languages
	// ------------------------------------------------------------------------------------------------
	for ($i=0; $i<count($langsArray); $i++)
	{
		$language	  = $langsArray[$i];

		for ($j=0; $j < count($bookLangTags); $j++)
		{
			eval ("\$xmlResponse .=	\"<$bookLangTags[$j]\$language><![CDATA[]]></$bookLangTags[$j]\$language>\";");
		}
	}

	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* addBook																								*/
/* ----------------------------------------------------------------------------------------------------	*/
function addBook ($xmlRequest)
{
	return (editBook ($xmlRequest, "add"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* doesBookExist																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function doesBookExist ($id)
{
	$queryStr		= "select count(*) from phonesBooks where id=$id";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$count	     = $row[0];

	return ($count > 0);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getBookNextId																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getBookNextId ()
{
	$queryStr	= "select max(id) from phonesBooks";
	$result		= commonDoQuery ($queryStr);
	$row		= commonQuery_fetchRow ($result);
	$id 		= $row[0] + 1;
	
	return $id;
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateBook																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateBook ($xmlRequest)
{
	editBook ($xmlRequest, "update");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* editBook																								*/
/* ----------------------------------------------------------------------------------------------------	*/
function editBook ($xmlRequest, $editType)
{
	global $usedLangs, $bookTags, $bookLangTags;

	for ($i=0; $i < count($bookTags); $i++)
	{
		eval ("\$$bookTags[$i] = commonDecode(xmlParser_getValue(\$xmlRequest,\"$bookTags[$i]\"));");	
	}

	$id		= xmlParser_getValue($xmlRequest, "bookId");

	if ($editType == "update")
	{
		if (!doesBookExist($id))
		{
			trigger_error ("ספר עם קוד זה ($id) לא קיימת במערכת. לא ניתן לבצע את העדכון");
		}
	}
	else
	{
		$id = getBookNextId ();
	}

	$vals = Array();

	for ($i=0; $i < count($bookTags); $i++)
	{
		eval ("array_push (\$vals,\$$bookTags[$i]);");
	}
	
	if ($editType == "update")
	{
		$queryStr = "update phonesBooks set ";

		for ($i=1; $i < count($bookTags); $i++)
		{
			$queryStr .= "$bookTags[$i] = '$vals[$i]',";
		}

		$queryStr = trim($queryStr, ",");

		$queryStr .= " where id = $id ";

		commonDoQuery ($queryStr);
	}
	else
	{
		$queryStr = "insert into phonesBooks (" . join(",",$bookTags) . ",insertTime) values ('" . join("','",$vals) . "',now())";
		commonDoQuery ($queryStr);
	}

	# delete all languages rows
	# ------------------------------------------------------------------------------------------------------
	$queryStr = "delete from phonesBooks_byLang where bookId='$id'";
	commonDoQuery ($queryStr);
	
	# add languages rows for this user
	# ------------------------------------------------------------------------------------------------------
	$langsArray = explode(",",$usedLangs);

	for ($i=0; $i<count($langsArray); $i++)
	{
		$language		= $langsArray[$i];

		$vals = Array();
		for ($j=0; $j < count($bookLangTags); $j++)
		{
			eval ("\$$bookLangTags[$j] = addslashes(commonDecode(xmlParser_getValue(\$xmlRequest,\"$bookLangTags[$j]\$language\")));");	
			eval ("array_push (\$vals,\$$bookLangTags[$j]);");
		}		

		$queryStr		= "insert into phonesBooks_byLang (bookId, language," . join(",",$bookLangTags) . ") 
						   values ($id, '$language', '" . join ("','", $vals) . "')";
	
		commonDoQuery ($queryStr);
	}
	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteBook																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteBook ($xmlRequest)
{
	$id  = xmlParser_getValue ($xmlRequest, "bookId");

	if ($id == "")
		trigger_error ("חסר קוד ספר לביצוע הפעולה");

	// delete all phonebook record
	$queryStr =  "delete from phonesRecords_byLang where recordId in (select id from phonesRecords where bookId = $id)";
	commonDoQuery ($queryStr);

	$queryStr =  "delete from phonesRecords where bookId=$id";
	commonDoQuery ($queryStr);

	$queryStr =  "delete from phonesBooks where id=$id";
	commonDoQuery ($queryStr);

	$queryStr =  "delete from phonesBooks_byLang where bookId=$id";
	commonDoQuery ($queryStr);

	return "";
}


/* ----------------------------------------------------------------------------------------------------	*/
/* getPhonesRecords																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getPhonesRecords ($xmlRequest)
{	
	global $usedLangs;
	$langsArray = explode(",",$usedLangs);

	$condition  = "";

	$bookId		= xmlParser_getValue($xmlRequest, "bookId");

	if ($bookId != "")
		$condition .= " and bookId = $bookId ";

	$name = addslashes(commonDecode(xmlParser_getValue($xmlRequest, "name")));
	$address = addslashes(commonDecode(xmlParser_getValue($xmlRequest, "address")));
	if ($address != "")
		$condition .= " and (city like '%$address%' or address like '%$address%' ) ";

	$joins		= "";
	$nameCond   = "";
	$addrCond   = "";

	$langsArray = explode(",",$usedLangs);

	if ($name == "" && $address == "")
		$countLangs = 1;
	else
		$countLangs = count($langsArray);

	for ($i=0; $i<$countLangs; $i++)
	{
		$language	 = $langsArray[$i];

		$joins		.= " left join phonesRecords_byLang pbl$i on id=pbl$i.recordId and pbl$i.language='$language'";

		if ($name != "")
		{
			if ($nameCond == "")
				$nameCond = " and (";
			else
				$nameCond .= " or ";

			$nameCond .= "   pbl$i.firstName like '%$name%' or pbl$i.lastName like '%$name%' 
						  or concat(pbl$i.firstName,pbl$i.lastName) like '%$name%'
						  or concat(pbl$i.lastName,pbl$i.firstName) like '%$name%' ";
		}

		if ($address != "")
		{
			if ($addrCond == "")
				$addrCond = " and (";
			else
				$addrCond .= " or ";

			$addrCond .= " pbl$i.city like '%$address%' or pbl$i.address like '%$address%' ";
		}
	}

	if ($nameCond != "")
		$condition .= $nameCond . ") ";

	if ($addrCond != "")
		$condition .= $addrCond . ") ";

	// get total
	$queryStr	 = "select count(*) 
					from phonesRecords $joins where 1 $condition";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$total	     = $row[0];

	$sortBy		= xmlParser_getValue($xmlRequest,"sortBy");

	if ($sortBy == "" || $sortBy == "recordId")
		$sortBy = "id";

	$sortDir	= xmlParser_getValue($xmlRequest,"sortDir");
	if ($sortDir == "")
		$sortDir = "desc";

	if ($sortBy == "fullname")
		$sortBy = "lastName $sortDir, firstName";

	// get details
	$queryStr = "select phonesRecords.*, pbl0.*
				 from phonesRecords $joins
				 where 1 $condition
				 order by $sortBy $sortDir " . commonGetLimit ($xmlRequest);

	$result	     = commonDoQuery ($queryStr);

	$numRows    = commonQuery_numRows($result);

	$xmlResponse = "<items>";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		$id   		= $row['id'];
		$status 	= formatPhoneRecordStatus($row['status']);
		$fullname	= commonValidXml($row['lastName'] . " " . $row['firstName']);
		$firstName	= commonValidXml($row['firstName']);
		$lastName	= commonValidXml($row['lastName']);
		$email		= commonValidXml($row['email']);
		$city		= commonValidXml($row['city']);

		if ($row['firstName'] == "" && $row['lastName'] == "")
		{
			$fullname = commonValidXml($row['occupation']);
		}

		$xmlResponse .=	"<item>
							 <recordId>$id</recordId>
							 <status>$status</status>
							 <fullname>$fullname</fullname>
							 <firstName>$firstName</firstName>
							 <lastName>$lastName</lastName>
							 <email>$email</email>
							 <city>$city</city>
						 </item>";
	}

	$xmlResponse .=	"</items>" .
					commonGetTotalXml($xmlRequest,$numRows,$total);
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getExtraDataNames																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function getExtraDataNames ($xmlRequest)
{
	$queryStr 	= "select * from phonesRecordsExtraData";
	$result	    = commonDoQuery ($queryStr);
	$row	    = commonQuery_fetchRow($result);

	$xmlResponse = "<extraData1>" . commonValidXml($row['extraData1']) . "</extraData1>
					<extraData2>" . commonValidXml($row['extraData2']) . "</extraData2>
					<extraData3>" . commonValidXml($row['extraData3']) . "</extraData3>
					<extraData4>" . commonValidXml($row['extraData4']) . "</extraData4>
					<enumId1>$row[enumId1]</enumId1>
					<enumId2>$row[enumId2]</enumId2>
					<enumId3>$row[enumId3]</enumId3>
					<enumId4>$row[enumId4]</enumId4>";

	for ($i=1; $i<=4; $i++)
	{
		eval ("\$enumId = \$row[\"enumId$i\"];");

		if ($enumId == 0) 
		{
			$xmlResponse .= "<enumName$i></enumName$i>";
		}
		else
		{
			// get enum  name
			$queryStr = "select name from enums where id = $enumId";
			$result		= commonDoQuery ($queryStr);
			$enumRow	= commonQuery_fetchRow ($result);

			$xmlResponse .= "<enumName$i>" . commonValidXml($enumRow['name']) . "</enumName$i>";
		}
	}

	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getRecordDetails																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getRecordDetails ($xmlRequest)
{
	global $usedLangs, $recordTags, $recordLangTags;

	$id		= xmlParser_getValue($xmlRequest, "id");

	if ($id == "")
		trigger_error ("חסר קוד רשומה לביצוע הפעולה");


	$queryStr = "select * from phonesRecords, phonesRecords_byLang
				 where phonesRecords.id = phonesRecords_byLang.recordId
				 and   phonesRecords.id = $id";
	$result   = commonDoQuery ($queryStr);

	if (commonQuery_numRows($result) == 0)
		trigger_error ("רשומה קוד זה ($id) לא קיים במערכת. לא ניתן לבצע את הפעולה");

	$langsArray = explode(",",$usedLangs);

	$xmlResponse = "";

	while ($row = commonQuery_fetchRow($result))
	{
		$language = $row['language'];

		$langsArray = commonArrayRemove ($langsArray, $language);	

		if ($xmlResponse == "")
		{
			for ($i=0; $i < count($recordTags); $i++)
			{
				eval ("\$$recordTags[$i] = \$row['$recordTags[$i]'];");

			}

			$birthDate 	= formatApplDate($birthDate);

			for ($i=0; $i < count($recordTags); $i++)
			{
				eval ("\$$recordTags[$i] = commonValidXml(\$$recordTags[$i]);");

				eval ("\$xmlResponse .= \"<$recordTags[$i]>\$$recordTags[$i]</$recordTags[$i]>\";");
			}
			
			$picFile   	   = commonValidXml($row['picFile']);
			$sourceFile	   = commonCData(commonEncode($row['sourceFile']));
	
			$fullFileName  = urlencode($row['picFile']);
			$fullFileName  = commonValidXml("/phonesBookFiles/$fullFileName");

			$pressText		   = "";
			if ($picFile != "") $pressText = commonPhpEncode("לחץ כאן");
	
			$xmlResponse .= "<recordId>$id</recordId>
							 <sourceFile>$sourceFile</sourceFile>
							 <formSourceFile>$sourceFile</formSourceFile>
							 <fullFileName>$fullFileName</fullFileName> 
							 <origPicFile>$picFile</origPicFile>
							 <show>$pressText</show>
							 <delete>$pressText</delete>
							 <usedLangs>$usedLangs</usedLangs>";
		}

		for ($i=0; $i < count($recordLangTags); $i++)
		{
			eval ("\$$recordLangTags[$i] = commonValidXml(\$row['$recordLangTags[$i]']);");
			eval ("\$xmlResponse .=	\"<$recordLangTags[$i]\$language>\$$recordLangTags[$i]</$recordLangTags[$i]\$language>\";");
		}
	}

	// add missing languages
	// ------------------------------------------------------------------------------------------------
	for ($i=0; $i<count($langsArray); $i++)
	{
		$language	  = $langsArray[$i];

		for ($j=0; $j < count($recordLangTags); $j++)
		{
			eval ("\$xmlResponse .=	\"<$recordLangTags[$j]\$language></$recordLangTags[$j]\$language>\";");
		}
	}

	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteRecord																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteRecord ($xmlRequest)
{
	$id = xmlParser_getValue($xmlRequest, "recordId");

	if ($id == "")
		trigger_error ("חסר קוד רשומה לביצוע הפעולה");

	$queryStr = "delete from phonesRecords where id = $id";
	commonDoQuery ($queryStr);

	$queryStr = "delete from phonesRecords_byLang where recordId = $id";
	commonDoQuery ($queryStr);

	$queryStr = "delete from categoriesItems where itemId = $id and type = 'phonesRecords'";
	commonDoQuery ($queryStr);

	return "";	
}

/* ----------------------------------------------------------------------------------------------------	*/
/* excelExport																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function excelExport ($xmlRequest)
{
	global $usedLangs;
	$langsArray = explode(",",$usedLangs);

	$bookId		= xmlParser_getValue($xmlRequest, "bookId");

	$excel	= "שם פרטי\tשם משפחה\tאימייל\tטלפון בבית\tטלפון עבודה\tטלפון נוסף 1\tטלפון נוסף 2\tטלפון נייד\tפקס\tכתובת\tעיר\tמיקוד\tיום הולדת\tתחום עיסוק\tסטטוס\n";

	$queryStr    = "select * from phonesRecords, phonesRecords_byLang
					where phonesRecords.id = phonesRecords_byLang.recordId and language = '$langsArray[0]'
					and   bookId = $bookId
					order by binary firstName";
	$result	     = commonDoQuery ($queryStr);

	while ($row = commonQuery_fetchRow($result))
	{
		$firstname 		= commonPrepareToFile(stripslashes($row['firstName']));
		$lastname 		= commonPrepareToFile(stripslashes($row['lastName']));
		$email 			= commonPrepareToFile(stripslashes($row['email']));
		$homePhone		= $row['homePhone'];
		$workPhone		= $row['workPhone'];
		$cellphone		= $row['cellphone'];
		$extraPhone1	= $row['extraPhone1'];
		$extraPhone2	= $row['extraPhone2'];
		$fax			= $row['fax'];
		$address 		= commonPrepareToFile(stripslashes($row['address']));
		$city			= commonPrepareToFile(stripslashes($row['city']));
		$zipcode		= $row['zipcode'];
		$birthDate		= formatApplDate($row['birthDate']);
		$occupation		= formatApplDate($row['occupation']);
		$status 		= $row['status'];

		switch ($status)
		{
			case "new"			: $status = "חדש";		break;
			case "approved"		: $status = "מאושר";	break;
			case "rejected"		: $status = "דחוי";		break;
		}

		$excel .= "$firstname\t$lastname\t$email\t$homePhone\t$workPhone\t$extraPhone1\t$extraPhone2\t$cellphone\t$fax\t$address\t$city\t$zipcode\t$birthDate\t$occupation\t$status\n";
	}

	return (commonDoExcel($excel));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* addRecord																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function addRecord ($xmlRequest)
{
	return (editRecord ($xmlRequest, "add"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateRecord																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateRecord ($xmlRequest)
{
	editRecord ($xmlRequest, "update");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* editRecord																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function editRecord ($xmlRequest, $editType)
{
	global $usedLangs, $recordTags, $recordLangTags;
	global $userId;
	global $ibosHomeDir;

	for ($i=0; $i < count($recordTags); $i++)
	{
		eval ("\$$recordTags[$i] = addslashes(commonDecode(xmlParser_getValue(\$xmlRequest,\"$recordTags[$i]\")));");	
	}

	$birthDate 	= formatApplToDB($birthDate . " 00:00:00");

	$id			= xmlParser_getValue($xmlRequest, "recordId");

	$oldFile	= "";
	if ($editType == "add")
	{
		$queryStr	= "select max(id) from phonesRecords";
		$result		= commonDoQuery ($queryStr);
		$row		= commonQuery_fetchRow ($result);
		$id 		= $row[0] + 1;
	}
	else
	{
		$queryStr	= "select picFile from phonesRecords where id = $id";
		$result		= commonDoQuery ($queryStr);
		$row		= commonQuery_fetchRow ($result);
		$oldFile	= $row['picFile'];
	}


	$sourceFile		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "sourceFile")));	
	$fileDeleted	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "fileDeleted")));	

	$fileDeleted	= ($fileDeleted == "1");
	$fileLoaded  	= false;

	$picFile 		= "";

	$suffix 		= "";
	if ($sourceFile != "")
	{
		$fileLoaded = true;
		$suffix		= commonFileSuffix ($sourceFile);
		$picFile 	= $id . $suffix;
	}

	if ($suffix == "." . $sourceFile)	// wrong file name - don't load it
	{
		$fileLoaded = false;
		$picFile    = "";
	}

	if ($fileLoaded)
	{
		$sql	= "select picDimension from phonesBooks where id = $bookId";
		$result	= commonDoQuery($sql);
		$row	= commonQuery_fetchRow($result);

		$picDimension = $row['picDimension'];
	
		if ($picDimension != "" && $picDimension != 0)
		{
			list($picWidth, $picHeight, $bgColor) = commonGetDimensionDetails ($picDimension);

			$resize = true;
		}
		else
		{
			$resize = false;
		}
	}

	$vals = Array();

	for ($i=0; $i < count($recordTags); $i++)
	{
		eval ("array_push (\$vals,\$$recordTags[$i]);");
	}
	
	if ($editType == "add")
	{
		$queryStr = "insert into phonesRecords (" . join(",",$recordTags) . ", picFile, sourceFile) 
					 values ('" . join("','",$vals) . "', '$picFile', '$sourceFile')";
		commonDoQuery ($queryStr);
	}
	else
	{
		$queryStr = "update phonesRecords set ";

		for ($i=1; $i < count($recordTags); $i++)
		{
			$queryStr .= "$recordTags[$i] = '$vals[$i]',";
		}

		$queryStr = trim($queryStr, ",");

		if ($fileLoaded)
		{
			$queryStr .= ", picFile = '$picFile', sourceFile = '$sourceFile' ";
		}
		else if ($fileDeleted)
		{
			$queryStr .= ",	picFile = '', sourceFile  = '' ";
		}

		$queryStr .= " where id = $id ";

		commonDoQuery ($queryStr);

		$queryStr = "delete from phonesRecords_byLang where recordId='$id'";
		commonDoQuery ($queryStr);
	}

	# add languages rows for this producer
	# ------------------------------------------------------------------------------------------------------
	$langsArray = explode(",",$usedLangs);

	for ($i=0; $i<count($langsArray); $i++)
	{
		$language		= $langsArray[$i];

		$vals = Array();
		for ($j=0; $j < count($recordLangTags); $j++)
		{
			array_push ($vals, addslashes(commonDecode(xmlParser_getValue($xmlRequest, $recordLangTags[$j] . $language))));
		}		

		$queryStr		= "insert into phonesRecords_byLang (recordId, language," . join(",",$recordLangTags) . ") 
						   values ($id, '$language', '" . join ("','", $vals) . "')";
	
		commonDoQuery ($queryStr);
	}

	// handle file
	# ------------------------------------------------------------------------------------------------------
	$filePath = "$ibosHomeDir/html/SWFUpload/files/$userId/";

	if ($fileLoaded || $fileDeleted)
	{
		$domainRow	= commonGetDomainRow();

		$connId = commonFtpConnect($domainRow); 

		ftp_chdir($connId, "phonesBookFiles/");
	}

	if ($fileLoaded)
	{
		if ($resize)
		{
			$picFile = $id . ".jpg";

			picsToolsResize("$filePath/$sourceFile", $suffix, $picWidth, $picHeight, "/../../tmp/$picFile", $bgColor);
		
			$upload = ftp_put($connId, $picFile, "/../../tmp/$picFile", FTP_BINARY);
		}
		else
		{
			$upload = ftp_put($connId, $picFile, "$filePath/$sourceFile", FTP_BINARY); 
		}

		unlink("$filePath/$sourceFile");

	}
	else if ($fileDeleted && $oldFile != "")
	{
		commonFtpDelete ($connId, $oldFile);
	}

	if ($fileLoaded || $fileDeleted)
		commonFtpDisconnect ($connId);

 	// delete old files
	commonDeleteOldFiles ($filePath, 3600);	// 1 hour

	return "";
}

?>
