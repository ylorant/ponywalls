function toggleLoginDialog()
{
	$('#registerBox').slideUp(400);
	$('#loginBox').slideToggle(400);
}

function toggleRegisterDialog()
{
	$('#loginBox').slideUp(400);
	$('#registerBox').slideToggle(400);
}

function closeDialog(type)
{
	$('#dialog_'+type).fadeOut(400);
	$('#shadow').fadeOut(400);
}

function showUploadDialog()
{
	$('#imguploaddialog').fadeIn(400);
	$('#shadow').fadeIn(400);
}

function hideUploadDialog()
{
	$('#imguploaddialog').fadeOut(400);
	$('#shadow').fadeOut(400);
}

function toggleUploadWait()
{
	$('#uploadform').fadeOut(200, function() {
		$('#uploadwait').fadeIn(200);
	});
}
