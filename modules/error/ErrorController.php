<?php

	class ErrorController implements ModuleController
	{

		public static function view($param)
		{
			Core::redirect('error/404');

			return array();
		}

		public static function _403()
		{
			return array();
		}

		public static function _404()
		{
			return array();
		}

		public static function _500()
		{
			return array();
		}

	}