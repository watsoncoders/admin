<?php

/* ----------------------------------------------------------------------------------------------------	*/
/* getStyles																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function getStyles ($xmlRequest)
{
	// get total
	$queryStr	= "select count(*) from styles";
	$result	    = commonDoQuery ($queryStr);
	$row	    = commonQuery_fetchRow($result);
	$total	    = $row[0];

	// get details
	$queryStr	= "select * from styles order by styleId " . commonGetLimit ($xmlRequest);
	$result	     = commonDoQuery ($queryStr);

	$xmlResponse = "<items>";
	while ($row = commonQuery_fetchRow($result))
	{
		$canDelete = 1;
		if ($row['isBasic'])
			$canDelete 			= 0;
			
		$styleName 		  	= commonValidXml($row['styleName']);
		$styleDescription 	= commonValidXml($row['styleDescription']); 
		$fontFamily		 	= $row['fontFamily'];
		$fontSize			= $row['fontSize'];
		$color				= $row['color'];
		$backgroundColor	= $row['backgroundColor'];
		$fontStyle			= $row['fontStyle'];
		$underline			= $row['underline'];
		$cursor				= $row['cursorType'];

		$fontDisplay		= "";

		if ($underline == "0") $underline = "none";
		if ($underline == "1") $underline = "underline";

		if ($fontStyle == "R" && ($underline == "" || $underline == "none"))
		{
			$fontDisplay = "רגיל";
		}
		else
		{
			if ($fontStyle == "B" ) $fontDisplay = "מודגש";
			if ($fontStyle == "I" ) $fontDisplay = "נטוי";
			if ($fontStyle == "BI") $fontDisplay = "מודגש ,נטוי";
			
			if ($underline != "" && $underline != "none") 
			{
				if ($fontDisplay != "") $fontDisplay .= ", ";

				switch ($underline)
				{
					case "underline"	: 
					case "1"			: $fontDisplay .= "קו תחתון";	break;
					case "line-through"	: $fontDisplay .= "קו חוצה";	break;
					case "overline"		: $fontDisplay .= "קו מעל";		break;
				}
			}
		}
		$fontDisplay 		= commonPhpEncode ($fontDisplay);
		$css				= commonValidXml ($row['css']);

		$moreForTable		= commonValidXml ("<div style='direction:ltr'>&nbsp;$row[css]&nbsp;</div>");

		$xmlResponse .=	"<item>"														.
							"<styleName>$styleName</styleName>"							.
							"<styleDescription>$styleDescription</styleDescription>"	.
							"<canDelete>$canDelete</canDelete>"							.
							"<fontFamily>$fontFamily</fontFamily>"						.
							"<fontSize>$fontSize</fontSize>"							.
							"<color>$color</color>"										.
							"<backgroundColor>$backgroundColor</backgroundColor>"		.
							"<fontStyle>$fontStyle</fontStyle>"							.
							"<fontDisplay>$fontDisplay</fontDisplay>"					.
							"<underline>$underline</underline>"							.
							"<cursor>$cursor</cursor>"									.
							"<more>$css</more>"											.
							"<moreForTable>$moreForTable</moreForTable>"				.
						"</item>";
	}

	$xmlResponse .=	"</items>"															.
					commonGetTotalXml($xmlRequest,commonQuery_numRows($result),$total);

	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* addStyle																								*/
