<?

include "commonAdmin.php";

if (!commonValidateSession())
{
	echo "<b>System Error</b> - please, call support<br/>
		  <br/><br/><font color='blue'><u>cookies</u>:</font><br/>";
	
	print_r ($_COOKIE);

	echo "<br/><br/><font color='blue'><u>get</u>:</font><br/>";
	print_r ($_GET);

	echo "<br/><br/><font color='blue'><u>post</u>:</font><br/>";
	print_r ($_POST);

	exit;
}

$fileName = "carTypes.csv";

// copy file to tmp
move_uploaded_file($_FILES['file']['tmp_name'], "/tmp/$fileName");

$f = fopen("/tmp/$fileName", "r");

$row		 = 0;

$queryStr	= "delete from carTypes";
commonDoQuery ($queryStr);

while ($line = fgets($f)) 
{ 
	$row++;

	$splitLine = explode(",", $line);

	$type			= commonPrepareToDB(addslashes(trim($splitLine[0])));
	$manufacturer	= commonPrepareToDB(addslashes(trim($splitLine[1])));
	$model			= commonPrepareToDB(addslashes(trim($splitLine[2])));

	$queryStr = "insert into carTypes (id, type, manufacturer, model) values ($row, '$type', '$manufacturer', '$model')";
	commonDoQuery ($queryStr);
}

fclose ($f);

unlink ("/tmp/$fileName");

header ("Location: ../html/content_tablets/handleCars.html?showCarTypes=1");
exit;

?>
