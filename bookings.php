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
 * Initially developped for :
 * Université de Cergy-Pontoise
 * 33, boulevard du Port
 * 95011 Cergy-Pontoise cedex
 * FRANCE
 * 
 * Block to book time slots
 *  
 * @package    block_ucpslotbooking 
 * @author     Brice Errandonea <brice.errandonea@u-cergy.fr>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * 
 * 
 * File : bookings.php 
 * Lists the slots booked by students.
 */

global $DB, $OUTPUT, $PAGE, $USER, $CFG;

require_once('../../config.php');
require_once('locallib.php');
require_once($CFG->dirroot.'/calendar/lib.php');
require_once($CFG->libdir.'/csvlib.class.php');



// Check params.
$courseid = required_param('courseid', PARAM_INT);
$blockid = required_param('blockid', PARAM_INT);
$listid = required_param('listid', PARAM_INT);
$getremove = optional_param('remove', 0, PARAM_INT);
$empty = optional_param('empty', 0, PARAM_INT);
$emptyall = optional_param('all', 0, PARAM_INT);
$postcsv = optional_param('csvslotid', 0, PARAM_INT);
$creategroup = optional_param('slotid', 0, PARAM_INT);
$createallgroups = optional_param('allslots', 0, PARAM_INT);

$courseurl = new moodle_url('/course/view.php', array('id' => $courseid));
$coursecontext = context_course::instance($courseid);

// EXPORT CSV.
if ($postcsv != 0) {

    $csvexporter = new csv_export_writer('semicolon');

    if ($postcsv == -1) {

        $listallslots = $DB->get_records('block_ucpslotbooking_slot', array('listid' => $listid));

        foreach ($listallslots as $slot) {

            $date = $DB->get_record('block_ucpslotbooking_date', array('id' => $slot->dateid))->datetext;

            $formatteddate = displaydate($date);

            $time = $DB->get_record('block_ucpslotbooking_time', array('id' => $slot->timeid));

            $firstline = array();
            $firstline[] = utf8_decode($slot->shortcomment.", ".$formatteddate.", ".get_string('from',
                    'block_ucpslotbooking')." $time->starttime ".get_string('to',
                    'block_ucpslotbooking')." $time->endtime.");
            $csvexporter->add_data($firstline);

            $secondline = array();
            $secondline[] = utf8_decode(get_string('name', 'block_ucpslotbooking'));
            $secondline[] = utf8_decode(get_string('firstname', 'block_ucpslotbooking'));
            $secondline[] = utf8_decode(get_string('login', 'block_ucpslotbooking'));
            $secondline[] = utf8_decode(get_string('email', 'block_ucpslotbooking'));
            $csvexporter->add_data($secondline);

            $listbookings = $DB->get_records('block_ucpslotbooking_booking', array('slotid' => $slot->id));

            foreach ($listbookings as $booking) {

                $user = $DB->get_record('user', array('id' => $booking->userid));

                $line = array();
                $line[] = utf8_decode($user->lastname);
                $line[] = utf8_decode($user->firstname);
                $line[] = utf8_decode($user->username);
                $line[] = utf8_decode($user->email);
                $csvexporter->add_data($line);
            }

            $spaceline = array();
            $spaceline[] = "";
            $csvexporter->add_data($spaceline);
            $csvexporter->add_data($spaceline);
        }
    } else {

        $slot = $DB->get_record('block_ucpslotbooking_slot', array('id' => $postcsv));

        $date = $DB->get_record('block_ucpslotbooking_date', array('id' => $slot->dateid))->datetext;

        $formatteddate = displaydate($date);

        $time = $DB->get_record('block_ucpslotbooking_time', array('id' => $slot->timeid));

        $firstline = array();
        $firstline[] = utf8_decode($slot->shortcomment.", ".$formatteddate.", ".get_string('from',
                'block_ucpslotbooking')." $time->starttime ".get_string('to',
                'block_ucpslotbooking')." $time->endtime.");
        $csvexporter->add_data($firstline);

        $secondline = array();
        $secondline[] = utf8_decode(get_string('name', 'block_ucpslotbooking'));
        $secondline[] = utf8_decode(get_string('firstname', 'block_ucpslotbooking'));
        $secondline[] = utf8_decode(get_string('login', 'block_ucpslotbooking'));
        $secondline[] = utf8_decode(get_string('email', 'block_ucpslotbooking'));
        $csvexporter->add_data($secondline);

        $listbookings = $DB->get_records('block_ucpslotbooking_booking', array('slotid' => $slot->id));

        foreach ($listbookings as $booking) {

            $user = $DB->get_record('user', array('id' => $booking->userid));

            $line = array();
            $line[] = utf8_decode($user->lastname);
            $line[] = utf8_decode($user->firstname);
            $line[] = utf8_decode($user->username);
            $line[] = utf8_decode($user->email);
            $csvexporter->add_data($line);
        }
    }

    $csvexporter->download_file();
}

