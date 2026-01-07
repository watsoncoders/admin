<?php

$tags		= array ("status", "memberLanguage", "extraData1", "extraData2", "extraData3", "extraData4", "extraData5",
       					 "extraData6", "extraData7", "extraData8", "extraData9", "extraData10", "verifyCode", "expireTime",
				  	 "username", "password", "firstname", "lastname", "nickname", "identityNumber", "email", "phone", "phone2", "cellphone", "fax", 
					 "messenger", "icq", "country", "city", "address", "zipcode", "occupation", "birthDate", "gender");

$web2Tags	= array ("memberType", "about", "extraData1", "extraData2", "extraData3", "extraData4", "extraData5", 
			 "extraData6", "extraData7", "extraData8", "extraData9", "extraData10", "albumId", "memberStatus", "bloggerId", "memberPageId");

/* ----------------------------------------------------------------------------------------------------	*/
/* getMembers																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function getMembers ($xmlRequest)
{	
	$domainRow   = commonGetDomainRow ();
	commonConnectToUserDB ($domainRow);

	$condition  = "";
	
	$memberType		= xmlParser_getValue($xmlRequest, "memberType");
	if ($memberType != "")
		$condition .= " and clubMembersWeb2.memberType = $memberType ";

	$anyText		= addslashes(xmlParser_getValue($xmlRequest, "anyText"));
	if ($anyText != "")
		$condition .= " and (username like '%$anyText%' or firstname like '%$anyText%' or lastname like '%$anyText%' or nickname like '%$anyText%' or
							 concat(clubMembers.firstname, concat(' ', clubMembers.lastname )) like '%$anyText%' or
							 concat(clubMembers.lastname,  concat(' ', clubMembers.firstname)) like '%$anyText%') ";

	$email		= addslashes(xmlParser_getValue($xmlRequest, "email"));
	if ($email != "")
		$condition .= " and email like '%$email%' ";

	// get total
	$queryStr     = "select count(*) from clubMembers, clubMembersWeb2, clubMembersWeb2Types 
					 where clubMembers.id = clubMembersWeb2.memberId
				     and   clubMembersWeb2.memberType = clubMembersWeb2Types.id  $condition";
	$result	     = commonDoQuery ($queryStr);
	$row	     = commonQuery_fetchRow($result);
	$total	     = $row[0];

	// get details
	$queryStr    = "select clubMembers.id, username, firstname, lastname, email, joinTime, status, clubMembersWeb2Types.name as memberTypeName, clubMembers.extraData8
				    from clubMembers, clubMembersWeb2, clubMembersWeb2Types
					where clubMembers.id = clubMembersWeb2.memberId
				    and   clubMembersWeb2.memberType = clubMembersWeb2Types.id $condition
					order by clubMembers.id desc " . commonGetLimit ($xmlRequest);
	$result	     = commonDoQuery ($queryStr);
	$numRows    = commonQuery_numRows($result);

	$xmlResponse = "<items>";

	for ($i = 0; $i < $numRows; $i++)
	{
		$row = commonQuery_fetchRow($result);
			
		$id   			= $row['id'];
		$username 		= commonValidXml ($row['username'],true);
		$firstname 		= commonValidXml ($row['firstname'],true);
		$lastname 		= commonValidXml ($row['lastname'],true);
		$email  		= commonValidXml ($row['email'],true);
		$memberType		= commonValidXml ($row['memberTypeName'],true);
		$joinTime		= formatApplDate($row['joinTime']);
		$status			= $row['status'];

		switch ($status)
		{
			case "new"		: $status = "חדש";		break;
			case "active"	: $status = "פעיל";		break;
			case "disabled" : $status = "חסום";		break;
		}
		$status = commonPhpEncode($status);

		if ($domainRow['id'] == 436 && $row['extraData8'] && $row['extraData8'] != '0000-00-00' && $row['extraData8'] < date("Y-m-d"))
		   $memberType .= commonPhpEncode(" - פג");
					
		$xmlResponse .=	"<item>
							<id>$id</id>
							<username>$username</username>
							<firstname>$firstname</firstname>
							<lastname>$lastname</lastname>
							<email>$email</email>
							<joinTime>$joinTime</joinTime>
							<status>$status</status>
							<memberType>$memberType</memberType>
						 </item>";
	}

	$xmlResponse .=	"</items>"								.
							commonGetTotalXml($xmlRequest,$numRows,$total);
	
	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* addMember																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function addMember ($xmlRequest)
{
	return (editMember ($xmlRequest, "add"));
}

/* ----------------------------------------------------------------------------------------------------	*/
/* updateMember																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function updateMember ($xmlRequest)
{
	editMember ($xmlRequest, "update");
}

/* ----------------------------------------------------------------------------------------------------	*/
/* editMember																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function editMember ($xmlRequest, $editType)
{
	global $tags, $web2Tags;

	$id		= xmlParser_getValue($xmlRequest, "id");

	if ($editType == "update")
	{
		if ($id == "")
			trigger_error ("חסר קוד חבר לביצוע הפעולה");
	}

	for ($i=0; $i < count($tags); $i++)
	{
		eval ("\$$tags[$i] = addslashes(commonDecode(xmlParser_getValue(\$xmlRequest,\"$tags[$i]\")));");	
	}

	$expireTime = formatApplToDB($expireTime);

	if ($verifyCode == "")
		$verifyCode = randomCode(25);

	$birthDate = xmlParser_getValue($xmlRequest,"birthDate");
	$birthDate .= " 00:00";
	$birthDate  = preg_replace("/^([0-9]{1,2})[\/\. -]+([0-9]{1,2})[\/\. -]+([0-9]{1,4})\s([0-9]{1,2}):([0-9]{2})/", "\\2/\\1/\\3 \\4:\\5", $birthDate); 
	$birthDate  = strtotime($birthDate);
	$birthDate  = date("Y-m-d", $birthDate);


	$vals = Array();
	for ($i=0; $i < count($tags); $i++)
	{
		eval ("array_push (\$vals,\$$tags[$i]);");
	}

	for ($i=0; $i < count($web2Tags); $i++)
	{
		eval ("\$$web2Tags[$i] = addslashes(commonDecode(xmlParser_getValue(\$xmlRequest,\"$web2Tags[$i]\")));");	
	}

	$domainRow   = commonGetDomainRow ();
	commonConnectToUserDB ($domainRow);

	$layoutId	= 0;
	if ($domainRow['id'] == 436)	// by the people
	{
		if ($memberType == 2)
			$layoutId = 0;
		else if ($memberType == 4)
			$layoutId = 13;
		else
			$layoutId = 12;
	}

	$extraData1	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "web2ExtraData1")));
	$extraData2	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "web2ExtraData2")));
	$extraData3	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "web2ExtraData3")));
	$extraData4	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "web2ExtraData4")));
	$extraData5	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "web2ExtraData5")));
	$extraData6	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "web2ExtraData6")));
	$extraData7	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "web2ExtraData7")));
	$extraData8	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "web2ExtraData8")));
	$extraData9	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "web2ExtraData9")));
	$extraData10	= addslashes(commonDecode(xmlParser_getValue($xmlRequest, "web2ExtraData10")));

	$web2Vals = Array();
	for ($i=0; $i < count($web2Tags); $i++)
	{
		eval ("array_push (\$web2Vals,\$$web2Tags[$i]);");
	}

	if ($editType == "add")
	{
		$queryStr   = "select max(id) from clubMembers";
		$result	    = commonDoQuery ($queryStr);
		$row	    = commonQuery_fetchRow($result);
		$id		    = $row[0]+1;

		$queryStr	= "insert into clubMembers (id, " . join(",",$tags) . ", joinTime) values ($id, '" . join("','",$vals) . ", now()')";
		commonDoQuery ($queryStr);

		$queryStr	= "insert into clubMembersWeb2 (memberId, " . join(",",$web2Tags) . ") values ($id, '" . join("','",$web2Vals) . "')";
		commonDoQuery ($queryStr);
	}
	else
	{
		// clubMembers
		$queryStr = "update clubMembers set ";
		for ($i=0; $i < count($tags); $i++)
		{
			$queryStr .= "$tags[$i] = '$vals[$i]',";
		}
		$queryStr = trim($queryStr, ",");
		$queryStr .= " where id = $id ";
		commonDoQuery ($queryStr);

		// clubMembersWeb2
		$queryStr = "update clubMembersWeb2 set ";
		for ($i=0; $i < count($web2Tags); $i++)
		{
			$queryStr .= "$web2Tags[$i] = '$web2Vals[$i]',";
		}
		$queryStr = trim($queryStr, ",");
		$queryStr .= " where memberId = $id ";
		commonDoQuery ($queryStr);
	}

	if ($layoutId != 0 && $memberPageId != "")
	{
		// update layout id
		$queryStr	= "update pages set layoutId = $layoutId where id = $memberPageId";
		commonDoQuery ($queryStr);
	}

	commonSaveItemFlags ($id, "member", $xmlRequest);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getExtraDataNames																					*/
