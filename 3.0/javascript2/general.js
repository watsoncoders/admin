var withDebug = setTheDEBUG();

/* ---------------------------------------------------------------- */
/* setTheDEBUG														*/
/* ---------------------------------------------------------------- */
function setTheDEBUG()
{
	if (getCookie_removeIt ("debug") == "1")
		return true;
	else
		return false;
}

/* ---------------------------------------------------------------- */
/* setCookie														*/
/* ---------------------------------------------------------------- */
function setCookie_removeIt (cookieName, cookieValue, exp)
{
//	sessionStorage.setItem (cookieName, cookieValue);
//	return;

		if (exp != 0)
		{
			//expiration
			var expiration = new Date();
			expiration.setTime(expiration.getTime() + exp);
			expiration = expiration.toUTCString();
		}
		else
			expiration = "";

		// check if do replace or add
		oldCookie = getCookie_removeIt (cookieName);

		if (oldCookie == "")
			document.cookie = cookieName+"="+ cookieValue +"; expires=" + expiration;
		else
		{
			//newStr = document.cookie.replace (cookieName+"="+oldCookie+";","XXXXXXXXXXXXXX");
			//newStr = document.cookie.replace (cookieName+"="+oldCookie+";", cookieName+"="+cookieValue+";");
			//document.cookie = newStr;
			document.cookie = cookieName+"="+ cookieValue +"; expires=" + expiration;
		}
}

/* ---------------------------------------------------------------- */
/* getCookie														*/
/* ---------------------------------------------------------------- */
function getCookie_removeIt (cookieName, window)
{
//	return sessionStorage.getItem (cookieName);

	var allCookie;

	if (window == undefined)
        allCookie = document.cookie;
	else
		allCookie = dialogArguments.document.cookie;

        var theValue;
        var start = allCookie.indexOf(cookieName+"=");
        if (start == -1)return "";
        var end = allCookie.indexOf(";", start);
        if (end == -1) end = allCookie.length;
        var name_value = allCookie.substring(start, end);
        theValue = name_value.substring(cookieName.length+1, name_value.length);
		return theValue;
}

/*
	SIZE ADJUSTMENT
	================
*/
/* function: 	sizeAdjustment(height)
 * description: setting the page according to the screen height
				(it changes the "gap" between the top and the contents of the page.)
				***requires an element where id= heightSpacer for applying the size adjustment*******
 * params: 		height - the height in pixels from the top
 * author: 		sharon shaar, 18.12.01
 * history: 	--
 */
function sizeAdjustment(height) {
	var theHeight;
	if (height != null) theHeight = height;
	else theHeight = 0;

	var size = screen.height;
	if (size > 600) window.heigthSpacer.style.height = theHeight;
	else window.heigthSpacer.style.height = "0";
	//else window.heigthSpacer.style.display = "none";
}


/*
 *Function: 	divSize(small,big)
 *Description: 	this function determines the 'dataDiv' height according to screen size
				the 'dataDiv' is the area for the scroll-down data table
 *Params: 		small, big - two ints for size calculation. 
				the small is for screens smaller than 600, 
				the big - for over 600
 *return:		none.
 *author: 		Sharon shaar.
 */
function divSize(small, big) {
   try {
		var size = screen.height;
   
    	if (size <= 600) {
        	window.dataDiv.style.height = size - small ;
	    }
    	else {
        	window.dataDiv.style.height = size - big ;
	    }
	}
	catch (error) {}
}


/*
 *Function: 	sendEmail.
 *Description: 	this function receives the span with the email info and opens a mail browser.
 *params: 		spanObj - the span object with the email inside.
 *returns: 		none.
 *author: 		Amit Naveh, 11/10/01.
 */
function sendEmail(spanObj) {
	//Canceling the event bubbling:
	event.cancelBubble = true;

	mailAddress = spanObj.innerText;
	location.href = "mailto:"+mailAddress;
}