// Check access.
if (!$course = $DB->get_record('course', array('id' => $courseid))) {
    print_error('invalidcourse', 'block_simplehtml', $courseid);
}
require_login($course);
require_capability('block/ucpslotbooking:addinstance', $coursecontext);

// Get slots list.
$list = $DB->get_record('block_ucpslotbooking_list', array('id' => $listid));
if ($list->blockid != $blockid) {
    print_error('invalidcourse', 'block_simplehtml', $courseid);
}

// Header code.
$title = get_string('bookings', 'block_ucpslotbooking').' - '.$list->name;
$PAGE->set_url('/blocks/ucpslotbooking/bookings.php', array('courseid' => $courseid));
$PAGE->set_pagelayout('standard');
$PAGE->set_heading($title);
$PAGE->set_title($title);

// Navigation node.
$settingsnode = $PAGE->settingsnav->add($list->name);
$params = array('listid' => $listid, 'courseid' => $courseid, 'blockid' => $blockid);
$editurl = new moodle_url('/blocks/ucpslotbooking/bookings.php', $params);
$editnode = $settingsnode->add($list->name, $editurl);
$editnode->make_active();

$site = get_site();

// Page display.
echo $OUTPUT->header();

if ($creategroup) {

    $slotgroup = $DB->get_record('block_ucpslotbooking_slot', array('id' => $creategroup));

    createandfillgroup($slotgroup);

    $date = $DB->get_record('block_ucpslotbooking_date', array('id' => $slotgroup->dateid))->datetext;
    $ordereddate = displaydate($date);
    $starttime = $DB->get_record('block_ucpslotbooking_time', array('id' => $slotgroup->timeid))->starttime;
    $endtime = $DB->get_record('block_ucpslotbooking_time', array('id' => $slotgroup->timeid))->endtime;

    $groupname = "Groupe du ".$ordereddate." de ".$starttime." à ".$endtime;

    $groupnumbers = $DB->count_records('block_ucpslotbooking_booking', array('slotid' => $slotgroup->id));

    $groupinfo = new stdClass();
    $groupinfo->name = $groupname;
    $groupinfo->number = $groupnumbers;

    echo "<br><br><br>".get_string('group_created', 'block_ucpslotbooking', $groupinfo)."<br><br><br>";
}

if ($createallgroups) {

    $listslots = $DB->get_records('block_ucpslotbooking_slot', array('listid' => $listid));

    foreach ($listslots as $slotgroup) {

        createandfillgroup($slotgroup);
    }

    echo "<br><br><br>".get_string('groups_created', 'block_ucpslotbooking')."<br><br><br>";
}

if ($empty) {

    if ($DB->record_exists('event', array('modulename' => '', 'courseid' => $courseid,
        'userid' => $USER->id, 'instance' => $empty))) {

        $eventid = $DB->get_record('event', array('modulename' => '', 'courseid' => $courseid,
            'userid' => $USER->id, 'instance' => $empty));

        $event = calendar_event::load($eventid);
        $event->delete(false);
    }

    $DB->delete_records('block_ucpslotbooking_booking', array('slotid' => $empty));
}

if ($getremove) {

    $slotid = $DB->get_record('block_ucpslotbooking_booking', array('id' => $getremove))->slotid;

    if ($DB->record_exists('event', array('modulename' => '', 'courseid' => $courseid,
        'userid' => $USER->id, 'instance' => $slotid))) {

        $eventid = $DB->get_record('event', array('modulename' => '', 'courseid' => $courseid,
            'userid' => $USER->id, 'instance' => $slotid));

        $event = calendar_event::load($eventid);
        $event->delete(false);
    }

    $DB->delete_records('block_ucpslotbooking_booking', array('id' => $getremove));
}

if ($emptyall == 205) {
    echo get_string('confirmempty', 'block_ucpslotbooking');
    echo "&nbsp;&nbsp;";
    echo "<a href='$editurl&all=457'><button>OK</button></a>&nbsp";
    echo "<a href='$editurl'>".get_string('cancel', 'block_ucpslotbooking')."</a><br><br>";
}

