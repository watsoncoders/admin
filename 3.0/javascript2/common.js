/* ---------------------------------------------------------------- */
/* 																	*/
/*							Common.js								*/
/*							---------								*/
/* Important: All the common function starts with the word 'common' */
/*			  in order to identify them from the outside.			*/
/*																	*/
/* ---------------------------------------------------------------- */

var winTitleCol		= {textHEB	: "כותרת הדפדפן", 	textENG	: "Window title", 		xmlTag	: "winTitle"	}
var rewriteNameCol 	= {textHEB  : "כתובת סטטית",	textENG : "Literal URL", 		xmlTag 	: "rewriteName"	}
var keywordsMetaCol	= {textHEB  : "מילות מפתח",		textENG : "Meta Keywords", 		xmlTag 	: "keywords"	}
var robotsCol		= {textHEB  : "ROBOTS",			textENG : "ROBOTS", 			xmlTag 	: "robots"		}
var descMetaCol		= {textHEB  : "תיאור הדף",		textENG : "Meta Description", 	xmlTag 	: "description"	}
var layoutCol		= {textHEB	: "תבנית עיצוב", 	textENG	: "Layout", 			xmlTag	: "layoutId"	}
var categoryCol		= {textHEB	: "קטגוריה", 		textENG	: "Category", 			xmlTag	: "category"	}

/* ---------------------------------------------------------------- */
/* commonAddSeoFrame												*/
/* ---------------------------------------------------------------- */
function commonAddSeoFrame (theFormId, fieldWidth, byLang)
{
	if (fieldWidth == undefined) fieldWidth = 475;
	if (byLang == undefined) byLang = true;

	var fieldSpace = fieldWidth + 10;

	var fieldsWidths = {HEB : new Array(150,fieldSpace),
						ENG : new Array(150,fieldSpace)}

	var frameId = pageObj.addSpecialFrame (theFormId, "קידום האתר", "SEO");

	if (byLang)
		var theLangs = langArray;
	else
		var theLangs = undefined;

	field1 = {type			: "text",					textHEB		 : winTitleCol.textHEB,
			  spanData		: 1,						textENG		 : winTitleCol.textENG,
			  dataFld		: winTitleCol.xmlTag,		width	 	 : fieldWidth,
			  minLength		: "1",						mandatory	 : false,
			  maxLength		: "100",					lang		 : theLangs,
			  action		: "commonSetUpdated(" + theFormId + ")",
			  helpTextHEB	: help_winTitleText,		counter		 : true}

	field2 = {type      	: "text",					textHEB 	 : keywordsMetaCol.textHEB,
			   spanData  	: 1,						textENG      : keywordsMetaCol.textENG,
			   dataFld   	: keywordsMetaCol.xmlTag,	width     	 : fieldWidth,
			   minLength 	: "1",					 	defaultValue : "",
			   maxLength 	: "600",					mandatory 	 : false,
	   		   helpTextHEB	: help_keywordsText,		lang		 : theLangs,
			   counter		: true}

	field3 = {type      	: "textEng",				textHEB 	 : robotsCol.textHEB,
			   spanData  	: 1,						textENG      : robotsCol.textENG,
			   dataFld   	: robotsCol.xmlTag,			width     	 : fieldWidth,
			   maxLength 	: "100",					mandatory 	 : false,
	   		   lang		    : theLangs}

	field4 = {type      	: "textarea",				textHEB 	 : descMetaCol.textHEB,
			   spanData  	: 1,						textENG      : descMetaCol.textENG,
			   dataFld   	: descMetaCol.xmlTag,		width     	 : fieldWidth,
			   rows 		: "3",					 	lang		 : theLangs,
			   helpTextHEB	: help_descriptionText,		counter		 : true}

	field5 = {type			: "text",					textHEB		 : rewriteNameCol.textHEB,
			  spanData		: 1,						textENG		 : rewriteNameCol.textENG,
			  dataFld		: rewriteNameCol.xmlTag,	width	 	 : fieldWidth,
			  minLength		: "1",						mandatory	 : false,
			  maxLength		: "200",					lang		 : theLangs,
			  helpTextHEB	: help_rewriteNameText}

	hidden = {type			: "hidden",					dataFld		: "updated",
			  lang		 	: theLangs}

  	fields = new Array(field1, field2, field3, field4, field5, hidden);

	pageObj.addFormFields (theFormId, frameId, fieldsWidths, fields);
}

