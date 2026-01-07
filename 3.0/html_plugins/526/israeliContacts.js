var globalContactType;

var guiLang				= commonGetGlobalData ("guiLang");
var pageObj				= new dynamicPageObj(guiLang);	
var serverObj			= new serverInterfaceObj(guiLang);

var pageTitleId			= -1;
var sendTitleId			= -1;
var updateTitleId		= -1;
var addTitleId			= -1;
var searchFormId		= -1;
var searchButtonsId		= -1;
var tableId				= -1;
var rowButtonsId		= -1;
var formId				= -1;
var formBtnsId			= -1;

var idCol				= {textHEB	: "קוד", 			textENG	: "", 		xmlTag	: "id"			}
var typeCol				= {textHEB	: "סוג פנייה",		textENG	: "", 		xmlTag	: "type"		}
var statusCol			= {textHEB	: "סטטוס",			textENG	: "", 		xmlTag	: "status"		}
var dateCol				= {textHEB	: "תאריך", 			textENG	: "", 		xmlTag	: "insertTime"	}
var fullnameCol			= {textHEB	: "שם מלא",			textENG	: "", 		xmlTag	: "fullname"	}
var phoneCol			= {textHEB	: "טלפון",			textENG	: "", 		xmlTag	: "phone"		}
var emailCol			= {textHEB	: "אימייל",			textENG	: "", 		xmlTag	: "email"		}
var titleCol			= {textHEB	: "כותרת", 			textENG	: "", 		xmlTag	: "title"		}

var globalIsSuperUser		= true;

/* ---------------------------------------------------------------------------------------- */
/* israeliContacts_onLoad																		*/
/* ---------------------------------------------------------------------------------------- */
function israeliContacts_onLoad()
{
	if (!commonCanStart()) return false;

	pageObj.setDebug      (true);
	serverObj.setDebug    (true);
	serverObj.setXmlDebug (false);

	pageObj.initPage ();

	commonGetUserInfo	("onLoad_continue");
}

function onLoad_continue ()
{
	globalIsSuperUser = commonGetGlobalData ("isSuperUser");

	createPageTitles    ();
	addSearchSection    ();
	createDataTable     ();	
	createTableButtons  ();	
	createSearchSection ();
}

