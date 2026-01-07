<?php

$allLangsCode = array("HEB", "ENG", "FRN", "ESP", "ARB", "RUS");
$allLangsText = array("עברית", "אנגלית", "צרפתית", "ספרדית", "ערבית", "רוסית");

/* ----------------------------------------------------------------------------------------------------	*/
/* getLangs																								*/
/* ----------------------------------------------------------------------------------------------------	*/
function getLangs ($xmlRequest)
{
	global $allLangsCode, $allLangsText;

	$queryStr    = "select langs from globalParms";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);

	$langs		 = $row['langs'];
	
	$langsArray = explode(",",$langs);

	$langsText = "";
	if ($langs == "")
		$langsText = "אין";
	else
	{
		for ($i=0; $i < count($langsArray); $i++)
		{
			$textIndex = array_search($langsArray[$i], $allLangsCode);

			if ($textIndex >= 0)
			{
				if ($langsText != "")
					$langsText .= ", ";

				$langsText .= $allLangsText[$textIndex];
			}
		}
	}

	$langsText = commonValidXml($langsText);
	
	$xmlResponse	= "<clientLangs>
							<langs>$langs</langs>
							<langsText>$langsText</langsText>
							<newLang></newLang>
							<otherLangs>";
							

	for ($i=0; $i < count($allLangsCode); $i++)
	{
		if (!in_array($allLangsCode[$i], $langsArray))
		{
			$xmlResponse .= 	"<otherLang>
									<lang>" . $allLangsCode[$i] . "</lang>
									<name>" . commonValidXml($allLangsText[$i]) . "</name>
								</otherLang>";
		}
	}
	$xmlResponse .=		"</otherLangs>
					   </clientLangs>";

	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getDesign																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getDesign ($xmlRequest)
{
	global $usedLangs;

	$queryStr    = "select * from design";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);

	$logoPic	 		= $row['logoPic'];
	$privateWidth 		= $row['privateWidth'];
	$privateMiddleWidth = $row['privateMiddleWidth'];
	$boxPic				= $row['boxPic'];
	$topHeight			= $row['topHeight'];
	$leftWidth	 		= $row['leftWidth'];
	$rightWidth	 		= $row['rightWidth'];
	$bottomHeight		= $row['bottomHeight'];
	
	$xmlResponse = "<design>
						<logoPic>$logoPic</logoPic>
						<privateWidth>$privateWidth</privateWidth>
						<privateMiddleWidth>$privateMiddleWidth</privateMiddleWidth>
						<boxPic>$boxPic</boxPic>
						<topHeight>$topHeight</topHeight>
						<leftWidth>$leftWidth</leftWidth>
						<rightWidth>$rightWidth</rightWidth>
						<bottomHeight>$bottomHeight</bottomHeight>
					</design>";

	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateDesign																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateDesign ($xmlRequest)
{
	global $sessionCode;
	
	$loadMain = 0;

	$update				= xmlParser_getValue($xmlRequest, "update");
	
	$logoPic			= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "logoPic")));
	$privateWidth		= xmlParser_getValue($xmlRequest, "privateWidth");
	$privateMiddleWidth	= xmlParser_getValue($xmlRequest, "privateMiddleWidth");
	$boxPic				= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "boxPic")));
	$topHeight			= xmlParser_getValue($xmlRequest, "topHeight");
	$leftWidth			= xmlParser_getValue($xmlRequest, "leftWidth");
	$rightWidth			= xmlParser_getValue($xmlRequest, "rightWidth");
	$bottomHeight		= xmlParser_getValue($xmlRequest, "bottomHeight");
	
	$doUpdates 		= "	logoPic			   = '$logoPic',
						privateWidth 	   = '$privateWidth',
						privateMiddleWidth = '$privateMiddleWidth',
						boxPic			   = '$boxPic',
						topHeight		   = '$topHeight',
						leftWidth  		   = '$leftWidth',
						rightWidth		   = '$rightWidth',
						bottomHeight  	   = '$bottomHeight'";

	$queryStr = "update design set $doUpdates";

	/* ---------------------------------------------------------------------------------------------------------------------------- */
	/* Box image split																												*/
	/* ---------------------------------------------------------------------------------------------------------------------------- */
		
	///////////////////////////////////
	// Connect to FTP				 //
	///////////////////////////////////
	$mysqlHandle = commonConnectToDB();

	$domainRow = commonGetDomainRow ();

	$connId    = commonFtpConnect	($domainRow);

	ftp_get($conn_id,"/../tmp/".$domainRow['ftpUsername']."box.jpg","/loadedFiles/$boxPic",FTP_BINARY);
	$image = imagecreatefromjpeg("/../tmp/".$domainRow['ftpUsername']."box.jpg");
	
	for ($i=1; $i<=8; $i++)
	{
			switch ($i)
			{
					case 1: // lt
							$crop = imagecreatetruecolor($leftWidth,$topHeight);
							imagecopy($crop, $image, 0, 0, 0, 0, $leftWidth, $topHeight);
							$sliceName = "rt.gif";
							$upload = ftp_put($conn_id, "images/$sliceName", imagegif($crop), FTP_BINARY);
							break;
					case 2: // line
							$crop = imagecreatetruecolor(1,$topHeight);
							imagecopy($crop, $image, 0, 0, $leftWidth, 0, 1, $topHeight);
							$sliceName = "line.gif";
							$upload = ftp_put($conn_id, "images/$sliceName", imagegif($crop), FTP_BINARY);
							break;
					case 3: // rt
							list($width, $height) = getimagesize($filename);
							$crop = imagecreatetruecolor($rightWidth,$topHeight);
							imagecopy($crop, $image, 0, 0, ($width-$rightWidth), 0, $rightWidth, $topHeight);
							$sliceName = "lt.gif";
							$upload = ftp_put($conn_id, "images/$sliceName", imagegif($crop), FTP_BINARY);
							break;
					case 4: // lcol
							$crop = imagecreatetruecolor($leftWidth,1);
							imagecopy($crop, $image, 0, 0, 0, $topHeight, $leftWidth, 1);
							$sliceName = "lcol.gif";
							$upload = ftp_put($conn_id, "images/$sliceName", imagegif($crop), FTP_BINARY);
							break;
					case 5: // col
							list($width, $height) = getimagesize($filename);
							$crop = imagecreatetruecolor($rightWidth,1);
							imagecopy($crop, $image, 0, 0, ($width-$rightWidth), $topHeight, $rightWidth, 1);
							$sliceName = "col.gif";
							$upload = ftp_put($conn_id, "images/$sliceName", imagegif($crop), FTP_BINARY);
							break;
					case 6: // lb
							list($width, $height) = getimagesize($filename);
							$crop = imagecreatetruecolor($leftWidth,$bottomHeight);
							imagecopy($crop, $image, 0, 0, 0, ($height-$bottomHeight), $leftWidth, $bottomHeight);
							$sliceName = "rb.gif";
							$upload = ftp_put($conn_id, "images/$sliceName", imagegif($crop), FTP_BINARY);
							break;
					case 7: // dline
							list($width, $height) = getimagesize($filename);
							$crop = imagecreatetruecolor(1,$bottomHeight);
							imagecopy($crop, $image, 0, 0, $leftWidth, ($height-$bottomHeight), 1, $bottomHeight);
							$sliceName = "dline.gif";
							$upload = ftp_put($conn_id, "images/$sliceName", imagegif($crop), FTP_BINARY);
							break;
					case 8: // rb
							list($width, $height) = getimagesize($filename);
							$crop = imagecreatetruecolor($rightWidth,$bottomHeight);
							imagecopy($crop, $image, 0, 0, ($width-$rightWidth), ($height-$bottomHeight), $rightWidth, $bottomHeight);
							$sliceName = "lb.gif";
							$upload = ftp_put($conn_id, "images/$sliceName", imagegif($crop), FTP_BINARY);
							break;
			}
	}

	commonDoQuery ($queryStr);

	return "<loadMain>$loadMain</loadMain>";
}

?>
