<?php

/* ----------------------------------------------------------------------------------------------------	*/
/* getFiles																								*/
/* ----------------------------------------------------------------------------------------------------	*/
function getFiles ($xmlRequest)
{
	$domainRow = commonGetDomainRow ();

	$connId    = commonFtpConnect	($domainRow);
	
	$files = ftp_rawlist($connId, "-a designFiles");

   	ftp_close($connId);

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
			
			$fullFileName = "";//commonGetDomainName($domainRow) . "/designFiles/" . urlencode($split[8]);
			$fullFileName = commonGetDomainName($domainRow) . "/designFiles/$fileName";

			$fileName = commonValidXml(commonEncode($fileName));

			$fullFileName = commonValidXml(commonEncode($fullFileName));

	   		$xmlResponse .= "<item>"											.
								"<fileName>$fileName</fileName>"				.
								"<fullFileName>$fullFileName</fullFileName>"	.
								"<permissions>$perms</permissions>"			.
								"<fileSize>$fileSize</fileSize>"				.
								"<date>$date</date>"							.
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
/* deleteFile																								*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteFile ($xmlRequest)
{
	$fn = commonDecode(xmlParser_getValue ($xmlRequest, "fn"));

	if ($fn == "")
		trigger_error ("חסר שם הקובץ לביצוע הפעולה");

	$domainRow = commonGetDomainRow ();

	$connId    = commonFtpConnect	($domainRow);
	
	commonFtpDelete($connId, "designFiles/$fn");

   	ftp_close($connId);

	return "";
}

?>