/* ---------------------------------------------------------------------------------------- */
/* commonSetUpdated																			*/
/* ---------------------------------------------------------------------------------------- */
function commonSetUpdated (theFormId)
{
	var	fieldLang	= $(".lang_selected").attr("id");

	if (fieldLang == undefined)
	{
		if (langArray != undefined)
			fieldLang = langArray[0];
	}
	pageObj.setFieldValue (theFormId, "updated" + fieldLang, "");
}

/* ---------------------------------------------------------------- */
/* commonCanStart													*/
/* ---------------------------------------------------------------- */
function commonCanStart ()
{

	if (top == undefined)
	{
		commonMsgBox ("info", "בעיה במערכת. נא לפנות לתמיכה");
		return false;
	}

	if (commonGetGlobalData ("langArray") == undefined)
	{
		commonMsgBox ("info", "נא להמתין לסיום טעינת המערכת");
		top.location.reload ();
		return false;
	}
	return true;
}

/* ---------------------------------------------------------------- */
/* commonChangeFormLang												*/
/* ---------------------------------------------------------------- */
function commonChangeFormLang (e, formId)
{
	var target = e.target || e.srcElement;

	pageObj.changeFormLanguage 	(formId, target.id);
	pageObj.chooseLang			(target.id);
}

/* ---------------------------------------------------------------- */
/* commonGoToMain													*/
/* ---------------------------------------------------------------- */
function commonGoToMain ()
{
	return "../";

	var href 	 = window.location.href;
	var startPos = href.indexOf ("html");

	href 	 = href.substring (startPos, href.length-1)

	var goBack     = href.split ("/").length-1;
	var goBackDirs = "";

	for (i=0; i < goBack; i++)
	{
		goBackDirs += "../";
	}

	return goBackDirs;
}

/* ---------------------------------------------------------------- */
/* commonGetInnerData                                               */
/* ---------------------------------------------------------------- */
function commonGetInnerData (dataXml, tagName, index)
{
    try
    {
		if (index == undefined)
			index = 0;

        var elements = dataXml.getElementsByTagName(tagName);

		if (window.DOMParser)
        	return elements.item(0).textContent;
		else
	        return elements.item(0).text;
    }
    catch (e)
    {
        return "";
    }
}

/* ---------------------------------------------------------------- */
/* commonGetDummyData                                               */
/* ---------------------------------------------------------------- */
function commonGetDummyData (dataXml, tagName)
{
    try
    {
		var dummyName = tagName.toUpperCase();

        var elements = dataXml.getElementsByTagName(dummyName);

		if (window.DOMParser)
        	var tagData = elements.item(0).textContent;
		else
	        var tagData = elements.item(0).text;

		if (tagData == "true")
			tagData = true;

		if (tagData == "false")
			tagData = false;

		return tagData;
    }
    catch (e)
    {
        return "";
    }
}

/* ---------------------------------------------------------------- */
/* openMsgBox														*/
/* ---------------------------------------------------------------- */
function openMsgBox (msgKind, msg, okAction)
{
	language = commonGetGlobalData("guiLang");

	if (msgKind == "confirm")
	{
		if (language == "HEB" || language == "")
			alertify.set({
				labels: {
				    ok     : "אישור",
				    cancel : "ביטול"
				},
				buttonFocus: "none"
			 });
		else
			alertify.set({
				labels: {
				    ok     : "Accept",
			    	cancel : "Deny"
				},
				buttonFocus: "none"
		   	});

		alertify.confirm (msg, function (e) 
						   {
						   	if (e) { window[okAction](); }
						   });
	}
	else
	{

		var html = "<div class='infoMsg'>" +
						"<div class='infoMsg_text'>" + msg + "</div>" +
				   "</div>";

//						"<div class='infoMsg_confirm rowOfButtons'>" +
//							"<div class='btn' onclick='$.fancybox.close()' style='width:75px'><div class='btnText'>אישור</div></div>" +
//						"</div>" +
		$.fancybox(html, {padding: 0, hideOnContentClick: true, overlayColor: "#E6E6E6"});
		return;
/*		if (language == "HEB" || language == "")
			alertify.set({
				labels: { ok     : "אישור" },
				buttonFocus: "none"
		   	});
		else
			alertify.set({
				labels: { ok     : "OK" },
				buttonFocus: "none"
		   	});

		alertify.alert (msg);
*/	}
}

