/* ---------------------------------------------------------------- */
/* 																	*/
/*							xmlObj.js								*/
/*							---------								*/
/*																	*/
/* ---------------------------------------------------------------- */

/* ---------------------------------------------------------------- */
/* xmlObj constructor												*/
/* ---------------------------------------------------------------- */
function xmlObj (async) 
{
	// data memebers
	//this.obj = new ActiveXObject("Microsoft.XMLDOM"); 	// the xml object to operate with
	if (window.DOMParser)
	{ 
	    this.obj 	 = new Object();
		this.obj.xml = "";
	}
	else
	{
	    this.obj	= new ActiveXObject("Microsoft.XMLDOM");
	} 

	this.async = async; 							  	// a boolean telling whether the process is async or not

	this.command = "";

	// methods
	this.init 		 = xmlObj_init;					  	// initializing the xml object
	this.sendRequest = xmlObj_sendRequest;				// sending a request to the server
	this.sendAsyncRequest = xmlObj_sendAsyncRequest;	// sending an asynchronous request to the server

	this.getXMLRequestObject	= xmlObj_getXMLRequestObject;

	this.isEmpty	 = xmlObj_isEmpty;					// checking if we got any xml response

	this.isSuccess 	 = xmlObj_isSuccess;			  	// checking if we got information back 
	this.isDuplicate = xmlObj_isDuplicate;			  	// checking if we got duplicate request
	this.isError	 = xmlObj_isError;				  	// checking if we got an error back
	this.isDebug	 = xmlObj_isDebug;				  	// checking if we got an debug msg back
	this.isInfo	 	 = xmlObj_isInfo;				  	// checking if we got an info back
	this.isRequire	 = xmlObj_isRequire;			  	// checking if we got a require command back
	this.isReLogin	 = xmlObj_isReLogin;				// checking if we got session expire back

	this.getErrorMsg = xmlObj_getMessage;				// returning message text
	this.getDebugMsg = xmlObj_getMessage;				// returning message text
	this.commandNode = xmlObj_commandNode; 				// getting the actual command node
	this.resultCode  = xmlObj_resultCode;				// parsing the result code

	this.getValue    = xmlObj_getValue;
	this.setValue    = xmlObj_setValue;

	this.countNodes	 = xmlObj_countNodes;

	this.getNodeXml		= xmlObj_getNodeXml;
	this.getResponseXml	= xmlObj_getResponseXml;
}

/* ---------------------------------------------------------------- */
/* xmlObj_init														*/
/*																	*/
/*		Initializing an xml object with the appropriate xml.		*/
/* ---------------------------------------------------------------- */
function xmlObj_init(xmlData) 
{
	var xmlPrefix="<?xml version='1.0' encoding='UTF-8' ?>";
//	var xmlPrefix="<?xml version='1.0' encoding='ISO-8859-8' ?>";
//	var xmlPrefix="";

	if (xmlData.indexOf("xml version") != -1)
		xmlPrefix = "";

	if (window.DOMParser)
	{ 
		this.obj.xml = xmlPrefix + xmlData;
	}
	else
	{
	    this.obj.async=false;
	    this.obj.loadXML(xmlPrefix + xmlData); 
	} 
}

/* ---------------------------------------------------------------- */
/* xmlObj_getXMLRequestObject										*/
/* ---------------------------------------------------------------- */
function xmlObj_getXMLRequestObject () 
{
	/* if native support, create & return object */
	if (window.XMLHttpRequest != null) 
	{
		return new XMLHttpRequest (); // for Mozilla/Opera/Safari/Konqueror
	} 
	else 
	{
		/* Build MS XMLHTTP version list - newest first */
		var MSXML_XMLHTTP_PROGIDS = new Array  ('MSXML2.XMLHTTP.6.0',
												'MSXML2.XMLHTTP.4.0',
												'MSXML2.XMLHTTP.3.0',
												'MSXML2.XMLHTTP',
												'Microsoft.XMLHTTP');

		/* Look for supported IE version */
		for (i = 0; MSXML_XMLHTTP_PROGIDS.length > i; i++) 
		{
			try 
			{
				return new ActiveXObject (MSXML_XMLHTTP_PROGIDS[i]);
			}
			catch (e) {}
		}
	}
}


