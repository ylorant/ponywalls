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

function updateWallSize()
{
	$('#view').css('height', window.innerHeight - 120);
}

function toggleData()
{
	if($('#data p').css('display') == 'none')
	{
		$('#data').slideToggle(400);
		setTimeout("$('#data p').fadeToggle(200);$('#data p').css('display', 'inline');", 200);
	}
	else
	{
		$('#data').slideToggle(400);
		$('#data p').fadeToggle(200);
	}
}

function editImageInfo()
{
	var image = $('#infoEditButton img');
	image.attr('src', image.attr('src').replace('edit.png', 'tick.png'));
	$('#infoEditButton').text(' Update');
	$('#infoEditButton').prepend(image);
	$('#infoEditButton').removeClass('twilightsparkle');
	$('#infoEditButton').addClass('applefritter');
	$('#dataShow').fadeOut(400, function() {
		$('#editDataForm').fadeIn(400);
	});
}
