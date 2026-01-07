/* ---------------------------------------------------------------- */
/* 																	*/
/*							serverOptions.js						*/
/*							----------------						*/
/*																	*/
/* ---------------------------------------------------------------- */

/* ---------------------------------------------------------------- */
/* serverOptions_getLinksBoxes										*/
/* ---------------------------------------------------------------- */
function serverOptions_getLinksBoxes (withoutBox, isMenus, afterFunction)
{
	if (withoutBox 	  == undefined) withoutBox	  = -1;
	if (isMenus 	  == undefined) isMenus 	  = false;

	var serverObj 	 = new serverInterfaceObj();

	serverObj.addTag	  ("type", 			"links");
	serverObj.addDummyTag ("withoutBox", 	withoutBox);
	serverObj.addDummyTag ("isMenus", 		isMenus);
	serverObj.addDummyTag ("afterFunction", afterFunction);

   	serverObj.sendRequest("boxes.getBoxes", undefined, "serverOptions_after_getLinksBoxes");
}

function serverOptions_after_getLinksBoxes (i)
{
	var responseXml = asyncResponseXml.getResponseXml (i);

	var selectOptions 	= new selectOptionsObj();

	if (responseXml != null)
	{
		var withoutBox 	= commonGetDummyData (responseXml, "withoutBox");
		var isMenus 	= commonGetDummyData (responseXml, "isMenus");

		var itemsNode = responseXml.getElementsByTagName("items").item(0);

		if (itemsNode == null)
		{
			if (isMenus)
				selectOptions.addOption ("", "אין תפריטים", "No Menus");
			else
				selectOptions.addOption ("", "אין תיבות", "No Boxes");
		} 
		else
		{
			if (!isMenus)
				selectOptions.addOption ("", "", "");

			for (i=0; i < itemsNode.childNodes.length; i++)
			{
				var currNode    = itemsNode.childNodes[i];

				if (currNode.nodeName != "item") continue;

				var id   	= commonGetInnerData (currNode, "id");
				var name   	= commonGetInnerData (currNode, "boxName");

				if (id == withoutBox) continue;

				var optionText = id + " - " + name;

				selectOptions.addOption (id, optionText, optionText);
			}
		}
	}

	var options 	  	= selectOptions.getOptions ();
	var afterFunction 	= commonGetDummyData (responseXml, "afterFunction");

	window[afterFunction](options);
}

/* ---------------------------------------------------------------- */
/* serverOptions_getMenus											*/
/* ---------------------------------------------------------------- */
function serverOptions_getMenus (withoutBox, afterFunction)
{
	if (withoutBox == undefined) 	withoutBox = -1;

	var serverObj 	 = new serverInterfaceObj();

	serverObj.addDummyTag ("withoutBox", 	withoutBox);
	serverObj.addDummyTag ("afterFunction", afterFunction);

   	serverObj.sendRequest("menus.getMenus", undefined, "serverOptions_after_getMenus");
}

function serverOptions_after_getMenus (i)
{
	var responseXml = asyncResponseXml.getResponseXml (i);

	var selectOptions 	= new selectOptionsObj();

	if (responseXml != null)
	{
		var withoutBox = commonGetDummyData (responseXml, "withoutBox");

		var itemsNode = responseXml.getElementsByTagName("items").item(0);

		if (itemsNode == null)
		{
			selectOptions.addOption ("", "אין תפריטים", "No Menus");
		} 
		else
		{
			for (i=0; i < itemsNode.childNodes.length; i++)
			{
				var currNode    = itemsNode.childNodes[i];

				if (currNode.nodeName != "item") continue;

				var id   	= commonGetInnerData (currNode, "id");
				var name   	= commonGetInnerData (currNode, "menuName");

				if (id == withoutBox) continue;

				var optionText = id + " - " + name;

				selectOptions.addOption (id, optionText, optionText);
			}
		}
	}

	var options 		= selectOptions.getOptions ();
	var afterFunction 	= commonGetDummyData (responseXml, "afterFunction");

	window[afterFunction](options);
}

/* ---------------------------------------------------------------- */
/* serverOptions_getSubMenus										*/
/* ---------------------------------------------------------------- */
function serverOptions_getSubMenus (menuId, afterFunction)
{
	var serverObj 	 = new serverInterfaceObj();

	serverObj.addTag 	  ("menuId", 		menuId);
	serverObj.addDummyTag ("afterFunction", afterFunction);

   	serverObj.sendRequest("menus.getSubMenus", undefined, "serverOptions_after_getSubMenus");
}

function serverOptions_after_getSubMenus (i)
{
	var responseXml = asyncResponseXml.getResponseXml (i);

	var selectOptions 	= new selectOptionsObj();
	
	if (responseXml != null)
	{
		var itemsNode = responseXml.getElementsByTagName("items").item(0);

		if (itemsNode == null)
		{
			selectOptions.addOption ("", "אין תת תפריטים", "No Sub-Menus");
		} 
		else
		{
			selectOptions.addOption ("", "", "");

			for (i=0; i < itemsNode.childNodes.length; i++)
			{
				var currNode    = itemsNode.childNodes[i];

				if (currNode.nodeName != "item") continue;

				var id  	= commonGetInnerData (currNode, "id");
				var name 	= commonGetInnerData (currNode, "name");
				var level 	= commonGetInnerData (currNode, "level");

				var optionText = name; //id + " - " + name;

				var style = "";
				if (level != 1)
					style   = "color:#808080";

				selectOptions.addOption (id, optionText, optionText, style);
			}
		}
	}

	var options 		= selectOptions.getOptions ();
	var afterFunction 	= commonGetDummyData (responseXml, "afterFunction");

	window[afterFunction](options);
}

/* ---------------------------------------------------------------- */
/* serverOptions_getPages											*/
/* ---------------------------------------------------------------- */
function serverOptions_getPages (emptyType, afterFunction)
{
	if (emptyType 	  == undefined) emptyType = "choose";

	var serverObj 	 = new serverInterfaceObj();
	serverObj.addDummyTag ("emptyType", 	emptyType);
	serverObj.addDummyTag ("afterFunction", afterFunction);

    serverObj.sendRequest("pages.getPages", undefined, "serverOptions_after_getPages");
}

/* ---------------------------------------------------------------- */
/* serverOptions_getHtmlPages										*/
/* ---------------------------------------------------------------- */
function serverOptions_getHtmlPages (emptyType, afterFunction)
{
	if (emptyType 	  == undefined) emptyType = "choose";

	var serverObj 	 = new serverInterfaceObj();
	serverObj.addTag 	  ("pageType", 		"html");
	serverObj.addDummyTag ("emptyType", 	emptyType);
	serverObj.addDummyTag ("afterFunction", afterFunction);

    serverObj.sendRequest("pages.getPagesNames", undefined, "serverOptions_after_getPages");
}

/* ---------------------------------------------------------------- */
/* serverOptions_getMembersPages									*/
/* ---------------------------------------------------------------- */
function serverOptions_getMembersPages (emptyType, afterFunction)
{
	if (emptyType 	  == undefined) emptyType = "choose";

	var serverObj 	 = new serverInterfaceObj();
	serverObj.addDummyTag ("emptyType", 	emptyType);
	serverObj.addDummyTag ("afterFunction", afterFunction);

    serverObj.sendRequest("pages.getMembersPages", undefined, "serverOptions_after_getPages");
}

/* ---------------------------------------------------------------- */
/* serverOptions_getAllPages										*/
/* ---------------------------------------------------------------- */
function serverOptions_getAllPages (emptyType, afterFunction)
{
	if (emptyType == undefined) emptyType = "empty";

	var serverObj 	 = new serverInterfaceObj();
	serverObj.addDummyTag ("emptyType", 	emptyType);
	serverObj.addDummyTag ("afterFunction", afterFunction);

    serverObj.sendRequest("pages.getAllPages", undefined, "serverOptions_after_getPages");
}

/* ---------------------------------------------------------------- */
/* serverOptions_getPagesNames										*/
/* ---------------------------------------------------------------- */
function serverOptions_getPagesNames (emptyType, afterFunction)
{
	if (emptyType == undefined) emptyType = "empty";
	
	var serverObj 	 = new serverInterfaceObj();
	serverObj.addDummyTag ("emptyType", 	emptyType);
	serverObj.addDummyTag ("afterFunction", afterFunction);

    serverObj.sendRequest("pages.getPagesNames", undefined, "serverOptions_after_getPages");
}

/* ---------------------------------------------------------------- */
/* serverOptions_after_getPages										*/
/* ---------------------------------------------------------------- */
function serverOptions_after_getPages (i)
{
	var responseXml = asyncResponseXml.getResponseXml (i);

	var selectOptions 	= new selectOptionsObj();

	if (responseXml != null)
	{
		var emptyType = commonGetDummyData (responseXml, "emptyType");

		var itemsNode = responseXml.getElementsByTagName("items").item(0);

		if (itemsNode == null)
		{
			selectOptions.addOption ("", "אין דפים מוגדרים", "No Pages");
		} 
		else
		{
			if (emptyType == "empty")
				selectOptions.addOption ("", "", "");
			else if (emptyType == "all")
				selectOptions.addOption ("", "כל הדפים", "");
			else if (emptyType != "multi")
				selectOptions.addOption ("", "<בחירת דף>", "");

			for (i=0; i < itemsNode.childNodes.length; i++)
			{
				var currNode    = itemsNode.childNodes[i];

				if (currNode.nodeName != "item") continue;

				var id  	 = commonGetInnerData (currNode, "pageId");
				var title 	 = commonGetInnerData (currNode, "title");
				var winTitle = commonGetInnerData (currNode, "winTitle");

				if (title == "") title = winTitle;

				var optionText = id + " - " + title;

				selectOptions.addOption (id, optionText, optionText);
			}
		}
	}

	var options 		= selectOptions.getOptions ();
	var afterFunction 	= commonGetDummyData (responseXml, "afterFunction");

	window[afterFunction](options);
}

/* ---------------------------------------------------------------- */
/* serverOptions_getRewriteNames									*/
/* ---------------------------------------------------------------- */
function serverOptions_getRewriteNames (emptyType, afterFunction)
{
	if (emptyType == undefined) emptyType = "choose";

	var serverObj 	 = new serverInterfaceObj();

	serverObj.addDummyTag ("emptyType", 	emptyType);
	serverObj.addDummyTag ("afterFunction", afterFunction);

    serverObj.sendRequest("pages.getRewriteNames", undefined, "serverOptions_after_getRewriteNames");
}

function serverOptions_after_getRewriteNames (i)
{
	var responseXml = asyncResponseXml.getResponseXml (i);

	var selectOptions 	= new selectOptionsObj();

	if (responseXml != null)
	{
		var emptyType = commonGetDummyData (responseXml, "emptyType");

		var itemsNode = responseXml.getElementsByTagName("items").item(0);

		if (itemsNode == null)
		{
			selectOptions.addOption ("", "אין דפים עם כתובות סטטיות", "No Pages");
		} 
		else
		{
			if (emptyType == "empty")
				selectOptions.addOption ("", "", "");
			else if (emptyType == "all")
				selectOptions.addOption ("", "כל הדפים", "");
			else if (emptyType != "multi")
				selectOptions.addOption ("", "<בחירת דף>", "");

			for (i=0; i < itemsNode.childNodes.length; i++)
			{
				var currNode    = itemsNode.childNodes[i];

				if (currNode.nodeName != "item") continue;

				var id  	 = commonGetInnerData (currNode, "pageId");
				var name 	 = commonGetInnerData (currNode ,"rewriteName");

				var optionText = id + " - " + name;

				selectOptions.addOption (id, optionText, optionText);
			}
		}
	}

	var options 		= selectOptions.getOptions ();
	var afterFunction 	= commonGetDummyData (responseXml, "afterFunction");

	window[afterFunction](options);
}

