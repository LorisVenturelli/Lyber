<?php

	class ErrorViewController extends ModuleViewController
	{

		public static function viewAction($param)
		{
			Core::redirect('error/404');

			return array();
		}

		public static function _403Action()
		{
			return array();
		}

		public static function _404Action()
		{
			return array();
		}

		public static function _500Action()
		{
			return array();
		}

	}