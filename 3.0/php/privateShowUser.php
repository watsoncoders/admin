<?
	function privateShowUser_getHtml ()
	{
		global $member;
		$sql ="select firstname,lastname,email,phone,phone2,cellphone,fax,city,address,zipcode,birthDate,efname,elname,seniority,workPlace,workDesc,workPart, picFile, siteUrl ";
		$sql .="from clubMembers,aepi_extraData where clubMembers.id=memberID and clubMembers.id=$member and memberID=$member";
		$result	= commonDoQuery($sql);
		$row	= commonQuery_fetchRow($result);
		$firstname = stripslashes($row['firstname']);	
		$firstname =str_replace("´", "&rsquo;",$firstname);
		$lastname = stripslashes($row['lastname']);	
		$lastname =str_replace("´", "&rsquo;",$lastname);
		$email = stripslashes($row['email']);	
		$siteUrl = (($row['siteUrl'] == "") ? "" : "<a href='$row[siteUrl]' target='_blank'>$row[siteUrl]</a>");
		$phone = stripslashes($row['phone']);	
		$cellphone = stripslashes($row['phone2']);	
		$fax = stripslashes($row['fax']);	
		$city = stripslashes($row['city']);	
		$city =str_replace("´", "&rsquo;",$city);
		$zipcode = stripslashes($row['zipcode']);	
		$efname = stripslashes($row['efname']);	
		$elname = stripslashes($row['elname']);	
		$birthDate = stripslashes($row['birthDate']);	
		$seniority = stripslashes($row['seniority']);	
		$address = stripslashes($row['address']);	
		$address =str_replace("´", "&rsquo;",$address);
		$efname = stripslashes($row['efname']);	
		$elname = stripslashes($row['elname']);	
		$workPlace = stripslashes($row['workPlace']);	
		$workPlace =str_replace("´", "&rsquo;",$workPlace);
		$workDesc = stripslashes($row['workDesc']);	
		$workDesc =str_replace("´", "&rsquo;",$workDesc);
		$workPart = stripslashes($row['workPart']);	
		$workPart =str_replace("´", "&rsquo;",$workPart);

		$memberPic = "";
		if ($row['picFile'] != "")
		{
			$memberPic = "<div class='memberPic'><img src='uploadedFiles/${member}_size0.jpg' title='$firstname $lastname' /></div>";
		}

		$sql2 ="select eName,subject,years,pos from aepi_education where memberID=$member order by pos";
		$result	= commonDoQuery($sql);
		$result2	= commonDoQuery($sql2);
		
		$sqlWork ="select place,part,years,pos from aepi_workPlaces where memberID=$member order by pos";
		$resultWork	= commonDoQuery($sqlWork);
		
		
		
		$html="				$memberPic
							<div class='memberSection1'>
							<table>
							<tr>
								<td colspan='2' class='memberSectionHeader'>פרטים אישיים</td>
							</tr>
							<tr>
								<td class='memberSection1TDheader'>	שם</td>
								<td class='memberSection1memName'>$firstname &nbsp; $lastname</td>
							</tr>
							<tr>
								<td class='memberSection1TDheader'></td>
								<td class='memberSection1engName'>$efname &nbsp; $elname</td>
							</tr>
							<tr>
								<td class='memberSection1TDheader'>שנת לידה</td>
								<td class='memberSection1Reg'>$birthDate</td>
							</tr>
							<tr>
								<td class='memberSection1TDheader'>וותק במקצוע התכנון משנה</td>
								<td class='memberSection1Reg'>$seniority</td>
							</tr>
							<tr>
								<td class='memberSectionHeader2' colspan='2'>כתובת התקשרות וקבלת דואר</td>
								<td class='memberSection1Reg'></td>
							</tr>
							<tr>
								<td class='memberSection1TDheader'>	רחוב ומס' </td>
								<td class='memberSection1Reg'>$address</td>
							</tr>
							<tr>
								<td class='memberSection1TDheader'>	יישוב </td>
								<td class='memberSection1Reg'>$city</td>
							</tr>
							<tr>
								<td class='memberSection1TDheader'>מיקוד</td>
								<td class='memberSection1Reg'>$zipcode</td>
							</tr>
							<tr>
								<td class='memberSection1TDheader'>	טלפון </td>
								<td class='memberSection1Reg'>$phone</td>
							</tr>
							<tr>
								<td class='memberSection1TDheader'>	טל' נייד </td>
								<td class='memberSection1Reg'>$cellphone</td>
							</tr>
							<tr>
								<td class='memberSection1TDheader'>פקס</td>
								<td class='memberSection1Reg'>$fax</td>
							</tr>
							<tr>
								<td class='memberSection1TDheader'>דואר אלקטרוני</td>
								<td class='memberMailLink'><a href='mailto:$email'>$email</a></td>
							</tr>
							<tr>
								<td class='memberSection1TDheader'>אתר אינטרנט</td>
								<td class='memberMailLink'>$siteUrl</td>
							</tr>
							<tr>
								<td class='memberSectionHeader2' colspan='2'>השכלה</td>
							</tr>";
							
							
		while ($row2= commonQuery_fetchRow($result2))
		{
			$eName = stripslashes($row2['eName']);	
			$eName =str_replace("´", "&rsquo;",$eName);
			$subject = stripslashes($row2['subject']);
			$subject =str_replace("´", "&rsquo;",$subject);			
			$years = stripslashes($row2['years']);
			$pos = stripslashes($row2['pos']);
			$deg="";
			if ($pos==1)
				$deg="תואר ראשון";
			if ($pos==2)
				$deg="תואר שני";
			if ($pos==3)
				$deg="תואר שלישי";
			$html.="<tr><td class='memberDegreeTD' collspan='2'>$deg</td></tr>";
			$html.="<tr><td class='memberSection1TDheader'>שם המוסד</td><td class='memberSection1Reg'>$eName</td></tr>";
			$html.="<tr><td class='memberSection1TDheader'>תחומי הלימודים</td><td class='memberSection1Reg'>$subject</td></tr>";
			$html.="<tr><td class='memberSection1TDheader'>שנת קבלת תואר</td><td class='memberSection1Reg'>$years</td></tr>";
		
		}
							
			$html .="</table></div>";

		$sql3 ="select skill,activity,secondary from aepi_extraData where memberID=$member";
		$result3	= commonDoQuery($sql3);
		$row3= commonQuery_fetchRow($result3);
		$memSkill = $row3['skill'];	
		$memActivity = $row3['activity'];	
		$memSecondary = $row3['secondary'];
		$arrSkills = split("[#,]",$memSkill);
		$arrActivity = split("[#,]",$memActivity);
		$arrSecondary = split("[#,]",$memSecondary);
		
		
		$html .="<div class='proffDiv'> 	
			<table>
				<tr>
					<td colspan='2' class='memberSectionHeader'>התמחות</td>
				</tr>
				<tr>
					<td class='memberSectionHeaderProf'>תחום התמחות</td>
					<td class='memberSectionHeaderProf'>אזור פעילות</td>
					<td class='memberSectionHeaderProf'>תחום משנה</td>
				</tr>";
		$first="<div class='firstSkill'>";
		$sec1="<div class='secAct'>";
		$third="<div class='thirdMishne'>";
		foreach ($arrSkills as $skill)
		{
		
			$s1 ="select text,id from enumsValues, enumsValues_byLang where id = valueId and language = 'HEB' and enumId = 1 and id=$skill";
			if ($skill=="")
				continue;
			$r1	= commonDoQuery($s1);
			$rr1= commonQuery_fetchRow($r1);
			$t1=$rr1['text'];
			$first.="<div class='oneItemMemProf'>$t1</div>";
		}

		foreach ($arrActivity as $act)
		{
			$s2 ="select text,id from enumsValues, enumsValues_byLang where id = valueId and language = 'HEB' and enumId = 2 and id=$act";
			if ($act=="")
				continue;
			$r2	= commonDoQuery($s2);
			$rr2= commonQuery_fetchRow($r2);
			$t2=$rr2['text'];
			$sec1.="<div class='oneItemMemProf'>$t2</div>";
		}
		foreach ($arrSecondary as $sec)
		{
			$s3 ="select text,id from enumsValues, enumsValues_byLang where id = valueId and language = 'HEB' and enumId = 3 and id=$sec";
			if ($sec=="")
			   continue;
			$rr3	= commonDoQuery($s3);
			$r3= commonQuery_fetchRow($rr3);
			$t3= $r3['text'];
			$third.="<div class='oneItemMemProf'>$t3</div>";
		}
		$first.="</div>";
		$sec1.="</div>";
		$third.="</div>";
		
		$html.="<tr><td class='itemListTD'>$first</td><td class='itemListTD'>$sec1</td><td class='itemListTD'>$third</td></tr></table>";
		$html .="<div class='proff'> 	
			<table>
				<tr>
					<td colspan='2' class='memberSectionHeader'>מקום העבודה:</td>
				</tr>
				<tr><td class='memberSection1TDheaderWork'>מקום העבודה</td><td class='memberSection1Work'>$workPlace</td></tr>
				<tr><td class='memberSection1TDheaderWork'>תפקיד</td><td class='memberSection1Work'>$workPart</td></tr>
				<tr><td class='memberSection1TDheaderWork'>תיאור המשרה</td><td class='memberSection1Work'>$workDesc</td></tr></div></table></div>";
		$html .="<div class='workAtPastDiv'><table><tr>
					<td colspan='2' class='memberSectionHeader'>תעסוקה - מקומות עבודה אחרונים </td>
					<tr><td class='workAtPastTableheader'>שם המשרד/מוסד</td><td class='workAtPastTableheader''>תפקיד</td><td class='workAtPastTableheader'>שנות פעילות</td></tr>
				</tr>";		
		while ($rowWork= commonQuery_fetchRow($resultWork))
		{
			$place = stripslashes($rowWork['place']);	
			$place =str_replace("´", "&rsquo;",$place);
			$part = stripslashes($rowWork['part']);	
			$part =str_replace("´", "&rsquo;",$part);
			$years = stripslashes($rowWork['years']);
			$pos = stripslashes($rowWork['pos']);
	
			$html.="<tr><td class='workAtPastTD'>$place<td class='workAtPastTD'>$part</td><td class='workAtPastTD'>$years</td></tr>";
		
		}		
		$html .="</table></div>";	
		
		$sqlProjects ="select name,part,client,jdate, id from aepi_projects where memberID=$member";
		$resultProjects	= commonDoQuery($sqlProjects);
		$html .="<div class='projectsDiv'> 	
			<table>
				<tr>
					<td colspan='3' class='projectsSectionHeader'>עבודות מקצועיות</td>
				</tr>";
		
		while ($rowPro= commonQuery_fetchRow($resultProjects))
		{
			$name = stripslashes($rowPro['name']);	
			$name =str_replace("´", "&rsquo;",$name);
			$part = stripslashes($rowPro['part']);
			$part =str_replace("´", "&rsquo;",$part);			
			$client = stripslashes($rowPro['client']);
			$client =str_replace("´", "&rsquo;",$client);
			$part = stripslashes($rowPro['part']);
			$part =str_replace("´", "&rsquo;",$part);
			$id = stripslashes($rowPro['id']);
			$jdate=stripslashes($rowPro['jdate']);
			
			$html.="<tr><td class='projectsTDnum' rowspan='4'>$id</td><td class='projectsTDheader'>שם הפרוייקט</td><td class='projectName'>$name</td></tr>";
			$html.="<tr><td class='projectsSection1'>מזמין העבודה</td><td class='projectsSection1Reg'>$client</td></tr>";
			$html.="<tr><td class='projectsSection1'>תפקיד בפרוייקט</td><td class='projectsSection1Reg'>$part</td></tr>";
			$html.="<tr><td class='projectsSection1'>תקופת הביצוע</td><td class='projectsSection1Reg'>$jdate</td></tr>";
			$html.="<tr><td class='projectsHr' colspan='3'><hr></hr></td></tr>";
		}
		$html.="</div></table>";


		
		
		$html.="</div>";
		
		return $html;
		}
?>
