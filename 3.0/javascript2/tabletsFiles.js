var fileIdCol			= {textHEB  : "מזהה",			textENG : "",	xmlTag  : "id"			}
var fileNameCol			= {textHEB  : "שם קובץ",		textENG : "",	xmlTag  : "sourceFile"	}
var fileTypeCol			= {textHEB  : "סוג קובץ",		textENG : "",	xmlTag  : "fileType"	}
var fileSizeCol			= {textHEB  : "גודל קובץ",		textENG : "",	xmlTag  : "fileSize"	}
var filePosCol			= {textHEB  : "מיקום",			textENG : "",	xmlTag  : "pos"			}

var filesTitleId		= -1;
var addFilesTitleId		= -1;
var updateFileTitleId	= -1;
var filesTableId		= -1;
var filesRowButtonsId	= -1;
var filesFormId			= -1;
var filesFormButtonsId	= -1;

var showTitleId			= -1;
var showButtonsId		= -1;
var spanFileId			= -1;

var filesXml			= null;

/* ---------------------------------------------------------------------------------------- */
/* tabletsFiles_createTitles																*/
/* ---------------------------------------------------------------------------------------- */
function tabletsFiles_createTitles ()
{
	filesTitleId		= pageObj.addPageSubTitle ("קבצי מודעה", "");
	addFilesTitleId 	= pageObj.addPageSubTitle ("הוספת קובץ מודעה", "");
	updateFileTitleId	= pageObj.addPageSubTitle ("עדכון פרטי קובץ", "");

	showTitleId			= pageObj.addPageSubTitle ("הצגת קובץ מודעה", "");
}
 
/* ---------------------------------------------------------------------------------------- */
/* tabletsFiles_createSpanFile																*/
/* ---------------------------------------------------------------------------------------- */
function tabletsFiles_createSpanFile ()
{
	spanFileId = pageObj.addSpan	();
}

/* ---------------------------------------------------------------------------------------- */
/* tabletsFiles_createTable																	*/
/* ---------------------------------------------------------------------------------------- */
function tabletsFiles_createTable ()
{
	filesTableId  	= pageObj.addTable ();
	
	pageObj.setLoadFunction  (filesTableId, "tabletsFiles_loadData");

	// handle the table
	// ------------------------------------------------------------------------------------
	column1		= {textHEB	: fileIdCol.textHEB,		widthHEB : 70,
				   textENG	: fileIdCol.textENG,		widthENG : 70,
				   sortType : "number",					sortDir  : "ascending",
				   xmlTag   : fileIdCol.xmlTag}
		   
	column2		= {textHEB	: fileNameCol.textHEB,		widthHEB : 250,
				   textENG	: fileNameCol.textENG,		widthENG : 250,
				   sortType : "text",					sortDir  : "ascending",
				   xmlTag   : fileNameCol.xmlTag,		dir		 : "ltr"}

	column3		= {textHEB	: fileTypeCol.textHEB,		widthHEB : 120,
				   textENG	: fileTypeCol.textENG,		widthENG : 120,
				   sortType : "text",					sortDir  : "ascending",
				   xmlTag   : fileTypeCol.xmlTag}

	column4		= {textHEB	: fileSizeCol.textHEB,		widthHEB : 120,
				   textENG	: fileSizeCol.textENG,		widthENG : 120,
				   sortType : "text",					sortDir  : "ascending",
				   xmlTag   : fileSizeCol.xmlTag}

	column5		= {textHEB	: filePosCol.textHEB,		widthHEB : 70,
				   textENG	: filePosCol.textENG,		widthENG : 70,
				   sortType : "text",					sortDir  : "ascending",
				   xmlTag   : filePosCol.xmlTag}


	columns = new Array ();
	columns.push (column1, column2, column3, column4, column5);

	pageObj.setTableColumns (filesTableId, columns);
	pageObj.setTableHeight	(filesTableId, 300, 250);
}

/* ---------------------------------------------------------------------------------------- */
/* tabletsFiles_createTableButtons															*/
/* ---------------------------------------------------------------------------------------- */
function tabletsFiles_createTableButtons ()
{
	btn1	= {type			: "",
			   textHEB		: "הוספת קבצים",
			   action		: "tabletsFiles_openForm('addFiles')",
			   inPopup	    : false}

	btn2	= {type			: "update",
			   action		: "tabletsFiles_openForm('updateFile')"}

	btn3	= {type			: "delete",
			   action		: "tabletsFiles_deleteFile()"}

   	btn4	= {type			: "show",
			   action		: "tabletsFiles_showFile()"}

	btn5	= {type			: "back",
			   action		: "handleDisplay('report')"}

	btnsGroups = new Array ();

	btnsGroups.push (new Array(btn1, btn2, btn3, btn4), new Array(btn5));

	filesRowButtonsId = pageObj.addRowOfButtons ();
	pageObj.generateRowOfButtons (filesRowButtonsId, btnsGroups, pageObj.getTableWidth(filesTableId));
}

