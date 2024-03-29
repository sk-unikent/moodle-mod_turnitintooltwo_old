<?php
namespace mod_turnitintooltwo\event;

/*
 * Log event when
 */

defined('MOODLE_INTERNAL') || die();

class list_submissions extends \core\event\base {
    protected function init() {
        $this->data['crud'] = 'r'; // c(reate), r(ead), u(pdate), d(elete)
        $this->data['edulevel'] = self::LEVEL_PARTICIPATING;
        $this->data['objecttable'] = 'turnitintooltwo';
    }

    public static function get_name() {
        return get_string('listsubmissions', 'mod_turnitintooltwo');
    }

    public function get_description() {
        return s($this->other['desc']);
    }

    public function get_url() {
        return new \moodle_url('/mod/turnitintooltwo/view.php', array( 'id' => $this->objectid));
    }

    /**
     * Custom validation.
     *
     * @throws \coding_exception
     * @return void
     */
    protected function validate_data() {
        parent::validate_data();
        if (!isset($this->other['desc'])) {
            throw new \coding_exception('The \'desc\' value must be set in other.');
        }

        if ($this->contextlevel != CONTEXT_MODULE) {
            throw new \coding_exception('Context level must be CONTEXT_MODULE.');
        }
    }
}