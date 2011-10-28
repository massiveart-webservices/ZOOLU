/* Demo Note:  This demo uses a FileProgress class that handles the UI for displaying the file name and percent complete.
The FileProgress class is not part of SWFUpload.
*/


/* **********************
   Event Handlers
   These are my custom event handlers to make my
   web application behave the way I went when SWFUpload
   completes different tasks.  These aren't part of the SWFUpload
   package.  They are part of my application.  Without these none
   of the actions SWFUpload makes will show up in my application.
   ********************** */

function swfUploadPreLoad() {
	var self = this;
	var loading = function () {
		//document.getElementById("divSWFUploadUI").style.display = "none";
		document.getElementById("divLoadingContent").style.display = "";

		var longLoad = function () {
			document.getElementById("divLoadingContent").style.display = "none";
			document.getElementById("divLongLoading").style.display = "";
		};
		this.customSettings.loadingTimeout = setTimeout(function () {
				longLoad.call(self)
			},
			15 * 1000
		);
	};
	
	this.customSettings.loadingTimeout = setTimeout(function () {
			loading.call(self);
		},
		1*1000
	);
}
function swfUploadLoaded() {
	var self = this;
	clearTimeout(this.customSettings.loadingTimeout);
	//document.getElementById("divSWFUploadUI").style.visibility = "visible";
	//document.getElementById("divSWFUploadUI").style.display = "block";
	document.getElementById("divLoadingContent").style.display = "none";
	document.getElementById("divLongLoading").style.display = "none";
	document.getElementById("divAlternateContent").style.display = "none";
	
	//document.getElementById("btnBrowse").onclick = function () { self.selectFiles(); };
	document.getElementById("btnCancel").onclick = function () { self.cancelQueue(); };
}
   
function swfUploadLoadFailed() {
	clearTimeout(this.customSettings.loadingTimeout);
	//document.getElementById("divSWFUploadUI").style.display = "none";
	document.getElementById("divLoadingContent").style.display = "none";
	document.getElementById("divLongLoading").style.display = "none";
	document.getElementById("divAlternateContent").style.display = "";
}
   
   
function fileQueued(file) {
	try {
		var progress = new FileProgress(file, this.customSettings.progressTarget);
		progress.setStatus("Pending...");
		progress.toggleCancel(true, this);

	} catch (ex) {
		this.debug(ex);
	}
}

