function $id(id) {
	return document.getElementById(id);
}
//
// output information
function Output(msg) {
	var m = $id("imguploaddialog");
	m.innerHTML = msg + m.innerHTML;
}

// call initialization file
if (window.File && window.FileList && window.FileReader) {
	Init();
}
//
// initialize
function Init() {
	var fileselect = $id("uploadimg"),
		filedrag = $id("body"),
		submitbutton = $id("submitupload");
	// file select
	fileselect.addEventListener("change", FileSelectHandler, false);
	// is XHR2 available?
	var xhr = new XMLHttpRequest();
	if (xhr.upload) {
		// file drop
		filedrag.addEventListener("dragover", FileDragHover, false);
		filedrag.addEventListener("dragleave", FileDragHover, false);
		filedrag.addEventListener("drop", FileSelectHandler, false);
		//filedrag.style.display = "block";
		// remove submit button
		//submitbutton.style.display = "none";
	}
}

function FileDragHover(e) { 
	ponywalls.showUploadDialog();
    e.stopPropagation();  
    e.preventDefault();  
    //e.target.className = (e.type == "dragover" ? "hover" : "");  
}  

function FileSelectHandler(e) {  
    // cancel event and hover styling  
    FileDragHover(e);  
    // fetch FileList object  
    var files = e.target.files || e.dataTransfer.files;
    // process all File objects  
 	t = prompt("Type tags for "+files[0].name);
 	ponywalls.toggleUploadWait();
	UploadFile(files[0], t);
}  

// upload JPEG files
function UploadFile(file, tags)
{
	var xhr = new XMLHttpRequest();
	if (xhr.upload)
	{
		xhr.open("POST", "wallpapers/add_ajax/"+tags, true);  
		xhr.setRequestHeader("X_FILENAME", file.name);
		  
		xhr.onreadystatechange = function()
		{
		    if (xhr.readyState != 4)
				return;
			else
			{
				var data = JSON.parse(xhr.responseText);
				debug.addTab(data.debug);
				ponywalls.dialog({ type: data.dialog[0], message: data.dialog[1] });
				setTimeout("ponywalls.toggleUploadForm(); ponywalls.hideUploadDialog();", 100);
			}
		};
		xhr.send(file);
	}
} 