/* ---------------------------------------------------------------- */
/* commonMsgBox														*/
/* ---------------------------------------------------------------- */
function commonMsgBox (msgKind, hebMsg, engMsg, okAction)
{
	if (commonGetGlobalData("guiLang") == "ENG")
		openMsgBox (msgKind, engMsg, okAction);
	else
		openMsgBox (msgKind, hebMsg, okAction);
}

/* ---------------------------------------------------------------- */
/* commonWaitingBox													*/
/* ---------------------------------------------------------------- */
function commonWaitingBox (theMsgKind, hebMsg, engMsg, httpObj, currRetry)
{
	if (commonGetGlobalData("guiLang") == "ENG")
		openMsgBox (theMsgKind, engMsg);
	else
		openMsgBox (theMsgKind, hebMsg);
		if (msgConfirmValue==undefined || msgConfirmValue==0)
			return 0;
		else 
			return msgConfirmValue;
}

/* ---------------------------------------------------------------- */
/* confirm															*/
/* ---------------------------------------------------------------- */
function confirm (msgHEB, msgENG, okAction)
{
	if (msgHEB.indexOf("יש ללחוץ") == -1)
		msgHEB = "יש ללחוץ 'אישור' ל" + msgHEB;

	if (msgENG.indexOf("Press ") == -1)
		msgENG = "Press 'OK' to " + msgENG;

	return commonMsgBox ("confirm", msgHEB, msgENG, okAction);
}

/* ---------------------------------------------------------------- */
/* commonEncode														*/
/* ---------------------------------------------------------------- */
function commonEncode (text)
{
	return text;
}

/* ---------------------------------------------------------------- */
/* commonDecode														*/
/* ---------------------------------------------------------------- */
function commonDecode (text)
{
	return text;
}

/* ---------------------------------------------------------------- */
/* commonShowUrl													*/
/* ---------------------------------------------------------------- */
function commonShowUrl (tableId, col)
{
    var rowOfCol    = col.parentNode;
    var rowIndex    = rowOfCol.rowIndex;

    var type        = pageObj.getRowValueOf (tableId, rowIndex, "type");

	var url 		= col.childNodes[1].innerText;
	var siteUrl		= commonGetGlobalData("siteUrl");

	switch (type)
	{
		case "page"		:	
		case "forum"	:
		case "blog"		:
		case "essay"	:
		case "gallery"	:	url = siteUrl + "/index2.php?id=" + url.substring(0,url.indexOf(" "));
							break;

		case "file"		:	url = siteUrl + "/loadedFiles/" + url;
							break;

		case "url"		: 	if (url.indexOf("http:") == -1)
							{
								url = siteUrl + "/" + url;
							}
							break;
	}

	window.open (url, "_blank","","");
}

/* ---------------------------------------------------------------- */
/* commonGetPosOptions												*/
/* ---------------------------------------------------------------- */
function commonGetPosOptions (xml, removePos, textTag, itemsTag)
{
	if (itemsTag == undefined)
		itemsTag = "items";

	var posOptions	 = new selectOptionsObj();

	if (xml != null) 
	{
		itemsNode = xml.getElementsByTagName(itemsTag).item(0);

		if (itemsNode != null)
		{
			for (i=0; i<itemsNode.childNodes.length; i++)
			{
				var currNode = itemsNode.childNodes[i];

				var pos = commonGetInnerData (currNode, "pos");

				if (pos == 1)
					posOptions.addOption ("1", "ראשון", "First");
				
//				if (pos*1+1 == removePos) continue;

				// on update item position
//				if (removePos != 0 && i == itemsNode.childNodes.length-1)
//					pos--;

//				if (removePos == 0) pos++;
				pos++;

				if (removePos == pos) continue;

//				if (pos != 1)
//				{
					var text  = commonGetInnerData (currNode, textTag);

					posOptions.addOption (pos, "אחרי " + text, "After " + text);
//				}
			}

			if (itemsNode.childNodes.length == 0)
				posOptions.addOption ("1", "ראשון", "First");
		}
	}

	return posOptions;
}

