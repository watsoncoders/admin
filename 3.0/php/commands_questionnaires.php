<?php

include "picsTools.php";

$tags 	  = Array("startText", "endText", "pageIdAfterSubmit", "section1Title", "section1Text", "section1StartFrom", "section2Title", 
				  "section2Text", "section2StartFrom", "section3Title", "section3Text", "section3StartFrom", 
				  "section4Title", "section4Text", "section4StartFrom", "section5Title", "section5Text", "section5StartFrom", 
				  "publishDate", "closeDate", "blockDuplicateVotes", "blockDuplicatePageId", "isOneByOne");

$qtags	  = Array("questionnaireId", "pos", "type", "isMandatory", "optionsDisplay", "question");

$atags	  = Array("questionId", "pos", "type", "answer", "beforeTextAnswer", "hideOnSelect");

/* ----------------------------------------------------------------------------------------------------	*/
/* getQuestionnaires																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function getQuestionnaires ($xmlRequest)
{	
	global $usedLangs;
	$langsArray = explode(",",$usedLangs);

	// get total
	$queryStr	 = "select count(*) from questionnaires";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$total	     = $row[0];

	// get details
	$queryStr = "select pages_byLang.pageId, pages_byLang.title
				 from (questionnaires, pages_byLang)
				 where questionnaires.pageId = pages_byLang.pageId and pages_byLang.language = '$langsArray[0]' 
				 order by questionnaires.pageId desc " . commonGetLimit ($xmlRequest);
	$result	     = commonDoQuery ($queryStr);

	$numRows    = commonQuery_numRows($result);

	$xmlResponse = "<items>";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		$id   		  	= $row['pageId'];
		$title			= $row['title'];

		$title		  	= commonValidXml($title);

		$xmlResponse .=	"<item>
							 <questionnaireId>$id</questionnaireId>
				 			 <title>$title</title>
						 </item>";
	}

	$xmlResponse .=	"</items>" .
					commonGetTotalXml($xmlRequest,$numRows,$total);
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getQuestionnaireDetails																				*/
/* ----------------------------------------------------------------------------------------------------	*/
function getQuestionnaireDetails ($xmlRequest)
{
	global $tags;

	$id		= xmlParser_getValue($xmlRequest, "questionnaireId");

	if ($id == "")
		trigger_error ("חסר קוד שאלון לביצוע הפעולה");


	$queryStr = "select * from questionnaires, pages, pages_byLang
				 where questionnaires.pageId = pages.id
				 and   pages.id = pages_byLang.pageId
				 and   questionnaires.pageId = $id";
	$result   = commonDoQuery ($queryStr);

	if (commonQuery_numRows($result) == 0)
		trigger_error ("שאלון עם קוד זה ($id) לא קיים במערכת. לא ניתן לבצע את הפעולה");

	$row	= commonQuery_fetchRow($result);

	$xmlResponse = "";

	$row['section1StartFrom'] = (($row['section1StartFrom'] == 0) ? "" : $row['section1StartFrom']);
	$row['section2StartFrom'] = (($row['section2StartFrom'] == 0) ? "" : $row['section2StartFrom']);
	$row['section3StartFrom'] = (($row['section3StartFrom'] == 0) ? "" : $row['section3StartFrom']);
	$row['section4StartFrom'] = (($row['section4StartFrom'] == 0) ? "" : $row['section4StartFrom']);
	$row['section5StartFrom'] = (($row['section5StartFrom'] == 0) ? "" : $row['section5StartFrom']);

	for ($i=0; $i < count($tags); $i++)
	{
		eval ("\$$tags[$i] = \$row['$tags[$i]'];");
	}

	$publishDate = formatApplDate($publishDate);
	$closeDate   = formatApplDate($closeDate);

	for ($i=0; $i < count($tags); $i++)
	{
		eval ("\$$tags[$i] = commonValidXml(\$$tags[$i]);");
		eval ("\$xmlResponse .= \"<$tags[$i]>\$$tags[$i]</$tags[$i]>\";");
	}
			
	$xmlResponse .= "<questionnaireId>$id</questionnaireId>
					 <title>" 		. commonValidXml($row['title']) 		. "</title>
					 <winTitle>" 	. commonValidXml($row['winTitle']) 		. "</winTitle>
					 <keywords>" 	. commonValidXml($row['keywords']) 		. "</keywords>
					 <description>" . commonValidXml($row['description']) 	. "</description>
					 <rewriteName>" . commonValidXml($row['rewriteName']) 	. "</rewriteName>
					 <layoutId>" 	. commonValidXml($row['layoutId']) 		. "</layoutId>
					 <isReady>" 	. commonValidXml($row['isReady']) 		. "</isReady>";

	$domainRow   = commonGetDomainRow ();
	$siteUrl     = commonGetDomainName($domainRow) . "/index2.php";


	$xmlResponse .= "<siteUrl>$siteUrl</siteUrl>";

	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* addQuestionnaire																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function addQuestionnaire ($xmlRequest)
{
	return (editQuestionnaire ($xmlRequest, "add"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* doesQuestionnaireExist																				*/
/* ----------------------------------------------------------------------------------------------------	*/
function doesQuestionnaireExist ($id)
{
	$queryStr		= "select count(*) from pages where id = $id";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$count	     = $row[0];

	return ($count > 0);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getQuestionnaireNextId																				*/
/* ----------------------------------------------------------------------------------------------------	*/
function getQuestionnaireNextId ()
{
	$queryStr	= "select max(id) from pages";
	$result		= commonDoQuery ($queryStr);
	$row		= commonQuery_fetchRow ($result);
	$id 		= $row[0] + 1;
	
	return $id;
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateQuestionnaire																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateQuestionnaire ($xmlRequest)
{
	editQuestionnaire ($xmlRequest, "update");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* editQuestionnaire																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function editQuestionnaire ($xmlRequest, $editType)
{
	global $tags;

	$language = "HEB";	// TBD - select lang from client side

	for ($i=0; $i < count($tags); $i++)
	{
		eval ("\$$tags[$i] = addslashes(commonDecode(xmlParser_getValue(\$xmlRequest,\"$tags[$i]\")));");	
	}

	$title			= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "title")));
	$winTitle		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "winTitle")));
	$keywords		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "keywords")));
	$description	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "description")));
	$rewriteName	= str_replace(" ", "-", addslashes(commonDecode(xmlParser_getValue($xmlRequest, "rewriteName"))));
	$layoutId		= xmlParser_getValue($xmlRequest, "layoutId");
	$isReady		= xmlParser_getValue($xmlRequest, "isReady");

	if ($editType == "update")
	{
		$id = xmlParser_getValue($xmlRequest, "questionnaireId");

		if (!doesQuestionnaireExist($id))
		{
			trigger_error ("שאלון עם קוד זה ($id) לא קיים במערכת. לא ניתן לבצע את העדכון");
		}
	}
	else
	{
		$id = getQuestionnaireNextId ();
	}

	if ($publishDate != "") $publishDate = formatApplToDB ($publishDate . " 00:00");
	if ($closeDate   != "") $closeDate   = formatApplToDB ($closeDate   . " 00:00");

	$vals = Array();

	for ($i=0; $i < count($tags); $i++)
	{
		eval ("array_push (\$vals,\$$tags[$i]);");
	}
	
	if ($editType == "add")
	{
		$ibosUserId = commonGetIbosUserId ();

		// pages table
		$queryStr = "insert into pages (id, type, ibosUserId, layoutId) values ('$id', 'questionnaire', '$ibosUserId', '$layoutId')";
		commonDoQuery ($queryStr);

		// pages_byLang table
		$queryStr = "insert into pages_byLang (pageId, language, title, winTitle, isReady, keywords, description, rewriteName, updated)
				     values ('$id','$language', '$title', '$winTitle', '$isReady', '$keywords', '$description', '$rewriteName', now())";
		commonDoQuery ($queryStr);

		// questionnaires table
		$queryStr = "insert into questionnaires (pageId, " . join(",",$tags) . ") values ($id, '" . join("','",$vals) . "')";
		commonDoQuery ($queryStr);
	}
	else // update
	{
		// pages table
		$queryStr = "update pages set layoutId	 = '$layoutId' where id=$id";
		commonDoQuery ($queryStr);

		// pages_byLang table
		$queryStr = "update pages_byLang set title		 = '$title',
											 winTitle	 = '$winTitle',
											 isReady	 = '$isReady',
											 keywords	 = '$keywords',
											 description = '$description',
											 rewriteName = '$rewriteName',
											 updated	 = now()
					 where pageId = $id and language = '$language'";
		commonDoQuery ($queryStr);

		// questionnaires table
		$queryStr = "update questionnaires set ";

		for ($i=0; $i < count($tags); $i++)
		{
			$queryStr .= "$tags[$i] = '$vals[$i]',";
		}

		$queryStr = trim($queryStr, ",");

		$queryStr .= " where pageId = $id ";
		commonDoQuery ($queryStr);

	}

	# update .htaccess
	$domainRow  = commonGetDomainRow ();
	$domainName = commonGetDomainName ($domainRow);
	
	fopen("$domainName/updateModRewrite.php","r");

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteQuestionnaire																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteQuestionnaire ($xmlRequest)
{
	$id = xmlParser_getValue($xmlRequest, "questionnaireId");

	if ($id == "")
		trigger_error ("חסר קוד שאלון לביצוע הפעולה");

	$queryStr = "delete from questionnaires where pageId = $id";
	commonDoQuery ($queryStr);

	$queryStr = "delete from pages where id = $id";
	commonDoQuery ($queryStr);

	$queryStr = "delete from pages_byLang where pageId = $id";
	commonDoQuery ($queryStr);

	$queryStr = "delete from questionnaireAnswers where questionId in (select questionId from questionnaireQuestions where questionnaireId = $id)";
	commonDoQuery ($queryStr);

	$queryStr = "delete from questionnaireQuestions where questionnaireId = $id";
	commonDoQuery ($queryStr);

	$queryStr = "delete from questionnaireParticipants where questionnaireId = $id";
	commonDoQuery ($queryStr);

	$queryStr = "delete from questionnaireResults where questionnaireId = $id"; 
	commonDoQuery ($queryStr);

	return "";	
}