/* ----------------------------------------------------------------------------------------------------	*/
function addStyle ($xmlRequest)
{
	return (editStyle ($xmlRequest, "add"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* doesStyleExist																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function doesStyleExist ($styleName)
{
	$queryStr	 = "select count(*) from styles where styleName='$styleName'";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$count	     = $row[0];

	return ($count > 0);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateStyle																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateStyle ($xmlRequest)
{
	editStyle ($xmlRequest, "update");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* editStyle																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function editStyle ($xmlRequest, $editType)
{
	$styleName		= trim(xmlParser_getValue($xmlRequest, "styleName"));

	if ($styleName == "")
		trigger_error ("חסר שם סגנון לביצוע הפעולה");

	if ($editType == "add")
	{
		if (doesStyleExist ($styleName))
		{
			trigger_error ("סגנון בשם זה ($styleName) כבר קיים במערכת");
		}
	}
	else	// update style
	{
		if (!doesStyleExist ($styleName))
		{
			trigger_error ("סגנון בשם זה ($styleName) לא קיים במערכת. לא ניתן לבצע את העדכון");
		}
	}

	$styleDescription	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "styleDescription")));
	$fontFamily			= xmlParser_getValue($xmlRequest, "fontFamily");
	$fontSize			= xmlParser_getValue($xmlRequest, "fontSize");
	$color				= xmlParser_getValue($xmlRequest, "color");
	$backgroundColor	= xmlParser_getValue($xmlRequest, "backgroundColor");
	$fontStyle			= xmlParser_getValue($xmlRequest, "fontStyle");
	$underline			= xmlParser_getValue($xmlRequest, "underline");
	$cursor				= xmlParser_getValue($xmlRequest, "cursor");
	$css				= xmlParser_getValue($xmlRequest, "more");

	if ($editType == "add")
	{
		$queryStr = "insert into styles (styleName, styleId, styleDescription, fontFamily, fontSize, color,
										 backgroundColor, fontStyle, underline, cursorType, css)
					 values ('$styleName', '50','$styleDescription', '$fontFamily', '$fontSize', '$color', 
					 		 '$backgroundColor', '$fontStyle', '$underline', '$cursor', '$css')";
	}
	else // update
	{
		$queryStr = "update styles set 	styleDescription = '$styleDescription',		
										fontFamily		 = '$fontFamily',		
										fontSize		 = '$fontSize',		
										color			 = '$color',		
										backgroundColor	 = '$backgroundColor',		
										fontStyle		 = '$fontStyle',		
										underline		 = '$underline',		
										cursorType		 = '$cursor',		
										css				 = '$css'		
					 where styleName='$styleName'";
	}

	commonDoQuery ($queryStr);

	updateCssFile ();

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteStyle																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteStyle ($xmlRequest)
{
	$styleName		= xmlParser_getValue($xmlRequest, "styleName");

	if ($styleName == "")
		trigger_error ("חסר שם סגנון לביצוע הפעולה");

	$queryStr = "delete from styles where styleName='$styleName'";

	commonDoQuery ($queryStr);

	updateCssFile ();

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateCssFile																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateCssFile ()
{
	$content	= "";

	$queryStr 	= "select * from styles order by styleId";
	$result	    = commonDoQuery ($queryStr);

	$styles = array();

	for ($i=1; $i<10; $i++)
	{
		eval ("\$styles[\".styleCustom$i\"] = \"מותאם $i\";");
	}

	while ($row = commonQuery_fetchRow($result))
	{
		$styleName 		  	= $row['styleName'];
		$styleDescription 	= $row['styleDescription']; 
		$fontFamily		 	= $row['fontFamily'];
		$fontSize			= $row['fontSize'];
		$color				= $row['color'];
		$backgroundColor	= $row['backgroundColor'];
		$fontStyle			= $row['fontStyle'];
		$underline			= $row['underline'];
		$cursor				= $row['cursorType'];
		$css				= $row['css'];

		$styles[$styleName] = $styleDescription;

		$content		   .= // In UTF-8 these Hebrew remarks make trouble "/* $styleDescription */\n" 	. 
							  "$styleName\n"				.
							  "{\n";

		if ($fontFamily != "")
			$content	   .= "font-family : $fontFamily;\n";

		if ($fontSize != "0")
			$content	   .= "font-size : ${fontSize}px;\n";

		if ($underline != "")
			$content	   .= "text-decoration : $underline;\n";

		if ($backgroundColor != "")
			$content	   .= "background-color : $backgroundColor;\n";

		if ($color != "")
			$content	   .= "color : $color;\n";

		if ($cursor != "")
			$content	   .= "cursor : $cursor;\n";

		if ($fontStyle == "B" || $fontStyle == "BI")
			$content	   .= "font-weight : bold;\n";

		if ($fontStyle == "I" || $fontStyle == "BI")
			$content	   .= "font-style : italic;\n";

		$content		   .= "$css\n"		.
							  "}\n\n";
	}
	
	$temp = tmpfile();
	fwrite($temp, $content);
	fseek($temp, 0);

	$domainRow = commonGetDomainRow ();
	$connId    = commonFtpConnect	($domainRow);
	ftp_fput ($connId,"common.css",$temp, FTP_BINARY);
	commonFtpDisconnect($connId);

	fclose($temp);

	/*
	$domainNameArray = explode("/", $domainRow['domainName']);
	if (count($domainNameArray) > 1)
	{
			$fixedDomainName = $domainNameArray[count($domainNameArray)-1];
	} else {
			$fixedDomainName = $domainNameArray[0];
	}

	$file = fopen ("/tmp/".$fixedDomainName.".css", "w");
	fwrite ($file, $content);
	fclose ($file);

	// create styles-heb.xml file
	$fileName   = "../html/Xstandard2/styles-heb-template.xml";
	$hebXmlFile = fopen ($fileName, "rb");
	$origHebXml = fread ($hebXmlFile, filesize($fileName));
	fclose ($hebXmlFile);

	$styleTemplate =
		"<style>
		<id>custom#id#</id>
		<label xml:lang=\"en\">Custom #id#</label>
		<label xml:lang=\"he\">#custom#</label>
		<elt>span</elt>
		<attr>
			<name>class</name>
			<value>styleCustom#id#</value>
		</attr>
		</style>
		";

	//Read Hebrew Letters as UTF-8
	$xd = fopen("../html/Xstandard2/hebletters.xml", "rb");
	$hebletters = fgets($xd);
	fclose($xd);
	$letters = explode(" ", $hebletters);

	$fullXml = "";
	while (list($key, $val) = each($styles))
	{
		if (strncmp($key,".styleCustom",12))
			continue;
		else
			$i = substr($key,12);

		$newText = "";
		for ($l=0; $l < strlen($val); $l++)
		{
			$ind = ord($val[$l]) - 224; 
			if ($ind >=0 && $ind < count($letters))
					$newText .= $letters[$ind+1];
			else
					$newText .= $val[$l];
		}
		$hebXml = str_replace("#custom#", $newText, $styleTemplate);
		$hebXml = str_replace("#id#", "$i", $hebXml);
		$fullXml .= $hebXml;
	}

	$origHebXml = str_replace("#HEBSTYLES#", $fullXml, $origHebXml);

	$file = fopen ("/tmp/".$fixedDomainName.".xml", "wb");
	fwrite ($file, $origHebXml);
	fclose ($file);

	$connId    = commonFtpConnect	($domainRow);

	ftp_put ($connId,"common.css","/tmp/".$fixedDomainName.".css", FTP_BINARY);
	ftp_put ($connId,"styles-heb.xml","/tmp/".$fixedDomainName.".xml", FTP_BINARY);
	
	commonFtpDisconnect($connId);
	 */
}


?>
