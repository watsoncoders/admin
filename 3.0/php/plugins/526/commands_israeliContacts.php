<?php

/* ----------------------------------------------------------------------------------------------------	*/
/* getContacts																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function getContacts ($xmlRequest)
{	
	$conditions = "";

	$sortBy		= xmlParser_getValue($xmlRequest,"sortBy");

	if ($sortBy == "" || $sortBy == "id")
		$sortBy = "israeli_contacts.id";

	$sortDir	= xmlParser_getValue($xmlRequest,"sortDir");
	if ($sortDir == "")
		$sortDir = "desc";

	$id		= xmlParser_getValue($xmlRequest,"id");
	if ($id != "")
		$conditions .= " and israeli_contacts.id = '$id'";

	$memberId		= xmlParser_getValue($xmlRequest,"memberId");
	if ($memberId != "")
		$conditions .= " and israeli_contacts.memberId = '$memberId'";

	$fullname		= xmlParser_getValue($xmlRequest,"fullname");
	if ($fullname != "")
		$conditions .= " and (israeli_contacts.fullname like '%$fullname%' or clubMembers.firstname like '%$fullname%')";

	$type		= xmlParser_getValue($xmlRequest,"type");
	if ($type != "")
		$conditions .= " and israeli_contacts.type = '$type'";

	// get total
	$sql    = "select israeli_contacts.*, clubMembers.firstname as memberName, clubMembers.email as memberEmail, clubMembers.phone as memberPhone
			   from israeli_contacts
			   left join clubMembers on israeli_contacts.memberId = clubMembers.id
			   where mainId = 0
			   $conditions order by $sortBy $sortDir";
	$result	= commonDoQuery ($sql);
	$total	= commonQueryNumRows($result);

	// get details
	$sql    .= commonGetLimit ($xmlRequest);
	$result  = commonDoQuery ($sql);
	$numRows = commonQueryNumRows($result);

	$xmlResponse = "<items>";

	while ($row = commonQueryFetchRow($result))
	{
		$id				= $row['id'];
		$type 			= $row['type'];
		$status 		= $row['status'];

		$type			= commonValidXml (getEnumText($type));
		$status			= commonValidXml (getEnumText($status));

		$fullname		= $row['fullname'];
		$phone			= $row['phone'];
		$email			= $row['email'];

		if ($row['memberId'] != 0)
		{
			$fullname	= $row['memberName'];
			$phone		= $row['memberPhone'];
			$email		= $row['memberEmail'];
		}

		$title	 		= commonValidXml 	 ($row['title']);
		$insertTime		= formatApplDateTime ($row['insertTime']);

		$xmlResponse .=	"<item>
							<id>$id</id>
							<type>$type</type>
							<status>$status</status>
							<title>$title</title>
							<fullname>$fullname</fullname>
							<phone>$phone</phone>
							<email>$email</email>
							<insertTime>$insertTime</insertTime>
						 </item>";
	}

	$xmlResponse .=	"</items>"								.
					commonGetTotalXml($xmlRequest,$numRows,$total);
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getEnumText																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function getEnumText ($text)
{
	switch($text)
	{
	case "sales":		$text	= "מכירות"; break;
	case "support":		$text	= "תמיכה טכנית"; break;
	case "crm":			$text	= "שירות לקוחות"; break;
	case "waiting":		$text	= "ממתין לטיפול"; break;
	case "ongoing":		$text	= "<span style='color:orange'>בטיפול</span>"; break;
	case "done":		$text	= "<span style='color:green'>טופל</span>"; break;
	}

	return $text;
}

/* ----------------------------------------------------------------------------------------------------	*/
/* addContact																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function addContact ($xmlRequest)
{
	$memberId	= xmlParser_getValue($xmlRequest, "memberId");

	if ($memberId == "")
		trigger_error (iconv("utf-8", "windows-1255", "חסר קוד חבר לביצוע הפעולה"));

	$sql 		= "select * from clubMembers where id = $memberId";
	$result		= commonDoQuery($sql);
	$memberRow	= commonQueryFetchRow($result);

	$name		= trim(stripslashes($memberRow['lastname']) . " " . stripslashes($memberRow['firstname']));
	$toEmail	= $memberRow['email'];

	$sql 		= "select max(id) from israeli_contacts";
	$result		= commonDoQuery($sql);
	$row		= commonQueryFetchRow($result);
	$id			= $row[0] + 1;

	$title		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "title")));
	$content	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "content")));

	$sql		= "insert into israeli_contacts (id, mainId, sender, insertTime, memberId, type, title, content)
				   values ($id, 0, 'crm', now(), $memberId, 'customers', '$title', '$content')";
	commonDoQuery($sql);

	sendEmail ($id, $toEmail, $title, $content, "");

	return "";
}


/* ----------------------------------------------------------------------------------------------------	*/
/* sendContact																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function sendContact ($xmlRequest)
{
	$mainId	= xmlParser_getValue($xmlRequest, "id");

	if ($mainId == "")
		trigger_error (iconv("utf-8", "windows-1255", "חסר קוד לביצוע הפעולה"));

	$sql 		= "select israeli_contacts.*, clubMembers.firstname as memberName, clubMembers.email as memberEmail, clubMembers.phone as memberPhone
				   from israeli_contacts
				   left join clubMembers on israeli_contacts.memberId = clubMembers.id
				   where israeli_contacts.id = $mainId";
	$result		= commonDoQuery($sql);
	$mainRow	= commonQueryFetchRow($result);

	$status			= xmlParser_getValue($xmlRequest,"status");
	if ($status != $mainRow['status'])
			commonDoQuery("update israeli_contacts set status='$status' where id = $mainId");

	$internal	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "internal")));
	if ($internal != $mainRow['internal'])
			commonDoQuery("update israeli_contacts set internal='$internal' where id = $mainId");

	$toEmail	= $mainRow['email'];

	if ($mainRow['memberId'] != 0)
	{
		$toEmail	= $mainRow['memberEmail'];
	}

	$sql 		= "select max(id) from israeli_contacts";
	$result		= commonDoQuery($sql);
	$row		= commonQueryFetchRow($result);
	$id			= $row[0] + 1;

	$title		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "title")));
	$content	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "answer")));

	$sql		= "insert into israeli_contacts (id, mainId, sender, insertTime, type, title, content)
				   values ($id, $mainId, 'crm', now(), '$mainRow[type]', '$title', '$content')";
	commonDoQuery($sql);

	sendEmail ($id, $toEmail, $title, $content, $mainRow['content']);

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* sendEmail																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function sendEmail ($id, $toEmail, $title, $content, $origContent)
{
	if ($content == "") // don't send empty emails
			return;

	$lang		= "HEB";

	$sql	 	= "select text from layoutSwitches_byLang where language = '$lang' and name = 'domain'";
	$result  	= commonDoQuery($sql);
	$row   	 	= commonQueryFetchRow($result);
	
	$domain		= stripslashes($row['text']);

	$sql	 	= "select text from layouts_byLang where layoutId = 9 and language = '$lang'";
	$result  	= commonDoQuery($sql);
	$row	 	= commonQueryFetchRow($result);

	$html		= stripslashes($row['text']);

	if ($origContent)
			$content	.= "<hr /><div style='direction:ltr;text-align:left'>Original application was:</div><!--[PNIYANO$id]--><br />$origContent:";
	else
			$content	.= "<hr /><!--[PNIYANO$id]-->";

	$html	= str_replace("#emailSubject#" ,	$title, 	$html);
	$html	= str_replace("#emailContent#" ,	$content,	$html);
	$html	= str_replace("@domain@",			$domain,  	$html);

	global $isUTF8;
	global $domainId;
	global $mailingListRow;
	$isUTF8 = 1;
	$domainId = 526;
	$mailingListRow['id'] = $id; // we're not really using it, but it has to be defined
	commonSendHtmlEmailBySMTP("info1@israeli-expert.co.il", "v7ybpHyWxH", "Israeli Experts Institute", "info@israeli-expert.co.il", $toEmail, $toEmail,
							  stripslashes($title), stripslashes($html), true);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* addContact																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function addContact_old ($xmlRequest)
{
	$type			= xmlParser_getValue($xmlRequest,"type");
	$status			= xmlParser_getValue($xmlRequest,"status");
	$email			= xmlParser_getValue($xmlRequest,"email");
	$phone			= xmlParser_getValue($xmlRequest,"phone");
	$fullname		= addslashes(xmlParser_getValue($xmlRequest,"fullname"));
	$title			= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "title")));
	$content		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "answer")));
	$internal		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "internal")));

	$sql 		= "select max(id) from israeli_contacts";
	$result		= commonDoQuery($sql);
	$row		= commonQueryFetchRow($result);
	$id			= $row[0] + 1;

	$sql		= "insert into israeli_contacts (id, mainId, sender, insertTime, type, status, title, content, email, phone, fullname, internal)
				   values ($id, 0, 'crm', now(), '$type', '$status', '$title', '$content', '$email', '$phone', '$fullname', '$internal')";
	commonDoQuery($sql);

	sendEmail ($id, $email, $title, $content, "");

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateContact																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateContact ($xmlRequest)
{
	$id	= xmlParser_getValue($xmlRequest, "id");

	if ($id == "")
		trigger_error (iconv("utf-8", "windows-1255", "חסר קוד לביצוע הפעולה"));

	$type			= xmlParser_getValue($xmlRequest,"type");
	$status			= xmlParser_getValue($xmlRequest,"status");
	$internal		= xmlParser_getValue($xmlRequest,"internal");

	$sql 	= "update israeli_contacts set type = '$type', status = '$status', internal = '$internal' where id = $id";
	commonDoQuery ($sql);

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getContactDetails																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function getContactDetails ($xmlRequest)
{
	$id	= xmlParser_getValue($xmlRequest, "id");
		
	if ($id == "")
		trigger_error (iconv("utf-8", "windows-1255", "חסר קוד לביצוע הפעולה"));

	$sql 	= "select israeli_contacts.*, clubMembers.firstname as memberName, clubMembers.email as memberEmail, clubMembers.phone as memberPhone
			   from israeli_contacts
			   left join clubMembers on israeli_contacts.memberId = clubMembers.id
			   where israeli_contacts.id = $id";
	$result	= commonDoQuery($sql);

	if (commonQueryNumRows($result) == 0)
		trigger_error (iconv("utf-8", "windows-1255", "הקוד ($id) לא קיימת במערכת. לא ניתן לבצע את העדכון"));

	$row 	= commonQueryFetchRow($result);

	$type		= $row['type'];
	$typeText	= getEnumText($type);
	$status		= $row['status'];
	$fullname	= $row['fullname'];
	$phone		= $row['phone'];
	$email		= $row['email'];
	$title		= commonCData($row['title']);
	$insertTime	= formatApplDateTime ($row['insertTime']);
	$internal	= commonValidXml($row['internal']);

	if ($row['memberId'] != 0)
	{
		$fullname	= $row['memberName'];
		$phone		= $row['memberPhone'];
		$email		= $row['memberEmail'];
	}

	$sender		= (($row['sender'] == "member") ? $fullname : "צוות האתר");
	$content	= "<tr>
					   	<td class='contactTbl_col1'>מאת:</td>
					   	<td class='contactTbl_col2'><div class='in'>$sender <div class='date'>$insertTime</div></div></td>
				   </tr>
				   <tr>
				   		<td class='contactTbl_col1'>כותרת:</td>
					   	<td class='contactTbl_col2'><div class='in'>$row[title]</div></td>
				   </tr>
				   <tr>
				   		<td class='contactTbl_col1'>תוכן:</td>
					   	<td class='contactTbl_col2'><div class='in'>$row[content]</div></td>
				   </tr>";

	$content 	= str_replace("<base href=\"https://www.israeli-expert.co.il\">", "", $content);

	$sql 	= "select israeli_contacts.* from israeli_contacts
			   where mainId = $id order by id";
	$result	= commonDoQuery($sql);

	while ($row = commonQueryFetchRow($result))
	{
		$date		= formatApplDateTime ($row['insertTime']);
		$sender		= (($row['sender'] == "member") ? $fullname : "צוות האתר");

		$content    = "<tr>
					   		<td class='contactTbl_col1'>מאת:</td>
						   	<td class='contactTbl_col2'><div class='in'>$sender <div class='date'>$date</div></div></td>
					   </tr>
					   <tr>
					   		<td class='contactTbl_col1'>כותרת:</td>
						   	<td class='contactTbl_col2'><div class='in'>$row[title]</div></td>
					   </tr>
					   <tr>
					   		<td class='contactTbl_col1'>תוכן:</td>
						   	<td class='contactTbl_col2'><div class='in'>$row[content]</div></td>
					   </tr>
					   <tr class='contactTbl_gap'><td colspan='2'><div></div></td></tr>
					   $content";
	}

	$content    = "<table class='contactTbl'>$content</table>";

	$content	= commonCData($content);

	$xmlResponse =	"<id>$id</id>
					 <type>$type</type>
					 <typeText>$typeText</typeText>
					<status>$status</status>
					 <fullname>$fullname</fullname>
					 <phone>$phone</phone>
					 <email>$email</email>
					 <insertTime>$insertTime</insertTime>
					 <content>$content</content>
					 <internal>$internal</internal>
					 <title>$title</title>
					 <answer></answer>";

	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteContact																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteContact ($xmlRequest)
{
	$id	= xmlParser_getValue($xmlRequest, "id");

	$sql 	= "delete from israeli_contacts where id = $id";
	commonDoQuery ($sql);

	$sql 	= "delete from israeli_contacts where mainId = $id";
	commonDoQuery ($sql);

	return "";
}

?>
