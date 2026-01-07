<?php

set_time_limit(0);
/*
mail ("liat@interuse.com", "Before", ini_get("memory_limit"));
ini_set("memory_limit", "15M"); 
mail ("liat@interuse.com", "After", ini_get("memory_limit"));
 */
include("/home/iboscoil/public_html/3.0/SimpleViewer_v17/ConvertCharset.class.php");

// create user directory

$filePath = "/home/iboscoil/public_html/3.0/html/SWFUpload/files/" . $_GET['userId'] . "/";

mkdir ("$filePath", 0777);

$origName 	= $_FILES['Filedata']['name'];

move_uploaded_file($_FILES['Filedata']['tmp_name'], $filePath . $origName);

chmod ($filePath . $origName, 0755);

$text 		= new ConvertCharset();
$origName2	= $text ->Convert($origName, "utf-8", "windows-1255");

if ($origName != $origName2)
	copy ("$filePath/$origName", "$filePath/$origName2");

echo "1";
?>
