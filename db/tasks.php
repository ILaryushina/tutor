<?php

defined('MOODLE_INTERNAL') || die();

$tasks = [
    [
        'classname' => 'mod_tutor\task\update_session_log',
        'blocking' => 0,
        'minute' => '*/5',
        'hour' => '*',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*'
    ]
];