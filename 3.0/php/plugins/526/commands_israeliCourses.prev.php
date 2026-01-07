<?php

$tags		= array ("pageId", "courseLanguage", "name", "about", "duration", "numLessons", "courseLang", "paymentsNo1", "paymentPrice1", 
					 "paymentsNo2", "paymentPrice2", "paymentsNo3", "paymentPrice3", "paymentsNo4", "paymentPrice4");

/* ----------------------------------------------------------------------------------------------------	*/
/* getCourses																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function getCourses ($xmlRequest)
{	
	$conditions = "";

	$sortBy		= xmlParser_getValue($xmlRequest,"sortBy");

	if ($sortBy == "")
		$sortBy = "pageId";

	if ($sortBy == "pageId")
		$sortBy = "israeli_courses.pageId";

	$sortDir	= xmlParser_getValue($xmlRequest,"sortDir");
	if ($sortDir == "")
		$sortDir = "asc";

	if ($sortBy == "status")
		$sortBy = "pages_byLang.isReady";

	$isAll		= xmlParser_getValue($xmlRequest, "all");

	if ($isAll == "1")
	{
		$sortBy = "name";
		$sortDir = "asc";
	}

	$status		= xmlParser_getValue($xmlRequest, "status");
	if ($status != "")
		$conditions	.= " and pages_byLang.isReady = $status";

	$courseLanguage	 	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "courseLanguage")));
	if ($courseLanguage != "")
	{
		$conditions	.= " and israeli_courses.courseLanguage = '$courseLanguage'";
	}

	$name		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "name")));
	if ($name != "")
		$conditions .= " and israeli_courses.name like '%$name%' ";

	// get total
	$sql    	= "select israeli_courses.*, pages_byLang.isReady
		   			from (israeli_courses, pages_byLang) 
					where israeli_courses.pageId = pages_byLang.pageId
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
		$name		= commonValidXml ($row['name']);
		$duration 	= commonValidXml ($row['duration']);
		$numLessons = commonValidXml ($row['numLessons']);
		$courseLang	= commonValidXml ($row['courseLang']);
		$status		= ($row['isReady'] == 1) ? "פעיל" : "לא פעיל";

		$courseLanguage	= commonValidXml(($row['courseLanguage'] == "HEB") ? "עברית" : "אנגלית");

		$xmlResponse .=	"<item>
							<pageId>$pageId</pageId>
							<name>$name</name>
							<duration>$duration</duration>
							<numLessons>$numLessons</numLessons>
							<courseLang>$courseLang</courseLang>
							<status>$status</status>
							<courseLanguage>$courseLanguage</courseLanguage>
						 </item>";
	}

	$xmlResponse .=	"</items>"								.
					commonGetTotalXml($xmlRequest,$numRows,$total);
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* addCourse																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function addCourse ($xmlRequest)
{
	return (editCourse ($xmlRequest, "add"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateCourse																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateCourse ($xmlRequest)
{
	editCourse ($xmlRequest, "update");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* editCourse																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function editCourse ($xmlRequest, $editType)
{
	global $tags;
	global $ibosHomeDir, $userId;

	for ($i=0; $i < count($tags); $i++)
	{
		eval ("\$$tags[$i] = addslashes(commonDecode(xmlParser_getValue(\$xmlRequest,\"$tags[$i]\")));");	
	}

	if ($editType == "update")
	{
		if ($pageId == "")
			trigger_error (iconv("utf-8", "windows-1255", "חסר קוד קורס - לא ניתן לבצע את העדכון"));
	}
	else
	{
		$sql		= "select max(id) from pages";
		$result	    = commonDoQuery ($sql);
		$row	    = commonQuery_fetchRow($result);
		$pageId	    = $row[0]+1;
	}

	$vals = Array();
	for ($i=0; $i < count($tags); $i++)
	{
		eval ("array_push (\$vals,\$$tags[$i]);");
	}

	if ($editType == "add")
	{
		$sql = "insert into pages (id, type, typeText, layoutId, navParentId) values ($pageId, 'specific', 'course', 4, 1)";
		commonDoQuery ($sql);

		$sql	= "select max(id) from clubMailingLists";
		$result	= commonDoQuery($sql);
		$row	= commonQuery_fetchRow($result);

		$mlid	= $row[0] + 1;

		$sql	= "insert into clubMailingLists (id, name, senderName, senderEmail, membersOnly, defaultLanguage)
				   values ($mlid, '$name', 'המכון הישראלי לחוות דעת מומחים ובוררים', 'info@israeli-expert.co.il', 0, 'HEB')";
		commonDoQuery($sql);

		$sql = "insert into israeli_courses (" . join(",",$tags) . ", insertTime, mailingList) values ('" . join("','",$vals) . "', now(), $mlid)";
		commonDoQuery ($sql);
	}
	else
	{
		$sql = "update israeli_courses set ";
		for ($i=1; $i < count($tags); $i++)
		{
			$sql .= "$tags[$i] = '$vals[$i]',";
		}
		$sql = trim($sql, ",");

		$sql .= " where pageId = $pageId ";
		commonDoQuery ($sql);
	}

	// SEO tags
	$winTitle		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "winTitle")));
	$keywords		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "keywords")));
	$metaDesc		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "description")));
	$rewriteName	= str_replace(" ", "-", addslashes(commonDecode(xmlParser_getValue($xmlRequest, "rewriteName"))));
	$isReady		= xmlParser_getValue($xmlRequest, "status");

	$courseLanguage	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "courseLanguage")));

	if ($editType == "add")
	{
		$sql	= "insert into pages_byLang (pageId, language, title, winTitle, isReady, keywords, description, rewriteName)
				   values ('$pageId','$courseLanguage', '$name', '$winTitle', '$isReady', '$keywords', '$metaDesc', '$rewriteName')";
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
				   where pageId = $pageId and language = '$courseLanguage'";
		commonDoQuery ($sql);
	}

	// Update .htaccess with mod_rewrite rules
	$domainRow   = commonGetDomainRow ();
	fopen(commonGetDomainName($domainRow) . "/updateModRewrite.php","r");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getCourseDetails																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getCourseDetails ($xmlRequest)
{
	global $tags;
	global $usedLangs;

	$langsArray = split(",",$usedLangs);

	$pageId		= xmlParser_getValue($xmlRequest, "pageId");

	if ($pageId == "")
		trigger_error (iconv("utf-8", "windows-1255", "חסר קוד בר - לא ניתן לקבל פרטים"));

	$sql	= "select israeli_courses.*, pages_byLang.isReady, pages_byLang.winTitle, pages_byLang.keywords, pages_byLang.description,
						  pages_byLang.rewriteName
			   from (israeli_courses, pages_byLang)
			   where israeli_courses.pageId = $pageId
			   and 	 israeli_courses.pageId = pages_byLang.pageId";
	$result		= commonDoQuery ($sql);
	$row 		= commonQuery_fetchRow($result);

	$xmlResponse = "";
	for ($i=0; $i < count($tags); $i++)
	{
		eval ("\$$tags[$i]    = \$row['$tags[$i]'];");
		eval ("\$$tags[$i] 	  = commonValidXml(\$$tags[$i]);");
		eval ("\$xmlResponse .= \"<$tags[$i]>\$$tags[$i]</$tags[$i]>\";");
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
/* deleteCourse																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteCourse ($xmlRequest)
{
	$id	= xmlParser_getValue($xmlRequest, "pageId");

	$sql	= "select mailingList from israeli_courses where pageId = $id";
	$result	= commonDoQuery($sql);
	$row	= commonQuery_fetchRow($result);

	if ($row['mailingList'] != 0)
	{
		$sql	= "delete from clubMailingLists where id = $row[mailingList]";
		commonDoQuery($sql);

		$sql	= "delete from clubMailingListsMembers where mailingListId = $row[mailingList]";
		commonDoQuery($sql);
	}

	$sql 	= "delete from israeli_courses where pageId = $id";
	commonDoQuery ($sql);

	$sql 	= "delete from pages where id = $id";
	commonDoQuery ($sql);

	$sql 	= "delete from pages_byLang where pageId = $id";
	commonDoQuery ($sql);

	$sql 	= "delete from israeli_coursesDays where courseId = $id";
	commonDoQuery ($sql);

	$sql 	= "delete from israeli_coursesLessons where courseId = $id";
	commonDoQuery ($sql);

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getCourseLessons																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getCourseLessons ($xmlRequest)
{	
	$courseId		= xmlParser_getValue($xmlRequest,"courseId");

	if ($courseId == "")
		trigger_error (iconv("utf-8", "windows-1255", "חסר קוד קורס"));

	$sql	= "select * from israeli_coursesDays where courseId = $courseId order by dayNo";
	$result	= commonDoQuery ($sql);

	$xmlDays	 = "";
	$xmlResponse = "<items>";

	while ($row = commonQuery_fetchRow($result))
	{
		$id			= commonValidXml ("<strong>$row[id]</strong>");
		$pos		= commonValidXml ("<strong>יום $row[dayNo]</strong>");
		$title		= commonValidXml ("<strong>$row[title]</strong>");

		$xmlResponse .=	"<item>
							<id>$row[id]</id>
							<idShow>$id</idShow>
							<pos>$pos</pos>
							<title>$title</title>
						 </item>";

		$xmlDays 	 .=	"<item>
							<id>$row[id]</id>
							<pos>$row[dayNo]</pos>
							<title>$title</title>
						 </item>";

		$sql		= "select israeli_coursesLessons.*, israeli_lessons.title as lessonTitle, israeli_lecturers.*, israeli_lessons.duration
					   from (israeli_coursesLessons, israeli_lessons)
					   left join israeli_lecturers on israeli_lessons.lecturerId = israeli_lecturers.pageId 
					   where courseId = $courseId and dayId = $row[id] 
					   and   israeli_coursesLessons.lessonId = israeli_lessons.id 
					   order by pos";
		$inResult	= commonDoQuery($sql);

		while ($inRow = commonQuery_fetchRow($inResult))
		{
			$id			= $inRow['id'];
			$code		= $inRow['code'];
			$pos		= commonValidXml ("&nbsp;&nbsp;&nbsp;הרצאה $row[dayNo].$inRow[pos]");
			$title		= commonValidXml ("&nbsp;&nbsp;&nbsp;$inRow[lessonTitle]");
			$lecturer	= commonValidXml (trim("$inRow[title] $inRow[lastname] $inRow[firstname]"));
			$duration	= commonValidXml ($inRow['duration']);

			$xmlResponse .=	"<item>
								<id>$id</id>
								<code>$code</code>
								<idShow>$id</idShow>
								<pos>$pos</pos>
								<title>$title</title>
								<lecturerId>$lecturer</lecturerId>
								<duration>$duration</duration>
							 </item>";
		}
	}

	$xmlResponse .=	"</items>";	

	$xmlResponse .= "<days><items>$xmlDays</items></days>";
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* addCourseDay																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function addCourseDay ($xmlRequest)
{
	return (editCourseDay ($xmlRequest, "add"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateCourseDay																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateCourseDay ($xmlRequest)
{
	editCourseDay ($xmlRequest, "update");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* editCourseDay																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function editCourseDay ($xmlRequest, $editType)
{
	$id			= xmlParser_getValue($xmlRequest, "id");
	$courseId	= xmlParser_getValue($xmlRequest, "courseId");
	$title		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "title")));
	$dayNo		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "dayNo")));

	if ($courseId == "" || ($editType == "update" && $id == ""))
		trigger_error (iconv("utf-8", "windows-1255", "חסרים פרטים לביצוע עדכון יום"));

	if ($editType == "add")
	{
		$sql	= "select max(id) from israeli_coursesDays";
		$result	= commonDoQuery($sql);
		$row	= commonQuery_fetchRow($result);

		$id		= $row[0] + 1;

		$sql	= "update israeli_coursesDays set dayNo = dayNo + 1 where courseId = $courseId and dayNo >= $dayNo";
		commonDoQuery ($sql);

		$sql	= "insert into israeli_coursesDays (id, courseId, dayNo, title) values ($id, $courseId, $dayNo, '$title')";
		commonDoQuery($sql);
	}
	else
	{
		$sql		= "select * from israeli_coursesDays where id = $id";
		$result		= commonDoQuery($sql);
		$dayRow		= commonQuery_fetchRow($result);

		$currDayNo	= $dayRow['dayNo'];

		if ($currDayNo > $dayNo)
		{
			$sql	= "update israeli_coursesDays set dayNo = dayNo + 1 where courseId = $courseId and dayNo >= $dayNo and dayNo < $currDayNo";
			commonDoQuery($sql);
		}

		if ($currDayNo < $dayNo)
		{
			$sql	= "update israeli_coursesDays set dayNo = dayNo - 1 where courseId = $courseId and dayNo > $currDayNo and dayNo <= $dayNo";
			commonDoQuery($sql);
		}

		$sql	= "update israeli_coursesDays set title = '$title', dayNo = $dayNo where id = $id";
		commonDoQuery($sql);
	}

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getCourseDayDetails																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function getCourseDayDetails ($xmlRequest)
{
	$id		= xmlParser_getValue($xmlRequest, "id");

	if ($id == "")
		trigger_error (iconv("utf-8", "windows-1255", "חסר קוד יום"));

	$sql		= "select * from israeli_coursesDays where id = $id";
	$result		= commonDoQuery($sql);
	$row		= commonQuery_fetchRow($result);

	$xmlResponse	= "<id>$row[id]</id>
					   <courseId>$row[courseId]</courseId>
					   <dayNo>$row[dayNo]</dayNo>
					   <title>" . commonValidXml($row['title']) . "</title>";

	return $xmlResponse;
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteCourseDay																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteCourseDay ($xmlRequest)
{
	$id			= xmlParser_getValue($xmlRequest, "id");

	if ($id == "")
		trigger_error (iconv("utf-8", "windows-1255", "חסר קוד יום"));

	$sql		= "select * from israeli_coursesDays where id = $id";
	$result		= commonDoQuery($sql);
	$row		= commonQuery_fetchRow($result);

	$courseId	= $row['courseId'];	
	$dayNo		= $row['dayNo'];

	$sql		= "update israeli_coursesDays set dayNo = dayNo - 1 where courseId = $courseId and dayNo > $dayNo";
	commonDoQuery($sql);

	$sql 		= "delete from israeli_coursesDays where id = $id";
	commonDoQuery ($sql);

	$sql 		= "delete from israeli_coursesLessons where dayId = $id";
	commonDoQuery ($sql);

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getDayLessons																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getDayLessons ($xmlRequest)
{	
	$dayId		= xmlParser_getValue($xmlRequest,"dayId");
	$id			= xmlParser_getValue($xmlRequest,"id");

	if ($dayId == "" && $id == "")
		trigger_error (iconv("utf-8", "windows-1255", "חסרים פרטים"));

	if ($dayId == "")
	{
		$sql	= "select dayId from israeli_coursesLessons where id = $id";
		$result	= commonDoQuery($sql);
		$row	= commonQuery_fetchRow($result);

		$dayId	= $row['dayId'];
	}

	$sql		= "select * from israeli_coursesLessons, israeli_lessons 
				   where israeli_coursesLessons.lessonId = israeli_lessons.id and dayId = $dayId order by pos";
	$result		= commonDoQuery($sql);

	$xmlResponse = "<items>";

	while ($row = commonQuery_fetchRow($result))
	{
		$id			= $row['id'];
		$pos		= $row['pos'];
		$title		= $row['code'];

		if ($title != "") $title .= " - ";
		$title	   .= $row['title'];

		if ($row['meetingNumber'] != "") $title .= " - מפגש $row[meetingNumber]";
		if ($row['meetingName']   != "") $title .= " - $row[meetingName]";
		if ($row['lessonNo'] 	  != "") $title .= " - הרצאה $row[lessonNo]";
		
		$title		= commonValidXml($title);

		$xmlResponse .=	"<item>
							<id>$id</id>
							<pos>$pos</pos>
							<title>$title</title>
						 </item>";
	}

	$xmlResponse .=	"</items>";	
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* addCourseLesson																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function addCourseLesson ($xmlRequest)
{
	return (editCourseLesson ($xmlRequest, "add"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateCourseLesson																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateCourseLesson ($xmlRequest)
{
	editCourseLesson ($xmlRequest, "update");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* editCourseLesson																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function editCourseLesson ($xmlRequest, $editType)
{
	$id				= xmlParser_getValue($xmlRequest, "id");
	$courseId		= xmlParser_getValue($xmlRequest, "courseId");
	$dayId			= xmlParser_getValue($xmlRequest, "dayId");
	$lessonId		= xmlParser_getValue($xmlRequest, "lessonId");
	$pos			= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "pos")));

	if ($courseId == "" || ($editType == "update" && $id == ""))
		trigger_error (iconv("utf-8", "windows-1255", "חסרים פרטים לביצוע עדכון הרצאה"));

	if ($editType == "add")
	{
		$sql	= "select max(id) from israeli_coursesLessons";
		$result	= commonDoQuery($sql);
		$row	= commonQuery_fetchRow($result);

		$id		= $row[0] + 1;

		$sql	= "update israeli_coursesLessons set pos = pos + 1 where courseId = $courseId and dayId = $dayId and pos >= $pos";
		commonDoQuery ($sql);

		$sql	= "insert into israeli_coursesLessons (id, courseId, dayId, pos, lessonId) 
				   values ($id, $courseId, $dayId, $pos, '$lessonId')";
		commonDoQuery($sql);
	}
	else
	{
		$sql		= "select * from israeli_coursesLessons where id = $id";
		$result		= commonDoQuery($sql);
		$lessonRow	= commonQuery_fetchRow($result);

		$currPos	= $lessonRow['pos'];

		if ($currPos > $pos)
		{
			$sql	= "update israeli_coursesLessons set pos = pos + 1 
					   where courseId = $courseId and dayId = $dayId and pos >= $pos and pos < $currPos";
			commonDoQuery($sql);
		}

		if ($currPos < $pos)
		{
			$sql	= "update israeli_coursesLessons set pos = pos - 1 
					   where courseId = $courseId and dayId = $dayId and pos > $currPos and pos <= $pos";
			commonDoQuery($sql);
		}

		$sql	= "update israeli_coursesLessons set lessonId = '$lessonId', pos = $pos where id = $id";
		commonDoQuery($sql);
	}

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getCourseLessonDetails																				*/
/* ----------------------------------------------------------------------------------------------------	*/
function getCourseLessonDetails ($xmlRequest)
{
	$id		= xmlParser_getValue($xmlRequest, "id");

	if ($id == "")
		trigger_error (iconv("utf-8", "windows-1255", "חסר קוד הרצאה"));

	$sql		= "select * from israeli_coursesLessons where id = $id";
	$result		= commonDoQuery($sql);
	$row		= commonQuery_fetchRow($result);

	$xmlResponse	= "<id>$row[id]</id>
					   <courseId>$row[courseId]</courseId>
					   <dayId>$row[dayId]</dayId>
					   <pos>$row[pos]</pos>
					   <lessonId>$row[lessonId]</lessonId>
					   <title>" . commonValidXml($row['title']) . "</title>";

	return $xmlResponse;
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteCourseLesson																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteCourseLesson ($xmlRequest)
{
	$id	= xmlParser_getValue($xmlRequest, "id");

	$sql		= "select * from israeli_coursesLessons where id = $id";
	$result		= commonDoQuery($sql);
	$row		= commonQuery_fetchRow($result);

	$courseId	= $row['courseId'];	
	$dayId		= $row['dayId'];	
	$pos		= $row['pos'];

	$sql		= "update israeli_coursesLessons set pos = pos - 1 where courseId = $courseId and dayId = $dayId and pos > $pos";
	commonDoQuery($sql);

	$sql 	= "delete from israeli_coursesLessons where id = $id";
	commonDoQuery ($sql);

	return "";
}

?>
