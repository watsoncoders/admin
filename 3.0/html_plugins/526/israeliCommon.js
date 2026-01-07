var global_whereAmI;

var globalCourses;

var	m_idCol				= {textHEB	: "קוד",				textENG	: "",	xmlTag	: "id"				}
var	m_courseCol			= {textHEB	: "קורס",			   	textENG	: "",   xmlTag	: "extraData3"		}
var	m_statusCol			= {textHEB	: "סטטוס",			   	textENG	: "",   xmlTag	: "status"			}
var	m_professionCol		= {textHEB	: "מקצוע",			 	textENG	: "",   xmlTag	: "fldProfession"	}
var	m_nameCol			= {textHEB	: "שם",			 		textENG	: "",   xmlTag	: "name"			}
var	m_firstnameCol		= {textHEB	: "שם פרטי",	 		textENG	: "",   xmlTag	: "firstname"		}
var	m_lastnameCol		= {textHEB	: "שם משפחה",	 		textENG	: "",   xmlTag	: "lastname"		}
var	m_usernameCol		= {textHEB	: "שם משתמש",	   		textENG	: "",   xmlTag	: "username"		}
var m_langCol			= {textHEB	: "שפה",		   		textENG	: "",   xmlTag	: "memberLanguage"	}

var m_addTitleId		= -1;
var m_updateTitleId		= -1;
var m_formId			= -1;
var m_formButtonsId		= -1;

var global_which;
var global_whichText;

function members_createTitles (which)
{
	m_addTitleId		= pageObj.addPageSubTitle ("", "");
	m_updateTitleId		= pageObj.addPageSubTitle ("", "");

	members_updateTitles (which);
}

function members_updateTitles (which)
{
	global_which = which;

	if (which == "expert")
	{
		global_whichText	= "מומחה";
	}
	else
	{
		global_whichText	= "משתלם";
	}

	pageObj.updatePageTitle (m_addTitleId, "הוספת " + global_whichText,		 "");
	pageObj.updatePageTitle (m_updateTitleId, "עדכון פרטי " + global_whichText, "");
}


var globalDetailsXml;
var globalType;
var globalId;

var m_formLangs	= new selectOptionsObj();
m_formLangs.addOption ("HEB",	"עברית",	"");
m_formLangs.addOption ("ENG",	"אנגלית",	"");

var m_langs	= new selectOptionsObj();
m_langs.addOption ("",		"",			"");
m_langs.addOption ("HEB",	"עברית",	"");
m_langs.addOption ("ENG",	"אנגלית",	"");

var m_formStatuses	= new selectOptionsObj();
m_formStatuses.addOption ("active",		"פעיל",		"");
m_formStatuses.addOption ("disabled",	"לא פעיל",	"");

var m_formCommonStatuses	= new selectOptionsObj();
m_formCommonStatuses.addOption ("new",		"לא פעיל - חדש",	"");
m_formCommonStatuses.addOption ("active",	"פעיל - רגיל",		"");
m_formCommonStatuses.addOption ("promoted",	"פעיל - מקודם",		"");
m_formCommonStatuses.addOption ("hidden",	"פעיל - ללא פרטים",	"");
m_formCommonStatuses.addOption ("inactive",	"לא פעיל - ישן",	"");

var m_formPromotions	= new selectOptionsObj();
m_formPromotions.addOption ("-2",	"מתרחק מרגיל",	"");
m_formPromotions.addOption ("-1",	"מתקרב  לרגיל",		"");
m_formPromotions.addOption ("0",	"רגיל",		"");
m_formPromotions.addOption ("1",	"חשוב",	"");
m_formPromotions.addOption ("2",	"חשוב מאד",	"");
m_formPromotions.addOption ("3",	"סופר חשוב",	"");

var m_formGenders		= new selectOptionsObj();
m_formGenders.addOption ("m",		"זכר",		"");
m_formGenders.addOption ("f",		"נקבה",		"");

var m_dialZones	= new selectOptionsObj();
m_dialZones.addOption ("",		"",			"");
m_dialZones.addOption ("02",	"02",		"");
m_dialZones.addOption ("03",	"03",		"");
m_dialZones.addOption ("04",	"04",		"");
m_dialZones.addOption ("08",	"08",		"");
m_dialZones.addOption ("09",	"09",		"");

