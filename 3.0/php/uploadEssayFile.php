<?php

include "commonAdmin.php";
include "picsTools.php";

mail ("liat@interuse.com", "I-BOS BUG", "I'm in uploadEssayFile.php ?!?!?");
exit;

// default color to fill in background is white
// FFU the application can set otherwise by POST


if (!commonValidateSession())
{
//	echo "<b>System Error</b> - please, call support<br/>
//		  <br/><br/><font color='blue'><u>cookies</u>:</font><br/>";
	
//	print_r ($_COOKIE);

//	echo "<br/><br/><font color='blue'><u>get</u>:</font><br/>";
//	print_r ($_GET);

//	echo "<br/><br/><font color='blue'><u>post</u>:</font><br/>";
//	print_r ($_POST);

//	exit;
}

// action (add | update)
// essayId
// layoutId
// navParentId
// staticname
// membersOnly
// date
// linkType1-3
// url1-3
// page1-3
// showOnSitemap
// navTitle		(by langs)
// winTitle 	(by langs)
// isReady 		(by langs)
// keywords		(by langs)
// description	(by langs)
// title  		(by langs)
// headline 	(by langs)
// subtitle 	(by langs)
// txt  		(by langs)
// author		(by langs)
// picTitle 2-5 (by langs)
// extraData1-5 (by langs)
// rewriteName  (by langs)
// updated		(by langs)
// dimensionId	(not saved on DB - only for resize picture)
// dimensionId2-5

/*if ($linkType1 == "url" || $linkType1 == "urlNoFollow" || $linkType1 == "onclick")
	$link1 = $url1;
else
	$link1 = $page1;

if ($linkType2 == "url" || $linkType2 == "urlNoFollow" || $linkType2 == "onclick")
	$link2 = $url2;
else
	$link2 = $page2;

if ($linkType3 == "url" || $linkType3 == "urlNoFollow" || $linkType3 == "onclick")
	$link3 = $url3;
else
	$link3 = $page3;
 */

$loadFiles		= array();
$sourceFiles	= array();
$suffixes		= array();
$picFiles		= array();

$picWidths		= array();
$picHeights		= array();
$bgColors		= array();

for ($i=1; $i<=5; $i++)
{
	if ($i != 1)
		eval ("\$dimensionId = \$dimensionId$i;");

	if ($dimensionId == 0)
	{
		$picWidth  = 0;
		$picHeight = 0;
		$bgColor   = "#FFFFFF";
	}
	else if ($dimensionId != "")
	{
		$queryStr   = "select width, height, color from dimensions where id = $dimensionId";
		$result		= commonDoQuery ($queryStr);
		$row		= commonQuery_fetchRow ($result);

		$picWidth 	= $row['width'];
		$picHeight 	= $row['height'];
		$bgColor 	= $row['color'];
	}
	else
	{
		$picWidth  = "200";
		$picHeight = "100";
		$bgColor   = "#FFFFFF";
	}

	$picWidths[$i]  = $picWidth;
	$picHeights[$i] = $picHeight;
	$bgColors[$i]   = $bgColor;
}

// find suffix and source file
if ($_FILES['picFile']['name'])
{
	$origName 	= $_FILES["picFile"]["name"];
	$suffix		= commonFileSuffix($origName);

	$suffixes[1] 	= $suffix;
	$loadFiles[1] 	= true;
}
else
{
	$loadFiles[1]	= false;
}

$sourceFiles[1]	= addslashes($sourceFile);

for ($i=2; $i<=5; $i++)
{
	$origName 	= $_FILES["picFile$i"]["name"];

	if ($origName)
	{
		$suffix		= commonFileSuffix($origName);

		$suffixes[$i]	 = $suffix;
		$loadFiles[$i]	 = true;
	}
	else
	{
		$loadFiles[$i]	= false;
	}

	eval ("\$sourceFile = addslashes(\$sourceFile$i);");
	$sourceFiles[$i] = $sourceFile;
}

