<?php
include "commonAdmin.php";
include "xmlParser.php";
include "format.php";

$xmlRequest = xmlParser_parse (commonValidXstandard($HTTP_RAW_POST_DATA));

$sessionCode = xmlParser_getValue ($xmlRequest, "sessionCode");
$realFilename = xmlParser_getValue ($xmlRequest, "filepath");

// get domainId
# --------------------------------------------------------------------------------------------
$domainRow = commonGetDomainRow ();

$connId    = commonFtpConnect    ($domainRow);

if (!$connId)
		mail("amir@interuse.co.il", "Failed to connect for $domainRow[ftpUsername] at ajaxfilemanager-server.php", "");

// upload the file
$upload = ftp_put($connId, "$realFilename", "../javascript2/tiny_mce/plugins/ajaxfilemanager/$realFilename", FTP_BINARY); 

unlink("../javascript2/tiny_mce/plugins/ajaxfilemanager/$realFilename");

?>
