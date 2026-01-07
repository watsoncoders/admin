<?php

$tags 	  = Array("id", "type", "numAnswers", "active", "publishDate", "closeDate", "pageId");
$langTags = Array("question", "answer1", "answer2", "answer3", "answer4", "answer5", "answer6", "answer7", "answer8", "answer9", "answer10",
				  "answer11", "answer12", "answer13", "answer14", "answer15", "answer16");

/* ----------------------------------------------------------------------------------------------------	*/
/* getSurveys																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function getSurveys ($xmlRequest)
{	
	global $usedLangs;
	$langsArray = explode(",",$usedLangs);

	// get total
	$queryStr	 = "select count(*) from surveys";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$total	     = $row[0];

	// get details
	$queryStr = "select * 
				 from surveys, surveys_byLang
				 where surveys.id = surveys_byLang.surveyId and surveys_byLang.language = '$langsArray[0]'
				 order by id desc " . commonGetLimit ($xmlRequest);

	$result	     = commonDoQuery ($queryStr);

	$numRows    = commonQuery_numRows($result);

	$xmlResponse = "<items>";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		$id   		  = $row['id'];
		$question	  = commonValidXml($row['question']);
		$answer1	  = commonCData(commonEncode("תשובה:") . " $row[answer1]");
		$answer2	  = commonCData(commonEncode("תשובה:") . " $row[answer2]");
		$answer3	  = commonCData(commonEncode("תשובה:") . " $row[answer3]");
		$answer4	  = commonCData(commonEncode("תשובה:") . " $row[answer4]");
		$answer5	  = commonCData(commonEncode("תשובה:") . " $row[answer5]");
		$answer6	  = commonCData(commonEncode("תשובה:") . " $row[answer6]");
		$answer7	  = commonCData(commonEncode("תשובה:") . " $row[answer7]");
		$answer8	  = commonCData(commonEncode("תשובה:") . " $row[answer8]");
		$answer9	  = commonCData(commonEncode("תשובה:") . " $row[answer9]");
		$answer10	  = commonCData(commonEncode("תשובה:") . " $row[answer10]");
		$answer11	  = commonCData(commonEncode("תשובה:") . " $row[answer11]");
		$answer12	  = commonCData(commonEncode("תשובה:") . " $row[answer12]");
		$answer13	  = commonCData(commonEncode("תשובה:") . " $row[answer13]");
		$answer14	  = commonCData(commonEncode("תשובה:") . " $row[answer14]");
		$answer15	  = commonCData(commonEncode("תשובה:") . " $row[answer15]");
		$answer16	  = commonCData(commonEncode("תשובה:") . " $row[answer16]");

		// get vote results
		$queryStr	 = "select count(*), answer from surveysVotes where surveyId = $id group by answer";
		$votesResult =  commonDoQuery ($queryStr);

		$countAnswer1  = 0;
		$countAnswer2  = 0;
		$countAnswer3  = 0;
		$countAnswer4  = 0;
		$countAnswer5  = 0;
		$countAnswer6  = 0;
		$countAnswer7  = 0;
		$countAnswer8  = 0;
		$countAnswer9  = 0;
		$countAnswer10 = 0;
		$countAnswer11 = 0;
		$countAnswer12 = 0;
		$countAnswer13 = 0;
		$countAnswer14 = 0;
		$countAnswer15 = 0;
		$countAnswer16 = 0;
		while ($voteRow = commonQuery_fetchRow($votesResult))
		{
			eval ("\$countAnswer$voteRow[1] = $voteRow[0];");
		}

		$totalCount = $countAnswer1  + $countAnswer2  + $countAnswer3  + $countAnswer4  + $countAnswer5  + $countAnswer6  + $countAnswer7  +
					  $countAnswer8  + $countAnswer9  + $countAnswer10 + $countAnswer11 + $countAnswer12 + $countAnswer13 + $countAnswer14 +
					  $countAnswer15 + $countAnswer16;
		if ($totalCount == 0)
		{
			$answer1Info  = "0%";
			$answer2Info  = "0%";
			$answer3Info  = "0%";
			$answer4Info  = "0%";
			$answer5Info  = "0%";
			$answer6Info  = "0%";
			$answer7Info  = "0%";
			$answer8Info  = "0%";
			$answer9Info  = "0%";
			$answer10Info = "0%";
			$answer11Info = "0%";
			$answer12Info = "0%";
			$answer13Info = "0%";
			$answer14Info = "0%";
			$answer15Info = "0%";
			$answer16Info = "0%";
		}
		else
		{
			$countAnswer1  == 0 ? $answer1Info  = "" : $answer1Info  = round($countAnswer1  / $totalCount * 100) . "% ($countAnswer1)";
			$countAnswer2  == 0 ? $answer2Info  = "" : $answer2Info  = round($countAnswer2  / $totalCount * 100) . "% ($countAnswer2)";
			$countAnswer3  == 0 ? $answer3Info  = "" : $answer3Info  = round($countAnswer3  / $totalCount * 100) . "% ($countAnswer3)";
			$countAnswer4  == 0 ? $answer4Info  = "" : $answer4Info  = round($countAnswer4  / $totalCount * 100) . "% ($countAnswer4)";
			$countAnswer5  == 0 ? $answer5Info  = "" : $answer5Info  = round($countAnswer5  / $totalCount * 100) . "% ($countAnswer5)";
			$countAnswer6  == 0 ? $answer6Info  = "" : $answer6Info  = round($countAnswer6  / $totalCount * 100) . "% ($countAnswer6)";
			$countAnswer7  == 0 ? $answer7Info  = "" : $answer7Info  = round($countAnswer7  / $totalCount * 100) . "% ($countAnswer7)";
			$countAnswer8  == 0 ? $answer8Info  = "" : $answer8Info  = round($countAnswer8  / $totalCount * 100) . "% ($countAnswer8)";
			$countAnswer9  == 0 ? $answer9Info  = "" : $answer9Info  = round($countAnswer9  / $totalCount * 100) . "% ($countAnswer9)";
			$countAnswer10 == 0 ? $answer10Info = "" : $answer10Info = round($countAnswer10 / $totalCount * 100) . "% ($countAnswer10)";
			$countAnswer11 == 0 ? $answer11Info = "" : $answer11Info = round($countAnswer11 / $totalCount * 100) . "% ($countAnswer11)";
			$countAnswer12 == 0 ? $answer12Info = "" : $answer12Info = round($countAnswer12 / $totalCount * 100) . "% ($countAnswer12)";
			$countAnswer13 == 0 ? $answer13Info = "" : $answer13Info = round($countAnswer13 / $totalCount * 100) . "% ($countAnswer13)";
			$countAnswer14 == 0 ? $answer14Info = "" : $answer14Info = round($countAnswer14 / $totalCount * 100) . "% ($countAnswer14)";
			$countAnswer15 == 0 ? $answer15Info = "" : $answer15Info = round($countAnswer15 / $totalCount * 100) . "% ($countAnswer15)";
			$countAnswer16 == 0 ? $answer16Info = "" : $answer16Info = round($countAnswer16 / $totalCount * 100) . "% ($countAnswer16)";
		}

		$xmlResponse .=	"<item>
							 <surveyId>$id</surveyId>
				 			 <question>$question</question>
				 			 <answer1>$answer1</answer1>
				 			 <answer2>$answer2</answer2>
				 			 <answer3>$answer3</answer3>
				 			 <answer4>$answer4</answer4>
				 			 <answer5>$answer5</answer5>
				 			 <answer6>$answer6</answer6>
				 			 <answer7>$answer7</answer7>
				 			 <answer8>$answer8</answer8>
				 			 <answer9>$answer9</answer9>
				 			 <answer10>$answer10</answer10>
				 			 <answer11>$answer11</answer11>
				 			 <answer12>$answer12</answer12>
				 			 <answer13>$answer13</answer13>
				 			 <answer14>$answer14</answer14>
				 			 <answer15>$answer15</answer15>
				 			 <answer16>$answer16</answer16>
							 <answer1Info>$answer1Info</answer1Info>
							 <answer2Info>$answer2Info</answer2Info>
							 <answer3Info>$answer3Info</answer3Info>
							 <answer4Info>$answer4Info</answer4Info>
							 <answer5Info>$answer5Info</answer5Info>
							 <answer6Info>$answer6Info</answer6Info>
							 <answer7Info>$answer7Info</answer7Info>
							 <answer8Info>$answer8Info</answer8Info>
							 <answer9Info>$answer9Info</answer9Info>
							 <answer10Info>$answer10Info</answer10Info>
							 <answer11Info>$answer11Info</answer11Info>
							 <answer12Info>$answer12Info</answer12Info>
							 <answer13Info>$answer13Info</answer13Info>
							 <answer14Info>$answer14Info</answer14Info>
							 <answer15Info>$answer15Info</answer15Info>
							 <answer16Info>$answer16Info</answer16Info>
						 </item>";
	}

	$xmlResponse .=	"</items>" .
					commonGetTotalXml($xmlRequest,$numRows,$total);
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getSurveyDetails																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getSurveyDetails ($xmlRequest)
{
	global $usedLangs, $tags, $langTags;

	$id		= xmlParser_getValue($xmlRequest, "surveyId");

	if ($id == "")
		trigger_error ("חסר קוד סקר לביצוע הפעולה");


	$queryStr = "select * from surveys, surveys_byLang
				 where surveys.id = surveys_byLang.surveyId
				 and   surveys.id = $id";
	$result   = commonDoQuery ($queryStr);

	if (commonQuery_numRows($result) == 0)
		trigger_error ("סקר קוד זה ($id) לא קיים במערכת. לא ניתן לבצע את הפעולה");

	$langsArray = explode(",",$usedLangs);

	$xmlResponse = "";

	while ($row = commonQuery_fetchRow($result))
	{
		$language = $row['language'];

		$langsArray = commonArrayRemove ($langsArray, $language);	

		if ($xmlResponse == "")
		{
			for ($i=0; $i < count($tags); $i++)
			{
				eval ("\$$tags[$i] = \$row['$tags[$i]'];");

			}

			// update status if close date has arrived
			if ($closeDate != "0000-00-00" && strtotime($closeDate) < strtotime(date("Y-m-d 00:00:00",strtotime("now"))))
			{
				$queryStr = "update surveys set active = 0 where id = $id";
				commonDoQuery ($queryStr);

				$active = "0";
			}

			$publishDate = formatApplDate($publishDate);
			$closeDate   = formatApplDate($closeDate);

			if ($pageId == "0") $pageId = "";

			for ($i=0; $i < count($tags); $i++)
			{
				eval ("\$$tags[$i] = commonValidXml(\$$tags[$i]);");

				eval ("\$xmlResponse .= \"<$tags[$i]>\$$tags[$i]</$tags[$i]>\";");
			}
			
			$xmlResponse .= "<surveyId>$id</surveyId>";
		}

		for ($i=0; $i < count($langTags); $i++)
		{
			eval ("\$$langTags[$i] = commonValidXml(\$row['$langTags[$i]']);");
			eval ("\$xmlResponse .=	\"<$langTags[$i]\$language>\$$langTags[$i]</$langTags[$i]\$language>\";");
		}
	}

	// add missing languages
	// ------------------------------------------------------------------------------------------------
	for ($i=0; $i<count($langsArray); $i++)
	{
		$language	  = $langsArray[$i];

		for ($j=0; $j < count($langTags); $j++)
		{
			eval ("\$xmlResponse .=	\"<$langTags[$j]\$language><![CDATA[]]></$langTags[$j]\$language>\";");
		}
	}

	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* addSurvey																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function addSurvey ($xmlRequest)
{
	return (editSurvey ($xmlRequest, "add"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* doesSurveyExist																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function doesSurveyExist ($id)
{
	$queryStr		= "select count(*) from surveys where id=$id";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$count	     = $row[0];

	return ($count > 0);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getSurveyNextId																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getSurveyNextId ()
{
	$queryStr	= "select max(id) from surveys";
	$result		= commonDoQuery ($queryStr);
	$row		= commonQuery_fetchRow ($result);
	$id 		= $row[0] + 1;
	
	return $id;
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateSurvey																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateSurvey ($xmlRequest)
{
	editSurvey ($xmlRequest, "update");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* editSurvey																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function editSurvey ($xmlRequest, $editType)
{
	global $usedLangs, $tags, $langTags;

	for ($i=0; $i < count($tags); $i++)
	{
		eval ("\$$tags[$i] = commonDecode(xmlParser_getValue(\$xmlRequest,\"$tags[$i]\"));");	
	}

	if ($editType == "update")
	{
		$id = xmlParser_getValue($xmlRequest, "surveyId");

		if (!doesSurveyExist($id))
		{
			trigger_error ("סקר עם קוד זה ($id) לא קיים במערכת. לא ניתן לבצע את העדכון");
		}
	
		$queryStr = "delete from surveys where id=$id";
		commonDoQuery ($queryStr);

		$queryStr = "delete from surveys_byLang where surveyId=$id";
		commonDoQuery ($queryStr);
	}
	else
	{
		$id = getSurveyNextId ();
	}

	if ($publishDate != "") $publishDate = formatApplToDB ($publishDate . " 00:00");
	if ($closeDate   != "") $closeDate   = formatApplToDB ($closeDate   . " 00:00");

	$vals = Array();

	for ($i=0; $i < count($tags); $i++)
	{
		eval ("array_push (\$vals,\$$tags[$i]);");
	}
	
	$queryStr = "insert into surveys (" . join(",",$tags) . ") values ('" . join("','",$vals) . "')";
	commonDoQuery ($queryStr);

	# add languages rows for this survey
	# ------------------------------------------------------------------------------------------------------
	$langsArray = explode(",",$usedLangs);

	for ($i=0; $i<count($langsArray); $i++)
	{
		$language		= $langsArray[$i];

		$vals = Array();
		for ($j=0; $j < count($langTags); $j++)
		{
			eval ("\$value = xmlParser_getValue(\$xmlRequest,\"$langTags[$j]\$language\");");	

			$value = addslashes(commonDecode($value));

			array_push ($vals, $value);
		}		

		$queryStr		= "insert into surveys_byLang (surveyId, language," . join(",",$langTags) . ") 
						   values ($id, '$language', '" . join ("','", $vals) . "')";
	
		commonDoQuery ($queryStr);
	}

	return "<surveyId>$id</surveyId>";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteSurvey																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteSurvey ($xmlRequest)
{
	$id = xmlParser_getValue($xmlRequest, "surveyId");

	if ($id == "")
		trigger_error ("חסר קוד סקר לביצוע הפעולה");

	$queryStr = "delete from surveys where id = $id";
	commonDoQuery ($queryStr);

	$queryStr = "delete from surveys_byLang where surveyId = $id";
	commonDoQuery ($queryStr);

	$queryStr = "delete from surveysVotes where surveyId = $id";
	commonDoQuery ($queryStr);

	$queryStr = "delete from categoriesItems where itemId = $id and type = 'survey'";
	commonDoQuery ($queryStr);

	return "";	
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getSurveyConfig																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getSurveyConfig ($xmlRequest)
{
	global $usedLangs;

	$queryStr    = "select * from surveyConfig";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);

	$resultsPageId 			= $row['resultsPageId'];
	$colorsMethod	 		= $row['colorsMethod'];
	$showNumVotes 			= $row['showNumVotes'];
	$blockDuplicateVotes	= $row['blockDuplicateVotes'];
	
	if ($resultsPageId == "0") $resultsPageId = "";

	$xmlResponse = "<resultsPageId>$resultsPageId</resultsPageId>
					<colorsMethod>$colorsMethod</colorsMethod>
					<showNumVotes>$showNumVotes</showNumVotes>
					<blockDuplicateVotes>$blockDuplicateVotes</blockDuplicateVotes>";

	# get config by language 
	# -------------------------------------------------------------------------------------------------
	$queryStr	 = "select * from surveyConfig_byLang";
	$result	     = commonDoQuery ($queryStr);

	$langsArray = explode(",",$usedLangs);

	while ($row = commonQuery_fetchRow($result))
	{
		$language = $row['language'];

		$langsArray = commonArrayRemove ($langsArray, $language);	

		$thanksMsg	 	= commonValidXml($row['thanksMsg']);
		$duplicateMsg	= commonValidXml($row['duplicateMsg']);
		$rulesMsg	 	= commonValidXml($row['rulesMsg']);

		$xmlResponse .= "<thanksMsg$language>$thanksMsg</thanksMsg$language>
						 <duplicateMsg$language>$duplicateMsg</duplicateMsg$language>
						 <rulesMsg$language>$rulesMsg</rulesMsg$language>";
	}

	// add missing languages
	// ------------------------------------------------------------------------------------------------
	for ($i=0; $i<count($langsArray); $i++)
	{
		$language	  = $langsArray[$i];

		$xmlResponse .= "<thanksMsg$language></thanksMsg$language>
						 <duplicateMsg$language></duplicateMsg$language>
						 <rulesMsg$language></rulesMsg$language>";
	}

	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateSurveyConfig																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateSurveyConfig ($xmlRequest)
{
	global $usedLangs;	// language cookie

	# delete all languages rows
	# --------------------------------------------------------------------------------------------------
	$queryStr = "delete from surveyConfig_byLang";
	commonDoQuery ($queryStr);

	# add languages rows for this page
	# --------------------------------------------------------------------------------------------------
	$langsArray = explode(",",$usedLangs);

	for ($i=0; $i<count($langsArray); $i++)
	{
		$language		= $langsArray[$i];
		$thanksMsg		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "thanksMsg$language")));
		$duplicateMsg	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "duplicateMsg$language")));
		$rulesMsg		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "rulesMsg$language")));

		$queryStr		= "insert into surveyConfig_byLang (language, thanksMsg, duplicateMsg, rulesMsg)
						   values ('$language', '$thanksMsg', '$duplicateMsg', '$rulesMsg')";

		commonDoQuery ($queryStr);		
	}

	$resultsPageId 		 = xmlParser_getValue($xmlRequest, "resultsPageId");
	$colorsMethod		 = xmlParser_getValue($xmlRequest, "colorsMethod");
	$showNumVotes		 = xmlParser_getValue($xmlRequest, "showNumVotes");
	$blockDuplicateVotes = xmlParser_getValue($xmlRequest, "blockDuplicateVotes");

	$queryStr = "update surveyConfig set resultsPageId 			= '$resultsPageId',
										 colorsMethod			= '$colorsMethod',
										 showNumVotes			= '$showNumVotes',
										 blockDuplicateVotes  	= '$blockDuplicateVotes'";

	commonDoQuery ($queryStr);

	return "";
}

?>
