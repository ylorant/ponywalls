<?php

class ModuleManager
{
	public $modules = array();
	private $classNames = array();
	private $config;
	
	public function __construct($config)
	{
		$this->config = $config;
		
		$initial = $this->config->get('Modules.Autoload');
		$initial = explode(',', str_replace(' ', '', $initial));
		
		Scrapebot::message('Loading modules: '.$this->config->get('Modules.Autoload'));
		$this->load($initial);
	}
	
	public function load($list)
	{
		if(!is_array($list))
			$list = array($list);
		
		foreach($list as $module)
		{
			if(isset($this->modules[$module]))
				continue;
			
			Scrapebot::message('Loading module '.$module.'...');
			if(!isset($this->classNames[$module]) && is_file('modules/'.$module.'.php'))
				include('modules/'.$module.'.php');
			elseif(!is_file('modules/'.$module.'.php'))
				continue;
				
			$this->init($module);
		}
	}
	
	public function setClassName($plugin, $class)
	{
		$this->classNames[$plugin] = $class;
	}
	
	public function init($name)
	{
		Scrapebot::message('Initializing module '.$name.'...');
		$this->modules[$name] = new $this->classNames[$name]($this->config);
	}
}

class Events
{
	private $hooks = array();
	
	public static $instance;
	
	public static function getInstance()
	{
		if(!self::$instance)
			self::$instance = new self();
		
		return self::$instance;
	}
	
	public static function __callStatic($func, $args)
	{
		$self = self::getInstance();
		call_user_func_array(array($self, 's_'.$func), $args);
	}
	
	public function s_hook($func, $interval)
	{
		Scrapebot::message('Hook added for func'.$func[1].' every '.$interval.' seconds.');
		$this->hooks[] = array('call' => $func, 'interval' => $interval, 'last' => 0);
	}
	
	public function s_unhook($func)
	{
		foreach($this->hooks as $i => $hook)
		{
			if($hook['call'] == $func)
				unset($this->hooks[$i]);
		}
	}
	
	public function s_tick()
	{
		foreach($this->hooks as &$hook)
		{
			if($hook['last'] + $hook['interval'] < time())
			{
				$hook['last'] = time();
				call_user_func_array($hook['call'], array());
			}
		}
	}
}

class Module
{
	protected $config;
	
	public function __construct($config)
	{
		$this->config = $config;
		
		$this->init();
	}
	
	public function init()
	{
		
	}
}
