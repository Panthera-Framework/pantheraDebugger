<?php
/**
 * Hooking support for Troi
 *
 * @author Damian Kęska <damian.keska@fingo.pl>
 */

class pHooks/* extends pSingleton*/
{
	protected static $hooks = array(

	);

	/**
	 * Hook a function to selected slot described by $hookName
	 *
	 * @param string $hookName Slot name
	 * @param callable $callable Callback
	 * @param int $priority (Optional) Execution priority
	 * @author Damian Kęska <damian.keska@fingo.pl>
	 */

	public static function addOption($hookName, $callable, $priority=100)
	{
		if (!isset(static::$hooks[$hookName]))
			static::$hooks[$hookName] = array();

		if (is_array($callable))
		{
			if (!is_object($callable[0]) or !method_exists($callable[0], $callable[1]))
			{
				return false;
			}

		} elseif (!is_callable($callable))
		{
			return false;
		}

		static::$hooks[$hookName][] = array(
			'callable' => $callable,
			'priority' => $priority,
		);

		usort(static::$hooks[$hookName], function($a, $b) {
			return $a['priority'] - $b['priority'];
		});
	}

	/**
	 * Execute hooks from selected slot
	 *
	 * @param string $hookName Slot name
	 * @param mixed $args Argument passed to all hooked functions
	 * @param mixed $optionalArgs Optional second argument
	 * @author Damian Kęska <damian.keska@fingo.pl>
	 */

	public static function execute($hookName, $args='', $optionalArgs='')
	{
		if (isset(static::$hooks[$hookName]) and static::$hooks[$hookName])
		{
			foreach (static::$hooks[$hookName] as $hook)
			{
				$args = call_user_func_array($hook['callable'], array($args, $optionalArgs));
			}
		}

		return $args;
	}
}