if ($action == "update")
{
	// get essay date
	$sql	= "select date from essays where id = $essayId";
	$result = commonDoQuery($sql);
	$row	= commonQuery_fetchRow($result);

	$dateSplit = explode(" ", $row['date']);

	$date .= " " . $dateSplit[1];
}
else
{
	$date .= " 00:00:00";
}

$date = preg_replace("/^([0-9]{1,2})[\/\. -]+([0-9]{1,2})[\/\. -]+([0-9]{1,4})\s([0-9]{1,2}):([0-9]{2}):([0-9]{2})/", "\\2/\\1/\\3 \\4:\\5:\\6", $date); 
$date = strtotime($date);
$date = date("Y-m-d H:i:s", $date);

if ($action == "add")
{
	$queryStr   = "select max(id) from pages";
	$result		= commonDoQuery ($queryStr);
	$row		= commonQuery_fetchRow ($result);
	$essayId 	= $row[0] + 1;

	$ibosUserId = commonGetIbosUserId ();

	$queryStr = "insert into pages (id, ibosUserId, type, layoutId, navParentId, staticname, membersOnly, showOnSitemap) values 
								   ('$essayId', '$ibosUserId', 'essay', '$layoutId', '$navParentId', '$staticname', '$membersOnly', '$showOnSitemap')";

	commonDoQuery ($queryStr);

	$queryStr = "insert into essays (id, date, link1, link2, link3, linkType1, linkType2, linkType3, 
									 picFile, sourceFile, picFile2, sourceFile2, picFile3, sourceFile3, picFile4, sourceFile4, picFile5, sourceFile5) 
				 values	('$essayId', '$date', '$link1', '$link2', '$link3', '$linkType1', '$linkType2', '$linkType3'";

	for ($i=1; $i<=5; $i++)
	{
		if ($loadFiles[$i])
		{
			if ($i == 1)
				$picFiles[$i]	= $essayId . "_size0" . $suffixes[$i];
			else
				$picFiles[$i]	= $essayId . "_" . $i . "_size0" . $suffixes[$i];

			$queryStr .= ", '" . $picFiles[$i] . "', '" . $sourceFiles[$i] . "'";
		}
		else
		{
			$queryStr .= ", '', ''";
		}
	}

	$queryStr	.= ")";

	commonDoQuery ($queryStr);

	if ($categoryId != "")
	{
		// get last pos
		$queryStr = "select max(pos) from categoriesItems where categoryId = $categoryId and type = 'essay'";
		$result		= commonDoQuery ($queryStr);
		$row		= commonQuery_fetchRow ($result);
		$pos 		= $row[0] + 1;

		$queryStr = "insert into categoriesItems (itemId, categoryId, type, pos)
					 values ($essayId, $categoryId, 'essay', $pos)";
		commonDoQuery ($queryStr);
	}
}
else
{
	$queryStr = "update essays set date  	 = '$date',
								   link1 	 = '$link1',
								   link2 	 = '$link2',
								   link3 	 = '$link3',
								   linkType1 = '$linkType1', 
								   linkType2 = '$linkType2', 
								   linkType3 = '$linkType3'";
	for ($i=1; $i<=5; $i++)
	{
		if ($loadFiles[$i])
		{
			if ($i == 1)
			{
				$picFiles[$i]	= $essayId . "_size0" . $suffixes[$i];
	
				$queryStr .= ",	  picFile 	 = '" . $picFiles[$i]    . "',
								  sourceFile = '" . $sourceFiles[$i] . "' ";
			}
			else
			{
				$picFiles[$i]	= $essayId . "_$i" . "_size0" . $suffixes[$i];
	
				$queryStr .= ",	  picFile$i    = '" . $picFiles[$i]    . "',
								  sourceFile$i = '" . $sourceFiles[$i] . "' ";
			}
		}
		else if ($sourceFiles[$i] == "")
		{
			if ($i == 1)
				$queryStr .= ",	  picFile      = '',
								  sourceFile   = '' ";
			else
				$queryStr .= ",	  picFile$i    = '',
								  sourceFile$i = '' ";
		}
	}

	$queryStr .= "where id = $essayId";
	commonDoQuery ($queryStr);

	$queryStr = "update pages set layoutId 		= '$layoutId',
								  navParentId 	= '$navParentId',
								  staticname 	= '$staticname',
								  membersOnly 	= '$membersOnly',
								  showOnSitemap = '$showOnSitemap'
				 where id=$essayId";

	commonDoQuery ($queryStr);
}


