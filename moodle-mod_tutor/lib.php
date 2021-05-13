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
 * Library of interface functions and constants for module tutor
 *
 * All the core Moodle functions, neeeded to allow the module to work
 * integrated in Moodle should be placed here.
 *
 * All the tutor specific functions, needed to implement all the module
 * logic, should go to locallib.php. This will help to save some memory when
 * Moodle is performing actions across all modules.
 *
 * @package    mod_tutor
 * @copyright  2019 Sergio Comerón Sánchez-Paniagua <sergiocomeron@icloud.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/* Moodle core API */

/**
 * Returns the information on whether the module supports a feature
 *
 * See {@link plugin_supports()} for more info.
 *
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function tutor_supports($feature) {

    switch($feature) {
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        default:
            return null;
    }
}

/**
 * Saves a new instance of the tutor into the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will create a new instance and return the id number
 * of the new instance.
 *
 * @param stdClass $tutor Submitted data from the form in mod_form.php
 * @param mod_tutor_mod_form $mform The form instance itself (if needed)
 * @return int The id of the newly inserted tutor record
 */
function tutor_add_instance($tutor,  $mform = null) {
    global $CFG, $DB;
    require_once($CFG->dirroot.'/mod/tutor/locallib.php');

    $tutor->timecreated = time();
    $cmid       = $tutor->coursemodule;

    $tutor->id = $DB->insert_record('tutor', $tutor);
    tutor_update_calendar($tutor, $cmid);

    return $tutor->id;
}

/**
 * Updates an instance of the tutor in the database
 *
 * Given an object containing all the necessary data,
 * (defined by the form in mod_form.php) this function
 * will update an existing instance with new data.
 *
 * @param stdClass $tutor An object from the form in mod_form.php
 * @param mod_tutor_mod_form $mform The form instance itself (if needed)
 * @return boolean Success/Fail
 */
function tutor_update_instance($tutor,  $mform = null) {
    global $CFG, $DB;
    require_once($CFG->dirroot.'/mod/tutor/locallib.php');

    $tutor->timemodified = time();
    $tutor->id = $tutor->instance;
    $cmid       = $tutor->coursemodule;

    $result = $DB->update_record('tutor', $tutor);
    tutor_update_calendar($tutor, $cmid);

    return $result;
}

/**
 * This standard function will check all instances of this module
 * and make sure there are up-to-date events created for each of them.
 * If courseid = 0, then every assignment event in the site is checked, else
 * only assignment events belonging to the course specified are checked.
 *
 * @param int $courseid
 * @param int|stdClass $instance tutor module instance or ID.
 * @param int|stdClass $cm Course module object or ID.
 * @return bool
 */
function tutor_refresh_events($courseid = 0, $instance = null, $cm = null) {
    global $CFG, $DB;
    require_once($CFG->dirroot . '/mod/tutor/locallib.php');

    if (isset($instance)) {
        if (!is_object($instance)) {
            $instance = $DB->get_record('tutor', array('id' => $instance), '*', MUST_EXIST);
        }
        if (isset($cm)) {
            if (!is_object($cm)) {
                $cm = (object)array('id' => $cm);
            }
        } else {
            $cm = get_coursemodule_from_instance('tutor', $instance->id);
        }
        tutor_update_calendar($instance, $cm->id);
        return true;
    }

    if ($courseid) {
        if (!is_numeric($courseid)) {
            return false;
        }
        if (!$tutors = $DB->get_records('tutor', array('course' => $courseid))) {
            return true;
        }
    } else {
        return true;
    }

    foreach ($tutors as $tutor) {
        $cm = get_coursemodule_from_instance('tutor', $tutor->id);
        tutor_update_calendar($tutor, $cm->id);
    }

    return true;
}

/**
 * Removes an instance of the tutor from the database
 *
 * Given an ID of an instance of this module,
 * this function will permanently delete the instance
 * and any data that depends on it.
 *
 * @param int $id Id of the module instance
 * @return boolean Success/Failure
 */
function tutor_delete_instance($id) {
    global $CFG, $DB;

    if (! $tutor = $DB->get_record('tutor', array('id' => $id))) {
        return false;
    }

    $result = true;

    if (! $DB->delete_records('tutor', array('id' => $tutor->id))) {
        $result = false;
    }

    return $result;
}

function tutor_myprofile_navigation(core_user\output\myprofile\tree $tree, $user, $iscurrentuser, $course) {
    global $DB, $CFG, $USER;
    if ($CFG->tutor_privatesessions == 1) {
        $urlparams = array('user' => $user->id);
        $url = new moodle_url('/mod/tutor/viewpriv.php', $urlparams);
        $category = new core_user\output\myprofile\category('tutor',
            get_string('tutor', 'tutor'), null);
        $tree->add_category($category);
        if ($iscurrentuser == 0) {
            $node = new core_user\output\myprofile\node('tutor', 'tutor',
                get_string('privatesession', 'tutor', $user->firstname), null, $url);
        } else {
            $node = new core_user\output\myprofile\node('tutor', 'tutor',
                get_string('myprivatesession', 'tutor'), null, $url);
        }
        $tree->add_node($node);
    }
    return true;
}
