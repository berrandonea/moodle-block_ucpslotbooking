<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once($CFG->libdir . '/formslib.php');

class ucpslotbooking_closingbookingsettings_form extends moodleform {

    public function definition() {
        
        $mform = $this->_form;

        $mform->addElement('text', 'numberofdays', get_string('numberofdays','block_ucpslotbooking')); // Add elements to your form
        $this->add_action_buttons();

        // Remplir avec les infos de la page

        $mform->addElement('hidden', 'courseid');
        $mform->addElement('hidden', 'blockid');
        $mform->addElement('hidden', 'listid');
    }
}