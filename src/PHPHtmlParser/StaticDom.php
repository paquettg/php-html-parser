<?php
namespace PHPHtmlParser;

final class StaticDom {

	private static $dom = null;

	public static function __callStatic($method, $arguments)
	{
		if (self::$dom instanceof Dom)
		{
			return call_user_func_array([self::$dom, $method], $arguments);
		}
		else
		{
			throw new Exception('The dom is not loaded. Can not call a dom method.');
		}
	}

	public static function mount($className = 'Dom', Dom $dom = null)
	{
		if (class_exists($className))
		{
			return false;
		}
		class_alias(__CLASS__, $className);
		if ($dom instanceof Dom)
		{
			self::$dom = $dom;
		}
		return true;
	}

	public static function load($str)
	{
		$dom       = new Dom;
		self::$dom = $dom;
		return $dom->load($str);
	}

	public static function loadFromFile($file)
	{
		$dom = new Dom;
		return $dom->loadFromFile($file);
	}

	public static function loadFromUrl($url)
	{
		$dom       = new Dom;
		self::$dom = $dom;
		return $dom->loadFromUrl($url);
	}
}
