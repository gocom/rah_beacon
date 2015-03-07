<?php

/**
 * Rah_beacon plugin for Textpattern CMS.
 *
 * @author  Jukka Svahn
 * @license GNU GPLv2
 * @link    https://github.com/gocom/rah_beacon
 * 
 * Copyright (C) 2013 Jukka Svahn http://rahforum.biz
 * Licensed under GNU General Public License version 2
 * http://www.gnu.org/licenses/gpl-2.0.html
 */

/**
 * The plugin class.
 */

class Rah_Beacon
{
    /**
     * Constructor.
     */

    public function __construct()
    {
        Txp::get('TagRegistry')->register(array($this, 'atts'), 'rah_beacon_atts');
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

        $beacon = new Rah_Beacon_Handler();

        foreach ($forms as $name) {
            if (!preg_match('/^[a-z][a-z0-9_]*$/', $name)) {
                trace_add('[rah_beacon: '.$name.' skipped]');
                continue;
            }

            Txp::get('Textpattern_Tag_Registry')->register(array($beacon, $name), $name);
        }
    }

    /**
     * A tag for creating attribute defaults.
     *
     * @param array $atts Attributes
     * @example
     * &lt;txp:rah_beacon_atts variable1="value" variable2="value" [...] /&gt;
     */

    public function atts($atts)
    {
        global $variable;
    
        foreach (lAtts($atts, $variable, false) as $name => $value) {
            $variable[$name] = $value;
        }
    }
}

/**
 * Creates lighthouse members.
 */

class Rah_Beacon_Handler
{
    /**
     * Handles calling the tag template.
     *
     * @param  string $alias Tag name
     * @param  string $args  Arguments
     * @return string
     */

    public function __call($alias, $args)
    {
        global $variable;

        $original = (array) $variable;

        list($atts, $thing) = $args;

        if ($thing !== null) {
            $atts['thing'] = $atts['true'] = parse(EvalElse($thing, true));
            $atts['false'] = parse(EvalElse($thing, false));
        }

        $variable = array_merge($original, $atts);
        $out = output_form(array('form' => $alias), $thing);

        foreach ($atts as $name => $value) {
            unset($variable[$name]);

            if (isset($original[$name])) {
                $variable[$name] = $original[$name];
            }
        }

        return $out;
    }
}

new Rah_Beacon();
