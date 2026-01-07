/*----------------------------------------------------------------------*a
/* 																		*/
/*							dynamicReportObj.js							*/
/*							------------------							*/
/*																		*/
/*  Report contains sections.											*/
/*	Each section has :													*/
/*	- title																*/
/* 	- lines																*/
/*	- sub total lines													*/
/*  - total lines														*/
/*																		*/
/*	Definition of report includes :										*/
/*	- headers  -> array of headers										*/
/*	- line	   -> array of columns										*/
/*	- subTotal -> array of columns										*/
/*  - total	   -> array of columns										*/
/*																		*/
/*	Header attributes :													*/
/*	- textHEB	: header hebrew text 									*/
/*	- textENG	: header english text                           		*/
/*	- widthHEB	: column hebrew width  									*/
/*  - widthENG	: column english width									*/
/*	- span		: col span												*/
/*																		*/
/*	Column attributes :													*/
/*	- xmlTag	: xml binding tag name 									*/
/*	- colSpan	: col span												*/
/*																		*/
/*																		*/
/*	Xml structure :														*/
/* 	<sections>															*/
/*		<section>														*/
/*			<title>	- section title - </title>							*/
/*			<lines>														*/
/*				<line>													*/
/*					line tags & values									*/
/*				</line>													*/
/*				...														*/
/*				<subTotal>												*/
/*					sub total tags & values								*/
/*				</subTotal>												*/
/*				...														*/
/*				<total>													*/
/*					total tags & values									*/
/*				</total>												*/
/*				...														*/
/*			</lines>													*/
/*		<section>														*/
/*		...																*/
/* 	</sections>															*/
/*																		*/
/*----------------------------------------------------------------------*/


/*----------------------------------------------------------------------*/
/* dynamicReportObj constructor											*/
/*----------------------------------------------------------------------*/
function dynamicReportObj (theLanguage, reportName) 
{

	// data memebers
	// -----------------------------------------------------------------

	this.objectName				= null;					// the object name
	this.destinationSpanName	= null;					// the destination span name of the report
	this.theLanguage			= theLanguage;			// the language
	
	this.direction				= "rtl";				// the direction (for styling)
	
	this.emptyMsg				= "אין נתונים מתאימים";
	this.totalTitle				= "סיכום";
	this.totalText				= "סה\"כ:&nbsp;";
	this.totalAlign				= "left";

	if (this.theLanguage == "ENG") 
	{
		this.direction  = "ltr"; 						// in english it is "ltr"
		this.emptyMsg	= "No Data Found";

		this.totalTitle = "Total";
		this.totalText	= "Total:&nbsp;";
		this.totalAlign = "right";
	}

	this.headers				= null;
	this.line					= null;
	this.subTotalLine			= null;
	this.totalLine				= null;

	// methods
	// -----------------------------------------------------------------

	// init methods
	this.setObjectName					= dynamicReportObj_setObjectName;
	this.createDestinationSpan			= dynamicReportObj_createDestinationSpan;

	// public methods
	this.setReportSection				= dynamicReportObj_setReportSection;

	this.generateReport					= dynamicReportObj_generateReport;

	this.print							= dynamicReportObj_print;

	this.getReportWidth					= dynamicReportObj_getReportWidth;

	// private methods
	this.generateSectionHtml			= dynamicReportObj_generateSectionHtml;
	this.generateSectionTitleHtml		= dynamicReportObj_generateSectionTitleHtml;
	this.generateSectionDataHtml		= dynamicReportObj_generateSectionDataHtml;
	this.generateSectionHeaderHtml		= dynamicReportObj_generateSectionHeaderHtml;
	this.generateSectionLinesHtml		= dynamicReportObj_generateSectionLinesHtml;
	

	// for debug
	this.checkLanguage					= dynamicReportObj_checkLanguage;

	// -----------------------------------------------------------------

	// init to the object : 1) set object name  2) create destination span
	this.setObjectName 			(reportName);
	this.createDestinationSpan 	();
}

/* -------------------------------------------------------------------- */
/* dynamicReportObj_checkLanguage										*/
/* -------------------------------------------------------------------- */
function dynamicReportObj_checkLanguage ()
{
	if (this.theLanguage != "HEB" && this.theLanguage != "ENG")
		throw new Error(1,"no report language - check report constructor");
}

/*----------------------------------------------------------------------*/
/* dynamicReportObj_setObjectName										*/
/*----------------------------------------------------------------------*/
function dynamicReportObj_setObjectName (objectName) 
{
	this.objectName = objectName;
}

/*----------------------------------------------------------------------*/
/* dynamicReportObj_createDestinationSpan								*/
/*----------------------------------------------------------------------*/
function dynamicReportObj_createDestinationSpan (spanName) 
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
 	throw new Error(10, error.description + "\n\t(dynamicReportObj.createDestinationSpan)");
  }
}

