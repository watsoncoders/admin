<?

// ##########################
// #    IMPORTANT NOTICE!   #
// # chmod 770 /public_html #
// ##########################

include "commonAdmin.php";

$mysqlHandle = commonConnectToDB();

$userRow = commonGetUserRow ($sessionCode);

$sql	= "select domainName, homeDir, ftpUsername, ftpPassword, dbUsername, dbPassword, dbName ".
		  "from domains where id=$userRow[domainId]";
$result 	= commonDoQuery($sql) or die(mysql_error());
$domainRow	= commonQuery_fetchRow($result);

commonDisConnect ($mysqlHandle);

if (substr($domainRow[homeDir],0,11) == "public_html")
	$hdir = substr($domainRow[homeDir],11);
else
	$hdir = $domainRow[homeDir];

// establish ftp connection to the server
$conn_id = ftp_connect($domainRow[domainName]); 

// login with username and password
$login_result = ftp_login($conn_id, $domainRow[ftpUsername], $domainRow[ftpPassword]); 

// check connection
if ((!$conn_id) || (!$login_result)) { 
      echo "FTP connection has failed!";
      exit; 
}

$isStaticMode = in_array("index.html", ftp_nlist($conn_id, "/".$domainRow[homeDir]."/"));

if ($action == "makeStatic_submit")
{
	ftp_delete($conn_id, "/".$domainRow[homeDir]."/index.php");

	// connect to user DB
	$mysqlHandle = mysql_connect($domainRow['dbHostname'], $domainRow['dbUsername'], $domainRow['dbPassword']) or 
				   die(mysql_error()); 
	mysql_select_db($domainRow['dbName'], $mysqlHandle) or die(mysql_error()); 

	$result = commonDoQuery("select * from pages where staticname<>''") or die(mysql_error());
	while ($rowArray = commonQuery_fetchRow ($result)) {
		echo stripslashes($rowArray[staticname]).".html ....";
		// Read page as html from the web
		$f = fopen("http://$domainRow[domainName]$hdir/index1.php?static=1&articleId=".$rowArray[id],"r");
		if (!$f) continue;
		$text = "";
		while (!feof($f)) {
		  $text .= fgets($f, 1024);
		}
		fclose($f);
		// Write html code to a temporary file
		$g = fopen("/tmp/$domainRow[domainName].html","w");
		fwrite($g, $text);
		fclose($g);
		// Ftp the temporary file to be a static page on the website
		$upload = ftp_put($conn_id, "/".$domainRow[homeDir]."/".stripslashes($rowArray[staticname]).".html",
					  "/tmp/$domainRow[domainName].html", FTP_BINARY); 

		// check upload status
		if (!$upload) { 
	       echo "FTP upload has failed!";
		}

		echo "created<BR>";
	}

	// close the FTP stream 
	ftp_close($conn_id); 

	commonDisconnect();

	echo "Done";
	exit;
}

if ($action == "undoStatic_submit")
{
	// Write html code to a temporary file
	$g = fopen("/tmp/$domainRow[domainName].stam","w");
	fwrite($g, "<? include \"index1.php\"; ?>");
	fclose($g);
	// Ftp the temporary file to be a static page on the website
	$upload = ftp_put($conn_id, "/".$domainRow[homeDir]."/index.php",
				  "/tmp/$domainRow[domainName].stam", FTP_BINARY); 
	// check upload status
	if (!$upload) { 
	      echo "FTP upload has failed!";
	}
	echo "index.php created<BR>";

	ftp_delete($conn_id, "/".$domainRow[homeDir]."/index.html");
	echo "index.html deleted<BR>";

	// connect to user DB
	$mysqlHandle = mysql_connect($domainRow['dbHostname'], $domainRow['dbUsername'], $domainRow['dbPassword']) or 
				   die(mysql_error()); 
	mysql_select_db($domainRow['dbName'], $mysqlHandle) or die(mysql_error()); 

	$result = commonDoQuery("select * from pages where staticname<>'index' and staticname<>''") or die(mysql_error());
	while ($rowArray = commonQuery_fetchRow ($result)) {
		echo stripslashes($rowArray[staticname]).".html ....";
		$isPageStatic = in_array($rowArray[staticname].".html", ftp_nlist($conn_id, "/".$domainRow[homeDir]."/"));
		if ($isPageStatic) {
			// Write html code to a temporary file
			$g = fopen("/tmp/$domainRow[domainName].stam","w");
			fwrite($g, "<HTML><HEAD><meta http-equiv=\"Refresh\" content=\"0; URL=");
			fwrite($g, "http://".$domainRow[domainName].$hdir."/index1.php".
					"?articleId=".$rowArray['id']."\">\n");
			fwrite($g, "</HEAD><BODY><BR><BR><CENTER>Page moved!</CENTER></BODY></HTML>");
			fclose($g);
			// Ftp the temporary file to be a static page on the website
			$upload = ftp_put($conn_id, "/".$domainRow[homeDir]."/".stripslashes($rowArray[staticname]).".html",
						  "/tmp/$domainRow[domainName].stam", FTP_BINARY); 
			// check upload status
			if (!$upload) { 
	    	   echo "FTP upload has failed!";
			}

			echo "replaced by stub<BR>";
		} else
			echo "new, doing nothing<BR>";
	}

	// close the FTP stream 
	ftp_close($conn_id); 

	commonDisconnect();

	echo "Done";
	exit;
}

