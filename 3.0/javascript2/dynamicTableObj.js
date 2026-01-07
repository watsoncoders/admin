/*----------------------------------------------------------------------*/
/* 																		*/
/*							dynamicTableObj.js							*/
/*							------------------							*/
/*																		*/
/*	Table is array of columns.											*/
/*	Column attributes :													*/
/*	 - textHEB			: header hebrew text							*/
/*	 - textENG			: header english text							*/
/*	 - widthHEB			: column hebrew width							*/
/*	 - widthENG			: column english width							*/
/*	 - sortType			: text | number | date (in dd-mm-yy[yy] format) */
/*	 - sortDir			: initial sort direction						*/
/*	 - xmlTag			: xml binding tag name							*/
/*																		*/
/*----------------------------------------------------------------------*/


/*----------------------------------------------------------------------*/
/* dynamicTableObj constructor											*/
/*----------------------------------------------------------------------*/
function dynamicTableObj (theLanguage, tableName) 
{
	// data memebers
	// -----------------------------------------------------------------

	this.objectName				= null;					// the object name
	this.destinationSpanName	= null;					// the destination span name of the table
	this.theLanguage			= theLanguage;			// the language
	
	this.direction				= "rtl";				// the direction (for styling)

	this.addLang				= "";
	
	this.emptyMsg				= "אין נתונים מתאימים";

	if (this.theLanguage == "ENG") 
	{
		this.direction  = "ltr"; 						// in english it is "ltr"
		this.emptyMsg	= "No Data Found";
		this.addLang	= "_ENG";
	}

	this.tableInForm			= false;

	this.dataTblId 				= tableName + "_dataTbl";	// the name for the data table
	this.sortTblId 				= tableName + "_sortTbl";	// the basic name for the sort table 
	this.dataDivId				= tableName + "_dataDiv";	// the name of the div data

	this.popupDivId				= tableName + "_popupDiv";	// the name of the popup div

	this.emptyTblId 			= tableName + "_emptyTbl";	// the name for the empty data table

	
	// create the xml element
	this.dataSrc				= tableName + "_xmlData";	
	this.totalSrc				= tableName + "_xmlTotal";
	this.rowSrc					= tableName + "_rowData";

	this.rowEmptyXml			= "";						// support binded text field

	this.xmlNode				= undefined;				// the xml node to be binded in the table
	this.xmlTopNodeName			= "";						// the xml top node - for sorting needs
	this.xmlEntryNodeName 		= "";						// an entry node name - for sorting needs
	this.xmlTotalNode			= undefined;				// for total table

	this.tableWidth				= 0;						// the table width

	this.divWidth				= "";						// div width - calculate
	this.divHeight				= "350";					// div height - to set the scroll height

	this.scrollWidth			= "";						// scroll width depends on the client
														// so we calc it on the constructor

	this.realWidth				= 0;						// global table width

	// paging
	this.loadFunction			= "loadReport";				// load function for paging
	this.withPaging				= false;
	this.pagingTblId			= tableName + "_pagingTbl";	// the name of the paging table
	this.fileName				= "";
	this.pageNumber				= "1";
	this.pagesAmount			= "";
	this.rowsTotal				= "";
	this.sortDir				= "";
	this.sortBy					= "";

	// try to learn div scroll width
	// -----------------------------------------------------------------

    var outer = document.createElement("div");
    outer.style.visibility 	= "hidden";
    outer.style.width 		= "100px";
    document.body.appendChild(outer);

    var widthNoScroll = outer.offsetWidth;
    // force scrollbars
    outer.style.overflow = "scroll";

    // add innerdiv
    var inner = document.createElement("div");
    inner.style.width = "100%";
    outer.appendChild(inner);        

    var widthWithScroll = inner.offsetWidth;

    // remove divs
    outer.parentNode.removeChild(outer);

    this.scrollWidth = widthNoScroll - widthWithScroll;
	
	// -----------------------------------------------------------------

	// the object arrays
	this.moreTblId				= tableName + "_moreTbl";	// the name of the more data table
	this.columns				= null;
	this.moreDataWidths     	= null;
	this.moreDataCols       	= null;
	this.moreDataBlocks			= new Array();				// for handling dynamic more data blocks
	this.moreDataEmptyTrs		= new Array();

	this.handleExtensionBlocksFunction = "";	
															// for after set xml data

	// mouse paiting (line coloring on mouse movement and click)
	this.notSelectedColor 		= "#ffffff";				// the color of a not selected line
	this.selectedColor			= "#cedc8c";				// the color of a selected line
	this.mouseOnColor			= "#e9f0f6";				// the color when the mouse is over
	this.currSelectedRows		= new Array();				// the currently selected rows
	this.timeToPaint			= false;					// sets the paint function to work
	this.rowWasPainted			= false;					// determines if the row was 
															// selected before (used for the painting
															// funtcions.

	this.withSelectionUtils		= true;						// add onmouseover/out events on table row
	this.isMultiSelection		= false;

	// table sort variables
	this.currSortIndex 			= 1;						// the current sort index (in the headers)
	this.paintFunction			= "";

	this.manualResetFields  	= new Array();              // for reset form method

	// methods
	// -----------------------------------------------------------------

	// initialization methods :
	// ----------------------

	this.setColumns					= dynamicTableObj_setColumns;
	this.setExtensionInfo			= dynamicTableObj_setExtensionInfo;

	// setting the object xml
	this.setXmls					= dynamicTableObj_setXmls;

	this.getXml						= dynamicTableObj_getXml;

	// setting the div height 
	this.setTableHeight				= dynamicTableObj_setTableHeight;

	// init methods
	this.setObjectName				= dynamicTableObj_setObjectName;
	this.setLoadFunction			= dynamicTableObj_setLoadFunction;
	this.setPaintFunction			= dynamicTableObj_setPaintFunction;
	this.createDestinationSpan		= dynamicTableObj_createDestinationSpan ;


	// generating the table html methods :
	// ---------------------------------

	// generating the sort table html (top blue table with sort buttons)
	this.generateSortHtmlStr		= dynamicTableObj_generateSortHtmlStr;

	// generating the data table html (gray)
	this.generateDataHtmlStr		= dynamicTableObj_generateDataHtmlStr;

	// generating the total table html (dark gray)
	this.generateTotalHtmlStr		= dynamicTableObj_generateTotalHtmlStr;

	// generating the table html
	this.generateTableHtmlStr		= dynamicTableObj_generateTableHtmlStr;

	// generating the paging table html
	this.generatePagingHtmlStr		= dynamicTableObj_generatePagingHtmlStr;

	// generate popup div
	this.generatePopupHtmlStr		= dynamicTableObj_generatePopupHtmlStr;

	// generating the paging table html
	this.generateMoreDataHtmlStr	= dynamicTableObj_generateMoreDataHtmlStr;
	this.generateMoreDataBlock		= dynamicTableObj_generateMoreDataBlock;
	this.addExtensionBlock			= dynamicTableObj_addExtensionBlock;
	this.showExtensionBlock			= dynamicTableObj_showExtensionBlock;
	this.hideExtensionBlock			= dynamicTableObj_hideExtensionBlock;
	this.hideAllExtensionBlocks		= dynamicTableObj_hideAllExtensionBlocks;
	this.setExtensionBlockDisplay	= dynamicTableObj_setExtensionBlockDisplay;
	this.setExtensionBlocksFunction	= dynamicTableObj_setExtensionBlocksFunction;

	// generating the table (including the binding)
	this.generateTable				= dynamicTableObj_generateTable;

	// generating print tables & styles for print method
	this.generatePrintStyles		= dynamicTableObj_generatePrintStyles;
	this.generatePrintJsCode		= dynamicTableObj_generatePrintJsCode;
	this.generatePrintHtmlTitle		= dynamicTableObj_generatePrintHtmlTitle;
	this.generatePrintHtmlHeaders	= dynamicTableObj_generatePrintHtmlHeaders;
	this.generatePrintHtmlData		= dynamicTableObj_generatePrintHtmlData;

	// handle select row
	this.paintSelectedRow			= dynamicTableObj_paintSelectedRow;
	this.cancelSelectedRow			= dynamicTableObj_cancelSelectedRow;
	this.mousePaint					= dynamicTableObj_mousePaint;
	this.getSelectedRow 			= dynamicTableObj_getSelectedRow;
	this.getSelectedRows 			= dynamicTableObj_getSelectedRows;
	this.setSelectedRow			    = dynamicTableObj_setSelectedRow;
	this.getSelectedRowAsXml 		= dynamicTableObj_getSelectedRowAsXml;
	this.getSelectedValueOf			= dynamicTableObj_getSelectedValueOf;
	this.getSelectedValuesOf		= dynamicTableObj_getSelectedValuesOf;
	this.isRowSelected				= dynamicTableObj_isRowSelected;
	this.areRowsSelected			= dynamicTableObj_areRowsSelected;
	this.setIsMultiSelection		= dynamicTableObj_setIsMultiSelection;
	this.checkAll					= dynamicTableObj_checkAll;
	this.uncheckAll					= dynamicTableObj_uncheckAll;

	this.showRowPopup				= dynamicTableObj_showRowPopup;
	this.hideRowPopup				= dynamicTableObj_hideRowPopup;

	this.tableSort					= dynamicTableObj_tableSort;

	this.setXmlData					= dynamicTableObj_setXmlData;

	this.getTableWidth				= dynamicTableObj_getTableWidth;

	this.getTableDivId				= dynamicTableObj_getTableDivId;

	this.reset						= dynamicTableObj_reset;

	this.tableChanged				= dynamicTableObj_tableChanged;

	// paging
	this.getPaging					= dynamicTableObj_getPaging;
	this.setPaging					= dynamicTableObj_setPaging;
	this.resetPaging				= dynamicTableObj_resetPaging;
	this.gotoPage					= dynamicTableObj_gotoPage;
	this.nextPage					= dynamicTableObj_nextPage;
	this.prevPage					= dynamicTableObj_prevPage;
	this.jumpToPage					= dynamicTableObj_jumpToPage;
	this.updatePaging				= dynamicTableObj_updatePaging;

	// general
	this.getRowAsXml 				= dynamicTableObj_getRowAsXml;
	this.getRowValueOf				= dynamicTableObj_getRowValueOf;
	this.setRowValueOf				= dynamicTableObj_setRowValueOf;
	this.getNumberOfRows			= dynamicTableObj_getNumberOfRows;

	// support this painting : regular, choosen
	this.paintRow					= dynamicTableObj_paintRow;

	this.setColImg					= dynamicTableObj_setColImg;
	this.getColImg					= dynamicTableObj_getColImg;

	this.fillColSpan				= dynamicTableObj_fillColSpan;

	this.updateCol					= dynamicTableObj_updateCol;

	// print
	this.print						= dynamicTableObj_print;
	
	// for debug
	this.checkLanguage				= dynamicTableObj_checkLanguage;

	// init to the object : 1) set object name  2) create destination span
	this.setObjectName 			(tableName);

	if (tableName.indexOf ("tableInForm") != -1)
	{
		this.tableInForm = true;
	}
	else
	{
		this.tableInForm = false;
		this.createDestinationSpan 	();
	}
}

