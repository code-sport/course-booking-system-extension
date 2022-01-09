<?php

namespace CBSE\Database;

use function do_action;

class CoursesForHead extends DatabaseBase
{
    private int $userId;
    private int $pastDays;
    private int $futureDays;
    private $timeslots;

    public function __construct(int $userId, int $pastDays = 7, int $futureDays = 7)
    {
        $this->userId = $userId;
        $this->pastDays = $pastDays;
        $this->futureDays = $futureDays;

        do_action('qm/debug', "CoursesForHead -> userId: {$userId}, pastDays: {$pastDays}, futureDays: {$futureDays}");

        $this->loadFromDatabase();
    }

    private function loadFromDatabase()
    {
        global $wpdb;
        $query = "SELECT `id` AS `courseId`, `column_id` AS columnId, `event_id` as eventId, `event_start` AS eventStart, `event_end` as eventEnd, `description` AS eventDescription, `" . $this->datebaseTableName('mp_timetable_bookings') . "`.`date` as date, COUNT(`" . $this->datebaseTableName('mp_timetable_bookings') . "`.`booking_id`) AS bookings, COUNT(`" . $this->datebaseTableName('mp_timetable_waitlists') . "`.`waitlist_id`) AS waitings, `" . $this->datebaseTableName('mp_timetable_substitutes') . "`.`user_id` AS substitutes_user_id, `note`";
        $query .= " FROM `" . $this->datebaseTableName('mp_timetable_data') . "`";
        $query .= " JOIN `" . $this->datebaseTableName('mp_timetable_bookings') . "` ON `" . $this->datebaseTableName('mp_timetable_data') . "`.`id` = `" . $this->datebaseTableName('mp_timetable_bookings') . "`.`course_id`";
        $query .= " LEFT JOIN `" . $this->datebaseTableName('mp_timetable_waitlists') . "` ON `" . $this->datebaseTableName('mp_timetable_data') . "`.`id` = `" . $this->datebaseTableName('mp_timetable_waitlists') . "`.`course_id` AND `" . $this->datebaseTableName('mp_timetable_bookings') . "`.`date` = `" . $this->datebaseTableName('mp_timetable_waitlists') . "`.`date`";
        $query .= " LEFT JOIN `" . $this->datebaseTableName('mp_timetable_notes') . "` ON `" . $this->datebaseTableName('mp_timetable_data') . "`.`id` = `" . $this->datebaseTableName('mp_timetable_notes') . "`.`course_id` AND `" . $this->datebaseTableName('mp_timetable_bookings') . "`.`date` = `" . $this->datebaseTableName('mp_timetable_notes') . "`.`date`";
        $query .= " LEFT JOIN `" . $this->datebaseTableName('mp_timetable_substitutes') . "` ON `" . $this->datebaseTableName('mp_timetable_data') . "`.`id` = `" . $this->datebaseTableName('mp_timetable_substitutes') . "`.`course_id` AND `" . $this->datebaseTableName('mp_timetable_bookings') . "`.`date` = `" . $this->datebaseTableName('mp_timetable_substitutes') . "`.`date`";
        $query .= " WHERE ((`" . $this->datebaseTableName('mp_timetable_data') . "`.user_id = %d AND `" . $this->datebaseTableName('mp_timetable_substitutes') . "`.`user_id` IS NULL)OR `" . $this->datebaseTableName('mp_timetable_substitutes') . "`.`user_id` = %d)";
        $query .= " AND DATE(`" . $this->datebaseTableName('mp_timetable_bookings') . "`.`date`) > (NOW() - INTERVAL %d DAY)";
        $query .= " AND DATE(`" . $this->datebaseTableName('mp_timetable_bookings') . "`.`date`) < (NOW() + INTERVAL %d DAY)";
        $query .= " GROUP BY  `" . $this->datebaseTableName('mp_timetable_bookings') . "`.`date`, `" . $this->datebaseTableName('mp_timetable_bookings') . "`.`course_id`";
        $query .= " ORDER BY `" . $this->datebaseTableName('mp_timetable_bookings') . "`.`date` ASC, `" . $this->datebaseTableName('mp_timetable_data') . "`.`event_start` ASC;";
        $this->timeslots = $wpdb->get_results($wpdb->prepare($query, $this->userId, $this->userId, $this->pastDays, $this->futureDays));
    }

    /**
     * @return mixed
     */
    public function getTimeslots()
    {
        return $this->timeslots;
    }
}
