<?php

/* ----------------------------------------------------------------------------------------------------	*/
/* getFiles																								*/
/* ----------------------------------------------------------------------------------------------------	*/
function getFiles ($xmlRequest)
{
	$domainRow = commonGetDomainRow ();

	$connId    = commonFtpConnect	($domainRow);
	
	$files = ftp_rawlist($connId, "-at loadedFiles");

   	ftp_close($connId);

	$showPicText = commonEncode("��� �����");

	$xmlResponse = "<items>";

   	$i = 0;
	
   	foreach ($files as $file) 
	{
       $split = preg_split("[ ]", $file, 9, PREG_SPLIT_NO_EMPTY);

	   // 0 - permissions		1 - number
	   // 2 - owner				3 - group
	   // 4 - size				5 - mount
	   // 6 - day				7 - time/year
	   // 8 - name

	   // windows
	   // 0 - date
	   // 1 - hour
	   // 2 - size
	   // 3 - filename
	   
       if ($split[0] != "total" && $split[0]{0} !== "d") 
	   {
			if (count($split) < 8)
			{
		   		$date 	 	= substr($split[0],0,5) . " " . substr($split[1],0,5);
				$fileName 	= $split[3];
				$fileSize 	= $split[2];
				$perms		= "";
			}
			else
			{
		   		$date 		= date("d-m-Y", strtotime($split[6] . $split[5])) . " " . $split[7];
				$fileName 	= $split[8];
				$fileSize 	= $split[4];
				$perms		= $split[0];
			}
			
			if (substr($fileName,0,2) == "X-") continue;
			
			$fullFileName = "";//commonGetDomainName($domainRow) . "/loadedFiles/" . urlencode($split[8]);
			$fullFileName = commonGetDomainName($domainRow) . "/loadedFiles/$fileName";
			$copyName	  = commonValidXml("loadedFiles/$fileName");

			$showPic	  = "<span class='styleLink' onclick='$.fancybox (\"<img src=$fullFileName width=130 height=130 />\", {padding: 0, hideOnContentClick: true, overlayShow: false, width: 130, height: 130, margin: 0})'>$showPicText</span>";

			$showPic	  = commonValidXml($showPic);

			$fileName = commonValidXml($fileName);

			$fullFileName = commonValidXml(commonEncode($fullFileName));

	   		$xmlResponse .= "<item>"											.
								"<fileName>$fileName</fileName>"				.
								"<fullFileName>$fullFileName</fullFileName>"	.
								"<permissions>$perms</permissions>"				.
								"<fileSize>$fileSize</fileSize>"				.
								"<date>$date</date>"							.
								"<copyName>$copyName</copyName>"				.
								"<showPic>$showPic</showPic>"					.
							"</item>";
           $i++;
       }
   	}

	$xmlResponse .=	"</items>"												.
					"<totals>"												.
						"<totalText>$i</totalText>"							.
					"</totals>";
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteFile																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteFile ($xmlRequest)
{
	$fn = commonDecode(xmlParser_getValue ($xmlRequest, "fn"));

	if ($fn == "")
		trigger_error ("חסר שם הקובץ לביצוע הפעולה");

	$domainRow = commonGetDomainRow ();

	$connId    = commonFtpConnect	($domainRow);
	
	commonFtpDelete($connId, "loadedFiles/$fn");

   	ftp_close($connId);

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* isFileExists																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function isFileExists ($xmlRequest)
{
	$dirName = commonDecode(xmlParser_getValue ($xmlRequest, "dirName"));
	$fn 	 = commonDecode(xmlParser_getValue ($xmlRequest, "fn"));

	if ($fn == "")
		trigger_error ("יש לטעון קובץ");

	$fn = preg_replace( '/^.+[\\\\\\/]/', '', $fn ); 

	$domainRow = commonGetDomainRow ();

	$connId    = commonFtpConnect	($domainRow);
	
	if (ftp_size($connId, "$dirName/$fn") != -1)
		$exists = "1";
	else
		$exists = "0";

   	ftp_close($connId);

	return "<isExists>$exists</isExists>";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* uploadFile																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function uploadFile ($xmlRequest)
{
	global $userId;
	global $ibosHomeDir;

	$dirName = commonDecode(xmlParser_getValue ($xmlRequest, "dirName"));
	$fn 		= commonDecode(xmlParser_getValue ($xmlRequest, "fn"));
	$fixFn		= format_filename(str_replace(" ", "_", $fn));

	$domainRow 	= commonGetDomainRow ();
	$domainName = commonGetDomainName ($domainRow);

	$filePath = "$ibosHomeDir/html/SWFUpload/files/$userId/";

	$connId 	= commonFtpConnect($domainRow); 
	ftp_chdir ($connId, $dirName);

	$upload = ftp_put($connId, $fixFn, "$filePath/$fn", FTP_BINARY);

	return "";
}

function format_filename($filename)
{
	$bads = array(' ','ā','č','ē','ģ','ī','ķ','ļ','ņ','ŗ','š','ū','ž','Ā','Č','Ē','Ģ','Ī','Ķ','Ļ','Ņ','Ŗ','Š','Ū','Ž','$','&','А','Б','В','Г','Д','Е','Ё','Ж','З','И','Й','К','Л','М','Н','О','П','Р','С','Т','У','Ф','Х','Ц','Ч','Ш','Щ','Ъ','ЫЬ','Э','Ю','Я','а','б','в','г','д','е','ё','ж','з','и','й','к','л','м','н','о','п','р','с','т','у','ф','х','ц','ч','шщ','ъ','ы','ь','э','ю','я','א','ב','ג','ד','ה','ו','ז','ח','ט','י','כ','ך','ל','מ','ם','נ','ן','ס','ע','פ','ף','צ','ץ','ק','ר','ש','ת','!','\'');
	$good = array('-','a','c','e','g','i','k','l','n','r','s','u','z','A','C','E','G','I','K','L','N','R','S','U','Z','s','and','A','B','V','G','D','E','J','Z','Z','I','J','K','L','M','N','O','P','R','S','T','U','F','H','C','C','S','S','T','T','E','Ju','Ja','a','b','v','g','d','e','e','z','z','i','j','k','l','m','n','o','p','r','s','t','u','f','h','c','c','s','t','t','y','z','e','ju','a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z','T','1','w');
	if (strspn($filename, "אבגדהוזחטיכךלמםנןסעפףצץקרשת") > 0) // filename conains hebrew characters
		$filename = "__heb__$filename";
	$filename = str_replace($bads,$good,$filename);
	$filename = str_replace('&#039;', '_', $filename);
	$filename = str_replace('&#037;', '_', $filename);
	return $filename;
}

?>
