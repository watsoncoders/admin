var langArray		= commonGetGlobalData ("langArray");

var guiLang			= commonGetGlobalData ("guiLang");
var pageObj			= new dynamicPageObj(guiLang);	
var serverObj		= new serverInterfaceObj(guiLang);

var pageTitleId			= -1;
var pageSubTitleId		= -1;
var menu_addTitleId		= -1;
var menu_updateTitleId	= -1;
var link_addTitleId		= -1;
var link_updateTitleId	= -1;
var searchFormId		= -1;
var searchButtonsId		= -1;
var tableId				= -1;
var rowButtonsId		= -1;
var formId				= -1;
var formButtonsId		= -1;

var	menuIdCol			= {textHEB	: "תפריט",	   	  	textENG	: "ID",   			xmlTag	: "menuId"			}
var	menuNameCol			= {textHEB	: "שם תפריט",  	  	textENG	: "Name",   		xmlTag	: "menuName"		}
var	menuTitleCol		= {textHEB	: "כותרת לתפריט", 	textENG	: "Title",   		xmlTag	: "title"			}

var subMenuCol			= {textHEB  : "תת תפריט",		textENG : "Parent Menu",	xmlTag  : "parentId"		}

var	linkIdCol			= {textHEB	: "קוד", 			textENG	: "ID",   			xmlTag	: "id"				}
var	linkNameCol			= {textHEB	: "שם", 			textENG	: "Name",   		xmlTag	: "name"			}
var	linkTypeCol			= {textHEB	: "סוג הקישור",		textENG	: "Type",   		xmlTag	: "type"			}
var	linkPosCol			= {textHEB	: "מיקום",	   		textENG	: "Position",   	xmlTag	: "pos"				}

var globalFromDesign;

/* ---------------------------------------------------------------------------------------- */
/* onLoad																					*/
/* ---------------------------------------------------------------------------------------- */
function onLoad(fromDesign)
{
	if (fromDesign == undefined) fromDesign = false;

	globalFromDesign = fromDesign;

	if (!commonCanStart()) return false;

	pageObj.setDebug      (true);
	serverObj.setDebug    (true);
	serverObj.setXmlDebug (false);

	pageObj.initPage 	  ();

	commonGetUserInfo 	  ("onLoad_continue");
}

function onLoad_continue ()
{
	createPageTitles    	();	

	addSearchSection		();

	pageSubTitleId	= pageObj.addPageSubTitle ("טבלת קישורי תפריט", "Menu Links table", 757);

	createDataTable     	();

	createTableButtons  	();

	createSearchSection		();

}

function onLoad_finish ()
{
	loadData				();
	handleDisplay 			("report");
}

/* ---------------------------------------------------------------------------------------- */
/* createPageTitles																			*/
/* ---------------------------------------------------------------------------------------- */
function createPageTitles ()
{	
	pageTitleId   	 	= pageObj.addPageTitle ("ניהול תפריטים",  			"Menus management");

	menu_addTitleId		= pageObj.addPageSubTitle ("הוספת תפריט",				"Add a menu");
	menu_updateTitleId	= pageObj.addPageSubTitle ("עדכון פרטי תפריט",			"Update a menu");

	link_addTitleId		= pageObj.addPageSubTitle ("הוספת קישור או תת תפריט",	"Add a menu link");
	link_updateTitleId	= pageObj.addPageSubTitle ("עדכון קישור תפריט",		"Update a menu link");
}
	
/* ---------------------------------------------------------------------------------------- */
/* addSearchSection																			*/
/* ---------------------------------------------------------------------------------------- */
function addSearchSection ()
{
	if (searchFormId == -1)
		searchFormId   = pageObj.addForm ();
	
	if (searchButtonsId == -1)
		searchButtonsId = pageObj.addRowOfButtons ();
}
	
var globalCurrMenu 		= "";
var globalMenusOptions	= undefined;

/* ---------------------------------------------------------------------------------------- */
/* createSearchSection																		*/
/* ---------------------------------------------------------------------------------------- */
function createSearchSection ()
{
	if (globalMenusOptions == undefined)
		serverOptions_getMenus("", "after_loadMenus");

	createSearchSection_continue ();
}

