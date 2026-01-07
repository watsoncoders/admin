<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" 
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" >
<head>

    <title>Flash upload</title>
	<script type="text/javascript" src="jscripts/SWFUpload/mmSWFUpload.js"></script>
	<script type="text/javascript" src="jscripts/example.js"></script>
	
	<style type="text/css">
		
		body, pre {
			font-family: "Trebuchet MS", Arial, Sans-serif;
		}

		h1 {
			background: url(images/common/big_logo.png) no-repeat;
			width: 458px;
			height: 206px;
		}

		h1 span {
			display: none;
		}

		h2 {
			font-size: 20px;
			font-weight: normal;
		}

		p, li, pre {
			font-size: 14px;
		}

		pre {
			color: #666666;
		}

		li {
			cl2ear: both;
		}

		#wrapper {
			width: 500px;
			margin: 0 auto;
		}
		
		#content {
			padding: 0 0 0 20px;
		}
		
		.clr {
			clear: both;
		}
		
		/* SWFUpload CSS */
		
		#filesDisplay {
			padding: 10px;
			margin-top: 20px;
			background: #f9f9f9;
			border: 1px solid #f3f3f3;
			display: none;
		}
		
		#SWFUpload {
			margin-left: 20px;
		}
		
		#mmUploadFileListing {
			margin: 0;
			padding: 0;
		}
		
		#mmUploadFileListing li {
			margin: 0 0 10px 0;
			display: block;
			float: left;
			width: 150px;
			list-style-type: none;
			font-size: 11px;
		}
		
		.uploading { color: #CCC; }
		.uploadDone { color: #000; }
		
		span.progressBar {
			width: 100px;
			b2ackground: #000;
			display: block;
			font-size: 10px;
			height: 4px;
			margin-top: 4px;
		}

	</style>
	
</head>
<body>

	<div id="wrapper">
	
		<h1><span>mmSWFUpload 0.5</span></h1>
		
		<div id="content">
		
			<h2>What is it?</h2>
			
			<p><strong>Upload files via flash to get the flash-upload dialog goodness.</strong></p>
				
			<ul>
				<li>Only display chosen filetypes in dialog</li>
				<li>Upload multiple files at once</li>
				<li>Trigger javascript functions on start, cancel, progress and complete</li>
				<li>Get file information/size before upload starts</li>
				<li>Style upload buttons any way you want</li>
				<li>Do progress bars/information using valid XHTML and CSS</li>
				<li>Degrades gracefully to a normal html upload form</li>
			</ul>
			

			<div id="SWFUpload">You need a newer version of flash</div>
			
			<script type="text/javascript">

				mmSWFUpload.init({
					// debug : true,
					upload_backend : "../../upload.php",
					button_image : "images/custom_button.png",
					button_mouseover_image : "images/custom_button_over.png",
					width : "258px",
					height : "82px",
					target : "SWFUpload",
					allowed_filetypes : "*.gif;*.jpg;*.png",
					upload_start_callback : 'uploadStart',
					upload_progress_callback : 'uploadProgress',
					upload_complete_callback : 'uploadComplete',
					// upload_error_callback : 'uploadError',
					upload_cancel_callback : 'uploadCancel'
				});
			
				
			</script>
			
			<div id="filesDisplay">
				<ul id="mmUploadFileListing"></ul>
				<br class="clr" />
			</div>
		
			<br />
			
			<h2>How do i use it?</h2>
			<p><strong>It's very simple!</strong><br />
				First, download the files: <a href="src/SWFUpload10.rar">SWFUpload10.rar</a>
				<br /><br />
				
				If you look at the simple example you will notice that all you need to do
				to use SWFUpload is to specify your backend-upload script and a target div
				where the button should be.<br /><br />
				In the package you will find a few default images etc. for SWFUpload, these
				images will be used if you don't specfiy your own. You could of course just
				replace these images as well. Remember that if you specify your own image(s)
				you have to set the width and height of the button as well.
			</p>
			<p>


<pre>

<strong>Simple example:</strong>
&lt;script type="text/javascript"&gt;

	mmSWFUpload.init({
		upload_backend : "../../upload.php",
		target : "SWFUpload",
	});

&lt;/script&gt;

<strong>Full featured example:</strong>
&lt;script type="text/javascript"&gt;

	mmSWFUpload.init({
		upload_backend : "../../upload.php",
		button_image : "images/custom_button.png",
		button_mouseover_image : "images/custom_button_over.png",
		width : "258px",
		height : "82px",
		target : "SWFUpload",
		allowed_filetypes : "*.gif;*.jpg;*.png",
		upload_start_callback : 'uploadStart',
		upload_progress_callback : 'uploadProgress',
		upload_complete_callback : 'uploadComplete',
		upload_error_callback : 'uploadError',
		upload_cancel_callback : 'uploadCancel'
	});

&lt;/script&gt;
</pre>
			
			
			</p>
			<p>
				Most of the configurations are optional, but if you want to do something more advanced
				you probably want the flash to call back to some javascript and present the information.
			</p>
			<p>
				all the callbacks will return an image object, so you will only need one argument
				in your javascript callback functions. The object always contains the file name,
				the file size and the file type.
				<br /><br />
				The object looks like this:<br />
				fileObj.name = filename (filename.png)<br />
				fileObj.size = filesize (192912)<br />
				fileObj.type = filetype (.png)<br /><br />
			
				upload_progress_callback also gets reports on bytes loaded.
				<br /><br />
				Please see the provided example.js for more info and more advanced examples.
			</p>
			
			<p>
<pre>
<strong>Some simple callbacks:</strong>

uploadStart = function(fileObj) {
	var container = document.getElementById("fileContainer");
	container.innerHTML += "&lt;span id='" + fileObj.name + "' &gt;";
	container.innerHTML += fileObj.name + ", ";
	container.innerHTML += fileObj.size + ", ";
	container.innerHTML += fileObj.type + "&lt;/span&gt;&lt;br /&gt;";
}

uploadProgress = function(fileObj, bytesLoaded) {
	var pie = document.getElementById("progressInfoElm");
	var proc = Math.ceil((bytesLoaded / fileObj.size) * 100)
	pie.innerHTML = p + " %";
}

uploadComplete = function(fileObj) {
	document.getElementById(fileObj.name).className = "uploadDone";
	document.getElementById(fileObj.name).innerHTML = objFile.name + " done!";
}

</pre>
			</p>
			
			<p>
				The other callbacks works on the same principle.<br />
				<em>Please note: above scrips are fairly untested...</em>
				Just do a view-source on the javascript for this page for more info on how to work
				with the callbacks.
			</p>
			<h2>Error handling</h2>
			<p>
				There is a bit of simple error handling built-in, but this can be expanded by using the 
				upload_error_callback. The SWF will return a few different error codes depending on 
				what goes wrong. Here is a short explanation:</p>
				
				<ul>
					<li>-10: HTTP error, also returns the http error code (404 etc)</li>
					<li>-20: Custom error code if no backend file is specified</li>
					<li>-30: IO-error</li>
					<li>-40: Security error</li>
				</ul>
			
			<p>
			
				The error always contains one of these codes and the file object that caused the error,
				some, like the http error also contains a message (the http error code).
							
			</p>
			<p>
				A big thanks to Geoff Stearns for his SWFObject, without that this litte hack
				wouldn't have been half as good. Check it out here: <a href="http://blog.deconcept.com/swfobject/">blog.deconcept.com/swfobject</a>
			</p>
			<p>
				SWFUpload is (c) 2006 Lars Huring and Mammon Media and is released under the MIT License:
				<a href="http://www.opensource.org/licenses/mit-license.php">http://www.opensource.org/licenses/mit-license.php</a>
			</p>
		</div>
		
	</div>
	
</body>
</html>