/* ---------------------------------------------------------------- */
/* serverOptions_getForums											*/
/* ---------------------------------------------------------------- */
function serverOptions_getForums (emptyType, ibosUserId, afterFunction)
{
	if (ibosUserId 	  == undefined) ibosUserId 	  = "";

	var serverObj 	 = new serverInterfaceObj();

	serverObj.addTag 	  ("ibosUserId", 	ibosUserId);
	serverObj.addDummyTag ("emptyType", 	emptyType);
	serverObj.addDummyTag ("afterFunction", afterFunction);

    serverObj.sendRequest("forums.getForums", undefined, "serverOptions_after_getForums");
}

function serverOptions_after_getForums (i)
{
	var responseXml = asyncResponseXml.getResponseXml (i);

	var selectOptions 	= new selectOptionsObj();

	if (responseXml != null)
	{
		var emptyType = commonGetDummyData (responseXml, "emptyType");

		var itemsNode = responseXml.getElementsByTagName("items").item(0);

		if (itemsNode == null)
		{
			selectOptions.addOption ("", "אין פורומים מוגדרים", "No Forums");
		} 
		else
		{
			if (emptyType == "choose")
				selectOptions.addOption ("", "<בחירת פורום>" ,"");

			for (i=0; i < itemsNode.childNodes.length; i++)
			{
				var currNode    = itemsNode.childNodes[i];

				if (currNode.nodeName != "item") continue;

				var id  	 = commonGetInnerData (currNode, "id");
				var name 	 = commonGetInnerData (currNode ,"name");

				var optionText = id + " - " + name;

				selectOptions.addOption (id, optionText, optionText);
			}
		}
	}

	var options 		= selectOptions.getOptions ();
	var afterFunction 	= commonGetDummyData (responseXml, "afterFunction");

	window[afterFunction](options);
}

/* ---------------------------------------------------------------- */
/* serverOptions_getDiscussions										*/
/* ---------------------------------------------------------------- */
function serverOptions_getDiscussions (emptyType, afterFunction)
{
	var serverObj 	 = new serverInterfaceObj();

	serverObj.addDummyTag ("emptyType", 	emptyType);
	serverObj.addDummyTag ("afterFunction", afterFunction);

    serverObj.sendRequest("discussions.getDiscussions", undefined, "serverOptions_after_getDiscussions");
}

function serverOptions_after_getDiscussions (i)
{
	var responseXml = asyncResponseXml.getResponseXml (i);

	var selectOptions 	= new selectOptionsObj();

	if (responseXml != null)
	{
		var emptyType = commonGetDummyData (responseXml, "emptyType");

		var itemsNode = responseXml.getElementsByTagName("items").item(0);

		if (itemsNode == null)
		{
			selectOptions.addOption ("", "אין דיונים מוגדרים", "No Discussions");
		} 
		else
		{
			if (emptyType == "choose")
				selectOptions.addOption ("", "<בחירת דיון>" ,"");

			for (i=0; i < itemsNode.childNodes.length; i++)
			{
				var currNode    = itemsNode.childNodes[i];

				if (currNode.nodeName != "item") continue;

				var id  	 = commonGetInnerData (currNode, "id");
				var title 	 = commonGetInnerData (currNode ,"title");

				var optionText = id + " - " + title;

				selectOptions.addOption (id, optionText, optionText);
			}
		}
	}

	var options 		= selectOptions.getOptions ();
	var afterFunction 	= commonGetDummyData (responseXml, "afterFunction");

	window[afterFunction](options);
}

/* ---------------------------------------------------------------- */
/* serverOptions_getEnums											*/
/* ---------------------------------------------------------------- */
function serverOptions_getEnums (emptyType, withoutId, afterFunction)
{
	if (withoutId == undefined) withoutId = 0;

	var serverObj 	 = new serverInterfaceObj();

	serverObj.addTag 	  ("withoutId", 	withoutId);
	serverObj.addDummyTag ("emptyType", 	emptyType);
	serverObj.addDummyTag ("afterFunction", afterFunction);

    serverObj.sendRequest("enums.getEnums", undefined, "serverOptions_after_getEnums");
}

function serverOptions_after_getEnums (i)
{
	var responseXml = asyncResponseXml.getResponseXml (i);

	var selectOptions 	= new selectOptionsObj();

	if (responseXml != null)
	{
		var emptyType = commonGetDummyData (responseXml, "emptyType");

		var itemsNode = responseXml.getElementsByTagName("items").item(0);

		if (itemsNode == null)
		{
			selectOptions.addOption ("", "אין רשימות מוגדרות", "No Enums");
		} 
		else
		{
			if (emptyType == "choose")
				selectOptions.addOption ("", "<בחירת רשימה>" ,"");

			if (emptyType == "empty")
				selectOptions.addOption ("", "" ,"");

			if (emptyType == "parentOf")
				selectOptions.addOption ("0", "רשימת אב" ,"");

			if (emptyType == "nadlan")
			{
				selectOptions.addOption ("-1", "לא מוצג" ,"");
				selectOptions.addOption ("0",  "שדה טקסט" ,"");
			}

			for (i=0; i < itemsNode.childNodes.length; i++)
			{
				var currNode    = itemsNode.childNodes[i];

				if (currNode.nodeName != "item") continue;

				var id  	 = commonGetInnerData (currNode, "enumId");
				var name 	 = commonGetInnerData (currNode ,"name");

				if (emptyType == "nadlan")
					var optionText = "רשימה - " + name;
				else
					var optionText = id + " - " + name;

				selectOptions.addOption (id, optionText, optionText);
			}
		}
	}

	var options 		= selectOptions.getOptions ();
	var afterFunction 	= commonGetDummyData (responseXml, "afterFunction");

	window[afterFunction](options);
}

/* ---------------------------------------------------------------- */
/* serverOptions_getEnumValues										*/
/* ---------------------------------------------------------------- */
function serverOptions_getEnumValues (enumId, withEmpty, showId, orderBy, afterFunction)
{
	if (withEmpty 	== undefined) withEmpty = true;
	if (showId 		== undefined) showId 	= true;
	if (orderBy 	== undefined) orderBy 	= "";

	// send request to server
	var serverObj 	 = new serverInterfaceObj();

	serverObj.addTag 	  ("enumId",  		enumId);
	serverObj.addTag 	  ("orderBy", 		orderBy);
	serverObj.addDummyTag ("withEmpty", 	withEmpty);
	serverObj.addDummyTag ("showId", 		showId);
	serverObj.addDummyTag ("afterFunction", afterFunction);

    serverObj.sendRequest("enums.getValues", undefined, "serverOptions_after_getValues");
}

var serverOptions_showEnumValue 	  = false;
var serverOptions_showEnumValueParent = true;

/* ---------------------------------------------------------------- */
/* serverOptions_getEnumValuesByParent								*/
/* ---------------------------------------------------------------- */
function serverOptions_getEnumValuesByParent (parentId, withEmpty, showId, afterFunction)
{
	if (withEmpty == undefined) withEmpty = true;
	if (showId 	  == undefined) showId 	  = true;

	
	var serverObj 	 = new serverInterfaceObj();

	serverObj.addTag 	  ("parentId", 		parentId);
	serverObj.addTag 	  ("showParent", 	serverOptions_showEnumValueParent);
	serverObj.addDummyTag ("withEmpty", 	withEmpty);
	serverObj.addDummyTag ("showId", 		showId);
	serverObj.addDummyTag ("afterFunction", afterFunction);

    serverObj.sendRequest("enums.getValues", undefined, "serverOptions_after_getValues");
}


function serverOptions_after_getValues (i)
{
	var responseXml = asyncResponseXml.getResponseXml (i);

	var selectOptions 	= new selectOptionsObj();

	if (responseXml != null)
	{
		var withEmpty = commonGetDummyData (responseXml, "withEmpty");
		var showId 	  = commonGetDummyData (responseXml, "showId");

		var itemsNode = responseXml.getElementsByTagName("items").item(0);

		if (itemsNode == null)
		{
			selectOptions.addOption ("", "אין ערכים", "No Values");
		} 
		else
		{
			if (withEmpty)
				selectOptions.addOption ("", "" ,"");

			for (i=0; i < itemsNode.childNodes.length; i++)
			{
				var currNode    = itemsNode.childNodes[i];

				if (currNode.nodeName != "item") continue;

				var id  	 = commonGetInnerData (currNode, "valueId");
				var name 	 = commonGetInnerData (currNode ,"text");

				if (serverOptions_showEnumValue)
					var name 	 = commonGetInnerData (currNode ,"value");

				if (showId)
					var optionText = id + " - " + name;
				else
					var optionText = name;

				selectOptions.addOption (id, optionText, optionText);
			}
		}
	}

	var options 		= selectOptions.getOptions ();
	var afterFunction 	= commonGetDummyData (responseXml, "afterFunction");

	window[afterFunction](options);
}

/* ---------------------------------------------------------------- */
/* serverOptions_getEssays											*/
/* ---------------------------------------------------------------- */
function serverOptions_getEssays (emptyType, limit, afterFunction)
{
	var serverObj 	 = new serverInterfaceObj();

	if (limit != undefined)
		serverObj.addTag ("limit", limit);

	serverObj.addDummyTag ("emptyType", 	emptyType);
	serverObj.addDummyTag ("afterFunction", afterFunction);

	serverObj.sendRequest("essays.getEssays", undefined, "serverOptions_after_getEssays");
}

function serverOptions_after_getEssays (i)
{
	var responseXml = asyncResponseXml.getResponseXml (i);

	var selectOptions 	= new selectOptionsObj();

	if (responseXml != null)
	{
		var emptyType = commonGetDummyData (responseXml, "emptyType");

		var itemsNode = responseXml.getElementsByTagName("items").item(0);

		if (itemsNode == null)
		{
			selectOptions.addOption ("", "אין כתבות מוגדרות", "No Essays");
		} 
		else
		{
			if (emptyType == "choose")
				selectOptions.addOption ("", "<בחירת כתבה>" ,"");

			if (emptyType == "empty")
				selectOptions.addOption ("", "" ,"");

			for (i=0; i < itemsNode.childNodes.length; i++)
			{
				var currNode    = itemsNode.childNodes[i];

				if (currNode.nodeName != "item") continue;

				var id  	 = commonGetInnerData (currNode, "essayId");
				var name 	 = commonGetInnerData (currNode ,"title");

				var optionText = id + " - " + name;

				selectOptions.addOption (id, optionText, optionText);
			}
		}
	}

	var options 		= selectOptions.getOptions ();
	var afterFunction 	= commonGetDummyData (responseXml, "afterFunction");

	window[afterFunction](options);
}

/* ---------------------------------------------------------------- */
/* serverOptions_getGalleries										*/
/* ---------------------------------------------------------------- */
function serverOptions_getGalleries (emptyType, afterFunction)
{
	var serverObj 	 = new serverInterfaceObj();

	serverObj.addDummyTag ("emptyType", 	emptyType);
	serverObj.addDummyTag ("afterFunction", afterFunction);

	serverObj.sendRequest("galleries.getGalleries", undefined, "serverOptions_after_getGalleries");
}