function after_loadMenus (options)
{
	globalMenusOptions = new Object(options);

	createSearchSection_continue ();
}

function createSearchSection_continue ()
{
	if (globalMenusOptions == undefined) return;

	// ------------------------------------------------------------------------------------ 
	
	pageObj.resetForm (searchFormId);

	// ------------------------------------------------------------------------------------ 

	var frameId = pageObj.addFormFrame (searchFormId, "בחירת תפריט", "Menu Selection")

	fields = new Array();

	if (globalCurrMenu == "" && globalMenusOptions.length > 0)
			globalCurrMenu = globalMenusOptions[0].value;

	field1 = {type      : "select",					textHEB		 : menuIdCol.textHEB,
			  spanData  : 1,						textENG		 : menuIdCol.textENG,
			  dataFld   : menuIdCol.xmlTag,			width   	 : 220,
			  mandatory : false,					action		 : "onChangeQuery()",
			  options	: globalMenusOptions,		defaultValue : globalCurrMenu}		

  	fields.push (field1);
	
	width = "*" + pageObj.getTableWidth(tableId);
	fieldsWidths = {HEB	: new Array(60,width),
					ENG : new Array(60,width)}

	pageObj.addFormFields (searchFormId, frameId, fieldsWidths, fields);

	// ------------------------------------------------------------------------------------ 

	collapseIds = new Array();
	collapseIds.push (searchButtonsId);

	expandIds = new Array();
	expandIds.push (tableId);
	
	pageObj.addCollapse	  (searchFormId, collapseIds, expandIds);

	pageObj.generateForm  (searchFormId);
	
	if (globalFromDesign)
	{
		btn1	= {type			: "",
				   textHEB		: "הוספת תפריט",
				   textENG		: "Menu Add",
				   action		: "openForm('menu','add')"}

		btn2	= {type			: "",
				   textHEB		: "עדכון תפריט",
				   textENG		: "Menu Update",
				   action		: "openForm('menu','update')"}
	
		btn3	= {type			: "",
			  	   textHEB		: "מחיקת תפריט",
			  	   textENG		: "Menu Delete",
				   action		: "deleteMenu()"}
	
		btnsGroups = new Array ();
	
		btnsGroups.push (new Array(btn1,btn2,btn3));

		pageObj.generateRowOfButtons (searchButtonsId, btnsGroups, pageObj.getTableWidth(tableId));
	}

	onLoad_finish ();
}

/* ---------------------------------------------------------------------------------------- */
/* onChangeQuery																			*/
/* ---------------------------------------------------------------------------------------- */
function onChangeQuery ()
{
	globalCurrMenu = pageObj.getFieldValue (searchFormId, menuIdCol.xmlTag);

	setTimeout ("doRefresh();", 100);
}

/* ---------------------------------------------------------------------------------------- */
/* createDataTable																			*/
/* ---------------------------------------------------------------------------------------- */
function createDataTable ()
{
	tableId  	= pageObj.addTable ();
	
	pageObj.setLoadFunction (tableId, "loadData");

	// handle the table
	// ------------------------------------------------------------------------------------
	column1		= {textHEB	: linkIdCol.textHEB,		widthHEB 	: 70,
				   textENG	: linkIdCol.textENG,		widthENG 	: 70,
				   xmlTag   : linkIdCol.xmlTag}
		   
	column2		= {textHEB	: linkNameCol.textHEB,		widthHEB 	: 240,
				   textENG	: linkNameCol.textENG,		widthENG 	: 240,
				   xmlTag   : linkNameCol.xmlTag}
		   
	column3		= {textHEB	: linkTypeCol.textHEB,		widthHEB 	: 125,
				   textENG	: linkTypeCol.textENG,		widthENG 	: 125,
				   xmlTag   : linkTypeCol.xmlTag + "Text"}
		   
	column4		= {textHEB	: "קישור",					widthHEB 	: 300,
				   textENG	: "Menu link",				widthENG 	: 300,
				   xmlTag   : "urlOrPageId",			onclick		: "commonShowUrl(" + tableId + ",this)",
				   className: "styleLink",				onmouseover : "this.title=\"לחיצה להצגת הקישור\""}
		   
	if (langArray[0] != "HEB" && langArray[0] != "HB2" && langArray[0] != "ARB")
		column2.dir = "ltr";

	columns = new Array ();
	columns.push (column1, column2, column3, column4);

	pageObj.setTableColumns (tableId, columns);
	pageObj.setTableHeight	(tableId, 340, 340);
}

