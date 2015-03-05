<?php

	class ProverbesController implements ModuleController
	{

		public static function view($param)
		{
			Core::require_data([
				$param => ['int']
			]);

			if(!empty($param))
				$return = ProverbesModel::getOne($param);
			else
				$return = ProverbesModel::getAll();
			
			if(count($return) == 0)
				$return = array('Aucun proverbe');

			return $return;
		}

		public static function edit($id)
		{
			Core::require_data([
				$id => ['notempty','int']
			]);

			return ProverbesModel::getOne($id);
		}

		public static function insert($id)
		{
			Core::require_data([
				$id => ['notempty','int']
			]);

			return ProverbesModel::getOne($id);
		}

		public static function delete($id)
		{
			Core::require_data([
				$id => ['notempty','int']
			]);

			return ProverbesModel::getOne($id);
		}

	}