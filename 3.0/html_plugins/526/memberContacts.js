var mc_titleId			= -1;
var mc_tableId			= -1;
var mc_tableBtnsId		= -1;
var mc_formTitleId		= -1;
var mc_formId			= -1;
var mc_formBtnsId		= -1;

var mc_memberIdCol		= {textHEB	: "חבר", 			textENG	: "", 		xmlTag	: "memberId"	}
var mc_idCol			= {textHEB	: "קוד", 			textENG	: "", 		xmlTag	: "id"			}
var mc_statusCol		= {textHEB	: "סטטוס",			textENG	: "", 		xmlTag	: "status"		}
var mc_dateCol			= {textHEB	: "תאריך", 			textENG	: "", 		xmlTag	: "insertTime"	}
var mc_fullnameCol		= {textHEB	: "שם מלא",			textENG	: "", 		xmlTag	: "fullname"	}
var mc_phoneCol			= {textHEB	: "טלפון",			textENG	: "", 		xmlTag	: "phone"		}
var mc_emailCol			= {textHEB	: "אימייל",			textENG	: "", 		xmlTag	: "email"		}
var mc_titleCol			= {textHEB	: "כותרת", 			textENG	: "", 		xmlTag	: "title"		}

var mc_whichText;

/* ---------------------------------------------------------------------------------------- */
/* memberContacts_createTitles																*/
/* ---------------------------------------------------------------------------------------- */
function memberContacts_createTitles (whichText)
{
	mc_titleId		= pageObj.addPageSubTitle ("",							"");
	mc_formTitleId	= pageObj.addPageSubTitle ("",							"");

	mc_whichText = whichText;
}

/* ---------------------------------------------------------------------------------------- */
/* memberContacts_show																		*/ 
/* ---------------------------------------------------------------------------------------- */
function memberContacts_show() 
{
	if (pageObj.areRowsSelected(tableId))
	{
		commonMsgBox ("info", "יש לבחור " + mc_whichText + " אחד בלבד");
		return false;
	}

    if (!pageObj.isRowSelected(tableId)) 
	{
		commonMsgBox ("info", "יש לבחור " + mc_whichText);
		return false;
	}

	pageObj.updatePageTitle (mc_titleId, "מיילים של ה" + mc_whichText + " <span class='green'>" + 
							 pageObj.getSelectedValueOf(tableId, m_nameCol.xmlTag)) + "</span>";

	if (mc_tableId == -1)
	{
		mc_tableId  	= pageObj.addTable ();
	
		pageObj.setLoadFunction (mc_tableId, "memberContacts_loadData");

		column1		= {textHEB	: mc_idCol.textHEB,			widthHEB : 60,
				 	   textENG	: mc_idCol.textENG,			widthENG : 60,
				   	   sortType	: "number",					sortDir  : "descending",
					   xmlTag   : mc_idCol.xmlTag}
		   
		column2		= {textHEB	: mc_fullnameCol.textHEB,	widthHEB : 120,
					   textENG	: mc_fullnameCol.textENG,	widthENG : 120,
					   sortType	: "text",					sortDir  : "ascending",
					   xmlTag   : mc_fullnameCol.xmlTag}
		   
		column3		= {textHEB	: mc_phoneCol.textHEB,		widthHEB : 120,
					   textENG	: mc_phoneCol.textENG,		widthENG : 120,
					   sortType	: "text",					sortDir  : "ascending",
					   xmlTag   : mc_phoneCol.xmlTag}
		   
		column4		= {textHEB	: mc_emailCol.textHEB,		widthHEB : 150,
					   textENG	: mc_emailCol.textENG,		widthENG : 150,
					   sortType	: "text",					sortDir  : "ascending",
					   xmlTag   : mc_emailCol.xmlTag}
		   
		column5		= {textHEB	: mc_titleCol.textHEB,		widthHEB : 200,
					   textENG	: mc_titleCol.textENG,		widthENG : 200,
					   sortType	: "text",					sortDir  : "ascending",
					   xmlTag   : mc_titleCol.xmlTag}
		   
		column6		= {textHEB	: mc_dateCol.textHEB,		widthHEB : 160,
					   textENG	: mc_dateCol.textENG,		widthENG : 160,
					   sortType	: "text",					sortDir  : "ascending",
					   xmlTag   : mc_dateCol.xmlTag}
		   
		column7		= {textHEB	: mc_statusCol.textHEB,		widthHEB : 120,
					   textENG	: mc_statusCol.textENG,		widthENG : 120,
					   sortType	: "text",					sortDir  : "ascending",
					   xmlTag   : mc_statusCol.xmlTag}
		   
		columns = new Array ();
		columns.push (column1, column2, column3, column4, column5, column6, column7);

		pageObj.setTableColumns (mc_tableId, columns);
		pageObj.setTableHeight	(mc_tableId, 350, 270);

		btn1	= {type			: "",
				   textHEB		: "שליחת מייל",
				   action		: "memberContacts_openForm('add')"}

		btn2	= {type			: "",
				   textHEB		: "שליחת תשובה",
				   action		: "memberContacts_openForm('send')"}

		btn3	= {type			: "delete",
				   action		: "memberContacts_delete()"}

		btn4	= {type			: "back",
				   action		: "handleDisplay('report')"}

		btnsGroups = new Array ();

		btnsGroups.push (new Array(btn1, btn2), new Array(btn4));

		if (mc_tableBtnsId == -1)
			mc_tableBtnsId = pageObj.addRowOfButtons ();
		pageObj.generateRowOfButtons (mc_tableBtnsId, btnsGroups, pageObj.getTableWidth(mc_tableId));
	}

	memberContacts_loadData ();
}

