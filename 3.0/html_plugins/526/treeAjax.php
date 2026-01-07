<?php

include "../../php/commonAdmin.php";

$mysqlHandle = commonConnectToDB();

$sql = "select * from domains where id = 526";
$result 	= commonDoQuery($sql);
$domainRow	= commonQuery_fetchRow($result);

$isUTF8 = 1;
commonConnectToUserDB ($domainRow);

$action 	= $_GET['action'];
$expertId 	= isset($_GET['expertId']) ? $_GET['expertId'] : "";
$value  	= isset($_GET['value'])    ? $_GET['value']    : "";

header("Content-Type: application/json");

$status	= "OK";
$msg	= "";
$catId	= $value;
$name	= "";

if ($action == "addExpertCat" || $action == "checkAddExpertCat")
{
	$sql	= "select * from categoriesItems where itemId = $expertId and categoryId = $catId and type = 'specific'";
	$result	= commonDoQuery($sql);

	if (commonQuery_numRows($result) == 0)
	{
		if ($action == "addExpertCat")
		{
			$sql	= "insert into categoriesItems (itemId, categoryId, type) values ($expertId, $catId, 'specific')";
			commonDoQuery($sql);
		}
	}
	else
	{
		$status	= "Error";
		$msg	= "המומחה כבר משוייך לקטגוריה זו";
	}
}

if ($action == "deleteExpertCat")
{
	$sql	= "select * from categoriesItems where itemId = $expertId and categoryId = $catId and type = 'specific'";
	$result	= commonDoQuery($sql);

	if (commonQuery_numRows($result) == 0)
	{
		$status	= "Error";
		$msg	= "שגיאה - הקטגוריה לא מקושרת למומחה";
	}
	else
	{
		$sql	= "delete from categoriesItems where itemId = $expertId and categoryId = $catId and type = 'specific'";
		commonDoQuery($sql);
	}
}

if ($action == "nextCatId")
{
	$sql	= "select max(id) from categories";
	$result	= commonDoQuery($sql);
	$row	= commonQuery_fetchRow($result);
	$catId	= $row[0] + 1;
}

if ($action == "edit")
{
	$name 	  		= $_GET["name"];
	$parentId		= $_GET["parentId"];
	$afterSibling	= $_GET["afterSibling"];

	if ($name != "")
	{
		$sql	= "select * from categories where id = $catId";
		$result	= commonDoQuery($sql);

		if (commonQuery_numRows($result) == 0)
		{
			// find pos
			if ($afterSibling == 0)
			{
				$sql	= "select max(pos) from categories where parentId = $parentId and type = 'specific'";
				$result	= commonDoQuery($sql);
				$pos	= 1;

				if (commonQuery_numRows($result) != 0)
				{
					$row	= commonQuery_fetchRow($result);
					$pos	= $row[0] + 1;
				}
			}
			else
			{
				$sql	= "select pos from categories where id = $afterSibling and type = 'specific'";
				$result	= commonDoQuery($sql);
				$row	= commonQuery_fetchRow($result);
				$pos	= $row[0] + 1;
	
				// increase pos for categories ahead
				$sql	= "update categories set pos = pos + 1 where parentId = $parentId and type = 'specific' and pos >= $pos";
				commonDoQuery($sql);
			}

			// add new category
			$sql	= "insert into categories (id, parentId, type, pos) values ($catId, '$parentId', 'specific', $pos)";
			commonDoQuery($sql);

			$sql	= "insert into categories_byLang (categoryId, language, name) values ($catId, 'HEB', '$name')";
			commonDoQuery($sql);
		}
		else
		{
			// update category name
			$sql	= "update categories_byLang set name = '$name' where categoryId = $catId";
			commonDoQuery($sql);
		}
	}
}

if ($action == "delete")
{
	// find siblings of this categoryId
	$ids	= array("$catId");

	$ids	= siblingsOf ($catId, $ids);

	$idsStr	= join(",", $ids);

	$sql	= "select count(*) from categoriesItems where categoryId in ($idsStr) and type = 'specific'";
	$result	= commonDoQuery($sql);
	$row	= commonQuery_fetchRow($result);

	if ($row[0] == 0)
	{
		$sql	= "delete from categories where id in ($idsStr)";
		commonDoQuery($sql);

		$sql	= "delete from categories_byLang where categoryId in ($idsStr)";
		commonDoQuery($sql);
	}
	else
	{
		$status	= "Error";
		$msg	= "לא ניתן למחוק קטגוריה זו. ישנם $row[0] מומחים שמקושרים אליה";
	}
}

$data	= array("status"	=> $status,
				"message"	=> $msg,
				"catId"		=> $catId,
				"name"		=> $name);					

echo json_encode($data);

function siblingsOf ($catId, $ids)
{
	$sql	= "select id from categories where parentId = $catId";
	$result	= commonDoQuery($sql);

	if (commonQuery_numRows($result) == 0) return $ids;

	while ($row	= commonQuery_fetchRow($result))
	{
		array_push($ids, $row['id']);

		return siblingsOf ($row['id'], $ids);
	}
}
