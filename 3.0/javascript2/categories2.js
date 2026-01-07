
var categories_global_formId;
var categories_global_formType;
var categories_global_frameTextHEB;
var categories_global_frameTextENG;
var categories_global_fieldsWidths;
var categories_global_afterFunction;

/* ---------------------------------------------------------------------------------------- */
/* categories_addFormFrame																	*/
/*  this should replace the addFrame function												*/
/* ---------------------------------------------------------------------------------------- */
function categories_addFormFrame (formId, formType, frameTextHEB, frameTextENG, fieldsWidths, itemId, type, afterFunction)
{
	if (afterFunction == undefined) afterFunction = "";

	// save params
	categories_global_formId 		= formId;
	categories_global_formType		= formType;
	categories_global_frameTextHEB	= frameTextHEB;
	categories_global_frameTextENG	= frameTextENG;
	categories_global_fieldsWidths	= fieldsWidths;
	categories_global_afterFunction	= afterFunction;

	if (categories_global_formType == "update")
	{
		theLangs = langArray.join(",");

		serverObj.cleanRequest ();
		serverObj.addTag	   ("theLangs", 	theLangs);
		serverObj.addTag	   ("itemId", 		itemId);
		serverObj.addTag	   ("type",   		type);
		serverObj.addTag	   ("selected",	    "1")
				
		serverObj.sendRequest("categories.getCategoriesOfItem", undefined, "categories_afterLoad");
	}
	else
	{
		categories_addFormFrame_continue ("");
	}
}

function categories_afterLoad (i)
{
	categories_addFormFrame_continue (asyncResponseXml.getResponseXml (i));
}

function categories_addFormFrame_continue (categoryItemsXml)
{
	var frameId = pageObj.addFormFrame (categories_global_formId, categories_global_frameTextHEB, categories_global_frameTextENG); 

    fields = new Array();

	tableCols = new Array();

	width = 0;
	for (i=0; i<categories_global_fieldsWidths.HEB.length; i++)
	{
		width  += categories_global_fieldsWidths.HEB[i];
	}
	width -= 20;

	width1 = Math.round (width * 0.1);
	if (width1 < 50) width1 = 50;
	width2 = width - width1;
	
	col1   = {textHEB   : "קוד",   				    widthHEB     : width1,
			  textENG   : "ID",					    widthENG     : width1,
       	      xmlTag    : "id"}

	col2   = {textHEB   : categoryCol.textHEB,      widthHEB     : width2,
			  textENG   : categoryCol.textENG,      widthENG     : width2,
       	      xmlTag    : "name"}

	tableCols.push (col1,col2)

	btn1 = {textHEB	: "הוספה",
			textENG	: "Add",
			action	: "addCategory(\"" + categories_global_formType + "\")"}
	
	btn2 = {textHEB	: "עדכון",
			textENG : "Update",
			action	: "updateCategory()"}
	
	btn3 = {textHEB	: "מחיקה",
			textENG : "Delete",
			action	: "deleteCategory()"}

	field1 = {type      : "table",
              height    : 150,
       	      tableCols : tableCols,
			  xml		: categoryItemsXml,
		  	  btns		: new Array(btn1,btn2,btn3)}

    fields.push (field1);

	pageObj.addFormFields (categories_global_formId, frameId, categories_global_fieldsWidths, fields);

	if (categories_global_afterFunction != undefined)
		window[categories_global_afterFunction]();
}

/* ---------------------------------------------------------------------------------------- */
/* categories_showPage																		*/
/* ---------------------------------------------------------------------------------------- */
function categories_showPage ()
{
	var height = 210;
	var width  = 480;

	guiLang	   = top.globalFrame.guiLang;
	if (guiLang == "") guiLang = "HEB";

	window.showModalDialog ("../../html/content/categoryOfItem.html", window,  "dialogHeight:" + height + "px;"	+
																 	   "dialogWidth:"  + width  + "px;"	+
																	   "resizable:no;status:no");
}

/* ---------------------------------------------------------------------------------------- */
/* categories_deleteCategory																*/
/* ---------------------------------------------------------------------------------------- */
function categories_deleteCategory (formId, formTableId, itemId, categoryId, type)
{
	serverObj.cleanRequest ();
	serverObj.addTag	   ("itemId", 		itemId);
	serverObj.addTag	   ("categoryId",	categoryId);
	
	guiLang	   = top.globalFrame.guiLang;
	if (guiLang == "") guiLang = "HEB";

	if (serverObj.sendRequest("categories.deleteCategoryItem") != null)
	{
		categories_loadItemCategories (formId, formTableId, itemId, type);
	}
}

/* ---------------------------------------------------------------------------------------- */
/* categories_loadItemCategories															*/
/* ---------------------------------------------------------------------------------------- */
function categories_loadItemCategories (formId, formTableId, itemId, type)
{
	guiLang	   = top.globalFrame.guiLang;
	if (guiLang == "") guiLang = "HEB";

	theLangs = langArray.join(",");

	pageObj.resetTable (formTableId);

	serverObj.cleanRequest ();
	serverObj.addTag	   ("theLangs", 	theLangs);
	serverObj.addTag	   ("itemId", 	    itemId);
	serverObj.addTag	   ("type",   	    type);
	serverObj.addTag	   ("selected", 	"1")
	
	categoryItemsXml = serverObj.sendRequest("categories.getCategoriesOfItem");

	pageObj.loadFormTable (formId, formTableId, categoryItemsXml);
}