function serverOptions_after_getGalleries (i)
{
	var responseXml = asyncResponseXml.getResponseXml (i);

	var selectOptions 	= new selectOptionsObj();

	if (responseXml != null)
	{
		var emptyType = commonGetDummyData (responseXml, "emptyType");

		var itemsNode = responseXml.getElementsByTagName("items").item(0);

		if (itemsNode == null)
		{
			selectOptions.addOption ("", "אין גלריות מוגדרות", "No Galleries");
		} 
		else
		{
			if (emptyType == "choose")
				selectOptions.addOption ("0", "<בחירת גלריה>" ,"");

			for (i=0; i < itemsNode.childNodes.length; i++)
			{
				var currNode    = itemsNode.childNodes[i];

				if (currNode.nodeName != "item") continue;

				var id  	 = commonGetInnerData (currNode, "galleryId");
				var title 	 = commonGetInnerData (currNode ,"title");

				var optionText = id + " - " + title;

				selectOptions.addOption (id, optionText, optionText);
			}
		}
	}

	var options 		= selectOptions.getOptions ();
	var afterFunction 	= commonGetDummyData (responseXml, "afterFunction");

	window[afterFunction](options);
}

/* ---------------------------------------------------------------- */
/* serverOptions_getCategories										*/
/* ---------------------------------------------------------------- */
function serverOptions_getCategories (emptyType, type, serverName, withGlobal, afterFunction)
{
	if (serverName == undefined || serverName == "") serverName = "categories";

	var serverObj 	 = new serverInterfaceObj();

	serverObj.addTag 	  ("type", 			type);
	serverObj.addDummyTag ("emptyType", 	emptyType);
	serverObj.addDummyTag ("afterFunction", afterFunction);

	if (withGlobal != undefined) 
		serverObj.addTag ("withGlobal", withGlobal);

   	serverObj.sendRequest(serverName + ".getCategories", undefined, "serverOptions_after_getCategories");
}

function serverOptions_after_getCategories (i)
{
	var responseXml = asyncResponseXml.getResponseXml (i);

	var selectOptions 	= new selectOptionsObj();

	if (responseXml != null)
	{
		var emptyType = commonGetDummyData (responseXml, "emptyType");

		var itemsNode = responseXml.getElementsByTagName("items").item(0);

		if (itemsNode == null)
		{
			selectOptions.addOption ("", "אין קטגוריות מוגדרות", "No Galleries");
		} 
		else
		{
			if (emptyType == "choose")
				selectOptions.addOption ("", "" ,"");

			for (i=0; i < itemsNode.childNodes.length; i++)
			{
				var currNode    = itemsNode.childNodes[i];

				if (currNode.nodeName != "item") continue;

				var id  	 	= commonGetInnerData (currNode, "id");
				var name 	 	= commonGetInnerData (currNode ,"selectName");

				var parentId  	 = commonGetInnerData (currNode, "parentId");
				var countItems 	 = commonGetInnerData (currNode ,"countItems");

				var style = "";
				if (parentId != 0)
					style   = "color:#808080";

				selectOptions.addOption (id, name, name, style);
			}
		}
	}

	var options 		= selectOptions.getOptions ();
	var afterFunction 	= commonGetDummyData (responseXml, "afterFunction");

	window[afterFunction](options);
}

/* ---------------------------------------------------------------- */
/* serverOptions_getShopCategories									*/
/* ---------------------------------------------------------------- */
function serverOptions_getShopCategories (emptyType, afterFunction)
{
	serverOptions_getCategories (emptyType, "shop", "", undefined, afterFunction);
}

/* ---------------------------------------------------------------- */
/* serverOptions_getEssayCategories									*/
/* ---------------------------------------------------------------- */
function serverOptions_getEssayCategories (emptyType, afterFunction)
{
	serverOptions_getCategories (emptyType, "essay", "", undefined, afterFunction);
}

/* ---------------------------------------------------------------- */
/* serverOptions_getShopProducts									*/
/* ---------------------------------------------------------------- */
function serverOptions_getShopProducts (emptyType, afterFunction)
{
	var serverObj 	 = new serverInterfaceObj();

	serverObj.addDummyTag ("emptyType", 	emptyType);
	serverObj.addDummyTag ("afterFunction", afterFunction);

    serverObj.sendRequest("shop.getProducts", undefined, "serverOptions_after_getShopProducts");
}

function serverOptions_after_getShopProducts (i)
{
	var responseXml = asyncResponseXml.getResponseXml (i);

	var selectOptions 	= new selectOptionsObj();

	if (responseXml != null)
	{
		var emptyType = commonGetDummyData (responseXml, "emptyType");

		var itemsNode = responseXml.getElementsByTagName("items").item(0);

		if (itemsNode == null)
		{
			selectOptions.addOption ("", "אין מוצרים מוגדרים", "No Products");
		} 
		else
		{
			if (emptyType == "choose")
				selectOptions.addOption ("", "<בחירת מוצר>" ,"");

			if (emptyType == "empty")
				selectOptions.addOption ("", "" ,"");

			for (i=0; i < itemsNode.childNodes.length; i++)
			{
				var currNode    = itemsNode.childNodes[i];

				if (currNode.nodeName != "item") continue;

				var id  	 	= commonGetInnerData (currNode, "productId");
				var name 	 	= commonGetInnerData (currNode ,"productName");

				selectOptions.addOption (id, name, name);
			}
		}
	}

	var options 		= selectOptions.getOptions ();
	var afterFunction 	= commonGetDummyData (responseXml, "afterFunction");

	window[afterFunction](options);
}

/* ---------------------------------------------------------------- */
/* serverOptions_getShipments										*/
/* ---------------------------------------------------------------- */
function serverOptions_getShipments (emptyType, afterFunction)
{
	var serverObj 	 = new serverInterfaceObj();

	serverObj.addDummyTag ("emptyType", 	emptyType);
	serverObj.addDummyTag ("afterFunction", afterFunction);

    serverObj.sendRequest("shop.getShipments", undefined, "serverOptions_after_getShipments");
}

function serverOptions_after_getShipments (i)
{
	var responseXml = asyncResponseXml.getResponseXml (i);

	var selectOptions 	= new selectOptionsObj();

	if (responseXml != null)
	{
		var emptyType = commonGetDummyData (responseXml, "emptyType");

		var itemsNode = responseXml.getElementsByTagName("items").item(0);

		if (itemsNode == null)
		{
			selectOptions.addOption ("", "אין משלוחים מוגדרים", "No Shipments");
		} 
		else
		{
			if (emptyType == "choose")
				selectOptions.addOption ("", "<בחירת משלוח>" ,"");

			for (i=0; i < itemsNode.childNodes.length; i++)
			{
				var currNode    = itemsNode.childNodes[i];

				if (currNode.nodeName != "item") continue;

				var id  	 	= commonGetInnerData (currNode, "id");
				var name 	 	= commonGetInnerData (currNode ,"name");

				selectOptions.addOption (id, name, name);
			}
		}
	}

	var options 		= selectOptions.getOptions ();
	var afterFunction 	= commonGetDummyData (responseXml, "afterFunction");

	window[afterFunction](options);
}

/* ---------------------------------------------------------------- */
/* serverOptions_getMusicFileNames									*/
/* ---------------------------------------------------------------- */
function serverOptions_getMusicFileNames (afterFunction)
{
	if (commonGetGlobalData("musicFilesXml") == undefined)
	{
		var serverObj 	 = new serverInterfaceObj();

		serverObj.addDummyTag ("afterFunction", afterFunction);

	    serverObj.sendRequest("pages.getMusicFiles", undefined, "serverOptions_saveMusicFilesXml");
	}
	else
	{
		return serverOptions_loadMusicFiles (afterFunction);
	}
}

function serverOptions_saveMusicFilesXml (i)
{
	commonSetGlobalData ("musicFilesXml", asyncResponseXml.getResponseXml (i));

	serverOptions_loadMusicFiles ();
}

function serverOptions_loadMusicFiles (afterFunction)
{
	var selectOptions 	= new selectOptionsObj();
	
	var xml = commonGetGlobalData("musicFilesXml");

	if (xml != null)
	{
		var itemsNode = xml.getElementsByTagName("items").item(0);

		if (itemsNode == null)
		{
			selectOptions.addOption ("", "אין קבצי מוזיקה טעונים",  "No music files");
		} 
		else
		{
			selectOptions.addOption ("", "", "");
		
			for (i=0; i < itemsNode.childNodes.length; i++)
			{
				var currNode    = itemsNode.childNodes[i];

				if (currNode.nodeName != "item") continue;

				var name  	 	= commonGetInnerData (currNode, "fileName");

				selectOptions.addOption (name, name, name);
			}
		}
	}

	var options 		= selectOptions.getOptions ();

	if (afterFunction == undefined)
		afterFunction 	= commonGetDummyData (xml, "afterFunction");

	window[afterFunction](options);
}

/* ---------------------------------------------------------------- */
/* serverOptions_getFiles											*/
/* ---------------------------------------------------------------- */
function serverOptions_getFiles (afterFunction)
{
	var serverObj 	 = new serverInterfaceObj();

	serverObj.addDummyTag ("afterFunction", afterFunction);

    serverObj.sendRequest("files.getFiles", undefined, "serverOptions_after_getFiles");
}

function serverOptions_after_getFiles (i)
{
	var responseXml = asyncResponseXml.getResponseXml (i);

	var selectOptions 	= new selectOptionsObj();

	if (responseXml != null)
	{
		var itemsNode = responseXml.getElementsByTagName("items").item(0);

		if (itemsNode == null)
		{
			selectOptions.addOption ("", "אין קבצים",  "No files");
		} 
		else
		{
			selectOptions.addOption ("", "<בחירת קובץ>", "");
		
			for (i=0; i < itemsNode.childNodes.length; i++)
			{
				var currNode    = itemsNode.childNodes[i];

				if (currNode.nodeName != "item") continue;

				var name  	 	= commonGetInnerData (currNode, "fileName");

				selectOptions.addOption (name, name, name);
			}
		}
	}

	var options 		= selectOptions.getOptions ();
	var afterFunction 	= commonGetDummyData (responseXml, "afterFunction");

	window[afterFunction](options);
}

/* ---------------------------------------------------------------- */
/* serverOptions_selectCategoryForItem								*/
/* ---------------------------------------------------------------- */
function serverOptions_selectCategoryForItem (itemId, type, theLangs, afterFunction)
{
	var serverObj 	 = new serverInterfaceObj();

	serverObj.addTag	   ("theLangs", 	theLangs);
	serverObj.addTag	   ("itemId", 		itemId);
	serverObj.addTag	   ("type",   		type);
	serverObj.addTag	   ("selected", 	"0")
	serverObj.addDummyTag  ("afterFunction", afterFunction);

    serverObj.sendRequest("categories.getCategoriesOfItem", undefined, "serverOptions_after_getCategoriesOfItem");
}

function serverOptions_after_getCategoriesOfItem (i)
{
	var responseXml = asyncResponseXml.getResponseXml (i);

	var selectOptions 	= new selectOptionsObj();

	if (responseXml != null)
	{
		var itemsNode = responseXml.getElementsByTagName("items").item(0);

		if (itemsNode == null)
		{
			selectOptions.addOption ("", "אין קטגוריות נוספות", "No Catrgories");
		} 
		else
		{
			selectOptions.addOption ("", "", "");

			for (i=0; i < itemsNode.childNodes.length; i++)
			{
				var currNode    = itemsNode.childNodes[i];

				if (currNode.nodeName != "item") continue;

				var id  	 	= commonGetInnerData (currNode, "id");
				var name  	 	= commonGetInnerData (currNode, "name");

				selectOptions.addOption (id, name, name);
			}
		}
	}

	var options 		= selectOptions.getOptions ();
	var afterFunction 	= commonGetDummyData (responseXml, "afterFunction");

	window[afterFunction](options);
}