/* -------------------------------------------------------------------- */
/* dynamicTableObj_checkLanguage										*/
/* -------------------------------------------------------------------- */
function dynamicTableObj_checkLanguage ()
{
	if (this.theLanguage != "HEB" && this.theLanguage != "ENG")
		throw new Error(1,"no table language - check table constructor");
}

/*----------------------------------------------------------------------*/
/* Function: 	dynamicTableObj_setObjectName							*/
/* Description: setting the object name									*/
/* Params: 		the object name											*/
/*----------------------------------------------------------------------*/
function dynamicTableObj_setObjectName (objectName) 
{
	this.objectName = objectName;
}

/*----------------------------------------------------------------------*/
/* Function: 	dynamicTableObj_createDestinationSpan					*/
/* Description: creating the destination span name						*/
/* Params: 		the span name											*/
/*----------------------------------------------------------------------*/
function dynamicTableObj_createDestinationSpan () 
{
  try
  {
	this.destinationSpanName = this.objectName + "_spn";

	spanElement = document.createElement("SPAN");

	spanElement.id = this.destinationSpanName;

	spanElement.style.display = "none";

	document.body.appendChild (spanElement);
  }
  catch (error)
  {
 	throw new Error(10, error.description + "\n\t(dynamicTableObj.createDestinationSpan)");
  }
}

/*----------------------------------------------------------------------*/
/* Function: 	dynamicTableObj_setColumns								*/
/* Description: setting the columns info.								*/
/* Params: 		array of structures with :								*/
/*				textHEB, widthHEB, textENG, widthENG, xmlTag ,			*/
/*				sortType & sortDir 										*/
/*----------------------------------------------------------------------*/
function dynamicTableObj_setColumns (columns) 
{
  try
  {
  	this.columns = new Object(columns);
  }
  catch (error)
  {
 	throw new Error(10, error.description + "\n\t(dynamicTableObj.setColumns)");
  }
}

/*----------------------------------------------------------------------*/
/* dynamicTableObj_setExtensionInfo										*/
/*----------------------------------------------------------------------*/
function dynamicTableObj_setExtensionInfo (widths, cols)
{
  try
  {
	this.moreDataWidths = new Object(widths);
	this.moreDataCols   = new Object(cols);
  }
  catch (error)
  {
 	throw new Error(10, error.description + "\n\t(dynamicTableObj.setExtensionInfo)");
  }
}

/*----------------------------------------------------------------------*/
/* dynamicTableObj_addExtensionBlock									*/
/*----------------------------------------------------------------------*/
function dynamicTableObj_addExtensionBlock (cols)
{
  try
  {
	// save block data
	var newBlock = {blockId 		: this.moreDataBlocks.length,
					blockCols		: cols,
					blockRowsIds	: new Array(),
					blockEmptyIds	: new Array()}

	return (this.moreDataBlocks.push (newBlock)-1);
  }
  catch (error)
  {
 	throw new Error(10, error.description + "\n\t(dynamicTableObj.addExtensionBlock)");
  }
}

/*----------------------------------------------------------------------*/
/* dynamicTableObj_showExtensionBlock									*/
/*----------------------------------------------------------------------*/
function dynamicTableObj_showExtensionBlock (blockId)
{
  try
  {
  	this.setExtensionBlockDisplay (blockId, "");
  }
  catch (error)
  {
 	throw new Error(10, error.description + "\n\t(dynamicTableObj.showExtensionBlock)");
  }
}

/*----------------------------------------------------------------------*/
/* dynamicTableObj_hideAllExtensionBlocks								*/
/*----------------------------------------------------------------------*/
function dynamicTableObj_hideAllExtensionBlocks ()
{
  try
  {
	for (var i = 0; i < this.moreDataBlocks.length; i++)
	{
		this.hideExtensionBlock (i);
	}
  }
  catch (error)
  {
 	throw new Error(10, error.description + "\n\t(dynamicTableObj.hideAllExtensionBlocks)");
  }
}

/*----------------------------------------------------------------------*/
/* dynamicTableObj_hideExtensionBlock									*/
/*----------------------------------------------------------------------*/
function dynamicTableObj_hideExtensionBlock (blockId)
{
  try
  {
  	this.setExtensionBlockDisplay (blockId, "none");
  }
  catch (error)
  {
 	throw new Error(10, error.description + "\n\t(dynamicTableObj.hideExtensionBlock)");
  }
}

/*----------------------------------------------------------------------*/
/* dynamicTableObj_setExtensionBlockDisplay								*/
/*----------------------------------------------------------------------*/
function dynamicTableObj_setExtensionBlockDisplay (blockId, display)
{
  try
  {
  	if (blockId > this.moreDataBlocks.length)
		throw new Error(10, "Extension block (id-" + blockId + ") does not exist");
		
	block = this.moreDataBlocks[blockId];

	if (display == "none")
		emptyDisplay = "";
	else
		emptyDisplay = "none";

	// run over all block rows
	for (var r = 0; r < block.blockRowsIds.length; r++)
	{
		rowId  = block.blockRowsIds[r];
		eval("document.all." + rowId).style.display = display;

		emptyRowId = block.blockEmptyIds[r];
		eval("document.all." + emptyRowId).style.display = emptyDisplay;
  	}
  }
  catch (error)
  {
 	throw new Error(10, error.description + "\n\t(dynamicTableObj.setExtensionBlockDisplay)");
  }
}

/*----------------------------------------------------------------------*/
/* dynamicTableObj_setExtensionBlocksFunction							*/
/*----------------------------------------------------------------------*/
function dynamicTableObj_setExtensionBlocksFunction (functionName)
{
  try
  {
  	this.handleExtensionBlocksFunction = functionName;
  }
  catch (error)
  {
 	throw new Error(10, error.description + "\n\t(dynamicTableObj.etExtensionBlocksFunction");
  }
}
/*----------------------------------------------------------------------*/
/* Function: 	dynamicTableObj_generateSortHtmlStr						*/
/* Description: generating the html for the sort table					*/
/*----------------------------------------------------------------------*/
function dynamicTableObj_generateSortHtmlStr () 
{
  try
  {
  	this.checkLanguage ();

	// adding the sort table

	htmlStr = 	"<table id='" + this.sortTblId + "' class='dataTbl_header' "		+
				" 		style='direction:" + this.direction + "'>" 					+
				"<tr>";
				
	// creating the TDs inside the header row
	this.divWidth   = 0;
	this.tableWidth = 0;
	var headerWidth;
	var displaySort = "";
	var sortImg		= "";

	for (var i=0; i<this.columns.length; i++) 
	{
		sortImg = "../../designFiles/sortUp.png";

		if ((this.columns[i].xmlTag == this.sortBy || (this.sortBy == "" && i == 0)) && this.columns[i].sortType != undefined)
		{
			this.currSortIndex = i;

			displaySort = "";

			if (this.sortDir == "asc")
				sortImg = "../../designFiles/sortUp.png";
			else
				sortImg = "../../designFiles/sortDown.png";
		}
		else
		{
			displaySort = "hidden";
		}

		width = eval("this.columns[i].width" + this.theLanguage);
		this.divWidth += width*1;

		headerText = eval("this.columns[i].text" + this.theLanguage);
		if (headerText == undefined)
			headerText = "";
			
		var margin = "";

		if (i == 0)
		{
			if (this.theLanguage == "HEB")
			{
				margin = " style='margin-right:" + this.scrollWidth + "'";
				headerWidth = width*1 + this.scrollWidth + 1;
			}
			else
			{
				margin = " style='margin-left:" + this.scrollWidth + "'";
				headerWidth = width*1 + this.scrollWidth;
			}
		}
		else
		{
			headerWidth = width;
		}

		/*
		if ((i == 0 					&& this.theLanguage == "HEB") ||
			(i == this.columns.length-1 && this.theLanguage == "ENG"))
		{
			if (this.theLanguage == "HEB")
			{
				margin = " style='margin-right:" + this.scrollWidth + "'";
				headerWidth = width*1 + this.scrollWidth + 1;
			}
			else
			{
				margin = " style='margin-left:" + this.scrollWidth + "'";
				headerWidth = width*1 + this.scrollWidth;
			}

		}
		*/
/*		else if ((i == this.columns.length-1 && this.theLanguage == "HEB") ||
				 (i == 0 					 && this.theLanguage == "ENG"))
		{
			if (this.theLanguage == "HEB")
				headerWidth = width*1 - 10;
			else
				headerWidth = width*1 - 12;
		}
*/

		this.tableWidth += headerWidth*1;
		
		// with sort ?
		if (this.columns[i].sortType == undefined)
		{
			var onclick   = "";
			var style	  = "";
		}
		else
		{
			var onclick   = this.objectName + ".tableSort(" + i + ")";
			var style	  = "cursor:pointer";
		}
		

		var firstOrLast = ""
		if (i == 0)
			firstOrLast = " first";
		else if (i == this.columns.length - 1)
			firstOrLast = " last"

		htmlStr +=  "<td width='" + headerWidth + "' style='" + style + "' onclick='" + onclick + "' "	+
					"	 class='headerCol" + firstOrLast + "'>"											+
						"<div class='headerText'" + margin + ">"										+
							headerText + "&nbsp;"														+
							"<img src='" + sortImg + "' style='visibility:" + displaySort + "' />"		+
					"	</div>"																			+
					"</td>";
	}
	
	htmlStr += "</tr></table>";

	return htmlStr
  }
  catch (error)
  {
 	throw new Error(10, error.description + "\n\t(dynamicTableObj.generateSortHtmlStr)");
  }
}

