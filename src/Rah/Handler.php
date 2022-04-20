<?php

/*
 * rah_beacon - Create alias tags using form partials in Textpattern CMS
 * https://github.com/gocom/rah_beacon
 *
 * Copyright (C) 2015 Jukka Svahn
 *
 * This file is part of rah_beacon.
 *
 * rah_beacon is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation, version 2.
 *
 * rah_beacon is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with rah_beacon. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 * Redirects tag calls to the proper Form partial.
 *
 * ```
 * $handler = new Rah_Beacon_Handler();
 * $handler->formpartialname(array(
 *     'variable1' => 'value',
 *     'variable2' => 'value',
 * ));
 * ```
 */
class Rah_Beacon_Handler
{
    /**
     * Handles routing the called template tag to the form.
     *
     * @param string $alias Tag name
     * @param string $args Arguments
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