/* ---------------------------------------------------------------------------------------- */
/* createTableButtons																		*/
/* ---------------------------------------------------------------------------------------- */
function createTableButtons ()
{
	btn1	= {type			: "add",
			   action		: "openForm('link','add')"}

	btn2	= {type			: "update",
			   action		: "openForm('link','update')"}

	btn3	= {type			: "delete",
			   action		: "deleteMenuItem()"}

	btn4	= {type			: "print",
			   action		: "pageObj.printTable (" + tableId + "," + pageTitleId + ")"}

	btnsGroups = new Array ();

	btnsGroups.push (new Array(btn1, btn2, btn3), new Array(btn4));

	if (rowButtonsId == -1)
		rowButtonsId = pageObj.addRowOfButtons ();
	pageObj.generateRowOfButtons (rowButtonsId, btnsGroups, pageObj.getTableWidth(tableId));
}

/* ---------------------------------------------------------------------------------------- */
/* handleDisplay																			*/
/* ---------------------------------------------------------------------------------------- */
function handleDisplay (type)
{
	if (type == "report")
	{
		// hide edit form
		pageObj.hideComponent (menu_addTitleId);
		pageObj.hideComponent (menu_updateTitleId)
		pageObj.hideComponent (link_addTitleId);
		pageObj.hideComponent (link_updateTitleId);
		pageObj.hideComponent (formId);
		pageObj.hideComponent (formButtonsId);

		// show table
		pageObj.showComponent (pageSubTitleId);
		pageObj.showComponent (pageTitleId);
		pageObj.showComponent (searchFormId);
		pageObj.showComponent (searchButtonsId);
		pageObj.showComponent (tableId);
		pageObj.showComponent (rowButtonsId);
	}

	if (type == "link_add" || type == "link_update" || type == "menu_add" || type == "menu_update")
	{
		// hide table
		pageObj.hideComponent (pageSubTitleId);
		pageObj.hideComponent (searchFormId);
		pageObj.hideComponent (searchButtonsId);
		pageObj.hideComponent (tableId);
		pageObj.hideComponent (rowButtonsId);

		// show from
		pageObj.showComponent (eval(type + "TitleId"));
		pageObj.showComponent (formId);
		pageObj.showComponent (formButtonsId);
	}
}

/* ---------------------------------------------------------------------------------------- */
/* loadData																					*/ 
/* ---------------------------------------------------------------------------------------- */
function loadData () 
{
	serverObj.cleanRequest ();
	serverObj.addTag (menuIdCol.xmlTag, globalCurrMenu);

	serverObj.sendRequest("menus.getMenuItems", pageObj.getTablePaging(tableId), "loadData_continue"); 
}

function loadData_continue (i)
{
	var responseXml = asyncResponseXml.getResponseXml (i);

	if (responseXml != null)
	{
		pageObj.setTableXmls   (tableId, responseXml);
		pageObj.setTablePaging (tableId, responseXml);

		pageObj.generateTable (tableId);

		pageObj.collapseSection (searchFormId, false);
	}

	return true;
}

var globalDimensions	= undefined;
var globalSubMenus		= undefined;
var globalLinksXml		= undefined;
var globalDetailsXml	= undefined;

var typeOptions  		= commonGetLinkTypes ("menu");

var globalType;
var globalItem;