/* ---------------------------------------------------------------- */
/* commonGetLinkTypes												*/
/* ---------------------------------------------------------------- */
function commonGetLinkTypes (forObject)
{
	var typeOptions  = new selectOptionsObj();

	typeOptions.addOption  ("" , 	 		 "", 		     	 		"");
	typeOptions.addOption  ("page",  		 "קישור לדף",  		 		"Link to Page");
	typeOptions.addOption  ("url",   		 "קישור URL",		 		"Link to URL");

	if (forObject == "menu")
		typeOptions.addOption  ("urlNoFollow", 	 "קישור URL-No-Follow",		"Link to URL no-follow");

	typeOptions.addOption  ("forum", 		 "קישור לפורום",	 		"Link to Forum");
	typeOptions.addOption  ("blog", 		 "קישור לבלוג",	 			"Link to Blog");
	typeOptions.addOption  ("gallery", 		 "קישור לגלריה",	 		"Link to Gallery");
	typeOptions.addOption  ("album", 		 "קישור לאלבום",	 		"Link to Album");
	typeOptions.addOption  ("essay", 		 "קישור לכתבה",		 		"Link to Essay");
	typeOptions.addOption  ("questionnaire", "קישור לשאלון",	 		"Link to Questionnaire");
	typeOptions.addOption  ("nadlan", 		 "קישור ללוח נדל\"ן", 		"Link to Nadlan tablet");

	if (forObject == "menu")
		typeOptions.addOption  ("staticName",	 "קישור לפי כתובת סטטית",	"Link to Literal URL");

	typeOptions.addOption  ("file", 		 "קישור לקובץ",		 		"Link to File");

	if (forObject == "menu")
	{
		typeOptions.addOption  ("addToFavorite", "הוסף למועדפים",	 		"Add to favorites");
		typeOptions.addOption  ("makeHomePage",  "הפוך לדף הבית",	 		"Make your homepage");
		typeOptions.addOption  ("onclick",   	 "קישור onClick",	 		"Link to URL onClick");
	}

	return typeOptions.getOptions();
}

var global_formId;
var global_value;
var global_pageFieldName;
var global_urlFieldName;
var global_afterFunction;

