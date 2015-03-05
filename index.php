<?php

require_once(__DIR__."/vendor/autoload.php");

Config::load(Core::getRoot()."config/global.ini");

// Library Logger
$_LOG = new \Monolog\Logger('UserName');
$_LOG->pushHandler(new \Monolog\Handler\StreamHandler(Core::getRoot().'logs/'.date('Y-m-d').'.txt', \Monolog\Logger::WARNING));
// $log->addWarning('Foo');
// $log->addError('Bar');

// Library Handle Errors
// $whoops = new \Whoops\Run;
// $whoops->pushHandler(new \Whoops\Handler\PrettyPageHandler);
// $whoops->pushHandler(function ($exception, $inspector, $run) {
// 	if(Config::get("global", "dev_mode") == "0") {
// 		GLOBAL $_LOG;
// 		$_LOG->addError($exception->getMessage());
//     	Email::send(Config::get("mails", "alertes_dev"),'Error exception non encapsulé !','Exception : '.$exception->getMessage().'<br>Code : '.$exception->getCode().'<br>File : '.$exception->getFile().'<br>Line : '.$exception->getLine().'<br>Previous : '.$exception->getPrevious().'<br><br>$_REQUEST : <pre>'.print_r($_SERVER,true).'</pre>');
//     	Core::redirect('error/500');
// 	}
// });
// $whoops->register();

// BDD
$_DATABASE = new Database();

// Routage avec librairie Flight
Flight::route('(/@module(/@function(/@param)))', function($module, $function, $param) use ($_DATABASE) {

	Core::getRequest();

	// Module par défault
	$module = (empty($module)) ? "dashboard" : $module;
	// Fonction par défault
	$function = (empty($function)) ? "view" : (is_numeric($function) ? "_".$function : $function);

	// Définition des noms des fichiers MVC
	$moduleController = ucfirst($module)."Controller";
	$moduleModel = ucfirst($module)."Model";

	
	// Test existance fichier config du view module
	if(file_exists('modules/'.$module.'/config/'.$function.'.ini'))
		$config = parse_ini_file('modules/'.$module.'/config/'.$function.'.ini', true);
	else
		throw new Exception('Fichier de config '.$function.'.ini du module '.$module.' non trouvée !', 1);

	// Test existance model
	if(file_exists('modules/'.$module.'/'.$moduleModel.'.php'))
		require_once('modules/'.$module.'/'.$moduleModel.'.php');
	else
		throw new Exception('Le '.$moduleModel.'.php du module '.$module.' non trouvée !', 1);

	// Test existance controller
	if(file_exists('modules/'.$module.'/'.$moduleController.'.php'))
		require_once('modules/'.$module.'/'.$moduleController.'.php');
	else
		throw new Exception('Le '.$moduleController.'.php du module '.$module.' non trouvée !', 1);

	// Test existance class controller
	if(!class_exists($moduleController))
		throw new Exception('Class '.$moduleController.' n\'existe pas !', 1);
	else if(!method_exists($moduleController, $function))
		throw new Exception('Function '.$moduleController.'::'.$function.' n\'existe pas !', 1);
	else
		$data = $moduleController::$function($param);
	
	// Paramètre fullpage du view module
	if($config['param']['fullpage'] == "0")
	{
		// Inclusion du header
		ob_start();
		
		    include("template/header.phtml");
		    $content_header = ob_get_contents();

	    ob_end_clean();
		
		// Inclusion du sidebar
		ob_start();
		
		    include("template/sidebar.phtml");
		    $content_sidebar = ob_get_contents();

	    ob_end_clean();

		// Inclusion du footer
		ob_start();
		
		    include("template/footer.phtml");
		    $content_footer = ob_get_contents();

	    ob_end_clean();
	} 
	else {
		$content_header = $content_sidebar = $content_footer = NULL;
	}

	// HTML template du view module
	ob_start();
	
	    include("modules/".$module."/view/".$function.".phtml");
	    $content_html = ob_get_contents();

    ob_end_clean();


    echo $content_header . $content_sidebar . $content_html . $content_footer;

});

// Flight::route('POST (/@module(/@function(/@param)))', function($module, $function, $param) use ($_DATABASE) {

// 	if(empty($module))
// 		die(Core::json(array('message'=>'module manquant'), false));
// 	else if(empty($function))
// 		die(Core::json(array('message'=>'function manquant'), false));
// 	else if(empty($param))
// 		die(Core::json(array('message'=>'param manquant'), false));

// 	require_once('modules/'.$module.'/model.php');

// 	$module = ucfirst($module)."Model";

// 	$data = $module::edit($param, $_POST);

// 	Core::json($data);

// });

// Flight::route('PUT (/@module(/@function))', function($module, $fonction) use ($_DATABASE) {

// 	Core::json(array("message"=>"PUT header method not supported, use POST method for insert or edit"), false);

// });


// Flight::route('DELETE (/@module(/@function(/@param)))', function($module, $function, $param) use ($_DATABASE) {

// 	if(empty($module))
// 		die(Core::json(array('message'=>'module manquant'), false));
// 	else if(empty($function))
// 		die(Core::json(array('message'=>'function manquant'), false));
// 	else if(empty($param))
// 		die(Core::json(array('message'=>'param manquant'), false));

// 	require_once('modules/'.$module.'/model.php');

// 	$module = ucfirst($module)."Model";

// 	$data = $module::delete($param);

// 	Core::json($data);

// });

// Flight::set('flight.handle_errors', false);
Flight::start();
