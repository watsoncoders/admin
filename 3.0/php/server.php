<?php

/* debug by amir */
/*$f = fopen("/tmp/homm.txt","a+");
fwrite($f,date('l dS \of F Y h:i:s A'));
fwrite($f,"\n[$HTTP_RAW_POST_DATA]\n");
fwrite($f,"##############################################\n");
fflush($f);
fclose($f);
/* */

if (function_exists("date_default_timezone_set"))
	date_default_timezone_set ("Asia/Tel_Aviv");

include "commonAdmin.php";
include "xmlParser.php";
include "format.php";

/* ----------------------------------------------------------------------------------------------------	*/
/* errorHandler																							*/
/*																										*/
/* 	error reporting level constants :																	*/
/*	- 1 	E_ERROR  																					*/
/*	- 2 	E_WARNING  																					*/
/*	- 4 	E_PARSE  																					*/
/*	- 8 	E_NOTICE  																					*/
/*	- 16	E_CORE_ERROR  																				*/
/*	- 32 	E_CORE_WARNING  																			*/
/*	- 64	E_COMPILE_ERROR  																			*/
/*	- 128 	E_COMPILE_WARNING  																			*/
/*	- 256 	E_USER_ERROR  																				*/
/*	- 512 	E_USER_WARNING  																			*/
/*	- 1024	E_USER_NOTICE  																				*/
/*	- 2047 	E_ALL  																						*/
/*	- 2048	E_STRICT  																					*/
/*																										*/
/*	In order to ignore error call error_reporting.														*/
/*																										*/
/*	Examples :																							*/
/*																										*/
/* 	// Turn off all error reporting																		*/
/*	error_reporting(0);																					*/
/*																										*/
/* 	// Report simple running errors																		*/
/* 	error_reporting(E_ERROR | E_WARNING | E_PARSE);														*/
/*																										*/
/*	// Reporting E_NOTICE can be good too (to report uninitialized										*/
/*	// variables or catch variable name misspellings ...)												*/
/*	error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);											*/
/*																										*/
/*	// Report all errors except E_NOTICE																*/
/*	// This is the default value set in php.ini															*/
/*	error_reporting(E_ALL ^ E_NOTICE);																	*/
/*																										*/
/*	// Report all PHP errors																			*/
/*	error_reporting(E_ALL);																				*/
/*																										*/
/* ----------------------------------------------------------------------------------------------------	*/
function errorHandler ($errno, $errstr, $errfile, $errline)
{
	global $HTTP_RAW_POST_DATA, $sessionCode;

	if ($errstr != "")
		$errstr = iconv("windows-1255", "utf-8", $errstr);
	
	echo "<?xml version='1.0' encoding='UTF-8' ?>" 		.
		 "<interuse>"									.
			"<response>"								.
				"<responseType>Error</responseType>"	.
				"<message>$errstr</message>"			.
				"<file>$errfile</file>"					.
				"<line>$errline</line>"					.
			"</response>"								.
		 "</interuse>";

	$mysqlHandle 	= commonConnectToDB();
	$userRow 		= commonGetUserRow ($sessionCode);
	$domainId 		= $userRow['domainId'];

	$userAgent = $_SERVER['HTTP_USER_AGENT']; 

    // First get the platform?
	if (preg_match('/linux/i', $userAgent)) 
	{
		$platform = 'linux';
    }
	elseif (preg_match('/macintosh|mac os x/i', $userAgent)) 
	{
    	$platform = 'mac';
    }
	elseif (preg_match('/windows|win32/i', $userAgent)) 
	{
    	$platform = 'windows';
    }
	
    if(preg_match('/MSIE/i',$userAgent) && !preg_match('/Opera/i',$userAgent)) 
    { 
    	$bname 	= 'Internet Explorer'; 
        $ub 	= "MSIE"; 
    } 
    elseif(preg_match('/Firefox/i',$userAgent)) 
    { 
    	$bname 	= 'Mozilla Firefox'; 
        $ub 	= "Firefox"; 
    } 
    elseif(preg_match('/Chrome/i',$userAgent)) 
    { 
    	$bname 	= 'Google Chrome'; 
        $ub 	= "Chrome"; 
    } 
    elseif(preg_match('/Safari/i',$userAgent)) 
    { 
    	$bname 	= 'Apple Safari'; 
        $ub 	= "Safari"; 
    } 
    elseif(preg_match('/Opera/i',$userAgent)) 
    { 
		$bname 	= 'Opera'; 
        $ub 	= "Opera"; 
    } 
    elseif(preg_match('/Netscape/i',$userAgent)) 
    { 
        $bname 	= 'Netscape'; 
        $ub 	= "Netscape"; 
    } 

    // finally get the correct version number
    $known 	= array('Version', $ub, 'other');
    $pattern 	= '#(?<browser>' . join('|', $known) .  ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';

	if (!preg_match_all($pattern, $userAgent, $matches)) 
	{
    	// we have no matching number just continue
    }
     
    // see how many we have
    $i = count($matches['browser']);
	if ($i != 1) 
	{
        // we will have two since we are not using 'other' argument yet see if version is before or after the name
		if (strripos($userAgent,"Version") < strripos($userAgent,$ub))
		{
            $version= $matches['version'][0];
        }
		else 
		{
             $version= $matches['version'][1];
        }
    }
	else 
	{
         $version= $matches['version'][0];
    }
     
    // check if we have a number
    if ($version == null || $version == "") { $version="?"; }
	
	if (strpos($errstr, "Undefined offset") === false)
		mail ("liat@interuse.com", "I-Bos ERROR", "Session code = $sessionCode\nDomain = $domainId\nUser = $userRow[id] - $userRow[username]\nError Message: $errstr\nFile: $errfile\nLine: $errline\nBroswer: $bname $version $platform\nXml:\n$HTTP_RAW_POST_DATA\nServer: " . var_export($_SERVER, true));

	exit;
}