/* ---------------------------------------------------------------- */
/* serverOptions_getLayouts											*/
/* ---------------------------------------------------------------- */
function serverOptions_getLayouts (afterFunction)
{
	var xml = commonGetGlobalData("layoutsXml");

	if (xml == undefined)
	{
		var serverObj = new serverInterfaceObj();

		serverObj.addDummyTag ("afterFunction", afterFunction);

	    serverObj.sendRequest("layouts.getLayouts", undefined, "serverOptions_saveLayoutsXml");
	}
	else
	{
		return serverOptions_loadLayouts (xml, afterFunction);
	}
}

function serverOptions_saveLayoutsXml (i)
{
	var xml = asyncResponseXml.getResponseXml (i);

	commonSetGlobalData ("layoutsXml", xml);

	serverOptions_loadLayouts (xml);
}

function serverOptions_loadLayouts (xml, afterFunction)
{
	var selectOptions 	= new selectOptionsObj();
	
	if (xml != null)
	{
		var itemsNode = xml.getElementsByTagName("items").item(0);

		if (itemsNode == null)
		{
			selectOptions.addOption ("", "אין דפי עיצוב מוגדרים", "No Layouts");
		} 
		else
		{
			selectOptions.addOption ("", "<בחירת תבנית עיצוב>", "");
			for (i=0; i < itemsNode.childNodes.length; i++)
			{
				var currNode    = itemsNode.childNodes[i];

				if (currNode.nodeName != "item") continue;

				var id  	 	= commonGetInnerData (currNode, "layoutId");
				var name  	 	= commonGetInnerData (currNode, "name");

				var optionText = id + " - " + name;

				selectOptions.addOption (id, optionText, optionText);
			}
		}
	}

	var options		 	= selectOptions.getOptions ();

	if (afterFunction == undefined)
		afterFunction 	= commonGetDummyData (xml, "afterFunction");

	window[afterFunction](options);
}

/* ---------------------------------------------------------------- */
/* serverOptions_getLayoutsByType									*/
/* ---------------------------------------------------------------- */
function serverOptions_getLayoutsByType (type, afterFunction)
{
	switch (type)
	{
		case "page"			:	xml = commonGetGlobalData("pageLayoutsXml");		break;
		case "newsletter"	:	xml = commonGetGlobalData("newsletterLayoutsXml");	break;
		case "essay"		: 	xml = commonGetGlobalData("essayLayoutsXml");		break;
	}

	if (xml == undefined)
	{
		var serverObj = new serverInterfaceObj();

		serverObj.addTag 	  ("type", 			type);
		serverObj.addDummyTag ("layoutType", 	type);
		serverObj.addDummyTag ("afterFunction", afterFunction);

		serverObj.sendRequest("layouts.getLayouts", undefined, "serverOptions_after_getLayoutsByType");
	}
	else
	{
		serverOptions_loadLayouts (xml, afterFunction);
	}
}

function serverOptions_after_getLayoutsByType (i)
{
	var responseXml = asyncResponseXml.getResponseXml (i);

	var layoutType = commonGetDummyData (responseXml, "layoutType");

	switch (layoutType)
	{
		case "page"			:	commonSetGlobalData ("pageLayoutsXml",		 responseXml);		break;
		case "newsletter"	:	commonSetGlobalData ("newsletterLayoutsXml", responseXml);		break;
		case "essay"		:	commonSetGlobalData ("essayLayoutsXml",		 responseXml);		break;
	}

	serverOptions_loadLayouts (responseXml);
}

/* ---------------------------------------------------------------- */
/* serverOptions_getAreas											*/
/* ---------------------------------------------------------------- */
function serverOptions_getAreas (forWhat, serverName, afterFunction)
{
	if (serverName == undefined) serverName = "areas";

	var serverObj 	 = new serverInterfaceObj();

	serverObj.addTag	  ("sortBy",		"superArea");
	serverObj.addTag	  ("sortDir",		"asc");

	serverObj.addDummyTag ("forWhat", 		forWhat);
	serverObj.addDummyTag ("afterFunction", afterFunction);

    serverObj.sendRequest(serverName + ".getAreas", undefined, "serverOptions_after_getAreas");
}

/* ---------------------------------------------------------------- */
/* serverOptions_getAreasOfCountry									*/
/* ---------------------------------------------------------------- */
function serverOptions_getAreasOfCountry (countryId, forWhat, serverName, afterFunction)
{
	if (serverName == undefined) serverName = "areas";

	var serverObj 	 = new serverInterfaceObj();

	serverObj.addTag	  ("countryId",		countryId);

	serverObj.addTag	  ("sortBy",		"superArea");
	serverObj.addTag	  ("sortDir",		"asc");

	serverObj.addDummyTag ("forWhat", 		forWhat);
	serverObj.addDummyTag ("afterFunction", afterFunction);

    serverObj.sendRequest(serverName + ".getAreas", undefined, "serverOptions_after_getAreas");
}

function serverOptions_after_getAreas (i)
{
	var responseXml = asyncResponseXml.getResponseXml (i);

	var selectOptions 	= new selectOptionsObj();

	if (responseXml != null)
	{
		var forWhat = commonGetDummyData (responseXml, "forWhat");

		var itemsNode = responseXml.getElementsByTagName("items").item(0);

		if (itemsNode == null)
		{
			selectOptions.addOption ("", "אין אזורים", "No Areas");
		} 
		else
		{
			if (forWhat != "noAll" && forWhat != "noEmpty")
			{
				selectOptions.addOption ("", "", "");

				selectOptions.addOption ("-1", "כל האזורים", "");
			}

			if (forWhat != "editForm" && forWhat != "noEmpty")
			{
				selectOptions.addOption ("", "", "");
			}

			for (i=0; i < itemsNode.childNodes.length; i++)
			{
				var currNode    = itemsNode.childNodes[i];

				if (currNode.nodeName != "item") continue;

				var id  	 	= commonGetInnerData (currNode, "id");
				var name  	 	= commonGetInnerData (currNode, "name");
				var superArea 	= commonGetInnerData (currNode, "superArea");

				if (superArea != "" && superArea != name)
					name = superArea + " - " + name;

				selectOptions.addOption (id, name, name);
			}
		}
	}

	var options 		= selectOptions.getOptions ();
	var afterFunction 	= commonGetDummyData (responseXml, "afterFunction");

	window[afterFunction](options);
}

/* ---------------------------------------------------------------- */
/* serverOptions_getCountries										*/
/* ---------------------------------------------------------------- */
function serverOptions_getCountries (forWhat, withoutId, afterFunction)
{
	if (withoutId == undefined) withoutId = 0;

	var serverObj 	 = new serverInterfaceObj();

	serverObj.addTag	  ("sortBy",		"name");
	serverObj.addTag	  ("sortDir",		"asc");

	serverObj.addTag 	  ("withoutId", 	withoutId);

	serverObj.addDummyTag ("forWhat", 		forWhat);
	serverObj.addDummyTag ("afterFunction", afterFunction);

	if (forWhat == "parent")
		serverObj.addTag ("parentId", "0");

    serverObj.sendRequest("countries.getCountries", undefined, "serverOptions_after_getCountries");
}

function serverOptions_after_getCountries (i)
{
	var responseXml = asyncResponseXml.getResponseXml (i);

	var selectOptions 	= new selectOptionsObj();
	
	if (responseXml != null)
	{
		var forWhat = commonGetDummyData (responseXml, "forWhat");

		var itemsNode = responseXml.getElementsByTagName("items").item(0);

		if (itemsNode == null)
		{
			selectOptions.addOption ("", "אין יבשות", "No Catrgories");
		} 
		else
		{
			if (forWhat != "noAll" && forWhat != "parent")
			{
				selectOptions.addOption ("", "", "");

				selectOptions.addOption ("-1", "כל המדינות", "");
			}

			if (forWhat != "editForm")
			{
				selectOptions.addOption ("", "", "");
			}

			for (i=0; i < itemsNode.childNodes.length; i++)
			{
				var currNode    = itemsNode.childNodes[i];

				if (currNode.nodeName != "item") continue;

				var id  	 	= commonGetInnerData (currNode, "id");
				var name  	 	= commonGetInnerData (currNode, "name");

				selectOptions.addOption (id, name, name);
			}
		}
	}

	var options 		= selectOptions.getOptions ();
	var afterFunction 	= commonGetDummyData (responseXml, "afterFunction");

	window[afterFunction](options);
}

/* ---------------------------------------------------------------- */
/* serverOptions_getCities											*/
/* ---------------------------------------------------------------- */
function serverOptions_getCities (withEmpty, serverName, afterFunction)
{
	if (withEmpty  == undefined) withEmpty  = true;
	if (serverName == undefined) serverName = "cities";

	var serverObj 	 = new serverInterfaceObj();

	serverObj.addDummyTag ("withEmpty", 	withEmpty);
	serverObj.addDummyTag ("afterFunction", afterFunction);

    serverObj.sendRequest(serverName + ".getCities", undefined, "serverOptions_after_getCities");
}

/* ---------------------------------------------------------------- */
/* serverOptions_getCitiesByArea									*/
/* ---------------------------------------------------------------- */
function serverOptions_getCitiesByArea (withEmpty, serverName, areaId, afterFunction)
{
	if (withEmpty  == undefined) withEmpty  = true;
	if (serverName == undefined) serverName = "cities";

	var serverObj 	 = new serverInterfaceObj();

	serverObj.addTag 	  ("areaId", 		areaId);
	serverObj.addDummyTag ("withEmpty", 	withEmpty);
	serverObj.addDummyTag ("afterFunction", afterFunction);

    serverObj.sendRequest(serverName + ".getCities", undefined, "serverOptions_after_getCities");
}

/* ---------------------------------------------------------------- */
/* serverOptions_getCitiesByCountry									*/
/* ---------------------------------------------------------------- */
function serverOptions_getCitiesByCountry (withEmpty, serverName, countryId, afterFunction)
{
	if (withEmpty  == undefined) withEmpty  = true;
	if (serverName == undefined) serverName = "cities";

	var serverObj 	 = new serverInterfaceObj();

	serverObj.addTag 	  ("countryId", 	countryId);
	serverObj.addDummyTag ("withEmpty", 	withEmpty);
	serverObj.addDummyTag ("afterFunction", afterFunction);

    serverObj.sendRequest(serverName + ".getCities", undefined, "serverOptions_after_getCities");
}

function serverOptions_after_getCities (i)
{
	var responseXml = asyncResponseXml.getResponseXml (i);

	var selectOptions 	= new selectOptionsObj();

	if (responseXml != null)
	{
		var withEmpty = commonGetDummyData (responseXml, "withEmpty");

		var itemsNode = responseXml.getElementsByTagName("items").item(0);

		if (itemsNode == null)
		{
			selectOptions.addOption ("", "אין יישובים", "No Cities");
		} 
		else
		{
			if (withEmpty)
				selectOptions.addOption ("", "", "");

			for (i=0; i < itemsNode.childNodes.length; i++)
			{
				var currNode    = itemsNode.childNodes[i];

				if (currNode.nodeName != "item") continue;

				var id  	 	= commonGetInnerData (currNode, "id");
				var name  	 	= commonGetInnerData (currNode, "name");

				selectOptions.addOption (id, name, name);
			}
		}
	}

	var options 		= selectOptions.getOptions ();
	var afterFunction 	= commonGetDummyData (responseXml, "afterFunction");

	window[afterFunction](options);
}

