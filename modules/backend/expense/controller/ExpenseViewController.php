<?php

	class ExpenseViewController extends BackViewController
	{

		public static function showAction($param)
		{
            $expense = new Expense();

			return json_encode($expense->all());
		}

		public static function addAction()
		{
			try {

				$data = Core::getParams('post');

				Core::require_data([
					$data['title'] => ['notempty','string'],
					$data['price'] => ['notempty','numeric']
				]);

                $expense = new Expense();
                $expense->title = $data['title'];
                $expense->price = $data['price'];
                $saved = $expense->Create();

                error_log($saved);

				if($saved)
					return Core::json(array("new_id"=>$expense->Id), true, "Expense ajouté avec succès.");
				else
					throw new Exception("Erreur d'enregistrement lors de la requete add:expense !", 1);

			} catch (Exception $e) {
				
				return Core::json(array(), false, $e->getMessage());
			}
		}

		public static function editAction($id)
		{
			try {

				$data = Core::getParams('post');

				Core::require_data([
					$id 			=> ['notempty','numeric'],
					$data['title'] 	=> ['notempty','string'],
					$data['price'] 	=> ['notempty','numeric']
				]);

				if(ExpenseModel::edit($id, $data['title'], $data['price']))
					return Core::json(array(), true, "Expense edité avec succès.");
				else
					throw new Exception("Erreur d'enregistrement lors de la requete edit:expense:".$id." !", 1);

			} catch (Exception $e) {
				
				return Core::json(array(), false, $e->getMessage());
			}
		}

		public static function deleteAction($id)
		{
			try {

				Core::require_data([
					$id => ['notempty','numeric']
				]);

				if(ExpenseModel::delete($id))
					return Core::json(array(), true, "Expense supprimé avec succès.");
				else
					throw new Exception("Erreur de suppression de l'expense delete:expense:".$id." !", 1);
				
			} catch (Exception $e) {
				
				return Core::json(array(), false, $e->getMessage());
			}
		}

	}