/*----------------------------------------------------------------------*/
/* dynamicReportObj_setReportSection									*/
/*----------------------------------------------------------------------*/
function dynamicReportObj_setReportSection (headers, line, subTotalLine, totalLine) 
{
  try
  {
	this.headers  	  = new Object(headers);
	this.line	  	  = new Object(line);
	this.subTotalLine = new Object(subTotalLine);
	this.totalLine 	  = new Object(totalLine);
  }
  catch (error)
  {
 	throw new Error(10, error.description + "\n\t(dynamicReportObj.setReportSection)");
  }
}

/*----------------------------------------------------------------------*/
/* dynamicReportObj_generateReport										*/
/*----------------------------------------------------------------------*/
function dynamicReportObj_generateReport (xml) 
{
  try
  {
	var reportSpan = eval("window." + this.destinationSpanName);

	// start the main table
	var htmlStr = 	"<table cellpadding='0' cellspacing='0' border='0'>"							+
					"<tr>"																			+
		    			"<td height='7'></td>"														+
					"</tr>"																            +
					"<tr>"															            	+
						"<td width='23'></td>"											            +
						"<td>"															            +
							"<table cellpadding='0' cellspacing='0' border='0'>";

	var sectionsNode = xml.getElementsByTagName("sections").item(0);

	if (sectionsNode != null)
	{
		var numSections	 = sectionsNode.childNodes.length;

		var sectionNode;
		for (var sectionIdx = 0; sectionIdx < numSections; sectionIdx++)
		{
			sectionNode = sectionsNode.getElementsByTagName("section").item(sectionIdx);
			
			htmlStr += this.generateSectionHtml (sectionNode);
		}
	}
		
	htmlStr += 				"</table>"												            	+
						"</td>"														            	+
					"</tr>"																			+
					"</table>";

	reportSpan.innerHTML = htmlStr;
  }
  catch (error)
  {
 	throw new Error(10, error.description + "\n\t(dynamicReportObj.generateReport)");
  }
}

/*----------------------------------------------------------------------*/
/* dynamicReportObj_generateSectionHtml									*/
/*----------------------------------------------------------------------*/
function dynamicReportObj_generateSectionHtml (sectionNode) 
{
  try
  {
  	var htmlStr = "";
	
	htmlStr +=  this.generateSectionTitleHtml (sectionNode)											+
				this.generateSectionDataHtml  (sectionNode);

	return (htmlStr);
  }
  catch (error)
  {
 	throw new Error(10, error.description + "\n\t(dynamicReportObj.generateSectionHtml)");
  }
}

/*----------------------------------------------------------------------*/
/* dynamicReportObj_generateSectionTitleHtml							*/
/*----------------------------------------------------------------------*/
function dynamicReportObj_generateSectionTitleHtml (sectionNode) 
{
  try
  {
  	var htmlStr = "";

	var titleNode = sectionNode.getElementsByTagName("title").item(0);	

	if (titleNode != null)
	{
		var title = titleNode.text;
		
		if (title == "#total")
			title = this.totalTitle;

		htmlStr += 	"<tr height='7'>"																+
						"<td></td>"																	+
					"</tr>"																			+ 
					"<tr>"																			+ 
						"<td class='styleSubTitle'>"												+
							"<u><b>"																+
								title																+
							"</u></b>"																+
						"</td>"																		+
					"</tr>"																			+
					"<tr height='4'>"																+
						"<td></td>"																	+
				   	"</tr>";
	}

	return (htmlStr);
  }
  catch (error)
  {
 	throw new Error(10, error.description + "\n\t(dynamicReportObj.generateSectionTitleHtml)");
  }
}

/*----------------------------------------------------------------------*/
/* dynamicReportObj_generateSectionDataHtml								*/
/*----------------------------------------------------------------------*/
function dynamicReportObj_generateSectionDataHtml (sectionNode) 
{
  try
  {
  	var htmlStr = "";

	htmlStr += "<tr>"																				+
					"<td>";

	linesNode = sectionNode.getElementsByTagName("lines").item(0);

	if (linesNode == null || linesNode.childNodes.length == 0)
	{
		htmlStr += "<span class='styleBlueTdText'><b>" + this.emptyMsg + "</b></span>";
	}
	else
	{
		// start section table
		htmlStr += 	"<table cellspacing='0' cellpadding='0' border='0' class='styleTableBorder'>"	+
					"<tr>"																			+
						"<td>"																		+
							"<table border='0' cellspacing='1' cellpadding='1' "					+
							"		style='direction:" + this.direction + "'>";

		// add headers
		htmlStr += this.generateSectionHeaderHtml ();

		// add lines
		htmlStr += this.generateSectionLinesHtml  (linesNode);


		// end table
		htmlStr +=			"</table>"																+
						"</td>"																		+
					"</tr>"																			+
					"</table>";
	}
					
	htmlStr +=		"</td>"																			+
			   "</tr>";

	return (htmlStr);
  }
  catch (error)
  {
 	throw new Error(10, error.description + "\n\t(dynamicReportObj.generateSectionDataHtml)");
  }
}

