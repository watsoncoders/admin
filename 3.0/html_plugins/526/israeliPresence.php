<!DOCTYPE html>
<html dir="rtl" lang="he">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<title>המכון הישראלי לחוות דעת מומחים ובוררים - מערכת ניהול</title>
<style>
body {
margin: 0;
font-family: Arial;
background-color: white;
}
.title {
background-color: #000046;
color: white;
padding: 10px;
}
.menu {
background-color: #010214;
padding: 10px;
height: 45px;
}
button.menu{
background: #FFA801;
border: 2px solid #FFA801;
padding: 5px 50px;
float: right;
margin-right: 60px;
font-size: 24px;
}
.datagrid table { border-collapse: collapse; text-align: right; width: 90%; } 
.datagrid {font: normal 12px/150% Arial, Helvetica, sans-serif;  overflow: none; }
.datagrid table td, .datagrid table th { padding: 3px 10px; }.datagrid table thead th {background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #006699), color-stop(1, #00557F) );background:-moz-linear-gradient( center top, #006699 5%, #00557F 100% );filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#006699', endColorstr='#00557F');background-color:#006699; color:#FFFFFF; font-size: 15px; font-weight: bold; border-left: 1px solid #0070A8; } .datagrid table thead th:first-child {  }.datagrid table tbody td { color: #00496B; border-left: 1px solid #E1EEF4;border-bottom: 1px solid #E1EEF4;font-size: 12px;font-weight: normal; }.datagrid table tbody .alt td { background: #E1EEF4; color: #00496B; }.datagrid table tbody td:first-child {  }.datagrid table tbody tr:last-child td { }.datagrid table tfoot td div { border-top: 1px solid #006699;background: #E1EEF4;} .datagrid table tfoot td { padding: 0; font-size: 12px } .datagrid table tfoot td div{ padding: 2px; }.datagrid table tfoot td ul { margin: 0; padding:0; list-style: none; text-align: right; }.datagrid table tfoot  li { display: inline; }.datagrid table tfoot li a { text-decoration: none; display: inline-block;  padding: 2px 8px; margin: 1px;color: #FFFFFF;border: 1px solid #006699;-webkit-border-radius: 3px; -moz-border-radius: 3px; border-radius: 3px; background:-webkit-gradient( linear, left top, left bottom, color-stop(0.05, #006699), color-stop(1, #00557F) );background:-moz-linear-gradient( center top, #006699 5%, #00557F 100% );filter:progid:DXImageTransform.Microsoft.gradient(startColorstr='#006699', endColorstr='#00557F');background-color:#006699; }.datagrid table tfoot ul.active, .datagrid table tfoot ul a:hover { text-decoration: none;border-color: #006699; color: #FFFFFF; background: none; background-color:#00557F;}div.dhtmlx_window_active, div.dhx_modal_cover_dv { position: fixed !important; }

.ptable td { height: 50px; vertical-align: top }

.past { color: lightgray; }

</style>
<script
  src="https://code.jquery.com/jquery-3.2.1.min.js"
  integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4="
  crossorigin="anonymous"></script>
<script>
function menu(i) { document.cookie = "page="+i; window.location = window.location.href; }
</script>
</head>
<body>
<div class="datagrid">
<?php
include "../../php/commonAdmin.php";
$mysqlHandle = commonConnectToDB();

$sql 		= "select * from domains where id = 526";
$result 	= commonDoQuery($sql);
$domainRow	= commonQuery_fetchRow($result);

$isUTF8 	= 1;
commonConnectToUserDB ($domainRow);

$cycle		= $_GET['cycle'];

switch ($_COOKIE['page'])
{
case 0:
case 1: // presence
		// read previous presence reports
		$prev = array();
		$sql = "select * from israeli_presence where cycle = $cycle";
		$res = commonDoQuery($sql);
		while ($row = commonQuery_fetchRow($res))
				$prev[$row['memberId']][$row['dayId']] = array($row['value'], $row['reason']);

		echo "<table class='ptable'>";
		
		$headersRow = "<tr><th>מס'</th><th>שם</th><th>מצב</th><th>קורס</th>";

		// list of courses dates
		$days = array();
		$sql = "SELECT id, dayDate, title, courseId as d FROM `israeli_coursesDays` where courseId < 104 order by dayDate asc";

		// [Oded removed dates from the course title] STR_TO_DATE(d,'%d.%m.%Y') asc";
		$lastDate = "";
		$cRes = commonDoQuery($sql);
		while ($cRow = commonQuery_fetchRow($cRes))
		{
				if ($lastDate != $cRow['dayDate'])
				{
						if (!empty($lastDays))
								array_push($days, $lastDays);
						$lastDays = array();
						
						$pastStyle = false; //(strtotime($cRow['d']) < strtotime("yesterday") ? " class='past' " : "");
						$headersRow .= "<th$pastStyle>".stripslashes($cRow['dayDate']." ".mb_substr($cRow['title'],0,20))."</th>";
						$lastDate = $cRow['dayDate'];
				}
				array_push($lastDays, array(0 => $cRow['id'], 1 => $cRow['d']));
		}
		array_push($days, $lastDays);

		$headersRow .= "</tr>";

		echo $headersRow;

		// list of registrars
		//
		// while a course is going on use:
		// 				where extraData3 != '' and extraData4 = 'student'
		// after course end use:
		// 				where clubMembers.id in (select distinct memberId from israeli_presence)
		//
		$sql = "select clubMembers.*, israeli_courses.name from clubMembers
				left join israeli_courses on extraData3 = pageId
				where extraData3 != '' and clubMembers.id in (select distinct memberId from israeli_presence where cycle = $cycle)
				order by lastname asc, firstname asc";
		$sRes = commonDoQuery($sql);
		for ($cnt = 1; $sRow = commonQuery_fetchRow($sRes); $cnt++)
		{
				$statusArray = explode("|", $sRow['extraData5']);
				$status = "<div style='width:220px'>";
				$status .= "<input type='checkbox' id='x1_$sRow[id]' ".($statusArray[0] == "true" ? 'checked' : '')." onchange='stat(\"x1_$sRow[id]\")'>קיבל ערכה ";
				$status .= "<input type='checkbox' id='x2_$sRow[id]' ".($statusArray[1] == "true" ? 'checked' : '')." onchange='stat(\"x2_$sRow[id]\")'>נתן תצהיר<br>";
				$status .= "<select id='x3_$sRow[id]' onchange='stat(\"x3_$sRow[id]\")'>
							<option value='0' ".($statusArray[2] == 0 ? 'selected' : '').">מצב התג</option>
							<option value='1' ".($statusArray[2] == 1 ? 'selected' : '').">תג תקין</option>
							<option value='2' ".($statusArray[2] == 2 ? 'selected' : '').">חסר תג</option>
							<option value='3' ".($statusArray[2] == 3 ? 'selected' : '').">תיקון תג</option>
							</select> ";
				$status .= "<select id='x4_$sRow[id]' onchange='stat(\"x4_$sRow[id]\")'>
							<option value='0' ".($statusArray[3] == 0 ? 'selected' : '').">מצב תשלום</option>
							<option value='1' ".($statusArray[3] == 1 ? 'selected' : '').">שילם הכל</option>
							<option value='2' ".($statusArray[3] == 2 ? 'selected' : '').">קיבל ח-ן</option>
							<option value='3' ".($statusArray[3] == 3 ? 'selected' : '').">יש בעיה</option>
							</select><br>";
				$status .= "<input type='checkbox' id='x5_$sRow[id]' ".($statusArray[4] == "true" ? 'checked' : '')." onchange='stat(\"x5_$sRow[id]\")'>קיבל תעודה ";
				$status .= "<input type='checkbox' id='x6_$sRow[id]' ".($statusArray[5] == "true" ? 'checked' : '')." onchange='stat(\"x6_$sRow[id]\")'>קיבל סיסמה<br>";
				$status .= "<input type='text' id='x7_$sRow[id]' placeholder='הערות' onblur='stat(\"x7_$sRow[id]\")' value='".htmlspecialchars(stripslashes($statusArray[6]), ENT_QUOTES)."'>";
				$status .= "</div>";

				$currExp = $prev[$sRow['id']];
				echo "<tr><td>$cnt</td><td>".stripslashes($sRow['lastname']." ".$sRow['firstname'])."</td><td>$status</td><td>$sRow[name]</td>";

				foreach ($days as $dayArr)
				{
						$select = "";
						foreach ($dayArr as $day)
							if ($currExp[$day[0]])
								$select = "<select id='p${day[0]}_$sRow[id]' name='p${day[0]}_$sRow[id]' onchange='statboy(\"${day[0]}_$sRow[id]\")' $pastStyle>
												<option value='0' ".($currExp[$day[0]][0] == 0 ? 'selected' : '')."></option>
												<option value='1' ".($currExp[$day[0]][0] == 1 ? 'selected' : '').">הגיע</option>
												<option value='2' ".($currExp[$day[0]][0] == 2 ? 'selected' : '').">איחר</option>
												<option value='3' ".($currExp[$day[0]][0] == 3 ? 'selected' : '').">יצא מוקדם</option>
												<option value='4' ".($currExp[$day[0]][0] == 4 ? 'selected' : '').">נעדר</option>
											</select>
							<input type='text' id='r${day[0]}_$sRow[id]' name='r${day[0]}_$sRow[id]' placeholder='סיבה' onblur='report(\"${day[0]}_$sRow[id]\")'
											".($currExp[$day[0]][0] > 1 ? "value='".str_replace("'", "\'", $currExp[$day[0]][1])."' style='width:120px;'"
											: "style='width:120px;display: none'")." $pastStyle />";
							else if ($sRow['extraData3'] == $day[1])
								$select = "<select id='p${day[0]}_$sRow[id]' name='p${day[0]}_$sRow[id]' onchange='statboy(\"${day[0]}_$sRow[id]\")' $pastStyle>
												<option value='0' selected></option>
												<option value='1' >הגיע</option>
												<option value='2' >איחר</option>
												<option value='3' >יצא מוקדם</option>
												<option value='4' >נעדר</option>
											</select>
							<input type='text' id='r${day[0]}_$sRow[id]' name='r${day[0]}_$sRow[id]' placeholder='סיבה' onblur='report(\"${day[0]}_$sRow[id]\")'
											style='width:120px;display: none' $pastStyle />";
	
						echo "<td>$select</td>";
				}

				echo "</tr>";

				if ($cnt % 4 == 0)
						echo $headersRow;
		}
		echo "</table>";
		echo "<br /><br /><br />"; // Edge compatability
		break;
}