/* ---------------------------------------------------------------- */
/* serverOptions_getNeighborhoods									*/
/* ---------------------------------------------------------------- */
function serverOptions_getNeighborhoods (cityId, withEmpty, serverName, afterFunction)
{
	if (withEmpty  == undefined) withEmpty  = true;
	if (serverName == undefined) serverName = "neighborhoods";
	
	var serverObj 	 = new serverInterfaceObj();

	serverObj.addTag 	  ("cityId",   cityId);
	serverObj.addDummyTag ("withEmpty", 	withEmpty);
	serverObj.addDummyTag ("afterFunction", afterFunction);

    serverObj.sendRequest(serverName + ".getNeighborhoods", undefined, "serverOptions_after_getNeighborhoods");
}

function serverOptions_after_getNeighborhoods (i)
{
	var responseXml = asyncResponseXml.getResponseXml (i);

	var selectOptions 	= new selectOptionsObj();

	if (responseXml != null)
	{
		var withEmpty = commonGetDummyData (responseXml, "withEmpty");

		var itemsNode = responseXml.getElementsByTagName("items").item(0);

		if (itemsNode == null)
		{
			selectOptions.addOption ("", "אין שכונות", "No Cities");
		} 
		else
		{
			if (withEmpty)
				selectOptions.addOption ("", "", "");

			for (i=0; i < itemsNode.childNodes.length; i++)
			{
				var currNode    = itemsNode.childNodes[i];

				if (currNode.nodeName != "item") continue;

				var id  	 	= commonGetInnerData (currNode, "id");
				var name  	 	= commonGetInnerData (currNode, "name");

				selectOptions.addOption (id, name, name);
			}
		}
	}

	var options 		= selectOptions.getOptions ();
	var afterFunction 	= commonGetDummyData (responseXml, "afterFunction");

	window[afterFunction](options);
}

/* ---------------------------------------------------------------- */
/* serverOptions_getProducers										*/
/* ---------------------------------------------------------------- */
function serverOptions_getProducers (afterFunction)
{
	var serverObj 	 = new serverInterfaceObj();

	serverObj.addDummyTag ("afterFunction", afterFunction);

   	serverObj.sendRequest("producers.getProducers", undefined, "serverOptions_after_getProducers");
}

function serverOptions_after_getProducers (i)
{
	var responseXml = asyncResponseXml.getResponseXml (i);

	var selectOptions 	= new selectOptionsObj();

	if (responseXml != null)
	{
		var itemsNode = responseXml.getElementsByTagName("items").item(0);

		if (itemsNode == null)
		{
			selectOptions.addOption ("", "אין יצרנים מוגדרים", "No Producers");
		} 
		else
		{
			selectOptions.addOption ("", "", "");
			for (i=0; i < itemsNode.childNodes.length; i++)
			{
				var currNode    = itemsNode.childNodes[i];

				if (currNode.nodeName != "item") continue;

				var id  	 	= commonGetInnerData (currNode, "id");
				var name  	 	= commonGetInnerData (currNode, "name");

				var optionText = id + " - " + name;

				selectOptions.addOption (id, optionText, optionText);
			}
		}
	}

	var options 		= selectOptions.getOptions ();
	var afterFunction 	= commonGetDummyData (responseXml, "afterFunction");

	window[afterFunction](options);
}

/* ---------------------------------------------------------------- */
/* serverOptions_getPicTypes										*/
/* ---------------------------------------------------------------- */
function serverOptions_getPicTypes (afterFunction)
{
	var serverObj 	 = new serverInterfaceObj();

	serverObj.addDummyTag ("afterFunction", afterFunction);

    serverObj.sendRequest("shop.getPicTypes", undefined, "serverOptions_after_getPicTypes");
}


function serverOptions_after_getPicTypes (i)
{
	var responseXml = asyncResponseXml.getResponseXml (i);

	var selectOptions 	= new selectOptionsObj();
	selectOptions.addOption ("", "", "");

	if (responseXml != null)
	{
		var itemsNode = responseXml.getElementsByTagName("items").item(0);

		for (i=0; i < itemsNode.childNodes.length; i++)
		{
			var currNode    = itemsNode.childNodes[i];

			if (currNode.nodeName != "item") continue;

			var id  	 	= commonGetInnerData (currNode, "id");
			var name  	 	= commonGetInnerData (currNode, "name");

			selectOptions.addOption (id, name, name);
		}
	}

	var options 		= selectOptions.getOptions ();
	var afterFunction 	= commonGetDummyData (responseXml, "afterFunction");

	window[afterFunction](options);
}

/* ---------------------------------------------------------------- */
/* serverOptions_getMailingLists									*/
/* ---------------------------------------------------------------- */
function serverOptions_getMailingLists (emptyType, afterFunction)
{
	if (emptyType == undefined) emptyType = "empty";

	var serverObj 	 = new serverInterfaceObj();

	serverObj.addDummyTag ("emptyType", 	emptyType);
	serverObj.addDummyTag ("afterFunction", afterFunction);

	if (emptyType != "empty")
		serverObj.addTag ("addSpecials", "0");

    serverObj.sendRequest("club.getClubMailingLists", undefined, "serverOptions_after_getMailingLists");
}

function serverOptions_after_getMailingLists (i)
{
	var responseXml = asyncResponseXml.getResponseXml (i);

	var selectOptions 	= new selectOptionsObj();

	if (responseXml != null)
	{
		var emptyType = commonGetDummyData (responseXml, "emptyType");

		var itemsNode = responseXml.getElementsByTagName("items").item(0);

		if (itemsNode == null)
		{
			if (emptyType == "empty")
				selectOptions.addOption ("", "אין רשימות תפוצה", "No Mailing Lists");
		} 
		else
		{
			if (emptyType == "empty")
				selectOptions.addOption ("", "<בחירת רשימת תפוצה>", "");

			if (emptyType == "null")
				selectOptions.addOption ("", "", "");

			for (i=0; i < itemsNode.childNodes.length; i++)
			{
				var currNode    = itemsNode.childNodes[i];

				if (currNode.nodeName != "item") continue;

				var id  	 	= commonGetInnerData (currNode, "id");
				var name  	 	= commonGetInnerData (currNode, "name");

				var optionText = id + " - " + name;

				selectOptions.addOption (id, optionText, optionText);
			}
		}
	}

	var options 		= selectOptions.getOptions ();
	var afterFunction 	= commonGetDummyData (responseXml, "afterFunction");

	window[afterFunction](options);
}

/* ---------------------------------------------------------------- */
/* serverOptions_getContacts										*/
/* ---------------------------------------------------------------- */
function serverOptions_getContacts (onlyActive, afterFunction)
{
	var serverObj 	 = new serverInterfaceObj();

	serverObj.addDummyTag ("afterFunction", afterFunction);

	if (onlyActive)
		serverObj.addTag ("status", "active");
 
	serverObj.addTag ("sortBy",  "fullname");
	serverObj.addTag ("sortDir", "asc");

    serverObj.sendRequest("contacts.getContacts", undefined, "serverOptions_after_getContacts");
}

function serverOptions_after_getContacts (i)
{
	var responseXml = asyncResponseXml.getResponseXml (i);

	var selectOptions 	= new selectOptionsObj();
	
	if (responseXml != null)
	{
		var itemsNode = responseXml.getElementsByTagName("items").item(0);

		if (itemsNode == null)
		{
			selectOptions.addOption ("", "אין התקשרויות", "No Contacts");
		} 
		else
		{
			selectOptions.addOption ("", "<בחירת גולש>", "");
			for (i=0; i < itemsNode.childNodes.length; i++)
			{
				var currNode    = itemsNode.childNodes[i];

				if (currNode.nodeName != "item") continue;

				var id  	 	= commonGetInnerData (currNode, "id");
				var name  	 	= commonGetInnerData (currNode, "fullname");
				var email  	 	= commonGetInnerData (currNode, "email");

				var optionText = name;
			   
				if (email != "")
					optionText += " [" + email + "]";

				selectOptions.addOption (id, optionText, optionText);
			}
		}
	}

	var options 		= selectOptions.getOptions ();
	var afterFunction 	= commonGetDummyData (responseXml, "afterFunction");

	window[afterFunction](options);
}

/* ---------------------------------------------------------------- */
/* serverOptions_getMailingListMembers								*/
/* ---------------------------------------------------------------- */
function serverOptions_getMailingListMembers (mailingListId, afterFunction)
{
	var serverObj 	 = new serverInterfaceObj();

	serverObj.addTag 	  ("mailingListId", mailingListId);
	serverObj.addDummyTag ("afterFunction", afterFunction);

    serverObj.sendRequest("club.getMailingListMembers", undefined, "serverOptions_after_getMailingListMembers");

}

function serverOptions_after_getMailingListMembers (i)
{
	var responseXml = asyncResponseXml.getResponseXml (i);

	var selectOptions 	= new selectOptionsObj();
	
	if (responseXml != null)
	{
		var itemsNode = responseXml.getElementsByTagName("items").item(0);

		if (itemsNode == null)
		{
			selectOptions.addOption ("", "אין רשומים", "No Contacts");
		} 
		else
		{
			selectOptions.addOption ("", "<בחירת גולש רשום>", "");
			for (i=0; i < itemsNode.childNodes.length && i < 1000; i++)
			{
				var currNode    = itemsNode.childNodes[i];

				if (currNode.nodeName != "item") continue;

				var id   	= commonGetInnerData (currNode, "id");
				var name 	= commonGetInnerData (currNode, "firstname") + " " + commonGetInnerData (currNode, "lastname");
				var email   = commonGetInnerData (currNode, "email");

				var optionText = name;

				if (email != "")
					optionText += " [" + email + "]";

				selectOptions.addOption (id, optionText, optionText);
			}
		}
	}

	var options 		= selectOptions.getOptions ();
	var afterFunction 	= commonGetDummyData (responseXml, "afterFunction");

	window[afterFunction](options);
}

/* ---------------------------------------------------------------- */
/* serverOptions_getBloggers										*/
/* ---------------------------------------------------------------- */
function serverOptions_getBloggers (withEmpty, afterFunction)
{
	var serverObj 	 = new serverInterfaceObj();

	serverObj.addDummyTag ("withEmpty", 	withEmpty);
	serverObj.addDummyTag ("afterFunction", afterFunction);

    serverObj.sendRequest("blogs.getBloggers", undefined, "serverOptions_after_getBloggers");
}