/*----------------------------------------------------------------------*/
/* Function: 	dynamicTableObj_generateTotalHtmlStr					*/
/* Description: generating the html for the total table					*/
/*----------------------------------------------------------------------*/
function dynamicTableObj_generateTotalHtmlStr () 
{
  try
  {
  	this.checkLanguage ();

	htmlStr = 	"<table class='dataTbl_footer'>";
				
	var totalPadding = "style='padding-";

	if (this.theLanguage == "HEB")
		totalPadding += "right";
	else
		totalPadding += "left";

	totalPadding += ":" + (this.scrollWidth + 5) + "px'";

	var width = this.getTableWidth() + 1;

	htmlStr   += "<tr>"																				+
			 		"<td width='" + width + "'>"													+
						"<table width='100%'>"														+
						"<tr>"																		+
							"<td>"																	+
								"<div class='dataTbl_footer_totalText' " + totalPadding + ">";

	
	if (this.theLanguage == "HEB")
	{
		htmlStr += 		"סה\"כ: ";
	}
	else
	{
		htmlStr +=		"Total: ";
	}

	var totalText	= "";
	
	if (this.xmlTotalNode != null)
		totalText = commonGetInnerData (this.xmlTotalNode, "totalText");

	htmlStr +=						"<span id='total_spn' dir='" + this.direction + "'>"			+
										totalText													+
									"</span>"														+
								"</div>"															+
							"</td>";
					
	if (this.isMultiSelection)
	{
		htmlStr += 			"<td style='width:70px'>"												+
							" <div style='padding-right:10px'>"	+
							"	<img src='../../designFiles/iconCheckAll.png' title='סמן הכל' "		+
							"		 onclick=\"" + this.objectName + ".checkAll();\" "				+
							"		 style='cursor:pointer' />"										+
							"	&nbsp;"																+
							"	<img src='../../designFiles/iconUncheckAll.png' title='בטל הכל' "	+
							"		 onclick=\"" + this.objectName + ".uncheckAll();\" "			+
							"		 style='cursor:pointer' />"										+
							" </div>"																+
						  "</td>";
	}

	if (this.withPaging)
		htmlStr +=			"<td>"																	+
								this.generatePagingHtmlStr()										+	
							"</td>";		

	htmlStr +=			"</tr>"																		+
						"</table>"																	+
					"</td>"																			+
				"</tr>"																				+
				"</table>";

	return htmlStr;
  }
  catch (error)
  {
 	throw new Error(10, error.description + "\n\t(dynamicTableObj.generateTotalHtmlStr)");
  }
}

/*----------------------------------------------------------------------*/
/* Function: 	dynamicTableObj_generateDataHtmlStr						*/
/* Description: generating the html for the data table (gray)			*/
/*----------------------------------------------------------------------*/
function dynamicTableObj_generateDataHtmlStr() 
{
  try
  {
  	this.checkLanguage ();

	var htmlStr = "";
	
	emptyColor = " style='color:white'";

	if (this.moreDataCols != null)
	{
		// [05.04.2013 added proudly by Amir] clean More Data area from previous enterance
		window.setTimeout(this.objectName+".setXmlData()", 50);
	}

	if (this.xmlNode == null || this.xmlNode.childNodes.length == 0)
	{
		displayEmpty	= "";
		displayData		= "none";
	}
	else
	{
		displayEmpty	= "none";
		displayData		= "";
	}

	htmlStr += 	"<div class='dataScroll' id='" + this.dataDivId + "'"								+
				"     style='height:" + this.divHeight + "'"										+
				"		onmouseout='" + this.objectName + ".hideRowPopup()'>"						+
				"<table id='" + this.emptyTblId + "' style='display:" + displayEmpty + "' "			+
				"		width='100%' height='100%'>" 												+
				"<tr align='center' valign='middle'>"												+
				"	<td class='styleInfo'" + emptyColor + ">" + this.emptyMsg + "</td>"				+
				"<tr>"																				+
				"</table>"																			+
				"<table id='" + this.dataTblId + "' class='dataTbl'"								+
				" 		style='direction:"+this.direction+";display:" + displayData + "'> ";
	
	// build th for sort
	// ---------------------------------------------------------------------------------------------
	htmlStr	+= "<thead><tr style='display:none'>";
	for (var i=0; i<this.columns.length; i++) 
	{
		var sortBy = "string";

		switch (this.columns[i].sortType)
		{
			case "text"		: sortBy = "string";	break
			case "number"	: sortBy = "int";		break;
			case "date"		: sortBy = "date";		break;
		}

		this.columns[i].defaultSortDir = this.columns[i].sortDir;

		if (this.currSortIndex == i)
		{
			if (this.columns[i].sortDir == "ascending")
				var defaultDir = "desc";
			else
				var defaultDir = "asc";
		}
		else
		{
			if (this.columns[i].sortDir == "ascending")
				var defaultDir = "asc";
			else
				var defaultDir = "desc";
		}

		htmlStr += "<th data-sort='" + sortBy + "' data-sort-default='" + defaultDir + "'></th>";
	}
	htmlStr	+= "</tr></thead><tbody>";

	// build table rows
	// ---------------------------------------------------------------------------------------------
	
	for (r=0; this.xmlNode != null && r < this.xmlNode.childNodes.length; r++)
	{
		currNode    = this.xmlNode.childNodes[r];

		htmlStr += "<tr class='dataTbl_row' xml-index='" + r + "' ";
				
		if (this.withSelectionUtils)
		{
			htmlStr +=	" onmouseover= '" + this.objectName + ".mousePaint(this, true)' " 			+
						" onmouseout = '" + this.objectName + ".mousePaint(this, false)' ";
		}

		if (this.moreDataCols != null)
		{
			htmlStr	+= " onclick= '" + this.objectName + ".setXmlData();'";
		}

		htmlStr += ">";

		var action;
		var showPopupAction;
		var onmouseoverAction;
		var setStyle;
		var title;
		var addTitle;
		var color;

		// creating the row TDs
		for (var i=0; i < this.columns.length; i++) 
		{
			width = eval("this.columns[i].width" + this.theLanguage);
		
			// @action - when there is a specific action - don't paint row !
			action = this.columns[i].action;
			if (action == undefined && this.withSelectionUtils)
			{
				action = this.objectName + ".paintSelectedRow(this.parentNode);";
			}

			if (this.withSelectionUtils)
			{
				showPopupAction = "return " + this.objectName + ".showRowPopup(event, this.parentNode, \"" + this.objectName + "\");"
			}

			var onmouseoverAction = "";
			var onmouseover = this.columns[i].onmouseover;
			if (onmouseover != undefined)
				onmouseoverAction = " onmouseover='" + onmouseover + "'";
	
			var onclickAction = "";
			var onclick = this.columns[i].onclick;
			if (onclick != undefined)
				action += onclick;

			changeClass = "";
			if (this.columns[i].className != undefined && this.columns[i].className != "")
				changeClass = "class='" + this.columns[i].className + "'";
		
			tdDir = this.direction;
			if (this.columns[i].dir != undefined)
			{
				tdDir = this.columns[i].dir;
			}
			tdDir = "direction:" + tdDir;

			var firstOrLast = ""
			if (i == 0)
				firstOrLast = " first";
			else if (i == this.columns.length - 1)
				firstOrLast = " last"


			htmlStr +=  "<td width='" + width + "'" 	+
						"	 class='dataTbl_col" + firstOrLast + "'"	+
						"	 onclick='" + action + "'"	+
						"	 oncontextmenu='" + showPopupAction + "'"	+
							 onmouseoverAction 			+
							 onclickAction				+
					 	" >";

			if (i == 0 && this.theLanguage == "ENG")
				htmlStr += "&nbsp;&nbsp;&nbsp;";

			// handle special definitions
			// -------------------------------------------------------------

			setStyle = "";

			// @title
			title    = eval("this.columns[i].title" + this.theLanguage);
			addTitle = "";

			if (title != undefined)
			{
				addTitle = " title='" + title + "' "; 
			}

			// @action
			action = this.columns[i].action;
	
			if (action != undefined)
			{
				setStyle  += " cursor:pointer;";
	
				if (this.columns[i].img == undefined)
					setStyle += "text-decoration:underline;";
			}
		
			// @color
			color    = this.columns[i].color;
			if (color != undefined)
			{
				setStyle += "color:" + color + ";";
			}
		
			// -------------------------------------------------------------
			if (this.columns[i].img != undefined)
			{
				if (this.columns[i].img == "")
				{
					setStyle += "display:none";
				}
				else
					setStyle += "display:";

				htmlStr += "<img src='" + this.columns[i].img + "' border='0' valign='top' "			+
								 addTitle + "style='" + setStyle + ";margin-top:0px'>";
								 //addTitle + "style='" + setStyle + ";margin-top:-10px'>";
			}
			else if (this.columns[i].lang != undefined)
			{
				htmlStr += "<span width='" + width + "'></span>";	
			}
			else if (this.columns[i].field != undefined)
			{
				this.manualResetFields.push (this.columns[i].xmlTag);
				
				switch (this.columns[i].field.type)
				{
					case "text"		: htmlStr += "<input type='text' class='styleInput' "				+
							  					 "	   dataFld='" + this.columns[i].xmlTag + "'>"     	+
							   					 "</input>";
								  	  break;
	
					case "checkbox"	: htmlStr += "<input type='checkbox' "								+
												 "		 dataFld='" + this.columns[i].xmlTag + "'>"		+
							   					 "</input>";
								  	  break;
				}
			}
			else
			{
				colValue	= commonGetInnerData (currNode, this.columns[i].xmlTag);

																											//  -20 for the div padding
				htmlStr +=  "<div class='data' " + addTitle + "style='" + setStyle + ";" + tdDir + ";width:" + (width-20) + "px;' " + changeClass+">" +
								colValue +
							"</div>";
				
//				htmlStr +=  "<span dataFld='" + this.columns[i].xmlTag + "' "							+
			}

			htmlStr +=	"</td>";
		}

		htmlStr +=  "</tr>";
	}

	htmlStr +=  "</tbody></table></div>"	

	return htmlStr;
  }
  catch (error)
  {
 	throw new Error(10, error.description + "\n\t(dynamicTableObj.generateDataHtmlStr)");
  }
}

/*----------------------------------------------------------------------*/
/* Function: 	dynamicTableObj_generateTableHtmlStr					*/
/* Description: generating the full table html 							*/
/*----------------------------------------------------------------------*/
function dynamicTableObj_generateTableHtmlStr() 
{
  try
  {
	var sortHtml  = this.generateSortHtmlStr ();
	var dataHtml  = this.generateDataHtmlStr ();
	var totalHtml = this.generateTotalHtmlStr();
	
	dataHtml = 		"<div id='" + this.dataDivId + "_container" + "'>"	+ dataHtml + "</div>";

	var htmlStr = sortHtml + dataHtml + totalHtml;

	htmlStr += this.generatePopupHtmlStr ();

	return htmlStr;
	
  }
  catch (error)
  {
 	throw new Error(10, error.description + "\n\t(dynamicTableObj.generateTableHtmlStr)");
  }
}

