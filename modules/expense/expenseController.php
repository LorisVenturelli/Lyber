<?php

	class expenseController implements ModuleController
	{

		public static function view($param)
		{
			return json_encode(expenseModel::getAll());
		}

		public static function add()
		{
			try {

				$data = Core::getParams('post');

				Core::require_data([
					$data['title'] => ['notempty','string'],
					$data['price'] => ['notempty','numeric']
				]);

				$new_id = expenseModel::add($data['title'], $data['price']);

				if(is_numeric($new_id))
					return Core::json(array("new_id"=>$new_id), true, "Expense ajouté avec succès.");
				else
					throw new Exception("Erreur d'enregistrement lors de la requete add:expense !", 1);

			} catch (Exception $e) {
				
				return Core::json(array(), false, $e->getMessage());
			}
		}

		public static function edit($id)
		{
			try {

				$data = Core::getParams('post');

				Core::require_data([
					$id 			=> ['notempty','numeric'],
					$data['title'] 	=> ['notempty','string'],
					$data['price'] 	=> ['notempty','numeric']
				]);

				if(expenseModel::edit($id, $data['title'], $data['price']))
					return Core::json(array(), true, "Expense edité avec succès.");
				else
					throw new Exception("Erreur d'enregistrement lors de la requete edit:expense:".$id." !", 1);

			} catch (Exception $e) {
				
				return Core::json(array(), false, $e->getMessage());
			}
		}

		public static function delete($id)
		{
			try {

				Core::require_data([
					$id => ['notempty','numeric']
				]);

				if(expenseModel::delete($id))
					return Core::json(array(), true, "Expense supprimé avec succès.");
				else
					throw new Exception("Erreur de suppression de l'expense delete:expense:".$id." !", 1);
				
			} catch (Exception $e) {
				
				return Core::json(array(), false, $e->getMessage());
			}
		}

	}