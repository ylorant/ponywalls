<?php

class Walls_model extends Model
{
	public function addWallpaper($filename, $orig_filename, $size, $keywords, $poster = -1, $rating = NULL)
	{
		//Escaping data
		$orig_filename = htmlentities($orig_filename);
		$keywords = explode(' ', strtolower(htmlentities($keywords)));
		
		$this->prepare('INSERT INTO walls (filename, orig_filename, size, poster, rating, time) VALUES(?, ?, ?, ?, ?, ?)');
		$this->execute(array($filename, $orig_filename, $size, $poster, $rating, time()));
		$id = $this->lastInsertID();
		
		$this->prepare('INSERT IGNORE INTO keywords (keyword) VALUES(?)');
		
		foreach($keywords as $keyword)
		{
			$this->execute(array($keyword));
			$this->_query->closeCursor();
		}
		
		$this->prepare('INSERT INTO wall_keywords (idWall, idKeyword) VALUES(?, (SELECT id FROM keywords WHERE keyword = ?))');
		foreach($keywords as $keyword)
		{
			$this->execute(array($id, $keyword));
			$this->_query->closeCursor();
		}
		
		return $id;
	}
	
	public function updateWallpaper($id, $keywords, $rating)
	{
		$keywords = explode(' ', strtolower(htmlentities($keywords)));
		$this->prepare('DELETE FROM wall_keywords WHERE idWall = ?');
		$this->execute(array($id));
		
		$this->prepare('INSERT IGNORE INTO keywords (keyword) VALUES(?)');
		
		foreach($keywords as $keyword)
		{
			$this->execute(array($keyword));
			$this->_query->closeCursor();
		}
		
		$this->prepare('INSERT INTO wall_keywords (idWall, idKeyword) VALUES(?, (SELECT id FROM keywords WHERE keyword = ?))');
		foreach($keywords as $keyword)
		{
			$this->execute(array($id, $keyword));
			$this->_query->closeCursor();
		}
		
		$this->prepare('UPDATE walls SET rating = ? WHERE id = ?');
		$this->execute(array($rating, $id));
	}
	
	public function searchWallpaper($keywords, $inclusive = FALSE)
	{
		$data = array();
		foreach($keywords as $keyword)
		{
			if(strpos($keyword, ':') !== FALSE)
			{
				$components = explode(':', $keyword, 2);
				switch($components[0])
				{
					case 'rating':
						$this->prepare('SELECT walls.id, walls.size, walls.filename FROM `walls` WHERE walls.rating = ?');
						break;
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
				if(!$inclusive)
					$data = $this->intersect($data, $fetch);
				else
					$data = $this->merge($data, $fetch);
			}
			else
				$data = $fetch;
		}
		
		return $data;
	}
	
	public function randomWallpapers()
	{
		$this->prepare('SELECT walls.id, walls.size, walls.filename FROM walls ORDER BY RAND() LIMIT 30');
		$this->execute();
		return $this->fetchAll();
	}
	
	public function lastWallpapers()
	{
		$this->prepare('SELECT walls.id, walls.size, walls.filename FROM walls ORDER BY id DESC LIMIT 30');
		$this->execute();
		return $this->fetchAll();
	}
	
	public function getWallpaper($id)
	{
		$this->prepare('SELECT walls.id, walls.size, walls.filename, IFNULL(walls.rating, \'s\') AS rating, IFNULL(users.login, \'Anonymous\') AS poster, walls.orig_filename, walls.time, k.keyword as keywords FROM `walls` 
						JOIN wall_keywords wk ON wk.idWall = walls.id 
						JOIN keywords k ON k.id = wk.idKeyword
						LEFT JOIN users ON users.id = walls.poster
						WHERE walls.id = ?');
		
		$this->execute(array($id));
		$ret = $this->fetchAll();
		
		$data = $ret[0];
		$data['keywords'] = array();
		
		foreach($ret as $val)
			$data['keywords'][] = $val['keywords'];
		
		return $data;
	}
	
	public function merge($arr1, $arr2)
	{
		$ret = $arr1;
		
		foreach($arr2 as $val)
		{
			if(!in_array($val, $ret))
				$ret[] = $val;
		}
		
		return $ret;
	}
	
	public function intersect($arr1, $arr2)
	{
		$ret = array();
		foreach($arr1 as $val)
		{
			if(in_array($val, $arr2))
				$ret[] = $val;
		}
		
		return $ret;
	}
}
