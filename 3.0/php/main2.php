<?
	include "commonAdmin.php";

	list ($websiteShortName, $pluginResults) = commonValidateSession (true);
	
	# query for private plugins
	if ($pluginResults) {
	 $pluginsDisplay = "";
	 $pluginsMenu = "";
	 while ($row2 = commonQuery_fetchRow($pluginResults)) {
		$pluginsDisplay .= "tr_".$row2['name'].".style.display			= display;\n";
		if ($pluginsMenu == "") // first time - add the Plugins header
		{
			$pluginsMenu .= "<tr height=\"24\" align=\"right\" onclick=\"openCloseMenu('plugins')\">
								<td>
									<span class=\"styleMenuOn\" style='color:#191970'>תוספות ייחודיות</span>&nbsp;
								</td>
								<td><img id=\"menuImg_plugins\" src=\"../images/openMenu.gif\" style=\"margin-top:4px\"></td>
								<td></td>
							</tr>";
		}
		$pluginsMenu .= "<tr height=\"24\" align=\"right\" id=\"tr_".$row2['name']."\" style=\"display:none\">
								<td width=\"130\" onclick=\"showPage('".$row2['name']."','../html/handle".ucwords($row2['name']).".html')\" align=\"right\">
									<span id=\"menuSpan_".$row2['name']."\" class=\"styleMenu\">".$row2['title']."</span>&nbsp;
								</td>
								<td><img id=\"menuImg_".$row2['name']."\" src=\"../imagesNew/Arrow_up.gif\"></td>
								<td width=\"25\"></td>
							</tr>";
	 }
	 if ($pluginsMenu != "") // last time - add seperating line
	 {
		$pluginsMenu .= "<tr height=\"1\" id=\"sep_general\"><td colspan=\"3\"><img src=\"../imagesNew/menuSep.gif\"></td></tr>";
	 }
	}
	
	# save user languages
	if ($websiteShortName)
	{
		$queryStr	 = "select langs from globalParms";
		$result	     = commonDoQuery ($queryStr);
		$row	     = commonQuery_fetchRow($result);

		$usedLangs = $row['langs'];

		setcookie ("goToPage","");
		//"HEB,ENG,FRN");
	}


?>

<html>

	<head>
	
			<meta http-equiv = "content-type" content="text/html;charset=windows-1255">
			<title>interUse - מערכת ניהול אתרים דינמיים</title>
			<link rel="stylesheet" href="../css/common.css" type="text/css">
			<script language="JavaScript" src="../javascript/general.js"></script>

			<script language="JavaScript">
			<!--
					var currId = "home";
					function onLoad ()
					{
						var langs = "<? echo $usedLangs; ?>";
						top.globalFrame.langArray = langs.split(",");
						top.globalFrame.websiteShortName = "<? echo $websiteShortName; ?>";
					}
					
					function showPage (id, url)
					{
						eval("menuSpan_" + currId).className = "styleMenu";
						eval("menuImg_"  + currId).src	    = "../imagesNew/Arrow_up.gif";
						currId = id;
						eval("menuSpan_" + currId).className = "styleMenuOn";
						eval("menuImg_"  + currId).src	    = "../imagesNew/Arrow_Down.gif";

						top.mainFrame.location.replace(url);
					}

					var currMenu = "data";
					function openCloseMenu (which)
					{
						if (which == currMenu)
						{
							currImg = eval("menuImg_" + currMenu);
							if (currImg.src.indexOf("openMenu") != -1)
							{
								currImg.src = "../images/closeMenu.gif";
								display		= "";
							}
							else
							{
								currImg.src = "../images/openMenu.gif";
								display		= "none";
							}
							setDisplay (which, display);
						}
						else
						{
							eval("menuImg_" + currMenu).src = "../images/openMenu.gif";
							setDisplay (currMenu, "none");
							
							currMenu = which;

							eval("menuImg_" + currMenu).src = "../images/closeMenu.gif";
							setDisplay (currMenu, "");
						}
					}

					function setDisplay (which, display)
					{
						switch (which)
						{
							case "general"	:
								tr_langs.style.display			= display;
								tr_parms.style.display			= display;
								tr_security.style.display		= display;
								tr_statistics.style.display		= display;
								tr_static.style.display			= display;

								break;

							case "graphic" :

								tr_design.style.display			= display;
								tr_styles.style.display			= display;
								break;

							case "data" :
								tr_categories.style.display		= display;
								tr_pages.style.display			= display;
								tr_essays.style.display			= display;
								tr_urls.style.display			= display;
								tr_boxes.style.display			= display;
								tr_files.style.display			= display;
								tr_forums.style.display			= display;
								tr_galleries.style.display		= display;
								break;

							case "shopping" :
								tr_shopConfig.style.display		= display;
								tr_products.style.display		= display;
								tr_shipments.style.display		= display;
								tr_feedbacks.style.display		= display;
								tr_orders.style.display			= display;
								break;

							case "club" :
								tr_clubConfig.style.display		= display;
								tr_clubMembers.style.display	= display;
								tr_clubMailingLists.style.display	= display;
								break;

							case "plugins" :
								<? echo $pluginsDisplay; ?>
						}
					}

			-->		
			</script>
    </head>
	<body onLoad="onLoad()" topMargin="0" leftMargin="0" rightMargin="0">
			<table bgcolor="#A4AFAE" border="0" width="100%" height="100%">
			<tr><td align="center">
			<table cellspacing="0" cellpadding="0" border="0">
			<tr valign="top">
					<td colspan="4"><img src="../imagesNew/Header.gif"></td>
			</tr>
			<tr valign="top">
					<td background="../imagesNew/Shadow_L_Bg.gif" width="8" height="570"></td>
					<td rowspan="2" width="720" height="100%">
						<table bgcolor="white" width="100%" height="585">
						<tr>
							<td align="right">
								<table cellpadding="0" cellspacing="0" border="0">
								<tr valign="top">
									<td width="30px" rowspan="3">&nbsp;</td>
									<td width="7px" valign="top"><img src="../imagesNew/bodyTL.gif"></img></td>
									<td style="background-image:url(../imagesNew/bodyT.gif);background-repeat: repeat-x" width="100%"></td>
									<td width="7px"><img src="../imagesNew/bodyTR.gif"></img></td>
								</tr>
								<tr height="550">
									<td background="../imagesNew/bodyL.gif"></img</td>
									<td background="../imagesNew/body.gif">
										<iframe name ="mainFrame" frameborder="0" width="100%" height="100%" 
										 	src="<?

