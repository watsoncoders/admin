<?php

/* ----------------------------------------------------------------------------------------------------	*/
/* getBoxes																								*/
/* ----------------------------------------------------------------------------------------------------	*/
function getBoxes ($xmlRequest)
{
	global $usedLangs;
	$langsArray = explode(",",$usedLangs);

	$conditions = "";
	
	$conditions = "where boxes.type != 'links'";
	
	// get total
	$queryStr	 = "select count(*) from boxes $conditions";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$total	     = $row[0];

	// get details
	$queryStr    = "select boxes.id as id, boxName, boxes.type as type, count(links.boxId) as countLinks, title
					from boxes 
					left join boxes_byLang on boxes.id = boxes_byLang.boxId and boxes_byLang.language = '$langsArray[0]'
					left join links on boxes.id = links.boxId
					$conditions 
					group by boxes.id
					order by boxes.id " . commonGetLimit ($xmlRequest);
	$result	     = commonDoQuery ($queryStr);

	$numRows    = commonQuery_numRows($result);

	$xmlResponse = "<items>";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		$id    	 = $row['id'];
		$boxName = commonValidXml ($row['boxName'],true);
		$title 	 = commonValidXml ($row['title'],true);

		$countLinks = $row['countLinks'];

		$xmlResponse .=	"<item>
							<id>$id</id>
							<boxName>$boxName</boxName>
							<countLinks>$countLinks</countLinks>
							<title>$title</title>
						</item>";
	}

	$xmlResponse .=	"</items>"												.
					commonGetTotalXml($xmlRequest,$numRows,$total);
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* addBox																								*/
/* ----------------------------------------------------------------------------------------------------	*/
function addBox ($xmlRequest)
{
	return (editBox ($xmlRequest, "add"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* doesBoxExist																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function doesBoxExist ($id)
{
	$queryStr		= "select count(*) from boxes where id=$id";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$count	     = $row[0];

	return ($count > 0);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getBoxNextId																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function getBoxNextId ($xmlRequest)
{
	$queryStr	= "select max(id) from boxes";
	$result		= commonDoQuery ($queryStr);
	$row		= commonQuery_fetchRow ($result);
	$id 		= $row[0] + 1;
	
	return "<id>$id</id>";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getBoxDetails																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getBoxDetails ($xmlRequest)
{
	global $usedLangs;

	$id		= xmlParser_getValue($xmlRequest, "id");

	if ($id == "")
		trigger_error ("חסר קוד תיבה לביצוע הפעולה");

	$queryStr	= "select boxes.*, language, title, content 
				   from boxes left join boxes_byLang on id=boxId
				   where id=$id";
	$result		= commonDoQuery ($queryStr);

	if (commonQuery_numRows($result) == 0)
		trigger_error ("תיבה קוד זה ($id) לא קיים במערכת. לא ניתן לבצע את העדכון");

	$langsArray = explode(",",$usedLangs);

	$xmlResponse = "";
	
	while ($row = commonQuery_fetchRow($result))
	{
		$language = $row['language'];

		$langsArray = commonArrayRemove ($langsArray, $language);	

		if ($xmlResponse == "")
		{
			$id		   = $row['id'];
			$boxName   = commonValidXml ($row['boxName'],true);
			$isScroll  = $row['isScroll'];
			$scrollDir = $row['scrollDir'];
	
			if ($isScroll == "0")
				$scroll = "";
			else
				$scroll = $scrollDir;

			$xmlResponse = 	"<id>$id</id>
							 <scroll>$scroll</scroll>
							 <boxName>$boxName</boxName>";
		}

		$content	= commonValidXml($row['content']);
		$title		= commonValidXml($row['title']);

		$xmlResponse   .= "<content$language>$content</content$language>
						   <title$language>$title</title$language>";
	}

	// add missing languages
	// ------------------------------------------------------------------------------------------------
	for ($i=0; $i<count($langsArray); $i++)
	{
		$language	  = $langsArray[$i];

		$xmlResponse .=	   "<content$language><![CDATA[]]></content$language>
						    <title$language><![CDATA[]]></title$language>";
	}

	return $xmlResponse;
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateBox																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateBox ($xmlRequest)
{
	editBox ($xmlRequest, "update");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* editBox																								*/
/* ----------------------------------------------------------------------------------------------------	*/
function editBox ($xmlRequest, $editType)
{
	global $usedLangs;

	$id		= xmlParser_getValue($xmlRequest, "id");

	if ($editType == "add")
	{
		$queryStr	= "select max(id) from boxes";
		$result		= commonDoQuery ($queryStr);
		$row		= commonQuery_fetchRow ($result);
		$id 		= $row[0] + 1;
	}

	if ($id == "")
		trigger_error ("חסר קוד תיבה לביצוע הפעולה");

	if ($editType == "add")
	{
		if (doesBoxExist($id))
		{
			trigger_error ("תיבה עם קוד זהה ($id) כבר קיים במערכת");
		}
	}
	else	// update box
	{
		if (!doesBoxExist($id))
		{
			trigger_error ("תיבה עם קוד זה ($id) לא קיים במערכת. לא ניתן לבצע את העדכון");
		}
	}

	# delete all languages rows
	# ------------------------------------------------------------------------------------------------------
	$queryStr = "delete from boxes_byLang where boxId='$id'";
	commonDoQuery ($queryStr);
	
	# add languages rows for this box
	# ------------------------------------------------------------------------------------------------------
	$langsArray = explode(",",$usedLangs);

	for ($i=0; $i<count($langsArray); $i++)
	{
		$language		= $langsArray[$i];

		$content		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "content$language")));
		$title			= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "title$language")));

		$queryStr		= "insert into boxes_byLang (boxId, language, title, content)
						   values ('$id','$language','$title','$content')";
	
		commonDoQuery ($queryStr);
	}

	$boxName	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "boxName")));
	$scroll		= xmlParser_getValue($xmlRequest, "scroll");

	if ($scroll == "")
	{
		$isScroll  = "0";
		$scrollDir = "";
	}
	else
	{
		$isScroll  = "1";
		$scrollDir = $scroll;
	}

	if ($editType == "add")
	{
		$queryStr = "insert into boxes (id, type, boxName, isScroll, scrollDir) values
						($id, 'html', '$boxName', '$isScroll', '$scrollDir')";
	}
	else // update
	{
		$queryStr = "update boxes set 	boxName		= '$boxName',
										isScroll	= '$isScroll',
										scrollDir	= '$scrollDir'		
					 where id=$id";
	}

	commonDoQuery ($queryStr);

	if ($isScroll == "1")
	{
//		buildTickerDirectory ($id);
	}

	return "<id>$id</id>";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* buildTickerDirectory																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function buildTickerDirectory ($boxId)
{
	$domainRow = commonGetDomainRow();

	$domainNameArray = explode("/", $domainRow['domainName']);
	$fixedDomainName = $domainNameArray[0];

	// establish ftp connection to the server
	$conn_id = ftp_connect($fixedDomainName); 

	// login with username and password
	$login_result = ftp_login($conn_id, $domainRow['ftpUsername'], $domainRow['ftpPassword']); 

	// check connection
	if ((!$conn_id) || (!$login_result)) 
	{ 
	       trigger_error("FTP connection has failed!");
	}
	
	// check if 'galleries' already exists
	if (!ftp_chdir($conn_id, "tickers")) 
	{
		ftp_mkdir($conn_id, "tickers");
		ftp_chdir($conn_id, "tickers");
	}

	// check if updating or craeting a ticker
	if (ftp_nlist($conn_id, ".") == "" || !in_array("ticker".$boxId, ftp_nlist($conn_id, "."))) 
	{ 
		// Creating ticker
		ftp_mkdir($conn_id, "ticker".$boxId);
		ftp_chdir($conn_id, "ticker".$boxId);

		// send ticker files
//		ftp_put($conn_id, "../../swfobject.js", "../ticker/swfobject.js", FTP_BINARY);
		ftp_put($conn_id, "ticker.xml", "../ticker/ticker.xml", FTP_BINARY);
		ftp_put($conn_id, "ticker.swf", "../ticker/flashticker.swf", FTP_BINARY);
		
		//php4 equivalent of the php5 function
		if (!function_exists('ftp_chmod')) 
		{
		   function ftp_chmod($ftpstream,$chmod,$file)
		   {
		       $old=error_reporting();//save old
		       error_reporting(0);//set to none
		       $result=ftp_site($ftpstream, "CHMOD ".$chmod." ".$file);
		       error_reporting($old);//reset to old
		       return $result;//will result TRUE or FALSE
		   }
		}
		
		if (ftp_chmod($conn_id,"0666","ticker.xml") == FALSE)
		{
			trigger_error ("ERROR FTP_CHMOD: can't change chmod of ticker.xml");
		}
	}

	// close the FTP stream 
	ftp_close($conn_id); 
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteBox																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteBox ($xmlRequest)
{
	$id = xmlParser_getValue ($xmlRequest, "id");

	if ($id == "")
		trigger_error ("חסר קוד תיבה לביצוע הפעולה");

	$queryStr = "delete from boxes where id = $id";
	commonDoQuery ($queryStr);

	$queryStr = "delete from boxes_byLang where boxId = $id";
	commonDoQuery ($queryStr);

	return "";
}

?>
