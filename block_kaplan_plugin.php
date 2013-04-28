<?php
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
        $this->content->text .= '<th>Id</th><th>Name</th><th>Users enrolled</th>';
        $this->content->text .= '</tr>';
        $this->content->text .= '</table>';
        $this->content->text .= '<div class="courseloading_image"><img src="'.$CFG->wwwroot.'/pix/i/loading_small.gif"/></div>';

        $this->content->text .= '<h3>Users</h3>';
        $this->content->text .= '<table id="kaplan_user_table">';
        $this->content->text .= '<tr>';
        $this->content->text .= '<th>Id</th><th>Fullname</th>';
        $this->content->text .= '</tr>';
        $this->content->text .= '</table>';
        $this->content->text .= '<div class="userloading_image"><img src="'.$CFG->wwwroot.'/pix/i/loading_small.gif"/></div>';
        // if (empty($currentcontext)) {
        //     return $this->content;
        // }
        // if ($this->page->course->id == SITEID) {
        //     $this->context->text .= "site context";
        // }

        if (! empty($this->config->text)) {
            $this->content->text .= $this->config->text;
        }

        $this->page->requires->js('/blocks/kaplan_plugin/kaplan_plugin.js');
        $this->page->requires->js_function_call('kaplan_loadCourseTable', array('kaplan_course_table', $coursesurl));
        $this->page->requires->js_function_call('kaplan_loadUserTable', array('kaplan_user_table', $usersurl));


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

    function has_config() {return flase;}

}
