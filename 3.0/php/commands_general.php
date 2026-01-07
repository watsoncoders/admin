<?php

/* ----------------------------------------------------------------------------------------------------	*/
/* saveChoice																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function saveChoice ($xmlRequest)
{	
	global $userId, $sessionCode;

	$domainRow = commonGetDomainRow();

	$sql 		= "select isSuper from sessions where code='$sessionCode'";
	$result 	= commonDoQuery($sql);

	if (commonQuery_numRows($result) != 0)
	{
		$row		= commonQuery_fetchRow($result);
		$isSuper	= $row['isSuper'];	

		// check if super user
		$featureId	= xmlParser_getValue($xmlRequest, "featureId");
		$queryStr	= "replace into usedFeatures (userId, isSuper, featureId, lastUsedAt) values ($userId, $isSuper, $featureId, now())";
		$result	    = commonDoQuery ($queryStr);
	}

	if ($domainRow != null)
		commonConnectToUserDB($domainRow);

	return "";
}
?>
