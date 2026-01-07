
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
		var theLangs = langArray.join(",");

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

	btn1 = {type	: "add",
			textHEB	: "הוספה",
			textENG	: "Add",
			action	: "addCategory(\"" + categories_global_formType + "\")"}
	
	btn2 = {type	: "update",
			textHEB	: "עדכון",
			textENG : "Update",
			action	: "updateCategory()"}
	
	btn3 = {type	: "delete",
			textHEB	: "מחיקה",
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
	// create categoryOfItem dialog (if not already created)
	if (document.getElementById("categoryOfItem") == undefined)
	{
		var oDialog = document.createElement("DIV"); 
		oDialog.id 	= "categoryOfItem";
		oDialog.style.display = "none";

		document.body.appendChild(oDialog); 

		var oIframe = document.createElement("IFRAME");
		oIframe.setAttribute ("id", "categoryOfItem_iframe");
		oIframe.setAttribute ("src", "../../html/content/categoryOfItem.html")
		oIframe.setAttribute ("width",  520)
		oIframe.setAttribute ("height", 280)
		oIframe.setAttribute ("frameborder", 0)
		oIframe.setAttribute ("style",  "margin:0px")

		oDialog.appendChild(oIframe); 
	}
	else
	{
		if (navigator.appName == "Microsoft Internet Explorer")
		{
		    document.getElementById("categoryOfItem_iframe").contentWindow.location.reload(true);
		}
		else 
		{
		    document.getElementById("categoryOfItem_iframe").src = document.getElementById("categoryOfItem_iframe").src;
		}
	}


	$("#categoryOfItem").dialog({
		modal: true,
		width: 520,
		height: 284,
		resizable: false,
		dialogClass: "no-close"
    });
}

var global_formId;
var global_formTableId;
var global_itemId;
var global_type;

/* ---------------------------------------------------------------------------------------- */
/* categories_deleteCategory																*/
/* ---------------------------------------------------------------------------------------- */
function categories_deleteCategory (formId, formTableId, itemId, categoryId, type)
{
	global_formId 	   = formId;
	global_formTableId = formTableId;
	global_itemId 	  = itemId;
	global_type 	  = type;

	serverObj.cleanRequest ();
	serverObj.addTag	   ("itemId", 		itemId);
	serverObj.addTag	   ("type",   	    type);
	serverObj.addTag	   ("categoryId",	categoryId);
	
	serverObj.sendRequest("categories.deleteCategoryItem", undefined, "categories_after_deleteCategory");
}

function categories_after_deleteCategory (i)
{
	var responseXml = asyncResponseXml.getResponseXml (i);

	categories_loadItemCategories (global_formId, global_formTableId, global_itemId, global_type);
}

/* ---------------------------------------------------------------------------------------- */
/* categories_loadItemCategories															*/
/* ---------------------------------------------------------------------------------------- */
function categories_loadItemCategories (formId, formTableId, itemId, type)
{
	global_formId 	   = formId;
	global_formTableId = formTableId;

	var theLangs = langArray.join(","); 

	pageObj.resetTable (formTableId);

	serverObj.cleanRequest ();
	serverObj.addTag	   ("theLangs", 	theLangs);
	serverObj.addTag	   ("itemId", 	    itemId);
	serverObj.addTag	   ("type",   	    type);
	serverObj.addTag	   ("selected", 	"1")
	
	serverObj.sendRequest("categories.getCategoriesOfItem", undefined, "categories_after_loadItemCategories");
}

function categories_after_loadItemCategories (i)
{
	var responseXml = asyncResponseXml.getResponseXml (i);

	pageObj.loadFormTable (global_formId, global_formTableId, responseXml);
}


