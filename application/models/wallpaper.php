<?php
namespace Model;
use \Exception\InvalidArgumentException;
use \Debug;

class Wallpaper extends Model
{
	public $id = -1;
	public $size = 0;
	public $filename = '';
	public $origFilename = '';
	public $poster = -1;
	public $rating = 's';
	public $source = '';
	public $author = '';
	public $postTime = 0;
	public $editTime = 0;
	public $md5 = 0; //MD5 for an empty string
	
	
	//Virtual properties
	public $keywords = array(); //Mapped to keyword and walls_keywords table
	public $score = 0; //Mapped to the wall_scores table.
	public $statusKey = null; //Computed key to make a wallpaper unique at a certain time.
	
	/*
	 * Constructor. Initializes the class on three different manners:
	 * 	- No parameter is given: All the values are initialized as their defaults.
	 * 	- The given parameter is an integer: The wallpaper with the given int as ID try to be loaded.
	 * 	- The given parameter is an array: The data of the array ('varname' => 'value') will be loaded into the object and no query will be made
	 */
	public function __init($data = null)
	{
		
		if($data !== null)
		{
			if(is_numeric($data))
				$this->load($data);
			elseif(is_array($data))
			{
				foreach($data as $key => $el)
				{
					if(isset($this->$key))
						$this->$key = $el;
				}
			}
			else
				throw new InvalidArgumentException('data', $data, $this);
		}
	}
	
