<?php

/* ----------------------------------------------------------------------------------------------------	*/
/* getProductFeedbacks																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function getProductFeedbacks ($xmlRequest)
{
	return getFeedbacks ($xmlRequest, "product");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getTalkbacks																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function getTalkbacks ($xmlRequest)
{
	return getFeedbacks ($xmlRequest, "essay");
}

/* ----------------------------------------------------------------------------------------------------	*/
/*  deleteAllTalkbacks																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteAllTalkbacks ($xmlRequest)
{
	return deleteAllFeedbacks ($xmlRequest, "essay");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getEventsFeedbacks																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function getEventsFeedbacks ($xmlRequest)
{
	return getFeedbacks ($xmlRequest, "event");
}


/* ----------------------------------------------------------------------------------------------------	*/
/* getPostComments																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getPostComments ($xmlRequest)
{
	return getFeedbacks ($xmlRequest, "blogPost");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getSpecificFeedbacks																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function getSpecificFeedbacks ($xmlRequest)
{
	return getFeedbacks ($xmlRequest, "specific");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getFeedbacks																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function getFeedbacks ($xmlRequest, $type)
{
	global $usedLangs;
	$langsArray = explode(",",$usedLangs);

	$condition  = "";

	switch ($type)
	{
		case "product" :
			$itemName   = "shopProducts_byLang.name";
			$leftJoin   = "left join shopProducts_byLang on feedbacks.itemId = shopProducts_byLang.productId 
								and  shopProducts_byLang.language = '$langsArray[0]'";
			break;

		case "essay" :
			$itemName   = "pages_byLang.title";
			$leftJoin   = "left join pages_byLang       on feedbacks.itemId = pages_byLang.pageId and pages_byLang.language = '$langsArray[0]'";
			break;

		case "event" :
			$itemName   = "events_byLang.name";
			$leftJoin   = "left join events_byLang       on feedbacks.itemId = events_byLang.eventId and events_byLang.language = '$langsArray[0]'";
			break;

		case "blogPost" :
			$itemName   = "blogsPosts.title";
			$leftJoin   = "left join blogsPosts       on feedbacks.itemId = blogsPosts.id";
			break;
	}

	$domainRow   = commonGetDomainRow ();
	$siteUrl     = commonGetDomainName($domainRow);

	commonConnectToUserDB ($domainRow);

	if ($domainRow['id'] == 513)
	{
		$type		= "specific";
		$itemName	= "ham_r13s.name";
		$leftJoin	= "left join ham_r13s on feedbacks.itemId = ham_r13s.id";
	}

	$project	= xmlParser_getValue($xmlRequest, "project");

	switch ($project)
	{
		case "marpad"	:
			$itemName   = "marpad_lessons.subject";
			$leftJoin   = "left join marpad_lessons on feedbacks.itemId = marpad_lessons.id";
			break;

		case "forex"	:
			$itemName   = "f_strategies.name";
			$leftJoin   = "left join f_strategies on feedbacks.itemId = f_strategies.id";
			break;

		case "shefanet"	:
			$itemName   = "shefanet_biz.bizName";
			$leftJoin   = "left join shefanet_biz on feedbacks.itemId = shefanet_biz.pageId";
			break;

		case "dailyzh"	:
			$itemName   = "dailyzh_lessons.lessonKey";
			$leftJoin   = "left join dailyzh_lessons on feedbacks.itemId = dailyzh_lessons.pageId";
			break;

		case "abiliko"	:
			$itemName   = "abiliko_questions.questionId";
			$leftJoin   = "left join abiliko_questions on feedbacks.itemId = abiliko_questions.questionId";
			break;

		case "isrlist"	:
			$itemName   = "isrlist_projects.pageId";
			$leftJoin   = "left join isrlist_projects on feedbacks.itemId = isrlist_projects.pageId";
			break;

		case "wbc"	:
			$itemName   = "wbc_biz.name";
			$leftJoin   = "left join wbc_biz on feedbacks.itemId = wbc_biz.pageId";
			break;

		case "beruham"	:
			$itemName   = "beruham_noflim.name";
			$leftJoin   = "left join beruham_noflim on feedbacks.itemId = beruham_noflim.pageId";
			break;

	}

	$status		= xmlParser_getValue($xmlRequest, "status");
	if ($status != "")
	{
		$condition = " and feedbacks.status = '$status' ";
	}

	$itemId  = xmlParser_getValue($xmlRequest, "itemId");
	if ($itemId != "")
	{
		$condition .= " and feedbacks.itemId = $itemId ";
	}
	
	$sortBy		= xmlParser_getValue($xmlRequest,"sortBy");

	if ($sortBy == "")
		$sortBy = "id";

	$sortDir	= xmlParser_getValue($xmlRequest,"sortDir");
	if ($sortDir == "")
		$sortDir = "desc";

//	$byType	= "";

//	if ($type != "essay")
//		$byType = "type = '$type'";

	// get total
	$queryStr	 = "select count(*) from feedbacks where feedbacks.type = '$type' $condition";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$total	     = $row[0];

	// get details
	$queryStr    = "select feedbacks.*, $itemName as itemName, clubMembers.firstname, clubMembers.lastname, clubMembers.nickname
					from feedbacks
					left join clubMembers on feedbacks.userId = clubMembers.id
					$leftJoin
					where feedbacks.type = '$type' $condition
					order by $sortBy $sortDir " . commonGetLimit ($xmlRequest);
	$result	     = commonDoQuery ($queryStr);

	$numRows    = commonQuery_numRows($result);

	$xmlResponse = "<items>";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		if ($row['userName'] == "")
			$row['userName'] = stripslashes ($row['firstname'] . " " . $row['lastname']);

		if (trim($row['userName']) == "")
			$row['userName'] = stripslashes ($row['nickname']);

		$id   		= $row['id'];
		$itemName  	= commonValidXml ($row['itemName'],true);
		$itemId		= $row['itemId'];
		$lang		= $row['language'];
		$score		= $row['score'];
		$userName  	= commonValidXml ($row['userName'],true);
		$feedback  	= commonValidXml (nl2br($row['feedback']));
		$title  	= commonValidXml ($row['title'],true);
		$userEmail  = commonValidXml ($row['userEmail'],true);
		$userAge  	= commonValidXml ($row['userAge'],true);
		$userCity   = commonValidXml ($row['userCity'],true);
		$status		= $row['status'];
		$statusText = formatFeedbackStatus($status);
		$insertDate = formatApplDateTime ($row['insertDate']);

		$itemLink	= commonValidXml("<a href='$siteUrl/index2.php?id=$itemId&lang=$lang' target='_blank'>$row[itemName]</a>");

		if ($domainRow['id'] == 513)
		{
			$itemLink	= commonValidXml("<a href='$siteUrl/index2.php?id=36&hamId=$itemId&lang=$lang' target='_blank'>$row[itemName]</a>");
		}

		$xmlResponse .=	"<item>
							 <id>$id</id>
							 <itemId>$itemId</itemId>
							 <lang>$lang</lang>
							 <itemName>$itemName</itemName>
							 <itemLink>$itemLink</itemLink>
							 <score>$score</score>
							 <userName>$userName</userName>
							 <feedback>$feedback</feedback>
							 <title>$title</title>
							 <userEmail>$userEmail</userEmail>
							 <userAge>$userAge</userAge>
							 <userCity>$userCity</userCity>
							 <status>$status</status>
							 <statusText>$statusText</statusText>
							 <insertDate>$insertDate</insertDate>
							 <siteUrl>$siteUrl/index2.php</siteUrl>
						 </item>";
	}

	$xmlResponse .=	"</items>"												.
					commonGetTotalXml($xmlRequest,$numRows,$total);
	
	return ($xmlResponse);
	
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getProductFeedbackDetails																			*/
/* ----------------------------------------------------------------------------------------------------	*/
function getProductFeedbackDetails ($xmlRequest)
{
	return getFeedbackDetails ($xmlRequest, "product");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getTalkbackDetails																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function getTalkbackDetails ($xmlRequest)
{
	return getFeedbackDetails ($xmlRequest, "essay");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getEventFeedbackDetails																				*/
/* ----------------------------------------------------------------------------------------------------	*/
function getEventFeedbackDetails ($xmlRequest)
{
	return getFeedbackDetails ($xmlRequest, "event");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getPostCommentDetails																				*/
/* ----------------------------------------------------------------------------------------------------	*/
function getPostCommentDetails ($xmlRequest)
{
	return getFeedbackDetails ($xmlRequest, "blogPost");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getSpecificFeedbackDetails																			*/
/* ----------------------------------------------------------------------------------------------------	*/
function getSpecificFeedbackDetails ($xmlRequest)
{
	return getFeedbackDetails ($xmlRequest, "specific");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getFeedbackDetails																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function getFeedbackDetails ($xmlRequest, $type)
{
	global $usedLangs;
	$langsArray = explode(",",$usedLangs);
	$lang		= $langsArray[0];

	switch ($type)
	{
		case "product" :
			$itemName   = "shopProducts_byLang.name";
			$leftJoin   = "left join shopProducts_byLang on feedbacks.itemId = shopProducts_byLang.productId and shopProducts_byLang.language = '$lang'";
			break;

		case "essay" :
			$itemName   = "pages_byLang.title";
			$leftJoin   = "left join pages_byLang       on feedbacks.itemId = pages_byLang.pageId and pages_byLang.language = '$lang'";
			break;

		case "event" :
			$itemName   = "events_byLang.name";
			$leftJoin   = "left join events_byLang       on feedbacks.itemId = events_byLang.eventId and events_byLang.language = '$lang'";
			break;

		case "blogPost" :
			$itemName   = "blogsPosts.title";
			$leftJoin   = "left join blogsPosts       on feedbacks.itemId = blogsPosts.id";
			break;
	}

	$project	= xmlParser_getValue($xmlRequest, "project");

	switch ($project)
	{
		case "marpad"	:
			$itemName   = "marpad_lessons.subject";
			$leftJoin   = "left join marpad_lessons on feedbacks.itemId = marpad_lessons.id";
			break;

		case "forex"	:
			$itemName   = "f_strategies.name";
			$leftJoin   = "left join f_strategies on feedbacks.itemId = f_strategies.id";
			break;

		case "shefanet"	:
			$itemName   = "shefanet_biz.bizName";
			$leftJoin   = "left join shefanet_biz on feedbacks.itemId = shefanet_biz.pageId";
			break;

		case "dailyzh"	:
			$itemName   = "dailyzh_lessons.lessonKey";
			$leftJoin   = "left join dailyzh_lessons on feedbacks.itemId = dailyzh_lessons.pageId";
			break;

		case "abiliko"	:
			$itemName   = "abiliko_questions.questionId";
			$leftJoin   = "left join abiliko_questions on feedbacks.itemId = abiliko_questions.questionId";
			break;

		case "isrlist"	:
			$itemName   = "isrlist_projects.name";
			$leftJoin   = "left join isrlist_projects on feedbacks.itemId = isrlist_projects.pageId";
			break;

		case "wbc"	:
			$itemName   = "wbc_biz.name";
			$leftJoin   = "left join wbc_biz on feedbacks.itemId = wbc_biz.pageId";
			break;

	}

	$id		= xmlParser_getValue($xmlRequest, "id");
	if ($id == "")
	{
		trigger_error ("חסר מזהה תגובת גולש לביצוע הפעולה");
	}

	// get details
	$queryStr    = "select feedbacks.*, $itemName as itemName, clubMembers.firstname, clubMembers.lastname, clubMembers.nickname, clubMembers.email
					from feedbacks
					left join clubMembers on feedbacks.userId = clubMembers.id
					$leftJoin
					where feedbacks.id = $id
					order by feedbacks.id";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);

	if ($row['userName'] == "")
		$row['userName'] = stripslashes ($row['firstname'] . " " . $row['lastname']);

	if (trim($row['userName']) == "")
		$row['userName'] = stripslashes ($row['nickname']);

	if ($row['userEmail'] == "")
		$row['userEmail'] = $row['email'];

	$itemName  	= commonValidXml ($row['itemName'],true);
	$score		= $row['score'];
	$userName  	= commonValidXml ($row['userName'],true);
	$userEmail 	= commonValidXml ($row['userEmail'],true);
	$userCity  	= commonValidXml ($row['userCity'],true);
	$userAge 	= commonValidXml ($row['userAge'],true);
	$status		= $row['status'];
	$title  	= commonValidXml ($row['title'],true);
	$feedback  	= commonValidXml ($row['feedback'],true);

	$xmlResponse =	"<id>$id</id>
					 <itemName>$itemName</itemName>
					 <score>$score</score>
					 <userName>$userName</userName>
					 <userEmail>$userEmail</userEmail>
					 <userCity>$userCity</userCity>
					 <userAge>$userAge</userAge>
					 <feedback>$feedback</feedback>
					 <title>$title</title>
					 <status>$status</status>";

	
	return ($xmlResponse);
	
}

/* ----------------------------------------------------------------------------------------------------	*/
/* handleFeedback																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function handleFeedback ($xmlRequest)
{
	$id		= xmlParser_getValue($xmlRequest, "id");
	if ($id == "")
	{
		trigger_error ("חסר מזהה תגובת גולש לביצוע הפעולה");
	}

	$newStatus = xmlParser_getValue($xmlRequest, "newStatus");
	if ($newStatus == "")
	{
		trigger_error ("חסר סטטוס תגובה לשם עדכונה");
	}

	if ($newStatus == "delete")
	{
		$queryStr = "delete from feedbacks where id=$id";
	}
	else
	{
		$queryStr = "update feedbacks set status='$newStatus' where id=$id";
	}
	commonDoQuery ($queryStr);
	
	return ("");
	
}
/* ----------------------------------------------------------------------------------------------------	*/
/* deleteAllFeedbacks																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteAllFeedbacks ($xmlRequest, $type)
{
	$queryStr = "delete from feedbacks where type = '$type'";
	commonDoQuery ($queryStr);
	
	return ("");
	
}
?>
