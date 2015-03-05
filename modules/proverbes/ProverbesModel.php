<?php

	class ProverbesModel implements ModuleModel
	{
		public static function getAll()
		{
			GLOBAL $_DATABASE;

			$rows = $_DATABASE->query('SELECT content FROM proverbes');

			return $rows;
		}

		public static function getOne($param)
		{
			GLOBAL $_DATABASE;

			$rows = $_DATABASE->query("SELECT content FROM proverbes WHERE  id = :id", array("id"=>$param));

			return $rows;
		}

		public static function edit($id, $data)
		{
			GLOBAL $_DATABASE;

			$return = array();

		    if(empty($data['content']))
		    	$return[] = $data;
		    else if($_DATABASE->prepare("UPDATE `proverbes` SET content = '".$data['content']."' WHERE id = '".$id."'")->execute())
		    	$return[] = 'success';
		    else
		    	$return[] = 'error';

		    return $return;
		}

		public static function insert($data)
		{
			GLOBAL $_DATABASE;

			$return = array();

			$sql = self::$_DATABASE->prepare("INSERT INTO `proverbes` (content) VALUES ('".$data->content."')")->execute();

		    if(empty($content))
		    	$return[] = 'message vide';
		    else if($sql)
		    	$return[] = self::$_DATABASE->lastInsertId();
		    else
		    	$return[] = 'error insert';

		    return $return;
		}

		public static function delete($id)
		{
			GLOBAL $_DATABASE;

			$return = array();

		    if(empty($id))
		    	$return[] = 'id vide';
		    else if(self::$_DATABASE->prepare("DELETE FROM `proverbes` WHERE id = '".$id."'")->execute())
		    	$return[] = 'success delete';
		    else
		    	$return[] = 'error delete';

		    return $return;
		}
	}