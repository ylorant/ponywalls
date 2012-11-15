<?php
namespace Controller;
use \Debug;
use \Model\Exception as ExceptionModel;
use \View\View;
use \Plugin\Session;
use \Exception\NotFoundException;

class Error extends Controller {
	
	function index()
	{
		$this->error404();
	}
	
	function error404()
	{
		Debug::exception(new NotFoundException(REQUESTED_PAGE));
		$session = new Session();
		
		//We load the main page.
		$main = new Main();
		$main->index();
	}
    
    /*
     * This function is called when an exception is NOT catched and hereby terminates the script.
     * Here, we log the error and show a fancy error output.
     * 
     * TODO: log the error.
     */
    function exception($e)
    {
		$model = new ExceptionModel();
		$model->file = $e->getFile();
		$model->line = $e->getLine();
		$model->stack = $e->getTraceAsString();
		$model->message = $e->getMessage();
		$model->time = time();
		
		if(get_class($e) == '\Exception\SQLException' || is_subclass_of($e, '\Exception\SQLException'))
		{
			$model->query = $e->query;
			$model->sqlcode = $e->query;
		}
		
		$model->save();
		Debug::exception($e);
		
		$this->HTTPReturnCode(500);
		$view = new View('error');
		$view->member = null;
		$view->render();
		return true;
	}
}

?>
