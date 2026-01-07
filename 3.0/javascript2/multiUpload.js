var currFormId;
var adds = new Array();

var ERROR_WAIT_LOADING = -1;

function multiUploadStart (uploadBackEnd, theFormId)
{
	if (uploadBackEnd == undefined)
		uploadBackEnd = "http://www.i-bos.co.il/3.0/php/onlineHtml5UploadFile.php";

	if (uploadBackEnd.indexOf("?") == -1)
		uploadBackEnd += "?";
	else
		uploadBackEnd += "&";

	uploadBackEnd += "sessionCode=" + commonGetGlobalData("sessionCode") + "&userId=" + commonGetGlobalData("userId");

	if (commonGetGlobalData("guiLang") == "ENG")
	{
		uploadBtn = "Browse";
	}
	else
	{
		uploadBtn = "עיון";
	}

	var flashUrl  = "http://www.i-bos.co.il/3.0/html/SWFUpload/swfupload.swf";
	var fileTypes = "*.gif;*.jpg;*.png;*.swf;*.avi;*.wmv;*.bmp;*.doc;*.pdf;*.xls;*.xlsx;*.ppt;*.pptx;*.pps;*.docx;*.csv;*.html;*.htm;*.txt;*.xml;*.mp4;*.mp3;*.rar;*.zip";
	var sizeLimit = "30 MB";

	if (theFormId == undefined)
		theFormId = formId;

	currFormId = theFormId;

	currFormName = pageObj.getFormName(currFormId);
	
	var formObj = pageObj.getFormObj (currFormId);

	ind = 0;

	for (c = 0; c < formObj.onlineUploadFrames.length; c++)
	{
		var fldUniqueName = currFormName + formObj.onlineUploadFields[c];
		$("#SWFUploadHolder" + fldUniqueName).html(
		  '<input type="file" name="filesToUpload'+fldUniqueName+'" id="filesToUpload'+fldUniqueName+'" form="NotInThisForm" style="display:none" />' +
          '<input type="button" class="uploadBtn" value="'+uploadBtn+'..." onclick="$(\'#filesToUpload'+fldUniqueName+'\').click();" />');

		$("#filesToUpload"+fldUniqueName).on("change", "", uploadBackEnd, fileSelect);

		adds[ind] = formObj.onlineUploadFields[c];
		ind++;
	}

	ind = 10;

	if (formObj.multiUploadFrame != -1)
	{
		$("#SWFUploadHolder" + currFormName).html(
	'<input type="file" name="filesToUpload'+currFormName+'" id="filesToUpload'+currFormName+'" form="NotInThisForm" style="display:none" multiple="multiple" />' +
    '<input type="button" class="uploadBtn" value="'+uploadBtn+'..." onclick="$(\'#filesToUpload'+currFormName+'\').click();" />');

		$("#filesToUpload"+currFormName).on("change", "", uploadBackEnd, fileSelect);
	
		adds[10] = "";
	}

	return true;
}

var uploadedFilesIndex = 0;
var arrivedFilesCount = 0;
function fileSelect(e)
{
		if (!(window.File && window.FileReader && window.FileList && window.Blob))
		{
			commonMsgBox ("info", "דפדפן זה אינו תומך בהעלאת תמונות", "This browser does not support file uploading");
			return;
		}	
	
		var files = e.target.files;
 
	    var file;
		for (var i = 0; file = files[i]; i++)
		{
            reader = new FileReader();
            reader.onload = (function (tFile) {
                return function (evt) {
				        var xhr = new XMLHttpRequest();
						xhr.onreadystatechange = function(ev)
						{
							if (this.readyState == 4 && this.status == 200)
							{
									var res = this.responseText.split("|");
									arrivedFilesCount++;

									var uniqueName = res[0].replace('filesToUpload', '');
									if (formObj.multiUploadFrame != -1)
									{
										$("li#uploadedFileNo"+this.fileIndex).html(res[1]);
									}
									else // single file
									{
										$("#uploadDone" + uniqueName).val(1);
										$("#uploadFileName" + uniqueName).css({'text-align':'left','color':'black'}).val(res[1]);
									}

									if (uniqueName == "form2fileToSet")
									{
										$("#form2_url").val ("loadedFiles/" + res[1]);
									}
							}
				        };
						xhr.upload.fld = e.target.name.replace('filesToUpload', '');
						xhr.upload.onprogress = function (ev)
						{
 							var percentage = Math.round(ev.loaded / ev.total * 100);
							if (formObj.multiUploadFrame != -1)
							{
								$("li#uploadedFileNo"+this.fileIndex).html(percentage+'%');
							}
							else // single file
								$("#uploadFileName" + this.fld).css({'text-align':'center','color':'green'}).val(percentage+'%');
						};

						this.fileIndex = uploadedFilesIndex++;
						xhr.fileIndex = this.fileIndex;
						xhr.upload.fileIndex = this.fileIndex;

 						if (formObj.multiUploadFrame != -1)
						{
							$("ul#mmUploadFileListing" + e.target.name.replace('filesToUpload', '')).append('<li id="uploadedFileNo'+this.fileIndex+'">0%</li>');
						}

				        var data = "fld=" + e.target.name + "&name=" + encodeURIComponent(tFile.name) + "&data=" + encodeURIComponent(evt.target.result);
						xhr.open("POST", e.data, true);
				        xhr.setRequestHeader("Content-type","application/x-www-form-urlencoded");
				        xhr.send(data);
				}
            }(file));
            reader.readAsDataURL(file);
		} // for
} // fileSelect


/* ------------------------------------------------------------------------------------------------------------	*/
/* uploadGetFileName																							*/
/* ------------------------------------------------------------------------------------------------------------	*/
function uploadGetFileName (ind)
{
	if (ind == undefined)
		ind = 0;

	currFormName = pageObj.getFormName(currFormId);

	if (uploadedFilesIndex > arrivedFilesCount)
	{
		commonMsgBox ("info", "נא להמתין לסיום טעינת הקבצים", "Please wait... loading files");
		return ERROR_WAIT_LOADING;
	}

	return $("#uploadFileName" + currFormName + adds[ind]).val();
}