/*----------------------------------------------------------------------*/
/* Function:	dynamicTableObj_generatePagingHtmlStr					*/
/* Description:	generating paging table html							*/
/*----------------------------------------------------------------------*/
function dynamicTableObj_generatePagingHtmlStr ()
{
  try
  {
  	this.checkLanguage ();

	// calculate paging table width
	pagingTblWidth = this.tableWidth + (this.columns.length-2) + 10;
	pagingTblWidth = "100%";

	if (this.theLanguage == "HEB")
	{
		alignTo 		= "left";

		lastPageTitle 	= "דף אחרון";
		nextPageTitle	= "דף הבא";
		prevPageTitle	= "דף קודם";
		firstPageTitle  = "דף ראשון";
		jumpToPageTitle	= "קפוץ לדף";
	}
	else
	{
		alignTo = "right";

		lastPageTitle 	= "Last Page";
		nextPageTitle	= "Next Page";
		prevPageTitle	= "Previous Page";
		firstPageTitle  = "First Page";
		jumpToPageTitle	= "Go to Page";
	}	
	
	var htmlStr = 	
		"<form id='pagingForm' style='display:inline;padding:0px;'>"								+
		"<table width='100%'>"																		+
		"<tr>"																						+
			"<td valign='top'>"																		+
				"<table width=" + pagingTblWidth + " id='" + this.pagingTblId + "' "				+ 
				" 		style='direction:rtl'>"														+
				"<tr>"																				+
					"<td align='" + alignTo + "' width='100%'>"										+
						"<table class='pagingIconsTbl' id='" 										+ 
								this.pagingTblId + "_pagingIconsTbl'>"								+
						"<tr>"																		+
							"<td valign='top'>"														+
							"  <input type='button' id='" + this.pagingTblId + "_last' "			+
							"		  class='pagingIcon' "											+
							"	onmouseover=\"this.title='" + lastPageTitle + "'\"" 				+
							"	onClick=\"" + this.objectName + ".gotoPage"							+
							"					(" + this.objectName + ".pagesAmount);\"> "			+
							"  <input type='button' id='" + this.pagingTblId + "_next' "			+
							"		  class='pagingIcon' " 											+
							"	onmouseover=\"this.title='" + nextPageTitle + "';\""				+
							"	onClick=\"" + this.objectName + ".nextPage('');\"> "				+
							"  <input type='button' id='" + this.pagingTblId + "_play'" 			+
							"		  class='pagingIcon' "              							+
							"      onmouseover=\"this.title='" + jumpToPageTitle + "'\"" 		 	+
							"	onClick=\"" + this.objectName + ".jumpToPage();\">"					+
							"</td>"																	+
							"<td id='" + this.pagingTblId + "_pageFieldsTd' valign='top'>"			+
							"  <div class='pagingInputs'>"											+
							"	<input type='text' class='pagingInput' "							+
							"		   id='" + this.pagingTblId + "_page' />"						+
							"	<input type='text' class='pagingInput' " 							+
							"		   id='" + this.pagingTblId + "_totalPage' readonly>"			+
							"  </div>"																+
							"</td>"																	+
							"<td valign='top'>"														+
							"  <input type='button' id='" + this.pagingTblId + "_prev'"				+
							"		  class='pagingIcon' "											+
							"	onmouseover=\"this.title='" + prevPageTitle + "'\""					+
							"	onClick=\"" + this.objectName + ".prevPage('');\">"					+
							"  <input type='button' id='" + this.pagingTblId + "_first'"			+
							"		  class='pagingIcon' " 											+
							"	onmouseover=\"this.title='" + firstPageTitle + "'\""				+
							"	onClick=\"" + this.objectName + ".gotoPage(1);\">"					+
							"</td>"																	+
						"</tr>"																		+
						"</table>"																	+
					"</td>"																			+
				"</tr>"																				+
				"</table>"																			+
			"</td>"																					+
		"</tr>"																						+
		"</table>"																					+
		"</form>";

	return htmlStr;
  }
  catch (error)
  {
 	throw new Error(10, error.description + "\n\t(dynamicTableObj.generatePagingHtmlStr)");
  }
}

/*----------------------------------------------------------------------*/
/* Function:	dynamicTableObj_generateMoreDataHtmlStr					*/
/* Description:	generating more data table html							*/
/*----------------------------------------------------------------------*/
function dynamicTableObj_generateMoreDataHtmlStr ()
{
  try
  {
  	this.checkLanguage ();

	if (this.moreDataCols == null && this.moreDataBlocks.length == 0)
		return "";

	this.rowEmptyXml = "<data>";

	// calculate more table width
	moreTblWidth = this.getTableWidth();

	var emptyHtml = "";

	var htmlStr =	"<table class='moreDataTbl'>";

	colWidths = eval("this.moreDataWidths." + this.theLanguage);

	// calc width when there is * width
	totalWidths = 0;
	for (var c = 0; c < colWidths.length; c++)
	{
		width = colWidths[c];

		if (width != "*")
		{
			totalWidths += width;
		}
		else
		{
			colWidths[c] = moreTblWidth - totalWidths - colWidths.length;
		}
	}
	
	// handle regular extensions
	if (this.moreDataCols != null)
	{
		htmls = this.generateMoreDataBlock (this.moreDataCols, colWidths, "regular");
		
		htmlStr += htmls.html;
	}

	for (var i = 0; i < this.moreDataBlocks.length; i++)
	{
		// clear block rows ids
		this.moreDataBlocks[i].blockRowsIds = new Array;
		
		handleCols = this.moreDataBlocks[i].blockCols;

		htmls = this.generateMoreDataBlock (handleCols, colWidths, "block", i);
		
		htmlStr   += htmls.html;
		emptyHtml += htmls.emptyHtml;
	}
		
	htmlStr +=	"</table>";

	if (emptyHtml != "")
	{
		htmlStr += "<table>" + emptyHtml + "</table>";
	}

	this.rowEmptyXml += "</data>";

	return htmlStr;
  }
  catch (error)
  {
 	throw new Error(10, error.description + "\n\t(dynamicTableObj.generateMoreDataHtmlStr)");
  }
}

/*----------------------------------------------------------------------*/
/* dynamicTableObj_generateMoreDataBlock								*/
/*----------------------------------------------------------------------*/
function dynamicTableObj_generateMoreDataBlock (cols, colWidths, type, blockIndex)
{
  try
  {
  		var htmlStr 	= "";
		var emptyTrHtml = "";

		var newLine 	= true;
		var colIndex	= 0;
		var currWidth	= 0;
		var rowIndex	= 0;

		while (colIndex < cols.length)
		{
			if (newLine)
			{
				rowIndex++;

				rowId    = "";
				addStyle = "";
				
				if (type == "block")
				{
					rowPrefix = this.moreTblId + "_block" + blockIndex;
					
					rowId    = rowPrefix + "_row" + rowIndex;
					addStyle = " style='display:none' ";

					this.moreDataBlocks[blockIndex].blockRowsIds.push (rowId);

					// create empty row
					emptyRowId = rowPrefix + "_emptyRow" + rowIndex;
					
					emptyTrHtml += "<tr class='moreDataTbl_row' id='" + emptyRowId + "'>"			+
									"<td class='moreDataTbl_col'>"									+
									"</td>"															+
								   "</tr>";

					// save empty row id
					this.moreDataBlocks[blockIndex].blockEmptyIds.push (emptyRowId);
				}
				
				htmlStr += "<tr class='moreDataTbl_row'	id='" + rowId + "'" + addStyle + ">";
				newLine  = false;
			}

			col = cols[colIndex];

			widthText = " width = '" + colWidths[currWidth] + "' ";
			widthData = "";
			spanText  = "";
		

			if (col.spanData == 1)
			{
				width 	  = colWidths[currWidth+1];
				widthData = " width = '"   + width + "' ";
			}
			else
			{
				spanText  = " colspan = '" + col.spanData + "' ";
			}

			text	= eval("col.text" + this.theLanguage);

			splitIndex = text.indexOf ("#");
			if (splitIndex != -1)
			{
				// binded field text
				defaultText = text.substring(0,splitIndex);
				text 		= text.substring(splitIndex+1, text.length);

				// save it for setXmlData for none selected row
				this.rowEmptyXml += "<" + text + ">" + defaultText + "</" + text + ">";

				text = "<span id='" + this.rowSrc + "_" + text + "'>"						+
							defaultText														+
					   "</span>";
			}
			
			action = "";
			if (col.action != undefined && col.action != "")
			{
				action = " onclick='" + col.action + "' style='cursor:pointer;color:blue;text-decoration:underline' ";
			}

			htmlStr +=			"<td " + widthText + " class='moreDataTbl_textCol'>"				+
									"<div>"	+ text + "</div>"										+
								"</td>"																+
								"<td " + widthData + spanText + " class='moreDataTbl_dataCol'>"		+
									"<div style='width:" + (width - 20) + ";overflow:hidden'>"		+
									  "<span id='" + this.rowSrc + "_" + col.xmlTag + "'" 			+ 
									  		 action + ">&nbsp;"										+
									  "</span>"														+
									"</div>"														+
								"</td>";


			colIndex++;
			currWidth  += col.spanData + 1;

			if (currWidth == colWidths.length)
			{
				htmlStr    +=	"</tr>";
				newLine	 	= true;
				currWidth	= 0;
			}
		}
		// end loop
	
		if (currWidth != 0)
		{
			missingCols = colWidths.length - currWidth;

			htmlStr +=		"<td colspan='" + missingCols + "' class='styleBlueTdText'>"			+
							"</td>"																	+
						"</tr>";
		}
	
		return {html:htmlStr, emptyHtml: emptyTrHtml};
  }
  catch (error)
  {
 	throw new Error(10, error.description + "\n\t(dynamicTableObj.generateMoreDataBlock)");
  }
}

/*----------------------------------------------------------------------*/
/* dynamicTableObj_generatePopupHtmlStr									*/
/*----------------------------------------------------------------------*/
function dynamicTableObj_generatePopupHtmlStr ()
{
  try
  {
	htmlStr = "<div id='" + this.popupDivId + "' style='display:none;position:absolute;' onmouseout='this.style.display=\"none\"'>" +
			  "</div>"; 

	return htmlStr;
  }
  catch (error)
  {
 	throw new Error(10, error.description + "\n\t(dynamicTableObj.generatePopupHtmlStr)");
  }
}

/*----------------------------------------------------------------------*/
/* Function: 	dynamicTableObj_generateTable							*/
/* Description: generating table html and loading it into 				*/
/*				the new span and performing the data binding.			*/
/*----------------------------------------------------------------------*/
function dynamicTableObj_generateTable () 
{
  try
  {
  	this.manualResetFields = new Array();

	tableSpan = document.getElementById(this.destinationSpanName);

	// support collapsing of the table
	if (tableSpan.innerHTML != "")		// not the first time
	{
		if (document.getElementById(this.dataDivId).offsetHeight != 0)
			this.divHeight = document.getElementById(this.dataDivId).offsetHeight;		// update current div height
	}

	// start with the main table and then add the specific table html
	htmlStr = 	"<table>"								+
				"<tr>"																            	+
			    	"<td>"																            +
						this.generateTableHtmlStr()										            +
					"</td>"																            +
				"</tr>";
				
	moreHtmlStr = this.generateMoreDataHtmlStr ();

	if (moreHtmlStr != "")
	{
		htmlStr +=
			  	"<tr>"																                +
					"<td>"																            +
						moreHtmlStr																	+
					"</td>"																            +
			  	"</tr>";
	}

	htmlStr +=	"</table>";
	
	tableSpan.innerHTML = htmlStr;

	var table = $("#" + this.dataTblId).stupidtable({
		"date": function(a,b) {
	        var aDate = a.substr(6,4) + a.substr(3,2) + a.substr(0,2);
	        var bDate = b.substr(6,4) + b.substr(3,2) + b.substr(0,2);

	        return aDate - bDate;
    	},
	
		"number" : function(a,b) {

			if (a == "") 
				var aInt = 0; 
			else 
				var aInt = parseInt(a);

			if (b == "") 
				var bInt = 0; 
			else 
				var bInt = parseInt(b);
		
			return aInt < bInt;
		}});

	this.timeToPaint = true;

	if (this.withPaging)
		this.updatePaging ();
  }
  catch (error)
  {
 	throw new Error(10, error.description + "\n\t(dynamicTableObj.generateTable)");
  }
}

