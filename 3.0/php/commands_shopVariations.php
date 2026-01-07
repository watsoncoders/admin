<?php

require_once "picsTools.php";

/* ----------------------------------------------------------------------------------------------------	*/
/* getVariationsGroups																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function getVariationsGroups ($xmlRequest)
{	
	global $usedLangs;

	$langsArray = explode(",",$usedLangs);
	
	// get total
	$queryStr	 = "select count(*) from shopVariationsGroups";
	$result	     = commonDoQuery ($queryStr);
	$total	     = commonQuery_numRows($result);

	// get details
	$queryStr = str_replace("count(*)", "*", $queryStr);

	$result	     = commonDoQuery ($queryStr);

	$numRows    = commonQuery_numRows($result);

	$xmlResponse = "<items>";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		$id     = $row['id'];
		$name   = commonValidXml($row['name']);

		$xmlResponse .=	"<item>
							<groupId>$id</groupId>
							<name>$name</name>
						 </item>";
	}

	$xmlResponse .=	"</items>"												.
					commonGetTotalXml($xmlRequest,$numRows,$total);
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getPriceFunctionText																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function getPriceFunctionText ($value)
{
	switch ($value)
	{
		case "none"			: return "ללא שינוי";	
	  	case "plusUnit"		: return "תוספת ביחידות";
	  	case "minusUnit"	: return "הורדה ביחידות";
	  	case "plusPercent"	: return "תוספת באחוזים";
	  	case "minusPercent"	: return "הורדה באחוזים";
	}

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getVariations																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getVariations ($xmlRequest)
{	
	global $usedLangs;

	$langsArray = explode(",",$usedLangs);
	
	$conditions = "";

	$groupId	= xmlParser_getValue($xmlRequest, "groupId");
	if ($groupId != "")
		$conditions .= " and shopVariations.groupId = $groupId ";

	// get total
	$queryStr	 = "select count(*) from shopVariations, shopVariations_byLang, shopVariationsGroups
					where shopVariations.id = shopVariations_byLang.variationId and language = '$langsArray[0]'
					and   shopVariations.type = 'public'
					and   shopVariations.groupId = shopVariationsGroups.id $conditions 
					order by shopVariations.groupId, shopVariations.id desc";
	$result	     = commonDoQuery ($queryStr);
	$row		 = commonQuery_fetchRow($result);
	$total	     = $row[0];

	// get details
	$queryStr = str_replace("count(*)", "shopVariations.*, shopVariations_byLang.*, shopVariationsGroups.name as groupName", $queryStr);

	$result	     = commonDoQuery ($queryStr);

	$numRows    = commonQuery_numRows($result);

	$domainRow   = commonGetDomainRow ();
	$siteUrl     = commonGetDomainName($domainRow);
	
	$xmlResponse = "<items>";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		$id     	= $row['id'];
		$groupName	= commonValidXml($row['groupName']);
		$index   	= $row['index1'];

		if ($row['index2'] != "")
			$index .= " " . $row['index2'];

		if ($row['index3'] != "")
			$index .= " " . $row['index3'];

		$index = commonValidXml($index);

		$price	= getPriceFunctionText($row['priceFunction']);

		if ($row['priceValue'] != 0)
			$price .= " " . $row['priceValue'];

		$price	= commonPhpEncode($price);

		$showPic = "";
		if ($row['picSource'] != "")
			$showPic = commonPhpEncode("לחץ להצגה");

		$xmlResponse .=	"<item>
							<variationId>$id</variationId>
							<index>$index</index>
							<groupName>$groupName</groupName>
							<price>$price</price>
							<showPic>$showPic</showPic>
							<pic>$siteUrl/productVarFiles/$row[picFile]</pic>
						 </item>";
	}

	$xmlResponse .=	"</items>"												.
					commonGetTotalXml($xmlRequest,$numRows,$total);
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getProductVariations																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function getProductVariations ($xmlRequest)
{	
	global $usedLangs;

	$langsArray = explode(",",$usedLangs);
	
	$productId	= xmlParser_getValue($xmlRequest, "productId");
	$conditions = " and shopProductsVariations.productId = $productId ";

	$groupId	= xmlParser_getValue($xmlRequest, "groupId");
	if ($groupId != "")
		$conditions .= " and shopVariations.groupId = $groupId ";

	// get total
	$queryStr	 = "select count(*) from shopProductsVariations, shopVariations, shopVariations_byLang
					where shopProductsVariations.variationId = shopVariations.id
		   			and   shopVariations.id	= shopVariations_byLang.variationId and language = '$langsArray[0]'
					and   shopVariations.type = 'public' $conditions
					order by shopProductsVariations.pos";
	$result	     = commonDoQuery ($queryStr);
	$row		 = commonQuery_fetchRow($result);
	$total	     = $row[0];

	// get details
	$queryStr = str_replace("count(*)", "shopProductsVariations.price, shopProductsVariations.priceValue as productPriceValue, 
										 shopProductsVariations.picFile as productPicFile, shopProductsVariations.picSource as productPicSource,
										 shopProductsVariations.pos, shopVariations.*, shopVariations_byLang.*", 
							$queryStr);

	$result	     = commonDoQuery ($queryStr);

	$numRows    = commonQuery_numRows($result);

	$domainRow   = commonGetDomainRow ();
	$siteUrl     = commonGetDomainName($domainRow);
	
	$xmlResponse = "<items>";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		$id     	= $row['id'];
		$pos     	= $row['pos'];
		$index   	= $row['index1'];

		if ($row['index2'] != "")
			$index .= " " . $row['index2'];

		if ($row['index3'] != "")
			$index .= " " . $row['index3'];

		$index = commonValidXml($index);

		$price = $row['price'];
		if ($price == "inheritor")
			$price	= $row['priceFunction'];

		$price = getPriceFunctionText($price);

		if ($row['price'] == "inheritor")
			$priceValue = $row['priceValue'];
		else
			$priceValue = $row['productPriceValue'];

		if ($priceValue != 0)
			$price .= " " . $priceValue;

		if ($row['price'] == "inheritor")
			$price	.= " (לפי הוריאציה)";

		$price	= commonPhpEncode($price);

		$picFile = "";
		$showPic = "";
		if ($row['productPicSource'] != "")
		{
			$showPic = commonPhpEncode("לחץ להצגה (תמונת מוצר)");
			$picFile = $row['productPicFile'];
		}
		elseif ($row['picSource'] != "")
		{
			$showPic = commonPhpEncode("לחץ להצגה");
			$picFile = $row['picFile'];
		}

		$xmlResponse .=	"<item>
							<variationId>$id</variationId>
							<pos>$pos</pos>
							<index>$index</index>
							<price>$price</price>
							<showPic>$showPic</showPic>
							<pic>$siteUrl/productVarFiles/$picFile</pic>
						 </item>";
	}

	$xmlResponse .=	"</items>"												.
					commonGetTotalXml($xmlRequest,$numRows,$total);
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getProductRestVariations																				*/
/* ----------------------------------------------------------------------------------------------------	*/
function getProductRestVariations ($xmlRequest)
{	
	global $usedLangs;

	$langsArray = explode(",",$usedLangs);
	
	$productId	= xmlParser_getValue($xmlRequest, "productId");
	$groupId	= xmlParser_getValue($xmlRequest, "groupId");

	$queryStr	= "select variationId from shopProductsVariations, shopVariations
				   where shopProductsVariations.productId = $productId
				   and   shopProductsVariations.variationId = shopVariations.id
				   and   shopVariations.groupId = $groupId";
	$result		= commonDoQuery($queryStr);

	$ids		= Array("0");

	while ($row = commonQuery_fetchRow($result))
	{
		array_push ($ids, $row['variationId']);
	}

	$conditions  = " and shopVariations.id not in (" . join(",", $ids) . ") ";

	// get total
	$queryStr	 = "select count(*) from shopVariations, shopVariations_byLang
					where shopVariations.id	= shopVariations_byLang.variationId and language = '$langsArray[0]'
					and   shopVariations.type = 'public' 
					and   shopVariations.groupId = $groupId $conditions";
	$result	     = commonDoQuery ($queryStr);
	$row		 = commonQuery_fetchRow($result);
	$total	     = $row[0];

	// get details
	$queryStr = str_replace("count(*)", "shopVariations.*, shopVariations_byLang.*", $queryStr);

	$result	     = commonDoQuery ($queryStr);

	$numRows    = commonQuery_numRows($result);

	$domainRow   = commonGetDomainRow ();
	$siteUrl     = commonGetDomainName($domainRow);
	
	$xmlResponse = "<items>";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		$id     	= $row['id'];
		$index   	= $row['index1'];

		if ($row['index2'] != "")
			$index .= " " . $row['index2'];

		if ($row['index3'] != "")
			$index .= " " . $row['index3'];

		$index = commonValidXml($index);

		$price	= getPriceFunctionText($row['priceFunction']);

		$priceValue = $row['priceValue'];

		if ($priceValue != 0)
			$price .= " " . $priceValue;

		$price	= commonPhpEncode($price);

		$xmlResponse .=	"<item>
							<variationId>$id</variationId>
							<index>$index</index>
							<price>$price</price>
						 </item>";
	}

	$xmlResponse .=	"</items>";
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getVariationDetails																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function getVariationDetails ($xmlRequest)
{
	global $usedLangs;

	$id		= xmlParser_getValue($xmlRequest, "variationId");

	if ($id == "")
		trigger_error ("חסר קוד לביצוע הפעולה");

	$queryStr	= "select * from shopVariations, shopVariations_byLang
				   where shopVariations.id = shopVariations_byLang.variationId
				   and id=$id";
	$result		= commonDoQuery ($queryStr);

	if (commonQuery_numRows($result) == 0)
		trigger_error ("וריאציה עם קוד זה ($id) לא קיימת במערכת. לא ניתן לבצע את העדכון");

	$langsArray = explode(",",$usedLangs);

	// siteUrl
	$domainRow   = commonGetDomainRow ();
	$siteUrl     = commonGetDomainName($domainRow);

	$xmlResponse = "";
	
	while ($row = commonQuery_fetchRow($result))
	{
		$language = $row['language'];

		$langsArray = commonArrayRemove ($langsArray, $language);	

		if ($xmlResponse == "")
		{
			$id			   = $row['id'];
			$groupId	   = $row['groupId'];
			$priceFunction = $row['priceFunction'];
			$priceValue	   = $row['priceValue'];
			$picFile   	   = commonValidXml($row['picFile']);
			$picSource	   = commonCData(commonEncode($row['picSource']));
	
			$linkToFile	    = "$siteUrl/productVarFiles/$row[picFile]";

			$pressText     = commonPhpEncode("לחץ כאן");

			$show	 = "";
			$delete  = "";

			if ($row['picFile']  != "") 
			{
				$show   = $pressText;
				$delete = $pressText;
			}

			$xmlResponse .= "<variationId>$id</variationId>
							 <groupId>$groupId</groupId>
							 <priceFunction>$priceFunction</priceFunction>
							 <priceValue>$priceValue</priceValue>
							 <usedLangs>$usedLangs</usedLangs>
							 <picSource>$picSource</picSource>
							 <formSourceFile>$picSource</formSourceFile>
							 <show>$show</show>
							 <delete>$delete</delete>
							 <linkToFile>$linkToFile</linkToFile>";
		}

		$index1		= commonValidXml($row['index1']);
		$index2		= commonValidXml($row['index2']);
		$index3		= commonValidXml($row['index3']);
		$desc		= commonValidXml($row['desc']);
	
		$xmlResponse .=	   "<index1$language>$index1</index1$language>
							<index2$language>$index2</index2$language>
							<index3$language>$index3</index3$language>
							<desc$language>$desc</desc$language>";
	}

	// add missing languages
	// ------------------------------------------------------------------------------------------------
	for ($i=0; $i<count($langsArray); $i++)
	{
		$language	  = $langsArray[$i];

		$xmlResponse .=	   "<index1$language><![CDATA[]]></index1$language>
							<index2$language><![CDATA[]]></index2$language>
							<index3$language><![CDATA[]]></index3$language>
							<desc$language><![CDATA[]]></desc$language>";
	}

	return $xmlResponse;
}


