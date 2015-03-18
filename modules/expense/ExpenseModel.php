<?php

	class ExpenseModel extends ModuleModel
	{
		public static function getOne($param)
		{
			GLOBAL $_DATABASE;

			$rows = $_DATABASE->row("SELECT * FROM expenses WHERE id = :id", array("id"=>$param));

			return $rows;
		}

		public static function getAll()
		{
			GLOBAL $_DATABASE;

			$rows = $_DATABASE->query("SELECT * FROM expenses");

			return $rows;
		}

		public static function add($title, $price)
		{
			GLOBAL $_DATABASE;
			
			$requete = $_DATABASE->query("INSERT INTO `expenses` (title, price) VALUES ('".$title."', '".$price."')");

			if($requete > 0)
				$new_id = $_DATABASE->single("SELECT id FROM expenses ORDER BY id DESC LIMIT 1");
			else
				throw new Exception("Erreur : last id non receptionnée ! ", 1);
				
			return $new_id;
		}

		public static function edit($id, $title, $price)
		{
			GLOBAL $_DATABASE;

			$price = floatval($price);
			
			$requete = $_DATABASE->query("UPDATE `expenses` SET title = :title, price = :price WHERE id = :id", 
										  array("id"=>$id, "title"=>$title, "price"=>$price));
			if($requete > 0)
				return true;
			else
				return false;
		}

		public static function delete($id)
		{
			GLOBAL $_DATABASE;
			
			$requete = $_DATABASE->query("DELETE FROM expenses WHERE id = :id", array("id"=>$id));
			
			if($requete > 0)
				return true;
			else
				return false;
		}
	}
