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
 * Newblock block caps.
 *
 * @package    block_newblock
 * @copyright  Daniel Neis <danielneis@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class block_kaplan_plugin extends block_base {

    function init() {
        $this->title = get_string('pluginname', 'block_kaplan_plugin');
    }

    function get_content() {
        global $CFG, $OUTPUT;

        if ($this->content !== null) {
            return $this->content;
        }

        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }

        $wsurl = $CFG->wwwroot . '/webservice/rest/server.php';


        $coursesurl = $wsurl;
        $coursesurl .= '?wsfunction=block_kaplan_plugin_get_courses_custom';
        $coursesurl .= '&moodlewsrestformat=json';
        $coursesurl .= '&wstoken=443f6ba99e6e86ffc2fca447eebd1e49';

        $usersurl = $wsurl;
        $usersurl .= '?wsfunction=block_kaplan_plugin_get_users_custom';
        $usersurl .= '&moodlewsrestformat=json';
        $usersurl .= '&wstoken=443f6ba99e6e86ffc2fca447eebd1e49';

        $this->page->requires->js('/blocks/kaplan_plugin/kaplan_plugin.js');
        $this->page->requires->js_function_call('kaplan_loadCourseTable', array('kaplan_course_table', $coursesurl));
        $this->page->requires->js_function_call('kaplan_loadUserTable', array('kaplan_user_table', $usersurl));

        $this->content = new stdClass();
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';

        // user/index.php expect course context, so get one if page has module context.
        // $currentcontext = $this->page->context->get_course_context(false);

        if (! empty($this->config->text)) {
            $this->content->text = $this->config->text;
        }

        $this->content->text .= '<h3>Courses</h3>';
        $this->content->text .= '<table id="kaplan_course_table">';
        $this->content->text .= '<tr>';
        $this->content->text .= '<th>Id</th><th>Name</th>';
        $this->content->text .= '</tr>';
        $this->content->text .= '</table>';

        $this->content->text .= '<h3>Users</h3>';
        $this->content->text .= '<table id="kaplan_user_table">';
        $this->content->text .= '<tr>';
        $this->content->text .= '<th>Id</th><th>Fullname</th>';
        $this->content->text .= '</tr>';
        $this->content->text .= '</table>';
        // if (empty($currentcontext)) {
        //     return $this->content;
        // }
        // if ($this->page->course->id == SITEID) {
        //     $this->context->text .= "site context";
        // }

        if (! empty($this->config->text)) {
            $this->content->text .= $this->config->text;
        }

        return $this->content;
    }

    // my moodle can only have SITEID and it's redundant here, so take it away
    public function applicable_formats() {
        return array('all' => false,
                     'site' => true,
                     'site-index' => true,
                     'course-view' => true, 
                     'course-view-social' => false,
                     'mod' => true, 
                     'mod-quiz' => false);
    }

    public function instance_allow_multiple() {
          return false;
    }

    function has_config() {return true;}

    public function cron() {
            mtrace( "Hey, my cron script is running" );
             
                 // do something
                  
                      return true;
    }
}