/* ---------------------------------------------------------------------------------------- */
/* memberContacts_loadData																	*/ 
/* ---------------------------------------------------------------------------------------- */
function memberContacts_loadData () 
{
	serverObj.cleanRequest ();
	serverObj.addTag (mc_memberIdCol.xmlTag, pageObj.getSelectedValueOf(tableId, m_idCol.xmlTag));

	serverObj.sendRequest("israeliContacts.getContacts", pageObj.getTablePaging(mc_tableId), "memberContacts_loadData_continue"); 
}

function memberContacts_loadData_continue (i)
{
	var responseXml = asyncResponseXml.getResponseXml (i);

	if (responseXml != null)
	{
		pageObj.setTableXmls   (mc_tableId, responseXml);
		pageObj.setTablePaging (mc_tableId, responseXml);

		pageObj.generateTable (mc_tableId);

		handleDisplay 		("contacts");
	}

	return true;
}

/* ---------------------------------------------------------------------------------------- */
/* memberContacts_handleDisplay																*/
/* ---------------------------------------------------------------------------------------- */
function memberContacts_handleDisplay (type)
{
	if (type == "report")
	{
		pageObj.hideComponent (mc_titleId);
		pageObj.hideComponent (mc_tableId);
		pageObj.hideComponent (mc_tableBtnsId);
	}
	else if (type == "contacts")
	{
		pageObj.hideComponent (mc_formTitleId);
		pageObj.hideComponent (mc_formId);
		pageObj.hideComponent (mc_formBtnsId);

		pageObj.showComponent (mc_titleId);
		pageObj.showComponent (mc_tableId);
		pageObj.showComponent (mc_tableBtnsId);
	}
	else if (type == "contactEdit")
	{
		pageObj.hideComponent (mc_titleId);
		pageObj.hideComponent (mc_tableId);
		pageObj.hideComponent (mc_tableBtnsId);

		pageObj.showComponent (mc_formTitleId);
		pageObj.showComponent (mc_formId);
		pageObj.showComponent (mc_formBtnsId);
	}
}

