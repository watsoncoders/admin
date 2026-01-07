<?

include "commonAdmin.php";

if (!commonValidateSession())
{
	echo "<b>System Error</b> - please, call support<br/>
		  <br/><br/><font color='blue'><u>cookies</u>:</font><br/>";
	
	print_r ($_COOKIE);

	echo "<br/><br/><font color='blue'><u>get</u>:</font><br/>";
	print_r ($_GET);

	echo "<br/><br/><font color='blue'><u>post</u>:</font><br/>";
	print_r ($_POST);

	exit;
}


$origName 	= $_FILES["excelFile"]["name"];
$suffix 	= commonFileSuffix ($origName);

if ($suffix != ".csv")
{
	echo "Wrong file format - should be csv!";
	exit;
}

// copy file to tmp
move_uploaded_file($_FILES['excelFile']['tmp_name'], "/tmp/$origName");

$f = fopen("/tmp/$origName", "r");
$line = fgets($f);	// read headers row

while ($line = fgets($f)) 
{ 
	$splitLine = explode(",", $line);

	$username		= commonPrepareToDB(trim($splitLine[0]));
	$password		= commonPrepareToDB(trim($splitLine[1]));
	$firstname		= commonPrepareToDB(trim($splitLine[2]));
	$lastname		= commonPrepareToDB(trim($splitLine[3]));
	$email			= commonPrepareToDB(trim($splitLine[4]));
	$joinTime		= commonPrepareToDB(trim($splitLine[5]));
	$mailingList	= commonPrepareToDB(trim($splitLine[6]));
	$phone			= commonPrepareToDB(trim($splitLine[7]));
	$country		= commonPrepareToDB(trim($splitLine[8]));
	$occupation		= commonPrepareToDB(trim($splitLine[9]));

	if ($joinTime)
	{
		$joinTime 		= preg_replace("/^([0-9]{1,2})[\/\. -]+([0-9]{1,2})[\/\. -]+([0-9]{1,4})/", "\\2/\\1/\\3", $joinTime);
		$joinTime 		= date("Y-m-d H:i:00", strtotime($joinTime));
	}

//	if ($username == "" || $password == "") continue;

	// check if member already exist
	if ($username != "")
	{
		$sql		= "select id from clubMembers where username = '$username'";
	}
	else
	{
		$sql		= "select id from clubMembers where email = '$email'";
	}

	$result			= commonDoQuery($sql);

	$newMember = false;
	if (commonQuery_numRows($result) == 0)
	{
		$newMember = true;

		// new member
		$sql		= "select max(id) from clubMembers";
		$result		= commonDoQuery($sql);
		$row		= commonQuery_fetchRow($result);
		$memberId	= $row[0]+1;
		$code 		= randomCode(25);

		$sql		= "insert into clubMembers (id, memberType, status, username, password, firstname, lastname, email, joinTime, verifyCode, gender,
												phone, country, occupation)
				values ($memberId, 'member', 'active', '".addslashes($username)."', '$password', '".addslashes($firstname)."', '".
				addslashes($lastname)."', '$email', '$joinTime', '$code', '', '$phone', '$country', '$occupation')";
		commonDoQuery ($sql);
	}
	else
	{
		$row		= commonQuery_fetchRow($result);
		$memberId	= $row[0];
	}


	if ($mailingList != "")
	{
		$addToList = true;

		if (!$newMember)
		{
			// check if already exist
			$sql		= "select id from clubMailingListsMembers where memberId = $memberId and mailingListId = $mailingList";
			$result		= commonDoQuery($sql);

			$addToList  = (commonQuery_numRows($result) == 0);
		}
	
		if ($addToList)
		{
			$sql		= "insert into clubMailingListsMembers (memberId, mailingListId, joinTime)
						   values ($memberId, $mailingList, '$joinTime')";
			commonDoQuery ($sql);
		}
	}
} 

fclose ($f);

unlink ("/tmp/$origName");

header ("Location: ../html/crm/handleClubMembers.html");
exit;

?>
