/*----------------------------------------------------------------------*/
/* 																		*/
/*						selectOptionsObj.js								*/
/*						-------------------								*/
/*																		*/
/*----------------------------------------------------------------------*/

/* -------------------------------------------------------------------- */
/* selectOptionsObj constructor											*/ 
/* -------------------------------------------------------------------- */
function selectOptionsObj ()
{
	this.options		= new Array ();

	this.addOption		= selectOptionsObj_addOption;
	this.getOptions		= selectOptionsObj_getOptions;
	this.clean			= selectOptionsObj_clean;

	this.getOptionsOf	= selectOptionsObj_getOptionsOf;
}

/* -------------------------------------------------------------------- */
/* selectOptionsObj_addOption											*/
/* -------------------------------------------------------------------- */
function selectOptionsObj_addOption (value, textHEB, textENG, style)
{
	if (style == undefined) style = "";

	var item = {value 	: value,
			    textHEB	: textHEB,
				textENG : textENG,
				style	: style}

	this.options.push (item);
}

/* -------------------------------------------------------------------- */
/* selectOptionsObj_getOptions											*/
/* -------------------------------------------------------------------- */
function selectOptionsObj_getOptions ()
{
	return this.options;
}

/* -------------------------------------------------------------------- */
/* selectOptionsObj_clean												*/
/* -------------------------------------------------------------------- */
function selectOptionsObj_clean ()
{
	this.options = new Array ();
}

/* -------------------------------------------------------------------- */
/* selectOptionsObj_getOptionsOf										*/
/* -------------------------------------------------------------------- */
function selectOptionsObj_getOptionsOf (type, withEmpty, value)
{
	this.clean ();

	if (withEmpty == undefined)
		withEmpty = true;

	if (withEmpty)
		this.addOption    ("","","");

	switch (type)
	{
		/* ----------------------------------------------------------------------------	*/

		case "orderStatus"	:	
		
//			if (value == "1" || value == "all")
				this.addOption ("1",  "התקבלה",	"");

//			if (value == "1" || value == "2" || value == "all")
				this.addOption ("2",  "סופקה",	"");

//			if (value == "2" || value == "3" || value == "all")
				this.addOption ("3",  "הוחזרה",	"");

			this.addOption ("4",  "בוטלה",	"");
			this.addOption ("5",  "ממתינה לאיסוף",	"");

			break;
		
		/* ----------------------------------------------------------------------------	*/

		case "currency" :
			this.addOption ("ILS",	 "ש\"ח",	"");
			this.addOption ("USD",	 "דולר",	"");

			break;


		default					:
		
			break;
	}

	return this.getOptions ();
}

