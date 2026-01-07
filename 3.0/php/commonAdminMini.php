<?
$currDBConnection = null;
$saDBConnection = null;

$ibosHomeDir = "/home/israeli/public_html/admin/3.0";

/* --------------------------------------------------------------------------------------------	*/
/* commonConnectToDB																			*/
/* --------------------------------------------------------------------------------------------	*/
function commonConnectToDB ()
{
	global $currDBConnection, $saDBConnection;

	if ($saDBConnection == null)
	{
			$saDBConnection = mysql_pconnect("localhost", "israeli_dbuser", "mumch1M") or die(mysql_error()); 
			mysql_select_db("israeli_admin", $saDBConnection) or die(mysql_error()); 
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

	if ($domainRow['homeDir'] != "")
			ftp_chdir($connId, $domainRow['homeDir']);

	return ($connId);
}

/* --------------------------------------------------------------------------------------------	*/
/* commonFtpConnect																				*/
/* --------------------------------------------------------------------------------------------	*/
function commonFtpDisconnect ($connId)
{
	ftp_close($connId);
}


/* --------------------------------------------------------------------------------------------	*/
/* commonDoQuery																				*/
/* --------------------------------------------------------------------------------------------	*/
function commonDoQuery ($queryStr)
{
	global $currDBConnection;

	$result = commonDoQuery ($queryStr, $currDBConnection);
	
	if (!$result) trigger_error (mysql_error()." Query was: ".$queryStr);

	return $result;
}

?>
