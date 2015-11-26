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
 * Turnitintwo adhoc tasks.
 *
 * @package    mod_turnitintooltwo
 * @copyright  2015 Skylar Kelty <S.Kelty@kent.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_turnitintooltwo\task;

/**
 * Submit queued assignments.
 */
class submit_assignment extends \core\task\adhoc_task
{
    public function get_component() {
        return 'mod_turnitintooltwo';
    }

    public function execute() {
        global $CFG, $DB;

        require_once($CFG->dirroot . "/mod/turnitintooltwo/lib.php");
        require_once($CFG->dirroot . "/mod/turnitintooltwo/turnitintooltwo_view.class.php");

        $data = (array)$this->get_custom_data();

        $user = $DB->get_record('user', array('id' => $data['userid']));
        \core\session\manager::set_user($user);

        $turnitintooltwo = $DB->get_record('turnitintooltwo', array('id' => $data['tiiid']));
        list($course, $cm) = get_course_and_cm_from_instance($turnitintooltwo, 'turnitintooltwo');

        $turnitintooltwoassignment = new \turnitintooltwo_assignment($turnitintooltwo->id, $turnitintooltwo);
        $turnitintooltwosubmission = new \turnitintooltwo_submission($data['submissionid'], "moodle", $turnitintooltwoassignment);
        $parts = $turnitintooltwoassignment->get_parts();

        $tiisubmission = $turnitintooltwosubmission->do_tii_submission($cm, $turnitintooltwoassignment);

        // Update submission.
        $DB->update_record('turnitintooltwo_submissions', array(
            'id' => $data['submissionid'],
            'submission_modified' => $data['subtime']
        ));

        $this->send_digital_receipt($tiisubmission);

        if ($tiisubmission['success'] !== true) {
            return false;
        }

        $lockedassignment = new \stdClass();
        $lockedassignment->id = $turnitintooltwoassignment->turnitintooltwo->id;
        $lockedassignment->submitted = 1;
        $DB->update_record('turnitintooltwo', $lockedassignment);

        $lockedpart = new \stdClass();
        $lockedpart->id = $data['submissionpart'];
        $lockedpart->submitted = 1;

        // Disable anonymous marking if post date has passed.
        if ($parts[$data['submissionpart']]->dtpost <= time()) {
            $lockedpart->unanon = 1;
        }

        $DB->update_record('turnitintooltwo_parts', $lockedpart);

        \core\session\manager::set_user(get_admin());

        return true;
    }

    /**
     * Setter for $customdata.
     * @param mixed $customdata (anything that can be handled by json_encode)
     * @throws \moodle_exception
     */
    public function set_custom_data($customdata) {
        if (empty($customdata['tiiid'])) {
            throw new \moodle_exception("tiiid cannot be empty!");
        }

        parent::set_custom_data($customdata);
    }

    /**
     * TODO - send message?
     */
    private function send_message($message, $error = false) {
        //mtrace("MESSAGE: " . $message);
    }

    /**
     * TODO - send message?
     */
    private function send_digital_receipt($digitalreciept) {
        //mtrace("digitalreciept: ");
        //print_r($digitalreciept);
    }
}