/* ---------------------------------------------------------------- */
/* commonOnChangeLinkType											*/
/* ---------------------------------------------------------------- */
function commonOnChangeLinkType (type, value, typeFieldName, pageFieldName, urlFieldName, theFormId, afterFunction)
{
	global_afterFunction = afterFunction;

	if (typeFieldName == undefined || typeFieldName == "") typeFieldName 	= "type";
	if (pageFieldName == undefined || pageFieldName == "") pageFieldName 	= "page";
	if (urlFieldName  == undefined || urlFieldName  == "") urlFieldName 	= "url";
	if (theFormId     == undefined || theFormId     == "") theFormId 		= formId;

	global_formId		 = theFormId;
	global_value		 = value;
	global_pageFieldName = pageFieldName;
	global_urlFieldName  = urlFieldName;

	if (type == undefined || type == "")
		type = pageObj.getFieldValue (theFormId, typeFieldName);

	if (type == "url" || type == "urlNoFollow" || type == "onclick")
	{
			pageObj.setFieldDisplay (theFormId, urlFieldName, 	"");
			pageObj.setFieldDisplay (theFormId, pageFieldName, 	"none");
			pageObj.changeMandatory (theFormId, urlFieldName,  	true);
			pageObj.setFieldState   (theFormId, urlFieldName,	"unlock");

			if (global_afterFunction != undefined)
				window[afterFunction]();
	}
	else if (type == "addToFavorite" || type == "makeHomePage" || type == "sitemap")
	{
			pageObj.setFieldDisplay (theFormId, urlFieldName, 	"");
			pageObj.setFieldDisplay (theFormId, pageFieldName, 	"none");
			pageObj.setFieldValue	(theFormId, urlFieldName,  	"");
			pageObj.changeMandatory (theFormId, urlFieldName,  	false);
			pageObj.setFieldState   (theFormId, urlFieldName,	"lock");

			if (global_afterFunction != undefined)
				window[afterFunction]();
	}
	else if (type == "")
	{
			pageObj.setFieldDisplay (theFormId, urlFieldName, 	"");
			pageObj.setFieldDisplay (theFormId, pageFieldName, 	"none");
			pageObj.changeMandatory (theFormId, urlFieldName,  	false);
			pageObj.setFieldState   (theFormId, urlFieldName, 	"lock");
			pageObj.setFieldValue	(theFormId, urlFieldName,	"");
			pageObj.setFieldValue	(theFormId, pageFieldName, 	"");

			if (global_afterFunction != undefined)
				window[afterFunction]();
	}
	else
	{
			if (type == "page")
				serverOptions_getPages 			("choose", "commonSetLinkOptions");

			if (type == "blog")
				serverOptions_getBlogs 			(true, "commonSetLinkOptions");

			if (type == "forum")
				serverOptions_getForums			("choose", undefined, "commonSetLinkOptions");

			if (type == "gallery")
				serverOptions_getGalleries		("choose", "commonSetLinkOptions");

			if (type == "album")
				serverOptions_getAlbums			("choose", "commonSetLinkOptions");

			if (type == "essay")
				serverOptions_getEssays			("choose", undefined, "commonSetLinkOptions");

			if (type == "nadlan")
				serverOptions_getNadlans		(true, "commonSetLinkOptions");

			if (type == "questionnaire")
				serverOptions_getQuestionnaires	(true, "commonSetLinkOptions");

			if (type == "file")
				serverOptions_getFiles			("commonSetLinkOptions");

			if (type == "staticName")
				serverOptions_getRewriteNames	("choose", "commonSetLinkOptions");
	}
}

function commonSetLinkOptions (options)
{
	pageObj.setFieldOptions (global_formId, global_pageFieldName, options);

	pageObj.setFieldDisplay (global_formId, global_urlFieldName,  "none");
	pageObj.setFieldDisplay (global_formId, global_pageFieldName, "");
	pageObj.changeMandatory (global_formId, global_urlFieldName,  true);
	pageObj.setFieldState   (global_formId, global_urlFieldName,  "unlock");
	
	if (global_value != undefined)
	{
		pageObj.setFieldValue (global_formId, global_pageFieldName, global_value);
	}

	if (global_afterFunction != undefined)
		window[global_afterFunction]();

	global_afterFunction = undefined;
}


/* ---------------------------------------------------------------------------------------- */
/* commonGetURLParam																		*/
/* ---------------------------------------------------------------------------------------- */
function commonGetURLParam (strParamName)
{
  var strReturn = "";
  var strHref = window.location.href;

  if ( strHref.indexOf("?") > -1 )
  {
    var strQueryString = strHref.substr(strHref.indexOf("?")).toLowerCase();
    var aQueryString = strQueryString.split("&");
	for (var iParam = 0; iParam < aQueryString.length; iParam++ )
	{
		if (aQueryString[iParam].indexOf(strParamName.toLowerCase() + "=") > -1 )
		{
    	    var aParam = aQueryString[iParam].split("=");
        	strReturn = aParam[1];
	        break;
    	}
    }
  }
  return unescape(strReturn);
} 

/*---------------------------------------------------------------------------*/
/* commonLoadSiteNames														 */
/*---------------------------------------------------------------------------*/
function commonLoadSiteNames (afterFunction)
{
	var serverObj 	= new serverInterfaceObj();

	serverObj.addDummyTag ("afterFunction", afterFunction);

   	serverObj.sendRequest("globalParms.getSiteNames", undefined, "common_after_loadSiteNames");
}

function common_after_loadSiteNames (i)
{
	commonLoadSiteNames_continue (asyncResponseXml.getResponseXml (i));
}

