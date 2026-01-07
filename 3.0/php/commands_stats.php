<?php

/* ----------------------------------------------------------------------------------------------------	*/
/* getStatsDetails																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getStatsDetails ($xmlRequest)
{
	$domainRow = commonGetDomainRow ();

		$userpass = "";

//	$initial = "http://$userpass$domainRow[domainName]";
	$initial = commonGetDomainName ($domainRow);

	$urlPrefix = "$initial/frontend/monsoon/";
	$awsPrefix = "$initial/cgi-bin/awstats/awstats.pl?framename=mainright&config=".$domainRow['domainName'];
	$awsPrefix = "$initial:9000/CMD_AWSTATS/$domainRow[domainName]/awstats.pl";
	$anzPrefix = "http://".$userpass."www.interuse.biz:9000/CMD_WEBALIZER/$domainRow[domainName]/index.html";

	$urlPrefix = commonValidXml ($urlPrefix);
	$awsPrefix = commonValidXml ($awsPrefix);
	$anzPrefix = commonValidXml ($anzPrefix);

	$xmlResponse = "<urlPrefix>$urlPrefix</urlPrefix>";
	$xmlResponse .= "<awsPrefix>$awsPrefix</awsPrefix>";
	$xmlResponse .= "<anzPrefix>$anzPrefix</anzPrefix>";

	return ($xmlResponse);
}

?>