/* ---------------------------------------------------------------------------------------- */
/* openForm																					*/
/* ---------------------------------------------------------------------------------------- */
function openForm (item, type)
{
	if (item == "link" && type == "update")
	{
		if (!pageObj.isRowSelected(tableId)) 
		{
			commonMsgBox ("info", "יש לבחור תפריט", "Please choose a menu");
			return false;
		}
	}

	globalType	= type;
	globalItem	= item;

	globalDetailsXml	= undefined;

	if (globalDimensions == undefined)
		serverOptions_getDimensions (true, "after_loadDimensions");

	if (item == "menu")
	{
		if (type == "update")
		{
			globalCurrMenu = pageObj.getFieldValue(searchFormId, menuIdCol.xmlTag);

			serverObj.cleanRequest ();
			serverObj.addTag	   (menuIdCol.xmlTag, globalCurrMenu);
			serverObj.sendRequest("menus.getMenuDetails", undefined, "after_loadMenuDetails");
		}
	}
	else // link
	{
		serverOptions_getSubMenus(pageObj.getFieldValue (searchFormId, menuIdCol.xmlTag), "after_loadSubMenus");

		if (type == "update")
		{
			serverObj.cleanRequest ();
			serverObj.addTag	   ("id", pageObj.getSelectedValueOf (tableId, linkIdCol.xmlTag));
			serverObj.sendRequest("menus.getMenuItemDetails", undefined, "after_loadLinkDetails");
		}
		else
		{
			after_loadLinkDetails (-1);
		}
	}
	
	openForm_continue ();
}

// ---------------------------------------------------------------------------------------- 

function after_loadDimensions (options)
{
	globalDimensions = new Object(options);

	openForm_continue ();
}

// ---------------------------------------------------------------------------------------- 

function after_loadSubMenus (options)
{
	globalSubMenus = new Object(options);

	openForm_continue ();
}

// ---------------------------------------------------------------------------------------- 

function after_loadLinkDetails (i)
{
	if (i == -1)
		var subMenuId = 0
	else
	{
		globalDetailsXml = asyncResponseXml.getResponseXml (i);

		var subMenuId = commonGetInnerData(globalDetailsXml, subMenuCol.xmlTag);
	}

	serverObj.cleanRequest 	();
	serverObj.addTag 		(menuIdCol.xmlTag,  pageObj.getFieldValue (searchFormId, menuIdCol.xmlTag));
	serverObj.addTag 		(subMenuCol.xmlTag, subMenuId);

	serverObj.sendRequest	("menus.getMenuItems", undefined, "after_loadMenuItems"); 
}

// ---------------------------------------------------------------------------------------- 

function after_loadMenuItems (i)
{
	globalLinksXml = asyncResponseXml.getResponseXml (i);

	openForm_continue ();
}

// ---------------------------------------------------------------------------------------- 

function after_loadMenuDetails (i)
{
	globalDetailsXml = asyncResponseXml.getResponseXml (i);

	openForm_continue ();
}

// ---------------------------------------------------------------------------------------- 

