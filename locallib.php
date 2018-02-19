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
 * File : locallib.php 
 * Various functions for the block
 */

function displaydate($datetext) {
    global $CFG;

    $dateparts = explode("-", $datetext);

    if ($dateparts[2] && ($CFG->lang == 'fr')) {
        
        $day = "";

        if (date("l", mktime(0,0,0, $dateparts[2], $dateparts[1], $dateparts[0])) == "Monday") {

            $day = "Lundi";
        } else if (date("l", mktime(0,0,0, $dateparts[2], $dateparts[1], $dateparts[0])) == "Tuesday") {

            $day = "Mardi";
        } else if (date("l", mktime(0,0,0, $dateparts[2], $dateparts[1], $dateparts[0])) == "Wednesday") {

            $day = "Mercredi";
        } else if (date("l", mktime(0,0,0, $dateparts[2], $dateparts[1], $dateparts[0])) == "Thursday") {

            $day = "Jeudi";
        } else if (date("l", mktime(0,0,0, $dateparts[2], $dateparts[1], $dateparts[0])) == "Friday") {

            $day = "Vendredi";
        } else if (date("l", mktime(0,0,0, $dateparts[2], $dateparts[1], $dateparts[0])) == "Saturday") {

            $day = "Samedi";
        } else if (date("l", mktime(0,0,0, $dateparts[2], $dateparts[1], $dateparts[0])) == "Sunday") {

            $day = "Dimanche";
        }

        return $dateparts[2]."/".$dateparts[1]."/".$dateparts[0];
    } else {
        return date("l", mktime(0,0,0, $dateparts[2], $dateparts[1], $dateparts[0])).$datetext;
    }
}

function createandfillgroup($slot) {
    global $DB, $COURSE;

    $groupkey = "AutomaticGroupForSlot".$slot->id;

    $date = $DB->get_record('block_ucpslotbooking_date', array('id' => $slot->dateid))->datetext;
    $ordereddate = displaydate($date);
    $starttime = $DB->get_record('block_ucpslotbooking_time', array('id' => $slot->timeid))->starttime;
    $endtime = $DB->get_record('block_ucpslotbooking_time', array('id' => $slot->timeid))->endtime;

    $group = new stdClass();
    $group->courseid = $COURSE->id;
    $group->idnumber = '';
    $group->name = "Groupe du ".$ordereddate." de ".$starttime." à ".$endtime;
    $group->description = "";
    $group->descriptionformat = FORMAT_HTML;

    $groupid = groups_create_group($group);

    if (isset($groupid)) {

        $listbookings = $DB->get_records('block_ucpslotbooking_booking', array('slotid' => $slot->id));

        foreach ($listbookings as $booking) {

            $groupmember = new stdClass();
            $groupmember->groupid = $groupid;
            $groupmember->userid = $booking->userid;
            $groupmember->timeadded = time();

            if (!$DB->record_exists('groups_members',
                    array('groupid' => $groupid, 'userid' => $booking->userid))) {

                $DB->insert_record('groups_members', $groupmember);
            }
        }
    }
}