function commonLoadSiteNames_continue (responseXml)
{
	var siteNames = new Array();
	var theLangs  = commonGetGlobalData("langArray");

	try
	{
		for (var i=0; i<theLangs.length; i++)
		{
			lang = theLangs[i];

			siteName = commonGetInnerData (responseXml, "siteName" + lang);

			siteNames[lang] = siteName;

			if (i == 0)
				siteNames["noLang"] = siteName;
		}
	}
	catch (e)
	{
	}

	commonSetGlobalData ("siteNames", siteNames);

	var afterFunction = commonGetDummyData (responseXml, "afterFunction");

	if (afterFunction != "")
		window[afterFunction]();

}

/*---------------------------------------------------------------------------*/
/* commonGetFlagsByType														 */
/*---------------------------------------------------------------------------*/
function commonGetFlagsByType (type, afterFunction)
{
	var serverObj 	 = new serverInterfaceObj();

	serverObj.addTag	  ("type", type);
	serverObj.addDummyTag ("afterFunction", afterFunction);

    serverObj.sendRequest ("flags.getFlags", undefined, "loadFlags");
}

function loadFlags (i)
{
	var responseXml = asyncResponseXml.getResponseXml (i);

	var flags = new Array();

	if (responseXml != null)
	{
		itemsNode = responseXml.getElementsByTagName("items").item(0);

		if (itemsNode != null)
		{
			for (i=0; i < itemsNode.childNodes.length; i++)
			{
				currNode    = itemsNode.childNodes[i];

				id  	= commonGetInnerData (currNode, "id");
				name 	= commonGetInnerData (currNode, "name");

				flags.push ({id : id, name : name})
			}
		}
	}

	var afterFunction 	= commonGetDummyData (responseXml, "afterFunction");

	window[afterFunction](flags);
}

/*---------------------------------------------------------------------------*/
/* commonCreateFlagsFrame													 */
/*---------------------------------------------------------------------------*/
function commonCreateFlagsFrame (theFormId, frameFieldsWidths, fieldWidth, flags)
{
	if (flags.length != 0)
	{
		var frameId = pageObj.addFormFrame (theFormId, "דגלונים", "Flags");

		var fields = new Array();

	  	for (var i=0; i < flags.length; i++)
		{
			flag = flags[i];

		    field = {type		: "yesNoSelect",		textHEB		 : flag.name,
			 		 spanData	: 1,					textENG		 : flag.name,
					 dataFld	: "flag"+flag.id,		width		 : fieldWidth,
			  		 mandatory	: false,				defaultValue : "0"}

			fields.push (field);
		}

		pageObj.addFormFields (theFormId, frameId, frameFieldsWidths, fields);
	}
}

function commonGetPageActions (previewLink, showLink)
{
	if (commonGetGlobalData("guiLang") == "ENG")
	{
			previewText = "Preview";
			showPage = "Show page";
	} 
	else 
	{
			previewText = "תצוגה מקדימה";
			showPage = "הצגת הדף";
	}

	previewHtml = "";
	if (previewLink != "")
		previewHtml = "<span class='link' onclick='" + previewLink + "'>" + previewText + "</span> | ";

	html = "<div class='inFormLinks'>" +
				previewHtml + 
				"<a href='" + showLink + "' target='_blank' >" + showPage + "</a>" + 
			"</div>";

	return html;
}

/*---------------------------------------------------------------------------*/
/* commonRemoveFromArray													 */
/*---------------------------------------------------------------------------*/
function commonRemoveFromArray (theArray, theItem)
{
	var i = 0;
	while (i < theArray.length) 
	{
		if (theArray[i] == theItem) 
		{
			theArray.splice(i, 1);
		} 
		else 
		{
			i++;
		}
	}
	return theArray;
}
		
/*---------------------------------------------------------------------------*/
/* commonInArray															 */
/*---------------------------------------------------------------------------*/
function commonInArray (theArray, theItem)
{
	var i = 0;
	while (i < theArray.length) 
	{
		if (theArray[i] == theItem) 
		{
			return i;
		} 
			
		i++;
	}

	return -1;
}
		
