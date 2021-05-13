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
 * Settings for tutor instances
 * @package   mod_tutor
 * @copyright  2019 Sergio Comerón (sergiocomeron@icloud.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    require_once($CFG->dirroot.'/mod/tutor/lib.php');
    $settings->add(new admin_setting_configtext('tutor_domain', 'Domain', 'Domain tutor Server', 'meet.jit.si'));
    $settings->add(new admin_setting_confightmleditor('tutor_help', get_string('help', 'tutor'),
        get_string('helpex', 'tutor'), null));
    $options = ['username' => get_string('username', 'tutor'),
        'nameandsurname' => get_string('nameandsurname', 'tutor'),
        'alias' => get_string('alias', 'tutor')];
    $settings->add(new admin_setting_configselect('tutor_id', get_string('identification', 'tutor'),
        get_string('identificationex', 'tutor'), null, $options));
    $sessionoptions = ['Course Shortname', 'Session ID', 'Session Name'];
    $sessionoptionsdefault = [0, 1, 2];

    $optionsseparator = ['.', '-', '_', 'empty'];
    $settings->add(new admin_setting_configselect('tutor_separator',
        get_string('separator', 'tutor'), get_string('separatorex', 'tutor'), '.', $optionsseparator));
    $settings->add(new admin_setting_configmultiselect('tutor_sesionname',
        get_string('sessionnamefields', 'tutor'), get_string('sessionnamefieldsex', 'tutor'),
        $sessionoptionsdefault, $sessionoptions));
    $settings->add(new admin_setting_configcheckbox('tutor_securitybutton', get_string('securitybutton', 'tutor'),
        get_string('securitybuttonex', 'tutor'), 0));
    $settings->add(new admin_setting_configcheckbox('tutor_invitebuttons', get_string('invitebutton', 'tutor'),
        get_string('invitebuttonex', 'tutor'), 0));
    $settings->add(new admin_setting_configtext('tutor_channellastcam', get_string('simultaneouscameras', 'tutor'),
        get_string('simultaneouscamerasex', 'tutor'), '4', PARAM_INT, 1));
    $settings->add(new admin_setting_configcheckbox('tutor_livebutton', get_string('streamingbutton', 'tutor'),
        get_string('streamingbuttonex', 'tutor'), 0));
    $settings->add(new admin_setting_configcheckbox('tutor_blurbutton', get_string('blurbutton', 'tutor'),
        get_string('blurbuttonex', 'tutor'), 0));
    $settings->add(new admin_setting_configcheckbox('tutor_shareyoutube', get_string('youtubebutton', 'tutor'),
        get_string('youtubebuttonex', 'tutor'), 0));
    $settings->add(new admin_setting_configtext('tutor_watermarklink', get_string('watermarklink', 'tutor'),
        get_string('watermarklinkex', 'tutor'), 'https://tutor.org'));
    $settings->add(new admin_setting_configcheckbox('tutor_finishandreturn', get_string('finishandreturn', 'tutor'),
        get_string('finishandreturnex', 'tutor'), 0));

    $settings->add(new admin_setting_configpasswordunmask('tutor_password', get_string('password', 'tutor'),
        get_string('passwordex', 'tutor'), ''));
    $settings->add(new admin_setting_configcheckbox('tutor_privatesessions', get_string('privatesessions', 'tutor'),
        get_string('privatesessionsex', 'tutor'), 1));

    $settings->add(new admin_setting_heading('bookmodeditdefaults',
        get_string('tokennconfig', 'tutor'), get_string('tokenconfigurationex', 'tutor')));
    $settings->add(new admin_setting_configtext('tutor_app_id', get_string('appid', 'tutor'),
        get_string('appidex', 'tutor'), ''));
    $settings->add(new admin_setting_configpasswordunmask('tutor_secret', get_string('secret', 'tutor'),
        get_string('secretex', 'tutor'), ''));
}