/* ----------------------------------------------------------------------------------------------------	*/
/* deleteVariation																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteVariation ($xmlRequest)
{
	$id = xmlParser_getValue($xmlRequest, "variationId");

	if ($id == "")
		trigger_error ("חסר קוד וריאציה לביצוע הפעולה");

	$queryStr = "select productId from shopProductsVariations where variationId = $id";
	$result   = commonDoQuery($queryStr);
	while ($row = commonQuery_fetchRow($result))
	{
		doDeleteProductVariation ($id, $row['productId']);
	}

	$queryStr = "select picFile from shopVariations where id = $id";
	$result   = commonDoQuery($queryStr);
	$row 	  = commonQuery_fetchRow($result);

	$queryStr = "delete from shopVariations where id = $id";
	commonDoQuery ($queryStr);

	$queryStr = "delete from shopVariations_byLang where variationId = $id";
	commonDoQuery ($queryStr);

	if ($row['picFile'] != "")
	{
		$domainRow	= commonGetDomainRow();
		$connId 	= commonFtpConnect($domainRow); 

		$suffix		= commonFileSuffix ($row['picFile']);

		ftp_delete ($connId, "productVarFiles/$id$suffix");
		ftp_delete ($connId, "productVarFiles/${id}_size0.jpg");

		commonFtpDisconnect ($connId);
	}

	return "";	
}
	
/* ----------------------------------------------------------------------------------------------------	*/
/* addVariation																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function addVariation ($xmlRequest)
{
	return (editVariation ($xmlRequest, "add"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateVariation																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateVariation ($xmlRequest)
{
	editVariation ($xmlRequest, "update");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* editVariation																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function editVariation ($xmlRequest, $editType)
{
	global $usedLangs;
	global $userId;
	global $ibosHomeDir;

	$groupId		= commonDecode(xmlParser_getValue($xmlRequest, "groupId"));	
	$priceFunction	= commonDecode(xmlParser_getValue($xmlRequest, "priceFunction"));	
	$priceValue		= commonDecode(xmlParser_getValue($xmlRequest, "priceValue"));	

	if ($editType == "add")
	{
		$queryStr 		= "select max(id) from shopVariations";
		$result	  		= commonDoQuery ($queryStr);
		$row	  		= commonQuery_fetchRow ($result);
		$variationId 	= $row[0] + 1;
	}
	else
	{
		$variationId	= xmlParser_getValue($xmlRequest, "variationId");	
	}


	// handle picture 
	# ------------------------------------------------------------------------------------------------------
	$picSource		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "picSource")));	
	$dimensionId	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "dimensionId")));	

	if ($dimensionId == "") $dimensionId = 0;

	$fileLoaded		= false;

	$picFile 		= "";

	$suffix 		= "";
	if ($picSource != "")
	{
		$fileLoaded = true;
		$suffix		= commonFileSuffix ($picSource);
		$picFile 	= "${variationId}.jpg";
	}

	if ($editType == "add")
	{
		$queryStr = "insert into shopVariations (id, groupId, type, priceFunction, priceValue, picFile, picSource) 
					 values ($variationId, '$groupId', 'public', '$priceFunction', '$priceValue', '$picFile', '$picSource')";
		commonDoQuery ($queryStr);
	}
	else
	{
		$queryStr = "update shopVariations set groupId			= '$groupId',
										   	   priceFunction 	= '$priceFunction',
										   	   priceValue 		= '$priceValue' ";

		if ($fileLoaded)
		{
			$queryStr	.= "			   ,
										   picFile 			= '$picFile',
										   picSource 		= '$picSource'";
		}
		
		$queryStr .= " where id=$variationId";
		
		commonDoQuery ($queryStr);
	}

	# add languages rows
	# ------------------------------------------------------------------------------------------------------
	$langsArray = explode(",",$usedLangs);

	for ($i=0; $i<count($langsArray); $i++)
	{
		$language		= $langsArray[$i];

		$index1 	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "index1$language")));	
		$index2 	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "index2$language")));	
		$index3 	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "index3$language")));	
		$desc 		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "desc$language")));	

		$queryStr		= "replace into shopVariations_byLang (variationId, language, index1, index2, index3, `desc`)
						   values ('$variationId', '$language', '$index1', '$index2', '$index3', '$desc')";
		commonDoQuery ($queryStr);
	}

	if ($dimensionId == 0)
	{
		$picWidth  = 0;
		$picHeight = 0;
		$bgColor   = "#FFFFFF";
	}
	else if ($dimensionId != "")
	{
		list($picWidth, $picHeight, $bgColor) = commonGetDimensionDetails ($dimensionId);
	}

	// handle file
	$filePath = "$ibosHomeDir/html/SWFUpload/files/$userId";

	if ($fileLoaded)
	{
		$domainRow	= commonGetDomainRow();

		$connId = commonFtpConnect($domainRow); 

		ftp_chdir ($connId, "productVarFiles");

		$upload = ftp_put($connId, $picFile, "$filePath/$picSource", FTP_BINARY); 

		$fileName = "${variationId}_size0.jpg";

		if ($picWidth == 0 && $picHeight == 0)
		{
			$upload = ftp_put($connId, $fileName, "$filePath/$picSource", FTP_BINARY);
		}
		else
		{
			picsToolsResize("$filePath/$picSource", $suffix, $picWidth, $picHeight, "/../../tmp/$fileName", $bgColor);
			$upload = ftp_put($connId, $fileName, "/../../tmp/$picFile", FTP_BINARY);
		}

		unlink("$filePath/$picSource");

		commonFtpDisconnect ($connId);
	}

 	// delete old files
	commonDeleteOldFiles ($filePath, 3600);	// 1 hour

	return "";
}


/* ----------------------------------------------------------------------------------------------------	*/
/* getProductVariationDetails																			*/
/* ----------------------------------------------------------------------------------------------------	*/ 
function getProductVariationDetails ($xmlRequest)
{
	global $usedLangs;

	$langsArray = explode(",",$usedLangs);
	
	$productId		= commonDecode(xmlParser_getValue($xmlRequest, "productId"));	
	$variationId	= commonDecode(xmlParser_getValue($xmlRequest, "variationId"));	

	if ($productId == "" || $variationId == "")
		trigger_error ("חסר מזהה לביצוע הפעולה");

	$queryStr = "select shopProductsVariations.price, shopProductsVariations.priceValue as productPriceValue, shopProductsVariations.pos, 
						shopProductsVariations.picFile as productPicFile, shopProductsVariations.picSource as productPicSource,
						shopProductsVariations.makat, shopVariations.*, shopVariations_byLang.*
				from  shopProductsVariations, shopVariations, shopVariations_byLang
				where shopProductsVariations.variationId = shopVariations.id
	   			and   shopVariations.id	= shopVariations_byLang.variationId and language = '$langsArray[0]'
				and   shopProductsVariations.productId = $productId
				and   shopProductsVariations.variationId = $variationId";
	$result	  = commonDoQuery ($queryStr);
	$row	  = commonQuery_fetchRow($result);

	$xmlResponse = "";

	$pos     		= $row['pos'];
	$index   		= $row['index1'];
	$makat			= commonValidXml($row['makat']);
	$priceFunction	= $row['price'];
	$priceValue		= $row['productPriceValue'];
	if ($priceValue == "0") $priceValue = "";

	if ($row['index2'] != "")
		$index .= " " . $row['index2'];

	if ($row['index3'] != "")
		$index .= " " . $row['index3'];

	$index = commonValidXml($index);

	$price	= getPriceFunctionText($row['priceFunction']);

	if ($row['priceValue'] != 0)
		$price .= " " . $row['priceValue'];

	$price	= commonPhpEncode($price);

	$picFile   = $row['productPicFile'];
	$picSource = $row['productPicSource'];

	$pressText     = commonPhpEncode("לחץ כאן");

	$show 	 = "";
	$delete	 = "";
	$link	 = "";

	if ($picSource != "" && $picFile != "")
	{
		$domainRow   = commonGetDomainRow ();
		$siteUrl     = commonGetDomainName($domainRow);
	
		$show   = $pressText;
		$delete = $pressText;
		$link	= "$siteUrl/productVarFiles/$picFile";
	}
	
	$xmlResponse .=	"<variationId>$variationId</variationId>
					 <productId>$productId</productId>
					 <pos>$pos</pos>
					 <index>$index</index>
					 <price>$price</price>
					 <priceFunction>$priceFunction</priceFunction>
					 <priceValue>$priceValue</priceValue>
					 <makat>$makat</makat>
					 <show>$show</show>
					 <delete>$delete</delete>
					 <picFile>$picFile</picFile>
					 <picSource>$picSource</picSource>
					 <formSourceFile>$picSource</formSourceFile>
					 <linkToFile>$link</linkToFile>
					 <dimensionId></dimensionId>";

	return $xmlResponse;
}

