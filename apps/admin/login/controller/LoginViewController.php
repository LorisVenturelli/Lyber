<?php

namespace Lyber\Apps\Admin\Login;

use Exception;
use Lyber\Common\Components\Auth;
use Lyber\Common\Components\Core;
use Lyber\Common\Controllers\ViewController;
use Lyber\Common\Entities\User;

class LoginViewController extends ViewController
{

	public static function showAction($param)
	{

		$email = Core::getParam('email','','post');
		$password = Core::getParam('password','','post');

		$return = array();
		$authentification = false;

		if(!empty($email) && !empty($password)){

			try {

				$authentification = Auth::login($email, $password);

				if($authentification || Auth::isLogged() !== false){
					Core::redirect(Core::absURL()."admin");
					exit;
				}

			}
			catch(Exception $e) {
				$return['error'] = $e->getMessage();
			}

		}

		return $return;
	}

	public static function disconnectAction($param){

		Auth::logout();

		Core::redirect(Core::absURL().'admin');

		return array();

	}

	public static function lockAction($param){

		if(Auth::lock() === false){
			Core::redirect(Core::absURL()."admin/login");
		}

		$password = Core::getParam('password','','post');

		$return = array();
		$authentification = false;

		if(!empty($password)){

			try {

				$authentification = Auth::login(User::getInstance()->getEmail(), $password);

				if($authentification || Auth::isLogged() !== false){
					Core::redirect(Core::absURL()."admin");
					exit;
				}

			}
			catch(Exception $e) {
				$return['error'] = $e->getMessage();
			}

		}

		return $return;

	}

}