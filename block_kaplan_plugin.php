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

        if (!isloggedin() or isguestuser()) {
            return '';      // Never useful unless you are logged in as real users
        }

        if(!$CFG->enablewebservices) {
            $this->content->text = '<p class="kaplan_notice">'.get_string('wsnotenabled', 'block_kaplan_plugin').'</p>';
            return $this->content;
        }

        if(strpos($CFG->webserviceprotocols, 'rest') === false) {
            $this->content->text = '<p class="kaplan_notice">'.get_string('restnotenabled', 'block_kaplan_plugin').'</p>';
            return $this->content;
        }

        $context = context_system::instance();

        //check if the service exists and is enabled
        $service = $DB->get_record('external_services', array('component' => 'block_kaplan_plugin', 'enabled' => 1));
        if (empty($service)) {
            // will print message if no service found
            $this->content->text = '<p class="kaplan_notice">'.get_string('wsnotfound', 'block_kaplan_plugin').'</p>';
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
            //Check to see if the service has any capabilities and check user has them
            if (empty($service->requiredcapability) || has_capability($service->requiredcapability, $context, $USER->id)) {
                $newtoken->externalserviceid = $service->id;
            } else {
                $this->content->text = '<p class="kaplan_notice">'.get_string('noaccess', 'block_kaplan_plugin').'</p>';
                return $this->content;
            }

            //Attempt to create a unique token
            $numtries = 0;
            do {
                $numtries ++;
                $generatedtoken = md5(uniqid(rand(),1));
                if ($numtries > 5){
                    $this->content->text = '<p class="kaplan_notice">'.get_string('tokenfail', 'block_kaplan_plugin').'</p>';
                    return $this->content;
                }
            } while ($DB->record_exists('external_tokens', array('token'=>$generatedtoken)));

            //Set the details of the new token
            $newtoken->token = $generatedtoken;
            $newtoken->tokentype = EXTERNAL_TOKEN_PERMANENT;
            $newtoken->userid = $USER->id;
            $newtoken->contextid = $context->id;
            $newtoken->creatorid = $USER->id;
            $newtoken->timecreated = time();
            $newtoken->sid = session_id();
            $newtoken->validuntil = 0;

            //Set and insert token in db
            $token = $newtoken;
            $DB->insert_record('external_tokens', $newtoken);
        } elseif (empty($token)) { //No token and unable to create one
            $this->content->text = '<p class="kaplan_notice">'.get_string('tokennotice', 'block_kaplan_plugin').'</p>';
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
        $this->content->text .= '<div id="kaplan_courses">';
        $this->content->text .= '<h3>Courses</h3>';
        $this->content->text .= '<table id="kaplan_course_table" class="kaplan_table">';
        $this->content->text .= '<thead><tr>';
        $this->content->text .= '<th class="kap_table_id">Id</th><th class="kap_table_name">Name</th><th class="kap_table_ue">Users enrolled</th>';
        $this->content->text .= '</tr></thead><tbody></tbody>';
        $this->content->text .= '</table>';
        $this->content->text .= '<div id="kaplan_courses_next" class="kaplan_courses_btn">next</div>';
        $this->content->text .= '<div id="kaplan_courses_prev" class="kaplan_courses_btn">prev</div>';
        $this->content->text .= '<div class="courseloading_image"><img src="'.$CFG->wwwroot.'/pix/i/loading_small.gif"/></div>';
        $this->content->text .= '</div>';

        $this->content->text .= '<div id="kaplan_users">';
        $this->content->text .= '<h3>Users</h3>';
        $this->content->text .= '<table id="kaplan_user_table" class="kaplan_table">';
        $this->content->text .= '<thead><tr>';
        $this->content->text .= '<th class="kap_table_id">Id</th><th class="kap_table_fullname">Fullname</th>';
        $this->content->text .= '</tr></thead><tbody></tbody>';
        $this->content->text .= '</table>';
        $this->content->text .= '<div id="kaplan_users_next" class="kaplan_users_btn">next</div>';
        $this->content->text .= '<div id="kaplan_users_prev" class="kaplan_users_btn">prev</div>';
        $this->content->text .= '<div class="userloading_image"><img src="'.$CFG->wwwroot.'/pix/i/loading_small.gif"/></div>';
        $this->content->text .= '</div>';

        //Setup and call the js
        $this->page->requires->js('/blocks/kaplan_plugin/kaplan_plugin.js');
        $this->page->requires->js_function_call('kaplan_loadCourseTable', array('kaplan_course_table', $coursesurl, 0));
        $this->page->requires->js_function_call('kaplan_loadUserTable', array('kaplan_user_table', $usersurl, 0));
        $this->page->requires->js_function_call('kaplan_register_btns', array('kaplan_course_table', 'kaplan_user_table'));

        return $this->content;
    }

    // my moodle can only have SITEID and it's redundant here, so take it away
    public function applicable_formats() {
        return array('all' => true);
    }

    public function instance_allow_multiple() {
          return false;
    }

    function has_config() {return false;}
}