var m_courses		= new selectOptionsObj();
m_courses.addOption ("",	"",					"");
m_courses.addOption	("101",	"קורס עדים מומחים",	"");
m_courses.addOption	("102",	"קורס בוררים",		"");
m_courses.addOption	("103",	"קורס משולב",		"");
m_courses.addOption	("15445",	"קורס מקוון דצמבר 18",		"");

/* ---------------------------------------------------------------------------------------- */
/* members_openForm																			*/
/* ---------------------------------------------------------------------------------------- */
function members_openForm (type)
{
	globalType = type;

	if (type == "update")
	{
		if (global_whereAmI == "updates")
		{
			var id = pageObj.getSelectedValueOf(tableId, "memberId");
		}
		else
		{
			if (pageObj.areRowsSelected(tableId))
			{
				commonMsgBox ("info", "יש לבחור " + global_whichText + " אחד בלבד");
				return false;
			}

			if (!pageObj.isRowSelected(tableId)) 
			{
				commonMsgBox ("info", "יש לבחור " + global_whichText);
				return false;
			}

			var id = pageObj.getSelectedValueOf(tableId, m_idCol.xmlTag);
		}

		globalDetailsXml = undefined;

		serverObj.cleanRequest ();
		serverObj.addTag(m_idCol.xmlTag, id);
		serverObj.sendRequest("israeliExperts.getMemberDetails", undefined, "members_after_getDetails");

		globalId = id;
	}
	else
	{
		members_openForm_continue ();
	}
}

// ----------------------------------------------------------------------------------------

function members_after_getDetails (i)
{
	globalDetailsXml = asyncResponseXml.getResponseXml (i);

	members_openForm_continue ();
}

// ----------------------------------------------------------------------------------------