# add languages rows for this essay
# ------------------------------------------------------------------------------------------------------
$langsArray = explode(",",$usedLangs);

for ($i=0; $i<count($langsArray); $i++)
{
	$language			= $langsArray[$i];

	// Liat (30.11.2008) change commonFixText to commonPrepareToDB
	
	eval ("\$title    		= commonPrepareToDB(\$title$language);");
	eval ("\$navTitle 		= commonPrepareToDB(\$navTitle$language);");
	eval ("\$winTitle 		= commonPrepareToDB(\$winTitle$language);");
	eval ("\$isReady  		= \$isReady$language;");
	eval ("\$keywords  		= commonPrepareToDB(\$keywords$language);");
	eval ("\$description  	= commonPrepareToDB(\$description$language);");
	eval ("\$updated  		= \$updated$language;");
	eval ("\$headline 		= commonPrepareToDB(\$headline$language);");
	eval ("\$subtitle 		= commonPrepareToDB(\$subtitle$language);");
	eval ("\$txt  	 		= commonPrepareToDB(commonValidXstandard(\$txt$language));");
	eval ("\$author   		= commonPrepareToDB(\$author$language);");
	eval ("\$picTitle 		= commonPrepareToDB(\$picTitle$language);");
	eval ("\$picTitle2 		= commonPrepareToDB(\$picTitle2$language);");
	eval ("\$picTitle3 		= commonPrepareToDB(\$picTitle3$language);");
	eval ("\$picTitle4 		= commonPrepareToDB(\$picTitle4$language);");
	eval ("\$picTitle5 		= commonPrepareToDB(\$picTitle5$language);");
	eval ("\$extraData1 	= commonPrepareToDB(\$extraData1$language);");
	eval ("\$extraData2 	= commonPrepareToDB(\$extraData2$language);");
	eval ("\$extraData3 	= commonPrepareToDB(\$extraData3$language);");
	eval ("\$extraData4 	= commonPrepareToDB(\$extraData4$language);");
	eval ("\$extraData5 	= commonPrepareToDB(\$extraData5$language);");
	eval ("\$rewriteName 	= commonPrepareToDB(\$rewriteName$language);");

	$rewriteName	= str_replace(" ", "-", $rewriteName);

	if ($action == "add")
	{
		$queryStr	= "insert into pages_byLang (pageId, language, title, navTitle, winTitle, isReady, keywords, description, rewriteName, updated)
					   values ('$essayId','$language', '$title', '$navTitle', '$winTitle', '$isReady', '$keywords', '$description', '$rewriteName', ";

		if ($updated == "" || $updated == "0000-00-00 00:00:00")
		{
			$queryStr  .= "now())";
		}
		else
		{
			$queryStr  .= "'$updated')";
		}

		commonDoQuery ($queryStr);

		$queryStr		= "insert into essays_byLang (essayId, language, headline, subtitle, txt, author, 
													  picTitle, picTitle2, picTitle3, picTitle4, picTitle5, 
													  extraData1, extraData2, extraData3, extraData4, extraData5)
						   values ('$essayId','$language', '$headline', '$subtitle','$txt','$author', 
								   '$picTitle', '$picTitle2', '$picTitle3', '$picTitle4', '$picTitle5', 
								   '$extraData1', '$extraData2','$extraData3', '$extraData4','$extraData5')";
		commonDoQuery ($queryStr);
	}
	else
	{
		$queryStr	= "update pages_byLang set title			= '$title', 
											   navTitle			= '$navTitle', 
											   winTitle			= '$winTitle', 
											   isReady			= '$isReady', 
											   keywords			= '$keywords', 
											   description		= '$description', 
											   rewriteName		= '$rewriteName', 
											   updated			= ";

		if ($updated == "" || $updated == "0000-00-00 00:00:00")
		{
			$queryStr  .= "now()";
		}
		else
		{
			$queryStr  .= "'$updated'";
		}

		$queryStr .= " where pageId = $essayId and language = '$language'";
		commonDoQuery ($queryStr);

		$queryStr	= "update essays_byLang set subtitle	= '$subtitle', 
												headline	= '$headline', 
												txt			= '$txt', 
												author		= '$author', 
												picTitle	= '$picTitle', 
												picTitle2	= '$picTitle2', 
												picTitle3	= '$picTitle3', 
												picTitle4	= '$picTitle4', 
												picTitle5	= '$picTitle5', 
												extraData1	= '$extraData1', 
												extraData2	= '$extraData2', 
												extraData3	= '$extraData3', 
												extraData4	= '$extraData4', 
												extraData5	= '$extraData5'
					   where essayId = $essayId and language = '$language'";
		commonDoQuery ($queryStr);
	}
}


