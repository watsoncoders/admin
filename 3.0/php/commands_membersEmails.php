<?php

/* ____________________________________________________________________________________________________ */
/*																										*/
/*                                             F O L D E R S                                            */
/* ____________________________________________________________________________________________________ */


/* ----------------------------------------------------------------------------------------------------	*/
/* getFolders																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function getFolders ($xmlRequest)
{	
	$conditions = "";

	$memberId = xmlParser_getValue($xmlRequest, "memberId");

	if ($memberId != "")
		$conditions .= " and (memberId = 0 or memberId = $memberId) ";

	// get total
	$queryStr	 = "select count(*) from emailFolders where 1 $conditions";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$total	     = $row[0];

	// get details
	$queryStr = "select * from emailFolders 
				 where 1 $conditions
				 order by id " . commonGetLimit ($xmlRequest);

	$result	     = commonDoQuery ($queryStr);

	$numRows    = commonQuery_numRows($result);

	$xmlResponse = "<items>";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		$id   		= $row['id'];
		$name		= commonValidXml ($row['name']);

		$xmlResponse .=	"<item>
							<id>$id</id>
				 			<name>$name</name>
						 </item>";
	}

	$xmlResponse .=	"</items>" .
					commonGetTotalXml($xmlRequest,$numRows,$total);
	
	return ($xmlResponse);
}

/* ____________________________________________________________________________________________________ */
/*																										*/
/*                                        E M A I L    M S G S                                          */
/* ____________________________________________________________________________________________________ */

/* ----------------------------------------------------------------------------------------------------	*/
/* getEmailMsgs																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function getEmailMsgs ($xmlRequest)
{	
	$memberId 	= xmlParser_getValue($xmlRequest, "memberId");
	$folderId 	= xmlParser_getValue($xmlRequest, "folderId"); 

	$queryStr   = "select emailMsgs.*, clubMembers.username, clubMembers.firstname, clubMembers.lastname, clubMembers.nickname
			       from emailMsgs
				   left join clubMembers on clubMembers.id = sendMemberId
				   where memberId = $memberId and folderId = '$folderId'";
	$result	    = commonDoQuery ($queryStr);
	$row	    = commonQuery_fetchRow($result);
	$total	    = commonQuery_numRows($result);

	$queryStr  .= " order by insertTime desc " . commonGetLimit ($xmlRequest);
	$result	    = commonDoQuery ($queryStr);

	$numRows   	= commonQuery_numRows($result);

	$xmlResponse = "<items>";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		$id   			= $row['id'];

		$sendMember		= $row['username'];
		if ($sendMember == "")
			$sendMember = commonPhpEncode("- $row[sendMemberId]# אינו קיים במערכת - ");
		else
			$sendMember	= commonValidXml ($sendMember);

		if ($row['firstname'] == "")
			$sendName	= $row['nickname'];
		else
			$sendName	= $row['firstname'] . " " . $row['lastname'];

		if (trim($sendName) == "")
			$sendName = commonPhpEncode("- $row[sendMemberId]# אינו קיים במערכת - ");
		else
			$sendName		= commonValidXml ($sendName);

		$status			= formatEmailMsgStatus($row['status']);
		$insertTime		= formatApplDateTime($row['insertTime']);
		$subject		= commonValidXml (commonCutText($row['subject'],100));
		$fullSubject	= commonValidXml ($row['subject']);

		$xmlResponse .=	"<item>
							<id>$id</id>
							<sendMember>$sendMember</sendMember>
							<sendName>$sendName</sendName>
							<status>$status</status>
							<insertTime>$insertTime</insertTime>
							<subject>$subject</subject>
							<fullSubject>$fullSubject</fullSubject>
						 </item>";
	}

	$xmlResponse .=	"</items>" .
					commonGetTotalXml($xmlRequest,$numRows,$total);
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getEmailMsgDetails																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function getEmailMsgDetails ($xmlRequest)
{
	$id		= xmlParser_getValue($xmlRequest, "id");

	if ($id == "")
		trigger_error ("חסר קוד הודעה לביצוע הפעולה");

	$queryStr = "select emailMsgs.*, clubMembers.username, clubMembers.firstname, clubMembers.lastname, clubMembers.nickname, 
						emailFolders.name as folderName
			     from (emailMsgs, emailFolders)
				 left join clubMembers on clubMembers.id = sendMemberId
				 where emailFolders.id = emailMsgs.folderId
				 and   emailMsgs.id = $id";
	$result		= commonDoQuery ($queryStr);

	if (commonQuery_numRows($result) == 0)
		trigger_error ("הודעה עם קוד זה ($id) לא קיימת במערכת. לא ניתן לבצע את הפעולה");

	$row			= commonQuery_fetchRow($result);

	$id   			= $row['id'];
	$folderName		= commonValidXml ($row['folderName']);

	$sendMember		= $row['username'];
	if ($sendMember == "")
		$sendMember = commonPhpEncode("- $row[sendMemberId]# אינו קיים במערכת - ");
	else
		$sendMember	= commonValidXml ($sendMember);

	if ($row['firstname'] == "")
		$sendName	= $row['nickname'];
	else
		$sendName	= $row['firstname'] . " " . $row['lastname'];

	if (trim($sendName) == "")
		$sendName = commonPhpEncode("- $row[sendMemberId]# אינו קיים במערכת - ");
	else
		$sendName		= commonValidXml ($sendName);

	$status			= formatEmailMsgStatus($row['status']);
	$insertTime		= formatApplDateTime($row['insertTime']);
	$subject		= commonValidXml ($row['subject']);
	$content		= commonValidXml ($row['content']);

	$xmlResponse 	=	"<id>$id</id>
				    	 <folderName>$folderName</folderName>
						 <sendMember>$sendMember</sendMember>
						 <sendName>$sendName</sendName>
						 <status>$status</status>
						 <insertTime>$insertTime</insertTime>
						 <subject>$subject</subject>
						 <content>$content</content>";

	return $xmlResponse;
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteEmailMsg																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteEmailMsg ($xmlRequest)
{
	$id = xmlParser_getValue($xmlRequest, "id");

	if ($id == "")
		trigger_error ("חסר קוד הודעה לביצוע הפעולה");

	$queryStr = "delete from emailMsgs where id = $id";
	commonDoQuery ($queryStr);
	
	return "";	
}

?>
