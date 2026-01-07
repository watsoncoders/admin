<?php
include('config/config.php');

if ($userRow)
{
        $sql    	= "select * from domains where id=$userRow[domainId]";
        $result     = commonDoQuery($sql);
        $domainRow  = commonQuery_fetchRow($result);

		if ($connId = commonFtpConnect($domainRow))
				ftp_put($connId, "loadedFiles".substr($_GET['targetFile'],strrpos($_GET['targetFile'], "/")),
					   	"../".substr($_GET['targetFile'], strpos($_GET['targetFile'], "3.0")+4), FTP_BINARY);

		unlink("../".substr($_GET['targetFile'], strpos($_GET['targetFile'], "3.0")+4));
}
?>      
