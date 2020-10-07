<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package local_metadata
 * @author Mike Churchward <mike.churchward@poetopensource.org>
 * @author Celine Pervès <cperves@unistra.fr>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright 2017, onwards Poet
 * @copyright  2020 Université de Strasbourg {@link http://unistra.fr}
 */
defined('MOODLE_INTERNAL') || die();

$capabilities = array(
        'local/metadata:editcategory' => array(
                'captype' => 'write',
                'contextlevel' => CONTEXT_SYSTEM,
                'archetypes' => array(
                        'coursecreator' => CAP_INHERIT,
                        'manager' => CAP_ALLOW
                ),
        ),
        'local/metadata:editcourse' => array(
                'captype' => 'write',
                'contextlevel' => CONTEXT_SYSTEM,
                'archetypes' => array(
                        'coursecreator' => CAP_INHERIT,
                        'manager' => CAP_ALLOW
                ),
        ),
        'local/metadata:editcohort' => array(
                'captype' => 'write',
                'contextlevel' => CONTEXT_SYSTEM,
                'archetypes' => array(
                        'coursecreator' => CAP_INHERIT,
                        'manager' => CAP_ALLOW
                ),
        ),
        'local/metadata:editgroup' => array(
                'captype' => 'write',
                'contextlevel' => CONTEXT_SYSTEM,
                'archetypes' => array(
                        'coursecreator' => CAP_INHERIT,
                        'manager' => CAP_ALLOW
                ),
        ),
        'local/metadata:editmodule' => array(
                'captype' => 'write',
                'contextlevel' => CONTEXT_SYSTEM,
                'archetypes' => array(
                        'coursecreator' => CAP_INHERIT,
                        'manager' => CAP_ALLOW
                ),
        ),
        'local/metadata:edituser' => array(
                'captype' => 'write',
                'contextlevel' => CONTEXT_SYSTEM,
                'archetypes' => array(
                        'coursecreator' => CAP_INHERIT,
                        'manager' => CAP_ALLOW
                ),
        ),
);