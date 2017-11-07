<?php

/**
 * Description of print_html
 *
 * @author carlos
 */
require($CFG->dirroot . '/course/moodleform_mod.php');

class print_html extends moodleform {

    function definition() {

        $mform = $this->_form;
        $resumo = $this->_customdata['editor'];
        $modcontext = $this->_customdata['modcontext'];
        
        $mform->addElement('editor', 'editor', get_string('editor', 'termsandcondition'), null, array('context' => $modcontext))->setValue(array('text' => $resumo));
        $mform->addRule('editor', get_string('obrigatorio', 'termsandcondition'), 'required', null, 'client');
        $mform->setType('editor', PARAM_RAW);
        
        $this->add_action_buttons($cancel = FALSE, get_string("salvar",'termsandcondition'));
    }

    function validation($data, $files) {
        return array();
    }

}
