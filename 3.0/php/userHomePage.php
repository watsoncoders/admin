<?

	include "commonAdmin.php";

/*
	include "../common.php";
*/
	$mysqlHandle = commonConnectToDB ();

	$userRow	 = commonGetUserRow ($sessionCode);

	// set global parms
	$domainRow = commonGetDomainRow ();
	$url = commonGetDomainName($domainRow);
?>

<html dir="rtl">

<head>
	
<meta http-equiv = "content-type" content="text/html;charset=windows-1255">
<link rel="stylesheet" href="../css/common.css" type="text/css"/>

<script language="JavaScript">

	top.globalFrame.siteUrl = "<? echo "$url"; ?>"; //$domainRow[domainName]$hdir"; ?>";

</script>
</head>
<body>
<?
	echo "<span class='styleTextTitle'>שלום <font color='blue'>$userRow[myName]</font>";

	$commonText = "<br><br></span><span class='styleBigText'>";
	
	if ($cookie_lastEnter == "")
	{
		echo " וברוכים הבאים למערכת הניהול";
		echo $commonText . "<table>
								<tr class='styleBigText'>
									<td>על מנת להתחיל, יש לבחור את אחד התפריטים המופיעים מימין.<br><br></<td>
						   			<td></td>
								</tr>
								<tr class='styleBigText'>
									<td></td>
									<td><i><b>בהצלחה!</b></i></td>
								</tr>
							</table>";
	}
	else
	{
		echo ",";
		echo $commonText . "כניסתך האחרונה למערכת בוצעה ב- " . commonApplDateTime($cookie_lastEnter); 
	}

	
	echo "</span>";

	echo "<BR><BR>";

	// establish ftp connection to the server
	$connId = ftp_connect($domainRow[domainName]); 

	// login with username and password
	$login_result = ftp_login($connId, $domainRow[ftpUsername], $domainRow[ftpPassword]); 

	// check connection
	if ((!$connId) || (!$login_result)) { 
       echo "FTP connection has failed!";
       exit; 
	}

	// close the FTP stream 
	ftp_close($connId); 

?>
</body>
</html>
