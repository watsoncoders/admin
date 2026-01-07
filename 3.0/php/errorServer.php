<?

include "commonAdmin.php";

$action	= $_POST['action'];

call_user_func ($action);

exit;

function reportError ()
{
	$error			= $_POST['error'];
	$sessionCode	= $_POST['sessionCode'];

	$mysqlHandle 	= commonConnectToDB();
	$userRow 		= commonGetUserRow ($sessionCode);
	$domainId 		= $userRow['domainId'];

	$html	= "sessionCode = $sessionCode\n\nDomain Id = $domainId\n\nUser = $userRow[id] - $userRow[username]\n\nError = $error";

	mail ("liat@interuse.com", "I-BOS Report error", $html);

	return "";
}

?>
