/*----------------------------------------------------------------------*/
/*																		*/
/* 							dynamicPageObj.js							*/
/*							-----------------							*/
/*																		*/
/*----------------------------------------------------------------------*/

/*----------------------------------------------------------------------*/
/* dynamicPageObj constructor											*/ 
/*----------------------------------------------------------------------*/
function dynamicPageObj (theLanguage)
{
	if (theLanguage == undefined)
		theLanguage = commonGetGlobalData("guiLang");

	if (theLanguage == undefined || theLanguage == "")
		theLanguage = "HEB";

	// data memebers
	// -----------------------------------------------------------------

	this.debug					 			= false;				// in debug mode ?

	this.theLanguage			 			= theLanguage;			// the language
	this.direction				 			= "rtl";				// the direction (for styling)

	this.langButtons						= "";

	if (this.theLanguage == "ENG") 
		this.direction  = "ltr"; 						// in english it is "ltr"

	this.components				 			= new Array();			// page components array

	this.tableId				 			= 0;
	this.reportId							= 0;
	this.formId					 			= 0;

	this.innerMargin						= 13;

	// methods
	// -----------------------------------------------------------------
	this.setDebug				 			= dynamicPageObj_setDebug;
	this.checkLanguage			 			= dynamicPageObj_checkLanguage;
	this.initPage				 			= dynamicPageObj_initPage;
	this.resetPage				 			= dynamicPageObj_resetPage;
	
	this.addNewComponent		 			= dynamicPageObj_addNewComponent;
	this.getComponent			 			= dynamicPageObj_getComponent;
	this.showComponent			 			= dynamicPageObj_showComponent;
	this.hideComponent			 			= dynamicPageObj_hideComponent;

	// page title
	this.addPageTitle			 			= dynamicPageObj_addPageTitle;
	this.updatePageTitle					= dynamicPageObj_updatePageTitle
	this.addPageSubTitle		 			= dynamicPageObj_addPageSubTitle;

	// table
	this.addTable				 			= dynamicPageObj_addTable;
	this.setLoadFunction		 			= dynamicPageObj_setLoadFunction;
	this.setPaintFunction		 			= dynamicPageObj_setPaintFunction;
	this.getTableObj			 			= dynamicPageObj_getTableObj;
	this.setTableHeight 		 			= dynamicPageObj_setTableHeight;
	this.setTableColumns		 			= dynamicPageObj_setTableColumns;
	this.setTableSort			 			= dynamicPageObj_setTableSort;
	this.getTableWidth 			 			= dynamicPageObj_setTableWidth;
	this.getTableDivId			 			= dynamicPageObj_getTableDivId;
	this.addTableExtension				 	= dynamicPageObj_addTableExtension;
	this.addTableExtensionBlock	 			= dynamicPageObj_addTableExtensionBlock;
	this.showTableExtensionBlock 			= dynamicPageObj_showTableExtensionBlock;
	this.hideTableExtensionBlock 			= dynamicPageObj_hideTableExtensionBlock;
	this.setTableExtensionBlocksFunction	= dynamicPageObj_setTableExtensionBlocksFunction;
	this.setTableXmls			 			= dynamicPageObj_setTableXmls;
	this.getTableXml			 			= dynamicPageObj_getTableXml;
	this.setTablePaging		 	 			= dynamicPageObj_setTablePaging;
	this.getTablePaging		 	 			= dynamicPageObj_getTablePaging;
	this.generateTable			 			= dynamicPageObj_generateTable;
	this.resetTable							= dynamicPageObj_resetTable;
	this.printTable				 			= dynamicPageObj_printTable;
	this.isRowSelected			 			= dynamicPageObj_isRowSelected;
	this.areRowsSelected			 		= dynamicPageObj_areRowsSelected;
	this.setSelectedRow						= dynamicPageObj_setSelectedRow;
	this.cancelSelectedRow		 			= dynamicPageObj_cancelSelectedRow;
	this.getSelectedRowAsXml	 			= dynamicPageObj_getSelectedRowAsXml;
	this.getSelectedValueOf		 			= dynamicPageObj_getSelectedValueOf;
	this.getSelectedValuesOf	 			= dynamicPageObj_getSelectedValuesOf;
	this.tableIsMultiSelection				= dynamicPageObj_tableIsMultiSelection;
	this.getNumberOfRows		 			= dynamicPageObj_getNumberOfRows;
	this.getRowAsXml		 	 			= dynamicPageObj_getRowAsXml;
	this.getRowValueOf			 			= dynamicPageObj_getRowValueOf;
	this.setRowValueOf			 			= dynamicPageObj_setRowValueOf;
	this.paintRow				 			= dynamicPageObj_paintRow;
	this.setColImg				 			= dynamicPageObj_setColImg;
	this.getColImg				 			= dynamicPageObj_getColImg;
	this.fillColSpan						= dynamicPageObj_fillColSpan;
	this.updateCol				 			= dynamicPageObj_updateCol;

	this.tableChanged						= dynamicPageObj_tableChanged;

	// report
	this.addReport							= dynamicPageObj_addReport;
	this.setReportSection					= dynamicPageObj_setReportSection;
	this.getReportWidth						= dynamicPageObj_getReportWidth;
	this.generateReport						= dynamicPageObj_generateReport;
	this.getReportObj			 			= dynamicPageObj_getReportObj;

	// row of buttons
	this.getMaxButtonWidth		 			= dynamicPageObj_getMaxButtonWidth;
	this.getTextWidth			 			= dynamicPageObj_getTextWidth;
	this.addRowOfButtons		 			= dynamicPageObj_addRowOfButtons;
	this.generateRowOfButtons	 			= dynamicPageObj_generateRowOfButtons;
	this.chooseLang							= dynamicPageObj_chooseLang;

	// form
	this.addForm				 			= dynamicPageObj_addForm;
	this.getFormName						= dynamicPageObj_getFormName;
	this.getFormObj				 			= dynamicPageObj_getFormObj;
	this.resetForm				 			= dynamicPageObj_resetForm;
	this.setFormLanguage					= dynamicPageObj_setFormLanguage;
	this.changeFormLanguage					= dynamicPageObj_changeFormLanguage;
	this.addFormFrame			 			= dynamicPageObj_addFormFrame;
	this.addSpecialFrame		 			= dynamicPageObj_addSpecialFrame;
	this.addSearchFrame			 			= dynamicPageObj_addSearchFrame;
	this.addFormFields			 			= dynamicPageObj_addFormFields;
	this.addCollapse			 			= dynamicPageObj_addCollapse;
	this.collapseSection					= dynamicPageObj_collapseSection;
	this.selectTab							= dynamicPageObj_selectTab;
	this.isCollapse							= dynamicPageObj_isCollapse;
	this.setFormXml				 			= dynamicPageObj_setFormXml;
	this.getFormXml				 			= dynamicPageObj_getFormXml;
	this.getFieldValue			 			= dynamicPageObj_getFieldValue;
	this.setFieldValue			 			= dynamicPageObj_setFieldValue;
    this.getOptionText                      = dynamicPageObj_getOptionText;
	this.getOptionValueText                 = dynamicPageObj_getOptionValueText;
	this.setFieldOptions					= dynamicPageObj_setFieldOptions;
	this.setFieldState			 			= dynamicPageObj_setFieldState;
	this.changeFieldTitle			 		= dynamicPageObj_changeFieldTitle;
	this.setFieldFocus			 			= dynamicPageObj_setFieldFocus;
	this.changeMandatory		 			= dynamicPageObj_changeMandatory;
	this.setFieldDisplay					= dynamicPageObj_setFieldDisplay;
	this.getFormWidth			 			= dynamicPageObj_getFormWidth;
	this.setFormAction						= dynamicPageObj_setFormAction;
	this.validateForm			 			= dynamicPageObj_validateForm;
	this.generateForm			 			= dynamicPageObj_generateForm;
	this.emptyFormFields		 			= dynamicPageObj_emptyFormFields;
	this.displayAndSetError					= dynamicPageObj_displayAndSetError;

	this.addTableForm			 			= dynamicPageObj_addTableForm;
	this.loadTableForm			 			= dynamicPageObj_loadTableForm;
	this.tableFormChanged					= dynamicPageObj_tableFormChanged;

	this.getFormTableId						= dynamicPageObj_getFormTableId;
	this.loadFormTable						= dynamicPageObj_loadFormTable;

	// span
	this.addSpan				 			= dynamicPageObj_addSpan;
	this.initSpan				 			= dynamicPageObj_initSpan;

	// debug 
	this.openDebugWindow					= dynamicPageObj_openDebugWindow; // like view source
}