/* ---------------------------------------------------------------- */
/* xmlObj_sendRequest												*/
/*																	*/
/*		params : url - the url to send the request to				*/
/*				 requestObj - an XML object to be sent if needed	*/
/*																	*/
/* ---------------------------------------------------------------- */
function xmlObj_sendRequest(url, requestObj) 
{
	try 
	{
		try
		{
			numRetries = 3;
			for (var i=0; i<numRetries; i++)
			{
				try
				{
					var httpObj = this.getXMLRequestObject();
	
					httpObj.open("POST", url, true);
				}
				catch (error)
				{
					this.init ("<local> " +
									"<response> " +
										"<responseType>Error</responseType> " +
										"<result>1001</result> " +
										"<message>(" + error.description + ") בעיה 1001</message>" +
									"</response> " +
							  "</local> ");
					return false;
				}

				httpObj.setRequestHeader("Content-type","text/xml");

				try
				{
					this.command = this.getValue ("command");

					httpObj.send(requestObj.xml);
				}
				catch (error)
				{
					this.init ("<local> " +
									"<response> " +
										"<responseType>Error</responseType> " +
										"<result>1005</result> " +
										"<message>בעיית תקשורת (1005). נא לצאת ללא שמירה ולנסות שוב<br/>" + 
												  "(" + error.description + " - " + error.number + ")</message>" +
									"</response> " +
							   "</local> ");

//										"<message>(" + error.description + " - " + error.number + ") בעיה 1005</message>" +
					httpObj.abort ();
					return false;
				}
	
				// [AMIR 12.12.08 - Handle the case in which someone wants asyncronous AJAX call
				if (this.async === true)
				{
						httpObj.onreadystatechange=function() 
						{
							if (xmlhttp.readyState==4) 
							{
								this.obj.loadXML(httpObj.responseText);
								var callbackHandler = commonGetInnerData (this.obj, "callbackHandler");

//								window.setTimeout (callbackHandler + "(xmlhttp.responseText)",0);
								eval (callbackHandler + "(xmlhttp.responseText)");

//								eval(requestObj.xml.getValue("callbackHandler") + xmlhttp.responseText));
							}
						}
						return true;
				}

//				requestObj.getElementsByTagName("command").item(0).text
				commonWaitingBox ("waiting", "אנא המתן...<br/>(" + this.command + ")", "Please wait...", httpObj, i);
			
				if (httpObj.readyState == 4)
				{
					break;
				}
//				alert("failed at trial " + i);
				httpObj.abort ();
			}		
		}
		catch (error)
		{
			this.init ("<local> " +
							"<response> " +
								"<responseType>Error</responseType> " +
								"<result>1002</result> " +
								"<message>(" + error.description + ") בעיה 1002</message>" +
							"</response> " +
					   "</local> ");
			httpObj.abort ();
			return false;
		}

		if (httpObj.readyState != 4)
		{
			this.init ("<local> " +
							"<response> " +
								"<responseType>Error</responseType> " +
								"<result>2000</result> " +
								"<message>בעיית תקשורת - נא לנסות שוב</message>" +
							"</response> " +
					   "</local> ");
			httpObj.abort ();
			return false;
		}

		//alert (requestObj.xml);
		//alert (httpObj.responseText.substr(0,800));
		//alert (httpObj.responseText);

		if (httpObj.status == 200) 
		{
			try
			{
				this.obj.loadXML(httpObj.responseText);
			}
			catch (error)
			{
				this.init ("<local> " +
								"<response> " +
									"<responseType>Error</responseType> " +
									"<result>1003</result> " +
									"<message>(" + error.description + " - " + error.number + ") בעיה 1003</message>" +
								"</response> " +
						   "</local> ");
				httpObj.abort ();
				return false;
			}

			try
			{
				if (this.obj.parseError.errorCode != 0)
				{
					this.init ("<local> " +
									"<response> " +
										"<responseType>Error</responseType> " +
										"<result>1111</result> " +
										"<message>בעיה 1111 - נא להתקשר לתמיכה          (Xml Parse error : " + this.obj.parseError.srcText + ") </message>" +
									"</response> " +
							   "</local> ");
					return false;
					alert ("Xml Parse error in line " + this.obj.parseError.line + " : " + this.obj.parseError.reason +
						   "\n >> " + this.obj.parseError.srcText);
				}
			}
			catch (e)
			{
				this.init ("<local> " +
								"<response> " +
									"<responseType>Error</responseType> " +
									"<result>1004</result> " +
									"<message>(" + error.description + ") בעיה 1004</message>" +
								"</response> " +
						   "</local> ");
				httpObj.abort ();
				return false;
			}
		}
		else 
		{
			this.init ("<local> " +
							"<response> " +
								"<responseType>Error</responseType> " +
								"<result>2001</result> " +
								"<message>" + httpObj.status + "</message>" +
							"</response> " +
					   "</local> ");
			httpObj.abort ();
			return false;
		}
	}
	catch (error) 
	{
		this.init ("<local> " +
						"<response> " +
							"<responseType>Error</responseType> " +
							"<result>3000</result> " +
							"<message>בעיית תקשורת - נא לנסות שוב !!!</message>" +
						"</response> " +
				   "</local> ");
		httpObj.abort ();
	}
}

