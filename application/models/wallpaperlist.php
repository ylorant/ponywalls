<?php
namespace Model;
use \Iterator;
use \Debug;
use \Countable;

class WallpaperList extends Model implements Iterator, Countable
{
	private $list = array();
	
	const SEARCH_EXCLUSIVE = 0x1000;
	const SEARCH_INCLUSIVE = 0x1001;
	
	/*
	 * Selects a range of wallpapers starting from the most recent ones (if the time is equal, the greater ID is first).
	 */
	public function latest($limit, $start = 0)
	{
		$this->prepare('SELECT walls.id, walls.size, walls.filename FROM walls ORDER BY postTime DESC LIMIT ?, ?');
		$this->execute($start, $limit);
		$data = $this->fetchAll();
		
		if(empty($data))
			return 0;
		else
		{
			$this->list = array();
			foreach($data as $line)
			{
				//Debug::show($line);
				$this->list[] = new Wallpaper($line);
			}
			return count($this->list);
		}
	}
	
	/*
	 * Select a bunch of random wallpapers.
	 * FIXME: Actually, the function can possibly select twice the same wallpaper. Check that and correct if possible.
	 */
	public function random($limit)
	{
		$this->prepare('SELECT walls.id, walls.size, walls.filename FROM walls ORDER BY RAND() LIMIT ?');
		$this->execute($limit);
		$data = $this->fetchAll();
		
		if(empty($data))
			return 0;
		else
		{
			$this->list = array();
			foreach($data as $line)
				$this->list[] = new Wallpaper($line);
			
			return count($this->list);
		}
	}
	
	/*
	 * Selects a list of wallpapers ordering them by score, with biggest score first
	 */
	public function coolest($limit, $start = 0)
	{
		$this->prepare('SELECT w.id, w.size, w.filename, (SELECT COUNT(wsu.idUser) FROM wall_scores wsu WHERE wsu.idWall = w.id) AS score FROM walls w LEFT JOIN wall_scores ws ON ws.idWall = w.id GROUP BY w.id ORDER BY score DESC LIMIT ?, ?');
		$this->execute($start, $limit);
		$data = $this->fetchAll();
		Debug::show($data);
		if(empty($data))
			return 0;
		else
		{
			$this->list = array();
			foreach($data as $line)
				$this->list[] = new Wallpaper($line);
			
			return count($this->list);
		}
	}
	
	/*
	 * Returns the count of wallpapers currently stored in the object
	 */
	public function count()
	{
		return count($this->list);
	}
	
	/*
	 * Searches a wallpaper
	 */
	public function search($search = array(), $searchtype = self::SEARCH_EXCLUSIVE)
	{
		$data = array();
		foreach($search as $keyword)
		{
			if(strpos($keyword, ':') !== FALSE)
			{
				$components = explode(':', $keyword, 2);
				switch($components[0])
				{
					case 'rating':
						$this->prepare('SELECT walls.id, walls.size, walls.filename FROM walls WHERE walls.rating = ?');
						break;
					case 'author':
						$this->prepare('SELECT walls.id, walls.size, walls.filename FROM walls WHERE walls.author = ?');
						break;
					case 'source':
						$this->prepare('SELECT walls.id, walls.size, walls.filename FROM walls WHERE walls.source = ?');
				}
				
				$keyword = $components[1];
			}
			else
			{
				$this->prepare('SELECT walls.id, walls.size, walls.filename FROM `walls` 
								JOIN wall_keywords wk ON wk.idWall = walls.id 
								JOIN keywords k ON k.id = wk.idKeyword
								WHERE k.keyword = ?');
			}
			
			$this->execute(array(strtolower($keyword)));
			$fetch = $this->fetchAll();
			if(!empty($data))
			{
				if($inclusive == self::SEARCH_EXCLUSIVE)
					$data = $this->intersect($data, $fetch);
				elseif($inclusive == self::SEARCH_INCLUSIVE)
					$data = $this->merge($data, $fetch);
			}
			else
				$data = $fetch;
		}
		
		$this->list = array();
		foreach($data as $el)
			$this->list[] = new Wallpaper($el);
			
		return count($this->list);
	}
	
	/*
	 * Iterator interface implementation functions
	 */
	public function current() { return current($this->list); }
	public function key() { return key($this->list); }
	public function next() { next($this->list); }
	public function rewind() { reset($this->list); }
	public function valid() { return current($this->list) !== FALSE; }
}
