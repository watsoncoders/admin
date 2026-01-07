<?php

include "commonAdmin.php";

$domainRow = commonGetDomainRow ();

$connId    = commonFtpConnect    ($domainRow);

// upload the file
if ($_FILES['uploadFile']['name'])
{
	$upload = ftp_put($connId, "loadedFiles/".$_FILES['uploadFile']['name'],
					  $_FILES['uploadFile']['tmp_name'], FTP_BINARY); 

	// check upload status
	if (!$upload) { 
	       echo "FTP upload has failed!";
   }

}

header ("Location: ../html/content/handleFiles.html");
exit;

?>
