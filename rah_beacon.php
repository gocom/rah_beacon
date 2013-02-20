<?php

/**
 * Rah_beacon plugin for Textpattern CMS.
 *
 * @author  Jukka Svahn
 * @date    2012-
 * @license GNU GPLv2
 * @link    https://github.com/gocom/rah_beacon
 * 
 * Copyright (C) 2012 Jukka Svahn http://rahforum.biz
 * Licensed under GNU Genral Public License version 2
 * http://www.gnu.org/licenses/gpl-2.0.html
 */

	new rah_beacon();

/**
 * The plugin class.
 */

class rah_beacon
{
	/**
	 * Constructor.
	 */

	public function __construct()
	{
		register_callback(array($this, 'light'), 'pretext');
	}

	/**
	 * Registers forms as tags.
	 */

	public function light()
	{	
		$forms = safe_column(
			'name',
			'txp_form',
			'1 = 1'
		);

		$beacon = new rah_beacons();

		foreach ($forms as $name)
		{
			if (!preg_match('/^[a-z_][a-z0-9_]*$/', $name))
			{
				trace_add('[rah_beacon: '.$name.' skipped]');
				continue;
			}

			$token = token_get_all('<?php function '.$name.'(){} ?>');

			if (isset($token[3][0]) && $token[3][0] !== T_STRING)
			{
				trace_add('[rah_beacon: '.$name.' skipped]');
				continue;
			}

			$beacon->$name();
		}
	}

	/**
	 * Handles tag calls.
	 */

	static public function operator($alias, $atts, $thing = null)
	{
		global $variable;

		$original = (array) $variable;

		if ($thing !== null)
		{
			$atts['thing'] = $atts['true'] = parse(EvalElse($thing, true));
			$atts['false'] = parse(EvalElse($thing, false));
		}

		$variable = array_merge($original, $atts);
		$out = output_form(array('form' => $alias), $thing);

		foreach ($atts as $name)
		{
			unset($variable[$name]);

			if (isset($original[$name]))
			{
				$variable[$name] = $original[$name];
			}
		}

		return $out;
	}
}

/**
 * Creates lighthouse members
 */

class rah_beacons
{
	/**
	 * Registers a new tag handler function.
	 *
	 * @param  string $name      Tag name
	 * @param  string $arguments Not used
	 * @return bool   FALSE on error
	 */

	public function __call($name, $arguments)
	{
		if (function_exists($name))
		{
			trace_add('[rah_beacon: <txp:'.$name.' /> already reserved]');
			return false;
		}

		trace_add('[rah_beacon: <txp:'.$name.' /> created]');

		eval(<<<EOF
			function {$name}(\$atts, \$thing) {
				return rah_beacon::operator(__FUNCTION__, \$atts, \$thing);
			}
EOF
		);

		return true;
	}
}

/**
 * A tag for creating attribute defaults.
 *
 * @param array $atts Attributes
 * @example
 * &lt;txp:rah_beacon_atts variable1="value" variable2="value" [...] /&gt;
 */

	function rah_beacon_atts($atts)
	{
		global $variable;

		foreach (lAtts($atts, $variable, false) as $name => $value)
		{
			$variable[$name] = $value;
		}
	}