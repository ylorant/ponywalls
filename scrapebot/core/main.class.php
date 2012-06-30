<?php

class Scrapebot
{
	public static $verbose = false;
	private $config;
	private static $status = false;
	public $modules;
	
	const VERSION = '0.1';
	
	public function init()
	{
		Scrapebot::message('Scrapebot for Ponywalls, version '. Scrapebot::VERSION);	
		$this->config = new Config('conf/');
		$this->config->load();
		$config = $this->config;
		Scrapebot::message('Connecting to database...');
		DB::connect($config('Database.Engine'),
					$config('Database.Host'),
					$config('Database.Port'),
					$config('Database.User'),
					$config('Database.Password'),
					$config('Database.Name'));
		$this->modules = new ModuleManager($this->config);
	}
	
	public static function fork($callback, $params)
	{
		$pid = pcntl_fork();
		if ($pid == -1)
			return false;
		else if ($pid)
			return true;
		else
		{
			call_user_func_array($callback, $params);
			die();
		}
	}
	
	public function run()
	{
		Scrapebot::message('Running.');
		while(true)
		{
			Events::tick();
			usleep(10000);
			pcntl_waitpid(-1, $st, WNOHANG);
		}
	}
	
	public static function message($message, $level = E_NOTICE)
	{
		if(!self::$verbose)
			return;
		
		switch($level)
		{
			case E_NOTICE:
				$prefix = '[INFO]';
				break;
			case E_WARNING:
				$prefix = '[WARN]';
				break;
			case E_ERROR:
				$prefix = '[ERROR]';
				break;
			default:
				$prefix = '[MSG]';
		}
		
		if(self::$status !== false)
		{
			echo "\r";
			echo str_repeat(' ', self::$status);
			echo "\r";
			self::$status = false;
		}
		
		echo $prefix.' '.$message.PHP_EOL;
	}
	
	public static function status($message)
	{
		if(!self::$verbose)
			return;
		
		if(self::$status !== false)
		{
			echo "\r";
			echo str_repeat(' ', self::$status);
			echo "\r";
		}
		
		self::$status = strlen($message);
		echo $message;
	}
}
