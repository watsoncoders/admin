/*--------------------------------------------------------------------------*/
/* 																			*/
/*							dynamicFormObj.js								*/
/*							------------------								*/
/*																			*/
/* Form is array of frames.													*/
/* Frame is array of fields.												*/
/* Field attributes :														*/
/*	- type				: see field types ...								*/
/*	- textHEB			: field hebrew text									*/
/*  - textENG			: field english text								*/
/*	- dataFld			: xml tag name										*/
/*	- spanData			: data col span										*/
/*	- width				: input field width (overwrite default css)			*/
/*	- minLength 		: minimum value length (for validation)				*/
/*	- maxLength			: maximum value length (for maxlength attribute)	*/
/*	- defaultValue  	: default value when no xml supplied				*/
/* 	- mandatory			: is mandatory field ? (for validation)				*/
/*	- options			: selection field options							*/
/*  - state				: set field state (lock...)							*/
/*  - action			: add onclick event for field						*/
/* 	- tabIndex			: index that defines the tab order on the form		*/
/*  - helpTextHEB		: hebrew text below field							*/
/*  - helpTextENG		: english text below field							*/
/*																			*/
/* Field types :															*/
/*	- text				: text input   (checkText validation)				*/
/* 	- textarea			: text area 										*/
/*	- number			: number input (checkNumber validation)				*/
/*  - phone				: phone number (checkPhone validation)				*/
/* 	- password			: password input 									*/
/* 	- passwordNoConfirm	: password input 									*/
/* 	- passwordConfirm	: confirm password input							*/ 
/*  - time				: time field (checkTime validation (hh:mm))			*/
/*  - date				: date field (with calendar)						*/
/*	- mmyy				: month/year field (checkMMYY)						*/
/* 	- amount			: amount field (checkAmount)						*/
/*  - email				: emails field (checkEmail validation)				*/
/*  - ip				: ip field (checkIp validation)						*/
/*	- hidden			: hidden input (for pass xml data)					*/
/*  - span				: span field   (for show read only data)			*/
/*	- checkbox			: checkbox field									*/
/*  - select			: single selection field							*/
/* 	- yesNoSelect		: yes/no selection field							*/
/* 	- multiSelect		: multi selection field								*/							
/* 	- multiSelectPlus	: multi selection field + select/unselect all		*/							
/* 	- tablePaging		: static table filled from paging table				*/
/*	- fieldPaging		: static text area filled from paging table			*/
/*  - tableForm			: table with form fields (no table paging)			*/
/* 	- color				: text field with color chooser button				*/
/*	- uploadFile		: text field + browse button						*/
/*	- multiUpload		: flash component for multi loading					*/
/*  - onlineUpload		: flash component for online loading files			*/
/*  - xstandard			: WYSIWYG editor for Strict XHTML 1.1				*/
/*																			*/
/*  - empty				: no field (space)									*/
/*																			*/
/*  !!! radio is not supported !!!											*/
/*																			*/
/* ------------------------------------------------------------------------ */

/* ------------------------------------------------------------------------ */
/* dynamicFormObj constructor												*/
/* ------------------------------------------------------------------------ */
function dynamicFormObj (theLanguage, formName) 
{
	// data memebers
	// ----------------------------------------------------------------

	this.objectName				= null;						// the object name
	this.destinationSpanName	= null;						// the destination span name of the form
	this.theLanguage			= theLanguage;				// the language
	this.direction				= "rtl";					// the direction (for styling)

	this.addLang				= "";

	this.formLanguage;

	if (commonGetGlobalData("langArray") == undefined)
		this.formLanguage = "HEB";
	else
		this.formLanguage = commonGetGlobalData("langArray")[0];

	this.fieldsByLang			= new Array();				// save all fields with more than one language

	this.multipleFields			= new Array();			

	this.xstandardField			= "";						// for setXml & getXml
	this.numTinymce				= 0;
	this.tinymceFields			= new Array();
	this.multiUploadFrame		= -1;
	this.onlineUploadFrames		= new Array();
	this.onlineUploadFields		= new Array();
	
	if (this.theLanguage== "ENG") 
	{
		this.direction  = "ltr"; 		
		this.addLang	= "_ENG";
	}

	this.xmlNode				= ""						// the xml node to be binded in the table

	this.tableFieldId			= formName + "_tableFieldId";
	this.tableCountSpan			= formName + "_tableCountSpan";
	this.tableFieldSrc			= formName + "_tableData"	// the name of the xml tag on table field

	this.tableFrameIndex		= -1;
	this.tableFieldIndex		= -1;
	this.tableFormId			= -1;
	this.tables					= new Array();
	this.pageObj				= -1;
	
	this.formWidth				= "";
	
	this.openTblId				= formName + "_openTbl";
	this.closeTblId				= formName + "_closeTbl";

	this.currTab				= -1;

	this.frames					= new Array();

	// form validation vars
	this.prevTextTdError		= "";

	// special classes
	this.regularClassName		= "styleTdText";
	this.lockClassName			= "styleTdTextDisabled";

	// save form action and onSubmit event (for post support like upload file)
	this.formAction				= "";
	this.formSubmit				= "";

	// methods
	// ----------------------------------------------------------------

	this.setFormLanguage		= dynamicFormObj_setFormLanguage;
	this.changeFormLanguage		= dynamicFormObj_changeFormLanguage;

	this.getTextByLang			= dynamicFormObj_getTextByLang;

	this.showInnerFormObjects	= dynamicFormObj_showInnerFormObjects;
	this.hideInnerFormObjects	= dynamicFormObj_hideInnerFormObjects;
	this.resetForm				= dynamicFormObj_resetForm;
	this.addFrame				= dynamicFormObj_addFrame;
	this.addFields				= dynamicFormObj_addFields;

	this.setFormAction			= dynamicFormObj_setFormAction;

	// setting the object xml
	this.setXml					= dynamicFormObj_setXml;
	this.getXml					= dynamicFormObj_getXml;

	this.loadXstandardField		= dynamicFormObj_loadXstandardField;
	this.loadTinymceField		= dynamicFormObj_loadTinymceField;

	// handle form fields
	this.getFieldValue			= dynamicFormObj_getFieldValue;
	this.setFieldValue			= dynamicFormObj_setFieldValue;

    this.getOptionText          = dynamicFormObj_getOptionText;
	this.getOptionValueText     = dynamicFormObj_getOptionValueText;

	this.setFieldOptions		= dynamicFormObj_setFieldOptions;

	this.setFieldState			= dynamicFormObj_setFieldState;

	this.setFieldFocus			= dynamicFormObj_setFieldFocus;

	this.changeFieldTitle		= dynamicFormObj_changeFieldTitle;

	this.changeMandatory		= dynamicFormObj_changeMandatory;
	
	this.setFieldDisplay		= dynamicFormObj_setFieldDisplay;

	// setting the div height 
	this.setObjectName			= dynamicFormObj_setObjectName;
	this.createDestinationSpan	= dynamicFormObj_createDestinationSpan;

	this.getFormWidth			= dynamicFormObj_getFormWidth;

	this.selectTab				= dynamicFormObj_selectTab;

	// generating the from
	this.generateTabs			= dynamicFormObj_generateTabs;
	this.generateData			= dynamicFormObj_generateData;
	this.generateFrame			= dynamicFormObj_generateFrame;
	this.getMaxFrameWidth		= dynamicFormObj_getMaxFrameWidth;

	// table paging
	this.createTablePaging		= dynamicFormObj_createTablePaging;
	this.loadTablePaging		= dynamicFormObj_loadTablePaging;
	this.addToTableField		= dynamicFormObj_addToTableField;
	this.deleteFromTableField	= dynamicFormObj_deleteFromTableField;
	this.deleteOnClick			= dynamicFormObj_deleteOnClick;
	this.openPagingSelect		= dynamicFormObj_openPagingSelect;

	// table form
	this.createTableForm		= dynamicFormObj_createTableForm;
	this.loadTableForm			= dynamicFormObj_loadTableForm;
	this.tableFormChanged		= dynamicFormObj_tableFormChanged;

	// form validation
	this.validate 				= dynamicFormObj_validate;
	this.displayAndSetError		= dynamicFormObj_displayAndSetError;
	this.checkMandatory			= dynamicFormObj_checkMandatory;
	this.checkNumber			= dynamicFormObj_checkNumber;
	this.checkAmount			= dynamicFormObj_checkAmount;
	this.checkPhone				= dynamicFormObj_checkPhone;
	this.checkEmail				= dynamicFormObj_checkEmail;
	this.checkPassword			= dynamicFormObj_checkPassword;
	this.checkText				= dynamicFormObj_checkText;
	this.checkTime				= dynamicFormObj_checkTime;
	this.checkMMYY				= dynamicFormObj_checkMMYY;

	// generating the table html
	this.generateFormHtmlStr	= dynamicFormObj_generateFormHtmlStr;

	// generating the table (including the binding)
	this.generateForm			= dynamicFormObj_generateForm;

	// support tables on form
	// ----------------------------------------------------------------
	this.getFormTableId			= dynamicFormObj_getFormTableId;
	this.loadFormTable			= dynamicFormObj_loadFormTable;

	// support collapse
	// ----------------------------------------------------------------
	this.addCollapse			= dynamicFormObj_addCollapse;
	this.collapseSection		= dynamicFormObj_collapseSection;
	this.isCollapse				= dynamicFormObj_isCollapse;

	// handle selection
	// ----------------------------------------------------------------
	this.selectUnSelect			= dynamicFormObj_selectUnSelect;

	this.emptyFormFields		= dynamicFormObj_emptyFormFields;			// form fields reset

	// for debug
	this.checkLanguage			= dynamicFormObj_checkLanguage;
	this.checkField				= dynamicFormObj_checkField;

	// init to the object : 1) set object name  2) create destination span
	this.setObjectName 			(formName);
	this.createDestinationSpan 	();
}

//var pleaseWaitText = "התוכן נטען כעת... יש להמתין עד 5 שניות<br />במקרה שהודעה זו לא נעלמת, יש ללחוץ על כפתור חזרה ולהיכנס שוב";
//var pleaseWait = "<p style=\"text-align: center;\" id='pleaseWaitMsg'><strong>" + pleaseWaitText + "</strong></p>";
var pleaseWaitText = "התוכן נטען כעת... במקרה שהודעה זו לא נעלמת - אין לבצע שמירה!";
var pleaseWait = pleaseWaitText;

/* ------------------------------------------------------------------------ */
/* dynamicFormObj_getTextByLang												*/
/* ------------------------------------------------------------------------ */
function dynamicFormObj_getTextByLang (textHEB, textENG)
{
//	return "\"" + eval("text" + this.theLanguage) + "\"";
	return eval("text" + this.theLanguage);
}

/* ------------------------------------------------------------------------ */
/* dynamicFormObj_checkLanguage												*/
/* ------------------------------------------------------------------------ */
function dynamicFormObj_checkLanguage ()
{
	if (this.theLanguage != "HEB" && this.theLanguage != "ENG")
		throw new Error(1,"no form language - check form constructor");
}

/* ------------------------------------------------------------------------ */
/* dynamicFormObj_checkField												*/
/* ------------------------------------------------------------------------ */
function dynamicFormObj_checkField (field)
{
	if (field.dataFld == "")
		throw  new Error (20, "Active field with no dataFld\n");
}

/* ------------------------------------------------------------------------ */
/* dynamicFormObj_setObjectName												*/
/* ------------------------------------------------------------------------ */
function dynamicFormObj_setObjectName (objectName) 
{
	this.objectName = objectName;
}

/* ------------------------------------------------------------------------ */
/* dynamicFormObj_createDestinationSpan										*/
/* ------------------------------------------------------------------------ */
function dynamicFormObj_createDestinationSpan (spanName) 
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
 	throw new Error(20, error.description + "\n\t(dynamicFormObj.createDestinationSpan)");
  }
}

/* ------------------------------------------------------------------------ */
/* dynamicFormObj_showInnerFormObjects										*/
/* ------------------------------------------------------------------------ */
function dynamicFormObj_showInnerFormObjects ()
{
  try
  {
  	if (this.tableFormId != -1)
	{
		this.pageObj.showComponent (this.tableFormId);	
	}
  }
  catch (error)
  {
 	throw new Error(20, error.description + "\n\t(dynamicFormObj.showInnerFormObjects)");
  }
}

/* ------------------------------------------------------------------------ */
/* dynamicFormObj_hideInnerFormObjects										*/
/* ------------------------------------------------------------------------ */
function dynamicFormObj_hideInnerFormObjects ()
{
  try
  {
  	if (this.tableFormId != -1)
	{
		this.pageObj.hideComponent (this.tableFormId);	
	}
  }
  catch (error)
  {
 	throw new Error(20, error.description + "\n\t(dynamicFormObj.hideInnerFormObjects)");
  }
}

/*--------------------------------------------------------------------------*/
/* dynamicFormObj_changeFormLanguage										*/
/*--------------------------------------------------------------------------*/
function dynamicFormObj_changeFormLanguage (lang) 
{
  try
  {
	if (this.xstandardField != "")
	{
		// save xstandard content
		fieldObj  = document.getElementById(this.xstandardField + this.formLanguage);
	
		if (fieldObj == undefined)
			fieldObj = document.getElementById(this.xstandardField);

		editorValue =  document.getElementById(this.objectName + "_editor0").value;
		
		editorValue = editorValue.replace (/\<\!\[CDATA\[/g,"");
		editorValue = editorValue.replace (/\]\]\>/g,"");

		fieldObj.value = editorValue;
	}

	for (var i = 1; i <= this.numTinymce; i++)
	{
		// save editor in hidden field
		fieldObj  		= document.getElementById(this.objectName + "_" + this.tinymceFields[i] + this.formLanguage);

		if (fieldObj != null)
		{
			editorValue		= tinymce.EditorManager.get("formTinymce" + this.objectName + "_" + this.tinymceFields[i]).getContent();

			editorValue 	= editorValue.replace (/\<\!\[CDATA\[/g,"");
			editorValue 	= editorValue.replace (/\]\]\>/g,"");

			fieldObj.value  = editorValue;
		}
	}

  	for (var i=0; i < this.fieldsByLang.length; i++)
	{
		field = document.getElementById(this.objectName + "_" + this.fieldsByLang[i] + this.formLanguage);
		field.style.display = "none";

		var fieldCounter = document.getElementById("fieldCounter_" + this.objectName + "_" + this.fieldsByLang[i] + this.formLanguage);
		if (fieldCounter != undefined)
			fieldCounter.style.display = "none";

		field = document.getElementById(this.objectName + "_" + this.fieldsByLang[i] + lang);
		if (field.type != "hidden")
			field.style.display = "";

		var fieldCounter = document.getElementById("fieldCounter_" + this.objectName + "_" + this.fieldsByLang[i] + lang);
		if (fieldCounter != undefined)
			fieldCounter.style.display = "";

		if (i == 0)
			try {field.focus ();} catch(e) {}

		langImg = document.getElementById(this.objectName + "_" + this.fieldsByLang[i] + "LangImg"); 

		if (lang.length == 4)	// mobile lang
			langImg.src = "../../designFiles/lang/" + lang + ".png";
		else
			langImg.src = "../../designFiles/lang/" + lang + ".jpg";
	}

	if (this.xstandardField != "")
	{
		// set xstandard by hidden field selected language
		fieldObj  = document.getElementById(this.xstandardField + lang);
		if (fieldObj == undefined)
			fieldObj = document.getElementById(this.xstandardField);

		document.getElementById(this.objectName + "_editor0").Value = fieldObj.value;

		if (lang == 'HEB' || lang == 'ARB' || lang == 'HB2')
			dir = 'rtl';
		else
			dir = 'ltr'

		document.getElementById(this.objectName + "_editor0").Dir = dir;
	}

	if (this.numTinymce != 0)
	{
		for (var i = 1; i <= this.numTinymce; i++)
		{
			fieldObj  = document.getElementById(this.objectName + "_" + this.tinymceFields[i] + lang);
			
			if (fieldObj != null)
			{
				tinymce.EditorManager.get("formTinymce" + this.objectName + "_" + this.tinymceFields[i]).setContent(fieldObj.value);
			}
		}

		if (lang == 'HEB' || lang == 'HB2' || lang == 'ARB')
		{
			tinyMCE.activeEditor.getWin().document.body.style.direction = "rtl";
			//tinyMCE.activeEditor.getWin().document.body.style.textAlign = "right";
		}
		else
		{
			tinyMCE.activeEditor.getWin().document.body.style.direction = "ltr";
			//tinyMCE.activeEditor.getWin().document.body.style.textAlign = "left";
		}

	}

	this.formLanguage = lang;
  }
  catch (error)
  {
 	throw new Error(20, error.description + "\n\t(dynamicFormObj.changeFormLanguage)");
  }
}

/*--------------------------------------------------------------------------*/
/* dynamicFormObj_setFormLanguage											*/
/*--------------------------------------------------------------------------*/
function dynamicFormObj_setFormLanguage (lang) 
{
  try
  {
	this.formLanguage = lang;
  }
  catch (error)
  {
 	throw new Error(20, error.description + "\n\t(dynamicFormObj.setFormLanguage)");
  }
}

/* ------------------------------------------------------------------------ */
/* dynamicFormObj_resetForm													*/
/* ------------------------------------------------------------------------ */
function dynamicFormObj_resetForm ()
{
  try
  {
  	this.currTab			= -1;
	this.frames 		 	= new Array();
	this.multipleFields 	= new Array();
	this.fieldsByLang		= new Array();

//	this.tables				= new Array();

	this.xstandardField		= "";
	this.numTinymce			= 0;
	this.tinymceFields		= new Array();
	globalTinymceField 		= new Array();

	this.multiUploadFrame	= -1;
	this.onlineUploadFrames	= new Array();
	this.onlineUploadFields = new Array();

	this.xmlNode 		 	= "";	

	this.prevTextTdError 	= "";

	this.tableFrameIndex 	= -1;
	this.tableFieldIndex 	= -1;

  	this.formAction = "";
	this.formSubmit = "";

	if (commonGetGlobalData("langArray") == undefined)
		this.formLanguage = "HEB";
	else
		this.formLanguage = commonGetGlobalData("langArray")[0];

  }
  catch (error)
  {
 	throw new Error(20, error.description + "\n\t(dynamicFormObj.resetForm)");
  }
}

