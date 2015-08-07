<?php

	class ErrorViewController extends BackViewController
	{

		public static function showAction($param)
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