var asyncHttpObj;
var asyncHttpObjs = new Array();
var currHttp = -1;
var asyncResponseXml = new xmlObj(false);

var globalXmlObj = "";

/* ---------------------------------------------------------------- */
/* xmlObj_sendAsyncRequest											*/
/* ---------------------------------------------------------------- */
function xmlObj_sendAsyncRequest(url, requestObj, functionName, args) 
{
	try 
	{
		asyncResponseXml.init (requestObj.xml);

		if (currHttp == 20)
			currHttp = 0;
		else
			currHttp++;

		var myCurrHttp = currHttp;

		if (asyncHttpObjs[currHttp] == undefined)
			asyncHttpObjs[currHttp] = xmlObj_getXMLRequestObject();

		var currAsyncHttpObj = asyncHttpObjs[currHttp];

		currAsyncHttpObj.open("POST", url, true);
		currAsyncHttpObj.setRequestHeader("Content-Type","text/xml");

		currAsyncHttpObj.onreadystatechange= function() 
		{
		  	if (currAsyncHttpObj.readyState==4) 
		  	{
				asyncHttpObj = asyncHttpObjs[myCurrHttp];

				var requestXmlObj = new xmlObj(false);

				requestXmlObj.init(asyncHttpObj.responseText);

				document.getElementById("waitingOverlay").style.display = "none";

				if (requestXmlObj.isReLogin())
				{
					// create relogin dialog (if not already created)
					if (document.getElementById("reloginDialog") == undefined)
					{
						var oDialog = document.createElement("DIV"); 
						oDialog.id 	= "reloginDialog";
						oDialog.style.display = "none";

						document.body.appendChild(oDialog); 

						var oIframe = document.createElement("IFRAME");
						oIframe.setAttribute ("src", "../../html/general/reLogin.html")
						oIframe.setAttribute ("width",  400)
						oIframe.setAttribute ("height", 200)
						oIframe.setAttribute ("frameborder", 0)
						oIframe.setAttribute ("scrolling", "no")

						oDialog.appendChild(oIframe); 
					}

					// save resend request details
        			asyncResponseXml.init(commonEncode(asyncResponseXml.obj.xml));

					var resendCommand = asyncResponseXml.getValue("command");

					commonSetGlobalData ("resendCommand", 	resendCommand);
					commonSetGlobalData ("resendXml", 		asyncResponseXml.getNodeXml (asyncResponseXml.obj.xml, resendCommand));
					commonSetGlobalData ("resendFunction",  functionName);
					
					$("#reloginDialog").dialog({
						modal: true,
						width: 400,
						height: 204,
						resizable: false,
						dialogClass: "no-close"
				    });

					return;
				}

				try
				{
					if (requestXmlObj.isError())
					{
        				errorMsg = requestXmlObj.getErrorMsg();

						globalXmlObj = requestXmlObj;
						
						if (errorMsg.indexOf("have an error") !== -1 || errorMsg.indexOf("Query was") !== -1)
						{
							errorMsg = "<span style='display:block;font-weight:bold;color:#D1333B;padding-bottom:8px'>שגיאת מערכת!</span>" + 
									   "האם ברצונך לשלוח דיווח לתמיכה?";

							commonMsgBox ("confirm", errorMsg, "", "sendQueryError");
						}
						else
						{
					        commonMsgBox ("info", errorMsg,errorMsg);
						}
						return;
					}
				}
				catch(e)
				{
				}

				if (args == undefined)
					window.setTimeout (functionName + "(" + myCurrHttp + ")",0);	
				else
					window.setTimeout (functionName + "(args," + myCurrHttp + ")",0);
			}
		}

		if (window.ActiveXObject)
			currAsyncHttpObj.send(commonEncode(requestObj.xml));
		else
			currAsyncHttpObj.send(commonEncode(requestObj.xml));
	}
	catch (error) 
	{
		this.init (	" <local> " +
						"<response> " +
							"<responseType>Error</responseType> " +
							"<result>3000</result> " +
							"<message>Communication problem (" + error.description + ")</message> " +
						"</response> " +
					" </local> ");
	}
}

