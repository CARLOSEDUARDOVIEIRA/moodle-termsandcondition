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
 * Prints a particular instance of termsandcondition
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_termsandcondition
 * @copyright  2016 Your Name <your@email.address>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
// Replace termsandcondition with the name of your module and remove this line.

require (dirname(dirname(dirname(__FILE__))) . '/config.php');
require (dirname(__FILE__) . '/lib.php');
require ("$CFG->libdir/pdflib.php");
require './classes/print_html.class.php';
require './classes/model_print_html.class.php';
require './classes/button.class.php';
require_once($CFG->dirroot . '/lib/completionlib.php');

$id = optional_param('id', 0, PARAM_INT);
$n = optional_param('n', 0, PARAM_INT);
$view = optional_param('view', 0, PARAM_INT);

if ($id) {
    $cm = get_coursemodule_from_id('termsandcondition', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $termsandcondition = $DB->get_record('termsandcondition', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($n) {
    $termsandcondition = $DB->get_record('termsandcondition', array('id' => $n), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $termsandcondition->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('termsandcondition', $termsandcondition->id, $course->id, false, MUST_EXIST);
} else {
    error('You must specify a course_module ID or an instance ID');
}

require_login($course, true, $cm);
$context_course = context_course::instance($course->id);

$event = \mod_termsandcondition\event\course_module_viewed::create(array(
            'objectid' => $PAGE->cm->instance,
            'context' => $PAGE->context,
        ));
$event->add_record_snapshot('course', $PAGE->course);
$event->add_record_snapshot($PAGE->cm->modname, $termsandcondition);
$event->trigger();

$PAGE->set_url('/mod/termsandcondition/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($termsandcondition->name));
$PAGE->set_heading(format_string($course->fullname));
$modcontext = context_module::instance($cm->id);

define('VIEW_URL_LINK', "./view.php?id=" . $id);
define('VIEW_INIT_URL_LINK', $CFG->wwwroot . "/course/view.php?id=" . $course->id);

echo $OUTPUT->header();
$model = new model_print_html();

$dataeditor = $model->get($termsandcondition->id);

if (has_capability('mod/termsandcondition:settings', $context_course)) {
    if ($dataeditor) {
        $editor = new print_html("./view.php?id={$id}&view=1", array('modcontext' => $modcontext, 'editor' => $dataeditor[$termsandcondition->id]->editor));
    } else {
        $editor = new print_html("./view.php?id={$id}&view=1", array('modcontext' => $modcontext, 'editor' => ''));
    }
    if ($editor->get_data()) {
        $print = new stdClass();
        $print->editor = $editor->get_data()->editor[text];
        $print->instance = $termsandcondition->id;
        if (!$model->get($termsandcondition->id)) {
            $model->save($print);
            redirect(VIEW_URL_LINK);
        } else {
            $model->update($print);
            redirect(VIEW_URL_LINK);
        }
    }
    ?>
    <div class="row">                
        <div class="row-fluid">
            <div class="form-group">
                <form action="" method="post">
                    <div class="col-sm-4"> 
                        <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#editmodal" data-whatever="@mdo"><?php echo get_string('abrireditor', 'termsandcondition'); ?></button>
                    </div>
                </form> 
            </div>
        </div>
    </div>

    <div class="row">     
        <div class="modal fade bd-example-modal-lg" id="editmodal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="exampleModalLabel">Editar html</h4>
                    </div>
                    <div class="modal-body">
                        <?php $editor->display(); ?>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
}

if (!$view) {
    if ($dataeditor) {
        $editor = html_writer::start_tag('div', array('style' => 'margin-left:25%; margin-top:5%; margin-right:25%; text-align:justify;'));
        $editor .= html_writer::start_tag('p') . $dataeditor[$termsandcondition->id]->editor . html_writer::end_tag('p');
        $editor .= html_writer::end_tag('div');
        echo $editor;
    }
}

if (!has_capability('mod/termsandcondition:settings', $context_course)) {

    $valueaccept = $model->get_accept($cm->id, $USER->id);

    if ($valueaccept && $valueaccept[$cm->id]->accept) {
        $accept = new button("./view.php?id={$id}", array('condition' => $valueaccept));
    } else {
        $accept = new button("./view.php?id={$id}");
    }

    $accept->display();
    if ($accept->get_data()->accept != null) {
        $object = new stdClass();
        $object->instance = $cm->id;
        $object->userid = $USER->id;
        $object->accept = $accept->get_data()->accept;
        $date = new DateTime("now", core_date::get_user_timezone_object());
        $object->dateaccept = $date->getTimestamp();
        $id = $model->save_accept($object);
        if ($id) {
//            $completion = new completion_info($course);
//            if ($completion->is_enabled($cm) && $accept->get_data()->accept) {
//                $completion->update_state($cm, COMPLETION_COMPLETE);
//            }
            $completion = new completion_info($course);
            $completion->set_module_viewed($cm);
        }
        redirect(VIEW_INIT_URL_LINK);
    }
    if($accept->get_data()){
        echo $OUTPUT->notification(get_string('errortoadd', 'termsandcondition'));
    }
}

$link_voltar = html_writer::start_tag('a', array('href' => VIEW_INIT_URL_LINK, 'style' => 'margin-bottom:3%; margin-left:25%;'));
$link_voltar .= get_string('voltar', 'termsandcondition');
$link_voltar .= html_writer::end_tag('a');
echo $link_voltar;

echo $OUTPUT->footer();
