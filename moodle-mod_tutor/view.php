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
 * Prints a particular instance of tutor
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_tutor
 * @copyright  2019 Sergio Comerón <sergiocomeron@icloud.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');

global $USER;

$id = optional_param('id', 0, PARAM_INT);
$n  = optional_param('n', 0, PARAM_INT);
if ($id) {
    $cm = get_coursemodule_from_id('tutor', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $tutor = $DB->get_record('tutor', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($n) {
    $tutor  = $DB->get_record('tutor', array('id' => $n), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $tutor->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('tutor', $tutor->id, $course->id, false, MUST_EXIST);
} else {
    print_error('missingparam');
}
require_login($course, true, $cm);
$event = \mod_tutor\event\course_module_viewed::create(array(
  'objectid' => $PAGE->cm->instance,
  'context' => $PAGE->context,
));
$event->add_record_snapshot('course', $PAGE->course);
$event->add_record_snapshot($PAGE->cm->modname, $tutor);
$event->trigger();
$PAGE->set_url('/mod/tutor/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($tutor->name));
$PAGE->set_heading(format_string($course->fullname));
echo $OUTPUT->header();
echo $OUTPUT->heading($tutor->name);
$context = context_module::instance($cm->id);
if (!has_capability('mod/tutor:view', $context)) {
    notice(get_string('noviewpermission', 'tutor'));
}
$courseid = $course->id;
$context = context_course::instance($courseid);

$roles = get_user_roles($context, $USER->id);

$rolestr[] = null;
foreach ($roles as $role) {
    $rolestr[] = $role->shortname;
}
if ($tutor->intro) {
    echo $OUTPUT->box(format_module_intro('tutor', $tutor, $cm->id), 'generalbox mod_introbox', 'tutorintro');
}


$moderation = false;
if (has_capability('mod/tutor:moderation', $context)) {
    $moderation = true;
}

$nom = null;
switch ($CFG->tutor_id) {
    case 'username':
        $nom = $USER->username;
        break;
    case 'nameandsurname':
        $nom = $USER->firstname.' '.$USER->lastname.' '.$USER->email;
        break;
    case 'alias':
        break;
}
$sessionoptionsparam = ['$course->shortname', '$tutor->id', '$tutor->name'];
$fieldssessionname = $CFG->tutor_sesionname;

$allowed = explode(',', $fieldssessionname);
$max = count($allowed);

// $sesparam = '';
// $optionsseparator = ['.', '-', '_', ''];
// for ($i = 0; $i < $max; $i++) {
//     if ($i != $max - 1) {
//         if ($allowed[$i] == 0) {
//             $sesparam .= string_sanitize($course->shortname).$optionsseparator[$CFG->tutor_separator];
//         } else if ($allowed[$i] == 1) {
//             $sesparam .= $tutor->id.$optionsseparator[$CFG->tutor_separator];
//         } else if ($allowed[$i] == 2) {
//             $sesparam .= string_sanitize($tutor->name).$optionsseparator[$CFG->tutor_separator];
//         }
//     } else {
//         if ($allowed[$i] == 0) {
//             $sesparam .= string_sanitize($course->shortname);
//         } else if ($allowed[$i] == 1) {
//             $sesparam .= $tutor->id;
//         } else if ($allowed[$i] == 2) {
//             $sesparam .= string_sanitize($tutor->name);
//         }
//     }
// }

// $avatar = $CFG->wwwroot.'/user/pix.php/'.$USER->id.'/f1.jpg';
// $urlparams = array('avatar' => $avatar, 'nom' => $nom, 'ses' => $sesparam,
//     'courseid' => $course->id, 'cmid' => $id, 't' => $moderation);

// echo $OUTPUT->single_button(new moodle_url('/mod/tutor/session.php', $urlparams), $tutor->chapter_id, 'post');
// echo $OUTPUT->single_button(new moodle_url('https://www.yandex.ru/', $urlparams), 'Яндекс', 'post');

// echo $OUTPUT->single_button(new moodle_url('/mod/tutor/session.php', $urlparams), $USER->firstname, 'post');
// echo $OUTPUT->single_button(new moodle_url('/mod/tutor/session.php', $urlparams), $USER->lastname, 'post');
// echo $OUTPUT->single_button(new moodle_url('/mod/tutor/session.php', $urlparams), $USER->email, 'post');

$obj = $DB->get_records('sessionlog');
$table = new html_table();
$table->head = array('Номер шага','Время, сек', 'Количество ошибок');
$timeOffset = 0;
$mistake = 0;
$all_time = 0;
$all_mistake = 0;
foreach ($obj as $str) {
    $timeOffset += (int)$str['time_offset'];
    $mistake += (int)$str['mistake']; 
    $all_mistake += (int)$str['mistake'];
    if ($str['mistake'] != 1) {
        $timeOffset = number_format((float)($timeOffset / 1000), 2, '.', '');
        $table->data[] = array($str['frame_number'], $timeOffset, $mistake);  
        $all_time += $timeOffset;
        $timeOffset = 0;
        $mistake = 0;
     }
}
$table->data[] = array("<b>Общее количество ошибок</b>", ' ', "<b>{$all_mistake}</b>");  
$table->data[] = array("<b>Затраченное время</b>", ' ', "<b>{$all_time}</b> <b>сек</b>");  
echo html_writer::table($table);