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
class local_metadata_menu_extensions_testcase extends advanced_testcase {
    private $editor;
    private $editorrole;


    public function test_add_settings_to_context_menu() {
        $this->resetAfterTest();
        global $DB, $CFG;
        require_once($CFG->dirroot . '/local/metadata/lib.php');
        require_once($CFG->libdir . '/adminlib.php');
        $this->setAdminUser();
        $contextname = self::create_field(CONTEXT_MODULE);
        $this->create_user_and_role();
        $course = $this->getDataGenerator()->create_course();
        $module= $this->getDataGenerator()->create_module('lesson', array('course' => $course->id));
        $modulectxhandler = \local_metadata\context\context_handler::factory($contextname, $module->cmid);
        $modulectxhandler->get_context();//to fill context property
        $this->setUser($this->editor);
        $tree = new admin_root(true);
        $tree->add('root', $one = new admin_category('modsettings', 'modsettings'));
        $tree->get_children()[0]->add('modsettings',new admin_category('managemodulescommon', 'managemodulescommon'));
        $modulectxhandler->add_settings_to_context_menu($tree);
        $newmenu = $tree->locate('metadatacontext_modules');
        $this->assertTrue(is_array($newmenu->req_capability));
        $this->assertCount(1,$newmenu->req_capability);
        $this->assertContains('moodle/site:config',$newmenu->req_capability);
    }

    public function test_module_extend_settings_navigation() {
        $this->resetAfterTest();
        global $PAGE, $CFG;
        require_once($CFG->dirroot . '/local/metadata/lib.php');
        // To test navigation.
        require_once($CFG->libdir.'/navigationlib.php');
        $this->setAdminUser();
        $contextname = self::create_field(CONTEXT_MODULE);
        $this->create_user_and_role();
        $course = $this->getDataGenerator()->create_course();
        $PAGE->set_course($course); // To emulate extend navigation.
        $PAGE->set_url('/course/view.php', array('id' => $course->id));
        $module = $this->getDataGenerator()->create_module('lesson', array('course' => $course->id));
        $modulectxhandler = \local_metadata\context\context_handler::factory($contextname, $module->cmid);
        $context = $modulectxhandler->get_context();// To fill context property.
        $this->setUser($this->editor);
        $rootnode = new navigation_node('rootnode');
        $modulenode= new navigation_node(array('shortname'=>'modulesettings', 'key'=> 'modulesettings',
                'description'=> 'modulesettings', 'type'=>navigation_node::TYPE_SETTING, 'text' => 'modulesettings'));
        $rootnode->add_node($modulenode);
        $this->assertFalse($modulenode->has_children());
        $modulectxhandler->extend_settings_navigation($rootnode, $context);
        $this->assertFalse($modulenode->has_children());
        assign_capability('moodle/course:manageactivities', CAP_ALLOW,
                $this->editorrole->id, context_system::instance()->id, true);
        $modulectxhandler->extend_settings_navigation($modulenode, $context);
        $this->assertFalse($modulenode->has_children());
        assign_capability('local/metadata:editmodule', CAP_ALLOW,
                $this->editorrole->id, context_system::instance()->id, true);
        $modulectxhandler->extend_settings_navigation($rootnode, $context);
        $this->assertTrue($modulenode->has_children());
        $this->assertCount(1,$modulenode->children);
    }

    public function test_category_extend_settings_navigation() {
        $this->resetAfterTest();
        global $PAGE, $CFG;
        require_once($CFG->dirroot . '/local/metadata/lib.php');
        // To test navigation.
        require_once($CFG->libdir.'/navigationlib.php');
        $this->setAdminUser();
        $contextname = self::create_field(CONTEXT_COURSECAT);
        $this->create_user_and_role();
        $category = $this->getDataGenerator()->create_category();
        $PAGE->set_category_by_id($category->id); // To emulate extend navigation.
        $PAGE->set_url('/course/editcategory.php', array('id' => $category->id));
        $categoryctxhandler = \local_metadata\context\context_handler::factory($contextname, $category->id);
        $context = $categoryctxhandler->get_context();// To fill context property.
        $this->setUser($this->editor);
        $rootnode = new navigation_node('rootnode');
        $categorynode= new navigation_node(array('shortname'=>'categorysettings', 'key'=> 'categorysettings',
            'description'=> 'categorysettings', 'type'=>navigation_node::TYPE_CONTAINER, 'text' => 'categorysettings'));
        $rootnode->add_node($categorynode);
        $this->assertFalse($categorynode->has_children());
        $categoryctxhandler->extend_settings_navigation($rootnode, $context);
        $this->assertFalse($categorynode->has_children());

        assign_capability('moodle/category:manage', CAP_ALLOW,
            $this->editorrole->id, context_system::instance()->id, true);
        $categoryctxhandler->extend_settings_navigation($categorynode, $context);
        $this->assertFalse($categorynode->has_children());
        assign_capability('local/metadata:editcategory', CAP_ALLOW,
            $this->editorrole->id, context_system::instance()->id, true);
        $categoryctxhandler->extend_settings_navigation($rootnode, $context);
        $this->assertTrue($categorynode->has_children());
        $this->assertCount(1,$categorynode->children);
    }

