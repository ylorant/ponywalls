<?php

class Debug
{
	private static $instance = null;
	private $variables = array();
	private $exceptions = array();
	private $queries = array();
	private $terminated = false;
	
	public static function getInstance()
	{
		if(empty(self::$instance))
			self::$instance = new self();
		
		return self::$instance;
	}
	
	public static function __callStatic($name, array $arguments)
	{
		if(method_exists('Debug', 's_'.$name))
		{
			$name = 's_'.$name;
			$self = self::getInstance();
			return call_user_func_array(array($self, $name), $arguments);
		}
	}
	
	public function s_show($var, $name = null)
	{
		if($name === null)
			$this->variables[] = $var;
		else
			$this->variables[$name] = $var;
	}
	
	public function s_exception($e)
	{
		$this->exceptions[] = $e;
	}
	
	public function s_query($q)
	{
		$this->queries[] = $q;
	}
	
	public function s_countExceptions()
	{
		return count($this->exceptions);
	}
	
	public function s_countVariables()
	{
		return count($this->exceptions);
	}
	
	public function s_terminated()
	{
		return $this->terminated;
	}
	
	public function s_terminate()
	{
		$this->terminated = true;
	}
	
	public function __toString()
	{
		$str =	" --- Application debug trace ---\n\n";
		
		if(!empty($_POST))
		{
			$str .= "+------------+\n".
					"| POST Data: |\n".
					"+------------+\n\n";
			
			ob_start();
			foreach($_POST as $k => $v)
			{
				echo '['.$k."]:";
				
				$str .= ob_get_contents();
				ob_clean();
				var_dump($v);
				$str .= trim(str_replace("\n", "\n\t", ob_get_contents()));
				ob_clean();
			}
			
			$str .= ob_get_clean();
		}
		
		if(!empty($_GET))
		{
			$str .= "+-----------+\n".
					"| GET Data: |\n".
					"+-----------+\n\n";
			
			ob_start();
			foreach($_GET as $k => $v)
			{
				echo '['.$k."]:";
				
				$str .= ob_get_contents();
				ob_clean();
				var_dump($v);
				$str .= trim(str_replace("\n", "\n\t", ob_get_contents()));
				ob_clean();
			}
			
			$str .= ob_get_clean();
		}
		
		$str .= "+------------+\n".
				"| Variables: |\n".
				"+------------+\n\n";
		
		if(!empty($this->variables))
		{
			ob_start();
			foreach($this->variables as $k => $var)
			{
				if(!is_int($k))
				{
					echo '['.$k."]:";
					
					$str .= ob_get_contents();
					ob_clean();
					var_dump($var);
					$str .= trim(str_replace("\n", "\n\t", ob_get_contents()));
					ob_clean();
				}
				else
					echo var_dump($var);
				
				echo "\n";
			}
			$str .= ob_get_clean();
		}
		else
			$str .= "None.\n";
		
		$str .=	"\n".
				"+-------------+\n".
				"| Exceptions: |\n".
				"+-------------+\n\n";
		
		if(!empty($this->exceptions))
		{
			foreach($this->exceptions as $var)
				$str .= str_replace("\n", "\n|\t", $var)."\n+----------\n\n";
		}
		else
			$str .= "None.\n";
		
		
		$str .=	"\n".
				"+--------------+\n".
				"| SQL Queries: |\n".
				"+--------------+\n\n";
		
		foreach($this->queries as $query)
		{
			$str .= $query."\n--------------------------\n";
		}
		
		$str .= "Total: ".count($this->queries);
		
		return $str;
	}
}