/*
 * Function: getGMTDateString()
 * Description: returns a string of the date in the format yyyy-mm-dd hh:mm:ss+zzzz 
				zzzz stands for time zone.
 * Params: --
 * Returns: string - date
 * Author: sharon shaar , 06/2002
 * History: --
 */ 
function getGMTDateString(){
	var theDate = new Date();
	var year = theDate.getFullYear();
	var month = theDate.getMonth() +1;
	var day = theDate.getDate();
	var hours = theDate.getHours();
	var minutes = theDate.getMinutes();
	var seconds = theDate.getSeconds();
	var zone = theDate.getTimezoneOffset();
	//processing the date string:
	month = (""+month).length == 1 ? "0"+month : month;
	day = (""+day).length == 1 ? "0"+day: day;
	hours = (""+hours).length == 1 ? "0"+hours: hours;
	minutes = (""+minutes).length == 1 ? "0"+minutes: minutes;
	seconds = (""+seconds).length == 1 ? "0"+seconds: seconds;
	var zonePositive = "+";
	if (zone<0) zonePositive = "-";
	zone = zone*-1;
	zone = zone/60*10;
	if (zone == "0") zone = zone+"0";
	if (zone+"".charAt(zone.length-1) == "5"){
		zone = (zone+"").substring(0,(zone+"").length-1) + "3";
	} 
	if ((""+zone).length == 1) zone = "0"+zone;
	if ((""+zone).length == 2) zone = "0"+zone+"0";
	else zone = zone+"0";

	var fullDateString = year+"-"+month+"-"+day+ " "+hours+":"+minutes+":"+seconds+zonePositive+zone;
	return fullDateString;	
}

function applicationDateTime (dateTime)
{
	if (dateTime != "")
	{
		// date time format : yyyy-mm-dd hh:mm:ss-zone
		// application date time : dd-mm-yyy hh:mm:ss
		var retDateTime = dateTime.substring(8,10) + "-" + dateTime.substring(5,7) + "-" +
						  dateTime.substring(0,4) + dateTime.substring(10,19);
	}
	else
		retDateTime = "";

	return retDateTime;
}

function today (separator)
{
	if (separator == undefined)
		separator = "-";

	var theDate = new Date();
	var year 	= theDate.getFullYear();
	var month 	= theDate.getMonth() +1;
	var day 	= theDate.getDate();

	month = ("" + month).length == 1 ? "0" + month : month;
	day   = ("" + day  ).length == 1 ? "0" + day   : day;

	return day + separator + month + separator + year;
}
function now (onlyTime)
{
	if (onlyTime == undefined) onlyTime = false;

	var theDate = new Date();
	
	var year 	= theDate.getFullYear();
	var month 	= theDate.getMonth() +1;
	var day 	= theDate.getDate();
	var hours 	= theDate.getHours();
	var minutes = theDate.getMinutes();
	
	month 		= ("" + month).length 	== 1 ? "0" + month   : month;
	day   		= ("" + day  ).length 	== 1 ? "0" + day     : day;
	hours 		= ("" + hours).length   == 1 ? "0" + hours	 : hours;
	minutes 	= ("" + minutes).length == 1 ? "0" + minutes : minutes;

	if (onlyTime)
		return hours + ":" + minutes;
	else
		return day + "-" + month + "-" + year + " " + hours + ":" + minutes;
}


function applicationAmount (amount)
{
	if (amount != "")
	{
		if (amount.indexOf(".") == -1)
		{
			amount = "" + amount + ".00";
		}
		
		var retAmount = "";

		var amountLen = amount.length;

		var index = amountLen - 4;

		while (index >= 3)
		{
			retAmount = "," + amount.substr(index-2,3) + retAmount;			
			index = index - 3;
		}

		if (index != -1)
			retAmount = amount.substr(0,index+1) + retAmount;

		retAmount = retAmount + amount.substr(amountLen-3,3);

		return retAmount;
	}
}