function openForm_continue ()
{
	if (globalDimensions == undefined) return;

	if (formId == -1)
		formId   = pageObj.addForm ();
	
	pageObj.resetForm (formId);

	var type = globalType;
	var item = globalItem;

	if (item == "menu")
	{
		if (type == "update" && globalDetailsXml == undefined) return;
	
		// menu
		// ************************************************************************************

		fieldsWidths = {HEB : new Array(110,200),
						ENG : new Array(110,200)}

		var fieldWidth  = 190;

		var frameId = pageObj.addFormFrame (formId, "הגדרות התפריט", "Menu Definitions"); 

  		field1 = {type			: "text",					textHEB		 : menuNameCol.textHEB,
				  spanData		: 1,						textENG		 : menuNameCol.textENG,
				  dataFld		: menuNameCol.xmlTag,		width	 	 : fieldWidth,
				  minLength		: "1",						mandatory	 : false,
				  maxLength		: "25"}

  		field2 = {type			: "text",					textHEB		 : menuTitleCol.textHEB,
			  	  spanData		: 1,						textENG		 : menuTitleCol.textENG,
				  dataFld		: menuTitleCol.xmlTag,		width	 	 : fieldWidth,
				  minLength		: "1",						mandatory	 : false,
				  maxLength		: "100",					lang		 : langArray}

		hidden = {type			: "hidden",
				  dataFld		: menuIdCol.xmlTag,
				  defaultValue  : id};
			  
		fields = new Array(field1, field2, hidden);
	
		pageObj.addFormFields (formId, frameId, fieldsWidths, fields);

		// --------------------------------------------------------------------------------
		if (type == "update")
		{
			if (globalDetailsXml != null)
				pageObj.setFormXml (formId, globalDetailsXml);
		}
	
		pageObj.generateForm  (formId, false);
	}
	else
	{
		if (globalSubMenus == undefined || globalLinksXml == undefined || (type == "update" && globalDetailsXml == undefined)) return;

		// link
		// ************************************************************************************

		fieldsWidths = {HEB : new Array(150,200,150,200),
						ENG : new Array(150,200,150,200)}

		var fieldWidth   = 190;
		var fieldWidth2  = 540;

		var frameId = pageObj.addFormFrame (formId, "הגדרות הקישור", "Link Definitions"); 

		var id 	   = "";
		var	menuId 	  = pageObj.getFieldValue (searchFormId, menuIdCol.xmlTag);
	
		if (type == "update")
		{
			if (globalDetailsXml != null)
				pageObj.setFormXml (formId, globalDetailsXml);
		}

		hidden = {type			: "hidden",	
				  dataFld		: linkIdCol.xmlTag,
	  	 		  defaultValue 	: id}

		field1 = {type			: "select",					textHEB		 : menuIdCol.textHEB,
				  spanData		: 1,						textENG		 : menuIdCol.textENG,
				  dataFld		: menuIdCol.xmlTag,			width	 	 : fieldWidth,
				  options		: globalMenusOptions,
	  			  mandatory	 	: true,
		  		  defaultValue	: menuId,					action		 : "onSelectMenu()"}

		field2 = {type			: "select",					textHEB		 : subMenuCol.textHEB,
				  spanData		: 1,						textENG		 : subMenuCol.textENG,
				  dataFld		: subMenuCol.xmlTag,		width	 	 : fieldWidth,
				  options		: globalSubMenus,
				  mandatory	 	: false,				    defaultValue : "",
				  action		: "onSelectSubMenu()"}

		// build pos options
		var removePos = 0;
		if (type == "update")
		{
			field1.type = "span";
			field1.action = "";
			field1.dataFld = menuNameCol.xmlTag;

			removePos = pageObj.getSelectedValueOf (tableId, linkPosCol.xmlTag)*1 + 1;
		}	
	
		var posOptions = commonGetPosOptions (globalLinksXml, removePos, linkNameCol.xmlTag);

		if (posOptions.getOptions().length == 0)
			defaultPos = 0;
		else
			defaultPos = posOptions.getOptions().length;

		field3 = {type			: "select",					textHEB		 : linkPosCol.textHEB,
				  spanData		: 1,						textENG		 : linkPosCol.textENG,
				  dataFld		: linkPosCol.xmlTag,		width	 	 : fieldWidth,
				  options		: posOptions.getOptions(),	mandatory	 : true,
		  		  defaultValue	: defaultPos}

  		field4 = {type			: "text",					textHEB		 : linkNameCol.textHEB,
				  spanData		: 1,						textENG		 : linkNameCol.textENG,
				  dataFld		: linkNameCol.xmlTag,		width	 	 : fieldWidth,
				  minLength		: "1",						mandatory	 : false,
				  maxLength		: "100",					lang		 : langArray}
	
		field5 = {type			: "select",					textHEB		 : linkTypeCol.textHEB,
				  spanData		: 3,						textENG		 : linkTypeCol.textENG,
				  dataFld		: linkTypeCol.xmlTag,		width	 	 : fieldWidth,
				  options		: typeOptions,				mandatory	 : false,
		  		  action		: "commonOnChangeLinkType()"}

	  	field6 = {type			: "textEng",				textHEB		 : "קישור",
				  type2			: "select",					textENG		 : "Link",
				  spanData		: 3,						width	 	 : fieldWidth2,
				  dataFld		: "url",					mandatory	 : false,
				  dataFld2		: "page",					options		 : "",
				  minLength		: "1",						maxLength	 : "250",
				  state			: "lock"}

  		fields = new Array(hidden, field1, field2, field4, field3, field5, field6);
	
		if (type == "update")
		{
			hidden = {type			: "hidden",	
					  dataFld		: menuIdCol.xmlTag,
		  	 		  defaultValue 	: menuId}

	  		fields.push (hidden);
		}

		pageObj.addFormFields (formId, frameId, fieldsWidths, fields);

		// -------------------------------------------------------------------------------- 

		var frameId = pageObj.addFormFrame (formId, "מאפיינים", "Characteristics"); 

	  	field1 = {type			: "text",					textHEB		 : "בועת הסבר",
				  spanData		: 1,						textENG		 : "Bubble Text",
				  dataFld		: "title",					width	 	 : fieldWidth,
				  minLength		: "1",						mandatory	 : false,
				  maxLength		: "100",					lang		 : langArray}
	
  		field2 = {type			: "yesNoSelect",			textHEB		 : "להציג בחלון חדש",
				  spanData		: 1,						textENG		 : "Open new window",
				  dataFld		: "isNewWin",				width		 : fieldWidth,
				  mandatory		: true,						defaultValue : "0"}

		field3 = {type			: "yesNoSelect",			textHEB		 : "קישור מהבהב",
				  spanData		: 1,						textENG		 : "Blink",
				  dataFld		: "isBlink",				width		 : fieldWidth,
				  mandatory		: false,					defaultValue : "0"}
	
  		field4 = {type			: "yesNoSelect",			textHEB		 : "קישור בסגנון ייחודי",
				  spanData		: 1,						textENG		 : "Special style",
				  dataFld		: "isUniqueStyle",			width		 : fieldWidth,
				  mandatory		: false,					defaultValue : "0"}

	  	field5 = {type			: "text",					textHEB		 : "פרמטר 1",
				  spanData		: 1,						textENG		 : "",
				  dataFld		: "extraData1",				width	 	 : fieldWidth,
				  minLength		: "1",						mandatory	 : false,
				  maxLength		: "50",						lang		 : langArray}
		
	  	field6 = {type			: "text",					textHEB		 : "פרמטר 2",
				  spanData		: 1,						textENG		 : "",
				  dataFld		: "extraData2",				width	 	 : fieldWidth,
				  minLength		: "1",						mandatory	 : false,
				  maxLength		: "50",						lang		 : langArray}
		
	  	field7 = {type			: "text",					textHEB		 : "פרמטר 3",
				  spanData		: 1,						textENG		 : "",
				  dataFld		: "extraData3",				width	 	 : fieldWidth,
				  minLength		: "1",						mandatory	 : false,
				  maxLength		: "50",						lang		 : langArray}
		
	  	field8 = {type			: "text",					textHEB		 : "פרמטר 4",
				  spanData		: 1,						textENG		 : "",
				  dataFld		: "extraData4",				width	 	 : fieldWidth,
				  minLength		: "1",						mandatory	 : false,
				  maxLength		: "50",						lang		 : langArray}

		fields = new Array(field1, field2, field3, field4, field5, field6, field7, field8);

		hidden = {type			: "hidden",					dataFld   	 : "sourceFile"}
  		fields.push (hidden);

		hidden = {type			: "hidden",					dataFld   	 : "fileDeleted",
				  											defaultValue : "0"}
  		fields.push (hidden);
	
		file   = {type			: "onlineUpload",			textHEB		 : "תמונה", 
				  spanData		: 1,						textENG		 : "Picture",
				  dataFld		: "picFile",				width		 : fieldWidth}

		dim   = {type      		: "select",					textHEB 	 : "גודל",
				  spanData  	: 1,						textENG      : "Size",
				  dataFld   	: "dimensionId",			width     	 : fieldWidth,
				  options		: globalDimensions}

		if (type == "add")
		{
			fields.push (file, dim);
		}
		else
		{
			source = {type		: "span",				textHEB		 : "קובץ מקור",
					  spanData  : 3,					textENG		 : "File source",
					  dataFld	: "formLogoSource",		width   	 : fieldWidth2,
					  className : "styleLeft"}

			show   = {type		: "span",				textHEB		 : "הצגת קובץ",
					  spanData	: 1,					textENG		 : "Show file",
					  dataFld   : "show",				width		 : fieldWidth,
					  className : "styleLink", 			action       : "showPic()"}

			del    = {type		: "span",				textHEB		 : "מחיקת קובץ",
					  spanData	: 1,					textENG		 : "Delete file",
					  dataFld   : "delete",				width		 : fieldWidth,
					  className : "styleLink", 			action       : "deletePic()"}

			hidden= {type		: "hidden",				dataFld   : "fileFullName"}

		    fields.push (hidden, file, dim, source, show, del);
		}

	
		pageObj.addFormFields (formId, frameId, fieldsWidths, fields);

		// -------------------------------------------------------------------------------- 
		pageObj.generateForm  (formId);

		if (type == "update" && globalDetailsXml != null)
		{
			linkType = commonGetInnerData (globalDetailsXml, linkTypeCol.xmlTag);
			page	 = commonGetInnerData (globalDetailsXml, "page");
			url		 = commonGetInnerData (globalDetailsXml, "url");
	
			if (linkType == "url" || linkType == "urlNoFollow" || linkType == "onclick")
				value = url;
			else
				value = page;

			setTimeout ("commonOnChangeLinkType(linkType, '" + value + "')",100);
		}

	}

	openForm_finish ();
}

