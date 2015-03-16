<?php

	class LoginViewController extends ModuleViewController
	{

		public static function showAction($param)
		{
			return array();
		}

		public static function APIConnectAppAction()
		{
			GLOBAL $_DATABASE;

			$data = Core::getParams('post');

			try {
				
				Core::require_data([
					$data['login'] => ['notempty','string'],
					$data['password'] => ['notempty','string']
				]);

				$user = LoginModel::getOneByEmail($data['login']);

				error_log(print_r($user,true));

				if(empty($user['password']) || ($user['password'] != md5($data['password']))) {
					throw new Exception("Login ou password incorrect.", 1);
				}

			} catch (Exception $e) {

				return Core::json(array(), false, $e->getMessage());
				
			}

			return Core::json(array(), true, 'Connexion avec succès.');
		}
		
	}