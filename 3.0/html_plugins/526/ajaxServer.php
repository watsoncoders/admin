<?

include "../../php/commonAdmin.php";
$mysqlHandle = commonConnectToDB();

$sql 		= "select * from domains where id = 526";
$result 	= commonDoQuery($sql);
$domainRow	= commonQuery_fetchRow($result);

$isUTF8 	= 1;
commonConnectToUserDB ($domainRow);


$formDataArray = array();
parse_str($_POST['formData'], $formDataArray);
	
if ($formDataArray['formName'] == 'presence')
{
		$id = commonQuery_escapeStr($formDataArray['fld']);
		$value = commonQuery_escapeStr($formDataArray['value']);
		$reason = commonQuery_escapeStr($formDataArray['reason']);

		list($dayId, $memberId) = explode('_', $id);

		if (!is_numeric($dayId) || !is_numeric($memberId) || !is_numeric($value))
				exit;

		commonDoQuery("replace israeli_presence set memberId = $memberId, dayId = $dayId, value = $value, reason = '$reason', lastUpdate = now()");

		exit;
}
if ($formDataArray['formName'] == 'stat')
{
		$fld = commonQuery_escapeStr($formDataArray['fld']);
		$value = commonQuery_escapeStr($formDataArray['value']);

		$arr = explode('_', $fld);
		$memberId = $arr[1];
		if (!is_numeric($memberId)) exit;
		$n = substr($arr[0],1);
		if (!is_numeric($n)) exit;
		$n--;

		$res = commonDoQuery("select extraData5 from clubMembers where id = $memberId");
		$row = commonQuery_fetchRow($res);
		if (!$row) exit;
		if ($row['extraData5'] == '')
				$row['extraData5'] = '||||||';
		$ex5 = explode('|', $row['extraData5']);
		$ex5[$n] = commonQuery_escapeStr($value);

		commonDoQuery("update clubMembers set extraData5 = '".implode('|', $ex5)."' where id = $memberId");
}
if ($formDataArray['formName'] == 'updateProfile')
{
		$value = commonQuery_escapeStr($formDataArray['value']);

		if ($value)
		{
				$decodedSql = base64_decode($value);
				//mail("amir@interuse.co.il", "ajaxServer @ israeli admin", $decodedSql);
				if (strpos($decodedSql, 'categoriesItems'))
						foreach(explode(';', $decodedSql) as $aSql)
								commonDoQuery($aSql);
				else
						commonDoQuery($decodedSql);
		}
}
?>