function openForm_finish ()
{
	if (multiUploadStart(undefined, formId))
	{
		createEditFormButtons (globalItem, globalType);
		handleDisplay 		  (globalItem + "_" + globalType);
	}
}

/* ---------------------------------------------------------------------------------------- */
/* showPic																					*/
/* ---------------------------------------------------------------------------------------- */
function showPic ()
{
	window.open (pageObj.getFieldValue(formId, "fileFullName"), "_blank");
}

/* ---------------------------------------------------------------------------------------- */
/* deletePic																				*/
/* ---------------------------------------------------------------------------------------- */
function deletePic ()
{
	pageObj.setFieldValue(formId, "formPicSource", "");
	pageObj.setFieldValue(formId, "fileDeleted",   "1");
}

/* ---------------------------------------------------------------------------------------- */
/* getNextId																				*/
/* ---------------------------------------------------------------------------------------- */
function getNextId (item)
{
	serverObj.cleanRequest ()
	serverObj.addTag	   ("item", item);

	var responseXml = serverObj.sendRequest("menus.getItemNextId");

	if (responseXml != null)
	{
		return commonGetInnerData (responseXml, "id");
	}
	return "";
}

/* ---------------------------------------------------------------------------------------- */
/* onSelectMenu																				*/
/* ---------------------------------------------------------------------------------------- */
function onSelectMenu ()
{
	serverOptions_getSubMenus(pageObj.getFieldValue(formId, menuIdCol.xmlTag), "after_onSelectMenu");
}

