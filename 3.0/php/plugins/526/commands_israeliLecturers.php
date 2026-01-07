<?php

$tags		= array ("pageId", "title", "firstname", "lastname", "role", "about", "priority");

/* ----------------------------------------------------------------------------------------------------	*/
/* getLecturers																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function getLecturers ($xmlRequest)
{	
	$conditions = "";

	$sortBy		= xmlParser_getValue($xmlRequest,"sortBy");

	if ($sortBy == "")
		$sortBy = "pageId";

	if ($sortBy == "pageId")
		$sortBy = "israeli_lecturers.pageId";

	$sortDir	= xmlParser_getValue($xmlRequest,"sortDir");
	if ($sortDir == "")
		$sortDir = "asc";

	$isAll		= xmlParser_getValue($xmlRequest, "all");

	if ($isAll == "1")
	{
		$sortBy = "title, lastname, firstname";
		$sortDir = "asc";
	}

	$status		= xmlParser_getValue($xmlRequest, "status");
	if ($status != "")
		$conditions	.= " and pages_byLang.isReady = $status";

	$name		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "name")));
	if ($name != "")
		$conditions .= " and (israeli_lecturers.firstname like '%$name%' or
							  israeli_lecturers.lastname  like '%$name%' or
							  concat(israeli_lecturers.firstname, concat(' ', israeli_lecturers.lastname))  like '%$name%' or
							  concat(israeli_lecturers.lastname,  concat(' ', israeli_lecturers.firstname)) like '%$name%') ";

	// get total
	$sql    	= "select israeli_lecturers.*, pages_byLang.isReady
		   			from (israeli_lecturers, pages_byLang) 
					where israeli_lecturers.pageId = pages_byLang.pageId
					$conditions order by $sortBy $sortDir";
	$result		= commonDoQuery ($sql);
	$total		= commonQuery_numRows($result);

	// get details
	$sql       .= commonGetLimit ($xmlRequest);
	$result	    = commonDoQuery  ($sql);
	$numRows	= commonQuery_numRows ($result);

	$xmlResponse = "<items>";

	while ($row = commonQuery_fetchRow($result))
	{
		$pageId		= $row['pageId'];
		$title		= commonValidXml ($row['title']);
		$firstname 	= commonValidXml ($row['firstname']);
		$lastname	= commonValidXml ($row['lastname']);
		$name		= commonValidXml (trim("$row[title] $row[lastname] $row[firstname]"));
		$status		= ($row['isReady'] == 1) ? "פעיל" : "לא פעיל";
		$priority	= ($row['priority'] == 0) ? "" : $row['priority'];

		$xmlResponse .=	"<item>
							<pageId>$pageId</pageId>
							<title>$title</title>
							<firstname>$firstname</firstname>
							<lastname>$lastname</lastname>
							<name>$name</name>
							<status>$status</status>
							<priority>$priority</priority>
						 </item>";
	}

	$xmlResponse .=	"</items>"								.
					commonGetTotalXml($xmlRequest,$numRows,$total);
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* addLecturer																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function addLecturer ($xmlRequest)
{
	return (editLecturer ($xmlRequest, "add"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateLecturer																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateLecturer ($xmlRequest)
{
	editLecturer ($xmlRequest, "update");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* editLecturer																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function editLecturer ($xmlRequest, $editType)
{
	global $tags;
	global $ibosHomeDir, $userId;

	$domainRow	= commonGetDomainRow();
	$domainName = commonGetDomainName ($domainRow);

	commonConnectToUserDB ($domainRow);

	for ($i=0; $i < count($tags); $i++)
	{
		eval ("\$$tags[$i] = addslashes(commonDecode(xmlParser_getValue(\$xmlRequest,\"$tags[$i]\")));");	
	}

	$oldPic		= "";
	$oldLink1	= "";
	$oldLink2	= "";

	if ($editType == "update")
	{
		if ($pageId == "")
			trigger_error ("חסר קוד מרצה - לא ניתן לבצע את העדכון");

		$sql		= "select * from israeli_lecturers where pageId = $pageId";
		$result	  	= commonDoQuery($sql);
		$row	  	= commonQuery_fetchRow($result);

		$oldPic		= $row['picFile'];
		$oldLink1	= $row['link1File'];
		$oldLink2	= $row['link2File'];
	}
	else
	{
		$sql		= "select max(id) from pages";
		$result	    = commonDoQuery ($sql);
		$row	    = commonQuery_fetchRow($result);
		$pageId	    = $row[0]+1;
	}

	// handle files
	# ------------------------------------------------------------------------------------------------------
	
	# pic
	$picSource		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "picSource")));	
	$picDeleted		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "picDeleted")));	

	$picDeleted		= ($picDeleted == "1");
	$picLoaded 		= false;

	$pic	 		= "";
	$picSuffix 		= "";

	if ($picSource != "")
	{
		$picLoaded 	= true;
		$picSuffix	= commonFileSuffix ($picSource);
		$pic 		= "${pageId}_pic_size0$picSuffix";
	}

	# link1
	$link1Source	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "link1Source")));	
	$link1Deleted	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "link1Deleted")));	

	$link1Deleted	= ($link1Deleted == "1");
	$link1Loaded 	= false;

	$link1	 		= "";
	$link1Suffix 	= "";

	if ($link1Source != "")
	{
		$link1Loaded 	= true;
		$link1Suffix	= commonFileSuffix ($link1Source);
		$link1 			= "${pageId}_link1$link1Suffix";
	}

	# link2
	$link2Source	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "link2Source")));	
	$link2Deleted	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "link2Deleted")));	

	$link2Deleted	= ($link2Deleted == "1");
	$link2Loaded 	= false;

	$link2	 		= "";
	$link2Suffix 	= "";

	if ($link2Source != "")
	{
		$link2Loaded 	= true;
		$link2Suffix	= commonFileSuffix ($link2Source);
		$link2 			= "${pageId}_link2$link2Suffix";
	}

	$vals = Array();
	for ($i=0; $i < count($tags); $i++)
	{
		eval ("array_push (\$vals,\$$tags[$i]);");
	}

	if ($editType == "add")
	{
		$sql = "insert into pages (id, type, typeText, layoutId, navParentId) values ($pageId, 'specific', 'lecturer', 4, 1)";
		commonDoQuery ($sql);

		$sql = "insert into israeli_lecturers (" . join(",",$tags) . ", insertTime, picFile, picSource, link1File, link1Source, link2File, link2Source) 
				values ('" . join("','",$vals) . "', now()";

		$sql .= ($picLoaded    ?  ", '$pic',    '$picSource'   " : ", '', ''");
		$sql .= ($link1Loaded  ?  ", '$link1',  '$link1Source' " : ", '', ''");
		$sql .= ($link2Loaded  ?  ", '$link2',  '$link2Source' " : ", '', ''");
		$sql .= ")";
		commonDoQuery ($sql);
	}
	else
	{
		$sql = "update israeli_lecturers set ";
		for ($i=1; $i < count($tags); $i++)
		{
			$sql .= "$tags[$i] = '$vals[$i]',";
		}
		$sql = trim($sql, ",");

		if ($picLoaded)
		{
			$sql .= ", 	picFile 	= '$pic',
						picSource	= '$picSource'	";
		}
		else if ($picDeleted)
		{
			$sql .= ", 	pic 		= '',
						picSource	= ''	";
		}

		if ($link1Loaded)
		{
			$sql .= ", 	link1File 	= '$link1',
						link1Source	= '$link1Source' ";
		}
		else if ($link1Deleted)
		{
			$sql .= ", 	link1File 	= '',
						link1Source	= ''	";
		}

		if ($link2Loaded)
		{
			$sql .= ", 	link2File 	= '$link2',
						link2Source	= '$link2Source' ";
		}
		else if ($link2Deleted)
		{
			$sql .= ", 	link2File	= '',
						link2Source	= ''	";
		}

		$sql .= " where pageId = $pageId ";
		commonDoQuery ($sql);
	}

	// SEO tags
	$name			= trim("$title $lastname $firstname");
	$winTitle		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "winTitle")));
	$keywords		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "keywords")));
	$metaDesc		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "description")));
	$rewriteName	= str_replace(" ", "-", addslashes(commonDecode(xmlParser_getValue($xmlRequest, "rewriteName"))));
	$isReady		= xmlParser_getValue($xmlRequest, "status");


	if ($editType == "add")
	{
		$sql	= "insert into pages_byLang (pageId, language, title, winTitle, isReady, keywords, description, rewriteName)
				   values ('$pageId','HEB', '$name', '$winTitle', '$isReady', '$keywords', '$metaDesc', '$rewriteName')";
		commonDoQuery ($sql);

	}
	else
	{
		$sql	= "update pages_byLang set title 		= '$name',
										   winTitle		= '$winTitle',
										   isReady		= '$isReady',
										   keywords		= '$keywords',
										   description	= '$metaDesc',
										   rewriteName	= '$rewriteName' 
					   where pageId = $pageId and language = 'HEB'";
		commonDoQuery ($sql);
	}

	// handle file
	$filePath = "$ibosHomeDir/html/SWFUpload/files/$userId";

	$connId 	= commonFtpConnect($domainRow); 
	ftp_chdir ($connId, "lecturersFiles");

	if ($picLoaded)
	{
		$upload = ftp_put($connId, $pic, "$filePath/$picSource", FTP_BINARY);

		$resizedFileName = "${pageId}_size1.jpg";

		list ($width, $height, $bgColor) = commonGetDimensionDetails (1);

		if ($width == 0 && $height == 0)
		{
			$upload = ftp_put($connId, $resizedFileName, "$filePath/$picSource", FTP_BINARY);
		}
		else
		{
			if (!function_exists("picsToolsResize"))
			{
				include "$ibosHomeDir/php/picsTools.php";
			}

			picsToolsResize("$filePath/$picSource", $suffix, $width, $height, "/../../tmp/$resizedFileName", $bgColor);
		
			$upload = ftp_put($connId, $resizedFileName, "/../../tmp/$resizedFileName", FTP_BINARY);
		}
	}
	else if ($picDeleted)
	{
		commonFtpDelete ($connId, $oldPic);

		$oldPic 	= "${pageId}_size1.jpg";
		commonFtpDelete ($connId, $oldPic);

	}

	if ($link1Loaded)
	{
		$upload = ftp_put($connId, $link1, "$filePath/$link1Source", FTP_BINARY);
	}
	else if ($link1Deleted)
	{
		commonFtpDelete ($connId, $oldLink1);
	}

	if ($link2Loaded)
	{
		$upload = ftp_put($connId, $link2, "$filePath/$link2Source", FTP_BINARY);
	}
	else if ($link2Deleted)
	{
		commonFtpDelete ($connId, $oldLink2);
	}

	commonFtpDisconnect ($connId);

 	// delete old files
	commonDeleteOldFiles ($filePath, 3600);	// 1 hour

	// Update .htaccess with mod_rewrite rules
	fopen(commonGetDomainName($domainRow) . "/updateModRewrite.php","r");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getLecturerDetails																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function getLecturerDetails ($xmlRequest)
{
	global $tags;
	global $usedLangs;

	$langsArray = split(",",$usedLangs);

	$pageId		= xmlParser_getValue($xmlRequest, "pageId");

	if ($pageId == "")
		trigger_error ("חסר קוד בר - לא ניתן לקבל פרטים");

	$sql	= "select israeli_lecturers.*, pages_byLang.isReady, pages_byLang.winTitle, pages_byLang.keywords, pages_byLang.description,
						  pages_byLang.rewriteName
			   from (israeli_lecturers, pages_byLang)
			   where israeli_lecturers.pageId = $pageId
			   and 	 israeli_lecturers.pageId = pages_byLang.pageId";
	$result		= commonDoQuery ($sql);
	$row 		= commonQuery_fetchRow($result);

	$xmlResponse = "";
	for ($i=0; $i < count($tags); $i++)
	{
		eval ("\$$tags[$i]    = \$row['$tags[$i]'];");
		eval ("\$$tags[$i] 	  = commonValidXml(\$$tags[$i]);");
		eval ("\$xmlResponse .= \"<$tags[$i]>\$$tags[$i]</$tags[$i]>\";");
	}
			
	$domainRow   = commonGetDomainRow ();
	$siteUrl     = commonGetDomainName($domainRow);

	$clickHere	 = "לחץ כאן";
		
	// pic
	$row['picFile']	= addslashes($row['picFile']);

	$xmlResponse .= "<formSourcePic>$row[picSource]</formSourcePic>
					 <picSource></picSource>";

	if ($row['picSource'] != "")
	{
		$xmlResponse	.= "<fullPicName>" . commonValidXml("$siteUrl/lecturersFiles/$row[picFile]") . "</fullPicName>
							<showPic>$clickHere</showPic>
							<deletePic>$clickHere</deletePic>";
	}

	// link1
	$row['link1File']	= addslashes($row['link1File']);

	$xmlResponse .= "<formSourceLink1>$row[link1Source]</formSourceLink1>
					 <link1Source></link1Source>";

	if ($row['link1Source'] != "")
	{

		$xmlResponse	.= "<fullLink1Name>" . commonValidXml("$siteUrl/lecturersFiles/$row[link1File]") . "</fullLink1Name>
							<showLink1>$clickHere</showLink1>
							<deleteLink1>$clickHere</deleteLink1>";
	}

	// link2
	$row['link2File']	= addslashes($row['link2File']);

	$xmlResponse .= "<formSourceLink2>$row[link2Source]</formSourceLink2>
					 <link2Source></link2Source>";

	if ($row['link2Source'] != "")
	{
		$xmlResponse	.= "<fullLink2Name>" . commonValidXml("$siteUrl/lecturersFiles/$row[link2File]") . "</fullLink2Name>
							<showLink2>$clickHere</showLink2>
							<deleteLink2>$clickHere</deleteLink2>";
	}

	// SEO tags
	$winTitle	= commonValidXml ($row['winTitle']);
	$keywords	= commonValidXml ($row['keywords']);
	$description= commonValidXml ($row['description']);
	$rewriteName= commonValidXml ($row['rewriteName']);
	$isReady	= $row['isReady'];

	$xmlResponse .= "<winTitle>$winTitle</winTitle>
					 <keywords>$keywords</keywords>
					 <description>$description</description>
					 <rewriteName>$rewriteName</rewriteName>
					 <status>$isReady</status>";

	return $xmlResponse;
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteLecturer																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteLecturer ($xmlRequest)
{
	$id	= xmlParser_getValue($xmlRequest, "pageId");

	$sql 	= "delete from israeli_lecturers where pageId = $id";
	commonDoQuery ($sql);

	$sql 	= "delete from pages where id = $id";
	commonDoQuery ($sql);

	$sql 	= "delete from pages_byLang where pageId = $id";
	commonDoQuery ($sql);

	return "";
}

?>
