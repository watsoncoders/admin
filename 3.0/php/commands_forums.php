<?php

include "$ibosHomeDir/php/picsTools.php";

include "commands_user.php";

/* ----------------------------------------------------------------------------------------------------	*/
/* getForums																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function getForums ($xmlRequest)
{	
	global $usedLangs;
	$langsArray = explode(",",$usedLangs);

	$condition 	 = "";

	$ibosUserId	 = xmlParser_getValue($xmlRequest,"ibosUserId");
	if ($ibosUserId != "")
		$condition = " and ibosUserId = $ibosUserId";

	// get total
	$queryStr	 = "select count(*) from forum where language = '$langsArray[0]' $condition";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$total	     = $row[0];

	// get details
	$queryStr    = "select forum.* from forum where language = '$langsArray[0]'  $condition order by id";
	$result	     = commonDoQuery ($queryStr);

	$numRows    = commonQuery_numRows($result);

	$xmlResponse = "<items>";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		$id   		  = $row['id'];
		$name 		  = commonValidXml ($row['name'],true);

		$xmlResponse .=	"<item>"											.
							"<id>$id</id>"	 								. 
							"<name>$name</name>"							. 
						"</item>";
	}

	$xmlResponse .=	"</items>";
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* addForum																								*/
/* ----------------------------------------------------------------------------------------------------	*/
function addForum ($xmlRequest)
{
	return (editForum ($xmlRequest, "add"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* doesForumExist																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function doesForumExist ($id)
{
	$queryStr		= "select count(*) from forum where id=$id";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$count	     = $row[0];

	return ($count > 0);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getForumNextId																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getForumNextId ($xmlRequest)
{
	$queryStr	= "select max(id) from pages";
	$result		= commonDoQuery ($queryStr);
	$row		= commonQuery_fetchRow ($result);
	$id 		= $row[0] + 1;
	
	return "<forumId>$id</forumId>";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getForumDetails																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getForumDetails ($xmlRequest)
{
	global $usedLangs;

	$forumId		= xmlParser_getValue($xmlRequest, "forumId");

	if ($forumId == "")
		trigger_error ("חסר קוד פורום לביצוע הפעולה");

	$queryStr	= "select pages.*, forum.*, pages_byLang.language, pages_byLang.isReady, pages_byLang.title, pages_byLang.winTitle, 
						  pages_byLang.navTitle, pages_byLang.description, pages_byLang.keywords, pages_byLang.rewriteName
				   from forum, pages_byLang
				   left join pages on pages.id=pages_byLang.pageId 
				   where forum.id = $forumId 
				   and   forum.language = pages_byLang.language
				   and   forum.id = pages.id";
	$result		= commonDoQuery ($queryStr);

	if (commonQuery_numRows($result) == 0)
		trigger_error ("פורום קוד זה ($forumId) לא קיים במערכת. לא ניתן לבצע את העדכון");

	$langsArray = explode(",",$usedLangs);

	$xmlResponse = "";
	
	// siteUrl
	$domainRow   = commonGetDomainRow ();
	$siteUrl     = commonGetDomainName($domainRow);

	while ($row = commonQuery_fetchRow($result))
	{
		$language = $row['language'];

		$langsArray = commonArrayRemove ($langsArray, $language);	

		if ($xmlResponse == "")
		{
			$layoutId		    = $row['layoutId'];
			$membersOnly  	 	= $row['membersOnly'];
			$staticname	   		= commonValidXml($row['staticname']);
			$showOnSitemap 		= $row['showOnSitemap'];
			$navParentId		= $row['navParentId'];
			$ibosUserId			= $row['ibosUserId'];
			$countViews			= $row['countViews'];
			$useEditor			= $row['useEditor'];
			$membersPostOnly	= $row['membersPostOnly'];
			$memberManager		= $row['memberManager'];
			$openedMsgs			= $row['openedMsgs'];
			$uploadPicDimension	= $row['uploadPicDimension'];
			$uploadDoc			= $row['uploadDoc'];
		
			$xmlResponse	= 	"<forumId>$forumId</forumId>
								 <layoutId>$layoutId</layoutId>
								 <membersOnly>$membersOnly</membersOnly>
								 <staticname>$staticname</staticname>
							 	 <navParentId>$navParentId</navParentId>
								 <showOnSitemap>$showOnSitemap</showOnSitemap>
								 <ibosUserId>$ibosUserId</ibosUserId>
								 <countViews>$countViews</countViews>
								 <siteUrl>$siteUrl/index2.php</siteUrl>
								 <useEditor>$useEditor</useEditor>
								 <membersPostOnly>$membersPostOnly</membersPostOnly>
								 <memberManager>$memberManager</memberManager>
								 <openedMsgs>$openedMsgs</openedMsgs>
								 <uploadPicDimension>$uploadPicDimension</uploadPicDimension>
								 <uploadDoc>$uploadDoc</uploadDoc>";

			$picFile   	   = commonValidXml($row['picFile']);
			$picSource	   = commonCData(commonEncode($row['picSource']));
			$linkToFile	    = "$siteUrl/forumsFiles/$row[picFile]";

			$pressText     = commonPhpEncode("לחץ כאן");

			$show	 = "";
			$delete  = "";

			if ($row['picFile']  != "") 
			{
				$show   = $pressText;
				$delete = $pressText;
			}
			
			$xmlResponse .= "<dimensionId></dimensionId>
							 <picFile>$picFile</picFile>
							 <formSourceFile>$picSource</formSourceFile>
							 <show>$show</show>
							 <delete>$delete</delete>
							 <linkToFile>$linkToFile</linkToFile>";
		}

		$name 				= commonValidXml($row['name']);
		$ownerName 			= commonValidXml($row['ownerName']);
		$ownerEmail 		= commonValidXml($row['ownerEmail']);
		$numMsgsInPage 		= $row['numMsgsInPage'];
		$numActivePages 	= $row['numActivePages'];
		$numArchivePages 	= $row['numArchivePages'];
		$numMsgsInTable 	= $row['numMsgsInTable'];
		$numResultsInPage 	= $row['numResultsInPage'];
		$emailWhenReplay	= $row['emailWhenReplay'];
		$canReplay			= $row['canReplay'];
		$emailOwner			= $row['emailOwner'];
		$hideMsgEmail		= $row['hideMsgEmail'];
		$isReady			= $row['isReady'];
		$title				= commonValidXml($row['title']);
		$winTitle			= commonValidXml($row['winTitle']);
		$navTitle			= commonValidXml($row['navTitle']);
		$keywords			= commonValidXml($row['keywords']);
		$description		= commonValidXml($row['description']);
		$rewriteName   		= commonValidXml($row['rewriteName']);
		$forumDescription  	= commonValidXml($row['forumDescription']);

		$xmlResponse .=	   "<name$language>$name</name$language>
						 	<ownerName$language>$ownerName</ownerName$language>
							<ownerEmail$language>$ownerEmail</ownerEmail$language>
							<numMsgsInPage$language>$numMsgsInPage</numMsgsInPage$language>
							<numActivePages$language>$numActivePages</numActivePages$language>
							<numArchivePages$language>$numArchivePages</numArchivePages$language>
							<numMsgsInTable$language>$numMsgsInTable</numMsgsInTable$language>
							<numResultsInPage$language>$numResultsInPage</numResultsInPage$language>
							<emailWhenReplay$language>$emailWhenReplay</emailWhenReplay$language>
							<canReplay$language>$canReplay</canReplay$language>
							<emailOwner$language>$emailOwner</emailOwner$language>
							<hideMsgEmail$language>$hideMsgEmail</hideMsgEmail$language>
							<isReady$language>$isReady</isReady$language>
							<navTitle$language>$navTitle</navTitle$language>
						    <title$language>$title</title$language>
							<keywords$language>$keywords</keywords$language>
							<description$language>$description</description$language>
							<rewriteName$language>$rewriteName</rewriteName$language>
						    <winTitle$language>$winTitle</winTitle$language>
						    <forumDescription$language>$forumDescription</forumDescription$language>";
	}

	// add missing languages
	// ------------------------------------------------------------------------------------------------
	for ($i=0; $i<count($langsArray); $i++)
	{
		$language	  = $langsArray[$i];

		$xmlResponse .=	   "<name$language><![CDATA[]]></name$language>
						 	<ownerName$language><![CDATA[]]></ownerName$language>
							<ownerEmail$language><![CDATA[]]></ownerEmail$language>
							<numMsgsInPage$language><![CDATA[]]></numMsgsInPage$language>
							<numActivePages$language><![CDATA[]]></numActivePages$language>
							<numArchivePages$language><![CDATA[]]></numArchivePages$language>
							<numMsgsInTable$language><![CDATA[]]></numMsgsInTable$language>
							<numResultsInPage$language><![CDATA[]]></numResultsInPage$language>
							<emailWhenReplay$language><![CDATA[]]></emailWhenReplay$language>
							<canReplay$language><![CDATA[]]></canReplay$language>
							<emailOwner$language><![CDATA[]]></emailOwner$language>
							<hideMsgEmail$language><![CDATA[]]></hideMsgEmail$language>
							<isReady$language><![CDATA[]]></isReady$language>
							<navTitle$language></navTitle$language>
						    <title$language><![CDATA[]]></title$language>
						    <winTitle$language><![CDATA[]]></winTitle$language>";
	}

	// add forum user details
	$forumUserName 		= "";
	$forumUserPassword	= "";
	if ($ibosUserId != 0)
	{
		commonConnectToDB ();
	
		$queryStr 	= "select username, password from users where id = $ibosUserId";
		$result		= commonDoQuery ($queryStr);
		$userRow	= commonQuery_fetchRow($result);

		$forumUserName 	   = commonValidXml($userRow['username']);
		$forumUserPassword = commonValidXml($userRow['password']);
	}

	$xmlResponse .= "<ibosUserId>$ibosUserId</ibosUserId>
					 <forumUserName>$forumUserName</forumUserName>
					 <forumUserNameSpn>$forumUserName</forumUserNameSpn>
					 <forumUserPassword>$forumUserPassword</forumUserPassword>";

	return $xmlResponse;
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateForum																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateForum ($xmlRequest)
{
	editForum ($xmlRequest, "update");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* editForum																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function editForum ($xmlRequest, $editType)
{
	global $usedLangs;
	global $isUTF8;
	global $userId;
	global $ibosHomeDir;

	$forumId			= xmlParser_getValue($xmlRequest, "forumId");

	$ibosUserId			= xmlParser_getValue($xmlRequest,"ibosUserId");
	$forumUserName 		= addslashes(commonDecode(xmlParser_getValue($xmlRequest,"forumUserName")));
	$forumUserPassword 	= addslashes(commonDecode(xmlParser_getValue($xmlRequest,"forumUserPassword")));

	$layoutId			= xmlParser_getValue($xmlRequest, "layoutId");
	$membersOnly		= xmlParser_getValue($xmlRequest, "membersOnly");
	$staticname			= addslashes(urlencode(commonDecode(xmlParser_getValue($xmlRequest, "staticname"))));
	$showOnSitemap		= xmlParser_getValue($xmlRequest, "showOnSitemap");
	$navParentId		= xmlParser_getValue($xmlRequest, "navParentId");
	$useEditor			= xmlParser_getValue($xmlRequest, "useEditor");
	$membersPostOnly	= xmlParser_getValue($xmlRequest, "membersPostOnly");
	$memberManager		= xmlParser_getValue($xmlRequest, "memberManager");
	$openedMsgs			= xmlParser_getValue($xmlRequest, "openedMsgs");
	$uploadPicDimension	= xmlParser_getValue($xmlRequest, "uploadPicDimension");
	$uploadDoc			= xmlParser_getValue($xmlRequest, "uploadDoc");

	if (ibosUserExists ($ibosUserId, $forumUserName))
	{
		trigger_error ("שם משתמש זה כבר קיים במערכת");
	}

	if ($ibosUserId == "") $ibosUserId = 0;

	$oldPicSource = "";
	$oldPicFile	  = "";

	if ($editType == "update")
	{
		if (!doesForumExist($forumId))
		{
			trigger_error ("פורום עם קוד זה ($forumId) לא קיים במערכת. לא ניתן לבצע את העדכון");
		}
		
		# delete all languages rows
		# --------------------------------------------------------------------------------------------------
		$queryStr = "delete from pages_byLang where pageId='$forumId'";
		commonDoQuery ($queryStr);

		$queryStr = "select * from forum where id = '$forumId'";
		$result	  = commonDoQuery($queryStr);
		$row	  = commonQuery_fetchRow($result);

		$oldPicSource = $row['picSource'];
		$oldPicFile   = $row['picFile'];

		$ibosUserId	  = $row['ibosUserId'];
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
		$picFile 	= "${forumId}_size0.jpg";
	}
	else
	{
		$picSource  = $oldPicSource;
		$picFile	= $oldPicFile;
	}

	if ($fileDeleted)
	{
		$picSource  = "";
		$picFile	= "";
	}

	list($picWidth, $picHeight, $bgColor) = commonGetDimensionDetails ($dimensionId);
	
	# add languages rows for this forum
	# ------------------------------------------------------------------------------------------------------
	$langsArray = explode(",",$usedLangs);

	for ($i=0; $i<count($langsArray); $i++)
	{
		$language		= $langsArray[$i];

		$ownerName			= addslashes(commonDecode(xmlParser_getValue($xmlRequest,"ownerName$language")));
		$ownerEmail			= addslashes(commonDecode(xmlParser_getValue($xmlRequest,"ownerEmail$language")));
		$numMsgsInPage 		= xmlParser_getValue($xmlRequest, "numMsgsInPage$language"); 
		$numActivePages 	= xmlParser_getValue($xmlRequest, "numActivePages$language");  
		$numArchivePages 	= xmlParser_getValue($xmlRequest, "numArchivePages$language");  
		$numMsgsInTable 	= xmlParser_getValue($xmlRequest, "numMsgsInTable$language");  
		$numResultsInPage 	= xmlParser_getValue($xmlRequest, "numResultsInPage$language");  
		$emailWhenReplay	= xmlParser_getValue($xmlRequest, "emailWhenReplay$language");
		$canReplay			= xmlParser_getValue($xmlRequest, "canReplay$language");
		$emailOwner			= xmlParser_getValue($xmlRequest, "emailOwner$language");
		$hideMsgEmail		= xmlParser_getValue($xmlRequest, "hideMsgEmail$language");
		$forumDescription	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "forumDescription$language")));


		$isReady			= xmlParser_getValue($xmlRequest, "isReady$language");
		$title				= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "title$language")));
		$name 				= $title;
		$winTitle			= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "winTitle$language")));
		$navTitle			= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "navTitle$language")));
		$keywords			= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "keywords$language")));
		$description		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "description$language")));
		$rewriteName		= str_replace(" ", "-", addslashes(commonDecode(xmlParser_getValue($xmlRequest, "rewriteName$language"))));

		if ($isReady 		  == "") $isReady 			= 1;
		if ($numMsgsInPage    == "") $numMsgsInPage 	= 10;
		if ($numActivePages   == "") $numActivePages 	= 10;
		if ($numMsgsInTable   == "") $numMsgsInTable 	= 50;
		if ($numArchivePages  == "") $numArchivePages 	= 10;
		if ($numResultsInPage == "") $numResultsInPage  = 25;
		if ($emailWhenReplay  == "") $emailWhenReplay   = 0;
		if ($canReplay  	  == "") $canReplay   		= 1;
		if ($hideMsgEmail     == "") $hideMsgEmail		= "yes";
		if ($emailOwner       == "") $emailOwner		= "never";



		$queryStr		= "insert into pages_byLang (pageId, navTitle, winTitle, title, language, isReady, keywords, description, rewriteName)
							values ('$forumId', '$navTitle', '$winTitle', '$title', '$language', '$isReady', '$keywords', '$description', 
						   		    '$rewriteName')";
		commonDoQuery ($queryStr);

		$queryStr 		= "replace into forum 
							(id, language, name, ownerName, ownerEmail, numMsgsInPage, numActivePages, numArchivePages, numMsgsInTable, 
							 numResultsInPage, emailWhenReplay, canReplay, emailOwner, hideMsgEmail, ibosUserId, 
							 useEditor, membersPostOnly, memberManager, openedMsgs, forumDescription, uploadPicDimension, uploadDoc, picFile, picSource) 
						   values 
							 ($forumId, '$language', '$name', '$ownerName', '$ownerEmail', '$numMsgsInPage', '$numActivePages', '$numArchivePages', 
							 '$numMsgsInTable', '$numResultsInPage', '$emailWhenReplay', '$canReplay', '$emailOwner', '$hideMsgEmail', 
							  $ibosUserId, '$useEditor', '$membersPostOnly', '$memberManager', '$openedMsgs', '$forumDescription', '$uploadPicDimension',
							 '$uploadDoc', '$picFile', '$picSource')";

		commonDoQuery ($queryStr);
	}

	if ($editType == "add")
	{
		$queryStr = "insert into pages (id, type, layoutId, navParentId, staticname, membersOnly, showOnSitemap) values 
									   ('$forumId', 'forum', '$layoutId', '$navParentId', '$staticname', '$membersOnly', '$showOnSitemap')";

		commonDoQuery ($queryStr);

	}
	else // update
	{
		$queryStr = "update pages set layoutId 		= '$layoutId',
									  navParentId	= '$navParentId',
									  staticname 	= '$staticname',
									  membersOnly 	= '$membersOnly',
									  showOnSitemap = '$showOnSitemap'
					 where id=$forumId";

		commonDoQuery ($queryStr);
	}

	
	$saveIsUTF8 = $isUTF8;
	$isUTF8 = 0;

	$ownerName			= addslashes(commonDecode(xmlParser_getValue($xmlRequest,"ownerName" . $langsArray[0])));
	$forumUserName 		= addslashes(commonDecode(xmlParser_getValue($xmlRequest,"forumUserName")));
	$forumUserPassword 	= addslashes(commonDecode(xmlParser_getValue($xmlRequest,"forumUserPassword")));

	$myName 			= substr($ownerName, 0, 17);

	if ($ibosUserId != 0)
	{
		// connect to admin db
		commonConnectToDB ();

		// update user details only
		$queryStr = "update users set username = '$forumUserName', 
									  password = '$forumUserPassword',
									  myName   = '$myName' 
					 where id = $ibosUserId";
		commonDoQuery ($queryStr);

		$isUTF8 = $saveIsUTF8;
	}
	else if ($forumUserPassword != "")
	{
		// connect to admin db
		commonConnectToDB ();

		// add new user to admin.users table
		$queryStr 	= "select max(id) from users";
		$result		= commonDoQuery ($queryStr);
		$row		= commonQuery_fetchRow($result);

		$ibosUserId	= $row[0]+1;

		// find domain id
		global $sessionCode;
		$userRow = commonGetUserRow ($sessionCode);

		$queryStr 	= "insert into users (id, username, password, myName, domainId, isSuperUser)
					   values ($ibosUserId, '$forumUserName', '$forumUserPassword', '$myName', $userRow[domainId], 0)";

		commonDoQuery ($queryStr);

		// add forums feature
		$queryStr	= "insert into usersFeatures (userId, featureId) values ($ibosUserId, 16)";
		commonDoQuery ($queryStr);

		// update forum table with the ibos user id
		commonConnectToUserDB (commonGetDomainRow());

		$queryStr 	= "update forum set ibosUserId = $ibosUserId where id = $forumId";
		commonDoQuery ($queryStr);
	}

	$isUTF8 = $saveIsUTF8;

	$domainRow	= commonGetDomainRow();

	// handle file
	$filePath = "$ibosHomeDir/html/SWFUpload/files/$userId";

	if ($fileLoaded)
	{
		$connId = commonFtpConnect($domainRow); 

		ftp_chdir ($connId, "forumsFiles");

		$upload = ftp_put($connId, $picFile, "$filePath/$picSource", FTP_BINARY); 

		$fileName = "${forumId}_size0.jpg";

		if ($picWidth == 0 && $picHeight == 0)
		{
			$upload = ftp_put($connId, "$fileName", "$filePath/$picSource", FTP_BINARY);
		}
		else
		{
			picsToolsResize("$filePath/$picSource", $suffix, $picWidth, $picHeight, "/../../tmp/$fileName", $bgColor);
			$upload = ftp_put($connId, "$fileName", "/../../tmp/$fileName", FTP_BINARY);
		}
		unlink("$filePath/$picSource");

		commonFtpDisconnect ($connId);
	}
	else if ($fileDeleted)
	{
		$connId = commonFtpConnect($domainRow); 

		commonFtpDelete($connId, "$oldPicFile");
	}

 	// delete old files
	commonDeleteOldFiles ($filePath, 3600);	// 1 hour

	$domainName = commonGetDomainName ($domainRow);

	// Update .htaccess with mod_rewrite rules
	fopen("$domainName/updateModRewrite.php","r");

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* ibosUserExists																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function ibosUserExists ($userId, $username)
{
	if ($userId == "" || $username == "") return false;

	commonConnectToDB ();

	$queryStr	= "select id from users where username = '$username' and id != $userId";
	$result		= commonDoQuery($queryStr);

	$exists		= (commonQuery_numRows($result) != 0);

	commonConnectToUserDB (commonGetDomainRow());

	return ($exists);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getForumMsgs																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function getForumMsgs ($xmlRequest)
{	
	global $maxRowsInPage;

	$forumId = xmlParser_getValue($xmlRequest, "forumId");
	$lang 	 = xmlParser_getValue($xmlRequest, "lang");

	if ($forumId == "")
	{
		return "<items></items>";
	}

	$msgType = xmlParser_getValue($xmlRequest, "msgType");

	$condition = "";

	if ($msgType == "sticky")
	{
		$condition = " isSticky=1 ";
	}
	else
	{
		$condition = " isSticky=0 and parentId=0 ";
	}

	$byText = xmlParser_getValue($xmlRequest, "byText");
	if ($byText != "") 
	{
		$byText	= str_replace("'", "\'", $byText);
		
		$condition = " (title like '%$byText%' or content like '%$byText%') ";
	}

	// get num msgs
	$queryStr	 = "select count(*) from forumMsgs where forumId = $forumId and language = '$lang' and $condition";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$numMsgs	 = $row[0];

	// get forum definitions
	$sql 	  = "select numMsgsInTable from forum where id = $forumId";
	$result	  = commonDoQuery($sql);
	$row	  = commonQuery_fetchRow($result);

	$numMsgsInTable		= $row['numMsgsInTable'];

	if ($numMsgsInTable == 0)
		$numMsgsInTable = 50;

	$domainRow   = commonGetDomainRow ();

	commonConnectToUserDB ($domainRow);

	if ($domainRow['id'] == 105)
		$numMsgsInTable = 20;

	$maxRowsInPage = $numMsgsInTable;

	$numPages = ceil($numMsgs/$numMsgsInTable);

	$page  = xmlParser_getValue($xmlRequest, "pageNumber");

	if ($msgType == "")
		$msgType = "all";

	$from			= $numMsgsInTable*($page-1);

	// get details
	$queryStr		= "select id, parentId, title, content, insertTime, ip, writer, email, isSticky from forumMsgs 
					   where forumId = $forumId and language = '$lang' ";

	if ($byText != "")
	{
		$queryStr  .= " and (title like '%$byText%' or content like '%$byText%') ";
	}
	else if ($msgType == "sticky")
	{
		$queryStr  .= " and isSticky=1 limit $from,$numMsgsInTable";
	}
	else
	{
		$queryStr  .= " and isSticky=0 and parentId=0
					   order by insertTime desc limit $from,$numMsgsInTable";

	}
	$result	     = commonDoQuery ($queryStr);

	$numRows    = commonQuery_numRows($result);

	$xmlResponse = "<items>";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		$xmlResponse .= getForumMsgXml ($row);

		if ($row['parentId'] == 0 && $msgType == "all")
		{
			$queryStr	= "select id, parentId, title, content, insertTime, ip, writer, email, isSticky
						   from forumMsgs 
						   where forumId = $forumId and language = '$lang' and isSticky=0 and parentId=$row[id]
					   	   order by insertTime";

			$subResult	= commonDoQuery ($queryStr);

			while ($row = commonQuery_fetchRow ($subResult))
			{
				$xmlResponse .= getForumMsgXml ($row);
			}
		}
 	}

	$xmlResponse .=	"</items>"												.
					commonGetTotalXml($xmlRequest,$numRows,$numMsgs);
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getForumMsgXml																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getForumMsgXml ($row)
{
	$xml 	= "";

	$title	= $row['title'];
	$title	= str_replace("\n","",$title);
	if ($row['parentId'] != 0)
	{
		$title = "&nbsp;&nbsp;&nbsp;$title";
	}

	if (strlen($title) > 40)
	{
//		$title  = substr($title,0,40) . "...";
	}

	if ($row['parentId'] == 0)
	{
		$title	= "<span style='font-weight:bold'>$title</span>";
	}

	$msgId 		  = $row['id'];
	$title 		  = commonValidXml 	   ($title,true);
	$content	  = commonValidXml 	   ($row['content'],true);
	$insertTime	  = formatApplDateTime ($row['insertTime']);
	$writer		  = commonValidXml	   ($row['writer']);
	$ip			  = $row['ip'];
	$parentId	  = $row['parentId'];
	$isSticky	  = $row['isSticky'];

	$content = "";

	$xml		 .=	"<item>"											.
						"<msgId>$msgId</msgId>"							. 
						"<title>$title</title>"							. 
						"<content>$content</content>"					. 
						"<insertTime>$insertTime</insertTime>"			. 
						"<writer>$writer</writer>"						. 
						"<ip>$ip</ip>"									. 
						"<parentId>$parentId</parentId>"				. 
						"<isSticky>$isSticky</isSticky>"				. 
					"</item>";

	return ($xml);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getUserDetails																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getUserDetails ($xmlRequest)
{
	$forumId	= xmlParser_getValue($xmlRequest, "forumId");

	$sql			= "select memberManager from forum where id = '$forumId'";
	$result			= commonDoQuery($sql);
	$row			= commonQuery_fetchRow($result);
	$memberManager	= $row['memberManager'];

	return ("<userIP>" . commonGetIP() . "</userIP>" .
			"<memberManager>$memberManager</memberManager>" .
			getUserInfo ($xmlRequest));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getForumMsgDetails																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function getForumMsgDetails ($xmlRequest)
{
	$msgId	= xmlParser_getValue($xmlRequest, "msgId");

	if ($msgId == "")
		trigger_error ("חסר קוד הודעה לביצוע הפעולה");

	$queryStr	= "select * from forumMsgs where id=$msgId";
	$result		= commonDoQuery ($queryStr);

	if (commonQuery_numRows($result) == 0)
		trigger_error ("הודעה קוד זה ($msgId) לא קיימת במערכת. לא ניתן לבצע את העדכון");

	$row 				= commonQuery_fetchRow($result);
			
	$msgId 		  = $row['id'];
	$title 		  = commonValidXml 	   		($row['title'],true);
	$content	  = commonValidXml			($row['content'],true);
	$insertTime	  = formatApplFormDateTime  ($row['insertTime']);
	$writer		  = commonValidXml	   		($row['writer']);
	$email		  = commonValidXml	   		($row['email']);
	$ip			  = $row['ip'];
	$parentId	  = $row['parentId'];
	$isSticky	  = $row['isSticky'];

	$msgType	  = "main";
	if ($parentId != "0")
		$msgType = "sub";
	else
		$parentId = "";

	$xmlResponse  =	"<msgId>$msgId</msgId>"							. 
					"<title>$title</title>"							. 
					"<content>$content</content>"					. 
					"<insertTime>$insertTime</insertTime>"			. 
					"<writer>$writer</writer>"						. 
					"<email>$email</email>"							.
					"<ip>$ip</ip>"									.
					"<parentId>$parentId</parentId>"				. 
					"<isSticky>$isSticky</isSticky>"				. 
					"<msgType>$msgType</msgType>";

	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* addMsg																								*/
/* ----------------------------------------------------------------------------------------------------	*/
function addMsg ($xmlRequest)
{
	return (editMsg ($xmlRequest, "add"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateMsg																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateMsg ($xmlRequest)
{
	editMsg ($xmlRequest, "update");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* editMsg																								*/
/* ----------------------------------------------------------------------------------------------------	*/
function editMsg ($xmlRequest, $editType)
{
	$title 				= addslashes(commonDecode(xmlParser_getValue($xmlRequest,"title")));
	$content 			= addslashes(commonDecode(xmlParser_getValue($xmlRequest,"content")));
//	$content 			= nl2br(addslashes(commonHyperlink($content)));
	$writer 			= addslashes(commonDecode(xmlParser_getValue($xmlRequest,"writer")));
	$email	 			= addslashes(commonDecode(xmlParser_getValue($xmlRequest,"email")));
	$ip			 		= xmlParser_getValue($xmlRequest, "ip"); 
	$forumIdip	 		= xmlParser_getValue($xmlRequest, "forumId"); 

	if ($editType == "add")
	{
		$forumId		= xmlParser_getValue($xmlRequest, "forumId");
		$lang 	 		= xmlParser_getValue($xmlRequest, "lang");

		if ($forumId == "")
			trigger_error ("חסר קוד פורום לביצוע הפעולה");

		$queryStr = "insert into forumMsgs 
						(title, content, writer, email, ip, insertTime, isSticky, forumId, language)
					 values 
					 	('$title', '$content', '$writer', '$email', '$ip', now(), 1, $forumId, '$lang')";
	}
	else // update
	{
		$msgId		= xmlParser_getValue($xmlRequest, "msgId");
		
		if ($msgId == "")
			trigger_error ("חסר קוד הודעה לביצוע הפעולה");

		// check date & time
		// --------------------------------------------------------------------------------------------
		$insertTime		= xmlParser_getValue($xmlRequest, "insertTime");
		
		$checkDateTime	= commonValidDateTime ($insertTime);

		if ($checkDateTime != "")
		{
			trigger_error ($checkDateTime);
		}

		$insertTime = formatApplToDB ($insertTime);

		// check msg type & parent id
		// --------------------------------------------------------------------------------------------
		if (xmlParser_getValue($xmlRequest, "msgType") != "sub")
		{
			$parentId = 0;
		}
		else
		{
			$parentId = xmlParser_getValue($xmlRequest, "parentId");

			if ($parentId == $msgId)
			{
				trigger_error ("לא ניתן להפוך הודעה ראשית לתגובה על עצמה");
			}

			// check that this parent id is really main msg
			$queryStr = "select parentId from forumMsgs where id='$parentId'";
			$result	  = commonDoQuery ($queryStr);

			
			if (commonQuery_numRows($result) == 0)
			{
				trigger_error ("אין הודעה ראשית בעלת מספר ההודעה שנבחרה");
			}

			$row = commonQuery_fetchRow($result);
			if ($row['parentId'] != "0")
			{
				trigger_error ("מספר ההודעה שהוזן אינו מספר של הודעה ראשית");
			}

			// remove all sub msgs of this main msg to be sub of the new parent
			$queryStr = "update forumMsgs set parentId='$parentId' where parentId=$msgId";
			commonDoQuery ($queryStr);
		}

		$queryStr = "update forumMsgs set title      = '$title',
										  content    = '$content',
										  email	     = '$email',
										  ip	     = '$ip',
										  insertTime = '$insertTime',
										  parentId	 = '$parentId'
				 	 where id=$msgId";
	}

	commonDoQuery ($queryStr);

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteForumMsg																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteForumMsg ($xmlRequest)
{
	$msgIds = xmlParser_getValues ($xmlRequest, "msgId");

	if (count($msgIds) == 0)
		trigger_error ("חסר קוד הודעה לביצוע הפעולה");

	foreach ($msgIds as $msgId)
	{
		$queryStr =  "delete from forumMsgs where id=$msgId or parentId=$msgId";
		commonDoQuery ($queryStr);
	}

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteSpamForumMsg																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteSpamForumMsg ($xmlRequest)
{
	$msgId = xmlParser_getValue ($xmlRequest, "msgId");

	if ($msgId == "")
		trigger_error ("חסר קוד הודעה לביצוע הפעולה");

	$queryStr	= "select * from forumMsgs where id=$msgId";
	$result		= commonDoQuery ($queryStr);

	if (commonQuery_numRows($result) == 0)
		trigger_error ("הודעה קוד זה ($msgId) לא קיימת במערכת. לא ניתן לבצע את העדכון");

	$row 		= commonQuery_fetchRow($result);
			
	$yesterday  = date("Y-m-d h:i:00", strtotime("-96 hours"));
	$queryStr 	=  "delete from forumMsgs where forumId=$row[forumId] and ip='$row[ip]' and insertTime >= '$yesterday'";
	commonDoQuery ($queryStr);

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteSpamAllForumsMsg																				*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteSpamAllForumsMsg ($xmlRequest)
{
	$msgId = xmlParser_getValue ($xmlRequest, "msgId");

	if ($msgId == "")
		trigger_error ("חסר קוד הודעה לביצוע הפעולה");

	$queryStr	= "select * from forumMsgs where id=$msgId";
	$result		= commonDoQuery ($queryStr);

	if (commonQuery_numRows($result) == 0)
		trigger_error ("הודעה קוד זה ($msgId) לא קיימת במערכת. לא ניתן לבצע את העדכון");

	$row 		= commonQuery_fetchRow($result);
			
	$yesterday  = date("Y-m-d h:i:00", strtotime("-96 hours"));
	$queryStr 	=  "delete from forumMsgs where ip='$row[ip]' and insertTime >= '$yesterday'";
	commonDoQuery ($queryStr);

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getForumMembers																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getForumMembers ($xmlRequest)
{
	$forumId	 = xmlParser_getValue($xmlRequest,"forumId");

	$sortBy		= xmlParser_getValue($xmlRequest,"sortBy");
	if ($sortBy == "")
		$sortBy = "memberId";

	$sortDir	= xmlParser_getValue($xmlRequest,"sortDir");
	if ($sortDir == "")
		$sortDir = "asc";

	$sql		= "select count(*) from clubMembers, forumMembers where forumMembers.memberId = clubMembers.id and forumMembers.forumId = $forumId";
	$result		= commonDoQuery ($sql);
	$row	    = commonQuery_fetchRow($result);
	$total	    = $row[0];

	$sql		= str_replace("count(*)", "*", $sql);
	$sql	   .= " order by $sortBy $sortDir " . commonGetLimit ($xmlRequest);
	$result		= commonDoQuery($sql);

	$numRows    = commonQuery_numRows($result);

	$xmlResponse = "<items>";

	while ($row = commonQuery_fetchRow($result))
	{
		$memberId		= $row['memberId'];
		$name			= commonValidXml(trim("$row[firstname] $row[lastname]"));
		$email			= commonValidXml(($row['email'] == "") ? $row['username'] : $row['email']);
		$status			= $row['status'];

		if ($status == "new")
			$status	= "מחכה לאישור";
		else if ($status == "approved")
			$status	= "מאושר";
		else if ($status == "rejected")
			$status = "נדחה";

		$registerDate	= formatApplDateTime($row['registerDate']);
		$statusDate		= formatApplDateTime($row['statusDate']);
		
		$type			= $row['type'];

		switch ($type)
		{
			case "all"		: $type	= "הרשאות מלאות";			break;
			case "newMsg"	: $type	= "הוספת הודעות בלבד";		break;
			case "reply"	: $type	= "הוספת תגובות בלבד";		break;
		}

		$xmlResponse	.= "<item>
								<memberId>$memberId</memberId>
								<name>$name</name>
								<type>$type</type>
								<email>$email</email>
								<status>$status</status>
								<registerDate>$registerDate</registerDate>
								<statusDate>$statusDate</statusDate>
							</item>";
	}

	$xmlResponse	.= "</items>" . commonGetTotalXml($xmlRequest,$numRows,$total);

	return $xmlResponse;
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getMemberInfo																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getMemberInfo ($xmlRequest)
{
	$details	= "";

	$memberId	 = xmlParser_getValue($xmlRequest,"memberId");
	$forumId	 = xmlParser_getValue($xmlRequest,"forumId");

	if ($memberId != "")
	{
		$sql	= "select * from clubMembers where";
			   
		if (is_numeric($memberId))
			$sql	.= " id = $memberId";
		else
			$sql	.= " username like '$memberId%'";

		$sql	.= " order by username";

		$result	= commonDoQuery($sql);

		if (commonQuery_numRows($result) == 0)
		{
			$details	= "משתמש עם קוד זה לא קיים במערכת";
		}
		else
		{
			$row		= commonQuery_fetchRow($result);

			$details	= trim("$row[firstname] $row[lastname]");

			$memberId	= $row['id'];
		}

		$sql		= "select * from forumMembers where forumId = $forumId and memberId = '$memberId'";
		$result		= commonDoQuery($sql);

		if (commonQuery_numRows($result) == 1)
		{
			$details	= "משתמש זה כבר רשום לפורום";
		}
	}

	return "<details>$details</details>";

}

/* ----------------------------------------------------------------------------------------------------	*/
/* addForumMember																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function addForumMember ($xmlRequest)
{
	$memberId	 = xmlParser_getValue($xmlRequest,"memberId");
	$forumId	 = xmlParser_getValue($xmlRequest,"forumId");
	$type		 = xmlParser_getValue($xmlRequest,"type");

	if ($memberId == "" || $forumId == "")
		trigger_error ("חסרים פרטים");

	$sql	= "select * from clubMembers where";
			   
	if (is_numeric($memberId))
		$sql	.= " id = $memberId";
	else
		$sql	.= " username like '$memberId%'";

	$sql	.= " order by username";

	$result	= commonDoQuery($sql);

	if (commonQuery_numRows($result) == 0)
		trigger_error ("משתמש עם קוד זה לא קיים במערכת");

	$row	= commonQuery_fetchRow($result);
	$memberId	= $row['id'];

	$sql		= "select * from forumMembers where forumId = $forumId and memberId = '$memberId'";
	$result		= commonDoQuery($sql);

	if (commonQuery_numRows($result) == 1)
		trigger_error ("משתמש זה כבר רשום לפורום");

	$sql	= "insert into forumMembers (memberId, forumId, type, registerDate, status, statusDate) 
			   values ($memberId, $forumId, '$type', now(), 'approved', now())";
	commonDoQuery($sql);

	return "";

}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateForumMember																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateForumMember ($xmlRequest)
{
	$memberId	 = xmlParser_getValue($xmlRequest,"memberId");
	$forumId	 = xmlParser_getValue($xmlRequest,"forumId");
	$type		 = xmlParser_getValue($xmlRequest,"type");

	if ($memberId == "" || $forumId == "")
		trigger_error ("חסרים פרטים");

	$sql		= "select * from forumMembers where forumId = $forumId and memberId = $memberId";
	$result		= commonDoQuery($sql);

	$sql	= "update forumMembers set type = '$type' where memberId = $memberId and forumId = $forumId";
	commonDoQuery($sql);

	return "";

}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteForumMember																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteForumMember ($xmlRequest)
{
	$memberId	 = xmlParser_getValue($xmlRequest,"memberId");
	$forumId	 = xmlParser_getValue($xmlRequest,"forumId");

	if ($memberId == "" || $forumId == "")
		trigger_error ("חסרים פרטים");

	$sql		= "select * from forumMembers where forumId = $forumId and memberId = $memberId";
	$result		= commonDoQuery($sql);

	if (commonQuery_numRows($result) == 0)
		trigger_error ("משתמש זה אינו רשום לפורום");

	$sql		= "delete from forumMembers where forumId = $forumId and memberId = $memberId";
	commonDoQuery($sql);

	return "";

}


/* ----------------------------------------------------------------------------------------------------	*/
/* updateMemberStatus																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateMemberStatus ($xmlRequest)
{
	$memberId	 = xmlParser_getValue($xmlRequest,"memberId");
	$forumId	 = xmlParser_getValue($xmlRequest,"forumId");
	$newStatus	 = xmlParser_getValue($xmlRequest,"newStatus");

	if ($memberId == "" || $forumId == "" || $newStatus == "")
		trigger_error ("חסרים פרטים");

	$sql		= "select * from forumMembers where forumId = $forumId and memberId = $memberId";
	$result		= commonDoQuery($sql);

	if (commonQuery_numRows($result) == 0)
		trigger_error ("משתמש זה אינו רשום לפורום");

	$row		= commonQuery_fetchRow($result);

	if ($row['status'] == $newStatus)
	{
		if ($newStatus == "approved")
			trigger_error ("משתמש זה כבר אושר");
		else if ($newStatus == "rejected")
			trigger_error ("משתמש זה כבר נדחה");
	}

	$sql		= "update forumMembers set status = '$newStatus', statusDate = now() where forumId = $forumId and memberId = $memberId";
	commonDoQuery($sql);

	return "";

}

/* ----------------------------------------------------------------------------------------------------	*/
/* getMemberDetails																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getMemberDetails ($xmlRequest)
{
	$forumId	= xmlParser_getValue($xmlRequest, "forumId");
	$memberId	= xmlParser_getValue($xmlRequest, "memberId");

	if ($forumId == "" || $memberId == "")
		trigger_error ("חסרים פרטים");

	$sql			= "select * from clubMembers, forumMembers 
				   	   where forumMembers.memberId = clubMembers.id and forumMembers.forumId = $forumId and memberId = $memberId";
	$result			= commonDoQuery($sql);
	$row 			= commonQuery_fetchRow($result);

	$memberId		= $row['memberId'];
	$details		= commonValidXml(trim("$row[firstname] $row[lastname]"));
	$email			= commonValidXml(($row['email'] == "") ? $row['username'] : $row['email']);
	$status			= $row['status'];
	$type			= $row['type'];
	
	$xmlResponse	= " <memberId>$memberId</memberId>
						<details>$details</details>
						<type>$type</type>
						<email>$email</email>
						<status>$status</status>
						<registerDate>$registerDate</registerDate>
						<statusDate>$statusDate</statusDate>";

	return ($xmlResponse);
}

?>