/* ------------------------------------------------------------------------ */
/* dynamicFormObj_addFrame													*/ 
/* ------------------------------------------------------------------------ */
function dynamicFormObj_addFrame (textHEB, textENG, isSpecial)
{
  try
  {
	if (isSpecial == undefined) isSpecial = false;

	var isSearch = (textENG == "Report Query");

	frame = {textHEB 		: textHEB, 
			 textENG 		: textENG, 
			 widths  		: null, 
			 fields  		: null,
			 collapse		: false,
			 expandIds		: null,
			 collapseIds	: null,
			 isSpecial 		: isSpecial,
			 isSearch		: isSearch}

	return (this.frames.push (frame)-1);
  }
  catch (error)
  {
 	throw new Error(20, error.description + "\n\t(dynamicFormObj.addFrame)");
  }
}

/* ------------------------------------------------------------------------ */
/* dynamicFormObj_addFields													*/
/* ------------------------------------------------------------------------ */
function dynamicFormObj_addFields (frameIndex, inWidths, fields)
{
  try
  {
	var widths = {HEB : new Array(),
				  ENG : new Array()}

	widths.HEB = inWidths.HEB.slice(0);		// create a new array
	widths.ENG = inWidths.ENG.slice(0);		// in order to avoid overwrite the same array


  	frame = this.frames[frameIndex];
	
	if (frame == undefined)
		throw new Error (20, "frame " + frameIndex + " does not exist");
		
	// calc width if there is * width
	// --------------------------------------------------------------------
	for (var i=0; i<2; i++)
	{
		if (widths == "")
			break;

		if (i == 0)
			widthsArray = widths.HEB;
		else
			widthsArray = widths.ENG;

		lastField = widthsArray.length-1;
		if ((widthsArray[lastField] + "").substr(0,1) == "*")
		{
			// get the max frame width
			lastFieldWidth = widthsArray[lastField].substr(1,widthsArray[lastField].length-1)*1+4;

			// subtract all other field widths and cellpadding
			for (var w=0; w<lastField; w++)
				lastFieldWidth -= widthsArray[w]+2;

			// save the calculated width
			if (i == 0)
				widths.HEB[lastField] = lastFieldWidth;
			else
				widths.ENG[lastField] = lastFieldWidth;
		}
	}
		
	// --------------------------------------------------------------------

	frame.widths = widths;

	// set default values for undefined field attributes of all fields
	for (var f=0; f < fields.length; f++)
	{
		if (fields[f].type != "empty"				&&
			fields[f].type != "jump" 				&& 
			fields[f].type != "text" 				&& 
			fields[f].type != "textarea"			&& 
			fields[f].type != "textEng" 			&& 
			fields[f].type != "time" 				&& 
			fields[f].type != "mmyy" 				&& 
			fields[f].type != "number" 				&&
			fields[f].type != "phone" 				&&
			fields[f].type != "amount" 				&&
			fields[f].type != "password" 			&&
			fields[f].type != "passwordNoConfirm"	&&
			fields[f].type != "passwordConfirm" 	&&
			fields[f].type != "date" 				&&
			fields[f].type != "email" 				&&
			fields[f].type != "hidden" 				&&
			fields[f].type != "span" 				&&
			fields[f].type != "checkbox" 			&&
			fields[f].type != "select" 				&&
			fields[f].type != "yesNoSelect" 		&&
			fields[f].type != "multiSelect" 		&&
			fields[f].type != "multiSelectPlus"		&&
			fields[f].type != "tablePaging"			&&
			fields[f].type != "fieldPaging"			&&
			fields[f].type != "tableForm"			&&
			fields[f].type != "table"				&&
			fields[f].type != "color"				&&
			fields[f].type != "uploadFile"			&&
			fields[f].type != "multiUpload"			&&
			fields[f].type != "onlineUpload"		&&
			fields[f].type != "xstandard")
		{
			throw new Error (20, "Wrong field type (" + fields[f].type + ")");
		}

		fields[f].enterAction	= "";

		if (frame.isSearch)
			fields[f].enterAction	= "doRefresh()";

		if (fields[f].dataFld == undefined)
			fields[f].dataFld = ""; 				// empty dataFld

		if (fields[f].width == undefined)
			fields[f].width = ""; 					// empty field width

		if (fields[f].minLength == undefined)
			fields[f].minLength = "";				// empty min length

		if (fields[f].maxLength == undefined)
			fields[f].maxLength = "";				// empty max length

		if (fields[f].minValue == undefined)
			fields[f].minValue = "";				// empty min value

		if (fields[f].maxValue == undefined)
			fields[f].maxValue = "";				// empty max value

		if (fields[f].mandatory == undefined)
			fields[f].mandatory = false;			// not mandatory 

		if (fields[f].spanData == undefined)
			fields[f].spanData = 1;					// one col span

		if (fields[f].rowSpan == undefined)
			fields[f].rowSpan = 1;					// one row span

		if (fields[f].defaultValue == undefined)
			fields[f].defaultValue = "";			// empty default value
		else if (fields[f].defaultValue == "lastOption")
		{
			fields[f].defaultValue = fields[f].options[fields[f].options.length-1].value;
		}

		if (fields[f].options == undefined)			// no select options
			fields[f].options = "";
			
		if (fields[f].state == undefined)			// no field state
			fields[f].state = "";

		if (fields[f].action == undefined)			// field onclick action
			fields[f].action = "";

		if (fields[f].tabIndex == undefined)		// field tab index
			fields[f].tabIndex = "0";

		// save all selection fields that necessary for manual reset
		if (fields[f].type == "select"				||
			fields[f].type == "multiSelect"			||
			fields[f].type == "multiSelectPlus")
		{
			if (fields[f].dataFld != "")
				this.multipleFields.push (fields[f].dataFld);
		}

		// create yesNo select options
		if (fields[f].type == "yesNoSelect")
		{
			optionsObj  = new selectOptionsObj();
			optionsObj.addOption ("0", "לא", "No");
			optionsObj.addOption ("1", "כן", "Yes");

			fields[f].options = optionsObj.getOptions();
		}

		// set time field max length
		if (fields[f].type == "time" || fields[f].type == "mmyy")
			fields[f].maxLength = "5";

		if (fields[f].type == "amount")
		{
			if (fields[f].beforeDot == undefined)
				fields[f].beforeDot = 6;

			if (fields[f].afterDot  == undefined)
				fields[f].afterDot = 2;

			fields[f].maxLength = fields[f].beforeDot + fields[f].afterDot + 1;
		}
	}
	
	// save frame fields
	frame.fields = new Object(fields);
  }
  catch (error)
  {
 	throw new Error(20, error.description + "\n\t(dynamicFormObj.addFields)");
  }
}
/* ------------------------------------------------------------------------ */
/* dynamicFormObj_setFormAction												*/
/* ------------------------------------------------------------------------ */
function dynamicFormObj_setFormAction (formAction, formSubmit)
{
  try
  {
  	this.formAction = formAction;
	this.formSubmit = formSubmit;
  }
  catch (error)
  {
 	throw new Error(20, error.description + "\n\t(dynamicFormObj.setFormAction)");
  }
}

/* ------------------------------------------------------------------------ */
/* dynamicFormObj_getFormTableId											*/
/* ------------------------------------------------------------------------ */
function dynamicFormObj_getFormTableId (frameIndex)
{
	for (i=0; i < this.tables.length; i++)
	{
		if (this.tables[i].tableName == "tableInForm" + frameIndex)
		{
			tableObj = this.tables[i].tableObj;
			break;
		}
	}
	return (tableObj);
}

/*--------------------------------------------------------------------------*/
/* dynamicFormObj_loadFormTable												*/
/*--------------------------------------------------------------------------*/
function dynamicFormObj_loadFormTable (tableObj, xml)
{
  try
  {
  	if (xml != undefined)
	{
		tableObj.setXmls (xml);
	}

	document.getElementById(this.tables[0].tableName + "_dataDiv_container").innerHTML = tableObj.generateDataHtmlStr();

	tableObj.reset ();

  }
  catch (error)
  {
 	throw new Error(20, error.description + "\n\t(dynamicFormObj.loadFormTable)");
  }
}