function serverOptions_after_getBloggers (i)
{
	var responseXml = asyncResponseXml.getResponseXml (i);

	var selectOptions 	= new selectOptionsObj();

	if (responseXml != null)
	{
		var withEmpty = commonGetDummyData (responseXml, "withEmpty");

		var itemsNode = responseXml.getElementsByTagName("items").item(0);

		if (itemsNode == null)
		{
			selectOptions.addOption ("", "אין בלוגרים", "No Bloggers");
		} 
		else
		{
			if (withEmpty)
				selectOptions.addOption ("", "", "");

			for (i=0; i < itemsNode.childNodes.length; i++)
			{
				var currNode    = itemsNode.childNodes[i];

				if (currNode.nodeName != "item") continue;

				var id   	= commonGetInnerData (currNode, "bloggerId");
				var name   	= commonGetInnerData (currNode, "fullname");

				selectOptions.addOption (id, name, name);
			}
		}
	}

	var options 		= selectOptions.getOptions ();
	var afterFunction 	= commonGetDummyData (responseXml, "afterFunction");

	window[afterFunction](options);
}
/* ---------------------------------------------------------------- */
/* serverOptions_getBlogBloggers									*/
/* ---------------------------------------------------------------- */
function serverOptions_getBlogBloggers (blogId, afterFunction)
{
	var serverObj 	 = new serverInterfaceObj();

	serverObj.addTag 	  ("blogId", 		blogId);
	serverObj.addDummyTag ("afterFunction", afterFunction);

    serverObj.sendRequest("blogs.getBlogBloggers", undefined, "serverOptions_after_getBlogBloggers");
}

function serverOptions_after_getBlogBloggers (i)
{
	var responseXml = asyncResponseXml.getResponseXml (i);

	var selectOptions 	= new selectOptionsObj();
	
	if (responseXml != null)
	{
		var itemsNode = responseXml.getElementsByTagName("items").item(0);

		for (i=0; i < itemsNode.childNodes.length; i++)
		{
			var currNode    = itemsNode.childNodes[i];

				if (currNode.nodeName != "item") continue;

			var id   	= commonGetInnerData (currNode, "bloggerId");
			var name   	= commonGetInnerData (currNode, "fullname");

			selectOptions.addOption (id, name, name);
		}
	}

	var options 		= selectOptions.getOptions ();
	var afterFunction 	= commonGetDummyData (responseXml, "afterFunction");

	window[afterFunction](options);
}

/* ---------------------------------------------------------------- */
/* serverOptions_getBlogs											*/
/* ---------------------------------------------------------------- */
function serverOptions_getBlogs (withEmpty, afterFunction)
{
	var serverObj 	 = new serverInterfaceObj();

	serverObj.addDummyTag ("withEmpty", 	withEmpty);
	serverObj.addDummyTag ("afterFunction", afterFunction);

    serverObj.sendRequest("blogs.getBlogs", undefined, "serverOptions_after_getBlogs");
}

function serverOptions_after_getBlogs (i)
{
	var responseXml = asyncResponseXml.getResponseXml (i);

	var selectOptions 	= new selectOptionsObj();

	if (responseXml != null)
	{
		var withEmpty = commonGetDummyData (responseXml, "withEmpty");

		var itemsNode = responseXml.getElementsByTagName("items").item(0);

		if (itemsNode == null)
		{
			selectOptions.addOption ("", "אין בלוגים", "No Blogs");
		} 
		else
		{
			if (withEmpty)
				selectOptions.addOption ("", "", "");

			for (i=0; i < itemsNode.childNodes.length; i++)
			{
				var currNode    = itemsNode.childNodes[i];

				if (currNode.nodeName != "item") continue;

				var id   	= commonGetInnerData (currNode, "blogId");
				var name   	= commonGetInnerData (currNode, "name");

				var optionText = id + " - " + name;

				selectOptions.addOption (id, optionText, optionText);
			}
		}
	}

	var options 		= selectOptions.getOptions ();
	var afterFunction 	= commonGetDummyData (responseXml, "afterFunction");

	window[afterFunction](options);
}

/* ---------------------------------------------------------------- */
/* serverOptions_getBlogsPosts										*/
/* ---------------------------------------------------------------- */
function serverOptions_getBlogsPosts (withEmpty, afterFunction)
{
	var serverObj 	 = new serverInterfaceObj();

	serverObj.addDummyTag ("withEmpty", 	withEmpty);
	serverObj.addDummyTag ("afterFunction", afterFunction);

    serverObj.sendRequest("blogs.getBlogsPosts", undefined, "serverOptions_after_getBlogsPosts");
}

function serverOptions_after_getBlogsPosts (i)
{
	var responseXml = asyncResponseXml.getResponseXml (i);

	var selectOptions 	= new selectOptionsObj();
	
	if (responseXml != null)
	{
		var withEmpty = commonGetDummyData (responseXml, "withEmpty");

		var itemsNode = responseXml.getElementsByTagName("items").item(0);

		if (itemsNode == null)
		{
			selectOptions.addOption ("", "אין רשומות", "No Posts");
		} 
		else
		{
			if (withEmpty)
				selectOptions.addOption ("", "", "");

			for (i=0; i < itemsNode.childNodes.length; i++)
			{
				var currNode    = itemsNode.childNodes[i];

				if (currNode.nodeName != "item") continue;

				var id   	= commonGetInnerData (currNode, "id");
				var name   	= commonGetInnerData (currNode, "title");

				var optionText = id + " - " + name;

				selectOptions.addOption (id, optionText, optionText);
			}
		}
	}

	var options 		= selectOptions.getOptions ();
	var afterFunction 	= commonGetDummyData (responseXml, "afterFunction");

	window[afterFunction](options);
}

/* ---------------------------------------------------------------- */
/* serverOptions_getTablets											*/
/* ---------------------------------------------------------------- */
function serverOptions_getTablets (withEmpty, afterFunction)
{
	if (withEmpty == undefined) withEmpty = true;

	var serverObj 	 = new serverInterfaceObj();

	serverObj.addDummyTag ("withEmpty", 	withEmpty);
	serverObj.addDummyTag ("afterFunction", afterFunction);

    serverObj.sendRequest("tablets.getTablets", undefined, "serverOptions_after_getTablets");
}

function serverOptions_after_getTablets (i)
{
	var responseXml = asyncResponseXml.getResponseXml (i);

	var selectOptions 	= new selectOptionsObj();

	if (responseXml != null)
	{
		var withEmpty = commonGetDummyData (responseXml, "withEmpty");

		var itemsNode = responseXml.getElementsByTagName("items").item(0);

		if (itemsNode == null)
		{
			selectOptions.addOption ("", "אין לוחות", "No Tablets");
		} 
		else
		{
			if (withEmpty)
				selectOptions.addOption ("", "", "");

			for (i=0; i < itemsNode.childNodes.length; i++)
			{
				var currNode    = itemsNode.childNodes[i];

				if (currNode.nodeName != "item") continue;

				var id   	= commonGetInnerData (currNode, "tabletId");
				var name   	= commonGetInnerData (currNode, "name");

				var optionText = id + " - " + name;

				selectOptions.addOption (id, optionText, optionText);
			}
		}
	}

	var options 		= selectOptions.getOptions ();
	var afterFunction 	= commonGetDummyData (responseXml, "afterFunction");

	window[afterFunction](options);
}

/* ---------------------------------------------------------------- */
/* serverOptions_getDimensions										*/
/* ---------------------------------------------------------------- */
function serverOptions_getDimensions (withEmpty, afterFunction)
{
	if (withEmpty 	  == undefined) withEmpty 		= true;

	if (commonGetGlobalData("dimensionsXml") == undefined)
	{
		var serverObj = new serverInterfaceObj();

		serverObj.addDummyTag ("withEmpty", 	withEmpty);
		serverObj.addDummyTag ("afterFunction", afterFunction);

	    serverObj.sendRequest("dimensions.getDimensions", undefined, "serverOptions_saveDimensionsXml");
	}
	else
	{
		serverOptions_loadDimensions (afterFunction);
	}
}

function serverOptions_saveDimensionsXml (i)
{
	commonSetGlobalData ("dimensionsXml", asyncResponseXml.getResponseXml (i));

	serverOptions_loadDimensions ();
}

function serverOptions_loadDimensions (afterFunction)
{
	var selectOptions 	= new selectOptionsObj();
	
	var xml = commonGetGlobalData("dimensionsXml");

	if (xml != null)
	{
		var withEmpty = commonGetDummyData (xml, "withEmpty");

		var itemsNode = xml.getElementsByTagName("items").item(0);

		if (itemsNode == null)
		{
			selectOptions.addOption ("", "אין מוגדרים ממדי תמונה ", "No Dimensions");
		} 
		else
		{
			if (withEmpty)
			{
				selectOptions.addOption ("", "", "");
				selectOptions.addOption ("0", "ללא שינוי גודל", "");
			}

			for (i=0; i < itemsNode.childNodes.length; i++)
			{
				var currNode    = itemsNode.childNodes[i];

				if (currNode.nodeName != "item") continue;

				var id   	= commonGetInnerData (currNode, "id");
				var name   	= commonGetInnerData (currNode, "description");
				var width  	= commonGetInnerData (currNode, "width");
				var height  = commonGetInnerData (currNode, "height");

				var optionText = name + " (" + width + "X" + height + ")";

				selectOptions.addOption (id, optionText, optionText);
			}
		}
	}

	var options 	  = selectOptions.getOptions ();

	if (afterFunction == undefined)
		afterFunction = commonGetDummyData (xml, "afterFunction");

	window[afterFunction](options);
}

/* ---------------------------------------------------------------- */
/* serverOptions_getGroups											*/
/* ---------------------------------------------------------------- */
function serverOptions_getGroups (withEmpty, afterFunction)
{
	if (withEmpty == undefined) withEmpty = true;

	var serverObj 	 = new serverInterfaceObj();

	serverObj.addDummyTag ("withEmpty", 	withEmpty);
	serverObj.addDummyTag ("afterFunction", afterFunction);

    serverObj.sendRequest("categories.getGroups", undefined, "serverOptions_after_getGroups");
}

function serverOptions_after_getGroups (i)
{
	var responseXml = asyncResponseXml.getResponseXml (i);

	var selectOptions 	= new selectOptionsObj();
	
	if (responseXml != null)
	{
		var withEmpty = commonGetDummyData (responseXml, "withEmpty");

		var itemsNode = responseXml.getElementsByTagName("items").item(0);

		if (itemsNode == null)
		{
			selectOptions.addOption ("", "אין קבוצות מוגדרות", "No Groups");
		} 
		else
		{
			if (withEmpty)
				selectOptions.addOption ("", "", "");

			for (i=0; i < itemsNode.childNodes.length; i++)
			{
				var currNode    = itemsNode.childNodes[i];

				if (currNode.nodeName != "item") continue;

				var id   	= commonGetInnerData (currNode, "id");
				var name   	= commonGetInnerData (currNode, "description");

				var optionText = id + " - " + name;

				selectOptions.addOption (id, optionText, optionText);
			}
		}
	}

	var options 		= selectOptions.getOptions ();
	var afterFunction 	= commonGetDummyData (responseXml, "afterFunction");

	window[afterFunction](options);
}

/* ---------------------------------------------------------------- */
/* serverOptions_getPhonesBooks										*/
/* ---------------------------------------------------------------- */
function serverOptions_getPhonesBooks (withEmpty, afterFunction)
{
	if (withEmpty == undefined) withEmpty = true;

	var serverObj 	 = new serverInterfaceObj();

	serverObj.addDummyTag ("withEmpty", 	withEmpty);
	serverObj.addDummyTag ("afterFunction", afterFunction);

    serverObj.sendRequest("phonesBook.getPhonesBooks", undefined, "serverOptions_after_getPhonesBooks");
}