/*----------------------------------------------------------------------*/
/* Function: 	dynamicTableObj_setXmls									*/
/* Description: setting the table xml									*/
/* Params: 		xmlNode=a valid xml node								*/
/*----------------------------------------------------------------------*/
function dynamicTableObj_setXmls (responseXml, rowsXmlName, totalXmlName)
{
  try
  {
  	// support standard tag names
  	if (rowsXmlName == undefined)
		rowsXmlName = "items";

	if (totalXmlName == undefined)
		totalXmlName = "totals";

	if (typeof(responseXml) == 'string' && responseXml == "")
	{
		responseXml = new ActiveXObject("Microsoft.XMLDOM");
		responseXml.loadXML ("<data><items></items><totals></totals></data>");
	}
		
	this.xmlNode 	 	= responseXml.getElementsByTagName(rowsXmlName).item(0);
	
  	if (this.xmlNode == null)
		throw new Error (10,"<" + rowsXmlName + "> - no such element in xml");

	this.xmlTopNodeName 	= this.xmlNode.nodeName;

	if (this.xmlNode.childNodes.length!=0) 
		this.xmlEntryNodeName	= this.xmlNode.childNodes[0].nodeName;

	this.xmlTotalNode = responseXml.getElementsByTagName(totalXmlName).item(0);
  }
  catch (error)
  {
 	throw new Error(10, error.description + "\n\t(dynamicTableObj.setXmls)");
  }
}

/*----------------------------------------------------------------------*/
/* Function: 	dynamicTableObj_getXml 									*/
/* Description: getting the table xml									*/
/*----------------------------------------------------------------------*/
function dynamicTableObj_getXml ()
{
  try
  {
  	var pageXmlObject = eval (this.dataSrc);
	return (pageXmlObject.xml);
  }
  catch (error)
  {
 	throw new Error(10, error.description + "\n\t(dynamicTableObj.getXml)");
  }
}

/*----------------------------------------------------------------------*/
/* Function: 	dynamicTableObj_setTableHeight							*/
/* Description: setting the table scroll height							*/
/* Params: 		scrollHeightOver800										*/
/* Params: 		scrollHeightBelow800									*/
/*----------------------------------------------------------------------*/
function dynamicTableObj_setTableHeight (heightOver800,heightBelow800) 
{
  try
  {
	if (screen.height > 800) 
	{
		this.divHeight = heightOver800;
	} 
	else 
	{
		this.divHeight = heightBelow800;
	}

//	this.divHeight = screen.height - 620;
  }
  catch (error)
  {
 	throw new Error(10, error.description + "\n\t(dynamicTableObj.setTableHeight)");
  }
}

$(document).keydown(function(event){
    if(event.which=="17")
        cntrlIsPressed = true;
});

$(document).keyup(function(){
    cntrlIsPressed = false;
});

var cntrlIsPressed = false;

/*----------------------------------------------------------------------*/
/* Function: 	dynamicTableObj_paintSelectedRow						*/
/* Description: painting the selected row 								*/
/*				and setting the value for the this.currSelectedRow		*/
/* Params: 		the row													*/
/*----------------------------------------------------------------------*/
function dynamicTableObj_paintSelectedRow (row) 
{
  try
  {
		if (row != null)
		{
			var rowBg = rgb2hex(row.style.background);

	        if (rowBg == this.selectedColor) 
			{
				var inArray = commonInArray(this.currSelectedRows, row);

        	    this.currSelectedRows[inArray].style.background = this.notSelectedColor;
	            this.currSelectedRows[inArray].style.color 	  = "#6C6C6C";

				this.currSelectedRows = commonRemoveFromArray (this.currSelectedRows, row);
    	    }
			else
			{
				if (this.currSelectedRows.length != 0 && (!this.isMultiSelection || (this.isMultiSelection && !cntrlIsPressed)))
				{
					// cancel prev selected row
	        	    this.currSelectedRows[0].style.background = this.notSelectedColor;
		            this.currSelectedRows[0].style.color 	  = "#6C6C6C";

					this.currSelectedRows = new Array();
				}

	        	row.style.background = this.selectedColor;
	    	    row.style.color      = "#0000AA";

				this.currSelectedRows.push (row);
			}
		}
  /*  }*/
  }
  catch (error)
  {
 	throw new Error(10, error.description + "\n\t(dynamicTableObj.paintSelectedRow)");
  }
}

/*----------------------------------------------------------------------*/
/* Function:    dynamicTableObj_cancelSelectedRow						*/
/*----------------------------------------------------------------------*/
function dynamicTableObj_cancelSelectedRow ()
{
	this.paintSelectedRow (null);
}

/*----------------------------------------------------------------------*/
/* Function: 	dynamicTableObj_mousePaint								*/
/* Description: handling the effect of the mouse-over coloring of a line*/
/* Params: 		row=the row to handle 									*/
/* Params: 		inOrOut: true=mouse over false=mouse out				*/
/*----------------------------------------------------------------------*/
function dynamicTableObj_mousePaint (row, inOrOut) 
{
  try
  {
	if (this.timeToPaint) 
	{
        if (this.rowWasPainted == true)  // if the row was un-checked	
		{
            this.rowWasPainted = false;
            return;
        }
        else 
		{
			var rowBg = rgb2hex(row.style.background);

			if (inOrOut && 
				rowBg != this.selectedColor &&
			    rowBg != this.mouseOnColor || 
				rowBg == this.notSelectedColor ) 
			{
	            row.style.background = this.mouseOnColor;
    	    }
	        else 
				if (!inOrOut && 
					rowBg == this.mouseOnColor && 
					rowBg != this.selectedColor ) 
				{
            		row.style.background = this.notSelectedColor ;
		        }
		}
	}
	else 
	{
		return;
	}
  }
  catch (error)
  {
 	throw new Error(10, error.description + "\n\t(dynamicTableObj.mousePaint)");
  }
}

function rgb2hex(rgb)
{

	//	rgb = rgb(233, 240, 246) none repeat scroll 0% 0%	 (firefox)
	
	if (rgb == "") return "";

 	rgb = rgb.match(/rgb\((\d+),\s*(\d+),\s*(\d+)\)/);

   	return "#" +
		  ("0" + parseInt(rgb[1],10).toString(16)).slice(-2) +
		  ("0" + parseInt(rgb[2],10).toString(16)).slice(-2) +
		  ("0" + parseInt(rgb[3],10).toString(16)).slice(-2);
}

/*----------------------------------------------------------------------*/
/* Function:    dynamicTableObj_getNumberOfRows							*/
/*----------------------------------------------------------------------*/
function dynamicTableObj_getNumberOfRows ()
{
	//return (this.xmlNode.childNodes.length);
	if (eval(this.dataTblId).style.display == "none")
		return 0;
	else
		return (eval(this.dataTblId).rows.length - 1);	// decrease th row
}

/*----------------------------------------------------------------------*/
/* Function: 	dynamicTableObj_setLoadFunction							*/
/* Description: setting the load function name for paging				*/
/* Params: 		the load function name									*/
/*----------------------------------------------------------------------*/
function dynamicTableObj_setLoadFunction (loadFunctionName) 
{
	this.loadFunction = loadFunctionName;
}

/*----------------------------------------------------------------------*/
/* Function: 	dynamicTableObj_setPaintFunction						*/
/* Description: setting the paint function name for after sorting		*/
/* Params: 		the paint function name									*/
/*----------------------------------------------------------------------*/
function dynamicTableObj_setPaintFunction (paintFunctionName)
{
	this.paintFunction = paintFunctionName;
}

/*----------------------------------------------------------------------*/
/* Function: 	dynamicTableObj_getSelectedRow							*/
/* Description: returns the selected row								*/
/*----------------------------------------------------------------------*/
function dynamicTableObj_getSelectedRow () 
{
	if (this.currSelectedRows.length != 1)
		return null;
	else
		return this.currSelectedRows[0];
}

/*----------------------------------------------------------------------*/
/* Function: 	dynamicTableObj_getSelectedRows							*/
/* Description: returns the selected rows								*/
/*----------------------------------------------------------------------*/
function dynamicTableObj_getSelectedRows () 
{
	return this.currSelectedRows;
}

/*----------------------------------------------------------------------*/
/* dynamicTableObj_setSelectedRow										*/
/*----------------------------------------------------------------------*/
function dynamicTableObj_setSelectedRow (tagName, value)
{
  try
  {
	var tbl = eval(this.dataTblId);
	var row = null;

	// find row
	for (r=0; r < tbl.rows.length; r++)
	{
		if (this.getRowValueOf (r,tagName) == value)
		{
			row = tbl.rows[r];
		}
	}
	if (row != null)
	{
		this.paintSelectedRow (row);

		row.scrollIntoView ();
	}
  }
  catch (error)
  {
 	throw new Error(10, error.description + "\n\t(dynamicTableObj.setSelectedRow)");
  }
}

/*----------------------------------------------------------------------*/
/* Function: 	dynamicTableObj_getRowAsXml								*/
/* Description: returns the passed row xml								*/
/*----------------------------------------------------------------------*/
function dynamicTableObj_getRowAsXml (rowIndex) 
{
  try
  {
	var xmlIndex = parseInt($("#" + this.dataTblId + " tbody tr:nth-child(" + rowIndex + ")").attr("xml-index"));

	return this.xmlNode.getElementsByTagName(this.xmlEntryNodeName).item(xmlIndex);
  }
  catch (error)
  {
 	throw new Error(10, error.description + "\n\t(dynamicTableObj.getRowAsXml)");
  }
}

/*----------------------------------------------------------------------*/
/* Function:    dynamicTableObj_paintRow								*/
/* Description:	change the row class according to the which param		*/
/*----------------------------------------------------------------------*/
function dynamicTableObj_paintRow (rowIndex, which)
{
  try
  {
 	className = "styleGrayTR";

  	switch (which)
	{
		case "regular"	: 
							break;

		case "choosen"	: 	className = "styleGrayTRChoosen";
							break;

	}
	eval(this.dataTblId).rows[rowIndex].className = className;
  }
  catch (error)
  {
 	throw new Error(10, error.description + "\n\t(dynamicTableObj.paintRow)");
  }
}

