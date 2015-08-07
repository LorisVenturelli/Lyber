<?php

	class LoginModel extends ModuleModel
	{
		public static function getOne($param)
		{
			GLOBAL $_DATABASE;

			$rows = $_DATABASE->row("SELECT * FROM users WHERE id = :id", array("id"=>$param));

			return $rows;
		}

		public static function getAll()
		{
			return array();
		}

		public static function getOneByEmail($email)
		{
			GLOBAL $_DATABASE;

			$rows = $_DATABASE->row("SELECT * FROM users WHERE email = :email", array("email"=>$email));

			return $rows;
		}
	}