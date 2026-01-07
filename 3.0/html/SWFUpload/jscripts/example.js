function $(id) {
	return document.getElementById(id);
}

// Default upload start function.
uploadStart = function(fileObj) {
	
	$("filesDisplay").style.display = "block";
		
	var li = document.createElement("li");
	var txt = document.createTextNode(fileObj.name);

	li.className = "uploading";
	li.id = fileObj.name;
	
	var prg = document.createElement("span");
	prg.id = fileObj.name + "progress";
	prg.className = "progressBar"
	
	li.appendChild(txt);
	li.appendChild(prg);

	$("mmUploadFileListing").appendChild(li);
		
}

uploadProgress = function(fileObj, bytesLoaded) {
	var progress = $(fileObj.name + "progress");
	var percent = Math.ceil((bytesLoaded / fileObj.size) * 100)
	
	progress.style.background = "url(jscripts/SWFUpload/images/progressbar.png) no-repeat -" + (100 - percent) + "px 0";
	
}

uploadComplete = function(fileObj) {
	$(fileObj.name).className = "uploadDone";
	$(fileObj.name).innerHTML += " " + (Math.ceil(fileObj.size / 1000)) + " kb";
}

uploadCancel = function() {
	alert("You pressed cancel!");
}