/*----------------------------------------------------------------------*/
/* Function:    dynamicTableObj_setColImg 								*/
/* Description:	change the column image 								*/
/*----------------------------------------------------------------------*/
function dynamicTableObj_setColImg (rowIndex, colIndex, image)
{
  try
  {
  	if (image == "")
		eval(this.dataTblId).rows[rowIndex].cells[colIndex-1].childNodes[1].style.display = "none";
	else
	{
		eval(this.dataTblId).rows[rowIndex].cells[colIndex-1].childNodes[1].style.display = "";
		eval(this.dataTblId).rows[rowIndex].cells[colIndex-1].childNodes[1].src 		  = image;
	}
  }
  catch (error)
  {
 	throw new Error(10, error.description + "\n\t(dynamicTableObj.setColImg)");
  }
}

/*----------------------------------------------------------------------*/
/* Function:    dynamicTableObj_getColImg 								*/
/* Description:	return the column image 								*/
/*----------------------------------------------------------------------*/
function dynamicTableObj_getColImg (rowIndex, colIndex)
{
  try
  {
  	image = eval(this.dataTblId).rows[rowIndex].cells[colIndex-1].childNodes[1];
  	if (image.style.display == "none")
		return "";
	else
		return (image.src);
  }
  catch (error)
  {
 	throw new Error(10, error.description + "\n\t(dynamicTableObj.setColImg)");
  }
}

/*----------------------------------------------------------------------*/
/* Function:    dynamicTableObj_fillColSpan 							*/
/*----------------------------------------------------------------------*/
function dynamicTableObj_fillColSpan (rowIndex, colIndex, innerHTML)
{
  try
  {
	document.getElementById(this.dataTblId).rows[rowIndex+1].cells[colIndex-1].childNodes[1].innerHTML = innerHTML;
  }
  catch (error)
  {
 	throw new Error(10, error.description + "\n\t(dynamicTableObj.fillColSpan)");
  }
}
/*----------------------------------------------------------------------*/
/* Function:    dynamicTableObj_updateCol 								*/
/* Description:	change column inner html 								*/
/*----------------------------------------------------------------------*/
function dynamicTableObj_updateCol (rowIndex, colIndex, colHtml)
{
  try
  {
	eval(this.dataTblId).rows[rowIndex].cells[colIndex-1].childNodes[1].innerHTML = colHtml;
  }
  catch (error)
  {
 	throw new Error(10, error.description + "\n\t(dynamicTableObj.updateCol)");
  }
}
/*----------------------------------------------------------------------*/
/* Function: 	dynamicTableObj_getSelectedRowAsXml						*/
/* Description: returns the xml of selected row							*/
/*----------------------------------------------------------------------*/
function dynamicTableObj_getSelectedRowAsXml () 
{
  try
  {
	rowIndex = this.getSelectedRow().rowIndex;
	return this.getRowAsXml (rowIndex);
  }
  catch (error)
  {
 	throw new Error(10, error.description + "\n\t(dynamicTableObj.getSelectedRowAsXml)");
  }
}

/*----------------------------------------------------------------------*/
/* Function:    dynamicTableObj_getRowValueOf							*/
/*----------------------------------------------------------------------*/
function dynamicTableObj_getRowValueOf (rowIndex, tagName)
{
  try
  {
	var xml = this.getRowAsXml (rowIndex);

	try
	{
		return commonGetInnerData(xml, tagName);
	}
	catch (e)
	{
		return "";
	}
  }
  catch (error)
  {
 	throw new Error(10, error.description + "\n\t(dynamicTableObj.getRowValueOf)");
  }
}

/*----------------------------------------------------------------------*/
/* Function:    dynamicTableObj_setRowValueOf							*/
/*----------------------------------------------------------------------*/
function dynamicTableObj_setRowValueOf (rowIndex, tagName, value)
{
  try
  {
  	if (tagName != "")
	{
		// update xml data
		var pageXmlObject = eval (this.dataSrc);
		var rowNode = pageXmlObject.getElementsByTagName(this.xmlEntryNodeName).item(rowIndex);

		if (window.DOMParser)
		{
			rowNode.getElementsByTagName(tagName).item(0).textContent = value;
		}
		else
		{
			rowNode.getElementsByTagName(tagName).item(0).text = value;
		}
	}
  }
  catch (error)
  {
 	throw new Error(10, error.description + "\n\t(dynamicTableObj.setRowValueOf)");
  }
}

/*----------------------------------------------------------------------*/
/* Function: 	dynamicTableObj_getSelectedValueOf						*/
/* Description: returns the selected tag value							*/
/*----------------------------------------------------------------------*/
function dynamicTableObj_getSelectedValueOf (tagName, i)
{
  try
  {
  	var selectedValue = "";
	
	var theSelectedRows = this.getSelectedRows();

  	if (theSelectedRows != null && theSelectedRows.length != 0)
	{
		if (i == undefined) i = 0;

		rowIndex = theSelectedRows[i].rowIndex;
		selectedValue = this.getRowValueOf (rowIndex, tagName);
	}

	return selectedValue;
  }
  catch (error)
  {
 	throw new Error(10, error.description + "\n\t(dynamicTableObj.getSelectedValueOf)");
  }
}

/*----------------------------------------------------------------------*/
/* Function: 	dynamicTableObj_getSelectedValuesOf						*/
/* Description: returns the selected tag values							*/
/*----------------------------------------------------------------------*/
function dynamicTableObj_getSelectedValuesOf (tagName)
{
  try
  {
  	var selectedValues = new Array();
	
  	if (this.currSelectedRows != null)
	{
		for (i = 0; i < this.currSelectedRows.length; i++)
		{
			rowIndex = this.currSelectedRows[i].rowIndex;
			selectedValues.push (this.getRowValueOf (rowIndex, tagName));
		}
	}

	return selectedValues;
  }
  catch (error)
  {
 	throw new Error(10, error.description + "\n\t(dynamicTableObj.getSelectedValuesOf)");
  }
}

/*----------------------------------------------------------------------*/
/* Function: 	dynamicTableObj_isRowSelected							*/
/* Description: indicating if there is a selected row or not.			*/
/*----------------------------------------------------------------------*/
function dynamicTableObj_isRowSelected () 
{
	return (this.currSelectedRows.length == 1);
}

/*----------------------------------------------------------------------*/
/* Function: 	dynamicTableObj_areRowsSelected							*/
/* Description: indicating if there is a selected row or not.			*/
/*----------------------------------------------------------------------*/
function dynamicTableObj_areRowsSelected () 
{
	return (this.currSelectedRows.length > 1);
}

/*----------------------------------------------------------------------*/
/* Function: 	dynamicTableObj_setIsMultiSelection						*/
/*----------------------------------------------------------------------*/
function dynamicTableObj_setIsMultiSelection (isMulti) 
{
	this.isMultiSelection = isMulti;
}

/*----------------------------------------------------------------------*/
/* Function: 	dynamicTableObj_checkAll								*/
/*----------------------------------------------------------------------*/
function dynamicTableObj_checkAll () 
{
	var tbl = eval(this.dataTblId);

	for (r=0; r < tbl.rows.length; r++)
	{
		row = tbl.rows[r];

        if (rgb2hex(row.style.background) != this.selectedColor) 
		{
        	row.style.background = this.selectedColor;
    	    row.style.color      = "#0000AA";

			this.currSelectedRows.push (row);
		}
	}
}

/*----------------------------------------------------------------------*/
/* Function: 	dynamicTableObj_uncheckAll								*/
/*----------------------------------------------------------------------*/
function dynamicTableObj_uncheckAll () 
{
	var tbl = document.getElementById(this.dataTblId);

	for (r=0; r < tbl.rows.length; r++)
	{
		row = tbl.rows[r];

        if (rgb2hex(row.style.background) == this.selectedColor) 
		{
       	    row.style.background = this.notSelectedColor;
            row.style.color 	  = "#6C6C6C";

			this.currSelectedRows = new Array();
		}
	}
}


/*----------------------------------------------------------------------*/
/* Function: 	dynamicTableObj_hideRowPopup							*/
/*----------------------------------------------------------------------*/
function dynamicTableObj_hideRowPopup () 
{
  try
  {
		popupDiv = eval(this.popupDivId);
		popupDiv.style.display = "none";

  }
  catch (error)
  {
 	throw new Error(10, error.description + "\n\t(dynamicTableObj.hideRowPopup)");
  }
}

/*----------------------------------------------------------------------*/
/* Function: 	dynamicTableObj_showRowPopup							*/
/*----------------------------------------------------------------------*/
function dynamicTableObj_showRowPopup (e, row, tableName) 
{
  try
  {
		 if (rgb2hex(row.style.background) != this.selectedColor)
				this.paintSelectedRow (row);

		 if (this.currSelectedRows.length > 1)
			var multiPopup = true;
		 else
			var multiPopup = false;

		popupDiv = eval(this.popupDivId);

		popupDiv.style.zIndex = 100;

		if (this.theLanguage == "HEB")
			popupDiv.style.left   = e.clientX - 70 + "px";
		else
			popupDiv.style.left   = e.clientX - 30 + "px";

		popupDiv.style.top    = document.body.scrollTop + e.clientY + "px";	
		popupDiv.style.width  = "125px";

		// fill popup div
		popupHtml = "<table class='rowPopupTbl'" +
					"	 onmouseover='this.parentNode.style.display=\"\"'>";

		// build btns from rowOfButtons component
		btnsHtml = "";

		// - first find table component id
		startSearch = 0;
		for (var i=0; i<pageObj.components.length; i++)
		{
			if (pageObj.components[i].type == "table" && pageObj.components[i].spanName == tableName + "_spn")
			{
				startSearch = i;
				break;
			}
		}

		searchId = -1;
		btnsId	 = -1;
		for (var i=startSearch; i<pageObj.components.length; i++)
		{
			if (searchId != -1)
			{
				if (pageObj.components[i].type == "rowOfButtons" && eval(pageObj.components[i].spanName).style.display != "none")
				{
					btnsId = i;
					break;
				}
			}
			else if (pageObj.components[i].spanName == this.objectName + "_spn")
			{
				searchId = i;
			}
		}

		if (btnsId == -1) return; 

		popupButtons = pageObj.getComponent(btnsId).popupButtons;

		var countPopupRows = 0;

		for (var g=0; g<popupButtons.length; g++)
		{
			buttons = popupButtons[g];
			
			for (var b=0; b<buttons.length; b++)
			{
				if (buttons[b].type == "add" || buttons[b].type == "print" || buttons[b].type == "image" || buttons[b].type == "lang" ||
					buttons[b].type == "back" || buttons[b].inPopup == false) continue;

				if (multiPopup && buttons[b].isMulti != true) continue;

				if (buttons[b].type == "")
					button = buttons[b];
				else
					button = eval(buttons[b].type + "Button");
					
				btnText	= eval("button.popup" + this.theLanguage)

				if (btnText == undefined)
					btnText = eval("button.text" + this.theLanguage);

				btnsHtml += "<tr onclick=\"" + buttons[b].action + "\">" +
								"<td><div class='rowPopup_text'><div>" + btnText + "</div></div></td>" +
							"</tr>";

				countPopupRows++;
			}
								
		}

		popupHtml += 			btnsHtml +
					"</table>";

		popupDiv.innerHTML = popupHtml;

		if (countPopupRows != 0)
		{
			// show popup div
			popupDiv.style.display = "";
		}


		return false;
  }
  catch (error)
  {
 	throw new Error(10, error.description + "\n\t(dynamicTableObj.showRowPopup)");
  }
}