/* ------------------------------------------------------------------------ */
/* dynamicFormObj_generateData												*/
/* ------------------------------------------------------------------------ */
function dynamicFormObj_generateData(frameIndex) 
{
  try
  {
	var htmlStr = "";
	var type;

	var frame  = this.frames[frameIndex];
	
	if (frame == undefined)
		throw new Error (20, "frame " + frameIndex + " does not exist");

	var widths = eval("frame.widths." + this.theLanguage);

	var newLine     = true;
	var fieldIndex  = 0;
	var currWidth	= 0;
	var dir;
	var width;
	var maxLength;

	if (this.theLanguage == "HEB")
		flagDir = "left";
	else
		flagDir = "right";

	hadRowSpan  = false;

	while (fieldIndex < frame.fields.length)
	{
		field = frame.fields[fieldIndex];

		currFieldIndex = fieldIndex;
		fieldIndex++;

		if (field.type == "table")
		{
			var tableObj = null;
			
			for (i=0; i < this.tables.length; i++)
			{
				if (this.tables[i].tableName == "tableInForm" + frameIndex)
				{
					tableObj = this.tables[i].tableObj;
					break;
				}
		  	}
			
			if (tableObj == null)
			{
				tableObj = new dynamicTableObj(this.theLanguage, "tableInForm" + frameIndex);

				// support tables on tab 0 to tab 4 
				
				if (frameIndex == 0) tableInForm0 = tableObj;
				if (frameIndex == 1) tableInForm1 = tableObj;
				if (frameIndex == 2) tableInForm2 = tableObj;
				if (frameIndex == 3) tableInForm3 = tableObj;
				if (frameIndex == 4) tableInForm4 = tableObj;
				if (frameIndex == 5) tableInForm5 = tableObj;
				if (frameIndex == 6) tableInForm6 = tableObj;
				if (frameIndex == 7) tableInForm7 = tableObj;
				if (frameIndex == 8) tableInForm8 = tableObj;

				newTable = {tableName : "tableInForm" + frameIndex,
							tableObj  : tableObj}
													
				this.tables.push (newTable);
			}

			// check table cols width
			var colsWidth = 0;
			for (var c = 0; c < field.tableCols.length; c++)
			{
				colsWidth += eval("field.tableCols[c].width" + this.theLanguage) - 1;
			}

			if (colsWidth  < this.formWidth)
			{
				field.tableCols[field.tableCols.length-1].widthHEB += this.formWidth - colsWidth - tableObj.scrollWidth;
				field.tableCols[field.tableCols.length-1].widthENG += this.formWidth - colsWidth - tableObj.scrollWidth;
			}

			tableObj.setColumns		(field.tableCols);
			tableObj.setTableHeight	(field.height);

			if (field.xml != null && field.xml != "")
				  tableObj.setXmls	(field.xml);

			if (field.btns != undefined && field.btns.length != 0)
			{
				htmlStr += "<div class='tableInForm_btns' dir='" + this.direction + "'"				+
						   "	 style='width:" + this.formWidth + "px'>"							+
						   	"<table	dir='" + this.direction + "'>"									+
							"<tr>";

				for (b=0; b<field.btns.length; b++)
				{
					var btnText = eval("field.btns[b].text" + this.theLanguage);

					switch (field.btns[b].type)
					{
						case "add"	: 
							var btn = "<img src='../../designFiles/iconAddRow.png' title='" + btnText + "' />";
							break;

						case "update" :
							var btn = "<img src='../../designFiles/iconUpdate.png' title='" + btnText + "' />";
							break;

						case "delete" :
							var btn = "<img src='../../designFiles/iconDeleteRow.png' title='" + btnText + "' />";
							break;

						default	:
							var btn = "<u>" + btnText + "</u>";
					}

					htmlStr += "<td width='8'></td>"												+
							   "<td style='cursor:pointer' onclick='" + field.btns[b].action + "'>"	+
							   		btn																+
							   "</td>";
				}

				htmlStr +=  "</tr>"																	+
						   	"</table>"																+
						   "</div>";
			}
			
			htmlStr += tableObj.generateTableHtmlStr ();

			htmlStr = "<tr><td>" + htmlStr + "</td></tr>";

			continue;
		}
									  
		var dataFld 	= field.dataFld;
		var objectId 	= "";
		var objectName 	= "";

		objectId   = field.dataFld;
		objectName = field.dataFld;

		if (field.lang != undefined && field.lang[0] != "")
		{
			objectId 	+= field.lang[0];
			dataFld  	+= field.lang[0];
			objectName  += field.lang[0];
		}

		var addToAll  = " id='"       + this.objectName + "_" + objectId + "' name='" + objectName + "' ";

		var fieldValue		= new Array();
		var fieldValue_m	= new Array();

		if (field.lang == undefined)
			var counter = 1;
		else
			var counter = field.lang.length;

		for (l = 0; l < counter; l++)
		{
			fieldValue[l] = "";

			if (this.xmlNode)
			{
				tagName = field.dataFld;

				if (field.lang != undefined && field.lang[l] != "")
				{
					tagName += field.lang[l];
				}

				valueNode = this.xmlNode.getElementsByTagName(tagName).item(0);
					
				if (valueNode != null)
				{
					if (window.DOMParser)
						fieldValue[l] = valueNode.textContent;
					else
						fieldValue[l] = valueNode.text;

					if (field.type != "span")
					{
						fieldValue[l] = fieldValue[l].replace(/&/g, "&amp;");
					}
					if (field.type != "textarea" && field.type != "span")
					{
						fieldValue[l] = fieldValue[l].replace(/'/g, "&#039;");
					}
				}

				// save mobile values
				if (field.mobile != undefined && field.mobile)
				{
					tagName += "m";
				}

				valueNode = this.xmlNode.getElementsByTagName(tagName).item(0);
					
				if (valueNode != null)
				{
					if (window.DOMParser)
						fieldValue_m[l] = valueNode.textContent;
					else
						fieldValue_m[l] = valueNode.text;

					fieldValue_m[l] = fieldValue_m[l].replace(/'/g, "&#039;");
				}
			}
		}

		if (field.tabIndex != 0)
		{
			addToAll += " tabIndex=" + field.tabIndex + " ";
		}

		if (field.action != "")
		{
			if (field.type == "span")
			{
				addToAll += " onclick ='" + field.action + "' ";
			}
			else
			{
				addToAll += " onchange ='" + field.action + "' ";
			}
		}

		// Check if this is hidden field
		if (field.type == "hidden")
		{
			// add hidden field (not drawn)
			htmlStr += "<input type='hidden' " + addToAll + " value='" + fieldValue[0] + "'></input>";

			if (field.lang != undefined && field.lang.length > 1)
			{
				for (l=1; l<field.lang.length; l++)
				{
					htmlStr += "<input type='hidden' "		+
							   "	   id='" + this.objectName + "_" + field.dataFld + field.lang[l] + "'>";
				}
			}
			continue;
		}

		if (newLine)
		{
			if (this.currTab == -1)
				htmlStr += "<tr class='noTabsTbl_row'>";
			else
				htmlStr += "<tr class='formFrameTbl_row'>";
			newLine  = false;
		}

		widthText  = widths[currWidth];
		widthField = "";
		widthData  = "";

		spanText   = "";

		if (field.spanData == 1)
		{
			width 	  = widths[currWidth+1]
			widthData = " width = '"   + width + "' ";
		}
		else
		{
			spanText  = " colspan = '" + field.spanData + "' ";
		}
	
		// support row span
		rowSpan	   = "";
		if (field.rowSpan  != 1)
		{
			rowSpan    = " rowspan='" + field.rowSpan + "' ";
			hadRowSpan = true;
		}

		addValign = "";
		if (field.type == "multiSelect" || field.type == "multiSelectPlus" || field.type == "textarea" || field.type == "xstandard")
			addValign = " valign='top' ";
			

		if (field.type == "empty")
		{
			setHeight = "";
			if (field.height != undefined)
				setHeight = " height:" + field.height + "px";
				
			setWidth = widthText + widths[currWidth+1];
			htmlStr += "<td class='styleTdText' colspan='" + field.spanData + "'"	+
					   "    style='width:" + setWidth + "px;" + setHeight + "px'>";
		} 
		else if (field.type == "xstandard")
		{
			className = this.regularClassName;

			htmlStr	+=	"<td style='width:" + widthText + "px;text-align:right' "			+
						"    class='" + className + "' "									+
							rowSpan;

			if (frame.fields.length == 1)
			{
				htmlStr += spanText;
			}

			htmlStr += " >";

			if (eval("field.text" + this.theLanguage) != "" || (field.lang != undefined && field.lang.length > 1))
			{
				htmlStr += 	"<table width='" + widthText + "'>"								+
							"<tr class='" + className + "'>"								+
							"	<td" + addValign + " id='" 									+
									this.objectName + "_" + field.dataFld + "TextTd'>";

				if (eval("field.text" + this.theLanguage) != "")
				{
					htmlStr +=	"<div class='styleTdText_in'>"	+
								 "<span id='" + this.objectName + "_" + field.dataFld + "Text_spn'>" + eval("field.text" + this.theLanguage)+"</span>"+
								"</div>";
					
					if (field.mandatory)
						htmlStr += " *";
				}
				htmlStr += 		"</td>";

				if (field.lang != undefined && field.lang[0] != "")
				{
					if (field.lang.length > 1)
					  	htmlStr +=	"	<td align='" + flagDir + "' class='langImg'>"						+
											"<img src='../../designFiles/lang/" + field.lang[0] + ".jpg'"	+
											" id='"+this.objectName+"_"+field.dataFld+"LangImg'>"			+
									"	</td>";

					this.fieldsByLang.push (field.dataFld);
			  	}
				htmlStr +=	"</tr>"																+
							"</table>";
			}

			if (frame.fields.length != 1)
				htmlStr += 
					"</td>" 																	+
					"<td class='styleTdField #isLast#' " + widthData + spanText + rowSpan + ">";
		}
		else if (field.type != "jump")
		{
			if (field.state == "lock")
			{
				className = this.lockClassName;
				state	  = " disabled";
			}
			else
			{
				className = this.regularClassName;
				state     = "";
			}
				
			fieldClass = "styleInput";
			if (field.style != undefined && field.style != "")
			{
				fieldClass = field.style;
			}
							
			htmlStr	+=	"<td style='width:" + widthText + "px' class='" + className + "' "	+
							rowSpan	+ " id='" 												+
									this.objectName + "_" + field.dataFld + "TextTd'>"		+
							"<table	width='100%' height='100%'>"							+
							"<tr>"															+
							"	<td" + addValign + ">";

			if (eval("field.text" + this.theLanguage) != "")
			{
				htmlStr +=	"	<div class='styleTdText_in' id='" 							+
									this.objectName + "_" + field.dataFld + "Text_in'>"		+
								"<span id='" + this.objectName + "_" + field.dataFld + "Text_spn'>" + eval("field.text" + this.theLanguage) + "</span>";
				
				if (field.mandatory)
					htmlStr += " *";

				htmlStr +=     "</div>";
			}
			htmlStr += 		"</td>";

			if (field.lang != undefined && field.lang[0] != "")
			{
				if (field.lang.length > 1)
				  	htmlStr +=	"	<td align='" + flagDir + "' class='langImg'>"						+
										"<img src='../../designFiles/lang/" + field.lang[0] + ".jpg'"	+
										" id='"+this.objectName+"_"+field.dataFld+"LangImg'>"			+
								"	</td>";

				this.fieldsByLang.push (field.dataFld);
		  	}

			htmlStr +=	"</tr>"																	+
						"</table>"																+
					"</td>" 																	+
					"<td class='styleTdField #isLast#' " + widthData + spanText + rowSpan + ">";
		}
						
		// create field html according to field type
		// -----------------------------------------------------------------------------------

		var fieldHtml 	= "";

		switch (field.type)
		{
			case "text"				: 
			case "textEng"			: 
			case "amount"			:
			case "email"			:
			case "time"				:
			case "mmyy"				:
			case "phone"			:
			case "number"			: dir = this.direction;
			
									  if (field.type == "email" ||
										  field.type == "textEng" ||
										  (field.lang != undefined && field.lang[0] != "HEB" && field.lang[0] != "ARB"
										   && field.lang[0] != "HB2"))
										  dir = "ltr";

									  width     = field.width     == "" ? "120" : field.width;
									  maxLength = field.maxLength == "" ? "200" : field.maxLength

									  counterText	= "";
									  counterAction = "";
									  if (field.counter != undefined && field.counter)
									  {
										counterText   = "<div id='fieldCounter_" + this.objectName + "_" + objectId + "' class='fieldCounter'>" +
															fieldValue[0].length + " תווים" + 
														"</div>";
										counterAction = " onkeyup='return updateFieldCounter(this, \"" + this.objectName + "_" + objectId + "\")'";
									  }

										var onEnter	= "";

										if (field.enterAction != undefined && field.enterAction != "")
										{
											onEnter	= "onkeypress='javascript:if(event.keyCode == 13) " + field.enterAction + ";'";
										}

									  fieldHtml = "<input type='text' class='" + fieldClass + "' "			+
												  "	 	 maxlength='" + maxLength  + "'"					+
												  "	  	 style='width:" + width + "px'"						+
												  "		 dir='" + dir + "'"									+
												  addToAll + state + " value='" + fieldValue[0] + "'" + counterAction + onEnter + "></input>" + 
												  counterText;

									  // adding other lang fields
									 if (field.lang != undefined && field.lang.length > 1)
									 {
									  	for (l=1; l<field.lang.length; l++)
										{
											langDir = "ltr";
											
											if (field.lang[l] == "HEB" || field.lang[l] == "ARB" || field.lang[l] == "HB2")
											{
												langDir = "rtl";
											}

									  	    counterText	  = "";
									  		counterAction = "";
										    if (field.counter != undefined && field.counter)
									  	    {
												counterAction = " onkeyup='return updateFieldCounter(this, \"" + this.objectName + "_" + 
																								 field.dataFld + field.lang[l] +"\")'";
												counterText   = "<div id='fieldCounter_" + this.objectName + "_" + field.dataFld + field.lang[l] + "'" +
																"     class='fieldCounter' style='display:none'>"								 +
																	fieldValue[l].length + " תווים" + 
																"</div>";
									  	    }

											var onEnter	= "";

											if (field.enterAction != undefined && field.enterAction != "")
											{
												onEnter	= "onkeypress='if(e.keyCode === 13) " + field.enterAction + "'";
											}

											fieldHtml += 
									  			  "<input type='text' class='" + fieldClass + "' "								+
												  "	 	 maxlength	= '" + maxLength  + "'"										+
												  "	  	 style		= 'width:" + width + "px;display:none'"						+
												  "		 dir		= '" + langDir + "'"										+
												  "		 id			= '" + this.objectName + "_" + field.dataFld + field.lang[l] + "'" 		+
												  "		 name		= '" + field.dataFld + field.lang[l] + "'" 					+
												  "		 onchange 	= '" + field.action + "' " + counterAction + onEnter		+
												  "		 value 		= '" + fieldValue[l] + "'></input>" + counterText;

										}
									  }

									  if (field.type2 == "select")
									  {
									  	fieldHtml += 
												  "<select class='" + fieldClass + "' "						+
												  "	  	   style='width:" + field.width + "px;display:none'"+
												  " 	   id = '" + this.objectName + "_" + field.dataFld2 + "'>";

									  	for (var i=0; i<field.options.length; i++)
									  	{
											option = field.options[i];
										
											fieldHtml += "<option value='" + option.value + "' style='" + option.style + "'>"	+
															eval("option.text" + this.theLanguage)	+
													 	 "</option>"
									  	}
									  
									  	fieldHtml += "</select>";
													
									  }

									  break;

			case "textarea"			: rows		= field.rows  == "" ? "5"   : field.rows;
									  width     = field.width == "" ? "120" : field.width;

									  dir = field.dir;
									  if (dir == undefined)
									  	dir = this.direction;

									  counterText	= "";
									  counterAction = "";
									  if (field.counter != undefined && field.counter)
									  {
										counterText   = "<div id='fieldCounter_" + this.objectName + "_" + objectId + "' class='fieldCounter'>" +
															fieldValue[0].length + " תווים" + 
														"</div>";
										counterAction = " onkeyup='return updateFieldCounter(this, \"" + this.objectName + "_" + objectId + "\")'";
									  }

									  fieldValue[0] = fieldValue[0].replace(/\</g, "&lt;");
									  fieldHtml = "<textarea rows='" + rows + "'"					+
											  	  "			 style='width:" + field.width + "px'" 	+
												  "			 dir='"  + dir  + "' "					+
												  addToAll + " class='styleTextarea' " + state + counterAction + ">" + fieldValue[0] + "</textarea>" +
												  counterText;

									  // adding other lang fields
									  if (field.lang != undefined)
									  {
									  	for (l=0; l<field.lang.length; l++)
										{
											langDir = "ltr";
											
											if ((field.lang[l] == "HEB" || field.lang[l] == "ARB" || field.lang[l] == "HB2")
												&& field.dir == undefined)
											{
												langDir = "rtl";
											}

											if (l != 0)
											{
												fieldValue[l] = fieldValue[l].replace(/\</g, "&lt;");

									  	    	counterText	  = "";
										  		counterAction = "";
											    if (field.counter != undefined && field.counter)
										  	    {
													counterAction = " onkeyup='return updateFieldCounter(this, \"" + this.objectName + "_" + 
																									 field.dataFld + field.lang[l] +"\")'";

													counterText   = "<div id='fieldCounter_" + this.objectName + "_" + field.dataFld+field.lang[l]+"'" +
																	"     class='fieldCounter' style='display:none'>"							  +
																		fieldValue[l].length + " תווים" + 
																	"</div>";
										  	    }

												fieldHtml += 
													  "<textarea rows='" + rows + "'"								+
													  "		     dir='" + langDir + "'"								+
													  "		     id='" + this.objectName + "_" + field.dataFld + field.lang[l] + "'" 		+
													  "		     name='" + field.dataFld + field.lang[l] + "'" 		+
													  "		     onchange ='" + field.action + "' "	+ counterAction +
													  "			 class='styleTextarea'"								+
													  "		     style='display:none;width:" + field.width + "px'>" + fieldValue[l] + 
													  "</textarea>" + counterText;
											}

											if (field.mobile != undefined && field.mobile)
											{
												fieldValue_m[l] = fieldValue_m[l].replace(/\</g, "&lt;");

												fieldHtml += 
													  "<textarea rows='" + rows + "'"								+
													  "		     dir='" + langDir + "'"								+
													  "		     id='" + this.objectName + "_" + field.dataFld + field.lang[l] + "m'" 		+
													  "		     name='" + field.dataFld + field.lang[l] + "m'" 	+
													  "		     onchange ='" + field.action + "' "					+
													  "			 class='styleTextarea'"								+
													  "		     style='display:none;width:" + field.width + "px'>" + fieldValue_m[l] + "</textarea>";
											}
										}
									  }
									  break;

			case "password"			:
			case "passwordNoConfirm":
			case "passwordConfirm"	: fieldHtml = "<input type='password' class='" + fieldClass + "' "			+
												  "	 	 maxlength='" + field.maxLength  + "'"					+
												  "	  	 style='width:" + field.width + "px'"					+
												  addToAll + state + " value='" + fieldValue[0] + "'></input>";
									  break;

			case "checkbox"			: if (field.boxes == undefined)	// regular check box
									  {
										if (fieldValue[0] == "1")
											checked = " checked='true'";
										else
											checked = "";

										fieldHtml = "<input type='checkbox' "									+
												 		  addToAll + state + checked +"></input>";
										break;
									  }

									  fieldHtml = "<table>";
									  for (i=0; i<field.boxes.length / field.cols; i++)
									  {
									  	fieldHtml += "<tr class='styleTrText'>";
									  	for (j=0; j < field.cols; j++)
										{
										  	index 	= (i*field.cols)+j;	
											boxText = field.boxes[index];

											if (boxText == undefined) break;

										  	newAddToAll = " id='"       + this.objectName + "_" + objectId + index + "' " + 
														  " name='" + objectName + index + "' ";
			
											valueNode = this.xmlNode.getElementsByTagName(field.dataFld + index).item(0);
					
											fieldValue = 0;
											if (valueNode != null)
											{
												if (window.DOMParser)
													fieldValue = valueNode.textContent;
												else
													fieldValue = valueNode.text;
											}

											if (fieldValue == -1)
												checked = " checked='true'";
											else
												checked = "";

										    fieldHtml += "<td><input type='checkbox' "						+
												 		  newAddToAll + state + checked + "></input>" + boxText + "</td>";
										}
									  	fieldHtml += "</tr>";
									   }
									   fieldHtml += "</table>";
										
									  break;

			case "date"				: width     = field.width == "" ? "90" : (field.width - 23);

									  disableImg = "display:none";
									  enableImg  = "display:";

									  if (field.state == "lock")
									  {
									  	disableImg = "display:";
									  	enableImg  = "display:none";
									  }
									  	
									  fieldHtml = "<input " + addToAll										+
												  " class='datepicker " + fieldClass + "' "					+
												  "	style='width:" + width +"px'" + state 					+ 
												  " value='" + fieldValue[0] + "'>"							+
												  "</input>";
									  break;
		
			case "select"			: 
			case "yesNoSelect"		:
			case "multiSelectPlus"	: 
			case "multiSelect"		: var addHeight = ((field.height == undefined) ? "" : ";height:" + field.height + "px !important");

									  fieldHtml = "<select class='" + fieldClass + "' "						+
												  "	  	   style='width:" + field.width + "px" + addHeight  + "'";
											  
									  if (field.type == "multiSelect" || 
										  field.type == "multiSelectPlus")
										fieldHtml += " multiple ";

										var onEnter	= "";

										if (field.enterAction != undefined && field.enterAction != "")
										{
											onEnter	= "onchange='" + field.enterAction + "'";
										}

									  fieldHtml += addToAll + state + onEnter + ">";
									  	
									  fieldOptions = "";
									  for (var i=0; i<field.options.length; i++)
									  {
										option = field.options[i];
										
										if (fieldValue[0] == option.value || fieldValue[0].indexOf("," + option.value + ",") != -1)
											selected = " selected='true'";
										else
											selected = "";

										fieldOptions += "<option value='" + option.value + "' " + selected + " style='" + option.style + "'>"	+
													  		eval("option.text" + this.theLanguage)	+
													    "</option>"
									  }
									  
									  fieldHtml += fieldOptions + "</select>";

									  // adding other lang fields
									  if (field.lang != undefined && field.lang.length > 1)
									  {
									  	for (l=1; l<field.lang.length; l++)
										{
									  		fieldOptions = "";
										  	for (var i=0; i<field.options.length; i++)
									  		{
												option = field.options[i];
										
												if (fieldValue[l] == option.value)
													selected = " selected='true'";
												else
													selected = "";

												fieldOptions += "<option value='" + option.value + "' " + selected + " style='" + option.style + "'>"+
																  		eval("option.text" + this.theLanguage)	+
															    "</option>"
									  		}
									  
											fieldHtml += "<select class='" + fieldClass + "' "												+
												  		 "		  style='width:" + field.width + "px;display:none'"							+
														 "		  id='" + this.objectName + "_" + field.dataFld + field.lang[l]  + "'" 		+ 
														 "		  name='" + field.dataFld + field.lang[l]  + "'" 							+ 
												  		 "		  onchange ='" + field.action + "'>"	 									+
														 	fieldOptions									 								+
														 "</select>";
										}
									  }

									  break;
									  
			case "color"			: onClick	= "'showColorPicker(this,\"" + this.objectName + "_" + objectId + "\")'";

									  width = field.width - 30;

									  color = "white";
									  text  = "?";
									  if (fieldValue[0] != undefined && fieldValue[0] != "")
									  {
									  	color = fieldValue[0];
										text  = "";
									  }
									  fieldHtml	+= "<table>"																	+
											  	   "<tr>"																		+
												   "	<td>"																	+
												   			"<input " + addToAll												+
												   " 			  class='" + fieldClass + "' "									+
												   "				  style='direction:ltr;width:" + width + "px'"				+
												   " 			  value='" + fieldValue[0] + "' " + state						+ 
												   " 			  onchange='chooseColor();' />"									+
												   		   "</td>"																+
												   "		<td>"																+
												   				"<span class='styleColorPicker' "								+
												   "				  style='background-color:" + color + "'"					+
												   "		 	 	  title='בחירת צבע'"										+ 
												   "				  id='" + this.objectName + "_" + objectId + "_spn'" 		+
												   "     	  	 onclick=" + onClick + ">" + text + "</span>"					+
												   "		</td>"																+
												   "	</tr>"																	+
												   "	</table>";
			
									  break;

			case "tablePaging"		:
			case "fieldPaging"		:
			case "tableForm"		: 
			case "table"			: 
									  break;

			case "uploadFile"		: width     = field.width     == "" ? "120" : field.width;
									  fieldHtml = "<input type='file' class='" + fieldClass + "' "				+
												  "	 	 maxlength='" + maxLength  + "'"		+
												  "	  	 style='width:" + width + "px'"			+
												  "		 dir='" + dir + "'"						+
												  		 addToAll								+
												  "		 ></input>"			+
												  "<input type='hidden' name='redirectUrl' "	+
												  "	      value='" + window.location.pathname + "'>";

									  break;


			case "multiUpload"		: fieldHtml = "<div id='SWFUploadDiv" + this.objectName + "' style='padding:5px'>" +
												  "	   <div id='SWFUploadHolder" + this.objectName + "'>You need a newer version of flash</div>" +
				   								  "</div>" + 
												  "<input type='hidden' id='uploadDone" + this.objectName + "' value='1' />" + 
												  "<div id='filesDisplay" + this.objectName + "' dir='ltr' class='filesDisplay'>" +
													"	<ul id='mmUploadFileListing" + this.objectName + "'></ul>" +
													"	<br class='clr' />" +
												  "</div>";

									  this.multiUploadFrame = frameIndex;

									  break;

			case "onlineUpload"		: fileWidth = field.width*1 - 60;
	
									  if (commonGetGlobalData("guiLang") == "ENG")
									  {
											 margin = "margin-right:9px";
											 side = "left";
									  }
									  else
									  {
											 side = "right";
											 margin = "margin-left:9px";
									  }

									  var progressRight = fileWidth + 10 - 51;

									  fieldHtml = "<div id='SWFUploadDiv" + this.objectName + field.dataFld + "' " +
											  	  "     style='float:" + side + ";margin-top:8px;" + margin + "'>" +
												  "	<div id='SWFUploadHolder" + this.objectName + field.dataFld  + "'>" + 
												  "  	You need a newer version of flash" + 
												  " </div>" +
											      "</div>" +
												  "<div style='position:relative'>" +
											  	  "	<input type='text' disabled='true' id='uploadFileName" + this.objectName + field.dataFld+ "' " + 
											  	  "        dir='ltr' class='" + fieldClass + "' value='' " + 
												  "        style='float:" + side + ";" + margin + ";margin-top:8px;width:" + fileWidth + "px' />" +
												  " <input type='hidden' id='uploadDone" + this.objectName + field.dataFld + "' value='1' />" + 
												  " <span class='field_progressBar' " +
												  "		  style='display:none;position:absolute; top:9px;right:" + progressRight + "px;'" +
												  "		  id='uploadProgress" + this.objectName + field.dataFld + "'></span>" +
												  "</div>";

									  this.onlineUploadFrames.push (frameIndex);
									  this.onlineUploadFields.push (field.dataFld);

									  break;

			case "xstandard"		: height	= field.height  == "" ? "100" : field.height - 20;
									  width     = field.width 	== "" ? "120" : field.width;

									  if (field.lang == undefined || field.lang[0] == "HEB" || field.lang[0] == "ARB" ||
										  field.lang[0] == "HB2")
									  	dir = "rtl";
									  else
									  	dir = "ltr";

									  dir = field.dir;
									  if (dir == undefined)
									  	dir = this.direction;

									  fieldHtml = "<div class='styleEditor styleEditor" + frame.fields.length + "'>" + 
											  	  "<textarea style='width: " + width + "px; height: " + height + "px' " +
												  "          class='mce_editor' id='formTinymce" + this.objectName + "_" + field.dataFld + "'>" + 
												  	pleaseWait + "</textarea>" +
												  "<input type='hidden' " + addToAll + " value='" + fieldValue[0] + "'/>";

									 // adding other lang fields
									 if (field.lang != undefined && field.lang.length > 1)
									 {
									 	for (l=1; l<field.lang.length; l++)
										{
											fieldHtml += 
												  "<input type='hidden'" 															+
												  "		 id='" + this.objectName + "_" + field.dataFld + field.lang[l] + "'" 		+
												  "		 name='" + this.objectName + "_" + field.dataFld + field.lang[l] + "'" 		+
												  "		 value='" + fieldValue[l] + "'" 											+
												  "		 style='display:none'></input>";
										}
									}

									fieldHtml += "</div>";

									this.numTinymce++;

									this.tinymceFields[this.numTinymce] = field.dataFld;

									break;

			case "span"				:
			default					: className = "";
									  if (field.className != undefined)
									  	className = " class=" + field.className;

									  var dir = this.direction;

									  if (field.dir != undefined)
											 dir = field.dir;

									  fieldHtml = "<div class='spanField'>"							+
											  		"<span dir='" + dir + "'" 						+ 
														addToAll + className + ">" + fieldValue[0] 	+ 
												    "</span>"										+
												  "</div>";
									  break;
		}

		helpText = eval("field.helpText" + this.theLanguage);
	  	if (helpText != undefined && helpText != "")
			fieldHtml += "<div style='font-size:9px;padding-bottom:3px;padding-right:4px;padding-top:3px;'>" +
						 	"<img src='../../designFiles/helpIcon.png' style='vertical-align:middle' /> "+
							 helpText + "</div>";

//		if (field.type != "xstandard" && field.type != "color" && field.type != "multiUpload")
//			fieldHtml = "&nbsp;" + fieldHtml;

		if (field.type != "jump")
			htmlStr += fieldHtml + "</td>";

		currWidth += field.spanData + 1;

		if (currWidth == widths.length)
		{
			htmlStr	   = htmlStr.replace("#isLast#", "last");
			htmlStr   += "</tr>";

			colspan = widths.length*2-1;

			newLine    = true;
			currWidth  = 0;
		}
		else
		{
			htmlStr	   = htmlStr.replace("#isLast#", "");
		}
		
		// ---------------------------------------------------------------------------------------
	}

	if (currWidth != 0)
	{
		if (!hadRowSpan)
		{
			missingCols = widths.length - currWidth;
			missingCols += Math.round (missingCols/2) + 1;	// add the sep columns
			colspan = widths.length*2-1;

			htmlStr += 		"<td colspan='" + missingCols + "' class='styleTdText'>"			+
							"</td>";
		}

		htmlStr +=		"</tr>";
	}
	
	return htmlStr;
  }
  catch (error)
  {
 	throw new Error(20, error.description + "\n\t(dynamicFormObj.generateData)");
  }
}

/* ------------------------------------------------------------------------ */
/* dynamicFormObj_createTablePaging											*/
/* ------------------------------------------------------------------------ */
function dynamicFormObj_createTablePaging (frameIndex, fieldIndex)
{
  try
  {
  	if (this.tableFrameIndex != -1)
		 throw new Error(1,"Form Object support only one table paging");

	// save frame and field indexes
	this.tableFrameIndex = frameIndex;
	this.tableFieldIndex = fieldIndex;

	field = this.frames[frameIndex].fields[fieldIndex];

	// send request
	var serverObj = field.fillBy.serverObject;
	if (serverObj == undefined)
		var serverObj = new serverInterfaceObj();

	if (field.fillBy.requestXml != undefined)
		serverObj.setXml (field.fillBy.requestXml);

	serverObj.addTag ("selected", "1");
	var responseXml = serverObj.sendRequest(field.fillBy.requestName);

	// save the response xml object
	field.fillBy.responseXml = responseXml;

	html  =	"<tr>"																					+
			  "<td width='100%'>"																	+
			  	"<table style='background-color:#FFFFFF;width:100%' border='0'>"					+
				"<tr>"																				+	
					"<td>"																			+
						"<div class='styleDataDiv' "												+
						"	  style='height:" + field.height + "px;width:100%'>"					+
						"<table width='100%' cellspacing='1' cellpadding='1'"						+
						"		dir='" + this.direction + "'"										+
						"		id='" + this.tableFieldId + "'"										+
						"		style='table-layout:fixed;display:none'>"							+
						"<tr class='styleGrayTR'>";
	
	if (this.theLanguage == "HEB")
		title 	= "לחץ למחיקה";
	else
		title	= "Click to delete";


	for (var c=0; c<field.tableCols.length; c++)
	{
		col   = field.tableCols[c];

		width 	  = eval("col.width" + this.theLanguage);

		html   += 			"<td width='" + width + "'>" +
								"&nbsp;<span dataFld='"  + col.xmlTag + "'></span>" 				+
							"</td>";
	}

	html +=					"<td width='17'>"														+
								"<img src='images/unselectItem.gif' "								+
								"	  style='cursor:pointer'"										+
								"	  title='" + title + "'"										+
								"	  onclick='" + this.objectName + ".deleteOnClick(this)'"		+
								">"																	+
							"</td>"																	+
						"</tr>"																		+
						"</table>"																	+
						"</div>"																	+
					"</td>"																			+
				"</tr>"																				+
				"</table>"																			+
			  "</td>"																				+
			"</tr>";

	return html;
  }
  catch (error)
  {
 	throw new Error(20, error.description + "\n\t(dynamicFormObj.createTablePaging)");
  }
}

/* ------------------------------------------------------------------------ */
/* dynamicFormObj_loadTablePaging											*/
/* ------------------------------------------------------------------------ */
function dynamicFormObj_loadTablePaging ()
{
  try
  {
  	if (this.tableFrameIndex != -1 && this.tableFieldIndex != -1)
	{
		field = this.frames[this.tableFrameIndex].fields[this.tableFieldIndex];

		var xmlNode	= field.fillBy.responseXml.getElementsByTagName(field.fillBy.xmlDataTag).item(0);
		
		tableXmlObject = eval (this.tableFieldSrc);

		tableXmlObject.loadXML(xmlNode.xml);

		numRows = xmlNode.childNodes.length;
		if (numRows == 0)
			eval(this.tableFieldId).style.display = "none";
		else
			eval(this.tableFieldId).style.display = "";

		eval(this.tableCountSpan).innerText = numRows;
	}
  }
  catch (error)
  {
 	throw new Error(20, error.description + "\n\t(dynamicFormObj.loadTablePaging)");
  }
}

/* ------------------------------------------------------------------------ */
/* dynamicFormObj_addToTableField											*/
/* ------------------------------------------------------------------------ */
function dynamicFormObj_addToTableField (xml)
{
  try
  {
	if (this.tableFrameIndex != -1 && this.tableFieldIndex != -1)
	{
		field = this.frames[this.tableFrameIndex].fields[this.tableFieldIndex];

		var xmlNode = field.fillBy.responseXml.getElementsByTagName(field.fillBy.xmlDataTag).item(0);

		xmlNode.appendChild (xml.cloneNode(true));

		this.loadTablePaging ();
	}
  }
  catch (error)
  {
 	throw new Error(20, error.description + "\n\t(dynamicFormObj.addToTableField)");
  }
}

/* ------------------------------------------------------------------------ */
/* dynamicFormObj_deleteFromTableField										*/
/* ------------------------------------------------------------------------ */
function dynamicFormObj_deleteFromTableField (xml)
{
  try
  {
	if (this.tableFrameIndex != -1 && this.tableFieldIndex != -1)
	{
		field = this.frames[this.tableFrameIndex].fields[this.tableFieldIndex];

		var xmlNode = field.fillBy.responseXml.getElementsByTagName(field.fillBy.xmlDataTag).item(0);

        // create row xml text as unchecked
        xml.getElementsByTagName(field.fillBy.checked).item(0).text = 0;
        xmlText0 = xml.text;

        // create row xml text as checked
        xml.getElementsByTagName(field.fillBy.checked).item(0).text = 1;
        xmlText1 = xml.text;

        for (var i=0; i < xmlNode.childNodes.length; i++)
        {
            if (xmlNode.childNodes[i].text == xmlText0 ||
                xmlNode.childNodes[i].text == xmlText1)
            {
                xmlNode.removeChild (xmlNode.childNodes[i]);
                this.loadTablePaging ();
                break;
            }
        }
	}
  }
  catch (error)
  {
 	throw new Error(20, error.description + "\n\t(dynamicFormObj.deleteFromTableField)");
  }
}
/* ------------------------------------------------------------------------ */
/* dynamicFormObj_deleteOnClick												*/
/* ------------------------------------------------------------------------ */
function dynamicFormObj_deleteOnClick (img)
{
  try
  {
	if (this.tableFrameIndex != -1 && this.tableFieldIndex != -1)
	{
		field = this.frames[this.tableFrameIndex].fields[this.tableFieldIndex];

		var rowIndex  = img.parentNode.parentNode.rowIndex;
		var xmlData	  = field.fillBy.responseXml.getElementsByTagName(field.fillBy.xmlDataTag).item(0);
		var xml 	  = xmlData.childNodes[rowIndex];

		this.deleteFromTableField (xml);
	}
  }
  catch (error)
  {
 	throw new Error(20, error.description + "\n\t(dynamicFormObj.deleteOnClick)");
  }
}

/* ------------------------------------------------------------------------ */
/* dynamicFormObj_openPagingSelect											*/
/* ------------------------------------------------------------------------ */
function dynamicFormObj_openPagingSelect (frameIndex, width)
{
	args = {formObj 	: this, 
			fieldObj 	: this.frames[frameIndex].fields[0]}
	
	showModalDialog (commonGoToMain() + "html/pagingSelect.html",args,
					 "status:no;help:no;scroll:no;resizable:yes;dialogWidth:" +  width + 
					 "px;dialogHeight:350px");
}

/* ------------------------------------------------------------------------ */
/* dynamicFormObj_createTableForm											*/
/* ------------------------------------------------------------------------ */
function dynamicFormObj_createTableForm (frameIndex)
{
  try
  {
//  	if (this.tableFormId != -1)
//		 throw new Error(1,"Form Object support only one table form");

	field = this.frames[frameIndex].fields[0];

	this.pageObj	 = field.pageObj;

	if (this.tableFormId == -1)
	{
		this.tableFormId = this.pageObj.addTableForm ();
	}

	this.pageObj.setTableColumns (this.tableFormId,field.tableCols);
	this.pageObj.setTableHeight	 (this.tableFormId,field.height);
	this.pageObj.showComponent	 (this.tableFormId);

	if (field.fillBy != undefined)
		this.loadTableForm (field.fillBy);
  }
  catch (error)
  {
 	throw new Error(20, error.description + "\n\t(dynamicFormObj.createTableForm)");
  }
}

/* ------------------------------------------------------------------------ */
/* dynamicFormObj_loadTableForm												*/
/* ------------------------------------------------------------------------ */
function dynamicFormObj_loadTableForm (fillBy)
{
  try
  {
  	if (this.tableFormId == -1)
		 throw new Error(1,"No table form in this form");

	this.pageObj.setTableXmls	 (this.tableFormId,
								  fillBy.xmlNode,
								  fillBy.xmlDataTag, 
							 	  fillBy.xmlTotalTag);
//	this.pageObj.setTablePaging	(this.tableFormId,fillBy.xmlNode);
	this.pageObj.generateTable	(this.tableFormId);
  }
  catch (error)
  {
 	throw new Error(20, error.description + "\n\t(dynamicFormObj.createTableForm)");
  }
}

/* ------------------------------------------------------------------------ */
/* dynamicFormObj_tableFormChanged											*/
/* ------------------------------------------------------------------------ */
function dynamicFormObj_tableFormChanged ()
{
  try
  {
  	if (this.tableFormId == -1)
		 throw new Error(1,"No table form in this form");

	return (this.pageObj.tableChanged(this.tableFormId));
  }
  catch (error)
  {
 	throw new Error(20, error.description + "\n\t(dynamicFormObj.tableFormChanged)");
  }
}

/* ------------------------------------------------------------------------ */
/* dynamicFormObj_isCollapse												*/
/* ------------------------------------------------------------------------ */
function dynamicFormObj_isCollapse ()
{
	if (document.getElementById(this.closeTblId) == null)
		return false;
	else
		return (document.getElementById(this.closeTblId).style.display == "");
}

/* ------------------------------------------------------------------------ */
/* dynamicFormObj_collapseSection											*/
/* ------------------------------------------------------------------------ */
function dynamicFormObj_collapseSection (toCollapse)
{
  try
  {
	var frame = this.frames[0];
	
	if (frame.collapse && frame.expandIds == null)
		return;

	var divIds = frame.expandIds;

	// TBD - collapse more than first div
		
	var dataDiv = document.getElementById(divIds[0]);

	var hiddenTable;
	var shownTable;

	if (toCollapse)
	{
		hiddenTable = document.getElementById(this.closeTblId);
		shownTable  = document.getElementById(this.openTblId);
	}
	else
	{
		hiddenTable = document.getElementById(this.openTblId);
		shownTable  = document.getElementById(this.closeTblId);
	}

	var currHeight = dataDiv.offsetHeight*1 + shownTable.offsetHeight*1;

	if (shownTable.style.display  == "none")	// no need to collapse - already in this state
		return;
		
	shownTable.style.display   = "none";
	hiddenTable.style.display  = "";

	var theObject;
	if (toCollapse) 
	{
		// add other collapsed items to the global height and hide them
		if (frame.collapseIds != null)
		{
			for (var i=0; i<frame.collapseIds.length; i++)
			{
				theObject   			 = document.getElementById(frame.collapseIds[i]);
//				theObject   			 = eval("window." + frame.collapseIds[i]);
				currHeight 				+= theObject.offsetHeight*1;
				theObject.style.display  = "none";
			}
		}
	}
	else
	{
		// subtract other collapsed items to the global height and show them
		if (frame.collapseIds != null)
		{
			for (var i=0; i<frame.collapseIds.length; i++)
			{
				theObject   			 = document.getElementById(frame.collapseIds[i]);
//				theObject   			 = eval("window." + frame.collapseIds[i]);
				theObject.style.display  = "";
				currHeight 				-= theObject.offsetHeight*1;
			}
		}

	}
	dataDiv.style.height   = currHeight*1 - hiddenTable.offsetHeight*1;
  }
  catch (error)
  {
 	throw new Error(20, error.description + "\n\t(dynamicFormObj.collapseSection)");
  }
}

/* ------------------------------------------------------------------------ */
/* dynamicFormObj_selectUnSelect											*/
/* ------------------------------------------------------------------------ */
function dynamicFormObj_selectUnSelect (fieldName, toSelect)
{
  try
  {
	field = document.getElementById(this.objectName + "_" + fieldName);

	for (var i=0; i<field.options.length; i++)
	{
		field.options[i].selected = toSelect;
	}
  }
  catch (error)
  {
  	showError ("dynamicFormObj", "selectUnSelect" , error);
  }
}

/* ------------------------------------------------------------------------ */
/* dynamicFormObj_generateFrame												*/
/* ------------------------------------------------------------------------ */
function dynamicFormObj_generateFrame (frameIndex ,width) 
{
  try
  {
	frame = this.frames[frameIndex];
	
	if (frame == undefined)
		throw new Error (20, "frame " + frameIndex + " does not exist");

	var htmlStr  = "";
		
	this.checkLanguage ();

	if (frame.fields[0].type.search ("tableForm") != -1)
	{
		this.createTableForm (frameIndex);
		return htmlStr;
	}
	
	// update last width according to this.formWidth
	// --------------------------------------------------------------------
	widths  = eval("frame.widths." + this.theLanguage);

	var totalWidth = 0;
	
	for (var i = 0; i < widths.length; i++)
	{
		totalWidth += widths[i];
	}

	if (totalWidth < this.formWidth)
	{
		if (this.theLanguage == "HEB")
		{
			frame.widths.HEB[widths.length-1] += this.formWidth - totalWidth;
		}
		else
		{
			frame.widths.ENG[widths.length-1] += this.formWidth - totalWidth;
		}
	}

	// --------------------------------------------------------------------

	var align1;
	var align2;

	if (this.theLanguage == "HEB")
	{
		align1 = "right";
		align2 = "left";
	}
	else
	{
		align1 = "left";
		align2 = "right";
	}
		
	if (frame.collapse)
	{
		htmlStr	+= 	"<table width='" + width + "' id='" + this.closeTblId + "' class='noTabsTbl close'>"+
					"<tr>"																				+
					"	<td>"																			+
					"		<table width='" + width + "' class='noTabsTbl_formTitle'>"					+
					"		<tr>"																		+
					"			<td class='noTabsTbl_title'>"											+
					"				<div>" 																+ 
										eval("frame.text" + this.theLanguage) 							+ 
					"					<img src='../../designFiles/iconExpand.png' "					+
				    "			    		 title='" + this.getTextByLang("פרוש","Expand") + "'"		+
					"						 onclick='" + this.objectName + ".collapseSection(0);'/>"	+
					"				</div>"																+
					"			</td>"																	+
					"		</tr>"																		+
					"		</table>"																	+
					"	</td>"																			+
					"</tr>"																				+
					"</table>" 																			+
					"<table width='" + width + "' id='" + this.openTblId + "' class='noTabsTbl open' "	+
					"		style='display:none'>"														+
					"<tr>"																				+
					"	<td>"																			+
					"		<table width='" + width + "' class='noTabsTbl_formTitle'>"					+
					"		<tr>"																		+
					"			<td class='noTabsTbl_title'>"											+
					"				<div>" 																+ 
										eval("frame.text" + this.theLanguage) 							+  
					"					<img src='../../designFiles/iconCollapse.png' "					+
				    "			    		 title='" + this.getTextByLang("כווץ","Collapse") + "'"		+
					"						 onclick='" + this.objectName + ".collapseSection(1);'/>"	+
					"				</div>"																+
					"			</td>"																	+
					"		</tr>"																		+
					"		</table>"																	+
					"	</td>"																			+
					"</tr>"																				+
					"<tr>"																				+
					"	<td>"																			+
					"		<table width='100%' dir='" + this.direction + "' class='noTabs_dataTbl'>"	+
								this.generateData(frameIndex) 							   				+
					"		</table>"																	+
					"	</td>"																			+
					"</table>";
	}
	else
	{
		htmlStr +=	"<table width='" + width + "' id='" + this.openTblId + "' class='formFrameTbl'>"	+
						this.generateData(frameIndex) 									   	   	+
					"</table>";
    }
	return htmlStr;
  }
  catch (error)
  {
 	throw new Error(20, error.description + "\n\t(dynamicFormObj.generateFrame)");
  }
}

/*--------------------------------------------------------------------------*/
/* dynamicFormObj_getMaxFrameWidth											*/
/*--------------------------------------------------------------------------*/
function dynamicFormObj_getMaxFrameWidth () 
{
  try
  {
	maxWidth = 0;

	for (var f=0; f < this.frames.length; f++)
	{
		frame  = this.frames[f];
		widths = eval("frame.widths." + this.theLanguage);
		
		if (widths == undefined)
			continue;

		frameWidth = 0;

		for (var w=0; w < widths.length; w++)
		{
			frameWidth += widths[w]*1;
			
			// add the cellspacing to the width
			if (w != 0)
				frameWidth += 2;
		}

		if (frameWidth > maxWidth)
			maxWidth = frameWidth;
	}

	return maxWidth;
  }
  catch (error)
  {
 	throw new Error(20, error.description + "\n\t(dynamicFormObj.getMaxFrameWidth)");
  }
}

/*--------------------------------------------------------------------------*/
/* dynamicFormObj_generateTabs												*/
/*--------------------------------------------------------------------------*/
function dynamicFormObj_generateTabs (width)
{
  try
  {
	htmlStr =  "<table>"			+
			   "<tr>";

	var tabClass = "formTab_selected";

	for (var f=0; f<this.frames.length; f++) 
	{
		frame = this.frames[f];

		// no more support of frame.isSpecial)

		tabText = eval("frame.text" + this.theLanguage);

		var firstOrLast = ""
		if (f == 0)
			firstOrLast = " first";
		else if (f == this.frames.length - 1)
			firstOrLast = " last"

		htmlStr += 	 "<td id='" + this.objectName + "_tabCurr" + f + "' "							+
					 "	onclick='" + this.objectName + ".selectTab(" + f + ")'"						+
					 "	class='" + tabClass + firstOrLast + "'>"									+
					 "	<div>" + tabText + "</div>"													+
					 "</td>";
	
		tabClass	= "formTab";
	}

	htmlStr += "</tr>"																				+
			   "</table>";

	return htmlStr;
  }
  catch (error)
  {
 	throw new Error(20, error.description + "\n\t(dynamicFormObj.generateTabs)");
  }
}

/*--------------------------------------------------------------------------*/
/* dynamicFormObj_selectTab													*/
/*--------------------------------------------------------------------------*/
function dynamicFormObj_selectTab (tab)
{
  try
  {
	var firstOrLast = ""
	if (this.currTab == 0)
		firstOrLast = " first";
	else if (this.currTab == this.frames.length - 1)
		firstOrLast = " last"

  	document.getElementById(this.objectName + "_frame" + this.currTab).style.display = "none";
  	document.getElementById(this.objectName + "_tabCurr" + this.currTab).className 	 = "formTab" + firstOrLast;

	this.currTab = tab;

	var firstOrLast = ""
	if (this.currTab == 0)
		firstOrLast = " first";
	else if (this.currTab == this.frames.length - 1)
		firstOrLast = " last"

  	document.getElementById(this.objectName + "_frame" + this.currTab).style.display = "";
  	document.getElementById(this.objectName + "_tabCurr" + this.currTab).className 	 = "formTab_selected" + firstOrLast;


	if (document.getElementById("showPic") != undefined)
		document.getElementById("showPic").style.display = "none";

	var filename = window.location.pathname.split("/").pop().replace(".html", "");

	commonSetGlobalData (filename + "_" + this.objectName + "_selectedTab", tab);

  }
  catch (error)
  {
// 	throw new Error(20, error.description + "\n\t(dynamicFormObj.selectTab)");
  }
}

/*--------------------------------------------------------------------------*/
/* dynamicFormObj_generateFormHtmlStr										*/
/*--------------------------------------------------------------------------*/
function dynamicFormObj_generateFormHtmlStr(tabs) 
{
  try
  {
  	if (tabs || !this.frames[0].collapse)
	{
		this.currTab = 0;
	}

	var htmlStr = 	"<form id='" + this.objectName + "' ";
					
	if (this.formAction != "")
	{
		htmlStr += 	"	   method='post' enctype='multipart/form-data' "							+
					"	   action='" + this.formAction + "' "										+
					"	   onsubmit='" + this.objectName + ".loadXstandardField();"					+ 
									     this.objectName + ".loadTinymceField();return " + this.formSubmit + "();'";
	}
					
	htmlStr +=		">"																				+
					"<table class='formTbl'>";
	
	// get the max frame width
	this.formWidth = this.getMaxFrameWidth();

	if (tabs && !this.frames[0].collapse)
	{
		htmlStr +=		"<tr>"																		+
							"<td class='formTabs'>"													+
								this.generateTabs (this.formWidth)									+
							"</td>"																	+
						"</tr>";
	}
	else if (!this.frames[0].collapse)
	{
		htmlStr +=     "<tr>"																		+
							"<td class='formNoTabsTop'>"											+
							"</td>"																	+
					   "</tr>";
  	}

	
	display = "";
	for (var f=0; f<this.frames.length; f++) 
	{
		if (tabs && f > 0)
		{
			display  = " style='display:none'";
		}

		htmlStr +=	"<tr id='" + this.objectName + "_frame" + f + "'" + display + ">" 				+
						"<td>"																		+
							this.generateFrame(f, this.formWidth)									+
						"</td>"																		+
					"</tr>";
	}
							
	htmlStr +=		"</table>" + 
					"</form>";

	return htmlStr;
  }
  catch (error)
  {
 	throw new Error(20, error.description + "\n\t(dynamicFormObj.generateFormHtmlStr)");
  }
}

var globalTinymceText  = new Array();

/*--------------------------------------------------------------------------*/
/* dynamicFormObj_generateForm												*/
/*--------------------------------------------------------------------------*/
function dynamicFormObj_generateForm (tabs) 
{
  try
  {
  	if (tabs == undefined) tabs = true;

	// fill the xml node
	if (this.xmlNode == null || this.xmlNode == "") 
	{
		var xmlStr = "";
		for (var f = 0; f < this.frames.length; f++) 
		{
			frame = this.frames[f];
			
			for (var i = 0; i < frame.fields.length; i++)
			{
				if (frame.fields[i].dataFld != "")
				{
					if (frame.fields[i].lang != undefined && frame.fields[i].lang.length != 0)
					{
						for (l=0; l<frame.fields[i].lang.length; l++)
						{
							xmlStr += "<"  + frame.fields[i].dataFld + frame.fields[i].lang[l] + "><![CDATA["		+
										  frame.fields[i].defaultValue										+
									  "]]></" + frame.fields[i].dataFld + frame.fields[i].lang[l] + ">";

							if (frame.fields[i].mobile != undefined && frame.fields[i].mobile)
								xmlStr += 
									  "<"  + frame.fields[i].dataFld + frame.fields[i].lang[l] + "m><![CDATA["		+
										  frame.fields[i].defaultValue										+
									  "]]></" + frame.fields[i].dataFld + frame.fields[i].lang[l] + "m>";
						}
					}
					else
					{
						xmlStr += "<"  + frame.fields[i].dataFld + "><![CDATA["		+
									  frame.fields[i].defaultValue			+
								  "]]></" + frame.fields[i].dataFld + ">";

						if (frame.fields[i].dataFld2 != undefined && frame.fields[i].dataFld2 != "")
						{
							xmlStr += "<"  + frame.fields[i].dataFld2 + "><![CDATA["		+
										  frame.fields[i].defaultValue			+
									  "]]></" + frame.fields[i].dataFld2 + ">";
						}
					}
				}
			}
		}

		xmlStr = "<data>" + xmlStr + "</data>";

		var requestXml = new xmlObj(false);
		
		this.setXml (requestXml.getNodeXml (xmlStr, "data"));
	}

	var htmlStr = this.generateFormHtmlStr(tabs);

	document.getElementById(this.destinationSpanName).innerHTML = htmlStr;

	// setup for the date picker
	if ($(".datepicker") != undefined)
	{
		$(".datepicker").datepicker({ changeMonth: true,
						    		  changeYear: true,
									  showOn: "button",
								      buttonImage: "../../designFiles/iconCalendar.png",
									  minDate: new Date(1950, 0, 1),
									  dateFormat: "dd-mm-yy" });
			
		$(".datepicker").datepicker($.datepicker.regional["he"]);
	}

	var theGuiLang = commonGetGlobalData("guiLang");

	// specific handling for the multiple selection fields && datepicker
	// ----------------------------------------------------------------------

	for (var f = 0; f < this.frames.length; f++)
	{
		frame = this.frames[f];

		for (var i = 0; i < frame.fields.length; i++)
		{
			field = frame.fields[i];

			if (field.type == "multiSelect" || field.type == "multiSelectPlus")
			{
				// select options by the xml
				valuesNode = this.xmlNode.getElementsByTagName(field.dataFld).item(0);

				if (valuesNode != null)
				{
					if (window.DOMParser)
						values = valuesNode.textContent.split(" ");
					else
						values = valuesNode.text.split(" ");
					
					fieldObj = document.getElementById(this.objectName + "_" + field.dataFld);

					if (fieldObj.options != null && values.length != 0)
					{
						for (var j = 0; j < fieldObj.options.length; j++)
						{
							for (var v=0; v < values.length; v++)
							{
								if (values[v] == fieldObj.options[j].value)
									fieldObj.options[j].selected = true;
							}
						}
					}
				}
			}
			else if (field.type == "date")
			{
				if (field.state == "lock")	
				{
					$("#" + this.objectName + "_" + field.dataFld).datepicker("option", "minDate", -1);
					$("#" + this.objectName + "_" + field.dataFld).datepicker("option", "maxDate", -2); 
				}
			}
		}
	}

	if (this.xstandardField != "")
	{
		fieldNode = this.xmlNode.getElementsByTagName (this.xstandardField + this.formLanguage).item(0);

		if (fieldNode == null)
			fieldNode = this.xmlNode.getElementsByTagName (this.xstandardField).item(0);

		if (fieldNode != null)
		{
			document.getElementById(this.objectName + "_editor0").Value = fieldNode.text;
		}
	}

	if (this.numTinymce != 0)
	{
		for (i = 1; i <= this.numTinymce; i++)
		{
			fieldNode = this.xmlNode.getElementsByTagName (this.tinymceFields[i] + this.formLanguage).item(0);

			if (fieldNode == null)
				fieldNode = this.xmlNode.getElementsByTagName (this.tinymceFields[i]).item(0);

			if (fieldNode != null)
			{
				if (window.DOMParser)
					globalTinymceText["formTinymce" + this.objectName + "_" + this.tinymceFields[i]]  = fieldNode.textContent;
				else
					globalTinymceText["formTinymce" + this.objectName + "_" + this.tinymceFields[i]]  = fieldNode.text;
			}
		}

		startTinyMCE(this.formLanguage);
	}

  }
  catch (error)
  {
 	throw new Error(20, error.description + "\n\t(dynamicFormObj.generateForm)");
  }

  try
  {
		if (tabs && globalType != undefined && globalType.indexOf("update") != -1)
		{
			var filename = window.location.pathname.split("/").pop().replace(".html", "");

			var selectedTab = commonGetGlobalData (filename + "_" + this.objectName + "_selectedTab");

			if (selectedTab != undefined && selectedTab <= this.frames.length)
				this.selectTab(selectedTab);
		}
   }
   catch (e) {}
}

/*
function loadTinymce ()
{
	for (var i=1; i <= 8; i++)
		if (globalTinymceField[i] != undefined && globalTinymceField[i] != "")
			tinymce.get(globalTinymceField[i].substring(1)).setContent(globalTinymceText[i]);

	var lang = commonGetGlobalData ("tinymceLang");

	if (lang == 'HEB' || lang == 'HB2' || lang == 'ARB')
	{
		tinyMCE.activeEditor.getWin().document.body.style.direction = "rtl";
		tinyMCE.activeEditor.getWin().document.body.style.textAlign = "right";
	}
	else
	{
		tinyMCE.activeEditor.getWin().document.body.style.direction = "ltr";
		tinyMCE.activeEditor.getWin().document.body.style.textAlign = "left";
	}
}*/

/*--------------------------------------------------------------------------*/
/* dynamicFormObj_addCollapse												*/
/*--------------------------------------------------------------------------*/
function dynamicFormObj_addCollapse (divsId, moreCollapseIds)
{
  try
  {
	if (this.frames.length > 0 && divsId.length > 0)
	{
		this.frames[0].collapse    = true;
		this.frames[0].expandIds   = divsId;
		this.frames[0].collapseIds = moreCollapseIds;
	}
  }
  catch (error)
  {
 	throw new Error(20, error.description + "\n\t(dynamicFormObj.addCollapse)");
  }
}

/*--------------------------------------------------------------------------*/
/* dynamicFormObj_validate													*/
/*--------------------------------------------------------------------------*/
function dynamicFormObj_validate () 
{
  try
  {
	for (c = 0; c < this.onlineUploadFrames.length; c++)
	{
		if (document.getElementById("uploadDone" + this.objectName + this.onlineUploadFields[c]).value == "0")
		{
			var frameId = this.onlineUploadFrames[c];
			var fieldId = this.onlineUploadFields[c];
			var field = this.frames[frameId].fields[fieldId];
			var lang = "";
			if (field.lang != undefined)
				lang = this.formLanguage;

			fieldName 	= field.dataFld + lang;

			this.displayAndSetError (fieldName, "יש להמתין לסיום הטעינה", "");
			return false;
		}
	}
  }
  catch (error)
  {
	if (field != undefined)
	 	throw new Error(20, "\n\n" + error.description + 
							"\n\t(dynamicFormObj.validate)");
  }

  try
  {
	for (var f=0; f < this.frames.length; f++) 
	{
		frame = this.frames[f];

		for (var i=0; i< frame.fields.length; i++)
		{
			field = frame.fields[i];

			// for debug
			// alert ("frame: " + f + "  field: " + i + "   type: " + field.type);

			switch (field.type)
			{
				// ----------------------------------------------------------

				case "text"				:
				case "textEng"			: 	
				case "textarea"			:
				case "passwordNoConfirm":	if (!this.checkText(field))
												return false;
											break;

				// ----------------------------------------------------------

				case "time"				: 	if (!this.checkTime(field))
												return false;
											break;

				// ----------------------------------------------------------

				case "mmyy"				: 	if (!this.checkMMYY(field))
												return false;
											break;

				// ----------------------------------------------------------

				case "number"			: 	if (!this.checkNumber(field))
												return false;
											break;

				// ----------------------------------------------------------

				case "phone"			: 	if (!this.checkPhone(field))
												return false;
											break;

				// ----------------------------------------------------------

				case "amount"			: 	if (!this.checkAmount(field))
												return false;
											break;

				// ----------------------------------------------------------

				case "password"			:	if (!this.checkPassword(field, i, fields))
												return false;
											break;
	
				// ----------------------------------------------------------

				case "email"			: 	if (!this.checkEmail(field))
												return false;
											break;

				// ----------------------------------------------------------

				case "uploadFile"		:
				case "date"				:
				case "select"	   		:
				case "yesNoSelect"		:
				case "multiSelect" 		:
				case "multiSelectPlus" 	:	if (!this.checkMandatory(field))
												return false;
											break;

				// ----------------------------------------------------------

				default					:	break;
			}
		}
	}

	if (this.prevTextTdError != "")
	{
		this.prevTextTdError.className = "styleTdText";
		this.prevTextTdError = "";
	}

	this.loadTinymceField	();

	for (i = 1; i <= this.numTinymce; i++)
	{
		fieldObj  		= document.getElementById(this.objectName + "_" + this.tinymceFields[i] + this.formLanguage);

		if (fieldObj == undefined)
			fieldObj  		= document.getElementById(this.objectName + "_" + this.tinymceFields[i]);

   		if (fieldObj.value.indexOf(pleaseWaitText) != -1)
  		{
			if (fieldObj.value.indexOf(pleaseWaitText) != -1)
			{
				commonMsgBox ("info", "!טעינת הנתונים נכשלה. יש ללחוץ על כפתור 'חזרה' ולבצע שוב את הפעולה");
				return false;
			}
  		}
  	}

	return true;
  }
  catch (error)
  {
	if (field != undefined)
	 	throw new Error(20, "\n\tframe index\t: "  + (f*1+1) + 
							"\n\tfield index\t: "  + (i*1+1) + 
							"\n\tfield type\t: "   + field.type + 
							"\n\tfield name\t: "   + field.dataFld + 
							"\n\n" + error.description + 
							"\n\t(dynamicFormObj.validate)");
  }
}

/*--------------------------------------------------------------------------*/
/* dynamicFormObj_displayAndSetError										*/
/*--------------------------------------------------------------------------*/
function dynamicFormObj_displayAndSetError (fieldName, errorHEB, errorENG)
{
  try
  {
	theField 	= document.getElementById(this.objectName + "_" + fieldName);

	if (theField == undefined)
		theField = document.getElementById(this.objectName + "_" + fieldName + this.formLanguage);

	theTextTd	= document.getElementById(this.objectName + "_" + fieldName + "TextTd");

	// cancel the previous 
	if (this.prevTextTdError != "" && this.prevTextTdError != theTextTd)
		this.prevTextTdError.className = "styleTdText";

	// save the current
	this.prevTextTdError = theTextTd;

	// set as error
	this.prevTextTdError.className = "styleTdTextError";

	// if this is a tab form - select the tab first
	if (this.currTab != -1)
	{
		fieldTab = this.currTab;

		for (var f=0; f < this.frames.length; f++)
		{
			frame  = this.frames[f];
			
			for (var i = 0; i < frame.fields.length; i++)
			{
				if (frame.fields[i].dataFld == fieldName)
				{
					fieldTab = f;
					break;
				}
			}
		}

		if (fieldTab != this.currTab)
			this.selectTab (fieldTab);

	}	

	try {theField.focus ()} catch(e) {};
	
	try 
	{
		if (theField.type == "text")    // not select field
			theField.select();
	}
	catch (e) 
	{
	}

	// show error msg
	commonMsgBox ("info", errorHEB, errorENG);
  }
  catch (error)
  {
 	throw new Error(20, error.description + "\n\t(dynamicFormObj.displayAndSetError)");
  }
}

/*--------------------------------------------------------------------------*/
/* dynamicFormObj_checkMandatory											*/
/*--------------------------------------------------------------------------*/
function dynamicFormObj_checkMandatory (field)
{
  try
  {
  	this.checkField (field);

	var lang = "";
	if (field.lang != undefined)
	{
		lang = this.formLanguage;
	}

	fieldName 	= field.dataFld + lang;
	fieldType	= field.type;
	if (field.type2 != undefined)
	{
		// check if type2 is the display field
		if (document.getElementById(this.objectName + "_" + field.dataFld2).style.display == "")
		{
			fieldName = field.dataFld2 + lang;
			fieldType = field.type2;
		}
	}

	try
	{
		fieldText   = document.getElementById(this.objectName + "_" + field.dataFld + "Text_spn").innerHTML; 
	}
	catch (e)
	{
		fieldText 	= eval("field.text" + this.theLanguage);
	}
	mandatory 	= field.mandatory;
	fieldValue  = document.getElementById(this.objectName + "_" + fieldName).value;

	// ---------------------------------------------------------------------

	if (fieldValue == "" && mandatory)
	{
		if (fieldType == "select" 	   || fieldType == "yesNoSelect"		||
			fieldType == "multiSelect" || fieldType == "multiSelectPlus")
		{
			errorHEB = "יש לבחור ";
			errorENG = "Please select ";
		}
		else
		{
			errorHEB = "יש להזין ";
			errorENG = "Please enter ";
		}

		errorHEB += fieldText;
		errorENG += fieldText;
		this.displayAndSetError (field.dataFld, errorHEB, errorENG);
		return false;
	}
	return true;
  }
  catch (error)
  {
 	throw new Error(20, error.description + "\n\t(dynamicFormObj.checkMandatory)");
  }
}

/*--------------------------------------------------------------------------*/
/* dynamicFormObj_checkNumber												*/
/*--------------------------------------------------------------------------*/
function dynamicFormObj_checkNumber (field)
{
  try
  {
  	this.checkField (field);

	var lang = "";
	if (field.lang != undefined)
	{
		lang = this.formLanguage;
	}
	fieldName 	= field.dataFld + lang;
	fieldText 	= eval("field.text" + this.theLanguage);
	minLength 	= field.minLength;
	minValue	= field.minValue;
	maxValue	= field.maxValue;

	fieldValue	= document.getElementById(this.objectName + "_" + fieldName).value;

	// ---------------------------------------------------------------------

	if (!this.checkMandatory (field))
		return false;

	// check if not number
	if (fieldValue.indexOf(".") != -1 || 
		fieldValue.indexOf("-") != -1 ||
		fieldValue.indexOf("e") != -1 ||
		fieldValue.indexOf("E") != -1 ||
		isNaN(fieldValue))
	{
		errorHEB = "(השדה " + fieldText + " אינו תקין (יש להזין ספרות בלבד";
		errorENG = "Illegal " + fieldText + "(Only digits allowed)";

		this.displayAndSetError (fieldName, errorHEB, errorENG);
		return false;
	}

	// check min length
	if (fieldValue != "" && minLength != "" && fieldValue.length < minLength)
	{
		errorHEB = "יש להזין " + fieldText + " באורך " + minLength + " ספרות לפחות";
		errorENG = "Illegal " + fieldText + "(At least " + minLength + " Digits)";

		this.displayAndSetError (fieldName, errorHEB, errorENG);
		return false;
	}

	// check min value
	if (fieldValue != "" && minValue != "" && fieldValue < minValue*1)
	{
		minValue = minValue*1;
		errorHEB = "יש להזין " + fieldText + " גדול מ-" + minValue;
		errorENG = "Illegal " + fieldText + "(Minimum value is " + minValue + ")";

		this.displayAndSetError (fieldName, errorHEB, errorENG);
		return false;
	}
	
	// check max value
	if (maxValue != "" && fieldValue > maxValue*1)
	{
		maxValue = maxValue*1;
		errorHEB = "יש להזין " + fieldText + " לא עולה על-" + maxValue;
		errorENG = "Illegal " + fieldText + "(Maximum value is " + maxValue + ")";

		this.displayAndSetError (fieldName, errorHEB, errorENG);
		return false;
	}

	return true;
  }
  catch (error)
  {
 	throw new Error(20, error.description + "\n\t(dynamicFormObj.checkNumber)");
  }
}

/*--------------------------------------------------------------------------*/
/* dynamicFormObj_checkPhone												*/
/*--------------------------------------------------------------------------*/
function dynamicFormObj_checkPhone (field)
{
  try
  {
  	this.checkField (field);

	fieldName 	= field.dataFld;
	fieldText 	= eval("field.text" + this.theLanguage);
	minLength 	= field.minLength;
	minValue	= field.minValue;
	maxValue	= field.maxValue;
	fieldValue  = document.getElementById(this.objectName + "_" + fieldName).value;

	// ---------------------------------------------------------------------

	if (!this.checkMandatory (field))
		return false;

	if (fieldValue == "") return true;

	errorHEB = "השדה " + fieldText + " אינו תקין";
	errorENG = "Illegal " + fieldText;

	if (fieldValue.indexOf("/") != -1)
	{
		fieldSplit = fieldValue.split ("/");

		if (fieldSplit.length != 2 || isNaN(fieldSplit[1]))
		{
			this.displayAndSetError (fieldName, errorHEB, errorENG);
			return false;
		}

		fieldValue = fieldSplit[0];
	}

	if (fieldValue.indexOf("-") != -1)
	{
		fieldSplit = fieldValue.split ("-");

		if (fieldSplit[0] != "1" && fieldSplit.length != 2)
		{
			this.displayAndSetError (fieldName, errorHEB, errorENG);
			return false;
		}
	

		regexp1  = new RegExp("0[23489]-[0-9]{7}$");
		regexp2  = new RegExp("05[0-9]-[0-9]{7}$");
		regexp3  = new RegExp("07-[0-9]{8}$");
		regexp4  = new RegExp("077-[0-9]{7}$");
		regexp5  = new RegExp("01[2789]-[0-9]{7}$");
		regexp6  = new RegExp("1-700-[0-9]{6}$");
		regexp7  = new RegExp("1-800-[0-9]{6}$");
		regexp8  = new RegExp("1-700-[0-9]{2}-[0-9]{2}-[0-9]{2}$");
		regexp9  = new RegExp("1-800-[0-9]{2}-[0-9]{2}-[0-9]{2}$");
		regexp10 = new RegExp("1-700-[0-9]{3}-[0-9]{3}$");
		regexp11 = new RegExp("1-800-[0-9]{3}-[0-9]{3}$");

		if (!regexp1.test(fieldValue) &&
			!regexp2.test(fieldValue) &&
			!regexp3.test(fieldValue) &&
			!regexp4.test(fieldValue) &&
			!regexp5.test(fieldValue) &&
			!regexp6.test(fieldValue) &&
			!regexp7.test(fieldValue) &&
			!regexp8.test(fieldValue) &&
			!regexp9.test(fieldValue) &&
			!regexp10.test(fieldValue) &&
			!regexp11.test(fieldValue))
		{
			this.displayAndSetError (fieldName, errorHEB, errorENG);
			return false;
		}
	}
	else
	{
		regexp1 = new RegExp("0[234895][0-9]{7}$");
		regexp2 = new RegExp("0[57][0-9]{8}$");
		regexp3 = new RegExp("01[2789][0-9]{7}$");
		regexp4 = new RegExp("1[78]00[0-9]{6}$");

		if (!regexp1.test(fieldValue) &&
		    !regexp2.test(fieldValue) &&
		    !regexp3.test(fieldValue) &&
		    !regexp4.test(fieldValue))
		{
			this.displayAndSetError (fieldName, errorHEB, errorENG);
			return false;
		}
	}

	return true;
  }
  catch (error)
  {
 	throw new Error(20, error.description + "\n\t(dynamicFormObj.checkPhone)");
  }
}

/*--------------------------------------------------------------------------*/
/* dynamicFormObj_checkAmount												*/
/*--------------------------------------------------------------------------*/
function dynamicFormObj_checkAmount (field)
{
  try
  {
  	this.checkField (field);

	fieldName 	= field.dataFld;
	fieldText 	= eval("field.text" + this.theLanguage);
	minLength 	= field.minLength;
	minValue	= field.minValue;
	maxValue	= field.maxValue;
	fieldValue  = document.getElementById(this.objectName + "_" + fieldName).value;

	// ---------------------------------------------------------------------

	if (!this.checkMandatory (field))
		return false;

	// check if not number
	if (isNaN(fieldValue))
	{
		errorHEB = "השדה " + fieldText + " אינו תקין (יש להזין מספר)";
		errorENG = "Illegal " + fieldText + "(Only number)";

		this.displayAndSetError (fieldName, errorHEB, errorENG);
		return false;
	}

/*	// check negative amount
	if (fieldValue < 0)
	{
		errorHEB = "השדה " + fieldText + " אינו תקין (יש להזין סכום חיובי)";
		errorENG = "Illegal " + fieldText + "(Only positive value)";

		this.displayAndSetError (fieldName, errorHEB, errorENG);
		return false;
	}
*/
	var beforeDot = field.beforeDot;
	var afterDot  = field.afterDot;

	var maxLen = beforeDot;

	if (fieldValue.indexOf(".") != -1)
	{
		var amountLen = fieldValue.length;

		if (fieldValue.indexOf(".") < (amountLen-3))
		{
			errorHEB = "השדה " + fieldText + " אינו תקין (ניתן להזין עד " + afterDot + 
					   " ספרות אחרי הנקודה)";
			errorENG = "Illegal " + fieldText + "(Only " + afterDot + 
					   " digits are allowed after the dot)";

			this.displayAndSetError (fieldName, errorHEB, errorENG);
			return false;
		}
		else
			maxLen = beforeDot + 3;
	}

	if (fieldValue.length > maxLen)
	{
		errorHEB = "השדה " + fieldText + " אינו תקין (ניתן להזין עד " + beforeDot + 
				   " ספרות לפני הנקודה)";
		errorENG = "Illegal " + fieldText + "(Only " + beforeDot + 
				   " digits are allowed before the dot)";

		this.displayAndSetError (fieldName, errorHEB, errorENG);
		return false;
	}

	// check min value
	if (minValue != "" && fieldValue < minValue*1)
	{
		minValue = minValue*1;
		errorHEB = "יש להזין " + fieldText + " גדול מ-" + minValue;
		errorENG = "Illegal " + fieldText + "(Minimum value is " + minValue + ")";

		this.displayAndSetError (fieldName, errorHEB, errorENG);
		return false;
	}
	
	// check max value
	if (maxValue != "" && fieldValue > maxValue*1)
	{
		maxValue = maxValue*1;
		errorHEB = "יש להזין " + fieldText + " לא עולה על-" + maxValue;
		errorENG = "Illegal " + fieldText + "(Maximum value is " + maxValue + ")";

		this.displayAndSetError (fieldName, errorHEB, errorENG);
		return false;
	}

	return true;
  }
  catch (error)
  {
 	throw new Error(20, error.description + "\n\t(dynamicFormObj.checkAmount)");
  }
}

/*--------------------------------------------------------------------------*/
/* dynamicFormObj_checkEmail												*/
/*--------------------------------------------------------------------------*/
function dynamicFormObj_checkEmail (field)
{
  try
  {
  	this.checkField (field);

	fieldName 	= field.dataFld;
	fieldText 	= eval("field.text" + this.theLanguage);
	minLength 	= field.minLength;
	fieldValue  = document.getElementById(this.objectName + "_" + fieldName).value;

	// ---------------------------------------------------------------------

	if (!this.checkMandatory (field))
		return false;

	// check min length
	if (fieldValue != "" && minLength != "" && fieldValue.length < minLength)
	{
		errorHEB = "יש להזין " + fieldText + " באורך " + minLength + " ספרות לפחות";
		errorENG = "Illegal " + fieldText + "(At least " + minLength + " Digits)";

		this.displayAndSetError (fieldName, errorHEB, errorENG);
		return false;
	}

	// check valid emails format
	if (fieldValue != "")
	{
		regexp = new RegExp("[0-9A-Za-z\.\_\-].*@[0-9A-Za-z\.\_\-].*");

		if (!regexp.test (fieldValue))
		{
			errorHEB = fieldText + " לא חוקי";
			errorENG = "Invalid " + fieldText;

			this.displayAndSetError (fieldName, errorHEB, errorENG);
			return false;
		}
	}

	return true;
  }
  catch (error)
  {
 	throw new Error(20, error.description + "\n\t(dynamicFormObj.checkNumber)");
  }
}

/*--------------------------------------------------------------------------*/
/* dynamicFormObj_checkPassword												*/
/*--------------------------------------------------------------------------*/
function dynamicFormObj_checkPassword (field, index, fields)
{
  try
  {
  	this.checkField (field);

	fieldName 	= field.dataFld;
	fieldText 	= eval("field.text" + this.theLanguage);
	minLength 	= field.minLength;
	fieldValue  = document.getElementById(this.objectName + "_" + fieldName).value;

	// ---------------------------------------------------------------------

	if (!this.checkMandatory (field))
		return false;

	// check min length
	if (fieldValue != "" && minLength != "" && fieldValue.length < minLength)
	{
		errorHEB = "יש להזין " + fieldText + " באורך " + minLength + " ספרות לפחות";
		errorENG = "Illegal " + fieldText + "(At least " + minLength + " Digits)";

		this.displayAndSetError (fieldName, errorHEB, errorENG);
		return false;
	}

	// search for password confirm field
	confirmField = "";
	for (var f=0; f < this.frames.length; f++)
	{
		var frame  = this.frames[f];

		for (var i=index; i < frame.fields.length; i++)
		{
			if (frame.fields[i].type == "passwordConfirm")
			{
				confirmField = frame.fields[i];
			}
		}
	}

	if (confirmField != "")
	{
		confirmFieldName  = confirmField.dataFld;
		confirmFieldValue = document.getElementById(this.objectName + "_" + confirmFieldName).value;
		confirmFieldText  = eval("confirmField.text" + this.theLanguage);

		if (fieldValue != "" && confirmFieldValue == "")
		{
			errorHEB = "יש להזין " + confirmFieldText;
			errorENG = "Please, enter " + confirmFieldText;

			this.displayAndSetError (confirmFieldName, errorHEB, errorENG);
			return false;
		}

		if (confirmFieldValue != fieldValue)
		{
			errorHEB = fieldText + " ו"    + confirmFieldText + " אינם תואמים. אנא הזן שוב";
			errorENG = fieldText + " and " + confirmFieldText + "are not the same, Please enter again.";

			this.displayAndSetError (fieldName, errorHEB, errorENG);
			return false;
		}
	}
	return true;
  }
  catch (error)
  {
 	throw new Error(20, error.description + "\n\t(dynamicFormObj.checkPassword)");
  }
}

/*--------------------------------------------------------------------------*/
/* dynamicFormObj_checkText													*/
/*--------------------------------------------------------------------------*/
function dynamicFormObj_checkText (field)
{
  try
  {
  	this.checkField (field);

	var lang = "";
	if (field.lang != undefined)
	{
		lang = this.formLanguage;
	}
	fieldName 	= field.dataFld + lang;
	fieldText 	= eval("field.text" + this.theLanguage);
	minLength 	= field.minLength;
	fieldValue  = document.getElementById(this.objectName + "_" + fieldName).value;

	if (!this.checkMandatory (field))
		return false;

	// check min length
	if (fieldValue != "" && minLength != "" && fieldValue.length < minLength)
	{
		errorHEB = "יש להזין " + fieldText + " באורך " + minLength + " תווים לפחות";
		errorENG = "Illegal " + fieldText + "(At least " + minLength + " Digits)";

		this.displayAndSetError (fieldName, errorHEB, errorENG);
		return false;
	}

	return true;
  }
  catch (error)
  {
 	throw new Error(20, error.description + "\n\t(dynamicFormObj.checkText (" + fieldName + "))");
  }
}

/*--------------------------------------------------------------------------*/
/* dynamicFormObj_checkTime													*/
/*--------------------------------------------------------------------------*/
function dynamicFormObj_checkTime (field)
{
  try
  {
  	this.checkField (field);

	fieldName 	= field.dataFld;
	fieldText 	= eval("field.text" + this.theLanguage);
	minLength 	= 5;
	fieldValue  = document.getElementById(this.objectName + "_" + fieldName).value;

	if (!this.checkMandatory (field))
		return false;

	if (fieldValue != "" &&
		(fieldValue.length != 5 || fieldValue.indexOf(":") != 2))
	{
		errorHEB = "יש להזין " + fieldText + " בפורמט שעה:דקה";
		errorENG = "Illegal " + fieldText + "(HH:MM format)";

		this.displayAndSetError (fieldName, errorHEB, errorENG);
		return false;
	}

	hour = fieldValue.substr(0,2);
	min  = fieldValue.substr(3,2);
	
	if (isNaN(hour) || hour > 24 || hour < 0)
	{
		errorHEB = "שעה לא תקינה בשדה " + fieldText;
		errorENG = "Invalid hour in " + fieldText + " Field";

		  alert ("1" + fieldName);
		this.displayAndSetError (fieldName, errorHEB, errorENG);
		return false;
	}

	if (isNaN(min) || min > 59 || min < 0)
	{
		errorHEB = "ערך דקות לא תקין בשדה " + fieldText;
		errorENG = "Invalid minutes in " + fieldText + " Field";

		this.displayAndSetError (fieldName, errorHEB, errorENG);
		return false;
	}
	
	return true;
  }
  catch (error)
  {
 	throw new Error(20, error.description + "\n\t(dynamicFormObj.checkTime)");
  }
}

/*--------------------------------------------------------------------------*/
/* dynamicFormObj_checkMMYY													*/
/*--------------------------------------------------------------------------*/
function dynamicFormObj_checkMMYY (field)
{
  try
  {
  	this.checkField (field);

	fieldName 	= field.dataFld;
	fieldText 	= eval("field.text" + this.theLanguage);
	minLength 	= 5;
	fieldValue  = document.getElementById(this.objectName + "_" + fieldName).value;

	if (!this.checkMandatory (field))
		return false;
	
	if (fieldValue != "")
	{
		valid = true;

		if (fieldValue.length < 4 			|| 
			fieldValue.indexOf("-") != -1	||
		    fieldValue.indexOf(".") != -1)
		{
			valid = false;
		}

		if (fieldValue.length == 5 			&&
				(fieldValue.indexOf("/") != 2	||
				 isNaN(fieldValue.substr(0,2))	||
				 isNaN(fieldValue.substr(3,2))))
		{
			valid = false;
		}

		if (fieldValue.length == 4 && isNaN(fieldValue))
		{
			valid = false;
		}

		if (!valid)
		{
			errorHEB = "יש להזין " + fieldText + " בפורמט שנה/חודש";
			errorENG = "Illegal " + fieldText + "(yy/mm format)";

			this.displayAndSetError (fieldName, errorHEB, errorENG);
			return false;
		}

		month = fieldValue.substr(0,2);

		if (month < 1 || month > 12)
		{
			errorHEB = "חודש לא תקין בשדה " + fieldText;
			errorENG = "Invalid month in " + fieldText + " Field";

			this.displayAndSetError (fieldName, errorHEB, errorENG);
			return false;
		}
	}

	return true;
  }
  catch (error)
  {
 	throw new Error(20, error.description + "\n\t(dynamicFormObj.checkMMYY)");
  }
}


/*--------------------------------------------------------------------------*/
/* dynamicFormObj_setXml													*/
/*--------------------------------------------------------------------------*/
function dynamicFormObj_setXml (xml) 
{
	// check if the passed xml parameter is string or object (xml node)
	if (typeof(xml) == 'string')
	{
		// xml is string - create xml node
		var requestXml = new xmlObj(false);

		if (xml == "")
		{
			requestXml.init ("");
		}
		else
			requestXml.init ("<data>" + xml + "</data>");

		// save it
		if (window.DOMParser)
		{
	    	parser	= new DOMParser();
			this.xmlNode = parser.parseFromString(requestXml.obj.xml,"text/xml");
		}
		else
		{
			this.xmlNode = requestXml.obj.getElementsByTagName("data").item(0);
		}
	}
	else
	{
		this.xmlNode = xml;
	}
}

/*--------------------------------------------------------------------------*/
/* dynamicFormObj_getXml													*/
/*--------------------------------------------------------------------------*/
function dynamicFormObj_getXml () 
{
  try
  {

	// handle xstandard field
	this.loadXstandardField ();

	this.loadTinymceField	();
	
	// build xml from fields
	var xmlStr = "<data>";
	for (var f=0; f < this.frames.length; f++)
	{
		frame  = this.frames[f];

		for (var i = 0; i < frame.fields.length; i++)
		{
			if (frame.fields[i].dataFld != "")
			{
				if (frame.fields[i].lang != undefined && frame.fields[i].lang.length != 0)
				{
					for (l=0; l<frame.fields[i].lang.length; l++)
					{
						tagName	= frame.fields[i].dataFld + frame.fields[i].lang[l];
						value	= this.getFieldValue (tagName);

						if (frame.fields[i].type == "checkbox")
						{
							value = (value) ? "1" : "0";
						}

						xmlStr += "<" + tagName + "><![CDATA[" + value + "]]></" + tagName + ">";

						if (frame.fields[i].mobile != undefined && frame.fields[i].mobile)
						{
							tagName	= frame.fields[i].dataFld + frame.fields[i].lang[l] + "m";
							value	= this.getFieldValue (tagName);

							xmlStr += "<" + tagName + "><![CDATA[" + value + "]]></" + tagName + ">";
						}
					}
				}
				else
				{
					tagName	= frame.fields[i].dataFld;
					value	= this.getFieldValue (tagName);

					if (frame.fields[i].type == "checkbox")
					{
						value = (value) ? "1" : "0";
					}

					xmlStr += "<"  + tagName + "><![CDATA["	+ value	+ "]]></" + tagName + ">";

					if (frame.fields[i].dataFld2 != undefined && frame.fields[i].dataFld2 != "")
					{
						tagName	= frame.fields[i].dataFld2;
						value	= this.getFieldValue (tagName);

						xmlStr += "<"  + tagName + "><![CDATA["	+ value	+ "]]></" + tagName + ">";
					}
				}
			}
		}
	}
	xmlStr += "</data>";
	
	// Bypass : add multi selection tags
	for (i=0; i < this.multipleFields.length; i++)
	{
		fieldName = this.multipleFields[i];

		fieldObj  = document.getElementById(fieldName);
		
		if (fieldObj != undefined && fieldObj.multiple)
		{
			fieldValue = "";

			for (j=0; j < fieldObj.childNodes.length; j++)
            {
				if (fieldObj.childNodes[j].selected)
				{
					fieldValue += fieldObj.childNodes[j].value + " ";
				}
			}
	
			regexp = new RegExp ("<" + fieldName + ">.*</" + fieldName + ">");
			xmlStr = xmlStr.replace(regexp,"<" + fieldName + ">" + fieldValue + "</" + fieldName + ">");
		}
	}

	// add paging table xml (if exist in form)
	if (this.tableFrameIndex != -1 && this.tableFieldIndex != -1)
	{
		tableFieldObject = eval(this.tableFieldSrc);
	
		xmlStr += tableFieldObject.xml;
	}

	// add table form xml
	if (this.tableFormId != -1)
	{
		xmlStr += this.pageObj.getTableXml (this.tableFormId);
	}

	xmlStr += "";

	return xmlStr;
  }
  catch (error)
  {
 	throw new Error(20, error.description + "\n\t(dynamicFormObj.getXml)");
  }
}

/*--------------------------------------------------------------------------*/
/* dynamicFormObj_loadXstandardField										*/
/*--------------------------------------------------------------------------*/
function dynamicFormObj_loadXstandardField ()
{
  try
  {
	if (this.xstandardField != "")
	{
			fieldObj  = document.getElementById(this.objectName + "_"  + this.xstandardField + this.formLanguage);

			if (fieldObj == undefined)
				fieldObj = document.getElementById(this.objectName + "_"  + this.xstandardField);
	
			editorValue =  document.getElementById(this.objectName + "_editor0").value;
		
			editorValue = editorValue.replace (/\<\!\[CDATA\[/g,"");
			editorValue = editorValue.replace (/\]\]\>/g,"");

			fieldObj.value = editorValue;

		  	// Hebrew Nikud characters as Unicode
/*			fieldObj.value = ""
			var chstr = "";
			for(var i=0; i < editorValue.length; i++) 
			{
				ch = editorValue.charCodeAt(i);
				chstr = chstr + " " + ch;
			   	 if ((ch > 1455 && ch < 1476) || (ch > 1519 && ch < 1525) || (ch > 8000))
				    //ch == 8364 || ch == 8482 || ch == 8230 || ch == 8205)
					fieldObj.value += "&#" + ch + ";";
				else
					fieldObj.value += editorValue.charAt(i);
			}
*/
	}

	if (this.numTinymce != 0)
		tinyMCE.activeEditor.save();
		//tinyMCE.triggerSave(); [17/6/10 Amir] for some reason sometimes we got "permission denied"
  }
  catch (error)
  {
 	throw new Error(20, error.description + "\n\t(dynamicFormObj.loadXstandardField)");
  }
}

/*--------------------------------------------------------------------------*/
/* dynamicFormObj_loadTinymceField											*/
/*--------------------------------------------------------------------------*/
function dynamicFormObj_loadTinymceField ()
{
  try
  {
	for (var i = 1; i <= this.numTinymce; i++)
	{
		fieldObj  		= document.getElementById(this.objectName + "_" + this.tinymceFields[i] + this.formLanguage);

		if (fieldObj == undefined)
			fieldObj  		= document.getElementById(this.objectName + "_" + this.tinymceFields[i]);

		editorValue		= tinymce.EditorManager.get("formTinymce" + this.objectName + "_" + this.tinymceFields[i]).getContent();

		editorValue 	= editorValue.replace (/\<\!\[CDATA\[/g,"");
		editorValue 	= editorValue.replace (/\]\]\>/g,"");

		fieldObj.value 	= editorValue;

	}
//	tinyMCE.triggerSave();
  }
  catch (error)
  {
 	throw new Error(20, error.description + "\n\t(dynamicFormObj.loadTinymceField)");
  }

}

/*--------------------------------------------------------------------------*/
/* dynamicFormObj_getFieldValue												*/
/*																			*/
/* !!! get field value without basing on xml								*/
/*--------------------------------------------------------------------------*/
function dynamicFormObj_getFieldValue (dataFld) 
{
  try
  {
	field  = document.getElementById(this.objectName + "_"  + dataFld);

	if (field == undefined)
	{
		return "#no such field#";
	}

	if (field.tagName == "SPAN")
	{
		return field.innerHTML;
	}
	else
	{
		if (field.multiple)
		{
			fieldValue = "";

			for (j=0; j < field.childNodes.length; j++)
   	        {
				if (field.childNodes[j].selected)
				{
					fieldValue += field.childNodes[j].value + " ";
				}
			}
				
			return fieldValue;
		}
		else
		{
			if (field.type == "checkbox")
				return field.checked;

			return field.value;
		}
	}
  }
  catch (error)
  {
 	throw new Error(20, error.description + "\n\t(dynamicFormObj.getFieldValue)");
  }
}

/*--------------------------------------------------------------------------*/
/* dynamicFormObj_setFieldValue												*/
/*--------------------------------------------------------------------------*/
function dynamicFormObj_setFieldValue (dataFld, value) 
{
  try
  {
	// support set value for xstandardField
	if (dataFld == this.xstandardField)
	{
		document.getElementById(this.objectName + "_editor0").Value = value;
		return;
	}

	for (var i = 1; i <= this.numTinymce; i++)
	{
		if (dataFld == this.tinymceFields[i])
		{
			fieldObj  		= document.getElementById(this.objectName + "_" + this.tinymceFields[i] + this.formLanguage);

			if (fieldObj == undefined)
				fieldObj  		= document.getElementById(this.objectName + "_" + this.tinymceFields[i]);

			fieldObj.value = value;
	
			return;
		}
	}

	field = document.getElementById(this.objectName + "_" + dataFld);

	if (field != undefined)
	{
		var fieldType = field.nodeName.toLowerCase();

		if (fieldType == "span")
		{
			field.innerHTML = value;
		}
		else
		{
			if (fieldType == "checkbox")
			{
				if (value == "-1")
				{
					field.checked = true;
				}
			}
			else
				field.value = value;
		}
	}
  }
  catch (error)
  {
 	throw new Error(20, error.description + "\n\t(dynamicFormObj.setFieldValue)");
  }
}

/*--------------------------------------------------------------------------*/
/* dynamicFormObj_getOptionText                                             */
/*--------------------------------------------------------------------------*/
function dynamicFormObj_getOptionText (dataFld)
{
  try
  {
    field  = document.getElementById(this.objectName + "_" + dataFld);

    if (field == undefined)
        throw new Error (20, "field '" + dataFld + "' does not found");

    if (field.options == undefined)
        throw new Error (20, "field '" + dataFld + "' is not a selection field");

    if (field.multiple)
        throw new Error (20, "getOptionText method support only regular selection field (no multiple)");

    return (field.options[field.selectedIndex].innerHTML);
  }
  catch (error)
  {
    throw new Error(20, error.description + "\n\t(dynamicFormObj.getOptionText)");
  }
}

/*--------------------------------------------------------------------------*/
/* dynamicFormObj_getOptionValueText                                        */
/*--------------------------------------------------------------------------*/
function dynamicFormObj_getOptionValueText (dataFld, value)
{
  try
  {
    field  = document.getElementById(this.objectName + "_" + dataFld);

    if (field == undefined)
        throw new Error (20, "field '" + dataFld + "' does not found");

    if (field.options == undefined)
        throw new Error (20, "field '" + dataFld + "' is not a selection field");

    if (field.multiple)
        throw new Error (20, "getOptionText method support only regular selection field (no multiple)");

    var optionIndex = -1;
    // find passed value
    for (var i=0; i < field.options.length; i++)
    {
        if (field.options[i].value == value)
        {
            optionIndex = i;
            break;
        }
    }

    if (optionIndex == -1)
        return "";
    else
        return (field.options[optionIndex].innerText);
  }
  catch (error)
  {
    throw new Error(20, error.description + "\n\t(dynamicFormObj.getOptionValueText)");
  }
}


/*--------------------------------------------------------------------------*/
/* dynamicFormObj_setFieldOptions											*/
/*--------------------------------------------------------------------------*/
function dynamicFormObj_setFieldOptions (dataFld, options, defaultValue) 
{
  try
  {
    var field  = document.getElementById(this.objectName + "_" + dataFld);

	if (field.options != undefined)
	{
		$("#" + this.objectName + "_" + dataFld).find('option').remove();

/*		var numOptions = field.options.length;

		for (var i = numOptions-1; i >= 0; i--)
		{
			field.options.remove(i);
		}
*/
		var newOption;
		var selectedIndex = 0;
		for (var i = 0; i < options.length; i++)
		{
			theOption = options[i];

			newOption = "<option value='" + theOption.value + "' style='" + theOption.style + "'>" + 
							eval("theOption.text" + this.theLanguage) + "</option>";

			$("#" + this.objectName + "_" + dataFld).append (newOption);
/*			newOption = document.createElement("OPTION");
			field.options.add (newOption);

			newOption.innerHTML  = eval("theOption.text" + this.theLanguage);
			newOption.value = theOption.value;
			newOption.style = theOption.style;
*/
			if (defaultValue == theOption.value)
				selectedIndex  = i;
		}

		field.selectedIndex = selectedIndex;
	}
  }
  catch (error)
  {
 	throw new Error(20, error.description + "\n\t(dynamicFormObj.setFieldOptions)");
  }
}

/*--------------------------------------------------------------------------*/
/* dynamicFormObj_setFieldState												*/
/*--------------------------------------------------------------------------*/
function dynamicFormObj_setFieldState (dataFld, state) 
{
  try
  {
    field  = document.getElementById(this.objectName + "_" + dataFld);
	text   = document.getElementById(this.objectName + "_" + dataFld + "TextTd"); 

	if (field != undefined && text != undefined)
	{
		if (state == "lock")
		{
			field.disabled = true;
			text.className = this.lockClassName;
		}
		else if (state == "unlock")
		{
			field.disabled = false;
			text.className = this.regularClassName;
		}

		if (field.className.indexOf("datepicker") != -1)
		{
			if (state == "lock")
			{
				$("#" + this.objectName + "_" + dataFld).datepicker("option", "minDate", -1);
				$("#" + this.objectName + "_" + dataFld).datepicker("option", "maxDate", -2); 
			}
			else
			{
				$("#" + this.objectName + "_" + dataFld).datepicker("option", "minDate", null);
				$("#" + this.objectName + "_" + dataFld).datepicker("option", "maxDate", null); 
			}
		}

		// check if we have image for this field
		disableImgField = document.getElementById(dataFld + "ImgDisable");
		enableImgField  = document.getElementById(dataFld + "ImgEnable");

		if (disableImgField != undefined && enableImgField != undefined)
		{
			if (state == "lock")
			{
				disableImgField.style.display = "";
				enableImgField.style.display  = "none";
			}
			else if (state == "unlock")
			{
				disableImgField.style.display = "none";
				enableImgField.style.display  = "";
			}
		}
	}
  }
  catch (error)
  {
 	throw new Error(20, error.description + "\n\t(dynamicFormObj.setFieldValue)");
  }
}

/*--------------------------------------------------------------------------*/
/* dynamicFormObj_changeFieldTitle											*/
/*--------------------------------------------------------------------------*/
function dynamicFormObj_changeFieldTitle (dataFld, title) 
{
  try
  {
	oSpan  = document.getElementById(this.objectName + "_" + dataFld + "Text_spn"); 

	if (oSpan != undefined)
	{
		oSpan.innerHTML = title;
	}
  }
  catch (error)
  {
 	throw new Error(20, error.description + "\n\t(dynamicFormObj.changeFieldTitle)");
  }
}
/*--------------------------------------------------------------------------*/
/* dynamicFormObj_setFieldFocus												*/
/*--------------------------------------------------------------------------*/
function dynamicFormObj_setFieldFocus (dataFld) 
{
  try
  {
	var field = document.getElementById(this.objectName + "_" + dataFld);

	try {field.focus ()} catch(e) {};
  }
  catch (error)
  {
 	throw new Error(20, error.description + "\n\t(dynamicFormObj.setFieldFocus)");
  }
}

/*--------------------------------------------------------------------------*/
/* dynamicFormObj_changeMandatory											*/
/*--------------------------------------------------------------------------*/
function dynamicFormObj_changeMandatory (dataFld, mandatory) 
{
  try
  {
  	// search the field 
	for (var f=0; f < this.frames.length; f++)
	{
		frame  = this.frames[f];

		for (var i = 0; i < frame.fields.length; i++)
		{
			if (frame.fields[i].dataFld == dataFld || frame.fields[i].dataFld2 == dataFld)
			{
				frame.fields[i].mandatory = mandatory;

				theTextTd	= document.getElementById(this.objectName + "_" + dataFld + "Text_in");

				try
				{
					fieldText   = document.getElementById(this.objectName + "_" + dataFld + "Text_spn").innerHTML; 
				}
				catch (e)
				{
					fieldText   = eval("frame.fields[i].text" + this.theLanguage);
				}


				if (theTextTd != null)
				{
					if (mandatory)
					{
						theTextTd.innerHTML = "<span id='" + this.objectName + "_" + dataFld + "Text_spn'>" + fieldText + "</span> *";
					}
					else
					{
						theTextTd.innerHTML = "<span id='" + this.objectName + "_" + dataFld + "Text_spn'>" + fieldText + "</span>";
					}
				}
				break;
			}
		}
	}
  }
  catch (error)
  {
 	throw new Error(20, error.description + "\n\t(dynamicFormObj.changeMandatory)");
  }
}

/*--------------------------------------------------------------------------*/
/* dynamicFormObj_setFieldDisplay											*/
/*--------------------------------------------------------------------------*/
function dynamicFormObj_setFieldDisplay (dataFld, display) 
{
  try
  {
	field = document.getElementById(this.objectName + "_" + dataFld);

	if (field != undefined)
	{
		field.style.display = display
	}
  }
  catch (error)
  {
 	throw new Error(20, error.description + "\n\t(dynamicFormObj.setFieldDisplay)");
  }
}

/*--------------------------------------------------------------------------*/
/* dynamicFormObj_getFormWidth												*/
/*--------------------------------------------------------------------------*/
function dynamicFormObj_getFormWidth ()
{
  	if (this.tableFormId == -1)
		return this.formWidth;
	else
		return this.pageObj.getTableWidth (this.tableFormId);	
}

/*--------------------------------------------------------------------------*/
/* dynamicFormObj_emptyFormFields											*/
/*--------------------------------------------------------------------------*/
function dynamicFormObj_emptyFormFields ()
{
  try
  {
	// Notice : 
	//		window + objectName - is the javascript object
	//  	document.body.all + objectName - is the html from

	var oForm = document.getElementById(this.objectName);

  	var elements = oForm.elements; 

  
	oForm.reset();

	for (i=0; i < elements.length; i++) 
	{
		switch (elements[i].type.toLowerCase()) 
		{
  
			case "text"			: 
			case "password"		: 
			case "textarea"		:
			case "hidden"		: 
   				elements[i].value = ""; 
   				break;

  			case "radio"		:
  			case "checkbox"		:
     			if (elements[i].checked) 
				{
					elements[i].checked = false; 
   				}
   				break;

  			case "select-one"	:
  			case "select-multi"	:
              	elements[i].selectedIndex = -1;
   				break;

  			default				: 
   				break;
 			}
    }
	
	// Clean table form fields (if exists)
	if (this.tableFormId != -1)
	{
		this.pageObj.resetTable (this.tableFormId);	
	}
  }
  catch (error)
  {
 	throw new Error(20, error.description + "\n\t(dynamicFormObj.emptyFormFields)");
  }
}

/*--------------------------------------------------------------------------*/
/* startTinyMCE																*/
/*--------------------------------------------------------------------------*/
function startTinyMCE(lang)
{
	if (lang == 'HEB' || lang == 'HB2' || lang == 'ARB')
			var dir = "rtl";
	else
			var dir = "ltr";

	var stylesArr = Array();
	var xml = commonGetGlobalData("stylesXml");
	if (xml != undefined)
	{
		itemsNode = xml.getElementsByTagName("items").item(0);
		for (i=0; i < itemsNode.childNodes.length; i++)
		{
			currNode    = itemsNode.childNodes[i];

			if (window.DOMParser)
				styleName  	= currNode.getElementsByTagName("styleName").item(0).textContent;
			else
				styleName  	= currNode.getElementsByTagName("styleName").item(0).text;
			if (styleName.indexOf("\.") != 0) continue;

			if (window.DOMParser)
				styleDesc 	= currNode.getElementsByTagName("styleDescription").item(0).textContent;
			else
				styleDesc 	= currNode.getElementsByTagName("styleDescription").item(0).text;

			var styleObj = Object();
			var a = [["fontFamily", "font-family", ""], ["fontSize", "font-size", "px"], ["color", "color", ""],
					 ["backgroundColor", "background-color", ""], ["fontStyle", "font-weight", ""], ["fontDisplay", "", ""],
			   		 ["underline", "text-decoration", ""], ["cursor", "cursor", ""], ["more", "more", ""]];
			for (var index = 0; index < a.length; ++index)
			{
				if (window.DOMParser)
					styleValue 	= currNode.getElementsByTagName(a[index][0]).item(0).textContent;
				else
					styleValue 	= currNode.getElementsByTagName(a[index][0]).item(0).text;
				if (styleValue != 0 && styleValue != '')
						if (a[index][1] != "more")
							styleObj[a[index][1]] = styleValue + a[index][2];
						else
						{
							var moreStyles = styleValue.split(";");
							for (var j = 0; j < moreStyles.length; ++j)
							{
									var inStyle = moreStyles[j].split(":");
									if (inStyle[1] != undefined)
										styleObj[inStyle[0]] = inStyle[1];
							}
						}
			}

			stylesArr.push({title: styleDesc, inline: 'span', classes: styleName.replace(/^\./, ""), styles: styleObj});
		}
	}

	var baseUrl = commonGetGlobalData("siteUrl");

	// remove previous instances
	for (var i = tinymce.editors.length - 1 ; i > -1 ; i--) 
	{
		var ed_id = tinymce.editors[i].id;
		try 
		{
            tinyMCE.execCommand("mceRemoveEditor", true, ed_id);
        }
		catch (e) {}
	}

	tinymce.init({
    	selector					:	"textarea.mce_editor",
		plugins						:	"directionality,table,image,link,anchor,media,charmap,hr,textcolor,code,contextmenu,responsivefilemanager,lists,advlist,autolink,paste,",
		//								"tinymceEmoji",
		language					:	"he_IL",
		menubar						:	false,
		statusbar					:	false,
		relative_urls				:	false, // needs to be 'false' to help Newsletters links to work properly
		allow_script_urls			:	true,
		remove_script_host			:	false,
		document_base_url 			:	baseUrl,
		content_css					:	"https://www.i-bos.co.il/3.0/css/tinymce" + dir.toUpperCase() + ".css",
		toolbar1					:	"removeformat bullist numlist styleselect | anchor link | media image table | backcolor forecolor | cut copy paste ",
		toolbar2					:	"underline strikethrough italic bold hr charmap | rtl ltr outdent indent alignleft aligncenter alignright alignjustify | " +
										"undo redo | code",
		image_advtab				:	true,
		external_filemanager_path	:	"../../filemanager/",
		filemanager_title			:	"Responsive Filemanager" ,
		image_title					:   true,
		external_plugins			:	{ "filemanager" : "../../filemanager/plugin.min.js"},
// Solve a bug for multiple tinymce
// https://stackoverflow.com/questions/48006587/integrating-responsive-file-manager-icon-showing-only-in-the-last-tinymce-edit
file_picker_types:'file image media',
file_picker_callback:function(cb,value,meta){var width=window.innerWidth-30;var height=window.innerHeight-60;if(width>1800)width=1800;if(height>1200)height=1200;if(width>600){var width_reduce=(width-20)%138;width=width-width_reduce+10;}var urltype=2;if(meta.filetype=='image'){urltype=1;}if(meta.filetype=='media'){urltype=3;}var title="RESPONSIVE FileManager";if(typeof this.settings.filemanager_title!=="undefined"&&this.settings.filemanager_title){title=this.settings.filemanager_title;}var akey="key";if(typeof this.settings.filemanager_access_key!=="undefined"&&this.settings.filemanager_access_key){akey=this.settings.filemanager_access_key;}var sort_by="";if(typeof this.settings.filemanager_sort_by!=="undefined"&&this.settings.filemanager_sort_by){sort_by="&sort_by="+this.settings.filemanager_sort_by;}var descending="false";if(typeof this.settings.filemanager_descending!=="undefined"&&this.settings.filemanager_descending){descending=this.settings.filemanager_descending;}var fldr="";if(typeof this.settings.filemanager_subfolder!=="undefined"&&this.settings.filemanager_subfolder){fldr="&fldr="+this.settings.filemanager_subfolder;}var crossdomain="";if(typeof this.settings.filemanager_crossdomain!=="undefined"&&this.settings.filemanager_crossdomain){crossdomain="&crossdomain=1";if(window.addEventListener){window.addEventListener('message',filemanager_onMessage,false);}else{window.attachEvent('onmessage',filemanager_onMessage);}}tinymce.activeEditor.windowManager.open({title:title,file:this.settings.external_filemanager_path+'dialog.php?type='+urltype+'&descending='+descending+sort_by+fldr+crossdomain+'&lang='+this.settings.language+'&akey='+akey+'&'+top.location.search.substring(1),width:width,height:height,resizable:true,maximizable:true,inline:1},{setUrl:function(url){value=/[^/]*$/.exec(url)[0];$.ajax({url:"../../filemanager/upload2website.php?targetFile="+encodeURI(url)+"&"+top.location.search.substring(1)});url='loadedFiles/'+value;cb(url);}});},
		extended_valid_elements 	:	"i[class],img[class|src|alt|title|style|width|height|align|onmouseover|onmouseout|onclick|name|usemap],button[class|type|value|onclick],script[language|type|src],a[id|class|name|style|href|target|title|onclick]",
		custom_elements				:	"ins",
		style_formats				:	[{
											title: "Headers",
											items: [
												{title: "Header 1",format: "h1"},
												{title: "Header 2",format: "h2"},
												{title: "Header 3",format: "h3"},
												{title: "Header 4",format: "h4"},
												{title: "Header 5",format: "h5"},
												{title: "Header 6",format: "h6"}
				                            ]
										},{
											title: "Font Family",
											items: [
												{title: 'Arial', inline: 'span', styles: { 'font-family':'arial'}},
												{title: 'Times New Roman', inline: 'span', styles: { 'font-family':'times new roman,times'}},
												{title: 'Verdana', inline: 'span', styles: { 'font-family':'Verdana'}},
												{title: 'Courier New', inline: 'span', styles: { 'font-family':'courier new,courier'}},
												{title: 'Georgia', inline: 'span', styles: { 'font-family':'georgia,palatino'}},
												{title: 'Helvetica', inline: 'span', styles: { 'font-family':'helvetica'}},
												{title: 'Open Sans', inline: 'span', styles: { 'font-family':'Open Sans'}},
												{title: 'Tahoma', inline: 'span', styles: { 'font-family':'tahoma'}},
												{title: 'David', inline: 'span', styles: { 'font-family':'david'}},
												{title: 'Miryam', inline: 'span', styles: { 'font-family':'miryam'}}
											]
										},{
											title: "Font Sizes",
										   	items: [
												{title: '8px', inline:'span', styles: { fontSize: '8px', 'font-size': '8px' }},
												{title: '10px', inline:'span', styles: { fontSize: '10px', 'font-size': '10px' }},
												{title: '12px', inline:'span', styles: { fontSize: '12px', 'font-size': '12px' }},
												{title: '14px', inline:'span', styles: { fontSize: '14px', 'font-size': '14px' }},
												{title: '16px', inline:'span', styles: { fontSize: '16px', 'font-size': '16px' }},
												{title: '18px', inline:'span', styles: { fontSize: '18px', 'font-size': '18px' }},
												{title: '20px', inline:'span', styles: { fontSize: '20px', 'font-size': '20px' }},
												{title: '24px', inline:'span', styles: { fontSize: '24px', 'font-size': '24px' }},
												{title: '30px', inline:'span', styles: { fontSize: '30px', 'font-size': '30px' }},
											]
										},{
											title: "Style",
										   	items: stylesArr
										},{
											title: "Float Image",
											items: [
											    { title: 'Left', selector: 'img', styles: { 'float': 'left', 'margin': '0 10px 10px 0' } },
     											{ title: 'Right', selector: 'img', styles: { 'float': 'right', 'margin': '0 0 10px 10px' } },
												{ title: 'Cancel', selector: 'img', styles: { 'float': 'none', 'margin': '0' } }
											]
										}],
		branding					:	false,
		setup						:	function(ed) {
								        ed.on('init', function(args) {
											tinymce.get(args.target.id).setContent(globalTinymceText[args.target.id]);
											ed.getBody().dir = dir;
								        });
								    }
		});
}

/*--------------------------------------------------------------------------*/
/* startTinyMCE_old															*/
/*--------------------------------------------------------------------------*/
function startTinyMCE_old(lang)
{
	var title;
	if (commonGetGlobalData("guiLang") == "ENG")
		title = 'Choose style';
	else
		title = 'בחר סגנון';

	// Creates a new plugin class and a custom listbox
	tinymce.create('tinymce.plugins.CommonStylesPlugin', {
		createControl: function(n, cm) {
        	switch (n) {
				case 'styleslistbox':
					var mlb = cm.createListBox('styleslistbox', {
                    	 title : title,
	                     onselect : function(v) {
							if (mlb.selectedValue === v) {
								//orig tinyMCE.execCommand('mceSetStyleInfo', 0, {command : 'removeformat'});
								var ed = tinyMCE.activeEditor;
								var selection = ed.selection;
								var selectedNode = selection.getNode();
								ed.dom.removeClass(selectedNode, v);
								mlb.select();
								return false;
							}
						   	else
							{
								var ed = tinyMCE.activeEditor;
								var selection = ed.selection;
								var selectedNode = selection.getNode();
								ed.dom.setAttrib(selectedNode, 'class', v);
							}
    	             	}
        	        });

					var xml = commonGetGlobalData("stylesXml");
					if (xml != undefined)
					{
						itemsNode = xml.getElementsByTagName("items").item(0);

						for (i=0; i < itemsNode.childNodes.length; i++)
						{
							currNode    = itemsNode.childNodes[i];

							if (window.DOMParser)
								styleName  	= currNode.getElementsByTagName("styleName").item(0).textContent;
							else
								styleName  	= currNode.getElementsByTagName("styleName").item(0).text;

							if (styleName.indexOf("\.") != 0) continue;

							if (window.DOMParser)
								styleDesc 	= currNode.getElementsByTagName("styleDescription").item(0).textContent;
							else
								styleDesc 	= currNode.getElementsByTagName("styleDescription").item(0).text;

    	            		mlb.add(styleDesc/* + "&nbsp;&nbsp;&nbsp;&nbsp;"*/, styleName.replace(/^\./, ""));
						}
					}

					// Return the new listbox instance
	                return mlb;
    	    }

        	return null;
	    }
	});
	// Register plugin with a short name
	tinymce.PluginManager.add('commonStyles', tinymce.plugins.CommonStylesPlugin);

	if (lang == 'HEB' || lang == 'HB2' || lang == 'ARB')
	{
			var tinymceCssFile = 'https://www.i-bos.co.il/3.0/css/tinymceRTL.css';
			var tinymceDirection = 'rtl';
			var tinymceLang = "he";
	}
	else
	{
			var tinymceCssFile = 'https://www.i-bos.co.il/3.0/css/tinymceLTR.css';
			var tinymceDirection = 'ltr';
			var tinymceLang = "en";
	}

	commonSetGlobalData ("tinymceLang", lang);

	var tinymceFields = "";
	for (var i = 1; i <= globalTinymceField.length; i++)
	{
		if (tinymceFields != "")
			tinymceFields += ",";

	 	tinymceFields += "formTinymce" + globalTinymceField[i];
	}
	
		var baseUrl = commonGetGlobalData("siteUrl");

		var content_css_param = tinymceCssFile + "," + baseUrl + "/common.css";

		tinyMCE.init({
		mode : "exact",
		elements : tinymceFields,
		content_css : content_css_param,
		document_base_url : baseUrl,
		relative_urls : false, // needs to be 'false' to help Newsletters links to work properly
		remove_script_host: false,
		theme :	"advanced",
		verify_html : false,
		language : tinymceLang,
		directionality: tinymceDirection,
		oninit: loadTinymce,							
		file_browser_callback : "imgLibManager.open",
		plugins : "-commonStyles,table,media,searchreplace,fullscreen,directionality,paste,contextmenu,advlink,inlinepopups,style,layer,advlist",
	   	// - means TinyMCE will not try to load it
		fullscreen_new_window : true,
		fullscreen_settings : {
			theme_advanced_path_location : "top"
		},
		media_use_script: true,
		setup : function(ed) {
		      ed.onNodeChange.add(function(ed, cm, e) {
		      // Activates the link button when the caret is placed in a anchor element
		      if (e.className != undefined && e.className.indexOf('styleCustom') != -1)
		             cm.get('styleslistbox').select(e.className);
		      else
		             cm.get('styleslistbox').select('');
	   	      });
		},
	        paste_preprocess : function(pl, o) {
	            // Content string containing the HTML from the clipboard
	            // alert(o.content);
	            // o.content = "&nbsp;" + o.content;
	        },
	        paste_postprocess : function(pl, o) {
	            // Content DOM node containing the DOM structure of the clipboard
	            // alert(o.node.innerHTML);
	            o.node.innerHTML = o.node.innerHTML + "&nbsp;";
	        },
		paste_convert_middot_lists : false,
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "right",
		theme_advanced_default_background_color : "#C0CBD1",
		theme_advanced_default_foreground_color : "#C0CBD1",
		theme_advanced_source_editor_width: '850',
		theme_advanced_source_editor_height: '500',
   		theme_advanced_buttons1 : 'insertlayer,moveforward,movebackward,absolute,removeformat,styleprops,|,charmap,media,anchor,unlink,link,image,table,bullist,numlist,|,formatselect,styleslistbox',
   		theme_advanced_buttons2 : 'fullscreen,restoredraft,code,undo,redo,|,outdent,indent,ltr,rtl,justifyfull,justifyleft,justifycenter,justifyright,|,pasteword,paste,copy,cut,|,backcolor,forecolor,sup,sub,underline,strikethrough,italic,bold,hr',
		theme_advanced_buttons3 : ''
		});

		imgLibManager.init({url: '../../javascript2/imglib/index.html'});
}

function ajaxfilemanager(field_name, url, type, win)
{
	var ajaxfilemanagerurl = "../../javascript2/tiny_mce/plugins/ajaxfilemanager/ajaxfilemanager.php";
	switch (type) {
		case "image":
			break;
		case "media":
			break;
		case "flash": 
			break;
		case "file":
			break;
		default:
			return false;
	}
    tinyMCE.activeEditor.windowManager.open({
              url: "../../javascript2/tiny_mce/plugins/ajaxfilemanager/ajaxfilemanager.php",
              width: 575,
              height: 440,
              inline : "yes",
              close_previous : "no"
          },{
              window : win,
              input : field_name
    });
}

/*--------------------------------------------------------------------------*/
/* updateFieldCounter														*/
/*--------------------------------------------------------------------------*/
function updateFieldCounter (oField, fieldName)
{
	document.getElementById("fieldCounter_" + fieldName).innerHTML = oField.value.length + " תווים";

	return true;
}