function after_onSelectMenu (options)
{
	pageObj.setFieldOptions (formId, subMenuCol.xmlTag, new Object(options));

	onSelectSubMenu ();
}

/* ---------------------------------------------------------------------------------------- */
/* onSelectSubMenu																			*/
/* ---------------------------	----------------------------------------------------------- */
function onSelectSubMenu ()
{
	var menu 	= pageObj.getFieldValue(formId, menuIdCol.xmlTag);
	var subMenu = pageObj.getFieldValue(formId, subMenuCol.xmlTag);

	serverObj.cleanRequest ();

	serverObj.addTag (menuIdCol.xmlTag, menu);

	if (subMenu != "")
	{
		serverObj.addTag (subMenuCol.xmlTag, subMenu);
	}
	else
	{
		serverObj.addTag (subMenuCol.xmlTag, 0);
	}
	
	serverObj.sendRequest("menus.getMenuItems", undefined, "onSelectSubMenu_continue"); 

}

function onSelectSubMenu_continue (i)
{
	var responseXml = asyncResponseXml.getResponseXml (i);

	var posOptions = commonGetPosOptions (responseXml, 0, linkNameCol.xmlTag);

	pageObj.setFieldOptions (formId, linkPosCol.xmlTag, posOptions.getOptions());
}

/* ---------------------------------------------------------------------------------------- */
/* createEditFormButtons																	*/
/* ---------------------------------------------------------------------------------------- */
function createEditFormButtons (item, type)
{
	btn1	= {type			: "back",
			   action		: "handleDisplay('report')"}

	btn2 	= {type			: "lang",
			   lang			: langArray,
			   action		: "commonChangeFormLang(" + formId + ")"}

	btn3	= {type			: type,
			   action		: "submitForm('" + item + "','" + type + "')"}

	btnsGroups = new Array ();

	btnsGroups.push (new Array(btn1), new Array(btn2), new Array(btn3));

	if (formButtonsId == -1)
		formButtonsId = pageObj.addRowOfButtons 	 ();
	pageObj.generateRowOfButtons (formButtonsId, btnsGroups, pageObj.getFormWidth(formId));

}

var global_submitType;

