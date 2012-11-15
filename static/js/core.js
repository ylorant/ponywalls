var ponywalls = {};

ponywalls.promptDefaultOptions =
{
	type: "confirm",
	buttons: {"Close": 'this.el.trigger("close")'},
	title: "I brought you a letter !",
	header: "According to myself, the message is:",
	message: "Empty."
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
	data = $.merge(data, ponywalls.promptDefaultOptions);
	
	if($('div#shadow').length == 0)
		$('body').append('<div id="shadow"></div>');
	
	var dialog = $('<div class="dialog"></div>');
	var image = $('<img src="" alt="" />');
	var title = $('<h1></h1>');
	var header = $('<p></p>');
	var content = $('<div class="dialogMessage"></div>');
	var buttonContainer = $('<div class="buttons"></div>')
	
	dialog.attr('id', 'dialog_' + data.type);
	image.attr('src', 'static/images/derpy_' + data.type);
	image.attr('alt', data.type);
	title.html(data.title);
	header.html(data.header);
	content.addClass(data.type);
	content.html(data.content);
	
	image.appendTo(dialog);
	title.appendTo(dialog);
	header.appendTo(dialog);
	content.appendTo(dialog);
	
	for(var name in data.buttons)
	{
		var button = $('<div class="promptbutton"');
	}
	
	
	$('body').append(dialog);
	
}
