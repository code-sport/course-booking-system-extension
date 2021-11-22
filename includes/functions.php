<?php

use CBSE\CBSE_PDF;
use CBSE\DocumentationPdf;

// TODO create own DTO
function cbse_courses_for_head($userId, $pastdays = 7, $futuredays = 7)
{
    if (!is_int($userId) || $userId < 0)
    {
        throw new Exception("userID must be a positive int.");
    }

    /* if (!is_int($pastdays) || $pastdays < 0) {
         throw new Exception("pastdays ($pastdays) must be a positive int.");
     }

     if (!is_int($futuredays)  || $futuredays < 0) {
         throw new Exception("futuredays ($futuredays) must be a positive int.");
     }
    */

    global $wpdb;
    $query = "SELECT `id` AS `course_id`, `column_id`, `event_id`, `event_start`, `event_end`, `description`, `" . $wpdb->prefix . "mp_timetable_bookings`.`date` as date, COUNT(`" . $wpdb->prefix . "mp_timetable_bookings`.`booking_id`) AS bookings, COUNT(`" . $wpdb->prefix . "mp_timetable_waitlists`.`waitlist_id`) AS waitings, `" . $wpdb->prefix . "mp_timetable_substitutes`.`user_id` AS substitutes_user_id, `note`";
    $query .= " FROM `" . $wpdb->prefix . "mp_timetable_data`";
    $query .= " JOIN `" . $wpdb->prefix . "mp_timetable_bookings` ON `" . $wpdb->prefix . "mp_timetable_data`.`id` = `" . $wpdb->prefix . "mp_timetable_bookings`.`course_id`";
    $query .= " LEFT JOIN `" . $wpdb->prefix . "mp_timetable_waitlists` ON `" . $wpdb->prefix . "mp_timetable_data`.`id` = `" . $wpdb->prefix . "mp_timetable_waitlists`.`course_id` AND `" . $wpdb->prefix . "mp_timetable_bookings`.`date` = `" . $wpdb->prefix . "mp_timetable_waitlists`.`date`";
    $query .= " LEFT JOIN `" . $wpdb->prefix . "mp_timetable_notes` ON `" . $wpdb->prefix . "mp_timetable_data`.`id` = `" . $wpdb->prefix . "mp_timetable_notes`.`course_id` AND `" . $wpdb->prefix . "mp_timetable_bookings`.`date` = `" . $wpdb->prefix . "mp_timetable_notes`.`date`";
    $query .= " LEFT JOIN `" . $wpdb->prefix . "mp_timetable_substitutes` ON `" . $wpdb->prefix . "mp_timetable_data`.`id` = `" . $wpdb->prefix . "mp_timetable_substitutes`.`course_id` AND `" . $wpdb->prefix . "mp_timetable_bookings`.`date` = `" . $wpdb->prefix . "mp_timetable_substitutes`.`date`";
    $query .= " WHERE ((`" . $wpdb->prefix . "mp_timetable_data`.user_id = %d AND `" . $wpdb->prefix . "mp_timetable_substitutes`.`user_id` IS NULL)OR `" . $wpdb->prefix . "mp_timetable_substitutes`.`user_id` = %d)";
    $query .= " AND DATE(`" . $wpdb->prefix . "mp_timetable_bookings`.`date`) > (NOW() - INTERVAL %d DAY)";
    $query .= " AND DATE(`" . $wpdb->prefix . "mp_timetable_bookings`.`date`) < (NOW() + INTERVAL %d DAY)";
    $query .= " GROUP BY  `" . $wpdb->prefix . "mp_timetable_bookings`.`date`, `" . $wpdb->prefix . "mp_timetable_bookings`.`course_id`";
    $query .= " ORDER BY `" . $wpdb->prefix . "mp_timetable_bookings`.`date` ASC, `" . $wpdb->prefix . "mp_timetable_data`.`event_start` ASC;";
    $timeslots = $wpdb->get_results($wpdb->prepare($query, $userId, $userId, $pastdays, $futuredays));

    return $timeslots;
}


function cbse_install_and_update()
{
    //TODO: Create own class!
    CBSE_PDF::installAndUpdate();
}


/**
 * Selects the courses on a specific date in a specific time.
 *
 * @param DateTime $timeFrom
 * @param DateTime $timeTo
 *
 * @return array|object|null
 */
function cbse_courses_in_time(DateTime $timeFrom, DateTime $timeTo)
{
    global $wpdb;

    $query = "SELECT `id` AS `course_id`, `column_id`, `event_id`, `event_start`, `event_end`, `" . $wpdb->prefix . "mp_timetable_bookings`.`date` as date, `" . $wpdb->prefix . "mp_timetable_substitutes`.`user_id` AS substitutes_user_id, `" . $wpdb->prefix . "mp_timetable_data`.user_id, TIMESTAMP(`" . $wpdb->prefix . "mp_timetable_bookings`.`date`,`event_start`) as event_start_timestamp";
    $query .= " FROM `" . $wpdb->prefix . "mp_timetable_data`";
    $query .= " JOIN `" . $wpdb->prefix . "mp_timetable_bookings`";
    $query .= ' ON `' . $wpdb->prefix . 'mp_timetable_data`.`id` = `' . $wpdb->prefix . 'mp_timetable_bookings`.`course_id`';
    $query .= " LEFT JOIN `" . $wpdb->prefix . "mp_timetable_substitutes`";
    $query .= " ON `" . $wpdb->prefix . "mp_timetable_data`.`id` = `" . $wpdb->prefix . "mp_timetable_substitutes`.`course_id`";
    $query .= " AND `" . $wpdb->prefix . "mp_timetable_bookings`.`date` = `" . $wpdb->prefix . "mp_timetable_substitutes`.`date`";
    $query .= " WHERE TIMESTAMP(`" . $wpdb->prefix . "mp_timetable_bookings`.`date`,`event_start`) >= '%s'";
    $query .= " AND  TIMESTAMP(`" . $wpdb->prefix . "mp_timetable_bookings`.`date`,`event_start`) < '%s'";
    $query .= " GROUP BY `" . $wpdb->prefix . "mp_timetable_bookings`.`date`, `" . $wpdb->prefix . "mp_timetable_bookings`.`course_id`";
    $query .= " ORDER BY `" . $wpdb->prefix . "mp_timetable_bookings`.`date` ASC, `" . $wpdb->prefix . "mp_timetable_data`.`event_start` ASC;";
    return $wpdb->get_results($wpdb->prepare($query, $timeFrom->format('Y-m-d H:i:s'), $timeTo->format('Y-m-d H:i:s')));
}

