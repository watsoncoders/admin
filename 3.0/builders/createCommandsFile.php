<?

include "../php/commonAdmin.php";

while (list($key, $val) = each($_GET)) 
{
	eval("$$key = '$val';");
}
reset($_GET);

while (list($key, $val) = each($_POST)) 
{
	eval("$$key = '$val';");
}
reset($_POST);

// connect to database
// ----------------------------------------------------------------------------------------------------------------------------------
$mysqlHandle = mysql_connect("localhost", "interuse_admin", "mimamu") or die(mysql_error()); 
mysql_select_db("interuse_admin", $mysqlHandle) or die(mysql_error()); 

if ($action == "add")
{
	$fileName = "files/commands_$objName"."s.php";

	copy ("commands_xxx.php", $fileName);

	$file	 = fopen ($fileName, "r");
	$content = fread ($file, filesize($fileName));
	fclose ($file);

	$content = str_replace("XXX", 		  ucfirst($objName), $content);
	$content = str_replace("xxx", 		  $objName,   $content);
	$content = str_replace("#tableName#", $tableName, $content);


	// connect to domain db
	$sql		= "select * from domains where id=$domainId";
	$result 	= commonDoQuery($sql) or die(mysql_error(). " Query was: " . $sql );
	$domainRow	= commonQuery_fetchRow($result);

	commonConnectToUserDB ($domainRow);

	$sql	= "desc $tableName";
	$result	= commonDoQuery($sql) or die(mysql_error(). " Query was: " . $sql );

	$tags 	= "";
	while ($row = commonQuery_fetchRow($result))
	{
		$tags .= ", \"$row[Field]\"";
	}
	
	$tags = trim($tags, ", ");

	$langTags = "";

	if ($tableByLangName == "true")
	{	
		$sql	= "desc $tableName" . "_byLang";
		$result	= commonDoQuery($sql); // or die(mysql_error(). " Query was: " . $sql );

		if ($result != "")
		{
			while ($row = commonQuery_fetchRow($result))
			{	
				$langTags .= ", \"$row[Field]\"";
			}
	
			$langTags = trim($langTags, ", ");
		}
	}	

	$content = str_replace("#tags#",  	  $tags,   		$content);
	$content = str_replace("#langTags#",  $langTags,   $content);

	$file	 = fopen ($fileName, "w");
	fwrite ($file, $content);
	fclose ($file);

	$url = "createCommandsFile.php?msg=success";
	header ("Location: $url");
	exit;
}

// domains
// ------------------------------------------------------------------------------------------------------------------------------
$fieldWidth = "210px";

$domains  = "<select id='domainId' name='domainId' style='direction:ltr;width:$fieldWidth' onchange='reload()'>
				<option value='0'></option>";

$sql 	= "select id, domainName
		   from domains
		   where version = '2.0'
		   order by domainName";
$result = commonDoQuery($sql) or die(mysql_error());

while ($row = commonQuery_fetchRow($result))
{
	$selected = "";
	if ($domainId == $row[id]) $selected = " selected";
		$domains .= "<option value='$row[id]' $selected>$row[domainName]</option>";
}
$domains .= "</select>";

// ------------------------------------------------------------------------------------------------------------------------------

if ($domainId != 0)
{
	// connect to domain db
	$sql		= "select * from domains where id=$domainId";
	$result 	= commonDoQuery($sql) or die(mysql_error(). " Query was: " . $sql );
	$domainRow	= commonQuery_fetchRow($result);

	commonConnectToUserDB ($domainRow);
}

// tables
// ------------------------------------------------------------------------------------------------------------------------------
$tables  = "<select id='tableName' name='tableName' style='direction:ltr;width:$fieldWidth' onchange='reload()'>
				<option value=''></option>";

if ($domainId != "")
{
	$sql 	= "show tables";
	$result = commonDoQuery($sql) or die(mysql_error(). " Query was: " . $sql );

	while ($row = commonQuery_fetchRow($result))
	{
		if (strpos($row[0], "_byLang") != false) continue;

		$selected = "";
		if ($tableName == $row[0]) $selected = " selected";
			$tables .= "<option value='$row[0]' $selected>$row[0]</option>";
	}
}

$tables .= "</select>";

// ------------------------------------------------------------------------------------------------------------------------------
$cols	= "";

if ($tableName != "")
{
	$sql	= "desc $tableName";
	$result	= commonDoQuery($sql) or die(mysql_error(). " Query was: " . $sql );

	while ($row = commonQuery_fetchRow($result))
	{
		$cols .= ", $row[Field]";
	}
}

$cols = trim($cols, ", ");

// ------------------------------------------------------------------------------------------------------------------------------

$colsByLang = "";

if ($tableName != "" && $tableByLangName == "true")
{
	$sql	= "desc $tableName" . "_byLang";
	$result	= commonDoQuery($sql); // or die(mysql_error(). " Query was: " . $sql );

	if ($result != "")
	{
		while ($row = commonQuery_fetchRow($result))
		{	
			$colsByLang .= ", $row[Field]";
		}

		$colsByLang = trim($colsByLang, ", ");
	}
}	

switch ($msg)
{
	case "success" :
		$showMsg = "alert ('הקובץ נוצר בהצלחה !')";
		break;

	default : 
		$showMsg = "";
}

echo   "<html dir='rtl'>
			<head>
				<title>יצירת קובץ commands</title>
				<script type='text/javascript'> 
					function onload ()
					{
						$showMsg	
					}

					function reload ()
					{
						window.location.href = 'createCommandsFile.php?domainId=' 		 + form.domainId.value + 
																	 '&tableName=' 		 + form.tableName.value + 
																	 '&objName=' 		 + form.objName.value + 
																	 '&tableByLangName=' + form.tableByLangName.checked;
					}

					function submitForm ()
					{
						return true;
					}
				</script>	
			</head>

			<body style='text-align:center;font-family:arial;' onload='onload()'>
				<h1>הוספת קובץ commands</h1>
				<form id='form' name='form' method='post' action='createCommandsFile.php' onsubmit='return submitForm()'>
				<input type='hidden' name='action' value='add' />
	 			<table cellspacing='2' cellpadding='0' border='0'>
				<tr>
					<td>בחירת Domain:</td>
					<td>$domains</td>
				</tr>
				<tr>
				 	<td>טבלת נתונים : </td>
					<td>$tables</td>
				</tr>
				<tr>
				 	<td>טבלת נתונים לפי שפה : </td>
					<td>
						<input type='checkbox' id='tableByLangName' name='tableByLangName' " . (($tableByLangName == "true") ? "checked" : "") . "/>
					</td>
				</tr>
				<tr>
					<td valign='top'>עמודות:</td>
					<td>
						<div style='width:$fieldWidth;direction:ltr'>$cols</div>
						<div style='width:$fieldWidth;direction:ltr;border-top:1px solid black'>$colsByLang</div>
					</td>
				</tr>
				<tr>
					<td>שם אובייקט:</td>
					<td>
						<input type='text' id='objName' name='objName' style='width:$fieldWidth;direction:ltr' />
				  	</td>	
				</tr>
				<tr>
					<td></td>
					<td><input type='submit' value='סע!' style='margin-right:75px' /></td>
				</tr>
				</table>
			</body>
		</html>";

?>