	/*
	 * Loads a wallpaper into the object
	 */
	public function load($id)
	{
		$this->prepare('SELECT	walls.id,
								walls.size,
								walls.filename,
								IFNULL(walls.source, \'spc://unknown\') AS source,
								IFNULL(walls.rating, \'s\') AS rating,
								walls.poster,
								walls.author,
								walls.orig_filename,
								walls.postTime,
								walls.editTime,
								k.keyword as keyword,
								walls.md5,
								(SELECT COUNT(idUser) FROM wall_scores WHERE idWall = walls.id) AS score
						FROM `walls`
							LEFT JOIN wall_keywords wk ON wk.idWall = walls.id 
							LEFT JOIN keywords k ON k.id = wk.idKeyword
						WHERE walls.id = ?');
		
		$this->execute($id);
		$data = $this->fetchAll();
		
		if(!empty($data))
		{
			$this->__init($data[0]);
			$this->poster = new User($this->poster);
			$this->keywords = array();
			
			foreach($data as $key => $el)
				$this->keywords[] = $el['keyword'];
			
			if($this->editTime == 0)
				$this->save();
			
			$this->computeStatusKey();
		}
	}
	
	/*
	 * Saves the wallpaper, according to its ID value. No value and the DB entry is created. A value, and it's updated.
	 */
	public function save()
	{
		//Updating the last DB edit time
		$this->editTime = time();
		
		if($this->id !== null)
			$this->update();
		else
			$this->create();
	}
	
	/*
	 * Updates the wallpaper data.
	 */
	public function update()
	{
		//Escaping
		foreach($this->keywords as $i => $keyword)
			$this->keywords[$i] = strtolower(htmlentities($keyword));
		$this->author = htmlentities($this->author);
		$this->source = htmlentities($this->source);
		
		//Deleting all previous wall-keyword associations, we don't like to have duplicates in the DB
		$this->prepare('DELETE FROM wall_keywords WHERE idWall = ?');
		$this->execute(array($this->id));
		
		$this->prepare('INSERT IGNORE INTO keywords (keyword) VALUES(?)');
		
		foreach($this->keywords as $keyId => $keyword)
		{
			if(strpos($keyword, ':')) //Special value keywords (like author:, rating:)
			{
				$keyword = explode(':', $keyword, 2);
				if(isset($this->{$keyword[0]}))
				$this->{$keyword[0]} = $keyword[1];
				
				unset($this->keywords[$keyId]);
			}
			elseif(!empty($keyword))
			{
				$this->execute($keyword);
				$this->reset();
			}
		}
		
		$this->prepare('INSERT INTO wall_keywords (idWall, idKeyword) VALUES(?, (SELECT id FROM keywords WHERE keyword = ?))');
		foreach($this->keywords as $keyword)
		{
			$this->execute($this->id, $keyword);
			$this->reset();
		}
		
		$this->prepare('UPDATE walls SET rating = ?, author = ?, source = ?, editTime = ? WHERE id = ?');
		$this->execute(array(	$this->rating,
								$this->author,
								$this->source,
								$this->editTime,
								$this->id ));
	}
	
	/*
	 * Creates the current wallpaper object into the database.
	 */
	public function create()
	{
		//Escaping data
		$orig_filename = htmlentities($this->origFilename);
		foreach($this->keywords as &$keyword)
			$keyword = strtolower(htmlentities($keyword));
		
		$this->prepare('INSERT IGNORE INTO keywords (keyword) VALUES(?)');
		
		//Special value keywords (like author:johnjoseco)
		foreach($this->keywords as $keyId => $keyword)
		{
			if(strpos($keyword, ':')) //Special value keywords (like author:, rating:)
			{
				$keyword = explode(':', $keyword, 2);
				if(isset($this->{$keyword[0]}))
				$this->{$keyword[0]} = $keyword[1];
				
				unset($this->keywords[$keyId]);
			}
			elseif(!empty($keyword))
			{
				$this->execute(array($keyword));
				$this->reset();
			}
		}
		
		$this->prepare('INSERT INTO walls (filename, orig_filename, size, poster, rating, postTime, editTime, md5, author, source) VALUES(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
		$this->execute(array(	$this->filename,
								$this->orig_filename,
								$this->size,
								$this->poster->id,
								$this->rating,
								$this->postTime,
								$this->editTime,
								$this->md5,
								$this->author,
								$this->source ));
		$this->id = $this->lastInsertID();
		
		
		
		$this->prepare('INSERT INTO wall_keywords (idWall, idKeyword) VALUES(?, (SELECT id FROM keywords WHERE keyword = ?))');
		foreach($this->keywords as $keyword)
		{
			if(!empty($keyword))
			{
				$this->execute(array($this->id, $keyword));
				$this->_query->closeCursor();
			}
		}
	}
	
	/*
	 * Checks if the user has already yay-ed a wallpaper.
	 */
	public function hasYay($user)
	{
		$this->prepare('SELECT idWall FROM wall_scores WHERE idUser = ? AND idWall = ?');
		$this->execute($user->id, $this->id);
		
		$result = $this->fetch();
		
		if(!empty($result))
			return true;
		else
			return false;
	}
	
	public function addScore($user)
	{
		if(is_null($user->id) || is_null($this->id))
			return false;
		
		//Checking if user has not already voted
		$this->prepare('SELECT COUNT(idUser) AS score FROM wall_scores WHERE idWall = ? AND idUser = ?');
		$this->execute($this->id, $user->id);
		$score = $this->fetch();
		
		if($score['score'] > 0)
			return false;
		
		//Now we can add the user into the scores
		$this->prepare('INSERT INTO wall_scores (idWall, idUser) VALUES(?, ?)');
		$result = $this->execute((int) $this->id, (int) $user->id);
		
		if(!$result)
			return false;
		
		return true;
	}
	
	public function removeScore($user)
	{
		if(is_null($user->id) || is_null($this->id))
			return false;
		
		//Checking if user has not already voted
		$this->prepare('SELECT COUNT(idUser) AS score FROM wall_scores WHERE idWall = ? AND idUser = ?');
		$this->execute($this->id, $user->id);
		$score = $this->fetch();
		
		if($score['score'] == 0)
			return false;
		
		//Now we can add the user into the scores
		$this->prepare('DELETE FROM wall_scores WHERE idWall = ? AND idUser = ?');
		$result = $this->execute((int) $this->id, (int) $user->id);
		
		if(!$result)
			return false;
		
		return true;
	}
	
	public function MD5Exists($md5)
	{
		$this->prepare('SELECT id FROM walls WHERE md5 = ?');
		$this->execute($md5);
		$data = $this->fetch();
		
		if(!empty($data))
			return false;
		else
			return true;
	}
	
	public function computeStatusKey()
	{
		$this->statusKey = md5(Model::serialize($this, array('score', 'statusKey')));
		
		return $this->statusKey;
	}
	
	public function getThumb()
	{
		$filename = explode('.', $this->filename);
		array_pop($filename);
		array_push($filename, 'png');
		$filename = join('.', $filename);
		
		return $filename;
	}
}
