<?php
namespace Controller;
use \Debug;
use \Model\WallpaperList;
use \Model\Wallpaper;
use \Model\Model;
use \Model\User;
use \View\View;
use \Exception\InvalidArgumentException;

class Main extends Controller
{
	private $ratios;
	private $user = null;
	public $lang = 'en';
	
	public function __construct()
	{
		$this->ratios = array('16:9' => 16/9, '16:10' => 16/10, '4:3' => 4/3, '5:4' => 5/4);
		
		if(!empty($_SESSION['user']))
			$this->user = Model::unserialize($_SESSION['user'], new User());
			
		Debug::show($this->user, 'User data');
	}
	
	//Main page (search area mainly)
	public function index()
	{
		$sentenceList = explode("\n", trim(file_get_contents('static/sentences.txt')));
		if($this->lang == 'fr')
			$template = new View('fr/index');
		else
			$template = new View('index');
		$template->set('titleSentence', str_replace('\n', '<br />',$sentenceList[rand(0, count($sentenceList) - 1)]));
		
		if(isset($_SESSION['message']))
		{
			$template->set('message', $_SESSION['message'][0]);
			$template->set('errorMsg', $_SESSION['message'][1]);
			unset($_SESSION['message']);
		}
		
		if(!empty($this->user))
		{
			$template->set('logged', TRUE);
			$template->set('user', $this->user);
		}
		
		$template->render();
	}
	
	//Performing a search
	public function search($data = '', $searchtype = 'exclusive')
	{
		global $config;
		
		if(isset($_POST['search']))
		{
			if(isset($_POST['searchtype']))
				$this->redirect('search/'.urlencode($_POST['search']).'/'.$_POST['searchtype']);
			else
				$this->redirect('search/'.urlencode($_POST['search']));
			exit();
		}
		
		Debug::show(func_get_args(), "Search");
		$data = urldecode($data);
		
		if(!$data)
		{
			
			Debug::exception(new InvalidArgumentException('data'));
			$this->redirect('index');
			exit();
		}
		
		if($data == 'order:latest')
		{
			$this->redirect('latest');
			exit();
		}
		else if($data == 'order:random')
		{
			$this->redirect('random');
			exit();
		}
		else if($data == 'order:coolest')
		{
			$this->redirect('coolest');
			exit();
		}
		
		$wallpaperList = new WallpaperList();
		$keywords = explode(' ', $data);
		$results = $wallpaperList->search($keywords, $searchtype == 'inclusive');
		//~ $related = $model->relatedKeywords($keywords);
		
		$template = new View('search');
		$template->set('wrelated', array());
		
		if($searchtype == 'inclusive')
			$template->set('inclusive', TRUE);
		
		if(!empty($this->user))
		{
			$template->set('logged', TRUE);
			$template->set('user', $this->user);
		}
		
		$template->set('search', $data);
		$template->set('results', $wallpaperList);
		$template->render();
		
		Debug::show($template->getVars());
	}
	
	public function random()
	{
		$model = new WallpaperList();
		$model->random(20);
		
		$template = new View('search');
		if(!empty($this->user))
		{
			$template->set('logged', TRUE);
			$template->set('user', $this->user);
		}
		
		$template->set('search', 'order:random');
		$template->set('results', $model);
		$template->render();
	}
	
	public function latest($page = 0)
	{
		$model = new WallpaperList();
		$model->latest(20, (int) $page);
		
		$template = new View('search');
		if(!empty($this->user))
		{
			$template->set('logged', TRUE);
			$template->set('user', $this->user);
		}
		
		$template->set('search', 'order:latest');
		$template->set('results', $model);
		$template->render();
	}
	
	public function coolest($page = 0)
	{
		$model = new WallpaperList();
		$model->coolest(20, (int) $page);
		
		$template = new View('search');
		if(!empty($this->user))
		{
			$template->set('logged', TRUE);
			$template->set('user', $this->user);
		}
		
		$template->set('search', 'order:coolest');
		$template->set('results', $model);
		$template->render();
	}
	
	public function view($id)
	{
		if(empty($id))
		{
			$_SESSION['message'] = array('error', 'This wallpaper does not exists');
			$this->redirect('index');
			exit();
		}
		
		$wallpaper = new Wallpaper($id);
		
		if(empty($wallpaper->id))
		{
			$_SESSION['message'] = array('error', 'This wallpaper does not exists');
			$this->redirect('index');
			exit();
		}
		
		
		switch($wallpaper->rating)
		{
			case 's':
				$wallpaper->rating_str = 'Safe';
				break;
			case 'q':
				$wallpaper->rating_str = 'Questionable';
				break;
			case 'e':
				$wallpaper->rating_str = 'Explicit';
				break;
		}
		
		//Origin determination, from URL
		$source = explode('://', $wallpaper->source, 2);
		
		$wallpaper->source_url = null;
		if($source[0] == 'spc')
		{
			$wallpaper->source_url = null;
			switch($source[1])
			{
				case 'orig':
					$wallpaper->source = 'Original';
					break;
				case 'unknown':
					$wallpaper->source = 'Unknown';
					break;
			}
		}
		elseif($source[0] == 'http')
		{
			$source[1] = explode('/', $source[1]);
			$wallpaper->source_url = $wallpaper->source;
			$wallpaper->source = $source[1][0];
		}
		
		//Ratio determination
		$size = explode('x', $wallpaper->size);
		$ratio = $size[0] / $size[1];
		
		if($r = array_search($ratio, $this->ratios))
			$wallpaper->ratio = $r;
		else
		{
			$gcd = gcd($size[0], $size[1]);
			$wallpaper->ratio = 'Unknown ('. ($size[0] / $gcd) .':'. ($size[1]/$gcd) .')';
		}
		
		//Author
		if(empty($wallpaper->author))
			$wallpaper->author = "Unknown";
		
		//Poster
		if(empty($wallpaper->poster->login))
			$wallpaper->poster->login = "Unknown";
		
		$template = new View('view');
		
		if(!empty($this->user))
		{
			$template->set('logged', TRUE);
			$template->set('user', $this->user);
			
			if($wallpaper->hasYay($this->user))
				$template->set('yayed', true);
			else
				$template->set('yayed', false);
		}
		
		$template->set('wallpaper', $wallpaper);
		$template->render();
	}
	
	public function about()
	{
		$template = $this->loadView('main_view');
		$template->render();
	}
    
}

function gcd($n, $m)
{ 
    $n=abs($n); $m=abs($m); 
    if ($n==0 && $m==0) 
        return 1;
    if ($n==$m && $n>=1) 
        return $n; 
    return $m<$n?gcd($n-$m,$n):gcd($n,$m-$n); 
} 	

?>