var mc_statuses = new selectOptionsObj();
mc_statuses.addOption	("waiting", 	"ממתין לטיפול", "");
mc_statuses.addOption	("ongoing", 	"בטיפול", 		"");
mc_statuses.addOption	("done", 		"טופל", 		"");

/* ---------------------------------------------------------------------------------------- */
/* memberContacts_openForm																	*/
/* ---------------------------------------------------------------------------------------- */
function memberContacts_openForm (type)
{
	globalType = type;

	if (type == "send")
	{
		if (!pageObj.isRowSelected(mc_tableId)) 
		{
			commonMsgBox ("info", "יש לבחור שורה", "");
			return false;
		}

		serverObj.cleanRequest ();
		serverObj.addTag	   (mc_idCol.xmlTag, pageObj.getSelectedValueOf (mc_tableId, mc_idCol.xmlTag));
		serverObj.sendRequest  ("israeliContacts.getContactDetails", undefined, "memberContacts_openForm_continue");
	}
	else
	{
		memberContacts_openForm_continue ();
	}
}

function memberContacts_openForm_continue (i)
{
	var type = globalType;

	if (mc_formId == -1)
		mc_formId  = pageObj.addForm ();

	pageObj.resetForm (mc_formId);

	var to	= " <span class='green'>" + pageObj.getSelectedValueOf(tableId, m_nameCol.xmlTag) + "</span>";

	if (type == "add")
		titleText	= "שליחת מייל ל" + mc_whichText + to;
	else
		titleText	= "שליחת תשובה";

	if (type == "send")
		pageObj.setFormXml (mc_formId, asyncResponseXml.getResponseXml (i));
	
	pageObj.updatePageTitle (mc_formTitleId, titleText);

	// ------------------------------------------------------------------------------------

	if (type == "add")
	{
		var frameId = pageObj.addFormFrame (mc_formId, "זיהוי", ""); 

		fieldsWidths = {HEB : new Array(100,670),
						ENG : new Array(100,670)}

		var fieldWidth  = 650;

		field1 = {type      : "text",							textHEB		 : mc_titleCol.textHEB,
				  spanData  : 1,								textENG		 : mc_titleCol.textENG,
				  dataFld   : mc_titleCol.xmlTag,				width   	 : fieldWidth}

		field2 = {type      : "xstandard",						textHEB		 : "תוכן",
				  spanData  : 1,								textENG		 : "",
				  dataFld   : "content",						width   	 : fieldWidth,
				  height	: 400}

	    hidden = {type		: "hidden",							dataFld	     : mc_memberIdCol.xmlTag,
															    defaultValue : pageObj.getSelectedValueOf(tableId, m_idCol.xmlTag)}

		fields = new Array(hidden, field1, field2);

		pageObj.addFormFields (mc_formId, frameId, fieldsWidths, fields);

		pageObj.generateForm  (mc_formId, false);
	}
	else
	{
		fieldsWidths = {HEB : new Array(130,210,130,210),
						ENG : new Array(130,210,130,210)}

		var fieldWidth  = 200;
		var fieldWidth2 = 540;

		fields = new Array();

		var frameId = pageObj.addFormFrame (mc_formId, "פרטים", "Details"); 

		field1 = {type      : "select",						textHEB		 : mc_statusCol.textHEB,
				  spanData  : 3,							textENG		 : mc_statusCol.textENG,
				  dataFld   : mc_statusCol.xmlTag,			width   	 : fieldWidth,
				  options	: mc_statuses.getOptions()}

		field2 = {type      : "span",						textHEB		 : mc_fullnameCol.textHEB,
				  spanData  : 1,							textENG		 : mc_fullnameCol.textENG,
				  dataFld   : mc_fullnameCol.xmlTag,		width   	 : fieldWidth}

		field3 = {type      : "span",						textHEB		 : mc_phoneCol.textHEB,
				  spanData  : 1,							textENG		 : mc_phoneCol.textENG,
				  dataFld   : mc_phoneCol.xmlTag,			width   	 : fieldWidth}

		field4 = {type      : "span",						textHEB		 : mc_emailCol.textHEB,
				  spanData  : 1,							textENG		 : mc_emailCol.textENG,
				  dataFld   : mc_emailCol.xmlTag,			width   	 : fieldWidth}

		field5 = {type      : "span",						textHEB		 : mc_dateCol.textHEB,
				  spanData  : 1,							textENG		 : mc_dateCol.textENG,
				  dataFld   : mc_dateCol.xmlTag,			width   	 : fieldWidth}

		field6 = {type		: "textarea",					textHEB		 : "הערות פנימיות",
				  spanData	: 3,							textENG		 : "",
				  dataFld	: "internal",					width	 	 : 540,
				  rows		: 7}

		field7 = {type		: "hidden",						dataFld	    : "extraPoints"}

	    hidden = {type		: "hidden",						dataFld	    : mc_idCol.xmlTag}

		fields.push (hidden, field1, field2, field3, field4, field5, field6, field7);

		pageObj.addFormFields (mc_formId, frameId, fieldsWidths, fields);

		// ------------------------------------------------------------------------------------

		fieldsWidths = {HEB : new Array(0,650),
						ENG : new Array(0,650)}

		var frameId = pageObj.addFormFrame (mc_formId, "תוכן הפנייה", ""); 

		field1 = {type		: "span",							textHEB		 : "",
				  spanData	: 1,								textENG		 : "",
				  dataFld	: "content",						width	 	 : 640}

		fields = new Array(field1);

		pageObj.addFormFields (mc_formId, frameId, fieldsWidths, fields);

		// ------------------------------------------------------------------------------------

		fieldsWidths = {HEB : new Array(10,650),
						ENG : new Array(10,650)}

		var frameId = pageObj.addFormFrame (mc_formId, "שליחת הודעה", ""); 

		field1 = {type		: "text",						textHEB		 : "",
				  spanData	: 1,							textENG		 : "",
				  dataFld	: "title",						width	 	 : 660}

		field2 = {type		: "xstandard",					textHEB		 : "",
				  spanData	: 1,							textENG		 : "",
				  dataFld	: "answer",						width	 	 : 640,
				  height	: 350}

		fields = new Array(field1, field2);

		pageObj.addFormFields (mc_formId, frameId, fieldsWidths, fields);

		// ------------------------------------------------------------------------------------

		pageObj.generateForm  (mc_formId);
	}

	btnsGroups = new Array();

	btn1	= {type			: "back",
			   action		: "handleDisplay('contacts')"}

	btn2	= {type			: "",
			   textHEB		: "שליחה",
			   action		: "memberContacts_submitForm('" + type + "')"}

	btnsGroups.push (new Array(btn1), new Array(btn2));

	if (mc_formBtnsId == -1)
		mc_formBtnsId = pageObj.addRowOfButtons 	 ();
	pageObj.generateRowOfButtons (mc_formBtnsId, btnsGroups, pageObj.getFormWidth(mc_formId));

	memberContacts_handleDisplay ("contactEdit");
}

/* ---------------------------------------------------------------------------------------- */
/* memberContacts_submitForm																*/
/* ---------------------------------------------------------------------------------------- */
function memberContacts_submitForm (type)
{
	if (!pageObj.validateForm(mc_formId)) return false;
	
	serverObj.setXml (pageObj.getFormXml(mc_formId));

	serverObj.sendRequest("israeliContacts." + type + "Contact", undefined, "memberContacts_submitForm_continue");
}

function memberContacts_submitForm_continue (i)
{
	var responseXml = asyncResponseXml.getResponseXml (i);

	if (responseXml != null)
	{
		memberContacts_handleDisplay	("contacts");
		pageObj.resetTable				(mc_tableId);
		memberContacts_loadData			();
	}
	return true;
}