function checking($dbField, $dbTable, $title)
{
		global $upRow, $exRow, $upRowPostedArr;

		//eval("\$arr = $upRow[posted];");
		if ($dbField == 'picFile')
		{
				$safeData = $upRow['picFile'];
				if (!$safeData)
						return;
		}
		else
				$safeData	= $upRowPostedArr[$dbField];

		if ($safeData == 'on') // checkbox
				$safeData = 1;

		$isDiff		= (preg_replace('/\s+/', ' ', $safeData) != str_replace('&#39;', "'", preg_replace('/\s+/', ' ', $exRow[$dbField])));
		if ($isDiff)
		{
				$safeData = str_replace("'", "&#39;", $safeData);
				$sqlCode = "update $dbTable set $dbField = '$safeData' where ";
				if ($dbTable == "clubMembers")
						$sqlCode .= "id = $exRow[id]";
				else
						$sqlCode .= "memberId = $exRow[id]";
		}
		$sqlCode = base64_encode ($sqlCode);
		if ($dbField == 'picFile')
		{
				$safeData = "<img src='../membersFiles/$safeData' height='80'>";
				$exRow[$dbField]= "<img src='../membersFiles/".$exRow[$dbField]."' height='80'>";
		}
		echo "<tr><td>$title</td><td".($isDiff?" style='color:red'":"").">$safeData</td><td>".$exRow[$dbField]."</td>
				  <td>".($isDiff?"<button onClick='updateProfile(\"$sqlCode\");this.remove()'>אישור השינוי</button>":"")."</td>
			  </tr>";
}
?>
<script>
function statboy(id)
{
		if ($('#p'+id).val() > 1)
		{
				$('#r'+id).show();
		}
		else
		{
				$('#r'+id).hide();
				$('#r'+id).val('');
		}

		report(id);
}
function report(id)
{
		$.ajax({
			type : "POST",
			url  : "ajaxServer.php",
			data : {formData: "formName=presence&fld="+id+"&value="+$('#p'+id).val()+"&reason="+$('#r'+id).val()}
		});
}
function stat(id)
{
		var val;
		if ($("#"+id).is("input:checkbox"))
				val = $("#"+id).is(':checked');
		else
				val = $("#"+id).val();

		$.ajax({
			type : "POST",
			url  : "ajaxServer.php",
			data : {formData: "formName=stat&fld="+id+"&value="+val}
		});
}
function updateProfile(sql)
{
		$.ajax({
			type : "POST",
			url  : "ajaxServer.php",
			data : {formData: "formName=updateProfile&value="+encodeURIComponent(sql)}
		});
}
</script>
</div>
</body></html>
