<?php

class View {

	private $pageVars = array();
	private $template;

	public function __construct($template)
	{
		$this->template = APP_DIR .'views/'. $template .'.php';
	}

	public function set($var, $val)
	{
		$this->pageVars[$var] = $val;
	}

	public function render()
	{
		extract($this->pageVars);

		ob_start();
		require($this->template);
		$content = ob_get_clean();
		$content = preg_replace('#<img( .+)? src="(.+)"( .+)? />#isU', '<img$1 src="'.BASE_URL.'$2"$3 />', $content);
		$content = preg_replace('#<script( .+)? src="(.+)"( .+)?>#isU', '<script$1 src="'.BASE_URL.'$2"$3>', $content);
		$content = preg_replace('#<a( .+)? href="!(http://)(.+)"( .+)?>#isU', '<a$1 href="'.BASE_URL.'$3"$4>', $content);
		$content = preg_replace('#<link( .+)? href="(.+)"( .+)?>#isU', '<link$1 href="'.BASE_URL.'$2"$3>', $content);
		$content = preg_replace('#<form( .+)? action="(.+)"( .+)?>#isU', '<form$1 action="'.BASE_URL.'$2"$3>', $content);
		
		echo $content;
	}
    
}

?>