/* ---------------------------------------------------------------- */
/* sendQueryError													*/
/* ---------------------------------------------------------------- */
function sendQueryError ()
{
	$.ajax({type : "POST",
			url  : "../../php/errorServer.php",
			data : {action		: "reportError",
					error		: globalXmlObj.getErrorMsg(),
					sessionCode	: commonGetGlobalData("sessionCode")}
	}).done(function (response) { setTimeout(sendQueryError_done(), 8000); });

	return;
}

function sendQueryError_done ()
{
	commonMsgBox("info", "<span style='width:260px;display:block;margin-bottom:8px;font-weight:bold;color:#829E22'>תודה!</span>השגיאה נשלחה לתמיכה<br/>ותטופל בהקדם.", "");
}

/* ---------------------------------------------------------------- */
/* xmlObj_isEmpty													*/
/* ---------------------------------------------------------------- */
function xmlObj_isEmpty ()
{
	if (this.obj == null || this.obj.xml == "") 
	{
		this.init(	" <local> " +
						" <response> "+
							" <responseType>Error</responseType> " +
							" <result>3000</result> " +
							" <message>לא התקבל מידע מהשרת. יש לנסות שוב (" + this.command + ")</message> " +
						" </response> "+
					" </local> " );
		return true;
	}
	return false;
}

/* ---------------------------------------------------------------- */
/* xmlObj_isSuccess													*/
/* ---------------------------------------------------------------- */
function xmlObj_isSuccess (command)
{
	var isSuccess;

	if (this.isEmpty ())
		isSuccess = false;
	else
	{
		var responseType = this.getValue ("responseType");

		if (responseType == "Success")
		{
			var requestCommand = this.getValue ("command");
			
			isSuccess = (requestCommand == command);
		}
		else
			isSuccess = false;
	}
	return isSuccess;
}

/* ---------------------------------------------------------------- */
/* xmlObj_isDuplicate												*/
/* ---------------------------------------------------------------- */
function xmlObj_isDuplicate ()
{
	var isDuplicate;

	if (this.isEmpty ())
		isDuplicate = false;
	else
	{
		var responseType = this.getValue ("responseType");

		isDuplicate = (responseType == "Duplicate");
	}
	return isDuplicate;
}

/* ---------------------------------------------------------------- */
/* xmlObj_isError													*/
/* ---------------------------------------------------------------- */
function xmlObj_isError ()
{
	var isError;

	if (this.isEmpty ())
		isError = false;
	else
	{
		var responseType = this.getValue ("responseType");

		isError = (responseType == "Error");
	}
	return isError;
}

/* ---------------------------------------------------------------- */
/* xmlObj_isDebug													*/
/* ---------------------------------------------------------------- */
function xmlObj_isDebug ()
{
	var isDebug;

	if (this.isEmpty ())
		isDebug = false;
	else
	{
		var responseType = this.getValue ("responseType");

		isDebug = (responseType == "Debug");
	}
	return isDebug;
}

/* ---------------------------------------------------------------- */
/* xmlObj_isInfo													*/
/* ---------------------------------------------------------------- */
function xmlObj_isInfo ()
{
	var isInfo;

	if (this.isEmpty ())
		isInfo = false;
	else
	{
		var responseType = this.getValue ("responseType");

		isInfo = (responseType == "Info");
	}
	return isInfo;
}