function serverOptions_after_getPhonesBooks (i)
{
	var responseXml = asyncResponseXml.getResponseXml (i);
	
	var selectOptions 	= new selectOptionsObj();
	
	if (responseXml != null)
	{
		var withEmpty = commonGetDummyData (responseXml, "withEmpty");

		var itemsNode = responseXml.getElementsByTagName("items").item(0);

		if (itemsNode == null)
		{
			selectOptions.addOption ("", "אין ספרי טלפונים", "No Phones books");
		} 
		else
		{
			if (withEmpty)
				selectOptions.addOption ("", "", "");

			for (i=0; i < itemsNode.childNodes.length; i++)
			{
				var currNode    = itemsNode.childNodes[i];

				if (currNode.nodeName != "item") continue;

				var id   	= commonGetInnerData (currNode, "id");
				var name   	= commonGetInnerData (currNode, "name");

				var optionText = id + " - " + name;

				selectOptions.addOption (id, optionText, optionText);
			}
		}
	}

	var options 		= selectOptions.getOptions ();
	var afterFunction 	= commonGetDummyData (responseXml, "afterFunction");

	window[afterFunction](options);
}

/* ---------------------------------------------------------------- */
/* serverOptions_getPhonesRecords									*/
/* ---------------------------------------------------------------- */
function serverOptions_getPhonesRecords (withEmpty, afterFunction)
{
	if (withEmpty == undefined) withEmpty = true;

	var serverObj 	 = new serverInterfaceObj();

	serverObj.addDummyTag ("withEmpty", 	withEmpty);
	serverObj.addDummyTag ("afterFunction", afterFunction);

    serverObj.sendRequest("phonesBook.getPhonesRecords", undefined, "serverOptions_after_getPhonesRecords");
}

function serverOptions_after_getPhonesRecords (i)
{
	var responseXml = asyncResponseXml.getResponseXml (i);

	var selectOptions 	= new selectOptionsObj();
	
	if (responseXml != null)
	{
		var withEmpty = commonGetDummyData (responseXml, "withEmpty");

		var itemsNode = responseXml.getElementsByTagName("items").item(0);

		if (itemsNode == null)
		{
			selectOptions.addOption ("", "אין ספרי טלפונים", "No Phones books");
		} 
		else
		{
			if (withEmpty)
				selectOptions.addOption ("", "", "");

			for (i=0; i < itemsNode.childNodes.length; i++)
			{
				var currNode    = itemsNode.childNodes[i];

				if (currNode.nodeName != "item") continue;

				var id   	= commonGetInnerData (currNode, "recordId");
				var name 	= commonGetInnerData (currNode, "firstName") + " " + commonGetInnerData (currNode, "lastName");

				var optionText = id + " - " + name;

				selectOptions.addOption (id, optionText, optionText);
			}
		}
	}

	var options 		= selectOptions.getOptions ();
	var afterFunction 	= commonGetDummyData (responseXml, "afterFunction");

	window[afterFunction](options);
}

/* ---------------------------------------------------------------- */
/* serverOptions_getNadlans											*/
/* ---------------------------------------------------------------- */
function serverOptions_getNadlans (withEmpty, afterFunction)
{
	if (withEmpty == undefined) withEmpty = true;

	var serverObj 	 = new serverInterfaceObj();

	serverObj.addDummyTag ("withEmpty", 	withEmpty);
	serverObj.addDummyTag ("afterFunction", afterFunction);

    serverObj.sendRequest("nadlans.getNadlans", undefined, "serverOptions_after_getNadlans");
}

function serverOptions_after_getNadlans (i)
{
	var responseXml = asyncResponseXml.getResponseXml (i);

	var selectOptions 	= new selectOptionsObj();

	if (responseXml != null)
	{
		var withEmpty = commonGetDummyData (responseXml, "withEmpty");

		var itemsNode = responseXml.getElementsByTagName("items").item(0);

		if (itemsNode == null)
		{
			selectOptions.addOption ("", "אין לוחות נדל\"ן", "No Tablets");
		} 
		else
		{
			if (withEmpty)
				selectOptions.addOption ("", "", "");

			for (i=0; i < itemsNode.childNodes.length; i++)
			{
				var currNode    = itemsNode.childNodes[i];

				if (currNode.nodeName != "item") continue;

				var id   	= commonGetInnerData (currNode, "id");
				var name   	= commonGetInnerData (currNode, "title");

				var optionText = id + " - " + name;

				selectOptions.addOption (id, optionText, optionText);
			}
		}
	}

	var options 		= selectOptions.getOptions ();
	var afterFunction 	= commonGetDummyData (responseXml, "afterFunction");

	window[afterFunction](options);
}

/* ---------------------------------------------------------------- */
/* serverOptions_getQuestionnaires									*/
/* ---------------------------------------------------------------- */
function serverOptions_getQuestionnaires (withEmpty, afterFunction)
{
	if (withEmpty == undefined) withEmpty = true;

	var serverObj 	 = new serverInterfaceObj();

	serverObj.addDummyTag ("withEmpty", 	withEmpty);
	serverObj.addDummyTag ("afterFunction", afterFunction);

    serverObj.sendRequest("questionnaires.getQuestionnaires", undefined, "serverOptions_after_getQuestionnaires");

}

function serverOptions_after_getQuestionnaires (i)
{
	var responseXml = asyncResponseXml.getResponseXml (i);

	var selectOptions 	= new selectOptionsObj();
	
	if (responseXml != null)
	{
		var withEmpty = commonGetDummyData (responseXml, "withEmpty");

		var itemsNode = responseXml.getElementsByTagName("items").item(0);

		if (itemsNode == null)
		{
			selectOptions.addOption ("", "אין שאלונים", "No Questionnaires");
		} 
		else
		{
			if (withEmpty)
				selectOptions.addOption ("", "", "");

			for (i=0; i < itemsNode.childNodes.length; i++)
			{
				var currNode    = itemsNode.childNodes[i];

				if (currNode.nodeName != "item") continue;

				var id   	= commonGetInnerData (currNode, "questionnaireId");
				var name   	= commonGetInnerData (currNode, "title");

				var optionText = id + " - " + name;

				selectOptions.addOption (id, optionText, optionText);
			}
		}
	}

	var options 		= selectOptions.getOptions ();
	var afterFunction 	= commonGetDummyData (responseXml, "afterFunction");

	window[afterFunction](options);
}

/* ---------------------------------------------------------------- */
/* serverOptions_getCars											*/
/* ---------------------------------------------------------------- */
function serverOptions_getCars (withEmpty, afterFunction)
{
	if (withEmpty == undefined) withEmpty = true;

	var serverObj 	 = new serverInterfaceObj();

	serverObj.addDummyTag ("withEmpty", 	withEmpty);
	serverObj.addDummyTag ("afterFunction", afterFunction);

    serverObj.sendRequest("cars.getCars", undefined, "serverOptions_after_getCars");
}

function serverOptions_after_getCars (i)
{
	var responseXml = asyncResponseXml.getResponseXml (i);

	var selectOptions 	= new selectOptionsObj();
	
	if (responseXml != null)
	{
		var withEmpty = commonGetDummyData (responseXml, "withEmpty");

		var itemsNode = responseXml.getElementsByTagName("items").item(0);

		if (itemsNode == null)
		{
			selectOptions.addOption ("", "אין לוחות רכב", "No Tablets");
		} 
		else
		{
			if (withEmpty)
				selectOptions.addOption ("", "", "");

			for (i=0; i < itemsNode.childNodes.length; i++)
			{
				var currNode    = itemsNode.childNodes[i];

				if (currNode.nodeName != "item") continue;

				var id   	= commonGetInnerData (currNode, "id");
				var name   	= commonGetInnerData (currNode, "title");

				var optionText = id + " - " + name;

				selectOptions.addOption (id, optionText, optionText);
			}
		}
	}

	var options 		= selectOptions.getOptions ();
	var afterFunction 	= commonGetDummyData (responseXml, "afterFunction");

	window[afterFunction](options);
}

/* ---------------------------------------------------------------- */
/* serverOptions_getCategoryTypes									*/
/* ---------------------------------------------------------------- */
function serverOptions_getCategoryTypesAndMostItemsType (afterFunction)
{
	var serverObj 	 = new serverInterfaceObj();

	serverObj.addDummyTag ("afterFunction", afterFunction);

    serverObj.sendRequest("categories.getCategoryTypes", undefined, "serverOptions_after_getCategoryTypes");
}

function serverOptions_after_getCategoryTypes (i)
{
	var responseXml = asyncResponseXml.getResponseXml (i);

	var selectOptions 	= new selectOptionsObj();
	
	if (responseXml != null)
	{
		var itemsNode = responseXml.getElementsByTagName("items").item(0);

		for (i=0; i < itemsNode.childNodes.length; i++)
		{
			var currNode    = itemsNode.childNodes[i];

			if (currNode.nodeName != "item") continue;

			var id   	= commonGetInnerData (currNode, "id");
	
			if (commonGetGlobalData("guiLang") == "ENG")
				var name 	= commonGetInnerData (currNode, "nameEng");
			else
				var name 	= commonGetInnerData (currNode, "name");

			selectOptions.addOption (id, name, name);
		}
	}

	var returnResult = new Array(selectOptions.getOptions (), commonGetInnerData(responseXml, "typeOfMostItems"));

	var afterFunction 	= commonGetDummyData (responseXml, "afterFunction");

	window[afterFunction](returnResult);
}

/* ---------------------------------------------------------------- */
/* serverOptions_getSiteLangs										*/
/* ---------------------------------------------------------------- */
function serverOptions_getSiteLangs (afterFunction)
{
	if (commonGetGlobalData("siteLangsXml") == undefined)
	{
		var serverObj 	 = new serverInterfaceObj();

		serverObj.addDummyTag ("afterFunction", afterFunction);

	    serverObj.sendRequest("globalParms.getSiteLangs", undefined, "serverOptions_saveSiteLangs");
	}
	else
	{
		return serverOptions_loadSiteLangs (afterFunction);
	}
}

function serverOptions_saveSiteLangs (i)
{
	commonSetGlobalData ("siteLangsXml", asyncResponseXml.getResponseXml (i));

	serverOptions_loadSiteLangs ();
}

function serverOptions_loadSiteLangs (afterFunction)
{
	var xml = commonGetGlobalData("siteLangsXml");

	var selectOptions 	= new selectOptionsObj();
	
	if (xml != null)
	{
		var itemsNode = xml.getElementsByTagName("items").item(0);

		for (i=0; i < itemsNode.childNodes.length; i++)
		{
			var currNode    = itemsNode.childNodes[i];

			if (currNode.nodeName != "item") continue;

			var id   	= commonGetInnerData (currNode, "lang");
			var name   	= commonGetInnerData (currNode, "name");

			selectOptions.addOption (id, name, name);
		}
	}

	var options 		= selectOptions.getOptions ();

	if (afterFunction == undefined)
		afterFunction 	= commonGetDummyData (xml, "afterFunction");

	window[afterFunction](options);
}

/* ---------------------------------------------------------------- */
/* serverOptions_getAlbums											*/
/* ---------------------------------------------------------------- */
function serverOptions_getAlbums (withEmpty, afterFunction)
{
	if (withEmpty == undefined) withEmpty = true;

	var serverObj 	 = new serverInterfaceObj();

	serverObj.addDummyTag ("withEmpty", 	withEmpty);
	serverObj.addDummyTag ("afterFunction", afterFunction);

    serverObj.sendRequest("albums.getAlbums", undefined, "serverOptions_after_getAlbums");
}

