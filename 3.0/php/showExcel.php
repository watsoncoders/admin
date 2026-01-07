<?

include "commonAdmin.php";

$excelFile = "$ibosHomeDir/tempExcels/" . $_GET['excelFile'];

header("Content-type: application/octet-stream");
header("Content-Disposition: attachment; filename=" . $_GET['excelFile']);
header("Pragma: no-cache");
header("Expires: 0");

$file	= fopen($excelFile, "r");
$excel	= fread($file, filesize($excelFile));
fclose ($file);

echo $excel;

?>
