var	idCol				= {textHEB	: "קוד", 			textENG	: "ID", 				xmlTag	: "productId"		}
var	nameCol				= {textHEB	: "שם מוצר", 		textENG	: "Name", 				xmlTag	: "productName"		}
var	statusCol			= {textHEB	: "סטטוס", 			textENG	: "Status", 			xmlTag	: "status"			}
var	producerCol			= {textHEB	: "יצרן", 			textENG	: "Producer", 			xmlTag	: "producerId"		}
var	stockCol			= {textHEB	: "מלאי", 			textENG	: "Stock", 				xmlTag	: "stock"			}
var	priceCol			= {textHEB	: "מחיר", 			textENG	: "Price", 				xmlTag	: "price"			}
var descCol				= {textHEB	: "תיאור המוצר", 	textENG	: "Description", 		xmlTag	: "description"		}
var	categoryCol	    	= {textHEB	: "קטגוריה", 		textENG	: "Category", 			xmlTag	: "category"		}
var	featuredCol			= {textHEB	: "מוצר נבחר", 		textENG	: "Sel.", 				xmlTag	: "featured"		}
var	makatCol			= {textHEB	: "מק\"ט", 			textENG : "Cat. No.", 			xmlTag	: "makat"			}
var buyOnlineCol		= {textHEB 	: "נמכר באתר",		textENG	: "For sale", 			xmlTag	: "buyOnline"		}
var winTitleCol 		= {textHEB  : "כותרת הדפדפן",	textENG : "Window title", 		xmlTag 	: "winTitle"		}

var statusOptions = new selectOptionsObj();
statusOptions.addOption ("" ,  "",	 			"");
statusOptions.addOption ("0",  "חדש", 		 	"New");
statusOptions.addOption ("1",  "זמין ומוצג",  	"In Stock & Shown");
statusOptions.addOption ("2",  "לא זמין ומוצג", "Out of Stock & Shown");
statusOptions.addOption ("3",  "לא מוצג", 		"Not Shown");
statusOptions.addOption ("4",  "במבצע",			"On Sale");
statusOptions.addOption ("5",  "חסר במלאי", 	"Out of Stock");

var globalShopXml;
var globalProducers;
var globalShopCats;
var globalGroups;

var currency1 			 = "";
var currency2 			 = "";

var remark1Field 		= "הערה 1";
var remark2Field 		= "הערה 2";
var remark3Field 		= "הערה 3";
var remark4Field 		= "הערה 3";

var conversionRate		 = "";
var handleStock			 = "";
var defaultMinSupplyDays = "";
var defaultMaxSupplyDays = "";


var buyOnlineOptions = new selectOptionsObj();
buyOnlineOptions.addOption ("",		"",   "");
buyOnlineOptions.addOption ("1",	"כן", "Yes");
buyOnlineOptions.addOption ("0",	"לא", "No");

var specailsCount;

/* ---------------------------------------------------------------------------------------- */
/* shopCommon_init																			*/
/* ---------------------------------------------------------------------------------------- */
function shopCommon_init ()
{
	serverObj.cleanRequest ();
	serverObj.addTag 	   ("type", "shop");
	serverObj.sendRequest  ("shop.initShop", undefined, "after_initShop");
}

