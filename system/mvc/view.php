<?php
namespace View;

class View {

	private $pageVars = array();
	private $template;

	public function __construct($template)
	{
		$this->template = APP_DIR .'views/'. $template .'.php';
	}
	
	public function __set($var, $val)
	{
		$this->pageVars[$var] = $val;
	}
	
	public function set($var, $val)
	{
		$this->pageVars[$var] = $val;
	}
	
	public static function strTime($s)
	{
		$str = '';
		$d = intval($s/3600);
		$s -= $d*3600;
		
		$h = intval($s/60);
		$s -= $h*60;
		
		if ($d) $str = $d . ' days ';
		if ($h) $str .= $h . ' hours ';
		if ($s) $str .= $s . ' minutes ';
		
		if(!$str)
			$str = '0 hours';
		
		return $str;
	}
	
	public static function toBool($val)
	{
	    return !!$val;
	}
	
	public static function toDate($val)
	{
	    return date('Y-m-d', $val);
	}
	
	public static function toTime($val)
	{
	    return date('H:i:s', $val);
	}
	
	public static function toDateTime($val)
	{
	    return date('Y-m-d H:i:s', $val);
	}
	
	public static function toInt($val)
	{
		if(is_bool($val))
			return $val ? 1 : 0;
		else
			return (int)$val;
	}
	
	public function getVars()
	{
		return $this->pageVars;
	}

	public function render()
	{
		extract($this->pageVars);

		ob_start();
		require($this->template);
		$content = ob_get_clean();
		$content = preg_replace('#<img([^>]+)? src="(.+)"( .+)? />#isU', '<img$1 src="'.BASE_URL.'$2"$3 />', $content);
		$content = preg_replace('#<script([^>]+)? src="(.+)"( .+)?>#isU', '<script$1 src="'.BASE_URL.'$2"$3>', $content);
		$content = preg_replace('#<a([^>]+)? href="(.+)"( .+)?>#isU', '<a$1 href="'.BASE_URL.'$2"$3>', $content);
		$content = preg_replace('#<a([^>]+)? href="'.BASE_URL.'http://(.+)"( .+)?>#isU', '<a$1 href="http://$2"$3>', $content);
		$content = preg_replace('#<link([^>]+)? href="(.+)"( .+)?>#isU', '<link$1 href="'.BASE_URL.'$2"$3>', $content);
		$content = preg_replace('#<form([^>]+)? action="(.+)"( .+)?>#isU', '<form$1 action="'.BASE_URL.'$2"$3>', $content);
		
		echo $content;
	}
    
}

?>
