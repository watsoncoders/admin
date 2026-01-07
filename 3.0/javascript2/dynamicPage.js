/* ---------------------------------------------------------------- */
/*																	*/
/* 							dynamicPage.js							*/
/*							--------------							*/
/*																	*/
/* ---------------------------------------------------------------- */

/*
if (top.globalFrame != undefined && top.globalFrame.siteUrl == "http://www.time2focus.com")
includeFiles = new Array ("general.js",
						  "xmlObj.js",
						  "serverInterfaceObj.js",
						  "common.js", 
						  "help.js",
						  "colors.js",
						  "colorPicker.js",
						  "selectOptionsObj.js",
						  "serverOptions.js",
						  "dynamicTableObj.js", 
						  "dynamicReportObj.js", 
						  "dynamicFormObj.js", 
						  "dynamicPageObj.js",
						  "swfupload.js",
						  "swfupload.queue.js",
						  "tinymce_3.4.5.nightly/tiny_mce.js",
						  "imglib/css/imglib_tiny_manager.js");
else /* previous version */
includeFiles = new Array ("https://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js",
						  "../alertify/alertify.min.js",
						  "../fancybox/jquery.fancybox-1.3.4.js?v=7",
						  "../datepicker/jquery-ui-1.10.3.custom.min.js",
						  "../datepicker/jquery.ui.datepicker-he.min.js",
						  "stupidtable.min.js",
						  "general.js",
						  "xmlObj.js?v=8",
						  "serverInterfaceObj.js",
						  "common.js?v=1", 
						  "help.js",
						  "colors.js",
						  "colorPicker.js",
						  "selectOptionsObj.js?v=2",
						  "serverOptions.js?v=1",
						  "dynamicTableObj.js", 
						  "dynamicReportObj.js", 
						  "dynamicFormObj.js?v=11", 
						  "dynamicPageObj.js",
  						  "jquery.PrintArea.js",
						  "swfupload.js",
						  "swfupload.queue.js",
						  "tinymce-4.6.4/tinymce.min.js?v=1");
// emoji. need utf8mb4	  "tinymce-4.6.4/plugins/tinymce-emoji/plugin.min.js");
/*						  "tinymce_3.4.5.nightly.26/tiny_mce.js",
						  "imglib/css/imglib_tiny_manager.js");
*/

document.write ("<link rel='stylesheet' href='../../css/common.css' type='text/css'/>");
document.write ("<link rel='stylesheet' href='../../css/upload.css' type='text/css'/>");
document.write ("<link rel='stylesheet' href='../../css/print.css'  type='text/css'/>");
document.write ("<link rel='stylesheet' href='../../css/colorPicker.css' type='text/css'/>");
document.write ("<link rel='stylesheet' href='../../datepicker/jquery-ui-1.10.3.custom.css' type='text/css'/>");
document.write ("<link rel='stylesheet' href='../../fancybox/jquery.fancybox-1.3.4.css' type='text/css'/>");
document.write ("<link rel='stylesheet' href='../../alertify/alertify.core.css' type='text/css'/>");
document.write ("<link rel='stylesheet' href='../../alertify/alertify.default.css' type='text/css'/>");
document.write ("<link rel='stylesheet' href='../../css/font-awesome.min.css' type='text/css'/>");

for (i=0; i<includeFiles.length; i++)
{
	src = includeFiles[i];

	if (i != 0)
		src = "../../javascript2/" + src;

	includeJS = "<script language='JavaScript' "	+
			    "		 src='" + src + "'></script>"

	document.write(includeJS);
}

if (window.top.jQuery('#globalData').data("guiLang") == "HEB")
	document.write ("<link rel='stylesheet' href='../../css/HEB.css' type='text/css'/>");
else
	document.write ("<link rel='stylesheet' href='../../css/ENG.css' type='text/css'/>");

var addCss = window.top.jQuery('#globalData').data("privateCss");
if (addCss != "###empty###")
	document.write ("<link rel='stylesheet' href='../../css/" + addCss + "' type='text/css'/>");

if (window.top.jQuery.browser.msie)
{
	document.write ("<link rel='stylesheet' href='../../css/ie.css' type='text/css'/>");
}

function showError (package, method, error)
{
	alert ("Package : " + package 		+ "\n" +
		   "Method  : " + method  		+ "\n" +
		   "Error      : " + error.description);
		   
		   //"Number  : " + error.number  + "\n" + 
}