# Handle RSS file update
# ------------------------------------------------------------------------------------------------------

$domainRow = commonGetDomainRow ();
$domainName = commonGetDomainName ($domainRow);

for ($i=0; $i<count($langsArray); $i++)
{
	$language			= $langsArray[$i];
	$file = fopen ("$domainName/essaysRSS.php?lang=$language","r");
	fclose ($file);
}

# upload files
# ------------------------------------------------------------------------------------------------------

$connId	   = commonFtpConnect($domainRow);
ftp_chdir($connId, "essayFiles");

for ($i=1; $i<=5; $i++)
{
	if ($loadFiles[$i])
	{

		if ($i == 1)
			$tmpName = $_FILES["picFile"]["tmp_name"];
		else
			$tmpName = $_FILES["picFile$i"]["tmp_name"];

		$upload = ftp_put($connId, $picFiles[$i], $tmpName, FTP_BINARY);

		if (!$upload) 
	   		echo "FTP upload has failed!";

		$resizedFileName = str_replace("size0","size1",$picFiles[$i]);

		// Destination must be jpg
		$destParts = explode(".",$resizedFileName);
		$destParts[count($destParts)-1] = "jpg";
		$resizedFileName = join(".", $destParts);

		if ($picWidths[$i] == 0)	// keep size
		{
			$upload = ftp_put($connId, "$resizedFileName", $tmpName, FTP_BINARY);
		}
		else
		{
			picsToolsResize($tmpName, $suffixes[$i], $picWidths[$i], $picHeights[$i], "/../../tmp/$resizedFileName", $bgColors[$i]);
		
			$upload = ftp_put($connId, "$resizedFileName", "/../../tmp/$resizedFileName", FTP_BINARY);
		}

		if (!$upload) 
			echo "FTP upload has failed!";
	} 
	else if ($action == "update")
	{
		if ($sourceFiles[$i] == "")
		{
			if ($connId == "")
			{
				// first connect
				$domainRow = commonGetDomainRow ();

				$connId	   = commonFtpConnect($domainRow);
				ftp_chdir($connId, "essayFiles");
			}

			// file name
			if ($i != 1)
				eval ("\$fullFileName = \$fullFileName$i;");

			$fileName = explode("/", $fullFileName);
			$fileName = $fileName[count($fileName)-1];

			// delete file
			@ftp_delete($connId, "$fileName");

			$fileName = str_replace("size0", "size1", $fileName);
			$destParts = explode(".",$fileName);
			$destParts[count($destParts)-1] = "jpg";
			$fileName = join(".", $destParts);

			@ftp_delete($connId, "$fileName");
		}
	}
}

if ($connId != "")
	commonFtpDisconnect ($connId);

fopen("$domainName/updateModRewrite.php","r");

$addEssayId = "";
if ($isSave)
	$addEssayId = "?essayId=$essayId";

header ("Location: ../html/content/handleEssays.html$addEssayId");
exit;

?>
