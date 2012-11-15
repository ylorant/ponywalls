var debug = {};
debug.iTab = 1;

debug.addTab = function(trace)
{
	$('<a class="debug-tab-link" onclick="debug.toggleTab(this, \'debug-tab-' + debug.iTab + '\');">AJAX Query ' + debug.iTab + '</a>').appendTo("div.debug-window div.debug-tabs");
	$('<div class="debug-tab" id="debug-tab-' + debug.iTab + '"><pre>' + trace + '</pre></div>').appendTo("div.debug-window div.debug-content").hide();
	
	debug.iTab++;
}

debug.toggleTab = function(link, tab)
{
	$('div.debug-window div.debug-content div.debug-tab').hide();
	$('div.debug-window a.debug-tab-link').removeClass('selected');
	$('div.debug-window div.debug-content div#' + tab).show();
	
	$(link).addClass('selected');
}

debug.toggleWindow = function()
{
	$('.debug-window').toggle();
}