/*----------------------------------------------------------------------*/
/* Function: 	dynamicTableObj_tableSort								*/
/* Description: performing the table sort - according to the index of 	*/
/* 				the header (which is the index of the node etc.) 		*/
/* Params: 		the index of the header to sort by						*/
/*----------------------------------------------------------------------*/
function dynamicTableObj_tableSort (index) 
{
  try
  {
	if (this.xmlNode.childNodes.length==0) 
		return;

	this.reset ();

	sortTbl = eval(this.sortTblId);

	// mark the selected sort as asc or desc sort
	//--------------------------------------------------------------------
	if (this.currSortIndex != index ) 
	{
		// hiding the arrow image
		sortTbl.rows[0].cells[this.currSortIndex].childNodes[0].childNodes[1].style.visibility = 'hidden';

		this.columns[this.currSortIndex].sortDir	= this.columns[this.currSortIndex].defaultSortDir;

		// setting the current sort index
		this.currSortIndex = index;
	}
	else
	{
		if (this.columns[index].sortDir == "descending") 
			this.columns[index].sortDir = "ascending";
		else
			this.columns[index].sortDir = "descending";
	}

	// a click on the column header switches the sort direction
	if (this.columns[index].sortDir == "descending") 
	{
		sortTbl.rows[0].cells[index].childNodes[0].childNodes[1].src = '../../designFiles/sortDown.png';
	}
	else 
	{
		sortTbl.rows[0].cells[index].childNodes[0].childNodes[1].src = '../../designFiles/sortUp.png';
	}

	sortTbl.rows[0].cells[index].childNodes[0].childNodes[1].style.visibility = 'visible';

	if (this.columns[index].sortDir == "descending")
		this.sortDir	= "desc";
	else
		this.sortDir	= "asc";

	this.sortBy			= this.columns[index].xmlTag;

	// do the sort
	// --------------------------------------------------------------------
	$("#" + this.dataTblId).find("th").eq(index).click();
	
	if (this.paintFunction != "")
		setTimeout (this.paintFunction + "()",800);
  }
  catch (error)
  {
 	throw new Error(10, error.description + "\n\t(dynamicTableObj.tableSort)");
  }
}

/*----------------------------------------------------------------------*/
/* Function: 	dynamicTableObj_setXmlData								*/
/* Description: Load xml to the more data table.						*/
/*----------------------------------------------------------------------*/
function dynamicTableObj_setXmlData ()
{
  try
  {
	var currRow	  = this.rowEmptyXml;

	if (this.currSelectedRows.length != 1)
	{
		this.hideAllExtensionBlocks ();
	}
	else 
	{
		var rowIndex = this.currSelectedRows[0].rowIndex;

		var currRow = this.getRowAsXml (rowIndex);
/*		if (this.xmlNode != null)
		{
			var currRow = this.xmlNode.childNodes[rowIndex];
		}
*/	}

	if (this.moreDataCols != null)
	{
		for (var colIndex = 0; colIndex < this.moreDataCols.length; colIndex++)
		{
			col = this.moreDataCols[colIndex];

			document.getElementById(this.rowSrc + "_" + col.xmlTag).innerHTML = commonGetInnerData(currRow, col.xmlTag);
		}
	}

	try
	{
		if (this.handleExtensionBlocksFunction != "")
		{
			eval(this.handleExtensionBlocksFunction + "()");
		}
	}
	catch (error)
	{
		throw new Error (10, "Handle extensions blocks function does not exist (" + 
							 this.handleExtensionBlocksFunction + ")");
	}
  }
  catch (error)
  {
 	showError ("dynamicTableObj", "setXmlData", error);
  }
}

/*----------------------------------------------------------------------*/
/* Function:    dynamicTableObj_getTableWidth							*/
/*----------------------------------------------------------------------*/
function dynamicTableObj_getTableWidth ()
{
  try
  {
  	this.checkLanguage ();

	if (this.realWidth == 0)
	{
		for (var i=0; i<this.columns.length; i++)
		{
			this.realWidth += eval("this.columns[i].width" + this.theLanguage)*1 + 1;	// add cell border
		}
		this.realWidth += this.scrollWidth;
	}

	return this.realWidth - 1;	// remove last cell border
  }
  catch (error)
  {
 	throw new Error(10, error.description + "\n\t(dynamicTableObj.getTableWidth)");
  }
}

/*----------------------------------------------------------------------*/
/* Function:    dynamicTableObj_reset									*/
/*----------------------------------------------------------------------*/
function dynamicTableObj_reset ()
{
  try
  {
	var dataTbl = document.getElementById(this.dataTblId);

	if (dataTbl == null) return;

	this.currSelectedRows    = new Array();

	this.uncheckAll ();

	this.setXmlData ();

	// clean form fields in table (if exists)
	// -----------------------------------------------------------------
	for (var f=0; f < this.manualResetFields.length; f++)
	{
		for (var i=0; i < dataTbl.rows.length; i++)
		{
			this.setRowValueOf (i, this.manualResetFields[f], "");
		}
	}
  }
  catch (error)
  {
 	throw new Error(10, error.description + "\n\t(dynamicTableObj.reset)");
  }
}

/*----------------------------------------------------------------------*/
/* Function:    dynamicTableObj_tableChanged							*/
/*----------------------------------------------------------------------*/
function dynamicTableObj_tableChanged ()
{
  try
  {
  	var currXml = this.getXml();
	currXml     = currXml.substring (0, currXml.lastIndexOf(">")+1);

	var origXml = "";
	if (this.xmlNode != null)
	{
		origXml = this.xmlNode.xml;
		origXml     = origXml.substring (0, origXml.lastIndexOf(">")+1);
	}

  	return (currXml != origXml);
  }
  catch (error)
  {
 	throw new Error(10, error.description + "\n\t(dynamicTableObj.reset)");
  }
}

/*----------------------------------------------------------------------*/
/* Function:	dynamicTableObj_getPaging								*/
/*----------------------------------------------------------------------*/
function dynamicTableObj_getPaging ()
{
	return 	"<pageNumber>" 	+ this.pageNumber 	+ "</pageNumber>"	+
			"<pagesAmount>"	+ this.pagesAmount 	+ "</pagesAmount>"	+
			"<fileName>"	+ this.fileName		+ "</fileName>"		+
			"<rowsTotal>"	+ this.rowsTotal	+ "</rowsTotal>"	+
			"<sortDir>"		+ this.sortDir		+ "</sortDir>"		+
			"<sortBy>"		+ this.sortBy		+ "</sortBy>";
}

/*----------------------------------------------------------------------*/
/* Function:	dynamicTableObj_setPaging								*/
/*----------------------------------------------------------------------*/
function dynamicTableObj_setPaging (xml)
{
	this.withPaging = true;
	if (this.xmlTotalNode != undefined)
	{
		if (xml.getElementsByTagName("pagesAmount").item(0) != null)
			this.pagesAmount  = commonGetInnerData (xml, "pagesAmount");

		if (xml.getElementsByTagName("sqlFileName").item(0) != null)
			this.fileName 	  = commonGetInnerData (xml, "sqlFileName");

		if (xml.getElementsByTagName("rowsTotal").item(0) != null)
			this.rowsTotal    = commonGetInnerData (xml, "rowsTotal");

	}
}

/*----------------------------------------------------------------------*/
/* Function:	dynamicTableObj_resetPaging								*/
/*----------------------------------------------------------------------*/
function dynamicTableObj_resetPaging ()
{
	this.fileName 	 = "";
	this.pageNumber  = 1;
	this.pagesAmount = "";
}

/*----------------------------------------------------------------------*/
/* Function:    dynamicTableObj_gotoPage								*/
/*----------------------------------------------------------------------*/
function dynamicTableObj_gotoPage (page)
{
	if (page != "")
		this.pageNumber = page*1;

	this.reset ();

	eval(this.loadFunction + "()");
}

/*----------------------------------------------------------------------*/
/* Function:	dynamicTableObj_nextPage								*/
/*----------------------------------------------------------------------*/
function dynamicTableObj_nextPage ()
{
	this.pageNumber++;

	this.gotoPage ("");
}

/*----------------------------------------------------------------------*/
/* Function:	dynamicTableObj_prevPage								*/
/*----------------------------------------------------------------------*/
function dynamicTableObj_prevPage ()
{
	this.pageNumber--;

	this.gotoPage ("");
}

/*----------------------------------------------------------------------*/
/* Function:   	dynamicTableObj_jumpToPage								*/
/*----------------------------------------------------------------------*/
function dynamicTableObj_jumpToPage ()
{
	var pageObj		= eval("document.all." + this.pagingTblId + "_page");

    pageValue = pageObj.value;

    if (isNaN(pageValue) || pageValue == "" || pageValue == 0)
    {
        commonMsgBox ("info","יש להזין מספרים בלבד", "Only digits");
		pageObj.value = this.pageNumber;
        return false;
    }
	if(eval(pageObj.value) > eval(this.pagesAmount) || eval(pageObj.value)<1 )
	{
		commonMsgBox ("info","עמוד לא קיים","No such page");
	}
	else
	{
		this.gotoPage (pageObj.value);
	}
}