function after_initShop (i)
{
	globalShopXml		 = asyncResponseXml.getResponseXml (i);

	specailsCount 		 = commonGetInnerData (globalShopXml, "count");

	currency1 			 = commonGetInnerData (globalShopXml, "currency1");
	currency2 			 = commonGetInnerData (globalShopXml, "currency2");
	conversionRate  	 = commonGetInnerData (globalShopXml, "conversionRate");
	handleStock  		 = commonGetInnerData (globalShopXml, "handleStock");
	defaultMinSupplyDays = commonGetInnerData (globalShopXml, "defaultMinSupplyDays");
	defaultMaxSupplyDays = commonGetInnerData (globalShopXml, "defaultMaxSupplyDays");

	remark1Field 		 = commonGetInnerData (globalShopXml, "remark1");
	remark2Field 		 = commonGetInnerData (globalShopXml, "remark2");
	remark3Field 		 = commonGetInnerData (globalShopXml, "remark3");
	remark4Field 		 = commonGetInnerData (globalShopXml, "remark4");

	if (remark1Field == "") remark1Field = "הערה 1";
	if (remark2Field == "") remark2Field = "הערה 2";
	if (remark3Field == "") remark3Field = "הערה 3";
	if (remark4Field == "") remark4Field = "הערה 4";

	// cats
	var selectOptions 	= new selectOptionsObj();

	itemsNode = globalShopXml.getElementsByTagName("cats").item(0);

	if (itemsNode == null)
	{
		selectOptions.addOption ("", "אין קטגוריות מוגדרות", "No Categories");
	} 
	else
	{
		selectOptions.addOption ("", "" ,"");

		for (i=0; i < itemsNode.childNodes.length; i++)
		{
			var	currNode    = itemsNode.childNodes[i];

			var	id  		= commonGetInnerData(currNode, "id");
			var	name 		= commonGetInnerData(currNode, "selectName");

			var	parentId 	= commonGetInnerData(currNode, "parentId");
			var	countItems 	= commonGetInnerData(currNode, "countItems");

			var	style = "";
			if (parentId != 0)
				style   = "color:#808080";

			selectOptions.addOption (id, name, name, style);
		}
	}
	globalShopCats = selectOptions.getOptions ();

	// groups
	var selectOptions 	= new selectOptionsObj();

	itemsNode = globalShopXml.getElementsByTagName("groups").item(0);

	if (itemsNode == null)
	{
		selectOptions.addOption ("", "אין קבוצות", "No Variations");
	} 
	else
	{
		for (i=0; i < itemsNode.childNodes.length; i++)
		{
			var currNode	= itemsNode.childNodes[i];

			var id  		= commonGetInnerData(currNode, "groupId");
			var name 		= commonGetInnerData(currNode, "name");

			var	optionText = id + " - " + name;

			selectOptions.addOption (id, name, name);
		}
	}

	globalGroups = selectOptions.getOptions ();

	// producers
	var selectOptions 	= new selectOptionsObj();

	itemsNode = globalShopXml.getElementsByTagName("producers").item(0);

	if (itemsNode == null)
	{
		selectOptions.addOption ("", "אין יצרנים מוגדרים", "No Producers");
	} 
	else
	{
		selectOptions.addOption ("", "", "");
		for (i=0; i < itemsNode.childNodes.length; i++)
		{
			var currNode	= itemsNode.childNodes[i];

			var id  		= commonGetInnerData(currNode, "id");
			var name 		= commonGetInnerData(currNode, "name");

			var optionText = id + " - " + name;

			selectOptions.addOption (id, optionText, optionText);
		}
	}

	globalProducers = selectOptions.getOptions ();

	onLoad_continue ();
}

var globalType;
var globalFormId;

var globalDetailsXml;
var globalOtherProducts;

/* ---------------------------------------------------------------------------------------- */
/* shopCommon_createProductEditForm															*/
/* ---------------------------------------------------------------------------------------- */
function shopCommon_createProductEditForm (editFormId, type, id)
{
	globalType 	 = type;
	globalFormId = editFormId;

	serverObj.cleanRequest ();

	globalDetailsXml = undefined;

	if (type == "add")
	{
		serverObj.sendRequest("shop.getProductNextId", undefined, "after_getDetails");
	}
	else
	{
		if (id == undefined)
			id = pageObj.getSelectedValueOf (tableId, idCol.xmlTag);

		serverObj.addTag	  (idCol.xmlTag, id);
		serverObj.sendRequest ("shop.getProductDetails", undefined, "after_getDetails");
	}
}

function after_getDetails (i)
{
	globalDetailsXml = asyncResponseXml.getResponseXml (i);;

	var productId 	 = commonGetInnerData (globalDetailsXml, "productId");

	serverObj.cleanRequest ();
	serverObj.addTag ("notId", productId);

    serverObj.sendRequest("shop.getProducts", undefined, "after_getOtherProducts");
}

