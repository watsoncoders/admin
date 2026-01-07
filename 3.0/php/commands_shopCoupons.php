<?php

/* ----------------------------------------------------------------------------------------------------	*/
/* getCoupons																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function getCoupons ($xmlRequest)
{	
	$conditions = "";

	$sortBy		= xmlParser_getValue($xmlRequest,"sortBy");

	if ($sortBy == "")
		$sortBy = "shopCoupons.id";

	$sortDir	= xmlParser_getValue($xmlRequest,"sortDir");
	if ($sortDir == "")
		$sortDir = "desc";

	$type		= xmlParser_getValue($xmlRequest, "type");
	if ($type != "")
		$conditions = " and type = '$type' ";

	$code		= xmlParser_getValue($xmlRequest, "code");
	if ($code != "")
		$conditions = " and code = '$code' ";

	// get total
	$sql    	= "select shopCoupons.* from (shopCoupons) where 1 $conditions order by $sortBy $sortDir";
	$result	    = commonDoQuery ($sql);
	$total	 	= commonQuery_numRows($result);

	// get details
	$sql   	   .= commonGetLimit ($xmlRequest);
	$result	    = commonDoQuery ($sql);
	$numRows 	= commonQuery_numRows($result);

	$xmlResponse = "<items>";

	while ($row = commonQuery_fetchRow($result))
	{
		$id			= $row['id'];
		$code		= $row['code'];
		$name		= commonValidXml($row['name']);
		$type		= $row['type'];

		if ($type == "amount")
			$type	= "הנחת סכום";
		else if ($type == "percent")
			$type	= "הנחת אחוזים";
		else if ($type == "percentCount")
			$type	= "הנחת אחוזים לכמות";

		$type		= commonPhpEncode($type);

		$value		= $row['value'];
		$expireDate	= formatApplDate($row['expireDate']);

		$quantity	= $row['leftQuantity'] . " / " . $row['startQuantity'];

		$xmlResponse .=	"<item>
							<id>$id</id>
							<code>$code</code>
							<name>$name</name>
							<type>$type</type>
							<value>$value</value>
							<expireDate>$expireDate</expireDate>
							<quantity>$quantity</quantity>
						 </item>";
	}

	$xmlResponse .=	"</items>"								.
					commonGetTotalXml($xmlRequest,$numRows,$total);
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* addCoupon																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function addCoupon ($xmlRequest)
{
	$sql			= "select max(id) from shopCoupons";
	$result	    	= commonDoQuery ($sql);
	$row	    	= commonQuery_fetchRow($result);
	$id	    		= $row[0]+1;

	$type			= xmlParser_getValue($xmlRequest, "type");
	$value			= xmlParser_getValue($xmlRequest, "value");
	$minCount		= xmlParser_getValue($xmlRequest, "minCount");
	$maxCount		= xmlParser_getValue($xmlRequest, "maxCount");
	$expireDays		= xmlParser_getValue($xmlRequest, "expireDays");
	$startQuantity	= xmlParser_getValue($xmlRequest, "startQuantity");
	$productId		= xmlParser_getValue($xmlRequest, "productId");
	$validOnRenew	= xmlParser_getValue($xmlRequest, "validOnRenew");
	$name			= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "name")));	
	$remarks		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "remarks")));	

	$code			= xmlParser_getValue($xmlRequest, "code");

	if ($code == "")
	{
		while ($code == "")
		{
			$code = randomCode(10);
	
			// check if code already exist
			$sql	 = "select count(*) from shopCoupons where code = '$code'";
			$result  = commonDoQuery ($sql);
			$row 	 = commonQuery_fetchRow($result);

			if ($row[0]) 
				$code = "";
		}
	}
	else
	{
		// check if unique
		$sql	 = "select count(*) from shopCoupons where code = '$code'";
		$result  = commonDoQuery ($sql);
		$row 	 = commonQuery_fetchRow($result);

		if ($row[0]) 
			trigger_error (iconv("windows-1255", "utf-8", "קופון עם קוד זהה כבר קיים במערכת"));
	}

	$expireDate = date("Y-m-d", strtotime("+$expireDays days"));

	$sql		= "insert into shopCoupons (id, code, name, type, value, minCount, maxCount, expireDate, startQuantity, leftQuantity, 
											validOnRenew, productId, remarks, insertTime)
				   values ($id, '$code', '$name', '$type', $value, '$minCount', '$maxCount', '$expireDate', '$startQuantity', '$startQuantity', 
						   '$validOnRenew', '$productId', '$remarks', now())";
	commonDoQuery ($sql);

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateCoupon																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateCoupon ($xmlRequest)
{
	$id		= xmlParser_getValue($xmlRequest, "id");

	if ($id == "")
		trigger_error (iconv("windows-1255", "utf-8", "חסר קוד קופון - לא ניתן לבצע עדכון"));

	$expireDate		= formatApplToDB(xmlParser_getValue($xmlRequest, "expireDate"));
	$startQuantity	= xmlParser_getValue($xmlRequest, "startQuantity");
	$productId		= xmlParser_getValue($xmlRequest, "productId");
	$validOnRenew	= xmlParser_getValue($xmlRequest, "validOnRenew");
	$name			= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "name")));	
	$remarks		= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "remarks")));	

	$sql			= "select * from shopCoupons where id = $id";
	$result			= commonDoQuery ($sql);
	$row 			= commonQuery_fetchRow($result);

	$leftQuantity	= $row['leftQuantity'];
	$used			= $row['startQuantity'] - $leftQuantity;

	if ($startQuantity < $used)
	{
		trigger_error (iconv("windows-1255", "utf-8", "הכמות הבנחרת חייבת להיות גדולה מהכמות שכבר נוצלה"));
	}

	$leftQuantity  += $startQuantity - $row['startQuantity'];

	$sql			= "update shopCoupons set expireDate 	 	 = '$expireDate', 
												   startQuantity =  $startQuantity, 
												   leftQuantity  =  $leftQuantity,
												   productId	 = '$productId',
												   validOnRenew	 = '$validOnRenew',
												   name			 = '$name',
												   remarks		 = '$remarks'
					   where id = $id";
	commonDoQuery ($sql);

}

/* ----------------------------------------------------------------------------------------------------	*/
/* getCouponDetails																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getCouponDetails ($xmlRequest)
{
	$id		= xmlParser_getValue($xmlRequest, "id");

	if ($id == "")
		trigger_error (iconv("windows-1255", "utf-8", "חסר קוד קופון - לא ניתן לקבל פרטים"));

	$sql	= "select shopCoupons.* from shopCoupons where id = $id";
	$result	= commonDoQuery ($sql);
	$row 	= commonQuery_fetchRow($result);

	$id				= $row['id'];
	$code			= $row['code'];
	$type			= $row['type'];

	if ($type == "amount")
		$type		= "הנחת סכום";
	else if ($type == "percent")
		$type		= "הנחת אחוזים";
	else if ($type == "percentCount")
		$type	= "הנחת אחוזים לכמות";

	$type			= commonPhpEncode($type);

	$value			= $row['value'];
	$minCount		= $row['minCount'];
	$maxCount		= $row['maxCount'];
	$expireDate		= formatApplDate($row['expireDate']);

	$startQuantity	= $row['startQuantity'];
	$leftQuantity	= $row['leftQuantity'];

	$productId		= $row['productId'];
	$validOnRenew	= $row['validOnRenew'];

	$name			= commonValidXml($row['name']);
	$remarks		= commonValidXml($row['remarks']);

	$xmlResponse	= "<id>$id</id>
					   <type>$type</type>
					   <value>$value</value>
					   <minCount>$minCount</minCount>
					   <maxCount>$maxCount</maxCount>
					   <code>$code</code>
					   <name>$name</name>
					   <startQuantity>$startQuantity</startQuantity>
					   <leftQuantity>$leftQuantity</leftQuantity>
					   <productId>$productId</productId>
					   <validOnRenew>$validOnRenew</validOnRenew>
					   <expireDate>$expireDate</expireDate>
					   <remarks>$remarks</remarks>";

	return $xmlResponse;
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteCoupon																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteCoupon ($xmlRequest)
{
	$id	= xmlParser_getValue($xmlRequest, "id");

	if ($id == "")
		trigger_error (iconv("windows-1255", "utf-8", "חסר קוד קופון - לא ניתן לבצע מחיקה"));

	$sql			= "select * from shopCoupons where id = $id";
	$result			= commonDoQuery ($sql);
	$row 			= commonQuery_fetchRow($result);

	if ($row['leftQuantity'] != $row['startQuantity'])
	{
		trigger_error (iconv("windows-1255", "utf-8", "קופון זה כבר נוצל - לא ניתן למחוקו"));
	}

	$sql 	= "delete from shopCoupons where id = $id";
	commonDoQuery ($sql);

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getPrintCoupon																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getPrintCoupon ($xmlRequest)
{
	global $ibosHomeDir;
	$id	= xmlParser_getValue($xmlRequest, "id");

	if ($id == "")
		trigger_error (iconv("windows-1255", "utf-8", "חסר קוד קופון - לא ניתן להדפיסו"));

	$siteUrl		= "http://62.90.141.80/~whiskyil";

	$sql			= "select * from shopCoupons where id = $id";
	$result			= commonDoQuery ($sql);
	$row 			= commonQuery_fetchRow($result);

	$path 	= "$ibosHomeDir/html_plugins/410/prints/";

	$files = glob("$path/*.html");

    # delete old files
    # -----------------------------------------------------------------------
	$existFiles = array();

	if ($files != "")
	{
		foreach ($files as $filename) 
		{
			$filedate =  filemtime($filename);
	
			if ($filedate + (60*60*60) < strtotime("now"))
				unlink ($filename);
			else
				array_push ($existFiles, $filename);
		}
    }

    # search for unique file name
    # -----------------------------------------------------------------------
	$code 	  = randomCode(6);
	$fileName = "print" . $code . ".html";
	while (in_array($fileName,$existFiles))
	{
		$code = randomCode(6);
		$fileName = "print" . $code . ".html";
	}

	$file = fopen ("$path/$fileName", "w");

	$code	= iconv("windows-1255", "utf-8", "קוד קופון ") . "&nbsp; <span>$row[code]#</span>";

	if ($row['type'] == "amount")
	{
		$discount = "$row[value] ש\"ח";
	}
	else
	{
		$discount = "$row[value]%";
	}

	$discount .= " הנחה לרכישת וויסקי בחנות האתר";
	$discount  = iconv("windows-1255", "utf-8", $discount);

	$remarks   = iconv("windows-1255", "utf-8", "בתוקף עד ה-") . date("d.m.Y", strtotime($row['expireDate'])) . "<br/>";
	$remarks  .= stripslashes($row['remarks']);

	$fileData = "<html lang='he' dir='rtl'>
					<head>
						<meta http-equiv='content-type' content='text/html; charset=utf-8' />
						<style>
							body
							{
								margin: 0px auto;
								font-family: arial;
								font-size: 13px;
							}

							div#coupon
							{
								position: relative;
								width: 537px;
								height: 161px;
							}

							div#couponBg
							{	
								position: absolute;
								top: 0px;
								right: 0px;
							}

							div#coupon div.data
							{
								position: absolute;
								right: 225px;
								width: 290px;
							}

							div#couponCode
							{
								top: 37px;
								font-size: 16px;
								font-weight: bold;
								text-align: center;
							}

							div#couponCode span
							{
								direction: ltr;
								font-size: 18px;
							}

							div#couponDiscount
							{
								top: 65px;
								color: #C9950D;
								font-size: 16px;
								font-weight: bold;
								text-align: center;
							}

							div#couponRemarks
							{
								top: 95px;
								font-size: 12px;
								color: #686860;
								text-align: center;
							}
						</style>
					</head>
					<body onload='window.print();window.close()'>
						<div id='coupon'>
							<div id='couponBg'><img src='$siteUrl/designFiles/coupon_bg.png' /></div>
							<div class='data' id='couponCode'>$code</div>
							<div class='data' id='couponDiscount'>$discount</div>
							<div class='data' id='couponRemarks'>$remarks</div>
						</div>
					</body>
				 </html>";

/*							<div class='data' id='couponGoShop'>
								<a href='$siteUrl/index2.php?id=40&lang=HEB' target='_blank'><img src='$siteUrl/designFiles/btnCouponGoShop.png' /></a>
							</div> */

	fwrite ($file,$fileData);
	fclose ($file);

	return ("<fileName>prints/$fileName</fileName>");
}


?>