if ($goToPage == "usedLangs")
	$goTo =  "../html/handleLangs2.html";
else
	$goTo = "../html/userHomePage2.html";

echo $goTo; ?>"></iframe>
									<iframe name="globalFrame" frameborder="0" width="0" height="0"
											src="../html/global.html"></iframe>
									</td>
									<td background="../imagesNew/bodyR.gif"></img</td>
								</tr>
								<tr height="7">
									<td width="7px"><img src="../imagesNew/bodyBL.gif"></img></td>
									<td style="background-image:url(../imagesNew/bodyB.gif);background-repeat: repeat-x" width="100%"></td>
									<td width="7px"><img src="../imagesNew/bodyBR.gif"></img></td>
								</tr>
								</table>	
							</td>
						</tr>
						</table>
					</td>
					<td rowspan="2" valign="top" bgcolor="white" height="585"><img src="../imagesNew/Page_TopCorner.gif"></td>
					<td background="../imagesNew/Bar_bg.gif" width="162" height="570">
						<table cellspacing="0" cellpadding="0" border="0">
							<!-- ---------------------------------------------------------------------------------------------------- -->
							<tr height="1"><td colspan="3"><img src="../imagesNew/menuSep.gif"></td></tr>
							<tr height="24" align="right">
								<td colspan="2">
									<span class="styleMenuOn" style='color:#191970'><? echo substr($websiteShortName,0,24); ?></span>
								</td>
								<td></td>
							</tr>
							<!-- ---------------------------------------------------------------------------------------------------- -->
							<tr height="1"><td colspan="3"><img src="../imagesNew/menuSep.gif"></td></tr>
							<tr height="24" align="right">
								<td width="130" onclick="showPage('home','../html/userHomePage2.html')" align="right">
									<span id="menuSpan_home" class="styleMenuOn">דף הבית</span>&nbsp;
								</td>
								<td><img id="menuImg_home" src="../imagesNew/Arrow_Down.gif"></td>
								<td width="25"></td>
							</tr>
							<!-- ---------------------------------------------------------------------------------------------------- -->
							<tr height="1"><td colspan="3"><img src="../imagesNew/menuSep.gif"></td></tr>
							<tr height="24" align="right" onclick="openCloseMenu('general')">
								<td>
									<span class="styleMenuOn" style='color:#191970'>הגדרות כלליות</span>&nbsp;
								</td>
								<td><img id="menuImg_general" src="../images/openMenu.gif" style="margin-top:4px"></td>
								<td></td>
							</tr>
							<!-- ---------------------------------------------------------------------------------------------------- -->
							<tr height="24" align="right" id="tr_langs" style="display:none">
								<td onclick="showPage('langs','../html/handleLangs2.html')">
									<span id="menuSpan_langs" class="styleMenu">שפות נתמכות</span>&nbsp;
								</td>
								<td><img id="menuImg_langs" src="../imagesNew/Arrow_up.gif"></td>
								<td></td>
							</tr>
							<!-- ---------------------------------------------------------------------------------------------------- -->
							<tr height="24" align="right" id="tr_parms" style="display:none">
							<? 	if ($usedLangs != "")
							   	{
							?>
								<td onclick="showPage('parms','../html/handleGlobalParms2.html')">
							<?	}
								else
								{
							?>
								<td onclick="alert('יש לקבוע שפה אחת לפחות בדף שפות')">

							<?	}
							?>
									<span id="menuSpan_parms" class="styleMenu">פרמטרים כלליים</span>&nbsp;
								</td>
								<td><img id="menuImg_parms" src="../imagesNew/Arrow_up.gif"></td>
								<td></td>
							</tr>
							<!-- ---------------------------------------------------------------------------------------------------- -->
							<tr height="24" align="right" id="tr_security" style="display:none">
								<td onclick="showPage('security','../html/handleSecurity2.html')">
									<span id="menuSpan_security" class="styleMenu">אבטחה</span>&nbsp;
								</td>
								<td><img id="menuImg_security" src="../imagesNew/Arrow_up.gif"></td>
								<td></td>
							</tr>
							<!-- ---------------------------------------------------------------------------------------------------- -->
							<tr height="24" align="right" id="tr_statistics" style="display:none">
								<td onclick="showPage('statistics','../html/handleStatistics2.html')">
									<span id="menuSpan_statistics" class="styleMenu">סטטיסטיקה</span>&nbsp;
								</td>
								<td><img id="menuImg_statistics" src="../imagesNew/Arrow_up.gif"></td>
								<td></td>
							</tr>
							<!-- ---------------------------------------------------------------------------------------------------- -->
							<tr height="24" align="right" id="tr_static" style="display:none">
								<td onclick="showPage('static','../html/handleStaticPages2.html')">
									<span id="menuSpan_static" class="styleMenu">דפים סטטיים</span>&nbsp;
								</td>
								<td><img id="menuImg_static" src="../imagesNew/Arrow_up.gif"></td>
								<td></td>
							</tr>
							<tr height="1" id="sep_general"><td colspan="3"><img src="../imagesNew/menuSep.gif"></td></tr>
							<!-- ---------------------------------------------------------------------------------------------------- -->
							<tr height="24" align="right" onclick="openCloseMenu('graphic')">
								<td>
									<span class="styleMenuOn" style='color:#191970'>עיצוב גרפי</span>&nbsp;
								</td>
								<td><img id="menuImg_graphic" src="../images/openMenu.gif" style="margin-top:4px"></td>
								<td></td>
							</tr>
							<!-- ---------------------------------------------------------------------------------------------------- -->
							<tr height="24" align="right" id="tr_design" style="display:none">
								<td onclick="showPage('design','../html/handleDesign2.html')">
									<span id="menuSpan_design" class="styleMenu">הגדרות - <font color="red">לא זמין</font></span>&nbsp;
								</td>
								<td><img id="menuImg_design" src="../imagesNew/Arrow_up.gif"></td>
								<td></td>
							</tr>
							<!-- ---------------------------------------------------------------------------------------------------- -->
							<tr height="24" align="right" id="tr_styles" style="display:none">
								<td onclick="showPage('styles','../html/handleStyles2.html')">
									<span id="menuSpan_styles" class="styleMenu">ניהול סגנונות</span>&nbsp;
								</td>
								<td><img id="menuImg_styles" src="../imagesNew/Arrow_up.gif"></td>
								<td></td>
							</tr>
							<tr height="1"><td colspan="3"><img src="../imagesNew/menuSep.gif"></td></tr>
							<!-- ---------------------------------------------------------------------------------------------------- -->
							<tr height="24" align="right" onclick="openCloseMenu('data')">
								<td>
									<span class="styleMenuOn" style='color:#191970'>ניהול תוכן</span>&nbsp;
								</td>
								<td><img id="menuImg_data" src="../images/closeMenu.gif" style="margin-top:4px"></td>
								<td></td>
							</tr>
							<!-- ---------------------------------------------------------------------------------------------------- -->
							<tr height="24" align="right" id="tr_categories">
							<? 	if ($usedLangs != "")
							   	{
							?>
									<td onclick="showPage('categories','../html/handleCategoriesTab2.html')">
							<?	
								}
								else
								{
							?>
								<td onclick="alert('יש לקבוע שפה אחת לפחות בדף שפות')">

							<?	}
							?>
									<span id="menuSpan_categories" class="styleMenu">ניהול קטגוריות</span>&nbsp;
								</td>
								<td><img id="menuImg_categories" src="../imagesNew/Arrow_up.gif"></td>
								<td></td>
							</tr>
							<!-- ---------------------------------------------------------------------------------------------------- -->
							<tr height="24" align="right" id="tr_pages">
							<? 	if ($usedLangs != "")
							   	{
									if ($websiteShortName == "goldpharm/new" ||
										$websiteShortName == "mikra-ahuvia" ||
										$websiteShortName == "bahava"       ||
										$websiteShortName == "homeopathmd"  ||
										$websiteShortName == "iq2you/new"   ||
										$websiteShortName == "hibuk/new"    ||
										$websiteShortName == "compex"       ||
										$websiteShortName == "yesivuk"      ||
										//$websiteShortName == "carlight"     ||
										$websiteShortName == "machon-kama"  ||
										strstr($websiteShortName,"62")       ||
										$websiteShortName == "talburg"       
									   ) 
									{
							?> 
										<td onclick="showPage('pages','../html/handlePages1.html')">
							<?		}
									else
									{
										if ($websiteShortName == "etgarim/demo")
										{
							?>
											<td onclick="showPage('pages','../html/handlePagesTab.html')">
							<?
										}
										else
										{
							?>
											<td onclick="showPage('pages','../html/handlePages.html')">
							<?			}
									} 
								}
								else
								{
							?>
								<td onclick="alert('יש לקבוע שפה אחת לפחות בדף שפות')">

							<?	}
							?>
									<span id="menuSpan_pages" class="styleMenu">ניהול דפים</span>&nbsp;
								</td>
								<td><img id="menuImg_pages" src="../imagesNew/Arrow_up.gif"></td>
								<td></td>
							</tr>
							<!-- ---------------------------------------------------------------------------------------------------- -->
							<tr height="24" align="right" id="tr_essays">
							<? 	if ($usedLangs != "")
							   	{
							?>
								<td onclick="showPage('essays','../html/handleEssays.html')">
							<?	}
								else
								{
							?>
								<td onclick="alert('יש לקבוע שפה אחת לפחות בדף שפות')">

							<?	}
							?>
									<span id="menuSpan_essays" class="styleMenu">ניהול כתבות</span>&nbsp;
								</td>
								<td><img id="menuImg_essays" src="../imagesNew/Arrow_up.gif"></td>
								<td></td>
							</tr>
							<!-- ---------------------------------------------------------------------------------------------------- -->
							<tr height="24" align="right" id="tr_urls">
							<? 	if ($usedLangs != "")
							   	{
							?>
								<td onclick="showPage('urls','../html/handleUrls2.html')">
							<?	}
								else
								{
							?>
								<td onclick="alert('יש לקבוע שפה אחת לפחות בדף שפות')">

							<?	}
							?>
									<span id="menuSpan_urls" class="styleMenu">ניהול קישורים</span>&nbsp;
								</td>
								<td><img id="menuImg_urls" src="../imagesNew/Arrow_up.gif"></td>
								<td></td>
							</tr>
							<!-- ---------------------------------------------------------------------------------------------------- -->
							<tr height="24" align="right" id="tr_boxes">
							<? 	if ($usedLangs != "")
							   	{
							?>
								<td onclick="showPage('boxes','../html/handleBoxes2.html')">
							<?	}
								else
								{
							?>
								<td onclick="alert('יש לקבוע שפה אחת לפחות בדף שפות')">

							<?	}
							?>
									<span id="menuSpan_boxes" class="styleMenu">ניהול תיבות</span>&nbsp;
								</td>
								<td><img id="menuImg_boxes" src="../imagesNew/Arrow_up.gif"></td>
								<td></td>
							</tr>
							<!-- ---------------------------------------------------------------------------------------------------- -->
							<tr height="24" align="right" id="tr_files">
								<td onclick="showPage('files','../html/handleFiles2.html')">
									<span id="menuSpan_files" class="styleMenu">ניהול קבצים</span>&nbsp;
								</td>
								<td><img id="menuImg_files" src="../imagesNew/Arrow_up.gif"></td>
								<td></td>
							</tr>
							<!-- ---------------------------------------------------------------------------------------------------- -->
							<tr height="24" align="right" id="tr_forums">
								<td onclick="showPage('forums','../html/handleForums2.html')">
									<span id="menuSpan_forums" class="styleMenu">ניהול פורומים</span>&nbsp;
								</td>
								<td><img id="menuImg_forums" src="../imagesNew/Arrow_up.gif"></td>
								<td></td>
							</tr>
							<!-- ---------------------------------------------------------------------------------------------------- -->
							<tr height="24" align="right" id="tr_galleries">
								<td onclick="showPage('galleries','../html/handleGalleries2.html')">
									<span id="menuSpan_galleries" class="styleMenu">ניהול גלריות</span>&nbsp;
								</td>
								<td><img id="menuImg_galleries" src="../imagesNew/Arrow_up.gif"></td>
								<td></td>
							</tr>
							<tr height="1"><td colspan="3"><img src="../imagesNew/menuSep.gif"></td></tr>
							<!-- ---------------------------------------------------------------------------------------------------- -->
							<tr height="24" align="right" onclick="openCloseMenu('shopping')">
								<td>
									<span class="styleMenuOn" style='color:#191970'>מסחר אלקטרוני</span>&nbsp;
								</td>
								<td><img id="menuImg_shopping" src="../images/openMenu.gif" style="margin-top:4px"></td>
								<td></td>
							</tr>
							<!-- ---------------------------------------------------------------------------------------------------- -->
							<tr height="24" align="right" id="tr_shopConfig" style="display:none">
								<td onclick="showPage('shopConfig','../html/handleShopConfig2.html')">
									<span id="menuSpan_shopConfig" class="styleMenu">הגדרות</span>&nbsp;
								</td>
								<td><img id="menuImg_shopConfig" src="../imagesNew/Arrow_up.gif"></td>
								<td></td>
							</tr>
							<!-- ---------------------------------------------------------------------------------------------------- -->
							<tr height="24" align="right" id="tr_products" style="display:none">
							<? 	if ($usedLangs != "")
							   	{
							?>
								<td onclick="showPage('products','../html/handleShopProducts2.html')">
							<?	}
								else
								{
							?>
								<td onclick="alert('יש לקבוע שפה אחת לפחות בדף שפות')">

							<?	}
							?>
									<span id="menuSpan_products" class="styleMenu">ניהול מוצרים</span>&nbsp;
								</td>
								<td><img id="menuImg_products" src="../imagesNew/Arrow_up.gif"></td>
								<td></td>
							</tr>
							<!-- ---------------------------------------------------------------------------------------------------- -->
							<tr height="24" align="right" id="tr_shipments" style="display:none">
							<? 	if ($usedLangs != "")
							   	{
							?>
								<td onclick="showPage('shipments','../html/handleShopShipments2.html')">
							<?	}
								else
								{
							?>
								<td onclick="alert('יש לקבוע שפה אחת לפחות בדף שפות')">

							<?	}
							?>
									<span id="menuSpan_shipments" class="styleMenu">טבלת משלוחים</span>&nbsp;
								</td>
								<td><img id="menuImg_shipments" src="../imagesNew/Arrow_up.gif"></td>
								<td></td>
							</tr>
							<!-- ---------------------------------------------------------------------------------------------------- -->
							<tr height="24" align="right" id="tr_feedbacks" style="display:none">
								<td onclick="showPage('feedbacks','../html/handleShopFeedbacks2.html')">
									<span id="menuSpan_feedbacks" class="styleMenu">תגובות גולשים</span>&nbsp;
								</td>
								<td><img id="menuImg_feedbacks" src="../imagesNew/Arrow_up.gif"></td>
								<td></td>
							</tr>
							<!-- ---------------------------------------------------------------------------------------------------- -->
							<tr height="24" align="right" id="tr_orders" style="display:none">
								<td onclick="showPage('orders','../html/handleShopOrders2.html')">
									<span id="menuSpan_orders" class="styleMenu">מעקב הזמנות</span>&nbsp;
								</td>
								<td><img id="menuImg_orders" src="../imagesNew/Arrow_up.gif"></td>
								<td></td>
							</tr>
							<!-- ---------------------------------------------------------------------------------------------------- -->
							<tr height="1"><td colspan="3"><img src="../imagesNew/menuSep.gif"></td></tr>
							<!-- ---------------------------------------------------------------------------------------------------- -->
							<tr height="24" align="right" onclick="openCloseMenu('club')">
								<td>
									<span class="styleMenuOn" style='color:#191970'>קשרי לקוחות</span>&nbsp;
								</td>
								<td><img id="menuImg_club" src="../images/openMenu.gif" style="margin-top:4px"></td>
								<td></td>
							</tr>
							<!-- ---------------------------------------------------------------------------------------------------- -->
							<tr height="24" align="right" id="tr_clubConfig" style="display:none">
								<td onclick="showPage('clubConfig','../html/handleClubConfig2.html')">
									<span id="menuSpan_clubConfig" class="styleMenu">הגדרות</span>&nbsp;
								</td>
								<td><img id="menuImg_clubConfig" src="../imagesNew/Arrow_up.gif"></td>
								<td></td>
							</tr>
							<!-- ---------------------------------------------------------------------------------------------------- -->
							<tr height="24" align="right" id="tr_clubMembers" style="display:none">
								<td onclick="showPage('clubMembers','../html/handleClubMembers2.html')">
									<span id="menuSpan_clubMembers" class="styleMenu">גולשים רשומים</span>&nbsp;
								</td>
								<td><img id="menuImg_clubMembers" src="../imagesNew/Arrow_up.gif"></td>
								<td></td>
							</tr>
							<!-- ---------------------------------------------------------------------------------------------------- -->
							<tr height="24" align="right" id="tr_clubMailingLists" style="display:none">
								<td onclick="showPage('clubMailingLists','../html/handleClubMailingLists2.html')">
									<span id="menuSpan_clubMailingLists" class="styleMenu">רשימות תפוצה</span>&nbsp;
								</td>
								<td><img id="menuImg_clubMailingLists" src="../imagesNew/Arrow_up.gif"></td>
								<td></td>
							</tr>
							<!-- ---------------------------------------------------------------------------------------------------- -->
							<tr height="1"><td colspan="3"><img src="../imagesNew/menuSep.gif"></td></tr>
							<!-- ---------------------------------------------------------------------------------------------------- -->
							<? echo $pluginsMenu; ?>
							<!-- ---------------------------------------------------------------------------------------------------- -->
							<tr height="24" align="right">
								<td onclick="showPage('help','../html/help2.html')" align="right">
									<span id="menuSpan_help" class="styleMenu">עזרה</span>&nbsp;
								</td>
								<td><img id="menuImg_help" src="../imagesNew/Arrow_up.gif"></td>
								<td></td>
							</tr>
								
							<tr height="1"><td colspan="3"><img src="../imagesNew/menuSep.gif"></td></tr>
							<tr align="right">
<?

	if ($siteId != "")
		echo "<td onclick=\"location.replace('../indexAdmin.php')\">";
	else
		echo "<td onclick=\"location.replace('../index2.php')\">";
								
?>
									<span class="styleMenu">יציאה</span>&nbsp;
								</td>
								<td><img src="../imagesNew/Arrow_up.gif"></td>
								<td></td>
							</tr>
							<tr height="1"><td colspan="3"><img src="../imagesNew/menuSep.gif"></td></tr>
						</table>
					</td>
			</tr>
			<tr valign="top">
					<td background="../imagesNew/Shadow_L_Bg.gif" width="7" height="12"></td>
					<td background="../imagesNew/Bar_B.gif" height="12"></td>
			</tr>
			<tr valign="top">
				<td background="../imagesNew/Shadow_BL.gif"   height="7"></td>
				<td background="../imagesNew/Shadow_B_Bg.gif" height="7"></td>
				<td background="../imagesNew/Shadow_B_Bg.gif" height="7"></td>
				<td background="../imagesNew/Shadow_BR.gif"   height="7"></td>
			</tr>
			</table>
			</td>
			</tr>
			</table>

	</body>
</html>