/* -------------------------------------------------------------------- */
/* closePopupEvent														*/
/*	add this event for the body of the page in order to close the		*/
/*	reports popup when click outside the popup							*/
/* -------------------------------------------------------------------- */
function closePopupEvent ()
{
	try 
	{
		top.main.withReportsPopupTbl.style.display = 'none'
	} 
	catch (error) 
	{
	}
}

/*----------------------------------------------------------------------*/
/* dynamicPageObj_setDebug												*/
/*----------------------------------------------------------------------*/
function dynamicPageObj_setDebug (doDebug)
{
	this.debug = doDebug;
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_checkLanguage											*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_checkLanguage ()
{
	if (this.theLanguage != "HEB" && this.theLanguage != "ENG")
		throw new Error(1,"no page language - check page constructor");
}

/*----------------------------------------------------------------------*/
/* dynamicPageObj_initPage												*/
/*----------------------------------------------------------------------*/
function dynamicPageObj_initPage ()
{
	try
	{
		document.body.dir 		= this.direction;
		document.body.topMargin	= "0";
//		document.body.attachEvent ("onclick",closePopupEvent);
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "initPage", error)

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_resetPage												*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_resetPage ()
{
	try
	{
		// find table object
		for (var i=0; i<this.components.length; i++)
		{
			if (this.components[i].type == "table")
			{
				tblObj = this.getComponent(i).object;
	
				tblObj.reset ();
				tblObj.resetPaging ();
			}
		}
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "resetPage", error);

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_addNewComponent										*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_addNewComponent (component)
{
	try
	{
		return (this.components.push (component)-1);	
	}
    catch (error)
    {
        if (this.debug)
            showError ("dynamicPageObj", "addNewComponent", error);

        throw error
    }
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_getComponent											*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_getComponent (componentId)
{
	try
	{
		if (componentId > this.components.length-1)
			throw new Error(2, "component (" +  componentId + ") does not exist");

		return this.components[componentId];
	}
    catch (error)
    {
        throw error
    }
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_showComponent											*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_showComponent (componentId)
{
	try
	{
		if (componentId != -1)
		{
			component = this.getComponent (componentId);

			object = eval("window." + component.spanName);

			if (object == undefined)
				throw new Error(6, "Component span is undefined");

			object.style.display = "";

			if (component.type == "form")
			{
//				if (component.object.tinymceField != "")
//					setTimeout (component.spanName + ".style.display = ''", 5000);
//				else
//					object.style.display = "";

				component.object.showInnerFormObjects ();	// for show inner form objects
			}
		}
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "showComponent", error)

		throw error
	}
	
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_hideComponent											*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_hideComponent (componentId)
{
	try
	{
		if (componentId < this.components.length && componentId != -1)
		{
			component = this.components[componentId];

			object = eval("window." + component.spanName);

			if (object == undefined)
				throw new Error(6, "Component span is undefined");

			object.style.display = "none";

			if (component.type == "form")
			{
				component.object.hideInnerFormObjects ();	// for hide inner form objects
			}
		}
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "hideComponent", error)

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_addPageTitle											*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_addPageTitle (titleHEB, titleENG, explainHEB, explainENG)
{
	try 
	{
		this.checkLanguage ();

		titleId		= this.components.length;

		spanName	= "title_" + titleId + "_spn";

		spanElement = document.createElement("SPAN");

		spanElement.id 			  = spanName;
		spanElement.style.display = "none";

		var titleHtml	= "<div class='pageTitle'>" 								+
						  "		<span id='theTitle_" + titleId + "'>"				+
									eval("title" + this.theLanguage)				+
								"</span>";
					   
		var explain  = eval("explain" + this.theLanguage);

		if (explain != undefined)
		{
			titleHtml += "		<div class='explain'>"								+
									"<span id='theExplain_" + titleId + "'>"		+
											explain									+
									"</span>"										+
								"</div>";
		}
		
		titleHtml +=   "</div>";

		spanElement.innerHTML = titleHtml;

		document.body.appendChild (spanElement);

		pageTitle = {type 	  : "pageTitle",
					 spanName : spanName,
					 object	  : null};

		return (this.addNewComponent(pageTitle));
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "addPageTitle", error);
		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_updatePageTitle										*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_updatePageTitle (titleId, titleHEB, titleENG)
{
	oTitle = document.getElementById("theTitle_" + titleId);

	if (oTitle == undefined)
		oTitle = document.getElementById("theSubTitle_" + titleId);


	oTitle.innerHTML = eval("title" + this.theLanguage);
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_addPageSubTitle										*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_addPageSubTitle (subTitleHEB, subTitleENG, width)
{
	try 
	{
		this.checkLanguage ();

		subTitleId	= this.components.length;

		spanName	= "subTitle_" + subTitleId + "_spn";

		spanElement = document.createElement("SPAN");

		spanElement.id 			  = spanName;
		spanElement.style.display = "none";

		var addWidth = "";
		if (width != undefined)
		{
			addWidth = " style='width: " + width + "px' ";
		}

		var subTitleHtml = "<div class='pageSubTitle'" + addWidth + ">"				+
						   " <div class='pageSubTitle_in'>"							+
						   "		<span id='theSubTitle_" + subTitleId + "'>"		+
									eval("subTitle" + this.theLanguage)				+
								"</span>"											+
						   " </div>"												+
						   "</div>";
								
		spanElement.innerHTML = subTitleHtml;

		document.body.appendChild (spanElement);

		pageSubTitle =  {type 	  : "pageSubTitle",
						 spanName : spanName,
						 object	  : null};

		return (this.addNewComponent(pageSubTitle));
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "addPageSubTitle", error);
		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_addTable												*/
/*																		*/
/*	Notice : We support up to 3 tables in page !						*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_addTable ()
{
	try
	{
		this.checkLanguage ();

		this.tableId++;

		if (this.tableId > 6)
			throw new Error(5, "Can not create another table (6 tables in page already)");

		tableName = "table" + this.tableId;
		spanName  = tableName + "_spn";

		table = {type 		: "table",
				 spanName	: spanName,
				 object		: new dynamicTableObj(this.theLanguage, tableName)};

		
		// save the table object - in order to support object methods
		if (this.tableId == 1)
			table1 = table.object;

		if (this.tableId == 2)
			table2 = table.object;

		if (this.tableId == 3)
			table3 = table.object;

		if (this.tableId == 4)
			table4 = table.object;

		if (this.tableId == 5)
			table5 = table.object;

		if (this.tableId == 6)
			table6 = table.object;

		if (this.tableId == 7)
			table7 = table.object;

		if (this.tableId == 8)
			table8 = table.object;

		if (this.tableId == 9)
			table9 = table.object;

		if (this.tableId == 10)
			table10 = table.object;

		return (this.addNewComponent(table));
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "addTable", error);

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_addTableForm											*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_addTableForm ()
{
	try
	{
		this.checkLanguage ();

		var tableId = this.addTable ();

		var tableObj = this.getTableObj (tableId);
		tableObj.withSelectionUtils = false;

		return (tableId);
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "addTableForm", error);

		throw error
	}
}
			
/* -------------------------------------------------------------------- */
/* dynamicPageObj_setLoadFunction										*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_setLoadFunction (tableId, loadFunctionName)
{
	try
	{
		tableObj = this.getTableObj (tableId);
		tableObj.setLoadFunction (loadFunctionName);
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "setLoadFunction", error);

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_setPaintFunction										*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_setPaintFunction (tableId, paintFunctionName)
{
	try
	{
		tableObj = this.getTableObj (tableId);
		tableObj.setPaintFunction (paintFunctionName);
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "setPaintFunction", error);

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_getTableObj											*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_getTableObj (tableId)
{
	try
	{
		if (typeof(tableId) == 'number')
		{
			component = this.getComponent (tableId);

			if (component.type != "table")
				throw new Error(4, "component (" + tableId + ") is not a table");

			return component.object;
		}
		else
		{
			// support tables on forms (the table id is the table object)
			return tableId;
		}
	}
	catch (error)
	{
		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_setTableColumns										*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_setTableColumns (tableId, columns)
{
	try
	{
		tableObj = this.getTableObj (tableId);
		tableObj.setColumns (columns);
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "setTableColumns", error);

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_setTableSort											*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_setTableSort (tableId, theSortBy, theSortDir)
{
	try
	{
		tableObj = this.getTableObj (tableId);
		tableObj.sortBy  = theSortBy;
		tableObj.sortDir = theSortDir;
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "setTableColumns", error);

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_setTableWidth											*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_setTableWidth (tableId)
{
	try
	{
		tableObj = this.getTableObj (tableId);
		return (tableObj.getTableWidth());
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "setTableWidth", error);

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_getTableDivId											*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_getTableDivId (tableId)
{
	try
	{
		tableObj = this.getTableObj (tableId);
		return (tableObj.getTableDivId());
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "getTableDivId", error);

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_setTableHeight										*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_setTableHeight (tableId, heightOver800, heightBelow800)
{
	try
	{
		tableObj = this.getTableObj (tableId);
		tableObj.setTableHeight (heightOver800, heightBelow800);
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "setTableHeight", error);

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_addTableExtension										*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_addTableExtension (tableId, widths, cols)
{
	try
	{
		tableObj = this.getTableObj (tableId);
		tableObj.setExtensionInfo (widths, cols);
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "addTableExtension", error);

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_addTableExtensionBlock								*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_addTableExtensionBlock (tableId, cols)
{
	try
	{
		tableObj = this.getTableObj (tableId);
		return (tableObj.addExtensionBlock (cols));
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "addTableExtensionBlock", error);

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_showTableExtensionBlock								*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_showTableExtensionBlock (tableId, blockId)
{
	try
	{
		tableObj = this.getTableObj (tableId);
		tableObj.showExtensionBlock (blockId);
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "showExtensionBlock", error);

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_hideTableExtensionBlock								*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_hideTableExtensionBlock (tableId, blockId)
{
	try
	{
		tableObj = this.getTableObj (tableId);
		tableObj.hideExtensionBlock (blockId)
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "hideExtensionBlock", error);

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_setTableExtensionBlocksFunction						*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_setTableExtensionBlocksFunction (tableId, functionName)
{
	try
	{
		tableObj = this.getTableObj (tableId);
		tableObj.setExtensionBlocksFunction (functionName);
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "setTableExtensionBlocksFunction", error);

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_setTableXmls											*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_setTableXmls (tableId, responseXml, rowsXmlName, totalXmlName)
{
	try
	{
		tableObj = this.getTableObj (tableId);
		tableObj.setXmls (responseXml, rowsXmlName, totalXmlName);
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "setTableXmls", error);

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_getTableXml											*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_getTableXml (tableId)
{
	try
	{
		tableObj = this.getTableObj (tableId);
		return (tableObj.getXml ());
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "getTableXml", error);

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_setTablePaging										*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_setTablePaging (tableId, responseXml)
{
	try
	{
		tableObj = this.getTableObj (tableId);
		tableObj.setPaging (responseXml);
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "setTablePaging", error);

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_getTablePaging										*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_getTablePaging (tableId)
{
	try
	{
		tableObj = this.getTableObj (tableId);
		return (tableObj.getPaging ());
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "getTablePaging", error);

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_generateTable											*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_generateTable (tableId)
{
	try
	{
		tableObj = this.getTableObj (tableId);
		tableObj.generateTable();
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "generateTable", error);

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_resetTable											*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_resetTable (tableId)
{
	try
	{
		tableObj = this.getTableObj (tableId);
		tableObj.reset();
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "resetTable", error);

		throw error
	}
}
/* -------------------------------------------------------------------- */
/* dynamicPageObj_printTable											*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_printTable (tableId, titleId)
{
	try
	{
		pageObj.generateTable (tableId);

		var component = this.getComponent (tableId);

		var options = {mode : "popup", popWd: 700, popTitle: "IBOS - הדפסה", 
				 	   popClose : "popup", standard : "html5", extraCss : "", retainAttr : ["id","class","style"], 
					   extraHead : '<meta charset="utf-8" />,<meta http-equiv="X-UA-Compatible" content="IE=edge"/>' };

	   	var print = "span#title_" + titleId + "_spn,span#" + component.spanName;
    
	 	$(print).printArea( options );
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "printTable", error);

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_isRowSelected											*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_isRowSelected (tableId)
{
	try
	{
		tableObj = this.getTableObj (tableId);

		return (tableObj.isRowSelected());
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "isRowSelected", error);

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_areRowsSelected										*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_areRowsSelected (tableId)
{
	try
	{
		tableObj = this.getTableObj (tableId);

		return (tableObj.areRowsSelected());
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "isRowSelected", error);

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_setSelectedRow										*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_setSelectedRow (tableId, tagName, value)
{
	try
	{
		tableObj = this.getTableObj (tableId);

		return (tableObj.setSelectedRow(tagName, value));
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "setSelectedRow", error);

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_tableIsMultiSelection									*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_tableIsMultiSelection (tableId)
{
	try
	{
		tableObj = this.getTableObj (tableId);

		return (tableObj.setIsMultiSelection(true));
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "isMultiSelection", error);

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_cancelSelectedRow										*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_cancelSelectedRow (tableId)
{
	try
	{
		tableObj = this.getTableObj (tableId);
		return (tableObj.cancelSelectedRow());
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "cancelSelectedRow", error);

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_getSelectedRowAsXml									*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_getSelectedRowAsXml (tableId)
{
	try
	{
		tableObj = this.getTableObj (tableId);

		return (tableObj.getSelectedRowAsXml());
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "getSelectedRowAsXml", error);

		throw error
	}
}
/* -------------------------------------------------------------------- */
/* dynamicPageObj_getSelectedValueOf									*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_getSelectedValueOf (tableId, tagName)
{
	try
	{
		tableObj = this.getTableObj (tableId);
		return (tableObj.getSelectedValueOf(tagName));
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "getSelectedValueOf", error);

		throw error
	}
}


/* -------------------------------------------------------------------- */
/* dynamicPageObj_getSelectedValuesOf									*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_getSelectedValuesOf (tableId, tagName)
{
	try
	{
		tableObj = this.getTableObj (tableId);
		return (tableObj.getSelectedValuesOf(tagName));
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "getSelectedValuesOf", error);

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_getNumberOfRows										*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_getNumberOfRows (tableId)
{
	try
	{
		tableObj = this.getTableObj (tableId);
		return (tableObj.getNumberOfRows());
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "getNumberOfRows", error);

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_getRowAsXml											*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_getRowAsXml (tableId, rowIndex)
{
	try
	{
		tableObj = this.getTableObj (tableId);
		return (tableObj.getRowAsXml(rowIndex));
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "getRowAsXml", error);

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_getRowValueOf											*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_getRowValueOf (tableId, rowIndex, tagName)
{
	try
	{
		tableObj = this.getTableObj (tableId);
		return (tableObj.getRowValueOf(rowIndex, tagName));
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "getRowValueOf", error);

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_setRowValueOf											*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_setRowValueOf (tableId, rowIndex, tagName, value)
{
	try
	{
		tableObj = this.getTableObj (tableId);
		tableObj.setRowValueOf(rowIndex, tagName, value);
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "setRowValueOf", error);

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_paintRow												*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_paintRow (tableId, rowIndex, which)
{
	try
	{
		tableObj = this.getTableObj (tableId);
		return (tableObj.paintRow(rowIndex, which));
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "paintRow", error);

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_setColImg												*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_setColImg (tableId, rowIndex, colIndex, image)
{
	try
	{
		tableObj = this.getTableObj (tableId);
		tableObj.setColImg(rowIndex, colIndex, image);
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "setColImg", error);

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_getColImg												*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_getColImg (tableId, rowIndex, colIndex)
{
	try
	{
		tableObj = this.getTableObj (tableId);
		return (tableObj.getColImg(rowIndex, colIndex));
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "getColImg", error);

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_fillColSpan											*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_fillColSpan (tableId, rowIndex, colIndex, innerHtml)
{
	try
	{
		tableObj = this.getTableObj (tableId);
		tableObj.fillColSpan (rowIndex, colIndex, innerHtml);
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "fillColSpan", error);

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_updateCol												*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_updateCol (tableId, rowIndex, colIndex, colHtml)
{
	try
	{
		tableObj = this.getTableObj (tableId);
		tableObj.updateCol(rowIndex, colIndex, colHtml);
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "updateCol", error);

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_tableChanged											*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_tableChanged (tableId)
{
	try
	{
		tableObj = this.getTableObj (tableId);
		return (tableObj.tableChanged());
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "tableChanged", error);

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_getReportObj											*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_getReportObj (reportId)
{
	try
	{
		component = this.getComponent (reportId);

		if (component.type != "report")
			throw new Error(4, "component (" + reportId + ") is not a report");

		return component.object;
	}
	catch (error)
	{
		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_addReport												*/
/*																		*/
/*	Notice : We support up to 3 reports in page !						*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_addReport ()
{
	try
	{
		this.checkLanguage ();

		this.reportId++;

		if (this.reportId > 3)
			throw new Error(5, "Can not create another report (3 reports in page already)");

		reportName = "report" + this.reportId;
		spanName   = reportName + "_spn";

		report = {type 		: "report",
				  spanName	: spanName,
				  object	: new dynamicReportObj(this.theLanguage, reportName)};
		
		// save the report object - in order to support object methods
		if (this.reportId == 1)
			report1 = report.object;

		if (this.reportId == 2)
			report2 = report.object;

		if (this.reportId == 3)
			report3 = report.object;

		return (this.addNewComponent(report));
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "addReport", error);

		throw error
	}
}
			
/* -------------------------------------------------------------------- */
/* dynamicPageObj_setReportSection										*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_setReportSection (reportId, headers, line, subTotalLine, totalLine)
{
	try
	{
		reportObj = this.getReportObj (reportId);
		reportObj.setReportSection (headers, line, subTotalLine, totalLine);
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "setReportSection", error);

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_getReportWidth										*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_getReportWidth (reportId)
{
	try
	{
		reportObj = this.getReportObj (reportId);
		return (reportObj.getReportWidth ());
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "getReportWidth", error);

		throw error
	}
}
/* -------------------------------------------------------------------- */
/* dynamicPageObj_generateReport										*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_generateReport (reportId, xml)
{
	try
	{
		reportObj = this.getReportObj (reportId);
		reportObj.generateReport (xml);
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "generateReport", error);

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_addForm												*/
/*																		*/
/*	Notice : We support up to 5 form in page !							*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_addForm ()
{
	try
	{
		this.checkLanguage ();

		if (this.formId > 10)
			throw new Error(5, "Can not create another from (10 forms in page already)");

		this.formId++;

		formName = "form" + this.formId;
		spanName = formName + "_spn";

		form = {type 		: "form",
				name		: formName,
				spanName	: spanName,
				object		: new dynamicFormObj(this.theLanguage, formName)};

		// save the form object - in order to support object methods
		if (this.formId == 1)
			form1 = form.object;

		if (this.formId == 2)
			form2 = form.object;

		if (this.formId == 3)
			form3 = form.object;

		if (this.formId == 4)
			form4 = form.object;

		if (this.formId == 5)
			form5 = form.object;

		if (this.formId == 6)
			form6 = form.object;

		if (this.formId == 7)
			form7 = form.object;

		if (this.formId == 8)
			form8 = form.object;

		if (this.formId == 9)
			form9 = form.object;

		if (this.formId == 10)
			form10 = form.object;

		return (this.addNewComponent(form));
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "addForm", error);

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_getFormName											*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_getFormName (formId)
{
	try
	{
		component = this.getComponent (formId);

		if (component.type != "form")
			throw new Error(3, "component (" + formId + ") is not a form");

		return (component.name);
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "getFormName", error);

		throw error
	}
}
			
/* -------------------------------------------------------------------- */
/* dynamicPageObj_getFormObj											*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_getFormObj (formId)
{
	try
	{
		component = this.getComponent (formId);

		if (component.type != "form")
			throw new Error(3, "component (" + formId + ") is not a form");

		return component.object;
	}
	catch (error)
	{
		throw error;
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_resetForm												*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_resetForm (formId)
{
	try
	{
		formObj	= this.getFormObj (formId);
		formObj.resetForm ();
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "resetForm", error);

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_setFormLanguage										*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_setFormLanguage (formId, lang)
{
	try
	{
		formObj	= this.getFormObj (formId);
		formObj.setFormLanguage (lang);
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "setFormLanguage", error);

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_changeFormLanguage									*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_changeFormLanguage (formId, lang)
{
	try
	{
		formObj	= this.getFormObj (formId);
		formObj.changeFormLanguage (lang);
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "changeFormLanguage", error);

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_addFrame												*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_addFormFrame (formId, textHEB, textENG)
{
	try
	{
		formObj	= this.getFormObj (formId);
		return (formObj.addFrame (textHEB, textENG));
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "addFormFrame", error);

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_addSpecialFrame										*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_addSpecialFrame (formId, textHEB, textENG)
{
	try
	{
		formObj	= this.getFormObj (formId);
		return (formObj.addFrame (textHEB, textENG, true));
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "addFormFrame", error);

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_addSearchFrame										*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_addSearchFrame (formId)
{
	try
	{
		formObj = this.getFormObj (formId);
		return (formObj.addFrame ("מנוע חיפוש", "Report Query"));
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "addSearchFrame", error);

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_addFormFields											*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_addFormFields (formId, frameId, widths, fields)
{
	try
	{
		formObj	= this.getFormObj (formId);
		formObj.addFields (frameId, widths, fields);
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "addFormFields", error);

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_addCollapse											*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_addCollapse (formId, collapseIds, expandIds)
{
	try
	{
		formObj	= this.getFormObj  (formId);

		tablesDivs = new Array();
		for (var i=0; i<expandIds.length; i++)
		{
			tablesDivs.push (this.getTableObj(expandIds[i]).getTableDivId());
		}

		moreCollapseIds = new Array();
		for (var i=0; i<collapseIds.length; i++)
		{
			moreCollapseIds.push (this.getComponent(collapseIds[i]).spanName);
		}
		formObj.addCollapse (tablesDivs, moreCollapseIds);
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "addCollapse", error);

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_isCollapse											*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_isCollapse (formId)
{
	try
	{
		formObj	= this.getFormObj  (formId);

		return (formObj.isCollapse ());
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "isCollapse", error);

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_collapseSection										*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_collapseSection (formId, toCollapse)
{
	try
	{
		formObj	= this.getFormObj  (formId);

		formObj.collapseSection (toCollapse);
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "collapseSection", error);

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_selectTab										*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_selectTab (formId, tab)
{
	try
	{
		formObj	= this.getFormObj  (formId);

		formObj.selectTab (tab);
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "selectTab", error);

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_setFormXml											*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_setFormXml (formId, xml)
{
	try
	{
		formObj	= this.getFormObj (formId);
		formObj.setXml (xml);
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "setFormXml", error);

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_getFormXml											*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_getFormXml (formId)
{
	try
	{
		formObj	= this.getFormObj (formId);
		return (formObj.getXml ());
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "getFormXml", error);

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_getFieldValue											*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_getFieldValue (formId, dataFld)
{
	try
	{
		formObj	= this.getFormObj (formId);
		return (formObj.getFieldValue (dataFld));
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "getFieldValue", error);

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_setFieldValue											*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_setFieldValue (formId, dataFld, value)
{
	try
	{
		formObj	= this.getFormObj (formId);
		return (formObj.setFieldValue (dataFld, value));
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "setFieldValue", error);

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_getOptionText                                         */
/* -------------------------------------------------------------------- */
function dynamicPageObj_getOptionText (formId, dataFld)
{
    try
    {
        formObj = this.getFormObj (formId);
        return (formObj.getOptionText (dataFld));
    }
    catch (error)
    {
        if (this.debug)
            showError ("dynamicPageObj", "getOptionText", error);

        throw error
    }
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_getOptionValueText                                    */
/* -------------------------------------------------------------------- */
function dynamicPageObj_getOptionValueText (formId, dataFld, value)
{
    try
    {
        formObj = this.getFormObj (formId);
        return (formObj.getOptionValueText (dataFld, value));
    }
    catch (error)
    {
        if (this.debug)
            showError ("dynamicPageObj", "getOptionValueText", error);

        throw error
    }
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_setFieldOptions										*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_setFieldOptions (formId, dataFld, options, defaultValue)
{
	try
	{
		formObj	= this.getFormObj (formId);
		return (formObj.setFieldOptions (dataFld, options, defaultValue));
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "setFieldOptions", error);

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_setFieldState											*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_setFieldState (formId, dataFld, state)
{
	try
	{
		formObj	= this.getFormObj (formId);
		formObj.setFieldState     (dataFld, state);
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "setFieldState", error);

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_changeFieldTitle										*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_changeFieldTitle (formId, dataFld, title)
{
	try
	{
		formObj	= this.getFormObj (formId);
		formObj.changeFieldTitle  (dataFld, title);
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "changeFieldTitle", error);

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_setFieldFocus											*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_setFieldFocus (formId, dataFld)
{
	try
	{
		formObj	= this.getFormObj (formId);
		formObj.setFieldFocus     (dataFld);
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "setFieldFocus", error);

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_changeMandatory										*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_changeMandatory (formId, dataFld, mandatory)
{
	try
	{
		formObj	= this.getFormObj (formId);
		formObj.changeMandatory   (dataFld, mandatory);
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "changeMandatory", error);

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_setFieldDisplay										*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_setFieldDisplay (formId, dataFld, display)
{
	try
	{
		formObj	= this.getFormObj (formId);
		formObj.setFieldDisplay   (dataFld, display);
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "setFieldDisplay", error);

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_getFormWidth											*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_getFormWidth (formId)
{
	try
	{
		formObj	= this.getFormObj (formId);
		return (formObj.getFormWidth ());
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "getFormWidth", error);

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_validateForm											*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_validateForm (formId)
{
	try
	{
		formObj = this.getFormObj (formId);
		return (formObj.validate());
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "validateForm", error);

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_setFormAction											*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_setFormAction (formId, formAction, formSubmit)
{
	try
	{
		formObj = this.getFormObj (formId);
		formObj.setFormAction(formAction, formSubmit);
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "setFormAction", error);

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_generateForm											*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_generateForm (formId, tabs)
{
	try
	{
		formObj = this.getFormObj (formId);
		formObj.generateForm(tabs);
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "generateForm", error);

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_emptyFormFields										*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_emptyFormFields (formId)
{
	try
	{
		formObj = this.getFormObj (formId);
		formObj.emptyFormFields();
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "emptyFormFields", error);

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_displayAndSetError									*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_displayAndSetError(formId, fieldName, errorHEB, errorENG)
{
	try
	{
		formObj = this.getFormObj  (formId);
		formObj.displayAndSetError (fieldName, errorHEB, errorENG);
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "displayAndSetError", error);

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_loadTableForm											*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_loadTableForm (formId, fillBy)
{
	try
	{
		formObj = this.getFormObj (formId);
		formObj.loadTableForm (fillBy);
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "loadTableForm", error);

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_tableFormChanged										*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_tableFormChanged (formId)
{
	try
	{
		formObj = this.getFormObj (formId);
		return (formObj.tableFormChanged());
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "tableFormChanged", error);

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_getFormTableId										*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_getFormTableId (formId, frameIndex)
{
	try
	{
		formObj = this.getFormObj (formId);
		frameIndex--;
		return (formObj.getFormTableId(frameIndex));
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "getFormTableId", error);

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_loadFormTable											*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_loadFormTable (formId, tableId, xml)
{
	try
	{
		formObj = this.getFormObj (formId);
		return (formObj.loadFormTable(tableId, xml));
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "loadFormTable", error);

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_getTextWidth											*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_getTextWidth (text)
{
	try
	{
		if (text == undefined)
			return 100;

		width = 0;

		A = 'A';
		Z = 'Z';
		a = 'a';
		z = 'z';
		w = 'w';

		space = ' ';
		
		Acode = A.charCodeAt(0);
		Zcode = Z.charCodeAt(0);
		wcode = w.charCodeAt(0);
		acode = a.charCodeAt(0);
		zcode = z.charCodeAt(0);

		spaceCode = space.charCodeAt(0);

		smallCodes = "";
		smallChars = new Array ("i", "l", "f", "t", "י", "ו", "ן");
		for (var i=0; i < smallChars.length; i++)
			smallCodes += "-" + smallChars[i].charCodeAt(0) + "-";

		mediumCodes = "";
		mediumChars = new Array ("ג", "ז", "נ");
		for (var i=0; i < mediumChars.length; i++)
			mediumCodes += "-" + mediumChars[i].charCodeAt(0) + "-";

		for (var i=0; i < text.length; i++)
		{
			charCode = text.charCodeAt(i);
			
			if (charCode == spaceCode)
				width += 6;

			else if (smallCodes.search ("-" + charCode + "-") != -1)
				width += 4;

			else if (mediumCodes.search ("-" + charCode + "-") != -1)
				width += 7;

			else if ((charCode >= Acode && charCode <= Zcode) || charCode == wcode)
				width += 12;
				
			else if (charCode >= acode && charCode <= zcode)
				width += 9;

			else
				width += 11;
		}

	//	alert ("text : " + text + "(" + width + ")");

		return width;
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "getTextWidth", error);

		throw error
	}
}

var addButton		= {textHEB : "הוספה",		textENG : "Add"			};
var updateButton 	= {textHEB : "עדכון",   	textENG : "Update" 	 	};
var duplicateButton = {textHEB : "שכפול",	   	textENG : "Duplicate" 	};
var saveButton		= {textHEB : "שמירה",		textENG : "Save"		};
var deleteButton	= {textHEB : "מחיקה",   	textENG : "Delete"		};
var excuteButton	= {textHEB : "ביצוע",   	textENG : "Execute"		};
var refreshButton	= {textHEB : "רענון",   	textENG : "Refresh"		};
var reportButton	= {textHEB : "הצגת דוח",   	textENG : "Show"		};
var searchButton	= {textHEB : "חיפוש",   	textENG : "Search"		};
var resetButton		= {textHEB : "נקה",   		textENG : "Reset"		};
var excelButton		= {textHEB : "דוח Excel",   textENG : "Excel"		};
var printButton		= {textHEB : "הדפסה",   	textENG : "Print"		};
var backButton		= {textHEB : "חזרה",	   	textENG : "Back"		};
var closeButton		= {textHEB : "סגירה",	   	textENG : "Close"		};
var showButton		= {textHEB : "הצגה",	   	textENG : "Show"		};
var loadButton		= {textHEB : "טעינת קובץ",	textENG : "Load File"	};
var loadingButton	= {textHEB : "טעינה",		textENG : "Load"		};
var restartButton	= {textHEB : "איתחול",		textENG : "Restart"		};

/* -------------------------------------------------------------------- */
/* dynamicPageObj_getMaxButtonWidth										*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_getMaxButtonWidth (buttons)
{
	try
	{
		this.checkLanguage ();

		maxWidth = 0;

		for (var b=0; b<buttons.length;b++)
		{
			try
			{
				if (buttons[b].type == "" || buttons[b].type == "image" || buttons[b].type == "lang")
					button = buttons[b];
				else
					button = eval(buttons[b].type + "Button");
			}
			catch (e)
			{
				throw new Error(1,"Button '" + buttons[b].type + "' is not supported")	
			}

			if (buttons[b].type == "image")
			{
				width = 20;
			}
			else if (buttons[b].type == "lang")
			{
				width = 0;
			}
			else
			{
				text  = eval("button.text" + this.theLanguage);	

				if (text == undefined)
					text = "[Amir]";

				width = this.getTextWidth (text); 
			}
			
			if (width > maxWidth)
				maxWidth = width;
				
		}

		if (maxWidth < 75)
			maxWidth = 75;

		return maxWidth;
	}
	catch (error)
	{
		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_addRowOfButtons										*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_addRowOfButtons ()
{
	try
	{
		spanName	= "rowOfButtons_" + this.components.length + "_spn";

		spanElement = document.createElement("SPAN");

		spanElement.id 			  = spanName;
		spanElement.style.display = "none";

		document.body.appendChild (spanElement);

		rowOfButtons = {type 			: "rowOfButtons", 
						spanName		: spanName,
						popupButtons	: null,
						popupButtons	: null,
						object			: null};

		return (this.addNewComponent(rowOfButtons));
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "addRowOfButtons", error);

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_generateRowOfButtons									*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_generateRowOfButtons (rowOfButtonsId, buttonsGroups, width, popupButtons)
{
	try
	{
		this.checkLanguage ();

		component = this.getComponent (rowOfButtonsId)

		this.components[rowOfButtonsId].buttonsGroups = buttonsGroups;

		if (popupButtons == undefined)
			popupButtons = buttonsGroups;

		this.components[rowOfButtonsId].popupButtons   = popupButtons;

		// create the buttons
		var btnsHtml  = "<div class='rowOfButtons'>"					+
						"	<table width='" + width + "'>"				+
						"	<tr>";


		for (var g=0; g<buttonsGroups.length; g++)
		{
			buttons = buttonsGroups[g];
			
//			maxWidth = this.getMaxButtonWidth (buttons);

			alignTo = "center";

			if (g == 0)
			{
				if (this.theLanguage == "HEB")
					alignTo = "right";
				else
					alignTo = "left";
			}
			else
			{
				if (g == buttonsGroups.length-1)
				{
					if (this.theLanguage == "HEB")
						alignTo = "left";
					else
						alignTo = "right";
				}
			}
				
			btnsHtml +=		"<td align=" + alignTo + " valign='top'>"	+
								"<table>"								+
								"<tr>"

			for (var b=0; b<buttons.length; b++)
			{
				btnsHtml +=	"		<td align=" + alignTo + " valign='top'>";

				if (buttons[b].type == "" || buttons[b].type == "image" || buttons[b].type == "lang")
					button = buttons[b];
				else
					button = eval(buttons[b].type + "Button");

				if (buttons[b].type == "lang")
				{
					if (buttons[b].lang.length > 1 || buttons[b].mobile)
					{
						var theAction = buttons[b].action;
						theAction = theAction.replace("Lang(", "Lang(event,");

						btnsHtml += "<div class='langBtns'>";
								
						this.langButtons = buttons[b];

						for (var l=0; l<buttons[b].lang.length; l++)
						{
							imgName = buttons[b].lang[l];
							if (l == 0)
								var imgClass = "lang_selected";
							else
								var imgClass = "lang_unselected";
	
							btnsHtml += "<img src='../../designFiles/lang/" + imgName + ".jpg'" 	+ 
										"	  id='" + buttons[b].lang[l] + "'"				+
										"	  class='" + imgClass + "' style='margin-top:1px'"  +
										"	  onclick='" + theAction + "'>&nbsp;";
						}

						if (buttons[b].mobile != undefined && buttons[b].mobile)
						{
							for (var l=0; l<buttons[b].lang.length; l++)
							{
								imgName = buttons[b].lang[l] + "m";
	
								btnsHtml += "<img src='../../designFiles/lang/" + imgName + ".png'" 		+ 
											"	  id='" + buttons[b].lang[l] + "m'"	+
											"	  onclick='" + theAction + "'>&nbsp;";
							}
						}
						btnsHtml +=	"</div>";
					}
				}
				else
				{
					btnType   = "button";
					btnAction = "onclick=\"" + buttons[b].action + "\"";
					
					if (buttons[b].action == "submit")
					{
						theForm	  = "document.body.all." + buttons[b].formName;
						btnType	  = "submit";
						btnAction = "onclick='if(" + theForm + ".fireEvent(\"onsubmit\")) " + theForm + ".submit()'";
					}

					var btnTitle = eval("buttons[b].title" + this.theLanguage)
					if (btnTitle == undefined)
						btnTitle = "";
						
					value = eval("button.text" + this.theLanguage);

					var isLast = "";
					if (g == buttonsGroups.length-1 &&  b == buttons.length-1)
						isLast = " last";

					width = this.getTextWidth (value); 
		
					if (width < 75)	width = 75;

					btnsHtml += "		<div class='btn" + isLast + "' " + btnAction + " title='" + btnTitle + "'"			+
								"			 style='width:" + width + "px'>"												+
								"			<div class='btnText'>" + value + "</div>"										+	
								"		</div>";
				}
								
				btnsHtml +=	"		</td>";
			}

			btnsHtml +=			"</tr></table></td>";
		}
		
		btnsHtml +=			"</tr>"													+
							"</table>"												+
					   "</div>";

		eval("window." + component.spanName).innerHTML = btnsHtml;
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "generateRowOfButtons", error);

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_chooseLang											*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_chooseLang (lang)
{
	try
	{
		for (var l=0; l<this.langButtons.lang.length; l++)
		{
			imgName = this.langButtons.lang[l];

			if (imgName == lang)
				var imgClass = "lang_selected";
			else
				var imgClass = "lang_unselected";

			eval(this.langButtons.lang[l]).className = imgClass;

			// mobile lang buttons
			imgName = this.langButtons.lang[l] + "m";
			if (document.getElementById(imgName) != undefined)
			{
				if (imgName == lang)
					var imgClass = "lang_selected";
				else
					var imgClass = "lang_unselected";

				eval(this.langButtons.lang[l] + "m").className = imgClass;
			}
		}
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "chooseLang", error);

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_addSpan												*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_addSpan ()
{
	try
	{
		spanName	= "span" + this.components.length + "_spn";

		spanElement = document.createElement("SPAN");

		spanElement.id 			  = spanName;
		spanElement.style.display = "none";

		document.body.appendChild (spanElement);

		span = {type 		: "span", 
				spanName	: spanName,
				object		: null};

		return (this.addNewComponent(span));
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "addSpan", error);

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_initSpan												*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_initSpan (spanId, spanHtml)
{
	try
	{
		this.checkLanguage ();

		component = this.getComponent (spanId)

		spanTbl		=  "<table width='100%' border='0' "							+
					   "	   cellspacing='0' cellpadding='0' border='0'>"			+
					   "<tr>"														+
							"<td width='" + this.innerMargin + "'></td>"									+
							"<td>"													+
								spanHtml											+
							"</td>"													+
					   "</tr>"														+
					   "</table>";
								
		eval("window." + component.spanName).innerHTML = spanTbl;
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "initSpan", error);

		throw error
	}
}

/* -------------------------------------------------------------------- */
/* dynamicPageObj_openDebugWindow										*/
/* -------------------------------------------------------------------- */
function dynamicPageObj_openDebugWindow ()
{
	try
	{
		var win = window.open ("debug.html", "", 
							   "location=no,scrollbars=yes,status=yes,menubar=no,resizable=yes,toolbar=no");

		win.document.open  ();
		win.document.write (window.document.body.innerHTML);
		win.document.close ();

		win.focus ();
	}
	catch (error)
	{
		if (this.debug)
			showError ("dynamicPageObj", "openDebugWindow", error);

		throw error
	}
}