function trigger_debug ($debugStr)
{
	if ($debugStr != "")
		$debugStr = iconv("windows-1255", "utf-8", $debugStr);
	

	echo "<?xml version='1.0' encoding='UTF-8' ?>" 		.
		 "<interuse>"									.
			"<response>"								.
				"<responseType>Debug</responseType>"	.
				"<message>$debugStr</message>"			.
			"</response>"								.
		 "</interuse>";
	exit;
}

set_error_handler ("errorHandler", E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED & ~E_WARNING);

if (PHP_VERSION_ID >= 70000)
{
	$HTTP_RAW_POST_DATA = file_get_contents("php://input");
}

if (strpos($HTTP_RAW_POST_DATA, "pleaseWaitMsg") !== false || strpos($HTTP_RAW_POST_DATA, "התוכן נטען כעת") !== false)
	trigger_error ("!טעינת הנתונים לא הסתיימה. לא לבצע שמירה");

/*
include("ConvertCharset.class.php");
$FromCharset = "utf-8";
$ToCharset = "windows-1255";
$text = new ConvertCharset();

$contents= $text ->Convert($HTTP_RAW_POST_DATA, $FromCharset, $ToCharset);
$xmlRequest = xmlParser_parse ($contents);
 */

//mail("amir@interuse.co.il","before",$HTTP_RAW_POST_DATA);
//mail("amir@interuse.co.il","after",iconv("utf-8", "windows-1255", $HTTP_RAW_POST_DATA));
//$xmlRequest = xmlParser_parse (iconv("utf-8", "windows-1255", $HTTP_RAW_POST_DATA));
$xmlRequest = xmlParser_parse ($HTTP_RAW_POST_DATA);

$sessionCode = xmlParser_getValue ($xmlRequest, "sessionCode");
$userId 	 = xmlParser_getValue ($xmlRequest, "userId");
$usedLangs 	 = xmlParser_getValue ($xmlRequest, "usedLangs");
$command     = xmlParser_getValue ($xmlRequest, "command");
$requestId   = xmlParser_getValue ($xmlRequest, "requestId");

$splitCommand = explode(".",$command);

if ($splitCommand[1] != "relogin" && $splitCommand[1] != "saveChoice" && !commonValidateSession ())
{
	echo "<interuse>
			 <response>
				 <responseType>SessionExpired</responseType>
				 <message></message>
				 <file></file>
				 <line></line>
			 </response>
		  </interuse>";
	exit;
}