/* ---------------------------------------------------------------------------------------- */
/* tabletsFiles_loadData																	*/ 
/* ---------------------------------------------------------------------------------------- */
function tabletsFiles_loadData () 
{
	serverObj.cleanRequest ();
	serverObj.addTag (idCol.xmlTag, pageObj.getSelectedValueOf(tableId,idCol.xmlTag));

	serverObj.sendRequest("tablets.getTabletFiles", pageObj.getTablePaging(filesTableId), "tabletsFiles_loadData_continue"); 
}

function tabletsFiles_loadData_continue (i)
{
	var responseXml = asyncResponseXml.getResponseXml (i);

	if (responseXml != null)
	{
		pageObj.setTableXmls   (filesTableId, responseXml);
		pageObj.setTablePaging (filesTableId, responseXml);

		pageObj.generateTable  (filesTableId);

		filesXml = responseXml;

		handleDisplay ("files");
	}

	return true;
}

var globalDetailsXml;

var globalType;

/* ---------------------------------------------------------------------------------------- */
/* tabletsFiles_openForm																	*/
/* ---------------------------------------------------------------------------------------- */
function tabletsFiles_openForm (type)
{
	globalType = type;

	if (type == "updateFile" && !pageObj.isRowSelected(filesTableId)) 
	{
		commonMsgBox ("info", "יש לבחור קובץ");
		return false;
	}

	if (type == "addFiles")
	{
		tabletsFiles_openForm_continue ();
	}
	else	// update file
	{
		globalDetailsXml = undefined;

		serverObj.cleanRequest ();
		serverObj.addTag	   (fileIdCol.xmlTag, pageObj.getSelectedValueOf (filesTableId, fileIdCol.xmlTag));
		serverObj.sendRequest("tablets.getFileDetails", undefined, "after_loadFileDetails");
	}
}

// ---------------------------------------------------------------------------------------- 

function after_loadFileDetails (i)
{
	globalDetailsXml = asyncResponseXml.getResponseXml (i);

	tabletsFiles_openForm_continue ();
}

// ---------------------------------------------------------------------------------------- 

function tabletsFiles_openForm_continue ()
{
	type = globalType;

	if (type == "updateFile" && globalDetailsXml == undefined) return;

	if (filesFormId == -1)
		filesFormId   = pageObj.addForm ();
	
	pageObj.resetForm (filesFormId);
	
	if (type == "addFiles")
	{
		fieldsWidths = {HEB : new Array(10,500),
						ENG : new Array(10,500)}

		fieldWidth  = 490;

		var frameId = pageObj.addFormFrame (filesFormId, "בחירת קבצים", ""); 

		field1 = {type			: "multiUpload",			textHEB		 	: "",
				  spanData		: 1,						textENG			: "",
				  dataFld		: "files",					width			: fieldWidth}

	  	fields = new Array(field1);
	
		pageObj.addFormFields (filesFormId, frameId, fieldsWidths, fields);
	}
	else // updateFile
	{
		pageObj.setFormXml (filesFormId, globalDetailsXml);

		fieldsWidths = {HEB : new Array(110,160,110,160),
						ENG : new Array(110,160,110,160)}

		var fieldWidth  = 150;
		var fieldWidth2 = 420;

		var frameId = pageObj.addFormFrame (filesFormId, "פרטים", ""); 

		hidden = {type			: "hidden",
				  dataFld		: fileIdCol.xmlTag}

		field1 = {type			: "span",					textHEB		 : fileNameCol.textHEB,
				  spanData		: 1,						textENG		 : fileNameCol.textENG,
				  dataFld		: fileNameCol.xmlTag,		width	 	 : fieldWidth}

		field2 = {type			: "span",					textHEB		 : fileTypeCol.textHEB,
				  spanData		: 1,						textENG		 : fileTypeCol.textENG,
				  dataFld		: fileTypeCol.xmlTag,		width	 	 : fieldWidth}

		// build pos options
		var removePos  = commonGetInnerData (globalDetailsXml, filePosCol.xmlTag)*1 + 1;

		var posOptions = commonGetPosOptions (filesXml, removePos, fileNameCol.xmlTag);

		field3 = {type			: "select",					textHEB		 : filePosCol.textHEB,
				  spanData		: 1,						textENG		 : filePosCol.textENG,
				  dataFld		: filePosCol.xmlTag,		width	 	 : fieldWidth,
				  options		: posOptions.getOptions(),	mandatory	 : true,
		  		  defaultValue	: "1"}

	  	fields = new Array(hidden, field1, field2, field3);
	
		pageObj.addFormFields (filesFormId, frameId, fieldsWidths, fields);

	}
	
	// ------------------------------------------------------------------------------------ 

	pageObj.generateForm  (filesFormId,false);

	if (type == "addFiles")
	{
		var id = pageObj.getSelectedValueOf(tableId,idCol.xmlTag);

		multiUploadStart("http://www.i-bos.co.il/3.0/php/uploadTabletsFiles.php?itemId=" + id, filesFormId);
	}

	tabletsFiles_createFormButtons 	(type);
	handleDisplay 		   			(type);
}