function after_getOtherProducts (i)
{
	var responseXml = asyncResponseXml.getResponseXml (i);;

	var selectOptions 	= new selectOptionsObj();

	if (responseXml != null)
	{
		// add options
		itemsNode = responseXml.getElementsByTagName("items").item(0);

		if (itemsNode == null)
		{
			selectOptions.addOption ("", "אין מוצרים נוספים", "No More Products");
		} 
		else
		{
			for (i=0; i < itemsNode.childNodes.length; i++)
			{
				var currNode = itemsNode.childNodes[i];

				var id  	 = commonGetInnerData(currNode, "productId");
				var name 	 = commonGetInnerData(currNode,"productName");

				selectOptions.addOption (id, name, name);
			}
		}
	}

	globalOtherProducts = selectOptions.getOptions ();

	shopCommon_createProductEditForm_continue ();
}
	
function shopCommon_createProductEditForm_continue ()
{
	var type	= globalType;
	var formId 	= globalFormId;

	pageObj.resetForm (formId);

	// ------------------------------------------------------------------------------------ 

	fieldsWidths = {HEB : new Array(140,220,130,220),
					ENG : new Array(140,220,130,220)}

	var fieldWidth   = 220;
	var fieldWidth2  = 530;

	// ------------------------------------------------------------------------------------ 

	var frameId = pageObj.addFormFrame (formId, "פרטים", "Details"); 

	if (type == "add")
	{
		var id = commonGetInnerData (globalDetailsXml, "productId");
		
		field1 = {type		: "number",					textHEB		 : idCol.textHEB,
				  spanData	: 1,						textENG		 : idCol.textENG,
				  dataFld	: idCol.xmlTag,				width	 	 : fieldWidth,
	  	 	  	  										defaultValue : id}
		titleAction = "generalSetWinTitle(\"productName\", \"add\")";

	}
	else
	{
		pageObj.setFormXml (formId, globalDetailsXml);

		id = commonGetInnerData (globalDetailsXml, "productId");	

		field1	= {type		: "span",					textHEB		 : idCol.textHEB,
				   spanData : 1,						textENG		 : idCol.textENG,
				   dataFld	: idCol.xmlTag,				width	 	 : fieldWidth,
									  	 	  	  	    defaultValue : id}
		titleAction = "generalSetWinTitle(\"productName\", \"update\")";
	}
	
  	field2 = {type			: "text",					textHEB		 : nameCol.textHEB,
			  spanData		: 1,						textENG		 : nameCol.textENG,
			  dataFld		: nameCol.xmlTag,			width	 	 : fieldWidth,
			  minLength		: "1",						mandatory	 : false,
			  maxLength		: "100",					lang		 : langArray,
	  		  action		: titleAction}

  	field3= {type			: "text",					textHEB		 : "מלאי",
			  spanData		: 1,						textENG		 : "Stock",
			  dataFld		: "stock",					width	 	 : fieldWidth}

	field4 = {type			: "select",					textHEB		 : producerCol.textHEB,
			  spanData		: 1,						textENG		 : producerCol.textENG,
			  dataFld		: producerCol.xmlTag,		width	 	 : fieldWidth,
			  options		: globalProducers}

	field5 = {type			: "number",					textHEB		 : "מינימום ימי אספקה",
			  spanData		: 1,						textENG		 : "Minimum supply days",
			  dataFld		: "productMinSupplyDays",	width	 	 : fieldWidth,
			  minLength		: 1,						mandatory	 : false,
			  maxLength		: 3,						defaultValue : defaultMinSupplyDays}

	field6 = {type			: "number",					textHEB		 : "מקסימום ימי אספקה",
			  spanData		: 1,						textENG		 : "Maximum supply days",
			  dataFld		: "productMaxSupplyDays",	width	 	 : fieldWidth,
			  minLength		: 1,						mandatory	 : false,
			  maxLength		: 3,						defaultValue : defaultMaxSupplyDays}

  	field7 = {type			: "text",					textHEB		 : makatCol.textHEB,
			  spanData		: 1,						textENG		 : makatCol.textENG,
			  dataFld		: makatCol.xmlTag,			width	 	 : fieldWidth,
			  minLength		: "1",						mandatory	 : false,
			  maxLength		: "30",						lang		 : langArray}

  	field8= {type			: "text",					textHEB		 : "מידות",
			  spanData		: 1,						textENG		 : "Measures",
			  dataFld		: "dimensions",				width	 	 : fieldWidth,
			  minLength		: "1",						mandatory	 : false,
			  maxLength		: "30"}

	field9 = {type			: "select",					textHEB		 : statusCol.textHEB,
			  spanData		: 1,						textENG		 : statusCol.textENG,
			  dataFld		: statusCol.xmlTag,			width	 	 : fieldWidth,
			  options		: statusOptions.getOptions(),
			  mandatory	 	: true,						defaultValue : "0"}

	field10= {type			: "yesNoSelect",			textHEB		 : featuredCol.textHEB,
			  spanData		: 1,						textENG		 : featuredCol.textENG,
			  dataFld		: featuredCol.xmlTag,		width	 	 : fieldWidth,
			  mandatory	 	: true,						defaultValue : "0"}

	field11= {type			: "yesNoSelect",			textHEB		 : "נמכר ביותר",
			  spanData		: 1,						textENG		 : "Best seller",
			  dataFld		: "bestSeller",				width	 	 : fieldWidth,
			  mandatory	 	: true,						defaultValue : "0"}

	field12= {type			: "date",					textHEB		 : "תאריך המוצר",
			  spanData		: 1,						textENG		 : "Date",
			  dataFld		: "productDate",			width	 	 : fieldWidth}

	field13= {type			: "yesNoSelect",			textHEB		 : buyOnlineCol.textHEB,
			  spanData		: 1,						textENG		 : buyOnlineCol.textENG,
			  dataFld		: buyOnlineCol.xmlTag,		width	 	 : fieldWidth,
			  defaultValue	: "1"}

	fields = new Array(field1, field2, field3, field4, field5, field6, field7, field8, field9, field10, field11, field12, field13);
	
	if (type == "add")
	{
		field1 = {type			: "select",					textHEB		 : "בחירת קטגוריה",
				  spanData		: 1,						textENG		 : "Choose Category",
				  dataFld		: "categoryId",				width	 	 : fieldWidth,
				  options		: globalShopCats}

		  fields.push(field1);
	}
	pageObj.addFormFields (formId, frameId, fieldsWidths, fields);

	// ------------------------------------------------------------------------------------ 

	fieldsWidths2 = {HEB : new Array(0,660),
				 	 ENG : new Array(0,660)}

	var frameId = pageObj.addFormFrame (formId, "תיאור", "Description"); 

	field1 = {type			: "xstandard",				textHEB		 : "",
			  spanData		: 1,						textENG		 : "",
			  dataFld		: descCol.xmlTag,			width	 	 : 640,
			  height	 	: 250,	 					lang		 : langArray}

	fields = new Array(field1);
	
	pageObj.addFormFields (formId, frameId, fieldsWidths2, fields);

	// ------------------------------------------------------------------------------------ 

	fieldsWidths2 = {HEB : new Array(100,550),
				 	 ENG : new Array(100,550)}

	var frameId = pageObj.addFormFrame (formId, "פרטים נוספים", "More details"); 

	field1 = {type			: "textarea",				textHEB		 : remark1Field,
			  spanData		: 1,						textENG		 : "Remark #1",
			  dataFld		: "remark1",				width	 	 : 540,
			  rows	 		: 3,	 					lang		 : langArray}

	field2 = {type			: "textarea",				textHEB		 : remark2Field,
			  spanData		: 1,						textENG		 : "Remark #2",
			  dataFld		: "remark2",				width	 	 : 540,
			  rows	 		: 3,	 					lang		 : langArray}

	field3 = {type			: "textarea",				textHEB		 : remark3Field,
			  spanData		: 1,						textENG		 : "Remark #3",
			  dataFld		: "remark3",				width	 	 : 540,
			  rows	 		: 3,	 					lang		 : langArray}

	field4 = {type			: "textarea",				textHEB		 : remark4Field,
			  spanData		: 1,						textENG		 : "Remark #4",
			  dataFld		: "remark4",				width	 	 : 540,
			  rows	 		: 3,	 					lang		 : langArray}

	fields = new Array(field1, field2, field3, field4);
	
	pageObj.addFormFields (formId, frameId, fieldsWidths2, fields);

	// ------------------------------------------------------------------------------------ 

	var frameTitle = "מחירון";
	if (handleStock == "1")
	{
		frameTitle += " ומלאי";
	}

	state = "unlock";
	if (currency2 == "")
		state = "lock";
		
	var frameId = pageObj.addFormFrame (formId, frameTitle, "Price List"); 

	field1 = {type			: "span",					textHEB		 : "מטבע ראשי",
			  spanData		: 1,						textENG		 : "Main currency",
			  dataFld		: "currency1",				defaultValue : currency1}

	if (currency2 == "")
		currency2 = "לא מוגדר";

	field2 = {type			: "span",					textHEB		 : "מטבע נוסף",
			  spanData		: 1,						textENG		 : "Other Currency",
			  dataFld		: "currency2",				defaultValue : currency2}

	field3 = {type			: "amount",					textHEB		 : "מחיר קנייה",
			  spanData		: 1,						textENG		 : "Buying Price",
			  dataFld		: "cost1",					width	 	 : fieldWidth,
	  		  action		: "onChange(\"cost\",1)"}

	field4 = {type			: "amount",					textHEB		 : "מחיר קנייה",
			  spanData		: 1,						textENG		 : "Buying Price",
			  dataFld		: "cost2",					width	 	 : fieldWidth,
	  		  state			: state,
	  		  action		: "onChange(\"cost\",2)"}

	field5 = {type			: "amount",					textHEB		 : "מחיר קטלוגי",
			  spanData		: 1,						textENG		 : "Catalogue Price",
			  dataFld		: "catalogPrice1",			width	 	 : fieldWidth,
	  		  action		: "onChange(\"catalogPrice\",1)"}

	field6 = {type			: "amount",					textHEB		 : "מחיר קטלוגי",
			  spanData		: 1,						textENG		 : "Catalogue Price",
			  dataFld		: "catalogPrice2",			width	 	 : fieldWidth,
	  		  state			: state,
	  		  action		: "onChange(\"catalogPrice\",2)"}

  	field7 = {type			: "amount",					textHEB		 : "אחוז הנחה",
			  spanData		: 1,						textENG		 : "Discount %",
			  dataFld		: "discount1",				width	 	 : fieldWidth,
			  minValue		: "0",						maxLength	 : "5",					
	  		  maxValue		: "100",
	  		  action		: "onChange(\"discount\",1)"}

	field8 = {type			: "amount",					textHEB		 : "אחוז הנחה",
			  spanData		: 1,						textENG		 : "Discount %",
			  dataFld		: "discount2",				width	 	 : fieldWidth,
			  minValue		: "0",						maxLength	 : "5",					
	  		  maxValue		: "100", 					state		 : state,
	  		  action		: "onChange(\"discount\",2)"}

	field9 = {type			: "amount",					textHEB		 : "מחיר לצרכן",
			  spanData		: 1,						textENG		 : "Purchase Price",
			  dataFld		: "customerPrice1",			width	 	 : fieldWidth,
	  		  action		: "onChange(\"customerPrice\",1)"}

	field10 = {type			: "amount",					textHEB		 : "מחיר לצרכן",
			  spanData		: 1,						textENG		 : "Purchase Price",
			  dataFld		: "customerPrice2",			width	 	 : fieldWidth,
	  		  state			: state,
	  		  action		: "onChange(\"customerPrice\",2)"}

  	field11= {type			: "amount",					textHEB		 : "אחוז רווח בפועל",
			  spanData		: 1,						textENG		 : "Profit %",
			  dataFld		: "profit1",				width	 	 : fieldWidth,
			  minValue		: "0",						maxLength	 : "5",					
	  		  action		: "onChange(\"profit\",1)"}

	field12= {type			: "amount",					textHEB		 : "אחוז רווח בפועל",
			  spanData		: 1,						textENG		 : "Profit %",
			  dataFld		: "profit2",				width	 	 : fieldWidth,
			  minValue		: "0",						maxLength	 : "5",					
	  		  maxValue		: "100", 					state	  	 : state,
	  		  action		: "onChange(\"profit\",2)"}

  	field13 = {type			: "amount",					textHEB		 : "מחיר לחבר",
			  spanData		: 1,						textENG		 : "Member Price",
			  dataFld		: "memberPrice1",			width	 	 : fieldWidth}

	field14 = {type			: "amount",					textHEB		 : "מחיר לחבר",
			  spanData		: 1,						textENG		 : "Member Price",
			  dataFld		: "memberPrice2",			width	 	 : fieldWidth}
	  		  state			: state,

  	field15 = {type			: "amount",					textHEB		 : "מחיר משלוח",
			  spanData		: 1,						textENG		 : "Shipment Price",
			  dataFld		: "shipmentPrice1",			width	 	 : fieldWidth}

	field16 = {type			: "amount",					textHEB		 : "מחיר משלוח",
			  spanData		: 1,						textENG		 : "Shipment Price",
			  dataFld		: "shipmentPrice2",			width	 	 : fieldWidth}
	  		  state			: state,


  	fields = new Array(field1, field2, field3, field4, field5, field6, field7, field8, field9, field10, field11, field12, field13, field14);
	
	if (handleStock == "1")
	{
		stockField	= 
			{type			: "number",					textHEB		 : "מלאי",
			 spanData		: 1,						textENG		 : "Stock",
			 dataFld		: "stock",					width		 : fieldWidth,
			 minLength		: "1",						mandatory	 : false,
			 maxLength		: "10"}

	 	fields.push (stockField);
	}
	
	pageObj.addFormFields (formId, frameId, fieldsWidths, fields);

	// ------------------------------------------------------------------------------------ 

	var frameId = pageObj.addFormFrame (formId, "קישורים", "Links"); 

  	field1 = {type			: "text",					textHEB		 : "שם קישור 1",
			  spanData		: 1,						textENG		 : "Link Name #1",
			  dataFld		: "specialLinkName1",		width	 	 : fieldWidth,
			  minLength		: "1",						mandatory	 : false,
			  maxLength		: "100",					lang		 : langArray}

  	field2 = {type			: "text",					textHEB		 : "כתובת קישור 1",
			  spanData		: 1,						textENG		 : "Link URL #1",
			  dataFld		: "specialLink1",			width	 	 : fieldWidth,
			  minLength		: "1",						mandatory	 : false,
			  maxLength		: "100"}

  	field3 = {type			: "text",					textHEB		 : "שם קישור 2",
			  spanData		: 1,						textENG		 : "Link Name #2",
			  dataFld		: "specialLinkName2",		width	 	 : fieldWidth,
			  minLength		: "1",						mandatory	 : false,
			  maxLength		: "100",					lang		 : langArray}

  	field4 = {type			: "text",					textHEB		 : "כתובת קישור 2",
			  spanData		: 1,						textENG		 : "Link URL #2",
			  dataFld		: "specialLink2",			width	 	 : fieldWidth,
			  minLength		: "1",						mandatory	 : false,
			  maxLength		: "100"}

  	field5 = {type			: "text",					textHEB		 : "שם קישור 3",
			  spanData		: 1,						textENG		 : "Link Name #3",
			  dataFld		: "specialLinkName3",		width	 	 : fieldWidth,
			  minLength		: "1",						mandatory	 : false,
			  maxLength		: "100",					lang		 : langArray}

  	field6 = {type			: "text",					textHEB		 : "כתובת קישור 3",
			  spanData		: 1,						textENG		 : "Link URL #3",
			  dataFld		: "specialLink3",			width	 	 : fieldWidth,
			  minLength		: "1",						mandatory	 : false,
			  maxLength		: "100"}

  	field7 = {type			: "text",					textHEB		 : "שם קישור 4",
			  spanData		: 1,						textENG		 : "Link Name #4",
			  dataFld		: "specialLinkName4",		width	 	 : fieldWidth,
			  minLength		: "1",						mandatory	 : false,
			  maxLength		: "100",					lang		 : langArray}

  	field8 = {type			: "text",					textHEB		 : "כתובת קישור 4",
			  spanData		: 1,						textENG		 : "Link URL #4",
			  dataFld		: "specialLink4",			width	 	 : fieldWidth,
			  minLength		: "1",						mandatory	 : false,
			  maxLength		: "100"}

  	field9 = {type			: "text",					textHEB		 : "שם קישור 5",
			  spanData		: 1,						textENG		 : "Link Name #5",
			  dataFld		: "specialLinkName5",		width	 	 : fieldWidth,
			  minLength		: "1",						mandatory	 : false,
			  maxLength		: "100",					lang		 : langArray}

  	field10= {type			: "text",					textHEB		 : "כתובת קישור 5",
			  spanData		: 1,						textENG		 : "Link URL #5",
			  dataFld		: "specialLink5",			width	 	 : fieldWidth,
			  minLength		: "1",						mandatory	 : false,
			  maxLength		: "100"}


	fields = new Array(field1, field2, field3, field4, field5, field6, field7, field8, field9, field10);
	
	pageObj.addFormFields (formId, frameId, fieldsWidths, fields);

	// -----------------------------------------------------------------------------------------------------------

	var frameId = pageObj.addFormFrame (formId, "מוצרים נלווים", "Accompanying Products"); 

	field1 = {type		: "multiSelect",				textHEB		: "בחירת מוצרים",
			  spanData  : 3,							textENG 	: "Choose Products",
			  dataFld	: "productIds",					width		: 300,
			  options	: globalOtherProducts,			height		: 200}

  	hidden = {type			: "hidden", 				dataFld		 : "otherProducts"}
	fields.push (hidden);

	fields = new Array(field1);

	pageObj.addFormFields (formId, frameId, fieldsWidths, fields);

	// ------------------------------------------------------------------------------------ 

	fieldsWidths = {HEB : new Array(180,470),
					ENG : new Array(180,470)}

	var frameId = pageObj.addSpecialFrame (formId, "קידום", "SEO");

	field1 = {type			: "text",					textHEB		 : "כותרת לחלון הדפדפן",
			  spanData		: 1,						textENG		 : "Window Title",
			  dataFld		: winTitleCol.xmlTag,		width	 	 : 460,
			  minLength		: "1",						mandatory	 : false,
			  maxLength		: "100",					lang		 : langArray,
			  helpTextHEB	: help_winTitleText,		counter		 : true}

	field2 = {type      	: "text",					textHEB 	 : "מילות מפתח",
			   spanData  	: 1,						textENG      : "Meta Keywords",
			   dataFld   	: "metaKeywords",			width     	 : 460,
			   minLength 	: "1",					 	defaultValue : "",
			   maxLength 	: "600",					mandatory 	 : false,
	   		   helpTextHEB	: help_keywordsText,		lang		 : langArray,		
			   counter		: true}

	field3 = {type      	: "textarea",				textHEB 	 : "תיאור הדף",
			   spanData  	: 1,						textENG      : "Meta Description",
			   dataFld   	: "metaDescription",		width     	 : 460,
			   rows 		: "3",					 	lang		 : langArray,
			   helpTextHEB	: help_descriptionText,		counter		 : true}

  	fields = new Array(field1, field2, field3);

	pageObj.addFormFields (formId, frameId, fieldsWidths, fields);

	// -----------------------------------------------------------------------------------------------------------

	if (type != "show")
		categories_addFormFrame (formId, type, "קטגוריות", "Categories", fieldsWidths2, id, "shop", "after_createCategoriesFrame")
	else
		after_createCategoriesFrame ();
}

function after_createCategoriesFrame ()
{
	var type 	= globalType;
	var formId	= globalFormId;

	// ------------------------------------------------------------------------------------ 

	pageObj.generateForm  (formId);

	if (type != "show")
	{
		formTableObj = new Object(pageObj.getFormTableId (formId, 8));	// 7 is the frame index

		commonSetGlobalData ("formTableObj", 	 formTableObj);	// save it for categoryOfItem iframe
	}

	openForm_continue (type);
}