/* ----------------------------------------------------------------------------------------------------	*/
/* resetQuestionnaireResults																			*/
/* ----------------------------------------------------------------------------------------------------	*/
function resetQuestionnaireResults ($xmlRequest)
{
	$id = xmlParser_getValue($xmlRequest, "questionnaireId");

	if ($id == "")
		trigger_error ("חסר קוד שאלון לביצוע הפעולה");

	$queryStr = "delete from questionnaireParticipants where questionnaireId = $id";
	commonDoQuery ($queryStr);

	$queryStr = "delete from questionnaireResults where questionnaireId = $id"; 
	commonDoQuery ($queryStr);

	return "";	
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getQuestionnaireQuestions																			*/
/* ----------------------------------------------------------------------------------------------------	*/
function getQuestionnaireQuestions ($xmlRequest)
{
	$id		= xmlParser_getValue($xmlRequest, "questionnaireId");

	if ($id == "")
		return "<items></items>";

	$queryStr	= "select count(*) from questionnaireQuestions where questionnaireId = $id order by pos";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$total	     = $row[0];

	$queryStr	= "select questionnaireQuestions.*, count(questionnaireAnswers.id) as countAnswers 
			   	   from questionnaireQuestions 
				   left join questionnaireAnswers on questionnaireAnswers.questionId = questionnaireQuestions.id
				   where questionnaireId = $id 
				   group by questionnaireQuestions.id
				   order by pos";
	$queryStr  .= " " . commonGetLimit ($xmlRequest);
	$result		= commonDoQuery($queryStr);

	$numRows    = commonQuery_numRows($result);

	$xmlResponse = "<items>";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		$id   		  	= $row['id'];
		$pos  		  	= $row['pos'];
		$question	  	= commonValidXml(commonCutText(strip_tags($row['question']), 100));
		$type			= $row['type'];
		$countAnswers	= $row['countAnswers'];

		$typeText		= "";
		switch ($type)
		{
			case "radio"		:	$typeText = commonPhpEncode("שאלת ברירה");	
									break;					

			case "checkbox"		:	$typeText = commonPhpEncode("שאלת בחירה מרובה");	
									break;					

			case "openShort"	:	$typeText = commonPhpEncode("שאלה פתוחה קצרה");	
									break;					

			case "openDetailed"	:	$typeText = commonPhpEncode("שאלה פתוחה מפורטת");	
									break;					
		}

		$countAnswers = (($countAnswers == 0) ? "" : $countAnswers);

		$xmlResponse .=	"<item>
							 <id>$id</id>
				 			 <pos>$pos</pos>
							 <question>$question</question>
							 <type>$type</type>
							 <typeText>$typeText</typeText>
							 <countAnswers>$countAnswers</countAnswers>
						 </item>";
	}

	$xmlResponse .=	"</items>" .
					commonGetTotalXml($xmlRequest,$numRows,$total);
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getQuestionDetails																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function getQuestionDetails ($xmlRequest)
{
	global $qtags;

	$id		= xmlParser_getValue($xmlRequest, "id");

	if ($id == "")
		trigger_error ("חסר קוד שאלה לביצוע הפעולה");


	$queryStr = "select * from questionnaireQuestions where id = $id";
	$result   = commonDoQuery ($queryStr);

	if (commonQuery_numRows($result) == 0)
		trigger_error ("שאלה עם קוד זה ($id) לא קיימת במערכת. לא ניתן לבצע את הפעולה");

	$row	= commonQuery_fetchRow($result);

	if ($row['type'] != "radio" && $row['type'] != "checkbox")
		$row['optionsDisplay'] = "";

	$xmlResponse = "";

	$domainRow   = commonGetDomainRow ();
	$domainUrl   = commonGetDomainName($domainRow);

	for ($i=0; $i < count($qtags); $i++)
	{
		eval ("\$$qtags[$i] = \$row['$qtags[$i]'];");
	}

	for ($i=0; $i < count($qtags); $i++)
	{
		eval ("\$$qtags[$i] = commonValidXml(\$$qtags[$i]);");
		eval ("\$xmlResponse .= \"<$qtags[$i]>\$$qtags[$i]</$qtags[$i]>\";");
	}
			
	$xmlResponse .= "<id>$id</id>";

	$sourceFile	   	= commonValidXml($row['picSourceFile']);
	
	$fullFileName  	= urlencode($row['picFile']);
	$fullFileName  	= commonValidXml("$domainUrl/questionnairesFiles/$fullFileName");
	
	$pressText     	= commonPhpEncode("לחץ כאן");

	$show	 		= "";
	$delete	 		= "";

	if ($row['picFile'] != "")
	{
		$show 	= $pressText;
		$delete	= $pressText;
	}

	$xmlResponse .= "<picSource>$sourceFile</picSource>
					 <formSourcePic>$sourceFile</formSourcePic>
					 <fullPicName>$fullFileName</fullPicName>
					 <showPic>$show</showPic>
					 <deletePic>$delete</deletePic>
					 <dimensionId>0</dimensionId>";

	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteQuestion																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteQuestion ($xmlRequest)
{
	$id = xmlParser_getValue($xmlRequest, "id");

	if ($id == "")
		trigger_error ("חסר קוד שאלה לביצוע הפעולה");

	// get curr pos
	$queryStr    = "select questionnaireId, pos from questionnaireQuestions where id=$id";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$questId	 = $row['questionnaireId'];
	$pos		 = $row['pos'];

	// update poses
	$queryStr 	 = "update questionnaireQuestions set pos = pos-1 where questionnaireId = $questId and pos > $pos";
	commonDoQuery ($queryStr);

	$queryStr = "delete from questionnaireQuestions where id = $id";
	commonDoQuery ($queryStr);

	$queryStr = "delete from questionnaireAnswers where questionId = $id";
	commonDoQuery ($queryStr);

	return "";	
}

/* ----------------------------------------------------------------------------------------------------	*/
/* addQuestion																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function addQuestion ($xmlRequest)
{
	return (editQuestion ($xmlRequest, "add"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* doesQuestionExist																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function doesQuestionExist ($id)
{
	$queryStr		= "select count(*) from questionnaireQuestions where id = $id";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$count	     = $row[0];

	return ($count > 0);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getQuestionNextId																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function getQuestionNextId ()
{
	$queryStr	= "select max(id) from questionnaireQuestions";
	$result		= commonDoQuery ($queryStr);
	$row		= commonQuery_fetchRow ($result);
	$id 		= $row[0] + 1;
	
	return $id;
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateQuestion																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateQuestion ($xmlRequest)
{
	editQuestion ($xmlRequest, "update");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* editQuestion																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function editQuestion ($xmlRequest, $editType)
{
	global $qtags;
	global $userId;
	global $ibosHomeDir;

	for ($i=0; $i < count($qtags); $i++)
	{
		eval ("\$$qtags[$i] = addslashes(commonDecode(xmlParser_getValue(\$xmlRequest,\"$qtags[$i]\")));");	
	}

	if ($editType == "update")
	{
		$id = xmlParser_getValue($xmlRequest, "id");

		if (!doesQuestionExist($id))
		{
			trigger_error ("שאלה עם קוד זה ($id) לא קיימת במערכת. לא ניתן לבצע את העדכון");
		}
	}
	else
	{
		$id = getQuestionNextId ();
	}

	$domainRow  = commonGetDomainRow ();
	$domainName = commonGetDomainName ($domainRow);
	commonConnectToUserDB($domainRow);

	// question pic
	$picSource 		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "picSource")));	
	$picDeleted		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "picDeleted")));	

	$picDeleted		= ($picDeleted == "1");
	$picLoaded  	= false;

	$picFile 		= "";
	$suffix 		= "";

	if ($picSource != "")
	{
		$picLoaded 	= true;
		$suffix		= commonFileSuffix ($picSource);
		$picFile 	= "question_${id}_size0$suffix";

		$dimensionId	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "dimensionId")));

		list ($picWidth, $picHeight, $bgColor, $forceSize) = commonGetDimensionDetails ($dimensionId);
	}

	if ($editType == "add")
	{
		$queryStr = "update questionnaireQuestions set pos = pos+1 where questionnaireId = $questionnaireId and pos >= $pos";
		commonDoQuery ($queryStr);

		$vals = Array();

		for ($i=0; $i < count($qtags); $i++)
		{
			eval ("array_push (\$vals,\$$qtags[$i]);");
		}
	
		$queryStr = "insert into questionnaireQuestions (id, " . join(",",$qtags) . ", picFile, picSourceFile) 
					 values ($id, '" . join("','",$vals) . "', '$picFile', '$picSource')";
		commonDoQuery ($queryStr);
	}
	else // update
	{
		// get curr question pos
		$queryStr    = "select pos from questionnaireQuestions where id=$id";
		$result	     = commonDoQuery ($queryStr);
		$row	     = commonQuery_fetchRow($result);
		$currPos	 = $row[0];

		if ($currPos > $pos)
		{
			$queryStr = "update questionnaireQuestions set pos = pos+1 where questionnaireId = $questionnaireId and pos >= $pos and pos < $currPos";
			commonDoQuery ($queryStr);
		}

		if ($currPos < $pos)
		{
			$queryStr = "update questionnaireQuestions set pos = pos-1 where questionnaireId = $questionnaireId and pos > $currPos and pos < $pos";
			commonDoQuery ($queryStr);

			$pos--;
		}

		$vals = Array();

		for ($i=0; $i < count($qtags); $i++)
		{
			eval ("array_push (\$vals,\$$qtags[$i]);");
		}
	
		$queryStr = "update questionnaireQuestions set ";

		for ($i=0; $i < count($qtags); $i++)
		{
			$queryStr .= "$qtags[$i] = '$vals[$i]',";
		}

		$queryStr = trim($queryStr, ",");

		if ($picLoaded)
		{
			$queryStr .= ", picFile  			= '$picFile',
							picSourceFile		= '$picSource' ";
		}
		else if ($picDeleted)
		{
			$queryStr .= ", picFile 			= '',
							picSourceFile		= '' ";
		}

		$queryStr .= " where id = $id ";
		commonDoQuery ($queryStr);
	}

	// handle file
	$filePath = "$ibosHomeDir/html/SWFUpload/files/$userId/";

	$connId = commonFtpConnect($domainRow); 

	ftp_chdir($connId, "questionnairesFiles/");

	if ($picLoaded)
	{
		commonFtpDelete ($connId, $picFile);

		$upload = ftp_put($connId, $picFile, "$filePath/$picSource", FTP_BINARY);

		$resizedFileName = "question_${id}_size1.jpg";

		if ($picWidth == 0 && $picHeight == 0)
		{
			$upload = ftp_put($connId, $resizedFileName, "$filePath/$picSource", FTP_BINARY);
		}
		else
		{
			if ($forceSize == "1")
				picsToolsForceResize("$filePath/$picSource", $suffix, $picWidth, $picHeight, "/../../tmp/$resizedFileName", $bgColor);
			else
				picsToolsResize("$filePath/$picSource", $suffix, $picWidth, $picHeight, "/../../tmp/$resizedFileName", $bgColor);
		
			$upload = ftp_put($connId, $resizedFileName, "/../../tmp/$resizedFileName", FTP_BINARY);
		}
	}
	else if ($picDeleted)
	{
		// find old file name !!! TBD
	}

 	// delete old files
	commonDeleteOldFiles ($filePath, 3600);	// 1 hour

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getQuestionAnswers																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function getQuestionAnswers ($xmlRequest)
{
	$id		= xmlParser_getValue($xmlRequest, "id");

	if ($id == "")
		return "<items></items>";

	$queryStr	= "select count(*) from questionnaireAnswers where questionId = $id order by pos";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$total	     = $row[0];

	$queryStr	= "select * from questionnaireAnswers where questionId = $id order by pos";
	$result		= commonDoQuery($queryStr);

	$numRows    = commonQuery_numRows($result);

	$xmlResponse = "<items>";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		$id   		  		= $row['id'];
		$pos  		  		= $row['pos'];
		$answer	  			= commonValidXml($row['answer']);
		$beforeTextAnswer	= commonValidXml($row['beforeTextAnswer']);
		$hideOnSelect		= $row['hideOnSelect'];
		$type				= $row['type'];

		$typeText		= "";
		switch ($type)
		{
			case "choose"			:	$typeText = commonPhpEncode("בחירה");	
										break;					

			case "chooseWithText"	:	$typeText = commonPhpEncode("בחירה עם פירוט");	
										break;					
		}

		$xmlResponse .=	"<item>
							 <id>$id</id>
				 			 <pos>$pos</pos>
							 <answer>$answer</answer>
							 <beforeTextAnswer>$beforeTextAnswer</beforeTextAnswer>
							 <type>$type</type>
							 <typeText>$typeText</typeText>
							 <hideOnSelect>$hideOnSelect</hideOnSelect>
						 </item>";
	}

	$xmlResponse .=	"</items>
					 <totals>
					 	<totalText>$total</totalText>
					 </totals>";
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getAnswerDetails																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getAnswerDetails ($xmlRequest)
{
	global $atags;

	$id		= xmlParser_getValue($xmlRequest, "id");

	if ($id == "")
		trigger_error ("חסר קוד תשובה לביצוע הפעולה");

	$queryStr = "select * from questionnaireAnswers where id = $id";
	$result   = commonDoQuery ($queryStr);

	if (commonQuery_numRows($result) == 0)
		trigger_error ("תשובה עם קוד זה ($id) לא קיימת במערכת. לא ניתן לבצע את הפעולה");

	$row	= commonQuery_fetchRow($result);

	$xmlResponse = "";

	$domainRow   = commonGetDomainRow ();
	$domainUrl   = commonGetDomainName($domainRow);

	for ($i=0; $i < count($atags); $i++)
	{
		eval ("\$$atags[$i] = \$row['$atags[$i]'];");
	}

	for ($i=0; $i < count($atags); $i++)
	{
		eval ("\$$atags[$i] = commonValidXml(\$$atags[$i]);");
		eval ("\$xmlResponse .= \"<$atags[$i]>\$$atags[$i]</$atags[$i]>\";");
	}
			
	$xmlResponse .= "<id>$id</id>";

	$sourceFile	   	= commonValidXml($row['picSourceFile']);
	
	$fullFileName  	= urlencode($row['picFile']);
	$fullFileName  	= commonValidXml("$domainUrl/questionnairesFiles/$fullFileName");
	
	$pressText     	= commonPhpEncode("לחץ כאן");

	$show	 		= "";
	$delete	 		= "";

	if ($row['picFile'] != "")
	{
		$show 	= $pressText;
		$delete	= $pressText;
	}

	$xmlResponse .= "<picSource>$sourceFile</picSource>
					 <formSourcePic>$sourceFile</formSourcePic>
					 <fullPicName>$fullFileName</fullPicName>
					 <showPic>$show</showPic>
					 <deletePic>$delete</deletePic>
					 <dimensionId>0</dimensionId>";

	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteAnswer																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteAnswer ($xmlRequest)
{
	$id = xmlParser_getValue($xmlRequest, "id");

	if ($id == "")
		trigger_error ("חסר קוד שתובה לביצוע הפעולה");

	// get curr pos
	$queryStr    = "select questionId, pos from questionnaireAnswers where id=$id";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$questionId  = $row['questionId'];
	$pos		 = $row['pos'];

	// update poses
	$queryStr 	 = "update questionnaireAnswers set pos = pos-1 where questionId = $questionId and pos > $pos";
	commonDoQuery ($queryStr);

	$queryStr = "delete from questionnaireAnswers where id = $id";
	commonDoQuery ($queryStr);

	return "";	
}

/* ----------------------------------------------------------------------------------------------------	*/
/* addAnswer																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function addAnswer ($xmlRequest)
{
	return (editAnswer ($xmlRequest, "add"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* doesAnswerExist																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function doesAnswerExist ($id)
{
	$queryStr		= "select count(*) from questionnaireAnswers where id = $id";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$count	     = $row[0];

	return ($count > 0);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getAnswerNextId																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getAnswerNextId ()
{
	$queryStr	= "select max(id) from questionnaireAnswers";
	$result		= commonDoQuery ($queryStr);
	$row		= commonQuery_fetchRow ($result);
	$id 		= $row[0] + 1;
	
	return $id;
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateAnswer																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateAnswer ($xmlRequest)
{
	editAnswer ($xmlRequest, "update");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* editAnswer																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function editAnswer ($xmlRequest, $editType)
{
	global $atags;
	global $userId;
	global $ibosHomeDir;

	for ($i=0; $i < count($atags); $i++)
	{
		eval ("\$$atags[$i] = addslashes(commonDecode(xmlParser_getValue(\$xmlRequest,\"$atags[$i]\")));");	
	}

	if ($editType == "update")
	{
		$id = xmlParser_getValue($xmlRequest, "id");

		if (!doesAnswerExist($id))
		{
			trigger_error ("שתובה עם קוד זה ($id) לא קיימת במערכת. לא ניתן לבצע את העדכון");
		}
	}
	else
	{
		$id = getAnswerNextId ();
	}

	$domainRow  = commonGetDomainRow ();
	$domainName = commonGetDomainName ($domainRow);
	commonConnectToUserDB($domainRow);

	// answer pic
	$picSource 		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "picSource")));	
	$picDeleted		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "picDeleted")));	

	$picDeleted		= ($picDeleted == "1");
	$picLoaded  	= false;

	$picFile 		= "";
	$suffix 		= "";

	if ($picSource != "")
	{
		$picLoaded 	= true;
		$suffix		= commonFileSuffix ($picSource);
		$picFile 	= "answer_${id}_size0$suffix";

		$dimensionId	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "dimensionId")));

		list ($picWidth, $picHeight, $bgColor, $forceSize) = commonGetDimensionDetails ($dimensionId);
	}

	if ($editType == "add")
	{
		$queryStr = "update questionnaireAnswers set pos = pos+1 where questionId = $questionId and pos >= $pos";
		commonDoQuery ($queryStr);

		$vals = Array();

		for ($i=0; $i < count($atags); $i++)
		{
			eval ("array_push (\$vals,\$$atags[$i]);");
		}
	
		$queryStr = "insert into questionnaireAnswers (id, " . join(",",$atags) . ", picFile, picSourceFile) 
					 values ($id, '" . join("','",$vals) . "', '$picFile', '$picSource')";
		commonDoQuery ($queryStr);
	}
	else // update
	{
		// get curr question pos
		$queryStr    = "select pos from questionnaireAnswers where id=$id";
		$result	     = commonDoQuery ($queryStr);
		$row	     = commonQuery_fetchRow($result);
		$currPos	 = $row[0];

		if ($currPos > $pos)
		{
			$queryStr = "update questionnaireAnswers set pos = pos+1 where questionId = $questionId and pos >= $pos and pos < $currPos";
			commonDoQuery ($queryStr);
		}

		if ($currPos < $pos)
		{
			$queryStr = "update questionnaireAnswers set pos = pos-1 where questionId = $questionId and pos > $currPos and pos < $pos";
			commonDoQuery ($queryStr);

			$pos--;
		}

		$vals = Array();

		for ($i=0; $i < count($atags); $i++)
		{
			eval ("array_push (\$vals,\$$atags[$i]);");
		}
	
		$queryStr = "update questionnaireAnswers set ";

		for ($i=0; $i < count($atags); $i++)
		{
			$queryStr .= "$atags[$i] = '$vals[$i]',";
		}

		$queryStr = trim($queryStr, ",");

		if ($picLoaded)
		{
			$queryStr .= ", picFile  			= '$picFile',
							picSourceFile		= '$picSource' ";
		}
		else if ($picDeleted)
		{
			$queryStr .= ", picFile 			= '',
							picSourceFile		= '' ";
		}

		$queryStr .= " where id = $id ";
		commonDoQuery ($queryStr);
	}

	// handle file
	$filePath = "$ibosHomeDir/html/SWFUpload/files/$userId/";

	$connId = commonFtpConnect($domainRow); 

	ftp_chdir($connId, "questionnairesFiles/");

	if ($picLoaded)
	{
		commonFtpDelete ($connId, $picFile);

		$upload = ftp_put($connId, $picFile, "$filePath/$picSource", FTP_BINARY);

		$resizedFileName = "answer_${id}_size1.jpg";

		if ($picWidth == 0 && $picHeight == 0)
		{
			$upload = ftp_put($connId, $resizedFileName, "$filePath/$picSource", FTP_BINARY);
		}
		else
		{
			if ($forceSize == "1")
				picsToolsForceResize("$filePath/$picSource", $suffix, $picWidth, $picHeight, "/../../tmp/$resizedFileName", $bgColor);
			else
				picsToolsResize("$filePath/$picSource", $suffix, $picWidth, $picHeight, "/../../tmp/$resizedFileName", $bgColor);
		
			$upload = ftp_put($connId, $resizedFileName, "/../../tmp/$resizedFileName", FTP_BINARY);
		}
	}
	else if ($picDeleted)
	{
		// find old file name !!! TBD
	}

 	// delete old files
	commonDeleteOldFiles ($filePath, 3600);	// 1 hour

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* excelReport																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function excelReport ($xmlRequest)
{
	$id		= xmlParser_getValue($xmlRequest, "questionnaireId");

	if ($id == "")
		trigger_error ("חסר קוד שאלון לביצוע הפעולה");

	$queryStr	= "select title from pages_byLang where pageId = $id";
	$result		= commonDoQuery($queryStr);
	$qRow		= commonQuery_fetchRow($result);

	$excelTitle = "תוצאות שאלון " . commonPrepareToFile(stripslashes($qRow['title']));

	$now	= commonPrepareToFile(commonPhpEncode("תאריך:") . date("d/m/Y H:i"));

    $excel = "<?xml version='1.0' encoding='ISO-8859-8' ?>												
				<Workbook xmlns=\"urn:schemas-microsoft-com:office:spreadsheet\"
				 	      xmlns:o=\"urn:schemas-microsoft-com:office:office\"
						  xmlns:x=\"urn:schemas-microsoft-com:office:excel\"
						  xmlns:ss=\"urn:schemas-microsoft-com:office:spreadsheet\"
						  xmlns:html=\"http://www.w3.org/TR/REC-html40\">
					<OfficeDocumentSettings xmlns=\"urn:schemas-microsoft-com:office:office\">
						<Colors>
							<Color>
								<Index>39</Index>
   								<RGB>#E3E3E3</RGB>
							</Color>
						</Colors>
					</OfficeDocumentSettings>
					<ExcelWorkbook xmlns=\"urn:schemas-microsoft-com:office:excel\">
						<WindowHeight>7860</WindowHeight>
						<WindowWidth>14040</WindowWidth>
			  			<WindowTopX>0</WindowTopX>
			  			<WindowTopY>1905</WindowTopY>
			  			<ProtectStructure>False</ProtectStructure>
			  			<ProtectWindows>False</ProtectWindows>
			 		</ExcelWorkbook>
 					<Styles>
  						<Style ss:ID=\"sTitle\">
							<Alignment ss:Horizontal=\"Right\" ss:Vertical=\"Bottom\"/>
							<Font x:Family=\"Swiss\" ss:Color=\"#FF5F00\" ss:Size=\"16\" ss:Bold=\"1\"/>
					  	</Style>
  						<Style ss:ID=\"sReportDate\">
							<Alignment ss:Horizontal=\"Right\" ss:Vertical=\"Bottom\"/>
							<Font x:Family=\"Swiss\" ss:Color=\"#597FA3\" ss:Size=\"12\" ss:Bold=\"1\"/>
					  	</Style>
  						<Style ss:ID=\"sTotal\">
							<Alignment ss:Horizontal=\"Center\" ss:Vertical=\"Bottom\"/>
							<Font x:Family=\"Swiss\" ss:Color=\"#333300\" ss:Size=\"13\" ss:Bold=\"1\"/>
					  	</Style>
 			 			<Style ss:ID=\"sHeader\">
			   				<Alignment ss:Horizontal=\"Center\" ss:Vertical=\"Bottom\"/>
			   				<Borders>
			   					<Border ss:Position=\"Bottom\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Left\"   ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Right\"  ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
			   					<Border ss:Position=\"Top\"    ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		   					</Borders>
			   				<Font x:Family=\"Swiss\" ss:Color=\"#505050\" ss:Bold=\"1\"/>
		   					<Interior ss:Color=\"#EEEEEE\" ss:Pattern=\"Solid\"/>
			  			</Style>
 			 			<Style ss:ID=\"sFooter\">
			   				<Alignment ss:Horizontal=\"Right\" ss:Vertical=\"Bottom\"/>
			   				<Borders>
			   					<Border ss:Position=\"Bottom\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Left\"   ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Right\"  ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
			   					<Border ss:Position=\"Top\"    ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		   					</Borders>
			   				<Font x:Family=\"Swiss\" ss:Color=\"#505050\" ss:Bold=\"1\"/>
		   					<Interior ss:Color=\"#EEEEEE\" ss:Pattern=\"Solid\"/>
			  			</Style>
 			 			<Style ss:ID=\"sFooterLeft\">
			   				<Alignment ss:Horizontal=\"Left\" ss:Vertical=\"Bottom\"/>
			   				<Borders>
			   					<Border ss:Position=\"Bottom\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Left\"   ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Right\"  ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
			   					<Border ss:Position=\"Top\"    ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		   					</Borders>
			   				<Font x:Family=\"Swiss\" ss:Color=\"#505050\" ss:Bold=\"1\"/>
		   					<Interior ss:Color=\"#EEEEEE\" ss:Pattern=\"Solid\"/>
			  			</Style>
			  			<Style ss:ID=\"sCell\">
			   				<Alignment ss:Horizontal=\"Right\" ss:Vertical=\"Bottom\"/>
			   				<Borders>
		    					<Border ss:Position=\"Bottom\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Left\"   ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Right\"  ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
			   				</Borders>
			   				<Font x:Family=\"Swiss\" ss:Bold=\"0\"/>
			 			</Style>
			  			<Style ss:ID=\"sCellEng\">
			   				<Alignment ss:Horizontal=\"Left\" ss:Vertical=\"Bottom\"/>
			   				<Borders>
		    					<Border ss:Position=\"Bottom\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Left\"   ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Right\"  ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
			   				</Borders>
			   				<Font x:Family=\"Swiss\" ss:Bold=\"0\"/>
			 			</Style>
						<Style ss:ID=\"Default\" ss:Name=\"Normal\">
			   				<Alignment ss:Vertical=\"Bottom\"/>
   							<Borders/>
							<Font x:CharSet=\"177\"/>
   							<Interior/>
			   				<NumberFormat/>
			   				<Protection/>
			  			</Style>
						<Style ss:ID=\"s32\">
			   				<Alignment ss:Horizontal=\"Center\" ss:Vertical=\"Bottom\"/>
			  			</Style>
			  			<Style ss:ID=\"s74\">
			   				<Alignment ss:Horizontal=\"Center\" ss:Vertical=\"Bottom\"/>
			  				<Borders>
								<Border ss:Position=\"Bottom\" ss:LineStyle=\"Continuous\" ss:Weight=\"2\"/>
								<Border ss:Position=\"Left\"   ss:LineStyle=\"Continuous\" ss:Weight=\"2\"/>
								<Border ss:Position=\"Right\"  ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
								<Border ss:Position=\"Top\"    ss:LineStyle=\"Continuous\" ss:Weight=\"2\"/>
							</Borders>
							<Font x:CharSet=\"177\" x:Family=\"Swiss\" ss:Color=\"#FFFFFF\" ss:Bold=\"1\"/>
							<Interior ss:Color=\"#969696\" ss:Pattern=\"Solid\"/>
			  			</Style>
			  			<Style ss:ID=\"s75\">
							<Alignment ss:Horizontal=\"Center\" ss:Vertical=\"Bottom\"/>
			   				<Borders>
   								<Border ss:Position=\"Bottom\" ss:LineStyle=\"Continuous\" ss:Weight=\"2\"/>
		    					<Border ss:Position=\"Left\"   ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Right\"  ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Top\"    ss:LineStyle=\"Continuous\" ss:Weight=\"2\"/>
				   			</Borders>
			   				<Font x:CharSet=\"177\" x:Family=\"Swiss\" ss:Color=\"#FFFFFF\" ss:Bold=\"1\"/>
							<Interior ss:Color=\"#969696\" ss:Pattern=\"Solid\"/>
			  			</Style>
			  			<Style ss:ID=\"s76\">
			   				<Alignment ss:Horizontal=\"Center\" ss:Vertical=\"Bottom\"/>
			   				<Borders>
		    					<Border ss:Position=\"Bottom\" ss:LineStyle=\"Continuous\" ss:Weight=\"2\"/>
		    					<Border ss:Position=\"Left\"   ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Right\"  ss:LineStyle=\"Continuous\" ss:Weight=\"2\"/>
		    					<Border ss:Position=\"Top\"    ss:LineStyle=\"Continuous\" ss:Weight=\"2\"/>
			  				</Borders>
			   				<Font x:CharSet=\"177\" x:Family=\"Swiss\" ss:Color=\"#FFFFFF\" ss:Bold=\"1\"/>
			   				<Interior ss:Color=\"#969696\" ss:Pattern=\"Solid\"/>
			  			</Style>
			  			<Style ss:ID=\"s77\">
			  				<Alignment ss:Horizontal=\"Left\" ss:Vertical=\"Bottom\"/>
			   				<Borders>
		    					<Border ss:Position=\"Left\"  ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Right\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
			   				</Borders>
			   				<Font x:Family=\"Swiss\" ss:Color=\"#0000FF\" ss:Bold=\"1\"/>
			  			</Style>
			  			<Style ss:ID=\"s88\">
   							<Font x:Family=\"Swiss\" ss:Color=\"#333300\" ss:Bold=\"1\"/>
			  			</Style>
			  			<Style ss:ID=\"s95\">
			   				<Alignment ss:Horizontal=\"Center\" ss:Vertical=\"Bottom\"/>
			   				<Borders>
		    					<Border ss:Position=\"Bottom\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Left\"   ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Right\"  ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Top\"    ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
			   				</Borders>
			   				<Interior ss:Color=\"#E3E3E3\" ss:Pattern=\"Solid\"/>
			  			</Style>
			  			<Style ss:ID=\"s96\">
			   				<Alignment ss:Horizontal=\"Center\" ss:Vertical=\"Bottom\"/>
			   				<Borders>
		    					<Border ss:Position=\"Bottom\" ss:LineStyle=\"Continuous\" ss:Weight=\"2\"/>
		    					<Border ss:Position=\"Left\"   ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Right\"  ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Top\"    ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
   							</Borders>
			   				<Interior ss:Color=\"#E3E3E3\" ss:Pattern=\"Solid\"/>
			  			</Style>
			  			<Style ss:ID=\"s98\">
			   				<Alignment ss:Horizontal=\"Left\" ss:Vertical=\"Bottom\"/>
			   				<Font x:Family=\"Swiss\" ss:Color=\"#0000FF\" ss:Italic=\"1\"/>
			 			</Style>
			  			<Style ss:ID=\"s99\">
			   				<Alignment ss:Horizontal=\"Left\" ss:Vertical=\"Bottom\"/>
			   				<Font x:Family=\"Swiss\" ss:Color=\"#0000FF\" ss:Italic=\"1\"/>
			   				<NumberFormat ss:Format=\"Short Date\"/>
			  			</Style>
			  			<Style ss:ID=\"s105\">
			   				<Borders>
		    					<Border ss:Position=\"Left\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
			   				</Borders>
			  			</Style>
			  			<Style ss:ID=\"s106\">
			   				<Alignment ss:Horizontal=\"Center\" ss:Vertical=\"Bottom\"/>
			   				<Borders>
		    					<Border ss:Position=\"Bottom\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Left\"   ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Right\"  ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
			   				</Borders>
			   				<Interior ss:Color=\"#E3E3E3\" ss:Pattern=\"Solid\"/>
			  			</Style>
 					</Styles>
					<Worksheet ss:Name=\"תוצאות שאלון\" ss:RightToLeft=\"1\">
					<Table x:FullColumns=\"1\" x:FullRows=\"1\">
						<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"60\"/>
						<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"40\"/>
						<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"100\"/>";

	$queryStr 		= "select * from questionnaireQuestions where questionnaireId = $id order by pos";
	$result	  		= commonDoQuery($queryStr);
	$numQuestions	= commonQuery_numRows($result);
	$mergeAcross	= $numQuestions + 2;

	for ($i = 0; $i < $numQuestions; $i++)
	{
		$excel .= "<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"20\"/>
				   <Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"100\"/>";
	}

	$excel .= "		<Row>
						<Cell ss:MergeAcross=\"$mergeAcross\" ss:StyleID=\"sTitle\"><Data ss:Type=\"String\">$excelTitle</Data></Cell>
					</Row>
					<Row>
						<Cell ss:MergeAcross=\"2\" ss:StyleID=\"sReportDate\"><Data ss:Type=\"String\">$now</Data></Cell>
						<Cell ss:MergeAcross=\"" . ($mergeAcross-2) . "\" ss:StyleID=\"sReportDate\"><Data ss:Type=\"String\">#count#</Data></Cell>
					</Row>
					<Row ss:Height=\"13.5\"/>
					<Row>
						<Cell ss:StyleID=\"sHeader\" ss:MergeAcross=\"1\"><Data ss:Type=\"String\">תאריך</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">גולש רשום</Data></Cell>";

	for ($i = 1; $i <= $numQuestions; $i++)
	{
		$excel .= "<Cell ss:MergeAcross=\"1\" ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">שאלה $i</Data></Cell>";
	}

	$excel .= 		"</Row>
					<Row>
						<Cell ss:StyleID=\"sHeader\" ss:MergeAcross=\"1\"><Data ss:Type=\"String\">ושעת השתתפות</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\"></Data></Cell>";

	while ($row = commonQuery_fetchRow($result))
	{
		$excel 	.= "	<Cell ss:MergeAcross=\"1\" ss:StyleID=\"sHeader\">
							<Data ss:Type=\"String\">" . 
								commonPrepareToFile(strip_tags(stripslashes($row['question'])))."
							</Data>
					   </Cell>";
	}

	$excel .= 		"</Row>";

	// get all checkbox answers
	$queryStr	= "select id, answer from questionnaireAnswers 
				   where questionId in (select id from questionnaireQuestions where questionnaireId = $id and type = 'checkbox')";

	$aResult 	= commonDoQuery($queryStr);

	$checkboxes = array();
	while ($aRow = commonQuery_fetchRow($aResult))
	{
		$checkboxes[$aRow['id']] = $aRow['answer'];
	}

	$queryStr	= "select questionnaireParticipants.*, clubMembers.firstname, clubMembers.lastname
				   from questionnaireParticipants 
				   left join clubMembers on questionnaireParticipants.memberId = clubMembers.id
				   where questionnaireId = $id order by participationDatetime desc";
	$pResult	= commonDoQuery($queryStr);

	$excel		= str_replace("#count#", "מספר משיבים: " . commonQuery_numRows($pResult), $excel);

	while ($pRow = commonQuery_fetchRow($pResult))
	{
		$datetime 	= formatApplFormDateTime ($pRow['participationDatetime']);

		$split	  	= explode(" ", $datetime);
		$date	  	= $split[0];	
		$time	  	= $split[1];	

		$member		= commonPrepareToFile(stripslashes("$pRow[firstname] $pRow[lastname]"));

		$excel .= "<Row ss:Height=\"13.5\">
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$date</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$time</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$member</Data></Cell>";

		$queryStr	= "select questionnaireResults.*, questionnaireQuestions.type, questionnaireQuestions.pos,
							  questionnaireAnswers.answer as radioAnswer, questionnaireAnswers.type as answerType
					   from (questionnaireResults, questionnaireQuestions)
					   left join questionnaireAnswers on questionnaireAnswers.id = questionnaireResults.answerId
					   where questionnaireResults.questionId = questionnaireQuestions.id 
					   and   participantId = $pRow[id] and questionnaireResults.questionnaireId = $id
					   order by questionId";
		$result	= commonDoQuery($queryStr);

		$codeAnswers = array();
		$answers 	 = array();
		while ($row = commonQuery_fetchRow($result))
		{
			$qid	= $row['questionId'];
			$pos	= $row['pos'];

			$answer = "";
			if ($row['type'] == "radio")
			{
				if ($row['answerType'] == "choose")
				{
					$answer = stripslashes($row['radioAnswer']);
				}
				else	// chooseWithText
				{
					$answer = stripslashes($row['answerText']);
				}

				if ($answer == "")
					$answer = $row['answerId'];

				$codeAnswer = $row['answerId'];
			}
			else if ($row['type'] == "checkbox")
			{
				if ($row['answerId'] != "")
				{
					$answerIds = explode(",", trim($row['answerId'], ","));

					foreach ($answerIds as $answerId)
					{
						if (isset($checkboxes[$answerId]))
							$answer .= ", " . stripslashes($checkboxes[$answerId]);
					}

					$answer = trim ($answer, ",");
				}
				else
				{
					$answer = $row['answerId'];
				}

				$codeAnswer = $row['answerId'];
			}
			else
			{
				$answer = stripslashes($row['answerText']);

				$codeAnswer = "";
			}

			$answers[$pos] 		= $answer;
			$codeAnswers[$pos] 	= $codeAnswer;
		}

		for ($i = 1; $i <= $numQuestions; $i++)
		{
			$answer = "";
			$codeAnswer = "";
			if (isset($answers[$i]))
			{
				$answer = commonPrepareToFile(stripslashes($answers[$i]));
				$codeAnswer = $codeAnswers[$i];
			}
	
			if ($codeAnswer == "")
			{
				$excel .= "<Cell ss:MergeAcross=\"1\" ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$answer</Data></Cell>";
			}
			else
			{
				$excel .= "<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"Number\">$codeAnswer</Data></Cell>
					 	   <Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$answer</Data></Cell>";
			}
		}

		$excel .= "</Row>";
	}

	$excel .= 	 "</Table>
				</Worksheet>
			</Workbook>";

	$xmlResponse = commonDoExcel ($excel);

	return ($xmlResponse);

}

?>
