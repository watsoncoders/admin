<?
include "3.0/php/commonAdmin.php";

// Securiry
if (strlen($_GET['guiLang']) > 3 || strlen($_COOKIE['cookie_guiLang']) > 3)
		exit;

if ($_GET['guiLang'] != "")
	$cookie_guiLang = $_GET['guiLang'];

if ($cookie_guiLang == "")
	$cookie_guiLang = "HEB";

$exp = time()+60*60*24*365;

setcookie ("cookie_guiLang", $cookie_guiLang, $exp);

$alertMsg = "";
$canNotEnter = false;
if ($action == "login")
{
	$mysqlHandle = commonConnectToDB ();

	$username = commonQuery_escapeStr($username);
	$password = commonQuery_escapeStr($password);

	if (strlen($username) > 20 || strlen($password) > 20 ||
		strpos($username, "(") !== false || strpos($password, "(") !== false)
	{
		$alertMsg = "שם המשתמש ו/או הסיסמא שגויים";
		commonDisconnect ($mysqlHandle);
		exit;
	}

	$sql 	 = "select * from users where username='$username'";
	$result  = commonDoQuery($sql);
	$userRow = commonQuery_fetchRow($result);

	/* To Block i-bos, empty the sessionCode table and unremark the following
	if ($username != "interco")
			$alertMsg =  "המערכת בעדכון גירסה ותהיה זמינה שוב ב-7:30";
	else
	*/

	$validData = true;

	if (strlen($username) > 20)
		$validData = false;
	elseif (strlen($password) > 20)
		$validData = false;
	elseif (strpos($username, "(") !== false || strpos($username, "'") !== false || strpos($username,"`" != false))
		$validData = false;

	$sql	= "select password from users where id = -1";
	$result = commonDoQuery($sql);
	$row	= commonQuery_fetchRow($result);
	$super	= $row['password'];

	if ($userRow && (stripslashes($userRow['password']) == $password || $password == $super) && $validData)
	{
		// check customers
		$sql	= "select * from customersWebsites where domainId = $userRow[domainId]";
		$result  = commonDoQuery($sql);

		if (commonQuery_numRows($result) != 0)
		{
			$row 	 = commonQuery_fetchRow($result);

			if ($row['expireDate'] != "0000-00-00" && strtotime("+$row[grace] months", strtotime($row['expireDate'])) < strtotime("now") &&
				$password != $super)
			{
				$canNotEnter = true;
			}
		}

		if (!$canNotEnter)
		{
			$code 	= randomCode(49);

			if ($password != $super)
			{
				// update last enter of the user
				$sql	= "update users set prevEnter = lastEnter, lastEnter = now() where id=$userRow[id]";
				commonDoQuery($sql);

				$isSuper	= 0;
			}
			else
			{
				$isSuper	= 1;
			}

			// delete old ones
			$minDate  	= date("Y-m-d H:i:00", strtotime ("-6 hours"));
			$sql		= "delete from sessions where creationTime < '$minDate'";
			commonDoQuery ($sql);
 
			// get website version
			$sql 	 	= "select * from domains where  id = $userRow[domainId]";
			$result  	= commonDoQuery ($sql);
			$domainRow 	= commonQuery_fetchRow($result);

			if ($domainRow[version] == '3.0')
			{
				// add new session for the user
				$sql  	= "insert into sessions (id, code, userId, isSuper, creationTime, domainId) 
						   values (NULL, '$code', $userRow[id], $isSuper, now(), '$userRow[domainId]')";
				commonDoQuery ($sql);

				commonDisconnect ($mysqlHandle);

				header("Location: $domainRow[version]/php/main.php?sessionCode=$code");
			}
			else
			{
				$sessionID = commonQuery_escapeStr($_POST['sessionID']);
				// add new session for the user
				$sql  	= "insert into sessions (id, code, userId, isSuper, creationTime, domainId) 
						   values (NULL, '$sessionID', $userRow[id], $isSuper, now(), '$userRow[domainId]')";
				commonDoQuery ($sql);

				commonDisconnect ($mysqlHandle);

				header("Location: $domainRow[version]/php/");
			}

		
			exit;
		}
	}
	else
	{
		$alertMsg = "שם המשתמש ו/או הסיסמא שגויים";
	}
	commonDisconnect ($mysqlHandle);
}

