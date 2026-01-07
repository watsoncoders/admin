<html lang="he" dir="rtl">

	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<link rel="stylesheet" type="text/css" href="font-awesome.min.css">
		<link rel="stylesheet" type="text/css" href="bootstrap.min.css">
		<link rel="stylesheet" type="text/css" href="tree.css">
		<title>פיתוח עץ</title>
		<script src="jquery.min.js.download"></script>
		<script src="bootstrap.min.js.download"></script>

		<script src="tree.js?v=3" charset="UTF-8"></script>

		<script>

			var global_expertId = <? echo $_GET['expertId']; ?>;

			$( document ).ready(function() 
			{
				$("body").on("click", ".deleteRmv", function (e) 
				{
					var catId = $(this).parent("p").attr("catId");

					$.getJSON("treeAjax.php?action=deleteExpertCat&expertId=" + global_expertId + "&value=" + catId, function(data)
					{
						if (data.status == "OK")
						{
							$("p#expertCat_" + data.catId).remove();
						}
						else
						{
							alert (data.message);
						}
					});
				});			

				$('.treeplant li a.paraplus').click(function()
				{
					$(this).toggleClass('paraplusClicked');
				});
			});
		</script>

	</head>

	<body>
<?

include "../../php/commonAdmin.php";

$mysqlHandle = commonConnectToDB();

$sql 		= "select * from domains where id = 526";
$result 	= commonDoQuery($sql);
$domainRow	= commonQuery_fetchRow($result);

$isUTF8 = 1;
commonConnectToUserDB ($domainRow);
//commonDoQuery("set names 'utf8'");

$uniqueId		= 0;
$disabledCats 	= array(3,4,5,6,191,253,288,814,815);

$sql			= "select categories_byLang.categoryId, categories_byLang.name from categoriesItems, categories_byLang 
				   where itemId = $expertId and categoriesItems.categoryId = categories_byLang.categoryId and categories_byLang.language = 'HEB'";
$result			= commonDoQuery($sql);

$cats			= "";

while ($row = commonQuery_fetchRow($result))
{
	$catId	 = $row['categoryId'];
	$name	 = getCategoryPath($catId);
	$cats	.= "<p class='pargrph' id='expertCat_$catId' catId='$catId'><i class='fa fa-times iconrtl deleteRmv'></i> <span>$name</span> </p>";
}

$tree	= "<div id='tree'>
				<div class='tree_in'>
					<ul class='DefineTree treeplant'>
						<li>
							<a data-toggle='collapse' href='#collapse" . ($uniqueId+1) . "' class='fromtree insExtra'></a>
							<span>עדים מומחים</span>" . 
							buildCategoryTree(3, false) . "
						</li>
					</ul>
					<ul class='DefineTree treeplant'>
						<li>
							<a data-toggle='collapse' href='#collapse" . ($uniqueId+1) . "' class='fromtree insExtra'></a>
							<span>בוררים</span>" . 
							buildCategoryTree(4, false) . "
						</li>
					</ul>
					<ul class='DefineTree treeplant'>
						<li>
							<a data-toggle='collapse' href='#collapse" . ($uniqueId+1) . "' class='fromtree insExtra'></a>
							<span>מגשרים</span>" . 
							buildCategoryTree(5, false) . "
						</li>
				    </ul>
				</div>
			</div>
			<div id='expertCats' class='rltdivp widthrldiv'>
				<div class='expertCatsTitle'>קטגוריות המומחה:</div>
				<div class='col-md-12 col-sm-12 col-xs-12 paddingZ clRmvse'>$cats</div>
			</div>";

/* ----------------------------------------------------------------------------------------------------	*/
/* getCategoryPath																						*/
/* ----------------------------------------------------------------------------------------------------	*/
function getCategoryPath ($catId)
{
	$sql	= "select parentId, name from categories
			   left join categories_byLang on id = categoryId and language = 'HEB'
			   where type = 'specific' and id = '$catId'";
	$result = commonDoQuery($sql);
	$row	= commonQuery_fetchRow($result);

	return ($row['parentId'] ? getCategoryPath($row['parentId']) . " &#10095; " : "") . $row['name'];
}

/* -------------------------------------------------------------------------------------------------------------------- */
/* buildCategoryTree																									*/							
/* -------------------------------------------------------------------------------------------------------------------- */
function buildCategoryTree($catId, $isLink = true, $isOpen = true)
{
	global $uniqueId, $disabledCats;

	$sql	= "select id, parentId, name from categories
			   left join categories_byLang on id = categoryId and language = 'HEB'
			   where type = 'specific' and parentId ='$catId'
			   order by pos asc";
	$result = commonDoQuery($sql);

	$html = "";
	for ($i = 0; $row = commonQuery_fetchRow($result); $i++)
	{
		$currId = ++$uniqueId;

		if ($i == 0) 
			$html .= "<ul id='collapse$currId' class='panel-collapse line-tree collapse" . ($isOpen ? " in" : "") . "'>";

		$sons	= buildCategoryTree($row['id'], $isLink, false);
		$flus	= ($sons == "" ? "" : "<a class='insExtra collapsed' data-toggle='collapse' href='#collapse" . ($currId+1) . "'></a>");

		if (in_array($row['id'], $disabledCats))
			$link 	= "javascript:void(0)";
		else
			$link	= ($isLink ? "index2.php?id=5125&catId=$row[id]&lang=HEB" : "javascript:onclickOptionTree(\"$row[name]\",$row[id],$row[parentId],0)");

		$html	.= "<li>$flus<a href='$link' class='paraplus'>$row[name]</a>";

		$html	.= $sons;
		$html	.= "</li>";
	}

	if ($i > 0) $html .= "</ul>";

	return $html;
}

echo $tree;

?>

</body>
</html>