/* ----------------------------------------------------------------------------------------------------	*/
/* addProductVariation																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function addProductVariation ($xmlRequest)
{
	return (editProductVariation ($xmlRequest, "add"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateProductVariation																				*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateProductVariation ($xmlRequest)
{
	editProductVariation ($xmlRequest, "update");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* editProductVariation																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function editProductVariation ($xmlRequest, $editType)
{
	global $userId;
	global $ibosHomeDir;

	$productId		= commonDecode(xmlParser_getValue($xmlRequest, "productId"));	
	$variationId	= commonDecode(xmlParser_getValue($xmlRequest, "variationId"));	
	$makat			= commonDecode(xmlParser_getValue($xmlRequest, "makat"));	
	$price			= commonDecode(xmlParser_getValue($xmlRequest, "priceFunction"));	
	$priceValue		= commonDecode(xmlParser_getValue($xmlRequest, "priceValue"));	
	$dimensionId	= commonDecode(xmlParser_getValue($xmlRequest, "dimensionId"));	
	$picSource		= commonDecode(xmlParser_getValue($xmlRequest, "picSource"));	
	$pos			= commonDecode(xmlParser_getValue($xmlRequest, "pos"));	

	if ($priceValue == "") $priceValue = 0;

	list($picWidth, $picHeight, $bgColor) = commonGetDimensionDetails ($dimensionId);

	$fileLoaded = false;

	$fileName	= $variationId . "_" . $productId;

	$suffix 	= "";
	if ($picSource != "")
	{
		$fileLoaded = true;
		$suffix		= commonFileSuffix ($picSource);
	}

	$groupCond	 = "productId = $productId and variationId in (select id from shopVariations where groupId = #groupId#)";

	if ($editType == "update")
	{
		// get curr product variation pos
		$queryStr    = "select pos, groupId, shopProductsVariations.picSource from shopProductsVariations, shopVariations 
						where shopProductsVariations.variationId = shopVariations.id
						and productId = $productId and variationId = $variationId";
		$result	     = commonDoQuery ($queryStr);
		$row	     = commonQuery_fetchRow($result);
		$currPos	 = $row['pos'];
		$groupId	 = $row['groupId'];
		$picSourceDB = $row['picSource'];

		$groupCond	 = str_replace("#groupId#", $groupId, $groupId);

		if ($currPos > $pos)
		{
			$queryStr = "update shopProductsVariations set pos = pos+1 
						 where $groupCond and pos >= $pos and pos < $currPos";
			$result	     = commonDoQuery ($queryStr);
		}

		if ($currPos < $pos)
		{
			$queryStr = "update shopProductsVariations set pos = pos-1 where $groupCond and pos > $currPos and pos <= $pos";
			$result	     = commonDoQuery ($queryStr);
		}

		$queryStr = "update shopProductsVariations set pos			= $pos,
													   price 		= '$price',
													   priceValue	= '$priceValue',
													   makat		= '$makat'";
		if ($fileLoaded)
		{
			$queryStr .= ", picFile	  = '$fileName$suffix',
							picSource = '$picSource' ";								
		}
		else if ($picSource == "")
		{
			$queryStr .= ", picFile	  = '',
							picSource = '' ";								
		}

		$queryStr .= " where productId = $productId and variationId = $variationId";
		commonDoQuery ($queryStr);
	}
	else
	{
		// update others pos
		$queryStr 	= "select groupId from shopVariations where id = $variationId";
		$result		= commonDoQuery ($queryStr);
		$row	    = commonQuery_fetchRow($result);
		$groupId	= $row['groupId'];

		$groupCond	 = str_replace("#groupId#", $groupId, $groupId);

		$queryStr = "update shopProductsVariations set pos = pos+1 where $groupCond and pos >= $pos";
		commonDoQuery ($queryStr);

		if ($fileLoaded)
			$picFile  = "$fileName$suffix";
		else
			$picFile  = "";

		$queryStr = "insert into shopProductsVariations (productId, variationId, pos, makat, price, priceValue, picFile, picSource)
				   	 values ($productId, $variationId, '$pos', '$makat', '$price', '$priceValue', '$picFile', '$picSource')";
		commonDoQuery ($queryStr);
	}

	$filePath = "$ibosHomeDir/html/SWFUpload/files/$userId/";

	if ($fileLoaded)
	{
		$domainRow	= commonGetDomainRow();

		$connId = commonFtpConnect($domainRow); 

		$upload = ftp_put($connId, "productVarFiles/$fileName$suffix", "$filePath/$picSource", FTP_BINARY); 

		if (!$upload) 
		   	echo "FTP upload has failed!";

		$fileName = "${fileName}_size0.jpg";

		if ($picWidth == 0 && $picHeight == 0)
		{
			// keep size
			$upload = ftp_put($connId, "productVarFiles/$fileName", "$filePath/$picSource", FTP_BINARY);
		}
		else
		{
			picsToolsResize("$filePath/$picSource", $suffix, $picWidth, $picHeight, "/../../tmp/$fileName", $bgColor);
		
			$upload = ftp_put($connId, "productVarFiles/$fileName", "/../../tmp/$fileName", FTP_BINARY);
		}

		unlink("$filePath/$picSource");

		commonFtpDisconnect ($connId);
	}
	else if ($editType == "update" && $picSource == "" && $picSourceDB != "")
	{
		deleteProductVariationFiles ($variationId, $productId, $picSourceDB);
	}

 	// delete old files
	commonDeleteOldFiles ($filePath, 3600);	// 1 hour

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteProductVariation																				*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteProductVariation ($xmlRequest)
{
	$productId		= commonDecode(xmlParser_getValue($xmlRequest, "productId"));	
	$variationId	= commonDecode(xmlParser_getValue($xmlRequest, "variationId"));	

	if ($productId == "" || $variationId == "")
		trigger_error ("חסר מזהה לביצוע הפעולה");

	doDeleteProductVariation ($variationId, $productId);

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* doDeleteProductVariation																				*/
/* ----------------------------------------------------------------------------------------------------	*/
function doDeleteProductVariation ($variationId, $productId)
{
	$queryStr	= "select pos, groupId, shopProductsVariations.picSource from shopProductsVariations, shopVariations 
				   where shopProductsVariations.variationId = shopVariations.id
				   and productId = $productId and variationId = $variationId";
	$result		= commonDoQuery($queryStr);
	$row		= commonQuery_fetchRow($result);
	$picSource	= $row['picSource'];
	$pos		= $row['pos'];
	$groupId	= $row['groupId'];

	$queryStr	= "delete from shopProductsVariations where productId = $productId and variationId = $variationId";
	commonDoQuery ($queryStr);

	// update places
	$groupCond	 = "productId = $productId and variationId in (select id from shopVariations where groupId = $groupId)";
	$queryStr 	 = "update shopProductsVariations set pos = pos-1 where $groupCond and pos > $pos";
	commonDoQuery ($queryStr);

	if ($picSource != "")
	{
		deleteProductVariationFiles ($variationId, $productId, $picSource);
	}
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteProductVariationFiles																			*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteProductVariationFiles ($variationId, $productId, $picSource)
{
	if ($picSource == "")
		return;

	$fileName	= $variationId . "_" . $productId;

	$domainRow	= commonGetDomainRow();
	$connId = commonFtpConnect($domainRow); 

	$suffix	= commonFileSuffix ($picSource);

	ftp_delete ($connId, "productVarFiles/$fileName$suffix");
	ftp_delete ($connId, "productVarFiles/${fileName}_size0.jpg");

	commonFtpDisconnect ($connId);

	commonConnectToUserDB ($domainRow);
}

?>