function fileQueueError(file, errorCode, message) {
	try {
		if (errorCode === SWFUpload.QUEUE_ERROR.QUEUE_LIMIT_EXCEEDED) {
			alert("You have attempted to queue too many files.\n" + (message === 0 ? "You have reached the upload limit." : "You may select " + (message > 1 ? "up to " + message + " files." : "one file.")));
			return;
		}

		var progress = new FileProgress(file, this.customSettings.progressTarget);
		progress.setError();
		progress.toggleCancel(false);

		switch (errorCode) {
		case SWFUpload.QUEUE_ERROR.FILE_EXCEEDS_SIZE_LIMIT:
			progress.setStatus("File is too big.");
			this.debug("Error Code: File too big, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			break;
		case SWFUpload.QUEUE_ERROR.ZERO_BYTE_FILE:
			progress.setStatus("Cannot upload Zero Byte files.");
			this.debug("Error Code: Zero byte file, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			break;
		case SWFUpload.QUEUE_ERROR.INVALID_FILETYPE:
			progress.setStatus("Invalid File Type.");
			this.debug("Error Code: Invalid File Type, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			break;
		default:
			if (file !== null) {
				progress.setStatus("Unhandled Error");
			}
			this.debug("Error Code: " + errorCode + ", File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			break;
		}
	} catch (ex) {
	  this.debug(ex);
	}
}

function fileDialogComplete(numFilesSelected, numFilesQueued) {
	try {
		if (numFilesSelected > 0) {
			document.getElementById(this.customSettings.cancelButtonId).disabled = false;
		}
		
		/* I want auto start the upload and I can do that here */
		this.startUpload();
	} catch (ex)  {
	  this.debug(ex);
	}
}

function uploadStart(file) {
	try {
		/* I don't want to do any file validation or anything,  I'll just update the UI and
		return true to indicate that the upload should start.
		It's important to update the UI here because in Linux no uploadProgress events are called. The best
		we can do is say we are uploading.
		 */
		var progress = new FileProgress(file, this.customSettings.progressTarget);
		progress.setStatus("Uploading...");
		progress.toggleCancel(true, this);
	}
	catch (ex) {}
	
	return true;
}

function uploadProgress(file, bytesLoaded, bytesTotal) {
	try {
		var percent = Math.ceil((bytesLoaded / bytesTotal) * 100);

		var progress = new FileProgress(file, this.customSettings.progressTarget);
		progress.setProgress(percent, "progressContainer green");
		progress.setStatus("Uploading...");
	} catch (ex) {
		this.debug(ex);
	}
}

function uploadSuccess(file, serverData) {
	try {
		var progress = new FileProgress(file, this.customSettings.progressTarget);
		progress.setComplete();
    if($(progress.fileProgressID)) $(progress.fileProgressID).update(serverData);
		//progress.setStatus("Complete.");
		//progress.toggleCancel(false);

	} catch (ex) {
		this.debug(ex);
	}
}

function uploadError(file, errorCode, message) {
	try {
		var progress = new FileProgress(file, this.customSettings.progressTarget);
		progress.setError();
		progress.toggleCancel(false);

		switch (errorCode) {
		case SWFUpload.UPLOAD_ERROR.HTTP_ERROR:
			progress.setStatus("Upload Error: " + message);
			this.debug("Error Code: HTTP Error, File name: " + file.name + ", Message: " + message);
			break;
		case SWFUpload.UPLOAD_ERROR.UPLOAD_FAILED:
			progress.setStatus("Upload Failed.");
			this.debug("Error Code: Upload Failed, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			break;
		case SWFUpload.UPLOAD_ERROR.IO_ERROR:
			progress.setStatus("Server (IO) Error");
			this.debug("Error Code: IO Error, File name: " + file.name + ", Message: " + message);
			break;
		case SWFUpload.UPLOAD_ERROR.SECURITY_ERROR:
			progress.setStatus("Security Error");
			this.debug("Error Code: Security Error, File name: " + file.name + ", Message: " + message);
			break;
		case SWFUpload.UPLOAD_ERROR.UPLOAD_LIMIT_EXCEEDED:
			progress.setStatus("Upload limit exceeded.");
			this.debug("Error Code: Upload Limit Exceeded, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			break;
		case SWFUpload.UPLOAD_ERROR.FILE_VALIDATION_FAILED:
			progress.setStatus("Failed Validation.  Upload skipped.");
			this.debug("Error Code: File Validation Failed, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			break;
		case SWFUpload.UPLOAD_ERROR.FILE_CANCELLED:
			// If there aren't any files left (they were all cancelled) disable the cancel button
			if (this.getStats().files_queued === 0) {
				document.getElementById(this.customSettings.cancelButtonId).disabled = true;
			}
			progress.setStatus("Cancelled");
			progress.setCancelled();
			break;
		case SWFUpload.UPLOAD_ERROR.UPLOAD_STOPPED:
			progress.setStatus("Stopped");
			break;
		default:
			progress.setStatus("Unhandled Error: " + errorCode);
			this.debug("Error Code: " + errorCode + ", File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
			break;
		}
	} catch (ex) {
        this.debug(ex);
    }
}

function uploadComplete(file) {
	if (this.getStats().files_queued === 0) {
		document.getElementById(this.customSettings.cancelButtonId).disabled = true;
	}
}

// This event comes from the Queue Plugin
function queueComplete(numFilesUploaded) {
	var status = document.getElementById("divStatus");
	status.innerHTML = numFilesUploaded + " file" + (numFilesUploaded === 1 ? "" : "s") + " uploaded.";
}


/*------------------------------*
 * single swf upload handlers   *
 *------------------------------*/

function singleSWFUploadLoaded() {
  $('btnSingleEditSubmit').onclick = doSubmit;  
}

// Called by the submit button to start the upload
function doSubmit(e) {    
  e = e || window.event;
  if (e.stopPropagation) {
    e.stopPropagation();
  }
  e.cancelBubble = true;
  
  try {
    var stats = swfu.getStats();
    if(stats.files_queued > 0){
      swfu.startUpload();
    }else{
      myMedia.editFiles(true);
    }
  } catch (ex) { }
  
  return false;
}

 // Called by the queue complete handler to submit the form
function singleUploadDone() {
  try {
    myMedia.editFiles(true);
  } catch (ex) {
    alert("Error submitting form");
  }
}

function singleFileDialogStart() {
  $('txtFileName').value = '';
  this.cancelUpload();
}

function singleFileQueueError(file, errorCode, message)  {
  try {
    // Handle this error separately because we don't want to create a FileProgress element for it.
    switch (errorCode) {
    case SWFUpload.QUEUE_ERROR.QUEUE_LIMIT_EXCEEDED:
      alert("You have attempted to queue too many files.\n" + (message === 0 ? "You have reached the upload limit." : "You may select " + (message > 1 ? "up to " + message + " files." : "one file.")));
      return;
    case SWFUpload.QUEUE_ERROR.FILE_EXCEEDS_SIZE_LIMIT:
      alert("The file you selected is too big.");
      this.debug("Error Code: File too big, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
      return;
    case SWFUpload.QUEUE_ERROR.ZERO_BYTE_FILE:
      alert("The file you selected is empty.  Please select another file.");
      this.debug("Error Code: Zero byte file, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
      return;
    case SWFUpload.QUEUE_ERROR.INVALID_FILETYPE:
      alert("The file you choose is not an allowed file type.");
      this.debug("Error Code: Invalid File Type, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
      return;
    default:
      alert("An error occurred in the upload. Try again later.");
      this.debug("Error Code: " + errorCode + ", File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
      return;
    }
  } catch (e) { }
}

function singleFileQueued(file) {
  try {
    var txtFileName = document.getElementById("txtFileName");
    txtFileName.value = file.name;
  } catch (e) { }
}

function singleFileDialogComplete(numFilesSelected, numFilesQueued) { }

function singleUploadProgress(file, bytesLoaded, bytesTotal) {
  try {
    var percent = Math.ceil((bytesLoaded / bytesTotal) * 100);

    file.id = "singlefile"; // This makes it so FileProgress only makes a single UI element, instead of one for each file
    var progress = new FileProgress(file, this.customSettings.progress_target);
    progress.setProgress(percent, "progressContainer");
    progress.setStatus("Uploading...");
  } catch (e) { }
}

function singleUploadSuccess(file, serverData) {
  try {
    file.id = "singlefile"; // This makes it so FileProgress only makes a single UI element, instead of one for each file
    var progress = new FileProgress(file, this.customSettings.progress_target);
    progress.setComplete();
    progress.setStatus("Complete.");
    progress.toggleCancel(false);
    
    if (serverData === " ") {
      this.customSettings.upload_successful = false;
    } else {
      this.customSettings.upload_successful = true;
    }
  } catch (e) { }
}

function singleUploadComplete(file) {
  try {
    if (this.customSettings.upload_successful) {
      //this.setButtonDisabled(true);
      singleUploadDone();
    } else {
      file.id = "singlefile"; // This makes it so FileProgress only makes a single UI element, instead of one for each file
      var progress = new FileProgress(file, this.customSettings.progress_target);
      progress.setError();
      progress.setStatus("File rejected");
      progress.toggleCancel(false);
      
      var txtFileName = document.getElementById("txtFileName");
      txtFileName.value = "";
      
      alert("There was a problem with the upload.\nThe server did not accept it.");
    }
  } catch (e) { }
}

function singleUploadError(file, errorCode, message) {
  try {
    
    if (errorCode === SWFUpload.UPLOAD_ERROR.FILE_CANCELLED) {
      // Don't show cancelled error boxes
      return;
    }
    
    var txtFileName = document.getElementById("txtFileName");
    txtFileName.value = "";
    validateForm();
    
    // Handle this error separately because we don't want to create a FileProgress element for it.
    switch (errorCode) {
    case SWFUpload.UPLOAD_ERROR.MISSING_UPLOAD_URL:
      alert("There was a configuration error.  You will not be able to upload a resume at this time.");
      this.debug("Error Code: No backend file, File name: " + file.name + ", Message: " + message);
      return;
    case SWFUpload.UPLOAD_ERROR.UPLOAD_LIMIT_EXCEEDED:
      alert("You may only upload 1 file.");
      this.debug("Error Code: Upload Limit Exceeded, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
      return;
    case SWFUpload.UPLOAD_ERROR.FILE_CANCELLED:
    case SWFUpload.UPLOAD_ERROR.UPLOAD_STOPPED:
      break;
    default:
      alert("An error occurred in the upload. Try again later.");
      this.debug("Error Code: " + errorCode + ", File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
      return;
    }

    file.id = "singlefile"; // This makes it so FileProgress only makes a single UI element, instead of one for each file
    var progress = new FileProgress(file, this.customSettings.progress_target);
    progress.setError();
    progress.toggleCancel(false);

    switch (errorCode) {
    case SWFUpload.UPLOAD_ERROR.HTTP_ERROR:
      progress.setStatus("Upload Error");
      this.debug("Error Code: HTTP Error, File name: " + file.name + ", Message: " + message);
      break;
    case SWFUpload.UPLOAD_ERROR.UPLOAD_FAILED:
      progress.setStatus("Upload Failed.");
      this.debug("Error Code: Upload Failed, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
      break;
    case SWFUpload.UPLOAD_ERROR.IO_ERROR:
      progress.setStatus("Server (IO) Error");
      this.debug("Error Code: IO Error, File name: " + file.name + ", Message: " + message);
      break;
    case SWFUpload.UPLOAD_ERROR.SECURITY_ERROR:
      progress.setStatus("Security Error");
      this.debug("Error Code: Security Error, File name: " + file.name + ", Message: " + message);
      break;
    case SWFUpload.UPLOAD_ERROR.FILE_CANCELLED:
      progress.setStatus("Upload Cancelled");
      this.debug("Error Code: Upload Cancelled, File name: " + file.name + ", Message: " + message);
      break;
    case SWFUpload.UPLOAD_ERROR.UPLOAD_STOPPED:
      progress.setStatus("Upload Stopped");
      this.debug("Error Code: Upload Stopped, File name: " + file.name + ", Message: " + message);
      break;
    }
  } catch (ex) { }
}