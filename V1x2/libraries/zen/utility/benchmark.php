<?php
/**
 * @package     Zen Library
 * @subpackage  Zen Library
 * @author      Joomla Bamboo - design@joomlabamboo.com
 * @copyright   Copyright (c) 2013 Joomla Bamboo. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @version     1.0.2
 */

// Check to ensure this file is within the rest of the framework
defined('_JEXEC') or die();

final class ZenUtilityBenchmark
{
	protected static $stacks = array();

	public static function start($name = 'stack')
	{
		$stack = new stdClass;
		$stack->name = $name;
		$stack->time = microtime();
		$stack->memory = memory_get_usage();

		self::$stacks[$name] = $stack;
	}

	public static function stop($name = 'stack')
	{
		$stack = self::$stacks[$name];
		$stack->time = microtime() - $stack->time;
		$stack->memory = memory_get_usage() - $stack->memory;

		var_dump($stack);

		unset($stack, self::$stacks[$name]);
	}
}