/* ---------------------------------------------------------------------------------------- */
/* submitForm																				*/
/* ---------------------------------------------------------------------------------------- */
function submitForm (item, type)
{
	if (!pageObj.validateForm(formId)) return false;
	
	serverObj.setXml (pageObj.getFormXml(formId));

	global_submitType = type;

	if (item == "menu")
		submitCommand = type + "Menu";
	else
	{
		var picFile = uploadGetFileName(0);
		serverObj.addTag ("sourceFile",  picFile);
		serverObj.addTag ("fileDeleted", pageObj.getFieldValue(formId, "fileDeleted"));

		submitCommand = type + "MenuItem";
	}

	serverObj.sendRequest("menus." + submitCommand, undefined, "submitForm_continue");
}

function submitForm_continue (i)
{
	var responseXml = asyncResponseXml.getResponseXml (i);

	var type = global_submitType;
	var item = globalItem;

	if (responseXml != null)
	{
		handleDisplay	("report");

		if (item == "menu")
		{
			serverOptions_getMenus("", "after_reloadMenus");
		}
		else
		{
			doRefresh 		();
		}
	}
	return true;
}

function after_reloadMenus (options)
{
	globalMenusOptions = new Object(options);

	pageObj.setFieldOptions (searchFormId, menuIdCol.xmlTag, globalMenusOptions, globalCurrMenu);
}

/* ---------------------------------------------------------------------------------------- */
/* deleteMenu																				*/ 
/* ---------------------------------------------------------------------------------------- */
function deleteMenu() 
{
	menuId = pageObj.getFieldValue (searchFormId, menuIdCol.xmlTag);

	if (menuId == "")
	{
		commonMsgBox ("info", "אין תפריטים", "No Menus");
		return false;
	}

	confirm("מחיקת התפריט", "Delete Menu", "deleteMenu_confirm");
}

function deleteMenu_confirm ()
{
	serverObj.cleanTags ();
	serverObj.addTag	(menuIdCol.xmlTag, pageObj.getFieldValue (searchFormId, menuIdCol.xmlTag));
	
	responseXml = serverObj.sendRequest("menus.deleteMenu", undefined, "deleteMenu_continue");
}

function deleteMenu_continue (i)
{
	var responseXml = asyncResponseXml.getResponseXml (i);

	if (responseXml != null)
	{
		globalCurrMenu = "";

		globalMenusOptions = serverOptions_getMenus("");

		if (globalMenusOptions.length > 0)
			globalCurrMenu = globalMenusOptions[0].value;

		pageObj.setFieldOptions (searchFormId, menuIdCol.xmlTag, globalMenusOptions, globalCurrMenu);

		doRefresh 		();
	}
	return true;
}

/* ---------------------------------------------------------------------------------------- */
/* deleteMenuItem																			*/
/* ---------------------------------------------------------------------------------------- */
function deleteMenuItem () 
{
    if (!pageObj.isRowSelected(tableId)) 
	{
		commonMsgBox ("info", "יש לבחור קישור או תת תפריט", "Please choose a link or parent menu");
		return false;
	}

	var id 		= pageObj.getSelectedValueOf(tableId,linkIdCol.xmlTag);
	
	confirm("מחיקת הקישור " + id, "Delete link " + id, "deleteMenuItem_confirm");
}

function deleteMenuItem_confirm ()
{
	var id 		= pageObj.getSelectedValueOf(tableId,linkIdCol.xmlTag);
	var menuId 	= pageObj.getFieldValue     (searchFormId,menuIdCol.xmlTag);
	
	serverObj.cleanTags ();
	serverObj.addTag	(linkIdCol.xmlTag, id);
	serverObj.addTag	(menuIdCol.xmlTag, menuId);
	
	serverObj.sendRequest("menus.deleteMenuItem", undefined, "deleteMenuItem_continue");
}

function deleteMenuItem_continue (i)
{
	var responseXml = asyncResponseXml.getResponseXml (i);

	if (responseXml != null)
	{
		doRefresh ();
	}
	return true;
}

/* ---------------------------------------------------------------------------------------- */
/* doRefresh																				*/ 
/* ---------------------------------------------------------------------------------------- */
function doRefresh() 
{
	pageObj.resetPage ();
	loadData		  ();	
}

