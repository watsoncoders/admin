<?

while (list($key, $val) = each($_GET)) 
	if (preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*/', $key))
		eval("$$key = '$val';");
while (list($key, $val) = each($_POST)) 
	if (preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*/', $key))
		eval("$$key = '$val';");
while (list($key, $val) = each($_COOKIE)) 
	if (preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*/', $key))
		eval("$$key = '$val';");

// [Amir 08/11/10] We'd like to avoid several mysql_connect to the same database in a single script run,
// since it's expensive for remote mysql server. So we will keep both user and SuperAdmin connections open.
$currDBConnection = null;
$usersDBConnection = array();
$saDBConnection = null;

$ibosHomeDir = "/home/israeli/public_html/admin/3.0";

function commonConnectToUserDB($domainRow)
{
	global $isUTF8, $currDBConnection, $usersDBConnection;

	$dbName	= $domainRow['dbName'];
	if (! array_key_exists($dbName, $usersDBConnection))
	{
		// allow domains with only IP, like 69.57.177.202/~green99
		if ($x = strpos($domainRow['domainName'], '/'))
			$domainRow['domainName'] = substr($domainRow['domainName'],0,$x);
	 
		// connect to user DB
		$port = ($domainRow['dbPort'] == "") ? "" : ":$domainRow[dbPort]";
		$dbHost	= "$domainRow[dbHostname]$dbPort";
		$dbUser	= $domainRow['dbUsername'];
		$dbPass	= $domainRow['dbPassword'];

		$usersDBConnection[$dbName] = 
				mysqli_connect($dbHost, $dbUser, $dbPass, $dbName) or die (mysqli_connect_errno());

		if ($isUTF8) 
		{
			mysqli_query($usersDBConnection[$dbName], "set names 'utf8mb4'") or die(mysqli_error($usersDBConnection[$dbName]));
		}
		mysqli_query($usersDBConnection[$dbName], "SET SESSION sql_mode = ''") or die(mysqli_error($usersDBConnection[$dbName]));
	}


	$currDBConnection = $usersDBConnection[$dbName];

}

/* --------------------------------------------------------------------------------------------	*/
/* commonValidateSession 																		*/
/* --------------------------------------------------------------------------------------------	*/
function commonValidateSession ($andPlugins = false)
{
	global $sessionCode, $siteId;
	global $isUTF8;
	global $currDBConnection;
	
	$mysqlHandle = commonConnectToDB();

	// check session expire
	$sql 	= "select creationTime from sessions where code='$sessionCode'";
	$result = commonDoQuery($sql);
	$row	= commonQuery_fetchRow($result);
	
	if ($row != "")
	{
		$expire	  = strtotime ("$row[creationTime] +2 hours");
		$today	  = strtotime ("now");
//		trigger_error (date("d-m-Y H:i", $today));

		if ($expire < $today)
		{
			return false;
		}
		else
		{
			$sql 	= "update sessions set creationTime = now() where code='$sessionCode'";
			commonDoQuery($sql);
		}
	}
	else
	{
		return false;
	}

	$userRow = commonGetUserRow ($sessionCode);

	if ($userRow == null || $userRow['id'] == 0)
	{
		trigger_error ("Session deleted");
	}

	if ($userRow['domainId'] == 0)	// super admin
	{
		$sql = "select * from domains where id=$siteId";
	}
	else
	{
		$sql = "select * from domains where id=$userRow[domainId]";
		$siteId = "";
	}

	$result 	= commonDoQuery($sql);
	$domainRow	= commonQuery_fetchRow($result);

	$isUTF8		= $domainRow['isUTF8'];

	if ($andPlugins) {
		$queryStr2		= "select * from plugins where domainId=".$userRow['domainId'] . " order by id";
		$pluginResults	= commonDoQuery($queryStr2);
	}

	commonDisconnect ($mysqlHandle);

	commonConnectToUserDB($domainRow);
	
	// Delete cache for static websites
	if ($domainRow['isStatic'] == 1)
		commonDoQuery("truncate staticPagesCache");

	/*
	// build short website name
	if (strpos($domainRow['domainName'], "62.90.141.80") !== false)
	{
		$webname = substr($domainRow['domainName'],strpos($domainRow['domainName'],'~')+1, strlen($domainRow['domainName']));
	}
	else if (strpos($domainRow['domainName'], "www") !== false)
	{
		$webname = substr($domainRow['domainName'],4);
		$webname = substr($webname,0,strpos($webname,'.'));
	}
	else
	{
		$webname = substr($domainRow['domainName'],0,strpos($domainRow['domainName'],'.'));
	}
	if (strpos($domainRow['homeDir'], '/'))
		$webname .= substr($domainRow['homeDir'],strpos($domainRow['homeDir'],'/'));
	 */

	$weblink = commonGetDomainName ($domainRow);
	$webname = str_replace("https://", "", str_replace("http://", "", $weblink));

	if ($andPlugins) 
		return array($webname, $pluginResults, $weblink);
	else
		return $webname; // also evaluated as 'true'
}

/* --------------------------------------------------------------------------------------------	*/
/* commonConnectToDB																			*/
/* --------------------------------------------------------------------------------------------	*/
function commonConnectToDB ()
{
	global $currDBConnection, $saDBConnection;

	if ($saDBConnection == null)
	{
			$saDBConnection = mysqli_connect("localhost", "israeli_dbuser", "oded1234", "israeli_admin") or die (mysqli_connect_errno());
	}

	$currDBConnection = $saDBConnection;

	return $currDBConnection;
}

/* --------------------------------------------------------------------------------------------	*/
/* commonDisconnect																				*/
/* --------------------------------------------------------------------------------------------	*/
function commonDisconnect ($mysqlHandle)
{
//	commonDisconnect ($mysqlHandle);
}

/* --------------------------------------------------------------------------------------------	*/
/* common.php compatibility																		*/
/* --------------------------------------------------------------------------------------------	*/
function commonQuery_fetchRow 	  ($result) 								{ return commonQueryFetchRow	 ($result); 		}
function commonQuery_numRows 	  ($result) 								{ return commonQueryNumRows		 ($result); 		}
function commonQuery_affectedRows () 										{ return commonQueryAffectedRows (); 				}
function commonQuery_dataSeek 	  ($result, $from) 							{ return commonQueryDataSeek	 ($result, $from); 	}
function commonQuery_escapeStr 	  ($result) 								{ return commonQueryEscapeStr	 ($result); 		}
function commonQuery_insertId 	  () 										{ return commonQueryLastInsertId (); 				}
function commonSendEmailAsNewsletter($toEmail, $subject, $message, $lang)	{ return commonPhpMail			 ($toEmail, $subject, $message); }

/* --------------------------------------------------------------------------------------------	*/
/* commonGetUserRow																				*/
/* --------------------------------------------------------------------------------------------	*/
function commonGetUserRow ($sessionCode)
{

	$sql 	= "select userId from sessions where code='$sessionCode'";
	$result = commonDoQuery($sql);

	if (commonQuery_numRows($result) == 0)
		return null;

	$row	= commonQuery_fetchRow($result);

	$sql	= "select * from users where id='$row[userId]'";
	$result = commonDoQuery($sql);
	$row	= commonQuery_fetchRow($result);

	return $row;
}

/* --------------------------------------------------------------------------------------------	*/
/* commonAddIbosUserCondition																	*/
/* --------------------------------------------------------------------------------------------	*/
function commonAddIbosUserCondition ($tableName = "pages")
{
	global $sessionCode;

	$mysqlHandle = commonConnectToDB();

	$userRow 	 = commonGetUserRow ($sessionCode);

	$sql		 = "select * from domains where id=$userRow[domainId]";
	$result 	 = commonDoQuery($sql);
	$domainRow	 = commonQuery_fetchRow($result);

	$condition  = "";

	if ($userRow['isSuperUser'] == "0")
		$condition .= " and $tableName.ibosUserId = $userRow[id] ";

	commonDisconnect ($mysqlHandle);

	commonConnectToUserDB ($domainRow);

	return $condition;
}

/* --------------------------------------------------------------------------------------------	*/
/* commonGetIbosUserId																			*/
/* --------------------------------------------------------------------------------------------	*/
function commonGetIbosUserId ()
{
	global $sessionCode;

	$mysqlHandle = commonConnectToDB();

	$userRow 	 = commonGetUserRow ($sessionCode);

	$sql		 = "select * from domains where id=$userRow[domainId]";
	$result 	 = commonDoQuery($sql);
	$domainRow	 = commonQuery_fetchRow($result);

	commonDisconnect ($mysqlHandle);

	commonConnectToUserDB ($domainRow);

	return $userRow['id'];
}

/* --------------------------------------------------------------------------------------------	*/
/* commonGetDomainRow																			*/
/* --------------------------------------------------------------------------------------------	*/
function commonGetDomainRow ()
{
	global $sessionCode;
	
	$mysqlHandle = commonConnectToDB();

	$userRow = commonGetUserRow ($sessionCode);

	if ($userRow == null)
		return null;

	$sql	= "select * from domains where id=$userRow[domainId]";
	$result 	= commonDoQuery($sql);
	$domainRow	= commonQuery_fetchRow($result);

	commonDisconnect ($mysqlHandle);

	return $domainRow;
}

/* --------------------------------------------------------------------------------------------	*/
/* commonSetStaticMode																			*/
/* --------------------------------------------------------------------------------------------	*/
function commonSetStaticMode ($isStatic)
{
	global $sessionCode;
	
	$mysqlHandle = commonConnectToDB();

	$userRow = commonGetUserRow ($sessionCode);

	$sql	= "update domains set isStaticPages=".($isStatic?"1":"0")." where id=$userRow[domainId]";
	$result 	= commonDoQuery($sql);

	commonDisconnect ($mysqlHandle);
}

/* --------------------------------------------------------------------------------------------	*/
/* commonGetDomainName																			*/
/* --------------------------------------------------------------------------------------------	*/
function commonGetDomainName ($domainRow)
{	
	if ($domainRow['siteUrl'] != "")
		return $domainRow['siteUrl'];

	if (substr($domainRow['homeDir'],0,11) == "public_html")
		$hdir = substr($domainRow['homeDir'],11);
	else
		if (substr($domainRow['homeDir'],0,8) == "httpdocs")
			$hdir = substr($domainRow['homeDir'],8);
		else
			if (strpos($domainRow['homeDir'],"wwwroot") !== false)
				$hdir = "";			
			else
				if (strpos($domainRow['homeDir'],"panya_web") !== false)
					$hdir = "";			
				else
					if (strcmp($domainRow['homeDir'],$domainRow['domainName']) == 0)
						$hdir = "";			
					else
						if ($domainRow['homeDir'] == "")
							$hdir = "";
						else
							$hdir = "/".$domainRow['homeDir'];

	$promo = "";
	if (substr($domainRow['domainName'],0,4) !== "www." && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}/',$domainRow['domainName']) == false
			&& strpos($domainRow['domainName'], "telhai") === false
			&& strpos($domainRow['domainName'], "leida") === false 
			&& strpos($domainRow['domainName'], "outdoor") === false
			&& strpos($domainRow['domainName'], "panya") === false
	)
			$promo = "www.";

	$protocol = ($domainRow['isHttps'] ? "https" : "http");

	return "$protocol://$promo$domainRow[domainName]$hdir";
}

/* --------------------------------------------------------------------------------------------	*/
/* commonFtpConnect																				*/
/* --------------------------------------------------------------------------------------------	*/
function commonFtpConnect ($domainRow)
{
	// allow domains with only IP, like 69.57.177.202/~green99
	if ($x = strpos($domainRow['domainName'], '/'))
		$domainRow['domainName'] = substr($domainRow['domainName'],0,$x);
	 
	// establish ftp connection to the server
	if ($domainRow['dbHostname'] == 'localhost')
		$connId = ftp_connect("127.0.0.1"); 
	else
		$connId = ftp_connect($domainRow['domainName']); 

	// login with username and password
	$loginResult = ftp_login($connId, $domainRow['ftpUsername'], $domainRow['ftpPassword']); 

	if ($domainRow['ftpPassiveMode'])
			ftp_pasv($connId, true);

	// check connection
	if ((!$connId) || (!$loginResult)) 
	{ 
		echo ("התחברות FTP נכשלה");
		exit;
	}

	if ($domainRow['ftpPassiveMode'])
			ftp_pasv($connId, true);

	if ($domainRow['homeDir'] != "")
	{
		$homeDir	= $domainRow['homeDir'];
		ftp_chdir($connId, $homeDir);
	}

	return ($connId);
}

/* --------------------------------------------------------------------------------------------	*/
/* commonFtpDisconnect																			*/
/* --------------------------------------------------------------------------------------------	*/
function commonFtpDisconnect ($connId)
{
	ftp_close($connId);
}

/* --------------------------------------------------------------------------------------------	*/
/* commonFtpDelete																				*/
/* --------------------------------------------------------------------------------------------	*/
function commonFtpDelete ($connId, $file)
{
	if (ftp_size($connId, $file) != -1)
	{
		ftp_delete($connId, $file);
		return true;
	}
	else
	{
		return false;
	}
}

/* --------------------------------------------------------------------------------------------	*/
/* commonCData																					*/
/* --------------------------------------------------------------------------------------------	*/
function commonCData ($str)
{
	return "<![CDATA[$str]]>";
}

/* --------------------------------------------------------------------------------------------	*/
/* commonDoQuery																				*/
/* --------------------------------------------------------------------------------------------	*/
function commonDoQuery ($queryStr)
{
	global $currDBConnection;

	$result = mysqli_query ($currDBConnection, $queryStr);
	
	if (!$result) trigger_error (mysqli_error($currDBConnection)." Query was: ".$queryStr);

	return $result;
}

/* --------------------------------------------------------------------------------------------	*/
/* commonDoUnbufferedQuery																		*/
/* --------------------------------------------------------------------------------------------	*/
function commonDoUnbufferedQuery ($queryStr)
{
	return commonDoQuery($queryStr);
/*
	global $currDBConnection;

	$result = mysql_unbuffered_query ($queryStr, $currDBConnection);
	
	if (!$result) trigger_error (mysql_error()." Query was: ".$queryStr);

	return $result;
*/
}

/* -------------------------------------------------------------------------------------------------------------------- */
/* commonQueryFetchRow																									*/
/* -------------------------------------------------------------------------------------------------------------------- */
function commonQueryFetchRow ($result)
{
	if (PHP_VERSION_ID >= 70000)
	{
		return mysqli_fetch_array($result);
	}
	else
	{
		return mysql_fetch_array($result);
	}
}

/* -------------------------------------------------------------------------------------------------------------------- */
/* commonQueryNumRows																									*/
/* -------------------------------------------------------------------------------------------------------------------- */
function commonQueryNumRows ($result)
{
	if (PHP_VERSION_ID >= 70000)
	{
		return mysqli_num_rows($result);
	}
	else
	{
		return mysql_num_rows($result);
	}
}

/* -------------------------------------------------------------------------------------------------------------------- */
/* commonQueryAffectedRows																								*/
/* -------------------------------------------------------------------------------------------------------------------- */
function commonQueryAffectedRows ()
{
	if (PHP_VERSION_ID >= 70000)
	{
		return mysqli_affected_rows();
	}
	else
	{
		return mysql_affected_rows();
	}
}

/* -------------------------------------------------------------------------------------------------------------------- */
/* commonQueryDataSeek																									*/
/* -------------------------------------------------------------------------------------------------------------------- */
function commonQueryDataSeek ($result, $from)
{
	if (PHP_VERSION_ID >= 70000)
	{
		return mysqli_data_seek($result, $from);
	}
	else
	{
		return mysql_data_seek($result, $from);
	}
}

/* -------------------------------------------------------------------------------------------------------------------- */
/* commonQueryEscapeStr																									*/
/* -------------------------------------------------------------------------------------------------------------------- */
function commonQueryEscapeStr ($result)
{
	global $currDBConnection;

	if (PHP_VERSION_ID >= 70000)
	{
		return mysqli_real_escape_string($currDBConnection, $result);
	}
	else
	{
		return mysql_real_escape_string($result);
	}
}

/* -------------------------------------------------------------------------------------------------------------------- */
/* commonQueryLastInsertId																								*/
/* -------------------------------------------------------------------------------------------------------------------- */
function commonQueryLastInsertId ()
{
	global $currDBConnection;

	if (PHP_VERSION_ID >= 70000)
	{
		return mysqli_insert_id($currDBConnection);
	}
	else
	{
		return mysql_insert_id();
	}
}

/* --------------------------------------------------------------------------------------------	*/
/* commonValidXml																				*/
/* --------------------------------------------------------------------------------------------	*/
function commonValidXml ($text, $withCData = true)
{
//	if (strpos($text,"liat") != false)
//		trigger_error(ord(substr($text, strpos($text,"liat")-2, 1)));

//	$text = str_replace(chr(153),"{TM}", $text);
//	$text = str_replace(chr(150),"-",    $text);

	$text = stripslashes($text);

	$text = preg_replace("~<colgroup>[a-zA-Z0-9 \"\=\<\>\/]*</colgroup>~", "", $text);
	/* Amir 28.11.2008 $text = commonEncode($text); */

	if ($withCData)
		$text	= commonCData($text);
		
	return ($text);
}

/* --------------------------------------------------------------------------------------------	*/
/* commonPhpEncode																				*/
/* --------------------------------------------------------------------------------------------	*/
function commonPhpEncode ($text)
{
	return commonCData(commonEncode ($text));
}

/* --------------------------------------------------------------------------------------------	*/
/* commonPrepareToDB																			*/
/* --------------------------------------------------------------------------------------------	*/
function commonPrepareToDB ($text)
{
	global $isUTF8;
	global $language;

	$text = addslashes($text);

	if ($isUTF8)
	{
		if ($language == "" || $language == "HEB" || $language == "ENG")
			$text   = iconv("windows-1255", "utf-8", $text);
		else
			$text 	= html_entity_decode($text,	ENT_COMPAT, "UTF-8");
	}

	return $text;

}

/* --------------------------------------------------------------------------------------------	*/
/* commonPrepareToFile																			*/
/* --------------------------------------------------------------------------------------------	*/
function commonPrepareToFile ($text)
{
	global $isUTF8;

	$text = stripslashes($text);

	if ($isUTF8)
	{
		$text   = iconv("utf-8", "windows-1255//IGNORE", $text);
	}

	return $text;

}

/* --------------------------------------------------------------------------------------------	*/
/* commonEncode																					*/
/* --------------------------------------------------------------------------------------------	*/
function commonEncode ($text)
{
	global $isUTF8;

	if ($isUTF8)
			$text = iconv("windows-1255", "utf-8//IGNORE", $text);

	return $text;
}

/* --------------------------------------------------------------------------------------------	*/
/* commonDecode																					*/
/* --------------------------------------------------------------------------------------------	*/
function commonDecode ($text)
{
	return $text;
}

/* --------------------------------------------------------------------------------------------	*/
/* commonUndo																					*/
/* --------------------------------------------------------------------------------------------	*/
function commonUndo ($text)
{
	$text = str_replace("{TM}",  chr(153),$text);

	return $text;
}

$maxRowsInPage = 500;

/* --------------------------------------------------------------------------------------------	*/
/* commonGetLimit																				*/
/* --------------------------------------------------------------------------------------------	*/
function commonGetLimit ($xmlRequest)
{
	global $maxRowsInPage;

	$pageNumber  = xmlParser_getValue($xmlRequest, "pageNumber");

	if ($pageNumber == "" || $pageNumber < 1)
	{
		$limit  = xmlParser_getValue($xmlRequest, "limit");

		if ($limit == "")
			return "";
		else
		{
			if (!is_numeric($limit))
				$limit = 500;

				return " limit 0,$limit";
		}
	}

	$from = $maxRowsInPage*($pageNumber-1);
	return " limit $from,$maxRowsInPage ";
}

/* --------------------------------------------------------------------------------------------	*/
/* commonGetTotalXml																			*/
/* --------------------------------------------------------------------------------------------	*/
function commonGetTotalXml ($xmlRequest, $numRows, $total)
{
	global $maxRowsInPage;

	if ($total == 0)
	{
		$totalText   = 0;
		$pagesAmount = 0;
		$rowsTotal	 = 0;
	}
	else
	{
		$language    = xmlParser_getValue($xmlRequest, "language");
		$pageNumber  = xmlParser_getValue($xmlRequest, "pageNumber");
	
		$from	     = $maxRowsInPage*($pageNumber-1);

		$pagesAmount = floor($total/$maxRowsInPage);

			if (($total % $maxRowsInPage) != 0)
			$pagesAmount++;
	
		$rowsTotal	 = $maxRowsInPage;

		if ($language == "HEB")
			$outOfText = commonEncode(" מתוך ");
		else
			$outOfText = " out of ";

		$totalText = $from+1 . "-" . ($from+$numRows) . $outOfText . $total;
	}

	return "<totals>
				<totalText>$totalText</totalText>
				<pagesAmount>$pagesAmount</pagesAmount>
				<rowsTotal>$rowsTotal</rowsTotal>
		    </totals>";

	
}

/* --------------------------------------------------------------------------------------------	*/
/* commonArrayRemove																			*/
/* --------------------------------------------------------------------------------------------	*/
function commonArrayRemove ($array, $item)
{
	if (in_array ($item, $array))
	{
		$itemIndex = array_search($item, $array);

		if ($itemIndex == 0)
		{
			$array = array_slice($array,1);
		}
		else
		{
			$array = array_merge(array_slice($array, 0,$itemIndex-1),array_slice($array,$itemIndex+1));
		}
	}
	return $array;
}

/* --------------------------------------------------------------------------------------------	*/
/* commonGetIP																					*/
/* --------------------------------------------------------------------------------------------	*/
function commonGetIP()
{
	if (getenv("HTTP_CLIENT_IP") && strcasecmp(getenv("HTTP_CLIENT_IP"), "unknown"))
	{
        $ip = getenv("HTTP_CLIENT_IP");
	}
	else if (getenv("HTTP_X_FORWARDED_FOR") && strcasecmp(getenv("HTTP_X_FORWARDED_FOR"), "unknown"))
	{
		$ip = getenv("HTTP_X_FORWARDED_FOR");
	}
    else if (getenv("REMOTE_ADDR") && strcasecmp(getenv("REMOTE_ADDR"), "unknown"))
	{
        $ip = getenv("REMOTE_ADDR");
	}
    else if (isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && 
			 strcasecmp($_SERVER['REMOTE_ADDR'], "unknown"))
	{
		$ip = $_SERVER['REMOTE_ADDR'];
	}
    else
	{
    	$ip = "";
	}
    return($ip);
}

/* --------------------------------------------------------------------------------------------	*/
/* 	commonValidDateTime																			*/
/* --------------------------------------------------------------------------------------------	*/
function commonValidDateTime ($dateTime)
{
	$valid = false;
	$error = "יש להזין תאריך ושעה בפורמט יום-חודש-שנה שעה:דקות";

	$date  = "";
	$time  = "";

	$splited = explode(' ',$dateTime);

	if ($splited && count($splited) == 2)
	{
		$date	= $splited[0];
		$time	= $splited[1];
	}

	if ($date != "")
	{
		$date = str_replace(".", "/", $date);
		$date = str_replace("-", "/", $date);

		$splited = explode("/", $date);

		if ($splited && count($splited) == 3)
		{
			$day 	= $splited[0];
			$month	= $splited[1];
			$year	= $splited[2];
		}

		$valid = checkdate($month, $day, $year);

		if (!$valid)
		{
			$error = "תאריך לא חוקי";
		}
	}

	if ($valid)
	{
		$valid = false;
		$splited = explode(':', $time);

		if ($splited && count($splited) == 2)
		{
			$hour = $splited[0];
			$min  = $splited[1];
		}

		$valid = ($hour >= 0 && $hour < 24);
		
		if (!$valid)
		{
			$error = "שעה לא חוקית";
		}
		else
		{
			$valid = (strlen($min) == 2 && $min >=0 && $min < 60);

			if (!$valid)
				$error = "דקה לא חוקית";
		}
	}


	if ($valid)
		return ("");
	else
		return ($error);
}

/* --------------------------------------------------------------------------------------------	*/
/* commonUpdateCategoriesOf																		*/
/* --------------------------------------------------------------------------------------------	*/
function commonUpdateCategoriesOf ($xmlRequest, $itemId, $type)
{
	$queryStr = "delete from categoriesItems where itemId='$itemId' and type='$type'";
	commonDoQuery ($queryStr);

	$categories = xmlParser_getValues ($xmlRequest, "id");

	for ($i=0; $i<count($categories); $i++)
	{
		$queryStr = "insert into categoriesItems (itemId, categoryId, type) values ('$itemId', '$categories[$i]', '$type')";
		commonDoQuery ($queryStr);
	}
}

/* --------------------------------------------------------------------------------------------	*/
/* commonCategoryItemRow																		*/
/* --------------------------------------------------------------------------------------------	*/
function commonCategoryItemRow ($itemId, $itemType, $lang)
{
	$queryStr = "";

	switch ($itemType)
	{
		case "page"			:	
		case "album"		:
		case "essay"		:	$queryStr = "select title as name from pages_byLang where pageId = $itemId and language='$lang'";
								break;
		case "url"			:	$queryStr = "select title as name from urls_byLang where urlId = $itemId and language='$lang'";
								break;
		case "faq"			:	$queryStr = "select question as name from faq_byLang where faqId = $itemId and language='$lang'";
								break;
		case "gallery"  	:	$queryStr = "select title as name from galleries_byLang where galleryId = $itemId and language='$lang'";
								break;
		case "forum"	  	:	$queryStr = "select name from forum where id = $itemId";
								break;
		case "shop" 		:	$queryStr = "select name from shopProducts_byLang where productId = $itemId and language='$lang'";
								break;
		case "blog" 		:	$queryStr = "select name from blogs where id = $itemId";
								break;
		case "blogPost" 	:	$queryStr = "select title as name from blogsPosts where id = $itemId";
								break;
		case "banner" 		:	$queryStr = "select name from banners where id = $itemId";
								break;
		case "tabletItem" 	:	$queryStr = "select title as name from tabletItems_byLang where itemId = $itemId and language='$lang'";
								break;
		case "news" 		:	$queryStr = "select title as name from news_byLang where newsId = $itemId";
								break;
		case "phonesBook"	:	$queryStr = "select concat(concat(firstName,' '),lastName) as name from phonesRecords_byLang where recordId = $itemId";
								break;
		case "event"		:	$queryStr = "select name from events_byLang where eventId = $itemId";
								break;
		case "member"		:	$queryStr = "select concat(concat(firstname,' '),lastname) as name from clubMembers where id = $itemId";
								break;
		case "hug"			:	$queryStr = "select name from hugs_byLang where hugId = $itemId";
								break;
		case "specific"		:   
		case "specific2"	: 	$queryStr = "select specificCategorySql from globalParms";
								$result   = commonDoQuery($queryStr);
								$row	  = commonQuery_fetchRow($result);
								$queryStr = $row[0];

								$queryStr = str_replace("\$itemId", $itemId, $queryStr);

								break;
	}

	$row = "";
	if ($queryStr != "")
	{
		$result = commonDoQuery ($queryStr);
		$row    = commonQuery_fetchRow($result);
	}
	return $row;
}

/* --------------------------------------------------------------------------------------------	*/
/* commonGetLayoutName																			*/
/* --------------------------------------------------------------------------------------------	*/
function commonGetLayoutName ($layoutId)
{
	if ($layoutId == "") return "";

	global $usedLangs;
	$langsArray = explode(",",$usedLangs);

	static $layoutNamesCache = Array();
	$arrayKey = $layoutId."-".$langsArray[0];
	if (array_key_exists($arrayKey, $layoutNamesCache))
			return $layoutNamesCache[$arrayKey];
	else
	{
		// fill in the cache with all Layouts
		$queryStr   = "select layoutId, name, language from layouts_byLang";
		$result     = commonDoQuery ($queryStr);
		while ($layoutRow  = commonQuery_fetchRow($result))
		{
			$arrayKey = $layoutRow['layoutId']."-".$layoutRow['language'];
			$layoutName = commonValidXml($layoutRow['name']);
			$layoutNamesCache[$arrayKey] = $layoutName;
		}
	}

	$arrayKey = $layoutId."-".$langsArray[0];
	if (array_key_exists($arrayKey, $layoutNamesCache))
		return $layoutNamesCache[$arrayKey];

	return "";
}

/* --------------------------------------------------------------------------------------------	*/
/* commonFixText																				*/
/* --------------------------------------------------------------------------------------------	*/
function commonFixText ($text)
{
	$word1 = chr(194).chr(160);
	$word2 = chr(160);
	$word3 = chr(226).chr(128).chr(147);

	$text = addslashes($text);
	$text = str_replace($word1, "", $text);
	$text = str_replace($word2, " ", $text);
	$text = str_replace($word3, "-", $text);

	//debug 
	/*	$txtNum = "[".strlen($text)."]";
		for ($j=0; $j < strlen($text); $j++)
				$txtNum .= ord($text[$j])." ";

		$f = fopen ("/tmp/ord.txt", "a");
		fwrite($f, $txtNum);
		fclose ($f);
	 */

	return $text;

}

/* --------------------------------------------------------------------------------------------	*/
/* commonValidXstandard																			*/
/* --------------------------------------------------------------------------------------------	*/
function commonValidXstandard ($text)
{
	return $text;

	$NIS = chr(226).chr(130).chr(170);
	$NIScorrection = "ש\"ח";
	$text = str_replace($NIS, $NIScorrection, $text);

	/*
	if (strpos($text,"liat") != false)
	{
		trigger_error($text);
		trigger_error(ord(substr($text, strpos($text,"liat")+4, 1)));
		trigger_error(ord(substr($text, strpos($text,"liat")+1, 1)));	// 179
//		trigger_error(ord(substr($text, strpos($text,"liat")-9, 1)));	// 179
//		$text = str_replace(chr(179),"!!!",$text);
		$word = chr(195).chr(180);
		$text = str_replace($word, "&ocirc;", $text);
	}
	*/

	$word = chr(195).chr(179);
	$text = str_replace($word, "&oacute;", $text);

	$text = str_replace("&#1488;","א",$text);
	$text = str_replace("&#1489;","ב",$text);
	$text = str_replace("&#1490;","ג",$text);
	$text = str_replace("&#1491;","ד",$text);
	$text = str_replace("&#1492;","ה",$text);
	$text = str_replace("&#1493;","ו",$text);
	$text = str_replace("&#1494;","ז",$text);
	$text = str_replace("&#1495;","ח",$text);
	$text = str_replace("&#1496;","ט",$text);
	$text = str_replace("&#1497;","י",$text);
	$text = str_replace("&#1498;","ך",$text);
	$text = str_replace("&#1499;","כ",$text);
	$text = str_replace("&#1500;","ל",$text);
	$text = str_replace("&#1501;","ם",$text);
	$text = str_replace("&#1502;","מ",$text);
	$text = str_replace("&#1503;","ן",$text);
	$text = str_replace("&#1504;","נ",$text);
	$text = str_replace("&#1505;","ס",$text);
	$text = str_replace("&#1506;","ע",$text);
	$text = str_replace("&#1507;","ף",$text);
	$text = str_replace("&#1508;","פ",$text);
	$text = str_replace("&#1509;","ץ",$text);
	$text = str_replace("&#1510;","צ",$text);
	$text = str_replace("&#1511;","ק",$text);
	$text = str_replace("&#1512;","ר",$text);
	$text = str_replace("&#1513;","ש",$text);
	$text = str_replace("&#1514;","ת",$text);

	return $text;

	$validStr = "";

	
$f = fopen("/tmp/debug.txt","a+");
fwrite($f,"----------------------------------------------------------\n");
$debug = "";
	for($i=0; $i<$strLength; $i++) 
	{
		//$cha = $str[$i];
		//$ch = ord($cha);


 $ud = 0;
  if (ord($str[$i]{0})>=0 && ord($str[$i]{0})<=127)
   $ud = ord($str[$i]{0}); $chlen = 1;
  if (ord($str[$i]{0})>=192 && ord($str[$i]{0})<=223)
   $ud = (ord($str[$i]{0})-192)*64 + (ord($str[$i]{1})-128); $chlen = 2;
  if (ord($str[$i]{0})>=224 && ord($str[$i]{0})<=239)
   $ud = (ord($str[$i]{0})-224)*4096 + (ord($str[$i]{1})-128)*64 + (ord($str[$i]{2})-128); $chlen = 2;
  if (ord($str[$i]{0})>=240 && ord($str[$i]{0})<=247)
   $ud = (ord($str[$i]{0})-240)*262144 + (ord($str[$i]{1})-128)*4096 + (ord($str[$i]{2})-128)*64 + (ord($str[$i]{3})-128);
  if (ord($str[$i]{0})>=248 && ord($str[$i]{0})<=251)
   $ud = (ord($str[$i]{0})-248)*16777216 + (ord($str[$i]{1})-128)*262144 + (ord($str[$i]{2})-128)*4096 + (ord($str[$i]{3})-128)*64 + (ord($str[$i]{4})-128);
  if (ord($str[$i]{0})>=252 && ord($str[$i]{0})<=253)
   $ud = (ord($str[$i]{0})-252)*1073741824 + (ord($str[$i]{1})-128)*16777216 + (ord($str[$i]{2})-128)*262144 + (ord($str[$i]{3})-128)*4096 + (ord($str[$i]{4})-128)*64 + (ord($str[$i]{5})-128);
  if (ord($str[$i]{0})>=254 && ord($str[$i]{0})<=255) //error
   $ud = false;


		$debug .= "$cha = $ud\n";
		if (($ud > 1455 && $ud < 1476) || ($ud > 1519 && $ud < 1525) || ($ud > 8000))
				    //ch == 8364 || ch == 8482 || ch == 8230 || ch == 8205)
			$validStr .= "&#$ud;";
		else
			$validStr .= $cha;
	}

fwrite($f, $debug);
fclose($f);
	return $validStr;
}

/* --------------------------------------------------------------------------------------------	*/
/* commonGetExtraDataNames																		*/
/* --------------------------------------------------------------------------------------------	*/
function commonGetExtraDataNames ($tableName)
{
	$queryStr = "select * from $tableName";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);

	$xml = "<extraData1>" . commonValidXml($row['extraData1']) . "</extraData1>
			<extraData2>" . commonValidXml($row['extraData2']) . "</extraData2>
			<extraData3>" . commonValidXml($row['extraData3']) . "</extraData3>
			<extraData4>" . commonValidXml($row['extraData4']) . "</extraData4>
			<extraData5>" . commonValidXml($row['extraData5']) . "</extraData5>";

	if ($tableName == "clubMembersExtraData" || $tableName == "clubMembersExtraDataByType")
		$xml .= "<extraData6>" . commonValidXml($row['extraData6']) . "</extraData6>
			<extraData7>" . commonValidXml($row['extraData7']) . "</extraData7>
			<extraData8>" . commonValidXml($row['extraData8']) . "</extraData8>
			<extraData9>" . commonValidXml($row['extraData9']) . "</extraData9>
			<extraData10>" . commonValidXml($row['extraData10']) . "</extraData10>";

	return ($xml);
}

/* --------------------------------------------------------------------------------------------	*/
/* commonDoExcel																				*/
/* --------------------------------------------------------------------------------------------	*/
function commonDoExcel ($excelData)
{
	global $ibosHomeDir;

	$path = "$ibosHomeDir/tempExcels/";
	$excelFileName = commonExcelFileName ($path);

	$file = fopen ("$path/$excelFileName", "w");
	fwrite ($file,$excelData);
	fclose ($file);

	return ("<excelFileName>$excelFileName</excelFileName>");
}

/* --------------------------------------------------------------------------------------------	*/
/* commonExcelFileName																			*/
/* --------------------------------------------------------------------------------------------	*/
function commonExcelFileName ($path)
{
	$files = glob("$path/*.xls");

    # delete old files
    # -----------------------------------------------------------------------
	$existFiles = array();

	if ($files != "")
	{
		foreach ($files as $filename) 
		{
			$filedate =  filemtime($filename);
	
			if ($filedate + (60*60*60) < strtotime("now"))
			{
				unlink ($filename);
			}
			else
			{
				array_push ($existFiles, $filename);
			}
		}
    }

    # search for unique file name
    # -----------------------------------------------------------------------
	$code 	  = randomCode(6);
	$fileName = "excel" . $code . ".xls";
	while (in_array($fileName,$existFiles))
	{
		$code = randomCode(6);
		$fileName = "excel" . $code . ".xls";
	}
    return $fileName;
}

/* --------------------------------------------------------------------------------------------	*/
/* commonDoCsv																					*/
/* --------------------------------------------------------------------------------------------	*/
function commonDoCsv ($csbData)
{
	global $ibosHomeDir;

	$path = "$ibosHomeDir/tempExcels/";
	$csvFileName = commonCsvFileName ($path);

	$file = fopen ("$path/$csvFileName", "w");
	fwrite ($file,$csbData);
	fclose ($file);

	return ("<excelFileName>$csvFileName</excelFileName>");
}

/* --------------------------------------------------------------------------------------------	*/
/* commonCsvFileName																			*/
/* --------------------------------------------------------------------------------------------	*/
function commonCsvFileName ($path)
{
    # delete old files
    # -----------------------------------------------------------------------
	$existFiles = array();

/*	foreach (glob("$path/*.csv") as $filename) 
	{
		$filedate =  filemtime($filename);

		if ($filedate + (60*60*60) < strtotime("now"))
		{
			unlink ($filename);
		}
		else
		{
			array_push ($existFiles, $filename);
		}
    }
*/

    # search for unique file name
    # -----------------------------------------------------------------------
	$code 	  = randomCode(6);
	$fileName = "excel" . $code . ".csv";
	while (in_array($fileName,$existFiles))
	{
		$code = randomCode(6);
		$fileName = "excel" . $code . ".csv";
	}
    return $fileName;
}

/* --------------------------------------------------------------------------------------------	*/
/* randomCode																					*/
/* --------------------------------------------------------------------------------------------	*/
function randomCode ($length)
{
	$code   = "";
	mt_srand ((double) microtime() * 1000000); 
	while (strlen($code) < $length)
	{
		$c = chr(mt_rand (0,255)); 

		if (preg_match("/^[A-Z0-9]$/", $c)) 
			$code .= $c; 
	}
	return $code;
}

/* --------------------------------------------------------------------------------------------	*/
/* commonSendHtmlEmail																			*/
/* --------------------------------------------------------------------------------------------	*/
function commonSendHtmlEmail($fromName, $fromEmail, $toEmail, $subject, $message, $lang = "HEB")
{
	global $isUTF8;

	if ($isUTF8)
	{
		$charset = "UTF-8";
		$encodedSubject	= "=?$charset?B?".base64_encode($subject)."?=";
		$encodedFrom 	= "=?$charset?B?".base64_encode($fromName)."?= <$fromEmail>";

		$encodedMsg = chunk_split(base64_encode($message));

		$headers = "From: $encodedFrom" . "\r\n" .
			  "Reply-To: $encodedFrom" . "\r\n" .
			  "X-Mailer: PHP/" . phpversion() . "\r\n".
			  "MIME-Version: 1.0". "\r\n".
			  "Content-Type: text/html; charset=$charset\r\n".
			  "Content-Transfer-Encoding: base64";

		// [24/12/11 Amir] Patch for landvalue.org.il
		if ($fromEmail == "landvalu@netvision.net.il")
			$headers .= "\r\nErrors-To: $fromEmail";
	}
	else
	{
		switch ($lang)
		{
			case "HEB": $charset = "windows-1255"; break;
			default	  : $charset = "ISO-8859-1";   break;
		}
		
		$encodedSubject = "=?$charset?B?".base64_encode($subject)."?=";
		$encodedFrom = "=?$charset?B?".base64_encode($fromName)."?= <$fromEmail>";
		$encodedMsg = quoted_printable_encode($message);
	
		$headers = "From: $encodedFrom" . "\r\n" .
			  "Reply-To: $encodedFrom" . "\r\n" .
			  "X-Mailer: PHP/" . phpversion() . "\r\n".
			  "MIME-Version: 1.0". "\r\n".
			  "Content-Type: text/html; charset=$charset". "\r\n".
		   	  "Content-Transfer-Encoding: quoted-printable";
	}

	mail($toEmail,$encodedSubject,$encodedMsg,$headers);
}

function commonAdvancedSendHtmlEmail($fromName, $fromEmail, $toEmail, $subject, $message, $lang = "HEB")
{
	global $isUTF8;

	// Using PEAR Mail

	require_once('Mail.php');
	require_once('Mail/mime.php');

	if ($lang == "HEB" && !$isUTF8)
	{
			$fromName = iconv('windows-1255', 'utf-8', $fromName);
			$subject = iconv('windows-1255', 'utf-8', $subject);
			$message = iconv('windows-1255', 'utf-8', $message);
			$message = str_ireplace('windows-1255', 'utf-8', $message);
	}

	$encodedFrom = Mail_mimePart::encodeHeader("FROM", "$fromName <$fromEmail>", "utf-8", "base64");
	$encodedSubject = Mail_mimePart::encodeHeader("SUBJECT", $subject, "utf-8", "base64");

	$hdrs = array(
		"From" => $encodedFrom,
		"Reply-To" => $encodedFrom,
		"Subject" => $encodedSubject
           );

	$params = array(
		"eol" => "\n",
		"head_charset"	=> "utf-8",
		"text_charset"	=> "utf-8",
		"html_charset"	=> "utf-8",
		"head_encoding"	=> "base64",
		"text_encoding"	=> "base64",
		"html_encoding"	=> "base64"
	);

	$mime = new Mail_mime($params);

	$mime->setHTMLBody($message);

	// Find images in $message
	$attachments = array();
	$startAt = 0;
	while ($pos = stripos($message, "<img ", $startAt))
	{
		$endPos = strpos($message, ">", $pos);
		if (!$endPos) break;
		$srcPos = stripos($message, "src=", $pos);
		if (!$srcPos || $srcPos > $endPos) break;
		$srcEndPos = strpos($message, substr($message, $srcPos+4, 1), $srcPos+5);
		if (!$srcEndPos || $srcEndPos > $endPos) break;
		array_push($attachments, substr($message, $srcPos + 5, $srcEndPos - $srcPos - 5));
		$startAt = $srcEndPos;
	}

	if (is_array($attachments))
		foreach ($attachments as $att)
			$mime->addHTMLImage(file_get_contents($att),"image/png",$att,false);
			//$mime->addHTMLImage("../../".$att, "image/png", $att);

	$body = $mime->getMessageBody();

	$hdrs = $mime->headers($hdrs);

	$mail = Mail::factory('mail');
	$mail->send($toEmail, $hdrs, $body);
}

/* --------------------------------------------------------------------------------------------	*/
/* quoted_printable_encode																		*/
/* --------------------------------------------------------------------------------------------	*/
/*
function quoted_printable_encode($sText,$bEmulate_imap_8bit=true) 
{
  // split text into lines
  $aLines=explode(chr(13).chr(10),$sText);

  for ($i=0;$i<count($aLines);$i++) {
   $sLine =& $aLines[$i];
   if (strlen($sLine)===0) continue; // do nothing, if empty

   $sRegExp = '/[^\x09\x20\x21-\x3C\x3E-\x7E]/e';

   // imap_8bit encodes x09 everywhere, not only at lineends,
   // for EBCDIC safeness encode !"#$@[\]^`{|}~,
   // for complete safeness encode every character :)
   if ($bEmulate_imap_8bit)
     $sRegExp = '/[^\x20\x21-\x3C\x3E-\x7E]/e';

   $sReplmt = 'sprintf( "=%02X", ord ( "$0" ) ) ;';
   $sLine = preg_replace( $sRegExp, $sReplmt, $sLine );  

   // encode x09,x20 at lineends
   {
     $iLength = strlen($sLine);
     $iLastChar = ord($sLine{$iLength-1});

     //              !!!!!!!!    
     // imap_8_bit does not encode x20 at the very end of a text,
     // here is, where I don't agree with imap_8_bit, 
     // please correct me, if I'm wrong, 
     // or comment next line for RFC2045 conformance, if you like
     if (!($bEmulate_imap_8bit && ($i==count($aLines)-1)))
         
     if (($iLastChar==0x09)||($iLastChar==0x20)) {
       $sLine{$iLength-1}='=';
       $sLine .= ($iLastChar==0x09)?'09':'20';
     }
   }    // imap_8bit encodes x20 before chr(13), too
   // although IMHO not requested by RFC2045, why not do it safer :)
   // and why not encode any x20 around chr(10) or chr(13)
   if ($bEmulate_imap_8bit) {
     $sLine=str_replace(' =0D','=20=0D',$sLine);
     //$sLine=str_replace(' =0A','=20=0A',$sLine);
     //$sLine=str_replace('=0D ','=0D=20',$sLine);
     //$sLine=str_replace('=0A ','=0A=20',$sLine);
   }

   // finally split into softlines no longer than 76 chars,
   // for even more safeness one could encode x09,x20 
   // at the very first character of the line 
   // and after soft linebreaks, as well,
   // but this wouldn't be caught by such an easy RegExp                  
   preg_match_all( '/.{1,73}([^=]{0,2})?/', $sLine, $aMatch );
   $sLine = implode( '=' . chr(13).chr(10), $aMatch[0] ); // add soft crlf's
  }

  // join lines into text
  return implode(chr(13).chr(10),$aLines);
}
*/

/* --------------------------------------------------------------------------------------------	*/
/* commonSendHtmlEmailBySMTP																	*/
/* --------------------------------------------------------------------------------------------	*/
function commonSendHtmlEmailBySMTP($fromUser, $fromPassword, $fromName, $fromEmail, $toEmail, $toName, $subject, $message, $embedImages = false)
{
	/*
	 *  BEFORE CALLING TO THIS FUNCTION - SET THESE 3 GLOBAL VARIABLES:
	 */
	global $isUTF8;
	global $domainId;
	global $mailingListRow; // with key 'id'
	global $ibosHomeDir;

	require_once "PHPMailerAutoload.php";
	$mail = new PHPMailer(true);

	$bouncedEmail = 'info1@israeli-expert.co.il';

	try
	{
		//Server settings
		$mail->SMTPDebug = 0;                                 // Enable verbose debug output
		$mail->isSMTP();                                      // Set mailer to use SMTP
		$mail->Host = 'mail.israeli-expert.co.il';            // Specify main and backup SMTP servers
		$mail->SMTPAuth = true;                               // Enable SMTP authentication
		$mail->Username = "info1@israeli-expert.co.il";       // SMTP username
		$mail->Password = "info1";  	                      // SMTP password
		$mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
		$mail->Port = 587;                                    // TCP port to connect to
		$mail->CharSet = 'UTF-8';
		$mail->SMTPOptions = array(
			'ssl' => array(
				'verify_peer' => false,
				'verify_peer_name' => false,
				'allow_self_signed' => true
			)
		);

		if (!$isUTF8) // assuming old Hebrew sites
		{
			$fromName = iconv('windows-1255', 'utf-8', $fromName);
			$toName = iconv('windows-1255', 'utf-8', $toName);
			$subject = iconv('windows-1255', 'utf-8', $subject);
			$message = iconv('windows-1255', 'utf-8', $message);
			$message = str_ireplace('windows-1255', 'utf-8', $message);
		}
		$mail->CharSet = 'UTF-8';

		//Recipients
		$mail->setFrom($fromEmail, $fromName);
		$mail->addReplyTo($fromEmail, $fromName);

		$mail->addCustomHeader("Precedence: bulk;");
		$mail->addCustomHeader("X-domainId: $domainId;"); // when e-mail is bounced - we need to identify which domain sent it
		$mail->addCustomHeader("X-mlid: $mailingListRow[id];"); // and also mailing list id

		foreach (explode(',', $toEmail) as $toAnEmail)
			$mail->AddAddress($toAnEmail , $toName);

		if ($embedImages)
		{
			// Find images in $message
			$attachments = array();
			$startAt = 0;
			while ($pos = stripos($message, "<img ", $startAt))
			{
				$endPos = strpos($message, ">", $pos);
				if (!$endPos) break;
				$srcPos = stripos($message, "src=", $pos);
				if (!$srcPos || $srcPos > $endPos) break;
				$srcEndPos = strpos($message, substr($message, $srcPos+4, 1), $srcPos+5);
				if (!$srcEndPos || $srcEndPos > $endPos) break;
				array_push($attachments, substr($message, $srcPos + 5, $srcEndPos - $srcPos - 5));
				$startAt = $srcEndPos;
			}
			// Replace images with CID and add them as embeded images
			foreach ($attachments as $att)
			{
				// assuming $att is a full-url like http://www.domain.com/designFiles/xyz.png
				$cidFileName = rand();
				$fileName = substr($att, strrpos($att, '/')+1);
				if (!file_exists("filesForSending/$fileName"))
						copy($att, "filesForSending/$fileName");
				$mail->AddEmbeddedImage("filesForSending/$fileName", $cidFileName, $cidFileName);
				$message = str_replace($att, "cid:$cidFileName", $message);
			}
		}

		$mail->WordWrap = 50;               // set word wrap
		$mail->Priority = 3;
		$mail->Subject  = $subject;
		$mail->Body     = $message;
		$mail->IsHTML(true);  

		if (!$mail->Send())
		{
			mail($bouncedEmail, "commonSendHtmlEmailBySMTP Sending Error: " . $mail->ErrorInfo, $message);
		}

	} catch (Exception $e)
		{
			mail($bouncedEmail, "commonSendHtmlEmailBySMTP Mailer Error: " . $e->getMessage() . " for mail $toEmail", $message);
		}
}

/* --------------------------------------------------------------------------------------------	*/
/* commonCutText																				*/
/* --------------------------------------------------------------------------------------------	*/
function commonCutText ($text, $len, $after = "")
{
	global $isUTF8;

	if ($isUTF8 == "1")
	{
		if (mb_strlen($text, "utf8") > $len)
		{
			$text = mb_substr($text,0,$len, "utf8") ;
		
			$matchpoint = mb_strrpos($text, " ", "utf8");
			if ($matchpoint === false)
				$text = mb_substr($text,0,$len, "utf8");
			else
				$text = mb_substr($text,0,$matchpoint, "utf8");

			$text .= $after;
		}
	}
	else
	{
		if (strlen($text) > $len)
		{
			$text = substr($text,0,$len) . "...";
		}
	}

	return $text;
}

/* --------------------------------------------------------------------------------------------	*/
/* commonGetEnumValue																			*/
/* --------------------------------------------------------------------------------------------	*/
function commonGetEnumValue ($valueId)
{
	if ($valueId == "") return "";

	$sql	= "select text from enumsValues_byLang where valueId = '" . addslashes($valueId) . "'";
	$result	= commonDoQuery($sql);

	if (commonQuery_numRows($result) == 0)
		return commonValidXml($valueId);

	$row	= commonQuery_fetchRow($result);

	return (commonValidXml($row[0]));
}

/* --------------------------------------------------------------------------------------------	*/
/* commonGetDimensionDetails																	*/
/* --------------------------------------------------------------------------------------------	*/
function commonGetDimensionDetails ($dimensionId)
{
	if ($dimensionId == 0)
	{
		$picWidth  = 0;
		$picHeight = 0;
		$bgColor   = "#FFFFFF";
		$forceSize = "0";
		$allowCrop = "0";
	}
	else if ($dimensionId != "")
	{
		$queryStr   = "select width, height, color, forceSize, allowCrop from dimensions where id = $dimensionId";
		$result		= commonDoQuery ($queryStr);
		$row		= commonQuery_fetchRow ($result);

		$picWidth 	= $row['width'];
		$picHeight 	= $row['height'];
		$bgColor 	= $row['color'];
		$forceSize 	= $row['forceSize'];
		$allowCrop	= $row['allowCrop'];
	}
	else
	{
		$picWidth  = "200";
		$picHeight = "100";
		$bgColor   = "#FFFFFF";
		$forceSize = "0";
		$allowCrop = "0";
	}

	return Array($picWidth, $picHeight, $bgColor, $forceSize, $allowCrop);
}

/* --------------------------------------------------------------------------------------------	*/
/* commonDeleteOldFiles																			*/
/* --------------------------------------------------------------------------------------------	*/
function commonDeleteOldFiles ($filePath, $cacheTime)
{
    $time0 = time();

	if (!file_exists ($filePath)) return;

    if ($dir = opendir("$filePath"))
    {
        while ($fn = readdir($dir))
        {
            if ($time0 - filemtime("$filePath/$fn") >= $cacheTime)
            {
                if (!($fn == '.' or $fn == '..' or is_dir("$filePath/$fn")))
                   unlink("$filePath/$fn");   // remove it
            }
          }
          closedir($dir);
    }
}

/* --------------------------------------------------------------------------------------------	*/
/* commonDuplicateRowTable																		*/
/* --------------------------------------------------------------------------------------------	*/
function commonDuplicateRowTable ($newId, $fromId, $tableName, $key)
{
	$sql	= "desc $tableName";
	$result	= commonDoQuery($sql);

	$cols		= "";
	$selectCols = "";
	while ($row = commonQuery_fetchRow($result))
	{
		$cols .= ", $row[Field]";
		if ($row['Field'] != $key)
		{
			$selectCols .= ", $row[Field]";
		}
	}

	$cols 		= trim($cols, ", ");
	$selectCols = trim($selectCols, ", ");

	$sql = "insert into $tableName ($cols) select $newId, $selectCols from $tableName where $key = $fromId";
	commonDoQuery ($sql);
}

/* --------------------------------------------------------------------------------------------	*/
/* commonHyperlink																				*/
/* --------------------------------------------------------------------------------------------	*/
function commonHyperlink (&$text)
{
	return preg_replace( "`((http)+(s)?:(//)|(www\.))((\w|\.|\-|_)+)(/)?(\S+)?`i",
						 "<a href=\"http\\3://\\5\\6\\8\\9\" target=\"_new\">\\5\\6\\8\\9</a>", $text);
}

/* --------------------------------------------------------------------------------------------	*/
/* commonGetFlagsByType																			*/
/* --------------------------------------------------------------------------------------------	*/
function commonGetFlagsByType ($type)
{
	$queryStr    = "select * from flagsConfig where type = '$type' order by id";
	$result	     = commonDoQuery ($queryStr);

	$flags  = array();
	while ($row = commonQuery_fetchRow($result))
	{
		array_push ($flags, $row['id']);
	}

	return $flags;

}

/* --------------------------------------------------------------------------------------------	*/
/* commonGetItemFlags																			*/
/* --------------------------------------------------------------------------------------------	*/
function commonGetItemFlags ($itemId, $itemType)
{
	$sql	= "select * from flags where itemId = $itemId and itemType = '$itemType'";
	$result = commonDoQuery($sql);

	$flags  = array();
	while ($row = commonQuery_fetchRow($result))
	{
		array_push ($flags, array($row['flagId'], $row['value']));
	}

	return $flags;
}

/* --------------------------------------------------------------------------------------------	*/
/* commonGetItemFlagsXml																		*/
/* --------------------------------------------------------------------------------------------	*/
function commonGetItemFlagsXml ($flags, $type)
{
	$xml = "";

	$myFlags = array();
	foreach ($flags as $flag)
	{
		list($flagId, $flagValue) = $flag;

		$xml .= "<flag$flagId>$flagValue</flag$flagId>";

		array_push ($myFlags, $flagId);
	}

	// get other flags (those i don't have)
	$allFlags = commonGetFlagsByType ($type);

	foreach ($allFlags as $flag)
	{
		if (!in_array($flag, $myFlags))
			$xml .= "<flag$flag>0</flag$flag>";
	}

	
	return $xml;
}

/* --------------------------------------------------------------------------------------------	*/
/* commonSaveItemFlags																			*/
/* --------------------------------------------------------------------------------------------	*/
function commonSaveItemFlags ($itemId, $itemType, $xmlRequest = "")
{
	$flags = commonGetFlagsByType ($itemType); 

	if (count($flags) != 0)
	{
		// delete old flags
		$queryStr = "delete from flags where itemId = $itemId and itemType = '$itemType'";
		commonDoQuery ($queryStr);

		foreach ($flags as $flag)
		{
			if ($xmlRequest == "")
					$value = $_POST['flag' . $flag];
			else
				$value = xmlParser_getValue($xmlRequest, "flag$flag");

			$queryStr = "insert into flags (flagId, itemId, itemType, value) values ($flag, $itemId, '$itemType', '$value')";
			commonDoQuery ($queryStr);
		}
	}
}

/* --------------------------------------------------------------------------------------------	*/
/* commonHasMobileVersion																		*/
/* --------------------------------------------------------------------------------------------	*/
function commonHasMobileVersion ()
{
	$queryStr	= "select hasMobileVersion from globalParms";
	$result		= commonDoQuery($queryStr);
	$row		= commonQuery_fetchRow($result);

	return ($row['hasMobileVersion'] == 1);
}

/* --------------------------------------------------------------------------------------------	*/
/* commonIncludeFileFromWebsite																	*/
/* --------------------------------------------------------------------------------------------	*/
function commonIncludeFileFromWebsite ($fileToInclude)
{
	$temp = tmpfile();

	$domainRow	= commonGetDomainRow();
	$connId 	= commonFtpConnect($domainRow); 

	commonConnectToUserDB ($domainRow);

	$fileSize = ftp_size($connId, $fileToInclude);
	ftp_fget ($connId, $temp, $fileToInclude, FTP_BINARY);
	
	rewind ($temp);

	$content = fread($temp, $fileSize);

	$content = str_replace('<?', "", $content);		// remove php start
	$content = str_replace('?'.'>', "", $content);		// remove php end

	eval($content);
}

$dimensionsArr = array();
/* --------------------------------------------------------------------------------------------	*/
/* commonPicResize																				*/
/* --------------------------------------------------------------------------------------------	*/
function commonPicResize($origFile, $destFile, $dimensionId)
{
		require_once("picsTools.php");

		if ($dimensionId == "")
			return;

		$domainRow	= commonGetDomainRow();

		if (!isset($dimensionsArr[$dimensionId]))
		{
				commonConnectToUserDB ($domainRow);
				$queryStr   					= "select * from dimensions where id = $dimensionId";
				$result							= commonDoQuery ($queryStr);
				if (commonQuery_numRows($result) == 0)
						return;
				$dimensionsArr[$dimensionId]	= commonQuery_fetchRow ($result);
		}

		if ($dimensionsArr[$dimensionId]['watermarkFile'])
				picsToolsAddWatermark ($origFile, commonGetDomainName($domainRow).'/dimensionsFiles/'.$dimensionsArr[$dimensionId]['watermarkFile'], $origFile);

		if ($dimensionsArr[$dimensionId]['forceSize'])
				picsToolsForceResize ($origFile, "", $dimensionsArr[$dimensionId]['width'], $dimensionsArr[$dimensionId]['height'], $destFile);
		else
				picsToolsResize ($origFile, "", $dimensionsArr[$dimensionId]['width'], $dimensionsArr[$dimensionId]['height'], $destFile,
					   			$dimensionsArr[$dimensionId]['color'], 98, $dimensionsArr[$dimensionId]['allowCrop']);
}

/* --------------------------------------------------------------------------------------------	*/
/* commonFileSuffix																				*/
/* --------------------------------------------------------------------------------------------	*/
function commonFileSuffix ($filename)
{
	$splitName 	= explode(".",$filename);
	$suffix 	= "";
	if (count($splitName) > 0)
		$suffix	= "." . $splitName[count($splitName)-1];

	return $suffix;
}

/* -------------------------------------------------------------------------------------------------------------------- */
/* commonGetBoxContent																									*/
/* -------------------------------------------------------------------------------------------------------------------- */
function commonGetBoxContent ($boxName, $lang = "HEB")
{
	$sql			= "select content from boxes, boxes_byLang where id = boxId and boxes_byLang.language = '$lang' and boxName = '$boxName'";
	$result			= commonDoQuery($sql);
	$boxRow			= commonQuery_fetchRow($result);

	return stripslashes($boxRow['content']);
}

/* -------------------------------------------------------------------------------------------------------------------- */
/* commonGetLayoutHtml																									*/
/* -------------------------------------------------------------------------------------------------------------------- */
function commonGetLayoutHtml ($layoutId, $lang = "HEB")
{
	$sql	 = "select text from layouts_byLang where layoutId = $layoutId and language = '$lang'";
	$result  = commonDoQuery($sql);
	$row	 = commonQuery_fetchRow($result);

	return stripslashes($row['text']);
}

/* -------------------------------------------------------------------------------------------------------------------- */
/* commonGetLayoutSwitchHtml																							*/
/* -------------------------------------------------------------------------------------------------------------------- */
function commonGetLayoutSwitchHtml ($switchName, $lang = "HEB")
{
	$sql	 = "select text from layoutSwitches_byLang where language = '$lang' and name = '$switchName'";
	$result  = commonDoQuery($sql);
	$row   	 = commonQuery_fetchRow($result);
	
	return stripslashes($row['text']);
}

/* -------------------------------------------------------------------------------------------------------------------- */
/* commonFixRewriteName																									*/
/* -------------------------------------------------------------------------------------------------------------------- */
function commonFixRewriteName ($rewriteName)
{
	$rewriteName	= str_replace(" ", "-", addslashes($rewriteName));

	return $rewriteName;
}

/* -------------------------------------------------------------------------------------------------------------------- */
/* commonCheckRewriteName																								*/
/* -------------------------------------------------------------------------------------------------------------------- */
function commonCheckRewriteName ($rewriteName, $pageId = 0)
{
	$rewriteName	= commonFixRewriteName($rewriteName);

	if ($rewriteName != "")
	{
		$sql 			= "select * from pages_byLang where rewriteName = '$rewriteName' and pageId != '$pageId' and pageId > 0";
		$result	     	= commonDoQuery ($sql);

		return (commonQuery_numRows($result) == 0);
	}

	return true;
}

?>