function members_openForm_continue ()
{
	var type = globalType;

	if (type == "update" && globalDetailsXml == undefined) return;

	if (m_formId == -1)
		m_formId   = pageObj.addForm ();

	pageObj.resetForm (m_formId);

	// ------------------------------------------------------------------------------------ 

	if (type == "update")
		pageObj.setFormXml (m_formId, globalDetailsXml);
	
	// -----------------------------------------------------------------------------------------------------------

	fieldsWidths = {HEB : new Array(160,300,160,300),
					ENG : new Array(160,300,160,300)}

	var fieldWidth  = 290;
	var fieldWidth2 = 721;
	var fieldWidth3 = 100;

	// -----------------------------------------------------------------------------------------------------------

	var frameId = pageObj.addFormFrame (m_formId, "פרטי זיהוי", ""); 

	hidden	 		 = {type		: "hidden",						dataFld		 : m_idCol.xmlTag};

  	langField	  	 = {type		: "select",						textHEB		 : m_langCol.textHEB,
		 		 		spanData	: 1,							textENG		 : m_langCol.textENG,
				  		dataFld		: m_langCol.xmlTag,				width	 	 : fieldWidth,
						options 	: m_formLangs.getOptions()}

  	firstnameField	 = {type		: "text",						textHEB		 : m_firstnameCol.textHEB,
		 		 		spanData	: 1,							textENG		 : m_firstnameCol.textENG,
				  		dataFld		: m_firstnameCol.xmlTag,		width	 	 : fieldWidth,
						maxLength 	: "50"}

  	lastnameField	 = {type		: "text",						textHEB		 : m_lastnameCol.textHEB,
				  		spanData	: 1,							textENG		 : m_lastnameCol.textENG,
				  		dataFld		: m_lastnameCol.xmlTag,			width	 	 : fieldWidth,
						maxLength 	: "50"}

  	extentNameField	 = {type		: "text",						textHEB		 : "תואר",
				  		spanData	: 1,							textENG		 : "",
				  		dataFld		: "fldExtentName",				width	 	 : fieldWidth,
						maxLength 	: "50"}

  	genderField	 	 = {type		: "select",						textHEB		 : "מגדר",
		  				spanData	: 1,							textENG		 : "",
						dataFld		: "gender",						width	 	 : fieldWidth,
						options		: m_formGenders.getOptions(),	defaultValue : "m"}

	commonStatusField= {type		: "select",						textHEB		 : "סטטוס",
					  	spanData  	: 1,							textENG 	 : "",
					 	dataFld		: "commonStatus",				width		 : fieldWidth,
						options		: m_formCommonStatuses.getOptions(),	defaultValue : "active",
						mandatory	: true}

	statusField 	 = {type		: "select",						textHEB		 : m_statusCol.textHEB,
					  	spanData  	: 1,							textENG 	 : m_statusCol.textENG,
					 	dataFld		: m_statusCol.xmlTag,			width		 : fieldWidth,
						options		: m_formStatuses.getOptions(),	defaultValue : "active",
						mandatory	: true}
	
	promotedField    = {type      	: "select",						textHEB		 : "דרגת הקידום",
					    spanData  	: 1,							textENG		 : "",
					    dataFld   	: "extraData6",					width   	 : fieldWidth,
						options		: m_formPromotions.getOptions(),	defaultValue : "0"}

	isReadyField     = {type		: "yesNoSelect",				textHEB		 : "פתוח לצפייה",
					    spanData  	: 1,							textENG 	 : "",
					    dataFld		: "isReady",					width		 : fieldWidth,
					    mandatory	: true}

  	specializField   = {type		: "text",						textHEB		 : "התמחות ראשית",
					    spanData	: 1,							textENG		 : "",
					    dataFld		: "fldSpecialization",			width	 	 : fieldWidth}

	birthDateField   = {type		: "date",						textHEB		 : "תאריך לידה",
					    spanData	: 1,							textENG		 : "",
					    dataFld		: "birthDate",					width	 	 : fieldWidth}

  	emailField  	 = {type		: "text",						textHEB		 : "דוא\"ל",
				  	    spanData	: 3,							textENG		 : "",
						dataFld		: "email",						width	 	 : fieldWidth}

	payFreeField  	 = {type		: "date",						textHEB		 : "פטור מתשלום עד",
					    spanData	: 1,							textENG		 : "",
					    dataFld		: "payFree",					width	 	 : fieldWidth}

	courseField   	 = {type		: "select",						textHEB		 : m_courseCol.textHEB,
					  	spanData  	: 1,							textENG 	 : m_courseCol.textENG,
					 	dataFld		: m_courseCol.xmlTag,			width		 : fieldWidth,
						options		: globalCourses,				defaultValue : ""}

	field11  = {type      	: "yesNoSelect",				textHEB		 : "נתן תעודת הוקרה?",
			    spanData  	: 1,							textENG		 : "",
			    dataFld   	: "thanksLetter",				width   	 : fieldWidth}

  	field12	 = {type		: "date",						textHEB		 : "הצטרפות",
		  	    spanData	: 1,							textENG		 : "",
				dataFld		: "joinTime",					width	 	 : fieldWidth}
	
	fields = new Array(hidden);

	if (global_which == "expert")
	{
		fields.push (langField, payFreeField, firstnameField, lastnameField, extentNameField, genderField, commonStatusField, birthDateField, 
					 promotedField, specializField, field11, field12);
	}
	else
	{
		fields.push (langField, payFreeField, firstnameField, lastnameField, extentNameField, genderField, commonStatusField, birthDateField,
					 courseField, specializField, field11, field12);
	}


	file   = {type			: "onlineUpload",				textHEB		 : "תמונה", 
			  spanData		: 3,							textENG		 : "",
			  dataFld		: "file",						width		 : fieldWidth}

	source = {type			: "span",						textHEB		 : "קובץ מקור",
			  spanData  	: 1,							textENG		 : "",
			  dataFld		: "formFileSource",				width   	 : fieldWidth,
			  dir		 	: "ltr"}

	show   = {type			: "span",						textHEB		 : "הצגת תמונה",
			  spanData		: 1,							textENG		 : "",
			  dataFld   	: "show",						width		 : fieldWidth,
			  className 	: "styleLink", 					action       : "members_showFile()"}

	del    = {type			: "span",						textHEB		 : "מחיקת תמונה",
			  spanData		: 1,							textENG		 : "",
			  dataFld   	: "delete",						width		 : fieldWidth,
			  className 	: "styleLink", 					action       : "members_deleteFile()"}

	hidden1 = {type			: "hidden",						dataFld      : "fileFullName"}
	hidden2 = {type			: "hidden",						dataFld   	 : "fileDeleted",
			  												defaultValue : "0"}
	hidden3 = {type			: "hidden",						dataFld      : "extraData3"}
	hidden4 = {type			: "hidden",						dataFld   	 : "status",
			  												defaultValue : "new"}
	if (type == "update")
	{
		file.spanData	= 1;
	}

	fields.push (hidden1, hidden2, hidden3, hidden4, file);

	if (type == "update")
	{
	    fields.push (source, show, del);
	}

	hidden = {type			: "hidden",						dataFld      : m_idCol.xmlTag}
	fields.push (hidden);

  	field1	 = {type		: "select",						textHEB		 : "אזור חיוג",
		  	    spanData	: 1,							textENG		 : "",
				dataFld		: "fldDialZone",				width	 	 : fieldWidth,
				options		: m_dialZones.getOptions()}

  	field2	 = {type		: "text",						textHEB		 : "נייד",
		  	    spanData	: 1,							textENG		 : "",
				dataFld		: "fldPublicMobil",				width	 	 : fieldWidth}

  	field3	 = {type		: "text",						textHEB		 : "טלפון",
		  	    spanData	: 1,							textENG		 : "",
				dataFld		: "fldPublicPhone",				width	 	 : fieldWidth}

  	field4	 = {type		: "text",						textHEB		 : "פקס",
		  	    spanData	: 1,							textENG		 : "",
				dataFld		: "fldPublicFax",				width	 	 : fieldWidth}

  	field5	 = {type		: "text",						textHEB		 : "דוא\"ל",
		  	    spanData	: 1,							textENG		 : "",
				dataFld		: "fldPublicEmail",				width	 	 : fieldWidth}

  	field6	 = {type		: "text",						textHEB		 : "כתובת",
		  	    spanData	: 1,							textENG		 : "",
				dataFld		: "fldPublicAddress",			width	 	 : fieldWidth}

  	field7	 = {type		: "text",						textHEB		 : "כתובת למשלוח דואר",
		  	    spanData	: 1,							textENG		 : "",
				dataFld		: "mailAddress",				width	 	 : fieldWidth}

  	field8	 = {type		: "textEng",					textHEB		 : "אתר אינטרנט",
		  	    spanData	: 1,							textENG		 : "",
				dataFld		: "mySite",						width	 	 : fieldWidth}

  	field9	 = {type		: "text",						textHEB		 : "מידע נוסף",
		  	    spanData	: 1,							textENG		 : "",
				dataFld		: "moreDetails",				width	 	 : fieldWidth}

	fields.push(field1, field2, field3, field4, field5, field6, field7, field8, field9);

	if (global_which == "expert")
	{
  		field1	 = {type		: "checkbox",				textHEB		 : "הסתרת פרטי ההתקשרות",
		  		    spanData	: 1,						textENG		 : "",
					dataFld		: "hideContactDetails",		width	 	 : fieldWidth}

		fields.push (field1);
	}

	pageObj.addFormFields (m_formId, frameId, fieldsWidths, fields);

	// -----------------------------------------------------------------------------------------------------------

	fieldsWidths2 = {HEB : new Array(250,100),
				 	 ENG : new Array(250,100)}

	var frameId = pageObj.addFormFrame (m_formId, "תכונות", ""); 

	fields = new Array();

  	field1	 = {type		: "checkbox",					textHEB		 : "מומחה מוסמך המכון מסלול מורחב",
		  	    spanData	: 1,							textENG		 : "",
				dataFld		: "fldGraduatedLevelAdv",		width	 	 : fieldWidth}

  	field2	 = {type		: "checkbox",					textHEB		 : "מומחה מוסמך המכון מסלול מצומצם",
		  	    spanData	: 1,							textENG		 : "",
				dataFld		: "fldGraduatedLevel",			width	 	 : fieldWidth}

  	field3	 = {type		: "checkbox",					textHEB		 : "מומחה חוץ",
		  	    spanData	: 1,							textENG		 : "",
				dataFld		: "fldExternalGraduated",		width	 	 : fieldWidth}

  	field4	 = {type		: "checkbox",					textHEB		 : "מגשר מוסמך מכון בפיקוח ועדת גדות",
		  	    spanData	: 1,							textENG		 : "",
				dataFld		: "fldMegasherHutzh",			width	 	 : fieldWidth}

  	field5	 = {type		: "checkbox",					textHEB		 : "מגשר מוסמך המכון",
		  	    spanData	: 1,							textENG		 : "",
				dataFld		: "fldMegasherMusmahMahon",		width	 	 : fieldWidth}

  	field6	 = {type		: "checkbox",					textHEB		 : "נתמך בתצהיר",
		  	    spanData	: 1,							textENG		 : "",
				dataFld		: "fldDeclaration",				width	 	 : fieldWidth}

  	field7	 = {type		: "checkbox",					textHEB		 : "נתקבלו המלצות",
		  	    spanData	: 1,							textENG		 : "",
				dataFld		: "fldHamlathot",				width	 	 : fieldWidth}

  	field8	 = {type		: "checkbox",					textHEB		 : "עורך דין",
		  	    spanData	: 1,							textENG		 : "",
				dataFld		: "fldLoyer",					width	 	 : fieldWidth}

  	field9	 = {type		: "checkbox",					textHEB		 : "שופט בדימוס",
		  	    spanData	: 1,							textENG		 : "",
				dataFld		: "fldJudge",					width	 	 : fieldWidth}

  	field10	 = {type		: "checkbox",					textHEB		 : "בורר",
		  	    spanData	: 1,							textENG		 : "",
				dataFld		: "fldBorer",					width	 	 : fieldWidth}

	fields = new Array(field1, field2, field3, field4, field5, field6, field7, field8, field9, field10);

	pageObj.addFormFields (m_formId, frameId, fieldsWidths2, fields);

	// -----------------------------------------------------------------------------------------------------------

	var frameId = pageObj.addFormFrame (m_formId, "השכלה", ""); 

  	field1	 = {type		: "text",						textHEB		 : "תואר",
		  		spanData	: 3,							textENG		 : "",
				dataFld		: "fldQualifications",			width	 	 : fieldWidth2}

	field2   = {type		: "textarea",					textHEB		 : "פירוט מובנה",
				spanData	: 3, 							textENG		 : "",
				dataFld		: "fldStructuredQualifications",
				rows		: 3,							width		 : fieldWidth2}

  	field3	 = {type		: "textarea",					textHEB		 : "פירוט חופשי",
		  		spanData	: 3,							textENG		 : "",
				dataFld		: "fldQualificationsAdditionalNotes",
				rows		: 6,							width	 	 : fieldWidth2}

  	field4	 = {type		: "text",						textHEB		 : "ידיעת שפות",
		  	    spanData	: 3,							textENG		 : "",
				dataFld		: "langs",						width	 	 : fieldWidth2}

	fields = new Array(field1, field2, field3, field4);

	pageObj.addFormFields (m_formId, frameId, fieldsWidths, fields);

	// -----------------------------------------------------------------------------------------------------------

	var frameId = pageObj.addFormFrame (m_formId, "התמחות", ""); 

  	field1	 = {type		: "textarea",					textHEB		 : "חבר בארגון",
		  		spanData	: 3,							textENG		 : "",
				dataFld		: "fldOrgenizations",			width	 	 : fieldWidth2,
				rows		: 8}

  	field2	 = {type		: "textarea",					textHEB		 : "התמחות מקצועית",
		  		spanData	: 3,							textENG		 : "",
				dataFld		: "fldAdditionalNotes",			width	 	 : fieldWidth2,
				rows		: 5}

  	field3	 = {type		: "text",						textHEB		 : "מידע נוסף",
		  	    spanData	: 3,							textENG		 : "",
				dataFld		: "moreSchoolDetails",			width	 	 : fieldWidth2}

  	field4	 = {type		: "textarea",					textHEB		 : "קו\"ח מקצועיים",
		  	    spanData	: 3,							textENG		 : "",
				dataFld		: "fldPublicAdditionalNotes",	width	 	 : fieldWidth2,
				rows		: 5}

	fields = new Array(field1, field2, field3, field4);

	pageObj.addFormFields (m_formId, frameId, fieldsWidths, fields);

	// -----------------------------------------------------------------------------------------------------------

/*	var frameId = pageObj.addFormFrame (m_formId, "פרטי התקשרות", ""); 

	fields = new Array();

  	field1	 = {type		: "select",						textHEB		 : "אזור חיוג",
		  	    spanData	: 1,							textENG		 : "",
				dataFld		: "fldDialZone",				width	 	 : fieldWidth,
				options		: m_dialZones.getOptions()}

  	field2	 = {type		: "text",						textHEB		 : "נייד",
		  	    spanData	: 1,							textENG		 : "",
				dataFld		: "fldPublicMobil",				width	 	 : fieldWidth}

  	field3	 = {type		: "text",						textHEB		 : "טלפון",
		  	    spanData	: 1,							textENG		 : "",
				dataFld		: "fldPublicPhone",				width	 	 : fieldWidth}

  	field4	 = {type		: "text",						textHEB		 : "פקס",
		  	    spanData	: 1,							textENG		 : "",
				dataFld		: "fldPublicFax",				width	 	 : fieldWidth}

  	field5	 = {type		: "text",						textHEB		 : "דוא\"ל",
		  	    spanData	: 1,							textENG		 : "",
				dataFld		: "fldPublicEmail",				width	 	 : fieldWidth}

  	field6	 = {type		: "text",						textHEB		 : "כתובת",
		  	    spanData	: 1,							textENG		 : "",
				dataFld		: "fldPublicAddress",			width	 	 : fieldWidth}

  	field7	 = {type		: "text",						textHEB		 : "כתובת למשלוח דואר",
		  	    spanData	: 1,							textENG		 : "",
				dataFld		: "mailAddress",				width	 	 : fieldWidth}

  	field8	 = {type		: "textEng",					textHEB		 : "אתר אינטרנט",
		  	    spanData	: 1,							textENG		 : "",
				dataFld		: "mySite",						width	 	 : fieldWidth}

  	field9	 = {type		: "text",						textHEB		 : "מידע נוסף",
		  	    spanData	: 3,							textENG		 : "",
				dataFld		: "moreDetails",				width	 	 : fieldWidth2}

	fields = new Array(field1, field2, field3, field4, field5, field6, field7, field8, field9);

	if (global_which == "expert")
	{
  		field1	 = {type		: "checkbox",				textHEB		 : "הסתרת פרטי ההתקשרות",
		  		    spanData	: 1,						textENG		 : "",
					dataFld		: "hideContactDetails",		width	 	 : fieldWidth}

		fields.push (field1);
	}

	pageObj.addFormFields (m_formId, frameId, fieldsWidths, fields);
*/
	// -----------------------------------------------------------------------------------------------------------

	var frameId = pageObj.addFormFrame (m_formId, "פרטי מקצוע ועיסוק", ""); 

	fields = new Array();

	fieldsWidths3 = {HEB : new Array(170,250,140,110),
					 ENG : new Array(170,250,140,110)}

	field1	 = {type		: "text",						textHEB		 : m_professionCol.textHEB,
		  		spanData	: 1,							textENG		 : m_professionCol.textENG,
				dataFld		: m_professionCol.xmlTag,			width	 	 : fieldWidth}

	field2   = {type		: "number",						textHEB		 : "ותק",
			    spanData	: 1,							textENG		 : "",
			    dataFld		: "fldGeneralLongevity",		width	 	 : fieldWidth3}

  	field3	 = {type		: "text",						textHEB		 : "פרטי מידע מיקומי אחר",
		  	    spanData	: 1,							textENG		 : "",
				dataFld		: "extraDetails",				width	 	 : fieldWidth}

  	field4	 = {type		: "text",						textHEB		 : "מספר רישיון",
		  	    spanData	: 1,							textENG		 : "",
				dataFld		: "licenseNo",					width	 	 : fieldWidth3}

  	field5	 = {type		: "text",						textHEB		 : "עיסוק נוכחי והגדרת תפקיד",
		  	    spanData	: 1,							textENG		 : "",
				dataFld		: "currBiz",					width	 	 : fieldWidth}

  	field6	 = {type		: "text",						textHEB		 : "ותק בעיסוק נוכחי",
		  	    spanData	: 1,							textENG		 : "",
				dataFld		: "fldLongevity",				width	 	 : fieldWidth3}

  	field7	 = {type		: "text",						textHEB		 : "שם המוסד בו מועסק",
		  	    spanData	: 3,							textENG		 : "",
				dataFld		: "workplace",					width	 	 : fieldWidth}

  	field8	 = {type		: "text",						textHEB		 : "הכנת חוות דעת",
		  	    spanData	: 3,							textENG		 : "",
				dataFld		: "experience1",				width	 	 : fieldWidth}

  	field9	 = {type		: "text",						textHEB		 : "נושאים חוות דעת",
		  	    spanData	: 3,							textENG		 : "",
				dataFld		: "experience2",				width	 	 : fieldWidth}

  	field10	 = {type		: "text",						textHEB		 : "הופעת כעד מומחה",
		  	    spanData	: 3,							textENG		 : "",
				dataFld		: "experience3",				width	 	 : fieldWidth}

  	field11	 = {type		: "text",						textHEB		 : "באילו משפטים הופעת",
		  	    spanData	: 3,							textENG		 : "",
				dataFld		: "experience4",				width	 	 : fieldWidth}

  	field12	 = {type		: "text",						textHEB		 : "הסבר בקשה להוספת קטגוריה",
		  	    spanData	: 3,							textENG		 : "",
				dataFld		: "catsExtraDetails",			width	 	 : fieldWidth}

	fields = new Array(field1, field2, field3, field4, field5, field6, field7, field8, field9, field10, field11, field12);

	pageObj.addFormFields (m_formId, frameId, fieldsWidths3, fields);

	// -----------------------------------------------------------------------------------------------------------

	fieldsWidths2 = {HEB : new Array(40,700),
				  	 ENG : new Array(40,700)}

	var frameId = pageObj.addFormFrame (m_formId, "תחומי עיסוק", ""); 

	fields = new Array();

  	field1	 = {type		: "span",						textHEB		 : "",
		  	    spanData	: 1,							textENG		 : "",
				dataFld		: "cats",						width	 	 : 700,
				rows		: 15}

	fields = new Array(field1);

	pageObj.addFormFields (m_formId, frameId, fieldsWidths2, fields);

	// -----------------------------------------------------------------------------------------------------------

	var frameId = pageObj.addFormFrame (m_formId, "לשימוש פנימי", ""); 

	fields = new Array();

  	field1	 = {type		: "text",						textHEB		 : "טלפון 1",
		  	    spanData	: 1,							textENG		 : "",
				dataFld		: "phone",						width	 	 : fieldWidth}

  	field2	 = {type		: "text",						textHEB		 : "טלפון 2",
		  	    spanData	: 1,							textENG		 : "",
				dataFld		: "phone2",						width	 	 : fieldWidth}

  	field3	 = {type		: "text",						textHEB		 : "נייד",
		  	    spanData	: 1,							textENG		 : "",
				dataFld		: "cellphone",					width	 	 : fieldWidth}

  	field4	 = {type		: "text",						textHEB		 : "פקס",
		  	    spanData	: 1,							textENG		 : "",
				dataFld		: "fax",						width	 	 : fieldWidth}

  	field5	 = {type		: "text",						textHEB		 : "רחוב",
		  	    spanData	: 1,							textENG		 : "",
				dataFld		: "address",					width	 	 : fieldWidth}

  	field6	 = {type		: "text",						textHEB		 : "מספר בית",
		  	    spanData	: 1,							textENG		 : "",
				dataFld		: "streetNo",					width	 	 : fieldWidth}

  	field7	 = {type		: "text",						textHEB		 : "יישוב",
		  	    spanData	: 1,							textENG		 : "",
				dataFld		: "city",						width	 	 : fieldWidth}

  	field8	 = {type		: "text",						textHEB		 : "מיקוד",
		  	    spanData	: 1,							textENG		 : "",
				dataFld		: "zipcode",					width	 	 : fieldWidth}

  	field9	 = {type		: "text",						textHEB		 : "מדינה",
		  	    spanData	: 1,							textENG		 : "",
				dataFld		: "country",					width	 	 : fieldWidth}

  	field10	 = {type		: "textEng",					textHEB		 : "תאריך לידה",
		  	    spanData	: 1,							textENG		 : "",
				dataFld		: "birthDate",					width	 	 : fieldWidth}

  	field11	 = {type		: "textEng",					textHEB		 : m_usernameCol.textHEB,
		  			    spanData	: 1,							textENG		 : m_usernameCol.textENG,
					    dataFld		: m_usernameCol.xmlTag,			width	 	 : fieldWidth}

  	field12	 = {type		: "text",						textHEB		 : "סיסמא",
		  				spanData	: 1,							textENG		 : "",
				  		dataFld		: "password",					width	 	 : fieldWidth,
						maxLength 	: "50"}

  	field13	 = {type		: "textarea",					textHEB		 : "הערות",
		  	    spanData	: 3,							textENG		 : "",
				dataFld		: "fldAdminRemarks",			width	 	 : fieldWidth2,
				rows		: 8}

	fields = new Array(field1, field2, field3, field4, field5, field6, field7, field8, field9, field10, emailField, field11, field12, field13);

	pageObj.addFormFields (m_formId, frameId, fieldsWidths, fields);

	// -----------------------------------------------------------------------------------------------------------

	pageObj.generateForm  (m_formId);

	if (type == "update")
	{
		var id = pageObj.getSelectedValueOf(tableId, m_idCol.xmlTag);

		$("#form2_cats").html ("<iframe src='buildTree.php?expertId=" + id + "' width='800' height='400' frameborder='0'></iframe>");

		var name = commonGetInnerData(globalDetailsXml, m_firstnameCol.xmlTag) + " " + commonGetInnerData(globalDetailsXml, m_lastnameCol.xmlTag);

		pageObj.updatePageTitle (m_updateTitleId, "עדכון פרטי מומחה - " + name, "");
	}

	multiUploadStart	  (undefined, m_formId);

	members_createEditFormButtons (type);

	handleDisplay 		  (type);
}