/* ---------------------------------------------------------------------------------------- */
/* createPageTitles																			*/
/* ---------------------------------------------------------------------------------------- */
function createPageTitles ()
{
	if (globalContactType == "sales")
		title	= "ניהול מכירות";
	else if (globalContactType == "support")
		title	= "תמיכה טכנית";
	else if (globalContactType == "customers")
		title	= "שירות לקוחות";

	pageTitleId   	= pageObj.addPageTitle 	  (title,  	"");
	sendTitleId		= pageObj.addPageSubTitle ("שליחת תשובה לפנייה", "");
	updateTitleId	= pageObj.addPageSubTitle ("פרטי פנייה", "");
	addTitleId		= pageObj.addPageSubTitle ("יצירת פנייה ללקוח", "");
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
	
var globalTypes;

/* ---------------------------------------------------------------------------------------- */
/* createSearchSection																		*/
/* ---------------------------------------------------------------------------------------- */
function createSearchSection ()
{
	var queryXml = commonGetGlobalData ("israeliContactsQueryXml");

	if (queryXml != undefined)
		pageObj.setFormXml (searchFormId, queryXml);

	var frameId = pageObj.addSearchFrame (searchFormId)

	fields = new Array();

	field1 = {type      : "text",					textHEB		 : idCol.textHEB,
			  spanData  : 1,						textENG		 : idCol.textENG,
			  dataFld   : idCol.xmlTag,				width   	 : 80}

	field2 = {type      : "text",					textHEB		 : fullnameCol.textHEB,
			  spanData  : 1,						textENG		 : fullnameCol.textENG,
			  dataFld   : fullnameCol.xmlTag,		width   	 : 150}

  	fields.push (field1, field2);
	
	width = "*" + pageObj.getTableWidth(tableId);
	fieldsWidths = {HEB	: new Array(30,120,50,width),
					ENG : new Array(30,120,50,width)}

	pageObj.addFormFields (searchFormId, frameId, fieldsWidths, fields);

	// ------------------------------------------------------------------------------------ 

	collapseIds = new Array();
	collapseIds.push (searchButtonsId);

	expandIds = new Array();
	expandIds.push (tableId);
	
	pageObj.addCollapse	  (searchFormId, collapseIds, expandIds);

	pageObj.generateForm  (searchFormId);
	
	btn1	= {type			: "search",
			   action		: "doRefresh()"}

	btn2	= {type			: "reset",
			   action		: "pageObj.emptyFormFields(" + searchFormId + ")"}

	btnsGroups = new Array ();

	btnsGroups.push (new Array(btn1, btn2));

	pageObj.generateRowOfButtons (searchButtonsId, btnsGroups, pageObj.getTableWidth(tableId));

	loadData			();
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
	column1		= {textHEB	: idCol.textHEB,			widthHEB : 60,
			 	   textENG	: idCol.textENG,			widthENG : 60,
			   	   sortType	: "number",					sortDir  : "descending",
				   xmlTag   : idCol.xmlTag}
		   
	column2		= {textHEB	: fullnameCol.textHEB,		widthHEB : 120,
				   textENG	: fullnameCol.textENG,		widthENG : 120,
				   sortType	: "text",					sortDir  : "ascending",
				   xmlTag   : fullnameCol.xmlTag}
		   
	column3		= {textHEB	: phoneCol.textHEB,			widthHEB : 120,
				   textENG	: phoneCol.textENG,			widthENG : 120,
				   sortType	: "text",					sortDir  : "ascending",
				   xmlTag   : phoneCol.xmlTag}
		   
	column4		= {textHEB	: emailCol.textHEB,			widthHEB : 150,
				   textENG	: emailCol.textENG,			widthENG : 150,
				   sortType	: "text",					sortDir  : "ascending",
				   xmlTag   : emailCol.xmlTag}
		   
	column5		= {textHEB	: titleCol.textHEB,			widthHEB : 200,
				   textENG	: titleCol.textENG,			widthENG : 200,
				   sortType	: "text",					sortDir  : "ascending",
				   xmlTag   : titleCol.xmlTag}
		   
	column6		= {textHEB	: dateCol.textHEB,			widthHEB : 160,
				   textENG	: dateCol.textENG,			widthENG : 160,
				   sortType	: "text",					sortDir  : "ascending",
				   xmlTag   : dateCol.xmlTag}
		   
	column7		= {textHEB	: statusCol.textHEB,		widthHEB : 120,
				   textENG	: statusCol.textENG,		widthENG : 120,
				   sortType	: "text",					sortDir  : "ascending",
				   xmlTag   : statusCol.xmlTag}
		   
	columns = new Array ();
	columns.push (column1, column2, column3, column4, column5, column6, column7);

	pageObj.setTableColumns (tableId, columns);
	pageObj.setTableHeight	(tableId, 350, 270);
}

/* ---------------------------------------------------------------------------------------- */
/* createTableButtons																		*/
/* ---------------------------------------------------------------------------------------- */
function createTableButtons ()
{
	btn1	= {type			: "",
			   textHEB		: "שליחת תשובה",
			   action		: "openForm('send')"}

	btn2	= {type			: "",
			   textHEB		: "פרטים",
			   action		: "openForm('update')"}

	btn3	= {type			: "delete",
			   action		: "deleteContact()"}

	btn4	= {type			: "",
			   textHEB		: "יצירת קשר",
			   action		: "openForm('add')"}

	btnsGroups = new Array ();

	btnsGroups.push (new Array(btn1, btn3));

	if (rowButtonsId == -1)
		rowButtonsId = pageObj.addRowOfButtons ();
	pageObj.generateRowOfButtons (rowButtonsId, btnsGroups, pageObj.getTableWidth(tableId));
}

/* ---------------------------------------------------------------------------------------- */
/* handleDisplay																			*/
/* ---------------------------------------------------------------------------------------- */
function handleDisplay (type, which)
{
	if (type == "report")
	{
		// hide edit form
		pageObj.hideComponent (sendTitleId);
		pageObj.hideComponent (updateTitleId);
		pageObj.hideComponent (formId);
		pageObj.hideComponent (formBtnsId);

		// show table
		pageObj.showComponent (pageTitleId);
		pageObj.showComponent (searchFormId);
		pageObj.showComponent (tableId);
		pageObj.showComponent (rowButtonsId);

		if (!pageObj.isCollapse (searchFormId))
			pageObj.showComponent (searchButtonsId);
	}

	if (type == "send" || type == "update" || type == "add")
	{
		pageObj.hideComponent (searchFormId);
		pageObj.hideComponent (searchButtonsId);
		pageObj.hideComponent (tableId);
		pageObj.hideComponent (rowButtonsId);

		// show from
		pageObj.showComponent (eval(type + "TitleId"));
		pageObj.showComponent (formId);
		pageObj.showComponent (formBtnsId);
	}
}

/* ---------------------------------------------------------------------------------------- */
/* loadData																					*/ 
/* ---------------------------------------------------------------------------------------- */
function loadData () 
{
	var queryXml = pageObj.getFormXml(searchFormId);

	commonSetGlobalData ("israeliContactsQueryXml", queryXml);

	serverObj.cleanRequest ();
	serverObj.setXml (queryXml);
	serverObj.addTag (typeCol.xmlTag, globalContactType);

	serverObj.sendRequest("israeliContacts.getContacts", pageObj.getTablePaging(tableId), "loadData_continue"); 
}

var global_firstTime = true;

function loadData_continue (i)
{
	var responseXml = asyncResponseXml.getResponseXml (i);

	if (responseXml != null)
	{
		pageObj.setTableXmls   (tableId, responseXml);
		pageObj.setTablePaging (tableId, responseXml);

		pageObj.generateTable (tableId);

		if (global_firstTime)
		{
			handleDisplay 		("report");
	
			pageObj.collapseSection (searchFormId, false);

			global_firstTime = false;
		}
	}

	return true;
}

var types = new selectOptionsObj();
types.addOption	("sales", 		"מכירות", 		"");
types.addOption	("support", 	"תמיכה טכנית", 	"");
types.addOption	("customers", 	"שירות לקוחות", "");

var statuses = new selectOptionsObj();
statuses.addOption	("waiting", 	"ממתין לטיפול", "");
statuses.addOption	("ongoing", 	"בטיפול", 		"");
statuses.addOption	("done", 		"טופל", 		"");

var globalDetailsXml;
var globalType;

/* ---------------------------------------------------------------------------------------- */
/* openForm																					*/
/* ---------------------------------------------------------------------------------------- */
function openForm (type)
{
	globalType = type;

	if (type == 'add')
	{
			openForm_continue();
			return;
	}

	if (!pageObj.isRowSelected(tableId)) 
	{
		commonMsgBox ("info", "יש לבחור שורה", "");
		return false;
	}

	serverObj.cleanRequest ();
	serverObj.addTag	   (idCol.xmlTag, pageObj.getSelectedValueOf (tableId, idCol.xmlTag));
	serverObj.sendRequest  ("israeliContacts.getContactDetails", undefined, "after_getDetails");
}

// ---------------------------------------------------------------------------------------- 

function after_getDetails (i)
{
	globalDetailsXml = asyncResponseXml.getResponseXml (i);

	openForm_continue ();
}

// ---------------------------------------------------------------------------------------- 

function openForm_continue ()
{
	var type = globalType;

	if (formId == -1)
		formId  = pageObj.addForm ();

	pageObj.resetForm (formId);

	if (type != 'add')
		pageObj.setFormXml (formId, globalDetailsXml);
	
	// ------------------------------------------------------------------------------------

	fieldsWidths = {HEB : new Array(130,210,130,210),
					ENG : new Array(130,210,130,210)}

	var fieldWidth  = 200;
	var fieldWidth2 = 540;

	fields = new Array();

	var frameId = pageObj.addFormFrame (formId, "פרטים", "Details"); 

	if (globalIsSuperUser)
	{
		field1 = {type      : "select",						textHEB		 : typeCol.textHEB,
				  spanData  : 1,							textENG		 : typeCol.textENG,
				  dataFld   : typeCol.xmlTag,				width   	 : fieldWidth,
				  options	: types.getOptions()}

		if (type == "send")
		{
			field1.type		= "span";
			field1.dataFld += "Text";
		}

		field2 = {type      : "select",						textHEB		 : statusCol.textHEB,
				  spanData  : 1,							textENG		 : statusCol.textENG,
				  dataFld   : statusCol.xmlTag,				width   	 : fieldWidth,
				  options	: statuses.getOptions()}

		fields.push (field1, field2);
	}

	if (type == "add")
	{
		field1 = {type      : "text",							textHEB		 : fullnameCol.textHEB,
				  spanData  : 1,								textENG		 : fullnameCol.textENG,
				  dataFld   : fullnameCol.xmlTag,				width   	 : fieldWidth}

		field2 = {type      : "text",							textHEB		 : phoneCol.textHEB,
				  spanData  : 1,								textENG		 : phoneCol.textENG,
				  dataFld   : phoneCol.xmlTag,					width   	 : fieldWidth}

		field3 = {type      : "text",							textHEB		 : emailCol.textHEB,
				  spanData  : 1,								textENG		 : emailCol.textENG,
				  dataFld   : emailCol.xmlTag,					width   	 : fieldWidth}

		field4 = {type		: "hidden"}; // stam
	}
	else
	{
		field1 = {type      : "span",							textHEB		 : fullnameCol.textHEB,
				  spanData  : 1,								textENG		 : fullnameCol.textENG,
				  dataFld   : fullnameCol.xmlTag,				width   	 : fieldWidth}

		field2 = {type      : "span",							textHEB		 : phoneCol.textHEB,
				  spanData  : 1,								textENG		 : phoneCol.textENG,
				  dataFld   : phoneCol.xmlTag,					width   	 : fieldWidth}

		field3 = {type      : "span",							textHEB		 : emailCol.textHEB,
				  spanData  : 1,								textENG		 : emailCol.textENG,
				  dataFld   : emailCol.xmlTag,					width   	 : fieldWidth}

		field4 = {type      : "span",							textHEB		 : dateCol.textHEB,
				  spanData  : 1,								textENG		 : dateCol.textENG,
				  dataFld   : dateCol.xmlTag,					width   	 : fieldWidth}
	}

	field5 = {type		: "textarea",					textHEB		 : "הערות פנימיות",
			  spanData	: 3,							textENG		 : "",
			  dataFld	: "internal",					width	 	 : 540,
			  rows		: 7}

	if (globalContactType == "support")
	{
		field6 = {type		: "text",						textHEB  	 : "תוספת נקודות",
				  spanData	: 3,							textENG		 : "",
				  dataFld	: "extraPoints",				width		 : fieldWidth}
	}
	else
	{
		field6 = {type		: "hidden",						dataFld	    : "extraPoints"}
	}

    hidden = {type		: "hidden",							dataFld	    : idCol.xmlTag}

	fields.push (hidden, field1, field2, field3, field4, field5, field6);

	pageObj.addFormFields (formId, frameId, fieldsWidths, fields);

	// ------------------------------------------------------------------------------------

	if (type != 'add')
	{
		fieldsWidths = {HEB : new Array(0,650),
						ENG : new Array(0,650)}

		var frameId = pageObj.addFormFrame (formId, "תוכן הפנייה", ""); 

		field1 = {type		: "span",							textHEB		 : "",
				  spanData	: 1,								textENG		 : "",
				  dataFld	: "content",						width	 	 : 640}

		fields = new Array(field1);

		pageObj.addFormFields (formId, frameId, fieldsWidths, fields);
	}

	// ------------------------------------------------------------------------------------

	if (type == "send" || type == "add")
	{
		fieldsWidths = {HEB : new Array(10,650),
						ENG : new Array(10,650)}

		var frameId = pageObj.addFormFrame (formId, "שליחת הודעה", ""); 

		field1 = {type		: "text",						textHEB		 : "",
				  spanData	: 1,							textENG		 : "",
				  dataFld	: "title",						width	 	 : 660}

		field2 = {type		: "xstandard",					textHEB		 : "",
				  spanData	: 1,							textENG		 : "",
				  dataFld	: "answer",						width	 	 : 640,
				  height	: 350}

		fields = new Array(field1, field2);

		pageObj.addFormFields (formId, frameId, fieldsWidths, fields);
	}

	// ------------------------------------------------------------------------------------

	pageObj.generateForm  (formId);

	createEditFormButtons (type);
	
	handleDisplay 		  (type);
}

/* ---------------------------------------------------------------------------------------- */
/* createEditFormButtons																	*/
/* ---------------------------------------------------------------------------------------- */
function createEditFormButtons (type)
{
	btnsGroups = new Array();

	btn1	= {type			: "back",
			   action		: "handleDisplay('report')"}

	btnsGroups.push (new Array(btn1));

	var btnText	= "";

	if (type == "send" || type == "add")
		btnText = "שליחה";
	else if (globalIsSuperUser)
		btnText = "עדכון";

	if (btnText != "")
	{
		btn2	= {type			: "",
				   textHEB		: btnText,
				   action		: "submitForm('" + type + "')"}

		btnsGroups.push (new Array(btn2));
	}

	if (formBtnsId == -1)
		formBtnsId = pageObj.addRowOfButtons 	 ();
	pageObj.generateRowOfButtons (formBtnsId, btnsGroups, pageObj.getFormWidth(formId));

}

/* ---------------------------------------------------------------------------------------- */
/* submitForm																				*/
/* ---------------------------------------------------------------------------------------- */
function submitForm (type)
{
	if (!pageObj.validateForm(formId)) return false;
	
	serverObj.setXml (pageObj.getFormXml(formId));

	serverObj.sendRequest("israeliContacts." + type + "Contact", undefined, "submitForm_continue");
}

function submitForm_continue (i)
{
	var responseXml = asyncResponseXml.getResponseXml (i);

	if (responseXml != null)
	{
		handleDisplay	("report");
		doRefresh 		();
	}
	return true;
}

/* ---------------------------------------------------------------------------------------- */
/* deleteContact																			*/ 
/* ---------------------------------------------------------------------------------------- */
function deleteContact() 
{
    if (!pageObj.isRowSelected(tableId)) 
	{
		commonMsgBox ("info", "יש לבחור שורה", "");
		return false;
	}

	confirm("מחיקת הפנייה", "", "deleteContact_confirm");
}

function deleteContact_confirm ()
{
	serverObj.cleanTags ();
	serverObj.addTag	(idCol.xmlTag, pageObj.getSelectedValueOf(tableId,idCol.xmlTag));
	
	serverObj.sendRequest("israeliContacts.deleteContact", undefined, "deleteContact_continue");
}

function deleteContact_continue (i)
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
