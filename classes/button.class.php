<?php

/**
 * Description of button
 *
 * @author carlos
 */
class button extends moodleform {

    function definition() {

        $mform = $this->_form;
        $condition = $this->_customdata['condition'];
        if ($condition) {
            $mform->addElement('checkbox', 'accept', get_string("aceite", "termsandcondition"));
            $mform->setDefault('accept', true);
            $this->_form->hardFreeze();
        } else {
            $mform->addElement('checkbox', 'accept', get_string("aceite", "termsandcondition"));
        }
        $this->add_action_buttons($cancel = FALSE, get_string("salvar", 'termsandcondition'));
    }

    function validation($data, $files) {
        return array();
    }

}
