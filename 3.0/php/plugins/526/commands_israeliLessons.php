<?php

$tags		= array ("id", "code", "title", "lecturerId", "duration", "targets", "syllabus", "meetingNumber", "meetingName", "lessonNo");

/* ----------------------------------------------------------------------------------------------------	*/
/* getLessons																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function getLessons ($xmlRequest)
{	
	$conditions = "";

	$sortBy		= xmlParser_getValue($xmlRequest,"sortBy");

	if ($sortBy == "")
		$sortBy = "id";

	$sortDir	= xmlParser_getValue($xmlRequest,"sortDir");
	if ($sortDir == "")
		$sortDir = "asc";

	$isAll		= xmlParser_getValue($xmlRequest, "all");

	if ($isAll == "1")
	{
		$sortBy = "israeli_lessons.title";
		$sortDir = "asc";
	}

	$lecturerId		= xmlParser_getValue($xmlRequest, "lecturerId");
	if ($lecturerId != "")
		$conditions	.= " and lecturerId = $lecturerId";

	// get total
	$sql    	= "select israeli_lessons.*, israeli_lessons.title as lessonTitle, israeli_lecturers.*
				   from (israeli_lessons) 
				   left join israeli_lecturers on israeli_lecturers.pageId = israeli_lessons.lecturerId 
				   where 1 $conditions order by $sortBy $sortDir";
	$result		= commonDoQuery ($sql);
	$total		= commonQuery_numRows($result);

	// get details
	if (!$isAll)
		$sql       .= commonGetLimit ($xmlRequest);

	$result	    = commonDoQuery  ($sql);
	$numRows	= commonQuery_numRows ($result);

	$xmlResponse = "<items>";

	while ($row = commonQuery_fetchRow($result))
	{
		$id				= $row['id'];
		$code			= commonValidXml ($row['code']);
		$title			= commonValidXml ($row['lessonTitle']);
		$duration 		= commonValidXml ($row['duration']);
		$lecturer		= commonValidXml (trim("$row[title] $row[lastname] $row[firstname]"));
		$title			= commonValidXml ($row['lessonTitle']);
		$meetingNumber	= commonValidXml ($row['meetingNumber']);
		$meetingName	= commonValidXml ($row['meetingName']);
		$lessonNo		= commonValidXml ($row['lessonNo']);

		$chooseText	    = commonCutText($row['lessonTitle'], 50) . " ## ";

		$chooseText	   .= $row['code'];

		if ($row['meetingNumber'] != "") $chooseText .= " - מפגש $row[meetingNumber]";
		if ($row['meetingName']   != "") $chooseText .= " - $row[meetingName]";
		if ($row['lessonNo'] 	  != "") $chooseText .= " - הרצאה $row[lessonNo]";
		
		$chooseText		= commonValidXml($chooseText);

		$xmlResponse .=	"<item>
							<id>$id</id>
							<code>$code</code>
							<title>$title</title>
							<duration>$duration</duration>
							<lecturerId>$lecturer</lecturerId>
							<meetingNumber>$meetingNumber</meetingNumber>
							<meetingName>$meetingName</meetingName>
							<lessonNo>$lessonNo</lessonNo>
							<chooseText>$chooseText</chooseText>
						 </item>";
	}

	$xmlResponse .=	"</items>"								.
					commonGetTotalXml($xmlRequest,$numRows,$total);
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* addLesson																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function addLesson ($xmlRequest)
{
	return (editLesson ($xmlRequest, "add"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateLesson																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateLesson ($xmlRequest)
{
	editLesson ($xmlRequest, "update");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* editLesson																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function editLesson ($xmlRequest, $editType)
{
	global $tags;

	for ($i=0; $i < count($tags); $i++)
	{
		eval ("\$$tags[$i] = addslashes(commonDecode(xmlParser_getValue(\$xmlRequest,\"$tags[$i]\")));");	
	}

	if ($editType == "update")
	{
		if ($id == "")
			trigger_error ("חסר קוד הרצאה - לא ניתן לבצע את העדכון");
	}
	else
	{
		$sql		= "select max(id) from israeli_lessons";
		$result	    = commonDoQuery ($sql);
		$row	    = commonQuery_fetchRow($result);
		$id		    = $row[0]+1;
	}

	$vals = Array();
	for ($i=0; $i < count($tags); $i++)
	{
		eval ("array_push (\$vals,\$$tags[$i]);");
	}

	if ($editType == "add")
	{
		$sql = "insert into israeli_lessons (" . join(",",$tags) . ", insertTime) values ('" . join("','",$vals) . "', now())";
		commonDoQuery ($sql);
	}
	else
	{
		$sql = "update israeli_lessons set ";
		for ($i=1; $i < count($tags); $i++)
		{
			$sql .= "$tags[$i] = '$vals[$i]',";
		}
		$sql = trim($sql, ",");

		$sql .= " where id = $id ";
		commonDoQuery ($sql);
	}
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getLessonDetails																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getLessonDetails ($xmlRequest)
{
	global $tags;

	$id		= xmlParser_getValue($xmlRequest, "id");

	if ($id == "")
		trigger_error ("חסר קוד הרצאה - לא ניתן לקבל פרטים");

	$sql	= "select * from israeli_lessons where id = $id";
	$result		= commonDoQuery ($sql);
	$row 		= commonQuery_fetchRow($result);

	$xmlResponse = "";
	for ($i=0; $i < count($tags); $i++)
	{
		eval ("\$$tags[$i]    = \$row['$tags[$i]'];");
		eval ("\$$tags[$i] 	  = commonValidXml(\$$tags[$i]);");
		eval ("\$xmlResponse .= \"<$tags[$i]>\$$tags[$i]</$tags[$i]>\";");
	}
			
	return $xmlResponse;
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteLesson																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteLesson ($xmlRequest)
{
	$id	= xmlParser_getValue($xmlRequest, "id");

	$sql	= "select count(*) from israeli_coursesLessons where lessonId = $id";
	$result	= commonDoQuery($sql);
	$row	= commonQuery_fetchRow($result);

	if ($row[0] != 0)
		trigger_error (iconv("utf-8", "windows-1255", "לא ניתן למחוק הרצאה שמופיעה בסילבוס של קורסים"));

	$sql 	= "delete from israeli_lessons where id = $id";
	commonDoQuery ($sql);

	return "";
}

?>
