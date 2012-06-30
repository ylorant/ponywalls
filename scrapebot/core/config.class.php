<?php

class Config
{
	private $_configDirectory;
	private $config;
	
	public function __construct($configDir = NULL)
	{
		if($configDir !== NULL)
			$this->setConfigLocation($configDir);
	}
	
	/** Loads configuration from config files.
	 * This function loads configuration from all .ini files in the configuration folder.
	 * Configurations for all servers can be in one file or multiple files, content will be glued together
	 * and read as an unique file (it allows a more flexible ordering for big configurations).
	 * 
	 * \return TRUE if the configuration loaded successfully, FALSE otherwise.
	 */
	public function load()
	{
		Scrapebot::message('Loading config...');
		if(!is_dir($this->_configDirectory))
		{
			Scrapebot::message('Could not access to user-set confdir : '.$this->_configDirectory, E_WARNING);
			
			//Throw a fatal error if the default config directory does not exists
			if(!is_dir('conf'))
			{
				Scrapebot::message('Could not access to default confdir : ./conf', E_ERROR);
				exit();
			}
			else
				$this->_configDirectory = 'conf';
		}
		
		//Scanning config directory recursively (with also recursive section names)
		$config = $this->parseCFGDirRecursive($this->_configDirectory);
		
		if(!$config)
			return FALSE;
		else
			$this->config = $config;
		
		Scrapebot::message('Config loaded.');
		
		return TRUE;	
	}
	
	/** Loads data from .cfg files into a directory, recursively.
	 * This function loads configuration from all .ini files in the given folder. It also loads the configurations found in all sub-directories.
	 * The files are proceeded as .ini files, but adds a useful feature to them : multi-level sections. Using the '.', users will be able to
	 * define more than one level of configuration (useful for data ordering). It does not parses the UNIX hidden directories.
	 * 
	 * \param $dir The directory to analyze.
	 * \return The configuration if it loaded successfully, FALSE otherwise.
	 */
	public function parseCFGDirRecursive($dir)
	{
		if(!($dirContent = scandir($dir)))
			return FALSE;
			
		$finalConfig = array();
		
		$cfgdata = '';
		foreach($dirContent as $file)
		{
			if(is_file($dir.'/'.$file) && pathinfo($file, PATHINFO_EXTENSION) == 'cfg')
				$cfgdata .= "\n".file_get_contents($dir.'/'.$file);
			elseif(is_dir($dir.'/'.$file) && !in_array($file, array('.', '..')) && $file[0] != '.')
			{
				if($fileConf = $this->parseCFGDirRecursive($dir.'/'.$file))
					$finalConfig = array_merge($finalConfig, $fileConf);
				else
				{
					Scrapebot::message('Parse error in '.$dir.' directory.', E_WARNING);
					return FALSE;
				}
			}
		}
		
		$finalConfig = array_merge($finalConfig, $this->parseINIStringRecursive($cfgdata));
		
		return $finalConfig;
	}
	
	public function parseINIStringRecursive($str)
	{
		$config = array();
		
		//Parsing string and determining recursive array
		$inidata = parse_ini_string($str, TRUE, INI_SCANNER_RAW);
		if(!$inidata)
			return FALSE;
		foreach($inidata as $section => $content)
		{
			if(is_array($content))
			{
				$section = explode('.', $section);
				//Getting reference on the config category pointed
				$edit = &$config;
				foreach($section as $el) 
					$edit = &$edit[$el];
				
				$edit = $content;
			}
			else
				Scrapebot::message('Orphan config parameter : '.$section, E_WARNING);
		}
		
		return $config;
	}
	
	/** Generates INI config string for recursive data.
	 * This function takes configuration array passed in parameter and generates an INI configuration string with recursive sections.
	 * 
	 * \param $data The data to be transformed.
	 * \param $root The root section. Normally, this parameter is used by the function to recursively parse data by calling itself.
	 * 
	 * \return The INI config data.
	 */
	public function generateINIStringRecursive($data = NULL, $root = "")
	{
		$out = "";
		
		if($data === NULL)
			$data = $this->config;
		
		if($root)
			$out = '['.$root.']'."\n";
		
		$arrays = array();
		
		//Process data, saving sub-arrays, putting direct values in config.
		foreach($data as $name => $value)
		{
			if(is_array($value) || is_object($value))
				$arrays[$name] = $value;
			elseif(is_bool($value))
				$out .= $name.'='.($value ? 'yes' : 'no')."\n";
			else
				$out .= $name.'='.$value."\n";	
		}
		
		if($out)
			$out .= "\n";
		
		//Processing sub-sections
		foreach($arrays as $name => $value)
			$out .= $this->generateINIStringRecursive($value, $root.($root ? '.' : '').$name)."\n\n";
		
		return trim($out);
	}
	
	/** Changes the configuration directory.
	 * Changes the configuration directory to $path. If $path is a file (like Leelabot config file for example),
	 * configuration directory will be guessed from this file.
	 * 
	 * \param $path The path where the bot will read the config.
	 * \return 	TRUE if config location successfully changed, FALSE otherwise (Leelabotly because $dir is 
	 * 			neither an existing directory path nor an existing file path).
	 */
	public function setConfigLocation($path)
	{
		if(substr($path, -1) == '/')
			$path = substr($path, 0, -1);
		
		if(is_dir($path))
			$this->_configDirectory = $path;
		elseif(is_file($path))
			$this->_configDirectory = pathinfo($path, PATHINFO_DIRNAME);
		else
			return FALSE;
		
		return TRUE;
	}
	
	/** Returns the configuration directory.
	 * This function simply returns the path to the current configuration directory, as specified by setConfigLocation,
	 * or the default one if it has not been overwrited yet.
	 * 
	 * \return The current configuration directory.
	 */
	public function getConfigLocation()
	{
		return $this->_configDirectory;
	}
	
	public function get($section = FALSE)
	{
		if($section === FALSE)
			return $this->config;
		else
		{
			$section = explode('.', $section);
			$sec = &$this->config;
			foreach($section as $s)
			{
				if(!isset($sec[$s]))
					return NULL;
				
				$sec = &$sec[$s];
			}
			
			return $sec;
		}
	}
	
	public function __invoke($section)
	{
		return $this->get($section);
	}
	
	public function set($section, $data)
	{
		if(!is_array($this->config))
			$this->config = array();
		
		$section = explode('.', $section);
		$sec = &$this->config;
		foreach($section as $s)
		{
			if(!isset($sec[$s]))
				$sec[$s] = array();
			$sec = &$sec[$s];
		}
		
		$sec = $data;
	}
	
	public function dump($file)
	{
		file_put_contents($file, $this->generateINIStringRecursive());
	}
}