function serverOptions_after_getAlbums (i)
{
	var responseXml = asyncResponseXml.getResponseXml (i);

	var selectOptions 	= new selectOptionsObj();
	
	if (responseXml != null)
	{
		var withEmpty = commonGetDummyData (responseXml, "withEmpty");

		var itemsNode = responseXml.getElementsByTagName("items").item(0);

		if (itemsNode == null)
		{
			selectOptions.addOption ("", "אין אלבומים", "No Albums");
		} 
		else
		{
			if (withEmpty)
				selectOptions.addOption ("", "", "");

			if (withEmpty == "choose")
				selectOptions.addOption ("", "<בחירת אלבום>" ,"");

			for (i=0; i < itemsNode.childNodes.length; i++)
			{
				var currNode    = itemsNode.childNodes[i];

				if (currNode.nodeName != "item") continue;

				var id   	= commonGetInnerData (currNode, "id");
				var name   	= commonGetInnerData (currNode, "title");

				var optionText = id + " - " + name;

				selectOptions.addOption (id, optionText, optionText);
			}
		}
	}

	var options 		= selectOptions.getOptions ();
	var afterFunction 	= commonGetDummyData (responseXml, "afterFunction");

	window[afterFunction](options);
}

/* ---------------------------------------------------------------- */
/* serverOptions_getEvents											*/
/* ---------------------------------------------------------------- */
function serverOptions_getEvents (emptyType, afterFunction)
{
	var serverObj 	 = new serverInterfaceObj();

	serverObj.addDummyTag ("emptyType", 	emptyType);
	serverObj.addDummyTag ("afterFunction", afterFunction);

    serverObj.sendRequest("events.getEvents", undefined, "serverOptions_after_getEvents");
}

function serverOptions_after_getEvents (i)
{
	var responseXml = asyncResponseXml.getResponseXml (i);

	var selectOptions 	= new selectOptionsObj();
	
	if (responseXml != null)
	{
		var emptyType = commonGetDummyData (responseXml, "emptyType");

		var itemsNode = responseXml.getElementsByTagName("items").item(0);

		if (itemsNode == null)
		{
			selectOptions.addOption ("", "אין אירועים מוגדרות", "No Essays");
		} 
		else
		{
			if (emptyType == "choose")
				selectOptions.addOption ("", "<בחירת אירוע>" ,"");

			if (emptyType == "empty")
				selectOptions.addOption ("", "" ,"");

			for (i=0; i < itemsNode.childNodes.length; i++)
			{
				var currNode    = itemsNode.childNodes[i];

				if (currNode.nodeName != "item") continue;

				var id   	= commonGetInnerData (currNode, "eventId");
				var name   	= commonGetInnerData (currNode, "name");

				var optionText = id + " - " + name;

				selectOptions.addOption (id, optionText, optionText);
			}
		}
	}

	var options 		= selectOptions.getOptions ();
	var afterFunction 	= commonGetDummyData (responseXml, "afterFunction");

	window[afterFunction](options);
}

/* ---------------------------------------------------------------- */
/* serverOptions_getBanners											*/
/* ---------------------------------------------------------------- */
function serverOptions_getBanners (emptyType, afterFunction)
{
	var serverObj 	 = new serverInterfaceObj();

	serverObj.addDummyTag ("emptyType", 	emptyType);
	serverObj.addDummyTag ("afterFunction", afterFunction);

    serverObj.sendRequest("banners.getBanners", undefined, "serverOptions_after_getBanners");
}

function serverOptions_after_getBanners (i)
{
	var responseXml = asyncResponseXml.getResponseXml (i);

	var selectOptions 	= new selectOptionsObj();
	
	if (responseXml != null)
	{
		var emptyType = commonGetDummyData (responseXml, "emptyType");

		var itemsNode = responseXml.getElementsByTagName("items").item(0);

		if (itemsNode == null)
		{
			selectOptions.addOption ("", "אין באנרים", "No Banners");
		} 
		else
		{
			if (emptyType == "choose")
				selectOptions.addOption ("", "<בחירת באנר>" ,"");

			if (emptyType == "empty")
				selectOptions.addOption ("", "" ,"");

			for (i=0; i < itemsNode.childNodes.length; i++)
			{
				var currNode    = itemsNode.childNodes[i];

				if (currNode.nodeName != "item") continue;

				var id   	= commonGetInnerData (currNode, "bannerId");
				var name   	= commonGetInnerData (currNode, "name");

				var optionText = id + " - " + name;

				selectOptions.addOption (id, optionText, optionText);
			}
		}
	}

	var options 		= selectOptions.getOptions ();
	var afterFunction 	= commonGetDummyData (responseXml, "afterFunction");

	window[afterFunction](options);
}

/* ---------------------------------------------------------------- */
/* serverOptions_getMembers											*/
/* ---------------------------------------------------------------- */
function serverOptions_getMembers (emptyType, optionField, showMemberId, afterFunction)
{
	if (optionField  == undefined) optionField = "username";
	if (showMemberId == undefined) showMemberId = true;

	// send request to server
	var serverObj 	 = new serverInterfaceObj();

	serverObj.addDummyTag ("emptyType", 	emptyType);
	serverObj.addDummyTag ("optionField", 	optionField);
	serverObj.addDummyTag ("showMemberId", 	showMemberId);
	serverObj.addDummyTag ("afterFunction", afterFunction);

    serverObj.sendRequest("club.getMembers", undefined, "serverOptions_after_getMembers");
}
 
function serverOptions_after_getMembers (i)
{
	var responseXml = asyncResponseXml.getResponseXml (i);

	var selectOptions 	= new selectOptionsObj();

	if (responseXml != null)
	{
		var emptyType 	 = commonGetDummyData (responseXml, "emptyType");
		var optionField  = commonGetDummyData (responseXml, "optionField");
		var showMemberId = commonGetDummyData (responseXml, "showMemberId");

		var itemsNode = responseXml.getElementsByTagName("items").item(0);

		if (itemsNode == null)
		{
			selectOptions.addOption ("", "אין גולשים רשומים", "No Members");
		} 
		else
		{
			if (emptyType == "choose")
				selectOptions.addOption ("", "<בחירת גולש>" ,"");

			if (emptyType == "empty")
				selectOptions.addOption ("", "" ,"");

			for (i=0; i < itemsNode.childNodes.length; i++)
			{
				currNode    = itemsNode.childNodes[i];

				var id   	= commonGetInnerData (currNode, "memberId");

				var name	= "";
				if (optionField == "username")
				{
					var name 	= commonGetInnerData (currNode, "username");
				}
				else if (optionField == "name")
				{
					var name 	= commonGetInnerData (currNode, "firstname") + " " + commonGetInnerData (currNode, "lastname");
				}

				var optionText = name;

				if (showMemberId)
					var optionText = id + " - " + name;

				selectOptions.addOption (id, optionText, optionText);
			}
		}
	}

	var options 		= selectOptions.getOptions ();
	var afterFunction 	= commonGetDummyData (responseXml, "afterFunction");

	window[afterFunction](options);
}

/* ---------------------------------------------------------------- */
/* serverOptions_getEmailFolders									*/
/* ---------------------------------------------------------------- */
function serverOptions_getEmailFolders (memberId, afterFunction)
{
	var serverObj 	 = new serverInterfaceObj();

	serverObj.addTag 	  ("memberId", 		memberId);
	serverObj.addDummyTag ("afterFunction", afterFunction);

    serverObj.sendRequest("membersEmails.getFolders", undefined, "serverOptions_after_getEmailFolders");
}

function serverOptions_after_getEmailFolders (i)
{
	var responseXml = asyncResponseXml.getResponseXml (i);

	var selectOptions 	= new selectOptionsObj();
	
	if (responseXml != null)
	{
		var itemsNode = responseXml.getElementsByTagName("items").item(0);

		if (itemsNode == null)
		{
			selectOptions.addOption ("", "אין ספריות", "No Folders");
		} 
		else
		{
			for (i=0; i < itemsNode.childNodes.length; i++)
			{
				var currNode    = itemsNode.childNodes[i];

				if (currNode.nodeName != "item") continue;

				var id   	= commonGetInnerData (currNode, "id");
				var name   	= commonGetInnerData (currNode, "name");

				selectOptions.addOption (id, name, name);
			}
		}
	}

	var options 		= selectOptions.getOptions ();
	var afterFunction 	= commonGetDummyData (responseXml, "afterFunction");

	window[afterFunction](options);
}

/* ---------------------------------------------------------------- */
/* serverOptions_getShopVariationsGroups							*/
/* ---------------------------------------------------------------- */
function serverOptions_getShopVariationsGroups (emptyType, afterFunction)
{
	var serverObj 	 = new serverInterfaceObj();

	serverObj.addDummyTag ("emptyType", 	emptyType);
	serverObj.addDummyTag ("afterFunction", afterFunction);

    serverObj.sendRequest("shopVariations.getVariationsGroups", undefined, "serverOptions_after_getShopVariationsGroups");
}

function serverOptions_after_getShopVariationsGroups (i)
{
	var responseXml = asyncResponseXml.getResponseXml (i);

	var selectOptions 	= new selectOptionsObj();

	if (responseXml != null)
	{
		var emptyType = commonGetDummyData (responseXml, "emptyType");

		var itemsNode = responseXml.getElementsByTagName("items").item(0);

		if (itemsNode == null)
		{
			selectOptions.addOption ("", "אין קבוצות", "No Variations");
		} 
		else
		{
			if (emptyType == "choose")
				selectOptions.addOption ("", "<בחירת קבוצה>", "");
			else if (emptyType == "empty")
				selectOptions.addOption ("", "", "");

			for (i=0; i < itemsNode.childNodes.length; i++)
			{
				var currNode    = itemsNode.childNodes[i];

				if (currNode.nodeName != "item") continue;

				var id   	= commonGetInnerData (currNode, "groupId");
				var name   	= commonGetInnerData (currNode, "name");

				var optionText = id + " - " + name;

				selectOptions.addOption (id, name, name);
			}
		}
	}

	var options 		= selectOptions.getOptions ();
	var afterFunction 	= commonGetDummyData (responseXml, "afterFunction");

	window[afterFunction](options);
}

/* ---------------------------------------------------------------- */
/* serverOptions_getShopVariations									*/
/* ---------------------------------------------------------------- */
function serverOptions_getShopVariations (emptyType, afterFunction)
{
	var serverObj 	 = new serverInterfaceObj();

	serverObj.addDummyTag ("emptyType", 	emptyType);
	serverObj.addDummyTag ("afterFunction", afterFunction);

    serverObj.sendRequest("shopVariations.getVariations", undefined, "serverOptions_after_getShopVariations");
}

function serverOptions_after_getShopVariations (i)
{
	var responseXml = asyncResponseXml.getResponseXml (i);

	var selectOptions 	= new selectOptionsObj();

	if (responseXml != null)
	{
		var emptyType = commonGetDummyData (responseXml, "emptyType");

		var itemsNode = responseXml.getElementsByTagName("items").item(0);

		if (itemsNode == null)
		{
			selectOptions.addOption ("", "אין וריאציות", "No Variations");
		} 
		else
		{
			if (emptyType == "choose")
				selectOptions.addOption ("", "<בחירת וריאצה>", "");
			else if (emptyType == "empty")
				selectOptions.addOption ("", "", "");

			for (i=0; i < itemsNode.childNodes.length; i++)
			{
				var currNode    = itemsNode.childNodes[i];

				if (currNode.nodeName != "item") continue;

				var id   	= commonGetInnerData (currNode, "variationId");
				var name   	= commonGetInnerData (currNode, "name");

				var optionText = id + " - " + name;

				selectOptions.addOption (id, optionText, optionText);
			}
		}
	}

	var options 		= selectOptions.getOptions ();
	var afterFunction 	= commonGetDummyData (responseXml, "afterFunction");

	window[afterFunction](options);
}

