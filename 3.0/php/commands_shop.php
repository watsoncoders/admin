<?php

include "picsTools.php";

$tags 	= array ("shopPageId", "categorysPageId", "productPageId", "cartPageId", "confirmOrderPageId", "checkOrderPageId", "numProductsInPage", 
			     "widePicWidth1", "widePicHeight1", "widePicWidth2", "widePicHeight2",
				 "longPicWidth1", "longPicHeight1", "longPicWidth2", "longPicHeight2",
				 "wideDimension1", "wideDimension2", "wideDimension3", "wideDimension4",
				 "longDimension1", "longDimension2", "longDimension3", "longDimension4",
				 "picType1", "picType2", "picType3", "picType4", "remark1", "remark2", "remark3", "remark4",
				 "currency1", "currency2", "conversionRate", "handleStock", 
				 "minSupplyDays", "maxSupplyDays", "informEmail", "shopPhone", "vat", "discountPercent");

$langTags = array("name", "shopAddress");

/* ----------------------------------------------------------------------------------------------------	*/
/* initShop																								*/
/* ----------------------------------------------------------------------------------------------------	*/
function initShop ($xmlRequest)
{
	$queryStr    = "select count(*) from shopSpecials";
	$result	     = commonDoQuery ($queryStr);
	$row = commonQuery_fetchRow($result);
			
	$xmlResponse = "<count>$row[0]</count>";

	$xmlResponse .= getShopConfig ($xmlRequest);

	$xmlResponse .= getRemarksFields ($xmlRequest);

	// cats
	include "commands_categories.php";

	$cats	= getCategories ($xmlRequest);
	$cats	= str_replace("items", "cats", $cats);

	$xmlResponse .= $cats;

	// producers
	include "commands_producers.php";

	$producers = getProducers ($xmlRequest);
	$producers = str_replace("items", "producers", $producers);

	$xmlResponse .= $producers;

	// groups
	include "commands_shopVariations.php";

	$groups = getVariationsGroups ($xmlRequest);
	$groups = str_replace("items", "groups", $groups);

	$xmlResponse .= $groups;

	return $xmlResponse;

}

