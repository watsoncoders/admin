<?php

/* ----------------------------------------------------------------------------------------------------	*/
/* getDiscussions																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getDiscussions ($xmlRequest)
{	
	global $usedLangs;
	$langsArray = explode(",",$usedLangs);
	$lang		= $langsArray[0];

	// get total
	$sql	 	= "select count(*) from discussions";
	$result	    = commonDoQuery ($sql);
	$row	    = commonQuery_fetchRow($result);
	$total	    = $row[0];

	// get details
	$sql    	= "select * from discussions, pages_byLang where pages_byLang.language = '$lang' and discussions.id = pages_byLang.pageId
				   order by id desc";
	$result	    = commonDoQuery ($sql);

	$numRows    = commonQuery_numRows($result);

	$xmlResponse = "<items>";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		$id   		  = $row['id'];
		$title 		  = commonValidXml ($row['title'],true);

		$xmlResponse .=	"<item>
							<id>$id</id>
							<title>$title</title>
						</item>";
	}

	$xmlResponse .=	"</items>";
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* addDiscussion																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function addDiscussion ($xmlRequest)
{
	return (editDiscussion ($xmlRequest, "add"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getDiscussionNextId																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function getDiscussionNextId ($xmlRequest)
{
	
	return "<discussionId>$id</discussionId>";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getDiscussionDetails																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function getDiscussionDetails ($xmlRequest)
{
	global $usedLangs;
	$langsArray = explode(",",$usedLangs);
	$lang		= $langsArray[0];

	$discussionId		= xmlParser_getValue($xmlRequest, "discussionId");

	if ($discussionId == "")
		trigger_error ("חסר קוד דיון לביצוע הפעולה");

	$sql	= "select pages.*, discussions.*, pages_byLang.language, pages_byLang.isReady, pages_byLang.title, pages_byLang.winTitle, 
						  pages_byLang.navTitle, pages_byLang.description, pages_byLang.keywords, pages_byLang.rewriteName
			   from discussions, pages, pages_byLang
			   where discussions.id = $discussionId 
			   and   pages.id = pages_byLang.pageId 
			   and   pages_byLang.language = '$lang'
			   and   discussions.id = pages.id";
	$result		= commonDoQuery ($sql);

	if (commonQuery_numRows($result) == 0)
		trigger_error ("דיון קוד זה ($discussionId) לא קיים במערכת. לא ניתן לבצע את העדכון");

	$row = commonQuery_fetchRow($result);

	$domainRow   		= commonGetDomainRow ();
	$siteUrl     		= commonGetDomainName($domainRow);

	$layoutId		    = $row['layoutId'];
	$membersOnly  	 	= $row['membersOnly'];
	$navParentId		= $row['navParentId'];
	$msgNeedApproval	= $row['msgNeedApproval'];
	$approvalEmail		= $row['approvalEmail'];
	$isReady			= $row['isReady'];

	$title 				= commonValidXml($row['title']);
	$promo 				= commonValidXml($row['promo']);
	$waitForApprovalMsg	= commonValidXml($row['waitForApprovalMsg']);

	$winTitle			= commonValidXml($row['winTitle']);
	$navTitle			= commonValidXml($row['navTitle']);
	$keywords			= commonValidXml($row['keywords']);
	$description		= commonValidXml($row['description']);
	$rewriteName   		= commonValidXml($row['rewriteName']);
		
	$xmlResponse	= 	"<discussionId>$discussionId</discussionId>
						 <layoutId>$layoutId</layoutId>
						 <membersOnly>$membersOnly</membersOnly>
					 	 <navParentId>$navParentId</navParentId>
						 <isReady>$isReady</isReady>
						 <siteUrl>$siteUrl/index2.php</siteUrl>
						 <msgNeedApproval>$msgNeedApproval</msgNeedApproval>
						 <approvalEmail>$approvalEmail</approvalEmail>
						 <title>$title</title>
						 <promo>$promo</promo>
						 <waitForApprovalMsg>$waitForApprovalMsg</waitForApprovalMsg>
						 <winTitle>$winTitle</winTitle>
						 <navTitle>$navTitle</navTitle>
						 <keywords>$keywords</keywords>
						 <description>$description</description>
						 <rewriteName>$rewriteName</rewriteName>";

	return $xmlResponse;
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateDiscussion																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateDiscussion ($xmlRequest)
{
	editDiscussion ($xmlRequest, "update");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* editDiscussion																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function editDiscussion ($xmlRequest, $editType)
{
	global $usedLangs;
	$langsArray = explode(",",$usedLangs);
	$lang		= $langsArray[0];

	if ($editType == "update")
	{
		$id		= xmlParser_getValue($xmlRequest, "discussionId");
	
		$sql	= "select count(*) from discussions where id = '$id'";
		$result	= commonDoQuery ($sql);
		$row	= commonQuery_fetchRow($result);

		if ($row[0] == 0)
		{
			trigger_error ("דיון עם קוד זה ($id) לא קיים במערכת. לא ניתן לבצע את העדכון");
		}
	}
	else
	{
		$sql	= "select max(id) from pages";
		$result	= commonDoQuery ($sql);
		$row	= commonQuery_fetchRow ($result);
		$id 	= $row[0] + 1;
	}

	$layoutId			= xmlParser_getValue($xmlRequest, "layoutId");
	$membersOnly		= xmlParser_getValue($xmlRequest, "membersOnly");
	$isReady			= xmlParser_getValue($xmlRequest, "isReady");
	$navParentId		= xmlParser_getValue($xmlRequest, "navParentId");
	$msgNeedApproval	= xmlParser_getValue($xmlRequest, "msgNeedApproval");
	$approvalEmail		= xmlParser_getValue($xmlRequest, "approvalEmail");
	$title				= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "title")));
	$promo				= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "promo")));
	$waitForApprovalMsg	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "waitForApprovalMsg")));

	$winTitle			= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "winTitle")));
	$navTitle			= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "navTitle")));
	$keywords			= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "keywords")));
	$description		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "description")));
	$rewriteName		= str_replace(" ", "-", addslashes(commonDecode(xmlParser_getValue($xmlRequest, "rewriteName"))));

	if ($editType == "add")
	{
		$sql 	= "insert into pages (id, type, layoutId, navParentId, membersOnly) 
				   values ('$id', 'discussion', '$layoutId', '$navParentId', '$membersOnly')";
		commonDoQuery ($sql);

		$sql	= "insert into pages_byLang (pageId, language, navTitle, winTitle, title, isReady, keywords, description, rewriteName)
				   values ('$id', '$lang', '$navTitle', '$winTitle', '$title', '$isReady', '$keywords', '$description', '$rewriteName')";
		commonDoQuery ($sql);

		$sql 	= "insert into discussions (id, promo, msgNeedApproval, approvalEmail, waitForApprovalMsg) 
				   values ($id, '$promo', '$msgNeedApproval', '$approvalEmail', '$waitForApprovalMsg')";
		commonDoQuery ($sql);
	}
	else // update
	{
		$sql = "update pages set layoutId 					= '$layoutId',
								 navParentId				= '$navParentId',
								 membersOnly 				= '$membersOnly'
				where id = $id";
		commonDoQuery ($sql);

		$sql = "update pages_byLang set navTitle 			= '$navTitle',
									    winTitle			= '$winTitle',
										title 				= '$title',
										isReady 			= '$isReady',
										keywords 			= '$keywords',
										description			= '$description',
										rewriteName			= '$rewriteName'
				where pageId = $id";
		commonDoQuery ($sql);

		$sql = "update discussions set promo 				= '$promo',
								 	   msgNeedApproval		= '$msgNeedApproval',
									   approvalEmail 		= '$approvalEmail',
									   waitForApprovalMsg	= '$waitForApprovalMsg'
				where id = $id";
		commonDoQuery ($sql);
	}

	$domainRow  = commonGetDomainRow ();
	$domainName = commonGetDomainName ($domainRow);

	// Update .htaccess with mod_rewrite rules
	fopen("$domainName/updateModRewrite.php","r");

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getDiscussionMsgs																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function getDiscussionMsgs ($xmlRequest)
{	
	$discussionId = xmlParser_getValue($xmlRequest, "discussionId");

	if ($discussionId == "")
	{
		return "<items></items>";
	}

	$msgType 	= xmlParser_getValue($xmlRequest, "msgType");

	// get num msgs
	$sql	 	= "select count(*) from discussionsMsgs where discussionId = $discussionId and parentId = 0";
	$result	    = commonDoQuery ($sql);
	$row	    = commonQuery_fetchRow($result);
	$numMsgs	= $row[0];

	// get details
	$sql		= "select * from discussionsMsgs 
				   where discussionId = $discussionId 
				   and parentId=0
				   order by insertTime desc " . commonGetLimit ($xmlRequest);
	$result	     = commonDoQuery ($sql);

	$numRows    = commonQuery_numRows($result);

	$xmlResponse = "<items>";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		$xmlResponse .= getDiscussionMsgXml ($row, $msgType, "");
 	}

	$xmlResponse .=	"</items>" .
					str_replace("</totalText>", commonPhpEncode(" (הודעות ראשיות)") . "</totalText>", commonGetTotalXml($xmlRequest,$numRows,$numMsgs));
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getDiscussionMsgXml																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function getDiscussionMsgXml ($row, $msgType, $prefix)
{
	$content	= $row['content'];
	$content	= commonCutText(str_replace("\n","",$content), 50, "...");

	if ($row['parentId'] != 0)
	{
		$content = "$prefix$content";
	}
	else
	{
		$content = "<strong>$content</strong>";
	}

	$msgId 		= $row['id'];
	$content	= commonValidXml("<span title='$row[content]'>$content</span>");
	$insertTime	= formatApplDateTime ($row['insertTime']);
	$writer		= commonValidXml ($row['writer']);
	$ip			= $row['ip'];
	$parentId	= $row['parentId'];
	$status		= $row['status'];

	switch ($status)
	{
		case "new"		: $status = "<span style='color:red'>חדשה!</span>";	break;
		case "approved"	: $status = "<span style='color:green'>אושרה</span>";	break;
		case "rejected"	: $status = "<span style='color:gray'>נדחתה</span>";	break;
	}

	$status		= commonPhpEncode($status);

	$xml	 	= "<item>
						<msgId>$msgId</msgId>
						<status>$status</status>
						<content>$content</content>
						<insertTime>$insertTime</insertTime>
						<writer>$writer</writer>
						<ip>$ip</ip>
						<parentId>$parentId</parentId>
				  </item>";

	if ($msgType == "all")
	{
		$prefix	= str_replace("raquo", "nbsp", $prefix);

		$sql	= "select * from discussionsMsgs 
				   where discussionId = $row[discussionId] and parentId = $row[id]
				   order by insertTime desc";
		$subResult	= commonDoQuery ($sql);

		while ($subRow = commonQuery_fetchRow ($subResult))
		{
			$xml .= getDiscussionMsgXml ($subRow, $msgType, "$prefix&raquo;&nbsp;&nbsp;");
		}
	}

	return ($xml);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getDiscussionMsgDetails																				*/
