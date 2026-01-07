/* ---------------------------------------------------------------- */
/*																	*/
/*						serverInterfaceObj.js						*/
/*						---------------------						*/
/*																	*/
/* ---------------------------------------------------------------- */

/* ---------------------------------------------------------------- */
/* serverInterfaceObj constructor									*/ 
/* ---------------------------------------------------------------- */
function serverInterfaceObj (theLanguage)
{
  try
  {
	if (theLanguage == "" || theLanguage == undefined)
		theLanguage = commonGetGlobalData("guiLang");

	if (theLanguage == "" || theLanguage == undefined)
		theLanguage = "HEB";
			
	if (theLanguage != "HEB" && theLanguage != "ENG")
	{
		var error = new Error(30,"Unknown language (" + theLanguage + ")");
		showError ("serverInterfaceObj", "constructor", error);
		throw error
	}

	this.debug			    = false;			// in debug mode ?
	this.xmlDebug		    = false;			// in xml debug mode ?

	this.theLanguage        = theLanguage; 		// the communication language

	this.server				= "../../php/server.php";

	this.requestName		= "";
	this.requestId			= "";
	this.requestTags		= new Array();
	this.requestXml			= "";

	// methods
	// ------------------------------------------------------------
	this.setDebug			= serverInterfaceObj_setDebug;
	this.setXmlDebug		= serverInterfaceObj_setXmlDebug;

	this.setServer			= serverInterfaceObj_setServer;
	this.getRequestXml		= serverInterfaceObj_getRequestXml;
	this.sendRequest		= serverInterfaceObj_sendRequest;
	this.sendRequestXml		= serverInterfaceObj_sendRequestXml;
	this.showDebugWin		= serverInterfaceObj_showDebugWin;
	this.showExcelReport	= serverInterfaceObj_showExcelReport;

	this.cleanTags			= serverInterfaceObj_cleanTags;
	this.cleanRequest		= serverInterfaceObj_cleanRequest;
	this.addTag				= serverInterfaceObj_addTag;
	this.addTags			= serverInterfaceObj_addTags;
	this.addDummyTag		= serverInterfaceObj_addDummyTag;

	this.setXml				= serverInterfaceObj_setXml;

	// add waiting overlay (cursor - wait)
	$(document).ready(function() 
	{
		if (document.getElementById("waitingOverlay") == undefined)
		{
			oDiv = document.createElement("DIV");
			oDiv.id = "waitingOverlay";
			oDiv.style.display = "none";

			document.body.appendChild (oDiv);
		}
	});
  }
  catch (error)
  {
  	if (this.debug)
		showError ("serverInterfaceObj", "constructor", error);
  }
}

/* ---------------------------------------------------------------- */
/* serverInterfaceObj_setDebug										*/
/* ---------------------------------------------------------------- */
function serverInterfaceObj_setDebug (doDebug)
{
	this.debug = doDebug;
}

/* ---------------------------------------------------------------- */
/* serverInterfaceObj_setXmlDebug									*/
/* ---------------------------------------------------------------- */
function serverInterfaceObj_setXmlDebug (doDebug)
{
	this.xmlDebug = doDebug;
}

/* ---------------------------------------------------------------- */
/* serverInterfaceObj_setServer										*/
/* ---------------------------------------------------------------- */
function serverInterfaceObj_setServer (server)
{
	this.server = server;
}

/* ---------------------------------------------------------------- */
/* serverInterfaceObj_cleanTags										*/
/* ---------------------------------------------------------------- */
function serverInterfaceObj_cleanTags ()
{
	while (this.requestTags.length != 0)
		this.requestTags.pop ();
}

/* ---------------------------------------------------------------- */
/* serverInterfaceObj_addTag										*/
/* ---------------------------------------------------------------- */
function serverInterfaceObj_addTag (name, value)
{
	newTag = {name : name, value : value}
	
	this.requestTags.push (newTag);
}

/* ---------------------------------------------------------------- */
/* serverInterfaceObj_addDummyTag									*/
/* ---------------------------------------------------------------- */
function serverInterfaceObj_addDummyTag (name, value)
{
	newTag = {name : "dummy_" + name, value : value}
	
	this.requestTags.push (newTag);
}

/* ---------------------------------------------------------------- */
/* serverInterfaceObj_addTags										*/
/* ---------------------------------------------------------------- */
function serverInterfaceObj_addTags (name, values)
{
	for (var i = 0; i < values.length; i++)
	{
		newTag = {name : name, value : values[i]}
	
		this.requestTags.push (newTag);
	}
}