/* ---------------------------------------------------------------------------------------- */
/* members_showFile																			*/
/* ---------------------------------------------------------------------------------------- */
function members_showFile ()
{
	window.open (pageObj.getFieldValue(m_formId, "fileFullName"), "_blank");
}

/* ---------------------------------------------------------------------------------------- */
/* members_deleteFile																		*/
/* ---------------------------------------------------------------------------------------- */
function members_deleteFile ()
{
	pageObj.setFieldValue(m_formId, "formFileSource", "");
	pageObj.setFieldValue(m_formId, "fileDeleted", "1");
	pageObj.setFieldValue(m_formId, "delete", "התמונה תמחק בעת העדכון");
}

/* ---------------------------------------------------------------------------------------- */
/* members_createEditFormButtons															*/
/* ---------------------------------------------------------------------------------------- */
function members_createEditFormButtons (type)
{
	btnsGroups = new Array ();

	btn1	= {type			: "back",
			   action		: "handleDisplay('report')"}

	btn2	= {type			: "",
			   textHEB		: "בקשות ה" + global_whichText,
			   action		: "showUpdates('expertForm')"}

	btn3	= {type			: type,
			   action		: "members_submitForm('" + type + "')"}

	btnsGroups.push (new Array(btn1));

	if (global_whereAmI == "updates")
	{
		btn1.action	= "handleDisplay('details')";
	}
	else if (type == "update")
	{
		btnsGroups.push (new Array(btn2));
	}

	btnsGroups.push (new Array(btn3));



	if (m_formButtonsId == -1)
		m_formButtonsId = pageObj.addRowOfButtons 	 ();
	pageObj.generateRowOfButtons (m_formButtonsId, btnsGroups, pageObj.getFormWidth(m_formId));

}

/* ---------------------------------------------------------------------------------------- */
/* members_submitForm																		*/
/* ---------------------------------------------------------------------------------------- */
function members_submitForm (type)
{
	if (!pageObj.validateForm(m_formId)) return false;

	serverObj.setXml (pageObj.getFormXml(m_formId));

	var file = uploadGetFileName(0);
	if (file == ERROR_WAIT_LOADING)
		return false;

	serverObj.addTag ("sourceFile",  file);
	serverObj.addTag ("fileDeleted", pageObj.getFieldValue(m_formId, "fileDeleted"));
	serverObj.addTag ("extraData4", global_which);
	
	serverObj.sendRequest("israeliExperts." + type + "Member", undefined, "members_submitForm_continue");
}

function members_submitForm_continue (i)
{
	var responseXml = asyncResponseXml.getResponseXml (i);

	if (responseXml != null)
	{
		if (global_whereAmI == "updates")
		{
			handleDisplay ("details");

			openForm ();
		}
		else
		{
			handleDisplay	("report");
			doRefresh 		();

			global_selected = commonGetInnerData(responseXml, m_idCol.xmlTag);
		}
	}
	return true;
}

