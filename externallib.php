<?php

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
 * External Web Service Template
 *
 * @package    localwstemplate
 * @copyright  2011 Moodle Pty Ltd (http://moodle.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->libdir . "/externallib.php");

class block_kaplan_plugin_external extends external_api {

    /**
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_courses_custom_parameters() {
        return new external_function_parameters(
            array()
        );
    }

    /**
     * Returns welcome message
     * @return string welcome message
     */
    public static function get_courses_custom() {
        global $CFG, $DB, $USER;
        require_once($CFG->dirroot . "/course/lib.php");

        //retrieve courses
        $courses = $DB->get_records('course');
        array_shift($courses);

        //create return value
        $coursesinfo = array();
        foreach ($courses as $course) {
            
            // now security checks
            $context = context_course::instance($course->id, IGNORE_MISSING);
            try {
                self::validate_context($context);
            } catch (Exception $e) {
                $exceptionparam = new stdClass();
                $exceptionparam->message = $e->getMessage();
                $exceptionparam->courseid = $course->id;
                throw new moodle_exception('errorcoursecontextnotvalid', 'webservice', '', $exceptionparam);
            }
            require_capability('moodle/course:view', $context);

            $courseinfo = array();
            $courseinfo['id'] = $course->id;
            $courseinfo['fullname'] = $course->fullname;

            //some field should be returned only if the user has update permission
            $courseadmin = has_capability('moodle/course:update', $context);

            if ($courseadmin or $course->visible
                    or has_capability('moodle/course:viewhiddencourses', $context)) {
                $coursesinfo[] = $courseinfo;
            }
        }

        return $coursesinfo;
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function get_courses_custom_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'course ID'),
                    'fullname' => new external_value(PARAM_TEXT, 'course Name')
                ), 'course'
            )
        );
    }

    public static function get_users_custom_parameters() {
        return new external_function_parameters(
            array()
        );
    }

    /**
     * Returns welcome message
     * @return string welcome message
     */
    public static function get_users_custom() {
        global $CFG, $DB, $USER;

        //retrieve courses
        $users = $DB->get_records('user');
        array_shift($users);

        //create return value
        $coursesinfo = array();
        foreach ($users as $user) {
            if (!empty($user->deleted)) {
                continue;
            }
            context_instance_preload($user);
            $usercontext = context_user::instance($user->id, IGNORE_MISSING);
            
            try {
                self::validate_context($usercontext);
            } catch (Exception $e) {
                $exceptionparam = new stdClass();
                $exceptionparam->message = $e->getMessage();
                $exceptionparam->userid = $user->id;
                throw new moodle_exception('errorcoursecontextnotvalid', 'webservice', '', $exceptionparam);
            }
            require_capability('moodle/user:viewdetails', $usercontext);

            $userinfo = array();
            $userinfo['id'] = $user->id;
            $userinfo['fullname'] = $user->firstname . ' ' . $user->lastname;

            $result[] = $userinfo;
        }


        return $result;
    }

    /**
     * Returns description of method result value
     * @return external_description
     */
    public static function get_users_custom_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'user id'),
                    'fullname' => new external_value(PARAM_TEXT, 'user fullname')
                ), 'user'
            )
        );
    }

}