/* ---------------------------------------------------------------- */
/* serverInterfaceObj_cleanRequest									*/
/* ---------------------------------------------------------------- */
function serverInterfaceObj_cleanRequest ()
{
	this.cleanTags ();
	this.requestXml = "";
}

/* ---------------------------------------------------------------- */
/* serverInterfaceObj_setXml										*/
/* ---------------------------------------------------------------- */
function serverInterfaceObj_setXml (theXml)
{
	this.cleanTags();

	if (jQuery.isXMLDoc(theXml))
	{
		this.requestXml = theXml.xml ? theXml.xml : (new XMLSerializer()).serializeToString(theXml);
	}
	else
	{
		this.requestXml = theXml;
	}
}

/* ---------------------------------------------------------------- */
/* serverInterfaceObj_getRequestXml									*/
/* ---------------------------------------------------------------- */
function serverInterfaceObj_getRequestXml ()
{
  try
  {
  	var xmlStr = "";
	
	if (this.requestTags != "")
	{
		for (i=0; i<this.requestTags.length; i++)
		{
			xmlStr +=			"<"  + this.requestTags[i].name + ">"						+
									this.requestTags[i].value								+
								"</" + this.requestTags[i].name + ">";
		}
	}
	
	if (this.requestXml != "")
	{
		xmlStr += this.requestXml;
	}
	
	return xmlStr;
  }
  catch (error)
  {
  	if (this.debug)
		showError ("serverInterfaceObj", "getRequestXml", error);
  }
}

/* ---------------------------------------------------------------- */
/* serverInterfaceObj_sendRequest									*/
/* ---------------------------------------------------------------- */
function serverInterfaceObj_sendRequest (requestName, paging, asyncReturnFunc)
{
  try
  {
	document.getElementById("waitingOverlay").style.display = "";

	this.requestName = requestName;

	if (this.xmlDebug)
		debug = 1;
	else
		debug = 0;

	try
	{
		usedLangs = commonGetGlobalData("langArray").join(",");
	}
	catch (e)
	{
		usedLangs = "";
	}

	var theUserId	  	= commonGetGlobalData("userId");
	var theSessionCode	= commonGetGlobalData("sessionCode");

	var oDate	  		= new Date();
	this.requestId 		= theUserId + "_" + oDate.getTime() + "_" + theSessionCode;

    xmlStr  =   "<interuse> "                 +
                        "<commandRequest> "     +
                            "<command>"         + this.requestName      	+ "</command> "     +
                            "<language>"        + this.theLanguage      	+ "</language> "    +
							"<debugXml>"		+ debug						+ "</debugXml> "	+
							"<sessionCode>"		+ theSessionCode			+ "</sessionCode> " +
							"<userId>"			+ theUserId					+ "</userId> " 		+
							"<requestId>"		+ this.requestId			+ "</requestId> "	+
							"<usedLangs>"		+ usedLangs					+ "</usedLangs> " 	+
                            "<" + this.requestName + "> ";

  	xmlStr += this.getRequestXml ();

	if (paging != undefined)
	{
		xmlStr += paging;
	}

	xmlStr += 				"</" + this.requestName + "> "      		+
                        "</commandRequest> "    +
                     "</interuse> ";

	if (xmlStr.indexOf("pleaseWaitMsg") != -1)
	{
		commonMsgBox ("info", "!טעינת הנתונים לא הסתיימה. לא לבצע שמירה", "");
		return false;
	}

	return this.sendRequestXml (xmlStr, asyncReturnFunc);
  }
  catch (error)
  {
  	if (this.debug)
		showError ("serverInterfaceObj", "sendRequest", error);
  }
}