/* ----------------------------------------------------------------------------------------------------	*/
function getExtraDataNames ($xmlRequest)
{
	$xmlResponse = commonGetExtraDataNames("clubMembersExtraData");

	$id = xmlParser_getValue($xmlRequest, "id");

	$queryStr	= "select memberType from clubMembersWeb2 where memberId = $id";
	$result		= commonDoQuery($queryStr);
	$row		= commonQuery_fetchRow($result);

	$queryStr	= "select * from clubMembersExtraDataByType where memberType = '$row[memberType]'";
	$result		= commonDoQuery($queryStr);

	if (commonQuery_numRows($result) != 0)
	{
		$row		= commonQuery_fetchRow($result);

		$xmlResponse .= "<web2ExtraData1>" . commonValidXml($row['extraData1']) . "</web2ExtraData1>
						 <web2ExtraData2>" . commonValidXml($row['extraData2']) . "</web2ExtraData2>
						 <web2ExtraData3>" . commonValidXml($row['extraData3']) . "</web2ExtraData3>
						 <web2ExtraData4>" . commonValidXml($row['extraData4']) . "</web2ExtraData4>
						 <web2ExtraData5>" . commonValidXml($row['extraData5']) . "</web2ExtraData5>
						 <web2ExtraData6>" . commonValidXml($row['extraData6']) . "</web2ExtraData6>
						 <web2ExtraData7>" . commonValidXml($row['extraData7']) . "</web2ExtraData7>
						 <web2ExtraData8>" . commonValidXml($row['extraData8']) . "</web2ExtraData8>
						 <web2ExtraData9>" . commonValidXml($row['extraData9']) . "</web2ExtraData9>
						 <web2ExtraData10>" . commonValidXml($row['extraData10']) . "</web2ExtraData10>";
	}
	else
	{
		$xmlResponse .= "<web2ExtraData1></web2ExtraData1>
						 <web2ExtraData2></web2ExtraData2>
						 <web2ExtraData3></web2ExtraData3>
						 <web2ExtraData4></web2ExtraData4>
						 <web2ExtraData5></web2ExtraData5>
						 <web2ExtraData6></web2ExtraData6>
						 <web2ExtraData7></web2ExtraData7>
						 <web2ExtraData8></web2ExtraData8>
						 <web2ExtraData9></web2ExtraData9>
						 <web2ExtraData10></web2ExtraData10>";
	}

	return $xmlResponse;
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getMemberDetails																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getMemberDetails ($xmlRequest)
{
	global $tags, $web2Tags;

	$id = xmlParser_getValue($xmlRequest, "id");
		
	if ($id == "")
		trigger_error ("חסר קוד חבר לביצוע הפעולה");

	$queryStr = "select * from clubMembers where id = '$id'";
	$result	  = commonDoQuery($queryStr);

	if (commonQuery_numRows($result) == 0)
		trigger_error ("חבר זה ($id) לא קיים במערכת. לא ניתן לבצע את העדכון");

	$xmlResponse = "";

	$row = commonQuery_fetchRow($result);

	// siteUrl
	$domainRow   = commonGetDomainRow ();
	$siteUrl     = commonGetDomainName($domainRow);

	commonConnectToUserDB ($domainRow);

	$row['birthDate'] 			= formatApplDate($row['birthDate'], true);
	$row['joinTime'] 			= formatApplDateTime($row['joinTime'], true);
	$row['expireTime'] 			= formatApplDate($row['expireTime'], true);
	$row['passwordExpireTime'] 	= formatApplDateTime($row['passwordExpireTime'], true);


	for ($i=0; $i < count($tags); $i++)
	{
		eval ("\$$tags[$i] = \$row['$tags[$i]'];");

		eval ("\$$tags[$i] = commonValidXml(\$$tags[$i]);");
		eval ("\$xmlResponse .= \"<$tags[$i]>\$$tags[$i]</$tags[$i]>\";");
	}

	$xmlResponse .= "<id>$id</id>
					 <joinTime>$row[joinTime]</joinTime>
					 <passwordExpireTime>$row[passwordExpireTime]</passwordExpireTime>";

	// clubMembersWeb2 table
	$queryStr = "select clubMembersWeb2.*, clubMembersWeb2Types.name as memberTypeName
		   		 from clubMembersWeb2, clubMembersWeb2Types 
			     where clubMembersWeb2.memberId = '$id'
			     and   clubMembersWeb2.memberType = clubMembersWeb2Types.id";
	$result	  = commonDoQuery($queryStr);
	$row = commonQuery_fetchRow($result);

	for ($i=0; $i < count($web2Tags); $i++)
	{
		eval ("\$$web2Tags[$i] = \$row['$web2Tags[$i]'];");

		eval ("\$$web2Tags[$i] = commonValidXml(\$$web2Tags[$i]);");

		if ($web2Tags[$i] == "extraData1" || $web2Tags[$i] == "extraData2" || $web2Tags[$i] == "extraData3" || $web2Tags[$i] == "extraData4" ||
			$web2Tags[$i] == "extraData5" || $web2Tags[$i] == "extraData6"  || $web2Tags[$i] == "extraData7"  || $web2Tags[$i] == "extraData8"  ||
		       	$web2Tags[$i] == "extraData9"  || $web2Tags[$i] == "extraData10" )
		{
			$tag = "web2" . ucfirst($web2Tags[$i]);
			eval ("\$xmlResponse .= \"<$tag>\$$web2Tags[$i]</$tag>\";");
		}
		else
			eval ("\$xmlResponse .= \"<$web2Tags[$i]>\$$web2Tags[$i]</$web2Tags[$i]>\";");

	}

	$xmlResponse .= "<memberTypeName>" . commonValidXml($row['memberTypeName']) ."</memberTypeName>";

	$flags = commonGetItemFlags ($id, "member");

	$xmlResponse .= commonGetItemFlagsXml ($flags, "member");

	return ($xmlResponse);
}

/* ----------------------------------------------------------------------------------------------------	*/
/* getMemberTypes																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getMemberTypes ($xmlRequest)
{
	$sql	= "select * from clubMembersWeb2Types order by id";
	$result	= commonDoQuery($sql);

	$xmlResponse = "<items>";

	while ($row = commonQuery_fetchRow($result))
	{
		$xmlResponse	.= "<item>
								<id>$row[id]</id>
								<name>" . commonValidXml($row['name']) . "</name>
						    </item>";
	}

	$xmlResponse .= "</items>";

	return $xmlResponse;
}

/* ----------------------------------------------------------------------------------------------------	*/
/* deleteMember																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function deleteMember ($xmlRequest)
{
	$id		= xmlParser_getValue($xmlRequest, "id");

	$queryStr = "delete from clubMembers where id = $id";
	commonDoQuery ($queryStr);

	$queryStr = "delete from clubMembersWeb2 where memberId = $id";
	commonDoQuery ($queryStr);

	$queryStr = "delete from clubMailingListsMembers where memberId = $id";
	commonDoQuery ($queryStr);

	return "";
}

/* ----------------------------------------------------------------------------------------------------	*/
/* excelReport																							*/
/* ----------------------------------------------------------------------------------------------------	*/
function excelReport ($xmlRequest)
{
	$domainRow = commonGetDomainRow();
	commonConnectToUserDB ($domainRow);

	$now		 = commonPrepareToFile(commonPhpEncode("תאריך הפקת הדוח:") . date("d/m/Y H:i"));

	$merge		 = 21;
	$excel		 = "";

	$queryStr	 = "select * from clubMembersExtraData";
	$result	     = commonDoQuery ($queryStr);

	$extraData1  = "";
	$extraData2  = "";
	$extraData3  = "";
	$extraData4  = "";
	$extraData5  = "";
	$extraData6  = "";
	$extraData7  = "";
	$extraData8  = "";
	$extraData9  = "";
	$extraData10 = "";
	if (commonQuery_numRows($result) != 0)
	{
		$row	     = commonQuery_fetchRow($result);
		$extraData1	 = commonPrepareToFile(stripslashes($row['extraData1']));
		$extraData2	 = commonPrepareToFile(stripslashes($row['extraData2']));
		$extraData3	 = commonPrepareToFile(stripslashes($row['extraData3']));
		$extraData4	 = commonPrepareToFile(stripslashes($row['extraData4']));
		$extraData5	 = commonPrepareToFile(stripslashes($row['extraData5']));
		$extraData6	 = commonPrepareToFile(stripslashes($row['extraData6']));
		$extraData7	 = commonPrepareToFile(stripslashes($row['extraData7']));
		$extraData8	 = commonPrepareToFile(stripslashes($row['extraData8']));
		$extraData9	 = commonPrepareToFile(stripslashes($row['extraData9']));
		$extraData10 = commonPrepareToFile(stripslashes($row['extraData10']));
						
		if ($extraData1  != "") 	{ $excel .= "<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">$extraData1</Data></Cell>";	$merge++; }
		if ($extraData2  != "") 	{ $excel .= "<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">$extraData2</Data></Cell>";	$merge++; }
		if ($extraData3  != "") 	{ $excel .= "<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">$extraData3</Data></Cell>";	$merge++; }
		if ($extraData4  != "") 	{ $excel .= "<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">$extraData4</Data></Cell>";	$merge++; }
		if ($extraData5  != "") 	{ $excel .= "<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">$extraData5</Data></Cell>";	$merge++; }
		if ($extraData6  != "") 	{ $excel .= "<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">$extraData6</Data></Cell>";	$merge++; }
		if ($extraData7  != "") 	{ $excel .= "<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">$extraData7</Data></Cell>";	$merge++; }
		if ($extraData8  != "") 	{ $excel .= "<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">$extraData8</Data></Cell>";	$merge++; }
		if ($extraData9  != "") 	{ $excel .= "<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">$extraData9</Data></Cell>";	$merge++; }
		if ($extraData10 != "") 	{ $excel .= "<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">$extraData10</Data></Cell>";	$merge++; }
	}

	$numFlags	= 0;

	$queryStr 	= "select * from flagsConfig where type = 'member' order by id";
	$result	    = commonDoQuery ($queryStr);

	$numFlags 	= commonQuery_numRows($result);
	$merge     += $numFlags;

	while ($row = commonQuery_fetchRow($result))
	{
		$flagName	 = commonPrepareToFile(stripslashes($row['name']));
		$excel 		.= "<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">$flagName</Data></Cell>";
	}

    $excel = "<?xml version='1.0' encoding='ISO-8859-8' ?>												
				<Workbook xmlns=\"urn:schemas-microsoft-com:office:spreadsheet\"
				 	      xmlns:o=\"urn:schemas-microsoft-com:office:office\"
						  xmlns:x=\"urn:schemas-microsoft-com:office:excel\"
						  xmlns:ss=\"urn:schemas-microsoft-com:office:spreadsheet\"
						  xmlns:html=\"http://www.w3.org/TR/REC-html40\">
					<OfficeDocumentSettings xmlns=\"urn:schemas-microsoft-com:office:office\">
						<Colors>
							<Color>
								<Index>39</Index>
   								<RGB>#E3E3E3</RGB>
							</Color>
						</Colors>
					</OfficeDocumentSettings>
					<ExcelWorkbook xmlns=\"urn:schemas-microsoft-com:office:excel\">
						<WindowHeight>7860</WindowHeight>
						<WindowWidth>14040</WindowWidth>
			  			<WindowTopX>0</WindowTopX>
			  			<WindowTopY>1905</WindowTopY>
			  			<ProtectStructure>False</ProtectStructure>
			  			<ProtectWindows>False</ProtectWindows>
			 		</ExcelWorkbook>
 					<Styles>
  						<Style ss:ID=\"sTitle\">
							<Alignment ss:Horizontal=\"Center\" ss:Vertical=\"Bottom\"/>
							<Font x:Family=\"Swiss\" ss:Color=\"#0E3966\" ss:Size=\"16\" ss:Bold=\"1\"/>
					  	</Style>
  						<Style ss:ID=\"sSubTitle\">
							<Alignment ss:Horizontal=\"Center\" ss:Vertical=\"Bottom\"/>
							<Font x:Family=\"Swiss\" ss:Color=\"#53B2E8\" ss:Size=\"14\" ss:Bold=\"1\"/>
					  	</Style>
  						<Style ss:ID=\"sInTitle\">
							<Alignment ss:Horizontal=\"Right\" ss:Vertical=\"Bottom\"/>
							<Font x:Family=\"Swiss\" ss:Color=\"#53B2E8\" ss:Size=\"14\" ss:Bold=\"1\"/>
			   				<Borders>
		    					<Border ss:Position=\"Bottom\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
			   				</Borders>
					  	</Style>
  						<Style ss:ID=\"sReportDate\">
							<Alignment ss:Horizontal=\"Center\" ss:Vertical=\"Bottom\"/>
							<Font x:Family=\"Swiss\" ss:Color=\"#ABABAB\" ss:Size=\"12\" ss:Bold=\"1\"/>
					  	</Style>
  						<Style ss:ID=\"sTotal\">
							<Alignment ss:Horizontal=\"Center\" ss:Vertical=\"Bottom\"/>
							<Font x:Family=\"Swiss\" ss:Color=\"#333300\" ss:Size=\"13\" ss:Bold=\"1\"/>
					  	</Style>
 			 			<Style ss:ID=\"sHeader\">
			   				<Alignment ss:Horizontal=\"Center\" ss:Vertical=\"Bottom\"/>
			   				<Borders>
			   					<Border ss:Position=\"Bottom\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Left\"   ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Right\"  ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
			   					<Border ss:Position=\"Top\"    ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		   					</Borders>
			   				<Font x:Family=\"Swiss\" ss:Color=\"#505050\" ss:Bold=\"1\"/>
		   					<Interior ss:Color=\"#EEEEEE\" ss:Pattern=\"Solid\"/>
			  			</Style>
 			 			<Style ss:ID=\"sFooter\">
			   				<Alignment ss:Horizontal=\"Right\" ss:Vertical=\"Bottom\"/>
			   				<Borders>
			   					<Border ss:Position=\"Bottom\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Left\"   ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Right\"  ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
			   					<Border ss:Position=\"Top\"    ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		   					</Borders>
			   				<Font x:Family=\"Swiss\" ss:Color=\"#505050\" ss:Bold=\"1\"/>
		   					<Interior ss:Color=\"#EEEEEE\" ss:Pattern=\"Solid\"/>
			  			</Style>
 			 			<Style ss:ID=\"sFooterLeft\">
			   				<Alignment ss:Horizontal=\"Left\" ss:Vertical=\"Bottom\"/>
			   				<Borders>
			   					<Border ss:Position=\"Bottom\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Left\"   ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Right\"  ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
			   					<Border ss:Position=\"Top\"    ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		   					</Borders>
			   				<Font x:Family=\"Swiss\" ss:Color=\"#505050\" ss:Bold=\"1\"/>
		   					<Interior ss:Color=\"#EEEEEE\" ss:Pattern=\"Solid\"/>
			  			</Style>
			  			<Style ss:ID=\"sCell\">
			   				<Alignment ss:Horizontal=\"Right\" ss:Vertical=\"Bottom\"/>
			   				<Borders>
		    					<Border ss:Position=\"Bottom\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Left\"   ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Right\"  ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
			   				</Borders>
			   				<Font x:Family=\"Swiss\" ss:Bold=\"0\"/>
			 			</Style>
			  			<Style ss:ID=\"sCellEng\">
			   				<Alignment ss:Horizontal=\"Left\" ss:Vertical=\"Bottom\"/>
			   				<Borders>
		    					<Border ss:Position=\"Bottom\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Left\"   ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Right\"  ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
			   				</Borders>
			   				<Font x:Family=\"Swiss\" ss:Bold=\"0\"/>
			 			</Style>
						<Style ss:ID=\"Default\" ss:Name=\"Normal\">
			   				<Alignment ss:Vertical=\"Bottom\"/>
   							<Borders/>
							<Font x:CharSet=\"177\"/>
   							<Interior/>
			   				<NumberFormat/>
			   				<Protection/>
			  			</Style>
						<Style ss:ID=\"s32\">
			   				<Alignment ss:Horizontal=\"Center\" ss:Vertical=\"Bottom\"/>
			  			</Style>
			  			<Style ss:ID=\"s74\">
			   				<Alignment ss:Horizontal=\"Center\" ss:Vertical=\"Bottom\"/>
			  				<Borders>
								<Border ss:Position=\"Bottom\" ss:LineStyle=\"Continuous\" ss:Weight=\"2\"/>
								<Border ss:Position=\"Left\"   ss:LineStyle=\"Continuous\" ss:Weight=\"2\"/>
								<Border ss:Position=\"Right\"  ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
								<Border ss:Position=\"Top\"    ss:LineStyle=\"Continuous\" ss:Weight=\"2\"/>
							</Borders>
							<Font x:CharSet=\"177\" x:Family=\"Swiss\" ss:Color=\"#FFFFFF\" ss:Bold=\"1\"/>
							<Interior ss:Color=\"#969696\" ss:Pattern=\"Solid\"/>
			  			</Style>
			  			<Style ss:ID=\"s75\">
							<Alignment ss:Horizontal=\"Center\" ss:Vertical=\"Bottom\"/>
			   				<Borders>
   								<Border ss:Position=\"Bottom\" ss:LineStyle=\"Continuous\" ss:Weight=\"2\"/>
		    					<Border ss:Position=\"Left\"   ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Right\"  ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Top\"    ss:LineStyle=\"Continuous\" ss:Weight=\"2\"/>
				   			</Borders>
			   				<Font x:CharSet=\"177\" x:Family=\"Swiss\" ss:Color=\"#FFFFFF\" ss:Bold=\"1\"/>
							<Interior ss:Color=\"#969696\" ss:Pattern=\"Solid\"/>
			  			</Style>
			  			<Style ss:ID=\"s76\">
			   				<Alignment ss:Horizontal=\"Center\" ss:Vertical=\"Bottom\"/>
			   				<Borders>
		    					<Border ss:Position=\"Bottom\" ss:LineStyle=\"Continuous\" ss:Weight=\"2\"/>
		    					<Border ss:Position=\"Left\"   ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Right\"  ss:LineStyle=\"Continuous\" ss:Weight=\"2\"/>
		    					<Border ss:Position=\"Top\"    ss:LineStyle=\"Continuous\" ss:Weight=\"2\"/>
			  				</Borders>
			   				<Font x:CharSet=\"177\" x:Family=\"Swiss\" ss:Color=\"#FFFFFF\" ss:Bold=\"1\"/>
			   				<Interior ss:Color=\"#969696\" ss:Pattern=\"Solid\"/>
			  			</Style>
			  			<Style ss:ID=\"s77\">
			  				<Alignment ss:Horizontal=\"Left\" ss:Vertical=\"Bottom\"/>
			   				<Borders>
		    					<Border ss:Position=\"Left\"  ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Right\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
			   				</Borders>
			   				<Font x:Family=\"Swiss\" ss:Color=\"#0000FF\" ss:Bold=\"1\"/>
			  			</Style>
			  			<Style ss:ID=\"s88\">
   							<Font x:Family=\"Swiss\" ss:Color=\"#333300\" ss:Bold=\"1\"/>
			  			</Style>
			  			<Style ss:ID=\"s95\">
			   				<Alignment ss:Horizontal=\"Center\" ss:Vertical=\"Bottom\"/>
			   				<Borders>
		    					<Border ss:Position=\"Bottom\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Left\"   ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Right\"  ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Top\"    ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
			   				</Borders>
			   				<Interior ss:Color=\"#E3E3E3\" ss:Pattern=\"Solid\"/>
			  			</Style>
			  			<Style ss:ID=\"s96\">
			   				<Alignment ss:Horizontal=\"Center\" ss:Vertical=\"Bottom\"/>
			   				<Borders>
		    					<Border ss:Position=\"Bottom\" ss:LineStyle=\"Continuous\" ss:Weight=\"2\"/>
		    					<Border ss:Position=\"Left\"   ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Right\"  ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Top\"    ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
   							</Borders>
			   				<Interior ss:Color=\"#E3E3E3\" ss:Pattern=\"Solid\"/>
			  			</Style>
			  			<Style ss:ID=\"s98\">
			   				<Alignment ss:Horizontal=\"Left\" ss:Vertical=\"Bottom\"/>
			   				<Font x:Family=\"Swiss\" ss:Color=\"#0000FF\" ss:Italic=\"1\"/>
			 			</Style>
			  			<Style ss:ID=\"s99\">
			   				<Alignment ss:Horizontal=\"Left\" ss:Vertical=\"Bottom\"/>
			   				<Font x:Family=\"Swiss\" ss:Color=\"#0000FF\" ss:Italic=\"1\"/>
			   				<NumberFormat ss:Format=\"Short Date\"/>
			  			</Style>
			  			<Style ss:ID=\"s105\">
			   				<Borders>
		    					<Border ss:Position=\"Left\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
			   				</Borders>
			  			</Style>
			  			<Style ss:ID=\"s106\">
			   				<Alignment ss:Horizontal=\"Center\" ss:Vertical=\"Bottom\"/>
			   				<Borders>
		    					<Border ss:Position=\"Bottom\" ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Left\"   ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
		    					<Border ss:Position=\"Right\"  ss:LineStyle=\"Continuous\" ss:Weight=\"1\"/>
			   				</Borders>
			   				<Interior ss:Color=\"#E3E3E3\" ss:Pattern=\"Solid\"/>
			  			</Style>
 					</Styles>
					<Worksheet ss:Name=\"דוח חברי הלשכה\" ss:RightToLeft=\"1\">
					<Table x:FullColumns=\"1\" x:FullRows=\"1\">
   	        		<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"60\"/>
   	        		<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"100\"/>
   	        		<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"100\"/>
   	        		<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"100\"/>
   	        		<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"100\"/>
   	        		<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"100\"/>
   	        		<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"100\"/>
   	        		<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"100\"/>
   	        		<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"100\"/>
   	        		<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"100\"/>
   	        		<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"100\"/>
   	        		<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"100\"/>
   	        		<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"100\"/>
   	        		<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"100\"/>
   	        		<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"100\"/>
   	        		<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"100\"/>
   	        		<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"100\"/>
   	        		<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"100\"/>
   	        		<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"100\"/>
   	        		<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"100\"/>
   	        		<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"100\"/>
   	        		<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"100\"/>
   	        		<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"100\"/>
   	        		<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"100\"/>
   	        		<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"100\"/>
   	        		<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"100\"/>
   	        		<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"100\"/>
   	        		<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"100\"/>
   	        		<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"100\"/>
   	        		<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"100\"/>
   	        		<Column ss:StyleID=\"s32\" ss:AutoFitWidth=\"0\" ss:Width=\"100\"/>
					<Row>
						<Cell ss:MergeAcross=\"$merge\" ss:StyleID=\"sTitle\"><Data ss:Type=\"String\">דוח חברים</Data></Cell>
					</Row>
					<Row>
						<Cell ss:MergeAcross=\"$merge\" ss:StyleID=\"sReportDate\"><Data ss:Type=\"String\">$now</Data></Cell>
					</Row>
					<Row>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">קוד</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">שם משתמש</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">סיסמא</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">שם פרטי</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">שם משפחה</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">כינוי</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">סוג חבר</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">מין</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">אימייל</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">טלפון</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">טלפון נוסף</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">סלולרי</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">פקס</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">עיר</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">כתובת</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">מיקוד</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">תחום העיסוק</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">האתר שלי</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">תאריך לידה</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">תאריך הצטרפות</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">תאריך תוקף</Data></Cell>
						<Cell ss:StyleID=\"sHeader\"><Data ss:Type=\"String\">סטטוס</Data></Cell>
						$excel
					</Row>";

	$condition  = "";
	
	$memberType		= xmlParser_getValue($xmlRequest, "memberType");
	if ($memberType != "")
		$condition .= " and clubMembersWeb2.memberType = $memberType ";

	$anyText		= commonDecode(xmlParser_getValue($xmlRequest, "anyText"));
	if ($anyText != "")
		$condition .= " and (username like '%$anyText%' or firstname like '%$anyText%' or lastname like '%$anyText%' or nickname like '%$anyText%' or
							 concat(clubMembers.firstname, concat(' ', clubMembers.lastname )) like '%$anyText%' or
							 concat(clubMembers.lastname,  concat(' ', clubMembers.firstname)) like '%$anyText%') ";

	$email		= commonDecode(xmlParser_getValue($xmlRequest, "email"));
	if ($email != "")
		$condition .= " and email like '%$email%' ";

	$queryStr    = "select clubMembers.*, clubMembersWeb2Types.name as memberTypeName
					from clubMembers, clubMembersWeb2, clubMembersWeb2Types 
					where clubMembers.id = clubMembersWeb2.memberId
				    and   clubMembersWeb2.memberType = clubMembersWeb2Types.id $condition order by clubMembers.id desc";

	$result	     = commonDoQuery ($queryStr);

	while ($row = commonQuery_fetchRow($result))
	{
		$id				= $row['id'];
		$username 		= commonPrepareToFile(stripslashes($row['username']));
		$password 		= commonPrepareToFile(stripslashes($row['password']));
		$firstname 		= commonPrepareToFile(stripslashes($row['firstname']));
		$lastname 		= commonPrepareToFile(stripslashes($row['lastname']));
		$nickname 		= commonPrepareToFile(stripslashes($row['nickname']));
		$email  		= commonPrepareToFile(stripslashes($row['email']));
		$phone			= commonPrepareToFile(stripslashes($row['phone']));
		$phone2			= commonPrepareToFile(stripslashes($row['phone2']));
		$cellphone		= commonPrepareToFile(stripslashes($row['cellphone']));
		$fax			= commonPrepareToFile(stripslashes($row['fax']));
		$city			= commonPrepareToFile(stripslashes($row['city']));
		$address		= commonPrepareToFile(stripslashes($row['address']));
		$zipcode		= commonPrepareToFile(stripslashes($row['zipcode']));
		$occupation		= commonPrepareToFile(stripslashes($row['occupation']));
		$mySite			= commonPrepareToFile(stripslashes($row['mySite']));
		$birthDate		= commonPrepareToFile(stripslashes($row['birthDate']));
		$expireTime		= commonPrepareToFile(stripslashes($row['expireTime']));
		$referer 		= commonPrepareToFile(stripslashes($row['referer']));
		$joinTime		= commonPrepareToFile(stripslashes(substr($row['joinTime'],0,10)));
		$status			= $row['status'];
		$memberType		= commonPrepareToFile(stripslashes($row['memberTypeName']));

		switch ($status)
		{
			case "new"		: $status = "חדש";		break;
			case "active"	: $status = "פעיל";		break;
			case "disabled" : $status = "חסום";		break;
		}

		if ($domainRow['id'] == 436 && $row['extraData8'] && $row['extraData8'] != '0000-00-00' && $row['extraData8'] < date("Y-m-d"))
		   $memberType .= " - פג";
					
		$gender			= $row['gender'];

		switch ($gender)
		{
			case "f"	:	$gender	= "נקבה";	break;
			case "m"	:	$gender	= "זכר";	break;
		}

		$excel .= "<Row ss:Height=\"13.5\">
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$id</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$username</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$password</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$firstname</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$lastname</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$nickname</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$memberType</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$gender</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$email</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$phone</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$phone2</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$cellphone</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$fax</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$city</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$address</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$zipcode</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$occupation</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$mySite</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$birthDate</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$joinTime</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$expireTime</Data></Cell>
						<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$status</Data></Cell>";
		
		switch ($domainRow['id'])
		{
			default :
				$row['extraData1']  = commonPrepareToFile(stripslashes($row['extraData1']));
				$row['extraData2']  = commonPrepareToFile(stripslashes($row['extraData2']));
				$row['extraData3']  = commonPrepareToFile(stripslashes($row['extraData3']));
				$row['extraData4']  = commonPrepareToFile(stripslashes($row['extraData4']));
				$row['extraData5']  = commonPrepareToFile(stripslashes($row['extraData5']));
				$row['extraData6']  = commonPrepareToFile(stripslashes($row['extraData6']));
				$row['extraData7']  = commonPrepareToFile(stripslashes($row['extraData7']));
				$row['extraData8']  = commonPrepareToFile(stripslashes($row['extraData8']));
				$row['extraData9']  = commonPrepareToFile(stripslashes($row['extraData9']));
				$row['extraData10'] = commonPrepareToFile(stripslashes($row['extraData10']));
		}

		if ($extraData1  != "") $excel .= "<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$row[extraData1]</Data></Cell>";
		if ($extraData2  != "") $excel .= "<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$row[extraData2]</Data></Cell>";
		if ($extraData3  != "") $excel .= "<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$row[extraData3]</Data></Cell>";
		if ($extraData4  != "") $excel .= "<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$row[extraData4]</Data></Cell>";
		if ($extraData5  != "") $excel .= "<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$row[extraData5]</Data></Cell>";
		if ($extraData6  != "") $excel .= "<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$row[extraData6]</Data></Cell>";
		if ($extraData7  != "") $excel .= "<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$row[extraData7]</Data></Cell>";
		if ($extraData8  != "") $excel .= "<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$row[extraData8]</Data></Cell>";
		if ($extraData9  != "") $excel .= "<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$row[extraData9]</Data></Cell>";
		if ($extraData10 != "") $excel .= "<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$row[extraData10]</Data></Cell>";

		if ($numFlags != 0)
		{
			$sql		= "select * from flags where itemId = $id and itemType = 'member'";
			$inResult 	= commonDoQuery($sql);

			if (commonQuery_numRows($inResult) == 0)
			{
				for ($i = 0; $i < $numFlags; $i++)
					$excel .= "<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">לא</Data></Cell>";
			}

			while ($inRow = commonQuery_fetchRow($inResult))
			{
				$value 	= $inRow['value'] == 0 ? "לא" : "כן";
				$excel .= "<Cell ss:StyleID=\"sCell\"><Data ss:Type=\"String\">$value</Data></Cell>";
			}
		}

		$excel .= "</Row>";
	}

	$excel .= 	 "</Table>
				</Worksheet>
			</Workbook>";

	return (commonDoExcel($excel));
}
?>
