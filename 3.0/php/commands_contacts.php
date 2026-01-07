<?php

$tags 	  = Array("id", "fullname", "age", "phone", "cellphone", "fax", "email", "address", "country", "zipcode", "title", "msg", "company", "companyRole",
				  "moreDetails", "followup", "insertTime", "fromPage", "attachfile", "referer", "firstArrivedAtPage");

/* ----------------------------------------------------------------------------------------------------	*/
/* getContacts																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function getContacts ($xmlRequest)
{
	$conditions  = "";

	$fullname 		 = commonDecode(xmlParser_getValue($xmlRequest, "fullname"));
	if ($fullname != "")
		$conditions .= " and (fullname like '%$fullname%' or email like '%$fullname%') ";
	
	$fromDate = xmlParser_getValue($xmlRequest, "fromDate");
	if ($fromDate != "")
	{
		$fromDate = formatApplToDB ("$fromDate 00:00");

		$conditions .= " and insertTime >= '$fromDate'";
	}

	// by to date
	$toDate = xmlParser_getValue($xmlRequest, "toDate");
	if ($toDate != "")
	{
		$toDate = formatApplToDB ("$toDate 23:59");

		$conditions .= " and insertTime <= '$toDate'";
	}

	$status 		 = xmlParser_getValue($xmlRequest, "status");
	if ($status != "")
		$conditions .= " and status = '$status' ";
	
	// get total
	$queryStr	 = "select count(*) from contacts where 1 $conditions";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$total	     = $row[0];

	$sortBy		= xmlParser_getValue($xmlRequest,"sortBy");
	if ($sortBy == "")
		$sortBy = "id";

	$sortDir	= xmlParser_getValue($xmlRequest,"sortDir");
	if ($sortDir == "")
		$sortDir = "desc";

	// get details
	$queryStr    = "select contacts.*
					from contacts
					where 1 $conditions
					order by $sortBy  $sortDir" . commonGetLimit ($xmlRequest);
	$result	     = commonDoQuery ($queryStr);

	$numRows    = commonQuery_numRows($result);

	$xmlResponse = "<items>";

	for ($i = 0; $i < $numRows && $i < 1000; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		$id   		= $row['id'];
		$insertTime = formatApplDateTime ($row['insertTime']);
		$fullname	= commonValidXml ($row['fullname']);
		$email		= commonValidXml ($row['email']);
		$title		= commonValidXml ($row['title']);
		$fromPage	= commonValidXml ($row['fromPage']);
		$referer	= commonValidXml (commonCutText($row['referer'],100));
		$firstArrivedAtPage	= commonValidXml ($row['firstArrivedAtPage']);

		$xmlResponse .=	"<item>
							<id>$id</id>
							<insertTime>$insertTime</insertTime>
							<fullname>$fullname</fullname>
							<email>$email</email>
							<title>$title</title>
							<fromPage>$fromPage</fromPage>
							<referer>$referer</referer>
							<firstArrivedAtPage>$firstArrivedAtPage</firstArrivedAtPage>
						</item>";
	}

	$xmlResponse .=	"</items>"												.
					commonGetTotalXml($xmlRequest,$numRows,$total);
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getContactDetails																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function getContactDetails ($xmlRequest)
{
	global $tags;

	$id		= xmlParser_getValue($xmlRequest, "id");

	if ($id == "")
		trigger_error ("חסר קוד התקשרות לביצוע הפעולה");

	$queryStr   = "select contacts.* from contacts where id=$id";
	$result		= commonDoQuery ($queryStr);

	if (commonQuery_numRows($result) == 0)
		trigger_error ("התקשרות קוד זה ($id) לא קיים במערכת. לא ניתן לבצע את הפעולה");

	$xmlResponse = "";

	$row		= commonQuery_fetchRow($result);

	  $domainRow   = commonGetDomainRow ();
	  $siteUrl     = commonGetDomainName($domainRow);
	
	for ($i=0; $i < count($tags); $i++)
	{
		eval ("\$$tags[$i] = nl2br(\$row['$tags[$i]']);");

		if ($tags[$i] == "insertTime")
			$insertTime = formatApplDateTime ($insertTime);

		if ($tags[$i] == "attachfile" && $attachfile != "")
			$attachfile = $siteUrl."/uploadedFiles/".$attachfile;

		eval ("\$$tags[$i] = commonValidXml(\$$tags[$i]);");
		eval ("\$xmlResponse .= \"<$tags[$i]>\$$tags[$i]</$tags[$i]>\";");
	}

	return $xmlResponse;
}

/* ----------------------------------------------------------------------------------------------------	*/
/* doesContactExist																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function doesContactExist ($id)
{
	$queryStr		= "select count(*) from contacts where id=$id";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$count	     = $row[0];

	return ($count > 0);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateContact																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateContact ($xmlRequest)
{
	$id 	 = xmlParser_getValue($xmlRequest, "id");
	$follwup = commonDecode(xmlParser_getValue($xmlRequest, "followup"));

	if (!doesContactExist($id))
	{
		trigger_error ("התקשרות עם קוד זה ($id) לא קיים במערכת. לא ניתן לבצע את העדכון");
	}
	
	$queryStr = "update contacts set followup = '$follwup' where id = $id ";

	commonDoQuery ($queryStr);

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteContact																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteContact ($xmlRequest)
{
	$ids = xmlParser_getValues ($xmlRequest, "ids");

	if (count($ids) == 0)
		trigger_error ("חסר קוד התקשרות לביצוע הפעולה");

	$queryStr = "delete from contacts where id in (" . join(",", $ids) . ")";
	commonDoQuery ($queryStr);

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* excelExport																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function excelExport ($xmlRequest)
{
	$conditions  = "";

	$fullname 		 = commonDecode(xmlParser_getValue($xmlRequest, "fullname"));
	if ($fullname != "")
		$conditions .= " and fullname like '%$fullname%' ";
	
	$fromDate = xmlParser_getValue($xmlRequest, "fromDate");
	if ($fromDate != "")
	{
		$fromDate = formatApplToDB ("$fromDate 00:00");

		$conditions .= " and insertTime >= '$fromDate'";
	}

	// by to date
	$toDate = xmlParser_getValue($xmlRequest, "toDate");
	if ($toDate != "")
	{
		$toDate = formatApplToDB ("$toDate 23:59");

		$conditions .= " and insertTime <= '$toDate'";
	}

	$status 		 = xmlParser_getValue($xmlRequest, "status");
	if ($status != "")
		$conditions .= " and status = '$status' ";
	
	$excel	= "תאריך ושעה\tהתקבל מדף\tשם מלא\tאימייל\tטלפון\tנייד\tפקס\tנתונים נוספים\tמקור הפנייה\tדף הגעה ראשוני\tכותרת\tתוכן\tכתובת\tחברה\n";

	$queryStr    = "select * from contacts where 1 $conditions order by insertTime desc";
	$result	     = commonDoQuery ($queryStr);

	while ($row = commonQuery_fetchRow($result))
	{
		$insertTime 	= formatApplDateTime ($row['insertTime']);
		$fromPage 		= commonPrepareToFile ($row['fromPage']);
		$fullname 		= commonPrepareToFile(stripslashes($row['fullname']));
		$email  		= commonPrepareToFile(stripslashes($row['email']));
		$phone  		= commonPrepareToFile(stripslashes($row['phone']));
		$cellphone  	= commonPrepareToFile(stripslashes($row['cellphone']));
		$moreDetails	= commonPrepareToFile(stripslashes($row['moreDetails']));
		$fax  			= commonPrepareToFile(stripslashes($row['fax']));
		$referer 		= commonPrepareToFile(stripslashes($row['referer']));
		$firstArrivedAtPage 	= commonPrepareToFile(stripslashes($row['firstArrivedAtPage']));
		$title 			= commonPrepareToFile(stripslashes($row['title']));
		$msg 			= commonPrepareToFile(stripslashes($row['msg']));
		$msg			= str_replace("\r\n", " ", $msg);
		$msg			= str_replace("\n", " ", $msg); 
		$msg			= str_replace(",", " ", $msg);
		$address 		= commonPrepareToFile(stripslashes($row['address']));
		$company 		= commonPrepareToFile(stripslashes($row['company']));

		$excel .= "$insertTime\t$fromPage\t$fullname\t$email\t$phone\t$cellphone\t$fax\t$moreDetails\t$referer\t$firstArrivedAtPage\t$title\t\"$msg\"\t$address\t$company\n";
	}

	return (commonDoExcel($excel));
}

?>
