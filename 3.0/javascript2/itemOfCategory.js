
var pageTitleId			= -1;
var formId				= -1;
var formButtonsId		= -1;

/* ---------------------------------------------------------------------------------------- */
/* onLoad																					*/
/* ---------------------------------------------------------------------------------------- */
function onLoad()
{
	pageObj.setDebug      (true);
	serverObj.setDebug    (true);
	serverObj.setXmlDebug (false);

	pageObj.initPage 	  ();

	createPageTitle       ();		
	openForm			  ();
}

/* ---------------------------------------------------------------------------------------- */
/* createPageTitle																			*/
/* ---------------------------------------------------------------------------------------- */
function createPageTitle ()
{
	pageTitleId	 = pageObj.addPageSubTitle ("עדכון מיקום רכיב בקטגוריה",		"");

	pageObj.showComponent (pageTitleId);
}
	

/* ---------------------------------------------------------------------------------------- */
/* openForm																					*/
/* ---------------------------------------------------------------------------------------- */
function openForm (type)
{
	createEditForm  	  ();

	createEditFormButtons ();
}

/* ---------------------------------------------------------------------------------------- */
/* createEditForm																			*/
/* ---------------------------------------------------------------------------------------- */
function createEditForm ()
{
	if (formId == -1)
		formId   = pageObj.addForm ();
	
	pageObj.resetForm (formId);

	// ------------------------------------------------------------------------------------ 

	fieldsWidths = {HEB : new Array(50,200),
					ENG : new Array(50,200)}

	var fieldWidth   = 190;

	// ------------------------------------------------------------------------------------ 

	var frameId = pageObj.addFormFrame (formId, "נתוני רכיב", ""); 

	fields = new Array();
	
	pageObj.setFormXml (formId, pageObj.getSelectedRowAsXml(formTableId));

	field1	= {type		: "span",					textHEB		 : "רכיב",
			   spanData : 1,						textENG		 : "",
			   dataFld	: "name",					width	 	 : fieldWidth}

	removePos = pageObj.getSelectedValueOf (formTableId, "pos")*1 + 1;
	var posOptions = commonGetPosOptions (categoryItemsXml, removePos, "name");

	field2	= {type		: "select",					textHEB		 : "מיקום",
			   spanData : 1,						textENG		 : "",
			   dataFld	: "pos",					width	 	 : fieldWidth,
			   options	: posOptions.getOptions(),	mandatory	 : true}

    fields.push (field1,field2);

    pageObj.addFormFields (formId, frameId, fieldsWidths, fields);

	// ------------------------------------------------------------------------------------ 

	pageObj.generateForm  (formId);

	pageObj.showComponent (formId);
}

/* ---------------------------------------------------------------------------------------- */
/* createEditFormButtons																	*/
/* ---------------------------------------------------------------------------------------- */
function createEditFormButtons ()
{
	btn1	= {type			: "close",
			   action		: "window.close()"}

	btn2	= {type			: "update",
			   action		: "submitForm()"}

	btnsGroups = new Array ();

	btnsGroups.push (new Array(btn1), new Array(btn2));

	if (formButtonsId == -1)
		formButtonsId = pageObj.addRowOfButtons 	 ();
	pageObj.generateRowOfButtons (formButtonsId, btnsGroups, pageObj.getFormWidth(formId));

	pageObj.showComponent (formButtonsId);

}

/* ---------------------------------------------------------------------------------------- */
/* submitForm																				*/
/* ---------------------------------------------------------------------------------------- */
function submitForm ()
{
	if (!pageObj.validateForm(formId)) return false;
	
	serverObj.setXml (pageObj.getFormXml(formId));
	serverObj.addTag ("categoryId", categoryId);

	var responseXml = serverObj.sendRequest("categories." + "updateCategoryItem");

	if (responseXml != null)
	{
		loadCategoryItems ();
		window.close ();
	}
	return true;
}

