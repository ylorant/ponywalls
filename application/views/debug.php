<div class="debug-window">
	<div class="debug-tabs">
		<a class="debug-tab-link selected" onclick="debug.toggleTab(this, 'debug-tab-0');">Main query</a>
	</div>
	<div class="debug-content">
		<div class="debug-tab" id="debug-tab-0">
			<pre><?php echo Debug::getInstance(); ?></pre>
		</div>
	</div>
</div>
