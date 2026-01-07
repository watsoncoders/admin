<?

/* ----------------------------------------------------------------------------------------------------	*/
/* relogin																								*/
/* ----------------------------------------------------------------------------------------------------	*/
function relogin ($xmlRequest)
{
	global $sessionCode, $userId;

	$mysqlHandle = commonConnectToDB();

	$sql		= "select password from users where id = -1";
	$result 	= commonDoQuery($sql) or die(mysql_error());
	$row		= commonQuery_fetchRow($result);
	$super		= $row['password'];

	$sql		= "select * from users where id= $userId";
	$result 	= commonDoQuery($sql);
	$userRow	= commonQuery_fetchRow($result);

	if (!$userRow || $userRow['id'] == 0 || $userRow['id'] != $userId)
	{
		trigger_error ("'לא ניתן לבצע התחברות מחדש. יש ללחוץ על כפתור 'התנתק");
	}

	$password = xmlParser_getValue ($xmlRequest, "password");
	if ($userRow['password'] != $password && $password != $super)
	{
		trigger_error ("סיסמא שגויה");
	}
	
	// update session creationTime
	$sql	= "select * from sessions where code = '$sessionCode' and userId = '$userId'";
	$result	= commonDoQuery($sql);

	if (commonQuery_numRows($result) != 0)
	{
		$sql 	= "update sessions set creationTime = now() where code='$sessionCode' and userId = '$userId'";
	}
	else
	{
		$sql	= "insert into sessions (code, userId, creationTime) values ('$sessionCode', '$userId', now())";
	}
	
	commonDoQuery ($sql);

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getUserInfo																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function getUserInfo ($xmlRequest)
{
	global $sessionCode;

	$mysqlHandle = commonConnectToDB();

	$userRow 		= commonGetUserRow ($sessionCode);
	$userId	 		= $userRow['id'];
	$isSuperUser	= $userRow['isSuperUser'];

	$sql	= "select * from domains where id=$userRow[domainId]";
	$result 	= commonDoQuery($sql);
	$domainRow	= commonQuery_fetchRow($result);

	$domainName = commonGetDomainName ($domainRow);

	$userName 	= commonEncode 	  ($userRow['myName']);
	$loginName 	= commonValidXml 	  ($userRow['username']);

	$lastEnter = "";
	if (isset($_COOKIE['cookie_lastEnter']))
		$lastEnter	= date("H:i d.m.Y", strtotime($_COOKIE['cookie_lastEnter'])); //formatApplDateTime ($row[0]);

	commonDisconnect ($mysqlHandle);

	$xmlResponse = "<userId>$userId</userId>"				.
				   "<siteUrl>$domainName</siteUrl>"			.
				   "<userName>$userName</userName>"			.
				   "<loginName>$loginName</loginName>"		.
				   "<lastEnter>$lastEnter</lastEnter>"		.
				   "<isSuperUser>$isSuperUser</isSuperUser>".
				   "<oldPassword></oldPassword>"			.
				   "<newPassword></newPassword>";


	return ($xmlResponse);
}
	
/* ----------------------------------------------------------------------------------------------------	*/
/* getUserMsgs																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function getUserMsgs ($xmlRequest)
{
	$userId = xmlParser_getValue($xmlRequest, "userId");

	if ($userId == "" || $userId == 0)
	{
		trigger_error ("חסר מזהה משתמש");
	}
	
	$mysqlHandle = commonConnectToDB();

	// get encoding
	/*
	$queryStr	= "select isUTF8 from domains where id = $userId";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$isUTF8	     = $row['isUTF8'];
	 */

	// get total
	$queryStr	= "select count(*) from userMsgs where userId=$userId";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$total	     = $row[0];

	// get details
	$queryStr	 = "select * from userMsgs where userId=$userId order by insertTime desc  " . 
				   commonGetLimit ($xmlRequest);
	$result	     = commonDoQuery ($queryStr);

	$numRows     = commonQuery_numRows($result);

	$countNews	 = 0;
	$xmlResponse = "<items>";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		$id   	 	= $row['id'];
		$subject 	= commonValidXml ($row['subject']);
		$insertTime = commonValidXml (formatApplDateTime($row['insertTime']));
		$status  	= $row['status'];

		if ($status == "new")
		{
			$statusText = "חדשה !";
			$countNews++;
		}
		else
		{
			$statusText = "נקראה";
		}

		$statusText = commonValidXml ($statusText);

		$xmlResponse .=	"<item>"											.
							"<id>$id</id>"	 								. 
							"<subject>$subject</subject>"					. 
							"<insertTime>$insertTime</insertTime>"			. 
							"<status>$status</status>"				 		. 
							"<statusText>$statusText</statusText>"	 		. 
						"</item>";
	}

	$xmlResponse .=	"</items>"												.
					"<newMsgs>$countNews</newMsgs>"							.
					commonGetTotalXml($xmlRequest,$numRows,$total);
	
	commonDisconnect ($mysqlHandle);

	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteMsg																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteMsg ($xmlRequest)
{
	$id = xmlParser_getValue ($xmlRequest, "id");

	if ($id == "")
		trigger_error ("חסר מזהה הודעה לביצוע הפעולה");

	$mysqlHandle = commonConnectToDB();

	$queryStr = "delete from userMsgs where id = $id";
	commonDoQuery ($queryStr);

	commonDisconnect ($mysqlHandle);

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getMsgDetails																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getMsgDetails ($xmlRequest)
{
	$id		= xmlParser_getValue($xmlRequest, "id");

	if ($id == "")
		trigger_error ("חסר מזהה הודעה לביצוע הפעולה");

	$mysqlHandle = commonConnectToDB();

	$queryStr	= "select * from userMsgs
				   where id=$id";
	$result		= commonDoQuery ($queryStr);

	if (commonQuery_numRows($result) == 0)
		trigger_error ("הודעה ($id) לא קיימת");

	
	$row = commonQuery_fetchRow($result);

	$id   	   	 = $row['id'];
	$subject 	 = commonValidXml ($row['subject']);
	$text 		 = commonValidXml ($row['text']);
	$insertTime  = commonValidXml (formatApplDateTime($row['insertTime']));
	$status   	 = $row['status'];

	$xmlResponse =	"<id>$id</id>"	 								. 
					"<status>$status</status>"						.
					"<subject>$subject</subject>"					. 
					"<insertTime>$insertTime</insertTime>"			. 
					"<text>$text</text>";

	$queryStr	 = "update userMsgs set status='old' where id=$id";
	commonDoQuery ($queryStr);

	commonDisconnect ($mysqlHandle);

	return $xmlResponse;
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getHomepageNews																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getHomepageNews ($xmlRequest)
{
	global $isUTF8;

	// connect to interuse.co.il to draw latest Interuse news
	$mysqlHandle = commonConnectToDB();

	$sql		= "select * from domains where id = 444";
	$result 	= commonDoQuery($sql);
	$domainRow	= commonQuery_fetchRow($result);

	$isUTF8 = true;
	commonConnectToUserDB($domainRow);

	$queryStr	 = "select * from news, news_byLang where id = newsId and status = 'active' and language = 'HEB' order by id desc limit 3";
	$res = commonDoQuery ($queryStr);
	$interuseNews = '<div>
							<div class="newsTitle" class="boxTitle"><div>'.commonEncode("חדשות מאינטריוז").'</div></div>
							<div class="news_in">	
								<table><tbody>';
																
	while ($row = commonQuery_fetchRow($res))
	{
			$interuseNews .= '	<tr>
									<td class="newsPic"><div><img src="http://www.interuse.co.il/newsFiles/'.$row['picFile'].'"></div></td>
									<td class="newsDetails">
										<div class="news_date">'.date("d/m/Y",strtotime($row['date'])).'</div>
										<div class="news_title"><a href="'.$row['urlOrPageId'].'" target="_blank">'.$row['title'].'</a></div>
										<div class="news_text">'.$row['txt'].'</div>
									</td>
								</tr>';
	}

	$interuseNews .= '			</tbody></table>
								<div id="moreNews" class="more"><a href="http://www.interuse.co.il/news" target="_new">&#187; '.commonEncode("לכל החדשות").'</a></div>
							</div>
						</div>			';

	$saDBConnection = mysql_pconnect("localhost", "interuse_admin", "mimamu") or die(mysql_error()); 
	commonDoQuery ("set names 'utf8'");
	mysql_select_db("interuse_admin", $saDBConnection) or die(mysql_error()); 
	$queryStr	 = "select * from ibosMsgs order by insertTime desc limit 12";
	$res = commonDoQuery($queryStr);
	$ibosNews = '<div>
							<div class="newsTitle" class="boxTitle"><div>'.commonEncode("שדרוגי תשתיות").'</div></div>
							<div class="news_in">	
								<table><tbody>';
																
	while ($row = commonQuery_fetchRow($res))
	{
			$ibosNews .= '	<tr>
									<td class="newsDate">
										<div class="news_date">'.date("d/m/Y",strtotime($row['insertTime'])).'</div>
									</td>
									<td class="newsMsg">
										<div class="news_text">'.$row['msg'].'</div>
									</td>
								</tr>';
	}

	$ibosNews .= '			</tbody></table>
							</div>
						</div>			';

	$xmlResponse =	"<homepageInteruseNews>".commonValidXml($interuseNews)."</homepageInteruseNews>". 
					"<homepageIbosNews>".commonValidXml($ibosNews)."</homepageIbosNews>";

	return $xmlResponse;
}
?>