/* ----------------------------------------------------------------------------------------------------	*/
/* getShopConfig																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getShopConfig ($xmlRequest)
{
	global $usedLangs, $tags, $langTags;

	$queryStr = "select * from shopConfig, shopConfig_byLang";
	$result	  = commonDoQuery ($queryStr);

	$langsArray = explode(",",$usedLangs);

	$xmlResponse = "";

	while ($row = commonQuery_fetchRow($result))
	{
		$language = $row['language'];

		$langsArray = commonArrayRemove ($langsArray, $language);	

		if ($xmlResponse == "")
		{
			if ($row['numProductsInPage']  		== "0" ) $row['numProductsInPage']  	= "";
			if ($row['widePicWidth1']  			== "0" ) $row['widePicWidth1']  		= "";
			if ($row['widePicHeight1'] 			== "0" ) $row['widePicHeight1'] 		= "";
			if ($row['widePicWidth2']  			== "0" ) $row['widePicWidth2']  		= "";
			if ($row['widePicHeight2'] 			== "0" ) $row['widePicHeight2'] 		= "";
			if ($row['longPicWidth1']  			== "0" ) $row['longPicWidth1']  		= "";
			if ($row['longPicHeight1'] 			== "0" ) $row['longPicHeight1'] 		= "";
			if ($row['longPicWidth2']  			== "0" ) $row['longPicWidth2']  		= "";
			if ($row['longPicHeight2'] 			== "0" ) $row['longPicHeight2'] 		= "";
			if ($row['minSupplyDays'] 			== "0" ) $row['minSupplyDays'] 			= "";
			if ($row['maxSupplyDays'] 			== "0" ) $row['maxSupplyDays'] 			= "";

			if ($row['wideDimension1'] 			== "0" ) $row['wideDimension1'] 		= "";
			if ($row['wideDimension2'] 			== "0" ) $row['wideDimension2'] 		= "";
			if ($row['wideDimension3'] 			== "0" ) $row['wideDimension3'] 		= "";
			if ($row['wideDimension4'] 			== "0" ) $row['wideDimension4'] 		= "";

			if ($row['longDimension1'] 			== "0" ) $row['longDimension1'] 		= "";
			if ($row['longDimension2'] 			== "0" ) $row['longDimension2'] 		= "";
			if ($row['longDimension3'] 			== "0" ) $row['longDimension3'] 		= "";
			if ($row['longDimension4'] 			== "0" ) $row['longDimension4'] 		= "";

			if ($row['vat'] 					== "0" ) $row['vat'] 					= "";

			if ($row['discountPercent'] 		== "0" ) $row['discountPercent'] 		= "";

			for ($i=0; $i < count($tags); $i++)
			{
				eval ("\$$tags[$i] = \$row['$tags[$i]'];");
			}

			for ($i=0; $i < count($tags); $i++)
			{
				eval ("\$$tags[$i] = commonValidXml(\$$tags[$i]);");

				eval ("\$xmlResponse .= \"<$tags[$i]>\$$tags[$i]</$tags[$i]>\";");
			}
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
/* updateShopConfig																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateShopConfig ($xmlRequest)
{
	global $usedLangs, $tags, $langTags;

	for ($i=0; $i < count($tags); $i++)
	{
		eval ("\$$tags[$i] = commonDecode(xmlParser_getValue(\$xmlRequest,\"$tags[$i]\"));");	
	}

	$vals = Array();

	for ($i=0; $i < count($tags); $i++)
	{
		eval ("array_push (\$vals,\$$tags[$i]);");
	}
	
	// update shopConfig table
	$queryStr = "update shopConfig set ";

	for ($i=0; $i < count($tags); $i++)
	{
		$queryStr .= "$tags[$i] = '$vals[$i]',";
	}

	$queryStr = trim($queryStr, ",");

	commonDoQuery ($queryStr);

	# delete all languages rows
	# ------------------------------------------------------------------------------------------------------
	$queryStr = "delete from shopConfig_byLang";
	commonDoQuery ($queryStr);
	
	# add languages rows for this user
	# ------------------------------------------------------------------------------------------------------
	$langsArray = explode(",",$usedLangs);

	for ($i=0; $i<count($langsArray); $i++)
	{
		$language		= $langsArray[$i];

		$vals = Array();
		for ($j=0; $j < count($langTags); $j++)
		{
			eval ("\$$langTags[$j] = addslashes(commonDecode(xmlParser_getValue(\$xmlRequest,\"$langTags[$j]\$language\")));");	
			eval ("array_push (\$vals,\$$langTags[$j]);");
		}		

		// shop config by lang table
		$queryStr		= "insert into shopConfig_byLang (language," . join(",",$langTags) . ") 
						   values ('$language', '" . join ("','", $vals) . "')";
		commonDoQuery ($queryStr);
	}

	return ("");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getPicTypes																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function getPicTypes ($xmlRequest)
{
	$queryStr = "select * from shopConfig";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);

	$xmlResponse = "<items>";

	$addedPicTypes = false;

	for ($i=1; $i<=4; $i++)
	{
		if ($row['picType' . $i] != "")
		{
			$addedPicTypes = true;

			$name = commonValidXml($row['picType' . $i]);
	
			$xmlResponse .= "<item>
								<id>$i</id>
								<name>$name</name>
							 </item>";
		}
	}

	if (!$addedPicTypes)
	{
		$name = commonPhpEncode("תמונה");

		$xmlResponse .= "<item>
							<id>pic</id>
							<name>$name</name>
						 </item>";
	}

	$name = commonPhpEncode("סרטון");
	$xmlResponse .= 	"<item>
							<id>video</id>
							<name>$name</name>
						 </item>";

	$name = commonPhpEncode("מסמך");
	$xmlResponse .= 	"<item>
							<id>doc</id>
							<name>$name</name>
						 </item>";

	$name = commonPhpEncode("מצגת PDF");
	$xmlResponse .= 	"<item>
							<id>pdf</id>
							<name>$name</name>
						 </item>";

	$xmlResponse .= "</items>";

	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getRemarksFields																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getRemarksFields ($xmlRequest)
{
	$queryStr = "select * from shopConfig";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);

	$remark1 = $row['remark1'];
	$remark2 = $row['remark2'];
	$remark3 = $row['remark3'];
	$remark4 = $row['remark4'];

	if ($remark1 == "") $remark1 = commonPhpEncode("הערה 1");
	else $remark1 = commonValidXml($remark1);

	if ($remark2 == "") $remark2 = commonPhpEncode("הערה 2");
	else $remark2 = commonValidXml($remark2);

	if ($remark3 == "") $remark3 = commonPhpEncode("הערה 3");
	else $remark3 = commonValidXml($remark3);

	if ($remark4 == "") $remark4 = commonPhpEncode("הערה 4");
	else $remark4 = commonValidXml($remark4);

	$xmlResponse = "<remark1>$remark1</remark1>
					<remark2>$remark2</remark2>
					<remark3>$remark3</remark3>
					<remark4>$remark4</remark4>";

	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getProducts																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function getProducts ($xmlRequest)
{	
	global $usedLangs;
	$langsArray = explode(",",$usedLangs);

	$condition  = "";

	$name		= commonDecode(xmlParser_getValue($xmlRequest, "productName"));
	if ($name != "")
		$condition = " and spl.name like '%$name%' ";

	$id		= commonDecode(xmlParser_getValue($xmlRequest, "productId"));
	if ($id != "")
		$condition = " and shopProducts.id = '$id' ";

	$addCategories = "";

	$category 	= xmlParser_getValue($xmlRequest, "category");

	if ($category != "")
	{
		$addCategories = " join categoriesItems spc ";

		$condition .= " and spc.itemId = id and spc.categoryId = $category ";
	}

	$status	 	= xmlParser_getValue($xmlRequest, "status");

	if ($status != "")
	{
		$condition .= " and status = '$status' ";
	}

	$notId = xmlParser_getValue($xmlRequest, "notId");
	if ($notId != "")
	{
		$condition .= " and shopProducts.id != $notId ";
	}

	$producerId	 	= xmlParser_getValue($xmlRequest, "producerId");
	if ($producerId != "")
		$condition .= " and shopProducts.producerId = '$producerId' ";

	$buyOnline	 	= xmlParser_getValue($xmlRequest, "buyOnline");
	if ($buyOnline != "")
		$condition .= " and shopProducts.buyOnline = '$buyOnline' ";

	// get total
	$queryStr	 = "select count(*) from shopProducts $addCategories  
					left join shopProducts_byLang spl on id = spl.productId and language = '$langsArray[0]' 
					where 1	$condition";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$total	     = $row[0];

	// get details
	$queryStr    = "select id, countViews, spl.name, stock, status, featured, shopProducers_byLang.name as producerName, spl.makat as makat
					from shopProducts $addCategories
					left join shopProducts_byLang spl on id = spl.productId and spl.language = '$langsArray[0]'
					left join shopProducers_byLang on shopProducts.producerId = shopProducers_byLang.producerId and 
													  shopProducers_byLang.language = '$langsArray[0]'
					where 1 $condition
					order by id desc " . commonGetLimit ($xmlRequest);
	$result	     = commonDoQuery ($queryStr);

	$numRows    = commonQuery_numRows($result);

	$xmlResponse = "<items>";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		$id   			= $row['id'];
		$status	  		= formatProductStatus($row['status']);
		$featured		= formatFeaturedProduct($row['featured']);
		$name 	  		= commonValidXml ($row['name'],true);
		$producerName  	= commonValidXml ($row['producerName'],true);
		$stock	  		= $row['stock'];
		$makat	  		= commonValidXml($row['makat']);
		$countViews		= (($row['countViews'] == 0) ? "" : $row['countViews']);

		$xmlResponse .=	"<item>
							<productId>$id</productId>
							<status>$status</status>
							<featured>$featured</featured>
							<productName>$name</productName>
							<producerName>$producerName</producerName>
							<stock>$stock</stock>
							<makat>$makat</makat>
							<countViews>$countViews</countViews>
						 </item>";
	}

	$xmlResponse .=	"</items>"												.
					commonGetTotalXml($xmlRequest,$numRows,$total);
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* resetCounters																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function resetCounters ($xmlRequest)
{
	$queryStr = "update shopProducts set countViews = 0";
	commonDoQuery ($queryStr);

	return "";
}


/* ----------------------------------------------------------------------------------------------------	*/
/* getProductNextId																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getProductNextId ($xmlRequest)
{
	$queryStr	= "select max(id) from shopProducts";
	$result		= commonDoQuery ($queryStr);
	$row		= commonQuery_fetchRow ($result);
	$id 		= $row[0] + 1;
	
	return "<productId>$id</productId>";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* addProduct																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function addProduct ($xmlRequest)
{
	return (editProduct ($xmlRequest, "add"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateProduct																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateProduct ($xmlRequest)
{
	editProduct ($xmlRequest, "update");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* doesProductExist																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function doesProductExist ($id)
{
	$queryStr		= "select count(*) from shopProducts where id=$id";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$count	     = $row[0];

	return ($count > 0);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* editProduct																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function editProduct ($xmlRequest, $editType)
{
	global $usedLangs;

	$productId = xmlParser_getValue($xmlRequest, "productId");
		
	if ($productId == "")
		trigger_error ("חסר קוד מוצר לביצוע הפעולה");

	# for later use - RSS
	$domainRow  = commonGetDomainRow ();
	$domainName = commonGetDomainName ($domainRow);
	
	commonConnectToUserDB($domainRow);

	# delete all languages rows
	# ------------------------------------------------------------------------------------------------------
	if ($editType == "update")
	{
		$queryStr = "delete from shopProducts_byLang where productId='$productId'";
		commonDoQuery ($queryStr);
	}
	
	$status			= xmlParser_getValue($xmlRequest, "status");
	$producerId		= xmlParser_getValue($xmlRequest, "producerId");
	$stock			= xmlParser_getValue($xmlRequest, "stock");
	$minSupplyDays	= xmlParser_getValue($xmlRequest, "productMinSupplyDays");
	$maxSupplyDays	= xmlParser_getValue($xmlRequest, "productMaxSupplyDays");
	$dimensions		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "dimensions")));
	$cost1			= xmlParser_getValue($xmlRequest, "cost1");
	$cost2			= xmlParser_getValue($xmlRequest, "cost2");
	$catalogPrice1 	= xmlParser_getValue($xmlRequest, "catalogPrice1");
	$catalogPrice2 	= xmlParser_getValue($xmlRequest, "catalogPrice2");
	$discount1		= xmlParser_getValue($xmlRequest, "discount1");
	$discount2		= xmlParser_getValue($xmlRequest, "discount2");
	$customerPrice1 = xmlParser_getValue($xmlRequest, "customerPrice1");
	$customerPrice2 = xmlParser_getValue($xmlRequest, "customerPrice2");
	$profit1		= xmlParser_getValue($xmlRequest, "profit1");
	$profit2		= xmlParser_getValue($xmlRequest, "profit2");
	$memberPrice1	= xmlParser_getValue($xmlRequest, "memberPrice1");
	$memberPrice2	= xmlParser_getValue($xmlRequest, "memberPrice2");
	$shipmentPrice1	= xmlParser_getValue($xmlRequest, "shipmentPrice1");
	$shipmentPrice2	= xmlParser_getValue($xmlRequest, "shipmentPrice2");
	$specialLink1 	= xmlParser_getValue($xmlRequest, "specialLink1");
	$specialLink2 	= xmlParser_getValue($xmlRequest, "specialLink2");
	$specialLink3 	= xmlParser_getValue($xmlRequest, "specialLink3");
	$specialLink4	= xmlParser_getValue($xmlRequest, "specialLink4");
	$specialLink5 	= xmlParser_getValue($xmlRequest, "specialLink5");
	$featured 		= xmlParser_getValue($xmlRequest, "featured");
	$countViews 	= xmlParser_getValue($xmlRequest, "countViews");
	$bestSeller		= xmlParser_getValue($xmlRequest, "bestSeller");
	$buyOnline		= xmlParser_getValue($xmlRequest, "buyOnline");
	$productDate 	= formatApplToDB(xmlParser_getValue($xmlRequest, "productDate") . " 00:00");

	# ------------------------------------------------------------------------------------------------------
	if ($editType == "add")
	{
		if (doesProductExist($productId))
		{
			trigger_error ("מוצר עם קוד זהה כבר קיים במערכת");
		}
		

		$queryStr = "insert into shopProducts (id, status, producerId, minSupplyDays, maxSupplyDays, dimensions, stock, 
											   cost1, cost2, catalogPrice1, catalogPrice2, discount1, discount2, buyOnline,
											   customerPrice1, customerPrice2, profit1, profit2, memberPrice1, memberPrice2, 
											   shipmentPrice1, shipmentPrice2, 
											   specialLink1, specialLink2, specialLink3, specialLink4, specialLink5, countViews,
											   featured, bestSeller, productDate)
					 values ('$productId', '$status', '$producerId', '$minSupplyDays', '$maxSupplyDays', '$dimensions', '$stock', 
					 		 '$cost1', '$cost2', '$catalogPrice1', '$catalogPrice2', '$discount1', '$discount2', '$buyOnline',
							 '$customerPrice1', '$customerPrice2', '$profit1', '$profit2', '$memberPrice1', '$memberPrice2', 
							 '$shipmentPrice1', '$shipmentPrice2',
							 '$specialLink1', '$specialLink2', '$specialLink3', '$specialLink4', '$specialLink5', 
							 '$countViews', '$featured', '$bestSeller', '$productDate')";

		commonDoQuery ($queryStr);

		$categoryId			= xmlParser_getValue($xmlRequest, "categoryId");
		if ($categoryId != "")
		{
			// get last pos
			$queryStr = "select max(pos) from categoriesItems where categoryId = $categoryId and type = 'shop'";
			$result		= commonDoQuery ($queryStr);
			$row		= commonQuery_fetchRow ($result);
			$pos 		= $row[0] + 1;

			$queryStr = "insert into categoriesItems (itemId, categoryId, type, pos)
						 values ($productId, $categoryId, 'shop', $pos)";
			commonDoQuery ($queryStr);
		}
	}
	else // update
	{
		$queryStr = "update shopProducts set status			= '$status',
											 producerId		= '$producerId',
											 minSupplyDays	= '$minSupplyDays',
											 maxSupplyDays	= '$maxSupplyDays',
											 dimensions		= '$dimensions',
											 stock			= '$stock',
											 cost1 			= '$cost1',
											 cost2 			= '$cost2',
											 catalogPrice1	= '$catalogPrice1',
											 catalogPrice2	= '$catalogPrice2',
											 discount1		= '$discount1',
											 discount2		= '$discount2',
											 customerPrice1	= '$customerPrice1',
											 customerPrice2	= '$customerPrice2',
											 profit1		= '$profit1',
										 	 profit2		= '$profit2',
											 memberPrice1	= '$memberPrice1',
											 memberPrice2	= '$memberPrice2',
											 shipmentPrice1	= '$shipmentPrice1',
											 shipmentPrice2	= '$shipmentPrice2',
											 specialLink1	= '$specialLink1',
											 specialLink2	= '$specialLink2',
											 specialLink3	= '$specialLink3',
											 specialLink4	= '$specialLink4',
											 specialLink5	= '$specialLink5',
											 countViews		= '$countViews',
											 featured		= '$featured',
											 bestSeller		= '$bestSeller',
											 buyOnline		= '$buyOnline',
											 productDate	= '$productDate'
					 where id=$productId";

		commonDoQuery ($queryStr);
	}


	# add languages rows for this product
	# ------------------------------------------------------------------------------------------------------
	$langsArray = explode(",",$usedLangs);

	for ($i=0; $i<count($langsArray); $i++)
	{
		$language			= $langsArray[$i];

		$makat				= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "makat$language")));
		$name				= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "productName$language")));
		$description 		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "description$language")));
		$winTitle			= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "winTitle$language")));
		$metaKeywords		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "metaKeywords$language")));
		$metaDescription	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "metaDescription$language")));
		$remark1	 		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "remark1$language")));
		$remark2	 		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "remark2$language")));
		$remark3	 		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "remark3$language")));
		$remark4	 		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "remark4$language")));
		$specialLinkName1 	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "specialLinkName1$language")));
		$specialLinkName2 	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "specialLinkName2$language")));
		$specialLinkName3 	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "specialLinkName3$language")));
		$specialLinkName4 	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "specialLinkName4$language")));
		$specialLinkName5 	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "specialLinkName5$language")));

		$queryStr		= "insert into shopProducts_byLang (productId, language, makat, name, description, 
															specialLinkName1, specialLinkName2, specialLinkName3, specialLinkName4, specialLinkName5,
															remark1, remark2, remark3, remark4, updated, winTitle, metaKeywords, metaDescription)
						   values ('$productId','$language','$makat', '$name', '$description', 
								   '$specialLinkName1', '$specialLinkName2', '$specialLinkName3', '$specialLinkName4', '$specialLinkName5',
				   				   '$remark1', '$remark2', '$remark3', '$remark4', now(), '$winTitle', '$metaKeywords', '$metaDescription')";
	
		commonDoQuery ($queryStr);

//		$file = fopen ("$domainName/productsRSS.php?lang=$language","r");
//		fclose ($file);

	}

	# delete and add links of this product to the passed categories
	# ------------------------------------------------------------------------------------------------------
/*	$queryStr = "delete from categoriesItems where itemId='$productId'";
	commonDoQuery ($queryStr);

	$categories = xmlParser_getValues ($xmlRequest, "id");

	for ($i=0; $i<count($categories); $i++)
	{
		$queryStr = "insert into categoriesItems (itemId, categoryId) values ('$productId', '$categories[$i]')";
		commonDoQuery ($queryStr);
	}
*/

	$queryStr	= "delete from shopAttachProducts where productId = $productId";
	commonDoQuery ($queryStr);

	$otherProducts		= trim(xmlParser_getValue($xmlRequest, "otherProducts"));

	if ($otherProducts != "")
	{
		$otherProducts = explode(" ", $otherProducts);

		foreach ($otherProducts as $otherProduct)
		{
			$queryStr	= "insert into shopAttachProducts (productId, otherProductId) values ($productId, $otherProduct)";
			commonDoQuery ($queryStr);
		}
	}

	return "<productId>$productId</productId>";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* duplicateProduct																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function duplicateProduct ($xmlRequest)
{
	global $usedLangs;

	$langsArray 		= explode(",",$usedLangs);

	$productId 			= xmlParser_getValue($xmlRequest, "productId");
	$copyFiles 			= xmlParser_getValue($xmlRequest, "copyFiles");
	$copyAttachProducts = xmlParser_getValue($xmlRequest, "copyAttachProducts");
		
	if ($productId == "")
		trigger_error ("חסר קוד מוצר לביצוע הפעולה");

	$queryStr	= "select max(id) from shopProducts";
	$result		= commonDoQuery ($queryStr);
	$row		= commonQuery_fetchRow ($result);
	$id 		= $row[0] + 1;
	
	// shopProducts + shopProducts_byLang
	commonDuplicateRowTable ($id, $productId, "shopProducts", 		 "id");
	commonDuplicateRowTable ($id, $productId, "shopProducts_byLang", "productId");

	// update product details by langs
	for ($i=0; $i<count($langsArray); $i++)
	{
		$language			= $langsArray[$i];
		$name				= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "productName$language")));
		$makat				= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "makat$language")));

		$queryStr	= "update shopProducts_byLang set name 	= '$name',
													  makat	= '$makat'
					   where productId = $id and language = '$language'";
		commonDoQuery ($queryStr);
	}
	
	// shopProductsFiles + shopProductsFiles_byLang
	$oldFilesNames = array();
	$newFilesNames = array();
	if ($copyFiles == "1")
	{
		$queryStr   = "select max(fileId) from shopProductsFiles";
		$result		= commonDoQuery ($queryStr);
		$row		= commonQuery_fetchRow ($result);
		$fileId 	= $row[0];

		$queryStr	= "select * from shopProductsFiles where productId = $productId";
	   	$result		= commonDoQuery($queryStr);

		while ($row = commonQuery_fetchRow($result))
		{
			$fileId++;

			$file		= str_replace("${productId}_$row[fileId]_size0", "${id}_${fileId}_size0", $row['file']);

			array_push ($oldFilesNames, array($row['fileType'],$row['file']));
			array_push ($newFilesNames, $file);

			$row['sourceFile'] = addslashes($row['sourceFile']);

			$queryStr	= "insert into shopProductsFiles (fileId, fileType, picType, productId, file, sourceFile)
						   values ($fileId, '$row[fileType]', '$row[picType]', $id, '$file', '$row[sourceFile]')";
			commonDoQuery ($queryStr);

			$queryStr	= "insert into shopProductsFiles_byLang (fileId, language, fileText) 
						   select $fileId, language, fileText from shopProductsFiles_byLang where fileId = $row[fileId]";
			commonDoQuery ($queryStr);
		}	

		// get pics dimensions
		$picSizeQuery	= "select wideDimension1, wideDimension2, wideDimension3, wideDimension4 
		        	       from shopConfig";
		$resultPicSize	= commonDoQuery ($picSizeQuery);
		$picSizeRow		= commonQuery_fetchRow ($resultPicSize);
	}

	// categories
	$queryStr	= "select categoryId from categoriesItems where itemId = $productId and type = 'shop'";
	$result		= commonDoQuery($queryStr);

	while ($row = commonQuery_fetchRow($result))
	{
		$categoryId			= $row['categoryId'];

		// get last pos
		$queryStr = "select max(pos) from categoriesItems where categoryId = $categoryId and type = 'shop'";
		$result		= commonDoQuery ($queryStr);
		$row		= commonQuery_fetchRow ($result);
		$pos 		= $row[0] + 1;

		$queryStr = "insert into categoriesItems (itemId, categoryId, type, pos) values ($id, $categoryId, 'shop', $pos)";
		commonDoQuery ($queryStr);
	}

	// attach products
	if ($copyAttachProducts == "1")
	{
		commonDuplicateRowTable ($id, $productId, "shopAttachProducts", "productId");
	}

	// product variations
	$queryStr = "select * from shopVariationsGroups";
	$result	  = commonDoQuery($queryStr);

	$oldVarFilesNames = array();
	$newVarFilesNames = array();

	while ($row = commonQuery_fetchRow($result))
	{
		$groupId			= $row['id'];
		$copyVariations	 	= xmlParser_getValue($xmlRequest, "copyVariations$groupId");

		if ($copyVariations == "1")
		{
			$queryStr	= "select variationId, price, priceValue, makat, pos, picFile, picSource 
						   from shopProductsVariations 
						   where productId = $productId and variationId in (select id from shopVariations where groupId = $groupId)";
	   		$result2	= commonDoQuery($queryStr);

			while ($varRow = commonQuery_fetchRow($result2))
			{
				$file	= "";

				if ($varRow['picFile'] != "")
				{
					$file	= str_replace("$varRow[variationId]_$productId", "$varRow[variationId]_$id", $varRow['picFile']);

					array_push ($oldVarFilesNames, $varRow['picFile']);
					array_push ($newVarFilesNames, $file);
				}

				$varRow['picSource'] = addslashes($varRow['picSource']);

				$queryStr	= "insert into shopProductsVariations (productId, variationId, price, priceValue, makat, pos, picFile, picSource)
							   values ($id, '$varRow[variationId]', '$varRow[price]', '$varRow[priceValue]', '$varRow[makat]', '$varRow[pos]', 
									   '$file', '$varRow[picSource]')";
				commonDoQuery ($queryStr);
			}
		}
	}

	// create ftp connection
	$domainRow = commonGetDomainRow ();

	$connId    = commonFtpConnect    ($domainRow);

	// copy files
	if ($copyFiles == "1")
	{
		for ($i = 0; $i < count($newFilesNames); $i++)
		{
			$oldFile = $oldFilesNames[$i][1];
			$newFile = $newFilesNames[$i];

			$upload  = ftp_get($connId, "/../../tmp/$newFile", "shopFiles/$oldFile", FTP_BINARY);
			$upload  = ftp_put($connId, "shopFiles/$newFile", "/../../tmp/$newFile", FTP_BINARY);

			if ($oldFilesNames[$i][0] != "pic") continue;

			$suffix		= commonFileSuffix ($oldFile);

			$oldFile	= str_replace("$suffix", "jpg", $oldFile);
			$newFile	= str_replace("$suffix", "jpg", $newFile);

			// size1
			$oldFile	= str_replace("size0",	"size1", $oldFile);
			$newFile	= str_replace("size0",	"size1", $newFile);
			if ($picSizeRow['wideDimension1'] != 0)
			{
				$upload  	= ftp_get($connId, "/../../tmp/$newFile", "shopFiles/$oldFile", FTP_BINARY);
				$upload  	= ftp_put($connId, "shopFiles/$newFile", "/../../tmp/$newFile", FTP_BINARY);
			}

			// size2
			$oldFile	= str_replace("size1",	"size2", $oldFile);
			$newFile	= str_replace("size1",	"size2", $newFile);
			if ($picSizeRow['wideDimension2'] != 0)
			{
				$upload  	= ftp_get($connId, "/../../tmp/$newFile", "shopFiles/$oldFile", FTP_BINARY);
				$upload  	= ftp_put($connId, "shopFiles/$newFile", "/../../tmp/$newFile", FTP_BINARY);
			}

			// size3
			$oldFile	= str_replace("size2",	"size3", $oldFile);
			$newFile	= str_replace("size2",	"size3", $newFile);
			if ($picSizeRow['wideDimension3'] != 0)
			{
				$upload  	= ftp_get($connId, "/../../tmp/$newFile", "shopFiles/$oldFile", FTP_BINARY);
				$upload  	= ftp_put($connId, "shopFiles/$newFile", "/../../tmp/$newFile", FTP_BINARY);
			}

			// size4
			$oldFile	= str_replace("size3",	"size4", $oldFile);
			$newFile	= str_replace("size3",	"size4", $newFile);
			if ($picSizeRow['wideDimension4'] != 0)
			{
				$upload  	= ftp_get($connId, "/../../tmp/$newFile", "shopFiles/$oldFile", FTP_BINARY);
				$upload  	= ftp_put($connId, "shopFiles/$newFile", "/../../tmp/$newFile", FTP_BINARY);
			}
		}
	}

	// copy product variations files
	if (count($oldVarFilesNames) != 0)
	{
		for ($i = 0; $i < count($newVarFilesNames); $i++)
		{
			$oldFile = $oldVarFilesNames[$i];
			$newFile = $newVarFilesNames[$i];

			$upload  = ftp_get($connId, "/../../tmp/$newFile", "productVarFiles/$oldFile", FTP_BINARY);
			$upload  = ftp_put($connId, "productVarFiles/$newFile", "/../../tmp/$newFile", FTP_BINARY);
			
			// size0
			$suffix		= commonFileSuffix ($oldFile);

			$oldFile	= str_replace(".$suffix",	"_size0.jpg", $oldFile);
			$newFile	= str_replace(".$suffix",	"_size0.jpg", $newFile);
			$upload  	= ftp_get($connId, "/../../tmp/$newFile", "productVarFiles/$oldFile", FTP_BINARY);
			$upload  	= ftp_put($connId, "productVarFiles/$newFile", "/../../tmp/$newFile", FTP_BINARY);
		}
	}

	ftp_close ($connId);

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getProductDetails																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function getProductDetails ($xmlRequest)
{
	global $usedLangs;

	$productId = xmlParser_getValue($xmlRequest, "productId");
		
	if ($productId == "")
		trigger_error ("חסר קוד מוצר לביצוע הפעולה");

	$queryStr = "select shopProducts.*, language, spl.name, spl.description, spl.makat as makatByLang,
						spl.specialLinkName1, spl.specialLinkName2, spl.specialLinkName3, spl.specialLinkName4, spl.specialLinkName5,
						spl.remark1, spl.remark2, spl.remark3, spl.remark4,
						spl.winTitle, spl.metaKeywords, spl.metaDescription
				 from shopProducts left join shopProducts_byLang spl on id=spl.productId
				 where id='$productId'";
	$result	  = commonDoQuery($queryStr);

	if (commonQuery_numRows($result) == 0)
		trigger_error ("מוצר זה ($productId) לא קיים במערכת. לא ניתן לבצע את העדכון");

	$langsArray = explode(",",$usedLangs);

	$xmlResponse = "";

	while ($row = commonQuery_fetchRow($result))
	{
		$language = $row['language'];

		$langsArray = commonArrayRemove ($langsArray, $language);	

		if ($xmlResponse == "")
		{
			$status				= $row['status'];
			$producerId			= $row['producerId'];
			$minSupplyDays 		= $row['minSupplyDays'];
			$maxSupplyDays		= $row['maxSupplyDays'];
			$dimensions			= commonValidXml($row['dimensions']);
			$stock				= $row['stock'];
			$cost1				= $row['cost1'];
			$cost2				= $row['cost2'];
			$catalogPrice1		= $row['catalogPrice1'];
			$catalogPrice2		= $row['catalogPrice2'];
			$discount1			= $row['discount1'];
			$discount2			= $row['discount2'];
			$customerPrice1		= $row['customerPrice1'];
			$customerPrice2		= $row['customerPrice2'];
			$profit1			= $row['profit1'];
			$profit2			= $row['profit2'];
			$memberPrice1		= $row['memberPrice1'];
			$memberPrice2		= $row['memberPrice2'];
			$shipmentPrice1		= $row['shipmentPrice1'];
			$shipmentPrice2		= $row['shipmentPrice2'];
			$specialLink1		= commonValidXml($row['specialLink1']);
			$specialLink2		= commonValidXml($row['specialLink2']);
			$specialLink3		= commonValidXml($row['specialLink3']);
			$specialLink4		= commonValidXml($row['specialLink4']);
			$specialLink5		= commonValidXml($row['specialLink5']);
			$countViews			= $row['countViews'];
			$featured			= $row['featured'];
			$bestSeller			= $row['bestSeller'];
			$buyOnline			= $row['buyOnline'];
			$productDate		= formatApplDate($row['productDate']);
			
			if ($featured == "") $featured = "0";

			$xmlResponse = "<productId>$productId</productId>
							<status>$status</status>
							<producerId>$producerId</producerId>
							<productMinSupplyDays>$minSupplyDays</productMinSupplyDays>
							<productMaxSupplyDays>$maxSupplyDays</productMaxSupplyDays>
							<dimensions>$dimensions</dimensions>
							<stock>$stock</stock>
							<cost1>$cost1</cost1>
							<cost2>$cost2</cost2>
							<catalogPrice1>$catalogPrice1</catalogPrice1>
							<catalogPrice2>$catalogPrice2</catalogPrice2>
							<discount1>$discount1</discount1>
							<discount2>$discount2</discount2>
							<customerPrice1>$customerPrice1</customerPrice1>
							<customerPrice2>$customerPrice2</customerPrice2>
							<profit1>$profit1</profit1>
							<profit2>$profit2</profit2>
							<memberPrice1>$memberPrice1</memberPrice1>
							<memberPrice2>$memberPrice2</memberPrice2>
							<shipmentPrice1>$shipmentPrice1</shipmentPrice1>
							<shipmentPrice2>$shipmentPrice2</shipmentPrice2>
							<specialLink1>$specialLink1</specialLink1>
							<specialLink2>$specialLink2</specialLink2>
							<specialLink3>$specialLink3</specialLink3>
							<specialLink4>$specialLink4</specialLink4>
							<specialLink5>$specialLink5</specialLink5>
							<countViews>$countViews</countViews>
							<featured>$featured</featured>
							<bestSeller>$bestSeller</bestSeller>
							<buyOnline>$buyOnline</buyOnline>
							<productDate>$productDate</productDate>
							<copyAttachProducts>1</copyAttachProducts>
							<copyFiles>1</copyFiles>
							<copyVariations1>1</copyVariations1>
							<copyVariations2>1</copyVariations2>
							<copyVariations3>1</copyVariations3>
							<copyVariations4>1</copyVariations4>";
		}

		$makat				= commonValidXml($row['makatByLang']);
		$name				= commonValidXml($row['name']);
		$description		= commonValidXml($row['description']);
		$winTitle			= commonValidXml($row['winTitle']);
		$metaKeywords		= commonValidXml($row['metaKeywords']);
		$metaDescription	= commonValidXml($row['metaDescription']);
		$remark1			= commonValidXml($row['remark1']);
		$remark2			= commonValidXml($row['remark2']);
		$remark3			= commonValidXml($row['remark3']);
		$remark4			= commonValidXml($row['remark4']);
		$specialLinkName1	= commonValidXml($row['specialLinkName1']);
		$specialLinkName2	= commonValidXml($row['specialLinkName2']);
		$specialLinkName3	= commonValidXml($row['specialLinkName3']);
		$specialLinkName4	= commonValidXml($row['specialLinkName4']);
		$specialLinkName5	= commonValidXml($row['specialLinkName5']);

		$xmlResponse   .= "<makat$language>$makat</makat$language>
						   <productName$language>$name</productName$language>
						   <description$language>$description</description$language>
						   <winTitle$language>$winTitle</winTitle$language>
						   <metaKeywords$language>$metaKeywords</metaKeywords$language>
						   <metaDescription$language>$metaDescription</metaDescription$language>
						   <remark1$language>$remark1</remark1$language>
						   <remark2$language>$remark2</remark2$language>
						   <remark3$language>$remark3</remark3$language>
						   <remark4$language>$remark4</remark4$language>
						   <specialLinkName1$language>$specialLinkName1</specialLinkName1$language>
						   <specialLinkName2$language>$specialLinkName2</specialLinkName2$language>
						   <specialLinkName3$language>$specialLinkName3</specialLinkName3$language>
						   <specialLinkName4$language>$specialLinkName4</specialLinkName4$language>
						   <specialLinkName5$language>$specialLinkName5</specialLinkName5$language>";

		$xmlResponse   .= getShopConfig ($xmlRequest);
	}

	// add missing languages
	// ------------------------------------------------------------------------------------------------
	for ($i=0; $i<count($langsArray); $i++)
	{
		$language	  = $langsArray[$i];

		$xmlResponse   .= "<makat$language><![CDATA[]]></makat$language>
						   <productName$language><![CDATA[]]></productName$language>
						   <producer$language><![CDATA[]]></producer$language>
						   <description$language><![CDATA[]]></description$language>
						   <winTitle$language><![CDATA[]]></winTitle$language>
						   <metaKeywords$language><![CDATA[]]></metaKeywords$language>
						   <metaDescription$language><![CDATA[]]></metaDescription$language>
						   <remark1$language><![CDATA[]]></remark1$language>
						   <remark2$language><![CDATA[]]></remark2$language>
						   <remark3$language><![CDATA[]]></remark3$language>
						   <remark4$language><![CDATA[]]></remark4$language>
						   <specialLinkName1$language><![CDATA[]]></specialLinkName1$language>
						   <specialLinkName2$language><![CDATA[]]></specialLinkName2$language>
						   <specialLinkName3$language><![CDATA[]]></specialLinkName3$language>
						   <specialLinkName4$language><![CDATA[]]></specialLinkName4$language>
						   <specialLinkName5$language><![CDATA[]]></specialLinkName5$language>";
	}

	// add attach products
	$queryStr	= "select otherProductId from shopAttachProducts where productId = $productId";
	$result		= commonDoQuery($queryStr);

	$otherProducts = "";

	while ($row = commonQuery_fetchRow($result))
	{
		$otherProducts .= " " . $row['otherProductId'];
	}

	$otherProducts = trim($otherProducts);

	$xmlResponse .= "<otherProducts>$otherProducts</otherProducts>
					 <productIds>$otherProducts</productIds>";

	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteProduct																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteProduct ($xmlRequest)
{
	$productIds = xmlParser_getValues ($xmlRequest, "productId");

	if (count($productIds) == 0)
		trigger_error ("חסר קוד מוצר לביצוע הפעולה");

	include "commands_shopVariations.php";

	foreach ($productIds as $productId)
	{
		// check if this is a special product
		$queryStr = "select count(*) from shopSpecials where productId = $productId";
		$result   = commonDoQuery ($queryStr);
		$row	  = commonQuery_fetchRow($result);

		if ($row[0] > 0)
			continue;
//			trigger_error ("לא ניתן למחוק מוצר שמופיע כמוצר נבחר");

		$queryStr =  "delete from shopProducts where id=$productId";
		commonDoQuery ($queryStr);

		$queryStr =  "delete from shopProducts_byLang where productId=$productId";
		commonDoQuery ($queryStr);

		$queryStr =  "delete from shopProductsFiles_byLang where fileId in (select fileId from shopProductsFiles where productId=$productId)";
		commonDoQuery ($queryStr);

		$queryStr =  "delete from shopProductsFiles where productId=$productId";
		commonDoQuery ($queryStr);

		$queryStr =  "delete from categoriesItems where itemId=$productId and type='shop'";
		commonDoQuery ($queryStr);
	
		$queryStr	= "delete from shopAttachProducts where productId = $productId or otherProductId = $productId";
		commonDoQuery ($queryStr);

		$queryStr	= "select variationId, picSource from shopProductsVariations where productId=$productId";
		$result		= commonDoQuery ($queryStr);

		$queryStr	= "delete from shopProductsVariations where productId=$productId";
		commonDoQuery ($queryStr);

		while ($row = commonQuery_fetchRow($result))
		{
			if ($row['picSource'] != "")
				deleteProductVariationFiles ($row['variationId'], $productId, $row['picSource']);
		}
	}

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getProductFiles																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getProductFiles ($xmlRequest)
{	
	global $usedLangs;
	$langsArray = explode(",",$usedLangs);

	$productId = xmlParser_getValue ($xmlRequest, "productId");

	if ($productId == "")
		trigger_error ("חסר קוד מוצר לביצוע הפעולה");

	// get pic types
	$queryStr = "select picType1, picType2, picType3, picType4 from shopConfig";
	$result	  = commonDoQuery ($queryStr);
	$picTypes = commonQuery_fetchRow($result);

	// get total
	$queryStr	 = "select count(*) from shopProductsFiles where productId=$productId";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$total	     = $row[0];

	// get details
	$queryStr    = "select shopProductsFiles.fileId as fileId, fileType, picType, file, sourceFile, fileText
					from shopProductsFiles
					left join shopProductsFiles_byLang on shopProductsFiles.fileId = shopProductsFiles_byLang.fileId and language = '$langsArray[0]'
					where productId=$productId
					order by shopProductsFiles.fileId " . commonGetLimit ($xmlRequest);
	$result	     = commonDoQuery ($queryStr);

	$numRows     = commonQuery_numRows($result);


	$xmlResponse = "<items>";

	$domainRow   = commonGetDomainRow ();
	$filePrefix  = commonGetDomainName($domainRow) . "/shopFiles/";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		$fileId  	 = $row['fileId'];
		$fileText  	 = commonValidXml ($row['fileText'],true);
		$picType  	 = $row['picType'];
		$fileType  	 = $row['fileType'];

		if ($picType != "0")
		{
			$fileTypeText = commonValidXml($picTypes['picType' . $picType]);
		}
		else
		{
			$fileTypeText = formatFileType($fileType);
		}
		$file	 	 = commonValidXml ($row['file'], true);
		$sourceFile	 = commonCData(commonEncode($row['sourceFile']));

		$fullFileName = $filePrefix . urlencode($row['file']);
		$fullFileName = commonValidXml($fullFileName);

		$xmlResponse .=	"<item>
							 <fileId>$fileId</fileId>
							 <fileText>$fileText</fileText>
							 <fileType>$fileType</fileType>
							 <fileTypeText>$fileTypeText</fileTypeText>
							 <file>$file</file>
							 <sourceFile>$sourceFile</sourceFile>
							 <fullFileName>$fullFileName</fullFileName>
						 </item>";
	}

	$xmlResponse .=	"</items>"												.
					commonGetTotalXml($xmlRequest,$numRows,$total);
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getProductFileDetails																				*/
/* ----------------------------------------------------------------------------------------------------	*/
function getProductFileDetails ($xmlRequest)
{
	global $usedLangs;
	$langsArray = explode(",",$usedLangs);

	$fileId		= xmlParser_getValue($xmlRequest, "fileId");

	if ($fileId == "")
		trigger_error ("חסר קוד קובץ לביצוע הפעולה");

	$queryStr	= "select shopProductsFiles.fileId as fileId, fileType, picType, file, sourceFile, fileText, 
					      shopProductsFiles_byLang.language as language, 
						  shopProductsFiles.productId as productId, shopProducts_byLang.name as productName
					from shopProductsFiles
					left join shopProductsFiles_byLang on shopProductsFiles.fileId = shopProductsFiles_byLang.fileId
					left join shopProducts_byLang      on shopProductsFiles.productId = shopProducts_byLang.productId and 
													      shopProducts_byLang.language = '$langsArray[0]'
					where shopProductsFiles.fileId=$fileId";
	$result		= commonDoQuery ($queryStr);

	if (commonQuery_numRows($result) == 0)
		trigger_error ("קובץ קוד זה ($fileId) לא קיימת במערכת. לא ניתן לבצע את העדכון");

	$xmlResponse = "";

	while ($row = commonQuery_fetchRow($result))
	{
		$language = $row['language'];

		$langsArray = commonArrayRemove ($langsArray, $language);	

		if ($xmlResponse == "")
		{
			$fileId  		 = $row['fileId'];
			$fileType	  	 = $row['fileType'];
			$picType	  	 = $row['picType'];

			if ($picType != "0")
				$fileType = $picType;

			$fileTypeText 	= formatFileType($fileType);
			$sourceFile	 	= commonValidXml($row['sourceFile']);
			$productId	 	= $row['productId'];
			$productIdText	= commonValidXml ($row['productName'],true);
			

			$xmlResponse  =	"<fileId>$fileId</fileId>
							 <fileType>$fileType</fileType>
							 <fileTypeText>$fileTypeText</fileTypeText>
							 <sourceFile>$sourceFile</sourceFile>
							 <formSourceFile>$sourceFile</formSourceFile>
							 <productId>$productId</productId>
							 <productIdText>$productIdText</productIdText>
							 <usedLangs>$usedLangs</usedLangs>";
		}

		$fileText			= commonValidXml($row['fileText']);

		$xmlResponse   .= "<fileText$language>$fileText</fileText$language>";
	}

	// add missing languages
	// ------------------------------------------------------------------------------------------------
	for ($i=0; $i<count($langsArray); $i++)
	{
		$language	  = $langsArray[$i];

		$xmlResponse .=	   "<fileText$language></fileText$language>";
	}

	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* addProductFile																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function addProductFile ($xmlRequest)
{
	return (editProductFile ($xmlRequest, "add"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateProductFile																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateProductFile ($xmlRequest)
{
	editProductFile ($xmlRequest, "update");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* editProductFile																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function editProductFile ($xmlRequest, $editType)
{
	global $usedLangs;
	global $userId;
	global $ibosHomeDir;
	
	$fileId		= xmlParser_getValue($xmlRequest, "fileId");
	$fileType	= xmlParser_getValue($xmlRequest, "fileType");
	$productId	= xmlParser_getValue($xmlRequest, "productId");

	// handle picture 
	# ------------------------------------------------------------------------------------------------------
	$sourceFile		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "file")));	

	$fileLoaded  	= false;

	$suffix 		= "";
	if ($sourceFile != "")
	{
		$fileLoaded = true;
		$suffix		= commonFileSuffix ($sourceFile);
	}

	$picType = "";
	if ($fileType != "video" && $fileType != "pdf" && $fileType != "doc")
	{
		$picType  = $fileType;
		$fileType = "pic";
	}

	if ($editType == "add")
	{
		$queryStr   = "select max(fileId) from shopProductsFiles";
		$result		= commonDoQuery ($queryStr);
		$row		= commonQuery_fetchRow ($result);
		$fileId 	= $row[0] + 1;

		$file	= $productId . "_" . $fileId . "_size0" . $suffix;

		$queryStr   = "insert into shopProductsFiles (fileId, fileType, picType, productId, file, sourceFile)
					   values ('$fileId', '$fileType', '$picType', '$productId', '$file', '$sourceFile')";
		commonDoQuery ($queryStr);
	}
	else
	{
		$queryStr = "update shopProductsFiles set fileType = '$fileType',
			   									  picType  = '$picType'	";

		if ($fileLoaded)
		{
			$file	= $productId . "_" . $fileId . "_size0" . $suffix;
		
			$queryStr .= ", file = '$file', sourceFile = '$sourceFile'";
		}

		$queryStr .= " where fileId=$fileId";
	
		commonDoQuery ($queryStr);
	}

	# add languages rows for this fileId
	# --------------------------------------------------------------------------------------------------
	$langsArray = explode(",",$usedLangs);

	for ($i=0; $i<count($langsArray); $i++)
	{
		$language	= $langsArray[$i];

		$fileText 	= addslashes(xmlParser_getValue($xmlRequest, "fileText$language"));

		$queryStr		= "replace into shopProductsFiles_byLang (fileId, language, fileText) values ('$fileId','$language','$fileText')";
		commonDoQuery ($queryStr);
	}

	if ($fileType == "pic")
	{
		// get user request image size
		$picSizeQuery	= "select wideDimension1, wideDimension2, wideDimension3, wideDimension4, 
						   		  longDimension1, longDimension2, longDimension3, longDimension4
			               from shopConfig";
		$resultPicSize	= commonDoQuery ($picSizeQuery);
		$picSizeRow		= commonQuery_fetchRow ($resultPicSize);

		$dims			= array();
		$sql			= "select * from dimensions";
		$result 		= commonDoQuery($sql);

		while ($dimRow = commonQuery_fetchRow($result))
		{
			$dims[$dimRow['id']] = $dimRow;
		}
	}
	 
	// handle file
	$filePath = "$ibosHomeDir/html/SWFUpload/files/$userId";

	if ($fileLoaded)
	{
		$domainRow	= commonGetDomainRow();

		$connId = commonFtpConnect($domainRow); 

		ftp_chdir ($connId, "shopFiles");

		$upload = ftp_put($connId, $file, "$filePath/$sourceFile", FTP_BINARY);

		if ($fileType == "pic")
		{
			list($width_orig, $height_orig) = getimagesize("$filePath/$sourceFile");

			$file = str_replace($suffix, ".jpg", $file);

			for ($i = 1; $i <= 4; $i++)
			{
				$resizedFileName = str_replace("size0", "size$i", $file);

				$toLoadResize = false;

				if ($width_orig >= $height_orig) 
				{
					$dim = $picSizeRow["wideDimension$i"];

					if ($dim == 0)
						$dim = $picSizeRow["longDimension$i"];
				}
				else
				{
					$dim = $picSizeRow["longDimension$i"];
	
					if ($dim == 0)
						$dim = $picSizeRow["wideDimension$i"];
				}

				if ($dim == 0) continue;

				// get dim details
				$dimRow = $dims[$dim];

				if ($dimRow['width'] != 0 || $dimRow['height'] != 0)
				{
					if ($dimRow['color'] == "") $dimRow['color'] = "#FFFFFF";

					picsToolsResize("$filePath/$sourceFile", $suffix, $dimRow['width'], $dimRow['height'],
								   "/../../tmp/$resizedFileName", $dimRow['color']);

					$toLoadResize = true;
				} 
	
				if ($toLoadResize)
				{
					$upload = ftp_put($connId, $resizedFileName, "/../../tmp/$resizedFileName", FTP_BINARY);
				}
			}
		}

		unlink("$filePath/$sourceFile");

		commonFtpDisconnect ($connId);
	}

 	// delete old files
	commonDeleteOldFiles ($filePath, 3600);	// 1 hour
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteProductFile																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteProductFile ($xmlRequest)
{
	$fileId	= xmlParser_getValue($xmlRequest, "fileId");
	$file	= xmlParser_getValue($xmlRequest, "file");

	if ($fileId == "" || $file == "")
		trigger_error ("חסרים פרטי קובץ לביצוע הפעולה");

	// get file type
	$queryStr  = "select fileType, file from shopProductsFiles where fileId=$fileId";
	$result		= commonDoQuery ($queryStr);
	$row		= commonQuery_fetchRow ($result);
	$fileType	= $row[0];
	$file		= $row[1];

	if ($fileType == "pic")
	{
		$picSizeQuery	= "select widePicWidth1, widePicHeight1, widePicWidth2, widePicHeight2, 
								  longPicWidth1, longPicHeight1, longPicWidth2, longPicHeight2 from shopConfig";
		$resultPicSize	= commonDoQuery ($picSizeQuery);
		$picSizeRow		= commonQuery_fetchRow ($resultPicSize);
	}

	$queryStr  = "delete from shopProductsFiles where fileId=$fileId";
	commonDoQuery ($queryStr);

	$queryStr  = "delete from shopProductsFiles_byLang where fileId=$fileId";
	commonDoQuery ($queryStr);

	$domainRow = commonGetDomainRow ();

	$connId    = commonFtpConnect	($domainRow);
	
	commonFtpDelete ($connId, "shopFiles/$file");

	if ($fileType == "pic")
	{
		// delete all sizes of this file
		for ($i = 1; $i <= 2; $i++)
		{
			$sizeFile = str_replace("size0","size$i",$file);

			if (ftp_size ($connId, "shopFiles/$sizeFile") != -1)	// check if file exits
				commonFtpDelete ($connId, "shopFiles/$sizeFile");
		}
	}

   	ftp_close  ($connId);

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getOrders																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function getOrders ($xmlRequest)
{	
	$sortBy		= xmlParser_getValue($xmlRequest,"sortBy");
	if ($sortBy == "")
		$sortBy = "orderNumber";

	$sortDir	= xmlParser_getValue($xmlRequest,"sortDir");
	if ($sortDir == "")
		$sortDir = "desc";

	$condition  = "";

	$queryStr 	= "select checkOrderPageId from shopConfig";
	$result	  	= commonDoQuery ($queryStr);
	$configRow	= commonQuery_fetchRow($result);

	$status		= xmlParser_getValue($xmlRequest, "status");
	$orderNumber= xmlParser_getValue($xmlRequest, "orderNumber");
	$details	= xmlParser_getValue($xmlRequest, "details");

	if ($status != "")
		$condition .= " and status = '$status' ";
		
	if ($orderNumber != "")
	{
		$condition .= " and orderNumber = '$orderNumber' ";
	}
		
	if ($details != "")
	{
		$like = " like '%$details%'";

		$condition .= " and (orderFirstName $like or orderLastName $like or orderAddress $like or email $like or
						     sendFirstName  $like or sendLastName  $like or sendAddress  $like) ";
	}

	// by from & to date
	$fromDate = xmlParser_getValue($xmlRequest, "fromDate");
	if ($fromDate != "")
	{
		$fromDate = formatApplToDB ("$fromDate 00:00");
		$condition .= " and orderDatetime >= '$fromDate' ";
	}

	$toDate   = xmlParser_getValue($xmlRequest, "toDate");
	if ($toDate != "")
	{
		$toDate = formatApplToDB ("$toDate 23:59");
		$condition .= "and orderDatetime <= '$toDate' ";
	}
	
	// get total
	$queryStr	 = "select count(*) from orders where orderType='product' $condition";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$total	     = $row[0];

	// get details
	$queryStr    = "select orderNumber, checkOrderCode, status, orderDatetime, suppliedDatetime,
						   orderFirstName, orderLastName
					from orders
					where orderType='product' $condition
					order by $sortBy $sortDir " . commonGetLimit ($xmlRequest);
	$result	     = commonDoQuery ($queryStr);

	$numRows    = commonQuery_numRows($result);

	$xmlResponse = "<items>";

	$domainRow   = commonGetDomainRow ();
	$siteUrl     = commonGetDomainName($domainRow);

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		$orderNumber   		= $row['orderNumber'];
		$status				= $row['status'];
		$statusText			= formatOrderStatus($status);
		$orderDatetime		= formatApplDateTime ($row['orderDatetime']);
		$suppliedDatetime	= formatApplDateTime ($row['suppliedDatetime']);
		$orderFullName		= commonValidXml ($row['orderFirstName']." ".$row['orderLastName'], true);

		$checkOrderCode  	= commonPhpEncode("<a href='$siteUrl/index2.php?id=$configRow[checkOrderPageId]&checkOrderCode=$row[checkOrderCode]' 
												  target='_blank' title='לחיצה להצגת דף מעקב הזמנה'>$row[checkOrderCode]</a>");


		$xmlResponse .=	"<item>"														.
							"<orderNumber>$orderNumber</orderNumber>"	 				. 
							"<checkOrderCode>$checkOrderCode</checkOrderCode>"			. 
							"<status>$status</status>"									. 
							"<statusText>$statusText</statusText>"						. 
							"<orderDatetime>$orderDatetime</orderDatetime>"				.
							"<suppliedDatetime>$suppliedDatetime</suppliedDatetime>"	.
							"<fullname>$orderFullName</fullname>".
						"</item>";
	}

	$xmlResponse .=	"</items>"												.
					commonGetTotalXml($xmlRequest,$numRows,$total);
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getOrderDetails																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getOrderDetails ($xmlRequest)
{
	$orderNumber =  xmlParser_getValue($xmlRequest, "orderNumber");

	if ($orderNumber == "")
		trigger_error ("חסר קוד הזמנה לביצוע הפעולה");

	$queryStr = "select * from orders where orderNumber='$orderNumber'";
	$result		= commonDoQuery ($queryStr);

	if (commonQuery_numRows($result) == 0)
		trigger_error ("הזמנה קוד זה ($id) לא קיימת במערכת. לא ניתן לבצע את העדכון");

	$row = commonQuery_fetchRow($result);

	$orderNumber	= $row['orderNumber'];
	$status			= $row['status'];
	$phone1			= $row['phone1'];
	$phone2			= $row['phone2'];
	$sendIsOrder	= $row['sendIsOrder'];
	$asPresent		= $row['asPresent'];
	$payMethod		= $row['payMethod'];
	$ccHolderTZ		= $row['ccHolderTZ'];
	$ccType			= $row['ccType'];
	$ccNumber		= $row['ccNumber'];
	$ccExpireDate	= $row['ccExpireDate'];
	$ccNumber		= $row['ccNumber'];
	$ccCvv			= $row['ccCvv'];
	$shipmentId		= $row['shipmentId'];
	$shipmentNumber	= $row['shipmentNumber'];
	$shipmentAgent	= $row['shipmentAgent'];
	$orderFirstName	= commonValidXml ($row['orderFirstName'],true);
	$orderLastName	= commonValidXml ($row['orderLastName'],true);
	$orderAddress	= commonValidXml ($row['orderAddress'],true);
	$sendFirstName	= commonValidXml ($row['sendFirstName'],true);
	$sendLastName	= commonValidXml ($row['sendLastName'],true);
	$sendAddress	= commonValidXml ($row['sendAddress'],true);
	$email			= commonValidXml ($row['email']);
	$remarks		= commonValidXml ($row['remarks'],true);
	$dedication		= commonValidXml ($row['dedication'],true);
	$ccHolderName	= commonValidXml ($row['ccHolderName'],true);

	if ($ccCvv == "0") $ccCvv = "";

	$xmlResponse = "<orderNumber>$orderNumber</orderNumber>
				    <status>$status</status>
				    <orderFirstName>$orderFirstName</orderFirstName>
				    <orderLastName>$orderLastName</orderLastName>
				    <phone1>$phone1</phone1>
				    <phone2>$phone2</phone2>
				    <orderAddress>$orderAddress</orderAddress>
				    <email>$email</email>
				    <sendIsOrder>$sendIsOrder</sendIsOrder>
				    <shipmentId>$shipmentId</shipmentId>
				    <shipmentNumber>$shipmentNumber</shipmentNumber>
				    <shipmentAgent>$shipmentAgent</shipmentAgent>
				    <sendFirstName>$sendFirstName</sendFirstName>
				    <sendLastName>$sendLastName</sendLastName>
				    <sendAddress>$sendAddress</sendAddress>
				    <remarks>$remarks</remarks>
				    <asPresent>$asPresent</asPresent>
				    <dedication>$dedication</dedication>
				    <payMethod>$payMethod</payMethod>
				    <ccHolderName>$ccHolderName</ccHolderName>
				    <ccHolderTZ>$ccHolderTZ</ccHolderTZ>
				    <ccType>$ccType</ccType>
				    <ccNumber>$ccNumber</ccNumber>
				    <ccExpireDate>$ccExpireDate</ccExpireDate>
				    <ccCvv>$ccCvv</ccCvv>";
				
	$domainRow = commonGetDomainRow();

	commonConnectToUserDB($domainRow);

	if ($domainRow['id'] == 410)
	{
		// get coupon details
		if ($row['coupon'] != 0)
		{
			$queryStr	= "select * from whiskyil_coupons where id = $row[coupon]";
			$result		= commonDoQuery ($queryStr);
			$row		= commonQuery_fetchRow($result);

			$coupon		= "קופון $row[code] - הנחה של $row[value] ";

			if ($row['type'] == "amount")
				$coupon .= "ש\"ח";
			else
				$coupon .= "%";

			$coupon = commonPhpEncode ($coupon);

			$xmlResponse .= "<coupon>$coupon</coupon>";

		}
	}

	return ($xmlResponse);

}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateOrder																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateOrder ($xmlRequest)
{
	$orderNumber 	= xmlParser_getValue($xmlRequest, "orderNumber");

	if ($orderNumber == "")
		trigger_error ("חסר קוד הזמנה לביצוע הפעולה");

	# get curr shipmentId and order status
	$queryStr 		= "select status, shipmentId from orders where orderNumber='$orderNumber'";
	$result			= commonDoQuery ($queryStr);
	$row 			= commonQuery_fetchRow($result);

	$currStatus		= $row['status'];
	$currShipmentId = $row['shipmentId'];

	$status			= xmlParser_getValue($xmlRequest, "status");
	$phone1 		= xmlParser_getValue($xmlRequest, "phone1");
	$phone2 		= xmlParser_getValue($xmlRequest, "phone2");
	$sendIsOrder 	= xmlParser_getValue($xmlRequest, "sendIsOrder");
	$asPresent 		= xmlParser_getValue($xmlRequest, "asPresent");
	$payMethod 		= xmlParser_getValue($xmlRequest, "payMethod");
	$ccHolderTZ 	= xmlParser_getValue($xmlRequest, "ccHolderTZ");
	$ccType 		= xmlParser_getValue($xmlRequest, "ccType");
	$ccNumber 		= xmlParser_getValue($xmlRequest, "ccNumber");
	$ccExpireDate 	= xmlParser_getValue($xmlRequest, "ccExpireDate");
	$ccCvv 			= xmlParser_getValue($xmlRequest, "ccCvv");
	$shipmentId 	= xmlParser_getValue($xmlRequest, "shipmentId");
	$shipmentNumber = xmlParser_getValue($xmlRequest, "shipmentNumber");
	$shipmentAgent 	= xmlParser_getValue($xmlRequest, "shipmentAgent");
	$orderFirstName = commonDecode(xmlParser_getValue($xmlRequest, "orderFirstName"));
	$orderLastName 	= commonDecode(xmlParser_getValue($xmlRequest, "orderLastName"));
	$orderAddress 	= commonDecode(xmlParser_getValue($xmlRequest, "orderAddress"));
	$sendFirstName 	= commonDecode(xmlParser_getValue($xmlRequest, "sendFirstName"));
	$sendLastName 	= commonDecode(xmlParser_getValue($xmlRequest, "sendLastName"));
	$sendAddress 	= commonDecode(xmlParser_getValue($xmlRequest, "sendAddress"));
	$email 			= xmlParser_getValue($xmlRequest, "email");
	$remarks	 	= commonDecode(xmlParser_getValue($xmlRequest, "remarks"));
	$dedication 	= commonDecode(xmlParser_getValue($xmlRequest, "dedication"));
	$ccHolderName 	= commonDecode(xmlParser_getValue($xmlRequest, "ccHolderName"));

	$addToQuery		= "";

	if ($currStatus == "1" && $status == "2")
	{
		$addToQuery .= ", suppliedDatetime = now() ";
	}

	if ($currShipmentId != $shipmentId)
	{
		$queryStr 	   = "select price from shopShipments where id='$shipmentId'";
		$result	       = commonDoQuery ($queryStr);
		$row	       = commonQuery_fetchRow($result);
		$shipmentPrice = $row['price'];

		$addToQuery	.= ", shipmentPrice = '$shipmentPrice' ";
	}
	
	$queryStr = "update orders set  status 			= '$status',
										phone1 			= '$phone1',
										phone2 			= '$phone2',
										sendIsOrder 	= '$sendIsOrder',
										asPresent 		= '$asPresent',
										payMethod 		= '$payMethod',
										ccHolderTZ		= '$ccHolderTZ',
										ccType 			= '$ccType',
										ccNumber		= '$ccNumber',
										ccExpireDate 	= '$ccExpireDate',
										ccCvv			= '$ccCvv',
										shipmentId		= '$shipmentId',
										shipmentNumber	= '$shipmentNumber',
										shipmentAgent	= '$shipmentAgent',
										orderFirstName 	= '$orderFirstName',
										orderLastName 	= '$orderLastName',
										orderAddress 	= '$orderAddress',
										sendFirstName	= '$sendFirstName',
										sendLastName 	= '$sendLastName',
										sendAddress		= '$sendAddress',
										email 			= '$email',
										remarks 		= '$remarks',
										dedication 		= '$dedication',
										ccHolderName 	= '$ccHolderName'
										$addToQuery
				  where orderNumber='$orderNumber'";

	commonDoQuery ($queryStr);

	return ("");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteOrder																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteOrder ($xmlRequest)
{	
	$orderNumber 	= xmlParser_getValue($xmlRequest, "orderNumber");

	if ($orderNumber == "")
		trigger_error ("חסר קוד הזמנה לביצוע הפעולה");

	$queryStr = "delete from orders where orderNumber=$orderNumber";
	commonDoQuery ($queryStr);

	$queryStr = "delete from orderItems where orderNumber=$orderNumber";
	commonDoQuery ($queryStr);

	return ("");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getOrderTotals																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getOrderTotals ($xmlRequest)
{	
	$orderNumber= xmlParser_getValue($xmlRequest, "orderNumber");

	if ($orderNumber == "")
		trigger_error ("חסר קוד הזמנה לביצוע הפעולה");

	// get details
	$queryStr    = "select status, currency, so.discountPercent, orderDatetime, suppliedDatetime, shipmentId, shipmentPrice,
						   sum(soi.quantity) as countProducts, sum(soi.price) as totalPrice
					from orders so left join orderItems soi 	  on so.orderNumber = soi.orderNumber
					where so.orderNumber='$orderNumber'
					group by so.orderNumber";

	$result	= commonDoQuery ($queryStr);


	$row = commonQuery_fetchRow($result);
		
	$statusText			= formatOrderStatus($row['status']);
	$currencyCode		= $row['currency'];
	$currency			= formatCurrency($row['currency'],true);
	$orderDatetime		= formatApplDateTime ($row['orderDatetime']);
	$suppliedDatetime	= formatApplDateTime ($row['suppliedDatetime']);
	$shipmentPrice		= $row['shipmentPrice'];
	$shipmentText		= getShipmentText ($row['shipmentId'], "HEB");
	$totalPrice			= $row['totalPrice'];
	$countProducts		= $row['countProducts'];

	if ($totalPrice == "") $totalPrice = "0";

	$discountPercent	= (($row['discountPercent'] == "") ? 0 : $row['discountPercent']);

	$totalWithDiscount 	= $totalPrice * (100-$discountPercent) / 100;

	$totalPrice			= formatAmount($totalPrice);
	$totalWithDiscount	= formatAmount($totalWithDiscount);
	$shipmentPrice		= formatAmount($shipmentPrice);

	$totalPriceInclude	= formatAmount($totalWithDiscount + $shipmentPrice);

	// get price id by currency
	$queryStr   = "select currency1 from shopConfig";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);

	if ($row['currency1'] == $currencyCode)
		$priceId = "1";
	else
		$priceId = "2";

	$xmlResponse 		=	"<orderNumber>$orderNumber</orderNumber>"					.
							"<statusText>$statusText</statusText>"						. 
							"<currency>$currency</currency>"							. 
							"<priceId>$priceId</priceId>"								. 
							"<orderDatetime>$orderDatetime</orderDatetime>"				.
							"<suppliedDatetime>$suppliedDatetime</suppliedDatetime>"	.
							"<shipmentPrice>$shipmentPrice</shipmentPrice>"				.
							"<shipmentText>$shipmentText</shipmentText>"				.
							"<countProducts>$countProducts</countProducts>"				.
							"<totalPrice>$totalPrice</totalPrice>"						.
							"<discountPercent>$discountPercent%</discountPercent>"		.
							"<totalWithDiscount>$totalWithDiscount</totalWithDiscount>" .
							"<totalPriceInclude>$totalPriceInclude</totalPriceInclude>";
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getOrderProducts																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getOrderProducts ($xmlRequest)
{	
	global $usedLangs;
	$langsArray = explode(",",$usedLangs);

	$orderNumber= xmlParser_getValue($xmlRequest, "orderNumber");

	if ($orderNumber == "")
		trigger_error ("חסר קוד הזמנה לביצוע הפעולה");


	// get num products
	$queryStr	 = "select count(*) from orderItems where orderNumber='$orderNumber'";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$total	 	 = $row[0];

	// get details
	$queryStr    = "select soi.id as id, soi.itemId as productId, spl.name as name, price, quantity
					from orderItems soi 
					left join shopProducts_byLang spl on soi.itemId = spl.productId and language = '$langsArray[0]'
					where soi.orderNumber = '$orderNumber'";
	$result	     = commonDoQuery ($queryStr);

	$numRows     = commonQuery_numRows($result);

	$xmlResponse = "<items>";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		$id		 	= $row['id'];
		$productId 	= $row['productId'];
		$name 	  	= commonValidXml ($row['name'],true);
		$price	  	= $row['price'];
		$quantity	= $row['quantity'];

		$xmlResponse .=	"<item>"											.
							"<orderNumber>$orderNumber</orderNumber>"		.
							"<id>$id</id>"									.
							"<productId>$productId</productId>"	 			. 
							"<name>$name</name>"							. 
							"<price>$price</price>"							. 
							"<quantity>$quantity</quantity>"				.
						"</item>";
	}

	$xmlResponse .=	"</items>"												.
					commonGetTotalXml($xmlRequest,$numRows,$total);
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getProductPrice																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getProductPrice ($xmlRequest)
{	
	$productId = xmlParser_getValue($xmlRequest, "productId");
	$priceId   = xmlParser_getValue($xmlRequest, "priceId");

	if ($priceId != "1" && $priceId != "2")
		trigger_error ("מחיר מוצר לפי מטבע לא קיים");
	
	$queryStr  = "select cost$priceId, customerPrice$priceId, catalogPrice$priceId from shopProducts where id='$productId'";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);

	$xmlResponse = "<cost>$row[0]</cost>"		.
				   "<price>$row[1]</price>"		.
				   "<catalogPrice>$row[2]</catalogPrice>";

	return ($xmlResponse);

}

/* ----------------------------------------------------------------------------------------------------	*/
/* addOrderProduct																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function addOrderProduct ($xmlRequest)
{	
	editOrderProduct ($xmlRequest, "add");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateOrderProduct																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateOrderProduct ($xmlRequest)
{	
	editOrderProduct ($xmlRequest, "update");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* editOrderProduct																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function editOrderProduct ($xmlRequest, $editType)
{	
	$price	     = xmlParser_getValue($xmlRequest, "price");
	$quantity    = xmlParser_getValue($xmlRequest, "quantity");

	if ($editType == "add")
	{
		$orderNumber = xmlParser_getValue($xmlRequest, "orderNumber");

		if ($orderNumber == "")
			trigger_error ("חסר קוד הזמנה לביצוע הפעולה");

		$productId   = xmlParser_getValue($xmlRequest, "productId");

		if ($productId == "")
			trigger_error ("חסר קוד מוצר לביצוע הפעולה");

		$queryStr = "insert into orderItems (orderNumber, itemId, price, quantity)
					 values ('$orderNumber', '$productId', '$price', '$quantity')";
	}
	else
	{
		$id			 = xmlParser_getValue($xmlRequest, "id");

		if ($id == "")
			trigger_error ("חסר קוד קישור בין מוצר להזמנה בביצוע הפעולה");

		$queryStr = "update orderItems set price    = '$price',
						   quantity = '$quantity'
						   where id='$id'";
	}
	commonDoQuery ($queryStr);

	return ("");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteOrderProduct																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteOrderProduct ($xmlRequest)
{	
	$id			 = xmlParser_getValue($xmlRequest, "id");

	if ($id == "")
		trigger_error ("חסר קוד קישור בין מוצר להזמנה בביצוע הפעולה");

	$queryStr = "delete from orderItems where id=$id";

	commonDoQuery ($queryStr);

	return ("");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getShipmentText 																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getShipmentText ($shipmentId, $language)
{
	$shipmentText = "";

	if ($shipmentId != "")
	{
		$queryStr 	  = "select name from shopShipments_byLang where shipmentId='$shipmentId' and language='$language'";
		$result	      = commonDoQuery ($queryStr);
		$row	      = commonQuery_fetchRow($result);
		$shipmentText = $row['name'];
		
	}

	return (commonValidXml ($shipmentText));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getShipments																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function getShipments ($xmlRequest)
{	
	global $usedLangs;
	$langsArray = explode(",",$usedLangs);

	$condition = " status != 'deleted'";

	$status		= xmlParser_getValue($xmlRequest, "status");

	if ($status != "")
		$condition = " status = '$status' ";

	// get total
	$queryStr	 = "select count(*) from shopShipments where $condition";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$total	     = $row[0];

	// get details
	$queryStr    = "select id, price, freeByAmount, freeByQuantity, status, name, description
					from shopShipments
					left join shopShipments_byLang on id = shipmentId and language = '$langsArray[0]'
					where $condition 
					order by id " . commonGetLimit ($xmlRequest);
	$result	     = commonDoQuery ($queryStr);

	$numRows    = commonQuery_numRows($result);

	$xmlResponse = "<items>";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		$id   			= $row['id'];
		$price	  		= $row['price'];
		$freeByAmount	= $row['freeByAmount'];
		$freeByQuantity	= $row['freeByQuantity'];
		$status			= formatActiveStatus($row['status']);
		$name 	  		= commonValidXml ($row['name'],true);
		$description  	= commonValidXml ($row['description'],true);

		if ($freeByAmount   == "0") $freeByAmount   = "";
		if ($freeByQuantity == "0") $freeByQuantity = "";
		
		$xmlResponse .=	"<item>"												.
							"<id>$id</id>"	 									. 
							"<price>$price</price>"								. 
							"<freeByAmount>$freeByAmount</freeByAmount>"		. 
							"<freeByQuantity>$freeByQuantity</freeByQuantity>"	. 
							"<status>$status</status>"							. 
							"<name>$name</name>"								. 
							"<description>$description</description>"			. 
						"</item>";
	}

	$xmlResponse .=	"</items>"												.
					commonGetTotalXml($xmlRequest,$numRows,$total);
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getShipmentDetails																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function getShipmentDetails ($xmlRequest)
{
	global $usedLangs;

	$id = xmlParser_getValue($xmlRequest, "id");
		
	if ($id == "")
		trigger_error ("חסר קוד משלוח לביצוע הפעולה");

	$queryStr = "select shopShipments.*, language, name, description, mustEnterShipmentCode 
				 from shopShipments left join shopShipments_byLang on shopShipments.id=shopShipments_byLang.shipmentId
				 where id='$id'";
	$result	  = commonDoQuery($queryStr);

	if (commonQuery_numRows($result) == 0)
		trigger_error ("משלוח זה ($id) לא קיים במערכת. לא ניתן לבצע את העדכון");

	$langsArray = explode(",",$usedLangs);

	$xmlResponse = "";

	while ($row = commonQuery_fetchRow($result))
	{
		$language = $row['language'];

		$langsArray = commonArrayRemove ($langsArray, $language);	

		if ($xmlResponse == "")
		{
			$price						= $row['price'];
			$freeByAmount				= $row['freeByAmount'];
			$freeByQuantity				= $row['freeByQuantity'];
			$mustEnterShipmentCode 		= $row['mustEnterShipmentCode'];
			$status						= $row['status'];
			
			if ($freeByAmount   == "0") $freeByAmount   = "";
			if ($freeByQuantity == "0") $freeByQuantity = "";
		
			$xmlResponse  =	"<id>$id</id>"											.	 
							"<price>$price</price>"									.
							"<freeByAmount>$freeByAmount</freeByAmount>"			.
							"<freeByQuantity>$freeByQuantity</freeByQuantity>"		.
							"<mustEnterShipmentCode>$mustEnterShipmentCode</mustEnterShipmentCode>" .
							"<status>$status</status>";
		}

		$name				= commonValidXml($row['name']);
		$description		= commonValidXml($row['description']);

		$xmlResponse   .= "<name$language>$name</name$language>"											.
						  "<description$language>$description</description$language>";
	}

	// add missing languages
	// ------------------------------------------------------------------------------------------------
	for ($i=0; $i<count($langsArray); $i++)
	{
		$language	  = $langsArray[$i];

		$xmlResponse   .= "<name$language></name$language>"								.
						  "<description$language></description$language>";
	}

	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* addShipment																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function addShipment ($xmlRequest)
{
	return (editShipment ($xmlRequest, "add"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateShipment																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateShipment ($xmlRequest)
{
	editShipment ($xmlRequest, "update");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* doesShipmentExist																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function doesShipmentExist ($id)
{
	$queryStr		= "select count(*) from shopShipments where id=$id";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$count	     = $row[0];

	return ($count > 0);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* editShipment																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function editShipment ($xmlRequest, $editType)
{
	global $usedLangs;

	# delete all languages rows
	# ------------------------------------------------------------------------------------------------------
	if ($editType == "update")
	{
		$id = xmlParser_getValue($xmlRequest, "id");
		
		if ($id == "")
			trigger_error ("חסר קוד מוצר לביצוע הפעולה");

		$queryStr = "delete from shopShipments_byLang where shipmentId='$id'";
		commonDoQuery ($queryStr);
	}
	
	$price						= xmlParser_getValue($xmlRequest, "price");
	$freeByAmount				= xmlParser_getValue($xmlRequest, "freeByAmount");
	$freeByQuantity				= xmlParser_getValue($xmlRequest, "freeByQuantity");
	$status 					= xmlParser_getValue($xmlRequest, "status");
	$mustEnterShipmentCode 		= xmlParser_getValue($xmlRequest, "mustEnterShipmentCode");

	# ------------------------------------------------------------------------------------------------------
	if ($editType == "add")
	{
		$queryStr = "insert into shopShipments (price, freeByAmount, freeByQuantity, mustEnterShipmentCode, status)
					 values ('$price', '$freeByAmount', '$freeByQuantity', '$mustEnterShipmentCode', '$status')";

		commonDoQuery ($queryStr);
		
		# get new shipment id
		# --------------------------------------------------------------------------------------------------
		$queryStr 	 = "select max(id) from shopShipments";
		$result	     = commonDoQuery ($queryStr);
		$row	     = commonQuery_fetchRow($result);
		$id		  	 = $row[0];
	}
	else // update
	{
		$queryStr = "update shopShipments set price			= '$price',
											 freeByAmount 	= '$freeByAmount',
											 freeByQuantity = '$freeByQuantity',
											 mustEnterShipmentCode = '$mustEnterShipmentCode',
											 status			= '$status'
					 where id=$id";

		commonDoQuery ($queryStr);
	}


	# add languages rows for this shipment
	# ------------------------------------------------------------------------------------------------------
	$langsArray = explode(",",$usedLangs);

	for ($i=0; $i<count($langsArray); $i++)
	{
		$language			= $langsArray[$i];

		$name				= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "name$language")));
		$description 		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "description$language")));

		$queryStr		= "insert into shopShipments_byLang (shipmentId, language, name, description)
						   values ('$id','$language','$name', '$description')";
	
		commonDoQuery ($queryStr);
	}

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteShipment																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteShipment ($xmlRequest)
{
	$id = xmlParser_getValue ($xmlRequest, "id");

	if ($id == "")
		trigger_error ("חסר קוד משלוח לביצוע הפעולה");

	// Logic delete
	$queryStr = "update shopShipments set status='deleted' where id=$id";
	commonDoQuery ($queryStr);

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getSpecailsCount																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getSpecailsCount ($xmlRequest)
{	
	$queryStr    = "select count(*) from shopSpecials";
	$result	     = commonDoQuery ($queryStr);
	$row = commonQuery_fetchRow($result);
			
	return "<count>$row[0]</count>";
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getShopSpecails																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getShopSpecails ($xmlRequest)
{	
	$queryStr    = "select * from shopSpecials order by id";
	$result	     = commonDoQuery ($queryStr);

	$numRows    = commonQuery_numRows($result);

	$xmlResponse = "<items>";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		$id   			= $row['id'];
		$description  	= commonValidXml ($row['description'],true);
		$productId 		= $row['productId'];
		
		if ($productId == 0) $productId = "";

		$xmlResponse .=	"<item>
							<id>$id</id>
							<productId>$productId</productId>
							<description>$description</description>
						</item>";
	}

	$xmlResponse .=	"</items>";
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateShopSpecails																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateShopSpecails ($xmlRequest)
{
	$queryStr    = "select id from shopSpecials";
	$result	     = commonDoQuery ($queryStr);

	while ($row = commonQuery_fetchRow($result))
	{
		$productId = xmlParser_getValue($xmlRequest, "special$row[id]");

		if ($productId == "") $productId = 0;

		$queryStr = "update shopSpecials set productId = $productId
					 where id=$row[id]";

		commonDoQuery ($queryStr);
	}

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* excelOrdersReport																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function excelOrdersReport ($xmlRequest)
{
	global $usedLangs;

	$langsArray = explode(",",$usedLangs);

	$domainRow = commonGetDomainRow();
	commonConnectToUserDB ($domainRow);

	$now	 = commonPrepareToFile(commonPhpEncode("תאריך הפקת הדוח:") . date("d/m/Y H:i"));

	$excel		 = "";

	$condition  = "";

	$status		= xmlParser_getValue($xmlRequest, "status");
	if ($status != "")
		$condition .= " and status = '$status' ";
		
	$orderNumber= xmlParser_getValue($xmlRequest, "orderNumber");
	if ($orderNumber != "")
	{
		$condition .= " and orderNumber = '$orderNumber' ";
	}
		
	$details	= xmlParser_getValue($xmlRequest, "details");
	if ($details != "")
	{
		$like = " like '%$details%'";

		$condition .= " and (orderFirstName $like or orderLastName $like or orderAddress $like or email $like or
						     sendFirstName  $like or sendLastName  $like or sendAddress  $like) ";
	}

	$fromDate = xmlParser_getValue($xmlRequest, "fromDate");
	if ($fromDate != "")
	{
		$fromDate = formatApplToDB ("$fromDate 00:00");
		$condition .= " and orderDatetime >= '$fromDate' ";
	}

	$toDate   = xmlParser_getValue($xmlRequest, "toDate");
	if ($toDate != "")
	{
		$toDate = formatApplToDB ("$toDate 23:59");
		$condition .= "and orderDatetime <= '$toDate' ";
	}
	
	// get total
	$queryStr	 = "select count(*) as totalItems from orders, orderItems 
					where orders.orderNumber = orderItems.orderNumber and orders.orderType='product' $condition
					group by orders.orderNumber
					order by totalItems desc";
	$result	     = commonDoQuery ($queryStr);

	$maxItems	 = 0;

	if (commonQuery_numRows($result) == 0)
		trigger_error ("לא נמצאו הזמנות מתאימות");

	if (commonQuery_numRows($result) != 0)
	{
		$row	     = commonQuery_fetchRow($result);
		$maxItems	 = $row[0];
	}

	$itemsCols 		= "";
	$itemHeaders	= "";

	$addIndex 		= " ss:Index=\"6\"";

	for ($i = 0; $i < $maxItems; $i++)
	{
		$itemsCols	.= "<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"30\"/>
					    <Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"200\"/>
					    <Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"40\"/>";

		$itemHeaders .= "<Cell ss:StyleID=\"sHeader\" $addIndex><Data ss:Type=\"String\">כמות</Data></Cell>
						 <Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">שם מוצר</Data></Cell>
						 <Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">מחיר</Data></Cell>";
		$addIndex	= "";
	}

	$merge		= 4 + 3*$maxItems;
	$itemsMerge	= 3*$maxItems - 1;

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
							<Alignment ss:Horizontal=\"Center\" ss:Vertical=\"Bottom\"/>
							<Font x:Family=\"Swiss\" ss:Color=\"#0E3966\" ss:Size=\"16\" ss:Bold=\"1\"/>
					  	</Style>
  						<Style ss:ID=\"sSubTitle\">
							<Alignment ss:Horizontal=\"Center\" ss:Vertical=\"Bottom\"/>
							<Font x:Family=\"Swiss\" ss:Color=\"#53B2E8\" ss:Size=\"14\" ss:Bold=\"1\"/>
					  	</Style>
  						<Style ss:ID=\"sInTitle\">
							<Alignment ss:Horizontal=\"Right\" ss:Vertical=\"Bottom\"/>
							<Font x:Family=\"Swiss\" ss:Color=\"#53B2E8\" ss:Size=\"14\" ss:Bold=\"1\"/>
			   				<Borders>
		    					<Border ss:Position=\"Bottom\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
			   				</Borders>
					  	</Style>
  						<Style ss:ID=\"sReportDate\">
							<Alignment ss:Horizontal=\"Center\" ss:Vertical=\"Bottom\"/>
							<Font x:Family=\"Swiss\" ss:Color=\"#ABABAB\" ss:Size=\"12\" ss:Bold=\"1\"/>
					  	</Style>
  						<Style ss:ID=\"sTotal\">
							<Alignment ss:Horizontal=\"Center\" ss:Vertical=\"Bottom\"/>
							<Font x:Family=\"Swiss\" ss:Color=\"#333300\" ss:Size=\"13\" ss:Bold=\"1\"/>
					  	</Style>
 			 			<Style ss:ID=\"sHeader\">
			   				<Alignment ss:Horizontal=\"Center\" ss:Vertical=\"Center\"/>
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
					<Worksheet ss:Name=\"דוח הזמנות\" ss:RightToLeft=\"1\">
					<Table x:FullColumns=\"1\" x:FullRows=\"1\">
   	        		<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"60\"/>
   	        		<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"60\"/>
   	        		<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"100\"/>
   	        		<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"100\"/>
   	        		<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"60\"/>
					$itemsCols
					<Row>
						<Cell ss:MergeAcross=\"$merge\" ss:StyleID=\"sTitle\"><Data ss:Type=\"String\">דוח הזמנות</Data></Cell>
					</Row>
					<Row>
						<Cell ss:MergeAcross=\"$merge\" ss:StyleID=\"sReportDate\"><Data ss:Type=\"String\">$now</Data></Cell>
					</Row>
					<Row>
						<Cell ss:StyleID=\"sHeader\" ss:MergeDown=\"1\"><Data ss:Type=\"String\">מספר הזמנה</Data></Cell>
						<Cell ss:StyleID=\"sHeader\" ss:MergeDown=\"1\"><Data ss:Type=\"String\">סטטוס הזמנה</Data></Cell>
						<Cell ss:StyleID=\"sHeader\" ss:MergeDown=\"1\"><Data ss:Type=\"String\">תאריך הזמנה</Data></Cell>
						<Cell ss:StyleID=\"sHeader\" ss:MergeDown=\"1\"><Data ss:Type=\"String\">שם המזמין</Data></Cell>
						<Cell ss:StyleID=\"sHeader\" ss:MergeDown=\"1\"><Data ss:Type=\"String\">קוד חבר</Data></Cell>
						<Cell ss:StyleID=\"sHeader\" ss:MergeAcross=\"$itemsMerge\"><Data ss:Type=\"String\">מוצרים</Data></Cell>
					</Row>
					<Row>
						$itemHeaders
					</Row>";

	$queryStr    = "select orderNumber, status, orderDatetime, orderFirstName, orderLastName, memberId
					from orders
					where orderType='product' $condition
					order by orderNumber desc";
	$result		= commonDoQuery($queryStr);

	while ($row = commonQuery_fetchRow($result))
	{
		$orderNumber	= $row['orderNumber'];
		$memberId		= $row['memberId'];
		$orderName 		= commonPrepareToFile(stripslashes("$row[orderFirstName] $row[orderLastName]"));
		$orderDatetime	= formatApplDateTime ($row['orderDatetime']);
		$status	  		= $row['status'];

		switch ($status)
		{
			case "1"	: $status = "התקבלה"; break;
			case "2"	: $status = "סופקה"; break;
			case "3"	: $status = "הוחזרה"; break;
			case "4"	: $status = "בוטלה"; break;
		}

		$excel .= "<Row ss:Height=\"13.5\">
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"Number\">$orderNumber</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$status</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$orderDatetime</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$orderName</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"Number\">$memberId</Data></Cell>";

		$queryStr    = "select shopProducts_byLang.name, orderItems.quantity, orderItems.price
						from orderItems, shopProducts_byLang 
						where orderItems.orderNumber = '$orderNumber'
						and orderItems.itemId = shopProducts_byLang.productId and shopProducts_byLang.language = '$langsArray[0]' ";
		$inResult	= commonDoQuery ($queryStr);

		$itemsCols  = $maxItems * 3;

		while ($inRow = commonQuery_fetchRow($inResult))
		{
			$quantity	= $inRow['quantity'];
			$product	= commonPrepareToFile(stripslashes($inRow['name']));
			$price		= $inRow['price'];

			$excel	.= "<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"Number\">$quantity</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$product</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"Number\">$price</Data></Cell>";

			$itemsCols = $itemsCols - 3; 
		}

		if ($itemsCols != 0)
		{
			$itemsCols--;
			$excel	.= "<Cell ss:StyleID=\"sCell\" ss:MergeAcross=\"$itemsCols\"><Data ss:Type=\"String\"></Data></Cell>";
		}

		$excel .= "</Row>";
	}

	$excel .= 	 "</Table>
				</Worksheet>
			</Workbook>";

	return (commonDoExcel($excel));
}

?>