/*---------------------------------------------------------------------------*/
/* getTitle																	 */
/*---------------------------------------------------------------------------*/
function getTitle (hebTitle, engTitle)
{
	if (theLanguage == "ENG")
		return engTitle;
	else
		return hebTitle;
}

/*---------------------------------------------------------------------------*/
/* generatePass																 */
/*---------------------------------------------------------------------------*/
function generatePass (plength)
{
	var keylist="ABCDEFGHIJKLMNOPQRSTUVWXYZ123456789"
	var pass=''
	for (i=0;i<plength;i++)
		pass+=keylist.charAt(Math.floor(Math.random()*keylist.length))
	return pass
}

function nextYear (separator)
{
	if (separator == undefined)
		separator = "-";

	var theDate = new Date();
	var year 	= theDate.getFullYear()+1;
	var month 	= theDate.getMonth() +1;
	var day 	= theDate.getDate();

	month = ("" + month).length == 1 ? "0" + month : month;
	day   = ("" + day  ).length == 1 ? "0" + day   : day;

	return day + separator + month + separator + year;

}

var general_global_theFormId;
var general_global_fieldName;
var general_global_fieldLang;
var general_global_editType;

/*---------------------------------------------------------------------------*/
/* generalSetWinTitle														 */
/*---------------------------------------------------------------------------*/
function generalSetWinTitle (fieldName, editType, theFormId, byLang)
{
	if (theFormId == undefined) theFormId = formId;
	if (byLang == undefined) byLang = true;

	var fieldLang = "";
	
	if (byLang)
	{
		fieldLang	= $(".lang_selected").attr("id");

		if (fieldLang == undefined)
		{
			fieldLang = langArray[0];
		}
	}
	
	general_global_theFormId = theFormId;
	general_global_fieldLang = fieldLang;
	general_global_editType	 = editType;
	general_global_fieldName = fieldName;

	generalGetSiteName(fieldLang);
}


/*---------------------------------------------------------------------------*/
/* generalGetSiteName														 */
/*---------------------------------------------------------------------------*/
function generalGetSiteName (lang)
{
	langInd = lang;
	if (lang == "")
		langInd = "noLang";

	var siteNames = commonGetGlobalData ("siteNames");

	if (siteNames == undefined || siteNames[langInd] == undefined)
		commonLoadSiteNames ("generalGetSiteName_continue");
	else
		generalGetSiteName_continue ();
}

function generalGetSiteName_continue ()
{
	var siteNames = commonGetGlobalData ("siteNames");

	var siteName = "";

	if (siteNames != undefined && siteNames[langInd] != undefined)
		siteName = siteNames[langInd];

	var theFormId = general_global_theFormId;
	var fieldLang = general_global_fieldLang;
	var editType  = general_global_editType;
	var fieldName = general_global_fieldName;

	var currWinTitle = pageObj.getFieldValue (theFormId, "winTitle" + fieldLang);

	if (currWinTitle == "" || editType == "add")
	{
		var theWinTitle = pageObj.getFieldValue (theFormId, fieldName + fieldLang) + " - " + siteName;
		pageObj.setFieldValue (theFormId, "winTitle" + fieldLang, theWinTitle);
	}
}


function generalShowPage (featureId, url)
{
	// save choice for last-used
	var serverObj 	 = new serverInterfaceObj();
	serverObj.addTag	    ("featureId", 	featureId);
	serverObj.addDummyTag	("url", 		url);
	serverObj.setServer		("server.php");
	serverObj.sendRequest	("general.saveChoice", undefined, "generalShowPage_continue");
}

function generalShowPage_continue (i)
{
	var responseXml = asyncResponseXml.getResponseXml (i);
	
	if (responseXml != null)
	{
		var url 	= commonGetDummyData (responseXml, "url");

		top.mainFrame.location.replace(url);
	}
	else
	{
		window.location.reload ();
	}
}