/* ---------------------------------------------------------------------------------------- */
/* tabletsFiles_createFormButtons															*/
/* ---------------------------------------------------------------------------------------- */
function tabletsFiles_createFormButtons (type)
{
	btnsGroups = new Array ();

	btn1	= {type			: "back",
			   action		: "handleDisplay('files')"}

	if (type == "addFiles")
	{
		btn1.action = "tabletsFiles_loadData();" + btn1.action;

		btnsGroups.push (new Array(btn1));
	}
	else
	{
		btn2 = {type		: "update",
			    action		: "tabletsFiles_submitForm()"}

		btnsGroups.push (new Array(btn1), new Array(btn2));
	}

	if (filesFormButtonsId == -1)
	{
		filesFormButtonsId = pageObj.addRowOfButtons 	 ();
	}
	pageObj.generateRowOfButtons (filesFormButtonsId, btnsGroups, pageObj.getFormWidth(filesFormId));

}

/* ---------------------------------------------------------------------------------------- */
/* tabletsFiles_submitForm																	*/
/* ---------------------------------------------------------------------------------------- */
function tabletsFiles_submitForm ()
{
	if (!pageObj.validateForm(filesFormId)) return false;
	
	serverObj.cleanRequest ();
	serverObj.setXml (pageObj.getFormXml(filesFormId));
	serverObj.addTag (idCol.xmlTag, pageObj.getSelectedValueOf(tableId,idCol.xmlTag));

	serverObj.sendRequest("tablets.updateFile", undefined, "tabletsFiles_submitForm_continue");
}

function tabletsFiles_submitForm_continue (i)
{
	var responseXml = asyncResponseXml.getResponseXml (i);

	if (responseXml != null)
	{
		handleDisplay			("files");
		tabletsFiles_doRefresh	();
	}
	return true;
}

/* ---------------------------------------------------------------------------------------- */
/* tabletsFiles_doRefresh																	*/ 
/* ---------------------------------------------------------------------------------------- */
function tabletsFiles_doRefresh() 
{
	pageObj.resetTable 		(filesTableId);
	tabletsFiles_loadData   ();	
}

/* ---------------------------------------------------------------------------------------- */
/* tabletsFiles_deleteFile																	*/ 
/* ---------------------------------------------------------------------------------------- */
function tabletsFiles_deleteFile () 
{
    if (!pageObj.isRowSelected(filesTableId)) 
	{
		commonMsgBox ("info", "יש לבחור קובץ");
		return false;
	}

	confirm("מחיקת הקובץ", "delete file", "tabletsFiles_deleteFile_confirm");
}

function tabletsFiles_deleteFile_confirm ()
{
	id 	= pageObj.getSelectedValueOf(filesTableId,fileIdCol.xmlTag);
	
	serverObj.cleanTags ();
	serverObj.addTag	(fileIdCol.xmlTag, id);
	
	serverObj.sendRequest("tablets.deleteFile", undefined, "tabletsFiles_deleteFile_continue");
}

function tabletsFiles_deleteFile_continue (i)
{
	var responseXml = asyncResponseXml.getResponseXml (i);

	if (responseXml != null)
	{
		tabletsFiles_doRefresh ();
	}
	return true;
}

/* ---------------------------------------------------------------------------------------- */
/* tabletsFiles_showPage																	*/
/* ---------------------------------------------------------------------------------------- */
function tabletsFiles_showPage ()
{
	if (pageObj.areRowsSelected(tableId))
	{
		commonMsgBox ("info", "יש לבחור שורה אחת בלבד");
		return false;
	}

    if (!pageObj.isRowSelected(tableId)) 
	{
		commonMsgBox ("info", "יש לבחור שורה");
		return false;
	}

	tabletsFiles_loadData 	  ();
}

/* ---------------------------------------------------------------------------------------- */
/* tabletsFiles_showFile																	*/
/* ---------------------------------------------------------------------------------------- */
function tabletsFiles_showFile ()
{
	if (!pageObj.isRowSelected(filesTableId)) 
	{
		commonMsgBox ("info", "יש לבחור קובץ");
		return false;
	}

	tabletsFiles_createShowPage    	();

	tabletsFiles_createShowButtons 	();

	handleDisplay 						("show");
}

/* ---------------------------------------------------------------------------------------- */
/* tabletsFiles_createShowPage																*/
/* ---------------------------------------------------------------------------------------- */
function tabletsFiles_createShowPage ()
{
	fileHtml = "<iframe frameborder='0' width='100%' height='420' style='background-color:#f8f8f8' src='" + 
						pageObj.getSelectedValueOf(filesTableId, "fullFileName") + "'" +
			   "</iframe>";

	pageObj.initSpan      (spanFileId, fileHtml);

	pageObj.showComponent (spanFileId);   
}

/* ---------------------------------------------------------------------------------------- */
/* tabletsFiles_createShowButtons															*/
/* ---------------------------------------------------------------------------------------- */
function tabletsFiles_createShowButtons ()
{
	btn1	= {type			: "back",
			   action		: "handleDisplay('files')"}

	btnsGroups = new Array ();

	btnsGroups.push (new Array(btn1));

	if (showButtonsId == -1)
		showButtonsId = pageObj.addRowOfButtons 	 ();
	pageObj.generateRowOfButtons (showButtonsId, btnsGroups, 10);

}