if (!isset($cookie_guiLang) || $cookie_guiLang == "")
	$cookie_guiLang = "HEB";

# get function name & do include
# --------------------------------------------------------------------------------------------
$functionName = $splitCommand[1];
$fileName     = "commands_$splitCommand[0].php";

// get domainId
# --------------------------------------------------------------------------------------------
$mysqlHandle = commonConnectToDB();
$userRow = commonGetUserRow ($sessionCode);
$domainId = $userRow['domainId'];

/*if ($splitCommand[1] == "updateEssay" && $domainId == 407)
{
	mail ("liat@interuse.com", "XML", $HTTP_RAW_POST_DATA);
	mail ("amir@interuse.co.il", "XML", $HTTP_RAW_POST_DATA);
}
 */
/* closing i-bos 
if ($domainId != 383 && $domainId != 294) // interco = 103, emethova = 142, newinter = 367
{
	trigger_error ("18.03.2012 22:30 השרת בעבודות תחזוקה עד לבוקר. עמכם הסליחה");
}
/* */

if (strpos($HTTP_RAW_POST_DATA, "</interuse>") === false)
{
	mail ("liat@interuse.com", "I-Bos - incomplete request", "Session code = $sessionCode\nDomain = $domainId\nUser = $userRow[id] - $userRow[username]\nXml:\n$HTTP_RAW_POST_DATA");

	trigger_error ("בעיה שמירת הנתונים. יש לבצע שמירה שוב!");
}

//if ($domainId == "153")
//{
//		mail ("liat@interuse.com", "FreedomFromFood - DEBUG", "Session code = $sessionCode\nDomain = $domainId\nUser = $userRow[id] - $userRow[username]\nXml:\n$HTTP_RAW_POST_DATA");
//}

if ($fileName != "commands_user.php" && $functionName != "getSiteLangs" && $functionName != "getAllPages" && $functionName != "getStyles" && 
	$functionName != "getSiteNames" && $functionName != "getSiteName" && $fileName != "commands_flags.php" && $fileName != "commands_general.php" &&
	$fileName != "commands_enums.php")
{

	// get domainId of the command/feature
	# ----------------------------------------------------------------------------------------
	$featureDomainId = "0";

	$sql 	= "select id, domainId from features_utf8 where concat(commandsFile,'.php') = '$fileName' and  (domainId = 0 or domainId = '$domainId')";
	$result = commonDoQuery($sql);
	if (commonQuery_numRows($result) != 0)
	{
		$featureRow	= commonQuery_fetchRow($result);
		$featureDomainId = $featureRow['domainId'];
	}
	else
	{
		trigger_error ("תקלה - נא לפנות לתמיכה ($domainId - $fileName)");
	}

	// get usersFeatures table name by user brand name
	# ----------------------------------------------------------------------------------------
	$usersFeaturesTable = "usersFeatures";

	// check user permission for this feature
	# ----------------------------------------------------------------------------------------
	$sql = "select count(*) from $usersFeaturesTable where userId = $userId and featureId = $featureRow[id]";
	$result = commonDoQuery($sql);
	$row    = commonQuery_fetchRow($result);
	if ($row[0] == 0)
	{
		trigger_error ("אין לך הרשאה לביצוע הפעולה ($featureRow[id] - $fileName - $command). אנא פנה לתמיכה");
	}
}

if (strpos($functionName, "get") 		 === false && 
	strpos($functionName, "update") 	 === false && 
	strpos($functionName, "excelReport") === false && 
	strpos($functionName, "preview") 	 === false)
{
	// check that this is a unique request - only for actions requests
	$queryStr	= "select * from requestsLog where requestId = '$requestId' and requestId != ''";
	$result		= commonDoQuery($queryStr);
	if (commonQuery_numRows($result) != 0)
	{
		// ignore this request
		mail ("liat@interuse.com", "I-Bos Duplicate", "Session code = $sessionCode\nDomain = $domainId\nUser = $userRow[id] - $userRow[username]\nFunction Name: $functionName\nXml:\n$HTTP_RAW_POST_DATA");

		echo "<interuse>
				 <response>
					 <responseType>Duplicate</responseType>
					 <command>$command</command>
					 <requestId>$requestId</requestId>
					 <responseData>
					 </responseData>
				 </response>
			  </interuse>";
		exit;
	}
}