// close the FTP stream 
ftp_close($conn_id); 

?>

<html dir="rtl">
	<head>
			<meta http-equiv = "content-type" content="text/html;charset=windows-1255">
			<link rel="stylesheet" href="../css/common.css" type="text/css"/>
	</head>
	<body topmargin="0" leftmargin="0">
	<br>
	<br>

	<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td width="23"></td>
		<td class="styleTitle">
		    <img style="position: relative; top: 1px" src="images/star.gif" border="0">
		    יצירת דפים סטטיים
		 </td>
	</tr>
	</table>

	<table width="100%" border="0" cellspacing="0" cellpadding="0">
	<tr>
	    <td height=7></td>
	</tr>
	<tr>
	    <td class='styleBigText'>
<? if (!$isStaticMode) { ?>
		המנגנון של דפים סטטיים מאפשר להציג את האתר כמורכב מדפים קבועים ומוכנים מראש,<BR>
		למרות היותו אתר דינאמי שניתן לערוך את דפיו במערכת זו כמובן.<BR>
		היתרונות בהצגת האתר כסטטי הם:<BR>
		1) לצורך דירוג גבוה יותר במנועי החיפוש, שמעדיפים דפים סטטיים וגם מתייחסים לשם הסטטי של הדף.<BR>
		2) לצורך טעינה מהירה יותר של עמודי האתר, כיוון שהם הוכנו מראש.<BR>
		במצב עבודה עם דפים סטטיים, שינויים שתעשה באתר דרך מערכת הניהול ייראו לגולשים באתר<BR>
		רק אחרי לחיצה על כפתור 'עדכן דפים סטטיים' שיופיע כאן בניהול דפים סטטיים.
<? } else { ?>
		<span class='styleTextTitle'>האתר נמצא במצב דפים סטטיים.</span><BR>
		שינויים שתעשה באתר דרך מערכת הניהול ייראו לגולשים באתר<BR>
		רק אחרי לחיצה על כפתור 'עדכן דפים סטטיים' שלהלן.
<? } ?>
		</td>
	</tr>
	<tr>
	    <td height=7></td>
	</tr>
	</table>

<? if (!$isStaticMode) { ?>
	<form action='<? echo $PHP_SELF; ?>' method=post>
		<input type=hidden name=action value=makeStatic_submit>
		<input class=styleWideButton type=submit value='עבור למצב דפים סטטיים'>
	</form>
<? } else { ?>
	<form action='<? echo $PHP_SELF; ?>' method=post>
		<input type=hidden name=action value=makeStatic_submit>
		<input class=styleWideButton type=submit value='עדכן דפים סטטיים'>
	</form>
	<form action='<? echo $PHP_SELF; ?>' method=post>
		<input type=hidden name=action value=undoStatic_submit>
		<input class=styleWideButton type=submit value='בטל מצב דפים סטטיים'>
	</form>
<? } ?>
	</body>
</html>
