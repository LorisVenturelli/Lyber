<?php

	class loginController implements ModuleController
	{

		public static function view($param)
		{
			return array();
		}

		public static function APIConnectApp()
		{
			GLOBAL $_DATABASE;

			$data = Core::getParams('post');

			error_log(print_r($data,true));

			try {
				
				Core::require_data([
					$data['login'] => ['notempty','string'],
					$data['password'] => ['notempty','string']
				]);

				$user = loginModel::getOneByEmail($data['login']);

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