var ponywalls = {};

ponywalls.promptDefaultOptions =
{
	confirm:
	{
		type: "confirm",
		buttons: {"Close": "$('#dialog_confirm').trigger('close')"},
		title: "I brought you a letter !",
		header: "According to myself, the message is:",
		message: "Empty."
	},
	error:
	{
		type: "error",
		buttons: {"Close": "$('#dialog_error').trigger('close')"},
		title: "Derp, an error occured",
		header: "According to Derpy Hooves, the error is:",
		message: "Unknown."
	}
};

ponywalls.submitImageInfo = function()
{
	$('#dataEditForm').submit();
}

ponywalls.yay = function(id, button)
{
	$.ajax({
		type: "POST",
		url: BASE_URL + "wallpapers/yay/" + id,
		data: "key=" + $(button).attr("data-key"),
		dataType: "json"
	})
	.done(
		function(data)
		{
			debug.addTab(data.debug);
			if(data.result == true)
			{
				$(button).html("Hush");
				$(button).attr("data-key", data.key)
				$(button).removeClass('rainbowdash');
				$(button).addClass('bigmac');
				$(button).attr('onclick', 'ponywalls.hush('+ id +', this);');
				$("#yay-score").html(data.score);
			}
			else
				alert(data.message);
		}
	);
}

ponywalls.hush = function(id, button)
{
	$.ajax({
		type: "POST",
		url: BASE_URL + "wallpapers/hush/" + id,
		data: "key=" + $(button).attr("data-key"),
		dataType: "json"
	})
	.done(
		function(data)
		{
			debug.addTab(data.debug);
			if(data.result == true)
			{
				$(button).html("Louder !");
				$(button).attr("data-key", data.key)
				$(button).removeClass('bigmac');
				$(button).addClass('rainbowdash');
				$(button).attr('onclick', 'ponywalls.yay('+ id +', this);');
				$("#yay-score").html(data.score);
			}
			else
				alert(data.message);
		}
	);
}

// Dialog

ponywalls.dialog = function(data)
{
	if(data.type != null)
	{
		if(data.type == "error")
			data = $.extend(ponywalls.promptDefaultOptions.error, data);
		else
			data = $.extend(ponywalls.promptDefaultOptions.confirm, data);
	}
	else
		data = $.extend(data, ponywalls.promptDefaultOptions.confirm);
	
	if($('div#shadow').length == 0)
		$('body').append('<div id="shadow"></div>');
	
	var dialog = $('<div class="dialog"></div>');
	var image = $('<img src="" alt="" />');
	var title = $('<h1></h1>');
	var header = $('<p></p>');
	var content = $('<div class="dialogMessage"></div>');
	var buttonContainer = $('<div class="buttons"></div>')
	
	dialog.attr('id', 'dialog_' + data.type);
	image.attr('src', BASE_URL + 'static/images/derpy_' + data.type + ".png");
	image.attr('alt', data.type);
	title.html(data.title);
	header.html(data.header);
	content.addClass(data.type);
	content.html(data.message);
	
	image.appendTo(dialog);
	title.appendTo(dialog);
	header.appendTo(dialog);
	content.appendTo(dialog);
	
	for(var name in data.buttons)
	{
		var button = $('<a class="promptbutton button twilightsparkle" onclick="' + data.buttons[name] + '">' + name + '</a>');
		button.appendTo(buttonContainer);
	}
	buttonContainer.appendTo(dialog);
	
	dialog.bind('close', function()
	{
		dialog.remove();
		$('#shadow').remove();
	});
	
	$('body').append(dialog);
	
}
