<?php

/**
 * Description of model_print_html
 *
 * @author carlos
 */
class model_print_html {

    public function save($object) {
        global $DB;

        return $DB->insert_record("termsandcondition_editor", $object, $returnid = true);
    }

    public function get($instance) {
        global $DB;
        return $DB->get_records_sql("SELECT instance, editor FROM mdl_termsandcondition_editor WHERE instance = ?", array($instance));
    }

    public function update($object) {
        global $DB;
        return $DB->execute("UPDATE mdl_termsandcondition_editor SET editor = ? WHERE instance = ?", array($object->editor, $object->instance));
    }

    public function save_accept($object) {
        global $DB;
        return $DB->insert_record("termsandcondition_accept", $object, $returnid = true);
    }

    public function get_accept($instance, $userid) {
        global $DB;
        return $DB->get_records_sql("SELECT instance, accept FROM mdl_termsandcondition_accept WHERE instance = ? AND userid = ?", array($instance, $userid));
    }

}
