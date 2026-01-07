<?php

include "commonAdmin.php";

while (list($key, $val) = each($_GET)) 
{
	eval("$$key = '$val';");
}
while (list($key, $val) = each($_POST)) 
{
	eval("$$key = '$val';");
}

commonValidateSession();

# load / reload file
# ------------------------------------------------------------------------------------------------------

// action (add | update)
// subject
// status
// title (by langs)
// text (by langs)
// source file

$loadFile = false;

if ($_FILES['picFile']['name'])
{
	$origName 	= $_FILES['picFile']['name'];
	$splitName 	= split("\.",$origName);
	$suffix 	= "";
	if (count($splitName) > 0)
		$suffix	= "." . $splitName[count($splitName)-1];

	$loadFile = true;

	$sourceFile	= addslashes($sourceFile);
}


if ($action == "add")
{
		# get new tip id
		# --------------------------------------------------------------------------------------------------
		$queryStr 	 = "select max(id) from z_tips";
		$result	     = commonDoQuery ($queryStr);
		$row	     = commonQuery_fetchRow($result);
		$id		  	 = $row[0] + 1;

		$tipFile	 = $id . $suffix;

		$queryStr = "insert into z_tips (id, subject, status, insertTime, tipFile, sourceFile) 
					 values ($id, '$subject', '$status', now(), '$tipFile', '$sourceFile')";

		commonDoQuery ($queryStr);
}
else
{
		$queryStr 	= "delete from z_tips_byLang where tipId='$id'";
		commonDoQuery ($queryStr);

		$queryStr = "update z_tips set subject 	= '$subject',
									   status	= '$status'";
		if ($loadFile)
		{
			$tipFile	= $id . $suffix;
			$queryStr	.= ",			   tipFile 		= '$tipFile',
										   sourceFile 	= '$sourceFile'";
		}
		$queryStr .= " where id=$id";
	
		commonDoQuery ($queryStr);
}

# add languages rows for this tip
# ------------------------------------------------------------------------------------------------------
$langsArray = split(",",$usedLangs);

for ($i=0; $i<count($langsArray); $i++)
{
		$language			= $langsArray[$i];

		eval ("\$title = \$title$language;");
		eval ("\$text  = \$text$language;");

		$title	= commonPrepareToDB($title);
		$text	= commonPrepareToDB($text);

		$queryStr		= "insert into z_tips_byLang (tipId, language, title, text)
						   values ('$id','$language','$title', '$text')";
	
		commonDoQuery ($queryStr);
}

# load / reload file
# ------------------------------------------------------------------------------------------------------

if ($loadFile)
{
	$domainRow = commonGetDomainRow ();

	$connId    = commonFtpConnect    ($domainRow);

	ftp_chdir($connId, "$domainRow[homeDir]/z-admin/tipPics/");

	$upload = ftp_put($connId, $tipFile, $_FILES['picFile']['tmp_name'], FTP_BINARY); 

	// check upload status
	if (!$upload) 
	{ 
	   echo "FTP upload has failed!";
	}
}

header ("Location: http://www.i-bos.net/admin/2.0/html/plugins/handleTips.html");
exit;

?>
