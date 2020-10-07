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
 * unit test for capabilities
 * @package local_metadata
 * @author Céline Pervès <cperves@unistra.fr>
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @copyright 2019 Université de Strasbourg 2020
 */


defined('MOODLE_INTERNAL') || die();

/**
 * @group local_metadata
 */
class local_metadata_capabilities_testcase extends advanced_testcase {


    /**
     * @expectedException required_capability_exception
     * Sets up the test cases.
     */
    public function test_require_access_fields_category_category_manage() {
        $category= $this->getDataGenerator()->create_category();
        $this->require_access_capabilities_test($category, CONTEXT_COURSECAT, []);
    }


    /**
     * @expectedException required_capability_exception
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function test_require_access_fields_category_metadata_editcategory() {
        $category= $this->getDataGenerator()->create_category();
        $this->require_access_capabilities_test($category, CONTEXT_COURSECAT, ['moodle/category:manage']);
    }

    public function test_require_access_fields_category_having_capabilities() {
        $category= $this->getDataGenerator()->create_category();
        $this->require_access_capabilities_test($category,
            CONTEXT_COURSECAT, ['moodle/category:manage', 'local/metadata:editcategory']);
    }
    /**
     * @expectedException required_capability_exception
     * Sets up the test cases.
     */
    public function test_require_access_fields_course_course_create() {
        $course= $this->getDataGenerator()->create_course();
        $this->require_access_capabilities_test($course, CONTEXT_COURSE, [], $course);
    }


    /**
     * @expectedException required_capability_exception
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function test_require_access_fields_course_metadata_editcourse() {
        $course= $this->getDataGenerator()->create_course();
        $this->require_access_capabilities_test($course, CONTEXT_COURSE, ['moodle/course:create'], $course);
    }


    public function test_require_access_fields_course_having_capabilities() {
        $course= $this->getDataGenerator()->create_course();
        $this->require_access_capabilities_test($course, CONTEXT_COURSE,
            ['moodle/course:create','local/metadata:editcourse'], $course);

    }

    /**
     * @expectedException required_capability_exception
     * Sets up the test cases.
     */
    public function test_require_access_fields_cohort_metadata_cohort_manage() {
        $cohort= $this->getDataGenerator()->create_cohort();
        $this->require_access_capabilities_test($cohort, CONTEXT_COHORT);
    }


    /**
     * @expectedException required_capability_exception
     */
    public function test_require_access_fields_cohort_metadata_editcohort() {
        $cohort= $this->getDataGenerator()->create_cohort();
        $this->require_access_capabilities_test($cohort, CONTEXT_COHORT, ['moodle/cohort:manage']);
    }

    public function test_require_access_fields_cohort_having_capabilities() {
        $cohort= $this->getDataGenerator()->create_cohort();
        $this->require_access_capabilities_test($cohort,
            CONTEXT_COHORT, ['moodle/cohort:manage', 'local/metadata:editcohort']);
    }

    /**
     * @expectedException required_capability_exception
     * Sets up the test cases.
     */
    public function test_require_access_fields_group_metadata_course_managegroups() {
        $course = $this->getDataGenerator()->create_course();
        $grouprecord = new stdClass();
        $grouprecord->courseid = $course->id;
        $group= $this->getDataGenerator()->create_group($grouprecord);
        $this->require_access_capabilities_test($group, CONTEXT_GROUP, [], $course);
    }


    /**
     * @expectedException required_capability_exception
     */
    public function test_require_access_fields_group_metadata_editgroup() {
        $course = $this->getDataGenerator()->create_course();
        $grouprecord = new stdClass();
        $grouprecord->courseid = $course->id;
        $group= $this->getDataGenerator()->create_group($grouprecord);
        $this->require_access_capabilities_test($group, CONTEXT_GROUP, ['moodle/course:managegroups'], $course);
    }

    public function test_require_access_fields_group_having_capabilities() {
        $course = $this->getDataGenerator()->create_course();
        $grouprecord = new stdClass();
        $grouprecord->courseid = $course->id;
        $group= $this->getDataGenerator()->create_group($grouprecord);
        $this->require_access_capabilities_test($group,
            CONTEXT_GROUP, ['moodle/course:managegroups', 'local/metadata:editgroup'], $course);
    }

    private function require_access_capabilities_test($object, $contextlevel, $capabilities = [], $course = null){
        global $CFG;
        require_once($CFG->dirroot . '/local/metadata/lib.php');
        $this->resetAfterTest();
        $this->setAdminUser();
        $contextname = self::create_field($contextlevel);
        $creator = $this->create_user_and_role($capabilities, $course);
        $ctxhandler = \local_metadata\context\context_handler::factory($contextname, $object->id);
        $ctxhandler->get_instance(); // To fill instance properly.
        $ctxhandler->get_context();// To fill context properly.
        $this->setUser($creator);
        $ctxhandler->require_access();
    }

    /**create user and give hime a role with capabilities
     * @param  capabilities array
     * @return stdClass coursecreator
     * @throws coding_exception
     * @throws dml_exception
     */
    private function create_user_and_role($capabilities=[], $course = null){
        global $DB;
        $editorroleid = $this->getDataGenerator()->create_role();
        $editorrole = $DB->get_record('role', array('id' => $editorroleid));
        // add category manage capability
        foreach($capabilities as $capability){
            assign_capability($capability, CAP_ALLOW,
                    $editorrole->id, context_system::instance()->id, true);
        }
        $user = $this->getDataGenerator()->create_user();
        $systemcontext = context_system::instance();
        $this->getDataGenerator()->role_assign($editorrole->id, $user->id, $systemcontext->id);
        if( ! is_null($course)){
            $this->getDataGenerator()->enrol_user($user->id, $course->id, $editorroleid);
        }
        return $user;
    }

    /**
     * @param int $contextlevel
     * @param moodle_database $DB
     * @return string
     * @throws dml_exception
     */
    private static function create_field(int $contextlevel){
        global $DB;
        $contextname = local_metadata_get_contextname($contextlevel);
        set_config('metadataenabled', 1, 'metadatacontext_' . $contextname);
        $DB->insert_record('local_metadata_field', array(
                'contextlevel' => $contextlevel, 'shortname' => 'field1', 'name' => 'Description of field1',
                'categoryid' => 1, 'datatype' => 'textarea'));
        return $contextname;
    }
}