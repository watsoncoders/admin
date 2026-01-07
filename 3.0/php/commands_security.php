<?

/* ----------------------------------------------------------------------------------------------------	*/
/* getSecurityDetails																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function getSecurityDetails ($xmlRequest)
{
	global $sessionCode;

	$mysqlHandle = commonConnectToDB();

	$userRow = commonGetUserRow ($sessionCode);

	$userId	 		= $userRow['id'];
	$login_username = commonValidXml 	  ($userRow['username']);

	$sql	= "select * from domains where id=$userRow[domainId]";
	$result 	= commonDoQuery($sql);
	$domainRow	= commonQuery_fetchRow($result);

	$domainId		= $domainRow['id'];
	$db_username 	= commonValidXml 	  ($domainRow['dbUsername']);
	$ftp_username 	= commonValidXml 	  ($domainRow['ftpUsername']);
	$cp_username 	= commonValidXml 	  ($domainRow['cpUsername']);

	commonDisconnect ($mysqlHandle);

	$xmlResponse = "<domainId>$domainId</domainId>
					<userId>$userId</userId>
				    <login_username>$login_username</login_username>
				    <login_oldPassword></login_oldPassword>
					<login_newPassword></login_newPassword>
				    <db_username>$db_username</db_username>
				    <db_oldPassword></db_oldPassword>
					<db_newPassword></db_newPassword>
				    <ftp_username>$ftp_username</ftp_username>
				    <ftp_oldPassword></ftp_oldPassword>
					<ftp_newPassword></ftp_newPassword>
				    <cp_username>$cp_username</cp_username>
				    <cp_oldPassword></cp_oldPassword>
					<cp_newPassword></cp_newPassword>";


	return ($xmlResponse);
}
	
/* ----------------------------------------------------------------------------------------------------	*/
/* changePasswords																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function changePasswords ($xmlRequest)
{
	$mysqlHandle = commonConnectToDB();

	$domainId		 = xmlParser_getValue($xmlRequest, "domainId");

	if ($domainId == "" || $domainId == 0)
	{
		trigger_error ("חסר מזהה דומיין");
	}
	
	$userId		 = xmlParser_getValue($xmlRequest, "userId");

	if ($userId == "" || $userId == 0)
	{
		trigger_error ("חסר מזהה משתמש");
	}

	$domainRow = "";
	$updatePrivateFile = false;

	// login password
	// ------------------------------------------------------------------------------------------------	
	$oldPassword 	= xmlParser_getValue($xmlRequest, "login_oldPassword");

	if ($oldPassword != "")
	{
		// get login password from DB
		$queryStr	 = "select password from users where id=$userId";
		$result	     = commonDoQuery ($queryStr);
		$row	     = commonQuery_fetchRow($result);
		$dbPassword  = $row[0];

		if ($dbPassword != $oldPassword)
		{
			trigger_error ("הסיסמת הקודמת למערכת הניהול אינה נכונה");
		}
	
		$newPassword		= xmlParser_getValue($xmlRequest, "login_newPassword");

		if ($oldPassword == $newPassword)
		{
			trigger_error ("יש לבחור למערכת הניהול סיסמא חדשה שונה מהסיסמא הקודמת");
		}

		$queryStr	 = "update users set password='$newPassword' where id=$userId";
		commonDoQuery ($queryStr);
	}

	// get domain row from DB
	// ------------------------------------------------------------------------------------------------	
	$queryStr 	 = "select * from domains where id = $domainId";
	$result	     = commonDoQuery ($queryStr);
	$domainRow   = commonQuery_fetchRow($result);

	// db password
	// ------------------------------------------------------------------------------------------------	
	$oldPassword 	= xmlParser_getValue($xmlRequest, "db_oldPassword");

	if ($oldPassword != "")
	{
		// sub-domains out
		if (strchr($domainRow['homeDir'],'/'))
		{
			trigger_error ("לא ניתן לשנות את סיסמת בסיס הנתונים מתת-אתר");
		}

		$dbPassword	 = $domainRow['dbPassword'];

		if ($dbPassword != $oldPassword)
		{
			trigger_error ("הסיסמת הקודמת לבסיס הנתונים אינה נכונה");
		}
	
		$newPassword		= xmlParser_getValue($xmlRequest, "db_newPassword");

		if ($oldPassword == $newPassword)
		{
			trigger_error ("יש לבחור לבסיס הנתונים סיסמא חדשה שונה מהסיסמא הקודמת");
		}

		$queryStr = "update domains set dbPassword = '$newPassword' where id = $domainId";
		commonDoQuery ($queryStr);

		if ($domainRow['outsideDomain'] == "0")
		{
			// update database password
				$data = "action=modifyuser&domain=".$domainRow['domainName']."&name=".$domainRow['dbName'].
						"&user=".$domainRow['dbUsername']."passwd=$newPassword&passwd2=$newPassword";

			yourWishIsMyCommand ($domainRow['cpUsername'], $domainRow['cpPassword'], "CMD_DB", $data);
		}

		$domainRow['dbPassword'] = $newPassword;

		$updatePrivateFile = true;
	}

	// ftp password
	// ------------------------------------------------------------------------------------------------	
	$oldPassword 	= xmlParser_getValue($xmlRequest, "ftp_oldPassword");

	if ($oldPassword != "")
	{
		// sub-domains out
		if (strchr($domainRow['homeDir'],'/'))
		{
			trigger_error ("לא ניתן לשנות את סיסמת ה-FTP מתת-אתר");
		}

		$dbPassword	 = $domainRow['ftpPassword'];

		if ($dbPassword != $oldPassword)
		{
			trigger_error ("הסיסמת הקודמת להעברת קבצים אינה נכונה");
		}
	
		$newPassword		= xmlParser_getValue($xmlRequest, "ftp_newPassword");

		if ($oldPassword == $newPassword)
		{
			trigger_error ("יש לבחור להעברת קבצים סיסמא חדשה שונה מהסיסמא הקודמת");
		}

		$queryStr = "update domains set ftpPassword = '$newPassword' where id = $domainId";
		commonDoQuery ($queryStr);

		if ($domainRow['outsideDomain'] == "0")
		{
			// update ftp password
			$data = "oldpass=$oldPassword&passwd=$newPassword&passwd2=$newPassword" .
					"&options=yes&ftp=yes";

			yourWishIsMyCommand ($domainRow['cpUsername'], $domainRow['cpPassword'], "CMD_PASSWD", $data);
		}

		$domainRow['ftpPassword'] = $newPassword;
	}

	// cp password
	// ------------------------------------------------------------------------------------------------	
	$oldPassword 	= xmlParser_getValue($xmlRequest, "cp_oldPassword");

	if ($oldPassword != "")
	{
		// sub-domains out
		if (strchr($domainRow['homeDir'],'/'))
		{
			trigger_error ("לא ניתן לשנות את סיסמת לוח הבקרה מתת-אתר");
		}

		$dbPassword	 = $domainRow['cpPassword'];

		if ($dbPassword != $oldPassword)
		{
			trigger_error ("הסיסמת הקודמת למערכת ניהול שרת אינה נכונה");
		}
	
		$newPassword		= xmlParser_getValue($xmlRequest, "cp_newPassword");

		if ($oldPassword == $newPassword)
		{
			trigger_error ("יש לבחור למערכת ניהול שרת סיסמא חדשה שונה מהסיסמא הקודמת");
		}

		$queryStr = "update domains set cpPassword = '$newPassword' where id = $domainId";
		commonDoQuery ($queryStr);

		if ($domainRow['outsideDomain'] == "0")
		{
			// update ftp password
			$data = "oldpass=$oldPassword&passwd=$newPassword&passwd2=$newPassword" .
					"&options=yes&system=yes";

			yourWishIsMyCommand ($domainRow['cpUsername'], $domainRow['cpPassword'], "CMD_PASSWD", $data);
		}

		$domainRow['cpPassword'] = $newPassword;
	}

	// at least - update private.php file if needed
	// ------------------------------------------------------------------------------------------------	
	if ($updatePrivateFile)
	{
		$f = fopen("/tmp/private.php", "w");
		fwrite($f,"<?\n
					\$privateDbUser = \"$domainRow[dbUsername]\";\n
					\$privateDbPass = \"$domainRow[dbPassword]\";\n
					\$privateDbName = \"$domainRow[dbName]\";\n
					\$privateDbHostname = \"localhost\";\n
				   ?>");
		fclose($f);

		$connId    = commonFtpConnect	($domainRow);
		ftp_put($connId, "/$domainRow[homeDir]/private.php", "/tmp/private.php",  FTP_BINARY);
		ftp_close($connId); 
	}

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* yourWishIsMyCommand 																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function yourWishIsMyCommand($u, $p, $cmd, $data)
{
 	$eol = "\r\n";
 	$errno = 0;
 	$errstr = '';
 	$fid = fsockopen('www.interuse.biz', 2222, &$errno, &$errstr, 60);

 	if ($fid) 
	{
		fputs ($fid, "POST /$cmd HTTP/1.1$eol");
		fputs ($fid, "HOST: localhost$eol");
  		fputs ($fid, "Connection: close$eol");
  		fputs ($fid, "Content-Type: application/x-www-form-urlencoded$eol");
  		fputs ($fid, "Authorization: Basic ".base64_encode($u.":".$p).$eol);
  		fputs ($fid, 'Content-Length: ' . strlen($data) . $eol);
  		fputs ($fid, $eol);
  		fputs ($fid, $data);
  		fputs ($fid, $eol);
  		fpassthru($fid);
  		fclose($fid);
 	} 
	else 
		trigger_error ("Error $errno: $errstr");
}

?>