// delete old requests
$before 	 = date("Y-m-d H:i:00", strtotime("-1 hours"));
$queryStr    = "delete from requestsLog where datetime < '$before'";
commonDoQuery ($queryStr);

// add new request
$queryStr = "insert into requestsLog (requestId, datetime) values ('$requestId', now())";
commonDoQuery($queryStr);


//commonDisconnect ($mysqlHandle);


//if ($domainId != 135 && $fileName == "commands_forums.php")
//{
//	trigger_error ("תת מערכת פורומים בבדיקה. נא להיכנס מאוחר יותר. תודה");
//}


if (count($splitCommand) != 2)
{
	trigger_error ("Wrong command name format (missing server file name) - $command");
}

if ($splitCommand[1] != "relogin" && $splitCommand[1] != "saveChoice" && !commonValidateSession ())
{
	echo "<interuse>
			 <response>
				 <responseType>SessionExpired</responseType>
				 <message></message>
				 <file></file>
				 <line></line>
			 </response>
		  </interuse>";
	exit;
}



if (!file_exists($fileName))
{
	// this is a plugin file
	# ----------------------------------------------------------------------------------------
		
	if ($featureDomainId == "0")
	{
		trigger_error ("אין הרשאה לביצוע הפעולה. אנא פנה לתמיכה");
	}
	
	$fileName = "plugins/" . abs($featureDomainId) . "/$fileName";
}

if ($functionName == "getUserMsgs" || $functionName == "getMsgDetails")
	$isUTF8 = 0;

eval ("include \"$fileName\";");

$xmlResponse = "<?xml version='1.0' encoding='UTF-8' ?>\n" 	. 
			   "<interuse>\n"								.
			   "	<response>\n"; 
					
if (function_exists($functionName))
{
	$dummyTags	= xmlParser_getDummyTags($xmlRequest);

	$xmlResponse .= 	"<responseType>Success</responseType>
						 <command>$command</command>
						 <requestId>$requestId</requestId>
						 <responseData>\n$dummyTags";

	$commandXml   = call_user_func($functionName, $xmlRequest);

	if ($commandXml != "")
	{
			if ($domainId == 135)
			{
					$hebKeys = explode(",","ֳ ,ֳ¡,ֳ¢,ֳ£,ֳ₪,ֳ¥,ֳ¦,ֳ§,ֳ¨,ֳ©,ֳ×,ֳ«,ֳ¬,ֳ­,ֳ®,ֳ¯,ֳ°,ֳ±,ֳ²,ֳ³,ֳ´,ֳµ,ֳ¶,ֳ·,ֳ¸,ֳ¹,ֳ÷");
					$hebVals = explode(",","א,ב,ג,ד,ה,ו,ז,ח,ט,י,ך,כ,ל,ם,מ,ן,נ,ס,ע,ף,פ,ץ,צ,ק,ר,ש,ת");
					$commandXml = str_replace($hebKeys, $hebVals, $commandXml);
			}

			if (!$isUTF8)
					$xmlResponse .= iconv("windows-1255", "utf-8//IGNORE", $commandXml);
			else
					$xmlResponse .= $commandXml;
	}

	$xmlResponse .= 	"</responseData>\n";
}
else
{
	$xmlResponse .= 	"<responseType>Error</responseType>
						 <message>Command '$command' does not exist</message>\n";
}

$xmlResponse .= 	"</response>
				 </interuse>";

// if (($domainId == 304 && $functionName == "getGlobalParms") || xmlParser_getValue ($xmlRequest, "debugXml") == 1)
if (($domainId == 304) || xmlParser_getValue ($xmlRequest, "debugXml") == 1)
{
		// write in xml
		$fileHandle = fopen("$ibosHomeDir/xmlDebug/in.xml", "w");
		fwrite ($fileHandle, $HTTP_RAW_POST_DATA);
		fclose ($fileHandle);

		$fileHandle = fopen("$ibosHomeDir/xmlDebug/out.xml", "w");
		fwrite ($fileHandle, $xmlResponse);
		fclose ($fileHandle);
}

#iconv_set_encoding ("output_encoding", "ISO-8859-1");

echo $xmlResponse;
?>