    public function test_cohort_extend_settings_navigation() {
        $this->resetAfterTest();
        global $PAGE, $CFG;
        require_once($CFG->dirroot . '/local/metadata/lib.php');
        // To test navigation.
        require_once($CFG->libdir.'/navigationlib.php');
        $this->setAdminUser();
        $contextname = self::create_field(CONTEXT_COHORT);
        $this->create_user_and_role();
        $cohort = $this->getDataGenerator()->create_cohort();
        $PAGE->set_context(context_system::instance()); // To emulate extend navigation.
        $PAGE->set_url('/cohort/edit.php', array('id' => $cohort->id));
        $cohortctxhandler = \local_metadata\context\context_handler::factory($contextname, $cohort->id);
        $cohortctxhandler->get_instance();// To fill instance properly.
        $context = $cohortctxhandler->get_context();// To fill context property.
        $this->setUser($this->editor);
        $rootnode = new navigation_node('rootnode');
        $categorynode= new navigation_node(array('shortname'=>'cohorts', 'key'=> 'cohorts',
            'description'=> 'cohorts', 'type'=>navigation_node::TYPE_SETTING, 'text' => 'cohorts'));
        $rootnode->add_node($categorynode);
        $this->assertFalse($categorynode->has_children());
        $cohortctxhandler->extend_settings_navigation($rootnode, $context);
        $this->assertFalse($categorynode->has_children());

        assign_capability('moodle/cohort:manage', CAP_ALLOW,
            $this->editorrole->id, context_system::instance()->id, true);
        $cohortctxhandler->extend_settings_navigation($categorynode, $context);
        $this->assertFalse($categorynode->has_children());
        assign_capability('local/metadata:editcohort', CAP_ALLOW,
            $this->editorrole->id, context_system::instance()->id, true);
        $cohortctxhandler->extend_settings_navigation($rootnode, $context);
        $this->assertTrue($categorynode->has_children());
        $this->assertCount(1,$categorynode->children);
    }

    public function test_group_extend_settings_navigation() {
        $this->resetAfterTest();
        global $PAGE, $CFG;
        require_once($CFG->dirroot . '/local/metadata/lib.php');
        // To test navigation.
        require_once($CFG->libdir.'/navigationlib.php');
        $this->setAdminUser();
        $contextname = self::create_field(CONTEXT_GROUP);
        $this->create_user_and_role();
        $course = $this->getDataGenerator()->create_course();
        $grouprecord = new stdClass();
        $grouprecord->courseid = $course->id;
        $group = $this->getDataGenerator()->create_group($grouprecord);
        $PAGE->set_context(context_course::instance($course->id)); // To emulate extend navigation.
        $PAGE->set_url('/group/group.php', array('id' => $group->id));
        $groupctxhandler = \local_metadata\context\context_handler::factory($contextname, $group->id);
        $groupctxhandler->get_instance();// To fill instance properly.
        $context = $groupctxhandler->get_context();// To fill context property.
        $this->setUser($this->editor);
        $rootnode = new navigation_node('rootnode');
        $categorynode= new navigation_node(array('shortname'=>'groups', 'key'=> 'groups',
            'description'=> 'groups', 'type'=>navigation_node::TYPE_SETTING, 'text' => 'groups'));
        $rootnode->add_node($categorynode);
        $this->assertFalse($categorynode->has_children());
        $groupctxhandler->extend_settings_navigation($rootnode, $context);
        $this->assertFalse($categorynode->has_children());

        assign_capability('moodle/course:managegroups', CAP_ALLOW,
            $this->editorrole->id, context_system::instance()->id, true);
        $groupctxhandler->extend_settings_navigation($categorynode, $context);
        $this->assertFalse($categorynode->has_children());
        assign_capability('local/metadata:editgroup', CAP_ALLOW,
            $this->editorrole->id, context_system::instance()->id, true);
        $groupctxhandler->extend_settings_navigation($rootnode, $context);
        $this->assertTrue($categorynode->has_children());
        $this->assertCount(1,$categorynode->children);
    }

    /**create user and give hime a role with capabilities
     * @param  capabilities array
     * @return stdClass coursecreator
     * @throws coding_exception
     * @throws dml_exception
     */
    private function create_user_and_role($capabilities=[]){
        global $DB;
        $editorroleid = $this->getDataGenerator()->create_role();
        $this->editorrole = $DB->get_record('role', array('id' => $editorroleid));
        // add category manage capability
        foreach($capabilities as $capability){
            assign_capability($capability, CAP_ALLOW,
                    $this->editorrole->id, context_system::instance()->id, true);
        }
        $this->editor = $this->getDataGenerator()->create_user();
        $systemcontext = context_system::instance();
        $this->getDataGenerator()->role_assign($this->editorrole->id, $this->editor->id, $systemcontext->id);
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