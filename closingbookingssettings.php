<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

require_once('../../config.php');
require_once('locallib.php');
require_once('closingbookingssettings_form.php');

global $CFG, $DB, $OUTPUT, $PAGE, $USER;

// Check params.
$courseid = required_param('courseid', PARAM_INT);
$blockid = required_param('blockid', PARAM_INT);
$listid = required_param('listid', PARAM_INT);

$courseurl = new moodle_url('/course/view.php', array('id' => $courseid));
$coursecontext = context_course::instance($courseid);

// Check access.
if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourse', 'block_simplehtml', $courseid);
}
require_login($course);

// Get slots list.
$list = $DB->get_record('block_ucpslotbooking_list', array('id' => $listid));
if ($list->blockid != $blockid) {
    print_error('invalidcourse', 'block_simplehtml', $courseid);
}
$nbshown = $list->nbshown;

// Header code.
$PAGE->set_url('/blocks/ucpslotbooking/closingbookingssettings.php',
        array('listid' => $listid, 'courseid' => $courseid, 'blockid' => $blockid));
$PAGE->set_title($list->name);
$PAGE->set_pagelayout('standard');
$PAGE->set_heading($list->name);

// Navigation node.
$settingsnode = $PAGE->settingsnav->add($list->name);
$editurl = new moodle_url('/blocks/ucpslotbooking/closingbookingssettings.php',
        array('listid' => $listid, 'courseid' => $courseid, 'blockid' => $blockid));
$editnode = $settingsnode->add($list->name, $editurl);
$editnode->make_active();

$site = get_site();

// Page display.
echo $OUTPUT->header();

$mform = new ucpslotbooking_closingbookingsettings_form();

$data['courseid'] = $courseid;
$data['blockid'] = $blockid;
$data['listid'] = $listid;
$data['numberofdays'] = $DB->get_record('block_ucpslotbooking_list',
        array('id' => $listid))->daysbeforelastbooking;

$mform->set_data($data);

if ($data = $mform->get_data()) {

    $updatedlist = $DB->get_record('block_ucpslotbooking_list', array('id' => $listid));
    $updatedlist->daysbeforelastbooking = $data->numberofdays;

    $DB->update_record('block_ucpslotbooking_list', $updatedlist);

    $slotsurl = new moodle_url('slots.php',
            array('list' => $listid, 'courseid' => $courseid, 'blockid' => $blockid));

    redirect($slotsurl);
} else if ($mform->is_cancelled()) {

    $slotsurl = new moodle_url('slots.php',
            array('list' => $listid, 'courseid' => $courseid, 'blockid' => $blockid));

    redirect($slotsurl);
}

$mform->display();

echo $OUTPUT->footer();