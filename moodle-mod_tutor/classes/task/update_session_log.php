<?php

//namespace mod_comdi\task;
namespace mod_tutor\task;

require_once($CFG->dirroot.'/mod/tutor/locallib.php');

class update_session_log extends \core\task\scheduled_task
{
    /**
     * Получить имя задачи
     *
     * @return string
     */
    public function get_name()
    {
        return get_string('task_update_session_log', 'mod_tutor');
    }

    public function execute()
    {
        global $DB;

        $f_json = 'https://tutor.venomroms.com/api/session/c104acf8-55f9-4eec-a2a5-15b427ba90a8';
        $json = file_get_contents($f_json);
        $obj = json_decode($json, true);
        $data = [];
        $timeOffset = 0;
        $mistake = 0;
        $sessionId = 'c104acf8-55f9-4eec-a2a5-15b427ba90a8';
        foreach ($obj as $str) {
            $newrecord = new \stdClass();
            $timeOffset += (int)$str['timeOffset'];
            $mistake += (int)$str['mistake']; 
            if ($str['mistake'] != 1) {
                $timeOffset = number_format((float)($timeOffset / 1000), 2, '.', '');
                $newrecord->session_id = $sessionId;
                $newrecord->frame_number = $str['frameNumber'];
                $newrecord->time_offset = $timeOffset;
                $newrecord->mistake = $mistake;
                $data->record[] = $newrecord;  
                $timeOffset = 0;
                $mistake = 0;
            }
        }
        $DB->insert_records('sessionlog', $data);
    }
}