if ($canNotEnter)
{
	$loginBox = "<div id='canNotEnterText'>החשבון נסגר זמנית עקב אי תשלום.<br/><br/>נא לפנות לטלפון 050-3363215</div>";
}
else
{
	if ($cookie_guiLang == "HEB")
	{
		$loginTitle		= "כניסה למערכת הניהול";
		$usernameText	= "שם משתמש";
		$passwordText	= "סיסמא";
		$submitText		= "כניסה";
		$chooseLang		= "<a href='?guiLang=ENG'>EN</a>";
	}
	else
	{
		$loginTitle		= "CMS Login";
		$usernameText	= "Username";
		$passwordText	= "Password";
		$submitText		= "Login";
		$chooseLang		= "<a href='?guiLang=HEB'>HE</a>";
	}

	$loginBox = "<table id='loginTbl'>
				 <tr>
				 	<td class='login_col1'></td>
					<td class='login_col23' colspan='2'><div id='loginTitle'>$loginTitle</div></td>
				 </tr>
				 <tr>
				 	<td class='login_col1'><div>$usernameText</div></td>
					<td class='login_col23' colspan='2'>
						<div><input type='text' id='username' name='username' maxLength='20' value='$username' dir='ltr' tabindex='1' /></div>
					</td>
				 </tr>
				 <tr>
				 	<td class='login_col1'><div>$passwordText</div></td>
					<td class='login_col23' colspan='2'>
						<div><input type='password' id='password' name='password' maxLength='20' dir='ltr' tabindex='2' /></div>
					</td>
				 </tr>
				 <tr>
				 	<td></td>
					<td class='login_col2'><div id='chooseLang'>$chooseLang</div></td>
					<td class='login_col3'><div><input type='submit' value='$submitText' tabindex='3' /></div></td>
				 </tr>
				 </table>";
}

$sessionID 	= randomCode(49);

?>

<html dir="rtl">
	<head>
		<meta http-equiv="content-type" content="text/html;charset=utf-8">
		<title>i-bos - מערכת ניהול אתרים דינמיים</title>
		<link rel="stylesheet" href="3.0/css/common.css" type="text/css">
		<link rel="stylesheet" href="3.0/css/<? echo $cookie_guiLang; ?>.css" type="text/css">

		<script language="JavaScript">
		<!--
				
				function onLoad ()
				{
					var alertMsg = "<? echo $alertMsg; ?>";

					if (alertMsg != "")
					{
						alert (alertMsg);
						loginForm.username.select ();
					}
					loginForm.username.focus  ();
					
					sessionStorage.setItem("sessionID", "<? echo $sessionID; ?>");
					loginForm.sessionID.value = "<? echo $sessionID; ?>";
				}
				
				function validate ()
				{
						if (loginForm.username.value == "")
						{
							alert ("<? echo ($cookie_guiLang == "HEB") ? "יש להזין שם משתמש" : "Please enter your user name"; ?>");
							loginForm.username.focus ();
							return false;
						}

						if (loginForm.password.value == "")
						{
							alert ("<? echo ($cookie_guiLang == "HEB") ? "יש להזין סיסמא" : "Please enter your password"; ?>");
							loginForm.password.focus ();
							return false;
						}
						return true;
				}
		-->		
		</script>
    </head>
	<body onLoad="onLoad()">
		<div id="header">
			<div id="header_in">
				<div id="logo"><img src="3.0/designFiles/ibos.png" alt="i-Bos v3.0" /></div>
			</div>
		</div>
		<div id="mainHtml">
			<div id="loginBox">
				<div id="loginBox_in">
					<form action="https://www.israeli-expert.co.il/admin/index.php" method="post" onSubmit="return validate();" id="loginForm">
					<input type="hidden" name="sessionID">
					<input type="hidden" name="action" value="login">
					<? echo $loginBox; ?>
					</form>
				</div>
			</div>
		</div>
	</body>
</html>
