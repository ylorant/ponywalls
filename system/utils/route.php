<?php

/* Predefined route ("static routes") class container
 * Route types:
 * 	- Static: pure static route, specifying a direct relation path->function.
 * 
 * 	- Dynamic (simple/reflexive): Dynamic route, but with only simple parameters (easy replacement for regexes, allow to do reflexive
 * 	  replacements):
 * 		-> integers: {int}
 * 		-> strings: {string}
 * 		-> a function which returns the string which will be in the path: {f:Class::method}. The URL will be transmitted as the unique parameter.
 * 		-> a class: {class}.
 * 		-> a method: {method}. If a class has already been guessed, then the method will be check in it.
 * 		-> a method from a defined class: {m:Class}.
 *	  Parameters will be sent to the function as arguments, in order of appearance.
 * 	  For class names: If they're not fully namespaced (starting with an antislash), they will me namespaced into the Controller namespace.
 *    Keep in mind that this analysis is really resource-hungry.
 *    The path has to be an array.
 * 
 * 	- Dynamic (RegExp): Dynamic route, using a full PCRE regex (delimiters and options included).
 * 
 * Note: No need to use the prefix Controller\\ before the class name (to specify the namespace).
 * It is assumed that the mapped class is a controller.
 * 
 * Examples of usage: 
 * 	- $route = new Route(Route::TYPE_STATIC, 'post/new', 'Post::create');
 * 	- $route = new Route(Route::TYPE_DYNAMIC, 'view/{int}', 'Post::view');
 * 	- $route = new Route(Route::TYPE_REGEXP, '#^edit/([0-9]+)/(comment|data)$#', 'Post::edit');
 */

class Route
{
	private $type = Route::TYPE_STATIC; //The route type
	private $path; //The path to check
	private $mapping; //The mapped class/method
	private $matches = array(); //The eventual var matches of an url
	private $separator = '/'; //The separator for dynamic matching
	public $callback = null; //The optionnal callback that will be called when the route is matched
	
	const TYPE_STATIC = 0x01;
	const TYPE_DYNAMIC = 0x02;
	const TYPE_REGEXP = 0x03;
	
	public function __construct($type = Route::TYPE_STATIC, $path = '', $mapping = null, $separator = '/', $callback = null)
	{
		$this->type = $type;
		$this->path = $path;
		$this->mapping = $mapping;
	}
	
	public function mapping()
	{
		$mapping = $this->mapping;
		
		foreach($this->matches as $id => $value)
		{
			if(strpos($mapping, '$'.($id+1)) !== false)
			{
				$mapping = str_replace('$'.($id+1), $value, $mapping);
				unset($this->matches[$id]);
			}
		}
		
		return $mapping;
	}
	
	public function __get($name)
	{
		if(isset($this->$name))
			return $this->$name;
		else
			throw new UnknownPropertyException($name);
	}
	
	public function match($url)
	{
		switch($this->type)
		{
			case self::TYPE_STATIC: //Static routes
				return $url == $this->path;
			case self::TYPE_REGEXP: //Regex
				return preg_match($this->path, $url, $this->matches);
				break;
			case self::TYPE_DYNAMIC: //Dynamic routes
				$this->matches = array();
				$path = $this->path;
				
				$pieces = explode($this->separator, $url);
				$j = 0;
				$k = -1;
				$loadedClass = null;
				foreach($pieces as $i => $el)
				{
					if(!isset($path[$k]) || $path[$k][0] != '[' || $path[$k][strlen($path[$k])-1] != ']')
					{
						$k++;
						if(!isset($path[$k]))
							return false;
						if($path[$k][0] != '[' || $path[$k][strlen($path[$k])-1] != ']')
							$currentpath = $path[$k];
						else
							$currentpath = substr($path[$k], 1, -1);
					}
					else
						$currentpath = substr($path[$k], 1, -1);
					
					if(substr($currentpath, 0, 3) == '{f:')
					{
						foreach($matches as $match)
						{
							$class = substr($path[$i], 3, -1);
							if($class[0] != '\\')
										$class = 'Controller\\'.$class;
							$func = $match[2];
							$obj = new $class();
							$currentpath = $obj->$func($url);
							
						}
					}
					
					if(substr($currentpath, 0, 3) == '{m:')
					{
						$class = substr($currentpath, 3, -1);
						if($class[0] != '\\')
							$class = 'Controller\\'.$class;
						
						//Try to load the class. Failing to load it will result in a false return.
						try
						{
							__autoload($class);
						}
						catch(\Exception\UnknownClassException $e)
						{
							return false;
						}
						
						if(!method_exists($class, $el))
							return false;
						$this->matches[$j++] = $el;
					}
					elseif($currentpath == '{class}')
					{
						$class = $el;
						if($class[0] != '\\')
							$class = 'Controller\\'.$class;
						
						//Try to load the class. Failing to load it will result in a false return.
						try
						{
							__autoload($class);
						}
						catch(\Exception\UnknownClassException $e)
						{
							return false;
						}
						$loadedClass = $class;
						$this->matches[$j++] = $el;
					}
					elseif($currentpath == '{method}')
					{
						if($loadedClass === null)
							return false;
						
						if(!method_exists($loadedClass, $el))
							return false;
							
						$this->matches[$j++] = $el;
					}
					elseif($currentpath == '{int}')
					{
						if(!is_numeric($el))
							return false;
						$this->matches[$j++] = $el;
					}
					elseif($currentpath == '{string}')
					{
						$this->matches[$j++] = $el;
					}
					else
					{
						if($el != $currentpath)
							return false;
					}
				}
				
				Debug::show($path, 'System/Matched path');
				return true;
				break;
			default:
				return false;
		}
	}

	public function __toString()
	{
		switch($this->type)
		{
			case Route::TYPE_STATIC:
			case Route::TYPE_REGEXP:
				return $this->path;
			case Route::TYPE_DYNAMIC:
				return join($this->separator, $this->path);
		}
	}
}
