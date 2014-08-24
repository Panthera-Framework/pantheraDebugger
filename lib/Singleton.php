<?php
/**
 * Singleton implementation
 *
 * @author Damian Kęska <damian.keska@fingo.pl>
 */

abstract class pSingleton
{
	public static $__instance = array();

	/**
	 * Get self instance, if no any then create a new instance
	 *
	 * @author Damian Kęska <damian.keska@fingo.pl>
	 * @return static
	 */
	public static function getInstance()
	{
		$c = get_called_class();

		if (!isset(static::$__instance[$c]))
			static::$__instance[get_called_class()] = new static;

		return static::$__instance[$c];
	}

	/**
	 * Allow adding self instance from any other class method
	 *
	 * @author Damian Kęska <damian.keska@fingo.pl>
	 * @return null
	 */

	public static function addInstance($this)
	{
		static::$__instance[get_called_class()] = $this;
	}
}