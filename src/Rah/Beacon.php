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
 * The plugin class.
 */
class Rah_Beacon
{
    const TAG_PATTERN = '/^[a-z][a-z0-9_]*$/';

    /**
     * @var \Textpattern\Tag\Registry
     */
    private $registry;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->registry = Txp::get('Textpattern\Tag\Registry');

        $this->registry->register(
            [$this, 'atts'],
            'rah_beacon_atts'
        );

        register_callback([$this, 'registerFormsAsTags'], 'pretext');
    }

    /**
     * Registers forms as tags.
     */
    public function registerFormsAsTags()
    {
        $forms = safe_column(
            'name',
            'txp_form',
            '1 = 1'
        );

        $handler = new Rah_Beacon_Handler();

        foreach ($forms as $name) {
            if (!preg_match(self::TAG_PATTERN, $name)) {
                trace_add("[rah_beacon: $name skipped, naming is not a valid tag]");
                continue;
            }

            if ($this->registry->isRegistered($name)) {
                trace_add("[rah_beacon: $name skipped, tag with same name already exists]");
                continue;
            }

            $this->registry->register(
                [$handler, $name],
                $name
            );
        }
    }

    /**
     * A tag for assigning attribute defaults for tags.
     *
     * This tag should be called within the Form partial if
     * it requires defaults for it's variables.
     *
     * ```
     * <txp:rah_beacon_atts color="blue" size="small" />
     * ```
     *
     * The above would create a variable named "color" and "size"
     * with values "blue" and "small" if one of them isn't
     * specified as attributes in the tag calling the form.
     *
     * @param  array $atts Attributes
     *
     * @return void
     */
    public function atts($atts)
    {
        global $variable;

        foreach (lAtts($atts, $variable, false) as $name => $value) {
            $variable[$name] = $value;
        }
    }
}
