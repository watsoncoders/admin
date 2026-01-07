/* ---------------------------------------------------------------- */
/*																	*/
/*							upload.js								*/
/*																	*/
/* ---------------------------------------------------------------- */

var uploadPageTitleId 	= -1;
var uploadPageButtonsId	= -1;
var uploadFormId		= -1;
var uploadCheckExists	= false;
var uploadDirName		= "loadedFiles";

/* -------------------------------------------------------------*/
/* uploadCreatePage												*/
/* -------------------------------------------------------------*/
function uploadCreatePage (pageTitleHEB, pageTitleENG, formId)
{
	uploadPageTitleId = pageObj.addPageSubTitle (pageTitleHEB, pageTitleENG);

	uploadCreateForm	(formId);

	multiUploadStart	(undefined, uploadFormId);

	uploadCreateButtons ();
}

/* -------------------------------------------------------------*/
/* uploadCreateForm												*/
/* -------------------------------------------------------------*/
function uploadCreateForm (formId)
{
	if (formId != undefined)
	{
		uploadFormId = formId;
	}
	else
	{
		uploadFormId = pageObj.addForm ();

		var frameId = pageObj.addFormFrame (uploadFormId, "טעינת קובץ", "File Loading");

		fields = new Array();

		field1 = {type		: "onlineUpload",			textHEB		 : "קובץ", 
				  spanData	: 1,						textENG		 : "File",
				  dataFld	: "uploadFile",				width		 : "300",
				  mandatory : true,						defaultValue : ""}

		fields.push (field1);

		widths = {HEB : new Array (100,320),
				  ENG : new Array (100,320)};

		pageObj.addFormFields (uploadFormId, frameId, widths, fields);
	}
	
	pageObj.generateForm  (uploadFormId, false);
}

/* -------------------------------------------------------------*/
/* uploadCreateButtons											*/
/* -------------------------------------------------------------*/
function uploadCreateButtons ()
{
	uploadPageButtonsId = pageObj.addRowOfButtons ();

	btn1	= {type		: "back",
			   action	: "uploadGoBack()"}

	btn2	= {type		: "loading",
			   action	: "uploadSubmitForm()"}

	btn3	= {type		: "reset",
			   action	: "pageObj.emptyFormFields(" + uploadFormId + ")"}

	btnsGroups = new Array ();

	btnsGroups.push (new Array(btn1), new Array(btn2, btn3));

	pageObj.generateRowOfButtons (uploadPageButtonsId, btnsGroups, pageObj.getFormWidth(uploadFormId));
}

/* -------------------------------------------------------------*/
/* uploadShowPage												*/
/* -------------------------------------------------------------*/
function uploadShowPage ()
{
	pageObj.setFieldValue (uploadFormId, "uploadFile", "");

	handleDisplay ("upload");

	pageObj.showComponent (uploadPageTitleId);
	pageObj.showComponent (uploadPageButtonsId);
	pageObj.showComponent (uploadFormId);
}
	
/* ---------------------------------------------------------------------------------------- */
/* uploadGoBack																				*/
/* ---------------------------------------------------------------------------------------- */
function uploadGoBack ()
{
	pageObj.hideComponent (uploadPageTitleId);
	pageObj.hideComponent (uploadPageButtonsId);
	pageObj.hideComponent (uploadFormId);

	handleDisplay ("report");
}

/* -------------------------------------------------------------*/
/* uploadSubmitForm												*/
/* -------------------------------------------------------------*/
function uploadSubmitForm ()
{
	if (!pageObj.validateForm (uploadFormId)) return false;

	var file = uploadGetFileName(0);
	if (file == ERROR_WAIT_LOADING)
	{
		return false;
	}

	if (uploadCheckExists)
	{
		serverObj.addTag	("dirName", uploadDirName);
		serverObj.addTag	("fn", 		file);
		
		serverObj.sendRequest("files.isFileExists", undefined, "isFileExists_response"); 
	}
	else
	{
		doUploadFile ();
	}
}

function isFileExists_response (i)
{
	var responseXml = asyncResponseXml.getResponseXml (i);

	if (responseXml != null)
	{
		var isExists = commonGetInnerData (responseXml, "isExists");

		if (isExists == "1")
		{
			confirm ("קובץ בשם זה כבר קיים באתר<br/>יש ללחוץ 'אישור' על מנת להחליף את הקובץ הקיים", "", "doUploadFile");
			return false;
		}

		doUploadFile ();
	}
}

function doUploadFile ()
{
	var file = uploadGetFileName(0);

	serverObj.cleanRequest ();
	serverObj.addTag ("dirName", uploadDirName);
	serverObj.addTag ("fn",  	 file);
	serverObj.sendRequest("files.uploadFile", undefined, "uploadFile_continue"); 
}

function uploadFile_continue (i)
{
	var responseXml = asyncResponseXml.getResponseXml (i);

	if (responseXml != null)
	{
		doRefresh 		();
		uploadGoBack 	();
	}

	return true;
}

/* -------------------------------------------------------------*/
/* uploadAfter													*/
/* -------------------------------------------------------------*/
function uploadAfter ()
{
	return (window.location.search != "");
}

/* -------------------------------------------------------------*/
/* uploadGetFiles												*/
/* -------------------------------------------------------------*/
function uploadGetFiles ()
{
	var tmpFileName = ""
	var originalFileName = "";

	if (window.location.search != "")
	{
		// extract tmpFileName and originaFileName
		tmpFileName      = window.location.search.substring(window.location.search.indexOf("tmpFileName")+12,
															window.location.search.indexOf("&"));
		
		originalFileName = window.location.search.substring(window.location.search.indexOf("originalFileName")+17,
															window.location.search.indexOf("originalFileName")+200);

		theSession       = top.back.theSession;
		theLanguage      = top.back.theLanguage;
	}

	return {tmpFileName: tmpFileName, originalFileName : originalFileName};
}



