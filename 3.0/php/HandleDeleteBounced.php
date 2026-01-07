<?php

include "commonAdmin.php";

$mysqlHandle = commonConnectToDB();

$sql		= "select * from domains where id = 413"; // smart-fertilizer
$result 	= commonDoQuery($sql);
$domainRow = commonQuery_fetchRow($result);

commonDisconnect ($mysqlHandle);

$isUTF8 = $domainRow['isUTF8'];
commonConnectToUserDB ($domainRow);
		
$res = commonDoQuery("select id from clubMembers where failedEmailsCounter >= 4");
while ($row = commonQuery_fetchRow($res))
		commonDoQuery("delete from clubMailingListsMembers where memberId = $row[id]");

echo "Done";
?>
