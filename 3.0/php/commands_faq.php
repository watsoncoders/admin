<?php

/* ----------------------------------------------------------------------------------------------------	*/
/* getFaq																								*/
/* ----------------------------------------------------------------------------------------------------	*/
function getFaq ($xmlRequest)
{	
	global $usedLangs;

	$condition = "";

	$categoryId		= xmlParser_getValue($xmlRequest, "categoryId");
	if ($categoryId != "")
		$condition .= " and categoriesItems.categoryId = $categoryId ";

	// get total
	$queryStr	 = "select count(*) from faq
					left join categoriesItems on faq.id = categoriesItems.itemId and categoriesItems.type = 'faq'
					where 1 $condition";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$total	     = $row[0];

	// get details
	$columns	 = "faq.*";
	$joins		= "";

	$langsArray = explode(",",$usedLangs);

	
	for ($i=0; $i<count($langsArray); $i++)
	{
		$language	 = $langsArray[$i];

		$columns	.= ",ul$i.question as question$language, ul$i.answer as answer$language, ul$i.shortAnswer as shortAnswer$language  ";

		$joins		.= " left join faq_byLang ul$i on id=ul$i.faqId and ul$i.language='$language'";
	}

	$queryStr = "select distinct $columns from faq 
			 	 left join categoriesItems on faq.id = categoriesItems.itemId and categoriesItems.type = 'faq'
				 $joins where 1 $condition order by id desc " . commonGetLimit ($xmlRequest);

	$result	     = commonDoQuery ($queryStr);

	$numRows    = commonQuery_numRows($result);

	$xmlResponse = "<items>";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		$id   		  = $row['id'];

		$onlyInPages  = $row['onlyInPages'];
		$onlyInPages  = trim($onlyInPages, ",");
		$onlyInPages  = join(" ", explode(",", $onlyInPages));

		$insertTime	  = formatApplDateTime($row['insertTime']);

		$status		  = $row['status'];

		switch ($status)
		{
			case "new"		: $status	= "חדש";		break;
			case "approved"	: $status	= "מאושר";		break;
			case "rejected"	: $status	= "לא מאושר";	break;
		}

		$status	= commonPhpEncode ($status);

		$xmlResponse .=	"<item>
							<faqId>$id</faqId>
							<status>$status</status>
							<insertTime>$insertTime</insertTime>
					 		<onlyInPages>$onlyInPages</onlyInPages>
					 		<pages></pages>";

		for ($l=0; $l<count($langsArray); $l++)
		{
			$language	  = $langsArray[$l];

			$question	  = commonValidXml($row['question'.$language]);
			$answer		  = commonValidXml($row['answer'.$language]);
			$shortAnswer  = commonValidXml($row['shortAnswer'.$language]);
			
			if ($l == 0)
			{
				$xmlResponse .=	"<question>$question</question>";
			}

			$xmlResponse .=	"<question$language>$question</question$language>
							 <shortAnswer$language>$shortAnswer</shortAnswer$language>
							 <answer$language>$answer</answer$language>";
		}

		$xmlResponse .= "</item>";
	}

	$xmlResponse .=	"</items>"												.
					commonGetTotalXml($xmlRequest,$numRows,$total);
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getFaqDetails																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getFaqDetails ($xmlRequest)
{	
	global $usedLangs;

	$langsArray = explode(",",$usedLangs);

	$faqId = xmlParser_getValue($xmlRequest, "faqId");

	$queryStr	 = "select * from faq, faq_byLang where faq.id = faq_byLang.faqId and id = $faqId";
	$result	     = commonDoQuery ($queryStr);

	$xmlResponse	= "";
	while ($row = commonQuery_fetchRow($result))
	{
		$status		  = $row['status'];
		$onlyInPages  = $row['onlyInPages'];
		$onlyInPages  = trim($onlyInPages, ",");
		$onlyInPages  = join(" ", explode(",", $onlyInPages));

		if ($xmlResponse == "")
		{
			$xmlResponse	= "<faqId>$faqId</faqId>
							   <status>$status</status>
					 		   <onlyInPages>$onlyInPages</onlyInPages>";
		}

		$language = $row['language'];

		$langsArray = commonArrayRemove ($langsArray, $language);	

		$question	  = commonValidXml($row['question']);
		$answer		  = commonValidXml($row['answer']);
		$shortAnswer  = commonValidXml($row['shortAnswer']);
			
		$xmlResponse .=	"<question$language>$question</question$language>
						 <shortAnswer$language>$shortAnswer</shortAnswer$language>
						 <answer$language>$answer</answer$language>";
	}
	
	// add missing languages
	// ------------------------------------------------------------------------------------------------
	for ($i=0; $i<count($langsArray); $i++)
	{
		$language	  = $langsArray[$i];

		$xmlResponse .=	   "<question$language><![CDATA[]]></question$language>
							<shortAnswer$language><![CDATA[]]></shortAnswer$language>
							<answer$language><![CDATA[]]></answer$language>";
	}

	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* addFaq																								*/
/* ----------------------------------------------------------------------------------------------------	*/
function addFaq ($xmlRequest)
{
	return (editFaq ($xmlRequest, "add"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* doesFaqExist																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function doesFaqExist ($id)
{
	$queryStr		= "select count(*) from faq where id=$id";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$count	     = $row[0];

	return ($count > 0);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getFaqNextId																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function getFaqNextId ()
{
	$queryStr	= "select max(id) from faq";
	$result		= commonDoQuery ($queryStr);
	$row		= commonQuery_fetchRow ($result);
	$id 		= $row[0] + 1;
	
	return $id;
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateFaq																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateFaq ($xmlRequest)
{
	editFaq ($xmlRequest, "update");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* editFaq																								*/
/* ----------------------------------------------------------------------------------------------------	*/
function editFaq ($xmlRequest, $editType)
{
	global $usedLangs;

	if ($editType == "update")
	{
		$faqId = xmlParser_getValue($xmlRequest, "faqId");

		if (!doesFaqExist($faqId))
		{
			trigger_error ("שו\"ת עם קוד זה ($faqId) לא קיים במערכת. לא ניתן לבצע את העדכון");
		}
	
		# delete all languages rows
		# ----------------------------------------------------------------------------------------------
		$queryStr = "delete from faq_byLang where faqId=$faqId";
		commonDoQuery ($queryStr);
	}
	else
	{
		$faqId = getFaqNextId ();
	}

	$status 	 = xmlParser_getValue($xmlRequest, "status");

	$onlyInPages = trim(xmlParser_getValue($xmlRequest, "onlyInPages"));
	if ($onlyInPages != "")
		$onlyInPages = "," . join(",", explode(" ", $onlyInPages)) . ",";

	# add languages rows for this faq
	# ------------------------------------------------------------------------------------------------------
	$langsArray = explode(",",$usedLangs);

	for ($i=0; $i<count($langsArray); $i++)
	{
		$language		= $langsArray[$i];

		$question		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "question$language")));
		$shortAnswer	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "shortAnswer$language")));
		$answer			= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "answer$language")));

		$queryStr		= "insert into faq_byLang (faqId, language, question, shortAnswer, answer)
						   values ('$faqId','$language','$question', '$shortAnswer', '$answer')";
						   
		commonDoQuery ($queryStr);
	}

	# ------------------------------------------------------------------------------------------------------

	if ($editType == "add")
	{
		$queryStr = "insert into faq (id, status, onlyInPages) values ($faqId, '$status', '$onlyInPages')";
		commonDoQuery ($queryStr);

		$categoryId		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "categoryId")));

		if ($categoryId != "")
		{
			// get last pos
			$queryStr = "select max(pos) from categoriesItems where categoryId = $categoryId and type = 'faq'";
			$result		= commonDoQuery ($queryStr);
			$row		= commonQuery_fetchRow ($result);
			$pos 		= $row[0] + 1;

			$queryStr = "insert into categoriesItems (itemId, categoryId, type, pos) values ($faqId, $categoryId, 'faq', $pos)";
			commonDoQuery ($queryStr);
		}
	}
	else // update
	{
		$queryStr	= "update faq set status = '$status', onlyInPages = '$onlyInPages' where id = $faqId";
		commonDoQuery ($queryStr);
	}
		

	return "<faqId>$faqId</faqId>";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteFaq																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteFaq ($xmlRequest)
{
	$id = xmlParser_getValue($xmlRequest, "faqId");

	if ($id == "")
		trigger_error ("חסר קוד שו\"ת לביצוע הפעולה");

	$queryStr = "delete from faq where id = $id";
	commonDoQuery ($queryStr);

	$queryStr = "delete from faq_byLang where faqId = $id";
	commonDoQuery ($queryStr);

	$queryStr = "delete from categoriesItems where itemId = $id and type = 'faq'";
	commonDoQuery ($queryStr);

	return "";	
}

?>
