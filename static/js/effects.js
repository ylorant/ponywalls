ponywalls.toggleLoginDialog = function()
{
	$('#registerBox').slideUp(400);
	$('#loginBox').slideToggle(400);
}

ponywalls.toggleRegisterDialog = function()
{
	$('#loginBox').slideUp(400);
	$('#registerBox').slideToggle(400);
}

ponywalls.closeDialog = function(type)
{
	$('#dialog_'+type).fadeOut(400);
	$('#shadow').fadeOut(400);
}

ponywalls.showUploadDialog = function()
{
	$('#imguploaddialog').fadeIn(400);
	$('#shadow').fadeIn(400);
}

ponywalls.hideUploadDialog = function()
{
	$('#imguploaddialog').fadeOut(400);
	$('#shadow').fadeOut(400);
}

ponywalls.toggleUploadWait = function()
{
	$('#uploadform').fadeOut(200, function() {
		$('#uploadwait').fadeIn(200);
	});
}

ponywalls.toggleUploadForm = function(callback)
{
	$('#uploadwait').hide();
	$('#uploadform').show();
}

ponywalls.updateWallSize = function()
{
	$('#view').css('height', window.innerHeight - 120);
}

ponywalls.toggleData = function()
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

ponywalls.toggleImageData = function()
{
	$('.contextzone').fadeToggle(400);
	if($('.contextzone').css('opacity') == '0')
		$('#view').animate( {width: '75%',right: '2%'}, 400);
	else
		$('#view').animate({width: '80%',right: '10%'}, 400);
}

ponywalls.editImageInfo = function()
{
	var image = $('#infoEditButton img');
	image.attr('src', image.attr('src').replace('edit.png', 'tick.png'));
	
	$('#infoEditButton').text(' Update');
	$('#infoEditButton').prepend(image);
	$('#infoEditButton').removeClass('twilightsparkle');
	$('#infoEditButton').addClass('applefritter');
	$('#infoEditButton').attr('onclick', 'ponywalls.submitImageInfo();');
	$('#dataShow').fadeOut(400, function() {
		$('#editDataForm').fadeIn(400);
	});
}
