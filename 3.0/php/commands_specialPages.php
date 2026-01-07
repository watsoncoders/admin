<?php


/* ----------------------------------------------------------------------------------------------------	*/
/* getSpecialPages																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getSpecialPages ($xmlRequest)
{
	global $usedLangs;
	$langsArray = explode(",",$usedLangs);

	// get total
	$queryStr	 = "select count(*) from specialPages";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$total	     = $row[0];

	$queryStr    = "select specialPages.id, specialPages.pos, pbl1.winTitle as belongTo, categories_byLang.name as categoryId, pbl2.title as pageId,
		  				   specialPages.description	
					from specialPages 
					left join pages_byLang pbl1 on belongTo = pbl1.pageId and pbl1.language = '$langsArray[0]' 
					left join categories_byLang on categories_byLang.categoryId = specialPages.categoryId and 
												   categories_byLang.language =  '$langsArray[0]'
					left join pages_byLang pbl2 on specialPages.pageId = pbl2.pageId and pbl2.language = '$langsArray[0]' 
					order by id " . commonGetLimit ($xmlRequest);
	$result	     = commonDoQuery ($queryStr);

	$numRows	 = commonQuery_numRows($result);

	$xmlResponse = "<items>";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);

		$id 		  = $row['id'];
		$belongTo	  = commonValidXml($row['belongTo']);
		$categoryId	  = commonValidXml($row['categoryId']);
		$description  = commonValidXml($row['description']);
		$pageId		  = commonValidXml($row['pageId']);
		$pos 		  = $row['pos'];

		$xmlResponse .= "<item>
							 <id>$id</id>
							 <belongTo>$belongTo</belongTo>
							 <categoryId>$categoryId</categoryId>
							 <pageId>$pageId</pageId>
							 <pos>$pos</pos>
							 <description>$description</description>
						</item>";
	}

	$xmlResponse .=	"</items>"												.
					commonGetTotalXml($xmlRequest,$numRows,$total);

	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getSpecialPageDetails																				*/
/* ----------------------------------------------------------------------------------------------------	*/
function getSpecialPageDetails ($xmlRequest)
{
	$id		= xmlParser_getValue($xmlRequest, "id");

	if ($id == "")
		trigger_error ("חסר קוד תוכן נבחר לביצוע הפעולה");

	$queryStr	= "select * from specialPages where id=$id";
	$result		= commonDoQuery ($queryStr);

	if (commonQuery_numRows($result) == 0)
		trigger_error ("קוד תוכן נבחר זה ($id) לא קיים במערכת. לא ניתן לבצע את העדכון");

	$row = commonQuery_fetchRow($result);
	
	$id				= $row['id'];
	$belongTo		= $row['belongTo'];
	$categoryId		= $row['categoryId'];
	$pageId			= $row['pageId'];
	$pos			= $row['pos'];
	$description	= commonValidXml($row['description']);
		
	$xmlResponse	= 	"<id>$id</id>
						 <belongTo>$belongTo</belongTo>
						 <categoryId>$categoryId</categoryId>
						 <pageId>$pageId</pageId>
						 <description>$description</description>
						 <pos>$pos</pos>";

	return $xmlResponse;
}

/* ----------------------------------------------------------------------------------------------------	*/
/* addSpecialPage																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function addSpecialPage ($xmlRequest)
{
	return (editSpecialPage ($xmlRequest, "add"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* doesSpecialPageExist																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function doesSpecialPageExist ($id)
{
	$queryStr		= "select count(*) from specialPages where id=$id";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$count	     = $row[0];

	return ($count > 0);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getSpecialPageNextId																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function getSpecialPageNextId ()
{
	$queryStr	= "select max(id) from specialPages";
	$result		= commonDoQuery ($queryStr);
	$row		= commonQuery_fetchRow ($result);
	$id 		= $row[0] + 1;
	
	return $id;
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateSpecialPage																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateSpecialPage ($xmlRequest)
{
	editSpecialPage ($xmlRequest, "update");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* editSpecialPage																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function editSpecialPage ($xmlRequest, $editType)
{
	$id		= xmlParser_getValue($xmlRequest, "id");

	if ($editType == "update")
	{
		if (!doesSpecialPageExist($id))
		{
			trigger_error ("תוכן נבחר עם קוד זה ($id) לא קיים במערכת. לא ניתן לבצע את העדכון");
		}
	}
	else
	{
		$id = getSpecialPageNextId();
	}

	$belongTo 	= xmlParser_getValue($xmlRequest, "belongTo");
	$categoryId	= xmlParser_getValue($xmlRequest, "categoryId");
	$pageId		= xmlParser_getValue($xmlRequest, "pageId");
	$pos		= xmlParser_getValue($xmlRequest, "pos");
	$description= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "description")));
	$pageType	= "essay";

	if ($editType == "add")
	{
		$queryStr = "insert into specialPages (id, belongTo, categoryId, pageType, pageId, pos, description) 
					 values ($id, '$belongTo', '$categoryId', '$pageType', '$pageId', '$pos', '$description')";

	}
	else // update
	{
		$queryStr = "update specialPages set belongTo   = '$belongTo',
											 categoryId = '$categoryId',
											 pageType	= '$pageType',
											 pageId		= '$pageId',
											 pos		= '$pos',
											 description= '$description'
				   	 where id=$id";
	}

	commonDoQuery ($queryStr);

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteSpecialPage																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteSpecialPage ($xmlRequest)
{
	$id  = xmlParser_getValue ($xmlRequest, "id");

	if ($id == "")
		trigger_error ("חסר קוד רשימה לביצוע הפעולה");

	$queryStr =  "delete from specialPages where id=$id";
	commonDoQuery ($queryStr);

	return "";
}

?>