/* ---------------------------------------------------------------- */
/* xmlObj_isRequire													*/
/* ---------------------------------------------------------------- */
function xmlObj_isRequire ()
{
	var isRequire;

	if (this.isEmpty ())
		isRequire = false;
	else
	{
		var responseType = this.getValue ("responseType");

		isRequire = (responseType == "Require");
	}
	return isRequire;
}

/* ---------------------------------------------------------------- */
/* xmlObj_isReLogin													*/
/* ---------------------------------------------------------------- */
function xmlObj_isReLogin ()
{
	var isReLogin;

	if (this.isEmpty ())
		isReLogin = false;
	else
	{
		var responseType = this.getValue ("responseType");

		isReLogin = (responseType == "SessionExpired");
	}
	return isReLogin;
}

/* ---------------------------------------------------------------- */
/* xmlObj_getMessage												*/
/* ---------------------------------------------------------------- */
function xmlObj_getMessage() 
{
	var msgText = commonDecode(this.getValue ("message"));

	if (msgText == "")
		msgText = "בעיה במערכת";

//	alert (msgText);
	if (msgText.indexOf ("500") != -1) 
		return "שרת המערכת לא מחזיר תשובה";

	if (msgText.indexOf ("12152") != -1) 
		return "שרת המערכת לא מחזיר תשובה - נא לנסות שוב";

	return msgText;
	
}

/* ---------------------------------------------------------------- */
/* xmlObj_commandNode												*/
/* ---------------------------------------------------------------- */
function xmlObj_commandNode() 
{
	var commandName = this.getValue("command");
	
	if (commandName == "") return;
	
	var commandNode = this.obj.getElementsByTagName(commandName).item(0);

	return commandNode;
}

/* ---------------------------------------------------------------- */
/* xmlObj_resultCode												*/
/* ---------------------------------------------------------------- */
function xmlObj_resultCode() 
{
	return this.getValue("result");
}

/* ---------------------------------------------------------------- */
/* xmlObj_getValue													*/
/* ---------------------------------------------------------------- */
function xmlObj_getValue (tagName)
{
	if (window.DOMParser)
	{ 
	    var parser	= new DOMParser();
		try
		{
			var xmlDoc 	= parser.parseFromString(this.obj.xml,"text/xml");
		}
		catch (e)
		{
			alert (this.obj.xml);
		}
	}
	else
	{
		var xmlDoc	= this.obj;
	}

	var theNode = xmlDoc.getElementsByTagName(tagName).item(0);

	if (theNode == null) 
		return "";
	else
	{
		if (window.DOMParser)
			return theNode.textContent;
		else
			return theNode.text
	}
}

/* ---------------------------------------------------------------- */
/* xmlObj_setValue													*/
/* ---------------------------------------------------------------- */
function xmlObj_setValue (tagName,value)
{
	var theNode = this.obj.getElementsByTagName(tagName).item(0);
    if (theNode != null) 
		theNode.text = value;
}

/* ---------------------------------------------------------------- */
/* xmlObj_countNodes												*/
/* ---------------------------------------------------------------- */
function xmlObj_countNodes () 
{
    if (this.obj != null && this.obj.xml != "") 
		return this.obj.documentElement.childNodes.length;
	else
		return 0;
}

/* ---------------------------------------------------------------- */
/* xmlObj_getNodeXml												*/
/* ---------------------------------------------------------------- */
function xmlObj_getNodeXml (xmlStr, tagName)
{
	this.init(xmlStr);

	if (window.DOMParser)
	{ 
	    var parser	= new DOMParser();
		var xmlDoc 	= parser.parseFromString(xmlStr,"text/xml");
	}
	else
	{
		var xmlDoc = this.obj;
	}

	return xmlDoc.getElementsByTagName(tagName).item(0);
}
															
/* ---------------------------------------------------------------- */
/* xmlObj_getResponseXml											*/
/* ---------------------------------------------------------------- */
function xmlObj_getResponseXml (i)
{
	var xml = commonDecode(asyncHttpObjs[i].responseText);

	return this.getNodeXml (xml, "responseData");
	
}


