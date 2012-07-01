<?php

class Main extends Controller
{
	private $ratios;
	
	public function __construct()
	{
		$this->ratios = array('16:9' => 16/9, '16:10' => 16/10, '4:3' => 4/3, '5:4' => 5/4);
	}
	
	//Main page (search area mainly)
	public function index()
	{
		$sentenceList = explode("\n", trim(file_get_contents('static/sentences.txt')));
		$template = $this->loadView('main_view');
		$template->set('titleSentence', str_replace('\n', '<br />',$sentenceList[rand(0, count($sentenceList) - 1)]));
		
		if(isset($_SESSION['message']))
		{
			$template->set('message', $_SESSION['message'][0]);
			$template->set('errorMsg', $_SESSION['message'][1]);
			unset($_SESSION['message']);
		}
		
		if(isset($_SESSION['login']))
		{
			$template->set('logged', TRUE);
			$template->set('userData', $_SESSION);
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
				header('Location: '.BASE_URL.'search/'.urlencode($_POST['search']).'/'.$_POST['searchtype']);
			else
				header('Location: '.BASE_URL.'search/'.urlencode($_POST['search']));
			exit();
		}
		
		$data = urldecode($data);
		
		if(!$data)
		{
			header('Location: '.BASE_URL.'index');
			exit();
		}
		
		if($data == 'order:latest')
		{
			header('Location: '.BASE_URL.'latest');
			exit();
		}
		else if($data == 'order:random')
		{
			header('Location: '.BASE_URL.'random');
			exit();
		}
		
		$model = $this->loadModel('Walls_model');
		$keywords = explode(' ', $data);
		$results = $model->searchWallpaper($keywords, $searchtype == 'inclusive');
		//~ $related = $model->relatedKeywords($keywords);
		
		$template = $this->loadView('search_view');
		$template->set('wrelated', array());
		
		if($searchtype == 'inclusive')
			$template->set('inclusive', TRUE);
		
		if(isset($_SESSION['login']))
		{
			$template->set('logged', TRUE);
			$template->set('userData', $_SESSION);
		}
		
		$template->set('search', $data);
		$template->set('results', $results);
		$template->render();
	}
	
	public function random()
	{
		$model = $this->loadModel('Walls_model');
		$results = $model->randomWallpapers();
		
		$template = $this->loadView('search_view');
		$template->set('wrelated', array());
		if(isset($_SESSION['login']))
		{
			$template->set('logged', TRUE);
			$template->set('userData', $_SESSION);
		}
		
		$template->set('search', 'order:random');
		$template->set('results', $results);
		$template->render();
	}
	
	public function latest()
	{
		$model = $this->loadModel('Walls_model');
		$results = $model->lastWallpapers();
		
		$template = $this->loadView('search_view');
		$template->set('wrelated', array());
		if(isset($_SESSION['login']))
		{
			$template->set('logged', TRUE);
			$template->set('userData', $_SESSION);
		}
		
		$template->set('search', 'order:latest');
		$template->set('results', $results);
		$template->render();
	}
	
	public function view($id)
	{
		$model = $this->loadModel('Walls_model');
		$wall = $model->getWallpaper($id);
		
		switch($wall['rating'])
		{
			case 's':
				$wall['rating_str'] = 'Safe';
				break;
			case 'q':
				$wall['rating_str'] = 'Questionable';
				break;
			case 'e':
				$wall['rating_str'] = 'Explicit';
				break;
		}
		
		$source = explode('://', $wall['source'], 2);
		
		if($source[0] == 'spc')
		{
			switch($source[1])
			{
				case 'orig':
					$wall['source'] = 'Original';
					break;
				case 'unknown':
					$wall['source'] = 'Unknown';
					break;
			}
		}
		
		$size = explode('x', $wall['size']);
		$ratio = $size[0] / $size[1];
		
		if($r = array_search($ratio, $this->ratios))
			$wall['ratio'] = $r;
		else
		{
			$gcd = gcd($size[0], $size[1]);
			$wall['ratio'] = 'Unknown ('. ($size[0] / $gcd) .':'. ($size[1]/$gcd) .')';
		}
		
		
		//header('Location:../static/wall/'.$wall['filename']);
		$template = $this->loadView('view_view');
		
		if(isset($_SESSION['login']))
		{
			$template->set('logged', TRUE);
			$template->set('userData', $_SESSION);
		}
		
		$template->set('wallpaper', $wall);
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