/*---------------------------------------------------------------------------*/
/* commonHasMobileVersion													 */
/*---------------------------------------------------------------------------*/
function commonHasMobileVersion ()
{
    if (commonGetGlobalData ("hasMobileVersion") == undefined)
	{
		serverObj.sendRequest("globalParms.getGlobalParms", undefined, "commonHasMobileVersion_continue");
	}
}

function commonHasMobileVersion_continue (i)
{
	var responseXml = asyncResponseXml.getResponseXml(i);

	commonSetGlobalData("hasMobileVersion", (commonGetInnerData (responseXml, "hasMobileVersion") == "1"));
}

/*---------------------------------------------------------------------------*/
/* commonGetUserInfo														 */
/*---------------------------------------------------------------------------*/
function commonGetUserInfo (afterFunction)
{
	var xml = commonGetGlobalData ("userInfoXml")
    if (xml == undefined || commonGetDummyData(xml, "afterFunction") != afterFunction)
	{
		serverObj.addDummyTag ("afterFunction", afterFunction);

		serverObj.sendRequest("user.getUserInfo", undefined, "commonSaveUserInfo");
	}
	else
	{
		commonGetUserInfo_continue ();
	}
}

function commonSaveUserInfo (i)
{
	var responseXml = asyncResponseXml.getResponseXml(i);

	commonSetGlobalData("userInfoXml", 	responseXml);

	commonGetUserInfo_continue ();
}

function commonGetUserInfo_continue ()
{
	var responseXml = commonGetGlobalData("userInfoXml");

	commonSetGlobalData ("isSuperUser", (commonGetInnerData(responseXml, "isSuperUser") == "1"));
	commonSetGlobalData ("ibosUserId",	 commonGetInnerData(responseXml, "userId"));

	var afterFunction 	= commonGetDummyData (responseXml, "afterFunction");

	window[afterFunction]();
}


/*---------------------------------------------------------------------------*/
/* commonSetGlobalData														 */
/*---------------------------------------------------------------------------*/
function commonSetGlobalData (dataName, dataValue)
{
	if (jQuery.isXMLDoc(dataValue))	// this is xml dom - convert to string
	{
		dataValue = dataValue.xml ? dataValue.xml : (new XMLSerializer()).serializeToString(dataValue);
	}
	else if ((typeof(dataValue) == 'string' && dataValue == "") || dataValue == undefined)	// empty string
	{
		dataValue = "###empty###";
	}

	window.top.jQuery('#globalData').data(dataName, dataValue);
}

/*---------------------------------------------------------------------------*/
/* commonGetGlobalData														 */
/*---------------------------------------------------------------------------*/
function commonGetGlobalData (dataName)
{
	try
	{
		var returnValue = window.top.jQuery('#globalData').data(dataName);

		if (returnValue == "###empty###") returnValue = undefined;

		if (returnValue != undefined && typeof(returnValue) == 'string' && returnValue.indexOf("<") === 0)
		{
			// this is xml string - create a xmldom
			if (window.DOMParser)
			{ 
			    var parser	= new DOMParser();
				returnValue 	= parser.parseFromString(returnValue,"text/xml");
			}
			else
			{
				var xmlDom = new ActiveXObject("Microsoft.XMLDOM");
				xmlDom.loadXML (returnValue);

				returnValue = xmlDom;
			}
		}

		return returnValue;
	}
	catch (e)
	{
		return undefined;
	}
}

/*---------------------------------------------------------------------------*/
/* commonFormSetQueryXml													 */
/*---------------------------------------------------------------------------*/
function commonFormSetQueryXml (queryName, theFormId)
{
	if (theFormId == undefined)
		theFormId = searchFormId;

	var queryXml = commonGetGlobalData (queryName);

	if (queryXml != undefined)
	{
		pageObj.setFormXml (theFormId, queryXml);
	}
}	

/*---------------------------------------------------------------------------*/
/* commonSaveQueryXml														 */
/*---------------------------------------------------------------------------*/
function commonSaveQueryXml (queryName, theFormId)
{
	if (theFormId == undefined)
		theFormId = searchFormId;

	var queryXml = pageObj.getFormXml(theFormId);

	commonSetGlobalData (queryName, queryXml);

	serverObj.cleanRequest ();
	serverObj.setXml (queryXml);
}