/* ---------------------------------------------------------------- */
/* serverInterfaceObj_sendRequestXml								*/
/* ---------------------------------------------------------------- */
function serverInterfaceObj_sendRequestXml (xmlStr, asyncReturnFunc)
{
  try
  {
	var requestXmlObj = new xmlObj(false);

    requestXmlObj.init (commonEncode(xmlStr));

	if (typeof(asyncReturnFunc) !== 'undefined')
	{
			requestXmlObj.sendAsyncRequest(this.server, requestXmlObj.obj, asyncReturnFunc);
			return;
	}

    requestXmlObj.sendRequest(this.server, requestXmlObj.obj);
	
	if (this.xmlDebug)
	{
		this.showDebugWin();
	}

    if (requestXmlObj.reLogin ())
    {
        requestXmlObj.init(commonEncode(xmlStr));
        requestXmlObj.sendRequest(this.server, requestXmlObj.obj);
    }

	if (requestXmlObj.isError())
    {
        errorMsg = requestXmlObj.getErrorMsg();
        commonMsgBox ("info", errorMsg,errorMsg);
	}

	if (requestXmlObj.isDebug())
    {
        debugMsg = requestXmlObj.getDebugMsg();
        commonMsgBox ("debug", debugMsg,debugMsg);
	}

    if (requestXmlObj.isSuccess(this.requestName) || requestXmlObj.isDuplicate())
    {
		requestXmlObj.init (commonDecode(requestXmlObj.obj.xml));

		var commandName = this.requestName.split(".")[1];
		var commandNode = requestXmlObj.obj.getElementsByTagName(commandName).item(0);

		if (requestXmlObj.getValue("requestId") != this.requestId)
			return null;
		else
			return commandNode;
	}

	return null;
  }
  catch (error)
  {
	if ((error.number & 0xFFFF) == 5)
	{
		alert ("(פריטים קופצים) popups על מנת להתחבר למערכת יש להוריד חסימת ");
		top.location.replace("../../../index.php");
	}
	else
	{
	  	if (this.debug)
			showError ("serverInterfaceObj", "sendRequestXml", error);
	}
  }
}

/* ---------------------------------------------------------------- */
/* serverInterfaceObj_showDebugWin									*/
/* ---------------------------------------------------------------- */
function serverInterfaceObj_showDebugWin ()
{

	var height = screen.availHeight - 100;
	var width  = screen.availWidth  - 100;

	html = "<html>"																	+
		   "<head>"																	+
			"<title>" + this.requestName + "</title>"								+
		   "</head>"																+
		   "<body>"																	+
				"<b>Request : </b><br/>"											+
				"<iframe src='../xmlDebug/in.xml' "									+
				"		 width='100%' height='45%'></iframe>"						+
				"<br/><br/>"														+
				"<b>Response : </b><br/>"											+
				"<iframe src='../xmlDebug/out.xml' "								+
				"		 width='100%' height='45%'></iframe>"						+
		   "</body>"																+
		   "</html>";

	// open window for the request (same window for same request)
	win = window.open  (this.requestName + ".html", this.requestName,
						"height=" + height + ",width=" + width + ",left=0,top=0");

	win.document.open  ();
	win.document.write (html);
	win.document.close ();
}

/* ---------------------------------------------------------------- */
/* serverInterfaceObj_showExcelReport								*/
/* ---------------------------------------------------------------- */
function serverInterfaceObj_showExcelReport (requestName, reportNameHEB, reportNameENG)
{
  try
  {
	theLanguage = "HEB";

  	var reportName = "";
  	if (theLanguage == "HEB" && reportNameHEB != undefined)
	{
		reportName = reportNameHEB;
	}

	if (theLanguage == "ENG" && reportNameENG != undefined)
	{
		reportName = reportNameENG;
	}
	
	this.addTag ("reportFormat", "Excel");
	this.addTag ("reportName",   reportName);

	this.sendRequest (requestName, undefined, "serverInterfaceObj_showExcelReport_continue");
  }
  catch (error)
  {
  	if (this.debug)
		showError ("serverInterfaceObj", "showExcelReport", error);
  }
}

function serverInterfaceObj_showExcelReport_continue (i)
{
  try
  {
	var responseXml = asyncResponseXml.getResponseXml (i);
	
	if (responseXml != null)
	{
		var excelFileName = commonGetInnerData(responseXml, "excelFileName");

		var height = screen.availHeight - 100;
		var width  = screen.availWidth  - 100;

		var y = 0;
		var x = (screen.availWidth  - width)  / 2;

		window.open ("http://www.i-bos.co.il/3.0/tempExcels/" + excelFileName, "",
					 "status=no,toolbar=no,menubar=yes,height=" + height + ", "	+
					 "								   width =" + width  + ", left="+x+", top="+y);
	}
	return true;
  }
  catch (error)
  {
  	if (this.debug)
		showError ("serverInterfaceObj", "showExcelReport", error);
  }
}

