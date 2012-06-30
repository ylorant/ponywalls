<?php

class LocalScrape extends Module
{
	private $folders = array();
	private $configuredFolders = array();
	private $filecount = 0;
	private $fp = array();
	private $analyzed = array();
	private $types = array();
	private $sizes = array();
	private $outpath;
	private $current = 0;
	
	public function init()
	{
		if(!$this->config->get('LocalScrape.Folders'))
			return false;
		
		foreach($this->config->get('LocalScrape.Folders') as $name => $folder)
		{
			$this->folders[] = $folder;
			$this->configuredFolders[$name] = $folder;
		}
		
		$this->outpath = $this->config->get('LocalScrape.OutPath');
		$this->types = explode(',', str_replace(' ','', $this->config->get('LocalScrape.MIMETypes')));
		$sizes = explode(',', str_replace(' ','', $this->config->get('LocalScrape.AllowedSizes')));
		
		foreach($sizes as $s)
			$this->sizes[] = explode('x', $s);
		
		Events::hook(array($this, 'updateDirectory'), 1);
		Events::hook(array($this, 'walkDirectory'), -1);
	}
	
	public function walkDirectory()
	{
		while(empty($this->fp[$this->current]))
		{
			$this->current++;
			if($this->current >= count($this->fp))
			{
				$this->current = 0;
				return;
			}
		}
		
		//If there is still data to read from the filelist
		while(!feof($this->fp[$this->current]))
		{
			$fname = trim(fgets($this->fp[$this->current]));
			$file = $this->folders[$this->current].'/'.$fname;
			Scrapebot::status("Analyzing $fname...");
			if(is_dir($file) && !in_array($fname, array('', '.', '..')))
			{
				$this->folders[] = $file;
			}
			else
			{
				$finfo = finfo_open(FILEINFO_MIME_TYPE);
				$mime = finfo_file($finfo, $file);
				
				//If the file has the mimetype we want
				if(in_array($mime, $this->types))
				{
					$size = getimagesize($file);
					
					foreach($this->sizes as $s)
					{
						$catch = false;
						if($size[0] == $s[0] && $size[1] == $s[1])
							$catch = true;
						
						if($catch == true)
						{
							$outpath = $this->outpath;
							if(preg_match_all('#{f:([a-z0-9]+)}#isU', $this->outpath, $matches))
							{
								foreach($matches[1] as $match)
									$outpath = str_replace('{f:'.$match.'}', $this->configuredFolders[$match], $outpath);
							}
							
							$outpath = str_replace('{filename}', $fname, $outpath);
							$outdir = explode('/', $outpath);
							array_pop($outdir);
							$outdir = join('/', $outdir);
							
							if(!is_dir($outdir))
								mkdir($outdir, 0777, true);
							
							Scrapebot::fork(array($this, 'copyImage'), array($file, $outpath));
							$this->filecount++;
							Scrapebot::message($this->filecount.': '.$fname);
						}
					}
				}
				
				fputs($this->analyzed[$this->current], $fname."\n");
			}
		}
		//~ else
		{
			fclose($this->fp[$this->current]);
			fclose($this->analyzed[$this->current]);
			unset($this->fp[$this->current]);
		}
		
		$this->current++;
		if($this->current >= count($this->fp))
			$this->current = 0;
	}
	
	public function copyImage($source, $dest)
	{
		copy($source, $dest);
	}
	
	public function updateDirectory()
	{
		foreach($this->folders as $i => $folder)
		{
			if(!isset($this->fp[$i]))
			{
				$content = scandir($folder);
				array_shift($content);
				array_shift($content);
				if(is_file($folder.'/scrapebot.lst'))
				{
					$read = file($folder.'/scrapebot.lst', FILE_IGNORE_NEW_LINES);
					$content = array_diff($content, $read);
				}
				
				$this->fp[$i] = tmpfile();
				fputs($this->fp[$i], join("\n", $content));
				fseek($this->fp[$i], 0);
				$this->analyzed[$i] = fopen($folder.'/scrapebot.lst', 'a+');
				//~ $filename = uniqid(md5(time()), true);
				//~ system('ls '.$this->folders[$this->current].' > tmp/'.$filename);
				//~ $this->fp[$this->current] = fopen('tmp/'.$filename, 'r+');
			}
		}
	}
}

$this->setClassName('localscrape', 'LocalScrape');
