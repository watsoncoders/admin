<?php
include "commonAdmin.php";
include "xmlParser.php";
include "format.php";

$xmlRequest = xmlParser_parse (commonValidXstandard($HTTP_RAW_POST_DATA));

$sessionCode = xmlParser_getValue ($xmlRequest, "sessionCode");
$realFilename = xmlParser_getValue ($xmlRequest, "filepath");


	$mysqlHandle = commonConnectToDB();

	$userRow = commonGetUserRow ($sessionCode);

	$sql	= "select * from domains where id=$userRow[domainId]";
	$result 	= commonDoQuery($sql);
	$domainRow	= commonQuery_fetchRow($result);

	commonDisconnect ($mysqlHandle);

	//mail("amir@interuse.co.il", "imglibUploadServer", "Loading $realFilename to $domainRow[ftpUsername]");

	$connId    = commonFtpConnect    ($domainRow);
	if (!$connId)
		mail("amir@interuse.co.il", "Failed to connect for $domainRow[ftpUsername] at imglibUploadServer.php", "");

	$soleFileName = str_replace("http://www.i-bos.co.il/3.0/javascript2/imglib/upload/$_COOKIE[userId]/", "", $realFilename);

	// upload the file
	$upload = ftp_put($connId, "loadedFiles/$soleFileName", "../javascript2/imglib/upload/$_COOKIE[userId]/".$soleFileName, FTP_BINARY); 

	unlink("../javascript2/imglib/upload/$_COOKIE[userId]/".$soleFileName);
?>