/*----------------------------------------------------------------------*/
/* Function:   	dynamicTableObj_updatePaging							*/
/*----------------------------------------------------------------------*/
function dynamicTableObj_updatePaging ()
{
	var imagesPath  = "../../designFiles/";

	var lastObj 	= document.getElementById(this.pagingTblId + "_last");
	var firstObj	= document.getElementById(this.pagingTblId + "_first");
	var playObj		= document.getElementById(this.pagingTblId + "_play");
	var nextObj		= document.getElementById(this.pagingTblId + "_next");
	var prevObj		= document.getElementById(this.pagingTblId + "_prev");
	var pageObj		= document.getElementById(this.pagingTblId + "_page");
	var totalObj	= document.getElementById(this.pagingTblId + "_totalPage");
	var pageTdObj	= document.getElementById(this.pagingTblId + "_pageFieldsTd");

	if (this.pagesAmount == 0)
	{
		document.getElementById(this.pagingTblId + "_pagingIconsTbl").style.display = "none";

		lastObj.style.backgroundImage  = "url('" + imagesPath + "iconLastPage_disabled.jpg')";
		firstObj.style.backgroundImage = "url('" + imagesPath + "iconFirstPage_disabled.jpg')";
		playObj.style.backgroundImage  = "url('" + imagesPath + "iconGotoPage_disabled.jpg')";
		nextObj.style.backgroundImage  = "url('" + imagesPath + "iconNextPage_disabled.jpg')";
		prevObj.style.backgroundImage  = "url('" + imagesPath + "iconPrevPage_disabled.jpg')";

		lastObj.disabled   = true;
		firstObj.disabled  = true;
		playObj.disabled   = true;
		nextObj.disabled   = true;
		prevObj.disabled   = true;

		pageObj.value 	   = "";
		totalObj.value 	   = "";

		return;
	}

	if (this.pagesAmount != 0)
	{
		lastObj.style.backgroundImage = "url('" + imagesPath + "iconLastPage.jpg')";
		playObj.style.backgroundImage = "url('" + imagesPath + "iconGotoPage.jpg')";
		nextObj.style.backgroundImage = "url('" + imagesPath + "iconNextPage.jpg')";
		pageObj.value = this.pageNumber;

		playObj.disabled   = false;
		
		if (this.theLanguage == "HEB")
		{
			pageTdObj.dir		 		= "rtl";
			totalObj.value  		   	= "מ-" + this.pagesAmount;
			pageObj.style.textAlign 	= "left";
			totalObj.style.textAlign 	= "right";
		}
		else
		{
			pageTdObj.dir		 		= "ltr";
			totalObj.value				= "out of " + this.pagesAmount;
			pageObj.style.textAlign 	= "right";
			totalObj.style.textAlign 	= "left";
		}
	}

	// check whether it the last page
	if ( this.pagesAmount == this.pageNumber )
	{
		lastObj.style.backgroundImage 	= "url('" + imagesPath + "iconLastPage_disabled.jpg')";
		nextObj.style.backgroundImage 	= "url('" + imagesPath + "iconNextPage_disabled.jpg')";
		lastObj.disabled = true;
		nextObj.disabled = true;
	}
	else
	{
		lastObj.disabled = false;
		nextObj.disabled = false;
	}

	// check whether it the first page
	if (this.pageNumber == 1)
	{
		prevObj.style.backgroundImage 	= "url('" + imagesPath + "iconPrevPage_disabled.jpg')";
		firstObj.style.backgroundImage 	= "url('" + imagesPath + "iconFirstPage_disabled.jpg')";
		prevObj.disabled  = true;
		firstObj.disabled = true;
	}
	else
	{
		prevObj.style.backgroundImage 	= "url('" + imagesPath + "iconPrevPage.jpg')";
		firstObj.style.backgroundImage 	= "url('" + imagesPath + "iconFirstPage.jpg')";
		prevObj.disabled  = false;
		firstObj.disabled = false;
	}
}

/*----------------------------------------------------------------------*/
/* Function:    dynamicTableObj_generatePrintStyles						*/
/*----------------------------------------------------------------------*/
function dynamicTableObj_generatePrintStyles ()
{
  try
  {
	style	= "<style>"									+
				"body" 									+
				"{" 									+		
				"   background-color: #f8f8f8;" 		+
				"	top-margin		: 0;" 				+
				"}" 									+
				".styleDate"							+
				"{" 									+
				"	font-size 		: 12px;" 			+
				"	font-family		: arial; " 			+
				"	color			: #003399; "		+
				"}" 									+
				".stylePageTitle" 						+
				"{" 									+
				"	font-size 		: 17px;" 			+
				"	font-family		: arial; " 			+
				"	font			: bolder; " 		+
				"	text-decoration : underline;" 		+
				"}" 									+
				".styleHeadTitle" 						+
				"{" 									+
				"	font-size 		: 12px;" 			+
				"	font-family		: arial; " 			+
				"	color			: midnightblue;" 	+
				"	font            : bolder;" 			+
				"	text-decoration : underline;" 		+
				"}" 									+
				".styleDataTbl" 						+
				"{" 									+
				"	border-color:gainsboro;"			+
				"}" 									+
				".styleGrayTR" 							+
				"{" 									+
				"   font-size		: 11px;" 			+
				"	font-family		: arial; " 			+
				"   height			: 16px;" 			+
				"	border-color:gainsboro;"			+
				"}" 									+
		     "</style>";

	return style;
  }
  catch (error)
  {
 	throw new Error(10, error.description + "\n\t(dynamicTableObj.generatePrintStyles)");
  }
}

/*----------------------------------------------------------------------*/
/* Function:	dynamicTableObj_generatePrintJsCode						*/
/*----------------------------------------------------------------------*/
function dynamicTableObj_generatePrintJsCode ()
{
  try
  {
	jsCode = "<script lang='javascript'>"				+
				"function onLoad()"						+
				"{"										+
					"setTimeout(\"printPage()\",500);"	+
				"}"										+
				"function printPage() "					+
				"{"										+
					"try"								+
					"{"									+
						"window.print();"				+
						"window.close();"				+
					"}"									+
					"catch (e) {}"						+
				"}"										+
			 "</script>"

	return jsCode;
  }
  catch (error)
  {
 	throw new Error(10, error.description + "\n\t(dynamicTableObj.generatePrintJsCode)");
  }
}

/*----------------------------------------------------------------------*/
/* Function:	dynamicTableObj_generatePrintHtmlTitle					*/
/*----------------------------------------------------------------------*/
function dynamicTableObj_generatePrintHtmlTitle (title)
{
  try
  {
	titleTbl  = "<table border='0' cellpadding='1' cellspacing='1'  width='100%' "					+
				"		style='direction:" + this.direction + "'>"									+
	            "<tr>"																				+
					"<td width='12%'  align='center'>"												+
						"<span class='styleDate'>"      + now() + "</span>"							+
					"</td>"																			+
					"<td width='88%' align='center' valign='center'>" 								+
						"<span class='stylePageTitle'>" + title   + "</span>"						+
					"</td>"																			+
				"</tr>"																				+
				"<tr height='50'><td></td></tr>"													+
				"</table>";

	return titleTbl;
  }
  catch (error)
  {
 	throw new Error(10, error.description + "\n\t(dynamicTableObj.generatePrintHtmlTitle)");
  }
}

/*----------------------------------------------------------------------*/
/* Function:    dynamicTableObj_generatePrintHtmlHeaders				*/
/*----------------------------------------------------------------------*/
function dynamicTableObj_generatePrintHtmlHeaders ()
{
  try
  {
  	this.checkLanguage ();

	htmlStr  = "<table cellspacing='0' cellpadding='0' width='100%'>"								+
			   "<tr>"                                              									+
			  	"<td width='100%' align='center'>"    												+
			  		"<table>"																		+	
						"<tr>" ;

	for (var i=0; i < this.columns.length; i++) 
	{
		width 		= eval("this.columns[i].width" + this.theLanguage);

		headerText  = eval("this.columns[i].text" + this.theLanguage);
		if (headerText == undefined)
			headerText = "";
			
		htmlStr +=  		"<td width='" + width + "'>" 											+
								"&nbsp; <span class='styleHeadTitle'>" + headerText + "</span>"		+
							"</td>";
	}
	// closing the row
	htmlStr +=  		"</tr>"																		+
					"</table>"																		+
			  	"</td>"																				+
			  	"</tr>"																				+
			  	"</table>";

	return htmlStr
  }
  catch (error)
  {
 	throw new Error(10, error.description + "\n\t(dynamicTableObj.generatePrintHtmlHeaders)");
  }
}

/*----------------------------------------------------------------------*/
/* Function:    dynamicTableObj_generatePrintHtmlData					*/
/*----------------------------------------------------------------------*/
function dynamicTableObj_generatePrintHtmlData ()
{
  try
  {
	dataTbl = document.getElementById(this.dataTblId).innerHTML;

	// remove events & images (using regexp - see http://www.javascriptkit.com/jsref/regexp.shtml)
	dataTbl = dataTbl.replace   (/onmouseover=".*"/g, 	"");
	dataTbl = dataTbl.replace   (/onclick=table.*\);/g, "");
	dataTbl = dataTbl.replace   (/<TBODY>/g,   		  	"");
	dataTbl	= dataTbl.replace   (/<\/TBODY>/g, 		  	"");
	dataTbl	= dataTbl.replace	(/<td /ig,			  	"<td style='border-color:gainsboro' ");	

	dataTbl = "<table class='styleDataTbl' cellspacing='0' cellpadding='0' width='100%'>"			+
			  "<tr>"																				+
			  "<td width='100%' align='center'>"													+
					"<table cellspacing='1' cellpadding='1' border='1'>" 							+ 
						dataTbl 																	+ 	
					"</table>"																		+
				"</td>"																				+
			  "</tr>"																				+
			  "</table>";

	return dataTbl;
  }
  catch (error)
  {
 	throw new Error(10, error.description + "\n\t(dynamicTableObj.generatePrintHtmlData)");
  }
}

/*----------------------------------------------------------------------*/
/* Function:    dynamicTableObj_print									*/
/*----------------------------------------------------------------------*/
function dynamicTableObj_print (title)
{
  try
  {
	styles	  = this.generatePrintStyles     ();
	jsCode 	  = this.generatePrintJsCode	 ();
	titleTbl  = this.generatePrintHtmlTitle  (title);
	headerTbl = this.generatePrintHtmlHeaders();
	dataTbl	  = this.generatePrintHtmlData   ();
	
	docHtml = "<html dir='" + this.direction + "'>"												+
				"<head>"																		+
					styles 																		+
					jsCode 																		+
				"</head>"																		+
			  	"<body onload='onLoad()'>"														+
				"<table width='100%' align='center'>"											+
				"<tr>"																			+
					"<td>"																		+
						titleTbl																+
						headerTbl																+
						dataTbl																	+
					"</td>"																		+
				"</tr>"																			+
				"</table>"																		+
				"</body>"																		+
		      "</html>";
	
    var win = window.open(commonGoToMain() + "general/printPage.html","print",
                          "location=no,scrollbars=yes,status=yes,menubar=no,resizable=yes,toolbar=no");
    win.document.open  ();
	win.document.write (docHtml);
	win.document.close ();

	win.focus ();

	return true;
  }
  catch (error)
  {
 	throw new Error(10, error.description + "\n\t(dynamicTableObj.print)");
  }
}

/*----------------------------------------------------------------------*/
/* Function:	dynamicTableObj_getTableDivId							*/
/*----------------------------------------------------------------------*/
function dynamicTableObj_getTableDivId ()
{
	return this.dataDivId;
}

