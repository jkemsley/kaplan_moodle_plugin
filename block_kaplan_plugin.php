<?php
defined('MOODLE_INTERNAL') || die();

class block_kaplan_plugin extends block_base {

    function init() {
        $this->title = get_string('pluginname', 'block_kaplan_plugin');
    }

    function get_content() {
        global $CFG, $OUTPUT, $USER, $DB;

        if ($this->content !== null) {
            return $this->content;
        }

        if (empty($this->instance)) {
            $this->content = '';
            return $this->content;
        }

        $this->content = new stdClass();
        $this->content->items = array();
        $this->content->icons = array();
        $this->content->footer = '';

        $context = context_system::instance();

        //check if the service exists and is enabled
        $service = $DB->get_record('external_services', array('component' => 'block_kaplan_plugin', 'enabled' => 1));
        if (empty($service)) {
            // will print message if no service found
            $this->content->text = '<p>Notice: Kaplan plugin webservice not found</p>';
            return $this->content;
        }

        //Get user token if it exists for this session already
        $token = $DB->get_record('external_tokens', array('userid'=> $USER->id, 'externalserviceid' => $service->id, 'sid' => session_id()));

        //No token? can we create one?
        if(empty($token) && has_capability('moodle/webservice:createtoken', $context)) {
            
            //Delete old tokens for old sessions
            $oldtokensql = "select * from {external_tokens}
                    where userid = $USER->id 
                    and externalserviceid = $service->id 
                    and sid != '" . session_id() . "'";
            $tokens = $DB->get_records_sql($oldtokensql);

            foreach ($tokens as $t) {
                $DB->delete_records('external_tokens', array('sid'=>$t->sid, 'userid' => $USER->id));
            }

            $newtoken = new stdClass;
            if (empty($service->requiredcapability) || has_capability($service->requiredcapability, $context, $USER->id)) {
                $newtoken->externalserviceid = $service->id;
            } else {
                $this->content->text = '<p class="kaplan_notice">Notice: You do not have access to use this service</p>';
                return $this->content;
            }

            $numtries = 0;
            do {
                $numtries ++;
                $generatedtoken = md5(uniqid(rand(),1));
                if ($numtries > 5){
                    $this->content->text = '<p class="kaplan_notice">Notice: Token generation failed</p>';
                    return $this->content;
                }
            } while ($DB->record_exists('external_tokens', array('token'=>$generatedtoken)));

            $newtoken->token = $generatedtoken;
            $newtoken->tokentype = EXTERNAL_TOKEN_PERMANENT;
            $newtoken->userid = $USER->id;
            $newtoken->contextid = $context->id;
            $newtoken->creatorid = $USER->id;
            $newtoken->timecreated = time();
            $newtoken->sid = session_id();
            $newtoken->validuntil = 0;
            $token = $newtoken;
            $DB->insert_record('external_tokens', $newtoken);
        } elseif (empty($token)) { //No token and unable to create one
            $this->content->text = '<p class="kaplan_error">You must be logged in to view this information</p>';
            return $this->content;
        }
        
        //Base api url
        $wsurl = $CFG->wwwroot . '/webservice/rest/server.php';

        //Courses api url
        $coursesurl = $wsurl;
        $coursesurl .= '?wsfunction=block_kaplan_plugin_get_courses_custom';
        $coursesurl .= '&moodlewsrestformat=json';
        $coursesurl .= '&wstoken=' . $token->token;

        //Users api url
        $usersurl = $wsurl;
        $usersurl .= '?wsfunction=block_kaplan_plugin_get_users_custom';
        $usersurl .= '&moodlewsrestformat=json';
        $usersurl .= '&wstoken=' . $token->token;

        if (! empty($this->config->text)) {
            $this->content->text = $this->config->text;
        }


        // Add the tables and the loading gifs
        $this->content->text .= '<h3>Courses</h3>';
        $this->content->text .= '<table id="kaplan_course_table" class="kaplan_table">';
        $this->content->text .= '<thead><tr>';
        $this->content->text .= '<th class="kap_table_id">Id</th><th class="kap_table_name">Name</th><th class="kap_table_ue">Users enrolled</th>';
        $this->content->text .= '</tr></thead><tbody></tbody>';
        $this->content->text .= '</table>';
        $this->content->text .= '<div class="courseloading_image"><img src="'.$CFG->wwwroot.'/pix/i/loading_small.gif"/></div>';

        $this->content->text .= '<h3>Users</h3>';
        $this->content->text .= '<table id="kaplan_user_table" class="kaplan_table">';
        $this->content->text .= '<thead><tr>';
        $this->content->text .= '<th>Id</th><th>Fullname</th>';
        $this->content->text .= '</tr></thead><tbody></tbody>';
        $this->content->text .= '</table>';
        $this->content->text .= '<div class="userloading_image"><img src="'.$CFG->wwwroot.'/pix/i/loading_small.gif"/></div>';

        //Setup and call the js
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

    function has_config() {return false;}


}