if ($emptyall == 457) {
    $slots = $DB->get_records('block_ucpslotbooking_slot', array('listid' => $listid));
    foreach ($slots as $slot) {

        if ($DB->record_exists('event', array('modulename' => '', 'courseid' => $courseid,
            'userid' => $USER->id, 'instance' => $slot->id))) {

            $eventid = $DB->get_record('event', array('modulename' => '', 'courseid' => $courseid,
                'userid' => $USER->id, 'instance' => $slot->id));

            $event = calendar_event::load($eventid);
            $event->delete(false);
        }

        $DB->delete_records('block_ucpslotbooking_booking', array('slotid' => $slot->id));
    }
}

$backurl = "slots.php?list=$listid&courseid=$courseid&blockid=$blockid";
echo "<a href='$backurl'><button>".get_string('back', 'block_ucpslotbooking')."</button></a>&nbsp";
echo "<a href='$editurl&all=205'><button>".get_string('emptyall', 'block_ucpslotbooking')."</button></a><br><br>";

$sql = "SELECT d.datetext, t.starttime, t.endtime, s.* "
        . "FROM {block_ucpslotbooking_slot} s, {block_ucpslotbooking_date} d, {block_ucpslotbooking_time} t "
        . "WHERE s.listid = $listid AND d.id = s.dateid AND t.id = s.timeid "
        . "ORDER BY d.datetext, t.starttime, t.endtime";
$slots = $DB->get_recordset_sql($sql);

foreach ($slots as $slot) {

    echo "<h3>";
    if ($slot->shortcomment) {
        echo "$slot->shortcomment, ";
    }
    $date = displaydate($slot->datetext).", ";
    echo $date;
    echo get_string('from', 'block_ucpslotbooking')." $slot->starttime ";
    echo get_string('to', 'block_ucpslotbooking')." $slot->endtime.";
    echo "&nbsp;<a href='$editurl&empty=$slot->id' style='font-size:14'>".get_string('empty', 'block_ucpslotbooking')."</a>";
    echo "</h3>";
    ?>
    <div style="overflow-x:auto;">
    <table style='border:2px solid white;border_collapse:collapse'>
        <tr style='color:white'>
            <?php
            $firstlinestyle = "style = 'font-weight:bold;background-color:#731472'";
            echo "<td $firstlinestyle>".get_string('name', 'block_ucpslotbooking')."</td>";
            echo "<td $firstlinestyle>".get_string('firstname', 'block_ucpslotbooking')."</td>";
            echo "<td $firstlinestyle>".get_string('login', 'block_ucpslotbooking')."</td>";
            echo "<td $firstlinestyle>".get_string('email', 'block_ucpslotbooking')."</td>";
            echo "<td $firstlinestyle>".get_string('unenrol', 'block_ucpslotbooking')."</td>";
            ?>
        </tr>
        <?php
        $bookings = $DB->get_records('block_ucpslotbooking_booking', array('slotid' => $slot->id));
        foreach ($bookings as $booking) {
            $student = $DB->get_record('user', array('id' => $booking->userid));
            echo "<tr>";
            echo "<td>$student->lastname</td>";
            echo "<td>$student->firstname</td>";
            echo "<td>$student->username</td>";
            echo "<td>$student->email</td>";

            echo "<td><a href='$editurl&remove=$booking->id'>".get_string('unenrol',
                    'block_ucpslotbooking')."</a></td>";
            
            echo "</tr>";
        }
        ?>
    </table>
    </div>
    <?php

    $slotid = $slot->id;

    echo '<form enctype="multipart/form-data" action="'.$editurl.'" method="post">'
            . '<input name="csvslotid" type="hidden" value="'.$slotid.'" />'
            . '<p style="text-align: right;"><input type="submit" value="'.get_string('csvslot', 'block_ucpslotbooking').'"/></p>'
            . '</form>'
            . '<br><br>';   

    echo '<form enctype="multipart/form-data" action="'.$editurl.'" method="post">'
            . '<input name="slotid" type="hidden" value="'.$slotid.'" />'
            . '<p style="text-align: right;"><input type="submit" value="'.get_string('creategroup', 'block_ucpslotbooking').'"/></p>'
            . '</form>'
            . '<br><br>';
}
$slots->close();

echo '<form enctype="multipart/form-data" action="'.$editurl.'" method="post">'
        . '<input name="csvslotid" type="hidden" value="-1" />'
        . '<p style="text-align: center;"><input type="submit" value="'.get_string('csvlist', 'block_ucpslotbooking').'"/></p>'
        . '</form>';

$allslots = 1;

echo '<form enctype="multipart/form-data" action="'.$editurl.'" method="post">'
        . '<input name="allslots" type="hidden" value="'.$allslots.'" />'
        . '<p style="text-align: center;"><input type="submit" value="'.get_string('createallgroups', 'block_ucpslotbooking').'"/></p>'
        . '</form>'
        . '<br><br>';

?>

<?php
echo $OUTPUT->footer();
