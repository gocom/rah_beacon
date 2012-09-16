<?php

/**
 * Rah_beacon plugin for Textpattern CMS.
 *
 * @author Jukka Svahn
 * @date 2012-
 * @license GNU GPLv2
 * @link https://github.com/gocom/rah_beacon
 * 
 * Copyright (C) 2012 Jukka Svahn <http://rahforum.biz>
 * Licensed under GNU Genral Public License version 2
 * http://www.gnu.org/licenses/gpl-2.0.html
 */

	new rah_beacon();

class rah_beacon {
	
	/**
	 * Constructor
	 */
	
	public function __construct() {
		
		$rs = 
			safe_rows(
				'name',
				'txp_form',
				"name LIKE 'rah\_beacon\__%'"
			);
		
		$beacon = new rah_beacons();
		$prefix = strlen(__CLASS__)+1;
		
		foreach($rs as $a) {
			$name = substr($a['name'], $prefix);
			if(preg_match('/^[a-z]+[a-z0-9_]*$/', $name)) {
				$beacon->$name();
			}
		}
	}
	
	/**
	 * Handles tag calls
	 */
	
	static public function operator($alias, $atts, $thing=null) {
		global $variable;
		
		$original = (array) $variable;
		
		if($thing !== null) {
			$atts['thing'] = $atts['true'] = parse(EvalElse($thing, true));
			$atts['false'] = parse(EvalElse($thing, false));
		}
		
		$variable = array_merge($original, $atts);
		$out = output_form(array('form' => __CLASS__ . '_' . $alias), $thing);
		
		foreach($atts as $name) {
			unset($variable[$name]);
			
			if(isset($original[$name])) {
				$variable[$name] = $original[$name];
			}
		}
		
		return $out;
	}
}

/**
 * Creates lighthouse members
 */

class rah_beacons {
	public function __call($name, $arguments) {
	
		if(function_exists($name)) {
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

?>