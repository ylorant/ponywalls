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
	if($('.contextzone p').css('display') == 'none')
	{
		$('.contextzone').slideToggle(400);
		setTimeout("$('.contextzone p').fadeToggle(200);$('.contextzone p').css('display', 'inline');", 200);
	}
	else
	{
		$('.contextzone').slideToggle(400);
		$('.contextzone p').fadeToggle(200);
	}
}

function toggleImageData()
{
	$('.contextzone').fadeToggle(400);
	if($('.contextzone').css('opacity') == '0')
		$('#view').animate( {width: '75%',right: '2%'}, 400);
	else
		$('#view').animate({width: '80%',right: '10%'}, 400);
}

function editImageInfo()
{
	var image = $('#infoEditButton img');
	image.attr('src', image.attr('src').replace('edit.png', 'tick.png'));
	$('#infoEditButton').text(' Update');
	$('#infoEditButton').prepend(image);
	$('#infoEditButton').removeClass('twilightsparkle');
	$('#infoEditButton').addClass('applefritter');
	$('#infoEditButton').attr('onclick', 'submitImageInfo();');
	$('#dataShow').fadeOut(400, function() {
		$('#editDataForm').fadeIn(400);
	});
}

function submitImageInfo()
{
	$('#dataEditForm').submit();
}
