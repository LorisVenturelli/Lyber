<?php

	interface ModuleModel
	{
		public static function getAll();

		public static function getOne($param);
	}