/*----------------------------------------------------------------------*/
/* dynamicReportObj_generateSectionHeaderHtml							*/
/*----------------------------------------------------------------------*/
function dynamicReportObj_generateSectionHeaderHtml () 
{
  try
  {
  	var htmlStr = "";

	if (this.headers != null && this.headers.length != 0)
	{
		htmlStr += 	"<tr class='styleTableHeadTitle'>";

		var header;
		var widthIdx = 0;
		for (var i=0; i < this.headers.length; i++)
		{
			header = this.headers[i];

			// calculate column width according to col span
			widthVal = 0;

			for (var w = 0; w < header.span; w++)
			{
				widthVal += (eval("this.line[widthIdx].width" + this.theLanguage)*1) + (w % 2)*2;
				widthIdx++;
			}
			width = "width='" + widthVal + "'";

			htmlStr +=			"<td colspan='" + header.span + "' " + width + ">"					+
									"&nbsp;" + eval("header.text" + this.theLanguage)				+
								"</td>";
		}

		htmlStr +=	"</tr>";
	}

	return (htmlStr);
  }
  catch (error)
  {
 	throw new Error(10, error.description + "\n\t(dynamicReportObj.generateSectionHeaderHtml)");
  }
}

/*----------------------------------------------------------------------*/
/* dynamicReportObj_generateSectionLinesHtml							*/
/*----------------------------------------------------------------------*/
function dynamicReportObj_generateSectionLinesHtml (linesNode) 
{
  try
  {
  	var htmlStr  = "";
  	var linesNum = linesNode.childNodes.length;

	var currLine;
	var lineClass;
	var lineDef;
	var setWidth;
	for (var i = 0; i < linesNum; i++)
	{
		lineDef   = "";
		lineClass = "";
		setWidth  = false;

		currLine = linesNode.childNodes(i);

		switch (currLine.tagName)
		{
			case "line"		:	lineClass = "styleGrayTrNoHeight";	
								lineDef   = this.line;
								setWidth  = true;
								
								break;

			case "subTotal"	: 	lineClass = "styleTotalTR";
								lineDef	  = this.subTotalLine;
								break;

			case "total"	:	lineClass = "styleTotalTR";
								lineDef	  = this.totalLine;
								setWidth  = (linesNum == 1);

								break;
		}

		// add new line
		htmlStr  += "<tr class='" + lineClass + "'>";

		var widthIdx = 0;
		for (var d = 0; d < lineDef.length; d++)
		{
			width = ""
			align = "";

			currDef = lineDef[d];
	
			if (setWidth)
			{
				// calculate column width according to col span
				widthVal = 0;

				for (var w = 0; w < currDef.span; w++)
				{
					widthVal += (eval("this.line[widthIdx].width" + this.theLanguage)*1) + (w % 2)*2;
					widthIdx++;
				}
				width = "width='" + widthVal + "'";
			}
		
			if (currDef.xmlTag == "")	// this is the total text
			{
				data  = this.totalText;
				align = "style='text-align:" + this.totalAlign + "'";
			}
			else
			{
				data = "";

				if (currLine.getElementsByTagName(currDef.xmlTag).item(0) != null)
					data = currLine.getElementsByTagName(currDef.xmlTag).item(0).text;
			}
			
			htmlStr += "<td " + width + " colspan='" + currDef.span + "' " + align + ">"			+
							"&nbsp;" + data															+
					   "</td>";
		}

		// end line
		htmlStr  += "</tr>";
	}

	return (htmlStr);
  }
  catch (error)
  {
 	throw new Error(10, error.description + "\n\t(dynamicReportObj.generateSectionLinesHtml)");
  }
}

/*----------------------------------------------------------------------*/
/* dynamicReportObj_print												*/
/*----------------------------------------------------------------------*/
function dynamicReportObj_print () 
{
  try
  {
  }
  catch (error)
  {
 	throw new Error(10, error.description + "\n\t(dynamicReportObj.print)");
  }
}

/*----------------------------------------------------------------------*/
/* dynamicReportObj_getReportWidth										*/
/*----------------------------------------------------------------------*/
function dynamicReportObj_getReportWidth ()
{
  try
  {
  	this.checkLanguage ();

	var width = 0;
	for (var i=0; i<this.line.length; i++)
	{
		width += eval("this.line[i].width" + this.theLanguage)*1 + 2;
	}

	width += this.line.length*2;

	return width;
  }
  catch (error)
  {
 	throw new Error(10, error.description + "\n\t(dynamicReportObj.getTableWidth)");
  }
}