/* ----------------------------------------------------------------------------------------------------	*/
function getDiscussionMsgDetails ($xmlRequest)
{
	$msgId	= xmlParser_getValue($xmlRequest, "msgId");

	if ($msgId == "")
		trigger_error ("חסר קוד הודעה לביצוע הפעולה");

	$sql	= "select * from discussionsMsgs where id = $msgId";
	$result		= commonDoQuery ($sql);

	if (commonQuery_numRows($result) == 0)
		trigger_error ("הודעה קוד זה ($msgId) לא קיימת במערכת. לא ניתן לבצע את העדכון");

	$row 				= commonQuery_fetchRow($result);
			
	$msgId 		  = $row['id'];
	$content	  = commonValidXml			($row['content']);
	$insertTime	  = formatApplFormDateTime  ($row['insertTime']);
	$writer		  = commonValidXml	   		($row['writer']);
	$status		  = $row['status'];
	$parentId	  = $row['parentId'];

	$msgType	  = "main";
	if ($parentId != "0")
		$msgType = "sub";
	else
		$parentId = "";

	$xmlResponse  =	"<msgId>$msgId</msgId>
					 <content>$content</content>
					 <insertTime>$insertTime</insertTime>
					 <writer>$writer</writer>
					 <status>$status</status>
					 <parentId>$parentId</parentId>
					 <msgType>$msgType</msgType>";

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
	$content 			= addslashes(commonDecode(xmlParser_getValue($xmlRequest,"content")));
	$writer 			= addslashes(commonDecode(xmlParser_getValue($xmlRequest,"writer")));
	$discussionId 		= xmlParser_getValue($xmlRequest, "discussionId"); 
	$parentId 			= xmlParser_getValue($xmlRequest, "parentId"); 
	$status 			= xmlParser_getValue($xmlRequest, "status"); 

	if ($editType == "add")
	{
		$discussionId	= xmlParser_getValue($xmlRequest, "discussionId");

		if ($discussionId == "")
			trigger_error ("חסר קוד דיון לביצוע הפעולה");

		$sql = "insert into discussionsMsgs (parentId, discussionId, insertTime, status, writer, content)
				values ('$parentId', $discussionId, now(), '$status', '$writer', '$content')";
	}
	else // update
	{
		$msgId		= xmlParser_getValue($xmlRequest, "msgId");
		
		if ($msgId == "")
			trigger_error ("חסר קוד הודעה לביצוע הפעולה");

		if ($parentId == $msgId)
			trigger_error ("לא ניתן להפוך הודעה לתגובה על עצמה");

		if ($parentId != "")
		{
			// check that there is such msg
			$sql = "select * from discussionsMsgs where id='$parentId'";
			$result	  = commonDoQuery ($sql);

			if (commonQuery_numRows($result) == 0)
				trigger_error ("אין הודעה בעלת מספר ההודעה שנבחרה ($parentId)");
		}

		$sql = "update discussionsMsgs set status      	= '$status',
										   writer		= '$writer',
										   content    	= '$content',
										   parentId	 	= '$parentId'
			    where id=$msgId";
	}

	commonDoQuery ($sql);

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteDiscussionMsg																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteDiscussionMsg ($xmlRequest)
{
	$msgIds = xmlParser_getValues ($xmlRequest, "msgId");

	if (count($msgIds) == 0)
		trigger_error ("חסר קוד הודעה לביצוע הפעולה");

	foreach ($msgIds as $msgId)
	{
		doDeleteMsg ($msgId);
	}

	return "";
}

function doDeleteMsg ($msgId)
{
	$sql	= "select id from discussionsMsgs where parentId = $msgId";
	$result	= commonDoQuery($sql);

	while ($row = commonQuery_fetchRow($result))
	{
		doDeleteMsg ($row['id']);
	}

	$sql =  "delete from discussionsMsgs where id = $msgId";
	commonDoQuery ($sql);
}

?>
